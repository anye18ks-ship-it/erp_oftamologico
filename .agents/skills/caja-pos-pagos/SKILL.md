---
name: caja-pos-pagos
description: Control de turnos de caja, conciliación ciega y procesamiento multi-medio de pagos (efectivo con cambio, múltiples tarjetas con detección BIN, transferencias bancarias validadas) en PostgreSQL, TypeScript y React para el nuevo ERP.
---

# Control de Cajas POS y Métodos de Pago Múltiples

Esta skill define la arquitectura contable, reglas operativas de arqueo ciego y procesamiento de pagos divididos (Split Payments) para el punto de venta (POS) del nuevo ERP clínico.

---

## 1. Esquema de Entidades en PostgreSQL

```sql
-- Terminales o Puntos de Cobro Físicos
CREATE TABLE cajas_terminales (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    sucursal_id UUID NOT NULL,
    nombre VARCHAR(50) NOT NULL, -- 'Caja Principal Ventanilla 1', 'Caja Óptica'
    activa BOOLEAN NOT NULL DEFAULT true
);

-- Sesión de Trabajo / Turno del Cajero
CREATE TABLE turnos_caja (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    caja_id UUID NOT NULL REFERENCES cajas_terminales(id),
    usuario_id UUID NOT NULL,
    fecha_apertura TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    monto_apertura DECIMAL(18, 2) NOT NULL, -- Fondo inicial en efectivo
    fecha_cierre TIMESTAMPTZ,
    monto_cierre_declarado DECIMAL(18, 2),   -- Arqueo ciego contado por el cajero
    monto_cierre_sistema DECIMAL(18, 2),     -- Calculado por el backend
    diferencia DECIMAL(18, 2),               -- Sobrante (+) o Faltante (-)
    estado VARCHAR(20) NOT NULL DEFAULT 'ABIERTO' -- 'ABIERTO', 'CERRADO'
);

-- Transacciones de Pago Asociadas a Facturas y Turnos
CREATE TABLE pagos (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    turno_caja_id UUID NOT NULL REFERENCES turnos_caja(id),
    factura_id UUID NOT NULL REFERENCES facturas(id),
    metodo_pago VARCHAR(20) NOT NULL, -- 'EFECTIVO', 'TARJETA', 'TRANSFERENCIA'
    monto DECIMAL(18, 2) NOT NULL,
    
    -- Campos específicos para Efectivo
    efectivo_recibido DECIMAL(18, 2),
    cambio_devuelto DECIMAL(18, 2),
    
    -- Campos específicos para Tarjetas
    tarjeta_franquicia VARCHAR(20),     -- 'VISA', 'MASTERCARD', 'AMEX', 'DISCOVER'
    tarjeta_ultimos_cuatro VARCHAR(4),
    tarjeta_autorizacion VARCHAR(30),   -- Voucher / Referencia del datáfono
    
    -- Campos específicos para Transferencias
    banco_origen VARCHAR(50),           -- 'BANCO POPULAR', 'BANRESERVAS', 'BHD'
    referencia_transferencia VARCHAR(50),
    comprobante_deposito_url TEXT,
    
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);
```

---

## 2. Métodos de Pago en el Punto de Venta (POS)

El checkout del POS permite liquidar una factura combinando múltiples métodos de pago:

### 2.1 Efectivo con Cálculo de Vueltas
* El cajero ingresa el `monto_pago` y el `efectivo_recibido`.
* El frontend calcula reactivamente:
  $$\text{Cambio} = \text{Efectivo Recibido} - \text{Monto Pago}$$
* Si $\text{Efectivo Recibido} < \text{Monto Pago}$, la interfaz bloquea el registro.

### 2.2 Pago con Múltiples Tarjetas (Crédito / Débito)
Permite dividir el cargo en más de una tarjeta (ej: paciente paga con dos plásticos):
* **Detección Automática de Franquicia por BIN (TypeScript / React)**:
  ```typescript
  export type CardBrand = 'VISA' | 'MASTERCARD' | 'AMEX' | 'DISCOVER' | 'OTRA';

  export function detectCardBrand(digits: string): CardBrand {
    const clean = digits.replace(/\s+/g, '');
    if (/^4/.test(clean)) return 'VISA';
    if (/^(5[1-5]|2[2-7])/.test(clean)) return 'MASTERCARD';
    if (/^3[47]/.test(clean)) return 'AMEX';
    if (/^6(?:011|5)/.test(clean)) return 'DISCOVER';
    return 'OTRA';
  }
  ```
* Cada tarjeta registra su franquicia, sus últimos 4 dígitos y el código de autorización del comprobante físico del verifone.

### 2.3 Transferencia Bancaria
* **Validación Obligatoria**:
  - Selección de la entidad financiera (Banco Popular, Banreservas, BHD, Scotia, etc.).
  - Número de referencia de la transferencia único para evitar reuso del mismo comprobante.
  - Carga opcional de fotografía o captura del volante bancario.

### 2.4 Exclusión de Coberturas 100% ARS
* Cuando un paciente es atendido bajo autorización médica con 100% de cobertura (`copago_paciente = 0.00`):
  - **NO se registra ningún registro en `pagos` ni en `turnos_caja`**.
  - No altera el cuadre de efectivo físico del turno.

---

## 3. Arqueo y Conciliación Ciega de Turno (`Blind Close`)

Para evitar fraudes o manipulaciones contables al cierre de jornada:

1. **Procedimiento Ciego en Frontend**:
   - Al hacer clic en *"Cerrar Turno"*, el cajero **NO ve el monto esperado por el sistema**.
   - Se le presenta un formulario donde cuenta y declara físicamente:
     - Total en efectivo contado (billetes y monedas).
     - Comprobantes de tarjetas físicos.
     - Vouchers de transferencias.
2. **Cálculo Transaccional en Backend (`CloseShiftService.ts`)**:
   ```typescript
   export async function closeCashShift(
     client: PoolClient,
     turnoId: string,
     montoDeclarado: number
   ) {
     // 1. Calcular total esperado en efectivo
     const cashRes = await client.query(`
       SELECT 
         t.monto_apertura,
         COALESCE(SUM(p.monto), 0) as total_ventas_efectivo
       FROM turnos_caja t
       LEFT JOIN pagos p ON p.turno_caja_id = t.id AND p.metodo_pago = 'EFECTIVO'
       WHERE t.id = $1 AND t.estado = 'ABIERTO'
       GROUP BY t.id, t.monto_apertura
     `, [turnoId]);

     if (cashRes.rows.length === 0) throw new Error('Turno no encontrado o ya cerrado');

     const { monto_apertura, total_ventas_efectivo } = cashRes.rows[0];
     const totalEsperado = Number(monto_apertura) + Number(total_ventas_efectivo);
     const diferencia = montoDeclarado - totalEsperado;

     // 2. Cerrar turno registrando la auditoría
     await client.query(`
       UPDATE turnos_caja 
       SET 
         fecha_cierre = CURRENT_TIMESTAMP,
         monto_cierre_declarado = $1,
         monto_cierre_sistema = $2,
         diferencia = $3,
         estado = 'CERRADO'
       WHERE id = $4
     `, [montoDeclarado, totalEsperado, diferencia, turnoId]);

     return { totalEsperado, montoDeclarado, diferencia };
   }
   ```
