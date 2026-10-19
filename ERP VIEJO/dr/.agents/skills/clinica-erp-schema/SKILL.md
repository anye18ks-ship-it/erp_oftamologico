---
name: clinica-erp-schema
description: >-
  Estructura completa de base de datos y lógica de negocio ERP (SAP Business One en SQL Server)
  para la clínica oftalmológica. Utilizar al consultar, crear o modificar tablas maestras,
  documentos de venta (POSOINV/POSINV1), notas de crédito (POSORIN/POSRIN1), entradas de mercancías
  (POSOIGN/POSIGN1), inventario (posOITM/posOITW), precios (posOPLN/positm1), socios/pacientes (posOCRD),
  aprobaciones ARS (AprobacionARS) y control de caja (diario_caja).
---

# Clínica ERP - Esquema de Base de Datos y Lógica de Negocio

Este documento contiene la referencia técnica de la arquitectura de datos y reglas de negocio del ERP de la clínica oftalmológica sobre Microsoft SQL Server / SAP Business One.

---

## 1. Maestros de Artículos, Inventario y Precios

### `posOITM` (Maestro de Artículos y Servicios)
* **`ItemCode`** (VARCHAR, PK): Código único del artículo.
* **`ItemName`** (VARCHAR): Descripción principal.
* **`FrgnName`** (VARCHAR): Nombre alternativo / extranjero.
* **`ItmsGrpCod`** (INT): Código de grupo (`posOITB.ItmsGrpCod`).
* **`CodeBars`** (VARCHAR): Código de barras.
* **`TaxCodeAr`** (VARCHAR): Código de impuesto (`posOSTC.Code`, ej: 'EXE', 'ITBIS18').
* **`DfltWH`** (VARCHAR): Almacén predeterminado (`posOWHS.WhsCode`).
* **`AvgPrice`** (NUMERIC): Costo promedio ponderado actual.
* **`OnHand`** (NUMERIC): Existencia total sumada en todos los almacenes.
* **Propiedades Operativas ERP:**
  * **`SellItem`** ('Y'/'N'): Si es 'Y', es elegible para facturar y aparece en `v_sale_item`.
  * **`PrchseItem`** ('Y'/'N'): Si es 'Y', es elegible para comprar y aparece en `entrada_mercancia`.
  * **`InvntItem`** ('Y'/'N'): Si es 'Y', controla existencias físicas (rebaja stock en ventas, aumenta en entradas). Si es 'N', es un **Servicio/Intangible** (consultas médicas, cirugías, procedimientos) y **NO rebaja ni bloquea por stock**.
* **Propiedades Especiales SAP (QryGroup):**
  * `QryGroup1` = Descripción editable en facturación.
  * `QryGroup2` = Artículo favorito / catálogo rápido.

### `positm1` (Precios por Lista)
* **`ItemCode`** (VARCHAR) + **`PriceList`** (INT) $\rightarrow$ PK compuesta.
* **`Price`** (NUMERIC): Precio de venta sin impuesto para esa lista.
* Siempre debe sincronizarse para todas las listas activas de `posOPLN`.

### `posOITW` (Existencia por Almacén)
* **`ItemCode`** (VARCHAR) + **`WhsCode`** (VARCHAR) $\rightarrow$ PK compuesta.
* **`OnHand`** (NUMERIC): Existencia física en ese almacén específico.

### `posOPLN` (Listas de Precios)
* **`ListNum`** (INT, PK), **`ListName`** (VARCHAR). Nunca asumir que el ID es siempre 1; consultar dinámicamente.

### `posOWHS` (Almacenes)
* **`WhsCode`** (VARCHAR, PK), **`WhsName`** (VARCHAR). Fallback a `SELECT TOP 1 WhsCode FROM posOWHS ORDER BY WhsCode ASC`.

### `posOSTC` (Códigos de Impuesto)
* **`Code`** (VARCHAR, PK), **`Name`** (VARCHAR), **`Rate`** (NUMERIC: % ITBIS, ej: 18.00 ó 0.00).

---

## 2. Documentos Transaccionales Maestro-Detalle

### Facturación de Venta: `POSOINV` (Cabecera) & `POSINV1` (Detalle)
* **ObjType**: `'13'` (Factura de Venta).
* **Cabecera (`POSOINV`):**
  * `C_DocEntry` (INT, PK Identity), `DocEntry` (INT), `DocNum` (INT).
  * `DocDate` (DATE), `CardCode` (VARCHAR), `CardName` (VARCHAR).
  * `NumAtCard` (VARCHAR, Secuencia interna ej: `SF130000001` o `F0000001`), `U_FACNUM` (NCF asignado ej: `E310000000001`, `E320000000001`).
  * `U_FacSerie` (VARCHAR, ej: `'e31'`, `'e32'`, `'B01'`), `U_NCFVence` (DATE).
  * `DocTotal` (NUMERIC), `DiscSum` (NUMERIC), `VatSum` (NUMERIC), `ExtraDays` (INT: 0=Contado, >0=Crédito).
  * `DocStatus` ('O' = Abierta, 'C' = Cerrada).
* **Detalle (`POSINV1`):**
  * `C_DocEntry` (FK), `LineNum` (0-indexed), `ItemCode`, `Dscription`, `Quantity`, `PriceBefDi`, `Price`, `DiscPrcnt`, `LineTotal`, `VatPrcnt`, `VatSum`, `TaxCode`, `WhsCode`.
* **Regla de Stock en Venta:** Para cada línea con `posOITM.InvntItem = 'Y'`:
  * `UPDATE posOITM SET OnHand = OnHand - :cant WHERE ItemCode = :code`
  * `UPDATE posOITW SET OnHand = OnHand - :cant WHERE ItemCode = :code AND WhsCode = :whs`
  *(Si `InvntItem = 'N'`, no se ejecuta ningún descuento).*

### Notas de Crédito: `POSORIN` (Cabecera) & `POSRIN1` (Detalle)
* **ObjType**: `'14'` (Nota de Crédito).
* **Cabecera (`POSORIN`):** `C_DocOrigenNum` (FK a `POSOINV.C_DocEntry`), `U_TipoNC` (FK a `TIPOS_ANULACION.code`), `U_FACNUM` (NCF e34 o B04).
* **Regla de Devolución:** Si `U_TipoNC` o `TIPOS_ANULACION.Name` indica devolución física (`06, 07, 08, 14, 16, 21, 23, 25, 38` ó contiene 'DEVOLUCION', 'MERCANCIA', 'PRODUCTO'), restituye existencia en `posOITM` y `posOITW` únicamente para artículos con `InvntItem = 'Y'`.

### Notas de Débito: `POSODIN` (Cabecera) & `POSDIN1` (Detalle)
* **Controlador dedicado:** `NotaDebitoController.php` (desacoplado de Nota de Crédito y Factura).
* **Cabecera (`POSODIN`):**
  * `C_DocEntry` (INT, PK Identity).
  * `C_DocOrigenNum` (INT, FK a `POSOINV.C_DocEntry` de la factura afectada).
  * `NumAtCard` (VARCHAR, Secuencia interna con prefijo `D`, ej: `D0000001`).
  * `U_FACNUM` (NCF asignado con serie `E33` para e-CF o `B03` para NCF tradicional).
  * `U_NCF_Modificado` (VARCHAR, NCF original de la factura que se debita).
  * `DocTotal` (NUMERIC), `VatSum` (NUMERIC).
* **Detalle (`POSDIN1`):**
  * Líneas con cargos adicionales, intereses moratorios o diferencias de precio.
* **Regla de Kardex:** **Neutro** (no afecta existencias físicas en `posOITM` ni `posOITW`).

### Entradas de Mercancía: `POSOIGN` (Cabecera) & `POSIGN1` (Detalle)
* **ObjType**: `'59'` (Entrada de Mercancía / Recepción de Compras).
* **Fórmula de Costo Promedio Ponderado (`AvgPrice`):**
  $$\text{Nuevo AvgPrice} = \frac{(\text{Stock Anterior} \times \text{Costo Anterior}) + (\text{Cant. Recibida} \times \text{Costo Unitario})}{\text{Stock Anterior} + \text{Cant. Recibida}}$$
* Aumenta `posOITM.OnHand` y `posOITW.OnHand`.

---

## 3. Seguros Médicos y Aprobaciones ARS

### `AprobacionARS`
* **`Id_Aprobacion`** / `IdARS` (INT, PK Identity).
* **`DocEntry_Factura`** (INT, FK a `POSOINV.C_DocEntry`, `NULL` si es Cobertura 100%).
* **`NumFactura`** (VARCHAR): Número de factura del paciente o `'COBERTURA 100%'`.
* **`DocEntry_FacturaARS`** (INT, FK a `POSOINV.C_DocEntry` de la factura de lote emitida a la ARS).
* **`NumFacturaARS`** (VARCHAR, NCF `E31`/`B01` emitido a la ARS).
* **`CardCode_ARS`** (VARCHAR): Código del seguro / ARS (ej: 'ARS001').
* **`NumAprobacion`** (VARCHAR): Número de autorización otorgado por la ARS.
* **`MontoAprobado`** (NUMERIC): Monto cubierto por la aseguradora.
* **`PorcentajeCober`** (NUMERIC): % de cobertura.
* **`MontoResponsable`** (NUMERIC): Copago a cargo del paciente (`DocTotal - MontoAprobado`).
* **`Status`**:
  * `'P'` = Pendiente de facturar a la ARS.
  * `'F'` = Facturado a la ARS (incluido en lote fiscal `E31`).
  * `'X'` = Anulado / Rechazado.

### `trazabilidad_reclamacion_ars` (Gestión de Cobranzas y Glosas)
* `id_trazabilidad` (INT, PK Identity).
* `DocEntry_Factura` (INT, FK a `POSOINV` del lote ARS).
* `Estado`: `'PENDIENTE'`, `'COBRADA'`, `'RECHAZADA'`, `'NOTA_CREDITO'`.
* `MontoReclamado`, `MontoCobrado`, `MontoGlosa`.
* `ReferenciaPago` (depósito/cheque), `DocEntry_NC`, `NCF_NC`.

---

## 4. Control de Caja y Flujo de Efectivo

### `diario_caja` (Cabecera de Turno)
* `id_diario_caja` (PK), `id_caja`, `id_usuario`, `fecha_apertura`, `fecha_cierre`, `monto_apertura`, `monto_cierre`, `estado` ('A'/'C').

### `diario_caja_det` (Movimientos de Turno)
* `id_diario_caja_det` (PK), `id_diario_caja` (FK).
* `id_tipo_movimiento`: `1` = Apertura, `2` = Cobro Tarjeta, `3` = Retiro, `4` = Cobro Transferencia, `5` = Cobro Efectivo.
* `id_documento` (`C_DocEntry` de la factura), `monto_factura`, `monto_caja`, `comentario`.
* *(Las transacciones con cobertura ARS 100% no se registran en `diario_caja_det` porque no involucran dinero físico en ventanilla).*

---

## 5. Reglas de Transacciones PDO
* Toda operación que afecte cabecera, detalle, inventario o NCF debe ejecutarse dentro de `$pdo->beginTransaction()` y cerrarse con `$pdo->commit()` o `$pdo->rollBack()`.
* Utilizar sentencias preparadas con parámetros nombrados o posicionales para evitar inyección SQL.

---

## 6. Mapeo de Nombres Canónicos para el Nuevo ERP
Al diseñar el nuevo ERP, se recomienda abandonar la nomenclatura críptica de SAP B1 y adoptar nombres limpios:

| Tabla Legacy SAP B1 | Nombre Canónico Nuevo ERP | Propósito |
| :--- | :--- | :--- |
| `posOITM` | `articulos` | Catálogo de artículos y servicios |
| `posOWHS` | `almacenes` | Sucursales y almacenes físicos |
| `posOITW` | `articulo_almacen` | Stock por almacén |
| `posOPLN` / `positm1` | `listas_precio` / `articulo_precios` | Precios configurables |
| `posOCRD` | `socios_negocio` / `pacientes` | Clientes, ARS y proveedores |
| `POSOINV` / `POSINV1` | `facturas` / `factura_detalles` | Ventas al paciente y lotes ARS |
| `POSORIN` / `POSRIN1` | `notas_credito` / `nota_credito_detalles` | Anulaciones y devoluciones (E34/B04) |
| `POSODIN` / `POSDIN1` | `notas_debito` / `nota_debito_detalles` | Cargos y diferencias (E33/B03) |
| `AprobacionARS` | `autorizaciones_ars` | Reclamaciones y autorizaciones médicas |

