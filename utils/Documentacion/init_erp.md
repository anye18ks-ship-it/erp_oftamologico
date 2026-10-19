# Guía de Inicialización y Estructura del Micro-ERP (SaaS Oftalmológico)

Este documento contiene la guía paso a paso para inicializar el backend y frontend del ERP, las extensiones recomendadas para VS Code y la estructura de directorios oficial.

---

## 1. Extensiones Recomendadas para VS Code
* **PostgreSQL** (`ckackett.vscode-postgres`) - Para consultar la base de datos PostgreSQL directamente desde VS Code.
* **Tailwind CSS IntelliSense** (`bradlc.vscode-tailwindcss`) - Autocompletado de clases Tailwind.
* **ES7+ React/Redux/React-Native snippets** (`dsznajder.es7-react-js-snippets`) - Snippets para componentes React (`rafce`).
* **Prettier - Code Formatter** (`esbenp.prettier-vscode`) - Formateo automático de código.
* **ESLint** (`dbaeumer.vscode-eslint`) - Detección de errores de sintaxis y buenas prácticas.
* **Thunder Client** (`rangav.vscode-thunder-client`) - Pruebas rápidas de endpoints de la API.
* **DotENV** (`mikestead.dotenv`) - Resaltado de sintaxis para archivos `.env`.


### Comandos Rápidos Globales
```bash
npm run dev
npm run build
```

---

# 1. Crear la carpeta principal del ERP y entrar en ella
mkdir erp-oftalmologia
cd erp-oftalmologia


## 2. Inicialización del Backend con Node.js & PostgreSQL

### Paso a paso:
```bash
# 1. Crear la carpeta para la API/Backend y entrar
mkdir backend
cd backend

# 2. Inicializar el proyecto Node.js
npm init -y

# 3. Instalar las dependencias principales
# (express, pg para PostgreSQL, cors, dotenv, jsonwebtoken y bcryptjs para auth, multer para carga de archivos/certificados)
npm install express pg cors dotenv jsonwebtoken bcryptjs multer

# 4. Instalar nodemon como dependencia de desarrollo (para auto-reload)
npm install -D nodemon

# 5. Volver a la raíz del proyecto
cd ..
```

### Configuración en `backend/package.json`:
Agrega en la sección `"scripts"` (apuntando a `src/index.js`):
```json
"scripts": {
  "start": "node src/index.js",
  "dev": "nodemon src/index.js"
}
```

Para iniciar el backend en desarrollo:
```bash
cd backend
npm run dev
```

---

## 3. Inicialización del Frontend con React + Vite + TypeScript

### Paso a paso:
```bash
# 1. Crear el proyecto de React con Vite usando la plantilla de TypeScript
npm create vite@latest frontend -- --template react-ts
# (Aquí seleccionar React y TypeScript)

# 2. Entrar a la carpeta del frontend recién creada
cd frontend

# 3. Instalar las dependencias iniciales de Vite, React y tipos TypeScript
npm install

# 4. Instalar las librerías necesarias para rutas, peticiones, iconos y alertas
npm install react-router-dom axios lucide-react sweetalert2

# 5. Instalar Tailwind CSS y herramientas PostCSS
npm install -D tailwindcss postcss autoprefixer

# 6. Inicializar el archivo de configuración de Tailwind CSS
npx tailwindcss init -p

# 7. Iniciar el servidor de desarrollo de Vite
npm run dev
```

---

## 4. Estructura de Directorios Recomendada para el ERP

```bash
erp-oftalmologia/
├── backend/
│   ├── src/
│   │   ├── config/          # Conexión a PostgreSQL (db.js) y Pool
│   │   ├── controllers/     # Lógica (FacturaController, RecetaController, NCFController)
│   │   ├── routes/          # Endpoints REST (auth.routes.js, facturas.routes.js, etc.)
│   │   ├── middlewares/     # Auth JWT, tenantResolver (SET search_path TO schema_empresa)
│   │   ├── services/        # Lógica de firmado XML / DGII e-CF
│   │   ├── workers/         # Tareas programadas en background (ecfWorker.js, ncfAlertWorker.js)
│   │   ├── utils/           # Encriptación AES-256 para certificados .pfx
│   │   └── index.js         # Servidor Express principal
│   ├── .env                 # Variables de entorno (DB_HOST, DB_USER, JWT_SECRET)
│   └── package.json
│
├── frontend/
│   ├── src/
│   │   ├── assets/          # Logo, imágenes de demo
│   │   ├── components/      # UI Reusable (Modal, Tabla, Botón, Input, Sidebar)
│   │   ├── pages/           # Vistas (Dashboard, Facturacion, HistorialClinico, Inventario)
│   │   ├── services/        # Cliente Axios (api.ts, authService.ts)
│   │   ├── context/         # Estado global tipado (AuthContext, CajaContext, DemoContext)
│   │   ├── hooks/           # Custom hooks reutilizables
│   │   ├── types/           # Interfaces TypeScript (factura.ts, paciente.ts, ncf.ts)
│   │   ├── App.tsx
│   │   ├── main.tsx
│   │   └── vite-env.d.ts
│   ├── tailwind.config.js
│   ├── vite.config.ts
│   ├── tsconfig.json
│   ├── tsconfig.node.json
│   └── package.json
│
└── README.md
```

---

## 5. Configuración Inicial de la Base de Datos en PostgreSQL

Sigue estos pasos para inicializar el motor de base de datos desde cero:

### 5.1 Crear la Base de Datos en la Interfaz (pgAdmin 4 o DBeaver)
1. **Abrir pgAdmin** y conectarte a tu servidor local (o en la nube).
2. En el árbol izquierdo, haz clic derecho sobre **Databases** ➔ **Create** ➔ **Database...**
3. En la pestaña **General**:
   * **Database**: `erp_clinica`
   * **Owner**: `postgres`
4. En la pestaña **Definition**:
   * **Encoding**: `UTF8`
5. Haz clic en **Save**.

### 5.2 Ajustes de Rendimiento en `postgresql.conf` (Opcional pero Recomendado)
Para maximizar el rendimiento con millones de datos, ubica tu archivo `postgresql.conf` (en Windows usualmente en `C:\Program Files\PostgreSQL\15\data\postgresql.conf`) y ajusta estos valores:
```ini
# Memoria de trabajo y caché
shared_buffers = 512MB                  # (o 25% de la RAM del servidor)
work_mem = 16MB                         # Acelera consultas complejas con ordenamientos
maintenance_work_mem = 128MB            # Acelera creación de índices y autovacuum

# Zona Horaria Oficial para República Dominicana
timezone = 'America/Santo_Domingo'

# Parámetros de escritura en disco
checkpoint_completion_target = 0.9
wal_buffers = 16MB
```
*(Reinicia el servicio de PostgreSQL en `services.msc` si modificas este archivo).*

### 5.3 Ejecutar el Script Maestro de Estructura
1. En pgAdmin, haz clic derecho sobre la base de datos `erp_clinica` recién creada y selecciona **Query Tool** (Herramienta de Consulta).
2. Abre o copia el archivo:
   [`DOC/erp_oftamologico/utils/backups_sql/ERP_BD_postgree.sql`](file:///c:/Users/asanchez/Documents/Project/dr/DOC/erp_oftamologico/utils/backups_sql/ERP_BD_postgree.sql)
3. Presiona **F5** (o el botón de **Play / Execute**).
4. El script creará automáticamente:
   * Las extensiones `uuid-ossp`, `pgcrypto` y `unaccent`.
   * El esquema `core` (para el SaaS multi-empresa) y `clinica_template` (plantilla clínica).
   * Las **61 tablas**, todas las llaves foráneas, triggers de kardex y recálculo de costos, y vistas analíticas.
5. El tiempo de ejecución es de apenas **1 a 2 segundos**.

### 5.4 Aprovisionar la Primera Clínica
Ejecuta la siguiente llamada SQL en el Query Tool para crear tu primera clínica inquilina:
```sql
CALL core.sp_aprovisionar_nueva_clinica(
    '131234567',                    -- RNC de la clínica
    'Centro Oftalmológico Láser SRL', -- Razón Social
    'Clínica de Ojos Visión',       -- Nombre Comercial
    'clinica_vision',               -- Slug del Esquema (sin espacios ni caracteres especiales)
    2,                              -- ID del Plan (2 = CLÍNICO)
    'admin@vision.com.do',          -- Correo Administrador
    'AdminClave2026*',              -- Clave inicial
    'Dr. Carlos',                   -- Nombre
    'Mendoza'                       -- Apellido
);
```

---

## 6. Tareas Programadas y Background Workers (Código y Configuración)

Una base de datos transaccional no debe hacer peticiones HTTP a servidores externos para no congelar las transacciones de los cajeros. Por eso, las tareas periódicas se manejan en el backend de Node.js mediante **`node-cron`**.

### Instalar `node-cron` en el backend:
```bash
cd backend
npm install node-cron axios
```

Crea la carpeta de workers:
```bash
mkdir src/workers
```

---

### 6.1 Worker 1: Consulta de Estatus e-CF DGII (`src/workers/ecfWorker.js`)
* **Frecuencia**: Cada 2 minutos (`*/2 * * * *`).
* **Función**: Busca facturas enviadas a la DGII que quedaron en estado `Q` (En Cola) o `P` (En Proceso) y consulta la API oficial de la DGII usando el `track_id` para actualizar su estado a `Y` (Aceptado) o `R` (Rechazado).

```javascript
// src/workers/ecfWorker.js
const cron = require('node-cron');
const axios = require('axios');
const pool = require('../config/db');

function iniciarWorkerECF() {
    // Se ejecuta cada 2 minutos
    cron.schedule('*/2 * * * *', async () => {
        console.log('[CRON e-CF] Verificando comprobantes pendientes en la DGII...');
        const client = await pool.connect();
        try {
            // 1. Obtener empresas activas
            const { rows: empresas } = await client.query("SELECT slug_esquema, ambiente_dgii FROM core.empresas WHERE activo = TRUE");

            for (const emp of empresas) {
                // Cambiar al esquema de la clínica
                await client.query(`SET search_path TO ${emp.slug_esquema}, public`);

                // 2. Buscar e-CF pendientes con track_id
                const { rows: pendientes } = await client.query(`
                    SELECT id_documento_ecf, encf, track_id 
                    FROM ecf_documentos 
                    WHERE estado_proceso IN ('Q', 'P') AND track_id IS NOT NULL 
                    LIMIT 20
                `);

                for (const doc of pendientes) {
                    try {
                        const urlDgii = emp.ambiente_dgii === 'prod' 
                            ? `https://ecf.dgii.gov.do/consulta-resultado/${doc.track_id}`
                            : `https://ecf.dgii.gov.do/testecf/consulta-resultado/${doc.track_id}`;

                        const resp = await axios.get(urlDgii, { timeout: 10000 });
                        const { estado, codigo, mensaje } = resp.data;

                        if (estado === 'ACEPTADO') {
                            await client.query(`
                                UPDATE ecf_documentos 
                                SET estado_proceso = 'Y', codigo_estado_dgii = $1, mensaje_dgii = $2, updated_at = NOW()
                                WHERE id_documento_ecf = $3
                            `, [codigo, mensaje, doc.id_documento_ecf]);
                            console.log(`[CRON e-CF] e-NCF ${doc.encf} ACEPTADO por la DGII.`);
                        } else if (estado === 'RECHAZADO') {
                            await client.query(`
                                UPDATE ecf_documentos 
                                SET estado_proceso = 'R', codigo_estado_dgii = $1, mensaje_dgii = $2, updated_at = NOW()
                                WHERE id_documento_ecf = $3
                            `, [codigo, mensaje, doc.id_documento_ecf]);
                            console.log(`[CRON e-CF] e-NCF ${doc.encf} RECHAZADO.`);
                        }
                    } catch (errHttp) {
                        console.error(`[CRON e-CF] Error consultando DGII para ${doc.encf}:`, errHttp.message);
                    }
                }
            }
        } catch (error) {
            console.error('[CRON e-CF] Error general:', error);
        } finally {
            client.release();
        }
    });
}

module.exports = iniciarWorkerECF;
```

---

### 6.2 Worker 2: Monitor Matutino de Secuencias NCF (`src/workers/ncfAlertWorker.js`)
* **Frecuencia**: Todos los días a las 8:00 AM (`0 8 * * *`).
* **Función**: Consulta la vista `v_disponibilidad_ncf`. Si el semáforo está en `ROJO_CRITICO` (menos de 50 facturas o menos de 5 días para expirar) o `AMARILLO_ALERTA`, genera una notificación en el sistema o envía correo al administrador.

```javascript
// src/workers/ncfAlertWorker.js
const cron = require('node-cron');
const pool = require('../config/db');

function iniciarWorkerNCFAlerts() {
    // Se ejecuta a las 8:00 AM de lunes a sábado
    cron.schedule('0 8 * * 1-6', async () => {
        console.log('[CRON NCF] Verificando semáforos de comprobantes fiscales...');
        const client = await pool.connect();
        try {
            const { rows: empresas } = await client.query("SELECT slug_esquema, razon_social, correo_contacto FROM core.empresas WHERE activo = TRUE");

            for (const emp of empresas) {
                await client.query(`SET search_path TO ${emp.slug_esquema}, public`);

                const { rows: alertas } = await client.query(`
                    SELECT serie, tipo_comprobante, secuencias_disponibles, dias_para_vencer, semaforo_estado
                    FROM v_disponibilidad_ncf
                    WHERE semaforo_estado IN ('ROJO_CRITICO', 'AMARILLO_ALERTA')
                `);

                for (const al of alertas) {
                    console.warn(`[ALERTA NCF] ${emp.razon_social} | Serie: ${al.serie} (${al.tipo_comprobante}) ` +
                                 `| Disponibles: ${al.secuencias_disponibles} | Vence en: ${al.dias_para_vencer} días | Estado: ${al.semaforo_estado}`);
                    
                    // Aquí puedes disparar un email (Nodemailer) o notificación Push a la interfaz
                }
            }
        } catch (error) {
            console.error('[CRON NCF] Error:', error);
        } finally {
            client.release();
        }
    });
}

module.exports = iniciarWorkerNCFAlerts;
```

---

### 6.3 Worker 3: Recordatorio de Citas Médicas (`src/workers/citasReminderWorker.js`)
* **Frecuencia**: Todos los días a las 6:00 PM (`0 18 * * *`).
* **Función**: Busca citas programadas para el día siguiente y despacha recordatorios a los pacientes por WhatsApp / SMS.

```javascript
// src/workers/citasReminderWorker.js
const cron = require('node-cron');
const pool = require('../config/db');

function iniciarWorkerCitas() {
    cron.schedule('0 18 * * *', async () => {
        console.log('[CRON Citas] Enviando recordatorios para citas de mañana...');
        const client = await pool.connect();
        try {
            const { rows: empresas } = await client.query("SELECT slug_esquema FROM core.empresas WHERE activo = TRUE");

            for (const emp of empresas) {
                await client.query(`SET search_path TO ${emp.slug_esquema}, public`);

                const { rows: citas } = await client.query(`
                    SELECT c.id_cita, c.hora_inicio, p.nombres, p.apellidos, p.telefono_principal, m.nombres AS medico
                    FROM citas_medicas c
                    JOIN expedientes exp ON exp.id_expediente = c.id_expediente
                    JOIN socios_negocio p ON p.id_socio = exp.id_paciente
                    JOIN socios_negocio m ON m.id_socio = c.id_medico
                    WHERE c.fecha_cita = CURRENT_DATE + INTERVAL '1 day'
                      AND c.estado = 'PROGRAMADA'
                `);

                for (const cita of citas) {
                    if (cita.telefono_principal) {
                        console.log(`[WHATSAPP] Recordatorio a ${cita.nombres} ${cita.apellidos} (${cita.telefono_principal}) - Mañana a las ${cita.hora_inicio} con ${cita.medico}`);
                        // Aquí se llama a la API de WhatsApp Business / Twilio
                    }
                }
            }
        } catch (error) {
            console.error('[CRON Citas] Error:', error);
        } finally {
            client.release();
        }
    });
}

module.exports = iniciarWorkerCitas;
```

---

### 6.4 Inicializador Central de Workers en el Servidor (`src/index.js`)
Agrega en tu archivo principal de Express:
```javascript
// En backend/src/index.js
const express = require('express');
const app = express();

// Importar workers
const iniciarWorkerECF = require('./workers/ecfWorker');
const iniciarWorkerNCFAlerts = require('./workers/ncfAlertWorker');
const iniciarWorkerCitas = require('./workers/citasReminderWorker');

// Iniciar demonios en background
iniciarWorkerECF();
iniciarWorkerNCFAlerts();
iniciarWorkerCitas();

const PORT = process.env.PORT || 4000;
app.listen(PORT, () => {
    console.log(`Servidor ERP y Workers corriendo en el puerto ${PORT}`);
});
```

---

## 7. Guía de Navegación por la Interfaz de Usuario (Dónde se Gestiona Todo)

Esta guía detalla la ruta y funcionalidad de cada pantalla en el Frontend React para que tú o el usuario sepan exactamente dónde interactuar:

```
┌────────────────────────────────────────────────────────────────────────┐
│                        MAPA DE LA INTERFAZ ERP                         │
├──────────────────────────┬───────────────────────┬─────────────────────┤
│ Módulo                   │ Ruta Frontend         │ Acción Principal    │
├──────────────────────────┼───────────────────────┼─────────────────────┤
│ 1. Configuración Tenant  │ /configuracion        │ Certificado e-CF    │
│ 2. Lotes NCF & Semáforos │ /facturacion/ncf      │ Alertas secuencias  │
│ 3. Monitor e-CF          │ /facturacion/ecf      │ TrackId & Reenvíos  │
│ 4. Turnos & Arqueo Caja  │ /caja/cierre          │ Billetes y Desglose │
│ 5. Sala de Espera        │ /citas/sala-espera    │ Llamador TV Turnos  │
│ 6. Historial & Tonometría│ /expediente/:id       │ IOP, OCT y Recetas  │
└──────────────────────────┴───────────────────────┴─────────────────────┘
```

### 7.1 Pantalla de Configuración de Empresa (`/configuracion`)
* **Ubicación en el Menú**: Sidebar ➔ **Ajustes** ➔ **Datos de la Clínica**.
* **Acciones**:
  1. Subir el archivo de **Certificado Digital** (`.p12` o `.pfx`) y escribir la contraseña del certificado.
  2. Seleccionar el **Ambiente DGII** (`test` para desarrollo, `cert` para certificación, `prod` para producción fiscal).
  3. Establecer el umbral de alerta de NCF (por defecto 50 unidades y 15 días).

### 7.2 Pantalla de Gestión de NCF y Comprobantes (`/facturacion/ncf`)
* **Ubicación en el Menú**: Sidebar ➔ **Facturación** ➔ **Secuencias NCF**.
* **Qué muestra**: La vista `v_disponibilidad_ncf` con tarjetas de colores:
  - 🟢 **Verde**: Secuencias disponibles > 100 y fecha de vencimiento lejana.
  - 🟡 **Amarillo**: Menos de 50 disponibles o menos de 15 días para vencer.
  - 🔴 **Rojo Crítico**: Menos de 20 disponibles o menos de 5 días para expirar (requiere solicitar nuevo lote a la DGII).
* **Acción**: Botón **"Cargar Nuevo Lote NCF"** para ingresar el nuevo rango otorgado por la DGII.

### 7.3 Monitor Transaccional e-CF DGII (`/facturacion/ecf`)
* **Ubicación en el Menú**: Sidebar ➔ **Facturación** ➔ **Monitor e-CF**.
* **Qué muestra**:
  - Estado del comprobante en tiempo real:
    * `Y` (Verde): Aceptado por DGII con su código QR timbrado.
    * `P` / `Q` (Azul/Amarillo): En vuelo o en proceso (el worker de Node.js lo está consultando).
    * `R` (Rojo): Rechazado (muestra el mensaje exacto de la DGII y glosas).
* **Acción**: Botón **"Forzar Sincronización DGII"** o **"Reintentar Firma"**.

### 7.4 Control de Turnos y Cierre de Caja (`/caja/cierre`)
* **Ubicación en el Menú**: Sidebar ➔ **Caja POS** ➔ **Cierre de Turno**.
* **Qué hace**:
  1. Muestra la tabla de desglose físico de billetes ($2000, $1000, $500, $200, $100, $50, monedas) para el arqueo.
  2. Calcula de forma automática el **Total Declarado** vs **Total del Sistema**.
  3. Imprime el ticket térmico de 72mm con corte exacto, sin saltos de página ni solapamientos.

### 7.5 Llamador de Turnos y Sala de Espera (`/citas/sala-espera`)
* **Ubicación en el Menú**: Sidebar ➔ **Citas** ➔ **Monitor de Espera**.
* **Qué hace**:
  1. Diseñada para pantalla de televisión en la sala de espera.
  2. Consulta la vista `v_flujo_pacientes_hoy`.
  3. Muestra turno actual, consultorio asignado y emite alerta sonora para el llamado de pacientes.


 