---
name: dgii-fiscal-ncf
description: Directrices, algoritmos de validación y gestión de NCF y e-CF (comprobantes fiscales DGII República Dominicana) para facturación, notas de crédito y anulaciones.
---

# DGII & Comprobantes Fiscales (NCF / e-CF)

Esta skill documenta las reglas fiscales, algoritmos de validación y esquemas de base de datos de República Dominicana (DGII) utilizados en este sistema ERP/Clínico.

---

## 1. Estructura de Comprobantes Fiscales

### 1.1 Comprobantes Electrónicos (e-CF - Norma General 01-2020 / Ley 32-23)
Los e-CF inician con la letra `E` seguida de 2 dígitos de tipo y 10 dígitos de secuencia (total 13 caracteres, ej: `E310000000001`):

| Prefijo | Tipo de e-CF | Descripción de Uso |
| :--- | :--- | :--- |
| **E31** | Factura de Crédito Fiscal Electrónica | Ventas a empresas/profesionales con valor fiscal (deducción ITBIS/ISR) |
| **E32** | Factura de Consumo Electrónica | Ventas a consumidor final (pacientes particulares sin RNC) |
| **E33** | Nota de Débito Electrónica | Cargos financieros, mora, penalidades o corrección de precio al alza sobre un e-CF |
| **E34** | Nota de Crédito Electrónica | Devolución de mercancía, corrección de factura o anulación fiscal de un e-CF |
| **E44** | Comprobante Especial Regímenes | Ventas exentas a zonas francas, embajadas u organismos |
| **E45** | Comprobante Gubernamental | Facturas emitidas al Estado Dominicano (Ministerios, SNS, etc.) |

> **Regla de Formato**: Cuando se genera una secuencia electrónica, se paddea a 10 dígitos:
> ```php
> $numeroFormateado = $prefijo . str_pad($secuenciaActual, 10, '0', STR_PAD_LEFT); // Ej: E310000000025, E330000000001
> ```

### 1.2 Comprobantes Tradicionales (NCF Serie B - Serie de 11 Caracteres)
Inician con `B` seguido de 2 dígitos y 8 dígitos de secuencia (total 11 caracteres, ej: `B0100000001`):
- `B01`: Crédito Fiscal
- `B02`: Consumo Final
- `B03`: Nota de Débito tradicional
- `B04`: Nota de Crédito tradicional
- `B14`: Regímenes Especiales
- `B15`: Gubernamental

---

## 2. Tablas del Sistema de Secuencias y Numeración Interna

### 2.1 Control de Comprobantes Fiscales (`carga_ncf` / `serie_ncf`)
- `ID`: Identificador de la serie.
- `NCF`: Prefijo/Nombre de la serie (ej: `e31`, `e32`, `e33`, `e34`, `B01`, `B02`, `B03`, `B04`).
- `secuencia_actual`: Entero con la última secuencia emitida.
- `secuencia_final` / `limite`: Límite máximo autorizado por DGII.
- `fecha_vencimiento`: Fecha límite de vigencia de la serie.
- `estado` / `Status`: `1` = Activa, `0` = Inactiva o Vencida.

### 2.2 Numeración Interna por Tipo de Documento (`secuencia`)
Para trazabilidad interna, cada documento genera un correlativo único con letra identificadora:
- **Facturas de Venta (`POSOINV`):** Prefijo `F` $\rightarrow$ `F0000001`.
- **Notas de Crédito (`POSORIN`):** Prefijo `N` $\rightarrow$ `N0000001`.
- **Notas de Débito (`POSODIN`):** Prefijo `D` $\rightarrow$ `D0000001`.

> **Reseed / Saneamiento para Entornos de Prueba:**
> Al reiniciar entornos para pruebas, se resetea `carga_ncf.secuencia_actual = 0`, se trunca o reseed `secuencia` a 1, y se aplican `DBCC CHECKIDENT ('...', RESEED, 0)` en las cabeceras transaccionales para que todos los correlativos comiencen de forma limpia en 1.


---

## 3. Algoritmo de Validación RNC y Cédula Dominicana (Módulo 10 y Módulo 11)

### 3.1 Validación de Cédula de Identidad (11 dígitos - Módulo 10)
```php
function validarCedula(string $cedula): bool {
    $ced = preg_replace('/[^0-9]/', '', $cedula);
    if (strlen($ced) !== 11) return false;
    
    $pesos = [1, 2, 1, 2, 1, 2, 1, 2, 1, 2];
    $suma = 0;
    
    for ($i = 0; $i < 10; $i++) {
        $prod = (int)$ced[$i] * $pesos[$i];
        if ($prod >= 10) {
            $prod = (int)substr((string)$prod, 0, 1) + (int)substr((string)$prod, 1, 1);
        }
        $suma += $prod;
    }
    
    $verificador = (10 - ($suma % 10)) % 10;
    return $verificador === (int)$ced[10];
}
```

### 3.2 Validación de RNC (9 dígitos - Módulo 11)
```php
function validarRNC(string $rnc): bool {
    $doc = preg_replace('/[^0-9]/', '', $rnc);
    if (strlen($doc) !== 9) return false;
    
    $pesos = [7, 9, 8, 6, 5, 4, 3, 2];
    $suma = 0;
    for ($i = 0; $i < 8; $i++) {
        $suma += (int)$doc[$i] * $pesos[$i];
    }
    
    $resto = $suma % 11;
    if ($resto === 0) {
        $digito = 2;
    } elseif ($resto === 1) {
        $digito = 1;
    } else {
        $digito = 11 - $resto;
    }
    
    return $digito === (int)$doc[8];
}
```

---

## 4. Tipos de Anulación y Notas de Crédito (`TIPOS_ANULACION`)

Tabla `TIPOS_ANULACION` (Catálogo DGII / SAP):
- `Code`: Código de motivo DGII (ej: `01`, `02`, `06`, `07`, `15`, `19`, `23`).
- `Name`: Descripción del motivo de anulación / devolución.
- `U_par_libro`: Código de agrupación fiscal DGII (`01` = Impresión/Deterioro, `02` = Operativo/Devolución).

### Comportamiento según el tipo de motivo:
1. **Devolución / Cambio de Productos (`06`, `07`, `23`)**:
   - Genera reversión contable/financiera en `POSORIN` / `POSRIN1`.
   - **Retorna la mercancía al inventario** sumando en `posOITM.OnHand` y `posOITW.OnHand` si `InvntItem = 'Y'`.
2. **Corrección de Información / Error en Facturación (`05`, `15`, `19`)**:
   - Ajuste financiero o anulación de saldo sin reingreso físico si la mercancía nunca salió o fue refacturada.
3. **Anulación por Deterioro / Cese (`01`, `02`, `10`, `11`)**:
   - Anulación directa del comprobante con reporte a DGII (Formato 608).
