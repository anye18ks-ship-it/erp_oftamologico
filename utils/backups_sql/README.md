# Respaldos y Estructuras de Base de Datos (CMS & Expediente)

Este directorio contiene la exportación completa de la estructura de base de datos del sistema clínico para referencia de migración a la nube (PostgreSQL, APIs en Node.js y React).

---

## 1. Archivos SQL en Texto Plano (Portables para Git y Análisis)

Los siguientes archivos contienen el código DDL 100% legible y portable sin requerir SQL Server instalado:

| Archivo | Tamaño | Contenido Incluido |
| :--- | :---: | :--- |
| [`schema_CMS.sql`](./schema_CMS.sql) | ~348 KB | **59 Tablas** completas con tipos y llaves primarias (incluyendo `POSOINV`, `POSINV1`, `posOITM`, `diario_caja_det`, `carga_ncf`), **1 Vista** (`v_sale_item`) y **2 Funciones** (incluyendo `GetInvoiceDataForXML`, que contiene toda la estructura requerida para facturación electrónica e-CF DGII). |
| [`schema_Expediente.sql`](./schema_Expediente.sql) | ~322 KB | **163 Tablas** (expedientes, pacientes, citas, historias, compras de lentes), **26 Vistas** (seguros, asistencias, citas), **3 Funciones** y **43 Procedimientos Almacenados** con su código SQL exacto (`sp_ComisionDoctores`, `sp_AddReciboCoPago`, `GenerarNCF`, `sp_ProcesarPagoSeguro`, etc.). |
| [`ERP_BD_postgree.sql`](./ERP_BD_postgree.sql) | ~88 KB | **Estructura Maestra PostgreSQL Multi-Tenant (Modernizada e Integral)**: Esquema `core` SaaS y esquema `clinica_template` con el 100% de la operativa: socios, clínica oftalmológica, tonometría IOP, recetas, inventario multialmacén, entradas de mercancía (`POSOIGN`), asignación y recálculo ponderado de costos (`AvgPrice`), listas de precios, cajas físicas, turnos (apertura, retiros, arqueo con denominaciones y cierre), pagos divididos (split payments), autorizaciones ARS, catálogo de impuestos DGII (`ecf_tax_config`), series y carga de NCF, facturas, notas de crédito (`POSORIN`) y débito (`POSODIN`), ecosistema e-CF completo, **suite de índices estratégicos de alto rendimiento (Covering, Partial & GIN)**, triggers automáticos de kardex y vistas analíticas. |

---

## 2. Respaldos Nativos SQL Server (.bak)

Se generaron los backups binarios completos directamente en el servidor de base de datos (`172.16.2.70`):

- **CMS**: `C:\CMS_backup.bak`
- **Expediente**: `C:\Expediente_backup.bak`

> Estos archivos `.bak` pueden ser copiados a un disco o pendrive desde el servidor si deseas restaurar la base de datos completa con datos reales en otro entorno SQL Server.

---

## 3. Utilidad para el Futuro Proyecto Cloud (PostgreSQL + Node + React)
Al trabajar en tu computadora personal:
1. Puedes abrir estos `.sql` directamente en VS Code.
2. La IA puede leer cualquier procedimiento almacenado (ej. comisiones de doctores o copagos de ARS) para reescribir la lógica de negocio de manera limpia en servicios de Node.js / TypeScript.
3. Puedes utilizar las definiciones de tablas para diseñar el esquema relacional normalizado en PostgreSQL con Drizzle/Prisma.
