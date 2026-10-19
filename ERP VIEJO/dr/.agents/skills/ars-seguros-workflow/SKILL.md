---
name: ars-seguros-workflow
description: >-
  Flujo integral de gestión, autorización, facturación por lotes, cobranza y trazabilidad
  de seguros médicos (ARS). Utilizar al consultar o programar lógica de aprobaciones ARS
  (AprobacionARS), coberturas 100% sin factura, facturación masiva con NCF de crédito fiscal (E31/B01),
  gestión de cobros/glosas/rechazos (trazabilidad_reclamacion_ars), notas de crédito a ARS y reportes PDF.
---

# Flujo Integral de Seguros Médicos (ARS), Aprobaciones y Facturación por Lotes

Esta skill documenta el ciclo de vida completo de las reclamaciones de seguros médicos, autorizaciones, facturación por lotes a aseguradoras y gestión de cobro/glosas en el ERP clínico.

---

## 1. Ciclo de Vida de una Autorización / Aprobación ARS

La tabla principal es **`AprobacionARS`** (vinculada con `posOCRD` para pacientes y aseguradoras):

```sql
AprobacionARS:
- IdARS (INT, PK Identity)
- CardCode (VARCHAR): Código del paciente (ej: 'C0001')
- CardName (VARCHAR): Nombre del paciente
- NumExpediente (VARCHAR): Expediente clínico
- NumAfiliado (VARCHAR): N° carnet / seguro del paciente
- CardCode_ARS (VARCHAR): Código SAP de la ARS (ej: 'S0007')
- NumAprobacion (VARCHAR): Número de autorización otorgado por la ARS
- FechaAprobacionD (DATE), FechaVencimientoD (DATE)
- MontoAprobado (NUMERIC): Monto que cubre la ARS
- PorcentajeCober (NUMERIC): % de cobertura (ej: 80.00, 100.00)
- MontoResponsable (NUMERIC): Copago a pagar por el paciente
- DocEntry_Factura (INT): ID de la factura emitida al paciente (NULL si cobertura 100%)
- NumFactura (VARCHAR): Número de factura del paciente o 'COBERTURA 100%'
- DocEntry_FacturaARS (INT): ID de la factura emitida en lote a la ARS
- NumFacturaARS (VARCHAR): NCF fiscal emitido a la ARS (ej: E310000000001)
- TipoServicio (VARCHAR): Consulta médica, Materiales, Cirugía, etc.
- Status (VARCHAR):
    'P' = Pendiente por facturar a la ARS
    'F' = Facturado a la ARS (incluido en lote)
    'X' = Anulado
```

---

## 2. Casos Operativos de Aprobación

### 2.1 Cobertura Parcial (Copago del Paciente - Prioridad Fiscal DGII)
* La aseguradora cubre una porción (`MontoAprobado`) y el paciente paga la diferencia (`MontoResponsable` / Copago).
* **Regla Fiscal y Contable DGII:**
  - Para evitar duplicidad de ingresos ante la DGII (declarar la factura del paciente E32 completa y luego el lote ARS E31), la factura al paciente en `POSOINV` tiene como total fiscal oficial `DocTotal = MontoResponsable` (el Copago).
  - En `POSINV1`, los totales de línea (`LineTotal`) se ajustan proporcionalmente para que la suma exacta coincida con `MontoResponsable`, registrando la cobertura de la ARS como descuento/deducción para el sellador e-CF (`GetInvoiceDataForXML`).
  - En `AprobacionARS`, `DocEntry_Factura` apunta a la factura del paciente, `Status = 'P'`, y conserva intactos `MontoAprobado` (para la futura factura E31 a la ARS) y `MontoResponsable`.
  - En `xmlceyes` y en caja (`diario_caja`), el comprobante y el cobro reflejan el Copago efectivamente pagado.

### 2.2 Cobertura 100% Directa (Sin Factura al Paciente)
* Ocurre cuando el seguro cubre la totalidad del servicio (`MontoResponsable = 0.00`).
* **Regla Fiscal y Contable:**
  - **NO** se emite factura fiscal al paciente (`POSOINV`).
  - **NO** se consume NCF de consumo (`E32` ni `B02`).
  - **NO** se inserta en `ecf_xml_invoice` ni en `diario_caja`.
  - Se registra directamente la autorización en `AprobacionARS` con:
    - `DocEntry_Factura = NULL`
    - `NumFactura = 'COBERTURA 100%'`
    - `Status = 'P'` (Pendiente por facturar a la ARS)
* **Sincronización con el Flujo de Citas / Pacientes del Día (`ListaHoy`):**
  - El paciente debe quedar marcado inmediatamente como **"Atendido Hoy / Facturado Hoy"**.
  - En la consulta del día (`FacturaController.php`), se excluye al paciente de la lista *"En Consulta / Pendientes"* para evitar que el personal lo vuelva a llamar o lo mantenga como en espera, garantizando que el expediente clínico y administrativo quede cerrado para la jornada.

---

## 3. Facturación por Lotes a Aseguradoras (ARS)

Las aseguradoras no se facturan paciente por paciente; se agrupan autorizaciones pendientes (`Status = 'P'`) de una misma ARS y se emite una sola Factura de Crédito Fiscal (`E31` o `B01`):

1. **Selección y Validación:**
   - Todas las aprobaciones del lote deben pertenecer a la misma ARS (`CardCode_ARS`).
2. **Creación de Cabecera en `POSOINV`:**
   - `CardCode` = Código de la ARS (ej: `S0007`).
   - `CardName` = Razón social de la ARS.
   - `U_FacSerie` = `E31` (o `B01`).
   - `Filler` = `'LOTE ARS'`.
   - `DocTotal` = Sumatoria de `MontoAprobado` de todas las reclamaciones incluidas.
3. **Creación de Detalle en `POSINV1`:**
   - Cada renglón representa un tipo de servicio o procedimiento autorizado.
4. **Actualización de `AprobacionARS`:**
   - Para cada `IdARS` incluido:
     ```sql
     UPDATE AprobacionARS 
     SET Status = 'F', 
         DocEntry_FacturaARS = :docEntryARS, 
         NumFacturaARS = :ncfARS,
         FechaFacturaARS = GETDATE()
     WHERE IdARS = :idARS;
     ```

---

## 4. Trazabilidad de Reclamaciones y Gestión de Cobro (`trazabilidad_reclamacion_ars`)

Para registrar la gestión de cobranza de las facturas emitidas a las ARS:

```sql
trazabilidad_reclamacion_ars:
- id_trazabilidad (INT, PK Identity)
- DocEntry_Factura (INT, FK a POSOINV.C_DocEntry)
- Estado (VARCHAR):
    'PENDIENTE'    = En espera de respuesta de la aseguradora.
    'COBRADA'      = Pago liquidado por la ARS.
    'RECHAZADA'    = Reclamación glosada o rechazada.
    'NOTA_CREDITO' = Nota de crédito fiscal aplicada.
- FechaEstado (DATETIME)
- Motivo (VARCHAR): Causa del rechazo/glosa (ej: Carnet vencido, falta de informe).
- ReferenciaPago (VARCHAR): N° cheque, depósito o comprobante bancario.
- DocEntry_NC (INT): ID de nota de crédito si fue anulada.
- NCF_NC (VARCHAR): NCF electrónico E34 o B04 de la nota de crédito.
- Observaciones (VARCHAR)
- Usuario (VARCHAR)
```

---

## 5. Emisión de Notas de Crédito a Facturas ARS

Si una factura o lote ARS es rechazado/glosado:
1. **Regla de Agrupación Fiscal DGII:**
   - No se pueden mezclar facturas tradicionales (`B01`) con electrónicas (`E31`).
   - Facturas con `E31` deben generar Nota de Crédito `E34`.
   - Facturas con `B01` deben generar Nota de Crédito `B04`.
   - Todas las facturas deben pertenecer a la misma ARS.
2. **Actualización de Trazabilidad:** Al emitir la NC en lote, se registra el evento `NOTA_CREDITO` con el `NCF_NC` asignado.

---

## 6. Normalización Multibyte UTF-8 en Generación de Reportes PDF (FPDF)

Para evitar la corrupción visual de caracteres acentuados en los comprobantes y reportes médicos (`ReporteAprobacion.php`, `ReporteFactAprobacion.php`):
* **Prohibido usar funciones de string mono-byte:** No utilizar `strtoupper()` ni `substr()`, ya que rompen secuencias UTF-8 (ej. producen `"MATERIALES MéDICOS"` o caracteres extraños ``).
* **Estándar Multibyte Obligatorio:**
  ```php
  // Conversión limpia a mayúsculas conservando acentos
  $nombreLimpio = mb_strtoupper($textoOriginal, 'UTF-8');
  
  // Truncamiento seguro por caracteres (no por bytes)
  $descripcionCorta = mb_substr($nombreLimpio, 0, 40, 'UTF-8');
  
  // Conversión final a codificación ISO-8859-1 para el motor FPDF
  $textoCelda = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $descripcionCorta);
  $pdf->Cell(80, 6, $textoCelda, 1, 0, 'L');
  ```

