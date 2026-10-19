-- ==========================================================================
-- EXPORTACIÓN ESTRUCTURA / SCHEMA COMPLETO DE CMS
-- Generado automáticamente para referencia de migración Cloud / PostgreSQL
-- Fecha: 2026-10-12 19:38:51
-- ==========================================================================

-- ==========================================================================
-- TABLAS Y COLUMNAS
-- ==========================================================================

-- Tabla: AprobacionARS
CREATE TABLE [dbo].[AprobacionARS] (
	[IdARS] int IDENTITY(1,1) NOT NULL,
	[TipoSocio] nvarchar(15) NULL,
	[CardCode] nvarchar(100) NULL,
	[CardName] nvarchar(100) NULL,
	[NumExpediente] int NULL,
	[NumAfiliado] nvarchar(50) NULL,
	[CardCode_ARS] nvarchar(100) NULL,
	[NumAprobacion] nvarchar(50) NULL,
	[FechaAprobacionT] datetime NULL,
	[FechaAprobacionD] date NULL,
	[FechaVencimientoT] datetime NULL,
	[FechaVencimientoD] date NULL,
	[MontoAprobado] numeric(19, 6) NULL,
	[PorcentajeCober] numeric(5, 2) NULL,
	[MontoResponsable] numeric(19, 6) NULL,
	[DocEntry_Factura] int NULL,
	[NumFactura] nvarchar(20) NULL,
	[FechaFacturaT] datetime NULL,
	[FechaFacturaD] date NULL,
	[DocEntry_FacturaARS] int NULL,
	[NumFacturaARS] nvarchar(20) NULL,
	[FechaFacturaARS] datetime NULL,
	[LoteARS] nvarchar(50) NULL,
	[TipoServicio] nvarchar(50) NULL,
	[Observaciones] nvarchar(254) NULL,
	[Status] char(1) NULL DEFAULT ('P'),
	[U_user] nvarchar(20) NULL,
	[CreateDateT] datetime NULL DEFAULT (getdate()),
	[CreateDateD] date NULL DEFAULT (CONVERT([date],getdate())),
	CONSTRAINT [PK_AprobacionARS] PRIMARY KEY CLUSTERED ([IdARS] ASC)
);
GO

-- Tabla: caja
CREATE TABLE [dbo].[caja] (
	[id_caja] int IDENTITY(1,1) NOT NULL,
	[nombre] varchar(20) NOT NULL,
	[descripcion] varchar(50) NOT NULL DEFAULT (''),
	[cuenta_efectivo] varchar(15) NOT NULL,
	[cuenta_cheque] varchar(15) NOT NULL,
	[cuenta_tarjeta] varchar(15) NOT NULL,
	[cuenta_general] varchar(15) NOT NULL,
	[sap_ocrcode] nvarchar(8) NOT NULL,
	[activo] char(1) NOT NULL DEFAULT ('Y'),
	[lectura_serial] char(1) NOT NULL DEFAULT ('N'),
	[alias] nvarchar(50) NULL,
	[interfaz_tactil] char(1) NOT NULL DEFAULT ('N'),
	[id_imp_contado] int NOT NULL DEFAULT ((-1)),
	[id_imp_credito] int NOT NULL DEFAULT ((-1)),
	[id_serie] char(1) NOT NULL DEFAULT (''),
	[facturar_contado] char(1) NOT NULL DEFAULT ('N'),
	[facturar_credito] char(1) NOT NULL DEFAULT ('N'),
	[usa_comanda] char(1) NOT NULL DEFAULT ('N'),
	[id_imp_comanda1] int NOT NULL DEFAULT ((-1)),
	[id_imp_comanda2] int NOT NULL DEFAULT ((-1)),
	[id_imp_comanda3] int NOT NULL DEFAULT ((-1)),
	[sap_whscode] nvarchar(8) NULL,
	[fondo_fijo] nchar(1) NOT NULL DEFAULT ('N'),
	[fondo] numeric(18, 2) NOT NULL DEFAULT ((0.00)),
	[exportDoc] char(1) NULL DEFAULT ('N'),
	[enviaEmail] char(1) NULL DEFAULT ('N'),
	[terminal] nvarchar(50) NULL,
	[ip_verifom] nvarchar(14) NULL,
	[puerto_verifom] int NULL,
	[tiempo_verifom] int NULL,
	[puerto_pos] int NULL,
	[cuenta_transferencia] varchar(20) NOT NULL DEFAULT (''),
	[tipo_comand_label] nvarchar(50) NULL DEFAULT ('ZPL'),
	[id_imp_label] int NULL DEFAULT ((-1)),
	[retiro_permitido] numeric(18, 2) NOT NULL DEFAULT ((0)),
	[copiascontado] int NOT NULL DEFAULT ((1)),
	CONSTRAINT [PK_caja_744] PRIMARY KEY CLUSTERED ([id_caja] ASC)
);
GO

-- Tabla: cardnet_log
CREATE TABLE [dbo].[cardnet_log] (
	[id] int IDENTITY(1,1) NOT NULL,
	[factura] nvarchar(100) NULL,
	[fecha_hora] datetime NULL,
	[fecha_trans] date NULL,
	[hora_trans] nvarchar(15) NULL,
	[operacion] nvarchar(15) NULL,
	[sub_total] numeric(19, 2) NULL,
	[impuesto] numeric(19, 2) NULL,
	[total] numeric(19, 2) NULL,
	[num_devoluciones] int NULL,
	[num_ventas] int NULL,
	[sub_total_devoluciones] numeric(19, 2) NULL,
	[impuesto_devoluciones] numeric(19, 2) NULL,
	[otro_impuestos] numeric(19, 2) NULL,
	[total_devoluciones] numeric(19, 2) NULL,
	[trama] nvarchar(MAX) NULL,
	[respuesta] varchar(MAX) NULL,
	[lote] nvarchar(15) NULL,
	[num_ref] nvarchar(15) NULL,
	[nombre_th] nvarchar(54) NULL,
	[aprobacion] nvarchar(15) NULL,
	[modo] nvarchar(15) NULL,
	[rrn] nvarchar(15) NULL,
	[host] nvarchar(15) NULL,
	[tipo_tarjeta] nvarchar(15) NULL,
	[tarjeta] nvarchar(16) NULL,
	[resultado] nvarchar(15) NULL,
	[terminal] nvarchar(15) NULL,
	[verifone_id] nvarchar(15) NULL,
	[id_diario_caja] int NULL,
	[SalesIndicator] varchar(10) NULL,
	[recurrente] char(1) NULL,
	[marginrate] varchar(20) NULL,
	[amount] varchar(20) NULL,
	[display_rate] varchar(20) NULL,
	[transaction_currency] varchar(10) NULL,
	[estatus_resp] varchar(50) NULL,
	[proveedor] varchar(50) NULL,
	[trama_original] text NULL,
	CONSTRAINT [PK_cardnet_log_926] PRIMARY KEY CLUSTERED ([id] ASC)
);
GO

-- Tabla: carga_ncf
CREATE TABLE [dbo].[carga_ncf] (
	[id_carga] int IDENTITY(1,1) NOT NULL,
	[fecha_carga] date NOT NULL DEFAULT (getdate()),
	[serie] char(3) NOT NULL,
	[vencimiento] date NULL,
	[inicial] int NOT NULL,
	[contador] int NOT NULL,
	[final] int NOT NULL,
	[usuario] varchar(30) NULL,
	CONSTRAINT [PK__carga_nc__D3C09EFBD0A68BAC] PRIMARY KEY CLUSTERED ([id_carga] ASC)
);
GO

-- Tabla: cierre_denominacion
CREATE TABLE [dbo].[cierre_denominacion] (
	[id_cierre_denominacion] int IDENTITY(1,1) NOT NULL,
	[id_diario_caja] int NOT NULL,
	[denominacion] numeric(15, 2) NOT NULL,
	[cantidad] int NOT NULL,
	[total] numeric(15, 2) NOT NULL,
	CONSTRAINT [PK_cierre_denominacion] PRIMARY KEY CLUSTERED ([id_cierre_denominacion] ASC)
);
GO

-- Tabla: concepto_retiro
CREATE TABLE [dbo].[concepto_retiro] (
	[id_concepto_retiro] int IDENTITY(1,1) NOT NULL,
	[descripcion] nvarchar(50) NOT NULL,
	[activo] char(1) NOT NULL DEFAULT ('Y'),
	CONSTRAINT [PK_concepto_retiro] PRIMARY KEY CLUSTERED ([id_concepto_retiro] ASC)
);
GO

-- Tabla: configuration
CREATE TABLE [dbo].[configuration] (
	[keyword] nvarchar(50) NULL,
	[value] nvarchar(50) NULL,
	[description] text NULL
);
GO

-- Tabla: connection
CREATE TABLE [dbo].[connection] (
	[id_connection] int IDENTITY(1,1) NOT NULL,
	[alias] nvarchar(20) NOT NULL,
	[server] nvarchar(20) NOT NULL,
	[port] nvarchar(10) NOT NULL DEFAULT (''),
	[db_name] nvarchar(50) NOT NULL,
	[db_user] nvarchar(20) NOT NULL,
	[db_password] nvarchar(300) NOT NULL,
	[db_driver] nvarchar(20) NOT NULL,
	[active] nchar(1) NOT NULL DEFAULT ('N'),
	[description] text NOT NULL DEFAULT ('')
);
GO

-- Tabla: devolucion
CREATE TABLE [dbo].[devolucion] (
	[id_factura] int NOT NULL,
	[id_razon] int NOT NULL,
	[id_chofer] int NOT NULL,
	[almacen_destino] varchar(10) NOT NULL,
	[id_almacen] int NOT NULL,
	[fecha_devolucion] date NOT NULL,
	[usuario] varchar(20) NOT NULL,
	[comentario] varchar(100) NULL,
	[fecha_hora] datetime NULL DEFAULT (getdate()),
	[date_to_sap] date NULL,
	[referencia] varchar(10) NULL,
	CONSTRAINT [PK_devolucion_987] PRIMARY KEY CLUSTERED ([id_factura] ASC)
);
GO

-- Tabla: diario_caja
CREATE TABLE [dbo].[diario_caja] (
	[id_diario_caja] int IDENTITY(1,1) NOT NULL,
	[id_caja] int NOT NULL,
	[fecha_apertura] date NOT NULL DEFAULT (getdate()),
	[hora_apertura] datetime NOT NULL DEFAULT (getdate()),
	[fecha_cierre] date NULL,
	[hora_cierre] datetime NULL,
	[monto_apertura] numeric(15, 2) NOT NULL,
	[monto_diferencia] numeric(15, 2) NOT NULL,
	[monto_efectivo] numeric(15, 2) NOT NULL DEFAULT ((0)),
	[monto_tarjeta] numeric(15, 2) NOT NULL DEFAULT ((0)),
	[monto_cheque] numeric(15, 2) NOT NULL DEFAULT ((0)),
	[monto_transferencia] numeric(15, 2) NOT NULL DEFAULT ((0)),
	[monto_credito] numeric(15, 2) NOT NULL DEFAULT ((0)),
	[usuario_apertura] varchar(15) NOT NULL,
	[usuario_cierre] varchar(15) NULL,
	[orden_cierre] int NOT NULL DEFAULT ((0)),
	[monto_retiro] numeric(15, 2) NOT NULL DEFAULT ((0)),
	[comentario] nvarchar(300) NULL,
	[source_id] int NOT NULL DEFAULT ((0)),
	CONSTRAINT [PK__diario_c__70DDBB265F28ED58] PRIMARY KEY CLUSTERED ([id_diario_caja] ASC)
);
GO

-- Tabla: diario_caja_det
CREATE TABLE [dbo].[diario_caja_det] (
	[id_diario_caja_det] int IDENTITY(1,1) NOT NULL,
	[id_diario_caja] int NOT NULL,
	[id_documento] int NOT NULL DEFAULT ((0)),
	[id_tipo_movimiento] int NOT NULL,
	[fecha] datetime NOT NULL DEFAULT (getdate()),
	[monto_factura] numeric(15, 2) NOT NULL,
	[monto_caja] numeric(15, 2) NOT NULL,
	[comentario] nvarchar(300) NULL,
	[U_SCIASerie] nvarchar(20) NOT NULL DEFAULT (''),
	[U_SCIADocNum] int NOT NULL DEFAULT ((0)),
	[C_Posted] char(1) NOT NULL DEFAULT ('N'),
	[docType] char(1) NOT NULL DEFAULT ('X'),
	[cuenta] int NULL,
	[referencia] nvarchar(10) NOT NULL DEFAULT (''),
	[devuelta] numeric(15, 2) NULL DEFAULT ((0)),
	[U_Cobrador] nvarchar(8) NULL,
	[U_user] nvarchar(20) NULL,
	[U_terminal] nvarchar(20) NULL,
	[mark] char(1) NULL DEFAULT ('N'),
	[descripcion] varchar(300) NULL DEFAULT (''),
	[ref_doc] nvarchar(10) NOT NULL DEFAULT (''),
	[series] int NULL DEFAULT ((95)),
	[trans_banc] varchar(10) NULL,
	[trans_ref] varchar(10) NULL,
	[trans_date] date NULL,
	[recibo_factura] int NULL,
	[sap_creditcard] int NOT NULL DEFAULT ((-1)),
	CONSTRAINT [PK__diario_c__37BB501BE11F12C0] PRIMARY KEY CLUSTERED ([id_diario_caja_det] ASC)
);
GO

-- Tabla: division
CREATE TABLE [dbo].[division] (
	[id_division] int IDENTITY(1,1) NOT NULL,
	[nombre] nvarchar(100) NULL,
	[descripcion] text NULL,
	[RNC] nvarchar(30) NULL,
	[razon_social] nvarchar(100) NULL,
	[direccion] text NOT NULL,
	[telefono1] nvarchar(15) NULL,
	[telefono2] nvarchar(15) NULL,
	[web] nvarchar(100) NULL,
	[email] nvarchar(100) NULL,
	[renglon_articulo] nchar(1) NOT NULL DEFAULT ('Y'),
	[cliente_prop] int NULL,
	[prioridad_condicion] char(1) NOT NULL DEFAULT ('C'),
	[sap_ocrcode] nvarchar(8) NULL,
	[cliente_generico] nvarchar(30) NULL,
	[isLinked] char(1) NOT NULL DEFAULT ('Y'),
	CONSTRAINT [PK_sociedad] PRIMARY KEY CLUSTERED ([id_division] ASC)
);
GO

-- Tabla: division_series_sap
CREATE TABLE [dbo].[division_series_sap] (
	[id_record] bigint IDENTITY(1,1) NOT NULL,
	[id_division] int NULL,
	[obj_type] int NULL,
	[series_sap] int NULL,
	CONSTRAINT [PK__division__13533D293D34F91D] PRIMARY KEY CLUSTERED ([id_record] ASC)
);
GO

-- Tabla: ecf_bad
CREATE TABLE [dbo].[ecf_bad] (
	[id_record] int NOT NULL,
	[doc_id] int NULL,
	[NumAtCard] nvarchar(20) NULL,
	[ecf_type] nvarchar(20) NULL,
	[ecf] nvarchar(50) NULL,
	[ecf_vence] date NULL,
	[ecf_print_pages] nvarchar(1) NULL,
	[doc_type] nvarchar(50) NULL,
	[ecf_rnc_emisor] nvarchar(50) NULL,
	[ecf_rnc_comprador] nvarchar(50) NULL,
	[xml_file_name] nvarchar(50) NULL,
	[xml_file] nvarchar(MAX) NULL,
	[xml_file_signed] nvarchar(MAX) NULL,
	[xml_security_code] nvarchar(50) NULL,
	[xml_file_sign_date] datetime NULL,
	[xml_file_qrcode] nvarchar(MAX) NULL,
	[xml_file_compact] nvarchar(MAX) NULL,
	[xml_file_compact_signed] nvarchar(MAX) NULL,
	[xml_secret_code] nvarchar(50) NULL,
	[xml_file_compact_qrcode] nvarchar(MAX) NULL,
	[xml_response] nvarchar(MAX) NULL,
	[xml_track_id] nvarchar(MAX) NULL,
	[xml_track_response] nvarchar(MAX) NULL,
	[xml_ecf_status_code] nvarchar(50) NULL,
	[xml_ecf_status_name] nvarchar(50) NULL,
	[xml_ecf_used_seq] nvarchar(20) NULL,
	[date_process] date NULL,
	[datetime_process] datetime NULL,
	[C_Posted] nvarchar(1) NULL,
	[active] nvarchar(1) NULL,
	[ecf_invoice_date] date NULL
);
GO

-- Tabla: ecf_catalog_type
CREATE TABLE [dbo].[ecf_catalog_type] (
	[id_record] int IDENTITY(1,1) NOT NULL,
	[catalog_type] nvarchar(20) NULL,
	[catalog_type_name] nvarchar(100) NULL,
	[catalog_type_status] nvarchar(1) NULL DEFAULT (N'O'),
	[active] nvarchar(1) NULL DEFAULT (N'Y'),
	CONSTRAINT [PK_ecf_catalog_type] PRIMARY KEY CLUSTERED ([id_record] ASC)
);
GO

-- Tabla: ecf_certificate
CREATE TABLE [dbo].[ecf_certificate] (
	[id_record] int IDENTITY(1,1) NOT NULL,
	[cert_name] nvarchar(50) NULL,
	[cert_description] nvarchar(100) NULL,
	[cert_file] varbinary(MAX) NULL,
	[cert_key] nvarchar(50) NULL,
	[cert_valid_from] date NULL,
	[cert_valid_to] date NULL,
	[cert_inserted] datetime NULL DEFAULT (getdate()),
	[cert_status] nvarchar(1) NULL DEFAULT (N'O'),
	[active] nvarchar(1) NULL DEFAULT (N'Y'),
	CONSTRAINT [PK_ecf_certificate] PRIMARY KEY CLUSTERED ([id_record] ASC)
);
GO

-- Tabla: ecf_emisor2
CREATE TABLE [dbo].[ecf_emisor2] (
	[id_record] int IDENTITY(1,1) NOT NULL,
	[group_id] int NULL,
	[group_name] nvarchar(50) NULL,
	[field_def] nvarchar(100) NULL,
	[field_definition] nvarchar(100) NULL,
	[rec1] nvarchar(MAX) NULL,
	[rec2] nvarchar(MAX) NULL,
	[rec3] nvarchar(MAX) NULL,
	[rec4] nvarchar(MAX) NULL,
	[rec5] nvarchar(MAX) NULL,
	[rec6] nvarchar(MAX) NULL,
	[rec7] nvarchar(MAX) NULL,
	[rec8] nvarchar(MAX) NULL,
	[rec9] nvarchar(MAX) NULL,
	[rec10] nvarchar(MAX) NULL,
	[rec11] nvarchar(MAX) NULL,
	[rec12] nvarchar(MAX) NULL,
	[rec13] nvarchar(MAX) NULL,
	[rec14] nvarchar(MAX) NULL,
	[rec15] nvarchar(MAX) NULL,
	[rec_status] nvarchar(1) NULL DEFAULT (N'O'),
	[active] nvarchar(1) NULL DEFAULT (N'Y'),
	CONSTRAINT [PK_ecf_emisor2] PRIMARY KEY CLUSTERED ([id_record] ASC)
);
GO

-- Tabla: ecf_global_config
CREATE TABLE [dbo].[ecf_global_config] (
	[id_record] int IDENTITY(1,1) NOT NULL,
	[config_name] nvarchar(50) NULL,
	[config_value] nvarchar(100) NULL,
	[config_status] nvarchar(1) NULL DEFAULT (N'O'),
	[active] nvarchar(1) NULL DEFAULT (N'Y'),
	CONSTRAINT [PK_ecf_global_config] PRIMARY KEY CLUSTERED ([id_record] ASC)
);
GO

-- Tabla: ecf_payment_forms
CREATE TABLE [dbo].[ecf_payment_forms] (
	[id_record] int IDENTITY(1,1) NOT NULL,
	[ecf_payform_id] int NULL,
	[ecf_payform_name] nvarchar(100) NULL,
	[ecf_payform_sap] int NULL,
	[ecf_payform_sap_name] nvarchar(50) NULL,
	[ecf_payform_status] nvarchar(1) NULL DEFAULT (N'O'),
	[active] nvarchar(1) NULL DEFAULT (N'Y'),
	CONSTRAINT [PK_ecf_payment_forms] PRIMARY KEY CLUSTERED ([id_record] ASC)
);
GO

-- Tabla: ecf_tax_config
CREATE TABLE [dbo].[ecf_tax_config] (
	[id_record] int IDENTITY(1,1) NOT NULL,
	[taxecf_id] nvarchar(10) NULL,
	[taxecf_name] nvarchar(50) NULL,
	[taxecf_value] numeric(12, 4) NULL,
	[taxsap_name] nvarchar(50) NULL,
	[taxsap_value] numeric(12, 4) NULL,
	[taxecf_status] nvarchar(1) NULL DEFAULT (N'O'),
	[active] nvarchar(1) NULL DEFAULT (N'Y'),
	CONSTRAINT [PK_ecf_tax_config] PRIMARY KEY CLUSTERED ([id_record] ASC)
);
GO

-- Tabla: ecf_type
CREATE TABLE [dbo].[ecf_type] (
	[id_record] int IDENTITY(1,1) NOT NULL,
	[ecf_type_id] nvarchar(10) NULL,
	[ecf_type_name] nvarchar(100) NULL,
	[ecf_type_alias] nvarchar(100) NULL,
	[ecf_type_min_yellow] int NULL,
	[ecf_type_min_red] int NULL,
	[ecf_type_order] int NULL,
	[ecf_type_status] nvarchar(1) NULL DEFAULT (N'O'),
	[active] nvarchar(1) NULL DEFAULT (N'Y'),
	CONSTRAINT [PK_ecf_type] PRIMARY KEY CLUSTERED ([id_record] ASC)
);
GO

-- Tabla: ecf_units
CREATE TABLE [dbo].[ecf_units] (
	[id_record] int IDENTITY(1,1) NOT NULL,
	[ecfunit_id] int NULL,
	[ecfunit_name] nvarchar(50) NULL,
	[ecfunit_description] nvarchar(255) NULL,
	[ecfsapunit_name] nvarchar(50) NULL,
	[ecfsapunit_namemf] nvarchar(50) NULL,
	[ecfunit_status] nvarchar(1) NULL DEFAULT (N'O'),
	[active] nvarchar(1) NULL DEFAULT (N'Y'),
	CONSTRAINT [PK_ecf_units] PRIMARY KEY CLUSTERED ([id_record] ASC)
);
GO

-- Tabla: ecf_url_api
CREATE TABLE [dbo].[ecf_url_api] (
	[id_record] int IDENTITY(1,1) NOT NULL,
	[url_api] nvarchar(MAX) NULL,
	[url_description] nvarchar(100) NULL,
	[url_type_code] int NULL,
	[url_type_name] nvarchar(50) NULL,
	[url_request_type] nvarchar(50) NULL,
	[url_order] int NULL,
	[url_status] nvarchar(1) NULL DEFAULT (N'O'),
	[active] nvarchar(1) NULL DEFAULT (N'Y'),
	CONSTRAINT [PK_ecf_url_api] PRIMARY KEY CLUSTERED ([id_record] ASC)
);
GO

-- Tabla: ecf_url_chars
CREATE TABLE [dbo].[ecf_url_chars] (
	[id_record] int IDENTITY(1,1) NOT NULL,
	[char_name] nvarchar(50) NULL,
	[char_value] nvarchar(10) NULL,
	[char_hex] nvarchar(10) NULL,
	CONSTRAINT [PK_ecf_url_chars] PRIMARY KEY CLUSTERED ([id_record] ASC)
);
GO

-- Tabla: ecf_xml_catalog
CREATE TABLE [dbo].[ecf_xml_catalog] (
	[id_record] int IDENTITY(1,1) NOT NULL,
	[tag_id] int NULL DEFAULT ((0)),
	[tag_number] nvarchar(50) NULL,
	[tag_name] nvarchar(100) NULL,
	[tag_to_remove] nvarchar(255) NULL,
	[tag_type_id] int NULL,
	[tag_type_name] nvarchar(50) NULL,
	[tag_parent_id] int NULL DEFAULT ((0)),
	[tag_parent_name] nvarchar(100) NULL,
	[tag_group_id] int NULL,
	[tag_group_name] nvarchar(50) NULL,
	[catalog_type] nvarchar(20) NULL,
	[tag_status] nvarchar(1) NULL DEFAULT (N'Y'),
	[active] nvarchar(1) NULL DEFAULT (N'Y'),
	CONSTRAINT [PK_ecf_xml_catalog] PRIMARY KEY CLUSTERED ([id_record] ASC)
);
GO

-- Tabla: ecf_xml_invoice
CREATE TABLE [dbo].[ecf_xml_invoice] (
	[id_record] int IDENTITY(1,1) NOT NULL,
	[doc_id] int NULL,
	[NumAtCard] nvarchar(20) NULL,
	[ecf_type] nvarchar(20) NULL,
	[ecf] nvarchar(50) NULL,
	[ecf_vence] date NULL,
	[ecf_print_pages] nvarchar(1) NULL DEFAULT (N'N'),
	[doc_type] nvarchar(50) NULL,
	[ecf_rnc_emisor] nvarchar(50) NULL,
	[ecf_rnc_comprador] nvarchar(50) NULL,
	[xml_file_name] nvarchar(50) NULL,
	[xml_file] nvarchar(MAX) NULL,
	[xml_file_signed] nvarchar(MAX) NULL,
	[xml_security_code] nvarchar(50) NULL,
	[xml_file_sign_date] datetime NULL,
	[xml_file_qrcode] nvarchar(MAX) NULL,
	[xml_file_compact] nvarchar(MAX) NULL,
	[xml_file_compact_signed] nvarchar(MAX) NULL,
	[xml_secret_code] nvarchar(50) NULL,
	[xml_file_compact_qrcode] nvarchar(MAX) NULL,
	[xml_response] nvarchar(MAX) NULL,
	[xml_track_id] nvarchar(MAX) NULL,
	[xml_track_response] nvarchar(MAX) NULL,
	[xml_ecf_status_code] nvarchar(50) NULL,
	[xml_ecf_status_name] nvarchar(50) NULL,
	[xml_ecf_used_seq] nvarchar(20) NULL,
	[date_process] date NULL DEFAULT (CONVERT([date],getdate())),
	[datetime_process] datetime NULL DEFAULT (getdate()),
	[C_Posted] nvarchar(1) NULL DEFAULT (N'N'),
	[active] nvarchar(1) NULL DEFAULT (N'Y'),
	[ecf_invoice_date] date NULL DEFAULT (CONVERT([date],getdate())),
	CONSTRAINT [PK_ecf_xml_invoice] PRIMARY KEY CLUSTERED ([id_record] ASC)
);
GO

-- Tabla: ecf_xml_token
CREATE TABLE [dbo].[ecf_xml_token] (
	[id_record] int IDENTITY(1,1) NOT NULL,
	[date_process] date NULL DEFAULT (CONVERT([date],getdate())),
	[datetime_process] datetime NULL DEFAULT (getdate()),
	[seed_file] xml NULL,
	[signed_seed_file] xml NULL,
	[token_xml] xml NULL,
	[token_bearer] nvarchar(MAX) NULL,
	[token_valid_from] datetime NULL,
	[token_valid_to] datetime NULL,
	[token_status] nvarchar(1) NULL DEFAULT (N'O'),
	[active] nvarchar(1) NULL DEFAULT (N'Y'),
	CONSTRAINT [PK_ecf_xml_token] PRIMARY KEY CLUSTERED ([id_record] ASC)
);
GO

-- Tabla: ecf_xsd_template
CREATE TABLE [dbo].[ecf_xsd_template] (
	[id_record] int IDENTITY(1,1) NOT NULL,
	[ecf_type] nvarchar(10) NULL,
	[doc_type] nvarchar(50) NULL,
	[xsd_tag_type] nvarchar(20) NULL,
	[xsd_filename] nvarchar(50) NULL,
	[xsd_template] nvarchar(MAX) NULL,
	[xsd_status] nvarchar(1) NULL DEFAULT (N'O'),
	[active] nvarchar(1) NULL DEFAULT (N'Y'),
	CONSTRAINT [PK_ecf_xsd_template] PRIMARY KEY CLUSTERED ([id_record] ASC)
);
GO

-- Tabla: impresora
CREATE TABLE [dbo].[impresora] (
	[id_impresora] int IDENTITY(1,1) NOT NULL,
	[nombre] nvarchar(50) NOT NULL,
	[descripcion] nvarchar(100) NULL,
	CONSTRAINT [PK_impresora] PRIMARY KEY CLUSTERED ([id_impresora] ASC)
);
GO

-- Tabla: medio_retiro
CREATE TABLE [dbo].[medio_retiro] (
	[id_medio_retiro] int IDENTITY(1,1) NOT NULL,
	[descripcion] nvarchar(50) NULL,
	[activo] char(1) NULL DEFAULT ('Y'),
	CONSTRAINT [PK_medio_retiro] PRIMARY KEY CLUSTERED ([id_medio_retiro] ASC)
);
GO

-- Tabla: POSDIN1
CREATE TABLE [dbo].[POSDIN1] (
	[C_Id] int IDENTITY(1,1) NOT NULL,
	[C_DocEntry] int NOT NULL,
	[DocEntry] int NULL,
	[LineNum] int NOT NULL,
	[TargetType] int NULL,
	[TrgetEntry] int NULL,
	[BaseRef] nvarchar(16) NULL,
	[BaseType] int NULL,
	[BaseEntry] int NULL,
	[BaseLine] int NULL,
	[LineStatus] char(1) NULL,
	[ItemCode] nvarchar(50) NULL,
	[Dscription] nvarchar(100) NULL,
	[Quantity] numeric(19, 6) NULL,
	[ShipDate] datetime NULL,
	[OpenQty] numeric(19, 6) NULL,
	[Price] numeric(19, 6) NULL,
	[Currency] nvarchar(3) NULL,
	[Rate] numeric(19, 6) NULL,
	[DiscPrcnt] numeric(19, 6) NULL,
	[LineTotal] numeric(19, 6) NULL,
	[TotalFrgn] numeric(19, 6) NULL,
	[OpenSum] numeric(19, 6) NULL,
	[OpenSumFC] numeric(19, 6) NULL,
	[VendorNum] nvarchar(50) NULL,
	[SerialNum] nvarchar(17) NULL,
	[WhsCode] nvarchar(8) NULL,
	[SlpCode] int NULL,
	[Commission] numeric(19, 6) NULL,
	[TreeType] char(1) NULL,
	[AcctCode] nvarchar(15) NULL,
	[TaxStatus] char(1) NULL,
	[GrossBuyPr] numeric(19, 6) NULL,
	[PriceBefDi] numeric(19, 6) NULL,
	[DocDate] datetime NULL,
	[Flags] int NULL,
	[OpenCreQty] numeric(19, 6) NULL,
	[UseBaseUn] char(1) NULL,
	[SubCatNum] nvarchar(50) NULL,
	[BaseCard] nvarchar(15) NULL,
	[TotalSumSy] numeric(19, 6) NULL,
	[OpenSumSys] numeric(19, 6) NULL,
	[InvntSttus] char(1) NULL,
	[OcrCode] nvarchar(8) NULL,
	[Project] nvarchar(20) NULL,
	[CodeBars] nvarchar(254) NULL,
	[VatPrcnt] numeric(19, 6) NULL,
	[VatGroup] nvarchar(8) NULL,
	[PriceAfVAT] numeric(19, 6) NULL,
	[Height1] numeric(19, 6) NULL,
	[Hght1Unit] smallint NULL,
	[Height2] numeric(19, 6) NULL,
	[Hght2Unit] smallint NULL,
	[Width1] numeric(19, 6) NULL,
	[Wdth1Unit] smallint NULL,
	[Width2] numeric(19, 6) NULL,
	[Wdth2Unit] smallint NULL,
	[Length1] numeric(19, 6) NULL,
	[Len1Unit] smallint NULL,
	[length2] numeric(19, 6) NULL,
	[Len2Unit] smallint NULL,
	[Volume] numeric(19, 6) NULL,
	[VolUnit] smallint NULL,
	[Weight1] numeric(19, 6) NULL,
	[Wght1Unit] smallint NULL,
	[Weight2] numeric(19, 6) NULL,
	[Wght2Unit] smallint NULL,
	[Factor1] numeric(19, 6) NULL,
	[Factor2] numeric(19, 6) NULL,
	[Factor3] numeric(19, 6) NULL,
	[Factor4] numeric(19, 6) NULL,
	[PackQty] numeric(19, 6) NULL,
	[UpdInvntry] char(1) NULL,
	[BaseDocNum] int NULL,
	[BaseAtCard] nvarchar(100) NULL,
	[SWW] nvarchar(16) NULL,
	[VatSum] numeric(19, 6) NULL,
	[VatSumFrgn] numeric(19, 6) NULL,
	[VatSumSy] numeric(19, 6) NULL,
	[FinncPriod] int NULL,
	[ObjType] nvarchar(20) NULL,
	[LogInstanc] int NULL,
	[BlockNum] nvarchar(100) NULL,
	[ImportLog] nvarchar(20) NULL,
	[DedVatSum] numeric(19, 6) NULL,
	[DedVatSumF] numeric(19, 6) NULL,
	[DedVatSumS] numeric(19, 6) NULL,
	[IsAqcuistn] char(1) NULL,
	[DistribSum] numeric(19, 6) NULL,
	[DstrbSumFC] numeric(19, 6) NULL,
	[DstrbSumSC] numeric(19, 6) NULL,
	[GrssProfit] numeric(19, 6) NULL,
	[GrssProfSC] numeric(19, 6) NULL,
	[GrssProfFC] numeric(19, 6) NULL,
	[VisOrder] int NULL,
	[INMPrice] numeric(19, 6) NULL,
	[PoTrgNum] int NULL,
	[PoTrgEntry] nvarchar(11) NULL,
	[DropShip] char(1) NULL,
	[PoLineNum] int NULL,
	[Address] nvarchar(254) NULL,
	[TaxCode] nvarchar(8) NULL,
	[TaxType] char(1) NULL,
	[OrigItem] nvarchar(50) NULL,
	[BackOrdr] char(1) NULL,
	[FreeTxt] nvarchar(100) NULL,
	[PickStatus] char(1) NULL,
	[PickOty] numeric(19, 6) NULL,
	[PickIdNo] int NULL,
	[TrnsCode] smallint NULL,
	[VatAppld] numeric(19, 6) NULL,
	[VatAppldFC] numeric(19, 6) NULL,
	[VatAppldSC] numeric(19, 6) NULL,
	[BaseQty] numeric(19, 6) NULL,
	[BaseOpnQty] numeric(19, 6) NULL,
	[VatDscntPr] numeric(19, 6) NULL,
	[WtLiable] char(1) NULL,
	[DeferrTax] char(1) NULL,
	[EquVatPer] numeric(19, 6) NULL,
	[EquVatSum] numeric(19, 6) NULL,
	[EquVatSumF] numeric(19, 6) NULL,
	[EquVatSumS] numeric(19, 6) NULL,
	[LineVat] numeric(19, 6) NULL,
	[LineVatlF] numeric(19, 6) NULL,
	[LineVatS] numeric(19, 6) NULL,
	[unitMsr] nvarchar(100) NOT NULL DEFAULT (N'UND'),
	[NumPerMsr] numeric(19, 6) NULL,
	[CEECFlag] char(1) NULL,
	[ToStock] numeric(19, 6) NULL,
	[ToDiff] numeric(19, 6) NULL,
	[ExciseAmt] numeric(19, 6) NULL,
	[TaxPerUnit] numeric(19, 6) NULL,
	[TotInclTax] numeric(19, 6) NULL,
	[CountryOrg] nvarchar(3) NULL,
	[StckDstSum] numeric(19, 6) NULL,
	[ReleasQtty] numeric(19, 6) NULL,
	[LineType] char(1) NULL,
	[TranType] char(1) NULL,
	[Text] ntext NULL,
	[OwnerCode] int NULL,
	[StockPrice] numeric(19, 6) NULL,
	[ConsumeFCT] char(1) NULL,
	[LstByDsSum] numeric(19, 6) NULL,
	[StckINMPr] numeric(19, 6) NULL,
	[LstBINMPr] numeric(19, 6) NULL,
	[StckDstFc] numeric(19, 6) NULL,
	[StckDstSc] numeric(19, 6) NULL,
	[LstByDsFc] numeric(19, 6) NULL,
	[LstByDsSc] numeric(19, 6) NULL,
	[StockSum] numeric(19, 6) NULL,
	[StockSumFc] numeric(19, 6) NULL,
	[StockSumSc] numeric(19, 6) NULL,
	[StckSumApp] numeric(19, 6) NULL,
	[StckAppFc] numeric(19, 6) NULL,
	[StckAppSc] numeric(19, 6) NULL,
	[ShipToCode] nvarchar(50) NULL,
	[ShipToDesc] nvarchar(254) NULL,
	[StckAppD] numeric(19, 6) NULL,
	[StckAppDFC] numeric(19, 6) NULL,
	[StckAppDSC] numeric(19, 6) NULL,
	[BasePrice] char(1) NULL,
	[GTotal] numeric(19, 6) NULL,
	[GTotalFC] numeric(19, 6) NULL,
	[GTotalSC] numeric(19, 6) NULL,
	[DistribExp] char(1) NULL,
	[DescOW] char(1) NULL,
	[DetailsOW] char(1) NULL,
	[GrossBase] smallint NULL,
	[VatWoDpm] numeric(19, 6) NULL,
	[VatWoDpmFc] numeric(19, 6) NULL,
	[VatWoDpmSc] numeric(19, 6) NULL,
	[CFOPCode] nvarchar(6) NULL,
	[CSTCode] nvarchar(6) NULL,
	[Usage] int NULL,
	[TaxOnly] char(1) NULL,
	[WtCalced] char(1) NULL,
	[QtyToShip] numeric(19, 6) NULL,
	[DelivrdQty] numeric(19, 6) NULL,
	[OrderedQty] numeric(19, 6) NULL,
	[CogsOcrCod] nvarchar(8) NULL,
	[CiOppLineN] int NULL,
	[CogsAcct] nvarchar(15) NULL,
	[ChgAsmBoMW] char(1) NULL,
	[ActDelDate] datetime NULL,
	[OcrCode2] nvarchar(8) NULL,
	[OcrCode3] nvarchar(8) NULL,
	[OcrCode4] nvarchar(8) NULL,
	[OcrCode5] nvarchar(8) NULL,
	[TaxDistSum] numeric(19, 6) NULL,
	[TaxDistSFC] numeric(19, 6) NULL,
	[TaxDistSSC] numeric(19, 6) NULL,
	[PostTax] char(1) NULL,
	[Excisable] char(1) NULL,
	[AssblValue] numeric(19, 6) NULL,
	[RG23APart1] int NULL,
	[RG23APart2] int NULL,
	[RG23CPart1] int NULL,
	[RG23CPart2] int NULL,
	[CogsOcrCo2] nvarchar(8) NULL,
	[CogsOcrCo3] nvarchar(8) NULL,
	[CogsOcrCo4] nvarchar(8) NULL,
	[CogsOcrCo5] nvarchar(8) NULL,
	[LnExcised] char(1) NULL,
	[LocCode] int NULL,
	[StockValue] numeric(19, 6) NULL,
	[GPTtlBasPr] numeric(19, 6) NULL,
	[unitMsr2] nvarchar(100) NULL,
	[NumPerMsr2] numeric(19, 6) NULL,
	[SpecPrice] char(1) NULL,
	[CSTfIPI] nvarchar(2) NULL,
	[CSTfPIS] nvarchar(2) NULL,
	[CSTfCOFINS] nvarchar(2) NULL,
	[ExLineNo] nvarchar(10) NULL,
	[isSrvCall] char(1) NULL,
	[PQTReqQty] numeric(19, 6) NULL,
	[PQTReqDate] datetime NULL,
	[PcDocType] int NULL,
	[PcQuantity] numeric(19, 6) NULL,
	[LinManClsd] char(1) NULL,
	[VatGrpSrc] char(1) NULL,
	[NoInvtryMv] char(1) NULL,
	[ActBaseEnt] int NULL,
	[ActBaseLn] int NULL,
	[ActBaseNum] int NULL,
	[OpenRtnQty] numeric(19, 6) NULL,
	[AgrNo] int NULL,
	[AgrLnNum] int NULL,
	[CredOrigin] char(1) NULL,
	[Surpluses] numeric(19, 6) NULL,
	[DefBreak] numeric(19, 6) NULL,
	[Shortages] numeric(19, 6) NULL,
	[UomEntry] int NULL,
	[UomEntry2] int NULL,
	[UomCode] nvarchar(20) NULL,
	[UomCode2] nvarchar(20) NULL,
	[FromWhsCod] nvarchar(8) NULL,
	[NeedQty] char(1) NULL,
	[PartRetire] char(1) NULL,
	[RetireQty] numeric(19, 6) NULL,
	[RetireAPC] numeric(19, 6) NULL,
	[RetirAPCFC] numeric(19, 6) NULL,
	[RetirAPCSC] numeric(19, 6) NULL,
	[InvQty] numeric(19, 6) NULL,
	[OpenInvQty] numeric(19, 6) NULL,
	[EnSetCost] char(1) NULL,
	[RetCost] numeric(19, 6) NULL,
	[Incoterms] int NULL,
	[TransMod] int NULL,
	[LineVendor] nvarchar(15) NULL,
	[DistribIS] char(1) NULL,
	[ISDistrb] numeric(19, 6) NULL,
	[ISDistrbFC] numeric(19, 6) NULL,
	[ISDistrbSC] numeric(19, 6) NULL,
	[IsByPrdct] char(1) NULL,
	[ItemType] int NULL,
	[PriceEdit] char(1) NULL,
	[PrntLnNum] int NULL,
	[LinePoPrss] char(1) NULL,
	[FreeChrgBP] char(1) NULL,
	[TaxRelev] char(1) NULL,
	[LegalText] nvarchar(254) NULL,
	[ThirdParty] char(1) NULL,
	[LicTradNum] nvarchar(32) NULL,
	[InvQtyOnly] char(1) NULL,
	[UnencReasn] int NULL,
	[ShipFromCo] nvarchar(50) NULL,
	[ShipFromDe] nvarchar(254) NULL,
	[FisrtBin] nvarchar(228) NULL,
	[AllocBinC] nvarchar(11) NULL,
	[ExpType] nvarchar(4) NULL,
	[ExpUUID] nvarchar(50) NULL,
	[ExpOpType] char(1) NULL,
	[DIOTNat] nvarchar(3) NULL,
	[MYFtype] nvarchar(2) NULL,
	[GPBefDisc] numeric(19, 6) NULL,
	[ReturnRsn] smallint NULL,
	[ReturnAct] smallint NULL,
	[StgSeqNum] int NULL,
	[StgEntry] int NULL,
	[StgDesc] nvarchar(100) NULL,
	[ItmTaxType] nvarchar(2) NULL,
	[SacEntry] int NULL,
	[NCMCode] int NULL,
	[HsnEntry] int NULL,
	[OriBAbsEnt] int NULL,
	[OriBLinNum] int NULL,
	[OriBDocTyp] int NULL,
	[U_TipoMonto] char(1) NULL,
	CONSTRAINT [POSDIN1_PRIMARY] PRIMARY KEY CLUSTERED ([C_Id] ASC)
);
GO

-- Tabla: POSIGN1
CREATE TABLE [dbo].[POSIGN1] (
	[C_Id] int IDENTITY(1,1) NOT NULL,
	[C_DocEntry] int NOT NULL,
	[DocEntry] int NOT NULL,
	[LineNum] int NOT NULL,
	[TargetType] int NULL DEFAULT ((-1)),
	[TrgetEntry] int NULL,
	[BaseRef] nvarchar(16) NULL,
	[BaseType] int NULL DEFAULT ((-1)),
	[BaseEntry] int NULL,
	[BaseLine] int NULL,
	[LineStatus] char(1) NULL DEFAULT ('O'),
	[ItemCode] nvarchar(50) NULL,
	[Dscription] nvarchar(100) NULL,
	[Quantity] numeric(19, 6) NULL,
	[ShipDate] datetime NULL,
	[OpenQty] numeric(19, 6) NULL,
	[Price] numeric(19, 6) NULL,
	[Currency] nvarchar(3) NULL,
	[Rate] numeric(19, 6) NULL,
	[DiscPrcnt] numeric(19, 6) NULL,
	[LineTotal] numeric(19, 6) NULL,
	[TotalFrgn] numeric(19, 6) NULL,
	[OpenSum] numeric(19, 6) NULL,
	[OpenSumFC] numeric(19, 6) NULL,
	[VendorNum] nvarchar(50) NULL,
	[SerialNum] nvarchar(17) NULL,
	[WhsCode] nvarchar(8) NULL,
	[SlpCode] int NULL DEFAULT ((-1)),
	[Commission] numeric(19, 6) NULL,
	[TreeType] char(1) NULL DEFAULT ('N'),
	[AcctCode] nvarchar(15) NULL,
	[TaxStatus] char(1) NULL,
	[GrossBuyPr] numeric(19, 6) NULL,
	[PriceBefDi] numeric(19, 6) NULL,
	[DocDate] datetime NULL,
	[Flags] int NULL DEFAULT ((0)),
	[OpenCreQty] numeric(19, 6) NULL,
	[UseBaseUn] char(1) NULL DEFAULT ('Y'),
	[SubCatNum] nvarchar(50) NULL,
	[BaseCard] nvarchar(15) NULL,
	[TotalSumSy] numeric(19, 6) NULL,
	[OpenSumSys] numeric(19, 6) NULL,
	[InvntSttus] char(1) NULL DEFAULT ('O'),
	[OcrCode] nvarchar(8) NULL,
	[Project] nvarchar(20) NULL,
	[CodeBars] nvarchar(254) NULL,
	[VatPrcnt] numeric(19, 6) NULL,
	[VatGroup] nvarchar(8) NULL,
	[PriceAfVAT] numeric(19, 6) NULL,
	[Height1] numeric(19, 6) NULL,
	[Hght1Unit] smallint NULL,
	[Height2] numeric(19, 6) NULL,
	[Hght2Unit] smallint NULL,
	[Width1] numeric(19, 6) NULL,
	[Wdth1Unit] smallint NULL,
	[Width2] numeric(19, 6) NULL,
	[Wdth2Unit] smallint NULL,
	[Length1] numeric(19, 6) NULL,
	[Len1Unit] smallint NULL,
	[length2] numeric(19, 6) NULL,
	[Len2Unit] smallint NULL,
	[Volume] numeric(19, 6) NULL,
	[VolUnit] smallint NULL,
	[Weight1] numeric(19, 6) NULL,
	[Wght1Unit] smallint NULL,
	[Weight2] numeric(19, 6) NULL,
	[Wght2Unit] smallint NULL,
	[Factor1] numeric(19, 6) NULL,
	[Factor2] numeric(19, 6) NULL,
	[Factor3] numeric(19, 6) NULL,
	[Factor4] numeric(19, 6) NULL,
	[PackQty] numeric(19, 6) NULL,
	[UpdInvntry] char(1) NULL DEFAULT ('Y'),
	[BaseDocNum] int NULL,
	[BaseAtCard] nvarchar(100) NULL,
	[SWW] nvarchar(16) NULL,
	[VatSum] numeric(19, 6) NULL,
	[VatSumFrgn] numeric(19, 6) NULL,
	[VatSumSy] numeric(19, 6) NULL,
	[FinncPriod] int NULL,
	[ObjType] nvarchar(20) NULL DEFAULT ('59'),
	[LogInstanc] int NULL DEFAULT ((0)),
	[BlockNum] nvarchar(100) NULL,
	[ImportLog] nvarchar(20) NULL,
	[DedVatSum] numeric(19, 6) NULL,
	[DedVatSumF] numeric(19, 6) NULL,
	[DedVatSumS] numeric(19, 6) NULL,
	[IsAqcuistn] char(1) NULL DEFAULT ('N'),
	[DistribSum] numeric(19, 6) NULL,
	[DstrbSumFC] numeric(19, 6) NULL,
	[DstrbSumSC] numeric(19, 6) NULL,
	[GrssProfit] numeric(19, 6) NULL,
	[GrssProfSC] numeric(19, 6) NULL,
	[GrssProfFC] numeric(19, 6) NULL,
	[VisOrder] int NULL,
	[INMPrice] numeric(19, 6) NULL,
	[PoTrgNum] int NULL,
	[PoTrgEntry] nvarchar(11) NULL,
	[DropShip] char(1) NULL DEFAULT ('N'),
	[PoLineNum] int NULL,
	[Address] nvarchar(254) NULL,
	[TaxCode] nvarchar(8) NULL,
	[TaxType] char(1) NULL,
	[OrigItem] nvarchar(50) NULL,
	[BackOrdr] char(1) NULL,
	[FreeTxt] nvarchar(100) NULL,
	[PickStatus] char(1) NULL DEFAULT ('N'),
	[PickOty] numeric(19, 6) NULL,
	[PickIdNo] int NULL,
	[TrnsCode] smallint NULL DEFAULT ((-1)),
	[VatAppld] numeric(19, 6) NULL,
	[VatAppldFC] numeric(19, 6) NULL,
	[VatAppldSC] numeric(19, 6) NULL,
	[BaseQty] numeric(19, 6) NULL,
	[BaseOpnQty] numeric(19, 6) NULL,
	[VatDscntPr] numeric(19, 6) NULL,
	[WtLiable] char(1) NULL,
	[DeferrTax] char(1) NULL DEFAULT ('N'),
	[EquVatPer] numeric(19, 6) NULL,
	[EquVatSum] numeric(19, 6) NULL,
	[EquVatSumF] numeric(19, 6) NULL,
	[EquVatSumS] numeric(19, 6) NULL,
	[LineVat] numeric(19, 6) NULL,
	[LineVatlF] numeric(19, 6) NULL,
	[LineVatS] numeric(19, 6) NULL,
	[unitMsr] nvarchar(100) NULL,
	[NumPerMsr] numeric(19, 6) NULL,
	[CEECFlag] char(1) NULL DEFAULT ('S'),
	[ToStock] numeric(19, 6) NULL,
	[ToDiff] numeric(19, 6) NULL,
	[ExciseAmt] numeric(19, 6) NULL,
	[TaxPerUnit] numeric(19, 6) NULL,
	[TotInclTax] numeric(19, 6) NULL,
	[CountryOrg] nvarchar(3) NULL,
	[StckDstSum] numeric(19, 6) NULL,
	[ReleasQtty] numeric(19, 6) NULL,
	[LineType] char(1) NULL DEFAULT ('R'),
	[TranType] char(1) NULL,
	[Text] ntext NULL,
	[OwnerCode] int NULL,
	[StockPrice] numeric(19, 6) NULL,
	[ConsumeFCT] char(1) NULL,
	[LstByDsSum] numeric(19, 6) NULL,
	[StckINMPr] numeric(19, 6) NULL,
	[LstBINMPr] numeric(19, 6) NULL,
	[StckDstFc] numeric(19, 6) NULL,
	[StckDstSc] numeric(19, 6) NULL,
	[LstByDsFc] numeric(19, 6) NULL,
	[LstByDsSc] numeric(19, 6) NULL,
	[StockSum] numeric(19, 6) NULL,
	[StockSumFc] numeric(19, 6) NULL,
	[StockSumSc] numeric(19, 6) NULL,
	[StckSumApp] numeric(19, 6) NULL,
	[StckAppFc] numeric(19, 6) NULL,
	[StckAppSc] numeric(19, 6) NULL,
	[ShipToCode] nvarchar(50) NULL,
	[ShipToDesc] nvarchar(254) NULL,
	[StckAppD] numeric(19, 6) NULL,
	[StckAppDFC] numeric(19, 6) NULL,
	[StckAppDSC] numeric(19, 6) NULL,
	[BasePrice] char(1) NULL DEFAULT ('E'),
	[GTotal] numeric(19, 6) NULL,
	[GTotalFC] numeric(19, 6) NULL,
	[GTotalSC] numeric(19, 6) NULL,
	[DistribExp] char(1) NULL,
	[DescOW] char(1) NULL DEFAULT ('N'),
	[DetailsOW] char(1) NULL DEFAULT ('N'),
	[GrossBase] smallint NULL,
	[VatWoDpm] numeric(19, 6) NULL,
	[VatWoDpmFc] numeric(19, 6) NULL,
	[VatWoDpmSc] numeric(19, 6) NULL,
	[CFOPCode] nvarchar(6) NULL,
	[CSTCode] nvarchar(6) NULL,
	[Usage] int NULL,
	[TaxOnly] char(1) NULL,
	[WtCalced] char(1) NULL DEFAULT ('N'),
	[QtyToShip] numeric(19, 6) NULL,
	[DelivrdQty] numeric(19, 6) NULL,
	[OrderedQty] numeric(19, 6) NULL,
	[CogsOcrCod] nvarchar(8) NULL,
	[CiOppLineN] int NULL DEFAULT ((-1)),
	[CogsAcct] nvarchar(15) NULL,
	[ChgAsmBoMW] char(1) NULL,
	[ActDelDate] datetime NULL,
	[OcrCode2] nvarchar(8) NULL,
	[OcrCode3] nvarchar(8) NULL,
	[OcrCode4] nvarchar(8) NULL,
	[OcrCode5] nvarchar(8) NULL,
	[TaxDistSum] numeric(19, 6) NULL,
	[TaxDistSFC] numeric(19, 6) NULL,
	[TaxDistSSC] numeric(19, 6) NULL,
	[PostTax] char(1) NULL DEFAULT ('Y'),
	[Excisable] char(1) NULL,
	[AssblValue] numeric(19, 6) NULL,
	[RG23APart1] int NULL,
	[RG23APart2] int NULL,
	[RG23CPart1] int NULL,
	[RG23CPart2] int NULL,
	[CogsOcrCo2] nvarchar(8) NULL,
	[CogsOcrCo3] nvarchar(8) NULL,
	[CogsOcrCo4] nvarchar(8) NULL,
	[CogsOcrCo5] nvarchar(8) NULL,
	[LnExcised] char(1) NULL,
	[LocCode] int NULL,
	[StockValue] numeric(19, 6) NULL,
	[GPTtlBasPr] numeric(19, 6) NULL,
	[unitMsr2] nvarchar(100) NULL,
	[NumPerMsr2] numeric(19, 6) NULL,
	[SpecPrice] char(1) NULL DEFAULT ('N'),
	[CSTfIPI] nvarchar(2) NULL,
	[CSTfPIS] nvarchar(2) NULL,
	[CSTfCOFINS] nvarchar(2) NULL,
	[ExLineNo] nvarchar(10) NULL,
	[isSrvCall] char(1) NULL DEFAULT ('N'),
	[PQTReqQty] numeric(19, 6) NULL,
	[PQTReqDate] datetime NULL,
	[PcDocType] int NULL DEFAULT ((-1)),
	[PcQuantity] numeric(19, 6) NULL,
	[LinManClsd] char(1) NULL DEFAULT ('N'),
	[VatGrpSrc] char(1) NULL DEFAULT ('N'),
	[NoInvtryMv] char(1) NULL DEFAULT ('N'),
	[ActBaseEnt] int NULL,
	[ActBaseLn] int NULL,
	[ActBaseNum] int NULL,
	[OpenRtnQty] numeric(19, 6) NULL,
	[AgrNo] int NULL,
	[AgrLnNum] int NULL,
	[CredOrigin] char(1) NULL,
	[Surpluses] numeric(19, 6) NULL,
	[DefBreak] numeric(19, 6) NULL,
	[Shortages] numeric(19, 6) NULL,
	[UomEntry] int NULL DEFAULT ((0)),
	[UomEntry2] int NULL DEFAULT ((0)),
	[UomCode] nvarchar(20) NULL,
	[UomCode2] nvarchar(20) NULL,
	[FromWhsCod] nvarchar(8) NULL,
	[NeedQty] char(1) NULL DEFAULT ('N'),
	[PartRetire] char(1) NULL DEFAULT ('N'),
	[RetireQty] numeric(19, 6) NULL,
	[RetireAPC] numeric(19, 6) NULL,
	[RetirAPCFC] numeric(19, 6) NULL,
	[RetirAPCSC] numeric(19, 6) NULL,
	[InvQty] numeric(19, 6) NULL,
	[OpenInvQty] numeric(19, 6) NULL,
	[EnSetCost] char(1) NULL DEFAULT ('N'),
	[RetCost] numeric(19, 6) NULL,
	[Incoterms] int NULL DEFAULT ((0)),
	[TransMod] int NULL DEFAULT ((0)),
	[LineVendor] nvarchar(15) NULL,
	[DistribIS] char(1) NULL DEFAULT ('N'),
	[ISDistrb] numeric(19, 6) NULL,
	[ISDistrbFC] numeric(19, 6) NULL,
	[ISDistrbSC] numeric(19, 6) NULL,
	[IsByPrdct] char(1) NULL DEFAULT ('N'),
	[ItemType] int NULL DEFAULT ((4)),
	[PriceEdit] char(1) NULL DEFAULT ('N'),
	[PrntLnNum] int NULL,
	[LinePoPrss] char(1) NULL DEFAULT ('N'),
	[FreeChrgBP] char(1) NULL DEFAULT ('N'),
	[TaxRelev] char(1) NULL DEFAULT ('Y'),
	[LegalText] nvarchar(254) NULL,
	[ThirdParty] char(1) NULL DEFAULT ('N'),
	[LicTradNum] nvarchar(32) NULL,
	[InvQtyOnly] char(1) NULL DEFAULT ('N'),
	[UnencReasn] int NULL,
	[ShipFromCo] nvarchar(50) NULL,
	[ShipFromDe] nvarchar(254) NULL,
	[FisrtBin] nvarchar(228) NULL,
	[AllocBinC] nvarchar(11) NULL,
	[ExpType] nvarchar(4) NULL,
	[ExpUUID] nvarchar(50) NULL,
	[ExpOpType] char(1) NULL,
	[DIOTNat] nvarchar(3) NULL,
	[MYFtype] nvarchar(2) NULL,
	[GPBefDisc] numeric(19, 6) NULL,
	[ReturnRsn] smallint NULL DEFAULT ((-1)),
	[ReturnAct] smallint NULL DEFAULT ((-1)),
	[StgSeqNum] int NULL,
	[StgEntry] int NULL,
	[StgDesc] nvarchar(100) NULL,
	[ItmTaxType] nvarchar(2) NULL,
	[SacEntry] int NULL,
	[NCMCode] int NULL DEFAULT ((-1)),
	[HsnEntry] int NULL,
	[OriBAbsEnt] int NULL,
	[OriBLinNum] int NULL,
	[OriBDocTyp] int NULL,
	[U_TipoMonto] char(1) NULL DEFAULT ('B'),
	CONSTRAINT [PK_POSIGN1_146] PRIMARY KEY CLUSTERED ([C_Id] ASC)
);
GO

-- Tabla: POSINV1
CREATE TABLE [dbo].[POSINV1] (
	[C_Id] int IDENTITY(1,1) NOT NULL,
	[C_DocEntry] int NOT NULL,
	[DocEntry] int NULL,
	[LineNum] int NULL,
	[TargetType] int NULL,
	[TrgetEntry] int NULL,
	[BaseRef] nvarchar(16) NULL,
	[BaseType] int NULL,
	[BaseEntry] int NULL,
	[BaseLine] int NULL,
	[LineStatus] char(1) NULL,
	[ItemCode] nvarchar(50) NULL,
	[Dscription] nvarchar(100) NULL,
	[Quantity] numeric(19, 6) NULL,
	[ShipDate] datetime NULL,
	[OpenQty] numeric(19, 6) NULL,
	[Price] numeric(19, 6) NULL,
	[Currency] nvarchar(3) NULL,
	[Rate] numeric(19, 6) NULL,
	[DiscPrcnt] numeric(19, 6) NULL,
	[LineTotal] numeric(19, 6) NULL,
	[TotalFrgn] numeric(19, 6) NULL,
	[OpenSum] numeric(19, 6) NULL,
	[OpenSumFC] numeric(19, 6) NULL,
	[VendorNum] nvarchar(50) NULL,
	[SerialNum] nvarchar(17) NULL,
	[WhsCode] nvarchar(8) NULL,
	[SlpCode] int NULL,
	[Commission] numeric(19, 6) NULL,
	[TreeType] char(1) NULL,
	[AcctCode] nvarchar(15) NULL,
	[TaxStatus] char(1) NULL,
	[GrossBuyPr] numeric(19, 6) NULL,
	[PriceBefDi] numeric(19, 6) NULL,
	[DocDate] datetime NULL,
	[Flags] int NULL,
	[OpenCreQty] numeric(19, 6) NULL,
	[UseBaseUn] char(1) NULL,
	[SubCatNum] nvarchar(50) NULL,
	[BaseCard] nvarchar(15) NULL,
	[TotalSumSy] numeric(19, 6) NULL,
	[OpenSumSys] numeric(19, 6) NULL,
	[InvntSttus] char(1) NULL,
	[OcrCode] nvarchar(8) NULL,
	[Project] nvarchar(20) NULL,
	[CodeBars] nvarchar(254) NULL,
	[VatPrcnt] numeric(19, 6) NULL,
	[VatGroup] nvarchar(8) NULL,
	[PriceAfVAT] numeric(19, 6) NULL,
	[Height1] numeric(19, 6) NULL,
	[Hght1Unit] smallint NULL,
	[Height2] numeric(19, 6) NULL,
	[Hght2Unit] smallint NULL,
	[Width1] numeric(19, 6) NULL,
	[Wdth1Unit] smallint NULL,
	[Width2] numeric(19, 6) NULL,
	[Wdth2Unit] smallint NULL,
	[Length1] numeric(19, 6) NULL,
	[Len1Unit] smallint NULL,
	[length2] numeric(19, 6) NULL,
	[Len2Unit] smallint NULL,
	[Volume] numeric(19, 6) NULL,
	[VolUnit] smallint NULL,
	[Weight1] numeric(19, 6) NULL,
	[Wght1Unit] smallint NULL,
	[Weight2] numeric(19, 6) NULL,
	[Wght2Unit] smallint NULL,
	[Factor1] numeric(19, 6) NULL,
	[Factor2] numeric(19, 6) NULL,
	[Factor3] numeric(19, 6) NULL,
	[Factor4] numeric(19, 6) NULL,
	[PackQty] numeric(19, 6) NULL,
	[UpdInvntry] char(1) NULL,
	[BaseDocNum] int NULL,
	[BaseAtCard] nvarchar(100) NULL,
	[SWW] nvarchar(16) NULL,
	[VatSum] numeric(19, 6) NULL,
	[VatSumFrgn] numeric(19, 6) NULL,
	[VatSumSy] numeric(19, 6) NULL,
	[FinncPriod] int NULL,
	[ObjType] nvarchar(20) NULL,
	[LogInstanc] int NULL,
	[BlockNum] nvarchar(100) NULL,
	[ImportLog] nvarchar(20) NULL,
	[DedVatSum] numeric(19, 6) NULL,
	[DedVatSumF] numeric(19, 6) NULL,
	[DedVatSumS] numeric(19, 6) NULL,
	[IsAqcuistn] char(1) NULL,
	[DistribSum] numeric(19, 6) NULL,
	[DstrbSumFC] numeric(19, 6) NULL,
	[DstrbSumSC] numeric(19, 6) NULL,
	[GrssProfit] numeric(19, 6) NULL,
	[GrssProfSC] numeric(19, 6) NULL,
	[GrssProfFC] numeric(19, 6) NULL,
	[VisOrder] int NULL,
	[INMPrice] numeric(19, 6) NULL,
	[PoTrgNum] int NULL,
	[PoTrgEntry] nvarchar(11) NULL,
	[DropShip] char(1) NULL,
	[PoLineNum] int NULL,
	[Address] nvarchar(254) NULL,
	[TaxCode] nvarchar(8) NULL,
	[TaxType] char(1) NULL,
	[OrigItem] nvarchar(50) NULL,
	[BackOrdr] char(1) NULL,
	[FreeTxt] nvarchar(100) NULL,
	[PickStatus] char(1) NULL,
	[PickOty] numeric(19, 6) NULL,
	[PickIdNo] int NULL,
	[TrnsCode] smallint NULL,
	[VatAppld] numeric(19, 6) NULL,
	[VatAppldFC] numeric(19, 6) NULL,
	[VatAppldSC] numeric(19, 6) NULL,
	[BaseQty] numeric(19, 6) NULL,
	[BaseOpnQty] numeric(19, 6) NULL,
	[VatDscntPr] numeric(19, 6) NULL,
	[WtLiable] char(1) NULL,
	[DeferrTax] char(1) NULL,
	[EquVatPer] numeric(19, 6) NULL,
	[EquVatSum] numeric(19, 6) NULL,
	[EquVatSumF] numeric(19, 6) NULL,
	[EquVatSumS] numeric(19, 6) NULL,
	[LineVat] numeric(19, 6) NULL,
	[LineVatlF] numeric(19, 6) NULL,
	[LineVatS] numeric(19, 6) NULL,
	[unitMsr] nvarchar(100) NOT NULL DEFAULT (N'UND'),
	[NumPerMsr] numeric(19, 6) NULL,
	[CEECFlag] char(1) NULL,
	[ToStock] numeric(19, 6) NULL,
	[ToDiff] numeric(19, 6) NULL,
	[ExciseAmt] numeric(19, 6) NULL,
	[TaxPerUnit] numeric(19, 6) NULL,
	[TotInclTax] numeric(19, 6) NULL,
	[CountryOrg] nvarchar(3) NULL,
	[StckDstSum] numeric(19, 6) NULL,
	[ReleasQtty] numeric(19, 6) NULL,
	[LineType] char(1) NULL,
	[TranType] char(1) NULL,
	[Text] ntext NULL,
	[OwnerCode] int NULL,
	[StockPrice] numeric(19, 6) NULL,
	[ConsumeFCT] char(1) NULL,
	[LstByDsSum] numeric(19, 6) NULL,
	[StckINMPr] numeric(19, 6) NULL,
	[LstBINMPr] numeric(19, 6) NULL,
	[StckDstFc] numeric(19, 6) NULL,
	[StckDstSc] numeric(19, 6) NULL,
	[LstByDsFc] numeric(19, 6) NULL,
	[LstByDsSc] numeric(19, 6) NULL,
	[StockSum] numeric(19, 6) NULL,
	[StockSumFc] numeric(19, 6) NULL,
	[StockSumSc] numeric(19, 6) NULL,
	[StckSumApp] numeric(19, 6) NULL,
	[StckAppFc] numeric(19, 6) NULL,
	[StckAppSc] numeric(19, 6) NULL,
	[ShipToCode] nvarchar(50) NULL,
	[ShipToDesc] nvarchar(254) NULL,
	[StckAppD] numeric(19, 6) NULL,
	[StckAppDFC] numeric(19, 6) NULL,
	[StckAppDSC] numeric(19, 6) NULL,
	[BasePrice] char(1) NULL,
	[GTotal] numeric(19, 6) NULL,
	[GTotalFC] numeric(19, 6) NULL,
	[GTotalSC] numeric(19, 6) NULL,
	[DistribExp] char(1) NULL,
	[DescOW] char(1) NULL,
	[DetailsOW] char(1) NULL,
	[GrossBase] smallint NULL,
	[VatWoDpm] numeric(19, 6) NULL,
	[VatWoDpmFc] numeric(19, 6) NULL,
	[VatWoDpmSc] numeric(19, 6) NULL,
	[CFOPCode] nvarchar(6) NULL,
	[CSTCode] nvarchar(6) NULL,
	[Usage] int NULL,
	[TaxOnly] char(1) NULL,
	[WtCalced] char(1) NULL,
	[QtyToShip] numeric(19, 6) NULL,
	[DelivrdQty] numeric(19, 6) NULL,
	[OrderedQty] numeric(19, 6) NULL,
	[CogsOcrCod] nvarchar(8) NULL,
	[CiOppLineN] int NULL,
	[CogsAcct] nvarchar(15) NULL,
	[ChgAsmBoMW] char(1) NULL,
	[ActDelDate] datetime NULL,
	[OcrCode2] nvarchar(8) NULL,
	[OcrCode3] nvarchar(8) NULL,
	[OcrCode4] nvarchar(8) NULL,
	[OcrCode5] nvarchar(8) NULL,
	[TaxDistSum] numeric(19, 6) NULL,
	[TaxDistSFC] numeric(19, 6) NULL,
	[TaxDistSSC] numeric(19, 6) NULL,
	[PostTax] char(1) NULL,
	[Excisable] char(1) NULL,
	[AssblValue] numeric(19, 6) NULL,
	[RG23APart1] int NULL,
	[RG23APart2] int NULL,
	[RG23CPart1] int NULL,
	[RG23CPart2] int NULL,
	[CogsOcrCo2] nvarchar(8) NULL,
	[CogsOcrCo3] nvarchar(8) NULL,
	[CogsOcrCo4] nvarchar(8) NULL,
	[CogsOcrCo5] nvarchar(8) NULL,
	[LnExcised] char(1) NULL,
	[LocCode] int NULL,
	[StockValue] numeric(19, 6) NULL,
	[GPTtlBasPr] numeric(19, 6) NULL,
	[unitMsr2] nvarchar(100) NULL,
	[NumPerMsr2] numeric(19, 6) NULL,
	[SpecPrice] char(1) NULL,
	[CSTfIPI] nvarchar(2) NULL,
	[CSTfPIS] nvarchar(2) NULL,
	[CSTfCOFINS] nvarchar(2) NULL,
	[ExLineNo] nvarchar(10) NULL,
	[isSrvCall] char(1) NULL,
	[PQTReqQty] numeric(19, 6) NULL,
	[PQTReqDate] datetime NULL,
	[PcDocType] int NULL,
	[PcQuantity] numeric(19, 6) NULL,
	[LinManClsd] char(1) NULL,
	[VatGrpSrc] char(1) NULL,
	[NoInvtryMv] char(1) NULL,
	[ActBaseEnt] int NULL,
	[ActBaseLn] int NULL,
	[ActBaseNum] int NULL,
	[OpenRtnQty] numeric(19, 6) NULL,
	[AgrNo] int NULL,
	[AgrLnNum] int NULL,
	[CredOrigin] char(1) NULL,
	[Surpluses] numeric(19, 6) NULL,
	[DefBreak] numeric(19, 6) NULL,
	[Shortages] numeric(19, 6) NULL,
	[UomEntry] int NULL,
	[UomEntry2] int NULL,
	[UomCode] nvarchar(20) NULL,
	[UomCode2] nvarchar(20) NULL,
	[FromWhsCod] nvarchar(8) NULL,
	[NeedQty] char(1) NULL,
	[PartRetire] char(1) NULL,
	[RetireQty] numeric(19, 6) NULL,
	[RetireAPC] numeric(19, 6) NULL,
	[RetirAPCFC] numeric(19, 6) NULL,
	[RetirAPCSC] numeric(19, 6) NULL,
	[InvQty] numeric(19, 6) NULL,
	[OpenInvQty] numeric(19, 6) NULL,
	[EnSetCost] char(1) NULL,
	[RetCost] numeric(19, 6) NULL,
	[Incoterms] int NULL,
	[TransMod] int NULL,
	[LineVendor] nvarchar(15) NULL,
	[DistribIS] char(1) NULL,
	[ISDistrb] numeric(19, 6) NULL,
	[ISDistrbFC] numeric(19, 6) NULL,
	[ISDistrbSC] numeric(19, 6) NULL,
	[IsByPrdct] char(1) NULL,
	[ItemType] int NULL,
	[PriceEdit] char(1) NULL,
	[PrntLnNum] int NULL,
	[LinePoPrss] char(1) NULL,
	[FreeChrgBP] char(1) NULL,
	[TaxRelev] char(1) NULL,
	[LegalText] nvarchar(254) NULL,
	[ThirdParty] char(1) NULL,
	[LicTradNum] nvarchar(32) NULL,
	[InvQtyOnly] char(1) NULL,
	[UnencReasn] int NULL,
	[ShipFromCo] nvarchar(50) NULL,
	[ShipFromDe] nvarchar(254) NULL,
	[FisrtBin] nvarchar(228) NULL,
	[AllocBinC] nvarchar(11) NULL,
	[ExpType] nvarchar(4) NULL,
	[ExpUUID] nvarchar(50) NULL,
	[ExpOpType] char(1) NULL,
	[DIOTNat] nvarchar(3) NULL,
	[MYFtype] nvarchar(2) NULL,
	[GPBefDisc] numeric(19, 6) NULL,
	[ReturnRsn] smallint NULL,
	[ReturnAct] smallint NULL,
	[StgSeqNum] int NULL,
	[StgEntry] int NULL,
	[StgDesc] nvarchar(100) NULL,
	[ItmTaxType] nvarchar(2) NULL,
	[SacEntry] int NULL,
	[NCMCode] int NULL,
	[HsnEntry] int NULL,
	[OriBAbsEnt] int NULL,
	[OriBLinNum] int NULL,
	[OriBDocTyp] int NULL,
	[U_TipoMonto] char(1) NULL,
	[C_Posted] nchar(1) NULL DEFAULT ('N'),
	[C_Puntos] numeric(18, 2) NULL DEFAULT ((0)),
	CONSTRAINT [POSINV1_PRIMARY] PRIMARY KEY CLUSTERED ([C_Id] ASC)
);
GO

-- Tabla: posITM1
CREATE TABLE [dbo].[posITM1] (
	[ItemCode] nvarchar(50) NOT NULL,
	[PriceList] smallint NOT NULL,
	[Price] numeric(19, 6) NULL,
	[Currency] nvarchar(3) NULL,
	[Ovrwritten] char(1) NULL DEFAULT ('N'),
	[Factor] numeric(19, 6) NULL,
	[LogInstanc] int NULL DEFAULT ((0)),
	[ObjType] nvarchar(20) NULL DEFAULT ('4'),
	[AddPrice1] numeric(19, 6) NULL,
	[Currency1] nvarchar(3) NULL,
	[AddPrice2] numeric(19, 6) NULL,
	[Currency2] nvarchar(3) NULL,
	[Ovrwrite1] char(1) NULL DEFAULT ('N'),
	[Ovrwrite2] char(1) NULL DEFAULT ('N'),
	[BasePLNum] smallint NULL,
	[UomEntry] int NULL,
	[PriceType] char(1) NULL DEFAULT ('M'),
	CONSTRAINT [ITM1_PRIMARY] PRIMARY KEY CLUSTERED ([ItemCode] ASC, [PriceList] ASC)
);
GO

-- Tabla: posOCRD
CREATE TABLE [dbo].[posOCRD] (
	[CardCode] nvarchar(15) NOT NULL,
	[CardName] nvarchar(100) NULL,
	[CardType] char(1) NULL DEFAULT ('C'),
	[GroupCode] smallint NULL,
	[CmpPrivate] char(1) NULL DEFAULT ('C'),
	[Address] nvarchar(100) NULL,
	[ZipCode] nvarchar(20) NULL,
	[MailAddres] nvarchar(100) NULL,
	[MailZipCod] nvarchar(20) NULL,
	[Phone1] nvarchar(20) NULL,
	[Phone2] nvarchar(20) NULL,
	[Fax] nvarchar(20) NULL,
	[CntctPrsn] nvarchar(90) NULL,
	[Notes] nvarchar(100) NULL,
	[Balance] numeric(19, 6) NULL,
	[ChecksBal] numeric(19, 6) NULL,
	[DNotesBal] numeric(19, 6) NULL,
	[OrdersBal] numeric(19, 6) NULL,
	[GroupNum] smallint NULL DEFAULT ((-1)),
	[CreditLine] numeric(19, 6) NULL,
	[DebtLine] numeric(19, 6) NULL,
	[Discount] numeric(19, 6) NULL,
	[VatStatus] char(1) NULL DEFAULT ('Y'),
	[LicTradNum] nvarchar(32) NULL,
	[DdctStatus] char(1) NULL DEFAULT ('N'),
	[DdctPrcnt] numeric(19, 6) NULL,
	[ValidUntil] datetime NULL,
	[Chrctrstcs] int NULL,
	[ExMatchNum] int NULL,
	[InMatchNum] int NULL,
	[ListNum] smallint NULL,
	[DNoteBalFC] numeric(19, 6) NULL,
	[OrderBalFC] numeric(19, 6) NULL,
	[DNoteBalSy] numeric(19, 6) NULL,
	[OrderBalSy] numeric(19, 6) NULL,
	[Transfered] char(1) NULL DEFAULT ('N'),
	[BalTrnsfrd] char(1) NULL,
	[IntrstRate] numeric(19, 6) NULL,
	[Commission] numeric(19, 6) NULL,
	[CommGrCode] smallint NULL,
	[Free_Text] ntext NULL,
	[SlpCode] int NULL,
	[PrevYearAc] char(1) NULL,
	[Currency] nvarchar(3) NULL,
	[RateDifAct] nvarchar(15) NULL,
	[BalanceSys] numeric(19, 6) NULL,
	[BalanceFC] numeric(19, 6) NULL,
	[Protected] char(1) NULL,
	[Cellular] nvarchar(50) NULL,
	[AvrageLate] smallint NULL,
	[City] nvarchar(100) NULL,
	[County] nvarchar(100) NULL,
	[Country] nvarchar(3) NULL,
	[MailCity] nvarchar(100) NULL,
	[MailCounty] nvarchar(100) NULL,
	[MailCountr] nvarchar(3) NULL,
	[E_Mail] nvarchar(100) NULL,
	[Picture] nvarchar(200) NULL,
	[DflAccount] nvarchar(50) NULL,
	[DflBranch] nvarchar(50) NULL,
	[BankCode] nvarchar(30) NULL,
	[AddID] nvarchar(64) NULL,
	[Pager] nvarchar(30) NULL,
	[FatherCard] nvarchar(15) NULL,
	[CardFName] nvarchar(100) NULL,
	[FatherType] char(1) NULL,
	[QryGroup1] char(1) NULL DEFAULT ('N'),
	[QryGroup2] char(1) NULL DEFAULT ('N'),
	[QryGroup3] char(1) NULL DEFAULT ('N'),
	[QryGroup4] char(1) NULL DEFAULT ('N'),
	[QryGroup5] char(1) NULL DEFAULT ('N'),
	[QryGroup6] char(1) NULL DEFAULT ('N'),
	[QryGroup7] char(1) NULL DEFAULT ('N'),
	[QryGroup8] char(1) NULL DEFAULT ('N'),
	[QryGroup9] char(1) NULL DEFAULT ('N'),
	[QryGroup10] char(1) NULL DEFAULT ('N'),
	[QryGroup11] char(1) NULL DEFAULT ('N'),
	[QryGroup12] char(1) NULL DEFAULT ('N'),
	[QryGroup13] char(1) NULL DEFAULT ('N'),
	[QryGroup14] char(1) NULL DEFAULT ('N'),
	[QryGroup15] char(1) NULL DEFAULT ('N'),
	[QryGroup16] char(1) NULL DEFAULT ('N'),
	[QryGroup17] char(1) NULL DEFAULT ('N'),
	[QryGroup18] char(1) NULL DEFAULT ('N'),
	[QryGroup19] char(1) NULL DEFAULT ('N'),
	[QryGroup20] char(1) NULL DEFAULT ('N'),
	[QryGroup21] char(1) NULL DEFAULT ('N'),
	[QryGroup22] char(1) NULL DEFAULT ('N'),
	[QryGroup23] char(1) NULL DEFAULT ('N'),
	[QryGroup24] char(1) NULL DEFAULT ('N'),
	[QryGroup25] char(1) NULL DEFAULT ('N'),
	[QryGroup26] char(1) NULL DEFAULT ('N'),
	[QryGroup27] char(1) NULL DEFAULT ('N'),
	[QryGroup28] char(1) NULL DEFAULT ('N'),
	[QryGroup29] char(1) NULL DEFAULT ('N'),
	[QryGroup30] char(1) NULL DEFAULT ('N'),
	[QryGroup31] char(1) NULL DEFAULT ('N'),
	[QryGroup32] char(1) NULL DEFAULT ('N'),
	[QryGroup33] char(1) NULL DEFAULT ('N'),
	[QryGroup34] char(1) NULL DEFAULT ('N'),
	[QryGroup35] char(1) NULL DEFAULT ('N'),
	[QryGroup36] char(1) NULL DEFAULT ('N'),
	[QryGroup37] char(1) NULL DEFAULT ('N'),
	[QryGroup38] char(1) NULL DEFAULT ('N'),
	[QryGroup39] char(1) NULL DEFAULT ('N'),
	[QryGroup40] char(1) NULL DEFAULT ('N'),
	[QryGroup41] char(1) NULL DEFAULT ('N'),
	[QryGroup42] char(1) NULL DEFAULT ('N'),
	[QryGroup43] char(1) NULL DEFAULT ('N'),
	[QryGroup44] char(1) NULL DEFAULT ('N'),
	[QryGroup45] char(1) NULL DEFAULT ('N'),
	[QryGroup46] char(1) NULL DEFAULT ('N'),
	[QryGroup47] char(1) NULL DEFAULT ('N'),
	[QryGroup48] char(1) NULL DEFAULT ('N'),
	[QryGroup49] char(1) NULL DEFAULT ('N'),
	[QryGroup50] char(1) NULL DEFAULT ('N'),
	[QryGroup51] char(1) NULL DEFAULT ('N'),
	[QryGroup52] char(1) NULL DEFAULT ('N'),
	[QryGroup53] char(1) NULL DEFAULT ('N'),
	[QryGroup54] char(1) NULL DEFAULT ('N'),
	[QryGroup55] char(1) NULL DEFAULT ('N'),
	[QryGroup56] char(1) NULL DEFAULT ('N'),
	[QryGroup57] char(1) NULL DEFAULT ('N'),
	[QryGroup58] char(1) NULL DEFAULT ('N'),
	[QryGroup59] char(1) NULL DEFAULT ('N'),
	[QryGroup60] char(1) NULL DEFAULT ('N'),
	[QryGroup61] char(1) NULL DEFAULT ('N'),
	[QryGroup62] char(1) NULL DEFAULT ('N'),
	[QryGroup63] char(1) NULL DEFAULT ('N'),
	[QryGroup64] char(1) NULL DEFAULT ('N'),
	[DdctOffice] nvarchar(10) NULL,
	[CreateDate] datetime NULL,
	[UpdateDate] datetime NULL,
	[ExportCode] nvarchar(8) NULL,
	[DscntObjct] smallint NULL DEFAULT ((-1)),
	[DscntRel] char(1) NULL DEFAULT ('L'),
	[SPGCounter] smallint NULL DEFAULT ((0)),
	[SPPCounter] int NULL DEFAULT ((0)),
	[DdctFileNo] nvarchar(9) NULL,
	[SCNCounter] smallint NULL,
	[MinIntrst] numeric(19, 6) NULL,
	[DataSource] char(1) NULL DEFAULT ('N'),
	[OprCount] int NULL,
	[ExemptNo] nvarchar(50) NULL,
	[Priority] int NULL DEFAULT ((-1)),
	[CreditCard] smallint NULL DEFAULT ((-1)),
	[CrCardNum] nvarchar(64) NULL,
	[CardValid] datetime NULL,
	[UserSign] smallint NULL,
	[LocMth] char(1) NULL DEFAULT ('Y'),
	[validFor] char(1) NULL DEFAULT ('N'),
	[validFrom] datetime NULL,
	[validTo] datetime NULL,
	[frozenFor] char(1) NULL DEFAULT ('N'),
	[frozenFrom] datetime NULL,
	[frozenTo] datetime NULL,
	[sEmployed] char(1) NULL DEFAULT ('N'),
	[MTHCounter] int NULL,
	[BNKCounter] int NULL,
	[DdgKey] int NULL DEFAULT ((-1)),
	[DdtKey] int NULL DEFAULT ((-1)),
	[ValidComm] nvarchar(30) NULL,
	[FrozenComm] nvarchar(30) NULL,
	[chainStore] char(1) NULL DEFAULT ('N'),
	[DiscInRet] char(1) NULL DEFAULT ('N'),
	[State1] nvarchar(3) NULL,
	[State2] nvarchar(3) NULL,
	[VatGroup] nvarchar(8) NULL,
	[LogInstanc] int NULL DEFAULT ((0)),
	[ObjType] nvarchar(20) NULL DEFAULT ('2'),
	[Indicator] nvarchar(2) NULL,
	[ShipType] smallint NULL,
	[DebPayAcct] nvarchar(15) NULL,
	[ShipToDef] nvarchar(50) NULL,
	[Block] nvarchar(100) NULL,
	[MailBlock] nvarchar(100) NULL,
	[Password] nvarchar(32) NULL,
	[ECVatGroup] nvarchar(8) NULL,
	[Deleted] char(1) NULL DEFAULT ('N'),
	[IBAN] nvarchar(50) NULL,
	[DocEntry] int IDENTITY(1,1) NOT NULL,
	[FormCode] int NULL,
	[Box1099] nvarchar(20) NULL,
	[PymCode] nvarchar(15) NULL DEFAULT ('-1'),
	[BackOrder] char(1) NULL DEFAULT ('Y'),
	[PartDelivr] char(1) NULL DEFAULT ('Y'),
	[DunnLevel] int NULL,
	[DunnDate] datetime NULL,
	[BlockDunn] char(1) NULL DEFAULT ('N'),
	[BankCountr] nvarchar(3) NULL,
	[CollecAuth] char(1) NULL DEFAULT ('N'),
	[DME] nvarchar(5) NULL,
	[InstrucKey] nvarchar(30) NULL,
	[SinglePaym] char(1) NULL DEFAULT ('N'),
	[ISRBillId] nvarchar(9) NULL,
	[PaymBlock] char(1) NULL DEFAULT ('N'),
	[RefDetails] nvarchar(20) NULL,
	[HouseBank] nvarchar(30) NULL DEFAULT ('-1'),
	[OwnerIdNum] nvarchar(15) NULL,
	[PyBlckDesc] int NULL DEFAULT ((-1)),
	[HousBnkCry] nvarchar(3) NULL,
	[HousBnkAct] nvarchar(50) NULL,
	[HousBnkBrn] nvarchar(50) NULL,
	[ProjectCod] nvarchar(20) NULL,
	[SysMatchNo] int NULL DEFAULT ((-1)),
	[VatIdUnCmp] nvarchar(32) NULL,
	[AgentCode] nvarchar(32) NULL,
	[TolrncDays] smallint NULL,
	[SelfInvoic] char(1) NULL,
	[DeferrTax] char(1) NULL,
	[LetterNum] nvarchar(20) NULL,
	[MaxAmount] numeric(19, 6) NULL,
	[FromDate] datetime NULL,
	[ToDate] datetime NULL,
	[WTLiable] char(1) NULL,
	[CrtfcateNO] nvarchar(20) NULL,
	[ExpireDate] datetime NULL,
	[NINum] nvarchar(20) NULL,
	[AccCritria] char(1) NULL DEFAULT ('N'),
	[WTCode] nvarchar(4) NULL,
	[Equ] char(1) NULL DEFAULT ('N'),
	[HldCode] nvarchar(20) NULL,
	[ConnBP] nvarchar(15) NULL,
	[MltMthNum] int NULL,
	[TypWTReprt] char(1) NULL DEFAULT ('C'),
	[VATRegNum] nvarchar(32) NULL,
	[RepName] nvarchar(15) NULL,
	[Industry] ntext NULL,
	[Business] ntext NULL,
	[WTTaxCat] ntext NULL,
	[IsDomestic] char(1) NULL DEFAULT ('Y'),
	[IsResident] char(1) NULL DEFAULT ('Y'),
	[AutoCalBCG] char(1) NULL DEFAULT ('N'),
	[OtrCtlAcct] nvarchar(15) NULL,
	[AliasName] ntext NULL,
	[Building] ntext NULL,
	[MailBuildi] ntext NULL,
	[BoEPrsnt] nvarchar(15) NULL,
	[BoEDiscnt] nvarchar(15) NULL,
	[BoEOnClct] nvarchar(15) NULL,
	[UnpaidBoE] nvarchar(15) NULL,
	[ITWTCode] nvarchar(4) NULL,
	[DunTerm] nvarchar(25) NULL,
	[ChannlBP] nvarchar(15) NULL,
	[DfTcnician] int NULL,
	[Territory] int NULL,
	[BillToDef] nvarchar(50) NULL,
	[DpmClear] nvarchar(15) NULL,
	[IntrntSite] nvarchar(100) NULL,
	[LangCode] int NULL,
	[HousActKey] int NULL,
	[Profession] nvarchar(50) NULL,
	[CDPNum] smallint NULL,
	[DflBankKey] int NULL,
	[BCACode] nvarchar(3) NULL,
	[UseShpdGd] char(1) NULL DEFAULT ('Y'),
	[RegNum] nvarchar(32) NULL,
	[VerifNum] nvarchar(32) NULL,
	[BankCtlKey] nvarchar(2) NULL,
	[HousCtlKey] nvarchar(2) NULL,
	[AddrType] nvarchar(100) NULL,
	[InsurOp347] char(1) NULL DEFAULT ('N'),
	[MailAddrTy] nvarchar(100) NULL,
	[StreetNo] nvarchar(100) NULL,
	[MailStrNo] nvarchar(100) NULL,
	[TaxRndRule] char(1) NULL DEFAULT ('D'),
	[VendTID] int NULL,
	[ThreshOver] char(1) NULL DEFAULT ('N'),
	[SurOver] char(1) NULL DEFAULT ('N'),
	[VendorOcup] nvarchar(15) NULL,
	[OpCode347] char(1) NULL,
	[DpmIntAct] nvarchar(15) NULL,
	[ResidenNum] char(1) NULL DEFAULT ('1'),
	[UserSign2] smallint NULL,
	[PlngGroup] nvarchar(10) NULL,
	[VatIDNum] nvarchar(32) NULL,
	[Affiliate] char(1) NULL DEFAULT ('N'),
	[MivzExpSts] char(1) NULL DEFAULT ('B'),
	[HierchDdct] char(1) NULL DEFAULT ('Y'),
	[CertWHT] char(1) NULL DEFAULT ('N'),
	[CertBKeep] char(1) NULL DEFAULT ('N'),
	[WHShaamGrp] char(1) NULL DEFAULT ('1'),
	[IndustryC] int NULL,
	[DatevAcct] nvarchar(9) NULL,
	[DatevFirst] char(1) NULL DEFAULT ('Y'),
	[GTSRegNum] nvarchar(20) NULL,
	[GTSBankAct] nvarchar(80) NULL,
	[GTSBilAddr] nvarchar(80) NULL,
	[HsBnkSwift] nvarchar(50) NULL,
	[HsBnkIBAN] nvarchar(50) NULL,
	[DflSwift] nvarchar(50) NULL,
	[AutoPost] char(1) NULL DEFAULT ('N'),
	[IntrAcc] nvarchar(15) NULL,
	[FeeAcc] nvarchar(15) NULL,
	[CpnNo] int NULL,
	[NTSWebSite] smallint NULL,
	[DflIBAN] nvarchar(50) NULL,
	[Series] int NULL,
	[Number] int NULL,
	[EDocExpFrm] int NULL,
	[TaxIdIdent] char(1) NULL DEFAULT ('3'),
	[Attachment] ntext NULL,
	[AtcEntry] int NULL,
	[DiscRel] char(1) NULL DEFAULT ('L'),
	[NoDiscount] char(1) NULL DEFAULT ('N'),
	[SCAdjust] char(1) NULL DEFAULT ('N'),
	[DflAgrmnt] int NULL,
	[GlblLocNum] nvarchar(50) NULL,
	[SenderID] nvarchar(50) NULL,
	[RcpntID] nvarchar(50) NULL,
	[MainUsage] int NULL,
	[SefazCheck] char(1) NULL DEFAULT ('N'),
	[free312] char(1) NULL,
	[free313] char(1) NULL,
	[DateFrom] datetime NULL,
	[DateTill] datetime NULL,
	[RelCode] nvarchar(2) NULL,
	[OKATO] nvarchar(11) NULL,
	[OKTMO] nvarchar(12) NULL,
	[KBKCode] nvarchar(20) NULL,
	[TypeOfOp] char(1) NULL,
	[OwnerCode] int NULL,
	[MandateID] nvarchar(35) NULL,
	[SignDate] datetime NULL,
	[Remark1] int NULL,
	[ConCerti] nvarchar(20) NULL,
	[TpCusPres] int NULL DEFAULT ((9)),
	[RoleTypCod] nvarchar(2) NULL,
	[BlockComm] char(1) NULL DEFAULT ('N'),
	[EmplymntCt] nvarchar(2) NULL,
	[ExcptnlEvt] char(1) NULL,
	[ExpnPrfFnd] numeric(19, 6) NULL,
	[EdrsFromBP] char(1) NULL DEFAULT ('Y'),
	[EdrsToBP] char(1) NULL DEFAULT ('N'),
	[CreateTS] int NULL,
	[UpdateTS] int NULL,
	[EDocGenTyp] char(1) NULL,
	[eStreet] nvarchar(38) NULL,
	[eStreetNum] nvarchar(4) NULL,
	[eBuildnNum] int NULL,
	[eZipCode] nvarchar(10) NULL,
	[eCityTown] nvarchar(48) NULL,
	[eCountry] nvarchar(3) NULL,
	[eDistrict] nvarchar(3) NULL,
	[RepFName] nvarchar(20) NULL,
	[RepSName] nvarchar(36) NULL,
	[RepCmpName] nvarchar(36) NULL,
	[RepFisCode] nvarchar(16) NULL,
	[RepAddID] nvarchar(28) NULL,
	[PECAddr] nvarchar(254) NULL,
	[IPACodePA] nvarchar(32) NULL,
	[PriceMode] char(1) NULL,
	[EffecPrice] char(1) NULL DEFAULT ('D'),
	[TxExMxVdTp] char(1) NULL DEFAULT ('I'),
	[MerchantID] nvarchar(15) NULL,
	[UseBilAddr] char(1) NULL DEFAULT ('N'),
	[NaturalPer] char(1) NULL DEFAULT ('N'),
	[DPPStatus] char(1) NULL DEFAULT ('N'),
	[EnAddID] ntext NULL,
	[EncryptIV] nvarchar(100) NULL,
	[EnDflAccnt] ntext NULL,
	[EnDflIBAN] ntext NULL,
	[U_NIT] nvarchar(20) NULL,
	[U_Comprob] nvarchar(30) NULL,
	[U_TNegocio] nvarchar(10) NULL,
	[U_CodAnt] nvarchar(20) NULL,
	[U_ParteRel] nvarchar(2) NULL DEFAULT ('02'),
	[U_TipoIngreso] nvarchar(2) NULL DEFAULT ('01'),
	[U_SubGrupo] nvarchar(50) NULL,
	[U_CadenaID] nvarchar(50) NULL,
	[U_TipoMembresia] nvarchar(50) NULL,
	[U_expediente] int NULL DEFAULT ((0)),
	CONSTRAINT [OCRD_PRIMARY] PRIMARY KEY CLUSTERED ([CardCode] ASC)
);
GO

-- Tabla: posOCRG
CREATE TABLE [dbo].[posOCRG] (
	[GroupCode] smallint IDENTITY(1,1) NOT NULL,
	[GroupName] nvarchar(20) NOT NULL,
	[GroupType] char(1) NULL DEFAULT ('C'),
	[Locked] char(1) NULL DEFAULT ('N'),
	[DataSource] char(1) NULL DEFAULT ('N'),
	[UserSign] smallint NULL,
	[PriceList] smallint NULL,
	[DiscRel] char(1) NULL DEFAULT ('L'),
	[EffecPrice] char(1) NULL DEFAULT ('D'),
	CONSTRAINT [OCRG_PRIMARY] PRIMARY KEY CLUSTERED ([GroupCode] ASC)
);
GO

-- Tabla: posOCTG
CREATE TABLE [dbo].[posOCTG] (
	[GroupNum] smallint IDENTITY(1,1) NOT NULL,
	[PymntGroup] nvarchar(100) NOT NULL,
	[PayDuMonth] char(1) NULL DEFAULT ('N'),
	[ExtraMonth] smallint NULL DEFAULT ((0)),
	[ExtraDays] smallint NULL DEFAULT ((0)),
	[PaymntsNum] smallint NULL,
	[CredLimit] numeric(19, 6) NULL,
	[VolumDscnt] numeric(19, 6) NULL,
	[LatePyChrg] numeric(19, 6) NULL,
	[ObligLimit] numeric(19, 6) NULL,
	[ListNum] smallint NULL,
	[Payments] char(1) NULL DEFAULT ('N'),
	[NumOfPmnts] smallint NULL DEFAULT ((1)),
	[Payment1] numeric(19, 6) NULL,
	[DataSource] char(1) NULL DEFAULT ('N'),
	[UserSign] smallint NULL,
	[OpenRcpt] char(1) NULL DEFAULT ('N'),
	[DiscCode] nvarchar(20) NULL,
	[DunningCod] nvarchar(20) NULL,
	[BslineDate] char(1) NULL DEFAULT ('T'),
	[InstNum] smallint NULL,
	[TolDays] smallint NULL,
	[VATFirst] char(1) NULL DEFAULT ('N'),
	[CrdMthd] char(1) NULL DEFAULT ('L'),
	[CshRelev] char(1) NULL DEFAULT ('N'),
	[UserSign2] smallint NULL,
	[UpdateDate] datetime NULL,
	[LogInstanc] int NULL DEFAULT ((0)),
	CONSTRAINT [OCTG_PRIMARY] PRIMARY KEY CLUSTERED ([GroupNum] ASC)
);
GO

-- Tabla: POSODIN
CREATE TABLE [dbo].[POSODIN] (
	[C_DocEntry] int IDENTITY(1,1) NOT NULL,
	[C_Posted] char(1) NOT NULL,
	[DocEntry] int NOT NULL,
	[DocNum] int NULL,
	[DocType] char(1) NULL,
	[CANCELED] char(1) NULL,
	[Handwrtten] char(1) NULL,
	[Printed] char(1) NULL,
	[DocStatus] char(1) NULL,
	[InvntSttus] char(1) NULL,
	[Transfered] char(1) NULL,
	[ObjType] nvarchar(20) NULL,
	[DocDate] datetime NULL,
	[DocDueDate] datetime NULL,
	[CardCode] nvarchar(15) NOT NULL,
	[CardName] nvarchar(100) NULL,
	[Address] nvarchar(254) NULL,
	[NumAtCard] nvarchar(100) NULL,
	[VatPercent] numeric(19, 6) NULL,
	[VatSum] numeric(19, 6) NULL,
	[VatSumFC] numeric(19, 6) NULL,
	[DiscPrcnt] numeric(19, 6) NULL,
	[DiscSum] numeric(19, 6) NULL,
	[DiscSumFC] numeric(19, 6) NULL,
	[DocCur] nvarchar(3) NULL,
	[DocRate] numeric(19, 6) NULL,
	[DocTotal] numeric(19, 6) NULL,
	[DocTotalFC] numeric(19, 6) NULL,
	[PaidToDate] numeric(19, 6) NULL,
	[PaidFC] numeric(19, 6) NULL,
	[GrosProfit] numeric(19, 6) NULL,
	[GrosProfFC] numeric(19, 6) NULL,
	[Ref1] nvarchar(11) NULL,
	[Ref2] nvarchar(11) NULL,
	[Comments] nvarchar(254) NULL,
	[JrnlMemo] nvarchar(50) NULL,
	[TransId] int NULL,
	[ReceiptNum] int NULL,
	[GroupNum] smallint NULL,
	[DocTime] smallint NULL,
	[SlpCode] int NULL,
	[TrnspCode] smallint NULL,
	[PartSupply] char(1) NULL,
	[Confirmed] char(1) NULL,
	[GrossBase] smallint NULL,
	[ImportEnt] int NULL,
	[CreateTran] char(1) NULL,
	[SummryType] char(1) NULL,
	[UpdInvnt] char(1) NULL,
	[UpdCardBal] char(1) NULL,
	[Instance] smallint NULL,
	[Flags] int NULL,
	[InvntDirec] char(1) NULL,
	[CntctCode] int NULL,
	[ShowSCN] char(1) NULL,
	[FatherCard] nvarchar(15) NULL,
	[SysRate] numeric(19, 6) NULL,
	[CurSource] char(1) NULL,
	[VatSumSy] numeric(19, 6) NULL,
	[DiscSumSy] numeric(19, 6) NULL,
	[DocTotalSy] numeric(19, 6) NULL,
	[PaidSys] numeric(19, 6) NULL,
	[FatherType] char(1) NULL,
	[GrosProfSy] numeric(19, 6) NULL,
	[UpdateDate] datetime NULL,
	[IsICT] char(1) NULL,
	[CreateDate] datetime NULL,
	[Volume] numeric(19, 6) NULL,
	[VolUnit] smallint NULL,
	[Weight] numeric(19, 6) NULL,
	[WeightUnit] smallint NULL,
	[Series] int NULL,
	[TaxDate] datetime NULL,
	[Filler] nvarchar(8) NULL,
	[DataSource] char(1) NULL,
	[StampNum] nvarchar(16) NULL,
	[isCrin] char(1) NULL,
	[FinncPriod] int NULL,
	[UserSign] smallint NULL,
	[selfInv] char(1) NULL,
	[VatPaid] numeric(19, 6) NULL,
	[VatPaidFC] numeric(19, 6) NULL,
	[VatPaidSys] numeric(19, 6) NULL,
	[UserSign2] smallint NULL,
	[WddStatus] char(1) NULL,
	[draftKey] int NULL,
	[TotalExpns] numeric(19, 6) NULL,
	[TotalExpFC] numeric(19, 6) NULL,
	[TotalExpSC] numeric(19, 6) NULL,
	[DunnLevel] int NULL,
	[Address2] nvarchar(254) NULL,
	[LogInstanc] int NULL,
	[Exported] char(1) NULL,
	[StationID] int NULL,
	[Indicator] nvarchar(2) NULL,
	[NetProc] char(1) NULL,
	[AqcsTax] numeric(19, 6) NULL,
	[AqcsTaxFC] numeric(19, 6) NULL,
	[AqcsTaxSC] numeric(19, 6) NULL,
	[CashDiscPr] numeric(19, 6) NULL,
	[CashDiscnt] numeric(19, 6) NULL,
	[CashDiscFC] numeric(19, 6) NULL,
	[CashDiscSC] numeric(19, 6) NULL,
	[ShipToCode] nvarchar(50) NULL,
	[LicTradNum] nvarchar(32) NULL,
	[PaymentRef] nvarchar(27) NULL,
	[WTSum] numeric(19, 6) NULL,
	[WTSumFC] numeric(19, 6) NULL,
	[WTSumSC] numeric(19, 6) NULL,
	[RoundDif] numeric(19, 6) NULL,
	[RoundDifFC] numeric(19, 6) NULL,
	[RoundDifSy] numeric(19, 6) NULL,
	[CheckDigit] char(1) NULL,
	[Form1099] int NULL,
	[Box1099] nvarchar(20) NULL,
	[submitted] char(1) NULL,
	[PoPrss] char(1) NULL,
	[Rounding] char(1) NULL,
	[RevisionPo] char(1) NULL,
	[Segment] smallint NULL,
	[ReqDate] datetime NULL,
	[CancelDate] datetime NULL,
	[PickStatus] char(1) NULL,
	[Pick] char(1) NULL,
	[BlockDunn] char(1) NULL,
	[PeyMethod] nvarchar(15) NULL,
	[PayBlock] char(1) NULL,
	[PayBlckRef] int NULL,
	[MaxDscn] char(1) NULL,
	[Reserve] char(1) NULL,
	[Max1099] numeric(19, 6) NULL,
	[CntrlBnk] nvarchar(15) NULL,
	[PickRmrk] nvarchar(254) NULL,
	[ISRCodLine] nvarchar(53) NULL,
	[ExpAppl] numeric(19, 6) NULL,
	[ExpApplFC] numeric(19, 6) NULL,
	[ExpApplSC] numeric(19, 6) NULL,
	[Project] nvarchar(20) NULL,
	[DeferrTax] char(1) NULL,
	[LetterNum] nvarchar(20) NULL,
	[FromDate] datetime NULL,
	[ToDate] datetime NULL,
	[WTApplied] numeric(19, 6) NULL,
	[WTAppliedF] numeric(19, 6) NULL,
	[BoeReserev] char(1) NULL,
	[AgentCode] nvarchar(32) NULL,
	[WTAppliedS] numeric(19, 6) NULL,
	[EquVatSum] numeric(19, 6) NULL,
	[EquVatSumF] numeric(19, 6) NULL,
	[EquVatSumS] numeric(19, 6) NULL,
	[Installmnt] smallint NULL,
	[VATFirst] char(1) NULL,
	[NnSbAmnt] numeric(19, 6) NULL,
	[NnSbAmntSC] numeric(19, 6) NULL,
	[NbSbAmntFC] numeric(19, 6) NULL,
	[ExepAmnt] numeric(19, 6) NULL,
	[ExepAmntSC] numeric(19, 6) NULL,
	[ExepAmntFC] numeric(19, 6) NULL,
	[VatDate] datetime NULL,
	[CorrExt] nvarchar(25) NULL,
	[CorrInv] int NULL,
	[NCorrInv] int NULL,
	[CEECFlag] char(1) NULL,
	[BaseAmnt] numeric(19, 6) NULL,
	[BaseAmntSC] numeric(19, 6) NULL,
	[BaseAmntFC] numeric(19, 6) NULL,
	[CtlAccount] nvarchar(15) NULL,
	[BPLId] int NULL,
	[BPLName] nvarchar(100) NULL,
	[VATRegNum] nvarchar(32) NULL,
	[TxInvRptNo] nvarchar(10) NULL,
	[TxInvRptDt] datetime NULL,
	[KVVATCode] ntext NULL,
	[WTDetails] nvarchar(100) NULL,
	[SumAbsId] int NULL,
	[SumRptDate] datetime NULL,
	[PIndicator] nvarchar(10) NULL,
	[ManualNum] nvarchar(20) NULL,
	[UseShpdGd] char(1) NULL,
	[BaseVtAt] numeric(19, 6) NULL,
	[BaseVtAtSC] numeric(19, 6) NULL,
	[BaseVtAtFC] numeric(19, 6) NULL,
	[NnSbVAt] numeric(19, 6) NULL,
	[NnSbVAtSC] numeric(19, 6) NULL,
	[NbSbVAtFC] numeric(19, 6) NULL,
	[ExptVAt] numeric(19, 6) NULL,
	[ExptVAtSC] numeric(19, 6) NULL,
	[ExptVAtFC] numeric(19, 6) NULL,
	[LYPmtAt] numeric(19, 6) NULL,
	[LYPmtAtSC] numeric(19, 6) NULL,
	[LYPmtAtFC] numeric(19, 6) NULL,
	[ExpAnSum] numeric(19, 6) NULL,
	[ExpAnSys] numeric(19, 6) NULL,
	[ExpAnFrgn] numeric(19, 6) NULL,
	[DocSubType] nvarchar(2) NULL,
	[DpmStatus] char(1) NULL,
	[DpmAmnt] numeric(19, 6) NULL,
	[DpmAmntSC] numeric(19, 6) NULL,
	[DpmAmntFC] numeric(19, 6) NULL,
	[DpmDrawn] char(1) NULL,
	[DpmPrcnt] numeric(19, 6) NULL,
	[PaidSum] numeric(19, 6) NULL,
	[PaidSumFc] numeric(19, 6) NULL,
	[PaidSumSc] numeric(19, 6) NULL,
	[FolioPref] nvarchar(4) NULL,
	[FolioNum] int NULL,
	[DpmAppl] numeric(19, 6) NULL,
	[DpmApplFc] numeric(19, 6) NULL,
	[DpmApplSc] numeric(19, 6) NULL,
	[LPgFolioN] int NULL,
	[Header] ntext NULL,
	[Footer] ntext NULL,
	[Posted] char(1) NULL,
	[OwnerCode] int NULL,
	[BPChCode] nvarchar(15) NULL,
	[BPChCntc] int NULL,
	[PayToCode] nvarchar(50) NULL,
	[IsPaytoBnk] char(1) NULL,
	[BnkCntry] nvarchar(3) NULL,
	[BankCode] nvarchar(30) NULL,
	[BnkAccount] nvarchar(50) NULL,
	[BnkBranch] nvarchar(50) NULL,
	[isIns] char(1) NULL,
	[TrackNo] nvarchar(30) NULL,
	[VersionNum] nvarchar(11) NULL,
	[LangCode] int NULL,
	[BPNameOW] char(1) NULL,
	[BillToOW] char(1) NULL,
	[ShipToOW] char(1) NULL,
	[RetInvoice] char(1) NULL,
	[ClsDate] datetime NULL,
	[MInvNum] int NULL,
	[MInvDate] datetime NULL,
	[SeqCode] smallint NULL,
	[Serial] int NULL,
	[SeriesStr] nvarchar(3) NULL,
	[SubStr] nvarchar(3) NULL,
	[Model] nvarchar(6) NULL,
	[TaxOnExp] numeric(19, 6) NULL,
	[TaxOnExpFc] numeric(19, 6) NULL,
	[TaxOnExpSc] numeric(19, 6) NULL,
	[TaxOnExAp] numeric(19, 6) NULL,
	[TaxOnExApF] numeric(19, 6) NULL,
	[TaxOnExApS] numeric(19, 6) NULL,
	[LastPmnTyp] char(1) NULL,
	[LndCstNum] int NULL,
	[UseCorrVat] char(1) NULL,
	[BlkCredMmo] char(1) NULL,
	[OpenForLaC] char(1) NULL,
	[Excised] char(1) NULL,
	[ExcRefDate] datetime NULL,
	[ExcRmvTime] nvarchar(8) NULL,
	[SrvGpPrcnt] numeric(19, 6) NULL,
	[DepositNum] int NULL,
	[CertNum] nvarchar(31) NULL,
	[DutyStatus] char(1) NULL,
	[AutoCrtFlw] char(1) NULL,
	[FlwRefDate] datetime NULL,
	[FlwRefNum] nvarchar(100) NULL,
	[VatJENum] int NULL,
	[DpmVat] numeric(19, 6) NULL,
	[DpmVatFc] numeric(19, 6) NULL,
	[DpmVatSc] numeric(19, 6) NULL,
	[DpmAppVat] numeric(19, 6) NULL,
	[DpmAppVatF] numeric(19, 6) NULL,
	[DpmAppVatS] numeric(19, 6) NULL,
	[InsurOp347] char(1) NULL,
	[IgnRelDoc] char(1) NULL,
	[BuildDesc] nvarchar(50) NULL,
	[ResidenNum] char(1) NULL,
	[Checker] int NULL,
	[Payee] int NULL,
	[CopyNumber] int NULL,
	[SSIExmpt] char(1) NULL,
	[PQTGrpSer] int NULL,
	[PQTGrpNum] int NULL,
	[PQTGrpHW] char(1) NULL,
	[ReopOriDoc] char(1) NULL,
	[ReopManCls] char(1) NULL,
	[DocManClsd] char(1) NULL,
	[ClosingOpt] smallint NULL,
	[SpecDate] datetime NULL,
	[Ordered] char(1) NULL,
	[NTSApprov] char(1) NULL,
	[NTSWebSite] smallint NULL,
	[NTSeTaxNo] nvarchar(50) NULL,
	[NTSApprNo] nvarchar(50) NULL,
	[PayDuMonth] char(1) NULL,
	[ExtraMonth] smallint NULL,
	[ExtraDays] smallint NULL,
	[CdcOffset] smallint NULL,
	[SignMsg] ntext NULL,
	[SignDigest] ntext NULL,
	[CertifNum] nvarchar(50) NULL,
	[KeyVersion] int NULL,
	[EDocGenTyp] char(1) NULL,
	[ESeries] smallint NULL,
	[EDocNum] nvarchar(50) NULL,
	[EDocExpFrm] int NULL,
	[OnlineQuo] char(1) NULL,
	[POSEqNum] nvarchar(20) NULL,
	[POSManufSN] nvarchar(20) NULL,
	[POSCashN] int NULL,
	[EDocStatus] char(1) NULL,
	[EDocCntnt] ntext NULL,
	[EDocProces] char(1) NULL,
	[EDocErrCod] nvarchar(50) NULL,
	[EDocErrMsg] ntext NULL,
	[EDocCancel] char(1) NULL,
	[EDocTest] char(1) NULL,
	[EDocPrefix] nvarchar(10) NULL,
	[CUP] int NULL,
	[CIG] int NULL,
	[DpmAsDscnt] char(1) NULL,
	[Attachment] ntext NULL,
	[AtcEntry] int NULL,
	[SupplCode] nvarchar(254) NULL,
	[GTSRlvnt] char(1) NULL,
	[BaseDisc] numeric(19, 6) NULL,
	[BaseDiscSc] numeric(19, 6) NULL,
	[BaseDiscFc] numeric(19, 6) NULL,
	[BaseDiscPr] numeric(19, 6) NULL,
	[CreateTS] int NULL,
	[UpdateTS] int NULL,
	[SrvTaxRule] char(1) NULL,
	[AnnInvDecR] int NULL,
	[Supplier] nvarchar(15) NULL,
	[Releaser] int NULL,
	[Receiver] int NULL,
	[ToWhsCode] nvarchar(8) NULL,
	[AssetDate] datetime NULL,
	[Requester] nvarchar(25) NULL,
	[ReqName] nvarchar(155) NULL,
	[Branch] smallint NULL,
	[Department] smallint NULL,
	[Email] nvarchar(100) NULL,
	[Notify] char(1) NULL,
	[ReqType] int NULL,
	[OriginType] char(1) NULL,
	[IsReuseNum] char(1) NULL,
	[IsReuseNFN] char(1) NULL,
	[DocDlvry] char(1) NULL,
	[PaidDpm] numeric(19, 6) NULL,
	[PaidDpmF] numeric(19, 6) NULL,
	[PaidDpmS] numeric(19, 6) NULL,
	[EnvTypeNFe] int NULL,
	[AgrNo] int NULL,
	[IsAlt] char(1) NULL,
	[AltBaseTyp] int NULL,
	[AltBaseEnt] int NULL,
	[AuthCode] nvarchar(250) NULL,
	[StDlvDate] datetime NULL,
	[StDlvTime] int NULL,
	[EndDlvDate] datetime NULL,
	[EndDlvTime] int NULL,
	[VclPlate] nvarchar(20) NULL,
	[ElCoStatus] nvarchar(10) NULL,
	[AtDocType] nvarchar(2) NULL,
	[ElCoMsg] nvarchar(254) NULL,
	[PrintSEPA] char(1) NULL,
	[FreeChrg] numeric(19, 6) NULL,
	[FreeChrgFC] numeric(19, 6) NULL,
	[FreeChrgSC] numeric(19, 6) NULL,
	[NfeValue] numeric(19, 6) NULL,
	[FiscDocNum] nvarchar(100) NULL,
	[RelatedTyp] int NULL,
	[RelatedEnt] int NULL,
	[CCDEntry] int NULL,
	[NfePrntFo] int NULL,
	[ZrdAbs] int NULL,
	[POSRcptNo] int NULL,
	[FoCTax] numeric(19, 6) NULL,
	[FoCTaxFC] numeric(19, 6) NULL,
	[FoCTaxSC] numeric(19, 6) NULL,
	[TpCusPres] int NULL,
	[ExcDocDate] datetime NULL,
	[FoCFrght] numeric(19, 6) NULL,
	[FoCFrghtFC] numeric(19, 6) NULL,
	[FoCFrghtSC] numeric(19, 6) NULL,
	[InterimTyp] smallint NULL,
	[PTICode] nvarchar(5) NULL,
	[Letter] char(1) NULL,
	[FolNumFrom] int NULL,
	[FolNumTo] int NULL,
	[FolSeries] int NULL,
	[SplitTax] numeric(19, 6) NULL,
	[SplitTaxFC] numeric(19, 6) NULL,
	[SplitTaxSC] numeric(19, 6) NULL,
	[ToBinCode] nvarchar(228) NULL,
	[PriceMode] char(1) NULL,
	[PoDropPrss] char(1) NULL,
	[PermitNo] nvarchar(20) NULL,
	[MYFtype] nvarchar(2) NULL,
	[DocTaxID] nvarchar(32) NULL,
	[DateReport] datetime NULL,
	[RepSection] nvarchar(3) NULL,
	[ExclTaxRep] char(1) NULL,
	[PosCashReg] int NULL,
	[DmpTransID] nvarchar(20) NULL,
	[ECommerBP] nvarchar(15) NULL,
	[EComerGSTN] nvarchar(15) NULL,
	[Revision] char(1) NULL,
	[RevRefNo] nvarchar(100) NULL,
	[RevRefDate] datetime NULL,
	[RevCreRefN] nvarchar(100) NULL,
	[RevCreRefD] datetime NULL,
	[TaxInvNo] nvarchar(100) NULL,
	[FrmBpDate] datetime NULL,
	[GSTTranTyp] nvarchar(2) NULL,
	[BaseType] int NULL,
	[BaseEntry] int NULL,
	[ComTrade] char(1) NULL,
	[UseBilAddr] char(1) NULL,
	[IssReason] smallint NULL,
	[ComTradeRt] char(1) NULL,
	[SplitPmnt] char(1) NULL,
	[SOIWizId] int NULL,
	[SelfPosted] char(1) NULL,
	[EnBnkAcct] ntext NULL,
	[EncryptIV] nvarchar(100) NULL,
	[U_FacNit] nvarchar(20) NULL,
	[U_FacNom] nvarchar(100) NULL,
	[U_FacFecha] datetime NULL,
	[U_TNegocio] nvarchar(20) NULL,
	[U_CGastos] nvarchar(10) NULL,
	[U_FacSerie] nvarchar(30) NULL,
	[U_TipoNC] nvarchar(30) NULL,
	[U_FACNUM] nvarchar(19) NULL,
	[U_NCO] nvarchar(19) NULL,
	[U_TipoIngreso] nvarchar(2) NULL,
	[U_ParteRel] nvarchar(2) NULL,
	[U_609TS] nvarchar(50) NULL,
	[U_FV6] datetime NULL,
	[U_PuntoEmision] int NULL,
	[U_user] nvarchar(20) NULL,
	[U_terminal] nvarchar(20) NULL,
	[U_OrdPedido] varchar(10) NULL,
	[U_Chofer] int NULL,
	[U_Ficha] nvarchar(10) NULL,
	[U_SCIASerie] nvarchar(10) NULL,
	[U_SCIADocNum] int NULL,
	[U_NCFVence] datetime NULL,
	[C_DocNet] numeric(19, 6) NULL,
	[U_DescLinea] numeric(19, 6) NULL,
	[C_ControlIndex] int NULL,
	[C_ListNum] int NULL,
	[C_ListName] nvarchar(30) NULL,
	[C_Inserted] datetime NULL,
	[C_Update] nchar(1) NULL,
	[C_DocStatus] char(1) NOT NULL DEFAULT ('C'),
	[C_DocOrigen] nvarchar(8) NULL,
	[C_DocOrigenNum] int NULL,
	[C_PuntosSum] numeric(18, 2) NULL,
	[C_FromNumAtCard] nvarchar(100) NULL,
	[C_FromObjType] nvarchar(20) NULL,
	[C_FromC_DocEntry] int NULL,
	[C_Phone] varchar(50) NULL,
	[C_Updated] datetime NULL,
	CONSTRAINT [POSODIN_PRIMARY] PRIMARY KEY CLUSTERED ([C_DocEntry] ASC)
);
GO

-- Tabla: POSOIGN
CREATE TABLE [dbo].[POSOIGN] (
	[C_DocEntry] int IDENTITY(1,1) NOT NULL,
	[C_Posted] char(1) NOT NULL DEFAULT ('N'),
	[DocEntry] int NOT NULL DEFAULT ((0)),
	[DocNum] int NULL,
	[DocType] char(1) NULL DEFAULT ('I'),
	[CANCELED] char(1) NULL DEFAULT ('N'),
	[Handwrtten] char(1) NULL DEFAULT ('N'),
	[Printed] char(1) NULL DEFAULT ('N'),
	[DocStatus] char(1) NULL DEFAULT ('O'),
	[InvntSttus] char(1) NULL DEFAULT ('O'),
	[Transfered] char(1) NULL DEFAULT ('N'),
	[ObjType] nvarchar(20) NULL DEFAULT ('59'),
	[DocDate] datetime NULL,
	[DocDueDate] datetime NULL,
	[CardCode] nvarchar(15) NULL,
	[CardName] nvarchar(100) NULL,
	[Address] nvarchar(254) NULL,
	[NumAtCard] nvarchar(100) NULL,
	[VatPercent] numeric(19, 6) NULL,
	[VatSum] numeric(19, 6) NULL,
	[VatSumFC] numeric(19, 6) NULL,
	[DiscPrcnt] numeric(19, 6) NULL,
	[DiscSum] numeric(19, 6) NULL,
	[DiscSumFC] numeric(19, 6) NULL,
	[DocCur] nvarchar(3) NULL,
	[DocRate] numeric(19, 6) NULL,
	[DocTotal] numeric(19, 6) NULL,
	[DocTotalFC] numeric(19, 6) NULL,
	[PaidToDate] numeric(19, 6) NULL,
	[PaidFC] numeric(19, 6) NULL,
	[GrosProfit] numeric(19, 6) NULL,
	[GrosProfFC] numeric(19, 6) NULL,
	[Ref1] nvarchar(11) NULL,
	[Ref2] nvarchar(11) NULL,
	[Comments] nvarchar(254) NULL,
	[JrnlMemo] nvarchar(50) NULL,
	[TransId] int NULL,
	[ReceiptNum] int NULL,
	[GroupNum] smallint NULL,
	[DocTime] smallint NULL,
	[SlpCode] int NULL DEFAULT ((-1)),
	[TrnspCode] smallint NULL DEFAULT ((-1)),
	[PartSupply] char(1) NULL DEFAULT ('Y'),
	[Confirmed] char(1) NULL DEFAULT ('Y'),
	[GrossBase] smallint NULL DEFAULT ((0)),
	[ImportEnt] int NULL,
	[CreateTran] char(1) NULL DEFAULT ('N'),
	[SummryType] char(1) NULL DEFAULT ('N'),
	[UpdInvnt] char(1) NULL DEFAULT ('N'),
	[UpdCardBal] char(1) NULL DEFAULT ('N'),
	[Instance] smallint NOT NULL DEFAULT ((0)),
	[Flags] int NULL DEFAULT ((0)),
	[InvntDirec] char(1) NULL DEFAULT ('X'),
	[CntctCode] int NULL,
	[ShowSCN] char(1) NULL DEFAULT ('N'),
	[FatherCard] nvarchar(15) NULL,
	[SysRate] numeric(19, 6) NULL,
	[CurSource] char(1) NULL DEFAULT ('C'),
	[VatSumSy] numeric(19, 6) NULL,
	[DiscSumSy] numeric(19, 6) NULL,
	[DocTotalSy] numeric(19, 6) NULL,
	[PaidSys] numeric(19, 6) NULL,
	[FatherType] char(1) NULL DEFAULT ('P'),
	[GrosProfSy] numeric(19, 6) NULL,
	[UpdateDate] datetime NULL,
	[IsICT] char(1) NULL DEFAULT ('N'),
	[CreateDate] datetime NULL,
	[Volume] numeric(19, 6) NULL,
	[VolUnit] smallint NULL,
	[Weight] numeric(19, 6) NULL,
	[WeightUnit] smallint NULL,
	[Series] int NULL,
	[TaxDate] datetime NULL,
	[Filler] nvarchar(8) NULL,
	[DataSource] char(1) NULL DEFAULT ('N'),
	[StampNum] nvarchar(16) NULL,
	[isCrin] char(1) NULL DEFAULT ('N'),
	[FinncPriod] int NULL,
	[UserSign] smallint NULL,
	[selfInv] char(1) NULL DEFAULT ('N'),
	[VatPaid] numeric(19, 6) NULL,
	[VatPaidFC] numeric(19, 6) NULL,
	[VatPaidSys] numeric(19, 6) NULL,
	[UserSign2] smallint NULL,
	[WddStatus] char(1) NULL DEFAULT ('-'),
	[draftKey] int NULL DEFAULT ((-1)),
	[TotalExpns] numeric(19, 6) NULL,
	[TotalExpFC] numeric(19, 6) NULL,
	[TotalExpSC] numeric(19, 6) NULL,
	[DunnLevel] int NULL,
	[Address2] nvarchar(254) NULL,
	[LogInstanc] int NULL DEFAULT ((0)),
	[Exported] char(1) NULL DEFAULT ('N'),
	[StationID] int NULL,
	[Indicator] nvarchar(2) NULL,
	[NetProc] char(1) NULL DEFAULT ('N'),
	[AqcsTax] numeric(19, 6) NULL,
	[AqcsTaxFC] numeric(19, 6) NULL,
	[AqcsTaxSC] numeric(19, 6) NULL,
	[CashDiscPr] numeric(19, 6) NULL,
	[CashDiscnt] numeric(19, 6) NULL,
	[CashDiscFC] numeric(19, 6) NULL,
	[CashDiscSC] numeric(19, 6) NULL,
	[ShipToCode] nvarchar(50) NULL,
	[LicTradNum] nvarchar(32) NULL,
	[PaymentRef] nvarchar(27) NULL,
	[WTSum] numeric(19, 6) NULL,
	[WTSumFC] numeric(19, 6) NULL,
	[WTSumSC] numeric(19, 6) NULL,
	[RoundDif] numeric(19, 6) NULL,
	[RoundDifFC] numeric(19, 6) NULL,
	[RoundDifSy] numeric(19, 6) NULL,
	[CheckDigit] char(1) NULL,
	[Form1099] int NULL,
	[Box1099] nvarchar(20) NULL,
	[submitted] char(1) NULL DEFAULT ('N'),
	[PoPrss] char(1) NULL DEFAULT ('N'),
	[Rounding] char(1) NULL DEFAULT ('N'),
	[RevisionPo] char(1) NULL DEFAULT ('N'),
	[Segment] smallint NOT NULL DEFAULT ((0)),
	[ReqDate] datetime NULL,
	[CancelDate] datetime NULL,
	[PickStatus] char(1) NULL DEFAULT ('N'),
	[Pick] char(1) NULL DEFAULT ('N'),
	[BlockDunn] char(1) NULL DEFAULT ('N'),
	[PeyMethod] nvarchar(15) NULL,
	[PayBlock] char(1) NULL DEFAULT ('N'),
	[PayBlckRef] int NULL,
	[MaxDscn] char(1) NULL DEFAULT ('N'),
	[Reserve] char(1) NULL DEFAULT ('N'),
	[Max1099] numeric(19, 6) NULL,
	[CntrlBnk] nvarchar(15) NULL,
	[PickRmrk] nvarchar(254) NULL,
	[ISRCodLine] nvarchar(53) NULL,
	[ExpAppl] numeric(19, 6) NULL,
	[ExpApplFC] numeric(19, 6) NULL,
	[ExpApplSC] numeric(19, 6) NULL,
	[Project] nvarchar(20) NULL,
	[DeferrTax] char(1) NULL DEFAULT ('N'),
	[LetterNum] nvarchar(20) NULL,
	[FromDate] datetime NULL,
	[ToDate] datetime NULL,
	[WTApplied] numeric(19, 6) NULL,
	[WTAppliedF] numeric(19, 6) NULL,
	[BoeReserev] char(1) NULL DEFAULT ('N'),
	[AgentCode] nvarchar(32) NULL,
	[WTAppliedS] numeric(19, 6) NULL,
	[EquVatSum] numeric(19, 6) NULL,
	[EquVatSumF] numeric(19, 6) NULL,
	[EquVatSumS] numeric(19, 6) NULL,
	[Installmnt] smallint NULL DEFAULT ((1)),
	[VATFirst] char(1) NULL,
	[NnSbAmnt] numeric(19, 6) NULL,
	[NnSbAmntSC] numeric(19, 6) NULL,
	[NbSbAmntFC] numeric(19, 6) NULL,
	[ExepAmnt] numeric(19, 6) NULL,
	[ExepAmntSC] numeric(19, 6) NULL,
	[ExepAmntFC] numeric(19, 6) NULL,
	[VatDate] datetime NULL,
	[CorrExt] nvarchar(25) NULL,
	[CorrInv] int NULL,
	[NCorrInv] int NULL,
	[CEECFlag] char(1) NULL DEFAULT ('N'),
	[BaseAmnt] numeric(19, 6) NULL,
	[BaseAmntSC] numeric(19, 6) NULL,
	[BaseAmntFC] numeric(19, 6) NULL,
	[CtlAccount] nvarchar(15) NULL,
	[BPLId] int NULL,
	[BPLName] nvarchar(100) NULL,
	[VATRegNum] nvarchar(32) NULL,
	[TxInvRptNo] nvarchar(10) NULL,
	[TxInvRptDt] datetime NULL,
	[KVVATCode] ntext NULL,
	[WTDetails] nvarchar(100) NULL,
	[SumAbsId] int NULL DEFAULT ((-1)),
	[SumRptDate] datetime NULL,
	[PIndicator] nvarchar(10) NOT NULL DEFAULT (' '),
	[ManualNum] nvarchar(20) NULL,
	[UseShpdGd] char(1) NULL DEFAULT ('N'),
	[BaseVtAt] numeric(19, 6) NULL,
	[BaseVtAtSC] numeric(19, 6) NULL,
	[BaseVtAtFC] numeric(19, 6) NULL,
	[NnSbVAt] numeric(19, 6) NULL,
	[NnSbVAtSC] numeric(19, 6) NULL,
	[NbSbVAtFC] numeric(19, 6) NULL,
	[ExptVAt] numeric(19, 6) NULL,
	[ExptVAtSC] numeric(19, 6) NULL,
	[ExptVAtFC] numeric(19, 6) NULL,
	[LYPmtAt] numeric(19, 6) NULL,
	[LYPmtAtSC] numeric(19, 6) NULL,
	[LYPmtAtFC] numeric(19, 6) NULL,
	[ExpAnSum] numeric(19, 6) NULL,
	[ExpAnSys] numeric(19, 6) NULL,
	[ExpAnFrgn] numeric(19, 6) NULL,
	[DocSubType] nvarchar(2) NOT NULL DEFAULT ('--'),
	[DpmStatus] char(1) NULL DEFAULT ('O'),
	[DpmAmnt] numeric(19, 6) NULL,
	[DpmAmntSC] numeric(19, 6) NULL,
	[DpmAmntFC] numeric(19, 6) NULL,
	[DpmDrawn] char(1) NULL DEFAULT ('N'),
	[DpmPrcnt] numeric(19, 6) NULL,
	[PaidSum] numeric(19, 6) NULL,
	[PaidSumFc] numeric(19, 6) NULL,
	[PaidSumSc] numeric(19, 6) NULL,
	[FolioPref] nvarchar(4) NULL,
	[FolioNum] int NULL,
	[DpmAppl] numeric(19, 6) NULL,
	[DpmApplFc] numeric(19, 6) NULL,
	[DpmApplSc] numeric(19, 6) NULL,
	[LPgFolioN] int NULL,
	[Header] ntext NULL,
	[Footer] ntext NULL,
	[Posted] char(1) NULL DEFAULT ('Y'),
	[OwnerCode] int NULL,
	[BPChCode] nvarchar(15) NULL,
	[BPChCntc] int NULL,
	[PayToCode] nvarchar(50) NULL,
	[IsPaytoBnk] char(1) NULL,
	[BnkCntry] nvarchar(3) NULL,
	[BankCode] nvarchar(30) NULL,
	[BnkAccount] nvarchar(50) NULL,
	[BnkBranch] nvarchar(50) NULL,
	[isIns] char(1) NULL DEFAULT ('N'),
	[TrackNo] nvarchar(30) NULL,
	[VersionNum] nvarchar(11) NULL,
	[LangCode] int NULL,
	[BPNameOW] char(1) NULL DEFAULT ('N'),
	[BillToOW] char(1) NULL DEFAULT ('N'),
	[ShipToOW] char(1) NULL DEFAULT ('N'),
	[RetInvoice] char(1) NULL DEFAULT ('N'),
	[ClsDate] datetime NULL,
	[MInvNum] int NULL,
	[MInvDate] datetime NULL,
	[SeqCode] smallint NULL,
	[Serial] int NULL,
	[SeriesStr] nvarchar(3) NULL,
	[SubStr] nvarchar(3) NULL,
	[Model] nvarchar(6) NULL DEFAULT ('0'),
	[TaxOnExp] numeric(19, 6) NULL,
	[TaxOnExpFc] numeric(19, 6) NULL,
	[TaxOnExpSc] numeric(19, 6) NULL,
	[TaxOnExAp] numeric(19, 6) NULL,
	[TaxOnExApF] numeric(19, 6) NULL,
	[TaxOnExApS] numeric(19, 6) NULL,
	[LastPmnTyp] char(1) NULL,
	[LndCstNum] int NULL,
	[UseCorrVat] char(1) NULL DEFAULT ('N'),
	[BlkCredMmo] char(1) NULL DEFAULT ('N'),
	[OpenForLaC] char(1) NULL DEFAULT ('Y'),
	[Excised] char(1) NULL DEFAULT ('O'),
	[ExcRefDate] datetime NULL,
	[ExcRmvTime] nvarchar(8) NULL,
	[SrvGpPrcnt] numeric(19, 6) NULL,
	[DepositNum] int NULL,
	[CertNum] nvarchar(31) NULL,
	[DutyStatus] char(1) NULL DEFAULT ('Y'),
	[AutoCrtFlw] char(1) NULL DEFAULT ('N'),
	[FlwRefDate] datetime NULL,
	[FlwRefNum] nvarchar(100) NULL,
	[VatJENum] int NULL DEFAULT ((-1)),
	[DpmVat] numeric(19, 6) NULL,
	[DpmVatFc] numeric(19, 6) NULL,
	[DpmVatSc] numeric(19, 6) NULL,
	[DpmAppVat] numeric(19, 6) NULL,
	[DpmAppVatF] numeric(19, 6) NULL,
	[DpmAppVatS] numeric(19, 6) NULL,
	[InsurOp347] char(1) NULL DEFAULT ('N'),
	[IgnRelDoc] char(1) NULL DEFAULT ('N'),
	[BuildDesc] nvarchar(50) NULL,
	[ResidenNum] char(1) NULL DEFAULT ('1'),
	[Checker] int NULL,
	[Payee] int NULL,
	[CopyNumber] int NULL DEFAULT ((0)),
	[SSIExmpt] char(1) NULL,
	[PQTGrpSer] int NULL,
	[PQTGrpNum] int NULL,
	[PQTGrpHW] char(1) NULL DEFAULT ('N'),
	[ReopOriDoc] char(1) NULL,
	[ReopManCls] char(1) NULL,
	[DocManClsd] char(1) NULL DEFAULT ('N'),
	[ClosingOpt] smallint NULL DEFAULT ((1)),
	[SpecDate] datetime NULL,
	[Ordered] char(1) NULL DEFAULT ('N'),
	[NTSApprov] char(1) NULL DEFAULT ('N'),
	[NTSWebSite] smallint NULL,
	[NTSeTaxNo] nvarchar(50) NULL,
	[NTSApprNo] nvarchar(50) NULL,
	[PayDuMonth] char(1) NULL,
	[ExtraMonth] smallint NULL,
	[ExtraDays] smallint NULL,
	[CdcOffset] smallint NULL DEFAULT ((0)),
	[SignMsg] ntext NULL,
	[SignDigest] ntext NULL,
	[CertifNum] nvarchar(50) NULL,
	[KeyVersion] int NULL,
	[EDocGenTyp] char(1) NULL DEFAULT ('N'),
	[ESeries] smallint NULL,
	[EDocNum] nvarchar(50) NULL,
	[EDocExpFrm] int NULL,
	[OnlineQuo] char(1) NULL DEFAULT ('N'),
	[POSEqNum] nvarchar(20) NULL,
	[POSManufSN] nvarchar(20) NULL,
	[POSCashN] int NULL,
	[EDocStatus] char(1) NULL DEFAULT ('C'),
	[EDocCntnt] ntext NULL,
	[EDocProces] char(1) NULL DEFAULT ('C'),
	[EDocErrCod] nvarchar(50) NULL,
	[EDocErrMsg] ntext NULL,
	[EDocCancel] char(1) NULL DEFAULT ('N'),
	[EDocTest] char(1) NULL DEFAULT ('N'),
	[EDocPrefix] nvarchar(10) NULL,
	[CUP] int NULL,
	[CIG] int NULL,
	[DpmAsDscnt] char(1) NULL DEFAULT ('N'),
	[Attachment] ntext NULL,
	[AtcEntry] int NULL,
	[SupplCode] nvarchar(254) NULL,
	[GTSRlvnt] char(1) NULL DEFAULT ('N'),
	[BaseDisc] numeric(19, 6) NULL,
	[BaseDiscSc] numeric(19, 6) NULL,
	[BaseDiscFc] numeric(19, 6) NULL,
	[BaseDiscPr] numeric(19, 6) NULL,
	[CreateTS] int NULL,
	[UpdateTS] int NULL,
	[SrvTaxRule] char(1) NULL DEFAULT ('N'),
	[AnnInvDecR] int NULL,
	[Supplier] nvarchar(15) NULL,
	[Releaser] int NULL,
	[Receiver] int NULL,
	[ToWhsCode] nvarchar(8) NULL,
	[AssetDate] datetime NULL,
	[Requester] nvarchar(25) NULL,
	[ReqName] nvarchar(155) NULL,
	[Branch] smallint NULL,
	[Department] smallint NULL,
	[Email] nvarchar(100) NULL,
	[Notify] char(1) NULL,
	[ReqType] int NULL DEFAULT ((12)),
	[OriginType] char(1) NULL DEFAULT ('M'),
	[IsReuseNum] char(1) NULL DEFAULT ('N'),
	[IsReuseNFN] char(1) NULL DEFAULT ('N'),
	[DocDlvry] char(1) NULL,
	[PaidDpm] numeric(19, 6) NULL,
	[PaidDpmF] numeric(19, 6) NULL,
	[PaidDpmS] numeric(19, 6) NULL,
	[EnvTypeNFe] int NULL DEFAULT ((-1)),
	[AgrNo] int NULL,
	[IsAlt] char(1) NULL DEFAULT ('N'),
	[AltBaseTyp] int NULL DEFAULT ((-1)),
	[AltBaseEnt] int NULL,
	[AuthCode] nvarchar(250) NULL,
	[StDlvDate] datetime NULL,
	[StDlvTime] int NULL,
	[EndDlvDate] datetime NULL,
	[EndDlvTime] int NULL,
	[VclPlate] nvarchar(20) NULL,
	[ElCoStatus] nvarchar(10) NULL,
	[AtDocType] nvarchar(2) NULL,
	[ElCoMsg] nvarchar(254) NULL,
	[PrintSEPA] char(1) NULL DEFAULT ('N'),
	[FreeChrg] numeric(19, 6) NULL,
	[FreeChrgFC] numeric(19, 6) NULL,
	[FreeChrgSC] numeric(19, 6) NULL,
	[NfeValue] numeric(19, 6) NULL,
	[FiscDocNum] nvarchar(100) NULL,
	[RelatedTyp] int NULL DEFAULT ((-1)),
	[RelatedEnt] int NULL,
	[CCDEntry] int NULL,
	[NfePrntFo] int NULL DEFAULT ((0)),
	[ZrdAbs] int NULL,
	[POSRcptNo] int NULL,
	[FoCTax] numeric(19, 6) NULL,
	[FoCTaxFC] numeric(19, 6) NULL,
	[FoCTaxSC] numeric(19, 6) NULL,
	[TpCusPres] int NULL,
	[ExcDocDate] datetime NULL,
	[FoCFrght] numeric(19, 6) NULL,
	[FoCFrghtFC] numeric(19, 6) NULL,
	[FoCFrghtSC] numeric(19, 6) NULL,
	[InterimTyp] smallint NULL DEFAULT ((0)),
	[PTICode] nvarchar(5) NULL,
	[Letter] char(1) NULL,
	[FolNumFrom] int NULL,
	[FolNumTo] int NULL,
	[FolSeries] int NULL,
	[SplitTax] numeric(19, 6) NULL,
	[SplitTaxFC] numeric(19, 6) NULL,
	[SplitTaxSC] numeric(19, 6) NULL,
	[ToBinCode] nvarchar(228) NULL,
	[PriceMode] char(1) NULL,
	[PoDropPrss] char(1) NULL DEFAULT ('N'),
	[PermitNo] nvarchar(20) NULL,
	[MYFtype] nvarchar(2) NULL,
	[DocTaxID] nvarchar(32) NULL,
	[DateReport] datetime NULL,
	[RepSection] nvarchar(3) NULL,
	[ExclTaxRep] char(1) NULL DEFAULT ('N'),
	[PosCashReg] int NULL,
	[DmpTransID] nvarchar(20) NULL,
	[ECommerBP] nvarchar(15) NULL,
	[EComerGSTN] nvarchar(15) NULL,
	[Revision] char(1) NULL DEFAULT ('N'),
	[RevRefNo] nvarchar(100) NULL,
	[RevRefDate] datetime NULL,
	[RevCreRefN] nvarchar(100) NULL,
	[RevCreRefD] datetime NULL,
	[TaxInvNo] nvarchar(100) NULL,
	[FrmBpDate] datetime NULL,
	[GSTTranTyp] nvarchar(2) NULL,
	[BaseType] int NULL DEFAULT ((-1)),
	[BaseEntry] int NULL,
	[ComTrade] char(1) NULL DEFAULT ('E'),
	[UseBilAddr] char(1) NULL,
	[IssReason] smallint NULL DEFAULT ((1)),
	[ComTradeRt] char(1) NULL DEFAULT ('N'),
	[SplitPmnt] char(1) NULL DEFAULT ('N'),
	[SOIWizId] int NULL,
	[SelfPosted] char(1) NULL DEFAULT ('N'),
	[EnBnkAcct] ntext NULL,
	[EncryptIV] nvarchar(100) NULL,
	[U_FacNit] nvarchar(20) NULL,
	[U_FacNom] nvarchar(100) NULL,
	[U_FacFecha] datetime NULL,
	[U_TNegocio] nvarchar(20) NULL,
	[U_CGastos] nvarchar(10) NOT NULL DEFAULT ('09'),
	[U_FacSerie] nvarchar(30) NULL,
	[U_TipoNC] nvarchar(30) NULL,
	[U_FACNUM] nvarchar(19) NULL,
	[U_NCO] nvarchar(19) NULL,
	[U_TipoIngreso] nvarchar(2) NULL DEFAULT ('01'),
	[U_ParteRel] nvarchar(2) NULL DEFAULT ('02'),
	[U_609TS] nvarchar(50) NULL,
	[U_FV6] datetime NULL,
	[U_PuntoEmision] int NULL,
	[U_user] nvarchar(20) NULL,
	[U_terminal] nvarchar(20) NULL,
	[U_OrdPedido] int NULL,
	[U_Chofer] int NULL,
	[U_Ficha] nvarchar(10) NULL,
	[U_SCIASerie] nvarchar(10) NULL,
	[U_SCIADocNum] nvarchar(10) NULL,
	[U_NCFVence] datetime NULL,
	CONSTRAINT [PK_POSOIGN_250] PRIMARY KEY CLUSTERED ([C_DocEntry] ASC)
);
GO

-- Tabla: POSOINV
CREATE TABLE [dbo].[POSOINV] (
	[C_DocEntry] int IDENTITY(1,1) NOT NULL,
	[C_Posted] char(1) NOT NULL DEFAULT ('N'),
	[DocEntry] int NOT NULL DEFAULT ((0)),
	[DocNum] int NULL,
	[DocType] char(1) NULL DEFAULT ('I'),
	[CANCELED] char(1) NULL DEFAULT ('N'),
	[Handwrtten] char(1) NULL DEFAULT ('N'),
	[Printed] char(1) NULL DEFAULT ('N'),
	[DocStatus] char(1) NULL DEFAULT ('O'),
	[InvntSttus] char(1) NULL DEFAULT ('O'),
	[Transfered] char(1) NULL DEFAULT ('N'),
	[ObjType] nvarchar(20) NULL DEFAULT ('13'),
	[DocDate] datetime NULL,
	[DocDueDate] datetime NULL,
	[CardCode] nvarchar(15) NOT NULL,
	[CardName] nvarchar(100) NULL,
	[Address] nvarchar(254) NULL,
	[NumAtCard] nvarchar(100) NULL,
	[VatPercent] numeric(19, 6) NULL,
	[VatSum] numeric(19, 6) NULL,
	[VatSumFC] numeric(19, 6) NULL,
	[DiscPrcnt] numeric(19, 6) NULL,
	[DiscSum] numeric(19, 6) NULL,
	[DiscSumFC] numeric(19, 6) NULL,
	[DocCur] nvarchar(3) NULL,
	[DocRate] numeric(19, 6) NULL,
	[DocTotal] numeric(19, 6) NULL,
	[DocTotalFC] numeric(19, 6) NULL,
	[PaidToDate] numeric(19, 6) NULL,
	[PaidFC] numeric(19, 6) NULL,
	[GrosProfit] numeric(19, 6) NULL,
	[GrosProfFC] numeric(19, 6) NULL,
	[Ref1] nvarchar(11) NULL,
	[Ref2] nvarchar(11) NULL,
	[Comments] nvarchar(254) NULL,
	[JrnlMemo] nvarchar(50) NULL,
	[TransId] int NULL,
	[ReceiptNum] int NULL,
	[GroupNum] smallint NULL,
	[DocTime] smallint NULL,
	[SlpCode] int NULL DEFAULT ((-1)),
	[TrnspCode] smallint NULL DEFAULT ((-1)),
	[PartSupply] char(1) NULL DEFAULT ('Y'),
	[Confirmed] char(1) NULL DEFAULT ('Y'),
	[GrossBase] smallint NULL DEFAULT ((0)),
	[ImportEnt] int NULL,
	[CreateTran] char(1) NULL DEFAULT ('N'),
	[SummryType] char(1) NULL DEFAULT ('N'),
	[UpdInvnt] char(1) NULL DEFAULT ('N'),
	[UpdCardBal] char(1) NULL DEFAULT ('N'),
	[Instance] smallint NULL DEFAULT ((0)),
	[Flags] int NULL DEFAULT ((0)),
	[InvntDirec] char(1) NULL DEFAULT ('X'),
	[CntctCode] int NULL,
	[ShowSCN] char(1) NULL DEFAULT ('N'),
	[FatherCard] nvarchar(15) NULL,
	[SysRate] numeric(19, 6) NULL,
	[CurSource] char(1) NULL DEFAULT ('C'),
	[VatSumSy] numeric(19, 6) NULL,
	[DiscSumSy] numeric(19, 6) NULL,
	[DocTotalSy] numeric(19, 6) NULL,
	[PaidSys] numeric(19, 6) NULL,
	[FatherType] char(1) NULL DEFAULT ('P'),
	[GrosProfSy] numeric(19, 6) NULL,
	[UpdateDate] datetime NULL,
	[IsICT] char(1) NULL DEFAULT ('N'),
	[CreateDate] datetime NULL,
	[Volume] numeric(19, 6) NULL,
	[VolUnit] smallint NULL,
	[Weight] numeric(19, 6) NULL,
	[WeightUnit] smallint NULL,
	[Series] int NULL,
	[TaxDate] datetime NULL,
	[Filler] nvarchar(8) NULL,
	[DataSource] char(1) NULL DEFAULT ('N'),
	[StampNum] nvarchar(16) NULL,
	[isCrin] char(1) NULL DEFAULT ('N'),
	[FinncPriod] int NULL,
	[UserSign] smallint NULL,
	[selfInv] char(1) NULL DEFAULT ('N'),
	[VatPaid] numeric(19, 6) NULL,
	[VatPaidFC] numeric(19, 6) NULL,
	[VatPaidSys] numeric(19, 6) NULL,
	[UserSign2] smallint NULL,
	[WddStatus] char(1) NULL DEFAULT ('-'),
	[draftKey] int NULL DEFAULT ((-1)),
	[TotalExpns] numeric(19, 6) NULL,
	[TotalExpFC] numeric(19, 6) NULL,
	[TotalExpSC] numeric(19, 6) NULL,
	[DunnLevel] int NULL,
	[Address2] nvarchar(254) NULL,
	[LogInstanc] int NULL DEFAULT ((0)),
	[Exported] char(1) NULL DEFAULT ('N'),
	[StationID] int NULL,
	[Indicator] nvarchar(2) NULL,
	[NetProc] char(1) NULL DEFAULT ('N'),
	[AqcsTax] numeric(19, 6) NULL,
	[AqcsTaxFC] numeric(19, 6) NULL,
	[AqcsTaxSC] numeric(19, 6) NULL,
	[CashDiscPr] numeric(19, 6) NULL,
	[CashDiscnt] numeric(19, 6) NULL,
	[CashDiscFC] numeric(19, 6) NULL,
	[CashDiscSC] numeric(19, 6) NULL,
	[ShipToCode] nvarchar(50) NULL,
	[LicTradNum] nvarchar(32) NULL,
	[PaymentRef] nvarchar(27) NULL,
	[WTSum] numeric(19, 6) NULL,
	[WTSumFC] numeric(19, 6) NULL,
	[WTSumSC] numeric(19, 6) NULL,
	[RoundDif] numeric(19, 6) NULL,
	[RoundDifFC] numeric(19, 6) NULL,
	[RoundDifSy] numeric(19, 6) NULL,
	[CheckDigit] char(1) NULL,
	[Form1099] int NULL,
	[Box1099] nvarchar(20) NULL,
	[submitted] char(1) NULL DEFAULT ('N'),
	[PoPrss] char(1) NULL DEFAULT ('N'),
	[Rounding] char(1) NULL DEFAULT ('N'),
	[RevisionPo] char(1) NULL DEFAULT ('N'),
	[Segment] smallint NULL DEFAULT ((0)),
	[ReqDate] datetime NULL,
	[CancelDate] datetime NULL,
	[PickStatus] char(1) NULL DEFAULT ('N'),
	[Pick] char(1) NULL DEFAULT ('N'),
	[BlockDunn] char(1) NULL DEFAULT ('N'),
	[PeyMethod] nvarchar(15) NULL,
	[PayBlock] char(1) NULL DEFAULT ('N'),
	[PayBlckRef] int NULL,
	[MaxDscn] char(1) NULL DEFAULT ('N'),
	[Reserve] char(1) NULL DEFAULT ('N'),
	[Max1099] numeric(19, 6) NULL,
	[CntrlBnk] nvarchar(15) NULL,
	[PickRmrk] nvarchar(254) NULL,
	[ISRCodLine] nvarchar(53) NULL,
	[ExpAppl] numeric(19, 6) NULL,
	[ExpApplFC] numeric(19, 6) NULL,
	[ExpApplSC] numeric(19, 6) NULL,
	[Project] nvarchar(20) NULL,
	[DeferrTax] char(1) NULL,
	[LetterNum] nvarchar(20) NULL,
	[FromDate] datetime NULL,
	[ToDate] datetime NULL,
	[WTApplied] numeric(19, 6) NULL,
	[WTAppliedF] numeric(19, 6) NULL,
	[BoeReserev] char(1) NULL DEFAULT ('N'),
	[AgentCode] nvarchar(32) NULL,
	[WTAppliedS] numeric(19, 6) NULL,
	[EquVatSum] numeric(19, 6) NULL,
	[EquVatSumF] numeric(19, 6) NULL,
	[EquVatSumS] numeric(19, 6) NULL,
	[Installmnt] smallint NULL DEFAULT ((1)),
	[VATFirst] char(1) NULL,
	[NnSbAmnt] numeric(19, 6) NULL,
	[NnSbAmntSC] numeric(19, 6) NULL,
	[NbSbAmntFC] numeric(19, 6) NULL,
	[ExepAmnt] numeric(19, 6) NULL,
	[ExepAmntSC] numeric(19, 6) NULL,
	[ExepAmntFC] numeric(19, 6) NULL,
	[VatDate] datetime NULL,
	[CorrExt] nvarchar(25) NULL,
	[CorrInv] int NULL,
	[NCorrInv] int NULL,
	[CEECFlag] char(1) NULL DEFAULT ('N'),
	[BaseAmnt] numeric(19, 6) NULL,
	[BaseAmntSC] numeric(19, 6) NULL,
	[BaseAmntFC] numeric(19, 6) NULL,
	[CtlAccount] nvarchar(15) NULL,
	[BPLId] int NULL,
	[BPLName] nvarchar(100) NULL,
	[VATRegNum] nvarchar(32) NULL,
	[TxInvRptNo] nvarchar(10) NULL,
	[TxInvRptDt] datetime NULL,
	[KVVATCode] ntext NULL,
	[WTDetails] nvarchar(100) NULL,
	[SumAbsId] int NULL DEFAULT ((-1)),
	[SumRptDate] datetime NULL,
	[PIndicator] nvarchar(10) NULL DEFAULT (' '),
	[ManualNum] nvarchar(20) NULL,
	[UseShpdGd] char(1) NULL DEFAULT ('N'),
	[BaseVtAt] numeric(19, 6) NULL,
	[BaseVtAtSC] numeric(19, 6) NULL,
	[BaseVtAtFC] numeric(19, 6) NULL,
	[NnSbVAt] numeric(19, 6) NULL,
	[NnSbVAtSC] numeric(19, 6) NULL,
	[NbSbVAtFC] numeric(19, 6) NULL,
	[ExptVAt] numeric(19, 6) NULL,
	[ExptVAtSC] numeric(19, 6) NULL,
	[ExptVAtFC] numeric(19, 6) NULL,
	[LYPmtAt] numeric(19, 6) NULL,
	[LYPmtAtSC] numeric(19, 6) NULL,
	[LYPmtAtFC] numeric(19, 6) NULL,
	[ExpAnSum] numeric(19, 6) NULL,
	[ExpAnSys] numeric(19, 6) NULL,
	[ExpAnFrgn] numeric(19, 6) NULL,
	[DocSubType] nvarchar(2) NULL DEFAULT ('--'),
	[DpmStatus] char(1) NULL DEFAULT ('O'),
	[DpmAmnt] numeric(19, 6) NULL,
	[DpmAmntSC] numeric(19, 6) NULL,
	[DpmAmntFC] numeric(19, 6) NULL,
	[DpmDrawn] char(1) NULL DEFAULT ('N'),
	[DpmPrcnt] numeric(19, 6) NULL,
	[PaidSum] numeric(19, 6) NULL,
	[PaidSumFc] numeric(19, 6) NULL,
	[PaidSumSc] numeric(19, 6) NULL,
	[FolioPref] nvarchar(4) NULL,
	[FolioNum] int NULL,
	[DpmAppl] numeric(19, 6) NULL,
	[DpmApplFc] numeric(19, 6) NULL,
	[DpmApplSc] numeric(19, 6) NULL,
	[LPgFolioN] int NULL,
	[Header] ntext NULL,
	[Footer] ntext NULL,
	[Posted] char(1) NULL DEFAULT ('Y'),
	[OwnerCode] int NULL,
	[BPChCode] nvarchar(15) NULL,
	[BPChCntc] int NULL,
	[PayToCode] nvarchar(50) NULL,
	[IsPaytoBnk] char(1) NULL,
	[BnkCntry] nvarchar(3) NULL,
	[BankCode] nvarchar(30) NULL,
	[BnkAccount] nvarchar(50) NULL,
	[BnkBranch] nvarchar(50) NULL,
	[isIns] char(1) NULL DEFAULT ('N'),
	[TrackNo] nvarchar(30) NULL,
	[VersionNum] nvarchar(11) NULL,
	[LangCode] int NULL,
	[BPNameOW] char(1) NULL DEFAULT ('N'),
	[BillToOW] char(1) NULL DEFAULT ('N'),
	[ShipToOW] char(1) NULL DEFAULT ('N'),
	[RetInvoice] char(1) NULL DEFAULT ('N'),
	[ClsDate] datetime NULL,
	[MInvNum] int NULL,
	[MInvDate] datetime NULL,
	[SeqCode] smallint NULL,
	[Serial] int NULL,
	[SeriesStr] nvarchar(3) NULL,
	[SubStr] nvarchar(3) NULL,
	[Model] nvarchar(6) NULL DEFAULT ('0'),
	[TaxOnExp] numeric(19, 6) NULL,
	[TaxOnExpFc] numeric(19, 6) NULL,
	[TaxOnExpSc] numeric(19, 6) NULL,
	[TaxOnExAp] numeric(19, 6) NULL,
	[TaxOnExApF] numeric(19, 6) NULL,
	[TaxOnExApS] numeric(19, 6) NULL,
	[LastPmnTyp] char(1) NULL,
	[LndCstNum] int NULL,
	[UseCorrVat] char(1) NULL DEFAULT ('N'),
	[BlkCredMmo] char(1) NULL DEFAULT ('N'),
	[OpenForLaC] char(1) NULL DEFAULT ('Y'),
	[Excised] char(1) NULL DEFAULT ('O'),
	[ExcRefDate] datetime NULL,
	[ExcRmvTime] nvarchar(8) NULL,
	[SrvGpPrcnt] numeric(19, 6) NULL,
	[DepositNum] int NULL,
	[CertNum] nvarchar(31) NULL,
	[DutyStatus] char(1) NULL DEFAULT ('Y'),
	[AutoCrtFlw] char(1) NULL DEFAULT ('N'),
	[FlwRefDate] datetime NULL,
	[FlwRefNum] nvarchar(100) NULL,
	[VatJENum] int NULL DEFAULT ((-1)),
	[DpmVat] numeric(19, 6) NULL,
	[DpmVatFc] numeric(19, 6) NULL,
	[DpmVatSc] numeric(19, 6) NULL,
	[DpmAppVat] numeric(19, 6) NULL,
	[DpmAppVatF] numeric(19, 6) NULL,
	[DpmAppVatS] numeric(19, 6) NULL,
	[InsurOp347] char(1) NULL DEFAULT ('N'),
	[IgnRelDoc] char(1) NULL DEFAULT ('N'),
	[BuildDesc] nvarchar(50) NULL,
	[ResidenNum] char(1) NULL DEFAULT ('1'),
	[Checker] int NULL,
	[Payee] int NULL,
	[CopyNumber] int NULL DEFAULT ((0)),
	[SSIExmpt] char(1) NULL,
	[PQTGrpSer] int NULL,
	[PQTGrpNum] int NULL,
	[PQTGrpHW] char(1) NULL DEFAULT ('N'),
	[ReopOriDoc] char(1) NULL,
	[ReopManCls] char(1) NULL,
	[DocManClsd] char(1) NULL DEFAULT ('N'),
	[ClosingOpt] smallint NULL DEFAULT ((1)),
	[SpecDate] datetime NULL,
	[Ordered] char(1) NULL DEFAULT ('N'),
	[NTSApprov] char(1) NULL DEFAULT ('N'),
	[NTSWebSite] smallint NULL,
	[NTSeTaxNo] nvarchar(50) NULL,
	[NTSApprNo] nvarchar(50) NULL,
	[PayDuMonth] char(1) NULL,
	[ExtraMonth] smallint NULL,
	[ExtraDays] smallint NULL,
	[CdcOffset] smallint NULL DEFAULT ((0)),
	[SignMsg] ntext NULL,
	[SignDigest] ntext NULL,
	[CertifNum] nvarchar(50) NULL,
	[KeyVersion] int NULL,
	[EDocGenTyp] char(1) NULL DEFAULT ('N'),
	[ESeries] smallint NULL,
	[EDocNum] nvarchar(50) NULL,
	[EDocExpFrm] int NULL,
	[OnlineQuo] char(1) NULL DEFAULT ('N'),
	[POSEqNum] nvarchar(20) NULL,
	[POSManufSN] nvarchar(20) NULL,
	[POSCashN] int NULL,
	[EDocStatus] char(1) NULL DEFAULT ('C'),
	[EDocCntnt] ntext NULL,
	[EDocProces] char(1) NULL DEFAULT ('C'),
	[EDocErrCod] nvarchar(50) NULL,
	[EDocErrMsg] ntext NULL,
	[EDocCancel] char(1) NULL DEFAULT ('N'),
	[EDocTest] char(1) NULL DEFAULT ('N'),
	[EDocPrefix] nvarchar(10) NULL,
	[CUP] int NULL,
	[CIG] int NULL,
	[DpmAsDscnt] char(1) NULL DEFAULT ('N'),
	[Attachment] ntext NULL,
	[AtcEntry] int NULL,
	[SupplCode] nvarchar(254) NULL,
	[GTSRlvnt] char(1) NULL DEFAULT ('N'),
	[BaseDisc] numeric(19, 6) NULL,
	[BaseDiscSc] numeric(19, 6) NULL,
	[BaseDiscFc] numeric(19, 6) NULL,
	[BaseDiscPr] numeric(19, 6) NULL,
	[CreateTS] int NULL,
	[UpdateTS] int NULL,
	[SrvTaxRule] char(1) NULL DEFAULT ('N'),
	[AnnInvDecR] int NULL,
	[Supplier] nvarchar(15) NULL,
	[Releaser] int NULL,
	[Receiver] int NULL,
	[ToWhsCode] nvarchar(8) NULL,
	[AssetDate] datetime NULL,
	[Requester] nvarchar(25) NULL,
	[ReqName] nvarchar(155) NULL,
	[Branch] smallint NULL,
	[Department] smallint NULL,
	[Email] nvarchar(100) NULL,
	[Notify] char(1) NULL,
	[ReqType] int NULL DEFAULT ((12)),
	[OriginType] char(1) NULL DEFAULT ('M'),
	[IsReuseNum] char(1) NULL DEFAULT ('N'),
	[IsReuseNFN] char(1) NULL DEFAULT ('N'),
	[DocDlvry] char(1) NULL,
	[PaidDpm] numeric(19, 6) NULL,
	[PaidDpmF] numeric(19, 6) NULL,
	[PaidDpmS] numeric(19, 6) NULL,
	[EnvTypeNFe] int NULL DEFAULT ((-1)),
	[AgrNo] int NULL,
	[IsAlt] char(1) NULL DEFAULT ('N'),
	[AltBaseTyp] int NULL DEFAULT ((-1)),
	[AltBaseEnt] int NULL,
	[AuthCode] nvarchar(250) NULL,
	[StDlvDate] datetime NULL,
	[StDlvTime] int NULL,
	[EndDlvDate] datetime NULL,
	[EndDlvTime] int NULL,
	[VclPlate] nvarchar(20) NULL,
	[ElCoStatus] nvarchar(10) NULL,
	[AtDocType] nvarchar(2) NULL,
	[ElCoMsg] nvarchar(254) NULL,
	[PrintSEPA] char(1) NULL DEFAULT ('N'),
	[FreeChrg] numeric(19, 6) NULL,
	[FreeChrgFC] numeric(19, 6) NULL,
	[FreeChrgSC] numeric(19, 6) NULL,
	[NfeValue] numeric(19, 6) NULL,
	[FiscDocNum] nvarchar(100) NULL,
	[RelatedTyp] int NULL DEFAULT ((-1)),
	[RelatedEnt] int NULL,
	[CCDEntry] int NULL,
	[NfePrntFo] int NULL DEFAULT ((0)),
	[ZrdAbs] int NULL,
	[POSRcptNo] int NULL,
	[FoCTax] numeric(19, 6) NULL,
	[FoCTaxFC] numeric(19, 6) NULL,
	[FoCTaxSC] numeric(19, 6) NULL,
	[TpCusPres] int NULL,
	[ExcDocDate] datetime NULL,
	[FoCFrght] numeric(19, 6) NULL,
	[FoCFrghtFC] numeric(19, 6) NULL,
	[FoCFrghtSC] numeric(19, 6) NULL,
	[InterimTyp] smallint NULL DEFAULT ((0)),
	[PTICode] nvarchar(5) NULL,
	[Letter] char(1) NULL,
	[FolNumFrom] int NULL,
	[FolNumTo] int NULL,
	[FolSeries] int NULL,
	[SplitTax] numeric(19, 6) NULL,
	[SplitTaxFC] numeric(19, 6) NULL,
	[SplitTaxSC] numeric(19, 6) NULL,
	[ToBinCode] nvarchar(228) NULL,
	[PriceMode] char(1) NULL,
	[PoDropPrss] char(1) NULL DEFAULT ('N'),
	[PermitNo] nvarchar(20) NULL,
	[MYFtype] nvarchar(2) NULL,
	[DocTaxID] nvarchar(32) NULL,
	[DateReport] datetime NULL,
	[RepSection] nvarchar(3) NULL,
	[ExclTaxRep] char(1) NULL DEFAULT ('N'),
	[PosCashReg] int NULL,
	[DmpTransID] nvarchar(20) NULL,
	[ECommerBP] nvarchar(15) NULL,
	[EComerGSTN] nvarchar(15) NULL,
	[Revision] char(1) NULL DEFAULT ('N'),
	[RevRefNo] nvarchar(100) NULL,
	[RevRefDate] datetime NULL,
	[RevCreRefN] nvarchar(100) NULL,
	[RevCreRefD] datetime NULL,
	[TaxInvNo] nvarchar(100) NULL,
	[FrmBpDate] datetime NULL,
	[GSTTranTyp] nvarchar(2) NULL,
	[BaseType] int NULL DEFAULT ((-1)),
	[BaseEntry] int NULL,
	[ComTrade] char(1) NULL DEFAULT ('E'),
	[UseBilAddr] char(1) NULL,
	[IssReason] smallint NULL DEFAULT ((1)),
	[ComTradeRt] char(1) NULL DEFAULT ('N'),
	[SplitPmnt] char(1) NULL DEFAULT ('N'),
	[SOIWizId] int NULL,
	[SelfPosted] char(1) NULL DEFAULT ('N'),
	[EnBnkAcct] ntext NULL,
	[EncryptIV] nvarchar(100) NULL,
	[U_FacNit] nvarchar(20) NULL,
	[U_FacNom] nvarchar(100) NULL,
	[U_FacFecha] datetime NULL,
	[U_TNegocio] nvarchar(20) NULL,
	[U_CGastos] nvarchar(10) NOT NULL DEFAULT ('09'),
	[U_FacSerie] nvarchar(30) NULL,
	[U_TipoNC] nvarchar(30) NULL,
	[U_FACNUM] nvarchar(19) NULL,
	[U_NCO] nvarchar(19) NULL,
	[U_TipoIngreso] nvarchar(2) NULL DEFAULT ('01'),
	[U_ParteRel] nvarchar(2) NULL DEFAULT ('02'),
	[U_609TS] nvarchar(50) NULL,
	[U_FV6] datetime NULL,
	[U_PuntoEmision] int NULL,
	[U_user] nvarchar(20) NULL,
	[U_terminal] nvarchar(20) NULL,
	[U_OrdPedido] varchar(10) NULL,
	[U_Chofer] int NULL,
	[U_Ficha] nvarchar(10) NULL,
	[U_SCIASerie] nvarchar(10) NULL,
	[U_SCIADocNum] int NULL,
	[U_NCFVence] datetime NULL,
	[C_DocNet] numeric(19, 6) NULL,
	[U_DescLinea] numeric(19, 6) NULL DEFAULT ((0)),
	[C_ControlIndex] int NULL,
	[C_ListNum] int NULL DEFAULT ((1)),
	[C_ListName] nvarchar(30) NULL DEFAULT (''),
	[C_Inserted] datetime NULL DEFAULT (getdate()),
	[C_Update] nchar(1) NULL DEFAULT ('N'),
	[C_DocStatus] char(1) NOT NULL DEFAULT ('O'),
	[C_DocOrigen] nvarchar(8) NULL,
	[C_DocOrigenNum] int NULL,
	[C_PuntosSum] numeric(18, 2) NULL DEFAULT ((0)),
	[C_FromNumAtCard] nvarchar(100) NULL DEFAULT (''),
	[C_FromObjType] nvarchar(20) NULL DEFAULT (''),
	[C_FromC_DocEntry] int NULL DEFAULT ((0)),
	[C_Phone] varchar(50) NULL DEFAULT (''),
	[C_Updated] datetime NULL,
	CONSTRAINT [POSOINV_PRIMARY] PRIMARY KEY CLUSTERED ([C_DocEntry] ASC)
);
GO

-- Tabla: posOITB
CREATE TABLE [dbo].[posOITB] (
	[ItmsGrpCod] smallint IDENTITY(1,1) NOT NULL,
	[ItmsGrpNam] nvarchar(20) NOT NULL,
	[Locked] char(1) NULL DEFAULT ('N'),
	[DataSource] char(1) NULL DEFAULT ('N'),
	[UserSign] smallint NULL,
	[BalInvntAc] nvarchar(15) NULL,
	[SaleCostAc] nvarchar(15) NULL,
	[TransferAc] nvarchar(15) NULL,
	[RevenuesAc] nvarchar(15) NULL,
	[VarianceAc] nvarchar(15) NULL,
	[DecreasAc] nvarchar(15) NULL,
	[IncreasAc] nvarchar(15) NULL,
	[ReturnAc] nvarchar(15) NULL,
	[ExpensesAc] nvarchar(15) NULL,
	[EURevenuAc] nvarchar(15) NULL,
	[EUExpensAc] nvarchar(15) NULL,
	[FrRevenuAc] nvarchar(15) NULL,
	[FrExpensAc] nvarchar(15) NULL,
	[ExmptIncom] nvarchar(15) NULL,
	[CycleCode] smallint NULL,
	[Alert] char(1) NULL DEFAULT ('N'),
	[PriceDifAc] nvarchar(15) NULL,
	[ExchangeAc] nvarchar(15) NULL,
	[BalanceAcc] nvarchar(15) NULL,
	[PurchaseAc] nvarchar(15) NULL,
	[PAReturnAc] nvarchar(15) NULL,
	[PurchOfsAc] nvarchar(15) NULL,
	[ShpdGdsAct] nvarchar(15) NULL,
	[VatRevAct] nvarchar(15) NULL,
	[DecresGlAc] nvarchar(15) NULL,
	[IncresGlAc] nvarchar(15) NULL,
	[InvntSys] char(1) NULL,
	[PlaningSys] char(1) NULL DEFAULT ('N'),
	[PrcrmntMtd] char(1) NULL DEFAULT ('B'),
	[OrdrIntrvl] smallint NULL,
	[OrdrMulti] numeric(19, 6) NULL,
	[MinOrdrQty] numeric(19, 6) NULL,
	[LeadTime] int NULL,
	[StokRvlAct] nvarchar(15) NULL,
	[StkOffsAct] nvarchar(15) NULL,
	[WipAcct] nvarchar(15) NULL,
	[WipVarAcct] nvarchar(15) NULL,
	[CostRvlAct] nvarchar(15) NULL,
	[CstOffsAct] nvarchar(15) NULL,
	[ExpClrAct] nvarchar(15) NULL,
	[ExpOfstAct] nvarchar(15) NULL,
	[Object] nvarchar(20) NULL DEFAULT ('52'),
	[logInstanc] int NULL,
	[createDate] datetime NULL,
	[userSign2] smallint NULL,
	[updateDate] datetime NULL,
	[ARCMAct] nvarchar(15) NULL,
	[ARCMFrnAct] nvarchar(15) NULL,
	[ARCMEUAct] nvarchar(15) NULL,
	[ARCMExpAct] nvarchar(15) NULL,
	[APCMAct] nvarchar(15) NULL,
	[APCMFrnAct] nvarchar(15) NULL,
	[APCMEUAct] nvarchar(15) NULL,
	[RevRetAct] nvarchar(15) NULL,
	[ItemClass] char(1) NULL DEFAULT ('2'),
	[OSvcCode] int NULL DEFAULT ((-1)),
	[ISvcCode] int NULL DEFAULT ((-1)),
	[ServiceGrp] int NULL DEFAULT ((-1)),
	[NCMCode] int NULL DEFAULT ((-1)),
	[MatType] nvarchar(3) NULL DEFAULT ('1'),
	[MatGrp] int NULL DEFAULT ((-1)),
	[ProductSrc] nvarchar(2) NULL DEFAULT ('0'),
	[NegStckAct] nvarchar(15) NULL,
	[StkInTnAct] nvarchar(15) NULL,
	[PurBalAct] nvarchar(15) NULL,
	[WhICenAct] nvarchar(15) NULL,
	[WhOCenAct] nvarchar(15) NULL,
	[WipOffset] nvarchar(15) NULL,
	[StockOffst] nvarchar(15) NULL,
	[UgpEntry] int NULL,
	[IUoMEntry] int NULL,
	[ToleranDay] int NULL,
	[RuleCode] nvarchar(2) NULL,
	[CompoWH] char(1) NULL DEFAULT ('B'),
	[FreeChrgSA] nvarchar(15) NULL,
	[FreeChrgPU] nvarchar(15) NULL,
	[RawMtrl] char(1) NULL DEFAULT ('N'),
	[U_CifId] nvarchar(10) NULL,
	CONSTRAINT [OITB_PRIMARY] PRIMARY KEY CLUSTERED ([ItmsGrpCod] ASC)
);
GO

-- Tabla: POSOITM
CREATE TABLE [dbo].[POSOITM] (
	[ItemCode] nvarchar(50) NOT NULL,
	[ItemName] nvarchar(100) NULL,
	[FrgnName] nvarchar(100) NULL,
	[ItmsGrpCod] smallint NULL,
	[CstGrpCode] smallint NULL,
	[VatGourpSa] nvarchar(8) NULL,
	[CodeBars] nvarchar(254) NULL,
	[VATLiable] char(1) NULL,
	[PrchseItem] char(1) NULL,
	[SellItem] char(1) NULL,
	[InvntItem] char(1) NULL,
	[OnHand] numeric(19, 6) NULL,
	[IsCommited] numeric(19, 6) NULL,
	[OnOrder] numeric(19, 6) NULL,
	[IncomeAcct] nvarchar(15) NULL,
	[ExmptIncom] nvarchar(15) NULL,
	[MaxLevel] numeric(19, 6) NULL,
	[DfltWH] nvarchar(8) NULL,
	[CardCode] nvarchar(15) NULL,
	[SuppCatNum] nvarchar(50) NULL,
	[BuyUnitMsr] nvarchar(100) NULL,
	[NumInBuy] numeric(19, 6) NULL,
	[ReorderQty] numeric(19, 6) NULL,
	[MinLevel] numeric(19, 6) NULL,
	[LstEvlPric] numeric(19, 6) NULL,
	[LstEvlDate] datetime NULL,
	[CustomPer] numeric(19, 6) NULL,
	[Canceled] char(1) NULL,
	[MnufctTime] int NULL,
	[WholSlsTax] char(1) NULL,
	[RetilrTax] char(1) NULL,
	[SpcialDisc] numeric(19, 6) NULL,
	[DscountCod] smallint NULL,
	[TrackSales] char(1) NULL,
	[SalUnitMsr] nvarchar(100) NULL,
	[NumInSale] numeric(19, 6) NULL,
	[Consig] numeric(19, 6) NULL,
	[QueryGroup] int NULL,
	[Counted] numeric(19, 6) NULL,
	[OpenBlnc] numeric(19, 6) NULL,
	[EvalSystem] char(1) NULL,
	[UserSign] smallint NULL,
	[FREE] char(1) NULL,
	[PicturName] nvarchar(200) NULL,
	[Transfered] char(1) NULL,
	[BlncTrnsfr] char(1) NULL,
	[UserText] ntext NULL,
	[SerialNum] nvarchar(17) NULL,
	[CommisPcnt] numeric(19, 6) NULL,
	[CommisSum] numeric(19, 6) NULL,
	[CommisGrp] smallint NULL,
	[TreeType] char(1) NULL,
	[TreeQty] numeric(19, 6) NULL,
	[LastPurPrc] numeric(19, 6) NULL,
	[LastPurCur] nvarchar(3) NULL,
	[LastPurDat] datetime NULL,
	[ExitCur] nvarchar(3) NULL,
	[ExitPrice] numeric(19, 6) NULL,
	[ExitWH] nvarchar(8) NULL,
	[AssetItem] char(1) NULL,
	[WasCounted] char(1) NULL,
	[ManSerNum] char(1) NULL,
	[SHeight1] numeric(19, 6) NULL,
	[SHght1Unit] smallint NULL,
	[SHeight2] numeric(19, 6) NULL,
	[SHght2Unit] smallint NULL,
	[SWidth1] numeric(19, 6) NULL,
	[SWdth1Unit] smallint NULL,
	[SWidth2] numeric(19, 6) NULL,
	[SWdth2Unit] smallint NULL,
	[SLength1] numeric(19, 6) NULL,
	[SLen1Unit] smallint NULL,
	[Slength2] numeric(19, 6) NULL,
	[SLen2Unit] smallint NULL,
	[SVolume] numeric(19, 6) NULL,
	[SVolUnit] smallint NULL,
	[SWeight1] numeric(19, 6) NULL,
	[SWght1Unit] smallint NULL,
	[SWeight2] numeric(19, 6) NULL,
	[SWght2Unit] smallint NULL,
	[BHeight1] numeric(19, 6) NULL,
	[BHght1Unit] smallint NULL,
	[BHeight2] numeric(19, 6) NULL,
	[BHght2Unit] smallint NULL,
	[BWidth1] numeric(19, 6) NULL,
	[BWdth1Unit] smallint NULL,
	[BWidth2] numeric(19, 6) NULL,
	[BWdth2Unit] smallint NULL,
	[BLength1] numeric(19, 6) NULL,
	[BLen1Unit] smallint NULL,
	[Blength2] numeric(19, 6) NULL,
	[BLen2Unit] smallint NULL,
	[BVolume] numeric(19, 6) NULL,
	[BVolUnit] smallint NULL,
	[BWeight1] numeric(19, 6) NULL,
	[BWght1Unit] smallint NULL,
	[BWeight2] numeric(19, 6) NULL,
	[BWght2Unit] smallint NULL,
	[FixCurrCms] nvarchar(3) NULL,
	[FirmCode] smallint NULL,
	[LstSalDate] datetime NULL,
	[QryGroup1] char(1) NULL,
	[QryGroup2] char(1) NULL,
	[QryGroup3] char(1) NULL,
	[QryGroup4] char(1) NULL,
	[QryGroup5] char(1) NULL,
	[QryGroup6] char(1) NULL,
	[QryGroup7] char(1) NULL,
	[QryGroup8] char(1) NULL,
	[QryGroup9] char(1) NULL,
	[QryGroup10] char(1) NULL,
	[QryGroup11] char(1) NULL,
	[QryGroup12] char(1) NULL,
	[QryGroup13] char(1) NULL,
	[QryGroup14] char(1) NULL,
	[QryGroup15] char(1) NULL,
	[QryGroup16] char(1) NULL,
	[QryGroup17] char(1) NULL,
	[QryGroup18] char(1) NULL,
	[QryGroup19] char(1) NULL,
	[QryGroup20] char(1) NULL,
	[QryGroup21] char(1) NULL,
	[QryGroup22] char(1) NULL,
	[QryGroup23] char(1) NULL,
	[QryGroup24] char(1) NULL,
	[QryGroup25] char(1) NULL,
	[QryGroup26] char(1) NULL,
	[QryGroup27] char(1) NULL,
	[QryGroup28] char(1) NULL,
	[QryGroup29] char(1) NULL,
	[QryGroup30] char(1) NULL,
	[QryGroup31] char(1) NULL,
	[QryGroup32] char(1) NULL,
	[QryGroup33] char(1) NULL,
	[QryGroup34] char(1) NULL,
	[QryGroup35] char(1) NULL,
	[QryGroup36] char(1) NULL,
	[QryGroup37] char(1) NULL,
	[QryGroup38] char(1) NULL,
	[QryGroup39] char(1) NULL,
	[QryGroup40] char(1) NULL,
	[QryGroup41] char(1) NULL,
	[QryGroup42] char(1) NULL,
	[QryGroup43] char(1) NULL,
	[QryGroup44] char(1) NULL,
	[QryGroup45] char(1) NULL,
	[QryGroup46] char(1) NULL,
	[QryGroup47] char(1) NULL,
	[QryGroup48] char(1) NULL,
	[QryGroup49] char(1) NULL,
	[QryGroup50] char(1) NULL,
	[QryGroup51] char(1) NULL,
	[QryGroup52] char(1) NULL,
	[QryGroup53] char(1) NULL,
	[QryGroup54] char(1) NULL,
	[QryGroup55] char(1) NULL,
	[QryGroup56] char(1) NULL,
	[QryGroup57] char(1) NULL,
	[QryGroup58] char(1) NULL,
	[QryGroup59] char(1) NULL,
	[QryGroup60] char(1) NULL,
	[QryGroup61] char(1) NULL,
	[QryGroup62] char(1) NULL,
	[QryGroup63] char(1) NULL,
	[QryGroup64] char(1) NULL,
	[CreateDate] datetime NULL,
	[UpdateDate] datetime NULL,
	[ExportCode] nvarchar(20) NULL,
	[SalFactor1] numeric(19, 6) NULL,
	[SalFactor2] numeric(19, 6) NULL,
	[SalFactor3] numeric(19, 6) NULL,
	[SalFactor4] numeric(19, 6) NULL,
	[PurFactor1] numeric(19, 6) NULL,
	[PurFactor2] numeric(19, 6) NULL,
	[PurFactor3] numeric(19, 6) NULL,
	[PurFactor4] numeric(19, 6) NULL,
	[SalFormula] nvarchar(40) NULL,
	[PurFormula] nvarchar(40) NULL,
	[VatGroupPu] nvarchar(8) NULL,
	[AvgPrice] numeric(19, 6) NULL,
	[PurPackMsr] nvarchar(30) NULL,
	[PurPackUn] numeric(19, 6) NULL,
	[SalPackMsr] nvarchar(30) NULL,
	[SalPackUn] numeric(19, 6) NULL,
	[SCNCounter] smallint NULL,
	[ManBtchNum] char(1) NULL,
	[ManOutOnly] char(1) NULL,
	[DataSource] char(1) NULL,
	[validFor] char(1) NULL,
	[validFrom] datetime NULL,
	[validTo] datetime NULL,
	[frozenFor] char(1) NULL,
	[frozenFrom] datetime NULL,
	[frozenTo] datetime NULL,
	[BlockOut] char(1) NULL,
	[ValidComm] nvarchar(30) NULL,
	[FrozenComm] nvarchar(30) NULL,
	[LogInstanc] int NULL,
	[ObjType] nvarchar(20) NULL,
	[SWW] nvarchar(16) NULL,
	[Deleted] char(1) NULL,
	[DocEntry] int NULL,
	[ExpensAcct] nvarchar(15) NULL,
	[FrgnInAcct] nvarchar(15) NULL,
	[ShipType] smallint NULL,
	[GLMethod] char(1) NULL,
	[ECInAcct] nvarchar(15) NULL,
	[FrgnExpAcc] nvarchar(15) NULL,
	[ECExpAcc] nvarchar(15) NULL,
	[TaxType] char(1) NULL,
	[ByWh] char(1) NULL,
	[WTLiable] char(1) NULL,
	[ItemType] char(1) NULL,
	[WarrntTmpl] nvarchar(20) NULL,
	[BaseUnit] nvarchar(20) NULL,
	[CountryOrg] nvarchar(3) NULL,
	[StockValue] numeric(19, 6) NULL,
	[Phantom] char(1) NULL,
	[IssueMthd] char(1) NULL,
	[FREE1] char(1) NULL,
	[PricingPrc] numeric(19, 6) NULL,
	[MngMethod] char(1) NULL,
	[ReorderPnt] numeric(19, 6) NULL,
	[InvntryUom] nvarchar(100) NULL,
	[PlaningSys] char(1) NULL,
	[PrcrmntMtd] char(1) NULL,
	[OrdrIntrvl] smallint NULL,
	[OrdrMulti] numeric(19, 6) NULL,
	[MinOrdrQty] numeric(19, 6) NULL,
	[LeadTime] int NULL,
	[IndirctTax] char(1) NULL,
	[TaxCodeAR] nvarchar(8) NULL,
	[TaxCodeAP] nvarchar(8) NULL,
	[OSvcCode] int NULL,
	[ISvcCode] int NULL,
	[ServiceGrp] int NULL,
	[NCMCode] int NULL,
	[MatType] nvarchar(3) NULL,
	[MatGrp] int NULL,
	[ProductSrc] nvarchar(2) NULL,
	[ServiceCtg] int NULL,
	[ItemClass] char(1) NULL,
	[Excisable] char(1) NULL,
	[ChapterID] int NULL,
	[NotifyASN] nvarchar(40) NULL,
	[ProAssNum] nvarchar(20) NULL,
	[AssblValue] numeric(19, 6) NULL,
	[DNFEntry] int NULL,
	[UserSign2] smallint NULL,
	[Spec] nvarchar(30) NULL,
	[TaxCtg] nvarchar(4) NULL,
	[Series] int NULL,
	[Number] int NULL,
	[FuelCode] int NULL,
	[BeverTblC] nvarchar(2) NULL,
	[BeverGrpC] nvarchar(2) NULL,
	[BeverTM] int NULL,
	[Attachment] ntext NULL,
	[AtcEntry] int NULL,
	[ToleranDay] int NULL,
	[UgpEntry] int NULL,
	[PUoMEntry] int NULL,
	[SUoMEntry] int NULL,
	[IUoMEntry] int NULL,
	[IssuePriBy] smallint NULL,
	[AssetClass] nvarchar(20) NULL,
	[AssetGroup] nvarchar(15) NULL,
	[InventryNo] nvarchar(12) NULL,
	[Technician] int NULL,
	[Employee] int NULL,
	[Location] int NULL,
	[StatAsset] char(1) NULL,
	[Cession] char(1) NULL,
	[DeacAftUL] char(1) NULL,
	[AsstStatus] char(1) NULL,
	[CapDate] datetime NULL,
	[AcqDate] datetime NULL,
	[RetDate] datetime NULL,
	[GLPickMeth] char(1) NULL,
	[NoDiscount] char(1) NULL,
	[MgrByQty] char(1) NULL,
	[AssetRmk1] nvarchar(100) NULL,
	[AssetRmk2] nvarchar(100) NULL,
	[AssetAmnt1] numeric(19, 6) NULL,
	[AssetAmnt2] numeric(19, 6) NULL,
	[DeprGroup] nvarchar(15) NULL,
	[AssetSerNo] nvarchar(32) NULL,
	[CntUnitMsr] nvarchar(100) NULL,
	[NumInCnt] numeric(19, 6) NULL,
	[INUoMEntry] int NULL,
	[OneBOneRec] char(1) NULL,
	[RuleCode] nvarchar(2) NULL,
	[ScsCode] nvarchar(10) NULL,
	[SpProdType] nvarchar(2) NULL,
	[IWeight1] numeric(19, 6) NULL,
	[IWght1Unit] smallint NULL,
	[IWeight2] numeric(19, 6) NULL,
	[IWght2Unit] smallint NULL,
	[CompoWH] char(1) NULL,
	[CreateTS] int NULL,
	[UpdateTS] int NULL,
	[VirtAstItm] char(1) NULL,
	[SouVirAsst] nvarchar(50) NULL,
	[InCostRoll] char(1) NULL,
	[PrdStdCst] numeric(19, 6) NULL,
	[EnAstSeri] char(1) NULL,
	[LinkRsc] nvarchar(50) NULL,
	[OnHldPert] numeric(19, 6) NULL,
	[onHldLimt] numeric(19, 6) NULL,
	[PriceUnit] int NULL,
	[GSTRelevnt] char(1) NULL,
	[SACEntry] int NULL,
	[GstTaxCtg] char(1) NULL,
	[AssVal4WTR] numeric(19, 6) NULL,
	[ExcImpQUoM] int NULL,
	[ExcFixAmnt] numeric(19, 6) NULL,
	[ExcRate] numeric(19, 6) NULL,
	[SOIExc] char(1) NULL,
	[TNVED] nvarchar(10) NULL,
	[Imported] char(1) NULL,
	[AutoBatch] char(1) NULL,
	[U_CodAnt] nvarchar(20) NULL,
	[U_TipoMonto] char(1) NULL,
	[U_SubGrupo] nvarchar(50) NULL,
	CONSTRAINT [OITM_PRIMARY] PRIMARY KEY CLUSTERED ([ItemCode] ASC)
);
GO

-- Tabla: posOITW
CREATE TABLE [dbo].[posOITW] (
	[ItemCode] nvarchar(50) NOT NULL,
	[WhsCode] nvarchar(8) NOT NULL,
	[OnHand] numeric(19, 6) NULL,
	[IsCommited] numeric(19, 6) NULL,
	[OnOrder] numeric(19, 6) NULL,
	[Consig] numeric(19, 6) NULL,
	[Counted] numeric(19, 6) NULL,
	[WasCounted] char(1) NULL DEFAULT ('N'),
	[UserSign] smallint NULL,
	[MinStock] numeric(19, 6) NULL,
	[MaxStock] numeric(19, 6) NULL,
	[MinOrder] numeric(19, 6) NULL,
	[AvgPrice] numeric(19, 6) NULL,
	[Locked] char(1) NULL DEFAULT ('N'),
	[BalInvntAc] nvarchar(15) NULL,
	[SaleCostAc] nvarchar(15) NULL,
	[TransferAc] nvarchar(15) NULL,
	[RevenuesAc] nvarchar(15) NULL,
	[VarianceAc] nvarchar(15) NULL,
	[DecreasAc] nvarchar(15) NULL,
	[IncreasAc] nvarchar(15) NULL,
	[ReturnAc] nvarchar(15) NULL,
	[ExpensesAc] nvarchar(15) NULL,
	[EURevenuAc] nvarchar(15) NULL,
	[EUExpensAc] nvarchar(15) NULL,
	[FrRevenuAc] nvarchar(15) NULL,
	[FrExpensAc] nvarchar(15) NULL,
	[ExmptIncom] nvarchar(15) NULL,
	[PriceDifAc] nvarchar(15) NULL,
	[ExchangeAc] nvarchar(15) NULL,
	[BalanceAcc] nvarchar(15) NULL,
	[PurchaseAc] nvarchar(15) NULL,
	[PAReturnAc] nvarchar(15) NULL,
	[PurchOfsAc] nvarchar(15) NULL,
	[ShpdGdsAct] nvarchar(15) NULL,
	[VatRevAct] nvarchar(15) NULL,
	[StockValue] numeric(19, 6) NULL,
	[DecresGlAc] nvarchar(15) NULL,
	[IncresGlAc] nvarchar(15) NULL,
	[StokRvlAct] nvarchar(15) NULL,
	[StkOffsAct] nvarchar(15) NULL,
	[WipAcct] nvarchar(15) NULL,
	[WipVarAcct] nvarchar(15) NULL,
	[CostRvlAct] nvarchar(15) NULL,
	[CstOffsAct] nvarchar(15) NULL,
	[ExpClrAct] nvarchar(15) NULL,
	[ExpOfstAct] nvarchar(15) NULL,
	[Object] nvarchar(20) NULL DEFAULT ('31'),
	[logInstanc] int NULL,
	[createDate] datetime NULL,
	[userSign2] smallint NULL,
	[updateDate] datetime NULL,
	[ARCMAct] nvarchar(15) NULL,
	[ARCMFrnAct] nvarchar(15) NULL,
	[ARCMEUAct] nvarchar(15) NULL,
	[ARCMExpAct] nvarchar(15) NULL,
	[APCMAct] nvarchar(15) NULL,
	[APCMFrnAct] nvarchar(15) NULL,
	[APCMEUAct] nvarchar(15) NULL,
	[RevRetAct] nvarchar(15) NULL,
	[NegStckAct] nvarchar(15) NULL,
	[StkInTnAct] nvarchar(15) NULL,
	[PurBalAct] nvarchar(15) NULL,
	[WhICenAct] nvarchar(15) NULL,
	[WhOCenAct] nvarchar(15) NULL,
	[WipOffset] nvarchar(15) NULL,
	[StockOffst] nvarchar(15) NULL,
	[DftBinAbs] int NULL,
	[DftBinEnfd] char(1) NULL DEFAULT ('N'),
	[Freezed] char(1) NULL DEFAULT ('N'),
	[FreezeDoc] int NULL,
	[FreeChrgSA] nvarchar(15) NULL,
	[FreeChrgPU] nvarchar(15) NULL,
	CONSTRAINT [OITW_PRIMARY2] PRIMARY KEY CLUSTERED ([ItemCode] ASC, [WhsCode] ASC)
);
GO

-- Tabla: posOPLN
CREATE TABLE [dbo].[posOPLN] (
	[ListNum] smallint IDENTITY(1,1) NOT NULL,
	[ListName] nvarchar(32) NOT NULL,
	[BASE_NUM] smallint NULL,
	[Factor] numeric(19, 6) NULL,
	[RoundSys] smallint NULL DEFAULT ((0)),
	[GroupCode] smallint NULL DEFAULT ((1)),
	[DataSource] char(1) NULL DEFAULT ('N'),
	[SPPCounter] int NULL,
	[UserSign] smallint NULL,
	[IsGrossPrc] char(1) NULL DEFAULT ('N'),
	[LogInstanc] int NULL DEFAULT ((0)),
	[UserSign2] smallint NULL,
	[UpdateDate] datetime NULL,
	[ValidFor] char(1) NULL DEFAULT ('Y'),
	[ValidFrom] datetime NULL,
	[ValidTo] datetime NULL,
	[CreateDate] datetime NULL,
	[PrimCurr] nvarchar(3) NULL,
	[AddCurr1] nvarchar(3) NULL,
	[AddCurr2] nvarchar(3) NULL,
	[RoundRule] char(1) NULL DEFAULT ('R'),
	[ExtAmount] numeric(19, 6) NULL,
	[RndFrmtInt] nvarchar(10) NULL,
	[RndFrmtDec] nvarchar(10) NULL,
	CONSTRAINT [OPLN_PRIMARY] PRIMARY KEY CLUSTERED ([ListNum] ASC)
);
GO

-- Tabla: POSORIN
CREATE TABLE [dbo].[POSORIN] (
	[C_DocEntry] int IDENTITY(1,1) NOT NULL,
	[C_Posted] char(1) NOT NULL DEFAULT ('N'),
	[DocEntry] int NOT NULL DEFAULT ((0)),
	[DocNum] int NULL,
	[DocType] char(1) NULL DEFAULT ('I'),
	[CANCELED] char(1) NULL DEFAULT ('N'),
	[Handwrtten] char(1) NULL DEFAULT ('N'),
	[Printed] char(1) NULL DEFAULT ('N'),
	[DocStatus] char(1) NULL DEFAULT ('O'),
	[InvntSttus] char(1) NULL DEFAULT ('O'),
	[Transfered] char(1) NULL DEFAULT ('N'),
	[ObjType] nvarchar(20) NULL DEFAULT ('13'),
	[DocDate] datetime NULL,
	[DocDueDate] datetime NULL,
	[CardCode] nvarchar(15) NOT NULL,
	[CardName] nvarchar(100) NULL,
	[Address] nvarchar(254) NULL,
	[NumAtCard] nvarchar(100) NULL,
	[VatPercent] numeric(19, 6) NULL,
	[VatSum] numeric(19, 6) NULL,
	[VatSumFC] numeric(19, 6) NULL,
	[DiscPrcnt] numeric(19, 6) NULL,
	[DiscSum] numeric(19, 6) NULL,
	[DiscSumFC] numeric(19, 6) NULL,
	[DocCur] nvarchar(3) NULL,
	[DocRate] numeric(19, 6) NULL,
	[DocTotal] numeric(19, 6) NULL,
	[DocTotalFC] numeric(19, 6) NULL,
	[PaidToDate] numeric(19, 6) NULL,
	[PaidFC] numeric(19, 6) NULL,
	[GrosProfit] numeric(19, 6) NULL,
	[GrosProfFC] numeric(19, 6) NULL,
	[Ref1] nvarchar(11) NULL,
	[Ref2] nvarchar(11) NULL,
	[Comments] nvarchar(254) NULL,
	[JrnlMemo] nvarchar(50) NULL,
	[TransId] int NULL,
	[ReceiptNum] int NULL,
	[GroupNum] smallint NULL,
	[DocTime] smallint NULL,
	[SlpCode] int NULL DEFAULT ((-1)),
	[TrnspCode] smallint NULL DEFAULT ((-1)),
	[PartSupply] char(1) NULL DEFAULT ('Y'),
	[Confirmed] char(1) NULL DEFAULT ('Y'),
	[GrossBase] smallint NULL DEFAULT ((0)),
	[ImportEnt] int NULL,
	[CreateTran] char(1) NULL DEFAULT ('N'),
	[SummryType] char(1) NULL DEFAULT ('N'),
	[UpdInvnt] char(1) NULL DEFAULT ('N'),
	[UpdCardBal] char(1) NULL DEFAULT ('N'),
	[Instance] smallint NULL DEFAULT ((0)),
	[Flags] int NULL DEFAULT ((0)),
	[InvntDirec] char(1) NULL DEFAULT ('X'),
	[CntctCode] int NULL,
	[ShowSCN] char(1) NULL DEFAULT ('N'),
	[FatherCard] nvarchar(15) NULL,
	[SysRate] numeric(19, 6) NULL,
	[CurSource] char(1) NULL DEFAULT ('C'),
	[VatSumSy] numeric(19, 6) NULL,
	[DiscSumSy] numeric(19, 6) NULL,
	[DocTotalSy] numeric(19, 6) NULL,
	[PaidSys] numeric(19, 6) NULL,
	[FatherType] char(1) NULL DEFAULT ('P'),
	[GrosProfSy] numeric(19, 6) NULL,
	[UpdateDate] datetime NULL,
	[IsICT] char(1) NULL DEFAULT ('N'),
	[CreateDate] datetime NULL,
	[Volume] numeric(19, 6) NULL,
	[VolUnit] smallint NULL,
	[Weight] numeric(19, 6) NULL,
	[WeightUnit] smallint NULL,
	[Series] int NULL,
	[TaxDate] datetime NULL,
	[Filler] nvarchar(8) NULL,
	[DataSource] char(1) NULL DEFAULT ('N'),
	[StampNum] nvarchar(16) NULL,
	[isCrin] char(1) NULL DEFAULT ('N'),
	[FinncPriod] int NULL,
	[UserSign] smallint NULL,
	[selfInv] char(1) NULL DEFAULT ('N'),
	[VatPaid] numeric(19, 6) NULL,
	[VatPaidFC] numeric(19, 6) NULL,
	[VatPaidSys] numeric(19, 6) NULL,
	[UserSign2] smallint NULL,
	[WddStatus] char(1) NULL DEFAULT ('-'),
	[draftKey] int NULL DEFAULT ((-1)),
	[TotalExpns] numeric(19, 6) NULL,
	[TotalExpFC] numeric(19, 6) NULL,
	[TotalExpSC] numeric(19, 6) NULL,
	[DunnLevel] int NULL,
	[Address2] nvarchar(254) NULL,
	[LogInstanc] int NULL DEFAULT ((0)),
	[Exported] char(1) NULL DEFAULT ('N'),
	[StationID] int NULL,
	[Indicator] nvarchar(2) NULL,
	[NetProc] char(1) NULL DEFAULT ('N'),
	[AqcsTax] numeric(19, 6) NULL,
	[AqcsTaxFC] numeric(19, 6) NULL,
	[AqcsTaxSC] numeric(19, 6) NULL,
	[CashDiscPr] numeric(19, 6) NULL,
	[CashDiscnt] numeric(19, 6) NULL,
	[CashDiscFC] numeric(19, 6) NULL,
	[CashDiscSC] numeric(19, 6) NULL,
	[ShipToCode] nvarchar(50) NULL,
	[LicTradNum] nvarchar(32) NULL,
	[PaymentRef] nvarchar(27) NULL,
	[WTSum] numeric(19, 6) NULL,
	[WTSumFC] numeric(19, 6) NULL,
	[WTSumSC] numeric(19, 6) NULL,
	[RoundDif] numeric(19, 6) NULL,
	[RoundDifFC] numeric(19, 6) NULL,
	[RoundDifSy] numeric(19, 6) NULL,
	[CheckDigit] char(1) NULL,
	[Form1099] int NULL,
	[Box1099] nvarchar(20) NULL,
	[submitted] char(1) NULL DEFAULT ('N'),
	[PoPrss] char(1) NULL DEFAULT ('N'),
	[Rounding] char(1) NULL DEFAULT ('N'),
	[RevisionPo] char(1) NULL DEFAULT ('N'),
	[Segment] smallint NULL DEFAULT ((0)),
	[ReqDate] datetime NULL,
	[CancelDate] datetime NULL,
	[PickStatus] char(1) NULL DEFAULT ('N'),
	[Pick] char(1) NULL DEFAULT ('N'),
	[BlockDunn] char(1) NULL DEFAULT ('N'),
	[PeyMethod] nvarchar(15) NULL,
	[PayBlock] char(1) NULL DEFAULT ('N'),
	[PayBlckRef] int NULL,
	[MaxDscn] char(1) NULL DEFAULT ('N'),
	[Reserve] char(1) NULL DEFAULT ('N'),
	[Max1099] numeric(19, 6) NULL,
	[CntrlBnk] nvarchar(15) NULL,
	[PickRmrk] nvarchar(254) NULL,
	[ISRCodLine] nvarchar(53) NULL,
	[ExpAppl] numeric(19, 6) NULL,
	[ExpApplFC] numeric(19, 6) NULL,
	[ExpApplSC] numeric(19, 6) NULL,
	[Project] nvarchar(20) NULL,
	[DeferrTax] char(1) NULL,
	[LetterNum] nvarchar(20) NULL,
	[FromDate] datetime NULL,
	[ToDate] datetime NULL,
	[WTApplied] numeric(19, 6) NULL,
	[WTAppliedF] numeric(19, 6) NULL,
	[BoeReserev] char(1) NULL DEFAULT ('N'),
	[AgentCode] nvarchar(32) NULL,
	[WTAppliedS] numeric(19, 6) NULL,
	[EquVatSum] numeric(19, 6) NULL,
	[EquVatSumF] numeric(19, 6) NULL,
	[EquVatSumS] numeric(19, 6) NULL,
	[Installmnt] smallint NULL DEFAULT ((1)),
	[VATFirst] char(1) NULL,
	[NnSbAmnt] numeric(19, 6) NULL,
	[NnSbAmntSC] numeric(19, 6) NULL,
	[NbSbAmntFC] numeric(19, 6) NULL,
	[ExepAmnt] numeric(19, 6) NULL,
	[ExepAmntSC] numeric(19, 6) NULL,
	[ExepAmntFC] numeric(19, 6) NULL,
	[VatDate] datetime NULL,
	[CorrExt] nvarchar(25) NULL,
	[CorrInv] int NULL,
	[NCorrInv] int NULL,
	[CEECFlag] char(1) NULL DEFAULT ('N'),
	[BaseAmnt] numeric(19, 6) NULL,
	[BaseAmntSC] numeric(19, 6) NULL,
	[BaseAmntFC] numeric(19, 6) NULL,
	[CtlAccount] nvarchar(15) NULL,
	[BPLId] int NULL,
	[BPLName] nvarchar(100) NULL,
	[VATRegNum] nvarchar(32) NULL,
	[TxInvRptNo] nvarchar(10) NULL,
	[TxInvRptDt] datetime NULL,
	[KVVATCode] ntext NULL,
	[WTDetails] nvarchar(100) NULL,
	[SumAbsId] int NULL DEFAULT ((-1)),
	[SumRptDate] datetime NULL,
	[PIndicator] nvarchar(10) NULL DEFAULT (' '),
	[ManualNum] nvarchar(20) NULL,
	[UseShpdGd] char(1) NULL DEFAULT ('N'),
	[BaseVtAt] numeric(19, 6) NULL,
	[BaseVtAtSC] numeric(19, 6) NULL,
	[BaseVtAtFC] numeric(19, 6) NULL,
	[NnSbVAt] numeric(19, 6) NULL,
	[NnSbVAtSC] numeric(19, 6) NULL,
	[NbSbVAtFC] numeric(19, 6) NULL,
	[ExptVAt] numeric(19, 6) NULL,
	[ExptVAtSC] numeric(19, 6) NULL,
	[ExptVAtFC] numeric(19, 6) NULL,
	[LYPmtAt] numeric(19, 6) NULL,
	[LYPmtAtSC] numeric(19, 6) NULL,
	[LYPmtAtFC] numeric(19, 6) NULL,
	[ExpAnSum] numeric(19, 6) NULL,
	[ExpAnSys] numeric(19, 6) NULL,
	[ExpAnFrgn] numeric(19, 6) NULL,
	[DocSubType] nvarchar(2) NULL DEFAULT ('--'),
	[DpmStatus] char(1) NULL DEFAULT ('O'),
	[DpmAmnt] numeric(19, 6) NULL,
	[DpmAmntSC] numeric(19, 6) NULL,
	[DpmAmntFC] numeric(19, 6) NULL,
	[DpmDrawn] char(1) NULL DEFAULT ('N'),
	[DpmPrcnt] numeric(19, 6) NULL,
	[PaidSum] numeric(19, 6) NULL,
	[PaidSumFc] numeric(19, 6) NULL,
	[PaidSumSc] numeric(19, 6) NULL,
	[FolioPref] nvarchar(4) NULL,
	[FolioNum] int NULL,
	[DpmAppl] numeric(19, 6) NULL,
	[DpmApplFc] numeric(19, 6) NULL,
	[DpmApplSc] numeric(19, 6) NULL,
	[LPgFolioN] int NULL,
	[Header] ntext NULL,
	[Footer] ntext NULL,
	[Posted] char(1) NULL DEFAULT ('Y'),
	[OwnerCode] int NULL,
	[BPChCode] nvarchar(15) NULL,
	[BPChCntc] int NULL,
	[PayToCode] nvarchar(50) NULL,
	[IsPaytoBnk] char(1) NULL,
	[BnkCntry] nvarchar(3) NULL,
	[BankCode] nvarchar(30) NULL,
	[BnkAccount] nvarchar(50) NULL,
	[BnkBranch] nvarchar(50) NULL,
	[isIns] char(1) NULL DEFAULT ('N'),
	[TrackNo] nvarchar(30) NULL,
	[VersionNum] nvarchar(11) NULL,
	[LangCode] int NULL,
	[BPNameOW] char(1) NULL DEFAULT ('N'),
	[BillToOW] char(1) NULL DEFAULT ('N'),
	[ShipToOW] char(1) NULL DEFAULT ('N'),
	[RetInvoice] char(1) NULL DEFAULT ('N'),
	[ClsDate] datetime NULL,
	[MInvNum] int NULL,
	[MInvDate] datetime NULL,
	[SeqCode] smallint NULL,
	[Serial] int NULL,
	[SeriesStr] nvarchar(3) NULL,
	[SubStr] nvarchar(3) NULL,
	[Model] nvarchar(6) NULL DEFAULT ('0'),
	[TaxOnExp] numeric(19, 6) NULL,
	[TaxOnExpFc] numeric(19, 6) NULL,
	[TaxOnExpSc] numeric(19, 6) NULL,
	[TaxOnExAp] numeric(19, 6) NULL,
	[TaxOnExApF] numeric(19, 6) NULL,
	[TaxOnExApS] numeric(19, 6) NULL,
	[LastPmnTyp] char(1) NULL,
	[LndCstNum] int NULL,
	[UseCorrVat] char(1) NULL DEFAULT ('N'),
	[BlkCredMmo] char(1) NULL DEFAULT ('N'),
	[OpenForLaC] char(1) NULL DEFAULT ('Y'),
	[Excised] char(1) NULL DEFAULT ('O'),
	[ExcRefDate] datetime NULL,
	[ExcRmvTime] nvarchar(8) NULL,
	[SrvGpPrcnt] numeric(19, 6) NULL,
	[DepositNum] int NULL,
	[CertNum] nvarchar(31) NULL,
	[DutyStatus] char(1) NULL DEFAULT ('Y'),
	[AutoCrtFlw] char(1) NULL DEFAULT ('N'),
	[FlwRefDate] datetime NULL,
	[FlwRefNum] nvarchar(100) NULL,
	[VatJENum] int NULL DEFAULT ((-1)),
	[DpmVat] numeric(19, 6) NULL,
	[DpmVatFc] numeric(19, 6) NULL,
	[DpmVatSc] numeric(19, 6) NULL,
	[DpmAppVat] numeric(19, 6) NULL,
	[DpmAppVatF] numeric(19, 6) NULL,
	[DpmAppVatS] numeric(19, 6) NULL,
	[InsurOp347] char(1) NULL DEFAULT ('N'),
	[IgnRelDoc] char(1) NULL DEFAULT ('N'),
	[BuildDesc] nvarchar(50) NULL,
	[ResidenNum] char(1) NULL DEFAULT ('1'),
	[Checker] int NULL,
	[Payee] int NULL,
	[CopyNumber] int NULL DEFAULT ((0)),
	[SSIExmpt] char(1) NULL,
	[PQTGrpSer] int NULL,
	[PQTGrpNum] int NULL,
	[PQTGrpHW] char(1) NULL DEFAULT ('N'),
	[ReopOriDoc] char(1) NULL,
	[ReopManCls] char(1) NULL,
	[DocManClsd] char(1) NULL DEFAULT ('N'),
	[ClosingOpt] smallint NULL DEFAULT ((1)),
	[SpecDate] datetime NULL,
	[Ordered] char(1) NULL DEFAULT ('N'),
	[NTSApprov] char(1) NULL DEFAULT ('N'),
	[NTSWebSite] smallint NULL,
	[NTSeTaxNo] nvarchar(50) NULL,
	[NTSApprNo] nvarchar(50) NULL,
	[PayDuMonth] char(1) NULL,
	[ExtraMonth] smallint NULL,
	[ExtraDays] smallint NULL,
	[CdcOffset] smallint NULL DEFAULT ((0)),
	[SignMsg] ntext NULL,
	[SignDigest] ntext NULL,
	[CertifNum] nvarchar(50) NULL,
	[KeyVersion] int NULL,
	[EDocGenTyp] char(1) NULL DEFAULT ('N'),
	[ESeries] smallint NULL,
	[EDocNum] nvarchar(50) NULL,
	[EDocExpFrm] int NULL,
	[OnlineQuo] char(1) NULL DEFAULT ('N'),
	[POSEqNum] nvarchar(20) NULL,
	[POSManufSN] nvarchar(20) NULL,
	[POSCashN] int NULL,
	[EDocStatus] char(1) NULL DEFAULT ('C'),
	[EDocCntnt] ntext NULL,
	[EDocProces] char(1) NULL DEFAULT ('C'),
	[EDocErrCod] nvarchar(50) NULL,
	[EDocErrMsg] ntext NULL,
	[EDocCancel] char(1) NULL DEFAULT ('N'),
	[EDocTest] char(1) NULL DEFAULT ('N'),
	[EDocPrefix] nvarchar(10) NULL,
	[CUP] int NULL,
	[CIG] int NULL,
	[DpmAsDscnt] char(1) NULL DEFAULT ('N'),
	[Attachment] ntext NULL,
	[AtcEntry] int NULL,
	[SupplCode] nvarchar(254) NULL,
	[GTSRlvnt] char(1) NULL DEFAULT ('N'),
	[BaseDisc] numeric(19, 6) NULL,
	[BaseDiscSc] numeric(19, 6) NULL,
	[BaseDiscFc] numeric(19, 6) NULL,
	[BaseDiscPr] numeric(19, 6) NULL,
	[CreateTS] int NULL,
	[UpdateTS] int NULL,
	[SrvTaxRule] char(1) NULL DEFAULT ('N'),
	[AnnInvDecR] int NULL,
	[Supplier] nvarchar(15) NULL,
	[Releaser] int NULL,
	[Receiver] int NULL,
	[ToWhsCode] nvarchar(8) NULL,
	[AssetDate] datetime NULL,
	[Requester] nvarchar(25) NULL,
	[ReqName] nvarchar(155) NULL,
	[Branch] smallint NULL,
	[Department] smallint NULL,
	[Email] nvarchar(100) NULL,
	[Notify] char(1) NULL,
	[ReqType] int NULL DEFAULT ((12)),
	[OriginType] char(1) NULL DEFAULT ('M'),
	[IsReuseNum] char(1) NULL DEFAULT ('N'),
	[IsReuseNFN] char(1) NULL DEFAULT ('N'),
	[DocDlvry] char(1) NULL,
	[PaidDpm] numeric(19, 6) NULL,
	[PaidDpmF] numeric(19, 6) NULL,
	[PaidDpmS] numeric(19, 6) NULL,
	[EnvTypeNFe] int NULL DEFAULT ((-1)),
	[AgrNo] int NULL,
	[IsAlt] char(1) NULL DEFAULT ('N'),
	[AltBaseTyp] int NULL DEFAULT ((-1)),
	[AltBaseEnt] int NULL,
	[AuthCode] nvarchar(250) NULL,
	[StDlvDate] datetime NULL,
	[StDlvTime] int NULL,
	[EndDlvDate] datetime NULL,
	[EndDlvTime] int NULL,
	[VclPlate] nvarchar(20) NULL,
	[ElCoStatus] nvarchar(10) NULL,
	[AtDocType] nvarchar(2) NULL,
	[ElCoMsg] nvarchar(254) NULL,
	[PrintSEPA] char(1) NULL DEFAULT ('N'),
	[FreeChrg] numeric(19, 6) NULL,
	[FreeChrgFC] numeric(19, 6) NULL,
	[FreeChrgSC] numeric(19, 6) NULL,
	[NfeValue] numeric(19, 6) NULL,
	[FiscDocNum] nvarchar(100) NULL,
	[RelatedTyp] int NULL DEFAULT ((-1)),
	[RelatedEnt] int NULL,
	[CCDEntry] int NULL,
	[NfePrntFo] int NULL DEFAULT ((0)),
	[ZrdAbs] int NULL,
	[POSRcptNo] int NULL,
	[FoCTax] numeric(19, 6) NULL,
	[FoCTaxFC] numeric(19, 6) NULL,
	[FoCTaxSC] numeric(19, 6) NULL,
	[TpCusPres] int NULL,
	[ExcDocDate] datetime NULL,
	[FoCFrght] numeric(19, 6) NULL,
	[FoCFrghtFC] numeric(19, 6) NULL,
	[FoCFrghtSC] numeric(19, 6) NULL,
	[InterimTyp] smallint NULL DEFAULT ((0)),
	[PTICode] nvarchar(5) NULL,
	[Letter] char(1) NULL,
	[FolNumFrom] int NULL,
	[FolNumTo] int NULL,
	[FolSeries] int NULL,
	[SplitTax] numeric(19, 6) NULL,
	[SplitTaxFC] numeric(19, 6) NULL,
	[SplitTaxSC] numeric(19, 6) NULL,
	[ToBinCode] nvarchar(228) NULL,
	[PriceMode] char(1) NULL,
	[PoDropPrss] char(1) NULL DEFAULT ('N'),
	[PermitNo] nvarchar(20) NULL,
	[MYFtype] nvarchar(2) NULL,
	[DocTaxID] nvarchar(32) NULL,
	[DateReport] datetime NULL,
	[RepSection] nvarchar(3) NULL,
	[ExclTaxRep] char(1) NULL DEFAULT ('N'),
	[PosCashReg] int NULL,
	[DmpTransID] nvarchar(20) NULL,
	[ECommerBP] nvarchar(15) NULL,
	[EComerGSTN] nvarchar(15) NULL,
	[Revision] char(1) NULL DEFAULT ('N'),
	[RevRefNo] nvarchar(100) NULL,
	[RevRefDate] datetime NULL,
	[RevCreRefN] nvarchar(100) NULL,
	[RevCreRefD] datetime NULL,
	[TaxInvNo] nvarchar(100) NULL,
	[FrmBpDate] datetime NULL,
	[GSTTranTyp] nvarchar(2) NULL,
	[BaseType] int NULL DEFAULT ((-1)),
	[BaseEntry] int NULL,
	[ComTrade] char(1) NULL DEFAULT ('E'),
	[UseBilAddr] char(1) NULL,
	[IssReason] smallint NULL DEFAULT ((1)),
	[ComTradeRt] char(1) NULL DEFAULT ('N'),
	[SplitPmnt] char(1) NULL DEFAULT ('N'),
	[SOIWizId] int NULL,
	[SelfPosted] char(1) NULL DEFAULT ('N'),
	[EnBnkAcct] ntext NULL,
	[EncryptIV] nvarchar(100) NULL,
	[U_FacNit] nvarchar(20) NULL,
	[U_FacNom] nvarchar(100) NULL,
	[U_FacFecha] datetime NULL,
	[U_TNegocio] nvarchar(20) NULL,
	[U_CGastos] nvarchar(10) NOT NULL DEFAULT ('09'),
	[U_FacSerie] nvarchar(30) NULL,
	[U_TipoNC] nvarchar(30) NULL,
	[U_FACNUM] nvarchar(19) NULL,
	[U_NCO] nvarchar(19) NULL,
	[U_TipoIngreso] nvarchar(2) NULL DEFAULT ('01'),
	[U_ParteRel] nvarchar(2) NULL DEFAULT ('02'),
	[U_609TS] nvarchar(50) NULL,
	[U_FV6] datetime NULL,
	[U_PuntoEmision] int NULL,
	[U_user] nvarchar(20) NULL,
	[U_terminal] nvarchar(20) NULL,
	[U_OrdPedido] varchar(10) NULL,
	[U_Chofer] int NULL,
	[U_Ficha] nvarchar(10) NULL,
	[U_SCIASerie] nvarchar(10) NULL,
	[U_SCIADocNum] int NULL,
	[U_NCFVence] datetime NULL,
	[C_DocNet] numeric(19, 6) NULL,
	[U_DescLinea] numeric(19, 6) NULL DEFAULT ((0)),
	[C_ControlIndex] int NULL,
	[C_ListNum] int NULL DEFAULT ((1)),
	[C_ListName] nvarchar(30) NULL DEFAULT (''),
	[C_Inserted] datetime NULL DEFAULT (getdate()),
	[C_Update] nchar(1) NULL DEFAULT ('N'),
	[C_DocStatus] char(1) NOT NULL DEFAULT ('O'),
	[C_DocOrigen] nvarchar(8) NULL,
	[C_DocOrigenNum] int NULL,
	[C_PuntosSum] numeric(18, 2) NULL DEFAULT ((0)),
	[C_FromNumAtCard] nvarchar(100) NULL DEFAULT (''),
	[C_FromObjType] nvarchar(20) NULL DEFAULT (''),
	[C_FromC_DocEntry] int NULL DEFAULT ((0)),
	[C_Phone] varchar(50) NULL DEFAULT (''),
	[C_Updated] datetime NULL,
	CONSTRAINT [POSORIN_PRIMARY] PRIMARY KEY CLUSTERED ([C_DocEntry] ASC)
);
GO

-- Tabla: POSOSTC
CREATE TABLE [dbo].[POSOSTC] (
	[Code] nvarchar(8) NOT NULL,
	[Name] nvarchar(100) NULL,
	[Rate] numeric(19, 6) NULL,
	[Freight] char(1) NULL DEFAULT ('N'),
	[UserSign] smallint NULL,
	[ValidForAR] char(1) NULL DEFAULT ('Y'),
	[ValidForAP] char(1) NULL DEFAULT ('Y'),
	[TfcId] int NULL,
	[Lock] char(1) NULL DEFAULT ('N'),
	[TaxIcms] nvarchar(2) NULL,
	[IsItmLevel] char(1) NULL DEFAULT ('N'),
	[CfopIn] nvarchar(6) NULL,
	[CfopOut] nvarchar(6) NULL,
	[LogInstanc] int NULL DEFAULT ((0)),
	[UserSign2] smallint NULL,
	[UpdateDate] datetime NULL,
	[FADebit] char(1) NULL DEFAULT ('N'),
	[IsSystem] char(1) NULL DEFAULT ('N'),
	[C_Indicador] varchar(2) NULL,
	[C_Descripcion] varchar(20) NULL,
	CONSTRAINT [OSTC_PRIMARY] PRIMARY KEY CLUSTERED ([Code] ASC)
);
GO

-- Tabla: posOWHS
CREATE TABLE [dbo].[posOWHS] (
	[WhsCode] nvarchar(8) NOT NULL,
	[WhsName] nvarchar(100) NOT NULL,
	CONSTRAINT [OWHS_PRIMARY] PRIMARY KEY CLUSTERED ([WhsCode] ASC)
);
GO

-- Tabla: POSRIN1
CREATE TABLE [dbo].[POSRIN1] (
	[C_Id] int IDENTITY(1,1) NOT NULL,
	[C_DocEntry] int NOT NULL,
	[DocEntry] int NULL DEFAULT ((0)),
	[LineNum] int NOT NULL,
	[TargetType] int NULL DEFAULT ((-1)),
	[TrgetEntry] int NULL,
	[BaseRef] nvarchar(16) NULL,
	[BaseType] int NULL DEFAULT ((-1)),
	[BaseEntry] int NULL,
	[BaseLine] int NULL,
	[LineStatus] char(1) NULL DEFAULT ('O'),
	[ItemCode] nvarchar(50) NULL,
	[Dscription] nvarchar(100) NULL,
	[Quantity] numeric(19, 6) NULL,
	[ShipDate] datetime NULL,
	[OpenQty] numeric(19, 6) NULL,
	[Price] numeric(19, 6) NULL,
	[Currency] nvarchar(3) NULL,
	[Rate] numeric(19, 6) NULL,
	[DiscPrcnt] numeric(19, 6) NULL,
	[LineTotal] numeric(19, 6) NULL,
	[TotalFrgn] numeric(19, 6) NULL,
	[OpenSum] numeric(19, 6) NULL,
	[OpenSumFC] numeric(19, 6) NULL,
	[VendorNum] nvarchar(50) NULL,
	[SerialNum] nvarchar(17) NULL,
	[WhsCode] nvarchar(8) NULL,
	[SlpCode] int NULL DEFAULT ((-1)),
	[Commission] numeric(19, 6) NULL,
	[TreeType] char(1) NULL DEFAULT ('N'),
	[AcctCode] nvarchar(15) NULL,
	[TaxStatus] char(1) NULL,
	[GrossBuyPr] numeric(19, 6) NULL,
	[PriceBefDi] numeric(19, 6) NULL,
	[DocDate] datetime NULL,
	[Flags] int NULL DEFAULT ((0)),
	[OpenCreQty] numeric(19, 6) NULL,
	[UseBaseUn] char(1) NULL DEFAULT ('N'),
	[SubCatNum] nvarchar(50) NULL,
	[BaseCard] nvarchar(15) NULL,
	[TotalSumSy] numeric(19, 6) NULL,
	[OpenSumSys] numeric(19, 6) NULL,
	[InvntSttus] char(1) NULL DEFAULT ('O'),
	[OcrCode] nvarchar(8) NULL,
	[Project] nvarchar(20) NULL,
	[CodeBars] nvarchar(254) NULL,
	[VatPrcnt] numeric(19, 6) NULL,
	[VatGroup] nvarchar(8) NULL,
	[PriceAfVAT] numeric(19, 6) NULL,
	[Height1] numeric(19, 6) NULL,
	[Hght1Unit] smallint NULL,
	[Height2] numeric(19, 6) NULL,
	[Hght2Unit] smallint NULL,
	[Width1] numeric(19, 6) NULL,
	[Wdth1Unit] smallint NULL,
	[Width2] numeric(19, 6) NULL,
	[Wdth2Unit] smallint NULL,
	[Length1] numeric(19, 6) NULL,
	[Len1Unit] smallint NULL,
	[length2] numeric(19, 6) NULL,
	[Len2Unit] smallint NULL,
	[Volume] numeric(19, 6) NULL,
	[VolUnit] smallint NULL,
	[Weight1] numeric(19, 6) NULL,
	[Wght1Unit] smallint NULL,
	[Weight2] numeric(19, 6) NULL,
	[Wght2Unit] smallint NULL,
	[Factor1] numeric(19, 6) NULL,
	[Factor2] numeric(19, 6) NULL,
	[Factor3] numeric(19, 6) NULL,
	[Factor4] numeric(19, 6) NULL,
	[PackQty] numeric(19, 6) NULL,
	[UpdInvntry] char(1) NULL DEFAULT ('Y'),
	[BaseDocNum] int NULL,
	[BaseAtCard] nvarchar(100) NULL,
	[SWW] nvarchar(16) NULL,
	[VatSum] numeric(19, 6) NULL,
	[VatSumFrgn] numeric(19, 6) NULL,
	[VatSumSy] numeric(19, 6) NULL,
	[FinncPriod] int NULL,
	[ObjType] nvarchar(20) NULL DEFAULT ('14'),
	[LogInstanc] int NULL DEFAULT ((0)),
	[BlockNum] nvarchar(100) NULL,
	[ImportLog] nvarchar(20) NULL,
	[DedVatSum] numeric(19, 6) NULL,
	[DedVatSumF] numeric(19, 6) NULL,
	[DedVatSumS] numeric(19, 6) NULL,
	[IsAqcuistn] char(1) NULL DEFAULT ('N'),
	[DistribSum] numeric(19, 6) NULL,
	[DstrbSumFC] numeric(19, 6) NULL,
	[DstrbSumSC] numeric(19, 6) NULL,
	[GrssProfit] numeric(19, 6) NULL,
	[GrssProfSC] numeric(19, 6) NULL,
	[GrssProfFC] numeric(19, 6) NULL,
	[VisOrder] int NULL,
	[INMPrice] numeric(19, 6) NULL,
	[PoTrgNum] int NULL,
	[PoTrgEntry] nvarchar(11) NULL,
	[DropShip] char(1) NULL DEFAULT ('N'),
	[PoLineNum] int NULL,
	[Address] nvarchar(254) NULL,
	[TaxCode] nvarchar(8) NULL,
	[TaxType] char(1) NULL,
	[OrigItem] nvarchar(50) NULL,
	[BackOrdr] char(1) NULL,
	[FreeTxt] nvarchar(100) NULL,
	[PickStatus] char(1) NULL DEFAULT ('N'),
	[PickOty] numeric(19, 6) NULL,
	[PickIdNo] int NULL,
	[TrnsCode] smallint NULL DEFAULT ((-1)),
	[VatAppld] numeric(19, 6) NULL,
	[VatAppldFC] numeric(19, 6) NULL,
	[VatAppldSC] numeric(19, 6) NULL,
	[BaseQty] numeric(19, 6) NULL,
	[BaseOpnQty] numeric(19, 6) NULL,
	[VatDscntPr] numeric(19, 6) NULL,
	[WtLiable] char(1) NULL,
	[DeferrTax] char(1) NULL DEFAULT ('N'),
	[EquVatPer] numeric(19, 6) NULL,
	[EquVatSum] numeric(19, 6) NULL,
	[EquVatSumF] numeric(19, 6) NULL,
	[EquVatSumS] numeric(19, 6) NULL,
	[LineVat] numeric(19, 6) NULL,
	[LineVatlF] numeric(19, 6) NULL,
	[LineVatS] numeric(19, 6) NULL,
	[unitMsr] nvarchar(100) NOT NULL DEFAULT (N'UND'),
	[NumPerMsr] numeric(19, 6) NULL,
	[CEECFlag] char(1) NULL DEFAULT ('S'),
	[ToStock] numeric(19, 6) NULL,
	[ToDiff] numeric(19, 6) NULL,
	[ExciseAmt] numeric(19, 6) NULL,
	[TaxPerUnit] numeric(19, 6) NULL,
	[TotInclTax] numeric(19, 6) NULL,
	[CountryOrg] nvarchar(3) NULL,
	[StckDstSum] numeric(19, 6) NULL,
	[ReleasQtty] numeric(19, 6) NULL,
	[LineType] char(1) NULL DEFAULT ('R'),
	[TranType] char(1) NULL,
	[Text] ntext NULL,
	[OwnerCode] int NULL,
	[StockPrice] numeric(19, 6) NULL,
	[ConsumeFCT] char(1) NULL,
	[LstByDsSum] numeric(19, 6) NULL,
	[StckINMPr] numeric(19, 6) NULL,
	[LstBINMPr] numeric(19, 6) NULL,
	[StckDstFc] numeric(19, 6) NULL,
	[StckDstSc] numeric(19, 6) NULL,
	[LstByDsFc] numeric(19, 6) NULL,
	[LstByDsSc] numeric(19, 6) NULL,
	[StockSum] numeric(19, 6) NULL,
	[StockSumFc] numeric(19, 6) NULL,
	[StockSumSc] numeric(19, 6) NULL,
	[StckSumApp] numeric(19, 6) NULL,
	[StckAppFc] numeric(19, 6) NULL,
	[StckAppSc] numeric(19, 6) NULL,
	[ShipToCode] nvarchar(50) NULL,
	[ShipToDesc] nvarchar(254) NULL,
	[StckAppD] numeric(19, 6) NULL,
	[StckAppDFC] numeric(19, 6) NULL,
	[StckAppDSC] numeric(19, 6) NULL,
	[BasePrice] char(1) NULL DEFAULT ('E'),
	[GTotal] numeric(19, 6) NULL,
	[GTotalFC] numeric(19, 6) NULL,
	[GTotalSC] numeric(19, 6) NULL,
	[DistribExp] char(1) NULL,
	[DescOW] char(1) NULL DEFAULT ('N'),
	[DetailsOW] char(1) NULL DEFAULT ('N'),
	[GrossBase] smallint NULL,
	[VatWoDpm] numeric(19, 6) NULL,
	[VatWoDpmFc] numeric(19, 6) NULL,
	[VatWoDpmSc] numeric(19, 6) NULL,
	[CFOPCode] nvarchar(6) NULL,
	[CSTCode] nvarchar(6) NULL,
	[Usage] int NULL,
	[TaxOnly] char(1) NULL,
	[WtCalced] char(1) NULL DEFAULT ('N'),
	[QtyToShip] numeric(19, 6) NULL,
	[DelivrdQty] numeric(19, 6) NULL,
	[OrderedQty] numeric(19, 6) NULL,
	[CogsOcrCod] nvarchar(8) NULL,
	[CiOppLineN] int NULL DEFAULT ((-1)),
	[CogsAcct] nvarchar(15) NULL,
	[ChgAsmBoMW] char(1) NULL,
	[ActDelDate] datetime NULL,
	[OcrCode2] nvarchar(8) NULL,
	[OcrCode3] nvarchar(8) NULL,
	[OcrCode4] nvarchar(8) NULL,
	[OcrCode5] nvarchar(8) NULL,
	[TaxDistSum] numeric(19, 6) NULL,
	[TaxDistSFC] numeric(19, 6) NULL,
	[TaxDistSSC] numeric(19, 6) NULL,
	[PostTax] char(1) NULL DEFAULT ('Y'),
	[Excisable] char(1) NULL,
	[AssblValue] numeric(19, 6) NULL,
	[RG23APart1] int NULL,
	[RG23APart2] int NULL,
	[RG23CPart1] int NULL,
	[RG23CPart2] int NULL,
	[CogsOcrCo2] nvarchar(8) NULL,
	[CogsOcrCo3] nvarchar(8) NULL,
	[CogsOcrCo4] nvarchar(8) NULL,
	[CogsOcrCo5] nvarchar(8) NULL,
	[LnExcised] char(1) NULL,
	[LocCode] int NULL,
	[StockValue] numeric(19, 6) NULL,
	[GPTtlBasPr] numeric(19, 6) NULL,
	[unitMsr2] nvarchar(100) NULL,
	[NumPerMsr2] numeric(19, 6) NULL,
	[SpecPrice] char(1) NULL DEFAULT ('N'),
	[CSTfIPI] nvarchar(2) NULL,
	[CSTfPIS] nvarchar(2) NULL,
	[CSTfCOFINS] nvarchar(2) NULL,
	[ExLineNo] nvarchar(10) NULL,
	[isSrvCall] char(1) NULL DEFAULT ('N'),
	[PQTReqQty] numeric(19, 6) NULL,
	[PQTReqDate] datetime NULL,
	[PcDocType] int NULL DEFAULT ((-1)),
	[PcQuantity] numeric(19, 6) NULL,
	[LinManClsd] char(1) NULL DEFAULT ('N'),
	[VatGrpSrc] char(1) NULL DEFAULT ('N'),
	[NoInvtryMv] char(1) NULL DEFAULT ('N'),
	[ActBaseEnt] int NULL,
	[ActBaseLn] int NULL,
	[ActBaseNum] int NULL,
	[OpenRtnQty] numeric(19, 6) NULL,
	[AgrNo] int NULL,
	[AgrLnNum] int NULL,
	[CredOrigin] char(1) NULL,
	[Surpluses] numeric(19, 6) NULL,
	[DefBreak] numeric(19, 6) NULL,
	[Shortages] numeric(19, 6) NULL,
	[UomEntry] int NULL DEFAULT ((0)),
	[UomEntry2] int NULL DEFAULT ((0)),
	[UomCode] nvarchar(20) NULL,
	[UomCode2] nvarchar(20) NULL,
	[FromWhsCod] nvarchar(8) NULL,
	[NeedQty] char(1) NULL DEFAULT ('N'),
	[PartRetire] char(1) NULL DEFAULT ('N'),
	[RetireQty] numeric(19, 6) NULL,
	[RetireAPC] numeric(19, 6) NULL,
	[RetirAPCFC] numeric(19, 6) NULL,
	[RetirAPCSC] numeric(19, 6) NULL,
	[InvQty] numeric(19, 6) NULL,
	[OpenInvQty] numeric(19, 6) NULL,
	[EnSetCost] char(1) NULL DEFAULT ('N'),
	[RetCost] numeric(19, 6) NULL,
	[Incoterms] int NULL DEFAULT ((0)),
	[TransMod] int NULL DEFAULT ((0)),
	[LineVendor] nvarchar(15) NULL,
	[DistribIS] char(1) NULL DEFAULT ('N'),
	[ISDistrb] numeric(19, 6) NULL,
	[ISDistrbFC] numeric(19, 6) NULL,
	[ISDistrbSC] numeric(19, 6) NULL,
	[IsByPrdct] char(1) NULL DEFAULT ('N'),
	[ItemType] int NULL DEFAULT ((4)),
	[PriceEdit] char(1) NULL DEFAULT ('N'),
	[PrntLnNum] int NULL,
	[LinePoPrss] char(1) NULL DEFAULT ('N'),
	[FreeChrgBP] char(1) NULL DEFAULT ('N'),
	[TaxRelev] char(1) NULL DEFAULT ('Y'),
	[LegalText] nvarchar(254) NULL,
	[ThirdParty] char(1) NULL DEFAULT ('N'),
	[LicTradNum] nvarchar(32) NULL,
	[InvQtyOnly] char(1) NULL DEFAULT ('N'),
	[UnencReasn] int NULL,
	[ShipFromCo] nvarchar(50) NULL,
	[ShipFromDe] nvarchar(254) NULL,
	[FisrtBin] nvarchar(228) NULL,
	[AllocBinC] nvarchar(11) NULL,
	[ExpType] nvarchar(4) NULL,
	[ExpUUID] nvarchar(50) NULL,
	[ExpOpType] char(1) NULL,
	[DIOTNat] nvarchar(3) NULL,
	[MYFtype] nvarchar(2) NULL,
	[GPBefDisc] numeric(19, 6) NULL,
	[ReturnRsn] smallint NULL DEFAULT ((-1)),
	[ReturnAct] smallint NULL DEFAULT ((-1)),
	[StgSeqNum] int NULL,
	[StgEntry] int NULL,
	[StgDesc] nvarchar(100) NULL,
	[ItmTaxType] nvarchar(2) NULL,
	[SacEntry] int NULL,
	[NCMCode] int NULL DEFAULT ((-1)),
	[HsnEntry] int NULL,
	[OriBAbsEnt] int NULL,
	[OriBLinNum] int NULL,
	[OriBDocTyp] int NULL,
	[U_TipoMonto] char(1) NULL DEFAULT ('B'),
	CONSTRAINT [POSRIN1_PRIMARY] PRIMARY KEY CLUSTERED ([C_Id] ASC, [LineNum] ASC)
);
GO

-- Tabla: retiro_caja
CREATE TABLE [dbo].[retiro_caja] (
	[id_retiro_caja] int IDENTITY(1,1) NOT NULL,
	[id_diario_caja] int NOT NULL,
	[id_concepto_retiro] int NOT NULL,
	[fecha] datetime2 NOT NULL DEFAULT (getdate()),
	[monto] numeric(15, 2) NOT NULL,
	[referencia] nvarchar(20) NULL,
	[quien_entrega] nvarchar(50) NULL,
	[quien_recibe] nvarchar(50) NULL,
	[comentario] text NOT NULL,
	[anulado] char(1) NOT NULL DEFAULT ('N'),
	[id_medio_retiro] int NULL,
	[c_posted] char(1) NOT NULL DEFAULT ('N'),
	CONSTRAINT [PK_retiro_caja] PRIMARY KEY CLUSTERED ([id_retiro_caja] ASC)
);
GO

-- Tabla: retiro_denominacion
CREATE TABLE [dbo].[retiro_denominacion] (
	[id_retiro_denominacion] int IDENTITY(1,1) NOT NULL,
	[id_retiro_caja] int NOT NULL,
	[denominacion] numeric(15, 2) NOT NULL,
	[cantidad] int NOT NULL,
	[total] numeric(15, 2) NOT NULL,
	CONSTRAINT [PK_retiro_denominacion] PRIMARY KEY CLUSTERED ([id_retiro_denominacion] ASC)
);
GO

-- Tabla: secuencia
CREATE TABLE [dbo].[secuencia] (
	[secuencia] int NOT NULL,
	[tipo] varchar(30) NOT NULL,
	CONSTRAINT [PK_dbo_seciencia] PRIMARY KEY CLUSTERED ([tipo] ASC)
);
GO

-- Tabla: serie
CREATE TABLE [dbo].[serie] (
	[id_serie] nchar(1) NOT NULL,
	[descripcion] nvarchar(100) NULL,
	[id_division] int NULL DEFAULT ((0)),
	[activo] char(1) NOT NULL DEFAULT ('N'),
	[contra_entrega] nchar(1) NULL DEFAULT ('N'),
	[cambio_fecha] char(1) NULL DEFAULT ('N'),
	[validar_inventario] char(1) NOT NULL DEFAULT ('N'),
	[clientes_serie] char(1) NOT NULL DEFAULT ('N'),
	[fct_contado] char(1) NOT NULL DEFAULT ('N'),
	CONSTRAINT [serie_pk] PRIMARY KEY CLUSTERED ([id_serie] ASC)
);
GO

-- Tabla: serie_ncf
CREATE TABLE [dbo].[serie_ncf] (
	[serie] char(3) NOT NULL,
	[descripcion] varchar(50) NOT NULL,
	[longitud] int NOT NULL,
	[alerta] int NOT NULL,
	[activo] char(1) NOT NULL,
	[tipo_ncf] int NOT NULL DEFAULT ((0)),
	[valida_rnc] char(1) NOT NULL DEFAULT ('Y'),
	CONSTRAINT [PK__serie_nc__DFD1906B62C01CE1] PRIMARY KEY CLUSTERED ([serie] ASC)
);
GO

-- Tabla: terminal
CREATE TABLE [dbo].[terminal] (
	[terminal] nvarchar(20) NOT NULL,
	[id_dpto] int NULL,
	[descripcion] nvarchar(100) NULL,
	[os] nvarchar(100) NULL,
	[os_architecture] nvarchar(30) NULL,
	[ip_address] nvarchar(15) NULL,
	[last_user] nvarchar(20) NULL,
	[activo] nchar(1) NULL DEFAULT ('Y'),
	[inserted] datetime NOT NULL DEFAULT (getdate()),
	[updated] datetime NULL,
	[java_version] varchar(30) NULL,
	[scia_version] varchar(30) NULL,
	[app_version] nvarchar(30) NULL,
	[app_size] nvarchar(30) NULL,
	[hostname] nvarchar(20) NOT NULL DEFAULT (''),
	[is_pos] char(1) NOT NULL DEFAULT ('N'),
	[touchscreen] char(1) NULL DEFAULT ('N'),
	CONSTRAINT [PK_terminal] PRIMARY KEY CLUSTERED ([terminal] ASC)
);
GO

-- Tabla: tipo_movimiento
CREATE TABLE [dbo].[tipo_movimiento] (
	[id_tipo_movimiento] int IDENTITY(1,1) NOT NULL,
	[descripcion] varchar(50) NOT NULL,
	[tipo_pago] char(1) NOT NULL,
	[activo] char(1) NOT NULL DEFAULT ('Y'),
	[id_tipo_pago] int NOT NULL DEFAULT ((1)),
	CONSTRAINT [PK__tipo_mov__5AA8858632EF66C1] PRIMARY KEY CLUSTERED ([id_tipo_movimiento] ASC)
);
GO

-- Tabla: tipo_pago
CREATE TABLE [dbo].[tipo_pago] (
	[id_tipo_pago] int NOT NULL,
	[descripcion] nvarchar(20) NOT NULL,
	CONSTRAINT [PK_tipo_pago] PRIMARY KEY CLUSTERED ([id_tipo_pago] ASC)
);
GO

-- Tabla: TIPOS_ANULACION
CREATE TABLE [dbo].[TIPOS_ANULACION] (
	[Code] nvarchar(50) NOT NULL,
	[Name] nvarchar(100) NOT NULL,
	[U_par_libro] nvarchar(10) NULL,
	CONSTRAINT [PK_TIPOS_ANULACION_430] PRIMARY KEY CLUSTERED ([Code] ASC)
);
GO

-- Tabla: trazabilidad_reclamacion_ars
CREATE TABLE [dbo].[trazabilidad_reclamacion_ars] (
	[id_trazabilidad] int IDENTITY(1,1) NOT NULL,
	[DocEntry_Factura] int NOT NULL,
	[NumAtCard] nvarchar(50) NULL,
	[NCF_Factura] nvarchar(30) NULL,
	[CardCode_ARS] nvarchar(100) NOT NULL,
	[CardName_ARS] nvarchar(200) NULL,
	[MontoFactura] numeric(19, 6) NOT NULL DEFAULT ((0)),
	[Estado] nvarchar(30) NOT NULL DEFAULT ('PENDIENTE'),
	[FechaEstado] datetime NOT NULL DEFAULT (getdate()),
	[Motivo] nvarchar(500) NULL,
	[ReferenciaPago] nvarchar(100) NULL,
	[DocEntry_NC] int NULL,
	[NCF_NC] nvarchar(30) NULL,
	[Usuario] nvarchar(50) NULL,
	[Observaciones] nvarchar(MAX) NULL,
	CONSTRAINT [PK__trazabil__0BA02106EDBE0F35] PRIMARY KEY CLUSTERED ([id_trazabilidad] ASC)
);
GO

-- Tabla: usuario
CREATE TABLE [dbo].[usuario] (
	[id_usuario] int IDENTITY(1,1) NOT NULL,
	[id_departamento] int NOT NULL,
	[usuario] nvarchar(50) NOT NULL,
	[clave] nvarchar(300) NULL DEFAULT ('N/D'),
	[nombre] nvarchar(100) NOT NULL,
	[codigo_empleado] nvarchar(20) NOT NULL,
	[division] nvarchar(100) NOT NULL DEFAULT (''),
	[sap_whscode] nvarchar(8) NULL,
	[sap_slpcode] int NULL,
	[last_access] datetime NULL,
	[last_terminal] nvarchar(20) NOT NULL DEFAULT (''),
	[su] nchar(1) NOT NULL DEFAULT ('N'),
	[block] char(1) NOT NULL DEFAULT ('N'),
	[activo] nchar(1) NOT NULL DEFAULT ('Y'),
	[libra_permitida] int NOT NULL DEFAULT ((0)),
	[descuento_permitido] numeric(4, 2) NOT NULL DEFAULT ((0)),
	[serie] nvarchar(3) NOT NULL DEFAULT ('N/A'),
	[id_serie] char(1) NULL DEFAULT ((0)),
	[U_Cobrador] nvarchar(8) NULL,
	[is_seller] nvarchar(1) NULL DEFAULT (N'N')
);
GO


-- ==========================================================================
-- VISTAS (VIEWS)
-- ==========================================================================

-- Vista: v_sale_item
CREATE VIEW dbo.v_sale_item
AS
SELECT        ItemCode, ItemName, PriceList, ListName, IsGrossPrc, unitMsr, CASE WHEN IsGrossPrc = 'Y' THEN Price / (VatPrcnt / 100 + 1) ELSE Price END AS PriceBefDi, TaxCode, VatPrcnt, WhsCode, OnHand, IsCommited, 
                         OnOrder, validFor, InvntItem, SellItem, U_TipoMonto, CodeBars, DfltWH, ItmsGrpCod, ItmsGrpNam, UserText, PicturName, FirmCode, QryGroup1, QryGroup2, QryGroup3, QryGroup4, QryGroup5, QryGroup6, 
                         QryGroup7, QryGroup8, QryGroup9, QryGroup10, QryGroup11, QryGroup12, QryGroup13, QryGroup14, QryGroup15, QryGroup16, QryGroup17, QryGroup18, QryGroup19, QryGroup20, QryGroup21, QryGroup22, 
                         QryGroup23, QryGroup24, QryGroup25, QryGroup26, QryGroup27, QryGroup28, QryGroup29, QryGroup30, QryGroup31, QryGroup32, QryGroup33, QryGroup34, QryGroup35, QryGroup36, QryGroup37, QryGroup38,
                          QryGroup39, QryGroup40, QryGroup41, QryGroup42, QryGroup43, QryGroup44, QryGroup45, QryGroup46, QryGroup47, QryGroup48, QryGroup49, QryGroup50, QryGroup51, QryGroup52, QryGroup53, 
                         QryGroup54, QryGroup55, QryGroup56, QryGroup57, QryGroup58, QryGroup59, QryGroup60, QryGroup61, QryGroup62, QryGroup63, QryGroup64
FROM            (SELECT        T0.ItemCode, T0.ItemName, T1.PriceList, T3.ListName, T3.IsGrossPrc, T1.Price, ISNULL(T0.SalUnitMsr, '') AS unitMsr, ISNULL(T0.TaxCodeAR, '') AS TaxCode, ISNULL(T2.Rate, 0) AS VatPrcnt, 
                                                    T4.WhsCode, T0.OnHand, T0.IsCommited, T0.OnOrder, T0.validFor, T0.InvntItem, T0.SellItem, T0.U_TipoMonto, ISNULL(T0.CodeBars, '') AS CodeBars, ISNULL(T0.DfltWH, '') AS DfltWH, 
                                                    T0.ItmsGrpCod, T5.ItmsGrpNam, ISNULL(T0.UserText, '') AS UserText, T0.PicturName, T0.FirmCode, T0.QryGroup1, T0.QryGroup2, T0.QryGroup3, T0.QryGroup4, T0.QryGroup5, T0.QryGroup6, 
                                                    T0.QryGroup7, T0.QryGroup8, T0.QryGroup9, T0.QryGroup10, T0.QryGroup11, T0.QryGroup12, T0.QryGroup13, T0.QryGroup14, T0.QryGroup15, T0.QryGroup16, T0.QryGroup17, T0.QryGroup18, 
                                                    T0.QryGroup19, T0.QryGroup20, T0.QryGroup21, T0.QryGroup22, T0.QryGroup23, T0.QryGroup24, T0.QryGroup25, T0.QryGroup26, T0.QryGroup27, T0.QryGroup28, T0.QryGroup29, T0.QryGroup30, 
                                                    T0.QryGroup31, T0.QryGroup32, T0.QryGroup33, T0.QryGroup34, T0.QryGroup35, T0.QryGroup36, T0.QryGroup37, T0.QryGroup38, T0.QryGroup39, T0.QryGroup40, T0.QryGroup41, T0.QryGroup42, 
                                                    T0.QryGroup43, T0.QryGroup44, T0.QryGroup45, T0.QryGroup46, T0.QryGroup47, T0.QryGroup48, T0.QryGroup49, T0.QryGroup50, T0.QryGroup51, T0.QryGroup52, T0.QryGroup53, T0.QryGroup54, 
                                                    T0.QryGroup55, T0.QryGroup56, T0.QryGroup57, T0.QryGroup58, T0.QryGroup59, T0.QryGroup60, T0.QryGroup61, T0.QryGroup62, T0.QryGroup63, T0.QryGroup64
                          FROM            dbo.posOITM AS T0 LEFT OUTER JOIN
                                                    dbo.posOSTC AS T2 ON T2.Code = T0.TaxCodeAR AND T2.ValidForAR = 'Y' AND T2.Lock = 'N' LEFT OUTER JOIN
                                                    dbo.posOITB AS T5 ON T5.ItmsGrpCod = T0.ItmsGrpCod INNER JOIN
                                                    dbo.posOITW AS T4 ON T0.ItemCode = T4.ItemCode AND T4.Locked = 'N' INNER JOIN
                                                    dbo.positm1 AS T1 ON T1.ItemCode = T0.ItemCode INNER JOIN
                                                    dbo.posOPLN AS T3 ON T3.ListNum = T1.PriceList AND T3.ValidFor = 'Y'
                          WHERE        (T0.SellItem = 'Y')) AS T0_1
GO


-- ==========================================================================
-- FUNCIONES (FUNCTIONS: ESCALARES Y DE TABLA)
-- ==========================================================================

-- Función (SQL_INLINE_TABLE_VALUED_FUNCTION): GetInvoiceDataForXML
CREATE FUNCTION [dbo].[GetInvoiceDataForXML](@InvoiceID INT, @eCFType NVARCHAR(20), @Resumen INT, @pSecurityCode NVARCHAR(50) = '', @pPrintPages NVARCHAR(1) = '')
RETURNS TABLE
AS
RETURN (  

        --PARA COMPROBANTES FISCALES ELECTRONICOS COMPLETOS @Resumen = 0
     SELECT ecf.*
       FROM ( 
  
             --Encabezado
             SELECT ROW_NUMBER() OVER (ORDER BY c.ColumnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'ECF' THEN 'ECF'
                        WHEN 'Version' THEN '1.0' 
                        WHEN 'TipoeCF' THEN i.ecf_type
                        WHEN 'eNCF' THEN i.ecf
                        WHEN 'FechaVencimientoSecuencia' THEN COALESCE(NULLIF(FORMAT(i.ecf_vence,'dd-MM-yyyy'),'01-01-1900'),'#e')
                        WHEN 'IndicadorNotaCredito' THEN '#e' --LOGICA PARA NOTA DE CREDITO
                        WHEN 'IndicadorMontoGravado' THEN CASE WHEN @eCFType IN (31,32,33,34,41,45) THEN '0' ELSE '#e' END -- (0) EL DEALLE DE LOS ITEM NO INCLUYE EL ITBIS
                        WHEN 'TipoIngresos' THEN CASE WHEN @eCFType IN (31,32,33,34,44,45,46) THEN '01' ELSE '#e' END -- (01) Ingresos por operaciones (No financieros).
                        WHEN 'TipoPago' THEN CAST(CASE WHEN F.ExtraDays <= 1 THEN 1 ELSE 2 END AS NVARCHAR(MAX)) -- 1:Contado | 2:Credio | 3:Gratuito
                        WHEN 'FechaLimitePago' THEN CASE WHEN F.ExtraDays > 1 THEN CASE WHEN @eCFType = 43 THEN '#e' ELSE FORMAT(f.DocDueDate,'dd-MM-yyyy') END ELSE '#e' END
                        WHEN 'TerminoPago' THEN CASE WHEN F.ExtraDays > 1 THEN CASE WHEN @eCFType IN ('31','32','33','41','44','45','46','47') THEN REPLACE(g.PymntGroup,'C - ','') ELSE '#e' END ELSE '#e' END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM cms.dbo.POSOINV f
         INNER JOIN cms.dbo.ecf_xml_invoice i ON (i.doc_id = f.C_DocEntry AND i.ecf_type IN (31,32,44,45)) 
          LEFT JOIN cms.dbo.POSOCTG g ON (g.ExtraDays = f.ExtraDays /*AND g.ListNum = 2*/)
        CROSS APPLY (VALUES (1,'ECF'), (2,'Version'), (3,'TipoeCF'),(4,'eNCF'), (5,'FechaVencimientoSecuencia'),(6,'IndicadorNotaCredito'),(7,'IndicadorMontoGravado'),(8,'TipoIngresos'),(9,'TipoPago'),(10,'FechaLimitePago'),(11,'TerminoPago')) c (columnOrder,ColumnName)
              WHERE f.C_DocEntry = @InvoiceID
UNION ALL     --FORMAS DE PAGO
             SELECT 20 + ROW_NUMBER() OVER (ORDER BY s.id_diario_caja_det, c.ColumnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                         WHEN 'FormaPago' THEN CAST(t.ecf_payform_id AS NVARCHAR(MAX))
                         WHEN 'MontoPago' THEN FORMAT(s.monto_caja,'0.00')
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM cms.dbo.POSOINV f
         INNER JOIN cms.dbo.diario_caja_det s ON (s.id_documento = f.C_DocEntry AND s.docType = 'P')
          LEFT JOIN cms.dbo.ecf_payment_forms t ON (t.ecf_payform_sap = s.id_tipo_movimiento)
        CROSS APPLY (VALUES (1,'FormaPago'), (2,'MontoPago')) c (columnOrder,ColumnName)
              WHERE f.C_DocEntry = @InvoiceID
                AND @eCFType IN ('31','32','33','41','44','45','46','47')
UNION ALL    --TOTAL DE PAGINAS
             SELECT 40 + ROW_NUMBER() OVER (ORDER BY p.pages) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    'TotalPaginas' AS xmlTag,
                    FORMAT(p.pages,'0') AS xml_value
               FROM (SELECT CEILING(COUNT(d.C_Id) / (SELECT CAST(config_value AS NUMERIC(5,1)) FROM cms.dbo.ecf_global_config WHERE config_name = 'ITEMSPERPAGE')) AS pages
                       FROM cms.dbo.POSINV1 d
                 INNER JOIN cms.dbo.POSOINV f ON (f.C_DocEntry = d.C_DocEntry)
                      WHERE d.C_DocEntry = @InvoiceID
                      GROUP BY f.C_DocEntry
                    ) p
              WHERE p.pages > 1
UNION ALL    --EMISOR & COMPRADOR
             SELECT 50 + ROW_NUMBER() OVER (ORDER BY c.ColumnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'RNCEmisor' THEN d.RNC --PARA QUE SIEMPRE ENVIE EL RNC DE AGROFEM
                        WHEN 'RazonSocialEmisor' THEN razon_social
                        WHEN 'NombreComercial' THEN UPPER(CAST(d.descripcion AS NVARCHAR(100)))
                        WHEN 'DireccionEmisor' THEN d.direccion
                        WHEN 'TelefonoEmisor' THEN d.telefono1
                        WHEN 'CorreoEmisor' THEN d.email
                        WHEN 'WebSite' THEN d.web
                        WHEN 'NumeroFacturaInterna' THEN f.NumAtCard
                        WHEN 'FechaEmision' THEN FORMAT(f.DocDate,'dd-MM-yyyy')
                        WHEN 'RNCComprador' THEN CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN CASE WHEN @eCFType IN ('31','32','33','34','41','44','45','46') THEN f.U_FacNit ELSE '#e' END ELSE CASE WHEN F.U_TNegocio = 3 THEN CASE WHEN @eCFType IN ('31','32','33','34','41','44','45','46') THEN '00000000000' ELSE '#e' END ELSE '#e' END END
                        WHEN 'RazonSocialComprador' THEN CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN CASE WHEN @eCFType IN ('31','32','33','34','41','44','45','46') THEN f.CardName ELSE '#e' END ELSE CASE WHEN F.U_TNegocio = 3 THEN CASE WHEN @eCFType IN ('31','32','33','34','41','44','45','46') THEN f.CardName ELSE '#e' END ELSE '#e' END END 
                        WHEN 'DireccionComprador' THEN CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN CASE WHEN @eCFType IN ('31','32','33','34','44','45','46') THEN SUBSTRING(COALESCE(NULLIF(f.Address2,''),'#e'),1,100) ELSE '#e' END ELSE '#e' END 
                        WHEN 'TelefonoAdicional' THEN '#e' --CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN CASE WHEN @eCFType IN ('31','32','33','34','44','45','46') THEN COALESCE(l.Cellular,l.phone1,l.phone2,'#e') ELSE '#e' END ELSE '#e' END 
                        WHEN 'CodigoInternoComprador' THEN CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN CASE WHEN @eCFType IN ('31','32','33','34','44','45','46') THEN f.CardCode ELSE '#e' END ELSE '#e' END 
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM cms.dbo.division d
	     INNER JOIN cms.dbo.serie s ON (s.id_division = d.id_division)
         INNER JOIN cms.dbo.POSOINV f ON (f.U_SCIASerie  = s.id_serie AND f.C_DocEntry = @InvoiceID)
          LEFT JOIN cms.dbo.POSOCRD l ON (l.CardCode = f.CardCode)
         INNER JOIN cms.dbo.ecf_xml_invoice i ON (i.doc_id = f.C_DocEntry AND i.ecf_type IN (31,32,44,45))
        CROSS APPLY (VALUES (1,'RNCEmisor'), (2,'RazonSocialEmisor'),(3,'NombreComercial'),(4,'DireccionEmisor'),(5,'TelefonoEmisor'),(6,'CorreoEmisor'),(7,'WebSite'),(8,'NumeroFacturaInterna'),(9,'FechaEmision'),(10,'RNCComprador'),(11,'RazonSocialComprador'),(12,'DireccionComprador'),(13,'TelefonoAdicional'),(14,'CodigoInternoComprador')) c (columnOrder,ColumnName)
UNION ALL    --TOTALES
             SELECT 100 + ROW_NUMBER() OVER (ORDER BY c.columnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'MontoGravadoTotal' THEN CASE WHEN a.monto_grabado > 0 THEN FORMAT(a.monto_grabado,'0.00') ELSE CASE WHEN @eCFType IN ('46') THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END END
                        WHEN 'MontoGravadoI1' THEN CASE WHEN a.monto_grabadoI1 > 0 THEN FORMAT(a.monto_grabadoI1,'0.00') ELSE '#e' END
                        WHEN 'MontoGravadoI2' THEN CASE WHEN a.monto_grabadoI2 > 0 THEN FORMAT(a.monto_grabadoI2,'0.00') ELSE '#e' END
                        WHEN 'MontoGravadoI3' THEN CASE WHEN a.monto_grabadoI3 > 0 THEN FORMAT(a.monto_grabadoI3,'0.00') ELSE CASE WHEN @eCFType IN ('46') THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END END
                        WHEN 'MontoExento' THEN CASE WHEN (a.monto_exento > 0 AND @eCFType IN ('31','32','33','34','41','43','44','45','47')) THEN FORMAT(a.monto_exento,'0.00') ELSE '#e' END
                        WHEN 'ITBIS1' THEN CASE WHEN a.itbis1 > 0 THEN FORMAT(a.itbis1,'00') ELSE '#e' END
                        WHEN 'ITBIS2' THEN CASE WHEN a.itbis2 > 0 THEN FORMAT(a.itbis2,'00') ELSE '#e' END
                        WHEN 'ITBIS3' THEN CASE WHEN a.itbis3 > 0 THEN FORMAT(a.itbis3,'0') ELSE CASE WHEN @eCFType IN ('46') THEN '0' ELSE '#e' END END
                        WHEN 'TotalITBIS' THEN CASE WHEN a.total_itbis > 0 THEN FORMAT(a.total_itbis,'0.00') ELSE CASE WHEN @eCFType IN ('46') THEN '0.00' ELSE '#e' END END
                        WHEN 'TotalITBIS1' THEN CASE WHEN a.total_itbis1 > 0 THEN FORMAT(a.total_itbis1,'0.00') ELSE '#e' END
                        WHEN 'TotalITBIS2' THEN CASE WHEN a.total_itbis2 > 0 THEN FORMAT(a.total_itbis2,'0.00') ELSE '#e' END
                        WHEN 'TotalITBIS3' THEN CASE WHEN a.total_itbis3 > 0 THEN FORMAT(a.total_itbis3,'0.00') ELSE CASE WHEN @eCFType IN ('46') THEN '0.00' ELSE '#e' END END
                        WHEN 'MontoTotal' THEN CASE WHEN a.monto_total > 0 THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END
                        WHEN 'TotalITBISRetenido' THEN CASE WHEN @eCFType IN ('41') THEN FORMAT(0,'0.00') ELSE '#e' END
                        WHEN 'TotalISRRetencion' THEN CASE WHEN @eCFType IN ('41','47') THEN FORMAT(0,'0.00') ELSE '#e' END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM (SELECT CAST(SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabado,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (1) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabadoI1,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (2) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabadoI2,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (3) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabadoI3,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (4) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_exento,
                            CAST(MAX(CASE WHEN t.taxecf_id IN (1) THEN d.VatPrcnt ELSE 0 END) AS NUMERIC(12,2)) AS itbis1,
                            CAST(MAX(CASE WHEN t.taxecf_id IN (2) THEN d.VatPrcnt ELSE 0 END) AS NUMERIC(12,2)) AS itbis2,
                            CAST(MAX(CASE WHEN t.taxecf_id IN (3) THEN d.VatPrcnt ELSE 0 END) AS NUMERIC(12,2)) AS itbis3,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (1) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis1,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (2) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis2,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (3) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis3,
                            CAST(SUM(d.LineTotal+d.VatSum) AS NUMERIC(12,2)) AS monto_total
                       FROM cms.dbo.POSOINV f
                 INNER JOIN cms.dbo.POSINV1 d ON (d.C_DocEntry = f.C_DocEntry)
                 INNER JOIN cms.dbo.ecf_tax_config t ON (t.taxsap_name = d.TaxCode)
                      WHERE f.C_DocEntry = @InvoiceID
                    ) a
        CROSS APPLY (VALUES (1,'MontoGravadoTotal'), (2,'MontoGravadoI1'), (3,'MontoGravadoI2'),(4,'MontoGravadoI3'), (5,'MontoExento'),(6,'ITBIS1'),(7,'ITBIS2'),(8,'ITBIS3'),(9,'TotalITBIS'),(10,'TotalITBIS1'),(11,'TotalITBIS2'),(12,'TotalITBIS3'),(13,'MontoTotal'),(14,'TotalITBISRetenido'),(15,'TotalISRRetencion')) c (columnOrder,ColumnName)
UNION ALL
         --2 - DetallesItems
         SELECT 1000 + ROW_NUMBER() OVER (ORDER BY d.LineNum, c.ColumnOrder) AS id_record,
                2 AS group_id,
                'DetallesItem' AS group_name,
                c.ColumnName AS xmlTag,
                CAST(CASE c.ColumnName
                    WHEN 'NumeroLinea' THEN CAST(d.LineNum+1 AS NVARCHAR(MAX))
                    WHEN 'IndicadorFacturacion' THEN CAST( CASE WHEN @eCFType IN ('46') THEN 3 ELSE t.taxecf_id END AS NVARCHAR(MAX))
                    WHEN 'IndicadorAgenteRetencionoPercepcion' THEN CAST(CASE WHEN @eCFType IN ('41','47') THEN '1' ELSE '#e' END AS NVARCHAR(MAX))
                    WHEN 'MontoITBISRetenido' THEN CASE WHEN @eCFType IN ('41') THEN '0.00' ELSE '#e' END
                    WHEN 'MontoISRRetenido' THEN CASE WHEN @eCFType IN ('41','47') THEN '0.00' ELSE '#e' END
                    WHEN 'NombreItem' THEN CAST(d.Dscription AS NVARCHAR(MAX))
                    WHEN 'IndicadorBienoServicio' THEN CAST(CASE WHEN @eCFType IN ('41','47','43') THEN 2 ELSE CASE WHEN f.DocType = 'S' THEN 2 ELSE 1 END END AS NVARCHAR(MAX))
                    WHEN 'CantidadItem' THEN FORMAT(d.Quantity,'0.00')
                    WHEN 'UnidadMedida' THEN CAST(u.ecfunit_id AS NVARCHAR(MAX))
                    WHEN 'PrecioUnitarioItem' THEN FORMAT(d.PriceBefDi,'0.00')
                    WHEN 'DescuentoMonto' THEN CASE WHEN d.DiscPrcnt > 0 THEN FORMAT((d.PriceBefDi*(d.DiscPrcnt/100))*d.Quantity,'0.00') ELSE '#e' END
                    WHEN 'TipoSubDescuento' THEN CASE WHEN d.DiscPrcnt > 0 THEN '%' ELSE '#e' END
                    WHEN 'SubDescuentoPorcentaje' THEN CASE WHEN d.DiscPrcnt > 0 THEN FORMAT(d.DiscPrcnt,'0.00') ELSE '#e' END
                    WHEN 'MontoSubDescuento' THEN CASE WHEN d.DiscPrcnt > 0 THEN FORMAT((d.PriceBefDi*(d.DiscPrcnt/100))*d.Quantity,'0.00') ELSE '#e' END
                    WHEN 'MontoItem' THEN FORMAT(d.LineTotal,'0.00')
                END AS NVARCHAR(MAX)) AS xml_value
           FROM cms.dbo.POSOINV f 
     INNER JOIN cms.dbo.POSINV1 d ON (d.C_DocEntry = f.C_DocEntry)
     INNER JOIN cms.dbo.ecf_tax_config t ON (t.taxsap_name = d.TaxCode)
     INNER JOIN cms.dbo.ecf_units u ON (u.ecfsapunit_name = d.unitMsr)
    CROSS APPLY (VALUES (1,'NumeroLinea'), (2,'IndicadorFacturacion'), (3,'IndicadorAgenteRetencionoPercepcion'), (4,'MontoITBISRetenido'), (4,'MontoISRRetenido'), (5,'NombreItem'),(6,'IndicadorBienoServicio'), (7,'CantidadItem'),(8,'UnidadMedida'),(9,'PrecioUnitarioItem'),(10,'DescuentoMonto'),(11,'TipoSubDescuento'),(12,'SubDescuentoPorcentaje'),(13,'MontoSubDescuento'),(14,'MontoItem')) c (columnOrder,ColumnName)
          WHERE f.C_DocEntry = @InvoiceID
UNION ALL
             --PAGINACION
             SELECT 100000+ROW_NUMBER() OVER (ORDER BY a.pagina_numero, c.columnOrder) AS id_record,
                    5 AS group_id,
                    'Paginacion' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'PaginaNo' THEN CAST(pagina_numero AS NVARCHAR(MAX))
                        WHEN 'NoLineaDesde' THEN CAST(linea_desde AS NVARCHAR(MAX))
                        WHEN 'NoLineaHasta' THEN CAST(linea_hasa AS NVARCHAR(MAX))
                        WHEN 'SubtotalMontoGravadoPagina' THEN CASE WHEN a.monto_grabado > 0 THEN FORMAT(a.monto_grabado,'0.00') ELSE '#e' END
                        WHEN 'SubtotalMontoGravado1Pagina' THEN CASE WHEN a.monto_grabadoI1 > 0 THEN FORMAT(a.monto_grabadoI1,'0.00') ELSE '#e' END
                        WHEN 'SubtotalMontoGravado2Pagina' THEN CASE WHEN a.monto_grabadoI2 > 0 THEN FORMAT(a.monto_grabadoI2,'0.00') ELSE '#e' END
                        WHEN 'SubtotalMontoGravado3Pagina' THEN CASE WHEN a.monto_grabadoI3 > 0 THEN FORMAT(a.monto_grabadoI3,'0.00') ELSE '#e' END
                        WHEN 'SubtotalExentoPagina' THEN CASE WHEN a.monto_exento > 0 THEN FORMAT(a.monto_exento,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbisPagina' THEN CASE WHEN a.total_itbis > 0 THEN FORMAT(a.total_itbis,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbis1Pagina' THEN CASE WHEN a.total_itbis1 > 0 THEN FORMAT(a.total_itbis1,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbis2Pagina' THEN CASE WHEN a.total_itbis2 > 0 THEN FORMAT(a.total_itbis2,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbis3Pagina' THEN CASE WHEN a.total_itbis3 > 0 THEN FORMAT(a.total_itbis3,'0.00') ELSE '#e' END
                        WHEN 'MontoSubtotalPagina' THEN CASE WHEN a.monto_total > 0 THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM (SELECT group_id AS pagina_numero,
                            MIN(w.numero_linea) AS linea_desde,
                            MAX(w.numero_linea) AS linea_hasa,
                            CAST(SUM(monto_grabado) AS NUMERIC(12,2)) AS monto_grabado,
                            CAST(SUM(monto_grabadoI1) AS NUMERIC(12,2)) AS monto_grabadoI1,
                            CAST(SUM(monto_grabadoI2) AS NUMERIC(12,2)) AS monto_grabadoI2,
                            CAST(SUM(monto_grabadoI3) AS NUMERIC(12,2)) AS monto_grabadoI3,
                            CAST(SUM(monto_exento) AS NUMERIC(12,2)) AS monto_exento,
                            CAST(SUM(total_itbis) AS NUMERIC(12,2)) AS total_itbis,
                            CAST(SUM(total_itbis1) AS NUMERIC(12,2)) AS total_itbis1,
                            CAST(SUM(total_itbis2) AS NUMERIC(12,2)) AS total_itbis2,
                            CAST(SUM(total_itbis3) AS NUMERIC(12,2)) AS total_itbis3,
                            CAST(SUM(monto_total) AS NUMERIC(12,2)) AS monto_total
                       FROM (SELECT (d.linenum+1) AS numero_linea,
                                    CEILING(ROW_NUMBER() OVER(ORDER BY d.linenum) / (SELECT CAST(config_value AS NUMERIC(5,1)) FROM cms.dbo.ecf_global_config WHERE config_name = 'ITEMSPERPAGE')) AS group_id,
                                    SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.LineTotal ELSE 0 END) AS monto_grabado,
                                    SUM(CASE WHEN t.taxecf_id IN (1) THEN d.LineTotal ELSE 0 END) AS monto_grabadoI1,
                                    SUM(CASE WHEN t.taxecf_id IN (2) THEN d.LineTotal ELSE 0 END) AS monto_grabadoI2,
                                    SUM(CASE WHEN t.taxecf_id IN (3) THEN d.LineTotal ELSE 0 END) AS monto_grabadoI3,
                                    SUM(CASE WHEN t.taxecf_id IN (4) THEN d.LineTotal ELSE 0 END) AS monto_exento,
                                    MAX(CASE WHEN t.taxecf_id IN (1) THEN d.VatPrcnt ELSE 0 END) AS itbis1,
                                    MAX(CASE WHEN t.taxecf_id IN (2) THEN d.VatPrcnt ELSE 0 END) AS itbis2,
                                    MAX(CASE WHEN t.taxecf_id IN (3) THEN d.VatPrcnt ELSE 0 END) AS itbis3,
                                    SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.VatSum ELSE 0 END) AS total_itbis,
                                    SUM(CASE WHEN t.taxecf_id IN (1) THEN d.VatSum ELSE 0 END) AS total_itbis1,
                                    SUM(CASE WHEN t.taxecf_id IN (2) THEN d.VatSum ELSE 0 END) AS total_itbis2,
                                    SUM(CASE WHEN t.taxecf_id IN (3) THEN d.VatSum ELSE 0 END) AS total_itbis3,
                                    SUM(d.LineTotal+d.VatSum) AS monto_total
                               FROM cms.dbo.POSOINV f
                         INNER JOIN cms.dbo.POSINV1 d ON (d.C_DocEntry = f.C_DocEntry)
                         INNER JOIN cms.dbo.ecf_tax_config t ON (t.taxsap_name = d.TaxCode)
                              WHERE f.C_DocEntry = @InvoiceID
                              GROUP BY d.linenum
                            ) w
                      GROUP BY w.group_id
                    ) a
        CROSS APPLY (VALUES (1,'PaginaNo'), (2,'NoLineaDesde'), (3,'NoLineaHasta'),(4,'SubtotalMontoGravadoPagina'), (5,'SubtotalMontoGravado1Pagina'),(6,'SubtotalMontoGravado2Pagina'),(7,'SubtotalMontoGravado3Pagina'),(8,'SubtotalExentoPagina'),(9,'SubtotalItbisPagina'),(10,'SubtotalItbis1Pagina'),(11,'SubtotalItbis2Pagina'),(12,'SubtotalItbis3Pagina'),(13,'MontoSubtotalPagina')) c (columnOrder,ColumnName)
              WHERE (SELECT CEILING(COUNT(d.C_Id) / (SELECT CAST(config_value AS NUMERIC(5,1)) FROM cms.dbo.ecf_global_config WHERE config_name = 'ITEMSPERPAGE')) AS pages FROM cms.dbo.POSINV1 d WHERE d.C_DocEntry = @InvoiceID) > 1
                AND @pPrintPages = 'Y'
            ) ecf 
        WHERE @Resumen = 0 AND @eCFType IN ('31','32','44','45')
          

UNION ALL 

        --PARA COMPROBANTES FISCALES ELECTRONICOS DE CONSUMO (FORMATO COMPACTO) @Resumen = 1
     SELECT ecf.*
       FROM ( 
  
             --Encabezado
             SELECT ROW_NUMBER() OVER (ORDER BY c.ColumnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'RFCE' THEN 'RFCE'
                        WHEN 'Version' THEN '1.0' 
                        WHEN 'TipoeCF' THEN i.ecf_type
                        WHEN 'eNCF' THEN i.ecf
                        WHEN 'TipoIngresos' THEN '01' -- (01) Ingresos por operaciones (No financieros).
                        WHEN 'TipoPago' THEN CAST(CASE WHEN F.ExtraDays <= 1 THEN 1 ELSE 2 END AS NVARCHAR(MAX)) -- 1:Contado | 2:Credio | 3:Gratuito
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM cms.dbo.POSOINV f
         INNER JOIN cms.dbo.ecf_xml_invoice i ON (i.doc_id = f.C_DocEntry AND i.ecf_type IN (31,32,44,45)) 
          LEFT JOIN cms.dbo.POSOCTG g ON (g.ExtraDays = f.ExtraDays /*AND g.ListNum = 2*/)
        CROSS APPLY (VALUES (1,'RFCE'), (2,'Version'), (3,'TipoeCF'),(4,'eNCF'),(5,'TipoIngresos'),(6,'TipoPago')) c (columnOrder,ColumnName)
              WHERE f.C_DocEntry = @InvoiceID
UNION ALL     --FORMAS DE PAGO
             SELECT 20 + ROW_NUMBER() OVER (ORDER BY s.id_diario_caja_det, c.ColumnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                         WHEN 'FormaPago' THEN CAST(t.ecf_payform_id AS NVARCHAR(MAX))
                         WHEN 'MontoPago' THEN FORMAT(s.monto_caja,'0.00')
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM cms.dbo.POSOINV f
         INNER JOIN cms.dbo.diario_caja_det s ON (s.id_documento = f.C_DocEntry AND s.docType = 'P')
          LEFT JOIN cms.dbo.ecf_payment_forms t ON (t.ecf_payform_sap = s.id_tipo_movimiento)
        CROSS APPLY (VALUES (1,'FormaPago'), (2,'MontoPago')) c (columnOrder,ColumnName)
              WHERE f.C_DocEntry = @InvoiceID
UNION ALL    --EMISOR & COMPRADOR
             SELECT 50 + ROW_NUMBER() OVER (ORDER BY c.ColumnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'RNCEmisor' THEN d.RNC --PARA QUE SIEMPRE ENVIE EL RNC DE AGROFEM
                        WHEN 'RazonSocialEmisor' THEN razon_social
                        WHEN 'FechaEmision' THEN FORMAT(f.DocDate,'dd-MM-yyyy')
                        WHEN 'RNCComprador' THEN CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN f.U_FacNit ELSE CASE WHEN F.U_TNegocio = 3 THEN '00000000000' ELSE '#e' END END
                        WHEN 'RazonSocialComprador' THEN CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN f.CardName ELSE CASE WHEN F.U_TNegocio = 3 THEN f.CardName ELSE '#e' END END 
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM cms.dbo.division d
	     INNER JOIN cms.dbo.serie s ON (s.id_division = d.id_division)
         INNER JOIN cms.dbo.POSOINV f ON (f.U_SCIASerie = s.id_serie AND f.C_DocEntry = @InvoiceID)
          LEFT JOIN cms.dbo.POSOCRD l ON (l.CardCode = f.CardCode)
         INNER JOIN cms.dbo.ecf_xml_invoice i ON (i.doc_id = f.C_DocEntry AND i.ecf_type IN (31,32,44,45))
        CROSS APPLY (VALUES (1,'RNCEmisor'), (2,'RazonSocialEmisor'),(3,'FechaEmision'),(4,'RNCComprador'),(5,'RazonSocialComprador')) c (columnOrder,ColumnName)
UNION ALL    --TOTALES
             SELECT 100 + ROW_NUMBER() OVER (ORDER BY c.columnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'MontoGravadoTotal' THEN CASE WHEN a.monto_grabado > 0 THEN FORMAT(a.monto_grabado,'0.00') ELSE '#e' END
                        WHEN 'MontoGravadoI1' THEN CASE WHEN a.monto_grabadoI1 > 0 THEN FORMAT(a.monto_grabadoI1,'0.00') ELSE '#e' END
                        WHEN 'MontoGravadoI2' THEN CASE WHEN a.monto_grabadoI2 > 0 THEN FORMAT(a.monto_grabadoI2,'0.00') ELSE '#e' END
                        WHEN 'MontoGravadoI3' THEN CASE WHEN a.monto_grabadoI3 > 0 THEN FORMAT(a.monto_grabadoI3,'0.00') ELSE '#e' END
                        WHEN 'MontoExento' THEN CASE WHEN a.monto_exento > 0 THEN FORMAT(a.monto_exento,'0.00') ELSE '#e' END
                        WHEN 'TotalITBIS' THEN CASE WHEN a.total_itbis > 0 THEN FORMAT(a.total_itbis,'0.00') ELSE '#e' END
                        WHEN 'TotalITBIS1' THEN CASE WHEN a.total_itbis1 > 0 THEN FORMAT(a.total_itbis1,'0.00') ELSE '#e' END
                        WHEN 'TotalITBIS2' THEN CASE WHEN a.total_itbis2 > 0 THEN FORMAT(a.total_itbis2,'0.00') ELSE '#e' END
                        WHEN 'TotalITBIS3' THEN CASE WHEN a.total_itbis3 > 0 THEN FORMAT(a.total_itbis3,'0.00') ELSE '#e' END
                        WHEN 'MontoTotal' THEN CASE WHEN a.monto_total > 0 THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM (SELECT CAST(SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabado,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (1) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabadoI1,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (2) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabadoI2,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (3) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabadoI3,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (4) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_exento,
                            CAST(MAX(CASE WHEN t.taxecf_id IN (1) THEN d.VatPrcnt ELSE 0 END) AS NUMERIC(12,2)) AS itbis1,
                            CAST(MAX(CASE WHEN t.taxecf_id IN (2) THEN d.VatPrcnt ELSE 0 END) AS NUMERIC(12,2)) AS itbis2,
                            CAST(MAX(CASE WHEN t.taxecf_id IN (3) THEN d.VatPrcnt ELSE 0 END) AS NUMERIC(12,2)) AS itbis3,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (1) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis1,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (2) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis2,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (3) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis3,
                            CAST(SUM(d.LineTotal+d.VatSum) AS NUMERIC(12,2)) AS monto_total
                       FROM cms.dbo.POSOINV f
                 INNER JOIN cms.dbo.POSINV1 d ON (d.C_DocEntry = f.C_DocEntry)
                 INNER JOIN cms.dbo.ecf_tax_config t ON (t.taxsap_name = d.TaxCode)
                      WHERE f.C_DocEntry = @InvoiceID
                    ) a
        CROSS APPLY (VALUES (1,'MontoGravadoTotal'), (2,'MontoGravadoI1'), (3,'MontoGravadoI2'),(4,'MontoGravadoI3'), (5,'MontoExento'),(6,'TotalITBIS'),(7,'TotalITBIS1'),(8,'TotalITBIS2'),(9,'TotalITBIS3'),(10,'MontoTotal')) c (columnOrder,ColumnName)
UNION ALL
         --1 - CodigoSeguridadeCF
             SELECT 200 AS id_record, --199 + ROW_NUMBER() OVER (ORDER BY i.id_record) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    'CodigoSeguridadeCF' AS xmlTag,
                    COALESCE(NULLIF(@pSecurityCode,''),'#e') AS xml_value
--               FROM cms.dbo.ecf_xml_invoice i
--              WHERE i.doc_id = @InvoiceID
            ) ecf 
        WHERE @Resumen = 1 AND @eCFType IN ('32')


UNION ALL

--NOTA DE CREDITO AGROFEM DESDE VENTAS
     SELECT ecf.*
       FROM ( 
  
             --Encabezado
             SELECT ROW_NUMBER() OVER (ORDER BY c.ColumnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'ECF' THEN 'ECF'
                        WHEN 'Version' THEN '1.0' 
                        WHEN 'TipoeCF' THEN i.ecf_type
                        WHEN 'eNCF' THEN UPPER(i.ecf)
                        WHEN 'FechaVencimientoSecuencia' THEN COALESCE(NULLIF(FORMAT(i.ecf_vence,'dd-MM-yyyy'),'01-01-1900'),'#e')
                        WHEN 'IndicadorNotaCredito' THEN CASE WHEN DATEDIFF(DAY,t.U_FacFecha,f.DocDate) <= 30 THEN '0' ELSE '1' END --'#e' --LOGICA PARA NOTA DE CREDITO
                        WHEN 'IndicadorMontoGravado' THEN CASE WHEN @eCFType IN ('31','32','33','34','41','45') THEN '0' ELSE '#e' END -- (0) EL DEALLE DE LOS ITEM NO INCLUYE EL ITBIS
                        WHEN 'TipoIngresos' THEN CASE WHEN @eCFType IN ('31','32','33','34','44','45','46') THEN '01' ELSE '#e' END -- (01) Ingresos por operaciones (No financieros).
                        WHEN 'TipoPago' THEN CAST(CASE WHEN F.ExtraDays <= 1 THEN 1 ELSE 2 END AS NVARCHAR(MAX)) -- 1:Contado | 2:Credio | 3:Gratuito
                        WHEN 'FechaLimitePago' THEN CASE WHEN F.ExtraDays > 1 THEN CASE WHEN @eCFType IN ('43') THEN '#e' ELSE FORMAT(f.DocDueDate,'dd-MM-yyyy') END ELSE '#e' END
                        WHEN 'TerminoPago' THEN CASE WHEN F.ExtraDays > 1 THEN CASE WHEN @eCFType IN ('31','32','33','41','44','45','46','47') THEN REPLACE(g.PymntGroup,'C - ','') ELSE '#e' END ELSE '#e' END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM cms.dbo.POSORIN f
         INNER JOIN cms.dbo.POSOINV t ON (t.U_FACNUM = f.U_NCO AND t.CANCELED = 'N')
         INNER JOIN cms.dbo.ecf_xml_invoice i ON (i.doc_id = f.C_DocEntry AND i.ecf_type IN (34)) 
          LEFT JOIN CMS.dbo.POSOCTG g ON (g.ExtraDays = f.ExtraDays /*AND g.ListNum = 2*/)
        CROSS APPLY (VALUES (1,'ECF'), (2,'Version'), (3,'TipoeCF'),(4,'eNCF'), (5,'FechaVencimientoSecuencia'),(6,'IndicadorNotaCredito'),(7,'IndicadorMontoGravado'),(8,'TipoIngresos'),(9,'TipoPago'),(10,'FechaLimitePago'),(11,'TerminoPago')) c (columnOrder,ColumnName)
              WHERE f.C_DocEntry = @InvoiceID 
UNION ALL     --FORMAS DE PAGO
             SELECT 20 + ROW_NUMBER() OVER (ORDER BY s.id_diario_caja_det, c.ColumnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                         WHEN 'FormaPago' THEN CAST(t.ecf_payform_id AS NVARCHAR(MAX))
                         WHEN 'MontoPago' THEN FORMAT(s.monto_caja,'0.00')
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM cms.dbo.POSOINV f
         INNER JOIN cms.dbo.diario_caja_det s ON (s.id_documento = f.C_DocEntry AND s.docType = 'P')
          LEFT JOIN cms.dbo.ecf_payment_forms t ON (t.ecf_payform_sap = s.id_tipo_movimiento)
        CROSS APPLY (VALUES (1,'FormaPago'), (2,'MontoPago')) c (columnOrder,ColumnName)
              WHERE f.C_DocEntry = @InvoiceID
                AND @eCFType IN ('31','32','33','41','44','45','46','47')
                AND f.C_Posted IN ('Y','N')
UNION ALL    --TOTAL DE PAGINAS
             SELECT 40 + ROW_NUMBER() OVER (ORDER BY p.pages) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    'TotalPaginas' AS xmlTag,
                    FORMAT(p.pages,'0') AS xml_value
               FROM (SELECT CEILING(COUNT(d.LineNum) / (SELECT CAST(config_value AS NUMERIC(5,1)) FROM cms.dbo.ecf_global_config WHERE config_name = 'ITEMSPERPAGE')) AS pages
                       FROM cms.dbo.POSRIN1 d
                 INNER JOIN cms.dbo.POSORIN f ON (f.C_DocEntry = d.C_DocEntry)
                      WHERE d.C_DocEntry = @InvoiceID
                      GROUP BY f.DocEntry 
                    ) p
              WHERE p.pages > 1
UNION ALL    --EMISOR & COMPRADOR
             SELECT 50 + ROW_NUMBER() OVER (ORDER BY c.ColumnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'RNCEmisor' THEN d.RNC --PARA QUE SIEMPRE ENVIE EL RNC DE AGROFEM
                        WHEN 'RazonSocialEmisor' THEN razon_social
                        WHEN 'NombreComercial' THEN UPPER(CAST(d.descripcion AS NVARCHAR(100)))
                        WHEN 'DireccionEmisor' THEN d.direccion
                        WHEN 'TelefonoEmisor' THEN d.telefono1
                        WHEN 'CorreoEmisor' THEN d.email
                        WHEN 'WebSite' THEN d.web
                        WHEN 'NumeroFacturaInterna' THEN COALESCE(o.NumAtCard,'#e')
                        WHEN 'FechaEmision' THEN FORMAT(o.DocDate,'dd-MM-yyyy')
                        --WHEN 'RNCComprador' THEN CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN CASE WHEN @eCFType IN ('34') OR (@eCFType IN ('34') AND f.DocTotal >= 250000) THEN f.U_FacNit ELSE '#e' END ELSE CASE WHEN F.U_TNegocio = 3 THEN CASE WHEN @eCFType IN ('34') OR (@eCFType IN ('34') AND f.DocTotal >= 250000) THEN '00000000000' ELSE '#e' END ELSE '#e' END END
                        WHEN 'RNCComprador' THEN CASE WHEN LEN(f.U_FacNit) IN (9,11) AND PATINDEX('%[^0-9]%', f.U_FacNit) = 0 THEN o.U_FacNit ELSE CASE WHEN f.U_TNegocio = 3 THEN '00000000000' ELSE '#e' END END
                        --WHEN 'RazonSocialComprador' THEN CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN CASE WHEN @eCFType IN ('34') OR (@eCFType IN ('34') AND f.DocTotal >= 250000) THEN f.CardName ELSE '#e' END ELSE CASE WHEN F.U_TNegocio = 3 THEN CASE WHEN @eCFType IN ('34') OR (@eCFType IN ('34') AND f.DocTotal >= 250000) THEN f.CardName ELSE '#e' END ELSE '#e' END END 
                        WHEN 'RazonSocialComprador' THEN CASE WHEN LEN(f.U_FacNit) IN (9,11) AND PATINDEX('%[^0-9]%', f.U_FacNit) = 0 THEN o.CardName ELSE CASE WHEN f.U_TNegocio = 3 THEN o.CardName ELSE '#e' END END
                        WHEN 'DireccionComprador' THEN '#e' 
                        WHEN 'TelefonoAdicional' THEN '#e' 
                        WHEN 'CodigoInternoComprador' THEN CASE WHEN LEN(f.U_FacNit) IN (9,11) AND PATINDEX('%[^0-9]%', f.U_FacNit) = 0 THEN o.CardCode ELSE CASE WHEN f.U_TNegocio = 3 THEN o.CardCode ELSE '#e' END END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM cms.dbo.POSORIN o
         INNER JOIN cms.dbo.POSOINV f ON (f.U_FACNUM = o.U_NCO AND f.CANCELED = 'N')
	     INNER JOIN cms.dbo.serie s ON (s.id_serie = f.U_SCIASerie)
         INNER JOIN cms.dbo.division d ON (d.id_division = s.id_division) 
          LEFT JOIN cms.dbo.POSOCRD l ON (l.CardCode = f.CardCode)
         INNER JOIN cms.dbo.ecf_xml_invoice i ON (i.doc_id = o.C_DocEntry AND i.ecf_type IN (34))
        CROSS APPLY (VALUES (1,'RNCEmisor'), (2,'RazonSocialEmisor'),(3,'NombreComercial'),(4,'DireccionEmisor'),(5,'TelefonoEmisor'),(6,'CorreoEmisor'),(7,'WebSite'),(8,'NumeroFacturaInterna'),(9,'FechaEmision'),(10,'RNCComprador'),(11,'RazonSocialComprador'),(12,'DireccionComprador'),(13,'TelefonoAdicional'),(14,'CodigoInternoComprador')) c (columnOrder,ColumnName)
              WHERE o.C_DocEntry = @InvoiceID
UNION ALL    --TOTALES
             SELECT 100 + ROW_NUMBER() OVER (ORDER BY c.columnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'MontoGravadoTotal' THEN CASE WHEN a.monto_grabado > 0 THEN FORMAT(a.monto_grabado,'0.00') ELSE CASE WHEN @eCFType IN ('46') THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END END
                        WHEN 'MontoGravadoI1' THEN CASE WHEN a.monto_grabadoI1 > 0 THEN FORMAT(a.monto_grabadoI1,'0.00') ELSE '#e' END
                        WHEN 'MontoGravadoI2' THEN CASE WHEN a.monto_grabadoI2 > 0 THEN FORMAT(a.monto_grabadoI2,'0.00') ELSE '#e' END
                        WHEN 'MontoGravadoI3' THEN CASE WHEN a.monto_grabadoI3 > 0 THEN FORMAT(a.monto_grabadoI3,'0.00') ELSE CASE WHEN @eCFType IN ('46') THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END END
                        WHEN 'MontoExento' THEN CASE WHEN (a.monto_exento > 0 AND @eCFType IN ('31','32','33','34','41','43','44','45','47')) THEN FORMAT(a.monto_exento,'0.00') ELSE '#e' END
                        WHEN 'ITBIS1' THEN CASE WHEN a.itbis1 > 0 THEN FORMAT(a.itbis1,'00') ELSE '#e' END
                        WHEN 'ITBIS2' THEN CASE WHEN a.itbis2 > 0 THEN FORMAT(a.itbis2,'00') ELSE '#e' END
                        WHEN 'ITBIS3' THEN CASE WHEN a.itbis3 > 0 THEN FORMAT(a.itbis3,'0') ELSE CASE WHEN @eCFType IN ('46') THEN '0' ELSE '#e' END END
                        WHEN 'TotalITBIS' THEN CASE WHEN a.total_itbis > 0 THEN FORMAT(a.total_itbis,'0.00') ELSE CASE WHEN @eCFType IN ('46') THEN '0.00' ELSE '#e' END END
                        WHEN 'TotalITBIS1' THEN CASE WHEN a.total_itbis1 > 0 THEN FORMAT(a.total_itbis1,'0.00') ELSE '#e' END
                        WHEN 'TotalITBIS2' THEN CASE WHEN a.total_itbis2 > 0 THEN FORMAT(a.total_itbis2,'0.00') ELSE '#e' END
                        WHEN 'TotalITBIS3' THEN CASE WHEN a.total_itbis3 > 0 THEN FORMAT(a.total_itbis3,'0.00') ELSE CASE WHEN @eCFType IN ('46') THEN '0.00' ELSE '#e' END END
                        WHEN 'MontoTotal' THEN CASE WHEN a.monto_total > 0 THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END
                        WHEN 'TotalITBISRetenido' THEN CASE WHEN @eCFType IN ('41') THEN FORMAT(0,'0.00') ELSE '#e' END
                        WHEN 'TotalISRRetencion' THEN CASE WHEN @eCFType IN ('41','47') THEN FORMAT(0,'0.00') ELSE '#e' END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM (SELECT CAST(SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabado,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (1) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabadoI1,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (2) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabadoI2,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (3) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabadoI3,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (4) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_exento,
                            CAST(MAX(CASE WHEN t.taxecf_id IN (1) THEN d.VatPrcnt ELSE 0 END) AS NUMERIC(12,2)) AS itbis1,
                            CAST(MAX(CASE WHEN t.taxecf_id IN (2) THEN d.VatPrcnt ELSE 0 END) AS NUMERIC(12,2)) AS itbis2,
                            CAST(MAX(CASE WHEN t.taxecf_id IN (3) THEN d.VatPrcnt ELSE 0 END) AS NUMERIC(12,2)) AS itbis3,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (1) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis1,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (2) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis2,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (3) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis3,
                            CAST(SUM(d.LineTotal+d.VatSum) AS NUMERIC(12,2)) AS monto_total
                       FROM cms.dbo.POSORIN f
                 INNER JOIN cms.dbo.POSRIN1 d ON (d.C_DocEntry = f.C_DocEntry)
                 INNER JOIN cms.dbo.POSOINV o ON (o.U_FACNUM = f.U_NCO AND o.CANCELED = 'N')
                 INNER JOIN cms.dbo.ecf_tax_config t ON (t.taxsap_name = d.TaxCode)
                      WHERE f.C_DocEntry = @InvoiceID
                        --AND DATEDIFF(DAY, o.U_FacFecha,f.DocDate) <= 30
                    ) a
        CROSS APPLY (VALUES (1,'MontoGravadoTotal'), (2,'MontoGravadoI1'), (3,'MontoGravadoI2'),(4,'MontoGravadoI3'), (5,'MontoExento'),(6,'ITBIS1'),(7,'ITBIS2'),(8,'ITBIS3'),(9,'TotalITBIS'),(10,'TotalITBIS1'),(11,'TotalITBIS2'),(12,'TotalITBIS3'),(13,'MontoTotal'),(14,'TotalITBISRetenido'),(15,'TotalISRRetencion')) c (columnOrder,ColumnName)
UNION ALL
         --2 - DetallesItems
         SELECT 1000 + ROW_NUMBER() OVER (ORDER BY d.LineNum, c.ColumnOrder) AS id_record,
                2 AS group_id,
                'DetallesItem' AS group_name,
                c.ColumnName AS xmlTag,
                CAST(CASE c.ColumnName
                    WHEN 'NumeroLinea' THEN CAST(DENSE_RANK() OVER(PARTITION BY d.C_DocEntry ORDER BY d.LineNum) AS NVARCHAR(MAX)) --CAST(d.LineNum+1 AS NVARCHAR(MAX))
                    WHEN 'IndicadorFacturacion' THEN CAST( CASE WHEN @eCFType IN ('46') THEN 3 ELSE t.taxecf_id END AS NVARCHAR(MAX))
                    WHEN 'IndicadorAgenteRetencionoPercepcion' THEN CAST(CASE WHEN @eCFType IN ('41','47') THEN '1' ELSE '#e' END AS NVARCHAR(MAX))
                    WHEN 'MontoITBISRetenido' THEN CASE WHEN @eCFType IN ('41') THEN '0.00' ELSE '#e' END
                    WHEN 'MontoISRRetenido' THEN CASE WHEN @eCFType IN ('41','47') THEN '0.00' ELSE '#e' END
                    WHEN 'NombreItem' THEN CAST(COALESCE(d.Dscription,f.comments) AS NVARCHAR(MAX))
                    WHEN 'IndicadorBienoServicio' THEN CAST(CASE WHEN @eCFType IN ('41','47','43') THEN 2 ELSE  CASE WHEN f.DocType = 'S' THEN 2 ELSE 1 END END AS NVARCHAR(MAX))
                    WHEN 'CantidadItem' THEN FORMAT(COALESCE(NULLIF(d.Quantity,0),1),'0.00')
                    WHEN 'UnidadMedida' THEN COALESCE(CAST(u.ecfunit_id AS NVARCHAR(MAX)),'#e')
                    WHEN 'PrecioUnitarioItem' THEN FORMAT(d.PriceBefDi,'0.00')
                    WHEN 'DescuentoMonto' THEN CASE WHEN d.DiscPrcnt > 0 THEN FORMAT((d.PriceBefDi*(d.DiscPrcnt/100))*d.Quantity,'0.00') ELSE '#e' END
                    WHEN 'TipoSubDescuento' THEN CASE WHEN d.DiscPrcnt > 0 THEN '%' ELSE '#e' END
                    WHEN 'SubDescuentoPorcentaje' THEN CASE WHEN d.DiscPrcnt > 0 THEN FORMAT(d.DiscPrcnt,'0.00') ELSE '#e' END
                    WHEN 'MontoSubDescuento' THEN CASE WHEN d.DiscPrcnt > 0 THEN FORMAT((d.PriceBefDi*(d.DiscPrcnt/100))*d.Quantity,'0.00') ELSE '#e' END
                    WHEN 'MontoItem' THEN FORMAT(d.LineTotal,'0.00')
                END AS NVARCHAR(MAX)) AS xml_value
           FROM cms.dbo.POSRIN1 d
     INNER JOIN cms.dbo.POSORIN f ON (f.C_DocEntry = d.C_DocEntry)
     INNER JOIN cms.dbo.ecf_tax_config t ON (t.taxsap_name = d.TaxCode)
      LEFT JOIN cms.dbo.ecf_units u ON (u.ecfsapunit_name = d.unitMsr)
    CROSS APPLY (VALUES (1,'NumeroLinea'), (2,'IndicadorFacturacion'), (3,'IndicadorAgenteRetencionoPercepcion'), (4,'MontoITBISRetenido'), (4,'MontoISRRetenido'), (5,'NombreItem'),(6,'IndicadorBienoServicio'), (7,'CantidadItem'),(8,'UnidadMedida'),(9,'PrecioUnitarioItem'),(10,'DescuentoMonto'),(11,'TipoSubDescuento'),(12,'SubDescuentoPorcentaje'),(13,'MontoSubDescuento'),(14,'MontoItem')) c (columnOrder,ColumnName)
          WHERE d.C_DocEntry = @InvoiceID
UNION ALL
             --PAGINACION
             SELECT 100000+ROW_NUMBER() OVER (ORDER BY a.pagina_numero, c.columnOrder) AS id_record,
                    5 AS group_id,
                    'Paginacion' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'PaginaNo' THEN CAST(pagina_numero AS NVARCHAR(MAX))
                        WHEN 'NoLineaDesde' THEN CAST(linea_desde AS NVARCHAR(MAX))
                        WHEN 'NoLineaHasta' THEN CAST(linea_hasa AS NVARCHAR(MAX))
                        WHEN 'SubtotalMontoGravadoPagina' THEN CASE WHEN a.monto_grabado > 0 THEN FORMAT(a.monto_grabado,'0.00') ELSE '#e' END
                        WHEN 'SubtotalMontoGravado1Pagina' THEN CASE WHEN a.monto_grabadoI1 > 0 THEN FORMAT(a.monto_grabadoI1,'0.00') ELSE '#e' END
                        WHEN 'SubtotalMontoGravado2Pagina' THEN CASE WHEN a.monto_grabadoI2 > 0 THEN FORMAT(a.monto_grabadoI2,'0.00') ELSE '#e' END
                        WHEN 'SubtotalMontoGravado3Pagina' THEN CASE WHEN a.monto_grabadoI3 > 0 THEN FORMAT(a.monto_grabadoI3,'0.00') ELSE '#e' END
                        WHEN 'SubtotalExentoPagina' THEN CASE WHEN a.monto_exento > 0 THEN FORMAT(a.monto_exento,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbisPagina' THEN CASE WHEN a.total_itbis > 0 THEN FORMAT(a.total_itbis,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbis1Pagina' THEN CASE WHEN a.total_itbis1 > 0 THEN FORMAT(a.total_itbis1,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbis2Pagina' THEN CASE WHEN a.total_itbis2 > 0 THEN FORMAT(a.total_itbis2,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbis3Pagina' THEN CASE WHEN a.total_itbis3 > 0 THEN FORMAT(a.total_itbis3,'0.00') ELSE '#e' END
                        WHEN 'MontoSubtotalPagina' THEN CASE WHEN a.monto_total > 0 THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM (SELECT group_id AS pagina_numero,
                            MIN(w.numero_linea) AS linea_desde,
                            MAX(w.numero_linea) AS linea_hasa,
                            CAST(SUM(monto_grabado) AS NUMERIC(12,2)) AS monto_grabado,
                            CAST(SUM(monto_grabadoI1) AS NUMERIC(12,2)) AS monto_grabadoI1,
                            CAST(SUM(monto_grabadoI2) AS NUMERIC(12,2)) AS monto_grabadoI2,
                            CAST(SUM(monto_grabadoI3) AS NUMERIC(12,2)) AS monto_grabadoI3,
                            CAST(SUM(monto_exento) AS NUMERIC(12,2)) AS monto_exento,
                            CAST(SUM(total_itbis) AS NUMERIC(12,2)) AS total_itbis,
                            CAST(SUM(total_itbis1) AS NUMERIC(12,2)) AS total_itbis1,
                            CAST(SUM(total_itbis2) AS NUMERIC(12,2)) AS total_itbis2,
                            CAST(SUM(total_itbis3) AS NUMERIC(12,2)) AS total_itbis3,
                            CAST(SUM(monto_total) AS NUMERIC(12,2)) AS monto_total
                       FROM (SELECT (d.linenum+1) AS numero_linea,
                                    CEILING(ROW_NUMBER() OVER(ORDER BY d.linenum) / (SELECT CAST(config_value AS NUMERIC(5,1)) FROM cms.dbo.ecf_global_config WHERE config_name = 'ITEMSPERPAGE')) AS group_id,
                                    SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.LineTotal ELSE 0 END) AS monto_grabado,
                                    SUM(CASE WHEN t.taxecf_id IN (1) THEN d.LineTotal ELSE 0 END) AS monto_grabadoI1,
                                    SUM(CASE WHEN t.taxecf_id IN (2) THEN d.LineTotal ELSE 0 END) AS monto_grabadoI2,
                                    SUM(CASE WHEN t.taxecf_id IN (3) THEN d.LineTotal ELSE 0 END) AS monto_grabadoI3,
                                    SUM(CASE WHEN t.taxecf_id IN (4) THEN d.LineTotal ELSE 0 END) AS monto_exento,
                                    MAX(CASE WHEN t.taxecf_id IN (1) THEN d.VatPrcnt ELSE 0 END) AS itbis1,
                                    MAX(CASE WHEN t.taxecf_id IN (2) THEN d.VatPrcnt ELSE 0 END) AS itbis2,
                                    MAX(CASE WHEN t.taxecf_id IN (3) THEN d.VatPrcnt ELSE 0 END) AS itbis3,
                                    SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.VatSum ELSE 0 END) AS total_itbis,
                                    SUM(CASE WHEN t.taxecf_id IN (1) THEN d.VatSum ELSE 0 END) AS total_itbis1,
                                    SUM(CASE WHEN t.taxecf_id IN (2) THEN d.VatSum ELSE 0 END) AS total_itbis2,
                                    SUM(CASE WHEN t.taxecf_id IN (3) THEN d.VatSum ELSE 0 END) AS total_itbis3,
                                    SUM(d.LineTotal+d.VatSum) AS monto_total
                               FROM cms.dbo.POSORIN f
                         INNER JOIN cms.dbo.POSRIN1 d ON (d.C_DocEntry = f.C_DocEntry)
                         INNER JOIN cms.dbo.ecf_tax_config t ON (t.taxsap_name = d.TaxCode)
                              WHERE f.C_DocEntry = @InvoiceID
                              GROUP BY d.linenum
                            ) w
                      GROUP BY w.group_id
                    ) a
        CROSS APPLY (VALUES (1,'PaginaNo'), (2,'NoLineaDesde'), (3,'NoLineaHasta'),(4,'SubtotalMontoGravadoPagina'), (5,'SubtotalMontoGravado1Pagina'),(6,'SubtotalMontoGravado2Pagina'),(7,'SubtotalMontoGravado3Pagina'),(8,'SubtotalExentoPagina'),(9,'SubtotalItbisPagina'),(10,'SubtotalItbis1Pagina'),(11,'SubtotalItbis2Pagina'),(12,'SubtotalItbis3Pagina'),(13,'MontoSubtotalPagina')) c (columnOrder,ColumnName)
              WHERE (SELECT CEILING(COUNT(d.LineNum) / (SELECT CAST(config_value AS NUMERIC(5,1)) FROM cms.dbo.ecf_global_config WHERE config_name = 'ITEMSPERPAGE')) AS pages FROM cms.dbo.POSRIN1 d WHERE d.C_DocEntry = @InvoiceID) > 1
                AND @pPrintPages = 'Y'
UNION ALL
             SELECT 500000 + ROW_NUMBER() OVER (ORDER BY c.ColumnOrder) AS id_record,
                    6 AS group_id,
                    'InformacionReferencia' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'NCFModificado' THEN UPPER(o.U_NCO)
                        WHEN 'FechaNCFModificado' THEN FORMAT(f.DocDate,'dd-MM-yyyy')
                        --WHEN 'CodigoModificacion' THEN CAST(3 AS NVARCHAR(1))
                        --SI EL MONTO DE LA NC ES IGUAL AL THE DE FACTURA ENTONCES ES PARA ANULAR FACTURA (1) DE LO CONTRARIO ES PARA MODIFICAR (3)
                        WHEN 'CodigoModificacion' THEN CAST(CASE WHEN (f.DocTotal-o.DocTotal) = 0 THEN 1 ELSE 3 END AS NVARCHAR(1))
                        WHEN 'RazonModificacion' THEN SUBSTRING(o.Comments,1,90) 
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM cms.dbo.POSORIN o
          LEFT JOIN cms.dbo.POSOINV f ON (f.U_FACNUM = o.U_NCO AND f.CANCELED = 'N')
        CROSS APPLY (VALUES (1,'NCFModificado'), (2,'FechaNCFModificado'),(3,'CodigoModificacion'),(4,'RazonModificacion')) c (columnOrder,ColumnName)
              WHERE O.C_DocEntry = @InvoiceID
               
            ) ecf 
        WHERE @Resumen = 0 AND @eCFType IN ('34')
          AND (SELECT COUNT(n.C_DocEntry) FROM cms.dbo.POSORIN n INNER JOIN cms.dbo.POSOINV i ON (i.U_FACNUM = n.U_NCO AND SUBSTRING(i.U_FACNUM,1,3) IN ('E31','E32','E44','E45','B01','B02','B14','B15')) WHERE n.C_DocEntry = @InvoiceID AND n.DocDate > '2024-01-01') > 0
/*
UNION ALL

--NOTA DE CREDITO AGROFEM DESDE COMPRAS
     SELECT ecf.*
       FROM ( 
  
             --Encabezado
             SELECT ROW_NUMBER() OVER (ORDER BY c.ColumnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'ECF' THEN 'ECF'
                        WHEN 'Version' THEN '1.0' 
                        WHEN 'TipoeCF' THEN i.ecf_type
                        WHEN 'eNCF' THEN UPPER(i.ecf)
                        WHEN 'FechaVencimientoSecuencia' THEN COALESCE(NULLIF(FORMAT(i.ecf_vence,'dd-MM-yyyy'),'01-01-1900'),'#e')
                        WHEN 'IndicadorNotaCredito' THEN CASE WHEN DATEDIFF(DAY,t.U_FacFecha,f.DocDate) <= 30 THEN '0' ELSE '1' END --'#e' --LOGICA PARA NOTA DE CREDITO
                        WHEN 'IndicadorMontoGravado' THEN CASE WHEN @eCFType IN ('31','32','33','34','41','45') THEN '0' ELSE '#e' END -- (0) EL DEALLE DE LOS ITEM NO INCLUYE EL ITBIS
                        WHEN 'TipoIngresos' THEN CASE WHEN @eCFType IN ('31','32','33','34','44','45','46') THEN '01' ELSE '#e' END -- (01) Ingresos por operaciones (No financieros).
                        WHEN 'TipoPago' THEN CAST(CASE WHEN F.ExtraDays <= 1 THEN 1 ELSE 2 END AS NVARCHAR(MAX)) -- 1:Contado | 2:Credio | 3:Gratuito
                        WHEN 'FechaLimitePago' THEN CASE WHEN F.ExtraDays > 1 THEN CASE WHEN @eCFType IN ('43') THEN '#e' ELSE FORMAT(f.DocDueDate,'dd-MM-yyyy') END ELSE '#e' END
                        WHEN 'TerminoPago' THEN CASE WHEN F.ExtraDays > 1 THEN CASE WHEN @eCFType IN ('31','32','33','41','44','45','46','47') THEN REPLACE(g.PymntGroup,'C - ','') ELSE '#e' END ELSE '#e' END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM SBO_AGROFEM.dbo.ORPC f
         INNER JOIN SBO_AGROFEM.dbo.OPCH t ON (t.U_FACNUM = f.U_NCO AND t.CANCELED = 'N')
         INNER JOIN cms.dbo.ecf_xml_invoice i ON (i.doc_id = f.DocEntry AND i.ecf_type IN (34)) 
          LEFT JOIN SBO_AGROFEM.dbo.OCTG g ON (g.ExtraDays = f.ExtraDays /*AND g.ListNum = 2*/)
        CROSS APPLY (VALUES (1,'ECF'), (2,'Version'), (3,'TipoeCF'),(4,'eNCF'), (5,'FechaVencimientoSecuencia'),(6,'IndicadorNotaCredito'),(7,'IndicadorMontoGravado'),(8,'TipoIngresos'),(9,'TipoPago'),(10,'FechaLimitePago'),(11,'TerminoPago')) c (columnOrder,ColumnName)
              WHERE f.DocEntry = @InvoiceID
UNION ALL     --FORMAS DE PAGO
             SELECT 20 + ROW_NUMBER() OVER (ORDER BY s.id_diario_caja_det, c.ColumnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                         WHEN 'FormaPago' THEN CAST(t.ecf_payform_id AS NVARCHAR(MAX))
                         WHEN 'MontoPago' THEN FORMAT(s.monto_caja,'0.00')
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM cms.dbo.POSOINV f
         INNER JOIN cms.dbo.diario_caja_det s ON (s.id_documento = f.C_DocEntry AND s.docType = 'P')
          LEFT JOIN cms.dbo.ecf_payment_forms t ON (t.ecf_payform_sap = s.id_tipo_movimiento)
        CROSS APPLY (VALUES (1,'FormaPago'), (2,'MontoPago')) c (columnOrder,ColumnName)
              WHERE f.C_DocEntry = @InvoiceID
                AND @eCFType IN ('31','32','33','41','44','45','46','47')
UNION ALL    --TOTAL DE PAGINAS
             SELECT 40 + ROW_NUMBER() OVER (ORDER BY p.pages) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    'TotalPaginas' AS xmlTag,
                    FORMAT(p.pages,'0') AS xml_value
               FROM (SELECT CEILING(COUNT(d.LineNum) / (SELECT CAST(config_value AS NUMERIC(5,1)) FROM cms.dbo.ecf_global_config WHERE config_name = 'ITEMSPERPAGE')) AS pages
                       FROM SBO_AGROFEM.dbo.RPC1 d
                 INNER JOIN SBO_AGROFEM.dbo.ORPC f ON (f.DocEntry = d.DocEntry)
                      WHERE d.DocEntry = @InvoiceID
                      GROUP BY f.DocEntry 
                    ) p
              WHERE p.pages > 1
UNION ALL    --EMISOR & COMPRADOR
             SELECT 50 + ROW_NUMBER() OVER (ORDER BY c.ColumnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'RNCEmisor' THEN d.RNC --PARA QUE SIEMPRE ENVIE EL RNC DE AGROFEM
                        WHEN 'RazonSocialEmisor' THEN razon_social
                        WHEN 'NombreComercial' THEN UPPER(CAST(d.descripcion AS NVARCHAR(100)))
                        WHEN 'DireccionEmisor' THEN d.direccion
                        WHEN 'TelefonoEmisor' THEN d.telefono1
                        WHEN 'CorreoEmisor' THEN d.email
                        WHEN 'WebSite' THEN d.web
                        WHEN 'NumeroFacturaInterna' THEN COALESCE(o.NumAtCard,'#e')
                        WHEN 'FechaEmision' THEN FORMAT(o.DocDate,'dd-MM-yyyy')
                        --WHEN 'RNCComprador' THEN CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN CASE WHEN @eCFType IN ('34') OR (@eCFType IN ('34') AND f.DocTotal >= 250000) THEN f.U_FacNit ELSE '#e' END ELSE CASE WHEN F.U_TNegocio = 3 THEN CASE WHEN @eCFType IN ('34') OR (@eCFType IN ('34') AND f.DocTotal >= 250000) THEN '00000000000' ELSE '#e' END ELSE '#e' END END
                        WHEN 'RNCComprador' THEN CASE WHEN LEN(f.U_FacNit) IN (9,11) AND PATINDEX('%[^0-9]%', f.U_FacNit) = 0 THEN o.U_FacNit ELSE CASE WHEN f.U_TNegocio = 3 THEN '00000000000' ELSE '#e' END END
                        --WHEN 'RazonSocialComprador' THEN CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN CASE WHEN @eCFType IN ('34') OR (@eCFType IN ('34') AND f.DocTotal >= 250000) THEN f.CardName ELSE '#e' END ELSE CASE WHEN F.U_TNegocio = 3 THEN CASE WHEN @eCFType IN ('34') OR (@eCFType IN ('34') AND f.DocTotal >= 250000) THEN f.CardName ELSE '#e' END ELSE '#e' END END 
                        WHEN 'RazonSocialComprador' THEN CASE WHEN LEN(f.U_FacNit) IN (9,11) AND PATINDEX('%[^0-9]%', f.U_FacNit) = 0 THEN o.CardName ELSE CASE WHEN f.U_TNegocio = 3 THEN o.CardName ELSE '#e' END END
                        WHEN 'DireccionComprador' THEN '#e' 
                        WHEN 'TelefonoAdicional' THEN '#e' 
                        WHEN 'CodigoInternoComprador' THEN CASE WHEN LEN(f.U_FacNit) IN (9,11) AND PATINDEX('%[^0-9]%', f.U_FacNit) = 0 THEN o.CardCode ELSE CASE WHEN f.U_TNegocio = 3 THEN o.CardCode ELSE '#e' END END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM SBO_AGROFEM.dbo.ORPC o
         INNER JOIN SBO_AGROFEM.dbo.OPCH f ON (f.U_FACNUM = o.U_NCO AND f.CANCELED = 'N')
	     --INNER JOIN cms.dbo.serie s ON (s.id_serie = f.U_SCIASerie)
          LEFT JOIN cms.dbo.division d ON (d.id_division = 1) 
          LEFT JOIN SBO_AGROFEM.dbo.OCRD l ON (l.CardCode = f.CardCode)
         INNER JOIN cms.dbo.ecf_xml_invoice i ON (i.doc_id = o.DocEntry AND i.ecf_type IN (34))
        CROSS APPLY (VALUES (1,'RNCEmisor'), (2,'RazonSocialEmisor'),(3,'NombreComercial'),(4,'DireccionEmisor'),(5,'TelefonoEmisor'),(6,'CorreoEmisor'),(7,'WebSite'),(8,'NumeroFacturaInterna'),(9,'FechaEmision'),(10,'RNCComprador'),(11,'RazonSocialComprador'),(12,'DireccionComprador'),(13,'TelefonoAdicional'),(14,'CodigoInternoComprador')) c (columnOrder,ColumnName)
              WHERE o.DocEntry = @InvoiceID
UNION ALL    --TOTALES
             SELECT 100 + ROW_NUMBER() OVER (ORDER BY c.columnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'MontoGravadoTotal' THEN CASE WHEN a.monto_grabado > 0 THEN FORMAT(a.monto_grabado,'0.00') ELSE CASE WHEN @eCFType IN ('46') THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END END
                        WHEN 'MontoGravadoI1' THEN CASE WHEN a.monto_grabadoI1 > 0 THEN FORMAT(a.monto_grabadoI1,'0.00') ELSE '#e' END
                        WHEN 'MontoGravadoI2' THEN CASE WHEN a.monto_grabadoI2 > 0 THEN FORMAT(a.monto_grabadoI2,'0.00') ELSE '#e' END
                        WHEN 'MontoGravadoI3' THEN CASE WHEN a.monto_grabadoI3 > 0 THEN FORMAT(a.monto_grabadoI3,'0.00') ELSE CASE WHEN @eCFType IN ('46') THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END END
                        WHEN 'MontoExento' THEN CASE WHEN (a.monto_exento > 0 AND @eCFType IN ('31','32','33','34','41','43','44','45','47')) THEN FORMAT(a.monto_exento,'0.00') ELSE '#e' END
                        WHEN 'ITBIS1' THEN CASE WHEN a.itbis1 > 0 THEN FORMAT(a.itbis1,'00') ELSE '#e' END
                        WHEN 'ITBIS2' THEN CASE WHEN a.itbis2 > 0 THEN FORMAT(a.itbis2,'00') ELSE '#e' END
                        WHEN 'ITBIS3' THEN CASE WHEN a.itbis3 > 0 THEN FORMAT(a.itbis3,'0') ELSE CASE WHEN @eCFType IN ('46') THEN '0' ELSE '#e' END END
                        WHEN 'TotalITBIS' THEN CASE WHEN a.total_itbis > 0 THEN FORMAT(a.total_itbis,'0.00') ELSE CASE WHEN @eCFType IN ('46') THEN '0.00' ELSE '#e' END END
                        WHEN 'TotalITBIS1' THEN CASE WHEN a.total_itbis1 > 0 THEN FORMAT(a.total_itbis1,'0.00') ELSE '#e' END
                        WHEN 'TotalITBIS2' THEN CASE WHEN a.total_itbis2 > 0 THEN FORMAT(a.total_itbis2,'0.00') ELSE '#e' END
                        WHEN 'TotalITBIS3' THEN CASE WHEN a.total_itbis3 > 0 THEN FORMAT(a.total_itbis3,'0.00') ELSE CASE WHEN @eCFType IN ('46') THEN '0.00' ELSE '#e' END END
                        WHEN 'MontoTotal' THEN CASE WHEN a.monto_total > 0 THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END
                        WHEN 'TotalITBISRetenido' THEN CASE WHEN @eCFType IN ('41') THEN FORMAT(0,'0.00') ELSE '#e' END
                        WHEN 'TotalISRRetencion' THEN CASE WHEN @eCFType IN ('41','47') THEN FORMAT(0,'0.00') ELSE '#e' END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM (SELECT CAST(SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabado,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (1) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabadoI1,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (2) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabadoI2,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (3) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabadoI3,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (4) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_exento,
                            CAST(MAX(CASE WHEN t.taxecf_id IN (1) THEN d.VatPrcnt ELSE 0 END) AS NUMERIC(12,2)) AS itbis1,
                            CAST(MAX(CASE WHEN t.taxecf_id IN (2) THEN d.VatPrcnt ELSE 0 END) AS NUMERIC(12,2)) AS itbis2,
                            CAST(MAX(CASE WHEN t.taxecf_id IN (3) THEN d.VatPrcnt ELSE 0 END) AS NUMERIC(12,2)) AS itbis3,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (1) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis1,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (2) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis2,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (3) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis3,
                            CAST(SUM(d.LineTotal+d.VatSum) AS NUMERIC(12,2)) AS monto_total
                       FROM SBO_AGROFEM.dbo.ORPC f
                 INNER JOIN SBO_AGROFEM.dbo.RPC1 d ON (d.DocEntry = f.DocEntry)
                 INNER JOIN SBO_AGROFEM.dbo.OPCH o ON (o.U_FACNUM = f.U_NCO AND o.CANCELED = 'N')
                 INNER JOIN cms.dbo.ecf_tax_config t ON (t.taxsap_name = d.TaxCode)
                      WHERE f.DocEntry = @InvoiceID
                        --AND DATEDIFF(DAY, o.U_FacFecha,f.DocDate) <= 30
                    ) a
        CROSS APPLY (VALUES (1,'MontoGravadoTotal'), (2,'MontoGravadoI1'), (3,'MontoGravadoI2'),(4,'MontoGravadoI3'), (5,'MontoExento'),(6,'ITBIS1'),(7,'ITBIS2'),(8,'ITBIS3'),(9,'TotalITBIS'),(10,'TotalITBIS1'),(11,'TotalITBIS2'),(12,'TotalITBIS3'),(13,'MontoTotal'),(14,'TotalITBISRetenido'),(15,'TotalISRRetencion')) c (columnOrder,ColumnName)
UNION ALL
         --2 - DetallesItems
         SELECT 1000 + ROW_NUMBER() OVER (ORDER BY d.LineNum, c.ColumnOrder) AS id_record,
                2 AS group_id,
                'DetallesItem' AS group_name,
                c.ColumnName AS xmlTag,
                CAST(CASE c.ColumnName
                    WHEN 'NumeroLinea' THEN CAST(DENSE_RANK() OVER(PARTITION BY d.DocEntry ORDER BY d.LineNum) AS NVARCHAR(MAX)) --CAST(d.LineNum+1 AS NVARCHAR(MAX))
                    WHEN 'IndicadorFacturacion' THEN CAST( CASE WHEN @eCFType IN ('46') THEN 3 ELSE t.taxecf_id END AS NVARCHAR(MAX))
                    WHEN 'IndicadorAgenteRetencionoPercepcion' THEN CAST(CASE WHEN @eCFType IN ('41','47') THEN '1' ELSE '#e' END AS NVARCHAR(MAX))
                    WHEN 'MontoITBISRetenido' THEN CASE WHEN @eCFType IN ('41') THEN '0.00' ELSE '#e' END
                    WHEN 'MontoISRRetenido' THEN CASE WHEN @eCFType IN ('41','47') THEN '0.00' ELSE '#e' END
                    WHEN 'NombreItem' THEN CAST(COALESCE(d.Dscription,f.comments) AS NVARCHAR(MAX))
                    WHEN 'IndicadorBienoServicio' THEN CAST(CASE WHEN @eCFType IN ('41','47','43') THEN 2 ELSE  CASE WHEN f.DocType = 'S' THEN 2 ELSE 1 END END AS NVARCHAR(MAX))
                    WHEN 'CantidadItem' THEN FORMAT(COALESCE(NULLIF(d.Quantity,0),1),'0.00')
                    WHEN 'UnidadMedida' THEN COALESCE(CAST(u.ecfunit_id AS NVARCHAR(MAX)),'#e')
                    WHEN 'PrecioUnitarioItem' THEN FORMAT(d.PriceBefDi,'0.00')
                    WHEN 'DescuentoMonto' THEN CASE WHEN d.DiscPrcnt > 0 THEN FORMAT((d.PriceBefDi*(d.DiscPrcnt/100))*d.Quantity,'0.00') ELSE '#e' END
                    WHEN 'TipoSubDescuento' THEN CASE WHEN d.DiscPrcnt > 0 THEN '%' ELSE '#e' END
                    WHEN 'SubDescuentoPorcentaje' THEN CASE WHEN d.DiscPrcnt > 0 THEN FORMAT(d.DiscPrcnt,'0.00') ELSE '#e' END
                    WHEN 'MontoSubDescuento' THEN CASE WHEN d.DiscPrcnt > 0 THEN FORMAT((d.PriceBefDi*(d.DiscPrcnt/100))*d.Quantity,'0.00') ELSE '#e' END
                    WHEN 'MontoItem' THEN FORMAT(d.LineTotal,'0.00')
                END AS NVARCHAR(MAX)) AS xml_value
           FROM SBO_AGROFEM.dbo.RPC1 d
     INNER JOIN SBO_AGROFEM.dbo.ORPC f ON (f.DocEntry = d.DocEntry)
     INNER JOIN cms.dbo.ecf_tax_config t ON (t.taxsap_name = d.TaxCode)
      LEFT JOIN cms.dbo.ecf_units u ON (u.ecfsapunit_name = d.unitMsr)
    CROSS APPLY (VALUES (1,'NumeroLinea'), (2,'IndicadorFacturacion'), (3,'IndicadorAgenteRetencionoPercepcion'), (4,'MontoITBISRetenido'), (4,'MontoISRRetenido'), (5,'NombreItem'),(6,'IndicadorBienoServicio'), (7,'CantidadItem'),(8,'UnidadMedida'),(9,'PrecioUnitarioItem'),(10,'DescuentoMonto'),(11,'TipoSubDescuento'),(12,'SubDescuentoPorcentaje'),(13,'MontoSubDescuento'),(14,'MontoItem')) c (columnOrder,ColumnName)
          WHERE d.DocEntry = @InvoiceID
UNION ALL
             --PAGINACION
             SELECT 100000+ROW_NUMBER() OVER (ORDER BY a.pagina_numero, c.columnOrder) AS id_record,
                    5 AS group_id,
                    'Paginacion' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'PaginaNo' THEN CAST(pagina_numero AS NVARCHAR(MAX))
                        WHEN 'NoLineaDesde' THEN CAST(linea_desde AS NVARCHAR(MAX))
                        WHEN 'NoLineaHasta' THEN CAST(linea_hasa AS NVARCHAR(MAX))
                        WHEN 'SubtotalMontoGravadoPagina' THEN CASE WHEN a.monto_grabado > 0 THEN FORMAT(a.monto_grabado,'0.00') ELSE '#e' END
                        WHEN 'SubtotalMontoGravado1Pagina' THEN CASE WHEN a.monto_grabadoI1 > 0 THEN FORMAT(a.monto_grabadoI1,'0.00') ELSE '#e' END
                        WHEN 'SubtotalMontoGravado2Pagina' THEN CASE WHEN a.monto_grabadoI2 > 0 THEN FORMAT(a.monto_grabadoI2,'0.00') ELSE '#e' END
                        WHEN 'SubtotalMontoGravado3Pagina' THEN CASE WHEN a.monto_grabadoI3 > 0 THEN FORMAT(a.monto_grabadoI3,'0.00') ELSE '#e' END
                        WHEN 'SubtotalExentoPagina' THEN CASE WHEN a.monto_exento > 0 THEN FORMAT(a.monto_exento,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbisPagina' THEN CASE WHEN a.total_itbis > 0 THEN FORMAT(a.total_itbis,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbis1Pagina' THEN CASE WHEN a.total_itbis1 > 0 THEN FORMAT(a.total_itbis1,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbis2Pagina' THEN CASE WHEN a.total_itbis2 > 0 THEN FORMAT(a.total_itbis2,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbis3Pagina' THEN CASE WHEN a.total_itbis3 > 0 THEN FORMAT(a.total_itbis3,'0.00') ELSE '#e' END
                        WHEN 'MontoSubtotalPagina' THEN CASE WHEN a.monto_total > 0 THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM (SELECT group_id AS pagina_numero,
                            MIN(w.numero_linea) AS linea_desde,
                            MAX(w.numero_linea) AS linea_hasa,
                            CAST(SUM(monto_grabado) AS NUMERIC(12,2)) AS monto_grabado,
                            CAST(SUM(monto_grabadoI1) AS NUMERIC(12,2)) AS monto_grabadoI1,
                            CAST(SUM(monto_grabadoI2) AS NUMERIC(12,2)) AS monto_grabadoI2,
                            CAST(SUM(monto_grabadoI3) AS NUMERIC(12,2)) AS monto_grabadoI3,
                            CAST(SUM(monto_exento) AS NUMERIC(12,2)) AS monto_exento,
                            CAST(SUM(total_itbis) AS NUMERIC(12,2)) AS total_itbis,
                            CAST(SUM(total_itbis1) AS NUMERIC(12,2)) AS total_itbis1,
                            CAST(SUM(total_itbis2) AS NUMERIC(12,2)) AS total_itbis2,
                            CAST(SUM(total_itbis3) AS NUMERIC(12,2)) AS total_itbis3,
                            CAST(SUM(monto_total) AS NUMERIC(12,2)) AS monto_total
                       FROM (SELECT (d.linenum+1) AS numero_linea,
                                    CEILING(ROW_NUMBER() OVER(ORDER BY d.linenum) / (SELECT CAST(config_value AS NUMERIC(5,1)) FROM cms.dbo.ecf_global_config WHERE config_name = 'ITEMSPERPAGE')) AS group_id,
                                    SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.LineTotal ELSE 0 END) AS monto_grabado,
                                    SUM(CASE WHEN t.taxecf_id IN (1) THEN d.LineTotal ELSE 0 END) AS monto_grabadoI1,
                                    SUM(CASE WHEN t.taxecf_id IN (2) THEN d.LineTotal ELSE 0 END) AS monto_grabadoI2,
                                    SUM(CASE WHEN t.taxecf_id IN (3) THEN d.LineTotal ELSE 0 END) AS monto_grabadoI3,
                                    SUM(CASE WHEN t.taxecf_id IN (4) THEN d.LineTotal ELSE 0 END) AS monto_exento,
                                    MAX(CASE WHEN t.taxecf_id IN (1) THEN d.VatPrcnt ELSE 0 END) AS itbis1,
                                    MAX(CASE WHEN t.taxecf_id IN (2) THEN d.VatPrcnt ELSE 0 END) AS itbis2,
                                    MAX(CASE WHEN t.taxecf_id IN (3) THEN d.VatPrcnt ELSE 0 END) AS itbis3,
                                    SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.VatSum ELSE 0 END) AS total_itbis,
                                    SUM(CASE WHEN t.taxecf_id IN (1) THEN d.VatSum ELSE 0 END) AS total_itbis1,
                                    SUM(CASE WHEN t.taxecf_id IN (2) THEN d.VatSum ELSE 0 END) AS total_itbis2,
                                    SUM(CASE WHEN t.taxecf_id IN (3) THEN d.VatSum ELSE 0 END) AS total_itbis3,
                                    SUM(d.LineTotal+d.VatSum) AS monto_total
                               FROM SBO_AGROFEM.dbo.ORPC f
                         INNER JOIN SBO_AGROFEM.dbo.RPC1 d ON (d.DocEntry = f.DocEntry)
                         INNER JOIN cms.dbo.ecf_tax_config t ON (t.taxsap_name = d.TaxCode)
                              WHERE f.DocEntry = @InvoiceID
                              GROUP BY d.linenum
                            ) w
                      GROUP BY w.group_id
                    ) a
        CROSS APPLY (VALUES (1,'PaginaNo'), (2,'NoLineaDesde'), (3,'NoLineaHasta'),(4,'SubtotalMontoGravadoPagina'), (5,'SubtotalMontoGravado1Pagina'),(6,'SubtotalMontoGravado2Pagina'),(7,'SubtotalMontoGravado3Pagina'),(8,'SubtotalExentoPagina'),(9,'SubtotalItbisPagina'),(10,'SubtotalItbis1Pagina'),(11,'SubtotalItbis2Pagina'),(12,'SubtotalItbis3Pagina'),(13,'MontoSubtotalPagina')) c (columnOrder,ColumnName)
              WHERE (SELECT CEILING(COUNT(d.LineNum) / (SELECT CAST(config_value AS NUMERIC(5,1)) FROM cms.dbo.ecf_global_config WHERE config_name = 'ITEMSPERPAGE')) AS pages FROM SBO_AGROFEM.dbo.RPC1 d WHERE d.DocEntry = @InvoiceID) > 1
                AND @pPrintPages = 'Y'
UNION ALL
             SELECT 500000 + ROW_NUMBER() OVER (ORDER BY c.ColumnOrder) AS id_record,
                    6 AS group_id,
                    'InformacionReferencia' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'NCFModificado' THEN UPPER(o.U_NCO)
                        WHEN 'FechaNCFModificado' THEN FORMAT(f.U_FacFecha,'dd-MM-yyyy')
                        --WHEN 'CodigoModificacion' THEN CAST(3 AS NVARCHAR(1))
                        --SI EL MONTO DE LA NC ES IGUAL AL THE DE FACTURA ENTONCES ES PARA ANULAR FACTURA (1) DE LO CONTRARIO ES PARA MODIFICAR (3)
                        WHEN 'CodigoModificacion' THEN CAST(CASE WHEN (f.DocTotal-o.DocTotal) = 0 THEN 1 ELSE 3 END AS NVARCHAR(1))
                        WHEN 'RazonModificacion' THEN SUBSTRING(o.Comments,1,90) 
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM SBO_AGROFEM.dbo.ORPC o
          LEFT JOIN SBO_AGROFEM.dbo.OPCH f ON (f.U_FACNUM = o.U_NCO AND f.CANCELED = 'N')
        CROSS APPLY (VALUES (1,'NCFModificado'), (2,'FechaNCFModificado'),(3,'CodigoModificacion'),(4,'RazonModificacion')) c (columnOrder,ColumnName)
              WHERE O.DocEntry = @InvoiceID
               
            ) ecf 
        WHERE @Resumen = 0 AND @eCFType IN ('34')
          AND (SELECT COUNT(n.DocEntry) FROM SBO_AGROFEM.dbo.ORPC n INNER JOIN SBO_AGROFEM.dbo.OPCH i ON (i.U_FACNUM = n.U_NCO  AND i.CANCELED = 'N' AND SUBSTRING(i.U_FACNUM,1,3) IN ('E41','E43')) WHERE n.DocEntry = @InvoiceID) > 0

UNION ALL

/*NOTA DE CREDITO ESPECIAL TIPO 50 SOLO PARA CORREGIR ENVIADOS POR ERROR*/

     SELECT ecf.*
       FROM ( 
  
             --Encabezado
             SELECT ROW_NUMBER() OVER (ORDER BY c.ColumnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'ECF' THEN 'ECF'
                        WHEN 'Version' THEN '1.0' 
                        WHEN 'TipoeCF' THEN i.ecf_type
                        WHEN 'eNCF' THEN i.ecf
                        WHEN 'FechaVencimientoSecuencia' THEN '#e' --COALESCE(NULLIF(FORMAT(i.ecf_vence,'dd-MM-yyyy'),'01-01-1900'),'#e')
                        WHEN 'IndicadorNotaCredito' THEN CASE WHEN DATEDIFF(DAY,t.U_FacFecha,f.DocDate) <= 30 THEN '0' ELSE '1' END --'#e' --LOGICA PARA NOTA DE CREDITO
                        WHEN 'IndicadorMontoGravado' THEN CASE WHEN @eCFType IN ('50') THEN '0' ELSE '#e' END -- (0) EL DEALLE DE LOS ITEM NO INCLUYE EL ITBIS
                        WHEN 'TipoIngresos' THEN CASE WHEN @eCFType IN ('50') THEN '01' ELSE '#e' END -- (01) Ingresos por operaciones (No financieros).
                        WHEN 'TipoPago' THEN CAST(CASE WHEN F.ExtraDays <= 1 THEN 1 ELSE 2 END AS NVARCHAR(MAX)) -- 1:Contado | 2:Credio | 3:Gratuito
                        WHEN 'FechaLimitePago' THEN CASE WHEN F.ExtraDays > 1 THEN CASE WHEN @eCFType IN ('43') THEN '#e' ELSE FORMAT(f.DocDueDate,'dd-MM-yyyy') END ELSE '#e' END
                        WHEN 'TerminoPago' THEN CASE WHEN F.ExtraDays > 1 THEN CASE WHEN @eCFType IN ('31','32','33','41','44','45','46','47') THEN REPLACE(g.PymntGroup,'C - ','') ELSE '#e' END ELSE '#e' END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM cms.dbo.POSOINV f
         INNER JOIN cms.dbo.POSOINV t ON (t.U_FACNUM = f.U_FACNUM)
         INNER JOIN cms.dbo.ecf_xml_invoice i ON (i.doc_id = f.C_DocEntry) 
          LEFT JOIN SBO_AGROFEM.dbo.OCTG g ON (g.ExtraDays = f.ExtraDays AND g.ListNum = 2)
        CROSS APPLY (VALUES (1,'ECF'), (2,'Version'), (3,'TipoeCF'),(4,'eNCF'), (5,'FechaVencimientoSecuencia'),(6,'IndicadorNotaCredito'),(7,'IndicadorMontoGravado'),(8,'TipoIngresos'),(9,'TipoPago'),(10,'FechaLimitePago'),(11,'TerminoPago')) c (columnOrder,ColumnName)
              WHERE f.C_DocEntry = @InvoiceID
UNION ALL     --FORMAS DE PAGO
             SELECT 20 + ROW_NUMBER() OVER (ORDER BY s.id_diario_caja_det, c.ColumnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                         WHEN 'FormaPago' THEN CAST(t.ecf_payform_id AS NVARCHAR(MAX))
                         WHEN 'MontoPago' THEN FORMAT(s.monto_caja,'0.00')
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM cms.dbo.POSOINV f
         INNER JOIN cms.dbo.diario_caja_det s ON (s.id_documento = f.C_DocEntry AND s.docType = 'P')
          LEFT JOIN cms.dbo.ecf_payment_forms t ON (t.ecf_payform_sap = s.id_tipo_movimiento)
        CROSS APPLY (VALUES (1,'FormaPago'), (2,'MontoPago')) c (columnOrder,ColumnName)
              WHERE f.C_DocEntry = @InvoiceID
                AND @eCFType IN ('31','32','33','41','44','45','46','47')
UNION ALL    --TOTAL DE PAGINAS
             SELECT 40 + ROW_NUMBER() OVER (ORDER BY p.pages) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    'TotalPaginas' AS xmlTag,
                    FORMAT(p.pages,'0') AS xml_value
               FROM (SELECT CEILING(COUNT(d.LineNum) / (SELECT CAST(config_value AS NUMERIC(5,1)) FROM cms.dbo.ecf_global_config WHERE config_name = 'ITEMSPERPAGE')) AS pages
                       FROM cms.dbo.POSINV1 d
                 INNER JOIN cms.dbo.POSOINV f ON (f.C_DocEntry = d.C_DocEntry)
                      WHERE d.DocEntry = @InvoiceID
                      GROUP BY f.C_DocEntry 
                    ) p
              WHERE p.pages > 1 AND @pPrintPages = 'Y'
UNION ALL    --EMISOR & COMPRADOR
             SELECT 50 + ROW_NUMBER() OVER (ORDER BY c.ColumnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'RNCEmisor' THEN d.RNC --PARA QUE SIEMPRE ENVIE EL RNC DE AGROFEM
                        WHEN 'RazonSocialEmisor' THEN razon_social
                        WHEN 'NombreComercial' THEN UPPER(d.nombre)
                        WHEN 'DireccionEmisor' THEN d.direccion
                        WHEN 'TelefonoEmisor' THEN d.telefono1
                        WHEN 'CorreoEmisor' THEN d.email
                        WHEN 'WebSite' THEN d.web
                        WHEN 'NumeroFacturaInterna' THEN f.NumAtCard
                        WHEN 'FechaEmision' THEN FORMAT(o.DocDate,'dd-MM-yyyy')
                        --WHEN 'RNCComprador' THEN CASE WHEN ((LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) AND PATINDEX('%[^0-9]%', f.U_FacNit) = 0) THEN CASE WHEN @eCFType IN ('34') OR (@eCFType IN ('34') AND f.DocTotal >= 250000) THEN f.U_FacNit ELSE '#e' END ELSE CASE WHEN F.U_TNegocio = 3 THEN CASE WHEN @eCFType IN ('31') OR (@eCFType IN ('32') AND f.DocTotal >= 250000) THEN '00000000000' ELSE '00000000000' END ELSE '#e' END END
                        WHEN 'RNCComprador' THEN CASE WHEN LEN(f.U_FacNit) IN (9,11) AND PATINDEX('%[^0-9]%', f.U_FacNit) = 0 THEN o.U_FacNit ELSE CASE WHEN f.U_TNegocio = 3 THEN '00000000000' ELSE '#e' END END
                        --WHEN 'RazonSocialComprador' THEN CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN CASE WHEN @eCFType IN ('34') OR (@eCFType IN ('34') AND f.DocTotal >= 250000) THEN f.CardName ELSE '#e' END ELSE CASE WHEN F.U_TNegocio = 3 THEN CASE WHEN @eCFType IN ('31') OR (@eCFType IN ('32') AND f.DocTotal >= 250000) THEN f.CardName ELSE f.CardName END ELSE '#e' END END 
                        WHEN 'RazonSocialComprador' THEN CASE WHEN LEN(f.U_FacNit) IN (9,11) AND PATINDEX('%[^0-9]%', f.U_FacNit) = 0 THEN o.CardName ELSE CASE WHEN f.U_TNegocio = 3 THEN o.CardName ELSE '#e' END END
                        WHEN 'DireccionComprador' THEN '#e' --CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN CASE WHEN @eCFType IN ('31') OR (@eCFType IN ('32') AND f.DocTotal >= 250000) THEN COALESCE(NULLIF(f.Address2,''),'#e') ELSE '#e' END ELSE '#e' END 
                        WHEN 'TelefonoAdicional' THEN '#e'
                        --WHEN 'TelefonoAdicional' THEN CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN CASE WHEN @eCFType IN ('31') OR (@eCFType IN ('32') AND f.DocTotal >= 250000) THEN COALESCE(l.Cellular,l.phone1,l.phone2,'#e') ELSE '#e' END ELSE '#e' END 
                        --WHEN 'CodigoInternoComprador' THEN CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN CASE WHEN @eCFType IN ('31') OR (@eCFType IN ('32') AND f.DocTotal >= 250000) THEN f.CardCode ELSE '#e' END ELSE '#e' END 
                        WHEN 'CodigoInternoComprador' THEN CASE WHEN LEN(f.U_FacNit) IN (9,11) AND PATINDEX('%[^0-9]%', f.U_FacNit) = 0 THEN o.CardCode ELSE CASE WHEN f.U_TNegocio = 3 THEN o.CardCode ELSE '#e' END END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM cms.dbo.POSOINV o
         INNER JOIN cms.dbo.POSOINV f ON (f.U_FACNUM = o.U_FACNUM)
	     INNER JOIN cms.dbo.serie s ON (s.id_serie = f.U_SCIASerie)
         INNER JOIN cms.dbo.division d ON (d.id_division = s.id_division) 
          LEFT JOIN SBO_AGROFEM.dbo.OCRD l ON (l.CardCode = f.CardCode)
         INNER JOIN cms.dbo.ecf_xml_invoice i ON (i.doc_id = o.C_DocEntry)
        CROSS APPLY (VALUES (1,'RNCEmisor'), (2,'RazonSocialEmisor'),(3,'NombreComercial'),(4,'DireccionEmisor'),(5,'TelefonoEmisor'),(6,'CorreoEmisor'),(7,'WebSite'),(8,'NumeroFacturaInterna'),(9,'FechaEmision'),(10,'RNCComprador'),(11,'RazonSocialComprador'),(12,'DireccionComprador'),(13,'TelefonoAdicional'),(14,'CodigoInternoComprador')) c (columnOrder,ColumnName)
              WHERE o.C_DocEntry = @InvoiceID
UNION ALL    --TOTALES
             SELECT 100 + ROW_NUMBER() OVER (ORDER BY c.columnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'MontoGravadoTotal' THEN CASE WHEN a.monto_grabado > 0 THEN FORMAT(a.monto_grabado,'0.00') ELSE CASE WHEN @eCFType IN ('46') THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END END
                        WHEN 'MontoGravadoI1' THEN CASE WHEN a.monto_grabadoI1 > 0 THEN FORMAT(a.monto_grabadoI1,'0.00') ELSE '#e' END
                        WHEN 'MontoGravadoI2' THEN CASE WHEN a.monto_grabadoI2 > 0 THEN FORMAT(a.monto_grabadoI2,'0.00') ELSE '#e' END
                        WHEN 'MontoGravadoI3' THEN CASE WHEN a.monto_grabadoI3 > 0 THEN FORMAT(a.monto_grabadoI3,'0.00') ELSE CASE WHEN @eCFType IN ('46') THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END END
                        WHEN 'MontoExento' THEN CASE WHEN (a.monto_exento > 0 AND @eCFType IN ('50')) THEN FORMAT(a.monto_exento,'0.00') ELSE '#e' END
                        WHEN 'ITBIS1' THEN CASE WHEN a.itbis1 > 0 THEN FORMAT(a.itbis1,'00') ELSE '#e' END
                        WHEN 'ITBIS2' THEN CASE WHEN a.itbis2 > 0 THEN FORMAT(a.itbis2,'00') ELSE '#e' END
                        WHEN 'ITBIS3' THEN CASE WHEN a.itbis3 > 0 THEN FORMAT(a.itbis3,'0') ELSE CASE WHEN @eCFType IN ('46') THEN '0' ELSE '#e' END END
                        WHEN 'TotalITBIS' THEN CASE WHEN a.total_itbis > 0 THEN FORMAT(a.total_itbis,'0.00') ELSE CASE WHEN @eCFType IN ('46') THEN '0.00' ELSE '#e' END END
                        WHEN 'TotalITBIS1' THEN CASE WHEN a.total_itbis1 > 0 THEN FORMAT(a.total_itbis1,'0.00') ELSE '#e' END
                        WHEN 'TotalITBIS2' THEN CASE WHEN a.total_itbis2 > 0 THEN FORMAT(a.total_itbis2,'0.00') ELSE '#e' END
                        WHEN 'TotalITBIS3' THEN CASE WHEN a.total_itbis3 > 0 THEN FORMAT(a.total_itbis3,'0.00') ELSE CASE WHEN @eCFType IN ('46') THEN '0.00' ELSE '#e' END END
                        WHEN 'MontoTotal' THEN CASE WHEN a.monto_total > 0 THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END
                        WHEN 'TotalITBISRetenido' THEN CASE WHEN @eCFType IN ('41') THEN FORMAT(0,'0.00') ELSE '#e' END
                        WHEN 'TotalISRRetencion' THEN CASE WHEN @eCFType IN ('41','47') THEN FORMAT(0,'0.00') ELSE '#e' END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM (SELECT CAST(SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabado,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (1) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabadoI1,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (2) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabadoI2,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (3) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabadoI3,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (4) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_exento,
                            CAST(MAX(CASE WHEN t.taxecf_id IN (1) THEN d.VatPrcnt ELSE 0 END) AS NUMERIC(12,2)) AS itbis1,
                            CAST(MAX(CASE WHEN t.taxecf_id IN (2) THEN d.VatPrcnt ELSE 0 END) AS NUMERIC(12,2)) AS itbis2,
                            CAST(MAX(CASE WHEN t.taxecf_id IN (3) THEN d.VatPrcnt ELSE 0 END) AS NUMERIC(12,2)) AS itbis3,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (1) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis1,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (2) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis2,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (3) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis3,
                            CAST(SUM(d.LineTotal+d.VatSum) AS NUMERIC(12,2)) AS monto_total
                       FROM cms.dbo.POSOINV f
                 INNER JOIN cms.dbo.POSINV1 d ON (d.C_DocEntry = f.C_DocEntry)
                 INNER JOIN SBO_AGROFEM.dbo.OINV o ON (o.U_FACNUM = f.U_FACNUM)
                 INNER JOIN cms.dbo.ecf_tax_config t ON (t.taxsap_name = d.TaxCode)
                      WHERE f.C_DocEntry = @InvoiceID
                        --AND DATEDIFF(DAY, o.U_FacFecha,f.DocDate) <= 30
                    ) a
        CROSS APPLY (VALUES (1,'MontoGravadoTotal'), (2,'MontoGravadoI1'), (3,'MontoGravadoI2'),(4,'MontoGravadoI3'), (5,'MontoExento'),(6,'ITBIS1'),(7,'ITBIS2'),(8,'ITBIS3'),(9,'TotalITBIS'),(10,'TotalITBIS1'),(11,'TotalITBIS2'),(12,'TotalITBIS3'),(13,'MontoTotal'),(14,'TotalITBISRetenido'),(15,'TotalISRRetencion')) c (columnOrder,ColumnName)
UNION ALL
         --2 - DetallesItems
         SELECT 1000 + ROW_NUMBER() OVER (ORDER BY d.LineNum, c.ColumnOrder) AS id_record,
                2 AS group_id,
                'DetallesItem' AS group_name,
                c.ColumnName AS xmlTag,
                CAST(CASE c.ColumnName
                    WHEN 'NumeroLinea' THEN CAST(DENSE_RANK() OVER(PARTITION BY d.DocEntry ORDER BY d.LineNum) AS NVARCHAR(MAX)) --CAST(d.LineNum+1 AS NVARCHAR(MAX))
                    WHEN 'IndicadorFacturacion' THEN CAST( CASE WHEN @eCFType IN ('46') THEN 3 ELSE t.taxecf_id END AS NVARCHAR(MAX))
                    WHEN 'IndicadorAgenteRetencionoPercepcion' THEN CAST(CASE WHEN @eCFType IN ('41','47') THEN '1' ELSE '#e' END AS NVARCHAR(MAX))
                    WHEN 'MontoITBISRetenido' THEN CASE WHEN @eCFType IN ('41') THEN '0.00' ELSE '#e' END
                    WHEN 'MontoISRRetenido' THEN CASE WHEN @eCFType IN ('41','47') THEN '0.00' ELSE '#e' END
                    WHEN 'NombreItem' THEN CAST(d.Dscription AS NVARCHAR(MAX))
                    WHEN 'IndicadorBienoServicio' THEN CAST(CASE WHEN @eCFType IN ('41','47','43') THEN 2 ELSE  CASE WHEN f.DocType = 'S' THEN 2 ELSE 1 END END AS NVARCHAR(MAX))
                    WHEN 'CantidadItem' THEN FORMAT(COALESCE(NULLIF(d.Quantity,0),1),'0.00')
                    WHEN 'UnidadMedida' THEN CAST(u.ecfunit_id AS NVARCHAR(MAX))
                    WHEN 'PrecioUnitarioItem' THEN FORMAT(d.PriceBefDi,'0.00')
                    WHEN 'DescuentoMonto' THEN CASE WHEN d.DiscPrcnt > 0 THEN FORMAT((d.PriceBefDi*(d.DiscPrcnt/100))*d.Quantity,'0.00') ELSE '#e' END
                    WHEN 'TipoSubDescuento' THEN CASE WHEN d.DiscPrcnt > 0 THEN '%' ELSE '#e' END
                    WHEN 'SubDescuentoPorcentaje' THEN CASE WHEN d.DiscPrcnt > 0 THEN FORMAT(d.DiscPrcnt,'0.00') ELSE '#e' END
                    WHEN 'MontoSubDescuento' THEN CASE WHEN d.DiscPrcnt > 0 THEN FORMAT((d.PriceBefDi*(d.DiscPrcnt/100))*d.Quantity,'0.00') ELSE '#e' END
                    WHEN 'MontoItem' THEN FORMAT(d.LineTotal,'0.00')
                END AS NVARCHAR(MAX)) AS xml_value
           FROM cms.dbo.POSINV1 d
     INNER JOIN cms.dbo.POSOINV f ON (f.C_DocEntry = d.C_DocEntry)
     INNER JOIN cms.dbo.ecf_tax_config t ON (t.taxsap_name = d.TaxCode)
      LEFT JOIN cms.dbo.ecf_units u ON (u.ecfsapunit_namemf = d.unitMsr)
    CROSS APPLY (VALUES (1,'NumeroLinea'), (2,'IndicadorFacturacion'), (3,'IndicadorAgenteRetencionoPercepcion'), (4,'MontoITBISRetenido'), (4,'MontoISRRetenido'), (5,'NombreItem'),(6,'IndicadorBienoServicio'), (7,'CantidadItem'),(8,'UnidadMedida'),(9,'PrecioUnitarioItem'),(10,'DescuentoMonto'),(11,'TipoSubDescuento'),(12,'SubDescuentoPorcentaje'),(13,'MontoSubDescuento'),(14,'MontoItem')) c (columnOrder,ColumnName)
          WHERE d.C_DocEntry = @InvoiceID
UNION ALL
             --PAGINACION
             SELECT 100000+ROW_NUMBER() OVER (ORDER BY a.pagina_numero, c.columnOrder) AS id_record,
                    5 AS group_id,
                    'Paginacion' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'PaginaNo' THEN CAST(pagina_numero AS NVARCHAR(MAX))
                        WHEN 'NoLineaDesde' THEN CAST(linea_desde AS NVARCHAR(MAX))
                        WHEN 'NoLineaHasta' THEN CAST(linea_hasa AS NVARCHAR(MAX))
                        WHEN 'SubtotalMontoGravadoPagina' THEN CASE WHEN a.monto_grabado > 0 THEN FORMAT(a.monto_grabado,'0.00') ELSE '#e' END
                        WHEN 'SubtotalMontoGravado1Pagina' THEN CASE WHEN a.monto_grabadoI1 > 0 THEN FORMAT(a.monto_grabadoI1,'0.00') ELSE '#e' END
                        WHEN 'SubtotalMontoGravado2Pagina' THEN CASE WHEN a.monto_grabadoI2 > 0 THEN FORMAT(a.monto_grabadoI2,'0.00') ELSE '#e' END
                        WHEN 'SubtotalMontoGravado3Pagina' THEN CASE WHEN a.monto_grabadoI3 > 0 THEN FORMAT(a.monto_grabadoI3,'0.00') ELSE '#e' END
                        WHEN 'SubtotalExentoPagina' THEN CASE WHEN a.monto_exento > 0 THEN FORMAT(a.monto_exento,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbisPagina' THEN CASE WHEN a.total_itbis > 0 THEN FORMAT(a.total_itbis,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbis1Pagina' THEN CASE WHEN a.total_itbis1 > 0 THEN FORMAT(a.total_itbis1,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbis2Pagina' THEN CASE WHEN a.total_itbis2 > 0 THEN FORMAT(a.total_itbis2,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbis3Pagina' THEN CASE WHEN a.total_itbis3 > 0 THEN FORMAT(a.total_itbis3,'0.00') ELSE '#e' END
                        WHEN 'MontoSubtotalPagina' THEN CASE WHEN a.monto_total > 0 THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM (SELECT group_id AS pagina_numero,
                            MIN(w.numero_linea) AS linea_desde,
                            MAX(w.numero_linea) AS linea_hasa,
                            CAST(SUM(monto_grabado) AS NUMERIC(12,2)) AS monto_grabado,
                            CAST(SUM(monto_grabadoI1) AS NUMERIC(12,2)) AS monto_grabadoI1,
                            CAST(SUM(monto_grabadoI2) AS NUMERIC(12,2)) AS monto_grabadoI2,
                            CAST(SUM(monto_grabadoI3) AS NUMERIC(12,2)) AS monto_grabadoI3,
                            CAST(SUM(monto_exento) AS NUMERIC(12,2)) AS monto_exento,
                            CAST(SUM(total_itbis) AS NUMERIC(12,2)) AS total_itbis,
                            CAST(SUM(total_itbis1) AS NUMERIC(12,2)) AS total_itbis1,
                            CAST(SUM(total_itbis2) AS NUMERIC(12,2)) AS total_itbis2,
                            CAST(SUM(total_itbis3) AS NUMERIC(12,2)) AS total_itbis3,
                            CAST(SUM(monto_total) AS NUMERIC(12,2)) AS monto_total
                       FROM (SELECT (d.linenum+1) AS numero_linea,
                                    CEILING(ROW_NUMBER() OVER(ORDER BY d.linenum) / (SELECT CAST(config_value AS NUMERIC(5,1)) FROM cms_mmf.dbo.ecf_global_config WHERE config_name = 'ITEMSPERPAGE')) AS group_id,
                                    SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.LineTotal ELSE 0 END) AS monto_grabado,
                                    SUM(CASE WHEN t.taxecf_id IN (1) THEN d.LineTotal ELSE 0 END) AS monto_grabadoI1,
                                    SUM(CASE WHEN t.taxecf_id IN (2) THEN d.LineTotal ELSE 0 END) AS monto_grabadoI2,
                                    SUM(CASE WHEN t.taxecf_id IN (3) THEN d.LineTotal ELSE 0 END) AS monto_grabadoI3,
                                    SUM(CASE WHEN t.taxecf_id IN (4) THEN d.LineTotal ELSE 0 END) AS monto_exento,
                                    MAX(CASE WHEN t.taxecf_id IN (1) THEN d.VatPrcnt ELSE 0 END) AS itbis1,
                                    MAX(CASE WHEN t.taxecf_id IN (2) THEN d.VatPrcnt ELSE 0 END) AS itbis2,
                                    MAX(CASE WHEN t.taxecf_id IN (3) THEN d.VatPrcnt ELSE 0 END) AS itbis3,
                                    SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.VatSum ELSE 0 END) AS total_itbis,
                                    SUM(CASE WHEN t.taxecf_id IN (1) THEN d.VatSum ELSE 0 END) AS total_itbis1,
                                    SUM(CASE WHEN t.taxecf_id IN (2) THEN d.VatSum ELSE 0 END) AS total_itbis2,
                                    SUM(CASE WHEN t.taxecf_id IN (3) THEN d.VatSum ELSE 0 END) AS total_itbis3,
                                    SUM(d.LineTotal+d.VatSum) AS monto_total
                               FROM cms.dbo.POSOINV f
                         INNER JOIN cms.dbo.POSINV1 d ON (d.C_DocEntry = f.C_DocEntry)
                         INNER JOIN cms.dbo.ecf_tax_config t ON (t.taxsap_name = d.TaxCode)
                              WHERE f.C_DocEntry = @InvoiceID
                              GROUP BY d.linenum
                            ) w
                      GROUP BY w.group_id
                    ) a
        CROSS APPLY (VALUES (1,'PaginaNo'), (2,'NoLineaDesde'), (3,'NoLineaHasta'),(4,'SubtotalMontoGravadoPagina'), (5,'SubtotalMontoGravado1Pagina'),(6,'SubtotalMontoGravado2Pagina'),(7,'SubtotalMontoGravado3Pagina'),(8,'SubtotalExentoPagina'),(9,'SubtotalItbisPagina'),(10,'SubtotalItbis1Pagina'),(11,'SubtotalItbis2Pagina'),(12,'SubtotalItbis3Pagina'),(13,'MontoSubtotalPagina')) c (columnOrder,ColumnName)
              WHERE (SELECT CEILING(COUNT(d.LineNum) / (SELECT CAST(config_value AS NUMERIC(5,1)) FROM cms.dbo.ecf_global_config WHERE config_name = 'ITEMSPERPAGE')) AS pages FROM cms.dbo.POSINV1 d WHERE d.C_DocEntry = @InvoiceID) > 1
                AND @pPrintPages = 'Y'
UNION ALL
             SELECT 500000 + ROW_NUMBER() OVER (ORDER BY c.ColumnOrder) AS id_record,
                    6 AS group_id,
                    'InformacionReferencia' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'NCFModificado' THEN o.U_FACNUM 
                        WHEN 'FechaNCFModificado' THEN FORMAT(f.DocDate,'dd-MM-yyyy')
                        WHEN 'CodigoModificacion' THEN CAST(CASE WHEN (f.DocTotal-o.DocTotal) = 0 THEN 1 ELSE 3 END AS NVARCHAR(1)) --CAST(1 AS NVARCHAR(1))
                        WHEN 'RazonModificacion' THEN SUBSTRING('ANULAR '+f.U_FACNUM,1,90)
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM cms.dbo.POSOINV o
          LEFT JOIN cms.dbo.POSOINV f ON (f.U_FACNUM = o.U_FACNUM)
        CROSS APPLY (VALUES (1,'NCFModificado'), (2,'FechaNCFModificado'),(3,'CodigoModificacion'),(4,'RazonModificacion')) c (columnOrder,ColumnName)
              WHERE o.C_DocEntry = @InvoiceID
               
            ) ecf 
        WHERE @Resumen = 0 AND @eCFType IN ('50')
*/
UNION ALL

--NOTA DE DEBITO AGROFEM DESDE VENTAS
     SELECT ecf.*
       FROM ( 
  
             --Encabezado
             SELECT ROW_NUMBER() OVER (ORDER BY c.ColumnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'ECF' THEN 'ECF'
                        WHEN 'Version' THEN '1.0' 
                        WHEN 'TipoeCF' THEN i.ecf_type
                        WHEN 'eNCF' THEN UPPER(i.ecf)
                        WHEN 'FechaVencimientoSecuencia' THEN COALESCE(NULLIF(FORMAT(i.ecf_vence,'dd-MM-yyyy'),'01-01-1900'),'#e')
                        WHEN 'IndicadorNotaCredito' THEN '#e' --LOGICA PARA NOTA DE CREDITO
                        WHEN 'IndicadorMontoGravado' THEN CASE WHEN @eCFType IN (33) THEN '0' ELSE '#e' END -- (0) EL DEALLE DE LOS ITEM NO INCLUYE EL ITBIS
                        WHEN 'TipoIngresos' THEN CASE WHEN @eCFType IN (33) THEN '01' ELSE '#e' END -- (01) Ingresos por operaciones (No financieros).
                        WHEN 'TipoPago' THEN CAST(CASE WHEN F.ExtraDays <= 1 THEN 1 ELSE 2 END AS NVARCHAR(MAX)) -- 1:Contado | 2:Credio | 3:Gratuito
                        WHEN 'FechaLimitePago' THEN CASE WHEN F.ExtraDays > 1 THEN CASE WHEN @eCFType = 43 THEN '#e' ELSE FORMAT(f.DocDueDate,'dd-MM-yyyy') END ELSE '#e' END
                        WHEN 'TerminoPago' THEN CASE WHEN F.ExtraDays > 1 THEN CASE WHEN @eCFType IN ('33') THEN REPLACE(g.PymntGroup,'C - ','') ELSE '#e' END ELSE '#e' END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM cms.dbo.POSODIN f
         INNER JOIN cms.dbo.ecf_xml_invoice i ON (i.doc_id = f.C_DocEntry AND i.ecf_type IN (33)) 
          LEFT JOIN cms.dbo.POSOCTG g ON (g.ExtraDays = f.ExtraDays /*AND g.ListNum = 2*/)
        CROSS APPLY (VALUES (1,'ECF'), (2,'Version'), (3,'TipoeCF'),(4,'eNCF'), (5,'FechaVencimientoSecuencia'),(6,'IndicadorNotaCredito'),(7,'IndicadorMontoGravado'),(8,'TipoIngresos'),(9,'TipoPago'),(10,'FechaLimitePago'),(11,'TerminoPago')) c (columnOrder,ColumnName)
              WHERE f.DocEntry = @InvoiceID
                --AND f.DocSubType = 'DN' //NO NECESSARY ANYMORE
UNION ALL     --FORMAS DE PAGO
             SELECT 20 + ROW_NUMBER() OVER (ORDER BY s.id_diario_caja_det, c.ColumnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                         WHEN 'FormaPago' THEN CAST(t.ecf_payform_id AS NVARCHAR(MAX))
                         WHEN 'MontoPago' THEN FORMAT(s.monto_caja,'0.00')
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM cms.dbo.POSOINV f
         INNER JOIN cms.dbo.diario_caja_det s ON (s.id_documento = f.C_DocEntry AND s.docType = 'P')
          LEFT JOIN cms.dbo.ecf_payment_forms t ON (t.ecf_payform_sap = s.id_tipo_movimiento)
        CROSS APPLY (VALUES (1,'FormaPago'), (2,'MontoPago')) c (columnOrder,ColumnName)
              WHERE f.C_DocEntry = @InvoiceID
                AND @eCFType IN ('31','32')
UNION ALL    --TOTAL DE PAGINAS
             SELECT 40 + ROW_NUMBER() OVER (ORDER BY p.pages) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    'TotalPaginas' AS xmlTag,
                    FORMAT(p.pages,'0') AS xml_value
               FROM (SELECT CEILING(COUNT(d.LineNum) / (SELECT CAST(config_value AS NUMERIC(5,1)) FROM cms.dbo.ecf_global_config WHERE config_name = 'ITEMSPERPAGE')) AS pages
                       FROM cms.dbo.POSDIN1 d
                 INNER JOIN cms.dbo.POSODIN f ON (f.C_DocEntry = d.C_DocEntry /*AND f.DocSubType = 'DN'*/)
                      WHERE d.C_DocEntry = @InvoiceID
                      GROUP BY f.C_DocEntry
                    ) p
              WHERE p.pages > 1
UNION ALL    --EMISOR & COMPRADOR
             SELECT 50 + ROW_NUMBER() OVER (ORDER BY c.ColumnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'RNCEmisor' THEN d.RNC --PARA QUE SIEMPRE ENVIE EL RNC DE AGROFEM
                        WHEN 'RazonSocialEmisor' THEN razon_social
                        WHEN 'NombreComercial' THEN UPPER(CAST(d.descripcion AS NVARCHAR(100)))
                        WHEN 'DireccionEmisor' THEN d.direccion
                        WHEN 'TelefonoEmisor' THEN d.telefono1
                        WHEN 'CorreoEmisor' THEN d.email
                        WHEN 'WebSite' THEN d.web
                        WHEN 'NumeroFacturaInterna' THEN COALESCE(o.NumAtCard,'#e')
                        WHEN 'FechaEmision' THEN FORMAT(o.DocDate,'dd-MM-yyyy')
                        --WHEN 'RNCComprador' THEN CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN CASE WHEN @eCFType IN ('33') OR (@eCFType IN ('33') AND f.DocTotal >= 250000) THEN o.U_FacNit ELSE '#e' END ELSE CASE WHEN F.U_TNegocio = 3 THEN CASE WHEN @eCFType IN ('33') OR (@eCFType IN ('33') AND f.DocTotal >= 250000) THEN '00000000000' ELSE '#e' END ELSE '#e' END END
                        --WHEN 'RazonSocialComprador' THEN CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN CASE WHEN @eCFType IN ('31','33') OR (@eCFType IN ('32') AND f.DocTotal >= 250000) THEN f.CardName ELSE '#e' END ELSE CASE WHEN F.U_TNegocio = 3 THEN CASE WHEN @eCFType IN ('31') OR (@eCFType IN ('32') AND f.DocTotal >= 250000) THEN f.CardName ELSE '#e' END ELSE '#e' END END 
                        WHEN 'RNCComprador' THEN CASE WHEN LEN(f.U_FacNit) IN (9,11) AND PATINDEX('%[^0-9]%', f.U_FacNit) = 0 THEN o.U_FacNit ELSE CASE WHEN f.U_TNegocio = 3 THEN '00000000000' ELSE '#e' END END
                        WHEN 'RazonSocialComprador' THEN CASE WHEN LEN(f.U_FacNit) IN (9,11) AND PATINDEX('%[^0-9]%', f.U_FacNit) = 0 THEN o.CardName ELSE CASE WHEN f.U_TNegocio = 3 THEN o.CardName ELSE '#e' END END
                        WHEN 'DireccionComprador' THEN CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN CASE WHEN @eCFType IN ('31','33') OR (@eCFType IN ('32') AND f.DocTotal >= 250000) THEN SUBSTRING(COALESCE(NULLIF(f.Address2,''),'#e'),1,100) ELSE '#e' END ELSE '#e' END 
                        WHEN 'TelefonoAdicional' THEN '#e' --CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN CASE WHEN @eCFType IN ('31','33') OR (@eCFType IN ('32') AND f.DocTotal >= 250000) THEN COALESCE(l.Cellular,l.phone1,l.phone2,'#e') ELSE '#e' END ELSE '#e' END 
                        --WHEN 'CodigoInternoComprador' THEN CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN CASE WHEN @eCFType IN ('31','33') OR (@eCFType IN ('32') AND f.DocTotal >= 250000) THEN f.CardCode ELSE '#e' END ELSE '#e' END 
                        WHEN 'CodigoInternoComprador' THEN CASE WHEN LEN(f.U_FacNit) IN (9,11) AND PATINDEX('%[^0-9]%', f.U_FacNit) = 0 THEN o.CardCode ELSE CASE WHEN f.U_TNegocio = 3 THEN o.CardCode ELSE '#e' END END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM cms.dbo.POSODIN o
         INNER JOIN cms.dbo.POSOINV f ON (f.U_FACNUM = o.U_NCO)
	     INNER JOIN cms.dbo.serie s ON (s.id_serie = f.U_SCIASerie)
         INNER JOIN cms.dbo.division d ON (d.id_division = s.id_division) 
          LEFT JOIN cms.dbo.POSOCRD l ON (l.CardCode = f.CardCode)
         INNER JOIN cms.dbo.ecf_xml_invoice i ON (i.doc_id = o.DocEntry AND i.ecf_type IN (33))
        CROSS APPLY (VALUES (1,'RNCEmisor'), (2,'RazonSocialEmisor'),(3,'NombreComercial'),(4,'DireccionEmisor'),(5,'TelefonoEmisor'),(6,'CorreoEmisor'),(7,'WebSite'),(8,'NumeroFacturaInterna'),(9,'FechaEmision'),(10,'RNCComprador'),(11,'RazonSocialComprador'),(12,'DireccionComprador'),(13,'TelefonoAdicional'),(14,'CodigoInternoComprador')) c (columnOrder,ColumnName)
              WHERE o.C_DocEntry = @InvoiceID
                --AND o.DocSubType = 'DN'
UNION ALL    --TOTALES
             SELECT 100 + ROW_NUMBER() OVER (ORDER BY c.columnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'MontoGravadoTotal' THEN CASE WHEN a.monto_grabado > 0 THEN FORMAT(a.monto_grabado,'0.00') ELSE CASE WHEN @eCFType IN ('46') THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END END
                        WHEN 'MontoGravadoI1' THEN CASE WHEN a.monto_grabadoI1 > 0 THEN FORMAT(a.monto_grabadoI1,'0.00') ELSE '#e' END
                        WHEN 'MontoGravadoI2' THEN CASE WHEN a.monto_grabadoI2 > 0 THEN FORMAT(a.monto_grabadoI2,'0.00') ELSE '#e' END
                        WHEN 'MontoGravadoI3' THEN CASE WHEN a.monto_grabadoI3 > 0 THEN FORMAT(a.monto_grabadoI3,'0.00') ELSE CASE WHEN @eCFType IN ('46') THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END END
                        WHEN 'MontoExento' THEN CASE WHEN (a.monto_exento > 0 AND @eCFType IN ('31','32','33','34','41','43','44','45','47')) THEN FORMAT(a.monto_exento,'0.00') ELSE '#e' END
                        WHEN 'ITBIS1' THEN CASE WHEN a.itbis1 > 0 THEN FORMAT(a.itbis1,'00') ELSE '#e' END
                        WHEN 'ITBIS2' THEN CASE WHEN a.itbis2 > 0 THEN FORMAT(a.itbis2,'00') ELSE '#e' END
                        WHEN 'ITBIS3' THEN CASE WHEN a.itbis3 > 0 THEN FORMAT(a.itbis3,'0') ELSE CASE WHEN @eCFType IN ('46') THEN '0' ELSE '#e' END END
                        WHEN 'TotalITBIS' THEN CASE WHEN a.total_itbis > 0 THEN FORMAT(a.total_itbis,'0.00') ELSE CASE WHEN @eCFType IN ('46') THEN '0.00' ELSE '#e' END END
                        WHEN 'TotalITBIS1' THEN CASE WHEN a.total_itbis1 > 0 THEN FORMAT(a.total_itbis1,'0.00') ELSE '#e' END
                        WHEN 'TotalITBIS2' THEN CASE WHEN a.total_itbis2 > 0 THEN FORMAT(a.total_itbis2,'0.00') ELSE '#e' END
                        WHEN 'TotalITBIS3' THEN CASE WHEN a.total_itbis3 > 0 THEN FORMAT(a.total_itbis3,'0.00') ELSE CASE WHEN @eCFType IN ('46') THEN '0.00' ELSE '#e' END END
                        WHEN 'MontoTotal' THEN CASE WHEN a.monto_total > 0 THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END
                        WHEN 'TotalITBISRetenido' THEN CASE WHEN @eCFType IN ('41') THEN FORMAT(0,'0.00') ELSE '#e' END
                        WHEN 'TotalISRRetencion' THEN CASE WHEN @eCFType IN ('41','47') THEN FORMAT(0,'0.00') ELSE '#e' END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM (SELECT CAST(SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabado,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (1) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabadoI1,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (2) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabadoI2,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (3) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabadoI3,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (4) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_exento,
                            CAST(MAX(CASE WHEN t.taxecf_id IN (1) THEN d.VatPrcnt ELSE 0 END) AS NUMERIC(12,2)) AS itbis1,
                            CAST(MAX(CASE WHEN t.taxecf_id IN (2) THEN d.VatPrcnt ELSE 0 END) AS NUMERIC(12,2)) AS itbis2,
                            CAST(MAX(CASE WHEN t.taxecf_id IN (3) THEN d.VatPrcnt ELSE 0 END) AS NUMERIC(12,2)) AS itbis3,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (1) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis1,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (2) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis2,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (3) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis3,
                            CAST(SUM(d.LineTotal+d.VatSum) AS NUMERIC(12,2)) AS monto_total
                       FROM cms.dbo.POSODIN f
                 INNER JOIN cms.dbo.POSDIN1 d ON (d.C_DocEntry = f.C_DocEntry)
                 INNER JOIN cms.dbo.ecf_tax_config t ON (t.taxsap_name = d.TaxCode)
                      WHERE f.C_DocEntry = @InvoiceID
                        --AND f.DocSubType = 'DN'
                    ) a
        CROSS APPLY (VALUES (1,'MontoGravadoTotal'), (2,'MontoGravadoI1'), (3,'MontoGravadoI2'),(4,'MontoGravadoI3'), (5,'MontoExento'),(6,'ITBIS1'),(7,'ITBIS2'),(8,'ITBIS3'),(9,'TotalITBIS'),(10,'TotalITBIS1'),(11,'TotalITBIS2'),(12,'TotalITBIS3'),(13,'MontoTotal'),(14,'TotalITBISRetenido'),(15,'TotalISRRetencion')) c (columnOrder,ColumnName)
UNION ALL
         --2 - DetallesItems
         SELECT 1000 + ROW_NUMBER() OVER (ORDER BY d.LineNum, c.ColumnOrder) AS id_record,
                2 AS group_id,
                'DetallesItem' AS group_name,
                c.ColumnName AS xmlTag,
                CAST(CASE c.ColumnName
                    WHEN 'NumeroLinea' THEN CAST(DENSE_RANK() OVER(PARTITION BY d.C_DocEntry ORDER BY d.LineNum) AS NVARCHAR(MAX)) --CAST(d.LineNum+1 AS NVARCHAR(MAX))
                    WHEN 'IndicadorFacturacion' THEN CAST( CASE WHEN @eCFType IN ('46') THEN 3 ELSE t.taxecf_id END AS NVARCHAR(MAX))
                    WHEN 'IndicadorAgenteRetencionoPercepcion' THEN CAST(CASE WHEN @eCFType IN ('41','47') THEN '1' ELSE '#e' END AS NVARCHAR(MAX))
                    WHEN 'MontoITBISRetenido' THEN CASE WHEN @eCFType IN ('41') THEN '0.00' ELSE '#e' END
                    WHEN 'MontoISRRetenido' THEN CASE WHEN @eCFType IN ('41','47') THEN '0.00' ELSE '#e' END
                    WHEN 'NombreItem' THEN CAST(d.Dscription AS NVARCHAR(MAX))
                    WHEN 'IndicadorBienoServicio' THEN CAST(CASE WHEN @eCFType IN ('41','47','43') THEN 2 ELSE  CASE WHEN o.DocType = 'S' THEN 2 ELSE 1 END END AS NVARCHAR(MAX))
                    WHEN 'CantidadItem' THEN FORMAT(COALESCE(NULLIF(d.Quantity,0),1),'0.00') --PARA FORZAR LA CANTIDAD A 1 EN CASO DE NOTA DE CREDITO O DEBITO
                    WHEN 'UnidadMedida' THEN CAST(COALESCE(FORMAT(u.ecfunit_id,'0'),'#e') AS NVARCHAR(MAX))
                    WHEN 'PrecioUnitarioItem' THEN FORMAT(d.PriceBefDi,'0.00')
                    WHEN 'DescuentoMonto' THEN CASE WHEN d.DiscPrcnt > 0 THEN FORMAT((d.PriceBefDi*(d.DiscPrcnt/100))*d.Quantity,'0.00') ELSE '#e' END
                    WHEN 'TipoSubDescuento' THEN CASE WHEN d.DiscPrcnt > 0 THEN '%' ELSE '#e' END
                    WHEN 'SubDescuentoPorcentaje' THEN CASE WHEN d.DiscPrcnt > 0 THEN FORMAT(d.DiscPrcnt,'0.00') ELSE '#e' END
                    WHEN 'MontoSubDescuento' THEN CASE WHEN d.DiscPrcnt > 0 THEN FORMAT((d.PriceBefDi*(d.DiscPrcnt/100))*d.Quantity,'0.00') ELSE '#e' END
                    WHEN 'MontoItem' THEN FORMAT(d.LineTotal,'0.00')
                END AS NVARCHAR(MAX)) AS xml_value
           FROM cms.dbo.POSODIN o
     INNER JOIN cms.dbo.POSDIN1 d ON (d.C_DocEntry = o.C_DocEntry)
     INNER JOIN cms.dbo.ecf_tax_config t ON (t.taxsap_name = d.TaxCode)
      LEFT JOIN cms.dbo.ecf_units u ON (u.ecfsapunit_name = d.unitMsr)
    CROSS APPLY (VALUES (1,'NumeroLinea'), (2,'IndicadorFacturacion'), (3,'IndicadorAgenteRetencionoPercepcion'), (4,'MontoITBISRetenido'), (4,'MontoISRRetenido'), (5,'NombreItem'),(6,'IndicadorBienoServicio'), (7,'CantidadItem'),(8,'UnidadMedida'),(9,'PrecioUnitarioItem'),(10,'DescuentoMonto'),(11,'TipoSubDescuento'),(12,'SubDescuentoPorcentaje'),(13,'MontoSubDescuento'),(14,'MontoItem')) c (columnOrder,ColumnName)
          WHERE o.C_DocEntry = @InvoiceID
            --AND o.DocSubType = 'DN'
UNION ALL
             --PAGINACION
             SELECT 100000+ROW_NUMBER() OVER (ORDER BY a.pagina_numero, c.columnOrder) AS id_record,
                    5 AS group_id,
                    'Paginacion' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'PaginaNo' THEN CAST(pagina_numero AS NVARCHAR(MAX))
                        WHEN 'NoLineaDesde' THEN CAST(linea_desde AS NVARCHAR(MAX))
                        WHEN 'NoLineaHasta' THEN CAST(linea_hasa AS NVARCHAR(MAX))
                        WHEN 'SubtotalMontoGravadoPagina' THEN CASE WHEN a.monto_grabado > 0 THEN FORMAT(a.monto_grabado,'0.00') ELSE '#e' END
                        WHEN 'SubtotalMontoGravado1Pagina' THEN CASE WHEN a.monto_grabadoI1 > 0 THEN FORMAT(a.monto_grabadoI1,'0.00') ELSE '#e' END
                        WHEN 'SubtotalMontoGravado2Pagina' THEN CASE WHEN a.monto_grabadoI2 > 0 THEN FORMAT(a.monto_grabadoI2,'0.00') ELSE '#e' END
                        WHEN 'SubtotalMontoGravado3Pagina' THEN CASE WHEN a.monto_grabadoI3 > 0 THEN FORMAT(a.monto_grabadoI3,'0.00') ELSE '#e' END
                        WHEN 'SubtotalExentoPagina' THEN CASE WHEN a.monto_exento > 0 THEN FORMAT(a.monto_exento,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbisPagina' THEN CASE WHEN a.total_itbis > 0 THEN FORMAT(a.total_itbis,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbis1Pagina' THEN CASE WHEN a.total_itbis1 > 0 THEN FORMAT(a.total_itbis1,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbis2Pagina' THEN CASE WHEN a.total_itbis2 > 0 THEN FORMAT(a.total_itbis2,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbis3Pagina' THEN CASE WHEN a.total_itbis3 > 0 THEN FORMAT(a.total_itbis3,'0.00') ELSE '#e' END
                        WHEN 'MontoSubtotalPagina' THEN CASE WHEN a.monto_total > 0 THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM (SELECT group_id AS pagina_numero,
                            MIN(w.numero_linea) AS linea_desde,
                            MAX(w.numero_linea) AS linea_hasa,
                            CAST(SUM(monto_grabado) AS NUMERIC(12,2)) AS monto_grabado,
                            CAST(SUM(monto_grabadoI1) AS NUMERIC(12,2)) AS monto_grabadoI1,
                            CAST(SUM(monto_grabadoI2) AS NUMERIC(12,2)) AS monto_grabadoI2,
                            CAST(SUM(monto_grabadoI3) AS NUMERIC(12,2)) AS monto_grabadoI3,
                            CAST(SUM(monto_exento) AS NUMERIC(12,2)) AS monto_exento,
                            CAST(SUM(total_itbis) AS NUMERIC(12,2)) AS total_itbis,
                            CAST(SUM(total_itbis1) AS NUMERIC(12,2)) AS total_itbis1,
                            CAST(SUM(total_itbis2) AS NUMERIC(12,2)) AS total_itbis2,
                            CAST(SUM(total_itbis3) AS NUMERIC(12,2)) AS total_itbis3,
                            CAST(SUM(monto_total) AS NUMERIC(12,2)) AS monto_total
                       FROM (SELECT (d.linenum+1) AS numero_linea,
                                    CEILING(ROW_NUMBER() OVER(ORDER BY d.linenum) / (SELECT CAST(config_value AS NUMERIC(5,1)) FROM cms.dbo.ecf_global_config WHERE config_name = 'ITEMSPERPAGE')) AS group_id,
                                    SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.LineTotal ELSE 0 END) AS monto_grabado,
                                    SUM(CASE WHEN t.taxecf_id IN (1) THEN d.LineTotal ELSE 0 END) AS monto_grabadoI1,
                                    SUM(CASE WHEN t.taxecf_id IN (2) THEN d.LineTotal ELSE 0 END) AS monto_grabadoI2,
                                    SUM(CASE WHEN t.taxecf_id IN (3) THEN d.LineTotal ELSE 0 END) AS monto_grabadoI3,
                                    SUM(CASE WHEN t.taxecf_id IN (4) THEN d.LineTotal ELSE 0 END) AS monto_exento,
                                    MAX(CASE WHEN t.taxecf_id IN (1) THEN d.VatPrcnt ELSE 0 END) AS itbis1,
                                    MAX(CASE WHEN t.taxecf_id IN (2) THEN d.VatPrcnt ELSE 0 END) AS itbis2,
                                    MAX(CASE WHEN t.taxecf_id IN (3) THEN d.VatPrcnt ELSE 0 END) AS itbis3,
                                    SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.VatSum ELSE 0 END) AS total_itbis,
                                    SUM(CASE WHEN t.taxecf_id IN (1) THEN d.VatSum ELSE 0 END) AS total_itbis1,
                                    SUM(CASE WHEN t.taxecf_id IN (2) THEN d.VatSum ELSE 0 END) AS total_itbis2,
                                    SUM(CASE WHEN t.taxecf_id IN (3) THEN d.VatSum ELSE 0 END) AS total_itbis3,
                                    SUM(d.LineTotal+d.VatSum) AS monto_total
                               FROM cms.dbo.POSODIN f
                         INNER JOIN cms.dbo.POSDIN1 d ON (d.C_DocEntry = f.C_DocEntry)
                         INNER JOIN cms.dbo.ecf_tax_config t ON (t.taxsap_name = d.TaxCode)
                              WHERE f.C_DocEntry = @InvoiceID
                                --AND f.DocSubType = 'DN'
                              GROUP BY d.linenum
                            ) w
                      GROUP BY w.group_id
                    ) a
        CROSS APPLY (VALUES (1,'PaginaNo'), (2,'NoLineaDesde'), (3,'NoLineaHasta'),(4,'SubtotalMontoGravadoPagina'), (5,'SubtotalMontoGravado1Pagina'),(6,'SubtotalMontoGravado2Pagina'),(7,'SubtotalMontoGravado3Pagina'),(8,'SubtotalExentoPagina'),(9,'SubtotalItbisPagina'),(10,'SubtotalItbis1Pagina'),(11,'SubtotalItbis2Pagina'),(12,'SubtotalItbis3Pagina'),(13,'MontoSubtotalPagina')) c (columnOrder,ColumnName)
              WHERE (SELECT CEILING(COUNT(d.LineNum) / (SELECT CAST(config_value AS NUMERIC(5,1)) FROM cms.dbo.ecf_global_config WHERE config_name = 'ITEMSPERPAGE')) AS pages FROM  cms.dbo.POSODIN o INNER JOIN cms.dbo.POSDIN1 d ON (d.C_DocEntry = o.C_DocEntry) WHERE o.C_DocEntry = @InvoiceID /*AND o.DocSubType = 'DN'*/) > 1
                AND @pPrintPages = 'Y'
UNION ALL
             SELECT 500000 + ROW_NUMBER() OVER (ORDER BY c.ColumnOrder) AS id_record,
                    6 AS group_id,
                    'InformacionReferencia' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'NCFModificado' THEN UPPER(o.U_NCO)
                        WHEN 'FechaNCFModificado' THEN FORMAT(f.DocDate,'dd-MM-yyyy')
                        --WHEN 'CodigoModificacion' THEN CAST(1 AS NVARCHAR(1))
                        --SI EL MONTO DE LA NC ES IGUAL AL THE DE FACTURA ENTONCES ES PARA ANULAR FACTURA (1) DE LO CONTRARIO ES PARA MODIFICAR (3)
                        WHEN 'CodigoModificacion' THEN CAST(CASE WHEN (f.DocTotal-o.DocTotal) = 0 THEN 3 ELSE 3 END AS NVARCHAR(1))
                        WHEN 'RazonModificacion' THEN o.Comments
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM cms.dbo.POSODIN o
         INNER JOIN cms.dbo.POSOINV f ON (f.U_FACNUM = o.U_NCO)
        CROSS APPLY (VALUES (1,'NCFModificado'), (2,'FechaNCFModificado'),(3,'CodigoModificacion'),(4,'RazonModificacion')) c (columnOrder,ColumnName)
              WHERE o.C_DocEntry = @InvoiceID
                --AND O.DocSubType = 'DN'

            ) ecf 
        WHERE @Resumen = 0 AND @eCFType IN ('33')
          --PARA CUANDO LA NOTA DE DEBITO SE LE HACE A UNA FACTURA
          AND (SELECT COUNT(i.C_DocEntry) FROM cms.dbo.POSOINV i INNER JOIN (SELECT n.U_NCO FROM cms.dbo.POSODIN n WHERE n.C_DocEntry = @InvoiceID /*AND n.DocSubType = 'DN'*/)  a ON (a.U_NCO = i.U_FACNUM)) > 0 
          --AND (SELECT COUNT(n.DocEntry) FROM SBO_AGROFEM.dbo.OINV n INNER JOIN SBO_AGROFEM.dbo.OINV i ON (i.U_FACNUM = n.U_NCO) WHERE n.DocEntry = @InvoiceID) > 0 --

		  /*
UNION ALL

--NOTA DE DEBITO AGROFEM DESDE NOTAS DE CREDITO
     SELECT ecf.*
       FROM ( 
  
             --Encabezado
             SELECT ROW_NUMBER() OVER (ORDER BY c.ColumnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'ECF' THEN 'ECF'
                        WHEN 'Version' THEN '1.0' 
                        WHEN 'TipoeCF' THEN i.ecf_type
                        WHEN 'eNCF' THEN UPPER(i.ecf)
                        WHEN 'FechaVencimientoSecuencia' THEN COALESCE(NULLIF(FORMAT(i.ecf_vence,'dd-MM-yyyy'),'01-01-1900'),'#e')
                        WHEN 'IndicadorNotaCredito' THEN '#e' --LOGICA PARA NOTA DE CREDITO
                        WHEN 'IndicadorMontoGravado' THEN CASE WHEN @eCFType IN (33) THEN '0' ELSE '#e' END -- (0) EL DEALLE DE LOS ITEM NO INCLUYE EL ITBIS
                        WHEN 'TipoIngresos' THEN CASE WHEN @eCFType IN (33) THEN '01' ELSE '#e' END -- (01) Ingresos por operaciones (No financieros).
                        WHEN 'TipoPago' THEN CAST(CASE WHEN F.ExtraDays <= 1 THEN 1 ELSE 2 END AS NVARCHAR(MAX)) -- 1:Contado | 2:Credio | 3:Gratuito
                        WHEN 'FechaLimitePago' THEN CASE WHEN F.ExtraDays > 1 THEN CASE WHEN @eCFType = 43 THEN '#e' ELSE FORMAT(f.DocDueDate,'dd-MM-yyyy') END ELSE '#e' END
                        WHEN 'TerminoPago' THEN CASE WHEN F.ExtraDays > 1 THEN CASE WHEN @eCFType IN ('33') THEN REPLACE(g.PymntGroup,'C - ','') ELSE '#e' END ELSE '#e' END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM SBO_AGROFEM.dbo.OINV f
         INNER JOIN cms.dbo.ecf_xml_invoice i ON (i.doc_id = f.DocEntry AND i.ecf_type IN (33)) 
          LEFT JOIN SBO_AGROFEM.dbo.OCTG g ON (g.ExtraDays = f.ExtraDays /*AND g.ListNum = 2*/)
        CROSS APPLY (VALUES (1,'ECF'), (2,'Version'), (3,'TipoeCF'),(4,'eNCF'), (5,'FechaVencimientoSecuencia'),(6,'IndicadorNotaCredito'),(7,'IndicadorMontoGravado'),(8,'TipoIngresos'),(9,'TipoPago'),(10,'FechaLimitePago'),(11,'TerminoPago')) c (columnOrder,ColumnName)
              WHERE f.DocEntry = @InvoiceID
                AND f.DocSubType = 'DN'
UNION ALL     --FORMAS DE PAGO
             SELECT 20 + ROW_NUMBER() OVER (ORDER BY s.id_diario_caja_det, c.ColumnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                         WHEN 'FormaPago' THEN CAST(t.ecf_payform_id AS NVARCHAR(MAX))
                         WHEN 'MontoPago' THEN FORMAT(s.monto_caja,'0.00')
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM cms.dbo.POSOINV f
         INNER JOIN cms.dbo.diario_caja_det s ON (s.id_documento = f.C_DocEntry AND s.docType = 'P')
          LEFT JOIN cms.dbo.ecf_payment_forms t ON (t.ecf_payform_sap = s.id_tipo_movimiento)
        CROSS APPLY (VALUES (1,'FormaPago'), (2,'MontoPago')) c (columnOrder,ColumnName)
              WHERE f.C_DocEntry = @InvoiceID
                AND @eCFType IN ('31','32')
UNION ALL    --TOTAL DE PAGINAS
             SELECT 40 + ROW_NUMBER() OVER (ORDER BY p.pages) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    'TotalPaginas' AS xmlTag,
                    FORMAT(p.pages,'0') AS xml_value
               FROM (SELECT CEILING(COUNT(d.LineNum) / (SELECT CAST(config_value AS NUMERIC(5,1)) FROM cms.dbo.ecf_global_config WHERE config_name = 'ITEMSPERPAGE')) AS pages
                       FROM SBO_AGROFEM.dbo.INV1 d
                 INNER JOIN SBO_AGROFEM.dbo.OINV f ON (f.DocEntry = d.DocEntry AND f.DocSubType = 'DN')
                      WHERE d.DocEntry = @InvoiceID
                      GROUP BY f.DocEntry
                    ) p
              WHERE p.pages > 1
UNION ALL    --EMISOR & COMPRADOR
             SELECT 50 + ROW_NUMBER() OVER (ORDER BY c.ColumnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'RNCEmisor' THEN d.RNC --PARA QUE SIEMPRE ENVIE EL RNC DE AGROFEM
                        WHEN 'RazonSocialEmisor' THEN razon_social
                        WHEN 'NombreComercial' THEN UPPER(CAST(d.descripcion AS NVARCHAR(100)))
                        WHEN 'DireccionEmisor' THEN d.direccion
                        WHEN 'TelefonoEmisor' THEN d.telefono1
                        WHEN 'CorreoEmisor' THEN d.email
                        WHEN 'WebSite' THEN d.web
                        WHEN 'NumeroFacturaInterna' THEN COALESCE(o.NumAtCard,'#e')
                        WHEN 'FechaEmision' THEN FORMAT(o.DocDate,'dd-MM-yyyy')
                        --WHEN 'RNCComprador' THEN CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN CASE WHEN @eCFType IN ('31','33') OR (@eCFType IN ('32') AND f.DocTotal >= 250000) THEN f.U_FacNit ELSE '#e' END ELSE CASE WHEN F.U_TNegocio = 3 THEN CASE WHEN @eCFType IN ('31') OR (@eCFType IN ('32') AND f.DocTotal >= 250000) THEN '00000000000' ELSE '#e' END ELSE '#e' END END
                        --WHEN 'RazonSocialComprador' THEN CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN CASE WHEN @eCFType IN ('31','33') OR (@eCFType IN ('32') AND f.DocTotal >= 250000) THEN f.CardName ELSE '#e' END ELSE CASE WHEN F.U_TNegocio = 3 THEN CASE WHEN @eCFType IN ('31') OR (@eCFType IN ('32') AND f.DocTotal >= 250000) THEN f.CardName ELSE '#e' END ELSE '#e' END END 
                        WHEN 'RNCComprador' THEN CASE WHEN LEN(f.U_FacNit) IN (9,11) AND PATINDEX('%[^0-9]%', f.U_FacNit) = 0 THEN o.U_FacNit ELSE CASE WHEN f.U_TNegocio = 3 THEN '00000000000' ELSE '#e' END END
                        WHEN 'RazonSocialComprador' THEN CASE WHEN LEN(f.U_FacNit) IN (9,11) AND PATINDEX('%[^0-9]%', f.U_FacNit) = 0 THEN o.CardName ELSE CASE WHEN f.U_TNegocio = 3 THEN o.CardName ELSE '#e' END END
                        WHEN 'DireccionComprador' THEN '#e' --CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN CASE WHEN @eCFType IN ('31','33') OR (@eCFType IN ('32') AND f.DocTotal >= 250000) THEN SUBSTRING(COALESCE(NULLIF(f.Address2,''),'#e'),1,100) ELSE '#e' END ELSE '#e' END 
                        WHEN 'TelefonoAdicional' THEN '#e' --CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN CASE WHEN @eCFType IN ('31','33') OR (@eCFType IN ('32') AND f.DocTotal >= 250000) THEN COALESCE(l.Cellular,l.phone1,l.phone2,'#e') ELSE '#e' END ELSE '#e' END 
                        --WHEN 'CodigoInternoComprador' THEN CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN CASE WHEN @eCFType IN ('31','33') OR (@eCFType IN ('32') AND f.DocTotal >= 250000) THEN f.CardCode ELSE '#e' END ELSE '#e' END 
                        WHEN 'CodigoInternoComprador' THEN CASE WHEN LEN(f.U_FacNit) IN (9,11) AND PATINDEX('%[^0-9]%', f.U_FacNit) = 0 THEN o.CardCode ELSE CASE WHEN f.U_TNegocio = 3 THEN o.CardCode ELSE '#e' END END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM SBO_AGROFEM.dbo.OINV o
         INNER JOIN SBO_AGROFEM.dbo.ORIN f ON (f.U_FACNUM = o.U_NCO)
	     --INNER JOIN cms.dbo.serie s ON (s.id_serie = f.U_SCIASerie)
         INNER JOIN cms.dbo.division d ON (d.id_division = 1) 
          LEFT JOIN SBO_AGROFEM.dbo.OCRD l ON (l.CardCode = f.CardCode)
         INNER JOIN cms.dbo.ecf_xml_invoice i ON (i.doc_id = o.DocEntry)
        CROSS APPLY (VALUES (1,'RNCEmisor'), (2,'RazonSocialEmisor'),(3,'NombreComercial'),(4,'DireccionEmisor'),(5,'TelefonoEmisor'),(6,'CorreoEmisor'),(7,'WebSite'),(8,'NumeroFacturaInterna'),(9,'FechaEmision'),(10,'RNCComprador'),(11,'RazonSocialComprador'),(12,'DireccionComprador'),(13,'TelefonoAdicional'),(14,'CodigoInternoComprador')) c (columnOrder,ColumnName)
              WHERE o.DocEntry = @InvoiceID
                AND o.DocSubType = 'DN'
UNION ALL    --TOTALES
             SELECT 100 + ROW_NUMBER() OVER (ORDER BY c.columnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'MontoGravadoTotal' THEN CASE WHEN a.monto_grabado > 0 THEN FORMAT(a.monto_grabado,'0.00') ELSE CASE WHEN @eCFType IN ('46') THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END END
                        WHEN 'MontoGravadoI1' THEN CASE WHEN a.monto_grabadoI1 > 0 THEN FORMAT(a.monto_grabadoI1,'0.00') ELSE '#e' END
                        WHEN 'MontoGravadoI2' THEN CASE WHEN a.monto_grabadoI2 > 0 THEN FORMAT(a.monto_grabadoI2,'0.00') ELSE '#e' END
                        WHEN 'MontoGravadoI3' THEN CASE WHEN a.monto_grabadoI3 > 0 THEN FORMAT(a.monto_grabadoI3,'0.00') ELSE CASE WHEN @eCFType IN ('46') THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END END
                        WHEN 'MontoExento' THEN CASE WHEN (a.monto_exento > 0 AND @eCFType IN ('31','32','33','34','41','43','44','45','47')) THEN FORMAT(a.monto_exento,'0.00') ELSE '#e' END
                        WHEN 'ITBIS1' THEN CASE WHEN a.itbis1 > 0 THEN FORMAT(a.itbis1,'00') ELSE '#e' END
                        WHEN 'ITBIS2' THEN CASE WHEN a.itbis2 > 0 THEN FORMAT(a.itbis2,'00') ELSE '#e' END
                        WHEN 'ITBIS3' THEN CASE WHEN a.itbis3 > 0 THEN FORMAT(a.itbis3,'0') ELSE CASE WHEN @eCFType IN ('46') THEN '0' ELSE '#e' END END
                        WHEN 'TotalITBIS' THEN CASE WHEN a.total_itbis > 0 THEN FORMAT(a.total_itbis,'0.00') ELSE CASE WHEN @eCFType IN ('46') THEN '0.00' ELSE '#e' END END
                        WHEN 'TotalITBIS1' THEN CASE WHEN a.total_itbis1 > 0 THEN FORMAT(a.total_itbis1,'0.00') ELSE '#e' END
                        WHEN 'TotalITBIS2' THEN CASE WHEN a.total_itbis2 > 0 THEN FORMAT(a.total_itbis2,'0.00') ELSE '#e' END
                        WHEN 'TotalITBIS3' THEN CASE WHEN a.total_itbis3 > 0 THEN FORMAT(a.total_itbis3,'0.00') ELSE CASE WHEN @eCFType IN ('46') THEN '0.00' ELSE '#e' END END
                        WHEN 'MontoTotal' THEN CASE WHEN a.monto_total > 0 THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END
                        WHEN 'TotalITBISRetenido' THEN CASE WHEN @eCFType IN ('41') THEN FORMAT(0,'0.00') ELSE '#e' END
                        WHEN 'TotalISRRetencion' THEN CASE WHEN @eCFType IN ('41','47') THEN FORMAT(0,'0.00') ELSE '#e' END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM (SELECT CAST(SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabado,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (1) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabadoI1,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (2) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabadoI2,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (3) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabadoI3,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (4) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_exento,
                            CAST(MAX(CASE WHEN t.taxecf_id IN (1) THEN d.VatPrcnt ELSE 0 END) AS NUMERIC(12,2)) AS itbis1,
                            CAST(MAX(CASE WHEN t.taxecf_id IN (2) THEN d.VatPrcnt ELSE 0 END) AS NUMERIC(12,2)) AS itbis2,
                            CAST(MAX(CASE WHEN t.taxecf_id IN (3) THEN d.VatPrcnt ELSE 0 END) AS NUMERIC(12,2)) AS itbis3,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (1) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis1,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (2) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis2,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (3) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis3,
                            CAST(SUM(d.LineTotal+d.VatSum) AS NUMERIC(12,2)) AS monto_total
                       FROM SBO_AGROFEM.dbo.OINV f
                 INNER JOIN SBO_AGROFEM.dbo.INV1 d ON (d.DocEntry = f.DocEntry)
                 INNER JOIN cms.dbo.ecf_tax_config t ON (t.taxsap_name = d.TaxCode)
                      WHERE f.DocEntry = @InvoiceID
                        AND f.DocSubType = 'DN'
                    ) a
        CROSS APPLY (VALUES (1,'MontoGravadoTotal'), (2,'MontoGravadoI1'), (3,'MontoGravadoI2'),(4,'MontoGravadoI3'), (5,'MontoExento'),(6,'ITBIS1'),(7,'ITBIS2'),(8,'ITBIS3'),(9,'TotalITBIS'),(10,'TotalITBIS1'),(11,'TotalITBIS2'),(12,'TotalITBIS3'),(13,'MontoTotal'),(14,'TotalITBISRetenido'),(15,'TotalISRRetencion')) c (columnOrder,ColumnName)
UNION ALL
         --2 - DetallesItems
         SELECT 1000 + ROW_NUMBER() OVER (ORDER BY d.LineNum, c.ColumnOrder) AS id_record,
                2 AS group_id,
                'DetallesItem' AS group_name,
                c.ColumnName AS xmlTag,
                CAST(CASE c.ColumnName
                    WHEN 'NumeroLinea' THEN CAST(DENSE_RANK() OVER(PARTITION BY d.DocEntry ORDER BY d.LineNum) AS NVARCHAR(MAX)) --CAST(d.LineNum+1 AS NVARCHAR(MAX))
                    WHEN 'IndicadorFacturacion' THEN CAST( CASE WHEN @eCFType IN ('46') THEN 3 ELSE t.taxecf_id END AS NVARCHAR(MAX))
                    WHEN 'IndicadorAgenteRetencionoPercepcion' THEN CAST(CASE WHEN @eCFType IN ('41','47') THEN '1' ELSE '#e' END AS NVARCHAR(MAX))
                    WHEN 'MontoITBISRetenido' THEN CASE WHEN @eCFType IN ('41') THEN '0.00' ELSE '#e' END
                    WHEN 'MontoISRRetenido' THEN CASE WHEN @eCFType IN ('41','47') THEN '0.00' ELSE '#e' END
                    WHEN 'NombreItem' THEN CAST(d.Dscription AS NVARCHAR(MAX))
                    WHEN 'IndicadorBienoServicio' THEN CAST(CASE WHEN @eCFType IN ('41','47','43') THEN 2 ELSE  CASE WHEN o.DocType = 'S' THEN 2 ELSE 1 END END AS NVARCHAR(MAX))
                    WHEN 'CantidadItem' THEN FORMAT(COALESCE(NULLIF(d.Quantity,0),1),'0.00') --PARA FORZAR LA CANTIDAD A 1 EN CASO DE NOTA DE CREDITO O DEBITO
                    WHEN 'UnidadMedida' THEN CAST(COALESCE(FORMAT(u.ecfunit_id,'0'),'#e') AS NVARCHAR(MAX))
                    WHEN 'PrecioUnitarioItem' THEN FORMAT(d.PriceBefDi,'0.00')
                    WHEN 'DescuentoMonto' THEN CASE WHEN d.DiscPrcnt > 0 THEN FORMAT((d.PriceBefDi*(d.DiscPrcnt/100))*d.Quantity,'0.00') ELSE '#e' END
                    WHEN 'TipoSubDescuento' THEN CASE WHEN d.DiscPrcnt > 0 THEN '%' ELSE '#e' END
                    WHEN 'SubDescuentoPorcentaje' THEN CASE WHEN d.DiscPrcnt > 0 THEN FORMAT(d.DiscPrcnt,'0.00') ELSE '#e' END
                    WHEN 'MontoSubDescuento' THEN CASE WHEN d.DiscPrcnt > 0 THEN FORMAT((d.PriceBefDi*(d.DiscPrcnt/100))*d.Quantity,'0.00') ELSE '#e' END
                    WHEN 'MontoItem' THEN FORMAT(d.LineTotal,'0.00')
                END AS NVARCHAR(MAX)) AS xml_value
           FROM SBO_AGROFEM.dbo.OINV o
     INNER JOIN SBO_AGROFEM.dbo.INV1 d ON (d.DocEntry = o.DocEntry)
     INNER JOIN cms.dbo.ecf_tax_config t ON (t.taxsap_name = d.TaxCode)
      LEFT JOIN cms.dbo.ecf_units u ON (u.ecfsapunit_name = d.unitMsr)
    CROSS APPLY (VALUES (1,'NumeroLinea'), (2,'IndicadorFacturacion'), (3,'IndicadorAgenteRetencionoPercepcion'), (4,'MontoITBISRetenido'), (4,'MontoISRRetenido'), (5,'NombreItem'),(6,'IndicadorBienoServicio'), (7,'CantidadItem'),(8,'UnidadMedida'),(9,'PrecioUnitarioItem'),(10,'DescuentoMonto'),(11,'TipoSubDescuento'),(12,'SubDescuentoPorcentaje'),(13,'MontoSubDescuento'),(14,'MontoItem')) c (columnOrder,ColumnName)
          WHERE o.DocEntry = @InvoiceID
            AND o.DocSubType = 'DN'
UNION ALL
             --PAGINACION
             SELECT 100000+ROW_NUMBER() OVER (ORDER BY a.pagina_numero, c.columnOrder) AS id_record,
                    5 AS group_id,
                    'Paginacion' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'PaginaNo' THEN CAST(pagina_numero AS NVARCHAR(MAX))
                        WHEN 'NoLineaDesde' THEN CAST(linea_desde AS NVARCHAR(MAX))
                        WHEN 'NoLineaHasta' THEN CAST(linea_hasa AS NVARCHAR(MAX))
                        WHEN 'SubtotalMontoGravadoPagina' THEN CASE WHEN a.monto_grabado > 0 THEN FORMAT(a.monto_grabado,'0.00') ELSE '#e' END
                        WHEN 'SubtotalMontoGravado1Pagina' THEN CASE WHEN a.monto_grabadoI1 > 0 THEN FORMAT(a.monto_grabadoI1,'0.00') ELSE '#e' END
                        WHEN 'SubtotalMontoGravado2Pagina' THEN CASE WHEN a.monto_grabadoI2 > 0 THEN FORMAT(a.monto_grabadoI2,'0.00') ELSE '#e' END
                        WHEN 'SubtotalMontoGravado3Pagina' THEN CASE WHEN a.monto_grabadoI3 > 0 THEN FORMAT(a.monto_grabadoI3,'0.00') ELSE '#e' END
                        WHEN 'SubtotalExentoPagina' THEN CASE WHEN a.monto_exento > 0 THEN FORMAT(a.monto_exento,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbisPagina' THEN CASE WHEN a.total_itbis > 0 THEN FORMAT(a.total_itbis,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbis1Pagina' THEN CASE WHEN a.total_itbis1 > 0 THEN FORMAT(a.total_itbis1,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbis2Pagina' THEN CASE WHEN a.total_itbis2 > 0 THEN FORMAT(a.total_itbis2,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbis3Pagina' THEN CASE WHEN a.total_itbis3 > 0 THEN FORMAT(a.total_itbis3,'0.00') ELSE '#e' END
                        WHEN 'MontoSubtotalPagina' THEN CASE WHEN a.monto_total > 0 THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM (SELECT group_id AS pagina_numero,
                            MIN(w.numero_linea) AS linea_desde,
                            MAX(w.numero_linea) AS linea_hasa,
                            CAST(SUM(monto_grabado) AS NUMERIC(12,2)) AS monto_grabado,
                            CAST(SUM(monto_grabadoI1) AS NUMERIC(12,2)) AS monto_grabadoI1,
                            CAST(SUM(monto_grabadoI2) AS NUMERIC(12,2)) AS monto_grabadoI2,
                            CAST(SUM(monto_grabadoI3) AS NUMERIC(12,2)) AS monto_grabadoI3,
                            CAST(SUM(monto_exento) AS NUMERIC(12,2)) AS monto_exento,
                            CAST(SUM(total_itbis) AS NUMERIC(12,2)) AS total_itbis,
                            CAST(SUM(total_itbis1) AS NUMERIC(12,2)) AS total_itbis1,
                            CAST(SUM(total_itbis2) AS NUMERIC(12,2)) AS total_itbis2,
                            CAST(SUM(total_itbis3) AS NUMERIC(12,2)) AS total_itbis3,
                            CAST(SUM(monto_total) AS NUMERIC(12,2)) AS monto_total
                       FROM (SELECT (d.linenum+1) AS numero_linea,
                                    CEILING(ROW_NUMBER() OVER(ORDER BY d.linenum) / (SELECT CAST(config_value AS NUMERIC(5,1)) FROM cms.dbo.ecf_global_config WHERE config_name = 'ITEMSPERPAGE')) AS group_id,
                                    SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.LineTotal ELSE 0 END) AS monto_grabado,
                                    SUM(CASE WHEN t.taxecf_id IN (1) THEN d.LineTotal ELSE 0 END) AS monto_grabadoI1,
                                    SUM(CASE WHEN t.taxecf_id IN (2) THEN d.LineTotal ELSE 0 END) AS monto_grabadoI2,
                                    SUM(CASE WHEN t.taxecf_id IN (3) THEN d.LineTotal ELSE 0 END) AS monto_grabadoI3,
                                    SUM(CASE WHEN t.taxecf_id IN (4) THEN d.LineTotal ELSE 0 END) AS monto_exento,
                                    MAX(CASE WHEN t.taxecf_id IN (1) THEN d.VatPrcnt ELSE 0 END) AS itbis1,
                                    MAX(CASE WHEN t.taxecf_id IN (2) THEN d.VatPrcnt ELSE 0 END) AS itbis2,
                                    MAX(CASE WHEN t.taxecf_id IN (3) THEN d.VatPrcnt ELSE 0 END) AS itbis3,
                                    SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.VatSum ELSE 0 END) AS total_itbis,
                                    SUM(CASE WHEN t.taxecf_id IN (1) THEN d.VatSum ELSE 0 END) AS total_itbis1,
                                    SUM(CASE WHEN t.taxecf_id IN (2) THEN d.VatSum ELSE 0 END) AS total_itbis2,
                                    SUM(CASE WHEN t.taxecf_id IN (3) THEN d.VatSum ELSE 0 END) AS total_itbis3,
                                    SUM(d.LineTotal+d.VatSum) AS monto_total
                               FROM SBO_AGROFEM.dbo.OINV f
                         INNER JOIN SBO_AGROFEM.dbo.INV1 d ON (d.DocEntry = f.DocEntry)
                         INNER JOIN cms.dbo.ecf_tax_config t ON (t.taxsap_name = d.TaxCode)
                              WHERE f.DocEntry = @InvoiceID
                                AND f.DocSubType = 'DN'
                              GROUP BY d.linenum
                            ) w
                      GROUP BY w.group_id
                    ) a
        CROSS APPLY (VALUES (1,'PaginaNo'), (2,'NoLineaDesde'), (3,'NoLineaHasta'),(4,'SubtotalMontoGravadoPagina'), (5,'SubtotalMontoGravado1Pagina'),(6,'SubtotalMontoGravado2Pagina'),(7,'SubtotalMontoGravado3Pagina'),(8,'SubtotalExentoPagina'),(9,'SubtotalItbisPagina'),(10,'SubtotalItbis1Pagina'),(11,'SubtotalItbis2Pagina'),(12,'SubtotalItbis3Pagina'),(13,'MontoSubtotalPagina')) c (columnOrder,ColumnName)
              WHERE (SELECT CEILING(COUNT(d.LineNum) / (SELECT CAST(config_value AS NUMERIC(5,1)) FROM cms.dbo.ecf_global_config WHERE config_name = 'ITEMSPERPAGE')) AS pages FROM  SBO_AGROFEM.dbo.OINV o INNER JOIN SBO_AGROFEM.dbo.INV1 d ON (d.DocEntry = o.DocEntry) WHERE o.DocEntry = @InvoiceID AND o.DocSubType = 'DN') > 1
                AND @pPrintPages = 'Y'
UNION ALL
             SELECT 500000 + ROW_NUMBER() OVER (ORDER BY c.ColumnOrder) AS id_record,
                    6 AS group_id,
                    'InformacionReferencia' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'NCFModificado' THEN UPPER(o.U_NCO)
                        WHEN 'FechaNCFModificado' THEN FORMAT(f.DocDate,'dd-MM-yyyy')
                        --WHEN 'CodigoModificacion' THEN CAST(1 AS NVARCHAR(1))
                        --SI EL MONTO DE LA NC ES IGUAL AL THE DE FACTURA ENTONCES ES PARA ANULAR FACTURA (1) DE LO CONTRARIO ES PARA MODIFICAR (3)
                        WHEN 'CodigoModificacion' THEN CAST(CASE WHEN (f.DocTotal-o.DocTotal) = 0 THEN 1 ELSE 3 END AS NVARCHAR(1))
                        WHEN 'RazonModificacion' THEN o.Comments
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM SBO_AGROFEM.dbo.OINV o
         INNER JOIN SBO_AGROFEM.dbo.ORIN f ON (f.U_FACNUM = o.U_NCO)
        CROSS APPLY (VALUES (1,'NCFModificado'), (2,'FechaNCFModificado'),(3,'CodigoModificacion'),(4,'RazonModificacion')) c (columnOrder,ColumnName)
              WHERE o.DocEntry = @InvoiceID
                AND O.DocSubType = 'DN'

            ) ecf 
        WHERE @Resumen = 0 AND @eCFType IN ('33')
          --PARA CUANDO LA NOTA DE DEBITO SE LE HACE A UNA NOTA DE CREDITO
          --
          AND (SELECT COUNT(i.DocEntry) FROM SBO_AGROFEM.dbo.ORIN i INNER JOIN (SELECT n.U_NCO FROM SBO_AGROFEM.dbo.OINV n WHERE n.DocEntry = @InvoiceID AND n.DocSubType = 'DN')  a ON (a.U_NCO = i.U_FACNUM)) > 0 
          --AND (SELECT COUNT(n.DocEntry) FROM SBO_AGROFEM.dbo.OINV n INNER JOIN SBO_AGROFEM.dbo.ORIN i ON (i.U_FACNUM = n.U_NCO) WHERE n.DocEntry = @InvoiceID) > 0 

UNION ALL

--------------------41 - COMPRAS ELECTRONICO
     SELECT ecf.*
       FROM ( 
  
             --Encabezado
             SELECT ROW_NUMBER() OVER (ORDER BY c.ColumnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'ECF' THEN 'ECF'
                        WHEN 'Version' THEN '1.0' 
                        WHEN 'TipoeCF' THEN i.ecf_type
                        WHEN 'eNCF' THEN i.ecf
                        WHEN 'FechaVencimientoSecuencia' THEN COALESCE(NULLIF(FORMAT(i.ecf_vence,'dd-MM-yyyy'),'01-01-1900'),'#e')
                        WHEN 'IndicadorNotaCredito' THEN '#e' --LOGICA PARA NOTA DE CREDITO
                        WHEN 'IndicadorMontoGravado' THEN CASE WHEN @eCFType   IN (41) THEN '0' ELSE '#e' END -- (0) EL DEALLE DE LOS ITEM NO INCLUYE EL ITBIS
                        WHEN 'TipoIngresos' THEN CASE WHEN @eCFType   IN (31,32,33,34,44,45,46) THEN '01' ELSE '#e' END -- (01) Ingresos por operaciones (No financieros).
                        WHEN 'TipoPago' THEN CAST(CASE WHEN f.ExtraDays <= 1 THEN 1 ELSE 2 END AS NVARCHAR(MAX)) -- 1:Contado | 2:Credio | 3:Gratuito
                        WHEN 'FechaLimitePago' THEN CASE WHEN f.ExtraDays > 1 THEN CASE WHEN @eCFType   = 43 THEN '#e' ELSE FORMAT(f.DocDueDate,'dd-MM-yyyy') END ELSE '#e' END
                        WHEN 'TerminoPago' THEN '#e' --CASE WHEN F.ExtraDays > 1 THEN CASE WHEN @eCFType   IN ('31','32','33','41','44','45','46','47') THEN REPLACE(g.PymntGroup,'P - ','') ELSE '#e' END ELSE '#e' END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM SBO_AGROFEM.dbo.OPCH f
         INNER JOIN cms.dbo.ecf_xml_invoice i ON (i.doc_id = f.DocEntry AND i.ecf_type IN (41)) 
          LEFT JOIN SBO_AGROFEM.dbo.OCTG g ON (g.ExtraDays = f.ExtraDays)
        CROSS APPLY (VALUES (1,'ECF'), (2,'Version'), (3,'TipoeCF'),(4,'eNCF'), (5,'FechaVencimientoSecuencia'),(6,'IndicadorNotaCredito'),(7,'IndicadorMontoGravado'),(8,'TipoIngresos'),(9,'TipoPago'),(10,'FechaLimitePago'),(11,'TerminoPago')) c (columnOrder,ColumnName)
              WHERE f.DocEntry = @InvoiceID
UNION ALL     --FORMAS DE PAGO
             SELECT 20 + ROW_NUMBER() OVER (ORDER BY s.id_diario_caja_det, c.ColumnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                         WHEN 'FormaPago' THEN CAST(t.ecf_payform_id AS NVARCHAR(MAX))
                         WHEN 'MontoPago' THEN FORMAT(s.monto_caja,'0.00')
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM cms.dbo.POSOINV f
         INNER JOIN cms.dbo.diario_caja_det s ON (s.id_documento = f.C_DocEntry AND s.docType = 'P')
          LEFT JOIN cms.dbo.ecf_payment_forms t ON (t.ecf_payform_sap = s.id_tipo_movimiento)
        CROSS APPLY (VALUES (1,'FormaPago'), (2,'MontoPago')) c (columnOrder,ColumnName)
              WHERE f.C_DocEntry = @InvoiceID
                AND @eCFType   IN ('31','32','33','44','45','46','47')
UNION ALL    --TOTAL DE PAGINAS
             SELECT 40 + ROW_NUMBER() OVER (ORDER BY p.pages) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    'TotalPaginas' AS xmlTag,
                    FORMAT(p.pages,'0') AS xml_value
               FROM (SELECT CEILING(COUNT(d.LineNum) / (SELECT CAST(config_value AS NUMERIC(5,1)) FROM cms.dbo.ecf_global_config WHERE config_name = 'ITEMSPERPAGE')) AS pages
                       FROM SBO_AGROFEM.dbo.PCH1 d
                 INNER JOIN SBO_AGROFEM.dbo.OPCH f ON (f.DocEntry = d.DocEntry)
                      WHERE d.DocEntry = @InvoiceID
                      GROUP BY f.DocEntry
                    ) p
              WHERE p.pages > 1 AND 'N' /*@pPrintPages*/ = 'Y'
UNION ALL    --EMISOR & COMPRADOR
             SELECT 50 + ROW_NUMBER() OVER (ORDER BY c.ColumnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'RNCEmisor' THEN d.RNC --PARA QUE SIEMPRE ENVIE EL RNC DE AGROFEM
                        WHEN 'RazonSocialEmisor' THEN razon_social
                        WHEN 'NombreComercial' THEN UPPER(CAST(d.descripcion AS NVARCHAR(100)))
                        WHEN 'DireccionEmisor' THEN d.direccion
                        WHEN 'TelefonoEmisor' THEN d.telefono1
                        WHEN 'CorreoEmisor' THEN d.email
                        WHEN 'WebSite' THEN d.web
                        WHEN 'NumeroFacturaInterna' THEN COALESCE(NULLIF(f.NumAtCard,''),'#e')
                        WHEN 'FechaEmision' THEN FORMAT(f.DocDate,'dd-MM-yyyy')
                        WHEN 'RNCComprador' THEN CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN CASE WHEN @eCFType   IN ('31','32','33','34','41','44','45','46') THEN f.U_FacNit ELSE '#e' END ELSE CASE WHEN F.U_TNegocio = 3 THEN CASE WHEN @eCFType   IN ('31','32','33','34','41','44','45','46') THEN '00000000000' ELSE '#e' END ELSE '#e' END END
                        WHEN 'RazonSocialComprador' THEN CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN CASE WHEN @eCFType   IN ('31','32','33','34','41','44','45','46') THEN f.CardName ELSE '#e' END ELSE CASE WHEN F.U_TNegocio = 3 THEN CASE WHEN @eCFType   IN ('31','32','33','34','41','44','45','46') THEN f.CardName ELSE '#e' END ELSE '#e' END END 
                        WHEN 'DireccionComprador' THEN CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN CASE WHEN @eCFType   IN ('31','32','33','34','44','45','46') THEN SUBSTRING(COALESCE(NULLIF(f.Address2,''),'#e'),1,100) ELSE '#e' END ELSE '#e' END 
                        WHEN 'TelefonoAdicional' THEN '#e'  
                        --WHEN 'TelefonoAdicional' THEN CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN CASE WHEN @eCFType   IN ('31','32','33','34','44','45','46') THEN COALESCE(l.Cellular,l.phone1,l.phone2,'#e') ELSE '#e' END ELSE '#e' END 
                        WHEN 'CodigoInternoComprador' THEN CASE WHEN (LEN(f.U_FacNit) = 9 OR LEN(f.U_FacNit) = 11) THEN CASE WHEN @eCFType   IN ('31','32','33','34','44','45','46') THEN f.CardCode ELSE '#e' END ELSE '#e' END 
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM cms.dbo.division d
          LEFT JOIN SBO_AGROFEM.dbo.OPCH f ON (f.DocEntry = @InvoiceID)
          LEFT JOIN SBO_AGROFEM.dbo.OCRD l ON (l.CardCode = f.CardCode)
         INNER JOIN cms.dbo.ecf_xml_invoice i ON (i.doc_id = f.DocEntry AND i.ecf_type IN (41))
        CROSS APPLY (VALUES (1,'RNCEmisor'), (2,'RazonSocialEmisor'),(3,'NombreComercial'),(4,'DireccionEmisor'),(5,'TelefonoEmisor'),(6,'CorreoEmisor'),(7,'WebSite'),(8,'NumeroFacturaInterna'),(9,'FechaEmision'),(10,'RNCComprador'),(11,'RazonSocialComprador'),(12,'DireccionComprador'),(13,'TelefonoAdicional'),(14,'CodigoInternoComprador')) c (columnOrder,ColumnName)
              WHERE d.id_division = 1 --FIJO PORQUE LAS COMPRAS IMFORMALES SOLO SE HACEN EL MF INDUSTRIAL
UNION ALL    --TOTALES
             SELECT 100 + ROW_NUMBER() OVER (ORDER BY c.columnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'MontoGravadoTotal' THEN CASE WHEN a.monto_grabado > 0 THEN FORMAT(a.monto_grabado,'0.00') ELSE CASE WHEN @eCFType   IN ('46') THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END END
                        WHEN 'MontoGravadoI1' THEN CASE WHEN a.monto_grabadoI1 > 0 THEN FORMAT(a.monto_grabadoI1,'0.00') ELSE '#e' END
                        WHEN 'MontoGravadoI2' THEN CASE WHEN a.monto_grabadoI2 > 0 THEN FORMAT(a.monto_grabadoI2,'0.00') ELSE '#e' END
                        WHEN 'MontoGravadoI3' THEN CASE WHEN a.monto_grabadoI3 > 0 THEN FORMAT(a.monto_grabadoI3,'0.00') ELSE CASE WHEN @eCFType   IN ('46') THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END END
                        WHEN 'MontoExento' THEN CASE WHEN (a.monto_exento > 0 AND @eCFType   IN ('31','32','33','34','41','43','44','45','47')) THEN FORMAT(a.monto_exento,'0.00') ELSE '#e' END
                        WHEN 'ITBIS1' THEN CASE WHEN a.itbis1 > 0 THEN FORMAT(a.itbis1,'00') ELSE '#e' END
                        WHEN 'ITBIS2' THEN CASE WHEN a.itbis2 > 0 THEN FORMAT(a.itbis2,'00') ELSE '#e' END
                        WHEN 'ITBIS3' THEN CASE WHEN a.itbis3 > 0 THEN FORMAT(a.itbis3,'0') ELSE CASE WHEN @eCFType   IN ('46') THEN '0' ELSE '#e' END END
                        WHEN 'TotalITBIS' THEN CASE WHEN a.total_itbis > 0 THEN FORMAT(a.total_itbis,'0.00') ELSE CASE WHEN @eCFType   IN ('46') THEN '0.00' ELSE '#e' END END
                        WHEN 'TotalITBIS1' THEN CASE WHEN a.total_itbis1 > 0 THEN FORMAT(a.total_itbis1,'0.00') ELSE '#e' END
                        WHEN 'TotalITBIS2' THEN CASE WHEN a.total_itbis2 > 0 THEN FORMAT(a.total_itbis2,'0.00') ELSE '#e' END
                        WHEN 'TotalITBIS3' THEN CASE WHEN a.total_itbis3 > 0 THEN FORMAT(a.total_itbis3,'0.00') ELSE CASE WHEN @eCFType   IN ('46') THEN '0.00' ELSE '#e' END END
                        WHEN 'MontoTotal' THEN CASE WHEN a.monto_total > 0 THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END
                        WHEN 'TotalITBISRetenido' THEN CASE WHEN @eCFType   IN ('41') THEN FORMAT(a.retitbis,'0.00') ELSE '#e' END
                        WHEN 'TotalISRRetencion' THEN CASE WHEN @eCFType   IN ('41','47') THEN FORMAT(a.retirs,'0.00') ELSE '#e' END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM (SELECT CAST(SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabado,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (1) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabadoI1,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (2) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabadoI2,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (3) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabadoI3,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (4) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_exento,
                            CAST(MAX(CASE WHEN t.taxecf_id IN (1) THEN d.VatPrcnt ELSE 0 END) AS NUMERIC(12,2)) AS itbis1,
                            CAST(MAX(CASE WHEN t.taxecf_id IN (2) THEN d.VatPrcnt ELSE 0 END) AS NUMERIC(12,2)) AS itbis2,
                            CAST(MAX(CASE WHEN t.taxecf_id IN (3) THEN d.VatPrcnt ELSE 0 END) AS NUMERIC(12,2)) AS itbis3,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (1) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis1,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (2) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis2,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (3) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis3,
                            CAST(SUM(d.VatSum) * MAX(COALESCE(n.retitbis,0)) AS NUMERIC(12,2)) AS retitbis,
                            CAST(SUM(d.LineTotal) * MAX(COALESCE(n.retirs,0)) AS NUMERIC(12,2)) AS retirs,
                            CAST(SUM(d.LineTotal+d.VatSum) AS NUMERIC(12,2)) AS monto_total
                       FROM SBO_AGROFEM.dbo.OPCH f
                 INNER JOIN SBO_AGROFEM.dbo.PCH1 d ON (d.DocEntry = f.DocEntry)
                 INNER JOIN cms.dbo.ecf_tax_config t ON (t.taxsap_name = d.TaxCode)
                  LEFT JOIN (SELECT r.AbsEntry,
                                    CAST(SUM(CASE WHEN t.U_TIPO = 'I' THEN r.Rate ELSE 0 END) / 100 AS NUMERIC(10,4)) AS retitbis,
                                    CAST(SUM(CASE WHEN t.U_TIPO = 'R' THEN r.Rate ELSE 0 END) / 100 AS NUMERIC(10,4)) AS retirs
                               FROM SBO_AGROFEM.DBO.PCH5 r
                          LEFT JOIN SBO_AGROFEM.DBO.OWHT t ON (t.WTCode = r.WTCode)
                              WHERE r.AbsEntry = @InvoiceID
                           GROUP BY r.AbsEntry) n ON (n.AbsEntry = f.DocEntry)
                      WHERE f.DocEntry = @InvoiceID
                    ) a
        CROSS APPLY (VALUES (1,'MontoGravadoTotal'), (2,'MontoGravadoI1'), (3,'MontoGravadoI2'),(4,'MontoGravadoI3'), (5,'MontoExento'),(6,'ITBIS1'),(7,'ITBIS2'),(8,'ITBIS3'),(9,'TotalITBIS'),(10,'TotalITBIS1'),(11,'TotalITBIS2'),(12,'TotalITBIS3'),(13,'MontoTotal'),(14,'TotalITBISRetenido'),(15,'TotalISRRetencion')) c (columnOrder,ColumnName)
UNION ALL
         --2 - DetallesItems
         SELECT 1000 + ROW_NUMBER() OVER (ORDER BY a.LineNum, c.ColumnOrder) AS id_record,
                2 AS group_id,
                'DetallesItem' AS group_name,
                c.ColumnName AS xmlTag,
                CAST(CASE c.ColumnName
                    WHEN 'NumeroLinea' THEN CAST(a.LineNum+1 AS NVARCHAR(MAX))
                    WHEN 'IndicadorFacturacion' THEN CAST( CASE WHEN @eCFType   IN ('46') THEN 3 ELSE a.taxecf_id END AS NVARCHAR(MAX))
                    WHEN 'IndicadorAgenteRetencionoPercepcion' THEN CAST(CASE WHEN @eCFType   IN ('41','47') THEN '1' ELSE '#e' END AS NVARCHAR(MAX))
                    WHEN 'MontoITBISRetenido' THEN CASE WHEN @eCFType   IN ('41') THEN FORMAT(a.retitbis,'0.00') ELSE '#e' END
                    WHEN 'MontoISRRetenido' THEN CASE WHEN @eCFType   IN ('41','47') THEN FORMAT(a.retirs,'0.00') ELSE '#e' END
                    WHEN 'NombreItem' THEN CAST(SUBSTRING(a.Dscription,1,80) AS NVARCHAR(MAX))
                    WHEN 'IndicadorBienoServicio' THEN CAST(CASE WHEN @eCFType   IN ('41','47','43') THEN 2 ELSE CASE WHEN a.DocType = 'S' THEN 2 ELSE 1 END END AS NVARCHAR(MAX))
                    WHEN 'CantidadItem' THEN FORMAT(a.Quantity,'0.00')
                    WHEN 'UnidadMedida' THEN CAST(a.ecfunit_id AS NVARCHAR(MAX))
                    WHEN 'PrecioUnitarioItem' THEN FORMAT(a.PriceBefDi,'0.00')
                    WHEN 'DescuentoMonto' THEN CASE WHEN a.DiscPrcnt > 0 THEN FORMAT((a.PriceBefDi*(a.DiscPrcnt/100))*a.Quantity,'0.00') ELSE '#e' END
                    WHEN 'TipoSubDescuento' THEN CASE WHEN a.DiscPrcnt > 0 THEN '%' ELSE '#e' END
                    WHEN 'SubDescuentoPorcentaje' THEN CASE WHEN a.DiscPrcnt > 0 THEN FORMAT(a.DiscPrcnt,'0.00') ELSE '#e' END
                    WHEN 'MontoSubDescuento' THEN CASE WHEN a.DiscPrcnt > 0 THEN FORMAT((a.PriceBefDi*(a.DiscPrcnt/100))*a.Quantity,'0.00') ELSE '#e' END
                    WHEN 'MontoItem' THEN FORMAT(a.LineTotal,'0.00')
                END AS NVARCHAR(MAX)) AS xml_value
           FROM (
                SELECT ROW_NUMBER() OVER(ORDER BY d.Dscription)-1 AS LineNum,
                       t.taxecf_id,
                       d.Dscription,
                       f.DocType,
                       COALESCE(u.ecfunit_id,43) AS ecfunit_id, --PARA FORZAR UNIDAD SI ESTE LLEGA VACIA
                       COALESCE(NULLIF(d.Quantity,0),1) AS Quantity, --PARA USAR 1 EN CASO DE SERVICIO
                       d.PriceBefDi AS PriceBefDi,
                       d.DiscPrcnt AS DiscPrcnt,
                       d.LineTotal AS LineTotal,
                       CAST(d.VatSum * COALESCE(n.retitbis,0) AS NUMERIC(12,2)) AS retitbis,
                       CAST(d.LineTotal * COALESCE(n.retirs,0) AS NUMERIC(12,2)) AS retirs

                  FROM SBO_AGROFEM.dbo.OPCH f 
            INNER JOIN SBO_AGROFEM.dbo.PCH1 d ON (d.DocEntry = f.DocEntry AND d.PriceBefDi > 0)
            INNER JOIN cms.dbo.ecf_tax_config t ON (t.taxsap_name = d.TaxCode)
             LEFT JOIN cms.dbo.ecf_units u ON (u.ecfsapunit_namemf = d.unitMsr)
             LEFT JOIN (SELECT r.AbsEntry,
                               CAST(SUM(CASE WHEN t.U_TIPO = 'I' THEN r.Rate ELSE 0 END) / 100 AS NUMERIC(10,4)) AS retitbis,
                               CAST(SUM(CASE WHEN t.U_TIPO = 'R' THEN r.Rate ELSE 0 END) / 100 AS NUMERIC(10,4)) AS retirs
                          FROM SBO_AGROFEM.DBO.PCH5 r
                     LEFT JOIN SBO_AGROFEM.DBO.OWHT t ON (t.WTCode = r.WTCode)
                         WHERE r.AbsEntry = @InvoiceID
                      GROUP BY r.AbsEntry) n ON (n.AbsEntry = f.DocEntry)
                WHERE f.DocEntry = @InvoiceID
                   

                ) a
    CROSS APPLY (VALUES (1,'NumeroLinea'), (2,'IndicadorFacturacion'), (3,'IndicadorAgenteRetencionoPercepcion'), (4,'MontoITBISRetenido'), (4,'MontoISRRetenido'), (5,'NombreItem'),(6,'IndicadorBienoServicio'), (7,'CantidadItem'),(8,'UnidadMedida'),(9,'PrecioUnitarioItem'),(10,'DescuentoMonto'),(11,'TipoSubDescuento'),(12,'SubDescuentoPorcentaje'),(13,'MontoSubDescuento'),(14,'MontoItem')) c (columnOrder,ColumnName)

UNION ALL
             --PAGINACION
             SELECT 100000+ROW_NUMBER() OVER (ORDER BY a.pagina_numero, c.columnOrder) AS id_record,
                    5 AS group_id,
                    'Paginacion' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'PaginaNo' THEN CAST(pagina_numero AS NVARCHAR(MAX))
                        WHEN 'NoLineaDesde' THEN CAST(linea_desde AS NVARCHAR(MAX))
                        WHEN 'NoLineaHasta' THEN CAST(linea_hasa AS NVARCHAR(MAX))
                        WHEN 'SubtotalMontoGravadoPagina' THEN CASE WHEN a.monto_grabado > 0 THEN FORMAT(a.monto_grabado,'0.00') ELSE '#e' END
                        WHEN 'SubtotalMontoGravado1Pagina' THEN CASE WHEN a.monto_grabadoI1 > 0 THEN FORMAT(a.monto_grabadoI1,'0.00') ELSE '#e' END
                        WHEN 'SubtotalMontoGravado2Pagina' THEN CASE WHEN a.monto_grabadoI2 > 0 THEN FORMAT(a.monto_grabadoI2,'0.00') ELSE '#e' END
                        WHEN 'SubtotalMontoGravado3Pagina' THEN CASE WHEN a.monto_grabadoI3 > 0 THEN FORMAT(a.monto_grabadoI3,'0.00') ELSE '#e' END
                        WHEN 'SubtotalExentoPagina' THEN CASE WHEN a.monto_exento > 0 THEN FORMAT(a.monto_exento,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbisPagina' THEN CASE WHEN a.total_itbis > 0 THEN FORMAT(a.total_itbis,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbis1Pagina' THEN CASE WHEN a.total_itbis1 > 0 THEN FORMAT(a.total_itbis1,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbis2Pagina' THEN CASE WHEN a.total_itbis2 > 0 THEN FORMAT(a.total_itbis2,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbis3Pagina' THEN CASE WHEN a.total_itbis3 > 0 THEN FORMAT(a.total_itbis3,'0.00') ELSE '#e' END
                        WHEN 'MontoSubtotalPagina' THEN CASE WHEN a.monto_total > 0 THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM (SELECT group_id AS pagina_numero,
                            MIN(w.numero_linea) AS linea_desde,
                            MAX(w.numero_linea) AS linea_hasa,
                            CAST(SUM(monto_grabado) AS NUMERIC(12,2)) AS monto_grabado,
                            CAST(SUM(monto_grabadoI1) AS NUMERIC(12,2)) AS monto_grabadoI1,
                            CAST(SUM(monto_grabadoI2) AS NUMERIC(12,2)) AS monto_grabadoI2,
                            CAST(SUM(monto_grabadoI3) AS NUMERIC(12,2)) AS monto_grabadoI3,
                            CAST(SUM(monto_exento) AS NUMERIC(12,2)) AS monto_exento,
                            CAST(SUM(total_itbis) AS NUMERIC(12,2)) AS total_itbis,
                            CAST(SUM(total_itbis1) AS NUMERIC(12,2)) AS total_itbis1,
                            CAST(SUM(total_itbis2) AS NUMERIC(12,2)) AS total_itbis2,
                            CAST(SUM(total_itbis3) AS NUMERIC(12,2)) AS total_itbis3,
                            CAST(SUM(monto_total) AS NUMERIC(12,2)) AS monto_total
                       FROM (SELECT (d.linenum+1) AS numero_linea,
                                    CEILING(ROW_NUMBER() OVER(ORDER BY d.linenum) / (SELECT CAST(config_value AS NUMERIC(5,1)) FROM cms.dbo.ecf_global_config WHERE config_name = 'ITEMSPERPAGE')) AS group_id,
                                    SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.LineTotal ELSE 0 END) AS monto_grabado,
                                    SUM(CASE WHEN t.taxecf_id IN (1) THEN d.LineTotal ELSE 0 END) AS monto_grabadoI1,
                                    SUM(CASE WHEN t.taxecf_id IN (2) THEN d.LineTotal ELSE 0 END) AS monto_grabadoI2,
                                    SUM(CASE WHEN t.taxecf_id IN (3) THEN d.LineTotal ELSE 0 END) AS monto_grabadoI3,
                                    SUM(CASE WHEN t.taxecf_id IN (4) THEN d.LineTotal ELSE 0 END) AS monto_exento,
                                    MAX(CASE WHEN t.taxecf_id IN (1) THEN d.VatPrcnt ELSE 0 END) AS itbis1,
                                    MAX(CASE WHEN t.taxecf_id IN (2) THEN d.VatPrcnt ELSE 0 END) AS itbis2,
                                    MAX(CASE WHEN t.taxecf_id IN (3) THEN d.VatPrcnt ELSE 0 END) AS itbis3,
                                    SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.VatSum ELSE 0 END) AS total_itbis,
                                    SUM(CASE WHEN t.taxecf_id IN (1) THEN d.VatSum ELSE 0 END) AS total_itbis1,
                                    SUM(CASE WHEN t.taxecf_id IN (2) THEN d.VatSum ELSE 0 END) AS total_itbis2,
                                    SUM(CASE WHEN t.taxecf_id IN (3) THEN d.VatSum ELSE 0 END) AS total_itbis3,
                                    SUM(d.LineTotal+d.VatSum) AS monto_total
                               FROM SBO_AGROFEM.dbo.OPCH f
                         INNER JOIN SBO_AGROFEM.dbo.PCH1 d ON (d.DocEntry = f.DocEntry)
                         INNER JOIN cms.dbo.ecf_tax_config t ON (t.taxsap_name = d.TaxCode)
                              WHERE f.DocEntry = @InvoiceID
                              GROUP BY d.linenum
                            ) w
                      GROUP BY w.group_id
                    ) a
        CROSS APPLY (VALUES (1,'PaginaNo'), (2,'NoLineaDesde'), (3,'NoLineaHasta'),(4,'SubtotalMontoGravadoPagina'), (5,'SubtotalMontoGravado1Pagina'),(6,'SubtotalMontoGravado2Pagina'),(7,'SubtotalMontoGravado3Pagina'),(8,'SubtotalExentoPagina'),(9,'SubtotalItbisPagina'),(10,'SubtotalItbis1Pagina'),(11,'SubtotalItbis2Pagina'),(12,'SubtotalItbis3Pagina'),(13,'MontoSubtotalPagina')) c (columnOrder,ColumnName)
              WHERE (SELECT CEILING(COUNT(d.LineNum) / (SELECT CAST(config_value AS NUMERIC(5,1)) FROM cms.dbo.ecf_global_config WHERE config_name = 'ITEMSPERPAGE')) AS pages FROM SBO_AGROFEM.dbo.PCH1 d WHERE d.DocEntry = @InvoiceID) > 1
                AND @pPrintPages = 'Y'
            ) ecf 
        WHERE @Resumen = 0 AND @eCFType   IN ('41')


UNION ALL

--------------------43 - GASTOS MENORES ELECTRONICOS
     SELECT ecf.*
       FROM ( 
  
             --Encabezado
             SELECT ROW_NUMBER() OVER (ORDER BY c.ColumnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'ECF' THEN 'ECF'
                        WHEN 'Version' THEN '1.0' 
                        WHEN 'TipoeCF' THEN i.ecf_type
                        WHEN 'eNCF' THEN i.ecf
                        WHEN 'FechaVencimientoSecuencia' THEN COALESCE(NULLIF(FORMAT(i.ecf_vence,'dd-MM-yyyy'),'01-01-1900'),'#e')
                        WHEN 'IndicadorNotaCredito' THEN '#e' --LOGICA PARA NOTA DE CREDITO
                        WHEN 'IndicadorMontoGravado' THEN CASE WHEN @eCFType   IN (41) THEN '0' ELSE '#e' END -- (0) EL DEALLE DE LOS ITEM NO INCLUYE EL ITBIS
                        WHEN 'TipoIngresos' THEN CASE WHEN @eCFType   IN (31,32,33,34,44,45,46) THEN '01' ELSE '#e' END -- (01) Ingresos por operaciones (No financieros).
                        WHEN 'TipoPago' THEN CAST(CASE WHEN f.ExtraDays <= 1 THEN 1 ELSE 2 END AS NVARCHAR(MAX)) -- 1:Contado | 2:Credio | 3:Gratuito
                        WHEN 'FechaLimitePago' THEN CASE WHEN f.ExtraDays > 1 THEN CASE WHEN @eCFType = 43 THEN '#e' ELSE FORMAT(f.DocDueDate,'dd-MM-yyyy') END ELSE '#e' END
                        WHEN 'TerminoPago' THEN CASE WHEN F.ExtraDays > 1 THEN CASE WHEN @eCFType   IN ('31','32','33','41','44','45','46','47') THEN REPLACE(g.PymntGroup,'P - ','') ELSE '#e' END ELSE '#e' END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM SBO_AGROFEM.dbo.OPCH f
         INNER JOIN cms.dbo.ecf_xml_invoice i ON (i.doc_id = f.DocEntry AND i.ecf_type IN (43)) 
          LEFT JOIN SBO_AGROFEM.dbo.OCTG g ON (g.ExtraDays = f.ExtraDays)
        CROSS APPLY (VALUES (1,'ECF'), (2,'Version'), (3,'TipoeCF'),(4,'eNCF'), (5,'FechaVencimientoSecuencia'),(6,'IndicadorNotaCredito'),(7,'IndicadorMontoGravado'),(8,'TipoIngresos'),(9,'TipoPago'),(10,'FechaLimitePago'),(11,'TerminoPago')) c (columnOrder,ColumnName)
              WHERE f.DocEntry = @InvoiceID
UNION ALL     --FORMAS DE PAGO
             SELECT 20 + ROW_NUMBER() OVER (ORDER BY s.id_diario_caja_det, c.ColumnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                         WHEN 'FormaPago' THEN CAST(t.ecf_payform_id AS NVARCHAR(MAX))
                         WHEN 'MontoPago' THEN FORMAT(s.monto_caja,'0.00')
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM cms.dbo.POSOINV f
         INNER JOIN cms.dbo.diario_caja_det s ON (s.id_documento = f.C_DocEntry AND s.docType = 'P')
          LEFT JOIN cms.dbo.ecf_payment_forms t ON (t.ecf_payform_sap = s.id_tipo_movimiento)
        CROSS APPLY (VALUES (1,'FormaPago'), (2,'MontoPago')) c (columnOrder,ColumnName)
              WHERE f.C_DocEntry = @InvoiceID
                AND @eCFType   IN ('31','32','33','44','45','46','47')
UNION ALL    --TOTAL DE PAGINAS
             SELECT 40 + ROW_NUMBER() OVER (ORDER BY p.pages) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    'TotalPaginas' AS xmlTag,
                    FORMAT(p.pages,'0') AS xml_value
               FROM (SELECT CEILING(COUNT(d.LineNum) / (SELECT CAST(config_value AS NUMERIC(5,1)) FROM cms.dbo.ecf_global_config WHERE config_name = 'ITEMSPERPAGE')) AS pages
                       FROM SBO_AGROFEM.dbo.PCH1 d
                 INNER JOIN SBO_AGROFEM.dbo.OPCH f ON (f.DocEntry = d.DocEntry)
                      WHERE d.DocEntry = @InvoiceID
                      GROUP BY f.DocEntry
                    ) p
              WHERE p.pages > 1 AND @pPrintPages = 'Y'
UNION ALL    --EMISOR & COMPRADOR
             SELECT 50 + ROW_NUMBER() OVER (ORDER BY c.ColumnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'RNCEmisor' THEN d.RNC --PARA QUE SIEMPRE ENVIE EL RNC DE AGROFEM
                        WHEN 'RazonSocialEmisor' THEN razon_social
                        WHEN 'NombreComercial' THEN UPPER(CAST(d.descripcion AS NVARCHAR(100)))
                        WHEN 'DireccionEmisor' THEN d.direccion
                        WHEN 'TelefonoEmisor' THEN d.telefono1
                        WHEN 'CorreoEmisor' THEN d.email
                        WHEN 'WebSite' THEN d.web
                        WHEN 'NumeroFacturaInterna' THEN COALESCE(NULLIF(f.NumAtCard,''),'#e')
                        WHEN 'FechaEmision' THEN FORMAT(f.DocDate,'dd-MM-yyyy')
                        WHEN 'RNCComprador' THEN '#e'
                        WHEN 'RazonSocialComprador' THEN '#e'
                        WHEN 'DireccionComprador' THEN '#e'
                        WHEN 'TelefonoAdicional' THEN '#e'  
                        WHEN 'CodigoInternoComprador' THEN '#e'
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM cms.dbo.division d
          LEFT JOIN SBO_AGROFEM.dbo.OPCH f ON (f.DocEntry = @InvoiceID)
          LEFT JOIN SBO_AGROFEM.dbo.OCRD l ON (l.CardCode = f.CardCode)
         INNER JOIN cms.dbo.ecf_xml_invoice i ON (i.doc_id = f.DocEntry AND i.ecf_type IN (43))
        CROSS APPLY (VALUES (1,'RNCEmisor'), (2,'RazonSocialEmisor'),(3,'NombreComercial'),(4,'DireccionEmisor'),(5,'TelefonoEmisor'),(6,'CorreoEmisor'),(7,'WebSite'),(8,'NumeroFacturaInterna'),(9,'FechaEmision'),(10,'RNCComprador'),(11,'RazonSocialComprador'),(12,'DireccionComprador'),(13,'TelefonoAdicional'),(14,'CodigoInternoComprador')) c (columnOrder,ColumnName)
              WHERE d.id_division = 1 --FIJO PORQUE LAS COMPRAS IMFORMALES SOLO SE HACEN EL MF INDUSTRIAL
UNION ALL    --TOTALES
             SELECT 100 + ROW_NUMBER() OVER (ORDER BY c.columnOrder) AS id_record,
                    1 AS group_id,
                    'Encabezado' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'MontoGravadoTotal' THEN '#e' --CASE WHEN a.monto_grabado > 0 THEN FORMAT(a.monto_grabado,'0.00') ELSE CASE WHEN @eCFType   IN ('46') THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END END
                        WHEN 'MontoGravadoI1' THEN '#e' --CASE WHEN a.monto_grabadoI1 > 0 THEN FORMAT(a.monto_grabadoI1,'0.00') ELSE '#e' END
                        WHEN 'MontoGravadoI2' THEN '#e' --CASE WHEN a.monto_grabadoI2 > 0 THEN FORMAT(a.monto_grabadoI2,'0.00') ELSE '#e' END
                        WHEN 'MontoGravadoI3' THEN '#e' --CASE WHEN a.monto_grabadoI3 > 0 THEN FORMAT(a.monto_grabadoI3,'0.00') ELSE CASE WHEN @eCFType   IN ('46') THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END END
                        WHEN 'MontoExento' THEN CASE WHEN (a.monto_exento > 0 AND @eCFType   IN ('31','32','33','34','41','43','44','45','47')) THEN FORMAT(a.monto_exento,'0.00') ELSE '#e' END
                        WHEN 'ITBIS1' THEN '#e' --CASE WHEN a.itbis1 > 0 THEN FORMAT(a.itbis1,'00') ELSE '#e' END
                        WHEN 'ITBIS2' THEN '#e' --CASE WHEN a.itbis2 > 0 THEN FORMAT(a.itbis2,'00') ELSE '#e' END
                        WHEN 'ITBIS3' THEN '#e' --CASE WHEN a.itbis3 > 0 THEN FORMAT(a.itbis3,'0') ELSE CASE WHEN @eCFType   IN ('46') THEN '0' ELSE '#e' END END
                        WHEN 'TotalITBIS' THEN '#e' --CASE WHEN a.total_itbis > 0 THEN FORMAT(a.total_itbis,'0.00') ELSE CASE WHEN @eCFType   IN ('46') THEN '0.00' ELSE '#e' END END
                        WHEN 'TotalITBIS1' THEN '#e' --CASE WHEN a.total_itbis1 > 0 THEN FORMAT(a.total_itbis1,'0.00') ELSE '#e' END
                        WHEN 'TotalITBIS2' THEN '#e' --CASE WHEN a.total_itbis2 > 0 THEN FORMAT(a.total_itbis2,'0.00') ELSE '#e' END
                        WHEN 'TotalITBIS3' THEN '#e' --CASE WHEN a.total_itbis3 > 0 THEN FORMAT(a.total_itbis3,'0.00') ELSE CASE WHEN @eCFType   IN ('46') THEN '0.00' ELSE '#e' END END
                        WHEN 'MontoTotal' THEN CASE WHEN a.monto_total > 0 THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END
                        WHEN 'TotalITBISRetenido' THEN '#e' --CASE WHEN @eCFType   IN ('41') THEN FORMAT(a.retitbis,'0.00') ELSE '#e' END
                        WHEN 'TotalISRRetencion' THEN '#e' --CASE WHEN @eCFType   IN ('41','47') THEN FORMAT(a.retirs,'0.00') ELSE '#e' END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM (SELECT CAST(SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabado,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (1) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabadoI1,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (2) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabadoI2,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (3) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_grabadoI3,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (4) THEN d.LineTotal ELSE 0 END) AS NUMERIC(12,2)) AS monto_exento,
                            CAST(MAX(CASE WHEN t.taxecf_id IN (1) THEN d.VatPrcnt ELSE 0 END) AS NUMERIC(12,2)) AS itbis1,
                            CAST(MAX(CASE WHEN t.taxecf_id IN (2) THEN d.VatPrcnt ELSE 0 END) AS NUMERIC(12,2)) AS itbis2,
                            CAST(MAX(CASE WHEN t.taxecf_id IN (3) THEN d.VatPrcnt ELSE 0 END) AS NUMERIC(12,2)) AS itbis3,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (1) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis1,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (2) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis2,
                            CAST(SUM(CASE WHEN t.taxecf_id IN (3) THEN d.VatSum ELSE 0 END) AS NUMERIC(12,2)) AS total_itbis3,
                            CAST(SUM(d.VatSum) * MAX(COALESCE(n.retitbis,0)) AS NUMERIC(12,2)) AS retitbis,
                            CAST(SUM(d.LineTotal) * MAX(COALESCE(n.retirs,0)) AS NUMERIC(12,2)) AS retirs,
                            CAST(SUM(d.LineTotal+d.VatSum) AS NUMERIC(12,2)) AS monto_total
                       FROM SBO_AGROFEM.dbo.OPCH f
                 INNER JOIN SBO_AGROFEM.dbo.PCH1 d ON (d.DocEntry = f.DocEntry)
                 INNER JOIN cms.dbo.ecf_tax_config t ON (t.taxsap_name = d.TaxCode)
                  LEFT JOIN (SELECT r.AbsEntry,
                                    CAST(SUM(CASE WHEN t.U_TIPO = 'I' THEN r.Rate ELSE 0 END) / 100 AS NUMERIC(10,4)) AS retitbis,
                                    CAST(SUM(CASE WHEN t.U_TIPO = 'R' THEN r.Rate ELSE 0 END) / 100 AS NUMERIC(10,4)) AS retirs
                               FROM SBO_AGROFEM.DBO.PCH5 r
                          LEFT JOIN SBO_AGROFEM.DBO.OWHT t ON (t.WTCode = r.WTCode)
                              WHERE r.AbsEntry = @InvoiceID
                           GROUP BY r.AbsEntry) n ON (n.AbsEntry = f.DocEntry)
                      WHERE f.DocEntry = @InvoiceID
                    ) a
        CROSS APPLY (VALUES (1,'MontoGravadoTotal'), (2,'MontoGravadoI1'), (3,'MontoGravadoI2'),(4,'MontoGravadoI3'), (5,'MontoExento'),(6,'ITBIS1'),(7,'ITBIS2'),(8,'ITBIS3'),(9,'TotalITBIS'),(10,'TotalITBIS1'),(11,'TotalITBIS2'),(12,'TotalITBIS3'),(13,'MontoTotal'),(14,'TotalITBISRetenido'),(15,'TotalISRRetencion')) c (columnOrder,ColumnName)
UNION ALL
         --2 - DetallesItems
         SELECT 1000 + ROW_NUMBER() OVER (ORDER BY a.LineNum, c.ColumnOrder) AS id_record,
                2 AS group_id,
                'DetallesItem' AS group_name,
                c.ColumnName AS xmlTag,
                CAST(CASE c.ColumnName
                    WHEN 'NumeroLinea' THEN CAST(a.LineNum+1 AS NVARCHAR(MAX))
                    WHEN 'IndicadorFacturacion' THEN CAST( CASE WHEN @eCFType IN ('46') THEN 3 ELSE a.taxecf_id END AS NVARCHAR(MAX))
                    WHEN 'IndicadorAgenteRetencionoPercepcion' THEN '#e' --CAST(CASE WHEN @eCFType   IN ('41','47') THEN '1' ELSE '#e' END AS NVARCHAR(MAX))
                    WHEN 'MontoITBISRetenido' THEN '#e' --CASE WHEN @eCFType   IN ('41') THEN FORMAT(a.retitbis,'0.00') ELSE '#e' END
                    WHEN 'MontoISRRetenido' THEN '#e' --CASE WHEN @eCFType   IN ('41','47') THEN FORMAT(a.retirs,'0.00') ELSE '#e' END
                    WHEN 'NombreItem' THEN CAST(SUBSTRING(a.Dscription,1,80) AS NVARCHAR(MAX))
                    WHEN 'IndicadorBienoServicio' THEN CAST(CASE WHEN @eCFType   IN ('41','47','43') THEN 2 ELSE CASE WHEN a.DocType = 'S' THEN 2 ELSE 1 END END AS NVARCHAR(MAX))
                    WHEN 'CantidadItem' THEN FORMAT(a.Quantity,'0.00')
                    WHEN 'UnidadMedida' THEN CAST(a.ecfunit_id AS NVARCHAR(MAX))
                    WHEN 'PrecioUnitarioItem' THEN FORMAT(a.PriceBefDi,'0.00')
                    WHEN 'DescuentoMonto' THEN CASE WHEN a.DiscPrcnt > 0 THEN FORMAT((a.PriceBefDi*(a.DiscPrcnt/100))*a.Quantity,'0.00') ELSE '#e' END
                    WHEN 'TipoSubDescuento' THEN CASE WHEN a.DiscPrcnt > 0 THEN '%' ELSE '#e' END
                    WHEN 'SubDescuentoPorcentaje' THEN CASE WHEN a.DiscPrcnt > 0 THEN FORMAT(a.DiscPrcnt,'0.00') ELSE '#e' END
                    WHEN 'MontoSubDescuento' THEN CASE WHEN a.DiscPrcnt > 0 THEN FORMAT((a.PriceBefDi*(a.DiscPrcnt/100))*a.Quantity,'0.00') ELSE '#e' END
                    WHEN 'MontoItem' THEN FORMAT(a.LineTotal,'0.00')
                END AS NVARCHAR(MAX)) AS xml_value
           FROM (
                SELECT ROW_NUMBER() OVER(ORDER BY d.Dscription)-1 AS LineNum,
                       t.taxecf_id,
                       d.Dscription,
                       f.DocType,
                       COALESCE(u.ecfunit_id,43) AS ecfunit_id, --PARA FORZAR UNIDAD SI ESTE LLEGA VACIA
                       COALESCE(NULLIF(d.Quantity,0),1) AS Quantity, --PARA USAR 1 EN CASO DE SERVICIO
                       d.PriceBefDi AS PriceBefDi,
                       d.DiscPrcnt AS DiscPrcnt,
                       d.LineTotal AS LineTotal
                  FROM SBO_AGROFEM.dbo.OPCH f 
            INNER JOIN SBO_AGROFEM.dbo.PCH1 d ON (d.DocEntry = f.DocEntry)
            INNER JOIN cms.dbo.ecf_tax_config t ON (t.taxsap_name = d.TaxCode)
             LEFT JOIN cms.dbo.ecf_units u ON (u.ecfsapunit_namemf = d.unitMsr)
                WHERE f.DocEntry = @InvoiceID

                ) a
    CROSS APPLY (VALUES (1,'NumeroLinea'), (2,'IndicadorFacturacion'), (3,'IndicadorAgenteRetencionoPercepcion'), (4,'MontoITBISRetenido'), (4,'MontoISRRetenido'), (5,'NombreItem'),(6,'IndicadorBienoServicio'), (7,'CantidadItem'),(8,'UnidadMedida'),(9,'PrecioUnitarioItem'),(10,'DescuentoMonto'),(11,'TipoSubDescuento'),(12,'SubDescuentoPorcentaje'),(13,'MontoSubDescuento'),(14,'MontoItem')) c (columnOrder,ColumnName)

UNION ALL
             --PAGINACION
             SELECT 100000+ROW_NUMBER() OVER (ORDER BY a.pagina_numero, c.columnOrder) AS id_record,
                    5 AS group_id,
                    'Paginacion' AS group_name,
                    c.ColumnName AS xmlTag,
                    CAST(CASE c.ColumnName
                        WHEN 'PaginaNo' THEN CAST(pagina_numero AS NVARCHAR(MAX))
                        WHEN 'NoLineaDesde' THEN CAST(linea_desde AS NVARCHAR(MAX))
                        WHEN 'NoLineaHasta' THEN CAST(linea_hasa AS NVARCHAR(MAX))
                        WHEN 'SubtotalMontoGravadoPagina' THEN CASE WHEN a.monto_grabado > 0 THEN FORMAT(a.monto_grabado,'0.00') ELSE '#e' END
                        WHEN 'SubtotalMontoGravado1Pagina' THEN CASE WHEN a.monto_grabadoI1 > 0 THEN FORMAT(a.monto_grabadoI1,'0.00') ELSE '#e' END
                        WHEN 'SubtotalMontoGravado2Pagina' THEN CASE WHEN a.monto_grabadoI2 > 0 THEN FORMAT(a.monto_grabadoI2,'0.00') ELSE '#e' END
                        WHEN 'SubtotalMontoGravado3Pagina' THEN CASE WHEN a.monto_grabadoI3 > 0 THEN FORMAT(a.monto_grabadoI3,'0.00') ELSE '#e' END
                        WHEN 'SubtotalExentoPagina' THEN CASE WHEN a.monto_exento > 0 THEN FORMAT(a.monto_exento,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbisPagina' THEN CASE WHEN a.total_itbis > 0 THEN FORMAT(a.total_itbis,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbis1Pagina' THEN CASE WHEN a.total_itbis1 > 0 THEN FORMAT(a.total_itbis1,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbis2Pagina' THEN CASE WHEN a.total_itbis2 > 0 THEN FORMAT(a.total_itbis2,'0.00') ELSE '#e' END
                        WHEN 'SubtotalItbis3Pagina' THEN CASE WHEN a.total_itbis3 > 0 THEN FORMAT(a.total_itbis3,'0.00') ELSE '#e' END
                        WHEN 'MontoSubtotalPagina' THEN CASE WHEN a.monto_total > 0 THEN FORMAT(a.monto_total,'0.00') ELSE '#e' END
                    END AS NVARCHAR(MAX)) AS xml_value
               FROM (SELECT group_id AS pagina_numero,
                            MIN(w.numero_linea) AS linea_desde,
                            MAX(w.numero_linea) AS linea_hasa,
                            CAST(SUM(monto_grabado) AS NUMERIC(12,2)) AS monto_grabado,
                            CAST(SUM(monto_grabadoI1) AS NUMERIC(12,2)) AS monto_grabadoI1,
                            CAST(SUM(monto_grabadoI2) AS NUMERIC(12,2)) AS monto_grabadoI2,
                            CAST(SUM(monto_grabadoI3) AS NUMERIC(12,2)) AS monto_grabadoI3,
                            CAST(SUM(monto_exento) AS NUMERIC(12,2)) AS monto_exento,
                            CAST(SUM(total_itbis) AS NUMERIC(12,2)) AS total_itbis,
                            CAST(SUM(total_itbis1) AS NUMERIC(12,2)) AS total_itbis1,
                            CAST(SUM(total_itbis2) AS NUMERIC(12,2)) AS total_itbis2,
                            CAST(SUM(total_itbis3) AS NUMERIC(12,2)) AS total_itbis3,
                            CAST(SUM(monto_total) AS NUMERIC(12,2)) AS monto_total
                       FROM (SELECT (d.linenum+1) AS numero_linea,
                                    CEILING(ROW_NUMBER() OVER(ORDER BY d.linenum) / (SELECT CAST(config_value AS NUMERIC(5,1)) FROM cms.dbo.ecf_global_config WHERE config_name = 'ITEMSPERPAGE')) AS group_id,
                                    SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.LineTotal ELSE 0 END) AS monto_grabado,
                                    SUM(CASE WHEN t.taxecf_id IN (1) THEN d.LineTotal ELSE 0 END) AS monto_grabadoI1,
                                    SUM(CASE WHEN t.taxecf_id IN (2) THEN d.LineTotal ELSE 0 END) AS monto_grabadoI2,
                                    SUM(CASE WHEN t.taxecf_id IN (3) THEN d.LineTotal ELSE 0 END) AS monto_grabadoI3,
                                    SUM(CASE WHEN t.taxecf_id IN (4) THEN d.LineTotal ELSE 0 END) AS monto_exento,
                                    MAX(CASE WHEN t.taxecf_id IN (1) THEN d.VatPrcnt ELSE 0 END) AS itbis1,
                                    MAX(CASE WHEN t.taxecf_id IN (2) THEN d.VatPrcnt ELSE 0 END) AS itbis2,
                                    MAX(CASE WHEN t.taxecf_id IN (3) THEN d.VatPrcnt ELSE 0 END) AS itbis3,
                                    SUM(CASE WHEN t.taxecf_id IN (1,2,3) THEN d.VatSum ELSE 0 END) AS total_itbis,
                                    SUM(CASE WHEN t.taxecf_id IN (1) THEN d.VatSum ELSE 0 END) AS total_itbis1,
                                    SUM(CASE WHEN t.taxecf_id IN (2) THEN d.VatSum ELSE 0 END) AS total_itbis2,
                                    SUM(CASE WHEN t.taxecf_id IN (3) THEN d.VatSum ELSE 0 END) AS total_itbis3,
                                    SUM(d.LineTotal+d.VatSum) AS monto_total
                               FROM SBO_AGROFEM.dbo.OPCH f
                         INNER JOIN SBO_AGROFEM.dbo.PCH1 d ON (d.DocEntry = f.DocEntry)
                         INNER JOIN cms.dbo.ecf_tax_config t ON (t.taxsap_name = d.TaxCode)
                              WHERE f.DocEntry = @InvoiceID
                              GROUP BY d.linenum
                            ) w
                      GROUP BY w.group_id
                    ) a
        CROSS APPLY (VALUES (1,'PaginaNo'), (2,'NoLineaDesde'), (3,'NoLineaHasta'),(4,'SubtotalMontoGravadoPagina'), (5,'SubtotalMontoGravado1Pagina'),(6,'SubtotalMontoGravado2Pagina'),(7,'SubtotalMontoGravado3Pagina'),(8,'SubtotalExentoPagina'),(9,'SubtotalItbisPagina'),(10,'SubtotalItbis1Pagina'),(11,'SubtotalItbis2Pagina'),(12,'SubtotalItbis3Pagina'),(13,'MontoSubtotalPagina')) c (columnOrder,ColumnName)
              WHERE (SELECT CEILING(COUNT(d.LineNum) / (SELECT CAST(config_value AS NUMERIC(5,1)) FROM cms.dbo.ecf_global_config WHERE config_name = 'ITEMSPERPAGE')) AS pages FROM SBO_AGROFEM.dbo.PCH1 d WHERE d.DocEntry = @InvoiceID) > 1
                AND @pPrintPages = 'Y'
            ) ecf 
        WHERE @Resumen = 0 AND @eCFType   IN ('43')
--------------------
*/
);
GO

-- Función (SQL_SCALAR_FUNCTION): ReplaceCharsInURL
CREATE FUNCTION [dbo].[ReplaceCharsInURL](@inputString NVARCHAR(MAX))
RETURNS NVARCHAR(MAX)
AS
BEGIN
    -- Start with the input string
    DECLARE @outputString NVARCHAR(MAX) = @inputString;

    -- Replace each character using the mapping table
    SELECT @outputString = REPLACE(@outputString, char_value, char_hex)
    FROM [cms].[dbo].[ecf_url_chars];

    -- Return the modified string
    RETURN @outputString;
END;
GO


-- ==========================================================================
-- PROCEDIMIENTOS ALMACENADOS (STORED PROCEDURES)
-- ==========================================================================

