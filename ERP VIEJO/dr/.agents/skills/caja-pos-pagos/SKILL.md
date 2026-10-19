---
name: caja-pos-pagos
description: >-
  Control de turnos de caja, conciliación y procesamiento multi-medio de pagos (efectivo, múltiples tarjetas,
  transferencias bancarias). Utilizar al implementar o modificar cobros en POS, aperturas/cierres de turno
  (diario_caja/caja), desglose de movimientos (diario_caja_det), detección de tarjetas por BIN y arqueo de caja.
---

# Control de Caja POS y Métodos de Pago Múltiples

Esta skill define la arquitectura contable y reglas operativas para la apertura, movimientos, arqueos, cierres de turnos de caja y cobros con múltiples métodos de pago (Efectivo, Múltiples Tarjetas, Transferencias).

---

## 1. Esquema de Tablas de Turnos y Movimientos

### 1.1 `caja` (Maestro de Terminales / Puntos de Venta)
* `id_caja` (INT, PK): Identificador físico del punto de venta o terminal (ej: 1 = Caja Principal).
* `nombre_caja`, `estado` ('A' = Activa, 'I' = Inactiva).

### 1.2 `diario_caja` (Cabecera de Turno Diario)
Registra la sesión de trabajo de un cajero/usuario:
* `id_diario_caja` (INT, PK Identity).
* `id_caja` (INT, FK).
* `id_usuario` (VARCHAR o INT): Cajero responsable.
* `fecha_apertura` (DATETIME): Hora de inicio de turno.
* `monto_apertura` (NUMERIC): Fondo inicial en efectivo (cambio base).
* `fecha_cierre` (DATETIME, NULL si el turno está abierto).
* `monto_cierre` (NUMERIC): Arqueo físico de cierre.
* `estado` ('A' = Abierto, 'C' = Cerrado).

### 1.3 `diario_caja_det` (Detalle de Movimientos del Turno)
Cada transacción realizada durante el turno genera un renglón:
* `id_diario_caja_det` (INT, PK Identity).
* `id_diario_caja` (INT, FK a `diario_caja`).
* `id_tipo_movimiento` (INT):
  * **`1`** = Apertura de Caja (fondo inicial).
  * **`2`** = Cobro con Tarjeta de Crédito / Débito.
  * **`3`** = Retiro de Efectivo / Gasto Menor / Entrega Parcial.
  * **`4`** = Cobro por Transferencia Bancaria.
  * **`5`** = Cobro en Efectivo.
* `id_documento` (INT): `C_DocEntry` de la factura de venta (`POSOINV`).
* `monto_factura` (NUMERIC): Total de la factura.
* `monto_caja` (NUMERIC): Importe específico cubierto por este método de pago.
* `comentario` (VARCHAR): Referencia de tarjeta (marca, últimos 4 dígitos), entidad bancaria y N° de transferencia.
* `fecha_movimiento` (DATETIME).

---

## 2. Métodos de Pago en el Punto de Venta (POS)

El sistema soporta división de pagos (Split Payments) donde una sola factura se cancela combinando medios:

### 2.1 Efectivo
* `monto_efectivo`: Monto aplicado a la factura.
* `efectivo_recibido`: Billetes entregados por el paciente/cliente.
* `cambio`: $$\text{Cambio Devuelto} = \text{Efectivo Recibido} - \text{Monto Efectivo}$$
* Se registra en `diario_caja_det` con `id_tipo_movimiento = 5`.

### 2.2 Pago con Múltiples Tarjetas (Crédito / Débito)
La interfaz permite iniciar con 1 tarjeta y agregar dinámicamente renglones adicionales mediante `+ Añadir otra tarjeta`:
* **Detección Automática de Franquicia por BIN (Expresiones Regulares):**
  - **Visa:** `/^4/`
  - **Mastercard:** `/^(5[1-5]|2[2-7])/`
  - **American Express:** `/^3[47]/`
  - **Discover:** `/^6(?:011|5)/`
* **Campos Registrados por Tarjeta:**
  - Monto asignado a esa tarjeta.
  - Franquicia detectada (Visa, Mastercard, etc.).
  - Últimos 4 dígitos.
  - N° de autorización o voucher del POS bancario (Verifone/Azul/Carnet).
* Cada tarjeta inserta su propio registro independiente en `diario_caja_det` con `id_tipo_movimiento = 2`.

### 2.3 Pago por Transferencia Bancaria
* Requiere validación de:
  - **Entidad Bancaria Emisora:** Banco Popular Dominicano, Banreservas, BHD, Scotiabank, APAP, etc.
  - **N° de Referencia / Comprobante Bancario:** Obligatorio cuando el monto > 0.
* Se registra en `diario_caja_det` con `id_tipo_movimiento = 4`.

### 2.4 Excepción Operativa: Coberturas 100% ARS
* Cuando un paciente es atendido con autorización ARS que cubre el 100% del servicio (`MontoResponsable = 0.00`):
  - **No se generan registros en `diario_caja_det`**.
  - No se altera el fondo de caja física ni se registra cobro en ventanilla, ya que no hubo transacción de dinero real.
  - La cuenta por cobrar se traslada directamente al módulo de reclamaciones y facturación por lotes ARS.

---

## 3. Validación de Cuadre en el Frontend
* La sumatoria de todos los medios debe coincidir exactamente con el total a pagar:
  $$\text{Total Pagado} = \text{Monto Efectivo} + \sum \text{Montos Tarjetas} + \text{Monto Transferencia}$$
* Si $\text{Total Pagado} < \text{Total Factura}$, el botón de cobrar permanece bloqueado.
* Si el paciente tiene seguro médico, el total a pagar corresponde al Copago del Paciente (`MontoResponsable`).

---

## 4. Arqueo y Cierre de Turno (`InformeCierreCaja.php`)
Al cerrar turno se totalizan los movimientos de `diario_caja_det`:
* **Ventas en Efectivo:** $\sum (\text{id\_tipo\_movimiento} = 5)$
* **Ventas con Tarjeta:** $\sum (\text{id\_tipo\_movimiento} = 2)$
* **Ventas por Transferencia:** $\sum (\text{id\_tipo\_movimiento} = 4)$
* **Retiros / Salidas:** $\sum (\text{id\_tipo\_movimiento} = 3)$
* **Efectivo Esperado en Caja:**
  $$\text{Esperado} = \text{Monto Apertura} + \text{Ventas Efectivo} - \text{Retiros}$$
* **Diferencia (Sobrante / Faltante):**
  $$\text{Diferencia} = \text{Monto Físico Cierre} - \text{Efectivo Esperado}$$

---

## 5. Diseño Escalable para el Nuevo ERP (Módulo de Tesorería)
Para el nuevo ERP:
1. **Desacoplamiento de Pagos:** Separar la tabla de pagos (`pagos` / `pago_metodos`) de la tabla de turnos (`turnos_caja`), permitiendo conciliar cobros remotos o transferencias directas sin obligar a que pertenezcan a un cajero físico de mostrador.
2. **Auditoría de Arqueos Ciegos:** Implementar arqueo ciego donde el cajero declara montos físicos antes de que el sistema le revele el esperado, previniendo alteraciones manuales.

