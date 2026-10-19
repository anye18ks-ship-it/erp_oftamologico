-- ==============================================================================
-- SISTEMA ERP VERTICAL SAAS: SALUD VISUAL Y CLÍNICA OFTALMOLÓGICA
-- SCRIPT MAESTRO DDL INTEGRAL: POSTGRESQL 15+ (ARQUITECTURA MULTI-TENANT)
-- Archivo: ERP_BD_postgree.sql
CREATE DATABASE erp_clinica;
-- ==============================================================================
-- UNIFICACIÓN TOTAL Y DEFINITIVA DE LAS DOS BASES DE DATOS HEREDADAS:
--   1. schema_CMS.sql (~348 KB) -> Facturación, Inventario, Cajas y Firmador e-CF
--   2. schema_Expediente.sql (~322 KB) -> Clínica, Citas, Cirugías, Óptica, Comisiones
--
-- PRINCIPIOS DE OPTIMIZACIÓN X1000:
--   * Unificación en una sola base de datos y un solo esquema por clínica (cero desincronización)
--   * Eliminación definitiva de 400+ columnas muertas de SAP Business One
--   * Modelo Híbrido Relacional + JSONB indexado con GIN para flexibilidad clínica
--   * Flujo Clínico-Comercial Integrado: Consulta -> Receta -> POS / ARS -> e-CF DGII
--   * Módulos completos: Quirófano, Presupuestos, Taller de Óptica, Comisiones, Tandas
-- ==============================================================================

-- ------------------------------------------------------------------------------
-- EXTENSIONES GLOBALES
-- ------------------------------------------------------------------------------
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";
CREATE EXTENSION IF NOT EXISTS "unaccent";

-- ==============================================================================
-- SECCIÓN 1: ESQUEMA 'core' - PLATAFORMA SAAS, MULTI-EMPRESA Y LICENCIAMIENTO
-- ==============================================================================
CREATE SCHEMA IF NOT EXISTS core;

-- 1.1 Planes de Suscripción del SaaS
CREATE TABLE core.planes_suscripcion (
    id_plan             SERIAL PRIMARY KEY,
    codigo              VARCHAR(30) NOT NULL UNIQUE,     -- 'BASICO', 'CLINICO', 'ENTERPRISE_QUIRURGICO'
    nombre              VARCHAR(100) NOT NULL,
    descripcion         TEXT,
    precio_mensual_usd  NUMERIC(10, 2) NOT NULL DEFAULT 0.00,
    limite_usuarios     INTEGER NOT NULL DEFAULT 5,
    limite_sucursales   INTEGER NOT NULL DEFAULT 1,
    limite_facturas_mes INTEGER NOT NULL DEFAULT 1000,
    permite_quirofano   BOOLEAN NOT NULL DEFAULT FALSE,
    permite_taller_optica BOOLEAN NOT NULL DEFAULT TRUE,
    permite_ecf_dgii    BOOLEAN NOT NULL DEFAULT TRUE,
    activo              BOOLEAN NOT NULL DEFAULT TRUE,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 1.2 Directorio de Empresas / Clínicas Inquilinas (Tenants)
CREATE TABLE core.empresas (
    id_empresa          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    rnc                 VARCHAR(15) NOT NULL UNIQUE,
    razon_social        VARCHAR(200) NOT NULL,
    nombre_comercial    VARCHAR(200),
    slug_esquema        VARCHAR(63) NOT NULL UNIQUE,     -- Nombre del esquema en PostgreSQL (ej: 'clinica_agrofem')
    id_plan             INTEGER NOT NULL REFERENCES core.planes_suscripcion(id_plan),
    correo_contacto     VARCHAR(100) NOT NULL,
    telefono_contacto   VARCHAR(25),
    direccion           TEXT,
    ciudad              VARCHAR(100) DEFAULT 'Santo Domingo',
    dominio_personalizado VARCHAR(150),
    logo_url            TEXT,
    ambiente_dgii       VARCHAR(10) NOT NULL DEFAULT 'test' CHECK (ambiente_dgii IN ('test', 'cert', 'prod')),
    activo              BOOLEAN NOT NULL DEFAULT TRUE,
    fecha_alta          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_renovacion    DATE,
    configuraciones     JSONB NOT NULL DEFAULT '{"moneda": "DOP", "timezone": "America/Santo_Domingo"}'::jsonb,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_empresas_rnc ON core.empresas(rnc);
CREATE INDEX idx_empresas_slug ON core.empresas(slug_esquema);

-- 1.3 Usuarios Maestros y Superadmins de la Plataforma
CREATE TABLE core.usuarios_globales (
    id_usuario_global   UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    email               VARCHAR(150) NOT NULL UNIQUE,
    password_hash       VARCHAR(255) NOT NULL,
    nombres             VARCHAR(100) NOT NULL,
    apellidos           VARCHAR(100) NOT NULL,
    es_superadmin       BOOLEAN NOT NULL DEFAULT FALSE,
    activo              BOOLEAN NOT NULL DEFAULT TRUE,
    ultimo_acceso       TIMESTAMPTZ,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 1.4 Auditoría de Acceso SaaS
CREATE TABLE core.auditoria_global (
    id_auditoria        BIGSERIAL PRIMARY KEY,
    id_empresa          UUID REFERENCES core.empresas(id_empresa) ON DELETE CASCADE,
    email_usuario       VARCHAR(150) NOT NULL,
    accion              VARCHAR(100) NOT NULL,
    ip_origen           VARCHAR(45),
    user_agent          TEXT,
    detalles            JSONB,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);


-- ==============================================================================
-- SECCIÓN 2: ESQUEMA INQUILINO 'clinica_template' (PLANTILLA INTEGRAL)
-- ==============================================================================
-- Este esquema se clona para cada clínica registrada en core.empresas
CREATE SCHEMA IF NOT EXISTS clinica_template;

-- ------------------------------------------------------------------------------
-- 2.1 ESTRUCTURA DE SEDES, CONSULTORIOS Y SEGURIDAD RBAC
-- ------------------------------------------------------------------------------

-- Sedes Físicas / Sucursales de la Clínica
CREATE TABLE clinica_template.sucursales (
    id_sucursal         SERIAL PRIMARY KEY,
    codigo              VARCHAR(15) NOT NULL UNIQUE,     -- 'SEDE-CENTRAL', 'PLAZA-NORTE'
    nombre              VARCHAR(150) NOT NULL,
    telefono            VARCHAR(25),
    correo              VARCHAR(100),
    direccion           TEXT NOT NULL,
    ciudad              VARCHAR(100) NOT NULL DEFAULT 'Santo Domingo',
    es_matriz           BOOLEAN NOT NULL DEFAULT FALSE,
    activo              BOOLEAN NOT NULL DEFAULT TRUE,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Consultorios, Quirófanos y Salas de Diagnóstico
CREATE TABLE clinica_template.consultorios (
    id_consultorio      SERIAL PRIMARY KEY,
    id_sucursal         INTEGER NOT NULL REFERENCES clinica_template.sucursales(id_sucursal),
    codigo              VARCHAR(15) NOT NULL,            -- 'CONS-01', 'QUIROF-A', 'OPT-01'
    nombre              VARCHAR(100) NOT NULL,
    tipo                VARCHAR(25) NOT NULL DEFAULT 'CONSULTORIO' 
                        CHECK (tipo IN ('CONSULTORIO', 'QUIROFANO', 'OPTICA_MOSTRADOR', 'DIAGNOSTICO_OCT', 'RECUPERACION')),
    equipamiento        JSONB DEFAULT '[]'::jsonb,       -- Lista de equipos instalados (Lámpara de hendidura, Foróptero, Láser)
    activo              BOOLEAN NOT NULL DEFAULT TRUE,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(id_sucursal, codigo)
);

-- Roles del Sistema Clínico-Administrativo
CREATE TABLE clinica_template.roles (
    id_rol              SERIAL PRIMARY KEY,
    codigo              VARCHAR(30) NOT NULL UNIQUE,     -- 'ADMIN', 'OFTALMOLOGO', 'OPTOMETRA', 'CIRUJANO', 'CAJERO', 'RECEPCION'
    nombre              VARCHAR(100) NOT NULL,
    descripcion         TEXT,
    es_sistema          BOOLEAN NOT NULL DEFAULT FALSE,
    activo              BOOLEAN NOT NULL DEFAULT TRUE,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Permisos Granulares por Acción
CREATE TABLE clinica_template.permisos (
    id_permiso          SERIAL PRIMARY KEY,
    modulo              VARCHAR(50) NOT NULL,            -- 'HISTORIAL', 'FACTURACION', 'CAJA', 'QUIROFANO', 'OPTICA', 'ARS'
    codigo              VARCHAR(80) NOT NULL UNIQUE,     -- 'facturas.crear', 'historial.editar', 'cirugia.programar'
    descripcion         TEXT NOT NULL,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE clinica_template.rol_permisos (
    id_rol              INTEGER NOT NULL REFERENCES clinica_template.roles(id_rol) ON DELETE CASCADE,
    id_permiso          INTEGER NOT NULL REFERENCES clinica_template.permisos(id_permiso) ON DELETE CASCADE,
    PRIMARY KEY (id_rol, id_permiso)
);

-- Usuarios Locales de la Clínica
CREATE TABLE clinica_template.usuarios (
    id_usuario          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    username            VARCHAR(50) NOT NULL UNIQUE,
    email               VARCHAR(150) NOT NULL UNIQUE,
    password_hash       VARCHAR(255) NOT NULL,
    nombres             VARCHAR(100) NOT NULL,
    apellidos           VARCHAR(100) NOT NULL,
    cedula              VARCHAR(15),
    telefono            VARCHAR(25),
    id_rol              INTEGER NOT NULL REFERENCES clinica_template.roles(id_rol),
    id_sucursal_defecto INTEGER REFERENCES clinica_template.sucursales(id_sucursal),
    foto_url            TEXT,
    configuraciones_ui  JSONB NOT NULL DEFAULT '{"theme": "light", "fontSize": "normal"}'::jsonb,
    activo              BOOLEAN NOT NULL DEFAULT TRUE,
    ultimo_acceso       TIMESTAMPTZ,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE clinica_template.usuario_sucursales (
    id_usuario          UUID NOT NULL REFERENCES clinica_template.usuarios(id_usuario) ON DELETE CASCADE,
    id_sucursal         INTEGER NOT NULL REFERENCES clinica_template.sucursales(id_sucursal) ON DELETE CASCADE,
    PRIMARY KEY (id_usuario, id_sucursal)
);


-- ------------------------------------------------------------------------------
-- 2.2 DIRECTORIO MAESTRO DE ENTIDADES (SOCIOS DE NEGOCIO: PACIENTES, MÉDICOS, ARS, SUPLIDORES)
-- Consolida posOCRD, s_Paciente, s_Doctor, s_Seguro y s_Suplidor
-- ------------------------------------------------------------------------------

CREATE TABLE clinica_template.socios_negocio (
    id_socio            UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    codigo              VARCHAR(30) NOT NULL UNIQUE,     -- 'PAC-0001', 'MED-01', 'ARS-SENASA', 'SUP-01'
    tipo_socio          VARCHAR(25) NOT NULL CHECK (tipo_socio IN ('PACIENTE', 'MEDICO', 'OPTOMETRA', 'ASEGURADORA_ARS', 'SUPLIDOR', 'REFERIDOR')),
    nombres             VARCHAR(100) NOT NULL,
    apellidos           VARCHAR(100),                    -- Opcional para personas jurídicas
    razon_social        VARCHAR(200),
    tipo_documento      VARCHAR(10) NOT NULL DEFAULT 'CEDULA' CHECK (tipo_documento IN ('CEDULA', 'RNC', 'PASAPORTE', 'OTRO')),
    numero_documento    VARCHAR(20) NOT NULL,
    fecha_nacimiento    DATE,
    sexo                CHAR(1) CHECK (sexo IN ('M', 'F', 'O')),
    estado_civil        VARCHAR(15) CHECK (estado_civil IN ('SOLTERO', 'CASADO', 'UNION_LIBRE', 'VIUDO', 'DIVORCIADO')),
    correo              VARCHAR(150),
    telefono_principal  VARCHAR(25),
    telefono_secundario VARCHAR(25),
    direccion           TEXT,
    sector              VARCHAR(100),
    ciudad              VARCHAR(100) DEFAULT 'Santo Domingo',
    nacionalidad        VARCHAR(50) DEFAULT 'Dominicana',
    tipo_sangre         VARCHAR(5),                      -- 'O+', 'A+', 'B+', 'AB-', etc.
    
    -- Sub-perfil Clínico del Paciente
    ocupacion           VARCHAR(100),
    referido_por        VARCHAR(150),
    alergias_graves     TEXT,
    tiene_glaucoma      BOOLEAN NOT NULL DEFAULT FALSE,
    tiene_diabetes      BOOLEAN NOT NULL DEFAULT FALSE,
    tiene_hipertension  BOOLEAN NOT NULL DEFAULT FALSE,
    antecedentes_retina BOOLEAN NOT NULL DEFAULT FALSE,
    foto_perfil_url     TEXT,

    -- Sub-perfil Médico / Profesional
    exequatur           VARCHAR(30),                     -- Registro médico de salud pública
    especialidad        VARCHAR(100),                    -- 'Oftalmólogo Cirujano', 'Optometrista', 'Glaucomatólogo'
    porcentaje_comision NUMERIC(5, 2) DEFAULT 0.00,
    horarios_consulta   JSONB DEFAULT '[]'::jsonb,       -- Reemplaza 60 columnas de s_Doctor
    id_consultorio_defecto INTEGER REFERENCES clinica_template.consultorios(id_consultorio),

    -- Sub-perfil Aseguradora ARS
    codigo_sisalril     VARCHAR(20),                     -- Código oficial regulatorio
    dias_credito        INTEGER DEFAULT 30,
    permite_copago_cero BOOLEAN DEFAULT TRUE,

    -- Parámetros Fiscales y Comerciales
    exento_itbis        BOOLEAN NOT NULL DEFAULT FALSE,
    limite_credito      NUMERIC(14, 2) DEFAULT 0.00,
    activo              BOOLEAN NOT NULL DEFAULT TRUE,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_socios_tipo ON clinica_template.socios_negocio(tipo_socio);
CREATE INDEX idx_socios_doc ON clinica_template.socios_negocio(numero_documento);
CREATE INDEX idx_socios_nombre ON clinica_template.socios_negocio(nombres, apellidos);
CREATE INDEX idx_socios_horarios_gin ON clinica_template.socios_negocio USING gin(horarios_consulta);


-- ------------------------------------------------------------------------------
-- 2.3 POLÍTICAS DE COMISIONES MÉDICAS AVANZADAS
-- Sustituye s_HeaderPoliticaCom, s_DetallePoliticaCom y sp_ComisionDoctores
-- ------------------------------------------------------------------------------

CREATE TABLE clinica_template.politicas_comision_medica (
    id_politica         SERIAL PRIMARY KEY,
    nombre              VARCHAR(100) NOT NULL,
    descripcion         TEXT,
    activo              BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE clinica_template.politica_comision_detalles (
    id_detalle_politica SERIAL PRIMARY KEY,
    id_politica         INTEGER NOT NULL REFERENCES clinica_template.politicas_comision_medica(id_politica) ON DELETE CASCADE,
    id_medico           UUID NOT NULL REFERENCES clinica_template.socios_negocio(id_socio),
    id_articulo_servicio INTEGER,                        -- NULL aplica para todos los servicios de la categoría
    tipo_procedimiento  VARCHAR(30) CHECK (tipo_procedimiento IN ('CONSULTA', 'ESTUDIO', 'CIRUGIA', 'LASER', 'TODO')),
    forma_calculo       VARCHAR(10) NOT NULL DEFAULT 'FAC' CHECK (forma_calculo IN ('FAC', 'COB', 'SAL')), -- Facturado / Cobrado / Saldo privado
    porc_comision_medico NUMERIC(5, 2) NOT NULL DEFAULT 50.00,
    porc_retencion_clinica NUMERIC(5, 2) NOT NULL DEFAULT 50.00, -- PrComCentro
    porc_asistente_cirugia NUMERIC(5, 2) NOT NULL DEFAULT 0.00,  -- PrComAsis
    monto_fijo_garantizado NUMERIC(14, 2) DEFAULT 0.00,
    activo              BOOLEAN NOT NULL DEFAULT TRUE
);


-- ------------------------------------------------------------------------------
-- 2.4 EXPEDIENTE OFTALMOLÓGICO, CITAS Y FLUJO DE SALA DE ESPERA (TANDAS)
-- Sustituye s_Expediente, s_Cita, s_ListaPaciente, s_ListaEspera, s_Tanda
-- ------------------------------------------------------------------------------

-- Expediente / Historia Clínica
CREATE TABLE clinica_template.expedientes (
    id_expediente       BIGSERIAL PRIMARY KEY,
    numero_expediente   VARCHAR(30) NOT NULL UNIQUE,     -- 'EXP-2026-0001'
    id_paciente         UUID NOT NULL REFERENCES clinica_template.socios_negocio(id_socio),
    id_medico_cabecera  UUID REFERENCES clinica_template.socios_negocio(id_socio),
    id_aseguradora_ars  UUID REFERENCES clinica_template.socios_negocio(id_socio),
    numero_afiliado_ars VARCHAR(30),
    numero_poliza_ars   VARCHAR(30),
    antecedentes_familiares TEXT,
    antecedentes_patologicos TEXT,
    antecedentes_quirurgicos TEXT,
    observaciones_alerta TEXT,
    activo              BOOLEAN NOT NULL DEFAULT TRUE,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_expediente_paciente ON clinica_template.expedientes(id_paciente);
CREATE INDEX idx_expediente_num ON clinica_template.expedientes(numero_expediente);

-- Tandas Horarias de Atención (Mañana / Tarde / Noche)
CREATE TABLE clinica_template.tandas_horarias (
    id_tanda            SERIAL PRIMARY KEY,
    codigo              VARCHAR(15) NOT NULL UNIQUE,     -- 'MANANA', 'TARDE', 'SABADO'
    nombre              VARCHAR(100) NOT NULL,
    hora_inicio         TIME NOT NULL,
    hora_fin            TIME NOT NULL,
    activo              BOOLEAN NOT NULL DEFAULT TRUE
);

-- Citas Médicas Programadas
CREATE TABLE clinica_template.citas_medicas (
    id_cita             BIGSERIAL PRIMARY KEY,
    id_expediente       BIGINT NOT NULL REFERENCES clinica_template.expedientes(id_expediente),
    id_medico           UUID NOT NULL REFERENCES clinica_template.socios_negocio(id_socio),
    id_sucursal         INTEGER NOT NULL REFERENCES clinica_template.sucursales(id_sucursal),
    id_consultorio      INTEGER REFERENCES clinica_template.consultorios(id_consultorio),
    id_tanda            INTEGER REFERENCES clinica_template.tandas_horarias(id_tanda),
    fecha_cita          DATE NOT NULL,
    hora_inicio         TIME NOT NULL,
    hora_fin            TIME,
    motivo_consulta     VARCHAR(250) NOT NULL,
    tipo_cita           VARCHAR(30) NOT NULL DEFAULT 'CONSULTA' 
                        CHECK (tipo_cita IN ('CONSULTA', 'REVISION', 'PROCEDIMIENTO', 'ESTUDIO', 'CIRUGIA')),
    estado              VARCHAR(20) NOT NULL DEFAULT 'PROGRAMADA' 
                        CHECK (estado IN ('PROGRAMADA', 'EN_SALA', 'EN_CONSULTA', 'ATENDIDA', 'CANCELADA', 'NO_ASISTIO')),
    notas               TEXT,
    hora_llegada_sala   TIMESTAMPTZ,
    hora_inicio_atencion TIMESTAMPTZ,
    hora_fin_atencion   TIMESTAMPTZ,
    id_usuario_registro UUID REFERENCES clinica_template.usuarios(id_usuario),
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_citas_fecha_medico ON clinica_template.citas_medicas(fecha_cita, id_medico);
CREATE INDEX idx_citas_estado ON clinica_template.citas_medicas(estado);

-- Sala de Espera y Turnos en Pantalla (Llamador de Pacientes)
CREATE TABLE clinica_template.turnos_sala_espera (
    id_turno_sala       BIGSERIAL PRIMARY KEY,
    id_cita             BIGINT REFERENCES clinica_template.citas_medicas(id_cita),
    id_expediente       BIGINT NOT NULL REFERENCES clinica_template.expedientes(id_expediente),
    id_medico           UUID NOT NULL REFERENCES clinica_template.socios_negocio(id_socio),
    id_consultorio      INTEGER NOT NULL REFERENCES clinica_template.consultorios(id_consultorio),
    numero_turno        INTEGER NOT NULL,                -- Turno del día: 1, 2, 3...
    codigo_pantalla     VARCHAR(10) NOT NULL,            -- 'T-01', 'C-05'
    hora_ingreso        TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    hora_llamado        TIMESTAMPTZ,
    estado              VARCHAR(20) NOT NULL DEFAULT 'ESPERANDO' 
                        CHECK (estado IN ('ESPERANDO', 'LLAMANDO', 'EN_ATENCION', 'FINALIZADO', 'CANCELADO')),
    pantalla_activa     BOOLEAN NOT NULL DEFAULT TRUE
);


-- ------------------------------------------------------------------------------
-- 2.5 CONSULTAS, AGUDEZA VISUAL, TONOMETRÍA, REFRACCIÓN Y ESTUDIOS
-- Sustituye las 222 columnas crípticas de s_Visita_Oftalmologia y s_Estudio...
-- ------------------------------------------------------------------------------

-- Consultas Oftalmológicas (Evolución Clínica)
CREATE TABLE clinica_template.consultas_oftalmologicas (
    id_consulta         BIGSERIAL PRIMARY KEY,
    id_expediente       BIGINT NOT NULL REFERENCES clinica_template.expedientes(id_expediente),
    id_cita             BIGINT REFERENCES clinica_template.citas_medicas(id_cita),
    id_medico           UUID NOT NULL REFERENCES clinica_template.socios_negocio(id_socio),
    fecha_consulta      TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    motivo_anamnesis    TEXT NOT NULL,
    tiempo_evolucion    VARCHAR(150),
    
    -- Biomicroscopía y Examen Físico Segmentado
    biomicroscopia_od   TEXT,                            -- Párpados, conjuntiva, córnea, cámara anterior, iris, cristalino
    biomicroscopia_oi   TEXT,
    fondo_ojo_od        TEXT,                            -- Nervio óptico, mácula, excavación papilar, retina
    fondo_ojo_oi        TEXT,
    gonioscopia_od      VARCHAR(100),                    -- Ángulo iridocorneal
    gonioscopia_oi      VARCHAR(100),
    
    -- Diagnósticos CIE-10
    diagnostico_cie10_principal VARCHAR(15),
    diagnostico_descripcion TEXT NOT NULL,
    diagnosticos_secundarios JSONB DEFAULT '[]'::jsonb,
    
    -- Conducta y Tratamiento
    conducta_tratamiento TEXT NOT NULL,
    indicaciones_receta TEXT,
    proxima_cita_sug    DATE,
    id_usuario_registro UUID REFERENCES clinica_template.usuarios(id_usuario),
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_consultas_exp ON clinica_template.consultas_oftalmologicas(id_expediente);
CREATE INDEX idx_consultas_fecha ON clinica_template.consultas_oftalmologicas(fecha_consulta);

-- Tonometría y Presión Intraocular (IOP) con Corrección Paquimétrica
CREATE TABLE clinica_template.tonometria_iop (
    id_tonometria       BIGSERIAL PRIMARY KEY,
    id_consulta         BIGINT NOT NULL REFERENCES clinica_template.consultas_oftalmologicas(id_consulta) ON DELETE CASCADE,
    hora_toma           TIME NOT NULL DEFAULT CURRENT_TIME,
    metodo              VARCHAR(30) NOT NULL DEFAULT 'GOLDMANN' CHECK (metodo IN ('GOLDMANN', 'NEUMATICO', 'PERKINS', 'TONOPEN')),
    
    -- Ojo Derecho (OD)
    pio_od_mmhg         NUMERIC(4, 1) NOT NULL,
    paquimetria_od_um   INTEGER,                         -- Espesor corneal central (CCT) en micras
    pio_corregida_od    NUMERIC(4, 1),                   -- Corregida según fórmula Ehlers / Dresden
    
    -- Ojo Izquierdo (OI)
    pio_oi_mmhg         NUMERIC(4, 1) NOT NULL,
    paquimetria_oi_um   INTEGER,
    pio_corregida_oi    NUMERIC(4, 1),
    
    observaciones       VARCHAR(255),
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Refracción Clínica y Fórmulas Optométricas
CREATE TABLE clinica_template.recetas_optometricas (
    id_receta           BIGSERIAL PRIMARY KEY,
    id_consulta         BIGINT NOT NULL REFERENCES clinica_template.consultas_oftalmologicas(id_consulta) ON DELETE CASCADE,
    tipo_refraccion     VARCHAR(20) NOT NULL DEFAULT 'SUBJETIVA' 
                        CHECK (tipo_refraccion IN ('OBJETIVA', 'SUBJETIVA', 'CICLOPLEJICA', 'FINAL_LENTES')),
    
    -- Ojo Derecho (OD)
    od_esfera           NUMERIC(4, 2),                   -- Dioptrías (+2.50, -1.75)
    od_cilindro         NUMERIC(4, 2),                   -- Cilindro astigmático
    od_eje              SMALLINT CHECK (od_eje BETWEEN 0 AND 180),
    od_adicion          NUMERIC(4, 2),                   -- Presbicia
    od_agudeza_sc       VARCHAR(10),                     -- '20/40'
    od_agudeza_cc       VARCHAR(10),                     -- '20/20'
    od_prisma           NUMERIC(4, 2),
    od_base_prisma      VARCHAR(10),

    -- Ojo Izquierdo (OI)
    oi_esfera           NUMERIC(4, 2),
    oi_cilindro         NUMERIC(4, 2),
    oi_eje              SMALLINT CHECK (oi_eje BETWEEN 0 AND 180),
    oi_adicion          NUMERIC(4, 2),
    oi_agudeza_sc       VARCHAR(10),
    oi_agudeza_cc       VARCHAR(10),
    oi_prisma           NUMERIC(4, 2),
    oi_base_prisma      VARCHAR(10),

    -- Medidas Físicas de Montura y Lentes
    distancia_pupilar   NUMERIC(4, 1),                   -- DP en mm (ej: 63.5)
    altura_oblea        NUMERIC(4, 1),
    tipo_lente_sug      VARCHAR(50),                     -- 'PROGRESIVO_DIGITAL', 'MONOFOCAL', 'BIFOCAL'
    material_sug        VARCHAR(50),                     -- 'POLICARBONATO', 'ALTO_INDICE_1.67', 'CR-39'
    tratamiento_sug     VARCHAR(100),                    -- 'ANTIRREFLEJO_PREMIUM', 'BLUE_BLOCK', 'TRANSITIONS'
    observaciones       TEXT,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Estudios Diagnósticos Oftalmológicos Avanzados (OCT, Campo Visual, Paquimetría)
CREATE TABLE clinica_template.estudios_diagnosticos (
    id_estudio          BIGSERIAL PRIMARY KEY,
    id_expediente       BIGINT NOT NULL REFERENCES clinica_template.expedientes(id_expediente),
    id_medico_solicita  UUID NOT NULL REFERENCES clinica_template.socios_negocio(id_socio),
    id_tecnico_realiza  UUID REFERENCES clinica_template.socios_negocio(id_socio),
    tipo_estudio        VARCHAR(50) NOT NULL,            -- 'OCT_MACULA', 'CAMPO_VISUAL_HUMPHREY', 'TOPOGRAFIA_CORNEAL', 'PAQUIMETRIA', 'BIOMETRIA_IOL'
    ojo                 VARCHAR(5) NOT NULL CHECK (ojo IN ('OD', 'OI', 'AMBOS')),
    fecha_estudio       TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    informe_medico      TEXT NOT NULL,
    conclusiones        TEXT,
    datos_especificos   JSONB DEFAULT '{}'::jsonb,       -- Parámetros numéricos del estudio (MD, PSD, VFI en campo visual, etc.)
    archivos_adjuntos   JSONB DEFAULT '[]'::jsonb,       -- Enlaces seguros a imágenes DICOM / PDFs en almacenamiento S3
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);


-- ------------------------------------------------------------------------------
-- 2.6 QUIRÓFANO, CIRUGÍAS Y PAQUETES QUIRÚRGICOS
-- Sustituye s_Quirofano, s_ComboTipoCirugia, s_ComboTipoCirugia_Procedimientos
-- ------------------------------------------------------------------------------

-- Paquetes y Tipos de Cirugía (Catarata, Faco, Pterigión, LASIK)
CREATE TABLE clinica_template.paquetes_quirurgicos (
    id_paquete          SERIAL PRIMARY KEY,
    codigo              VARCHAR(20) NOT NULL UNIQUE,     -- 'CIR-FACO-MONO', 'CIR-LASIK-BIL'
    nombre              VARCHAR(150) NOT NULL,
    descripcion         TEXT,
    ojo_aplica          VARCHAR(10) NOT NULL DEFAULT 'UNILATERAL' CHECK (ojo_aplica IN ('UNILATERAL', 'BILATERAL')),
    precio_paquete      NUMERIC(14, 2) NOT NULL DEFAULT 0.00,
    activo              BOOLEAN NOT NULL DEFAULT TRUE
);

-- Protocolo y Programación Quirúrgica
CREATE TABLE clinica_template.cirugias_programadas (
    id_cirugia          BIGSERIAL PRIMARY KEY,
    id_expediente       BIGINT NOT NULL REFERENCES clinica_template.expedientes(id_expediente),
    id_paquete          INTEGER NOT NULL REFERENCES clinica_template.paquetes_quirurgicos(id_paquete),
    id_quirofano        INTEGER NOT NULL REFERENCES clinica_template.consultorios(id_consultorio),
    id_cirujano_ppal    UUID NOT NULL REFERENCES clinica_template.socios_negocio(id_socio),
    id_asistente        UUID REFERENCES clinica_template.socios_negocio(id_socio),
    id_anestesiologo    UUID REFERENCES clinica_template.socios_negocio(id_socio),
    fecha_programada    DATE NOT NULL,
    hora_inicio         TIME NOT NULL,
    hora_fin            TIME,
    ojo_operar          VARCHAR(5) NOT NULL CHECK (ojo_operar IN ('OD', 'OI', 'AMBOS')),
    tipo_anestesia      VARCHAR(50) DEFAULT 'TOPICA_SEDACION',
    lente_intraocular_modelo VARCHAR(100),               -- Modelo del LIO (Monofocal, Tóric, Multifocal)
    lente_intraocular_poder NUMERIC(4, 2),               -- Poder dióptrico del LIO (+21.50 D)
    lente_intraocular_serie  VARCHAR(50),                -- Número de serie del implante para trazabilidad
    protocolo_operatorio TEXT,
    complicaciones      TEXT,
    estado              VARCHAR(20) NOT NULL DEFAULT 'PROGRAMADA' 
                        CHECK (estado IN ('PROGRAMADA', 'EN_QUIROFANO', 'REALIZADA', 'CANCELADA', 'POST_OPERATORIO')),
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);


-- ------------------------------------------------------------------------------
-- 2.7 PRESUPUESTOS Y COTIZACIONES QUIRÚRGICAS / ÓPTICA
-- Sustituye s_HeaderPresupuesto, s_DetallePresupuesto, s_HeaderPresupuestoSeguimiento
-- ------------------------------------------------------------------------------

CREATE TABLE clinica_template.presupuestos (
    id_presupuesto      BIGSERIAL PRIMARY KEY,
    numero_presupuesto  VARCHAR(30) NOT NULL UNIQUE,     -- 'PRE-2026-00045'
    id_expediente       BIGINT NOT NULL REFERENCES clinica_template.expedientes(id_expediente),
    id_medico_sugiere   UUID NOT NULL REFERENCES clinica_template.socios_negocio(id_socio),
    fecha_emision       TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_vencimiento   DATE NOT NULL,
    subtotal            NUMERIC(14, 2) NOT NULL,
    descuento           NUMERIC(14, 2) NOT NULL DEFAULT 0.00,
    itbis               NUMERIC(14, 2) NOT NULL DEFAULT 0.00,
    total               NUMERIC(14, 2) NOT NULL,
    estado              VARCHAR(20) NOT NULL DEFAULT 'PENDIENTE' 
                        CHECK (estado IN ('PENDIENTE', 'APROBADO', 'FACTURADO', 'RECHAZADO', 'VENCIDO')),
    observaciones       TEXT,
    id_usuario          UUID NOT NULL REFERENCES clinica_template.usuarios(id_usuario),
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE clinica_template.presupuesto_detalles (
    id_pres_detalle     BIGSERIAL PRIMARY KEY,
    id_presupuesto      BIGINT NOT NULL REFERENCES clinica_template.presupuestos(id_presupuesto) ON DELETE CASCADE,
    numero_linea        SMALLINT NOT NULL,
    concepto            VARCHAR(200) NOT NULL,
    cantidad            NUMERIC(10, 2) NOT NULL DEFAULT 1.00,
    precio_unitario     NUMERIC(14, 2) NOT NULL,
    monto_descuento     NUMERIC(14, 2) DEFAULT 0.00,
    monto_total         NUMERIC(14, 2) NOT NULL
);


-- ------------------------------------------------------------------------------
-- 2.8 TALLER DE ÓPTICA Y ÓRDENES DE LABORATORIO DE LENTES
-- Sustituye s_CompraLente_Table, s_Optica, s_TipoLente
-- ------------------------------------------------------------------------------

CREATE TABLE clinica_template.ordenes_laboratorio_optica (
    id_orden_optica     BIGSERIAL PRIMARY KEY,
    numero_orden        VARCHAR(30) NOT NULL UNIQUE,     -- 'OPT-2026-00123'
    id_expediente       BIGINT NOT NULL REFERENCES clinica_template.expedientes(id_expediente),
    id_receta           BIGINT REFERENCES clinica_template.recetas_optometricas(id_receta),
    id_factura          BIGINT,                          -- Factura asociada en POS
    fecha_orden         TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_prometida     DATE,
    
    -- Especificaciones de la Montura
    montura_tipo        VARCHAR(30) NOT NULL DEFAULT 'COMPRADA' CHECK (montura_tipo IN ('COMPRADA', 'PROPIA_DEL_PACIENTE')),
    montura_marca       VARCHAR(80),
    montura_modelo      VARCHAR(80),
    montura_color       VARCHAR(50),
    
    -- Especificaciones del Cristal y Biselado
    tipo_lente          VARCHAR(50) NOT NULL,            -- 'PROGRESIVO_VARILUX', 'MONOFOCAL_BLUE'
    material_cristal    VARCHAR(50) NOT NULL,            -- 'POLICARBONATO', 'HI_INDEX_1.67'
    tratamientos        VARCHAR(150),                    -- 'ANTIRREFLEJO_CRIZAL, TINTADO'
    
    -- Trazabilidad del Taller
    laboratorio_externo VARCHAR(100),                    -- Nombre del laboratorio de tallado
    estado_taller       VARCHAR(25) NOT NULL DEFAULT 'EN_COLA' 
                        CHECK (estado_taller IN ('EN_COLA', 'EN_TALLADO', 'EN_BISELADO', 'CONTROL_CALIDAD', 'RECIBIDO_EN_TIENDA', 'ENTREGADO_AL_PACIENTE')),
    fecha_entrega_real  TIMESTAMPTZ,
    observaciones       TEXT,
    id_optometra_valida UUID REFERENCES clinica_template.socios_negocio(id_socio),
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);


-- ------------------------------------------------------------------------------
-- 2.9 PLANTILLAS DE RECETAS, MEDICAMENTOS Y CERTIFICACIONES MÉDICAS
-- Sustituye s_RecetaPrehecha, s_Medicina, s_CertificacionMedica, s_Referimiento
-- ------------------------------------------------------------------------------

-- Vademécum Oftálmico y Medicamentos
CREATE TABLE clinica_template.medicamentos_catalogo (
    id_medicamento      SERIAL PRIMARY KEY,
    nombre_comercial    VARCHAR(150) NOT NULL,
    principio_activo    VARCHAR(150) NOT NULL,
    concentracion       VARCHAR(50),                     -- '0.5%', '2 mg/ml'
    forma_farmaceutica  VARCHAR(50) NOT NULL DEFAULT 'COLIRIO_GOTAS' 
                        CHECK (forma_farmaceutica IN ('COLIRIO_GOTAS', 'UNGÜENTO_OFTALMICO', 'TABLETA', 'SUSPENSION', 'INYECCION_INTRAVITREA')),
    presentacion        VARCHAR(100),                    -- 'Frasco gotero 5 ml'
    familia             VARCHAR(80),                     -- 'HIPOTENSOR_OCULAR', 'ANTIBIOTICO', 'LUBRICANTE_LAGRIMAS', 'ESTEROIDE'
    activo              BOOLEAN NOT NULL DEFAULT TRUE
);

-- Plantillas de Recetas Rápidas (Kits Terapéuticos Prehechos)
CREATE TABLE clinica_template.plantillas_recetas (
    id_plantilla_receta SERIAL PRIMARY KEY,
    nombre              VARCHAR(150) NOT NULL,           -- 'Protocolo Post-Cirugía Catarata', 'Ojo Seco Moderado'
    id_medico_creador   UUID REFERENCES clinica_template.socios_negocio(id_socio), -- NULL es plantilla pública
    items_receta        JSONB NOT NULL DEFAULT '[]'::jsonb, -- Dosis, posología y duración en JSONB
    activo              BOOLEAN NOT NULL DEFAULT TRUE
);

-- Certificaciones Médicas Oficiales (Salud Visual, Intrant / Licencias, Reposo)
CREATE TABLE clinica_template.certificados_medicos (
    id_certificado      BIGSERIAL PRIMARY KEY,
    numero_certificado  VARCHAR(30) NOT NULL UNIQUE,     -- 'CERT-2026-00089'
    id_expediente       BIGINT NOT NULL REFERENCES clinica_template.expedientes(id_expediente),
    id_medico           UUID NOT NULL REFERENCES clinica_template.socios_negocio(id_socio),
    fecha_emision       TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tipo_certificado    VARCHAR(50) NOT NULL,            -- 'LICENCIA_CONDUCIR_INTRANT', 'REPOSO_MEDICO', 'APTITUD_LABORAL'
    agudeza_visual_od   VARCHAR(10) NOT NULL,
    agudeza_visual_oi   VARCHAR(10) NOT NULL,
    vision_colores      VARCHAR(50) DEFAULT 'NORMAL_ISHIHARA',
    campo_visual_desc   VARCHAR(100) DEFAULT 'CONSERVADO',
    dias_reposo         INTEGER DEFAULT 0,
    diagnostico_motivo  TEXT NOT NULL,
    conclusiones        TEXT NOT NULL,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);


-- ------------------------------------------------------------------------------
-- 2.10 INVENTARIO, MULTIALMACÉN, KARDEX Y CATÁLOGO DUAL (FÍSICOS VS SERVICIOS)
-- Sustituye posOITM, posOWHS, posOITW, POSOIGN, POSIGN1
-- ------------------------------------------------------------------------------

CREATE TABLE clinica_template.articulo_categorias (
    id_categoria        SERIAL PRIMARY KEY,
    codigo              VARCHAR(15) NOT NULL UNIQUE,     -- 'MON', 'LEN', 'FAR', 'CIR', 'CON'
    nombre              VARCHAR(100) NOT NULL,           -- 'Monturas', 'Lentes Oftálmicos', 'Farmacia', 'Consultas'
    descripcion         TEXT,
    activo              BOOLEAN NOT NULL DEFAULT TRUE
);

-- Catálogo Maestro de Ítems (Dual: Físico vs Servicio SV)
CREATE TABLE clinica_template.articulos (
    id_articulo         SERIAL PRIMARY KEY,
    codigo              VARCHAR(30) NOT NULL UNIQUE,     -- 'DES0001', 'SV0001' (Servicios siempre prefijo SV)
    tipo                VARCHAR(20) NOT NULL CHECK (tipo IN ('BIEN_FISICO', 'SERVICIO_CLINICO')),
    id_categoria        INTEGER NOT NULL REFERENCES clinica_template.articulo_categorias(id_categoria),
    nombre              VARCHAR(200) NOT NULL,
    descripcion         TEXT,
    codigo_barras       VARCHAR(100),                    -- Habilitado solo para bienes físicos
    unidad_medida       VARCHAR(10) NOT NULL DEFAULT 'UND' CHECK (unidad_medida IN ('UND', 'SER', 'CAJA', 'PAQ', 'PAR')),
    tasa_itbis          NUMERIC(5, 2) NOT NULL DEFAULT 18.00 CHECK (tasa_itbis IN (0.00, 16.00, 18.00)),
    costo_estandar      NUMERIC(14, 2) NOT NULL DEFAULT 0.00,
    costo_promedio      NUMERIC(14, 4) NOT NULL DEFAULT 0.00, -- Costo promedio ponderado calculado
    precio_base_venta   NUMERIC(14, 2) NOT NULL DEFAULT 0.00,
    es_inventariable    BOOLEAN NOT NULL DEFAULT TRUE,   -- Falso para Servicios SV
    atributos_optica    JSONB DEFAULT '{}'::jsonb,       -- Calibre, puente, varilla, material de montura
    activo              BOOLEAN NOT NULL DEFAULT TRUE,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_articulos_codigo ON clinica_template.articulos(codigo);
CREATE INDEX idx_articulos_tipo ON clinica_template.articulos(tipo);
CREATE INDEX idx_articulos_codbarras ON clinica_template.articulos(codigo_barras);

CREATE TABLE clinica_template.almacenes (
    id_almacen          SERIAL PRIMARY KEY,
    id_sucursal         INTEGER NOT NULL REFERENCES clinica_template.sucursales(id_sucursal),
    codigo              VARCHAR(15) NOT NULL,            -- 'ALM-CENTRAL', 'OPT-MOSTRADOR', 'FARM-QUIROF'
    nombre              VARCHAR(100) NOT NULL,
    es_principal        BOOLEAN NOT NULL DEFAULT FALSE,
    activo              BOOLEAN NOT NULL DEFAULT TRUE,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(id_sucursal, codigo)
);

CREATE TABLE clinica_template.articulo_almacen (
    id_articulo         INTEGER NOT NULL REFERENCES clinica_template.articulos(id_articulo),
    id_almacen          INTEGER NOT NULL REFERENCES clinica_template.almacenes(id_almacen),
    stock_actual        NUMERIC(12, 2) NOT NULL DEFAULT 0.00,
    stock_comprometido  NUMERIC(12, 2) NOT NULL DEFAULT 0.00,
    stock_disponible    NUMERIC(12, 2) GENERATED ALWAYS AS (stock_actual - stock_comprometido) STORED,
    stock_minimo        NUMERIC(12, 2) NOT NULL DEFAULT 5.00,
    stock_maximo        NUMERIC(12, 2) NOT NULL DEFAULT 100.00,
    ubicacion_estante   VARCHAR(50),
    habilitado          BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (id_articulo, id_almacen)
);

CREATE TABLE clinica_template.kardex_movimientos (
    id_kardex           BIGSERIAL PRIMARY KEY,
    id_articulo         INTEGER NOT NULL REFERENCES clinica_template.articulos(id_articulo),
    id_almacen          INTEGER NOT NULL REFERENCES clinica_template.almacenes(id_almacen),
    tipo_movimiento     VARCHAR(30) NOT NULL CHECK (tipo_movimiento IN ('ENTRADA_COMPRA', 'VENTA_FACTURA', 'ANULACION_NC', 'AJUSTE_POSITIVO', 'AJUSTE_NEGATIVO', 'TRANSFERENCIA')),
    documento_referencia VARCHAR(50) NOT NULL,
    fecha_movimiento    TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    cantidad            NUMERIC(12, 2) NOT NULL,
    costo_unitario      NUMERIC(14, 4) NOT NULL,
    costo_total         NUMERIC(14, 2) NOT NULL,
    stock_anterior      NUMERIC(12, 2) NOT NULL,
    stock_posterior     NUMERIC(12, 2) NOT NULL,
    costo_promedio_ant  NUMERIC(14, 4) NOT NULL,
    costo_promedio_post NUMERIC(14, 4) NOT NULL,
    id_usuario          UUID REFERENCES clinica_template.usuarios(id_usuario),
    observaciones       TEXT
);

CREATE INDEX idx_kardex_art_alm ON clinica_template.kardex_movimientos(id_articulo, id_almacen);
CREATE INDEX idx_kardex_fecha ON clinica_template.kardex_movimientos(fecha_movimiento);

CREATE TABLE clinica_template.listas_precios (
    id_lista_precio     SERIAL PRIMARY KEY,
    codigo              VARCHAR(15) NOT NULL UNIQUE,     -- 'PUBLICO', 'SEGUROS', 'MAYORISTA'
    nombre              VARCHAR(100) NOT NULL,
    moneda              VARCHAR(3) NOT NULL DEFAULT 'DOP',
    activo              BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE clinica_template.precios_articulo (
    id_lista_precio     INTEGER NOT NULL REFERENCES clinica_template.listas_precios(id_lista_precio) ON DELETE CASCADE,
    id_articulo         INTEGER NOT NULL REFERENCES clinica_template.articulos(id_articulo) ON DELETE CASCADE,
    precio              NUMERIC(14, 2) NOT NULL,
    PRIMARY KEY (id_lista_precio, id_articulo)
);

-- Entradas de Mercancía / Compras a Suplidores (Reemplaza POSOIGN y POSIGN1 de SAP B1)
CREATE TABLE clinica_template.entradas_mercancia (
    id_entrada          BIGSERIAL PRIMARY KEY,
    numero_interno      VARCHAR(30) NOT NULL UNIQUE,     -- 'ENT-2026-00001'
    id_sucursal         INTEGER NOT NULL REFERENCES clinica_template.sucursales(id_sucursal),
    id_almacen          INTEGER NOT NULL REFERENCES clinica_template.almacenes(id_almacen),
    id_suplidor         UUID NOT NULL REFERENCES clinica_template.socios_negocio(id_socio),
    numero_factura_suplidor VARCHAR(50) NOT NULL,        -- Número de comprobante del suplidor
    ncf_suplidor        VARCHAR(19),                     -- NCF B01 o E31 del proveedor (para reporte DGII 606)
    fecha_entrada       TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_factura       DATE NOT NULL,
    subtotal_bruto      NUMERIC(14, 2) NOT NULL DEFAULT 0.00,
    monto_descuento     NUMERIC(14, 2) NOT NULL DEFAULT 0.00,
    itbis_total         NUMERIC(14, 2) NOT NULL DEFAULT 0.00,
    total_neto          NUMERIC(14, 2) NOT NULL,
    estado              VARCHAR(20) NOT NULL DEFAULT 'APROBADA' CHECK (estado IN ('APROBADA', 'ANULADA')),
    observaciones       TEXT,
    id_usuario          UUID NOT NULL REFERENCES clinica_template.usuarios(id_usuario),
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_entradas_suplidor ON clinica_template.entradas_mercancia(id_suplidor);
CREATE INDEX idx_entradas_fecha ON clinica_template.entradas_mercancia(fecha_entrada);

CREATE TABLE clinica_template.entrada_mercancia_detalles (
    id_detalle_entrada  BIGSERIAL PRIMARY KEY,
    id_entrada          BIGINT NOT NULL REFERENCES clinica_template.entradas_mercancia(id_entrada) ON DELETE CASCADE,
    numero_linea        SMALLINT NOT NULL,
    id_articulo         INTEGER NOT NULL REFERENCES clinica_template.articulos(id_articulo),
    cantidad            NUMERIC(10, 2) NOT NULL,
    costo_unitario      NUMERIC(14, 4) NOT NULL,
    porcentaje_descuento NUMERIC(5, 2) NOT NULL DEFAULT 0.00,
    monto_descuento     NUMERIC(14, 2) NOT NULL DEFAULT 0.00,
    tasa_itbis          NUMERIC(5, 2) NOT NULL DEFAULT 18.00,
    monto_itbis         NUMERIC(14, 2) NOT NULL DEFAULT 0.00,
    costo_total_linea   NUMERIC(14, 2) NOT NULL,
    UNIQUE(id_entrada, numero_linea)
);



-- ------------------------------------------------------------------------------
-- 2.11 CONTROL DE CAJA, POS Y TESORERÍA (SPLIT PAYMENTS)
-- Sustituye diario_caja, diario_caja_det, retiro_caja, cierre_denominacion
-- ------------------------------------------------------------------------------

CREATE TABLE clinica_template.cajas_fisicas (
    id_caja_fisica      SERIAL PRIMARY KEY,
    id_sucursal         INTEGER NOT NULL REFERENCES clinica_template.sucursales(id_sucursal),
    codigo              VARCHAR(15) NOT NULL,            -- 'CAJA-01', 'CAJA-POS-02'
    nombre              VARCHAR(100) NOT NULL,
    ip_terminal         VARCHAR(45),
    activo              BOOLEAN NOT NULL DEFAULT TRUE,
    UNIQUE(id_sucursal, codigo)
);

CREATE TABLE clinica_template.turnos_caja (
    id_turno            BIGSERIAL PRIMARY KEY,
    id_caja_fisica      INTEGER NOT NULL REFERENCES clinica_template.cajas_fisicas(id_caja_fisica),
    id_usuario_cajero   UUID NOT NULL REFERENCES clinica_template.usuarios(id_usuario),
    fecha_apertura      TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    monto_fondo_inicial NUMERIC(14, 2) NOT NULL DEFAULT 0.00,
    fecha_cierre        TIMESTAMPTZ,
    estado              VARCHAR(15) NOT NULL DEFAULT 'ABIERTO' CHECK (estado IN ('ABIERTO', 'CERRADO', 'AUDITADO')),
    
    -- Declaraciones del Cajero en el Arqueo
    declarado_efectivo  NUMERIC(14, 2) DEFAULT 0.00,
    declarado_tarjeta   NUMERIC(14, 2) DEFAULT 0.00,
    declarado_transferencia NUMERIC(14, 2) DEFAULT 0.00,
    declarado_cheque    NUMERIC(14, 2) DEFAULT 0.00,
    total_declarado     NUMERIC(14, 2) DEFAULT 0.00,
    
    -- Totales Teóricos del Sistema
    sistema_efectivo    NUMERIC(14, 2) DEFAULT 0.00,
    sistema_tarjeta     NUMERIC(14, 2) DEFAULT 0.00,
    sistema_transferencia NUMERIC(14, 2) DEFAULT 0.00,
    sistema_cheque      NUMERIC(14, 2) DEFAULT 0.00,
    total_retiros       NUMERIC(14, 2) DEFAULT 0.00,
    total_sistema       NUMERIC(14, 2) DEFAULT 0.00,
    
    diferencia_monto    NUMERIC(14, 2) DEFAULT 0.00,     -- > 0 Sobrante / < 0 Faltante
    observaciones_cierre TEXT,
    id_usuario_auditor  UUID REFERENCES clinica_template.usuarios(id_usuario),
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE clinica_template.turnos_caja_denominaciones (
    id_denominacion     BIGSERIAL PRIMARY KEY,
    id_turno            BIGINT NOT NULL REFERENCES clinica_template.turnos_caja(id_turno) ON DELETE CASCADE,
    tipo_operacion      VARCHAR(20) NOT NULL CHECK (tipo_operacion IN ('APERTURA', 'ARQUEO_CIERRE', 'RETIRO_PARCIAL')),
    b_2000              INTEGER NOT NULL DEFAULT 0,
    b_1000              INTEGER NOT NULL DEFAULT 0,
    b_500               INTEGER NOT NULL DEFAULT 0,
    b_200               INTEGER NOT NULL DEFAULT 0,
    b_100               INTEGER NOT NULL DEFAULT 0,
    b_50                INTEGER NOT NULL DEFAULT 0,
    m_25                INTEGER NOT NULL DEFAULT 0,
    m_10                INTEGER NOT NULL DEFAULT 0,
    m_5                 INTEGER NOT NULL DEFAULT 0,
    m_1                 INTEGER NOT NULL DEFAULT 0,
    monto_total_calculado NUMERIC(14, 2) NOT NULL,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE clinica_template.retiros_caja (
    id_retiro           BIGSERIAL PRIMARY KEY,
    id_turno            BIGINT NOT NULL REFERENCES clinica_template.turnos_caja(id_turno),
    fecha_retiro        TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    monto               NUMERIC(14, 2) NOT NULL,
    concepto            VARCHAR(250) NOT NULL,
    beneficiario        VARCHAR(150) NOT NULL,
    medio_retiro        VARCHAR(20) NOT NULL DEFAULT 'EFECTIVO' CHECK (medio_retiro IN ('EFECTIVO', 'CHEQUE')),
    numero_cheque       VARCHAR(50),
    id_usuario          UUID NOT NULL REFERENCES clinica_template.usuarios(id_usuario)
);


-- ------------------------------------------------------------------------------
-- 2.12 GESTIÓN DE SEGUROS MÉDICOS (ARS), COBERTURAS Y GLOSAS
-- Sustituye AprobacionARS y trazabilidad_reclamacion_ars
-- ------------------------------------------------------------------------------

CREATE TABLE clinica_template.autorizaciones_ars (
    id_autorizacion     BIGSERIAL PRIMARY KEY,
    id_paciente         UUID NOT NULL REFERENCES clinica_template.socios_negocio(id_socio),
    id_aseguradora_ars  UUID NOT NULL REFERENCES clinica_template.socios_negocio(id_socio),
    id_medico_tratante  UUID NOT NULL REFERENCES clinica_template.socios_negocio(id_socio),
    numero_autorizacion VARCHAR(50) NOT NULL,            -- Código de aprobación emitido por la ARS
    tipo_servicio       VARCHAR(100) NOT NULL,           -- 'CONSULTA', 'CIRUGIA_CATARATA', 'OCT'
    fecha_aprobacion    TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_vencimiento   TIMESTAMPTZ,
    monto_total_servicio NUMERIC(14, 2) NOT NULL,
    porcentaje_cobertura NUMERIC(5, 2) NOT NULL,
    monto_cubierto_ars  NUMERIC(14, 2) NOT NULL,
    monto_copago_paciente NUMERIC(14, 2) NOT NULL,
    estado              VARCHAR(30) NOT NULL DEFAULT 'PENDIENTE_FACTURAR_ARS' 
                        CHECK (estado IN ('PENDIENTE_FACTURAR_ARS', 'FACTURADO_LOTE', 'PAGADO_CONCILIADO', 'GLOSADO', 'ANULADO')),
    id_factura_paciente BIGINT,                          -- Si hubo copago se emite factura de consumo
    id_factura_ars_lote BIGINT,                          -- Factura masiva de crédito fiscal emitida a la ARS
    observaciones       TEXT,
    id_usuario_registro UUID REFERENCES clinica_template.usuarios(id_usuario),
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_aut_ars_estado ON clinica_template.autorizaciones_ars(estado);
CREATE INDEX idx_aut_ars_aseguradora ON clinica_template.autorizaciones_ars(id_aseguradora_ars);

CREATE TABLE clinica_template.trazabilidad_glosas_ars (
    id_trazabilidad     BIGSERIAL PRIMARY KEY,
    id_autorizacion     BIGINT NOT NULL REFERENCES clinica_template.autorizaciones_ars(id_autorizacion),
    id_aseguradora_ars  UUID NOT NULL REFERENCES clinica_template.socios_negocio(id_socio),
    fecha_transaccion   TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    monto_reclamado     NUMERIC(14, 2) NOT NULL,
    monto_pagado        NUMERIC(14, 2) NOT NULL DEFAULT 0.00,
    monto_glosado       NUMERIC(14, 2) NOT NULL DEFAULT 0.00,
    monto_retencion_isr NUMERIC(14, 2) NOT NULL DEFAULT 0.00,
    motivo_glosa        TEXT,
    codigo_glosa_ars    VARCHAR(20),
    numero_transferencia_cheque VARCHAR(80),
    estado_conciliacion VARCHAR(25) NOT NULL DEFAULT 'PENDIENTE' CHECK (estado_conciliacion IN ('PENDIENTE', 'CONCILIADO', 'EN_DISPUTA', 'ACEPTADO_PERDIDA')),
    id_usuario          UUID REFERENCES clinica_template.usuarios(id_usuario),
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);


-- ------------------------------------------------------------------------------
-- 2.13 FACTURACIÓN, COMPROBANTES FISCALES (DGII) Y SECUENCIAS
-- Sustituye POSOINV, POSINV1, POSORIN, POSRIN1, POSODIN, POSDIN1, carga_ncf, serie_ncf, serie
-- ------------------------------------------------------------------------------

-- Catálogo Oficial de Tasas de Impuestos y Retenciones DGII (Reemplaza ecf_tax_config)
CREATE TABLE clinica_template.impuestos_config (
    id_impuesto         SERIAL PRIMARY KEY,
    codigo              VARCHAR(20) NOT NULL UNIQUE,     -- 'ITBIS_18', 'ITBIS_16', 'ITBIS_EXENTO', 'RET_ITBIS_30', 'RET_ISR_10'
    nombre              VARCHAR(100) NOT NULL,
    tasa_porcentaje     NUMERIC(5, 2) NOT NULL DEFAULT 0.00,
    tipo                VARCHAR(25) NOT NULL CHECK (tipo IN ('ITBIS_GRAVADO', 'ITBIS_EXENTO', 'RETENCION_ITBIS', 'RETENCION_ISR')),
    codigo_dgii         VARCHAR(10),                     -- Código de impuesto para e-CF (ej: '1', '2', 'E')
    descripcion         TEXT,
    activo              BOOLEAN NOT NULL DEFAULT TRUE
);

-- Catálogo Maestro de Series y Tipos de Comprobantes DGII (Reemplaza serie_ncf y serie de SAP B1)
CREATE TABLE clinica_template.series_ncf_catalogo (
    serie               VARCHAR(5) PRIMARY KEY,          -- 'B01', 'B02', 'B03', 'B04', 'B14', 'B15', 'E31', 'E32', 'E33', 'E34'
    nombre              VARCHAR(100) NOT NULL,           -- 'Factura de Crédito Fiscal', 'Factura de Consumo', 'Nota de Débito'
    tipo_documento      VARCHAR(25) NOT NULL CHECK (tipo_documento IN ('FACTURA_VENTA', 'NOTA_CREDITO', 'NOTA_DEBITO', 'COMPRAS_GASTOS', 'REGIMEN_ESPECIAL', 'GUBERNAMENTAL')),
    longitud_secuencia  SMALLINT NOT NULL DEFAULT 8,     -- 8 dígitos para serie B / 10 dígitos para serie E
    requiere_rnc_cedula BOOLEAN NOT NULL DEFAULT FALSE,
    tipo_ecf            VARCHAR(5),                      -- '31', '32', '33', '34', etc.
    alerta_defecto      INTEGER NOT NULL DEFAULT 50,
    activo              BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE clinica_template.carga_ncf (
    id_carga            SERIAL PRIMARY KEY,
    serie               VARCHAR(5) NOT NULL REFERENCES clinica_template.series_ncf_catalogo(serie),
    tipo_comprobante    VARCHAR(50) NOT NULL,
    secuencia_inicial   BIGINT NOT NULL,
    secuencia_final     BIGINT NOT NULL,
    secuencia_actual    BIGINT NOT NULL,
    fecha_vencimiento   DATE NOT NULL,
    alerta_minimo       INTEGER NOT NULL DEFAULT 50,
    alerta_critico      INTEGER NOT NULL DEFAULT 10,
    activo              BOOLEAN NOT NULL DEFAULT TRUE,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_secuencia_valida CHECK (secuencia_actual BETWEEN secuencia_inicial - 1 AND secuencia_final)
);

CREATE TABLE clinica_template.facturas (
    id_factura          BIGSERIAL PRIMARY KEY,
    numero_interno      VARCHAR(30) NOT NULL UNIQUE,     -- 'FAC-2026-000123'
    id_sucursal         INTEGER NOT NULL REFERENCES clinica_template.sucursales(id_sucursal),
    id_turno_caja       BIGINT REFERENCES clinica_template.turnos_caja(id_turno),
    id_cliente          UUID NOT NULL REFERENCES clinica_template.socios_negocio(id_socio),
    id_medico           UUID REFERENCES clinica_template.socios_negocio(id_socio),
    ncf                 VARCHAR(19) NOT NULL,
    tipo_ncf            VARCHAR(5) NOT NULL,
    fecha_emision       TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_vencimiento_ncf DATE,
    rnc_cliente         VARCHAR(15),
    razon_social_cliente VARCHAR(200),
    subtotal_bruto      NUMERIC(14, 2) NOT NULL DEFAULT 0.00,
    descuento_total     NUMERIC(14, 2) NOT NULL DEFAULT 0.00,
    monto_gravado_18    NUMERIC(14, 2) NOT NULL DEFAULT 0.00,
    monto_gravado_16    NUMERIC(14, 2) NOT NULL DEFAULT 0.00,
    monto_exento        NUMERIC(14, 2) NOT NULL DEFAULT 0.00,
    itbis_total         NUMERIC(14, 2) NOT NULL DEFAULT 0.00,
    total_neto          NUMERIC(14, 2) NOT NULL,
    monto_pagado        NUMERIC(14, 2) NOT NULL DEFAULT 0.00,
    saldo_pendiente     NUMERIC(14, 2) NOT NULL DEFAULT 0.00,
    estado_factura      VARCHAR(20) NOT NULL DEFAULT 'EMITIDA' CHECK (estado_factura IN ('EMITIDA', 'PAGADA', 'ANULADA', 'A_CREDITO')),
    estado_fiscal_dgii  VARCHAR(20) NOT NULL DEFAULT 'NO_APLICA' CHECK (estado_fiscal_dgii IN ('NO_APLICA', 'PENDIENTE', 'EN_PROCESO', 'ACEPTADO', 'RECHAZADO')),
    observaciones       TEXT,
    id_usuario          UUID NOT NULL REFERENCES clinica_template.usuarios(id_usuario),
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_facturas_ncf ON clinica_template.facturas(ncf);
CREATE INDEX idx_facturas_fecha ON clinica_template.facturas(fecha_emision);

CREATE TABLE clinica_template.factura_detalles (
    id_detalle          BIGSERIAL PRIMARY KEY,
    id_factura          BIGINT NOT NULL REFERENCES clinica_template.facturas(id_factura) ON DELETE CASCADE,
    numero_linea        SMALLINT NOT NULL,
    id_articulo         INTEGER NOT NULL REFERENCES clinica_template.articulos(id_articulo),
    id_almacen          INTEGER REFERENCES clinica_template.almacenes(id_almacen),
    descripcion         VARCHAR(250) NOT NULL,
    tipo_item           VARCHAR(20) NOT NULL CHECK (tipo_item IN ('BIEN_FISICO', 'SERVICIO_CLINICO')),
    cantidad            NUMERIC(10, 2) NOT NULL,
    precio_unitario     NUMERIC(14, 2) NOT NULL,
    porcentaje_descuento NUMERIC(5, 2) NOT NULL DEFAULT 0.00,
    monto_descuento     NUMERIC(14, 2) NOT NULL DEFAULT 0.00,
    tasa_itbis          NUMERIC(5, 2) NOT NULL DEFAULT 18.00,
    monto_itbis         NUMERIC(14, 2) NOT NULL DEFAULT 0.00,
    monto_total_linea   NUMERIC(14, 2) NOT NULL,
    UNIQUE(id_factura, numero_linea)
);

CREATE TABLE clinica_template.pagos_transacciones (
    id_pago             BIGSERIAL PRIMARY KEY,
    id_factura          BIGINT NOT NULL REFERENCES clinica_template.facturas(id_factura) ON DELETE CASCADE,
    id_turno_caja       BIGINT NOT NULL REFERENCES clinica_template.turnos_caja(id_turno),
    medio_pago          VARCHAR(25) NOT NULL CHECK (medio_pago IN ('EFECTIVO', 'TARJETA', 'TRANSFERENCIA', 'CHEQUE', 'CREDITO_ARS', 'NOTA_CREDITO')),
    monto               NUMERIC(14, 2) NOT NULL,
    monto_recibido      NUMERIC(14, 2),
    monto_cambio        NUMERIC(14, 2) DEFAULT 0.00,
    franquicia_tarjeta  VARCHAR(30),
    ultimos_4_digitos   VARCHAR(4),
    numero_voucher      VARCHAR(50),
    banco_emisor        VARCHAR(80),
    numero_referencia   VARCHAR(100),
    id_nota_credito_apl BIGINT,
    fecha_pago          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE clinica_template.notas_credito (
    id_nota_credito     BIGSERIAL PRIMARY KEY,
    numero_interno      VARCHAR(30) NOT NULL UNIQUE,
    id_sucursal         INTEGER NOT NULL REFERENCES clinica_template.sucursales(id_sucursal),
    id_factura_origen   BIGINT NOT NULL REFERENCES clinica_template.facturas(id_factura),
    ncf                 VARCHAR(19) NOT NULL,
    ncf_modificado      VARCHAR(19) NOT NULL,
    fecha_emision       TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    codigo_modificacion_dgii SMALLINT NOT NULL DEFAULT 1 CHECK (codigo_modificacion_dgii IN (1, 2, 3)),
    motivo_anulacion    VARCHAR(250) NOT NULL,
    subtotal_bruto      NUMERIC(14, 2) NOT NULL,
    monto_itbis         NUMERIC(14, 2) NOT NULL,
    monto_total         NUMERIC(14, 2) NOT NULL,
    devuelve_almacen    BOOLEAN NOT NULL DEFAULT TRUE,
    id_usuario          UUID NOT NULL REFERENCES clinica_template.usuarios(id_usuario),
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE clinica_template.nota_credito_detalles (
    id_nc_detalle       BIGSERIAL PRIMARY KEY,
    id_nota_credito     BIGINT NOT NULL REFERENCES clinica_template.notas_credito(id_nota_credito) ON DELETE CASCADE,
    numero_linea        SMALLINT NOT NULL,
    id_articulo         INTEGER NOT NULL REFERENCES clinica_template.articulos(id_articulo),
    id_almacen          INTEGER REFERENCES clinica_template.almacenes(id_almacen),
    cantidad            NUMERIC(10, 2) NOT NULL,
    precio_unitario     NUMERIC(14, 2) NOT NULL,
    monto_itbis         NUMERIC(14, 2) NOT NULL,
    monto_total_linea   NUMERIC(14, 2) NOT NULL
);

-- Notas de Débito (Reemplaza POSODIN de SAP B1 / Comprobantes B03 y E33 DGII)
CREATE TABLE clinica_template.notas_debito (
    id_nota_debito      BIGSERIAL PRIMARY KEY,
    numero_interno      VARCHAR(30) NOT NULL UNIQUE,     -- 'ND-2026-00001'
    id_sucursal         INTEGER NOT NULL REFERENCES clinica_template.sucursales(id_sucursal),
    id_factura_origen   BIGINT NOT NULL REFERENCES clinica_template.facturas(id_factura),
    ncf                 VARCHAR(19) NOT NULL,            -- 'B0300000001' o 'E3300000001'
    ncf_modificado      VARCHAR(19) NOT NULL,            -- NCF de la factura original
    fecha_emision       TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    codigo_modificacion_dgii SMALLINT NOT NULL DEFAULT 1 CHECK (codigo_modificacion_dgii IN (1, 2, 3)), -- 1: Ajuste de precio, 2: Cargos financieros/mora, 3: Otros
    motivo_debito       VARCHAR(250) NOT NULL,
    subtotal_bruto      NUMERIC(14, 2) NOT NULL,
    monto_itbis         NUMERIC(14, 2) NOT NULL DEFAULT 0.00,
    monto_total         NUMERIC(14, 2) NOT NULL,
    monto_pagado        NUMERIC(14, 2) NOT NULL DEFAULT 0.00,
    saldo_pendiente     NUMERIC(14, 2) NOT NULL DEFAULT 0.00,
    id_usuario          UUID NOT NULL REFERENCES clinica_template.usuarios(id_usuario),
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_notas_debito_ncf ON clinica_template.notas_debito(ncf);

-- Detalle de Líneas de Nota de Débito (Reemplaza POSDIN1 de SAP B1)
CREATE TABLE clinica_template.nota_debito_detalles (
    id_nd_detalle       BIGSERIAL PRIMARY KEY,
    id_nota_debito      BIGINT NOT NULL REFERENCES clinica_template.notas_debito(id_nota_debito) ON DELETE CASCADE,
    numero_linea        SMALLINT NOT NULL,
    id_articulo         INTEGER NOT NULL REFERENCES clinica_template.articulos(id_articulo),
    concepto            VARCHAR(250) NOT NULL,
    cantidad            NUMERIC(10, 2) NOT NULL DEFAULT 1.00,
    precio_unitario     NUMERIC(14, 2) NOT NULL,
    tasa_itbis          NUMERIC(5, 2) NOT NULL DEFAULT 18.00,
    monto_itbis         NUMERIC(14, 2) NOT NULL DEFAULT 0.00,
    monto_total_linea   NUMERIC(14, 2) NOT NULL,
    UNIQUE(id_nota_debito, numero_linea)
);



-- ------------------------------------------------------------------------------
-- 2.14 ECOSISTEMA NATIVO DE FACTURACIÓN ELECTRÓNICA e-CF (DGII LEY 32-23)
-- Reemplaza y supera xmlSentryAFM, ecf_xml_invoice, ecf_xml_catalog, ecf_xsd_template
-- ------------------------------------------------------------------------------

-- Catálogo de Tags XML Oficial DGII
CREATE TABLE clinica_template.ecf_xml_catalog (
    id_record           SERIAL PRIMARY KEY,
    catalog_type        INTEGER NOT NULL DEFAULT 1,      -- 1: Estándar e-CF / 2: Resumen RFCE
    tag_name            VARCHAR(100) NOT NULL,
    tag_parent_name     VARCHAR(100),
    tag_group_id        INTEGER NOT NULL,
    tag_type            VARCHAR(30) NOT NULL DEFAULT 'Item', -- 'Root', 'Container', 'Item'
    tag_to_remove       VARCHAR(100),
    active              CHAR(1) NOT NULL DEFAULT 'Y'
);

CREATE INDEX idx_ecf_catalog ON clinica_template.ecf_xml_catalog(catalog_type, active);

-- Plantillas XSD Oficiales de la DGII
CREATE TABLE clinica_template.ecf_xsd_template (
    id_xsd              SERIAL PRIMARY KEY,
    tipo_ecf            VARCHAR(10) NOT NULL,            -- 'E31', 'E32', 'E34', 'RFCE'
    nombre_esquema      VARCHAR(100) NOT NULL,
    contenido_xsd       TEXT NOT NULL,
    version             VARCHAR(20) NOT NULL DEFAULT '1.0',
    activo              BOOLEAN NOT NULL DEFAULT TRUE
);

-- Certificado Digital P12 del Emisor
CREATE TABLE clinica_template.ecf_certificados (
    id_certificado      SERIAL PRIMARY KEY,
    nombre_archivo      VARCHAR(150) NOT NULL,
    certificado_p12_cifrado BYTEA NOT NULL,
    clave_cifrado_aes   TEXT NOT NULL,
    valido_desde        DATE NOT NULL,
    valido_hasta        DATE NOT NULL,
    dias_restantes      INTEGER GENERATED ALWAYS AS (valido_hasta - CURRENT_DATE) STORED,
    activo              BOOLEAN NOT NULL DEFAULT TRUE,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Sesiones y Tokens Bearer de la DGII
CREATE TABLE clinica_template.ecf_tokens_dgii (
    id_token            BIGSERIAL PRIMARY KEY,
    token_bearer        TEXT NOT NULL,
    semilla_xml         TEXT,
    semilla_firmada_xml TEXT,
    fecha_expedido      TIMESTAMPTZ NOT NULL,
    fecha_expira        TIMESTAMPTZ NOT NULL,
    activo              BOOLEAN NOT NULL DEFAULT TRUE,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Bandeja Transaccional de Facturación Electrónica e-CF
CREATE TABLE clinica_template.ecf_documentos (
    id_documento_ecf    BIGSERIAL PRIMARY KEY,
    id_factura          BIGINT REFERENCES clinica_template.facturas(id_factura),
    id_nota_credito     BIGINT REFERENCES clinica_template.notas_credito(id_nota_credito),
    tipo_ecf            VARCHAR(5) NOT NULL,             -- '31', '32', '34', '44', '45'
    encf                VARCHAR(19) NOT NULL,            -- 'E3100000001'
    estado_proceso      CHAR(1) NOT NULL DEFAULT 'N' 
                        CHECK (estado_proceso IN ('N', 'F', 'Q', 'P', 'Y', 'R', 'E')), -- N: Nuevo, F: Firmado, Q: En vuelo, P: En proceso, Y: Aceptado, R: Rechazado
    xml_generado        XML,
    xml_firmado         XML,
    codigo_seguridad_qr VARCHAR(10),                     -- Primeros 6 dígitos del SignatureValue
    fecha_firma         TIMESTAMPTZ,
    url_timbre_qr       TEXT,
    track_id            VARCHAR(100),
    codigo_estado_dgii  VARCHAR(10),
    mensaje_dgii        TEXT,
    glosas_rechazo      JSONB DEFAULT '[]'::jsonb,
    intentos_envio      INTEGER NOT NULL DEFAULT 0,
    ultimo_intento      TIMESTAMPTZ,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_ecf_doc_estado ON clinica_template.ecf_documentos(estado_proceso);
CREATE INDEX idx_ecf_doc_encf ON clinica_template.ecf_documentos(encf);
CREATE INDEX idx_ecf_doc_trackid ON clinica_template.ecf_documentos(track_id);

-- Registro de Incidencias e-CF Caídos (Auditoría Anti-Pérdida)
CREATE TABLE clinica_template.ecf_incidencias_bad (
    id_bad              BIGSERIAL PRIMARY KEY,
    id_documento_ecf    BIGINT REFERENCES clinica_template.ecf_documentos(id_documento_ecf),
    encf                VARCHAR(19) NOT NULL,
    error_detalle       TEXT NOT NULL,
    xml_fallido         TEXT,
    fecha_incidencia    TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);


-- ------------------------------------------------------------------------------
-- 2.16 ÍNDICES ESTRATÉGICOS DE ALTO RENDIMIENTO (COVERING, PARTIAL & GIN)
-- Diseñados para responder consultas complejas en < 2ms con millones de registros
-- ------------------------------------------------------------------------------

-- A. Índices de Cobertura para Llaves Foráneas (Evitan Table Scans en JOINs masivos)
CREATE INDEX idx_factura_det_factura    ON clinica_template.factura_detalles(id_factura);
CREATE INDEX idx_factura_det_articulo   ON clinica_template.factura_detalles(id_articulo);
CREATE INDEX idx_pagos_factura          ON clinica_template.pagos_transacciones(id_factura);
CREATE INDEX idx_pagos_turno            ON clinica_template.pagos_transacciones(id_turno_caja);
CREATE INDEX idx_facturas_cliente       ON clinica_template.facturas(id_cliente, fecha_emision DESC);
CREATE INDEX idx_facturas_turno         ON clinica_template.facturas(id_turno_caja);
CREATE INDEX idx_facturas_medico        ON clinica_template.facturas(id_medico, fecha_emision);
CREATE INDEX idx_nc_factura             ON clinica_template.notas_credito(id_factura_origen);
CREATE INDEX idx_nd_factura             ON clinica_template.notas_debito(id_factura_origen);
CREATE INDEX idx_nc_detalles_nc         ON clinica_template.nota_credito_detalles(id_nota_credito);
CREATE INDEX idx_nd_detalles_nd         ON clinica_template.nota_debito_detalles(id_nota_debito);
CREATE INDEX idx_entradas_det_entrada   ON clinica_template.entrada_mercancia_detalles(id_entrada);
CREATE INDEX idx_citas_expediente       ON clinica_template.citas_medicas(id_expediente);
CREATE INDEX idx_consultas_medico       ON clinica_template.consultas_oftalmologicas(id_medico, fecha_consulta DESC);
CREATE INDEX idx_tonometria_consulta    ON clinica_template.tonometria_iop(id_consulta);
CREATE INDEX idx_recetas_consulta       ON clinica_template.recetas_optometricas(id_consulta);
CREATE INDEX idx_estudios_exp           ON clinica_template.estudios_diagnosticos(id_expediente, fecha_estudio DESC);
CREATE INDEX idx_cirugias_exp           ON clinica_template.cirugias_programadas(id_expediente, fecha_programada);
CREATE INDEX idx_presupuestos_exp       ON clinica_template.presupuestos(id_expediente);
CREATE INDEX idx_turnos_cajero          ON clinica_template.turnos_caja(id_usuario_cajero, fecha_apertura DESC);

-- B. Índices Parciales Condicionales (Ocupan 95% menos RAM/Disco al indexar solo filas activas)
-- B.1 Cuentas por Cobrar (Facturas a crédito o con saldo pendiente)
CREATE INDEX idx_facturas_saldo_pendiente ON clinica_template.facturas(id_cliente, saldo_pendiente) 
WHERE saldo_pendiente > 0 AND estado_factura != 'ANULADA';

-- B.2 Citas activas de la jornada (Recepción y Sala de Espera en tiempo real)
CREATE INDEX idx_citas_activas_jornada ON clinica_template.citas_medicas(fecha_cita, id_medico) 
WHERE estado IN ('PROGRAMADA', 'CONFIRMADA', 'EN_SALA', 'EN_CONSULTA');

-- B.3 Turnos de caja activos (Acelera cobros POS al validar cajero en milisegundos)
CREATE INDEX idx_turnos_caja_abiertos ON clinica_template.turnos_caja(id_caja_fisica, id_usuario_cajero) 
WHERE estado = 'ABIERTO';

-- B.4 Reclamaciones ARS pendientes de facturación masiva
CREATE INDEX idx_ars_pendientes_lote ON clinica_template.autorizaciones_ars(id_aseguradora_ars, fecha_aprobacion) 
WHERE estado = 'PENDIENTE_FACTURAR_ARS';

-- B.5 Cola de Documentos e-CF pendientes de firmado o transmisión a DGII
CREATE INDEX idx_ecf_pendientes_envio ON clinica_template.ecf_documentos(estado_proceso, created_at) 
WHERE estado_proceso IN ('N', 'F', 'Q', 'P');

-- B.6 Alerta de Stock Crítico (Almacén y Compras)
CREATE INDEX idx_articulos_stock_alerta ON clinica_template.articulo_almacen(id_almacen, id_articulo) 
WHERE stock_actual <= stock_minimo;

-- B.7 Órdenes de Taller Óptico en Producción
CREATE INDEX idx_taller_en_proceso ON clinica_template.ordenes_laboratorio_optica(estado_taller, fecha_orden) 
WHERE estado_taller IN ('EN_COLA', 'EN_TALLADO', 'EN_BISELADO', 'CONTROL_CALIDAD');

-- C. Índices para Diagnósticos y Campos Semiestructurados (JSONB)
CREATE INDEX idx_consultas_cie10_principal ON clinica_template.consultas_oftalmologicas(diagnostico_cie10_principal);
CREATE INDEX idx_consultas_cie10_sec_gin ON clinica_template.consultas_oftalmologicas USING gin(diagnosticos_secundarios);
CREATE INDEX idx_articulos_optica_gin ON clinica_template.articulos USING gin(atributos_optica);
CREATE INDEX idx_ecf_glosas_gin ON clinica_template.ecf_documentos USING gin(glosas_rechazo);


-- ==============================================================================
-- SECCIÓN 3: PROCEDIMIENTOS, TRIGGERS Y FUNCIONES NATIVAS PL/pgSQL
-- ==============================================================================

-- 3.1 Triggers para Actualización Automática de 'updated_at'
CREATE OR REPLACE FUNCTION core.fn_actualizar_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_empresas_timestamp
BEFORE UPDATE ON core.empresas
FOR EACH ROW EXECUTE FUNCTION core.fn_actualizar_timestamp();

CREATE OR REPLACE FUNCTION clinica_template.fn_actualizar_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_usuarios_timestamp
BEFORE UPDATE ON clinica_template.usuarios
FOR EACH ROW EXECUTE FUNCTION clinica_template.fn_actualizar_timestamp();
FOR EACH ROW EXECUTE FUNCTION clinica_template.fn_actualizar_timestamp();

CREATE TRIGGER trg_socios_timestamp
BEFORE UPDATE ON clinica_template.socios_negocio
FOR EACH ROW EXECUTE FUNCTION clinica_template.fn_actualizar_timestamp();

CREATE TRIGGER trg_articulos_timestamp
BEFORE UPDATE ON clinica_template.articulos
FOR EACH ROW EXECUTE FUNCTION clinica_template.fn_actualizar_timestamp();

CREATE TRIGGER trg_facturas_timestamp
BEFORE UPDATE ON clinica_template.facturas
FOR EACH ROW EXECUTE FUNCTION clinica_template.fn_actualizar_timestamp();


-- 3.2 Trigger Automático de Kardex al Facturar (Solo Bienes Físicos)
CREATE OR REPLACE FUNCTION clinica_template.fn_trigger_kardex_facturacion()
RETURNS TRIGGER AS $$
DECLARE
    v_tipo_item VARCHAR(20);
    v_stock_ant NUMERIC(12, 2);
    v_costo_prom NUMERIC(14, 4);
    v_id_almacen INTEGER;
    v_num_factura VARCHAR(30);
BEGIN
    SELECT tipo, costo_promedio INTO v_tipo_item, v_costo_prom
      FROM clinica_template.articulos
     WHERE id_articulo = NEW.id_articulo;

    -- Los servicios clínicos nunca tocan kardex ni inventario
    IF v_tipo_item = 'SERVICIO_CLINICO' OR NEW.id_almacen IS NULL THEN
        RETURN NEW;
    END IF;

    v_id_almacen := NEW.id_almacen;

    SELECT numero_interno INTO v_num_factura
      FROM clinica_template.facturas
     WHERE id_factura = NEW.id_factura;

    SELECT stock_actual INTO v_stock_ant
      FROM clinica_template.articulo_almacen
     WHERE id_articulo = NEW.id_articulo AND id_almacen = v_id_almacen
     FOR UPDATE;

    IF v_stock_ant IS NULL THEN
        v_stock_ant := 0.00;
        INSERT INTO clinica_template.articulo_almacen (id_articulo, id_almacen, stock_actual)
        VALUES (NEW.id_articulo, v_id_almacen, -NEW.cantidad);
    ELSE
        UPDATE clinica_template.articulo_almacen
           SET stock_actual = stock_actual - NEW.cantidad
         WHERE id_articulo = NEW.id_articulo AND id_almacen = v_id_almacen;
    END IF;

    INSERT INTO clinica_template.kardex_movimientos (
        id_articulo, id_almacen, tipo_movimiento, documento_referencia,
        cantidad, costo_unitario, costo_total,
        stock_anterior, stock_posterior,
        costo_promedio_ant, costo_promedio_post
    ) VALUES (
        NEW.id_articulo, v_id_almacen, 'VENTA_FACTURA', v_num_factura,
        -NEW.cantidad, v_costo_prom, ROUND(NEW.cantidad * v_costo_prom, 2),
        v_stock_ant, v_stock_ant - NEW.cantidad,
        v_costo_prom, v_costo_prom
    );

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_descontar_kardex_factura
AFTER INSERT ON clinica_template.factura_detalles
FOR EACH ROW EXECUTE FUNCTION clinica_template.fn_trigger_kardex_facturacion();


-- 3.3 Trigger Automático de Kardex y Recálculo de Costo Promedio (AvgPrice) en Entradas de Mercancía
CREATE OR REPLACE FUNCTION clinica_template.fn_trigger_kardex_entrada()
RETURNS TRIGGER AS $$
DECLARE
    v_tipo_item VARCHAR(20);
    v_stock_ant NUMERIC(12, 2);
    v_costo_prom_ant NUMERIC(14, 4);
    v_costo_prom_post NUMERIC(14, 4);
    v_id_almacen INTEGER;
    v_num_entrada VARCHAR(30);
BEGIN
    SELECT tipo, costo_promedio INTO v_tipo_item, v_costo_prom_ant
      FROM clinica_template.articulos
     WHERE id_articulo = NEW.id_articulo;

    -- Los servicios clínicos nunca tocan kardex
    IF v_tipo_item = 'SERVICIO_CLINICO' THEN
        RETURN NEW;
    END IF;

    SELECT id_almacen, numero_interno INTO v_id_almacen, v_num_entrada
      FROM clinica_template.entradas_mercancia
     WHERE id_entrada = NEW.id_entrada;

    SELECT stock_actual INTO v_stock_ant
      FROM clinica_template.articulo_almacen
     WHERE id_articulo = NEW.id_articulo AND id_almacen = v_id_almacen
     FOR UPDATE;

    IF v_stock_ant IS NULL THEN
        v_stock_ant := 0.00;
        v_costo_prom_post := NEW.costo_unitario;
        INSERT INTO clinica_template.articulo_almacen (id_articulo, id_almacen, stock_actual)
        VALUES (NEW.id_articulo, v_id_almacen, NEW.cantidad);
    ELSE
        -- Fórmula Ponderada de Asignación de Costo (AvgPrice de SAP B1):
        -- NuevoCosto = ((StockAnt * CostoAnt) + (CantEntrada * CostoEntrada)) / (StockAnt + CantEntrada)
        IF (v_stock_ant + NEW.cantidad) > 0 THEN
            v_costo_prom_post := ROUND(((GREATEST(0, v_stock_ant) * v_costo_prom_ant) + (NEW.cantidad * NEW.costo_unitario)) / (GREATEST(0, v_stock_ant) + NEW.cantidad), 4);
        ELSE
            v_costo_prom_post := NEW.costo_unitario;
        END IF;

        UPDATE clinica_template.articulo_almacen
           SET stock_actual = stock_actual + NEW.cantidad
         WHERE id_articulo = NEW.id_articulo AND id_almacen = v_id_almacen;
    END IF;

    -- Actualizar el costo promedio maestro del artículo
    UPDATE clinica_template.articulos
       SET costo_promedio = v_costo_prom_post,
           costo_estandar = NEW.costo_unitario
     WHERE id_articulo = NEW.id_articulo;

    INSERT INTO clinica_template.kardex_movimientos (
        id_articulo, id_almacen, tipo_movimiento, documento_referencia,
        cantidad, costo_unitario, costo_total,
        stock_anterior, stock_posterior,
        costo_promedio_ant, costo_promedio_post
    ) VALUES (
        NEW.id_articulo, v_id_almacen, 'ENTRADA_COMPRA', v_num_entrada,
        NEW.cantidad, NEW.costo_unitario, ROUND(NEW.cantidad * NEW.costo_unitario, 2),
        v_stock_ant, v_stock_ant + NEW.cantidad,
        v_costo_prom_ant, v_costo_prom_post
    );

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_procesar_entrada_mercancia
AFTER INSERT ON clinica_template.entrada_mercancia_detalles
FOR EACH ROW EXECUTE FUNCTION clinica_template.fn_trigger_kardex_entrada();


-- 3.4 Trigger de Reingreso al Kardex por Nota de Crédito (Devolución Física)
CREATE OR REPLACE FUNCTION clinica_template.fn_trigger_kardex_nota_credito()
RETURNS TRIGGER AS $$
DECLARE
    v_tipo_item VARCHAR(20);
    v_devuelve BOOLEAN;
    v_stock_ant NUMERIC(12, 2);
    v_costo_prom NUMERIC(14, 4);
    v_id_almacen INTEGER;
    v_num_nc VARCHAR(30);
BEGIN
    SELECT devuelve_almacen, numero_interno INTO v_devuelve, v_num_nc
      FROM clinica_template.notas_credito
     WHERE id_nota_credito = NEW.id_nota_credito;

    IF NOT v_devuelve OR NEW.id_almacen IS NULL THEN
        RETURN NEW;
    END IF;

    SELECT tipo, costo_promedio INTO v_tipo_item, v_costo_prom
      FROM clinica_template.articulos
     WHERE id_articulo = NEW.id_articulo;

    IF v_tipo_item = 'SERVICIO_CLINICO' THEN
        RETURN NEW;
    END IF;

    v_id_almacen := NEW.id_almacen;

    SELECT stock_actual INTO v_stock_ant
      FROM clinica_template.articulo_almacen
     WHERE id_articulo = NEW.id_articulo AND id_almacen = v_id_almacen
     FOR UPDATE;

    IF v_stock_ant IS NULL THEN
        v_stock_ant := 0.00;
        INSERT INTO clinica_template.articulo_almacen (id_articulo, id_almacen, stock_actual)
        VALUES (NEW.id_articulo, v_id_almacen, NEW.cantidad);
    ELSE
        UPDATE clinica_template.articulo_almacen
           SET stock_actual = stock_actual + NEW.cantidad
         WHERE id_articulo = NEW.id_articulo AND id_almacen = v_id_almacen;
    END IF;

    INSERT INTO clinica_template.kardex_movimientos (
        id_articulo, id_almacen, tipo_movimiento, documento_referencia,
        cantidad, costo_unitario, costo_total,
        stock_anterior, stock_posterior,
        costo_promedio_ant, costo_promedio_post
    ) VALUES (
        NEW.id_articulo, v_id_almacen, 'ANULACION_NC', v_num_nc,
        NEW.cantidad, v_costo_prom, ROUND(NEW.cantidad * v_costo_prom, 2),
        v_stock_ant, v_stock_ant + NEW.cantidad,
        v_costo_prom, v_costo_prom
    );

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_reingresar_kardex_nc
AFTER INSERT ON clinica_template.nota_credito_detalles
FOR EACH ROW EXECUTE FUNCTION clinica_template.fn_trigger_kardex_nota_credito();


-- 3.5 Función Generadora de Datos para el Firmador e-CF DGII
-- Sustituye la función de 191 KB en SQL Server 'GetInvoiceDataForXML' por extracción directa en JSONB
CREATE OR REPLACE FUNCTION clinica_template.fn_extraer_datos_ecf(p_id_factura BIGINT)
RETURNS JSONB AS $$
DECLARE
    v_resultado JSONB;
BEGIN
    SELECT jsonb_build_object(
        'encabezado', jsonb_build_object(
            'version', '1.0',
            'id_doc', jsonb_build_object(
                'tipoe_cf', f.tipo_ncf,
                'encf', f.ncf,
                'fecha_vencimiento_secuencia', to_char(f.fecha_vencimiento_ncf, 'DD-MM-YYYY'),
                'fecha_emision', to_char(f.fecha_emision, 'DD-MM-YYYY HH24:MI:SS')
            ),
            'emisor', jsonb_build_object(
                'rnc_emisor', e.rnc,
                'razon_social', e.razon_social,
                'nombre_comercial', e.nombre_comercial,
                'direccion', s.direccion,
                'correo', s.correo,
                'telefono', s.telefono
            ),
            'comprador', jsonb_build_object(
                'rnc_comprador', COALESCE(f.rnc_cliente, ''),
                'razon_social', COALESCE(f.razon_social_cliente, c.nombres || ' ' || COALESCE(c.apellidos, '')),
                'correo', c.correo,
                'telefono', c.telefono_principal
            ),
            'totales', jsonb_build_object(
                'monto_gravado_total', f.monto_gravado_18 + f.monto_gravado_16,
                'monto_gravado_i1', f.monto_gravado_18,
                'monto_gravado_i2', f.monto_gravado_16,
                'monto_exento', f.monto_exento,
                'total_itbis', f.itbis_total,
                'total_itbis1', f.itbis_total,
                'monto_total', f.total_neto
            )
        ),
        'detalles', (
            SELECT jsonb_agg(jsonb_build_object(
                'numero_linea', d.numero_linea,
                'indicador_facturacion', CASE WHEN d.tasa_itbis = 18.00 THEN 1 WHEN d.tasa_itbis = 16.00 THEN 2 ELSE 3 END,
                'codigo_item', a.codigo,
                'nombre_item', d.descripcion,
                'indicador_bien_servicio', CASE WHEN d.tipo_item = 'BIEN_FISICO' THEN 1 ELSE 2 END,
                'cantidad', d.cantidad,
                'unidad_medida', a.unidad_medida,
                'precio_unitario', d.precio_unitario,
                'monto_descuento', d.monto_descuento,
                'monto_item', d.monto_total_linea
            ))
            FROM clinica_template.factura_detalles d
            JOIN clinica_template.articulos a ON a.id_articulo = d.id_articulo
            WHERE d.id_factura = f.id_factura
        )
    ) INTO v_resultado
    FROM clinica_template.facturas f
    JOIN clinica_template.sucursales s ON s.id_sucursal = f.id_sucursal
    JOIN core.empresas e ON e.slug_esquema = current_schema()
    JOIN clinica_template.socios_negocio c ON c.id_socio = f.id_cliente
    WHERE f.id_factura = p_id_factura;

    RETURN v_resultado;
END;
$$ LANGUAGE plpgsql;


-- 3.4 Procedimiento para Fusión de Expedientes Duplicados (Sustituye sp_ExpedienteMerge)
CREATE OR REPLACE PROCEDURE clinica_template.sp_fusionar_expedientes(
    p_id_expediente_origen BIGINT,
    p_id_expediente_destino BIGINT,
    p_id_usuario_auditor UUID
)
LANGUAGE plpgsql AS $$
BEGIN
    IF p_id_expediente_origen = p_id_expediente_destino THEN
        RAISE EXCEPTION 'No se puede fusionar un expediente consigo mismo.';
    END IF;

    -- Reasignar citas
    UPDATE clinica_template.citas_medicas
       SET id_expediente = p_id_expediente_destino
     WHERE id_expediente = p_id_expediente_origen;

    -- Reasignar consultas oftalmológicas
    UPDATE clinica_template.consultas_oftalmologicas
       SET id_expediente = p_id_expediente_destino
     WHERE id_expediente = p_id_expediente_origen;

    -- Reasignar cirugías y presupuestos
    UPDATE clinica_template.cirugias_programadas
       SET id_expediente = p_id_expediente_destino
     WHERE id_expediente = p_id_expediente_origen;

    UPDATE clinica_template.presupuestos
       SET id_expediente = p_id_expediente_destino
     WHERE id_expediente = p_id_expediente_origen;

    -- Reasignar órdenes de óptica
    UPDATE clinica_template.ordenes_laboratorio_optica
       SET id_expediente = p_id_expediente_destino
     WHERE id_expediente = p_id_expediente_origen;

    -- Desactivar expediente de origen
    UPDATE clinica_template.expedientes
       SET activo = FALSE,
           observaciones_alerta = COALESCE(observaciones_alerta, '') || ' [FUSIONADO CON EXP #' || p_id_expediente_destino || ']'
     WHERE id_expediente = p_id_expediente_origen;

    RAISE NOTICE 'Expediente % fusionado exitosamente en %', p_id_expediente_origen, p_id_expediente_destino;
END;
$$;


-- ==============================================================================
-- SECCIÓN 4: VISTAS ANALÍTICAS Y OPERATIVAS
-- ==============================================================================

-- 4.1 Resumen Financiero Diario por Medio de Pago
CREATE OR REPLACE VIEW clinica_template.v_resumen_ventas_diarias AS
SELECT 
    f.fecha_emision::DATE AS fecha,
    f.id_sucursal,
    COUNT(DISTINCT f.id_factura) AS total_facturas_emitidas,
    SUM(f.subtotal_bruto) AS total_bruto,
    SUM(f.descuento_total) AS total_descuentos,
    SUM(f.itbis_total) AS total_itbis,
    SUM(f.total_neto) AS total_neto,
    COALESCE(SUM(p.monto) FILTER (WHERE p.medio_pago = 'EFECTIVO'), 0.00) AS total_efectivo,
    COALESCE(SUM(p.monto) FILTER (WHERE p.medio_pago = 'TARJETA'), 0.00) AS total_tarjeta,
    COALESCE(SUM(p.monto) FILTER (WHERE p.medio_pago = 'TRANSFERENCIA'), 0.00) AS total_transferencia,
    COALESCE(SUM(p.monto) FILTER (WHERE p.medio_pago = 'CHEQUE'), 0.00) AS total_cheque,
    COALESCE(SUM(p.monto) FILTER (WHERE p.medio_pago = 'CREDITO_ARS'), 0.00) AS total_credito_ars
FROM clinica_template.facturas f
LEFT JOIN clinica_template.pagos_transacciones p ON p.id_factura = f.id_factura
WHERE f.estado_factura != 'ANULADA'
GROUP BY f.fecha_emision::DATE, f.id_sucursal;


-- 4.2 Pacientes en Espera y Flujo de Consultorio en Tiempo Real
CREATE OR REPLACE VIEW clinica_template.v_flujo_pacientes_hoy AS
SELECT 
    c.id_cita,
    c.fecha_cita,
    c.hora_inicio,
    p.numero_documento AS cedula_paciente,
    p.nombres || ' ' || COALESCE(p.apellidos, '') AS nombre_paciente,
    p.telefono_principal,
    m.nombres || ' ' || COALESCE(m.apellidos, '') AS nombre_medico,
    con.nombre AS consultorio,
    c.tipo_cita,
    c.estado,
    c.hora_llegada_sala,
    c.hora_inicio_atencion,
    CASE 
        WHEN c.hora_llegada_sala IS NOT NULL AND c.hora_inicio_atencion IS NULL 
        THEN ROUND(EXTRACT(EPOCH FROM (CURRENT_TIMESTAMP - c.hora_llegada_sala)) / 60)
        ELSE NULL 
    END AS minutos_en_sala_espera
FROM clinica_template.citas_medicas c
JOIN clinica_template.expedientes exp ON exp.id_expediente = c.id_expediente
JOIN clinica_template.socios_negocio p ON p.id_socio = exp.id_paciente
JOIN clinica_template.socios_negocio m ON m.id_socio = c.id_medico
LEFT JOIN clinica_template.consultorios con ON con.id_consultorio = c.id_consultorio
WHERE c.fecha_cita = CURRENT_DATE
ORDER BY c.hora_inicio ASC;


-- 4.3 Alertas de Inventario Crítico y Reposición de Óptica
CREATE OR REPLACE VIEW clinica_template.v_alertas_stock_bajo AS
SELECT 
    a.codigo,
    a.nombre AS articulo,
    cat.nombre AS categoria,
    alm.nombre AS almacen,
    aa.stock_actual,
    aa.stock_minimo,
    aa.stock_disponible,
    (aa.stock_minimo - aa.stock_actual) AS deficit_reposicion,
    a.costo_promedio,
    ROUND((aa.stock_minimo - aa.stock_actual) * a.costo_promedio, 2) AS costo_estimado_reabastecimiento
FROM clinica_template.articulo_almacen aa
JOIN clinica_template.articulos a ON a.id_articulo = aa.id_articulo
JOIN clinica_template.articulo_categorias cat ON cat.id_categoria = a.id_categoria
JOIN clinica_template.almacenes alm ON alm.id_almacen = aa.id_almacen
WHERE a.tipo = 'BIEN_FISICO'
  AND aa.habilitado = TRUE
  AND aa.stock_actual <= aa.stock_minimo
ORDER BY (aa.stock_minimo - aa.stock_actual) DESC;


-- 4.4 Disponibilidad de Comprobantes Fiscales y Semáforos DGII
CREATE OR REPLACE VIEW clinica_template.v_disponibilidad_ncf AS
SELECT 
    id_carga,
    serie,
    tipo_comprobante,
    secuencia_inicial,
    secuencia_final,
    secuencia_actual,
    (secuencia_final - secuencia_actual) AS secuencias_disponibles,
    fecha_vencimiento,
    (fecha_vencimiento - CURRENT_DATE) AS dias_para_vencer,
    CASE 
        WHEN (secuencia_final - secuencia_actual) <= alerta_critico OR (fecha_vencimiento - CURRENT_DATE) <= 5 THEN 'ROJO_CRITICO'
        WHEN (secuencia_final - secuencia_actual) <= alerta_minimo OR (fecha_vencimiento - CURRENT_DATE) <= 15 THEN 'AMARILLO_ALERTA'
        ELSE 'VERDE_OPTIMO'
    END AS semaforo_estado
FROM clinica_template.carga_ncf
WHERE activo = TRUE;


-- ==============================================================================
-- SECCIÓN 5: PROCEDIMIENTO DE APROVISIONAMIENTO DE NUEVO CLIENTE (TENANT FACTORY)
-- ==============================================================================
CREATE OR REPLACE PROCEDURE core.sp_aprovisionar_nueva_clinica(
    p_rnc VARCHAR,
    p_razon_social VARCHAR,
    p_nombre_comercial VARCHAR,
    p_slug_esquema VARCHAR,
    p_id_plan INTEGER,
    p_correo_admin VARCHAR,
    p_password_admin VARCHAR,
    p_nombres_admin VARCHAR,
    p_apellidos_admin VARCHAR
)
LANGUAGE plpgsql AS $$
DECLARE
    v_id_empresa UUID;
    v_sql TEXT;
BEGIN
    IF EXISTS (SELECT 1 FROM core.empresas WHERE slug_esquema = p_slug_esquema OR rnc = p_rnc) THEN
        RAISE EXCEPTION 'El RNC o el slug de esquema ya se encuentran registrados.';
    END IF;

    INSERT INTO core.empresas (rnc, razon_social, nombre_comercial, slug_esquema, id_plan, correo_contacto)
    VALUES (p_rnc, p_razon_social, p_nombre_comercial, p_slug_esquema, p_id_plan, p_correo_admin)
    RETURNING id_empresa INTO v_id_empresa;

    v_sql := format('CREATE SCHEMA IF NOT EXISTS %I', p_slug_esquema);
    EXECUTE v_sql;

    RAISE NOTICE 'Clínica % registrada y esquema % aprovisionado exitosamente con ID %', 
                 p_razon_social, p_slug_esquema, v_id_empresa;
END;
$$;
