---
name: erp-data-architecture
description: Directrices de arquitectura de datos relacional para PostgreSQL en el nuevo ERP. Normalización desde cero, nombres limpios, tipos de datos, precisión monetaria, índices, integridad referencial y mapeo conceptual desde el ERP legado.
---

# Arquitectura de Datos y Modelo Relacional (PostgreSQL)

Esta skill define los principios, estándares de modelado relacional, convenciones de nomenclatura y estructura de entidades para el nuevo ERP clínico y oftalmológico desarrollado en **PostgreSQL**.

> **Principio Fundamental**: La base de datos del nuevo ERP **se diseña desde cero**. Se descarta la nomenclatura y limitaciones del modelo legado de SAP Business One (`posOITM`, `POSOINV`, `posOCRD`, etc.) en favor de un diseño normalizado, limpio, escalable y optimizado para PostgreSQL.

---

## 1. Convenciones y Estándares de Diseño

1. **Nomenclatura Limpia y Consistente**:
   - Nombres de tablas en **minúsculas**, en **plural** o singular coherente (`pacientes`, `articulos`, `facturas`, `pagos`, `almacenes`).
   - Nombres de columnas en `snake_case` (`fecha_emision`, `monto_total`, `ncf_asignado`).
   - Claves primarias: `id` (de tipo `BIGSERIAL` o `UUID v4` según el volumen).
   - Claves foráneas: `{entidad_singular}_id` (ej: `paciente_id`, `factura_id`, `almacen_id`).
2. **Tipos de Datos y Precisión Numérica**:
   - **Moneda e Importes**: `DECIMAL(18, 2)` (ej: RD$ 1,500.50). Jamás usar `FLOAT` o `REAL`.
   - **Costos Unitarios y Precios con Desglose**: `DECIMAL(18, 4)` para evitar errores de redondeo en costeo promedio ponderado.
   - **Cantidades de Inventario**: `DECIMAL(12, 3)` o `INTEGER` según la unidad de medida.
   - **Fechas y Timestamps**: `TIMESTAMPTZ` (timestamp con zona horaria) para trazabilidad universal.
   - **Textos y Códigos**: `VARCHAR(N)` o `TEXT`.
3. **Auditoría e Integridad**:
   - Toda tabla transaccional debe incluir:
     - `created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP`
     - `updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP`
     - `created_by UUID / BIGINT`
     - `deleted_at TIMESTAMPTZ NULL` (Soft delete para entidades maestras).
   - Claves foráneas obligatorias con `ON DELETE RESTRICT` en registros contables para evitar eliminación accidental.
4. **Indexación Estratégica**:
   - Índices B-Tree en todas las claves foráneas (`FOREIGN KEY`).
   - Índices compuestos para búsquedas frecuentes (ej: `(tenant_id, estado)` o `(paciente_id, fecha_emision)`).
   - Índices únicos parciales para códigos activos:
     ```sql
     CREATE UNIQUE INDEX uq_articulo_codigo ON articulos (codigo) WHERE deleted_at IS NULL;
     ```

---

## 2. Mapa Conceptual de Dominios del Nuevo ERP

A continuación se presenta la correspondencia conceptual entre los módulos de negocio y las nuevas tablas diseñadas para PostgreSQL:

```
[ Pacientes & Médicos ] ──────> [ Facturas (Ventas) ] <────── [ Comprobantes DGII ]
          │                             │                              │
          ▼                             ▼                              ▼
[ Aprobaciones ARS ]             [ Pagos (POS) ]              [ Factura Lote ARS ]
          │                             │
          ▼                             ▼
[ Lote Reclamaciones ]          [ Turnos de Caja ]
                                        │
[ Catálogo Artículos ] <─────── [ Kardex / Almacenes ]
```

### 2.1 Dominio: Personas, Pacientes y Profesionales
* **`pacientes`** (anteriormente `posOCRD` con tipo cliente):
  - `id`, `codigo_expediente`, `nombre`, `apellido`, `tipo_documento` ('CEDULA', 'PASAPORTE', 'RNC'), `numero_documento`, `telefono`, `email`, `direccion`, `fecha_nacimiento`, `genero`.
* **`medicos` / `oftalmologos`**:
  - `id`, `usuario_id`, `nombres`, `apellidos`, `exequatur`, `especialidad`, `porcentaje_comision`.
* **`aseguradoras_ars`** (anteriormente `posOCRD` tipo aseguradora):
  - `id`, `rnc`, `razon_social`, `nombre_comercial`, `telefono`, `dias_credito`.
* **`afiliaciones_seguro_paciente`**:
  - Vincula un paciente con una ARS, almacenando número de afiliado / carnet y plan de salud.

### 2.2 Dominio: Catálogo Dual de Artículos y Servicios
* **`articulos`** (anteriormente `posOITM`):
  - `id`, `codigo`, `descripcion`, `nombre_comercial`, `categoria_id`, `codigo_barra`.
  - `es_inventariable` (`BOOLEAN`):
    - `true`: Artículo físico (monturas, cristales, fármacos, descartables). Controla stock y requiere almacén.
    - `false`: Servicio médico / procedimiento clínico (prefijo reservado `SV`). No maneja stock ni códigos de barra.
  - `costo_promedio` (`DECIMAL(18, 4)`), `tasa_itbis` (`DECIMAL(5, 2)`).
  - `activo` (`BOOLEAN`).
* **`listas_precio`** y **`precios_articulo`** (anteriormente `posOPLN` / `positm1`):
  - Permite múltiples listas de precios por sucursal o canal con vigencia y moneda.

### 2.3 Dominio: Inventario, Almacenes y Kardex
* **`almacenes`** (anteriormente `posOWHS`):
  - `id`, `codigo`, `nombre`, `sucursal_id`, `es_principal`.
* **`stock_almacen`** (anteriormente `posOITW`):
  - `articulo_id`, `almacen_id`, `cantidad_existencia`, `stock_minimo`, `stock_maximo`.
  - Llave primaria compuesta `(articulo_id, almacen_id)`.
* **`entradas_mercancia`** y **`entrada_mercancia_detalles`** (anteriormente `POSOIGN` / `POSIGN1`):
  - Registran compras a proveedores y recepciones, recalculando el costo promedio ponderado.
* **`kardex_movimientos`**:
  - Libro mayor inmutable que registra todo ingreso, salida o transferencia con `stock_anterior`, `cantidad`, `stock_posterior`, `costo_unitario` y documento origen.

### 2.4 Dominio: Facturación de Ventas y Comprobantes
* **`facturas`** (anteriormente `POSOINV`):
  - `id`, `correlativo_interno` (`F0000001`), `tipo_comprobante` ('E31', 'E32', 'B01', 'B02', etc.), `ncf`, `ncf_modificado` (para notas de crédito/débito), `fecha_vencimiento_ncf`.
  - `paciente_id`, `medico_id`, `subtotal`, `descuento_total`, `itbis_total`, `total_general`.
  - `estado` ('BORRADOR', 'EMITIDA', 'PAGADA', 'ANULADA').
  - `estado_dgii` (`C_Posted`: 'N', 'F', 'P', 'Y', 'R', 'E').
  - `track_id_dgii`, `codigo_seguridad_qr`.
* **`factura_detalles`** (anteriormente `POSINV1`):
  - `id`, `factura_id`, `articulo_id`, `descripcion`, `cantidad`, `precio_unitario`, `descuento_porcentaje`, `itbis_monto`, `total_linea`, `almacen_id`.
* **`notas_credito`** y **`notas_debito`** (anteriormente `POSORIN` / `POSODIN`):
  - Documentos fiscales vinculados a una factura origen con motivo de anulación oficial de la DGII.

### 2.5 Dominio: Pagos y Control de Cajas (POS)
* **`cajas_terminales`** (anteriormente `caja`):
  - Puntos de cobro físicos (`caja_id`, `nombre`, `sucursal_id`, `activa`).
* **`turnos_caja`** (anteriormente `diario_caja`):
  - `id`, `caja_id`, `usuario_id`, `fecha_apertura`, `monto_apertura`, `fecha_cierre`, `monto_cierre_declarado` (arqueo ciego), `monto_cierre_sistema`, `diferencia`, `estado`.
* **`pagos`** (anteriormente `diario_caja_det`):
  - `id`, `turno_caja_id`, `factura_id`, `metodo_pago` ('EFECTIVO', 'TARJETA', 'TRANSFERENCIA').
  - `monto_pago`, `monto_recibido`, `cambio_devuelto`.
  - `tarjeta_franquicia`, `tarjeta_ultimos_digitos`, `tarjeta_autorizacion`.
  - `banco_origen`, `referencia_transferencia`, `comprobante_deposito_url`.

### 2.6 Dominio: Aprobaciones ARS y Cobranzas
* **`autorizaciones_ars`** (anteriormente `AprobacionARS`):
  - `id`, `paciente_id`, `aseguradora_id`, `numero_autorizacion`, `monto_aprobado`, `porcentaje_cobertura`, `copago_paciente`.
  - `factura_paciente_id` (`NULL` si es cobertura 100%).
  - `factura_lote_ars_id` (FK a la factura de crédito fiscal emitida a la aseguradora).
  - `estado` ('PENDIENTE', 'FACTURADO_ARS', 'ANULADO').
* **`lotes_facturacion_ars`** y **`trazabilidad_glosas_ars`**:
  - Control de envíos masivos, recepción de pagos de la ARS, registro de glosas médicas y conciliación de saldos.

---

## 3. Reglas de Transaccionalidad en PostgreSQL (Node.js con `pg`)

1. **Uso de Transacciones Atómicas**:
   Toda creación de factura que descuenta stock, consume NCF e inserta pagos debe ejecutarse dentro de un bloque transaccional:
   ```typescript
   const client = await pool.connect();
   try {
     await client.query('BEGIN');
     // 1. Asignar NCF con SELECT ... FOR UPDATE
     // 2. Insertar cabecera de factura
     // 3. Insertar líneas y actualizar stock
     // 4. Registrar pagos en turno de caja
     // 5. Encolar job de firma digital e-CF
     await client.query('COMMIT');
   } catch (error) {
     await client.query('ROLLBACK');
     throw error;
   } finally {
     client.release();
   }
   ```
2. **Bloqueo Pesimista para Concurrencia**:
   Para evitar números de factura o secuencias NCF duplicadas bajo alta concurrencia en ventanilla, usar siempre:
   ```sql
   SELECT secuencia_actual, limite_secuencia 
   FROM secuencias_ncf 
   WHERE tipo_ncf = $1 AND activo = true 
   FOR UPDATE;
   ```
