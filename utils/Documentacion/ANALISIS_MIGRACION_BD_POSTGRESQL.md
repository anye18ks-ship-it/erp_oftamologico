# 🏛️ ANÁLISIS ARQUITECTÓNICO DE UNIFICACIÓN Y MIGRACIÓN: DE DOS BDs FRAGMENTADAS A POSTGRESQL MULTI-TENANT
## Micro-ERP Vertical SaaS para Clínicas Oftalmológicas y Salud Visual
### República Dominicana • Ley 32-23 (e-CF DGII) • Stack: React 18, Node.js / Next.js, PostgreSQL 15+

---

## 1. Justificación Crítica: ¿Por qué UNIFICAR `CMS` y `Expediente` en una sola Base de Datos?

En el sistema heredado, el desarrollador anterior cometió un **grave error de diseño arquitectónico**: dividir la operación en dos bases de datos completamente separadas e incomunicadas:
1. **`Expediente` (Base de datos clínica):** Almacenaba pacientes, citas, historias médicas, cirugías, recetas y comisiones.
2. **`CMS` (Base de datos comercial / POS):** Almacenaba facturas SAP, inventarios, turnos de caja, secuencias NCF y el firmador e-CF.

### ❌ Las Consecuencias Graves del Modelo Separado (Sistema Antiguo):
- **Duplicación Inevitable de Pacientes:** El paciente existía como `s_Expediente` en una BD y como socio de negocio `posOCRD` en la otra. Cuando una secretaria corregía un teléfono, seguro o RNC en recepción, la caja no se enteraba a menos que corriera un script de sincronización.
- **Riesgo Crítico de Desincronización y Errores:** Al facturar un copago o un lente en caja (`CMS`), el sistema debía ejecutar consultas cruzadas (`linked servers` o conexiones ODBC simultáneas). Si la conexión caía en medio del cobro, la consulta médica quedaba registrada pero la factura no se emitía, o viceversa.
- **Incapacidad de Trazabilidad Integral:** Era imposible consultar en un solo reporte rápido: *"Ver la refracción del paciente, la orden de taller de sus lentes progresivos, la factura POS emitida, la aprobación de la ARS y el timbre e-CF de la DGII"*.

###  La Solución Definitiva en el Nuevo ERP:
**UNIFICACIÓN TOTAL EN UNA SOLA BASE DE DATOS Y UN SOLO ESQUEMA POR CLÍNICA.**
La consulta oftalmológica, la orden de lentes, la autorización de ARS, el cobro en mostrador POS y la emisión del comprobante electrónico e-CF a la DGII conviven en un **mismo modelo relacional ACID, 100% coherente y transaccional**.

---

## 2. Cuadro Comparativo Detallado de Migración

| Módulo Funcional | Sistema Heredado Fragmentado | Nuevo ERP PostgreSQL Unificado (`ERP_BD_postgree.sql`) |
| :--- | :--- | :--- |
| **Arquitectura de Datos** | 2 BDs separadas en SQL Server (`CMS` ~348 KB + `Expediente` ~322 KB) | **1 Sola Base de Datos PostgreSQL** con Esquemas Aislados por Inquilino (*Schema-per-Tenant*) |
| **Facturación de Ventas** | `POSOINV` + `POSINV1` (220+ columnas vacías de SAP B1 por línea) | `facturas` + `factura_detalles` (20 columnas esenciales, normalizadas y optimizadas) |
| **Expediente Oftalmológico** | `s_Visita_Oftalmologia` (222 columnas crípticas sin formato: `kodk1`, `codavsc`) | `consultas_oftalmologicas`, `tonometria_iop`, `recetas_optometricas` (tipadas y precisas) |
| **Taller de Óptica & Monturas** | `s_CompraLente_Table`, `s_Optica` (tablas planas sin seguimiento de laboratorio) | `ordenes_laboratorio_optica` (control de montura propia vs comprada, biselado, etapas) |
| **Quirófano & Cirugías** | `s_Quirofano`, `s_ComboTipoCirugia` | `paquetes_quirurgicos`, `cirugias_programadas` (Faco, Catarata, LIO trazable por serie) |
| **Presupuestos Quirúrgicos** | `s_HeaderPresupuesto`, `s_DetallePresupuesto` | `presupuestos`, `presupuesto_detalles` (cotizaciones clínicas con estados y vencimientos) |
| **Comisiones de Doctores** | `sp_ComisionDoctores` (procedimiento de 600 líneas con filtros complejos) | `politicas_comision_medica`, `politica_comision_detalles` (porc. clínica, médico y asistente) |
| **Recepción y Sala de Espera** | `s_ListaPaciente`, `s_ListaEspera`, `s_Tanda` | `tandas_horarias`, `citas_medicas`, `turnos_sala_espera` (llamador a consultorio en pantalla) |
| **Vademécum & Recetas Prehechas**| `s_RecetaPrehecha`, `s_Medicina` | `medicamentos_catalogo`, `plantillas_recetas` (kits de colirios y posología en JSONB) |
| **Certificados Médicos** | `s_CertificacionMedica`, `s_Referimiento` | `certificados_medicos` (formatos para Intrant / Licencias, reposos y aptitud visual) |
| **Firmador Electrónico e-CF** | Función SQL Server de 191 KB (`GetInvoiceDataForXML`) + app externa | `ecf_xml_catalog`, `ecf_xsd_template`, `ecf_documentos` + función nativa PL/pgSQL JSONB |
| **Fusión de Duplicados** | `sp_ExpedienteMerge` | `sp_fusionar_expedientes` (reasigna citas, consultas, cirugías y órdenes en 1 transacción) |

---

## 3. Arquitectura del Firmador e-CF Integrado (DGII Ley 32-23)

El nuevo ERP no depende de servicios externos vulnerables ni de tablas intermedias lentas:
1. **`ecf_xml_catalog`**: Almacena la jerarquía y reglas de cada nodo oficial exigido por la DGII.
2. **`ecf_xsd_template`**: Valida localmente la estructura XML contra los esquemas XSD antes de firmar, evitando envíos con errores a la DGII.
3. **`ecf_certificados`**: Custodia en base de datos el archivo `.p12` cifrado del cliente con cálculo automático de días de vigencia.
4. **`ecf_documentos`**: Maneja la máquina de estados transaccional:
   - `N`: Generado
   - `F`: Firmado con XMLDSig (algoritmo SHA-256 + RSA)
   - `Q`: En vuelo / enviado a la API REST de la DGII
   - `P`: En proceso
   - `Y`: Aceptado con `TrackId` y timbre QR oficial
   - `R`: Rechazado con glosas de error almacenadas en JSONB
5. **Función `fn_extraer_datos_ecf(p_id_factura)`**: Extrae el árbol completo de la factura en formato JSONB nativo en **menos de 2 milisegundos**, permitiendo al backend en Node.js firmar el documento de forma instantánea.

---

## 4. Conclusión
El archivo [`ERP_BD_postgree.sql`](../backups_sql/ERP_BD_postgree.sql) representa la **columna vertebral integral del nuevo ERP**, unificando el 100% de la operativa clínica oftalmológica y la gestión administrativa-fiscal en una arquitectura limpia, segura y preparada para escalar como producto SaaS.
