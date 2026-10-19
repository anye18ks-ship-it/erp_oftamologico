---
name: erp-inventario-kardex
description: >-
  Lógica de control de inventario físico y contable, almacenes múltiples (posOWHS/posOITW),
  entradas de mercancía (POSOIGN/POSIGN1), costos promedio ponderado (AvgPrice), secuencias de artículos (físicos vs servicios SV),
  compatibilidad con vistas legacy (v_sale_item) y reglas de kardex para el ERP actual y nuevo.
---

# Control de Inventarios, Almacenes Múltiples y Costeo Kardex

Esta skill contiene las reglas de negocio, fórmulas de costeo, gestión de almacenes y compatibilidad de base de datos para el inventario de la clínica oftalmológica bajo el modelo SAP Business One y su transición al nuevo ERP.

---

## 1. Clasificación Operativa: Artículos Físicos vs Servicios Clínicos (`posOITM`)

En el ERP, un registro de `posOITM` se comporta de dos formas completamente distintas mediante el flag `InvntItem`:

| Bandera / Parámetro | Artículo de Inventario (Físico) | Servicio / Intangible (Procedimiento Clínico) |
| :--- | :--- | :--- |
| **`InvntItem`** | `'Y'` | `'N'` |
| **Naturaleza** | Fármacos, lentes, monturas, descartables | Consultas médicas, cirugías, refracción, OCT |
| **Control de Existencia** | Maneja stock físico en almacenes (`OnHand`) | **No maneja existencias**. Facturación ilimitada sin bloqueos |
| **Código de Barras (`CodeBars`)** | Permitido / editable para escáner POS | **Bloqueado / No aplica** en interfaz |
| **Almacén Principal (`DfltWH`)** | Obligatorio (ej: `'01'`, `'02'`) | `NULL` en `posOITM` |
| **Enlace a `posOITW`** | Registra existencias reales en cada almacén habilitado | **Fila Dummy de compatibilidad** (`OnHand = 0.00`) para vistas |
| **Prefijo de Código** | 2-3 letras del grupo sanitizadas (ej: `DES0001`, `QUI0001`) | Prefijo reservado **`SV`** (ej: `SV0001`) |

---

## 2. Gestión de Almacenes (`posOWHS` y `posOITW`) y Compatibilidad Legacy

### 2.1 Almacenes Habilitados por Defecto
- Al registrar un nuevo artículo físico, **por defecto únicamente se habilita el Almacén Predeterminado (`DfltWH`)**.
- Los demás almacenes inician deshabilitados y con existencia 0.00, evitando que el usuario o el cajero resten mercancía de depósitos o quirófanos no asignados.

### 2.2 Solución de Compatibilidad con la Vista Legacy `v_sale_item`
La vista legacy `v_sale_item` (utilizada por el POS de facturación para buscar productos) fue construida históricamente con la siguiente condición:
```sql
INNER JOIN posOITW ON T0.ItemCode = T1.ItemCode AND T0.DfltWH = T1.WhsCode
```
> **Regla Crítica de Compatibilidad:**
> Para los **Servicios Clínicos** (`InvntItem = 'N'`), donde no existe stock físico y `DfltWH` es `NULL` en `posOITM`, **NO se debe alterar el DDL de la vista de la BD en producción**.
> En su lugar, el sistema crea automáticamente un registro lógico en `posOITW`:
> - `ItemCode` = Código del servicio (ej: `SV0001`)
> - `WhsCode` = Almacén predeterminado del sistema (ej: `'01'`)
> - `OnHand` = `0.00`
> Esto garantiza que los servicios aparezcan instantáneamente en el catálogo de ventas sin generar inconsistencias de inventario.

---

## 3. Secuencia Dinámica de Códigos de Artículo (`generarCodigoArticulo`)

Para asegurar uniformidad en los catálogos y evitar colisiones:
1. **Si la Naturaleza es Servicio Clínico (`InvntItem = 'N'`):**
   - El prefijo asignado es siempre **`SV`** (ej: `SV0001`).
   - Esto evita colisiones con el código reservado del sistema para seguros `SERV-ARS` y con grupos estándar.
2. **Si la Naturaleza es Artículo Físico (`InvntItem = 'Y'`):**
   - Se extraen las **2 a 3 primeras letras** del Grupo (`posOITB.ItmsGrpNam`), eliminando acentos, tildes y caracteres especiales (ej: `DES` para DESECHABLES, `QUI` para QUIRÚRGICOS). Si no hay grupo, el prefijo por defecto es `ART`.
3. **Correlativo Numérico con Padding:**
   - Se consulta el valor numérico máximo existente para dicho prefijo y se suma 1, rellenando con ceros a 4 dígitos:
   ```php
   $prefijo = ($esServicio) ? 'SV' : $prefijoGrupo;
   $nuevoCodigo = $prefijo . str_pad($maxSecuencia + 1, 4, '0', STR_PAD_LEFT); // Ej: SV0005, QUI0012
   ```
4. **Campo Editable:** El campo se pre-carga automáticamente pero permanece editable si se requiere ingresar una referencia específica de fabricante.

---

## 4. Entrada de Mercancía (`POSOIGN` / `POSIGN1`) y Costo Promedio Ponderado (`AvgPrice`)

Cada entrada de mercancía aumenta el stock físico y recalcula el costo unitario del artículo en `posOITM.AvgPrice`:

### 4.1 Fórmula del Costo Promedio Ponderado
$$\text{Nuevo AvgPrice} = \frac{(\text{Stock Actual} \times \text{AvgPrice Anterior}) + (\text{Cantidad Recibida} \times \text{Costo Unitario Entrada})}{\text{Stock Actual} + \text{Cantidad Recibida}}$$

### 4.2 Transacción SQL Atómica
Al procesar una entrada de mercancía:
1. Insertar cabecera en `POSOIGN` (`ObjType = '59'`).
2. Insertar líneas en `POSIGN1` con cantidad, costo y almacén destino (`WhsCode`).
3. Para cada línea con `InvntItem = 'Y'`:
   - Calcular nuevo `AvgPrice`.
   - Incrementar `posOITW.OnHand` en el almacén especificado:
     ```sql
     UPDATE posOITW SET OnHand = OnHand + :cantidad 
     WHERE ItemCode = :itemCode AND WhsCode = :whsCode;
     ```
   - Incrementar `posOITM.OnHand` y actualizar `AvgPrice`:
     ```sql
     UPDATE posOITM 
     SET OnHand = OnHand + :cantidad, AvgPrice = :nuevoAvgPrice 
     WHERE ItemCode = :itemCode;
     ```

---

## 5. Movimientos que Afectan el Kardex

| Tipo de Transacción | Tabla Cabecera | Tabla Detalle | Impacto en Stock | Restitución / Regla |
| :--- | :--- | :--- | :--- | :--- |
| **Factura de Venta** | `POSOINV` | `POSINV1` | **Disminuye** (`- Quantity`) | **Solo si `InvntItem = 'Y'`.** Si es `'N'`, no altera stock ni arroja error. |
| **Nota de Crédito** | `POSORIN` | `POSRIN1` | **Aumenta** (`+ Quantity`) | Solo si el motivo de anulación implica devolución física de mercancía (`InvntItem = 'Y'`). |
| **Nota de Débito** | `POSODIN` | `POSDIN1` | **Neutro** | Es un ajuste financiero/contable; no mueve existencias físicas. |
| **Entrada de Mercancía** | `POSOIGN` | `POSIGN1` | **Aumenta** (`+ Quantity`) | Aumenta stock y recalcula `AvgPrice`. |
| **Aprobación ARS Directa (100%)** | `AprobacionARS` | N/A | **Neutro** | No genera factura ni consumo de material de almacén. |

