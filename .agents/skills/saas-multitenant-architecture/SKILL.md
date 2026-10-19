---
name: saas-multitenant-architecture
description: Arquitectura SaaS Multi-tenant (Schema-per-Tenant en PostgreSQL), middleware tenantResolver, aprovisionamiento automatizado, modelo Zero-Contact, aislamiento de datos y stack Node.js/Express + TypeScript + React.
---

# Arquitectura SaaS Multi-Tenant (PostgreSQL Schema-per-Tenant)

Esta skill establece la arquitectura técnica, estándares de aislamiento de datos, flujo de aprovisionamiento automatizado y directrices de implementación para el nuevo ERP clínico bajo el modelo **SaaS Multi-Tenant "Zero-Contact"**.

---

## 1. Patrón de Aislamiento: Schema-per-Tenant en PostgreSQL

Para garantizar cumplimiento de normativas de salud (HIPAA, confidencialidad de expedientes clínicos) y segregación fiscal exigida por la DGII, se utiliza el patrón **Schema-per-Tenant**:

* **Base de Datos Única**: Una única instancia física de PostgreSQL aloja a todos los clientes.
* **Esquema Global (`public`)**:
  - `tenants`: Registro central de clínicas/empresas (`id`, `slug`, `legal_name`, `rnc`, `schema_name`, `subscription_status`, `created_at`).
  - `plans`: Definición de planes, límites de usuarios, módulos y almacenamiento.
  - `subscriptions`: Estado de facturación recurrente, método de pago, vigencia.
  - `system_logs` / `global_audit`: Auditoría y eventos globales de la plataforma.
* **Esquema Aislado por Clínica (`tenant_{slug}` o `org_{uuid}`)**:
  - Cada cliente opera dentro de su propio esquema en PostgreSQL (`tenant_oftalmo_centro`, `tenant_clinica_norte`).
  - Contiene todas las tablas operativas: `users`, `roles`, `patients`, `items`, `sales_invoices`, `payments`, `cash_shifts`, `ars_authorizations`, `dgii_ncf_ranges`, etc.
  - **Ventaja Crítica**: Fuga cero entre clientes. Si una consulta omite un filtro WHERE, jamás expondrá datos de otro tenant. Backup y restauración independiente por clínica con `pg_dump -n tenant_slug`.

---

## 2. Resolución de Tenant en Node.js / Express (`tenantResolver`)

El backend identifica dinámicamente el tenant a través de un middleware de resolución antes de procesar cualquier solicitud:

### 2.1 Métodos de Detección
1. **Subdominio**: `https://clinica-norte.oftalmoerp.com` $\rightarrow$ `slug = 'clinica-norte'`.
2. **Header HTTP**: `X-Tenant-ID: clinica-norte` (utilizado por apps móviles y webhooks).
3. **Claim en JWT**: En peticiones autenticadas, el token firmado contiene `{ tenant_id, user_id, role }`.

### 2.2 Middleware de Enrutamiento de Esquema (`tenantMiddleware.ts`)

```typescript
import { Request, Response, NextFunction } from 'express';
import { pool } from '../config/database';

export interface TenantRequest extends Request {
  tenantSchema?: string;
  tenantId?: string;
}

export const tenantResolver = async (req: TenantRequest, res: Response, next: NextFunction) => {
  try {
    // 1. Extraer identificador de subdominio o header
    let slug = req.headers['x-tenant-id'] as string;
    if (!slug && req.hostname) {
      const parts = req.hostname.split('.');
      if (parts.length > 2) slug = parts[0];
    }

    if (!slug) {
      return res.status(400).json({ error: 'Tenant identifier is required' });
    }

    // 2. Validar existencia y vigencia en el esquema público
    const tenantRes = await pool.query(
      `SELECT id, schema_name, subscription_status FROM public.tenants WHERE slug = $1 AND is_active = true`,
      [slug]
    );

    if (tenantRes.rows.length === 0) {
      return res.status(404).json({ error: 'Tenant not found or inactive' });
    }

    const tenant = tenantRes.rows[0];
    if (tenant.subscription_status === 'suspended') {
      return res.status(403).json({ error: 'Subscription is suspended' });
    }

    // 3. Asignar esquema a la petición
    req.tenantSchema = tenant.schema_name;
    req.tenantId = tenant.id;

    // 4. Configurar el search_path en la conexión de la transacción
    // Cada query scoped debe ejecutar: SET search_path TO "tenant_xxx", public;
    next();
  } catch (error) {
    next(error);
  }
};
```

---

## 3. Filosofía Zero-Contact SaaS (Autoservicio Completo)

El sistema opera bajo el principio de **Zero-Contact**: cualquier clínica u oftalmólogo debe poder registrarse, configurar su clínica y comenzar a facturar en menos de 5 minutos sin intervención de soporte técnico:

```
[ Landing Page ] ──> [ Registro & Aceptación EULA ] ──> [ Aprovisionamiento Automático ]
                                                                   │
                                                                   ▼
[ Onboarding Wizard en Frontend ] <── [ Redirección a subdominio / Dashboard listo ]
  - Paso 1: Configurar Almacén Inicial
  - Paso 2: Cargar Catálogo (Excel / Rápido)
  - Paso 3: Carga de NCF DGII
  - Paso 4: Carga de Certificado e-CF (.p12)
```

### 3.1 Proceso de Aprovisionamiento Automático (`TenantProvisioner.ts`)
Al completarse el registro en el portal:
1. `CREATE SCHEMA "tenant_{slug}";`
2. Ejecución automatizada de migraciones base sobre el nuevo esquema.
3. Siembra de datos iniciales:
   - Rol de Administrador inicial y asignación del usuario creador.
   - Listas de precios por defecto (`Lista Principal`).
   - Almacén principal predeterminado (`Almacén General`).
   - Catálogo base de impuestos dominicanos (`ITBIS 18%`, `Exento`).
   - Cajas y métodos de pago estándar (Efectivo, Tarjeta, Transferencia).
4. Generación de credenciales JWT y redirección al Onboarding Wizard.

---

## 4. Estrategia de Migraciones Multi-Tenant

Para aplicar cambios de base de datos a cientos de esquemas de clientes:

1. **Scripts Idempotentes**: Todas las migraciones usan `IF NOT EXISTS` y `ADD COLUMN IF NOT EXISTS`.
2. **Runner Centralizado (`migrate-tenants.ts`)**:
   ```typescript
   export async function runTenantMigrations() {
     const tenants = await pool.query('SELECT schema_name FROM public.tenants WHERE is_active = true');
     for (const { schema_name } of tenants.rows) {
       console.log(`Migrating schema: ${schema_name}`);
       await pool.query(`SET search_path TO "${schema_name}", public;`);
       await executePendingMigrations(schema_name);
     }
   }
   ```
3. **Transaccionalidad**: Si una migración falla en un tenant, se revierte solo para ese tenant y se alerta en el log administrativo sin detener el resto del sistema.

---

## 5. Frontend Multi-Tenant en React + TypeScript

* **Contexto Global (`TenantContext.tsx`)**:
  - Almacena el perfil de la clínica (`nombreComercial`, `rnc`, `logoUrl`, `plan`, `modulosHabilitados`).
  - Adapta la navegación según los módulos suscritos (ej: si el plan no incluye Farmacia o Laboratorio, oculta las rutas correspondientes).
* **Almacenamiento de Tokens**:
  - `localStorage` o `HttpOnly Cookies` con prefijo de tenant para evitar colisiones entre sesiones de distintos tenants en el mismo navegador.
* **Axios / Fetch Interceptor**:
  - Inyecta automáticamente el header `X-Tenant-ID` en cada llamada a la API.

---

## 6. Seguridad y Cumplimiento Normativo

1. **Cifrado en Reposo**:
   - Claves de certificados DGII (`.p12`), tokens de pago y datos sensibles se cifran utilizando `AES-256-GCM` antes de persistir en base de datos.
2. **Row-Level Security (RLS)**:
   - Como segunda capa de defensa dentro de cada esquema, los roles restringidos (`cajero`, `recepcionista`) tienen políticas RLS para evitar leer información financiera ajena a su turno.
3. **Auditoría Transaccional**:
   - Toda creación, modificación o anulación guarda `user_id`, `ip_address`, `user_agent` y `created_at` inmutable.
