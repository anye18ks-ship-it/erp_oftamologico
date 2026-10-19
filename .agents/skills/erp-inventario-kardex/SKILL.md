---
name: erp-inventario-kardex
description: Control de inventario físico y contable para el nuevo ERP en PostgreSQL y TypeScript. Catálogo dual (artículos físicos vs servicios clínicos SV), almacenes múltiples, costeo promedio ponderado (WAC), libro mayor de kardex inmutable y reglas de prevención de stock negativo.
---

# Control de Inventarios, Almacenes Múltiples y Kardex

Esta skill define la arquitectura de inventario, reglas de costeo contable, clasificación operativa y trazabilidad en Kardex para el nuevo ERP clínico desarrollado en **PostgreSQL, Node.js y TypeScript**.

---

## 1. Clasificación Dual del Catálogo: Físicos vs Servicios

En el nuevo ERP, el catálogo unificado (`articulos`) maneja dos naturalezas operativas completamente diferenciadas a través del campo booleano `es_inventariable`:

| Propiedad | Artículo Físico (`es_inventariable = true`) | Servicio / Procedimiento (`es_inventariable = false`) |
| :--- | :--- | :--- |
| **Naturaleza** | Lentes, monturas, fármacos, colirios, descartables | Consultas oftalmológicas, refracción, cirugías, OCT |
| **Control de Existencia** | Control estricto por almacén (`stock_almacen`) | **Sin control de stock**. Facturación ilimitada sin bloqueos |
| **Código de Barras** | Permitido para escáner POS | **Bloqueado / No aplica** en interfaz |
| **Almacenes** | Requiere asignación a uno o más almacenes | **No asigna almacén** (no requiere filas dummy) |
| **Kardex** | Genera movimientos de entrada, salida y ajustes | **No genera movimientos de Kardex** |
| **Prefijo de Código** | 2 a 3 letras de la categoría (ej: `MON0001`, `DES0012`) | Prefijo reservado **`SV`** (ej: `SV0001`, `SV-CONS-01`) |

> **Mejora sobre el ERP Legado**: A diferencia del ERP anterior que requería inyectar filas ficticias en `posOITW` para que los servicios aparecieran en vistas de venta, el nuevo modelo relacional en PostgreSQL consulta directamente la tabla de artículos con `LEFT JOIN` a stock, tratando los servicios como entidades nativas sin existencias.

---

## 2. Generación Dinámica de Códigos de Artículo (`generateItemCode`)

Para asegurar estandarización y evitar colisiones:

```typescript
export async function generateItemCode(
  client: PoolClient,
  esInventariable: boolean,
  categoriaPrefijo?: string
): Promise<string> {
  const prefix = esInventariable ? (categoriaPrefijo?.toUpperCase().slice(0, 3) || 'ART') : 'SV';

  // Buscar el correlativo más alto para ese prefijo
  const query = `
    SELECT codigo FROM articulos 
    WHERE codigo LIKE $1 
    ORDER BY LENGTH(codigo) DESC, codigo DESC 
    LIMIT 1
  `;
  const res = await client.query(query, [`${prefix}%`]);

  let nextNum = 1;
  if (res.rows.length > 0) {
    const lastCode = res.rows[0].codigo as string;
    const numPart = lastCode.replace(prefix, '');
    const parsed = parseInt(numPart, 10);
    if (!isNaN(parsed)) nextNum = parsed + 1;
  }

  return `${prefix}${nextNum.toString().padStart(4, '0')}`; // Ej: SV0001, MON0024
}
```

---

## 3. Almacenes Múltiples y Asignación de Stock

* **Tabla `almacenes`**: `id`, `codigo`, `nombre`, `sucursal_id`, `es_principal`, `activo`.
* **Tabla `stock_almacen`**:
  - `articulo_id` (FK), `almacen_id` (FK), `existencia` (`DECIMAL(12, 3)`), `stock_minimo`, `stock_maximo`.
  - Llave primaria compuesta: `PRIMARY KEY (articulo_id, almacen_id)`.
* **Asignación Selectiva**:
  - Un artículo físico solo puede tener existencias y venderse en los almacenes expresamente habilitados para él.
  - Al crearse, se vincula por defecto únicamente al almacén principal del tenant, pudiendo activarse en almacenes adicionales desde la interfaz colapsable de almacenes permitidos.

---

## 4. Costeo Promedio Ponderado (Weighted Average Cost - WAC)

Cada entrada de mercancía (`entradas_mercancia`) recalcula el costo unitario del artículo en `articulos.costo_promedio`:

### 4.1 Fórmula
$$\text{Nuevo Costo Promedio} = \frac{(\text{Stock Actual} \times \text{Costo Actual}) + (\text{Cantidad Recibida} \times \text{Costo Entrada})}{\text{Stock Actual} + \text{Cantidad Recibida}}$$

* **Precisión Numérica**: Cálculos ejecutados con 4 decimales (`DECIMAL(18, 4)`).
* Si el stock actual es $\le 0$, el nuevo costo promedio pasa a ser exactamente el costo unitario de la nueva entrada.

### 4.2 Proceso de Entrada de Mercancía (`GoodsReceiptService.ts`)
```typescript
export async function processGoodsReceipt(client: PoolClient, data: GoodsReceiptInput) {
  // 1. Insertar cabecera de entrada
  const receiptRes = await client.query(
    `INSERT INTO entradas_mercancia (proveedor_id, almacen_destino_id, ncf_proveedor, fecha) VALUES ($1, $2, $3, $4) RETURNING id`,
    [data.proveedorId, data.almacenDestinoId, data.ncfProveedor, data.fecha]
  );
  const receiptId = receiptRes.rows[0].id;

  for (const item of data.detalles) {
    // 2. Bloquear artículo para recálculo de costo
    const artRes = await client.query(
      `SELECT costo_promedio, es_inventariable FROM articulos WHERE id = $1 FOR UPDATE`,
      [item.articuloId]
    );
    const art = artRes.rows[0];
    if (!art.es_inventariable) continue; // Los servicios no reciben mercancía

    // 3. Obtener stock global actual
    const stockRes = await client.query(
      `SELECT COALESCE(SUM(existencia), 0) as total_stock FROM stock_almacen WHERE articulo_id = $1`,
      [item.articuloId]
    );
    const currentStock = Number(stockRes.rows[0].total_stock);
    const currentCost = Number(art.costo_promedio);
    const qtyIn = Number(item.cantidad);
    const costIn = Number(item.costoUnitario);

    const newStock = currentStock + qtyIn;
    const newAvgCost = currentStock <= 0 
      ? costIn 
      : ((currentStock * currentCost) + (qtyIn * costIn)) / newStock;

    // 4. Actualizar costo promedio
    await client.query(
      `UPDATE articulos SET costo_promedio = $1 WHERE id = $2`,
      [newAvgCost, item.articuloId]
    );

    // 5. Incrementar existencia en el almacén de destino
    await client.query(
      `INSERT INTO stock_almacen (articulo_id, almacen_id, existencia)
       VALUES ($1, $2, $3)
       ON CONFLICT (articulo_id, almacen_id)
       DO UPDATE SET existencia = stock_almacen.existencia + $3`,
      [item.articuloId, data.almacenDestinoId, qtyIn]
    );

    // 6. Registrar en Kardex
    await recordKardexMovement(client, {
      articuloId: item.articuloId,
      almacenId: data.almacenDestinoId,
      tipoMovimiento: 'ENTRADA_COMPRA',
      documentoTipo: 'ENTRADA_MERCANCIA',
      documentoId: receiptId,
      stockAnterior: currentStock,
      cantidad: qtyIn,
      stockPosterior: newStock,
      costoUnitario: costIn
    });
  }
}
```

---

## 5. Libro Mayor de Kardex Inmutable (`kardex_movimientos`)

El nuevo ERP implementa un Kardex contable e inmutable para auditoría:

```sql
CREATE TABLE kardex_movimientos (
    id BIGSERIAL PRIMARY KEY,
    articulo_id UUID NOT NULL REFERENCES articulos(id),
    almacen_id UUID NOT NULL REFERENCES almacenes(id),
    tipo_movimiento VARCHAR(30) NOT NULL, -- 'ENTRADA_COMPRA', 'VENTA_POS', 'DEVOLUCION_NC', 'AJUSTE_INVENTARIO', 'TRANSFERENCIA'
    documento_tipo VARCHAR(30) NOT NULL,   -- 'FACTURA', 'NOTA_CREDITO', 'ENTRADA_MERCANCIA', 'AJUSTE'
    documento_id UUID NOT NULL,
    stock_anterior DECIMAL(12, 3) NOT NULL,
    cantidad DECIMAL(12, 3) NOT NULL,      -- Positivo en entradas, negativo en salidas
    stock_posterior DECIMAL(12, 3) NOT NULL,
    costo_unitario DECIMAL(18, 4) NOT NULL,
    comentario TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    created_by UUID
);

CREATE INDEX idx_kardex_articulo_fecha ON kardex_movimientos (articulo_id, created_at DESC);
```

---

## 6. Reglas de Venta y Prevención de Stock Negativo

1. **Validación en Venta de Mostrador (POS)**:
   - Si `es_inventariable = true`, se valida que `stock_almacen.existencia >= cantidad_solicitada`.
   - Si la existencia es insuficiente, el backend rechaza la transacción con código `INSUFFICIENT_STOCK` (o alerta en amarillo si la política del tenant permite stock negativo transitorio).
2. **Restitución por Nota de Crédito (`E34` / `B04`)**:
   - Solo restituye stock al almacén si el motivo de anulación es devolución física de producto (`tipo_devolucion = 'FISICA'`) y el ítem es físico (`es_inventariable = true`).
