---
name: ars-seguros-workflow
description: Flujo integral de seguros médicos (ARS) para el nuevo ERP clínico. Cobertura 100% directa sin factura en ventanilla, copago dividido, facturación fiscal por lotes (E31/B01), ciclo de cobranzas, trazabilidad de glosas y notas de crédito en PostgreSQL y TypeScript.
---

# Flujo Integral de Seguros Médicos (ARS) y Reclamaciones

Esta skill define la lógica clínica y contable para la gestión de aseguradoras médicas (ARS) de República Dominicana, autorizaciones de consultas/procedimientos, facturación masiva por lotes y auditoría de glosas en el nuevo ERP.

---

## 1. Ciclo de Vida de una Autorización Médica (`autorizaciones_ars`)

En el nuevo ERP, las aprobaciones de seguro médico se registran en una entidad normalizada:

```sql
CREATE TABLE autorizaciones_ars (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    paciente_id UUID NOT NULL REFERENCES pacientes(id),
    aseguradora_id UUID NOT NULL REFERENCES aseguradoras_ars(id),
    numero_afiliado VARCHAR(50) NOT NULL,
    numero_autorizacion VARCHAR(50) NOT NULL,
    tipo_servicio VARCHAR(50) NOT NULL,       -- 'CONSULTA', 'ESTUDIO_OCT', 'CIRUGIA', 'LAB'
    monto_reclamado DECIMAL(18, 2) NOT NULL,
    monto_aprobado DECIMAL(18, 2) NOT NULL,
    porcentaje_cobertura DECIMAL(5, 2) NOT NULL,
    copago_paciente DECIMAL(18, 2) NOT NULL,
    
    -- Enlaces a facturas
    factura_paciente_id UUID REFERENCES facturas(id), -- NULL si cobertura 100%
    factura_lote_ars_id UUID REFERENCES facturas(id), -- Factura E31 a la ARS
    
    estado VARCHAR(20) NOT NULL DEFAULT 'PENDIENTE', -- 'PENDIENTE', 'FACTURADO_ARS', 'ANULADO'
    fecha_autorizacion DATE NOT NULL DEFAULT CURRENT_DATE,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_auth_ars_pendiente ON autorizaciones_ars (aseguradora_id, estado);
```

---

## 2. Modalidades de Cobro en Mostrador

### 2.1 Cobertura 100% Directa (Sin Factura al Paciente)
Ocurre cuando la aseguradora asume la totalidad del costo (`copago_paciente = 0.00`):

* **Reglas Contables y Fiscales**:
  1. **NO** se emite factura fiscal al paciente (`facturas`).
  2. **NO** se consume NCF de consumo (`E32` ni `B02`).
  3. **NO** se genera ningún movimiento en el turno de caja (`pagos` / `turnos_caja`), ya que no ingresó dinero físico.
  4. Se inserta el registro en `autorizaciones_ars` con:
     - `factura_paciente_id = NULL`
     - `estado = 'PENDIENTE'`
* **Impacto en el Flujo Clínico**:
  - El paciente queda registrado automáticamente como **"Atendido / Autorizado Hoy"**, permitiendo que pase inmediatamente a la sala de espera o al consultorio oftalmológico sin generar retrasos administrativos.

### 2.2 Cobertura Parcial (Copago del Paciente)
Cuando el seguro solo cubre una fracción y el paciente asume una diferencia:
* Se emite una factura en el POS (`facturas`) al paciente por el valor del copago (`copago_paciente`) con comprobante de consumo (`E32` / `B02`) o crédito fiscal si lo solicita.
* Se cobra el copago en ventanilla registrando el pago en el turno de caja activo.
* Se guarda la autorización vinculada a `factura_paciente_id = factura.id` con `estado = 'PENDIENTE'`.

---

## 3. Facturación Fiscal por Lotes a la Aseguradora (`lotes_facturacion_ars`)

Las clínicas no cobran paciente por paciente a la ARS; agrupan periódicamente (semanal o quincenal) todas las autorizaciones acumuladas y emiten una única **Factura de Crédito Fiscal (`E31` o `B01`)**:

```
[ Autorizaciones Pendientes (ARS Humano) ]
  ├── Aut #1024 - Paciente A (RD$ 2,500)
  ├── Aut #1025 - Paciente B (RD$ 1,800)  ───> [ Lote de Facturación ] ───> [ Factura E31 a la ARS ]
  └── Aut #1026 - Paciente C (RD$ 4,200)                                     (Total: RD$ 8,500)
```

### 3.1 Proceso de Generación del Lote (`ArsBillingService.ts`)
```typescript
export async function createArsBatchInvoice(
  client: PoolClient,
  aseguradoraId: string,
  authIds: string[]
): Promise<string> {
  // 1. Obtener datos de la aseguradora (RNC fiscal)
  const arsRes = await client.query(`SELECT rnc, razon_social FROM aseguradoras_ars WHERE id = $1`, [aseguradoraId]);
  const ars = arsRes.rows[0];

  // 2. Sumar montos aprobados de las autorizaciones seleccionadas
  const sumRes = await client.query(
    `SELECT SUM(monto_aprobado) as total_lote 
     FROM autorizaciones_ars 
     WHERE id = ANY($1) AND aseguradora_id = $2 AND estado = 'PENDIENTE'
     FOR UPDATE`,
    [authIds, aseguradoraId]
  );
  const totalLote = Number(sumRes.rows[0].total_lote);

  // 3. Asignar NCF de Crédito Fiscal (E31)
  const { ncf, fechaVence } = await assignNextNCF(client, 'E31');

  // 4. Crear factura fiscal de venta a nombre de la ARS
  const invRes = await client.query(
    `INSERT INTO facturas (
      tipo_comprobante, ncf, fecha_vencimiento_ncf, rnc_comprador, razon_social_comprador,
      subtotal, itbis_total, total_general, estado, notas
    ) VALUES ('E31', $1, $2, $3, $4, $5, 0.00, $5, 'EMITIDA', 'LOTE RECLAMACIONES ARS')
    RETURNING id`,
    [ncf, fechaVence, ars.rnc, ars.razon_social, totalLote]
  );
  const invoiceId = invRes.rows[0].id;

  // 5. Actualizar las autorizaciones al estado FACTURADO_ARS
  await client.query(
    `UPDATE autorizaciones_ars 
     SET estado = 'FACTURADO_ARS', factura_lote_ars_id = $1 
     WHERE id = ANY($2)`,
    [invoiceId, authIds]
  );

  // 6. Encolar job de firma electrónica para la factura E31 emitida
  await enqueueEcfSigningJob(invoiceId);

  return invoiceId;
}
```

---

## 4. Trazabilidad de Reclamaciones, Cobranzas y Glosas

Cuando la ARS remite la liquidación del lote:

```sql
CREATE TABLE trazabilidad_glosas_ars (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    factura_lote_ars_id UUID NOT NULL REFERENCES facturas(id),
    autorizacion_id UUID NOT NULL REFERENCES autorizaciones_ars(id),
    monto_reclamado DECIMAL(18, 2) NOT NULL,
    monto_pagado DECIMAL(18, 2) NOT NULL,
    monto_glosa DECIMAL(18, 2) NOT NULL,
    motivo_glosa TEXT,
    estado_glosa VARCHAR(20) NOT NULL, -- 'ACEPTADA', 'REPUTADA', 'COBRADA'
    nota_credito_id UUID REFERENCES facturas(id), -- NCF E34 si se acepta la glosa
    fecha_liquidacion DATE NOT NULL DEFAULT CURRENT_DATE
);
```

### 4.1 Tratamiento Contable de la Glosa Médica
Si la ARS objeta un monto reclamado (por ejemplo, por falta de informe de biopsia o código no cubierto):
1. **Aceptación de la Glosa**:
   - La clínica emite una **Nota de Crédito Electrónica (`E34`)** a la ARS por el valor exacto objetado (`monto_glosa`).
   - El saldo pendiente de la cuenta por cobrar se reduce limpiamente sin descuadrar la factura original.
2. **Reclamación / Reputa de Glosa**:
   - Se adjunta la documentación clínica subsanada para reenvío en el siguiente corte.
