# Plan Maestro y Especificación Arquitectónica: Micro-ERP Vertical SaaS (Salud Visual y Oftalmología)

Este documento centraliza la visión estratégica, la arquitectura técnica, el modelo de datos y las directrices operativas para el desarrollo del micro-ERP especializado en el sector de la salud visual en la República Dominicana.

---

## 1. Visión General y Nicho de Mercado
* **Enfoque Vertical:** Un sistema unificado que conecta los procesos clínicos (historiales médicos, graduaciones, recetas optométricas) con los procesos comerciales (Punto de Venta/POS, inventario multialmacén de monturas/lentes y gestión de coberturas de ARS).
* **Nicho Objetivo:** Clínicas oftalmológicas medianas y centros ópticos (salud visual), elegidos por ser un mercado dinámico con necesidades urgentes de control de inventario de mostrador y facturación, representando una entrada comercial más ágil (*low-hanging fruit*).
* **Modelo Comercial (SaaS por Niveles / *Tiers*):** Se desecha la venta de módulos independientes para evitar fragmentación operativa. Se ofrecen planes mensuales cerrados (Ej. Básico, Clínico, Enterprise) que brindan acceso a todo el ecosistema escalado según el tamaño del centro médico.

---

## 2. Filosofía Operativa: El Modelo "Zero-Contact" (Cero Soporte)
* **Objetivo:** Construir un producto tan robusto, transparente e intuitivo que el cliente no requiera asistencia técnica directa, permitiendo alcanzar ingresos pasivos reales a largo plazo.
* **Mecanismos de Autogestión:** 
  * Alertas proactivas integradas en la interfaz ante cualquier incidencia operativa.
  * Asistentes guiados para que los usuarios corrijan o gestionen sus propios registros dentro de los márgenes legales.
  * Paneles de auditoría y reportes financieros transparentes para que los administradores investiguen sus flujos de caja sin intermediarios.

---

## 3. Arquitectura Tecnológica y Despliegue

### 3.1 Separación de Componentes
1. **Landing Page / Portal Comercial (SEO & Marketing):**
   * Desarrollada con tecnologías optimizadas para velocidad y posicionamiento web.
   * Alojada globalmente en redes CDN de alto rendimiento (como **Vercel** o **Cloudflare Pages**) bajo el dominio principal (`tudominio.com`).
2. **Aplicación del ERP (Frontend SPA con TypeScript):**
   * Desarrollada en **React + Vite + TypeScript**, sirviendo como la interfaz de usuario protegida por autenticación JWT en `app.tudominio.com`.
   * El uso de **TypeScript** asegura tipado estricto en los modelos clínicos (historiales, agudeza visual, recetas optométricas) y en las estructuras tributarias/financieras (líneas de factura, comprobantes e-CF, cotizaciones, autorizaciones ARS), previniendo errores de datos en tiempo de compilación.
3. **Backend y Base de Datos (API y Motor Central):**
   * Desarrollado en **Node.js / Express** (o NestJS).
   * Alojado junto con la base de datos en un servidor virtual privado (**VPS**) optimizado.

### 3.2 Especificación de Infraestructura (Hetzner VPS)
* **Proveedor seleccionado:** Hetzner (Plan **CPX22** o equivalente de la línea *Regular Performance*).
* **Recursos iniciales:** 2 vCPUs (AMD), **4 GB de RAM**, y 80 GB de almacenamiento SSD NVMe.
* **Escalabilidad:** Permite crecimiento vertical transparente (*Resize*) a medida que la base de clientes y el volumen de datos aumentan.

---

## 4. Arquitectura de Base de Datos y Alto Rendimiento (PostgreSQL)

### 4.1 Aislamiento por Inquilino (*Schema-per-Tenant*)
* Para garantizar seguridad absoluta y privacidad de los datos médicos de los pacientes sin incurrir en el caos operativo de una base de datos única masiva, se implementa una arquitectura de **Esquemas por Inquilino (*Schemas-per-Tenant*)** en PostgreSQL.
* Cada clínica u óptica registrada posee su propio esquema aislado dentro de la misma instancia de PostgreSQL (ej. `schema_clinica_alfa`), permitiendo respaldos independientes y migraciones de código automatizadas y rápidas.

### 4.2 Modelo Híbrido y Rendimiento Masivo
* **Transacciones ACID:** PostgreSQL garantiza la consistencia estricta requerida para los módulos financieros, contables y de inventario.
* **Optimización de Relaciones:** Se aplican restricciones estrictas (*Foreign Keys*) en tablas maestras (clientes, productos, catálogos), mientras que en tablas de transacciones masivas se optimiza el diseño para evitar bloqueos de inserción, complementado con validaciones a nivel de aplicación.
* **Escalabilidad de Datos:** Uso de particionamiento nativo en PostgreSQL (por rangos de fechas o esquemas) y consultas con paginación basada en cursores (*Lazy Loading*) para procesar millones de registros sin degradación de velocidad.

---

## 5. Modelo de Negocio Clínico-Comercial (Salud Visual & Oftalmología)

### 5.1 Catálogo Dual: Artículos Tangibles vs Servicios Clínicos
El sistema elimina la complejidad de sistemas legados clasificando los ítems con una arquitectura limpia:
* **Artículos de Inventario (Físicos):**
  * Monturas oftálmicas, lentes de sol, lentes de contacto, medicamentos oftalmológicos (gotas, colirios) y descartables quirúrgicos.
  * Manejo multialmacén (`almacenes`, `articulo_almacen`), con un almacén principal habilitado por defecto y almacenes secundarios deshabilitados hasta su activación.
  * Control de Kardex con costo promedio ponderado (`costo_promedio`) actualizado en cada entrada de mercancía.
  * Código de barras habilitado para lectura rápida con pistolas POS.
  * Prefijo dinámico de 2 a 3 letras según la categoría (ej. `DES`, `QUI`, `MED`).
* **Servicios / Procedimientos Clínicos:**
  * Consultas oftalmológicas, optometría, refracción, cirugías (catarata, LASIK, pterigión), estudios de diagnóstico (OCT, topografía, campimetría).
  * **Cero control de stock físico:** Nunca bloquean la facturación por existencias ni descuentan inventario.
  * Almacén predeterminado en `NULL` y código de barras deshabilitado en interfaz.
  * Prefijo reservado estandarizado **`SV`** (ej. `SV0001`) para evitar colisiones con seguros o inventario de compras.

---

## 6. Flujo Integral de Seguros Médicos (ARS) y Copagos

La operación oftalmológica en República Dominicana depende en más de un 70% de aseguradoras médicas (ARS). El nuevo ERP automatiza su ciclo completo:

1. **Atención con Copago Parcial:**
   * El sistema desglosa automáticamente la cobertura ARS (`monto_cubierto`) y el copago del paciente (`monto_paciente`).
   * Al paciente se le cobra y factura únicamente su copago en ventanilla (POS).
2. **Atención con Cobertura 100% Directa:**
   * Si la ARS cubre el 100% del servicio (`copago = 0.00`):
     * **No se emite factura fiscal al paciente** ni se consume NCF de consumo.
     * **No se genera movimiento en caja física**.
     * La autorización queda registrada en `autorizaciones_ars` con estado `'PENDIENTE_FACTURAR_ARS'`.
     * El paciente se marca automáticamente como **"Atendido Hoy / Facturado Hoy"** para retirarlo de la cola de espera de consulta médica.
3. **Facturación por Lotes a Aseguradoras (Crédito Fiscal):**
   * Agrupación periódica de autorizaciones pendientes de una misma ARS para emitir una sola Factura de Crédito Fiscal (`B01` / `E31`).
4. **Trazabilidad de Reclamaciones y Glosas:**
   * Módulo para registrar pagos de ARS (cheque/transferencia), conciliar glosas o aplicar Notas de Crédito (`B04` / `E34`).

---

## 7. Punto de Venta (POS) y Módulo de Tesorería

1. **División de Pagos (Split Payments):**
   * Soporte nativo para liquidar una factura combinando múltiples medios:
     * **Efectivo:** Con cálculo instantáneo de cambio devuelto.
     * **Múltiples Tarjetas:** Interfaz dinámica que permite añadir tarjetas adicionales, con detección automática de franquicia mediante expresiones regulares del BIN (Visa, Mastercard, Amex, Discover) y registro de vouchers.
     * **Transferencia Bancaria:** Selector de bancos dominicanos (Banreservas, Popular, BHD, etc.) y campo de referencia/comprobante bancario obligatorio.
2. **Control de Turnos de Caja:**
   * Apertura con fondo inicial, registro granular de cada movimiento, arqueo de caja y cierre con cálculo de sobrante/faltante.
   * Desacoplamiento de pagos electrónicos remotos de las cajas físicas de mostrador.

---

## 8. Gestión de Comprobantes Fiscales (DGII) y Lotes Autónomos

1. **Gestión Autónoma de Lotes NCF (Self-Service Multi-Tenant):**
   * El cliente carga el archivo o digita su resolución de comprobantes autorizada por DGII en el módulo de configuración fiscal.
   * El backend inyecta las secuencias directamente en la tabla `carga_ncf` del esquema correspondiente (`schema_empresa`).
   * El ERP calcula en tiempo real los comprobantes disponibles (`disponibles = secuencia_final - secuencia_actual`) y activa semáforos en la barra superior:
     * **Verde:** Inventario fiscal óptimo (> 100).
     * **Amarillo:** Nivel de advertencia (< 50).
     * **Rojo:** Nivel crítico (< 10) con aviso de recarga.
2. **Numeración y Trazabilidad Interna:**
   * Prefijos correlativos independientes: `F` para Facturas, `N` para Notas de Crédito, `D` para Notas de Débito.
   * Validación algorítmica de RNC (Módulo 11) y Cédula (Módulo 10) antes de autorizar ventas a crédito fiscal.

---

## 9. Arquitectura de Base de Datos y Modelo de Datos (Diseño desde Cero)

> **Directriz Estratégica de Diseño de Base de Datos:**
> La base de datos del nuevo ERP **NO será una réplica ni una traducción 1 a 1 de las tablas legadas**. 
> 
> Se diseñará una **estructura completamente nueva, limpia, normalizada y altamente optimizada en PostgreSQL**, diseñada para alto rendimiento, integridad referencial real y escalabilidad SaaS. El diseño formal de entidades, tipos de datos, índices compuestos y particionamiento se analizará y definirá en una fase dedicada posterior.

A modo de **referencia funcional comparativa temporal** (para identificar qué lógica de negocio del sistema actual resuelve cada necesidad), se tiene la siguiente correspondencia de módulos:

| Módulo / Tabla Legacy (SAP B1) | Entidad Funcional en Nuevo ERP | Propósito Conceptual |
| :--- | :--- | :--- |
| `posOITM` | Catálogo de Ítems | Separación nítida entre productos tangibles y servicios clínicos. |
| `posOWHS` / `posOITW` | Inventario & Almacenes | Control multisede, existencias físicas y costeo promedio ponderado. |
| `posOPLN` / `positm1` | Política de Precios | Listas de precios, seguros y tarifas preferenciales. |
| `posOCRD` | Directorio de Entidades | Pacientes, médicos tratantes, aseguradoras (ARS) y suplidores. |
| `POSOINV` / `POSINV1` | Facturación de Ventas | Comprobantes de mostrador, copagos y facturación agrupada a ARS. |
| `POSORIN` / `POSRIN1` | Notas de Crédito | Anulaciones, devoluciones de almacén y correcciones tributarias. |
| `POSODIN` / `POSDIN1` | Notas de Débito | Recuperación de costos, intereses o cargos adicionales desacoplados. |
| `AprobacionARS` | Reclamaciones & Autorizaciones | Coberturas 100%, copagos de pacientes y autorizaciones médicas. |
| `trazabilidad_reclamacion_ars` | Cobranzas ARS & Glosas | Conciliación de pagos de aseguradoras y gestión de discrepancias. |
| `diario_caja` / `diario_caja_det` | Control de Tesorería & Turnos | Arqueos de caja, sesiones de cajero y cobros multi-medio (split). |
| `carga_ncf` | Secuencias & Control Fiscal | Lotes de comprobantes autorizados por DGII con alerta de agotamiento. |

---

## 10. Filosofía de Diseño UI Libre de Saturación (Ergonomía Visual)

Para garantizar un producto ágil y estéticamente superior:
* **Cero KPIs Inútiles:** Se prohíbe el uso de tarjetas KPI decorativas o redundantes en pantallas de listado o formularios (Artículos, Socios, Usuarios, Facturas).
* **Conteo Minimalista en Badges (`.tab-badge`):** El volumen de registros se presenta integrado discretamente en la pestaña de cada módulo.
* **Componentes Escalables:** Para relaciones uno-a-muchos (como asignación de almacenes), se implementan contenedores colapsables con buscador en tiempo real y switches compactos, soportando cientos de elementos sin romper el diseño responsive.

---

## 11. Protección de Propiedad Intelectual y Seguridad

* **Modelo SaaS Estricto (Cero Código Local):** El código fuente nunca es entregado ni instalado en las instalaciones del cliente. La aplicación se ejecuta exclusivamente en la nube administrada por el desarrollador.
* **Blindaje contra Modificaciones (*Forking*):** Los departamentos de TI de los clientes no tienen acceso al servidor ni al backend, imposibilitando modificaciones no autorizadas de la plataforma.
* **Resguardo Legal:** Uso de un Acuerdo de Licencia de Usuario Final (**EULA**) redactado legalmente que prohíbe de forma explícita la ingeniería inversa, la extracción masiva de esquemas y la clonación del software.

---

## 12. Herramientas de Incorporación y Migración de Datos
* **Asistentes ETL Visuales:** Funcionalidades integradas en el ERP que permiten a las nuevas clínicas cargar sus datos históricos (desde hojas de cálculo o sistemas legados) mediante un mapeador visual de columnas y validaciones automatizadas por lotes (*batch inserts*), facilitando una transición fluida hacia la plataforma.