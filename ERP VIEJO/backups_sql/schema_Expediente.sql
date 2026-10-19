-- ==========================================================================
-- EXPORTACIÓN ESTRUCTURA / SCHEMA COMPLETO DE Expediente
-- Generado automáticamente para referencia de migración Cloud / PostgreSQL
-- Fecha: 2026-10-12 19:38:56
-- ==========================================================================

-- ==========================================================================
-- TABLAS Y COLUMNAS
-- ==========================================================================

-- Tabla: s_Alergeno
CREATE TABLE [dbo].[s_Alergeno] (
	[IdAlergeno] varchar(10) NOT NULL,
	[NomAlergeno] varchar(50) NULL,
	[IdTipoAlergeno] varchar(10) NULL,
	[Estatus] char(1) NULL,
	CONSTRAINT [PK_s_Alergeno] PRIMARY KEY CLUSTERED ([IdAlergeno] ASC)
);
GO

-- Tabla: s_Analisis
CREATE TABLE [dbo].[s_Analisis] (
	[anal_IdAnalisis] varchar(10) NOT NULL,
	[anal_Nombre] varchar(50) NULL,
	[anal_Estatus] char(1) NULL DEFAULT ('A'),
	CONSTRAINT [PK_s_Analisis] PRIMARY KEY CLUSTERED ([anal_IdAnalisis] ASC)
);
GO

-- Tabla: s_AutorizacionesSeguro
CREATE TABLE [dbo].[s_AutorizacionesSeguro] (
	[NumAut] bigint NOT NULL,
	[Fecha] datetime NULL,
	[IdSeguro] varchar(10) NULL,
	[IdPlanSeguro] int NULL,
	[NumeroExpediente] bigint NULL,
	[IdDoctor] varchar(15) NULL,
	[Especialidad] varchar(20) NULL,
	[Nombre] varchar(30) NULL,
	[Apellido] varchar(30) NULL,
	[NumeroAfiliado] varchar(15) NULL,
	[NumeroPoliza] varchar(15) NULL,
	[NumeroAutorizacion] varchar(25) NULL,
	[TipoServicio] char(1) NULL DEFAULT ('C'),
	[Cedula] char(15) NULL,
	[TipoDoc] char(1) NULL DEFAULT ('C'),
	[IdDiagnostico] varchar(10) NOT NULL,
	[CodSubPatologia] varchar(10) NULL,
	[ProcedimientoRealizado] varchar(400) NULL,
	[Monto] float NULL,
	[CoberturaSeguro] float NULL,
	[PagoPaciente] float NULL,
	[RegPor] varchar(15) NULL,
	[hpag_NumeroReclamacionSeguro] bigint NULL,
	[hpag_NumeroPagoSeguro] bigint NULL DEFAULT ((0)),
	[Estatus] char(1) NULL DEFAULT ('N'),
	[TipoAfiliado] char(1) NULL DEFAULT ('T'),
	[AutorizadoPor] varchar(50) NULL,
	CONSTRAINT [PK_s_AutorizacionesSeguro] PRIMARY KEY CLUSTERED ([NumAut] ASC)
);
GO

-- Tabla: s_Banco
CREATE TABLE [dbo].[s_Banco] (
	[banc_IdBanco] varchar(10) NOT NULL,
	[banc_Nombre] varchar(50) NULL,
	[banc_NumeroCuenta] varchar(20) NULL,
	[banc_Estatus] char(1) NULL DEFAULT ('A'),
	[IdCuentaSIN] varchar(15) NULL,
	CONSTRAINT [PK_s_Banco] PRIMARY KEY CLUSTERED ([banc_IdBanco] ASC)
);
GO

-- Tabla: s_Caja
CREATE TABLE [dbo].[s_Caja] (
	[caja_IdCaja] varchar(10) NOT NULL,
	[caja_Nombre] varchar(50) NULL,
	[caja_EstatusOperacion] char(1) NULL,
	[caja_MontoApertura] float NULL,
	[caja_MontoCierre] float NULL,
	[caja_FechaApertura] smalldatetime NULL,
	[caja_FechaCierre] smalldatetime NULL,
	[caja_IdUsuarioAbrioCaja] varchar(15) NULL,
	[caja_IdUsuarioCerroCaja] varchar(15) NULL,
	[caja_Estatus] char(1) NULL DEFAULT ('A'),
	[caja_IdMoneda] varchar(5) NULL DEFAULT ('RD$'),
	[caja_IdSecCFPagoPrivadoEfe] varchar(10) NULL,
	[caja_IdSecCFPagoPrivadoTar] varchar(10) NULL,
	[caja_IdSecCFPagoSeguro] varchar(10) NULL,
	[caja_GenerarCF] bit NULL,
	[caja_ReporteFormatoPago] varchar(50) NULL,
	[NumCuenEfe] varchar(15) NULL,
	[NumCuenChk] varchar(15) NULL,
	[NumCuenTar] varchar(15) NULL,
	[caja_IdSecCFPagoSeguroTar] varchar(10) NULL,
	[CodCajSin] varchar(2) NULL,
	[IdCentro] varchar(5) NULL,
	CONSTRAINT [PK_s_Caja] PRIMARY KEY CLUSTERED ([caja_IdCaja] ASC)
);
GO

-- Tabla: s_CajaSecuenciaCF
CREATE TABLE [dbo].[s_CajaSecuenciaCF] (
	[IdCaja] varchar(10) NOT NULL,
	[IdSecCF] varchar(10) NOT NULL,
	CONSTRAINT [PK_s_CajaSecuenciaCF] PRIMARY KEY CLUSTERED ([IdCaja] ASC, [IdSecCF] ASC)
);
GO

-- Tabla: s_Carrier
CREATE TABLE [dbo].[s_Carrier] (
	[IdCarrier] varchar(10) NOT NULL,
	[NomCarrier] varchar(50) NULL,
	[IdCarrierSIN] varchar(10) NULL,
	[Estatus] char(1) NULL,
	[PorComision] decimal(5, 2) NULL,
	CONSTRAINT [PK_s_Carrier] PRIMARY KEY CLUSTERED ([IdCarrier] ASC)
);
GO

-- Tabla: s_Centro
CREATE TABLE [dbo].[s_Centro] (
	[IdCentro] varchar(5) NOT NULL,
	[NomCentro] varchar(50) NULL,
	[Contacto] varchar(50) NULL,
	[Telefono] varchar(14) NULL,
	[Estatus] char(1) NULL DEFAULT ('A'),
	[Direccion1] varchar(80) NULL,
	[Direccion2] varchar(80) NULL,
	[Telefono2] varchar(14) NULL,
	[Celular] varchar(14) NULL,
	[Logo] image NULL,
	[Email] varchar(50) NULL,
	[NocCentro] varchar(10) NULL,
	CONSTRAINT [PK_s_Centro] PRIMARY KEY CLUSTERED ([IdCentro] ASC)
);
GO

-- Tabla: s_CertificacionMedica
CREATE TABLE [dbo].[s_CertificacionMedica] (
	[cert_NumeroCertificado] bigint NOT NULL,
	[cert_Fecha] datetime NULL,
	[cert_NumeroExpediente] bigint NULL,
	[cert_Nombre] varchar(50) NULL,
	[cert_cedula] varchar(15) NULL,
	[cert_Constatado] varchar(3000) NULL,
	[cert_Recomendacion] varchar(3000) NULL,
	[cert_Ciudad] varchar(30) NULL,
	[cert_IdDoctor] varchar(15) NULL,
	[cert_IdTipoCert] varchar(10) NULL,
	CONSTRAINT [PK_s_CertificacionMedica] PRIMARY KEY CLUSTERED ([cert_NumeroCertificado] ASC)
);
GO

-- Tabla: s_Cita
CREATE TABLE [dbo].[s_Cita] (
	[cita_IdDoctor] varchar(15) NOT NULL DEFAULT (''),
	[cita_Fecha] smalldatetime NOT NULL,
	[cita_hora] char(6) NOT NULL,
	[cita_NumeroExpediente] bigint NULL,
	[cita_HoraNumero] float NULL,
	[cita_Nombre] varchar(30) NULL,
	[cita_Apellido] varchar(30) NULL,
	[cita_Telefono] varchar(15) NULL,
	[cita_IdSeguro] varchar(10) NULL,
	[cita_IdPlanSeguro] int NULL,
	[cita_Comentario] varchar(50) NULL,
	[cita_EstatusConf] char(1) NULL DEFAULT (''),
	[cita_FechaEstatusConf] datetime NULL,
	[cita_RegPorEstatusConf] varchar(15) NULL,
	[cita_RegPor] varchar(15) NULL,
	[cita_FecReg] datetime NULL DEFAULT (getdate()),
	[cita_Email] varchar(50) NULL,
	[cita_StsEmail] char(1) NULL,
	[cita_FechaEmail] datetime NULL,
	[cita_ConfEmailUsada] int NULL,
	[cita_Nota] varchar(1500) NULL,
	[NumCita] bigint NOT NULL,
	[IdOrig] int NULL,
	[NumLista] bigint NULL,
	[IdConsultorio] varchar(10) NULL,
	[IdEntidadRef] varchar(10) NULL,
	[cita_Celular] varchar(15) NULL,
	[NumPreCita] bigint NULL,
	[cita_NumeroAfiliado] varchar(15) NULL,
	[cita_NumeroPoliza] varchar(15) NULL,
	CONSTRAINT [PK_s_Cita] PRIMARY KEY CLUSTERED ([NumCita] ASC)
);
GO

-- Tabla: s_Ciudad
CREATE TABLE [dbo].[s_Ciudad] (
	[CodCiu] varchar(5) NOT NULL,
	[NomCiu] varchar(50) NULL,
	[CodProv] varchar(5) NULL,
	[Estatus] char(1) NULL DEFAULT ('A'),
	CONSTRAINT [PK_s_Ciudad] PRIMARY KEY CLUSTERED ([CodCiu] ASC)
);
GO

-- Tabla: s_ComboTipoCirugia
CREATE TABLE [dbo].[s_ComboTipoCirugia] (
	[IdCombo] varchar(9) NOT NULL,
	[DesCombo] varchar(200) NULL,
	[Estatus] char(1) NULL DEFAULT ('A'),
	[Cups] varchar(20) NULL,
	CONSTRAINT [PK_s_ComboTipoCirugia] PRIMARY KEY CLUSTERED ([IdCombo] ASC)
);
GO

-- Tabla: s_ComboTipoCirugia_Procedimientos
CREATE TABLE [dbo].[s_ComboTipoCirugia_Procedimientos] (
	[IdCombo] varchar(9) NOT NULL,
	[Linea] int NOT NULL,
	[IdTipoListaEspecial] varchar(10) NULL,
	CONSTRAINT [PK_s_ComboTipoCirugia_Procedimientos] PRIMARY KEY CLUSTERED ([IdCombo] ASC, [Linea] ASC)
);
GO

-- Tabla: s_CompraLente_Table
CREATE TABLE [dbo].[s_CompraLente_Table] (
	[Numero] int NOT NULL,
	[Fecha] smalldatetime NULL,
	[NumeroExpediente] bigint NULL,
	[NumFac] varchar(20) NULL,
	[Monto] float NULL,
	[Descripcion] varchar(50) NULL,
	[RegPor] varchar(15) NULL,
	[Estatus] char(1) NULL DEFAULT ('A'),
	[IdOptica] varchar(10) NULL,
	[IdDoctor] varchar(15) NULL,
	[FechaReceta] smalldatetime NULL,
	CONSTRAINT [PK_s_CompraLente] PRIMARY KEY CLUSTERED ([Numero] ASC)
);
GO

-- Tabla: s_ConceptoPago
CREATE TABLE [dbo].[s_ConceptoPago] (
	[conc_IdConcepto] varchar(10) NOT NULL,
	[conc_Nombre] varchar(80) NULL,
	[conc_Tipo] char(1) NULL,
	[conc_PrecioASugerir] float NULL,
	[conc_IdLista] varchar(10) NULL,
	[conc_IdListaServicio] varchar(10) NULL,
	[conc_Estatus] char(1) NULL DEFAULT ('A'),
	[conc_Costo] float NULL,
	[conc_CodTArt] varchar(10) NULL,
	[conc_IdUnidad] varchar(10) NULL,
	[conc_IdTipoSer] varchar(10) NULL,
	[conc_CoberturaASugerir] float NULL,
	[conc_PrPagoInterno] float NULL,
	[conc_PrPagoExterno] float NULL,
	[conc_Precio1] decimal(12, 2) NULL,
	[conc_Precio2] decimal(12, 2) NULL,
	[conc_Precio3] decimal(12, 2) NULL,
	[conc_Precio4] decimal(12, 2) NULL,
	[CodTipoServ] varchar(10) NULL,
	[CodSubTipoServ] varchar(10) NULL,
	[CodArtSin] varchar(16) NULL,
	[conc_Descripcion] varchar(800) NULL,
	CONSTRAINT [PK_s_Concepto] PRIMARY KEY CLUSTERED ([conc_IdConcepto] ASC)
);
GO

-- Tabla: s_ConceptosCaja
CREATE TABLE [dbo].[s_ConceptosCaja] (
	[conc_IdConcepto] varchar(10) NOT NULL,
	[conc_Nombre] varchar(50) NULL,
	[conc_Tipo] char(1) NULL DEFAULT ('E'),
	[conc_estatus] char(1) NULL DEFAULT ('A'),
	[IdConceptoSIN] varchar(10) NULL,
	CONSTRAINT [PK_s_ConceptosCaja] PRIMARY KEY CLUSTERED ([conc_IdConcepto] ASC)
);
GO

-- Tabla: s_Configuracion
CREATE TABLE [dbo].[s_Configuracion] (
	[conf_UltimoExpediente] bigint NULL,
	[conf_UltimoPago] bigint NULL,
	[conf_idBanco] varchar(10) NULL,
	[conf_MinutosIntervaloCita] int NULL,
	[conf_FmtPreguntaAntecedentesPersonales] text NULL,
	[conf_FmtPreguntaAntecedentesFamiliares] text NULL,
	[conf_NombreEmpresa] varchar(80) NULL,
	[conf_NombreEmpresa2] varchar(80) NULL,
	[conf_NombreEmpresa3] varchar(80) NULL,
	[conf_NombreEmpresa4] varchar(80) NULL,
	[conf_DireccionEmpresa1] varchar(80) NULL,
	[conf_DireccionEmpresa2] varchar(80) NULL,
	[conf_RNC] varchar(15) NULL,
	[conf_Telefono1] varchar(15) NULL,
	[conf_Extension1] varchar(6) NULL,
	[conf_Telefono2] varchar(15) NULL,
	[conf_Extension2] varchar(6) NULL,
	[conf_Beeper] varchar(15) NULL,
	[conf_LogoCompania] image NULL,
	[conf_FmtPreImpReceta] bit NULL DEFAULT (0),
	[conf_FmtPreImpReciboPago] bit NULL DEFAULT (0),
	[conf_FmtPreImpReclamaciones] bit NULL DEFAULT (0),
	[conf_FmtPreImpEgresosCaja] bit NULL DEFAULT (0),
	[conf_FmtPreImpNotas] bit NULL DEFAULT (0),
	[conf_FmtPreImpReferimientos] bit NULL DEFAULT (0),
	[conf_FmtPreImpCertificaciones] bit NULL,
	[conf_FmtPreImpPresupuesto] bit NULL DEFAULT (0),
	[conf_ServidorSQL] varchar(30) NULL,
	[conf_UsuarioSQL] varchar(15) NULL,
	[conf_ClaveSQL] varchar(15) NULL,
	[conf_DescripcionBackup1] varchar(50) NULL,
	[conf_UbicacionBackup1] varchar(50) NULL,
	[conf_UltimoBackup1] smalldatetime NULL,
	[conf_DescripcionBackup2] varchar(50) NULL,
	[conf_UbicacionBackup2] varchar(50) NULL,
	[conf_UltimoBackup2] smalldatetime NULL,
	[conf_DescripcionBackup3] varchar(50) NULL,
	[conf_UbicacionBackup3] varchar(50) NULL,
	[conf_UltimoBackup3] smalldatetime NULL,
	[conf_Email] varchar(50) NULL,
	[conf_Website] varchar(50) NULL,
	[conf_MesesVencPresupuesto] int NULL DEFAULT (1),
	[conf_IdMotivoConsultaDefault] varchar(10) NULL,
	[conf_ReporteFormatoPago] varchar(50) NULL,
	[conf_UltimaReclamacionSeguro] bigint NULL,
	[conf_UltimoPagoSeguro] bigint NULL,
	[conf_FmtPreImpRecetaAnalisis] bit NULL DEFAULT (0),
	[conf_PiePagina] varchar(600) NULL,
	[conf_ReporteFormatoEgreso] varchar(50) NULL,
	[conf_CantLineasSepReceta] int NULL DEFAULT (0),
	[conf_RealizarPago] char(1) NULL,
	[conf_BgColorOpt] varchar(20) NULL,
	[conf_BgColorCon] varchar(20) NULL,
	[conf_BgColorEst] varchar(20) NULL,
	[conf_IdSecCFReclamacion] varchar(10) NULL,
	[conf_ReporteFormatoReceta] varchar(50) NULL,
	[conf_ImpTicketAlPrep] bit NULL,
	[conf_MarginTop] int NULL,
	[conf_NomPasoPrevio] varchar(50) NULL,
	[conf_NocPasoPrevio] varchar(10) NULL,
	[conf_FuenteBDPadron] varchar(5) NULL,
	[conf_DirFotosPadron] varchar(80) NULL,
	[conf_llenarMotivo] bit NULL DEFAULT (0),
	[conf_DirFotosPaciente] varchar(80) NULL,
	[conf_DirImagenesEstudio] varchar(80) NULL,
	[conf_DirImagenesEstudioAlt] varchar(80) NULL,
	[conf_TitMensCita] varchar(100) NULL,
	[conf_ContMensCita] varchar(1500) NULL,
	[conf_MensCitaHTML] bit NULL DEFAULT ((1)),
	[conf_TitMensCump] varchar(100) NULL,
	[conf_ContMensCump] varchar(1500) NULL,
	[conf_MensCumpHTML] bit NULL DEFAULT ((1)),
	[conf_SMTPHost] varchar(50) NULL,
	[conf_SMTPPort] int NULL,
	[conf_SMTPUser] varchar(50) NULL,
	[conf_SMTPPassword] varchar(50) NULL,
	[conf_SMTPSSL] bit NULL DEFAULT ((0)),
	[conf_SMTPHost2] varchar(50) NULL,
	[conf_SMTPPort2] int NULL,
	[conf_SMTPUser2] varchar(50) NULL,
	[conf_SMTPPassword2] varchar(20) NULL,
	[conf_SMTPSSL2] bit NULL DEFAULT ((0)),
	[conf_SMTPHost3] varchar(50) NULL,
	[conf_SMTPPort3] int NULL,
	[conf_SMTPUser3] varchar(50) NULL,
	[conf_SMTPPassword3] varchar(20) NULL,
	[conf_SMTPSSL3] bit NULL DEFAULT ((0)),
	[conf_SMTPHost4] varchar(50) NULL,
	[conf_SMTPPort4] int NULL,
	[conf_SMTPUser4] varchar(50) NULL,
	[conf_SMTPPassword4] varchar(20) NULL,
	[conf_SMTPSSL4] bit NULL DEFAULT ((0)),
	[conf_SMTPHost5] varchar(50) NULL,
	[conf_SMTPPort5] int NULL,
	[conf_SMTPUser5] varchar(50) NULL,
	[conf_SMTPPassword5] varchar(20) NULL,
	[conf_SMTPSSL5] bit NULL DEFAULT ((0)),
	[conf_SMTPFromEmail] varchar(50) NULL,
	[conf_PagosHab] bit NULL DEFAULT ((1)),
	[conf_RecSegHab] bit NULL DEFAULT ((1)),
	[conf_InvHab] bit NULL DEFAULT ((1)),
	[conf_LEspHab] bit NULL DEFAULT ((1)),
	[conf_RepAddHab] bit NULL DEFAULT ((1)),
	[conf_ComLenHab] bit NULL DEFAULT ((1)),
	[conf_DiasAperturaExp] int NULL DEFAULT ((0)),
	[conf_TicketFontFamily] varchar(100) NULL,
	[conf_TicketFontSize] varchar(10) NULL,
	[conf_IdPerfilDoctor] varchar(10) NULL,
	[conf_IdEspecialidad] char(3) NULL,
	[conf_NomCampoAlerta1] varchar(20) NULL,
	[conf_NomCampoAlerta2] varchar(20) NULL,
	[conc_IdConceptoCobroCuota] varchar(10) NULL,
	[conf_OrdenListaEspera] char(1) NULL DEFAULT ('L'),
	[conf_CopiasReciboPago] int NULL DEFAULT ((1)),
	[conf_PagoDoctorConEgreso] bit NULL DEFAULT ((0)),
	[conf_IdConceptoPagoDoctor] varchar(10) NULL,
	[conf_CopiasEgresoCaja] int NULL DEFAULT ((1)),
	[conf_UsarSeguroInterno] bit NULL DEFAULT ((0)),
	[conf_IdSeguroInterno] varchar(10) NULL,
	[conf_IdConceptoAbono] varchar(10) NULL,
	[conf_MontoPagoInicial] decimal(12, 2) NULL,
	[conf_MontoAbono] decimal(12, 2) NULL,
	[conf_ImagenInicial] image NULL,
	[conf_RecetaFontFamily] varchar(100) NULL,
	[conf_RecetaFontSize] varchar(10) NULL,
	[conf_RecetaPLineaBold] bit NULL,
	[conf_LlenarCedula] bit NULL DEFAULT ((1)),
	[conf_LlenarTelefonoCasa] bit NULL DEFAULT ((1)),
	[conf_LlenarCelular] bit NULL DEFAULT ((1)),
	[conf_LlenarFechaNacimiento] bit NULL DEFAULT ((1)),
	[conf_LlenarReferidoPor] bit NULL DEFAULT ((1)),
	[conf_ConectarSIN] bit NULL,
	[conf_BDSin] varchar(50) NULL,
	[conf_ConectarNOM] bit NULL,
	[conf_BDNom] varchar(50) NULL,
	[conf_lblNivPre1] varchar(12) NULL,
	[conf_lblNivPre2] varchar(12) NULL,
	[conf_lblNivPre3] varchar(12) NULL,
	[conf_lblNivPre4] varchar(12) NULL,
	[conf_CaseNombrePac] char(1) NULL,
	[conf_NomDoctorAsi] varchar(20) NULL,
	[conf_ReporteFormatoCert] varchar(50) NULL,
	[conf_UrlSIN] varchar(50) NULL,
	[conf_UrlNOM] varchar(50) NULL,
	[conf_ConectarCIR] bit NULL,
	[conf_BDCir] varchar(50) NULL,
	[conf_UrlCIR] varchar(50) NULL,
	[conf_TicketCommand] varchar(10) NULL,
	[conf_TicketCols] int NULL,
	[conf_TicketLineasAntes] int NULL,
	[conf_TicketLineasDespues] int NULL,
	[conf_TicketLineasNomcia] int NULL,
	[conf_TicketLineasDircia] int NULL,
	[conf_PreCita] bit NULL DEFAULT ((0)),
	[conf_IdPerfilProgQui] varchar(10) NULL,
	[conf_DiasMinPrecita] int NULL DEFAULT ((31)),
	[conf_VariasVisitas] bit NULL,
	[conf_Ciudad] varchar(30) NULL,
	[conf_IdPerfilProgEst] varchar(10) NULL,
	[conf_IdPerfilProgQuiE] varchar(10) NULL,
	[conf_ValEmail] bit NULL,
	[conf_UrlApiValEmail] varchar(100) NULL,
	[conf_KeyValEmail] varchar(100) NULL,
	[conf_NomCampoAlerta3] varchar(20) NULL,
	[conf_NomCampoAlerta4] varchar(20) NULL,
	[conf_CaseDirPac] char(1) NULL,
	[conf_NomProceso] varchar(50) NULL,
	[conf_MinProceso] int NULL,
	[conf_BgColorIniProceso] varchar(20) NULL,
	[conf_BgColorFinProceso] varchar(20) NULL,
	[conf_BgColorExeProceso] varchar(20) NULL,
	[conf_LlenarDiag] bit NULL,
	[conf_LlenarPlan] bit NULL,
	[conf_LlenarTrat] bit NULL,
	[Conf_DspAntPer] bit NULL,
	[Conf_DspAntFam] bit NULL,
	[conf_ReporteFormatoNota] varchar(50) NULL,
	[conf_UrlMED] varchar(50) NULL,
	[conf_UrlEXP] varchar(50) NULL,
	[conf_CodPais] varchar(5) NULL,
	[GenRecCoPago] bit NULL DEFAULT ((0)),
	[AlertaCantServicios] int NULL,
	[conf_CitaConsultorio] bit NULL,
	[conf_RegNroExp] bit NULL DEFAULT ((0)),
	[RepServFac] bit NULL,
	[conf_ReporteFormatoTicket] varchar(50) NULL
);
GO

-- Tabla: s_Configuracion_Alergia
CREATE TABLE [dbo].[s_Configuracion_Alergia] (
	[MinValEritema] float NULL DEFAULT (0),
	[MaxValEritema] float NULL DEFAULT (100),
	[AlertaValEritema] float NULL DEFAULT (20),
	[MinValPapula] float NULL DEFAULT (0),
	[MaxValPapula] float NULL DEFAULT (100),
	[AlertaValPapula] float NULL DEFAULT (20),
	[MinValBronch] float NULL DEFAULT (0),
	[MaxValBronch] float NULL DEFAULT (120),
	[AlertaValBronch] float NULL DEFAULT (80),
	[MinValPronBronch] float NULL DEFAULT (0),
	[MaxValPronBronch] float NULL DEFAULT (120),
	[AlertaValPronBronch] float NULL DEFAULT (80),
	[MinValONE] float NULL DEFAULT (0),
	[MaxValONE] float NULL DEFAULT (150),
	[AlertaValONE] float NULL DEFAULT (25),
	[MinValIgA] float NULL DEFAULT (0),
	[MaxValIgA] float NULL DEFAULT (500),
	[AlertaValIgA] float NULL DEFAULT (170),
	[MinValIgG] float NULL DEFAULT (0),
	[MaxValIgG] float NULL DEFAULT (1500),
	[AlertaValIgG] float NULL DEFAULT (820),
	[MinValIgM] float NULL DEFAULT (0),
	[MaxValIgM] float NULL DEFAULT (200),
	[AlertaValIgM] float NULL DEFAULT (90),
	[MinValIgETotal] float NULL DEFAULT (0),
	[MaxValIgETotal] float NULL DEFAULT (900),
	[AlertaValIgETotal] float NULL DEFAULT (80),
	[MinValIgEEspecifica] float NULL DEFAULT (0),
	[MaxValIgEEspecifica] float NULL DEFAULT (595),
	[AlertaValIgEEspecifica] float NULL DEFAULT (80),
	[MinValTestSudor] float NULL DEFAULT (0),
	[MaxValTestSudor] float NULL DEFAULT (595),
	[AlertaValTestSudor] float NULL DEFAULT (80),
	[MinValPPD] float NULL DEFAULT (0),
	[MaxValPPD] float NULL DEFAULT (100),
	[AlertaValPPD] float NULL DEFAULT (80),
	[FmtPreguntaHistoriaClinica] text NULL,
	[MinValC3] float NULL,
	[MaxValC3] float NULL,
	[AlertaValC3] float NULL,
	[MinValC4] float NULL,
	[MaxValC4] float NULL,
	[AlertaValC4] float NULL
);
GO

-- Tabla: s_Configuracion_Cardiologia
CREATE TABLE [dbo].[s_Configuracion_Cardiologia] (
	[FmtPreguntaHistoriaClinica] text NULL,
	[conf_llenarMotivo] bit NULL DEFAULT ((0)),
	[conf_LlenarDiag] bit NULL DEFAULT ((0)),
	[conf_LlenarPlan] bit NULL DEFAULT ((0)),
	[conf_LlenarTrat] bit NULL DEFAULT ((0)),
	[conf_DspAntPer] bit NULL DEFAULT ((0)),
	[conf_DspAntFam] bit NULL DEFAULT ((0)),
	[Conf_dspRevFisica] bit NULL DEFAULT ((0))
);
GO

-- Tabla: s_Configuracion_Dermatologia
CREATE TABLE [dbo].[s_Configuracion_Dermatologia] (
	[FmtPreguntaHistoriaClinica] text NULL,
	[conf_llenarMotivo] bit NULL DEFAULT ((0)),
	[conf_LlenarDiag] bit NULL DEFAULT ((0)),
	[conf_LlenarPlan] bit NULL DEFAULT ((0)),
	[conf_LlenarTrat] bit NULL DEFAULT ((0)),
	[conf_DspAntPer] bit NULL DEFAULT ((0)),
	[conf_DspAntFam] bit NULL DEFAULT ((0)),
	[Conf_dspRevFisica] bit NULL DEFAULT ((0))
);
GO

-- Tabla: s_Configuracion_General
CREATE TABLE [dbo].[s_Configuracion_General] (
	[conf_FmtPreguntaHistoriaClinica] text NULL,
	[conf_llenarMotivo] bit NULL DEFAULT ((0)),
	[conf_LlenarDiag] bit NULL DEFAULT ((0)),
	[conf_LlenarPlan] bit NULL DEFAULT ((0)),
	[conf_LlenarTrat] bit NULL DEFAULT ((0)),
	[Conf_DspAntPer] bit NULL DEFAULT ((1)),
	[Conf_DspAntFam] bit NULL DEFAULT ((0)),
	[Conf_DspRevFisica] bit NULL DEFAULT ((1))
);
GO

-- Tabla: s_Configuracion_MedInt
CREATE TABLE [dbo].[s_Configuracion_MedInt] (
	[conf_FmtPreguntaHistoriaClinica] text NULL,
	[conf_llenarMotivo] bit NULL DEFAULT ((0)),
	[conf_LlenarDiag] bit NULL DEFAULT ((0)),
	[conf_LlenarPlan] bit NULL DEFAULT ((0)),
	[conf_LlenarTrat] bit NULL DEFAULT ((0)),
	[Conf_DspAntPer] bit NULL DEFAULT ((1)),
	[Conf_DspAntFam] bit NULL DEFAULT ((0)),
	[Conf_DspRevFisica] bit NULL DEFAULT ((1))
);
GO

-- Tabla: s_Configuracion_Neumologia
CREATE TABLE [dbo].[s_Configuracion_Neumologia] (
	[conf_FmtPreguntaHistoriaClinica] text NULL,
	[conf_llenarMotivo] bit NULL DEFAULT ((0)),
	[conf_LlenarDiag] bit NULL DEFAULT ((0)),
	[conf_LlenarPlan] bit NULL DEFAULT ((0)),
	[conf_LlenarTrat] bit NULL DEFAULT ((0)),
	[Conf_DspAntPer] bit NULL DEFAULT ((1)),
	[Conf_DspAntFam] bit NULL DEFAULT ((0)),
	[Conf_DspRevFisica] bit NULL DEFAULT ((1))
);
GO

-- Tabla: s_Configuracion_Odontologia
CREATE TABLE [dbo].[s_Configuracion_Odontologia] (
	[FmtPreguntaHistoriaClinica] text NULL,
	[conf_llenarMotivo] bit NULL DEFAULT ((0)),
	[conf_LlenarDiag] bit NULL DEFAULT ((0)),
	[conf_LlenarPlan] bit NULL DEFAULT ((0)),
	[conf_LlenarTrat] bit NULL DEFAULT ((0)),
	[conf_DspAntPer] bit NULL DEFAULT ((0)),
	[conf_DspAntFam] bit NULL DEFAULT ((0)),
	[Conf_dspRevFisica] bit NULL DEFAULT ((0))
);
GO

-- Tabla: s_Configuracion_Oftalmologia
CREATE TABLE [dbo].[s_Configuracion_Oftalmologia] (
	[conf_ValorMinK] smallmoney NULL DEFAULT (10.0),
	[conf_ValorMaxK] smallmoney NULL DEFAULT (50.0),
	[conf_ValorIncK] smallmoney NULL DEFAULT (0.01),
	[conf_ValorMinESF_CIL] smallmoney NULL DEFAULT (0.25),
	[conf_ValorMaxESF_CIL] smallmoney NULL DEFAULT (20.00),
	[conf_ValorIncESF_CIL] smallmoney NULL DEFAULT (0.25),
	[conf_ValorMinTAP] smallmoney NULL DEFAULT (0),
	[conf_ValorMaxTAP] smallmoney NULL DEFAULT (70),
	[conf_ValorIncTAP] smallmoney NULL DEFAULT (1),
	[conf_ValorMinN] smallmoney NULL DEFAULT (0),
	[conf_ValorMaxN] smallmoney NULL DEFAULT (15),
	[conf_ValorIncN] smallmoney NULL DEFAULT (1),
	[conf_ValorMinDI] smallmoney NULL DEFAULT (30),
	[conf_ValorMaxDI] smallmoney NULL DEFAULT (70),
	[conf_ValorIncDI] smallmoney NULL DEFAULT (1),
	[conf_ValorMinCO] smallmoney NULL DEFAULT (28),
	[conf_ValorMaxCO] smallmoney NULL DEFAULT (70),
	[conf_ValorIncCO] smallmoney NULL DEFAULT (1),
	[conf_ValorMinME] smallmoney NULL DEFAULT (0),
	[conf_ValorMaxME] smallmoney NULL DEFAULT (60),
	[conf_ValorIncME] smallmoney NULL DEFAULT (1),
	[conf_ValorMinEJE] smallmoney NULL DEFAULT (0),
	[conf_ValorMaxEJE] smallmoney NULL DEFAULT (360),
	[conf_ValorIncEJE] smallmoney NULL DEFAULT (1),
	[conf_ValorMinADD] smallmoney NULL DEFAULT (1),
	[conf_ValorMaxADD] smallmoney NULL DEFAULT (3),
	[conf_ValorIncADD] smallmoney NULL DEFAULT (0.25),
	[conf_FmtPreguntaHistoriaClinica] text NULL,
	[conf_FmtPreImpRecetaLente] bit NULL DEFAULT (0),
	[conf_ValorMaxTAPAlerta] smallmoney NULL DEFAULT (22),
	[conf_NomPresionTono] varchar(20) NULL,
	[conf_llenarMotivo] bit NULL DEFAULT (0),
	[conf_llenarSA] bit NULL DEFAULT (0),
	[conf_LlenarFO] bit NULL DEFAULT (0),
	[conf_LlenarDiag] bit NULL DEFAULT (0),
	[conf_LlenarPlan] bit NULL DEFAULT (0),
	[conf_LlenarTrat] bit NULL DEFAULT (0),
	[Conf_DspAntPer] bit NULL DEFAULT ((1)),
	[Conf_DspAntFam] bit NULL DEFAULT ((0)),
	[Conf_DspSAFO] bit NULL DEFAULT ((1)),
	[Conf_DspMOOcu] bit NULL DEFAULT ((0)),
	[Conf_DspDI] bit NULL DEFAULT ((1)),
	[Conf_llenarDI] bit NULL DEFAULT ((1)),
	[Conf_OrdenRefraccion] char(1) NULL DEFAULT ('1'),
	[Conf_CopiarAVaOI] bit NULL,
	[conf_MsgRecetaLente] varchar(300) NULL,
	[conf_AutoCompletarRef] bit NULL DEFAULT ((1))
);
GO

-- Tabla: s_Configuracion_Ortopedia
CREATE TABLE [dbo].[s_Configuracion_Ortopedia] (
	[conf_FmtPreguntaHistoriaClinica] text NULL
);
GO

-- Tabla: s_Configuracion_Otorrino
CREATE TABLE [dbo].[s_Configuracion_Otorrino] (
	[conf_FmtPreguntaHistoriaClinica] text NULL
);
GO

-- Tabla: s_Configuracion_Psicologia
CREATE TABLE [dbo].[s_Configuracion_Psicologia] (
	[conf_FmtPreguntaHistoriaClinica] text NULL,
	[conf_llenarMotivo] bit NULL DEFAULT ((0)),
	[conf_LlenarDiag] bit NULL DEFAULT ((0)),
	[conf_LlenarPlan] bit NULL DEFAULT ((0)),
	[conf_LlenarTrat] bit NULL DEFAULT ((0)),
	[Conf_DspAntPer] bit NULL DEFAULT ((1)),
	[Conf_DspAntFam] bit NULL DEFAULT ((0)),
	[Conf_DspDiag] bit NULL DEFAULT ((0))
);
GO

-- Tabla: s_Configuracion_Terapia
CREATE TABLE [dbo].[s_Configuracion_Terapia] (
	[conf_FmtPreguntaComentario] text NULL,
	[conf_llenarComentario] bit NULL DEFAULT ((0))
);
GO

-- Tabla: s_Configuracion_Urologia
CREATE TABLE [dbo].[s_Configuracion_Urologia] (
	[FmtPreguntaHistoriaClinica] text NULL,
	[conf_llenarMotivo] bit NULL DEFAULT ((0)),
	[conf_LlenarDiag] bit NULL DEFAULT ((0)),
	[conf_LlenarPlan] bit NULL DEFAULT ((0)),
	[conf_LlenarTrat] bit NULL DEFAULT ((0)),
	[conf_DspAntPer] bit NULL DEFAULT ((0)),
	[conf_DspAntFam] bit NULL DEFAULT ((0)),
	[Conf_dspRevFisica] bit NULL DEFAULT ((0))
);
GO

-- Tabla: s_Consultorio
CREATE TABLE [dbo].[s_Consultorio] (
	[IdConsultorio] varchar(10) NOT NULL,
	[NomConsultorio] varchar(50) NULL,
	[BgColor] varchar(20) NULL,
	[Orden] int NULL,
	[Estatus] char(1) NULL DEFAULT ('A'),
	[Preposicion] varchar(5) NULL,
	CONSTRAINT [PK_s_Consultorio] PRIMARY KEY CLUSTERED ([IdConsultorio] ASC)
);
GO

-- Tabla: s_CuadreCaja
CREATE TABLE [dbo].[s_CuadreCaja] (
	[cuac_IdCaja] varchar(10) NOT NULL,
	[cuac_FechaApertura] smalldatetime NOT NULL,
	[cuac_FechaCierre] smalldatetime NOT NULL,
	[cuac_MontoCierre] float NULL,
	[cuac_MontoApertura] float NULL,
	[cuac_cantmon2000] int NULL,
	[cuac_cantmon1000] int NULL,
	[cuac_cantmon500] int NULL,
	[cuac_cantmon100] int NULL,
	[cuac_cantmon50] int NULL,
	[cuac_cantmon25] int NULL,
	[cuac_cantmon20] int NULL,
	[cuac_cantmon10] int NULL,
	[cuac_cantmon5] int NULL,
	[cuac_cantmon1] int NULL,
	[cuac_cantmon001] int NULL,
	[cuac_cuac_MontochkDisponible] float NULL,
	[cuac_MontoDisponible] float NULL,
	[cuac_CantidadPagos] int NULL,
	[cuac_CantidadIngresos] int NULL,
	[cuac_CantidadEgresos] int NULL,
	[cuac_TotalEfectivo] float NULL,
	[cuac_TotalCheque] float NULL,
	[cuac_TotalTarjeta] float NULL,
	[cuac_TotalPago] float NULL,
	[cuac_TotalIngreso] float NULL,
	[cuac_TotalEgreso] float NULL,
	[cuac_TotalADepositar] float NULL,
	[cuac_FechaDeposito] datetime NULL,
	[cuac_TotalDeposito] float NULL,
	[cuac_IdBancoDeposito] varchar(10) NULL,
	[cuac_NumeroCuenta] varchar(20) NULL,
	[cuac_TotalDeposito2] float NULL,
	[cuac_IdBancoDeposito2] varchar(10) NULL,
	[cuac_NumeroCuenta2] varchar(20) NULL,
	[cuac_TotalDeposito3] float NULL,
	[cuac_IdBancoDeposito3] varchar(10) NULL,
	[cuac_NumeroCuenta3] varchar(20) NULL,
	[cuac_IdUsuarioAbrioCaja] varchar(15) NULL,
	[cuac_IdUsuarioCerroCaja] varchar(15) NULL,
	[cuac_Estatus] char(1) NULL DEFAULT ('N'),
	[cuac_ComentarioCuadre] varchar(250) NULL,
	[cuac_cantmon200] int NULL,
	[cuac_TotalTransf] float NULL,
	[cuac_FechaReg] datetime NULL DEFAULT (getdate()),
	CONSTRAINT [PK_s_CuadreCaja] PRIMARY KEY CLUSTERED ([cuac_IdCaja] ASC, [cuac_FechaApertura] ASC)
);
GO

-- Tabla: s_DetalleMovInv
CREATE TABLE [dbo].[s_DetalleMovInv] (
	[Numero] int NOT NULL,
	[Linea] int NOT NULL,
	[CodArt] varchar(15) NULL,
	[Cantidad] float NULL,
	[Costo] float NULL,
	[Comentario] varchar(50) NULL,
	CONSTRAINT [PK_s_DetalleMovInv] PRIMARY KEY CLUSTERED ([Numero] ASC, [Linea] ASC)
);
GO

-- Tabla: s_DetallePago
CREATE TABLE [dbo].[s_DetallePago] (
	[dpag_IdCaja] varchar(10) NOT NULL,
	[dpag_Numero] bigint NOT NULL,
	[dpag_Secuencia] int NOT NULL DEFAULT (1),
	[dpag_IdConcepto] varchar(10) NULL,
	[dpag_Monto] decimal(12, 2) NULL,
	[dpag_CoberturaSeguro] decimal(12, 2) NULL,
	[dpag_RealizadoPor] varchar(15) NULL,
	[dpag_CoberturaSeguroInterno] decimal(12, 2) NULL,
	[dpag_Descripcion] varchar(200) NULL,
	[dpag_Cantidad] int NULL DEFAULT ((1)),
	CONSTRAINT [PK_s_DetallePago] PRIMARY KEY CLUSTERED ([dpag_IdCaja] ASC, [dpag_Numero] ASC, [dpag_Secuencia] ASC)
);
GO

-- Tabla: s_DetallePagoPreHecho
CREATE TABLE [dbo].[s_DetallePagoPreHecho] (
	[Numero] bigint NOT NULL,
	[Secuencia] int NOT NULL,
	[IdConcepto] varchar(10) NULL,
	[Monto] float NULL,
	[CoberturaSeguro] float NULL,
	CONSTRAINT [PK_s_DetallePagoPreHecho] PRIMARY KEY CLUSTERED ([Numero] ASC, [Secuencia] ASC)
);
GO

-- Tabla: s_DetallePoliticaCom
CREATE TABLE [dbo].[s_DetallePoliticaCom] (
	[NumPol] int NOT NULL,
	[IdConcepto] varchar(10) NOT NULL,
	[PrComInterno] decimal(5, 2) NULL,
	[PrComExterno] decimal(5, 2) NULL,
	[PrComSegInterno] decimal(5, 2) NULL,
	[PrComSegExterno] decimal(5, 2) NULL,
	[PrComRef] decimal(5, 2) NULL,
	[PrComAsis] decimal(5, 2) NULL,
	[IdFormaCalculo] varchar(3) NULL,
	[PrComCentro] decimal(5, 2) NULL DEFAULT ((0)),
	[MontoFijo] decimal(12, 2) NULL DEFAULT ((0)),
	CONSTRAINT [PK_s_DetallePoliticaCom] PRIMARY KEY CLUSTERED ([NumPol] ASC, [IdConcepto] ASC)
);
GO

-- Tabla: s_DetallePrePago
CREATE TABLE [dbo].[s_DetallePrePago] (
	[dpag_IdCaja] varchar(10) NOT NULL,
	[dpag_Numero] bigint NOT NULL,
	[dpag_Secuencia] int NOT NULL DEFAULT (1),
	[dpag_IdConcepto] varchar(10) NULL,
	[dpag_Monto] decimal(12, 2) NULL,
	[dpag_CoberturaSeguro] decimal(12, 2) NULL,
	[dpag_RealizadoPor] varchar(15) NULL,
	[dpag_CoberturaSeguroInterno] decimal(12, 2) NULL,
	[dpag_Descripcion] varchar(200) NULL,
	[dpag_Cantidad] int NULL DEFAULT ((1)),
	CONSTRAINT [PK_s_DetallePrePago] PRIMARY KEY CLUSTERED ([dpag_IdCaja] ASC, [dpag_Numero] ASC, [dpag_Secuencia] ASC)
);
GO

-- Tabla: s_DetallePresupuesto
CREATE TABLE [dbo].[s_DetallePresupuesto] (
	[dpre_Numero] bigint NOT NULL,
	[dpre_Secuencia] int NOT NULL DEFAULT (1),
	[dpre_IdConcepto] varchar(10) NULL,
	[dpre_Monto] decimal(12, 2) NULL,
	[dpre_CoberturaSeguro] decimal(12, 2) NULL,
	[dpre_descripcion] varchar(200) NULL,
	[dpre_FechaRequerida] smalldatetime NULL,
	[dpre_CoberturaSeguroInterno] decimal(12, 2) NULL,
	[dpre_Cantidad] int NULL DEFAULT ((1)),
	CONSTRAINT [PK_s_DetallePresupuesto] PRIMARY KEY CLUSTERED ([dpre_Numero] ASC, [dpre_Secuencia] ASC)
);
GO

-- Tabla: s_Diagnostico
CREATE TABLE [dbo].[s_Diagnostico] (
	[diag_IdDiagnostico] varchar(10) NOT NULL,
	[diag_nombre] varchar(150) NULL,
	[diag_Estatus] char(1) NULL DEFAULT ('A'),
	[diag_IdGrupoDiag] varchar(10) NULL,
	[diag_CodSubPatologia] varchar(10) NULL,
	CONSTRAINT [PK_s_Diagnostico] PRIMARY KEY CLUSTERED ([diag_IdDiagnostico] ASC)
);
GO

-- Tabla: s_DiagnosticoEspecialidades
CREATE TABLE [dbo].[s_DiagnosticoEspecialidades] (
	[diae_IdDiagnostico] varchar(10) NOT NULL,
	[diae_IdEspecialidad] char(3) NOT NULL,
	CONSTRAINT [PK_s_DiagnosticoEspecialidades] PRIMARY KEY CLUSTERED ([diae_IdDiagnostico] ASC, [diae_IdEspecialidad] ASC)
);
GO

-- Tabla: s_DiasNoLaborable
CREATE TABLE [dbo].[s_DiasNoLaborable] (
	[dinl_IdDoctor] varchar(15) NOT NULL,
	[dinl_DesdeFecha] smalldatetime NOT NULL,
	[dinl_HastaFecha] smalldatetime NOT NULL,
	[dinl_Tipo] char(1) NULL DEFAULT ('F'),
	[dinl_Descripcion] varchar(50) NULL,
	CONSTRAINT [PK_s_DiasNoLaborable] PRIMARY KEY CLUSTERED ([dinl_IdDoctor] ASC, [dinl_DesdeFecha] ASC, [dinl_HastaFecha] ASC)
);
GO

-- Tabla: s_DirectorioTelefonico
CREATE TABLE [dbo].[s_DirectorioTelefonico] (
	[dirt_Numero] int NOT NULL,
	[dirt_Nombre] varchar(50) NULL,
	[dirt_Ocupacion] varchar(50) NULL,
	[dirt_Telef1] varchar(14) NULL,
	[dirt_Telef2] varchar(14) NULL,
	[dirt_NroFax] varchar(14) NULL,
	[dirt_direccion] varchar(50) NULL,
	[dirt_ciudad] varchar(30) NULL,
	[dirt_Pais] varchar(30) NULL,
	[dirt_email] varchar(50) NULL,
	[dirt_Contacto] varchar(50) NULL,
	[dirt_lugartrabajo] varchar(50) NULL,
	[dirt_teleftrabajo] varchar(14) NULL,
	[dirt_notas] varchar(300) NULL,
	[dirt_RegistradoPor] varchar(15) NULL,
	[dirt_FechaRegistro] smalldatetime NULL,
	[dirt_Estatus] char(1) NULL DEFAULT ('A'),
	CONSTRAINT [PK_s_DirectorioTelefonico] PRIMARY KEY CLUSTERED ([dirt_Numero] ASC)
);
GO

-- Tabla: s_Doctor
CREATE TABLE [dbo].[s_Doctor] (
	[doct_IdDoctor] varchar(15) NOT NULL,
	[doct_Nombre] varchar(50) NULL,
	[doct_Execuator] varchar(15) NULL,
	[doct_PorcientoARecibir] float NULL,
	[doct_IdCajaDefecto] varchar(10) NULL,
	[doct_Estatus] char(1) NULL DEFAULT ('A'),
	[doct_UtilizarSoloCajaDefecto] bit NULL DEFAULT (0),
	[doct_ConsultaDom] bit NULL DEFAULT (0),
	[doct_ConsultaLun] bit NULL DEFAULT (0),
	[doct_ConsultaMar] bit NULL DEFAULT (0),
	[doct_ConsultaMie] bit NULL DEFAULT (0),
	[doct_ConsultaJue] bit NULL DEFAULT (0),
	[doct_ConsultaVie] bit NULL DEFAULT (0),
	[doct_ConsultaSab] bit NULL DEFAULT (0),
	[doct_CirugiaDom] bit NULL DEFAULT (0),
	[doct_CirugiaLun] bit NULL DEFAULT (0),
	[doct_CirugiaMar] bit NULL DEFAULT (0),
	[doct_CirugiaMie] bit NULL DEFAULT (0),
	[doct_CirugiaJue] bit NULL DEFAULT (0),
	[doct_CirugiaVie] bit NULL DEFAULT (0),
	[doct_CirugiaSab] bit NULL DEFAULT (0),
	[doct_Iniciales] varchar(10) NULL,
	[doct_HorIniConDom] varchar(6) NULL,
	[doct_HorFinConDom] varchar(6) NULL,
	[doct_HorIniConLun] varchar(6) NULL,
	[doct_HorFinConLun] varchar(6) NULL,
	[doct_HorIniConMar] varchar(6) NULL,
	[doct_HorFinConMar] varchar(6) NULL,
	[doct_HorIniConMie] varchar(6) NULL,
	[doct_HorFinConMie] varchar(6) NULL,
	[doct_HorIniConJue] varchar(6) NULL,
	[doct_HorFinConJue] varchar(6) NULL,
	[doct_HorIniConVie] varchar(6) NULL,
	[doct_HorFinConVie] varchar(6) NULL,
	[doct_HorIniConSab] varchar(6) NULL,
	[doct_HorFinConSab] varchar(6) NULL,
	[doct_HorIniCirDom] varchar(6) NULL,
	[doct_HorFinCirDom] varchar(6) NULL,
	[doct_HorIniCirLun] varchar(6) NULL,
	[doct_HorFinCirLun] varchar(6) NULL,
	[doct_HorIniCirMar] varchar(6) NULL,
	[doct_HorFinCirMar] varchar(6) NULL,
	[doct_HorIniCirMie] varchar(6) NULL,
	[doct_HorFinCirMie] varchar(6) NULL,
	[doct_HorIniCirJue] varchar(6) NULL,
	[doct_HorFinCirJue] varchar(6) NULL,
	[doct_HorIniCirVie] varchar(6) NULL,
	[doct_HorFinCirVie] varchar(6) NULL,
	[doct_HorIniCirSab] varchar(6) NULL,
	[doct_HorFinCirSab] varchar(6) NULL,
	[doct_HorIniExcDom] varchar(6) NULL,
	[doct_HorFinExcDom] varchar(6) NULL,
	[doct_HorIniExcLun] varchar(6) NULL,
	[doct_HorFinExcLun] varchar(6) NULL,
	[doct_HorIniExcMar] varchar(6) NULL,
	[doct_HorFinExcMar] varchar(6) NULL,
	[doct_HorIniExcMie] varchar(6) NULL,
	[doct_HorFinExcMie] varchar(6) NULL,
	[doct_HorIniExcJue] varchar(6) NULL,
	[doct_HorFinExcJue] varchar(6) NULL,
	[doct_HorIniExcVie] varchar(6) NULL,
	[doct_HorFinExcVie] varchar(6) NULL,
	[doct_HorIniExcSab] varchar(6) NULL,
	[doct_HorFinExcSab] varchar(6) NULL,
	[doct_ManListaEspera] bit NULL DEFAULT ((1)),
	[doct_UsarPorDefecto] bit NULL DEFAULT ((0)),
	[doct_CitaDom] bit NULL DEFAULT ((0)),
	[doct_CitaLun] bit NULL DEFAULT ((0)),
	[doct_CitaMar] bit NULL DEFAULT ((0)),
	[doct_CitaMie] bit NULL DEFAULT ((0)),
	[doct_CitaJue] bit NULL DEFAULT ((0)),
	[doct_CitaVie] bit NULL DEFAULT ((0)),
	[doct_CitaSab] bit NULL DEFAULT ((0)),
	[doct_HorIniCitDom] varchar(6) NULL,
	[doct_HorFinCitDom] varchar(6) NULL,
	[doct_HorIniCitLun] varchar(6) NULL,
	[doct_HorFinCitLun] varchar(6) NULL,
	[doct_HorIniCitMar] varchar(6) NULL,
	[doct_HorFinCitMar] varchar(6) NULL,
	[doct_HorIniCitMie] varchar(6) NULL,
	[doct_HorFinCitMie] varchar(6) NULL,
	[doct_HorIniCitJue] varchar(6) NULL,
	[doct_HorFinCitJue] varchar(6) NULL,
	[doct_HorIniCitVie] varchar(6) NULL,
	[doct_HorFinCitVie] varchar(6) NULL,
	[doct_HorIniCitSab] varchar(6) NULL,
	[doct_HorFinCitSab] varchar(6) NULL,
	[doct_DesEspecialidad] varchar(800) NULL,
	[doct_MinutosIntervaloCita] int NULL DEFAULT ((20)),
	[doct_FmtReceta] varchar(40) NULL,
	[doct_FmtRecetaLente] varchar(40) NULL,
	[doct_FmtRecetaAnalisis] varchar(40) NULL,
	[doct_FmtReferimiento] varchar(40) NULL,
	[doct_FmtCertificacion] varchar(40) NULL,
	[doct_FmtNotaExp] varchar(40) NULL,
	[doct_IdEspecialidad] char(3) NULL,
	[doct_CantCitaDom] int NULL DEFAULT ((0)),
	[doct_CantCitaDomWEB] int NULL DEFAULT ((0)),
	[doct_CantCitaLun] int NULL DEFAULT ((0)),
	[doct_CantCitaLunWEB] int NULL DEFAULT ((0)),
	[doct_CantCitaMar] int NULL DEFAULT ((0)),
	[doct_CantCitaMarWEB] int NULL DEFAULT ((0)),
	[doct_CantCitaMie] int NULL DEFAULT ((0)),
	[doct_CantCitaMieWEB] int NULL DEFAULT ((0)),
	[doct_CantCitaJue] int NULL DEFAULT ((0)),
	[doct_CantCitaJueWEB] int NULL DEFAULT ((0)),
	[doct_CantCitaVie] int NULL DEFAULT ((0)),
	[doct_CantCitaVieWEB] int NULL DEFAULT ((0)),
	[doct_CantCitaSab] int NULL DEFAULT ((0)),
	[doct_CantCitaSabWEB] int NULL DEFAULT ((0)),
	[doct_CantConDom] int NULL DEFAULT ((0)),
	[doct_CantConLun] int NULL DEFAULT ((0)),
	[doct_CantConMar] int NULL DEFAULT ((0)),
	[doct_CantConMie] int NULL DEFAULT ((0)),
	[doct_CantConJue] int NULL DEFAULT ((0)),
	[doct_CantConVie] int NULL DEFAULT ((0)),
	[doct_CantConSab] int NULL DEFAULT ((0)),
	[doct_IdSubEspecialidad] varchar(10) NULL,
	[doct_Foto] image NULL,
	[doct_NumConsultorio] varchar(5) NULL,
	[doct_Telefonos] varchar(50) NULL,
	[doct_Email] varchar(50) NULL,
	[doct_UsarParaEstudio] bit NULL DEFAULT ((0)),
	[doct_NumPolCom] int NULL,
	[doct_EdadMinPac] int NULL DEFAULT ((0)),
	[doct_EdadMaxPac] int NULL DEFAULT ((120)),
	[IdEmpleadoNom] int NULL,
	[doct_Sexo] char(1) NULL,
	[doct_IdCentro] varchar(5) NULL,
	[doct_IdConsultorio] varchar(10) NULL,
	[doct_FechaReg] datetime NULL DEFAULT (getdate()),
	CONSTRAINT [PK_s_Doctor] PRIMARY KEY CLUSTERED ([doct_IdDoctor] ASC)
);
GO

-- Tabla: s_Doctor_Disponibilidad
CREATE TABLE [dbo].[s_Doctor_Disponibilidad] (
	[Fecha] datetime NOT NULL,
	[IdDoctor] varchar(15) NOT NULL,
	[Orden] int NULL,
	[EstadoConsulta] char(1) NULL DEFAULT ('A'),
	[RazonCierre] varchar(200) NULL,
	[CantMaxPacientes] int NULL,
	[UltimoAsignado] bit NULL DEFAULT ((0)),
	[HoraIniConsulta] char(6) NULL,
	[HoraCierreConsulta] char(6) NULL,
	[PacientesAsig] int NULL,
	[UltAsig] datetime NULL,
	[Origen] char(1) NULL DEFAULT ('D'),
	CONSTRAINT [PK_s_Doctor_Disponibilidad] PRIMARY KEY CLUSTERED ([Fecha] ASC, [IdDoctor] ASC)
);
GO

-- Tabla: s_EgresoCaja
CREATE TABLE [dbo].[s_EgresoCaja] (
	[egre_IdCaja] varchar(10) NOT NULL,
	[egre_Numero] bigint NOT NULL,
	[egre_Fecha] smalldatetime NULL,
	[egre_docref] varchar(10) NULL,
	[egre_Nomben] varchar(50) NULL,
	[egre_IdConcepto] varchar(10) NULL,
	[egre_Desmov] varchar(90) NULL,
	[egre_ValMov] float NULL,
	[egre_CodUsr] varchar(15) NULL,
	[egre_Estatus] char(1) NULL DEFAULT ('N'),
	[NumEgresoSin] int NULL,
	CONSTRAINT [PK_s_EgresoCaja] PRIMARY KEY CLUSTERED ([egre_IdCaja] ASC, [egre_Numero] ASC)
);
GO

-- Tabla: s_EntidadReclamadoraSeguro
CREATE TABLE [dbo].[s_EntidadReclamadoraSeguro] (
	[entr_IdEntidad] varchar(10) NOT NULL,
	[entr_Nombre] varchar(50) NULL,
	[entr_UltimaReclamacionSeguro] bigint NULL,
	[entr_UltimoPagoSeguro] bigint NULL,
	[entr_IdSerieCF] char(1) NULL,
	[entr_IdDivisionNegocioCF] char(2) NULL,
	[entr_IdPuntoEmisionCF] char(3) NULL,
	[entr_IdAreaCF] char(3) NULL,
	[entr_Estatus] char(1) NULL,
	CONSTRAINT [PK_s_EntidadReclamadoraSeguro] PRIMARY KEY CLUSTERED ([entr_IdEntidad] ASC)
);
GO

-- Tabla: s_EntidadReferimiento
CREATE TABLE [dbo].[s_EntidadReferimiento] (
	[entr_IdEntidad] varchar(10) NOT NULL,
	[entr_nombre] varchar(50) NULL,
	[entr_TipoEntidad] char(1) NULL DEFAULT ('D'),
	[entr_LugarTrabajo] varchar(50) NULL,
	[entr_Direccion] varchar(90) NULL,
	[entr_Ciudad] varchar(30) NULL,
	[entr_Pais] varchar(30) NULL,
	[entr_Telefono] varchar(14) NULL,
	[entr_Telefono2] varchar(14) NULL,
	[entr_Email] varchar(50) NULL,
	[entr_Estatus] char(1) NULL,
	[entr_IdEspecialidad] varchar(10) NULL,
	[entr_RecibeRef] bit NULL DEFAULT (1),
	[entr_EnviaRef] bit NULL DEFAULT (1),
	[entr_Procedencia] char(1) NULL DEFAULT ('E'),
	[entr_IdDoctor] varchar(15) NULL,
	[entr_NumPolCom] int NULL,
	[entr_DesEspecialidad] varchar(100) NULL,
	CONSTRAINT [PK_s_EntidadReferimiento] PRIMARY KEY CLUSTERED ([entr_IdEntidad] ASC)
);
GO

-- Tabla: s_Especialidad
CREATE TABLE [dbo].[s_Especialidad] (
	[espe_IdEspecialidad] char(3) NOT NULL,
	[espe_Nombre] varchar(50) NULL,
	[espe_PaginaConfiguracion] varchar(50) NULL,
	[espe_PaginaVisita] varchar(50) NULL,
	[espe_PaginaImpresionHistoriaClinica] varchar(50) NULL,
	[espe_Estatus] char(1) NULL DEFAULT ('A'),
	[espe_NombreTabla] varchar(30) NULL,
	CONSTRAINT [PK_s_Especialidad] PRIMARY KEY CLUSTERED ([espe_IdEspecialidad] ASC)
);
GO

-- Tabla: s_EstatusColor
CREATE TABLE [dbo].[s_EstatusColor] (
	[IdEstatus] char(1) NOT NULL,
	[Codigo] varchar(20) NULL,
	[DesEstatusColor] varchar(50) NULL,
	[Orden] int NULL,
	[Estatus] char(1) NULL DEFAULT ('A'),
	CONSTRAINT [PK_s_EstatusColor] PRIMARY KEY CLUSTERED ([IdEstatus] ASC)
);
GO

-- Tabla: s_Estudio
CREATE TABLE [dbo].[s_Estudio] (
	[estu_IdEstudio] varchar(10) NOT NULL,
	[estu_Nombre] varchar(50) NULL,
	[estu_IdEspecialidad] char(3) NULL,
	[estu_SeccionAplica] varchar(15) NULL DEFAULT ('SA'),
	[estu_PagoEmpleado] float NULL,
	[estu_Estatus] char(1) NULL DEFAULT ('A'),
	[estu_Descripcion] text NULL,
	[estu_Html] bit NULL DEFAULT ((1)),
	[estu_ImpGenPac] bit NULL DEFAULT ((1)),
	[estu_Justificar] bit NULL DEFAULT ((1)),
	CONSTRAINT [PK_s_Estudio] PRIMARY KEY CLUSTERED ([estu_IdEstudio] ASC)
);
GO

-- Tabla: s_EstudioAngiografia
CREATE TABLE [dbo].[s_EstudioAngiografia] (
	[agf] varchar(2000) NULL,
	[NumVisita] bigint NOT NULL,
	CONSTRAINT [PK_s_EstudioAngiografia] PRIMARY KEY CLUSTERED ([NumVisita] ASC)
);
GO

-- Tabla: s_EstudioCampoVisual
CREATE TABLE [dbo].[s_EstudioCampoVisual] (
	[cv] varchar(2000) NULL,
	[NumVisita] bigint NOT NULL,
	CONSTRAINT [PK_s_EstudioCampoVisual] PRIMARY KEY CLUSTERED ([NumVisita] ASC)
);
GO

-- Tabla: s_EstudioCatarata
CREATE TABLE [dbo].[s_EstudioCatarata] (
	[k1] int NULL,
	[k2] int NULL,
	[la] int NULL,
	[ca] float NULL,
	[ep1] int NULL,
	[ep2] int NULL,
	[ep3] int NULL,
	[ep4] int NULL,
	[ep5] int NULL,
	[ep6] int NULL,
	[ep7] int NULL,
	[k1OI] int NULL,
	[k2OI] int NULL,
	[laOI] int NULL,
	[caOI] float NULL,
	[NumVisita] bigint NOT NULL,
	[ConstanteOD] decimal(5, 2) NULL,
	[ConstanteOI] decimal(5, 2) NULL,
	CONSTRAINT [PK_s_EstudioCatarata] PRIMARY KEY CLUSTERED ([NumVisita] ASC)
);
GO

-- Tabla: s_EstudioEcografia
CREATE TABLE [dbo].[s_EstudioEcografia] (
	[eco] varchar(2000) NULL,
	[NumVisita] bigint NOT NULL,
	CONSTRAINT [PK_s_EstudioEcografia] PRIMARY KEY CLUSTERED ([NumVisita] ASC)
);
GO

-- Tabla: s_EstudioEstrabismo
CREATE TABLE [dbo].[s_EstudioEstrabismo] (
	[te] varchar(80) NULL,
	[th] varchar(80) NULL,
	[tk] varchar(80) NULL,
	[ct] varchar(80) NULL,
	[ctp] varchar(80) NULL,
	[version1] varchar(80) NULL,
	[version2] varchar(80) NULL,
	[version3] varchar(80) NULL,
	[version4] varchar(80) NULL,
	[NumVisita] bigint NOT NULL,
	CONSTRAINT [PK_s_EstudioEstrabismo] PRIMARY KEY CLUSTERED ([NumVisita] ASC)
);
GO

-- Tabla: s_EstudioGlaucoma
CREATE TABLE [dbo].[s_EstudioGlaucoma] (
	[tg11] int NULL,
	[hora1] char(6) NULL,
	[tg12] int NULL,
	[tg21] int NULL,
	[hora2] char(6) NULL,
	[tg22] int NULL,
	[tg31] int NULL,
	[hora3] char(6) NULL,
	[tg32] int NULL,
	[tg41] int NULL,
	[hora4] char(6) NULL,
	[tg42] int NULL,
	[gradoOD] char(1) NULL,
	[gradoOI] char(1) NULL,
	[comentario] varchar(500) NULL,
	[CampoVisualOD] varchar(80) NULL,
	[CampoVisualOI] varchar(80) NULL,
	[GradoDerOD] char(1) NULL DEFAULT (''),
	[GradoIzqOD] char(1) NULL DEFAULT (''),
	[GradoSupOD] char(1) NULL DEFAULT (''),
	[GradoInfOD] char(1) NULL DEFAULT (''),
	[ExcavacionOD] float NULL,
	[GradoDerOI] char(1) NULL DEFAULT (''),
	[GradoIzqOI] char(1) NULL DEFAULT (''),
	[GradoSupOI] char(1) NULL DEFAULT (''),
	[GradoInfOI] char(1) NULL DEFAULT (''),
	[ExcavacionOI] float NULL,
	[NumVisita] bigint NOT NULL,
	CONSTRAINT [PK_s_EstudioGlaucoma] PRIMARY KEY CLUSTERED ([NumVisita] ASC)
);
GO

-- Tabla: s_EstudioImagenes
CREATE TABLE [dbo].[s_EstudioImagenes] (
	[IdEstudio] varchar(10) NOT NULL,
	[Sec] int NOT NULL,
	[NombreArchivo] varchar(200) NULL,
	[Descripcion] varchar(100) NULL,
	[NumVisita] bigint NOT NULL,
	CONSTRAINT [PK_s_EstudioImagenes] PRIMARY KEY CLUSTERED ([NumVisita] ASC, [IdEstudio] ASC, [Sec] ASC)
);
GO

-- Tabla: s_EstudioImagenes_SinVisita
CREATE TABLE [dbo].[s_EstudioImagenes_SinVisita] (
	[NumeroExpediente] bigint NOT NULL,
	[FechaVisita] datetime NOT NULL,
	[IdEstudio] varchar(10) NOT NULL,
	[Sec] int NOT NULL,
	[NombreArchivo] varchar(100) NULL,
	[Descripcion] varchar(100) NULL,
	CONSTRAINT [PK_s_EstudioImagenes_SinVisita] PRIMARY KEY CLUSTERED ([NumeroExpediente] ASC, [FechaVisita] ASC, [IdEstudio] ASC, [Sec] ASC)
);
GO

-- Tabla: s_Expediente
CREATE TABLE [dbo].[s_Expediente] (
	[expe_NumeroExpediente] bigint NOT NULL,
	[expe_Nombres] varchar(30) NULL,
	[expe_Apellidos] varchar(30) NULL,
	[expe_Sexo] char(1) NULL,
	[expe_EstadoCivil] char(1) NULL,
	[expe_FechaNacimiento] datetime NULL,
	[expe_Direccion] varchar(100) NULL,
	[expe_Sector] varchar(50) NULL,
	[expe_Ciudad] varchar(50) NULL,
	[expe_TelefonoCasa] varchar(15) NULL,
	[expe_TelefonoOficina] varchar(15) NULL,
	[expe_ExtensionOficina] varchar(15) NULL,
	[expe_Celular] varchar(15) NULL,
	[expe_Cedula] char(15) NULL,
	[expe_Ocupacion] varchar(30) NULL,
	[expe_LugarNacimiento] varchar(50) NULL,
	[expe_Lee] int NULL DEFAULT (1),
	[expe_Escribe] int NULL DEFAULT (1),
	[expe_Religion] varchar(20) NULL,
	[expe_ReferidoPor] varchar(100) NULL,
	[expe_AntecedentesPersonales] varchar(2000) NULL,
	[expe_AntecedentesFamiliares] varchar(2000) NULL,
	[expe_IdSeguroMedico] varchar(10) NULL,
	[expe_NombreSeguro] varchar(30) NULL,
	[expe_IdPlanSeguro] int NULL DEFAULT (1),
	[expe_NumeroAfiliado] varchar(15) NULL,
	[expe_NumeroPoliza] varchar(15) NULL,
	[expe_Observacion] varchar(2000) NULL,
	[expe_TieneGlaucoma] bit NULL DEFAULT (0),
	[expe_IdDoctor] varchar(15) NULL,
	[expe_FechaCreacion] smalldatetime NULL,
	[expe_IdUsuarioRegistro] varchar(15) NULL,
	[expe_Email] varchar(50) NULL,
	[expe_Estatus] char(1) NULL DEFAULT ('A'),
	[expe_ArchivoFoto] varchar(80) NULL,
	[expe_ProblemaRetina] bit NULL DEFAULT (0),
	[expe_Escolaridad] varchar(20) NULL,
	[expe_NombreMadre] varchar(50) NULL,
	[expe_NombrePadre] varchar(50) NULL,
	[expe_SecuenciaAnual] int NULL,
	[expe_StsEmailCump] char(1) NULL,
	[expe_FechaEmailCump] datetime NULL,
	[expe_ConfEmailUsadaCump] int NULL,
	[expe_Facebook] varchar(50) NULL,
	[expe_Twitter] varchar(50) NULL,
	[IdOrig] int NULL,
	[expe_EstatusColor] char(1) NULL,
	[expe_Alerta3] bit NULL,
	[expe_Alerta4] bit NULL,
	[expe_CodCiu] varchar(5) NULL,
	[expe_CodPais] varchar(5) NULL,
	[expe_Procedencia] char(1) NULL DEFAULT ('I'),
	[expe_TipoId] char(1) NULL DEFAULT ('C'),
	[expe_FecIniProceso] datetime NULL,
	[expe_CantProc] tinyint NULL,
	CONSTRAINT [PK_Expediente] PRIMARY KEY CLUSTERED ([expe_NumeroExpediente] ASC)
);
GO

-- Tabla: s_ExpedientePoliza
CREATE TABLE [dbo].[s_ExpedientePoliza] (
	[NumeroExpediente] bigint NOT NULL,
	[NumPoliza] int NOT NULL,
	[IdRelacion] varchar(5) NULL,
	CONSTRAINT [PK_s_ExpedientePoliza] PRIMARY KEY CLUSTERED ([NumeroExpediente] ASC, [NumPoliza] ASC)
);
GO

-- Tabla: s_GrupoDiagnostico
CREATE TABLE [dbo].[s_GrupoDiagnostico] (
	[IdGrupoDiag] varchar(10) NOT NULL,
	[NomGrupoDiag] varchar(150) NULL,
	[CodPatologia] varchar(10) NULL,
	[Estatus] char(1) NULL,
	[MostrarAlerta] bit NULL DEFAULT ((0)),
	[MensajeAlerta] varchar(200) NULL,
	CONSTRAINT [PK_s_GrupoDiagnostico] PRIMARY KEY CLUSTERED ([IdGrupoDiag] ASC)
);
GO

-- Tabla: s_GrupoMotivo
CREATE TABLE [dbo].[s_GrupoMotivo] (
	[IdGrupoMotivo] varchar(10) NOT NULL,
	[NomGrupoMotivo] varchar(50) NULL,
	[Estatus] char(1) NULL DEFAULT ('A'),
	CONSTRAINT [PK_s_GrupoMotivo] PRIMARY KEY CLUSTERED ([IdGrupoMotivo] ASC)
);
GO

-- Tabla: s_GrupoRecetaPrehecha
CREATE TABLE [dbo].[s_GrupoRecetaPrehecha] (
	[IdGrupoReceta] varchar(10) NOT NULL,
	[Nombre] varchar(50) NULL,
	[Descripcion] varchar(800) NULL,
	[Estatus] char(1) NULL DEFAULT ('A'),
	[FechaReg] datetime NULL DEFAULT (getdate()),
	[RegPor] varchar(15) NULL,
	[UltMod] datetime NULL,
	[ModPor] varchar(15) NULL,
	CONSTRAINT [PK_s_GrupoRecetaPrehecha] PRIMARY KEY CLUSTERED ([IdGrupoReceta] ASC)
);
GO

-- Tabla: s_GrupoRecetaPrehecha_Recetas
CREATE TABLE [dbo].[s_GrupoRecetaPrehecha_Recetas] (
	[IdGrupoReceta] varchar(10) NOT NULL,
	[IdRecetaPrehecha] varchar(10) NOT NULL,
	[Orden] int NULL,
	CONSTRAINT [PK_s_GrupoRecetaPrehecha_Recetas] PRIMARY KEY CLUSTERED ([IdGrupoReceta] ASC, [IdRecetaPrehecha] ASC)
);
GO

-- Tabla: s_HeaderMovInv
CREATE TABLE [dbo].[s_HeaderMovInv] (
	[Numero] int NOT NULL,
	[codtmov] varchar(10) NULL,
	[Fecha] smalldatetime NULL,
	[Descripcion] varchar(50) NULL,
	[Valor] float NULL,
	[RegPor] varchar(15) NULL,
	[Estatus] char(1) NULL,
	[IdSuplidor] varchar(10) NULL,
	[IdDoctor] varchar(15) NULL,
	[NumeroExpediente] bigint NULL,
	[Referencia] varchar(10) NULL,
	[IdSeguro] varchar(10) NULL,
	[FechaReg] datetime NULL DEFAULT (getdate()),
	CONSTRAINT [PK_s_HeaderMovInv] PRIMARY KEY CLUSTERED ([Numero] ASC)
);
GO

-- Tabla: s_HeaderPago
CREATE TABLE [dbo].[s_HeaderPago] (
	[hpag_IdCaja] varchar(10) NOT NULL,
	[hpag_Numero] bigint NOT NULL,
	[hpag_Fecha] smalldatetime NULL,
	[hpag_Hora] varchar(6) NULL,
	[hpag_NumeroExpediente] bigint NULL,
	[hpag_Nombre] varchar(30) NULL,
	[hpag_Apellido] varchar(30) NULL,
	[hpag_Desmov] varchar(50) NULL,
	[hpag_Monto] decimal(12, 2) NULL,
	[hpag_CoberturaSeguro] decimal(12, 2) NULL,
	[hpag_Descuento] decimal(12, 2) NULL DEFAULT ((0)),
	[hpag_IdSeguro] varchar(10) NULL,
	[hpag_IdPlanSeguro] int NULL,
	[hpag_NumeroAfiliado] varchar(15) NULL,
	[hpag_NumeroPoliza] varchar(15) NULL,
	[hpag_NumeroAutorizacion] varchar(25) NULL,
	[hpag_TipoPago] char(1) NULL,
	[hpag_ValorRecibidoEfectivo] decimal(12, 2) NULL,
	[hpag_ValorRecibidoCheque] decimal(12, 2) NULL,
	[hpag_NumeroCheque] varchar(10) NULL,
	[hpag_ValorRecibidoTarjeta] decimal(12, 2) NULL,
	[hpag_NumeroTarjeta] varchar(16) NULL,
	[hpag_NumeroAprobacionTarjeta] varchar(10) NULL,
	[hpag_IdUsuario] varchar(15) NULL,
	[hpag_Estatus] char(1) NULL DEFAULT ('N'),
	[hpag_NumeroReclamacionSeguro] bigint NULL,
	[hpag_NumeroPagoSeguro] bigint NULL DEFAULT (0),
	[hpag_IdDoctor] varchar(15) NULL,
	[hpag_NumeroNotaCreditoSeguro] bigint NULL,
	[hpag_RazonAnulacion] varchar(100) NULL,
	[hpag_Modif] bit NULL DEFAULT (0),
	[hpag_ModPor] varchar(15) NULL,
	[hpag_FecMod] datetime NULL,
	[hpag_IdSecCF] varchar(10) NULL,
	[hpag_NCF] varchar(19) NULL,
	[hpag_Procedencia] char(1) NULL,
	[hpag_IdEntidadRef] varchar(10) NULL,
	[hpag_IdDoctor2] varchar(15) NULL,
	[hpag_ValorRecibidoTransf] decimal(12, 2) NULL,
	[hpag_IdBancoTransf] varchar(10) NULL,
	[hpag_RefTransf] varchar(10) NULL,
	[hpag_TieneSegInterno] bit NULL DEFAULT ((0)),
	[hpag_NumeroPolizaInterno] int NULL,
	[hpag_IdRelacion] varchar(5) NULL,
	[hpag_IdPlanSeguroInterno] int NULL,
	[hpag_CoberturaSeguroInterno] decimal(12, 2) NULL,
	[hpag_FechaAut] datetime NULL,
	[hpag_Rnc] varchar(11) NULL,
	[hpag_NomEmpresa] varchar(80) NULL,
	[IdFacSin] int NULL,
	[NumCobroSin] int NULL,
	[hpag_MontoAvance] decimal(12, 2) NULL DEFAULT ((0)),
	[hpag_IdCarrier] varchar(10) NULL,
	[hpag_IdTipoTarjeta] varchar(10) NULL,
	[hpag_MontoPagado] decimal(12, 2) NULL,
	[hpag_FechaSaldo] datetime NULL,
	[hpag_FecVenNCF] datetime NULL,
	[hpag_FechaReg] datetime NULL DEFAULT (getdate()),
	[hpag_MontoDebito] decimal(12, 2) NULL DEFAULT ((0)),
	[hpag_NCFAsig] bit NULL DEFAULT ((0)),
	[NumCirugia] int NULL,
	[hpag_CobradoPor] varchar(15) NULL,
	[NumRecOrigen] bigint NULL,
	CONSTRAINT [PK_s_HeaderPago] PRIMARY KEY CLUSTERED ([hpag_IdCaja] ASC, [hpag_Numero] ASC)
);
GO

-- Tabla: s_HeaderPago_Descuento
CREATE TABLE [dbo].[s_HeaderPago_Descuento] (
	[hpag_IdCaja] varchar(10) NOT NULL,
	[hpag_Numero] bigint NOT NULL,
	[hpag_Descuento] decimal(12, 2) NULL DEFAULT ((0)),
	[hpag_IdSeguro] varchar(10) NULL,
	[hpag_NumeroReclamacionSeguro] bigint NULL,
	[hpag_MontoPagado] decimal(12, 2) NULL DEFAULT ((0)),
	[hpag_MontoCredito] decimal(12, 2) NULL DEFAULT ((0)),
	[hpag_NumeroPagoSeguro] bigint NULL,
	[hpag_NumeroNotaCreditoSeguro] bigint NULL,
	[hpag_FechaSaldo] datetime NULL,
	CONSTRAINT [PK_s_HeaderPago_Descuento] PRIMARY KEY CLUSTERED ([hpag_IdCaja] ASC, [hpag_Numero] ASC)
);
GO

-- Tabla: s_HeaderPagoPreHecho
CREATE TABLE [dbo].[s_HeaderPagoPreHecho] (
	[Numero] bigint NOT NULL,
	[Descripcion] varchar(50) NULL,
	[Monto] float NULL,
	[CoberturaSeguro] float NULL,
	[Estatus] char(1) NULL DEFAULT ('A'),
	CONSTRAINT [PK_s_HeaderPagoPreHecho] PRIMARY KEY CLUSTERED ([Numero] ASC)
);
GO

-- Tabla: s_HeaderPoliticaCom
CREATE TABLE [dbo].[s_HeaderPoliticaCom] (
	[NumPol] int NOT NULL,
	[NomPolitica] varchar(50) NULL,
	[RegPor] varchar(15) NULL,
	[Estatus] char(1) NULL DEFAULT ('A'),
	CONSTRAINT [PK_s_HeaderPoliticaCom] PRIMARY KEY CLUSTERED ([NumPol] ASC)
);
GO

-- Tabla: s_HeaderPrePago
CREATE TABLE [dbo].[s_HeaderPrePago] (
	[hpag_IdCaja] varchar(10) NOT NULL,
	[hpag_Numero] bigint NOT NULL,
	[hpag_Fecha] smalldatetime NULL,
	[hpag_Hora] varchar(6) NULL,
	[hpag_NumeroExpediente] bigint NULL,
	[hpag_Nombre] varchar(30) NULL,
	[hpag_Apellido] varchar(30) NULL,
	[hpag_Desmov] varchar(50) NULL,
	[hpag_Monto] decimal(12, 2) NULL,
	[hpag_CoberturaSeguro] decimal(12, 2) NULL,
	[hpag_Descuento] decimal(12, 2) NULL DEFAULT ((0)),
	[hpag_IdSeguro] varchar(10) NULL,
	[hpag_IdPlanSeguro] int NULL,
	[hpag_NumeroAfiliado] char(15) NULL,
	[hpag_NumeroPoliza] char(15) NULL,
	[hpag_NumeroAutorizacion] varchar(25) NULL,
	[hpag_TipoPago] char(1) NULL,
	[hpag_ValorRecibidoEfectivo] decimal(12, 2) NULL,
	[hpag_ValorRecibidoCheque] decimal(12, 2) NULL,
	[hpag_NumeroCheque] varchar(10) NULL,
	[hpag_ValorRecibidoTarjeta] decimal(12, 2) NULL,
	[hpag_NumeroTarjeta] varchar(16) NULL,
	[hpag_NumeroAprobacionTarjeta] varchar(10) NULL,
	[hpag_IdUsuario] varchar(15) NULL,
	[hpag_Estatus] char(1) NULL DEFAULT ('N'),
	[hpag_IdDoctor] varchar(15) NULL,
	[hpag_Procedencia] char(1) NULL,
	[hpag_IdSecCF] varchar(10) NULL,
	[hpag_NCF] varchar(19) NULL,
	[hpag_IdEntidadRef] varchar(10) NULL,
	[hpag_IdDoctor2] varchar(15) NULL,
	[hpag_ValorRecibidoTransf] decimal(12, 2) NULL,
	[hpag_IdBancoTransf] varchar(10) NULL,
	[hpag_RefTransf] varchar(10) NULL,
	[hpag_TieneSegInterno] bit NULL DEFAULT ((0)),
	[hpag_NumeroPolizaInterno] int NULL,
	[hpag_IdRelacion] varchar(5) NULL,
	[hpag_IdPlanSeguroInterno] int NULL,
	[hpag_CoberturaSeguroInterno] decimal(12, 2) NULL,
	[hpag_FechaAut] datetime NULL,
	[hpag_Rnc] varchar(11) NULL,
	[hpag_NomEmpresa] varchar(80) NULL,
	[hpag_MontoAvance] decimal(12, 2) NULL DEFAULT ((0)),
	[hpag_IdCarrier] varchar(10) NULL,
	[hpag_IdTipoTarjeta] varchar(10) NULL,
	[hpag_FechaReg] datetime NULL DEFAULT (getdate()),
	[hpag_NCFAsig] bit NULL DEFAULT ((0)),
	CONSTRAINT [PK_s_HeaderPrePago] PRIMARY KEY CLUSTERED ([hpag_IdCaja] ASC, [hpag_Numero] ASC)
);
GO

-- Tabla: s_HeaderPrePago_Descuento
CREATE TABLE [dbo].[s_HeaderPrePago_Descuento] (
	[hpag_IdCaja] varchar(10) NOT NULL,
	[hpag_Numero] bigint NOT NULL,
	[hpag_Descuento] decimal(12, 2) NULL,
	[hpag_IdSeguro] varchar(10) NULL,
	[hpag_NumeroReclamacionSeguro] bigint NULL,
	[hpag_MontoPagado] decimal(12, 2) NULL DEFAULT ((0)),
	[hpag_MontoCredito] decimal(12, 2) NULL DEFAULT ((0)),
	CONSTRAINT [PK_s_HeaderPrePago_Descuento] PRIMARY KEY CLUSTERED ([hpag_IdCaja] ASC, [hpag_Numero] ASC)
);
GO

-- Tabla: s_HeaderPresupuesto
CREATE TABLE [dbo].[s_HeaderPresupuesto] (
	[hpre_Numero] bigint NOT NULL,
	[hpre_Fecha] smalldatetime NULL,
	[hpre_IdDoctor] varchar(15) NULL,
	[hpre_NumeroExpediente] bigint NULL,
	[hpre_Nombre] varchar(30) NULL,
	[hpre_Apellido] varchar(30) NULL,
	[hpre_IdSeguro] varchar(10) NULL,
	[hpre_IdPlanSeguro] int NULL,
	[hpre_NumeroAfiliado] varchar(15) NULL,
	[hpre_NumeroPoliza] varchar(15) NULL,
	[hpre_NumeroAutorizacion] varchar(25) NULL,
	[hpre_Descripcion] varchar(350) NULL,
	[hpre_Monto] decimal(12, 2) NULL,
	[hpre_CoberturaSeguro] decimal(12, 2) NULL,
	[hpre_Descuento] decimal(12, 2) NULL DEFAULT ((0)),
	[hpre_PreparadoPor] varchar(15) NULL,
	[hpre_FechaVencimiento] smalldatetime NULL,
	[hpre_Estatus] char(1) NULL DEFAULT ('N'),
	[hpre_FechaAprobacion] smalldatetime NULL,
	[hpre_TieneSegInterno] bit NULL DEFAULT ((0)),
	[hpre_NumeroPolizaInterno] int NULL,
	[hpre_IdRelacion] varchar(5) NULL,
	[hpre_CoberturaSeguroInterno] decimal(12, 2) NULL,
	[hpre_MontoPagoInicial] decimal(12, 2) NULL,
	[hpre_MontoAbono] decimal(12, 2) NULL,
	[hpre_MontoAPagar] decimal(12, 2) NULL,
	[hpre_PrDsc] decimal(5, 2) NULL DEFAULT ((0)),
	[hpre_Cerradopor] varchar(15) NULL,
	[hpre_ComentarioCierre] varchar(500) NULL,
	[hpre_FecCierre] datetime NULL,
	[hpre_FechaReg] datetime NULL DEFAULT (getdate()),
	CONSTRAINT [PK_s_HeaderPresupuesto] PRIMARY KEY CLUSTERED ([hpre_Numero] ASC)
);
GO

-- Tabla: s_HeaderPresupuestoSeguimiento
CREATE TABLE [dbo].[s_HeaderPresupuestoSeguimiento] (
	[hprs_Numero] bigint NOT NULL,
	[hprs_Fecha] smalldatetime NOT NULL,
	[hprs_Tipo] char(1) NOT NULL,
	[hprs_Descripcion] varchar(50) NULL,
	[hprs_IdResponsable] varchar(15) NULL,
	[hprs_Comentario] varchar(50) NULL,
	[hprs_Estatus] char(1) NULL DEFAULT ('P'),
	CONSTRAINT [PK_s_HeaderPresupuestoSeguimiento] PRIMARY KEY CLUSTERED ([hprs_Numero] ASC, [hprs_Fecha] ASC, [hprs_Tipo] ASC)
);
GO

-- Tabla: s_ListaEspecial
CREATE TABLE [dbo].[s_ListaEspecial] (
	[lise_IdLista] varchar(10) NOT NULL,
	[lise_nombre] varchar(200) NULL,
	[lise_Restringida] bit NULL DEFAULT (0),
	[lise_estatus] char(1) NULL DEFAULT ('A'),
	[lise_TipoLista] char(1) NULL DEFAULT ('P'),
	[lise_DesProc] text NULL,
	[lise_Html] bit NULL DEFAULT ((1)),
	[lise_ImpGenPac] bit NULL DEFAULT ((1)),
	[lise_Justificar] bit NULL DEFAULT ((1)),
	[lise_IdRecetaPreHecha] varchar(10) NULL,
	[lise_Cups] varchar(20) NULL,
	CONSTRAINT [PK_s_ListaEspecial] PRIMARY KEY CLUSTERED ([lise_IdLista] ASC)
);
GO

-- Tabla: s_ListaEspecialPacientes
CREATE TABLE [dbo].[s_ListaEspecialPacientes] (
	[lisp_IdListaEspecial] varchar(10) NOT NULL,
	[lisp_NumeroExpediente] bigint NOT NULL,
	[lisp_FechaRegistro] smalldatetime NOT NULL,
	[lisp_Comentario] varchar(400) NULL,
	[lisp_FechaRequerida] smalldatetime NULL,
	[lisp_FechaPlanificada] smalldatetime NULL,
	[lisp_horaPlanificada] char(6) NULL,
	[lisp_IdDoctor] varchar(15) NULL,
	[lisp_RegistradoPor] varchar(15) NULL,
	[lisp_FechaSalida] smalldatetime NULL,
	[lisp_Estatus] char(1) NULL DEFAULT ('A'),
	[lisp_IdQuirofano] varchar(10) NULL,
	[lisp_HoraInicio] varchar(6) NULL,
	[lisp_HoraFin] varchar(6) NULL,
	[lisp_DesProc] text NULL,
	[lisp_IdSeguro] varchar(10) NULL,
	[lisp_NumAut] varchar(25) NULL,
	[lisp_Lente] varchar(30) NULL,
	[lisp_Seguimiento] varchar(2000) NULL,
	[lisp_Ojo] char(2) NULL,
	[lisp_DifAPagar] float NULL,
	[lisp_Microscopia] int NULL,
	[lisp_TipoGota] varchar(20) NULL,
	[lisp_Cirujano] varchar(30) NULL,
	[lisp_Anestesiologo] varchar(30) NULL,
	[lisp_Ayudante] varchar(30) NULL,
	[NumLista] bigint NOT NULL,
	[lisp_Cobertura] decimal(9, 2) NULL,
	[lisp_Monto] float NULL,
	[lisp_PorcCobert] float NULL,
	[lisp_AjusteCobert] decimal(9, 2) NULL,
	[NumRecIng] bigint NULL,
	[CerradoPor] varchar(15) NULL,
	[FechaCierre] datetime NULL,
	[EstatusProceso] char(1) NULL,
	[NumeroAfiliado] varchar(15) NULL,
	[AutorizadoPor] varchar(30) NULL,
	[lisp_EstatusColor] char(1) NULL,
	[IdCajaRecIng] varchar(10) NULL,
	[AsignadoA] varchar(15) NULL,
	[lisp_IdDoctor2] varchar(15) NULL,
	[lisp_UltMod] datetime NULL,
	[lisp_ModPor] varchar(15) NULL,
	[TipoCita] char(1) NULL,
	[NumCita] bigint NULL,
	[lisp_IdPlanSeguro] int NULL,
	[lisp_Contratante] varchar(100) NULL,
	[lisp_NumAutCierre] varchar(20) NULL,
	[lisp_RepSeguro] varchar(50) NULL,
	[NumCot] bigint NULL,
	[lisp_IdAnestesiologo] varchar(15) NULL,
	[lisp_IdEntidadRef] varchar(10) NULL,
	CONSTRAINT [PK_s_ListaEspecialPacientes] PRIMARY KEY CLUSTERED ([NumLista] ASC)
);
GO

-- Tabla: s_ListaEspecialPacientes_Actividades
CREATE TABLE [dbo].[s_ListaEspecialPacientes_Actividades] (
	[NumAct] bigint NOT NULL,
	[NumLista] bigint NOT NULL,
	[Estatus] char(1) NOT NULL,
	[Fecha] smalldatetime NOT NULL DEFAULT (getdate()),
	[Descripcion] varchar(600) NULL,
	[FechaRec] smalldatetime NULL,
	[FechaEjec] smalldatetime NULL,
	[EstatusEjec] char(1) NULL DEFAULT ('P'),
	[AsignadoA] varchar(15) NULL,
	[ComentarioEjec] varchar(200) NULL,
	CONSTRAINT [PK_s_ListaEspecialPacientes_Actividades] PRIMARY KEY CLUSTERED ([NumAct] ASC)
);
GO

-- Tabla: s_ListaEspecialPacientes_Documentos
CREATE TABLE [dbo].[s_ListaEspecialPacientes_Documentos] (
	[NumLista] bigint NOT NULL,
	[Sec] int NOT NULL,
	[NombreArchivo] varchar(200) NULL,
	[Descripcion] varchar(100) NULL,
	CONSTRAINT [PK_s_ListaEspecialPacientes_Documentos] PRIMARY KEY CLUSTERED ([NumLista] ASC, [Sec] ASC)
);
GO

-- Tabla: s_ListaPaciente
CREATE TABLE [dbo].[s_ListaPaciente] (
	[lisp_IdDoctor] varchar(15) NOT NULL,
	[lisp_Fecha] smalldatetime NOT NULL,
	[lisp_Secuencia] int NOT NULL,
	[lisp_NumeroExpediente] bigint NULL,
	[lisp_Nombre] varchar(30) NULL,
	[lisP_Apellido] varchar(30) NULL,
	[lisp_Comentario] varchar(150) NULL,
	[lisp_IdMotivo] varchar(10) NULL,
	[lisp_IdSeguro] varchar(10) NULL,
	[lisp_IdPlanSeguro] int NULL,
	[lisp_NumeroAfiliado] varchar(15) NULL,
	[lisp_NumeroPoliza] varchar(15) NULL,
	[lisp_NumeroAutorizacion] varchar(25) NULL,
	[lisp_IdCaja] varchar(10) NULL,
	[lisp_NumeroPago] bigint NULL,
	[lisp_ComentarioConsulta] varchar(50) NULL,
	[lisp_Estatus] char(1) NULL DEFAULT ('E'),
	[lisp_HoraLlegada] char(6) NULL,
	[lisp_HoraFinConsulta] char(6) NULL,
	[lisp_HoraPago] char(6) NULL,
	[lisp_IdEntidadRef] varchar(10) NULL,
	[lisp_RegPor] varchar(15) NULL,
	[lisp_StsPag] char(1) NULL DEFAULT ('E'),
	[lisp_StsOpt] char(1) NULL DEFAULT ('E'),
	[lisp_HoraFinOpt] char(6) NULL,
	[lisp_ComentarioOpt] varchar(50) NULL,
	[lisp_StsEst] char(1) NULL DEFAULT ('E'),
	[lisp_HoraFinEst] char(6) NULL,
	[lisp_ComentarioEst] varchar(50) NULL,
	[lisp_UsrRegOpt] varchar(15) NULL,
	[NumVisita] bigint NULL,
	[IdOrig] int NULL,
	[NumLista] bigint NOT NULL,
	[lisp_IdCajaEgreso] varchar(10) NULL,
	[lisp_NumeroEgreso] bigint NULL,
	[lisp_IdDoctorInter] varchar(15) NULL,
	[lisp_NomProceso] varchar(50) NULL,
	[lisp_EstatusProceso] char(1) NULL,
	[lisp_HoraIniProceso] char(6) NULL,
	[lisp_HoraFinProceso] char(6) NULL,
	[lisp_UsrIniProceso] varchar(15) NULL,
	[lisp_UsrFinProceso] varchar(15) NULL,
	[lisp_MinProceso] int NULL,
	[lisp_HoraLlamada] char(6) NULL,
	[lisp_HoraIniConsulta] char(6) NULL,
	[lisp_IdConsultorio] varchar(10) NULL,
	CONSTRAINT [PK_s_ListaPaciente] PRIMARY KEY CLUSTERED ([NumLista] ASC)
);
GO

-- Tabla: s_ListaServicios
CREATE TABLE [dbo].[s_ListaServicios] (
	[liss_IdListaServicio] varchar(10) NOT NULL,
	[liss_Nombre] varchar(50) NULL,
	[liss_Estatus] char(1) NULL DEFAULT ('A'),
	CONSTRAINT [PK_s_ListaServicios] PRIMARY KEY CLUSTERED ([liss_IdListaServicio] ASC)
);
GO

-- Tabla: s_Log
CREATE TABLE [dbo].[s_Log] (
	[Numero] bigint NOT NULL,
	[FechaHora] datetime NULL,
	[IdUsuario] varchar(15) NULL,
	[Entidad] varchar(15) NULL,
	[Referencia] varchar(15) NULL,
	[Accion] char(1) NULL,
	[Descripcion] varchar(100) NULL,
	CONSTRAINT [PK_s_Log] PRIMARY KEY CLUSTERED ([Numero] ASC)
);
GO

-- Tabla: s_Medicina
CREATE TABLE [dbo].[s_Medicina] (
	[medi_IdMedicina] varchar(10) NOT NULL,
	[medi_Nombre] varchar(50) NOT NULL,
	[medi_PrescripcionComun] varchar(1000) NULL,
	[medi_Estatus] char(1) NULL DEFAULT ('A'),
	[medi_NombreGenerico] varchar(100) NULL,
	[medi_Indicacion] varchar(100) NULL,
	[medi_Contraindicaciones] varchar(100) NULL,
	[medi_Laboratorio] varchar(50) NULL,
	[medi_Presentacion] varchar(100) NULL,
	[medi_Descripcion] varchar(100) NULL,
	[medi_IdEspecialidad] char(3) NOT NULL DEFAULT ('OFT'),
	CONSTRAINT [PK_s_Medicina] PRIMARY KEY CLUSTERED ([medi_IdEspecialidad] ASC, [medi_IdMedicina] ASC)
);
GO

-- Tabla: s_MotivoConsulta
CREATE TABLE [dbo].[s_MotivoConsulta] (
	[moti_IdMotivo] varchar(10) NOT NULL,
	[moti_Nombre] varchar(50) NULL,
	[moti_IdServicio] varchar(10) NULL,
	[moti_Estatus] char(1) NULL DEFAULT ('A'),
	[moti_bgcolor] varchar(20) NULL,
	[moti_IdGrupoMotivo] varchar(10) NULL,
	CONSTRAINT [PK_s_MotivoConsulta] PRIMARY KEY CLUSTERED ([moti_IdMotivo] ASC)
);
GO

-- Tabla: s_Nota
CREATE TABLE [dbo].[s_Nota] (
	[nota_idNota] varchar(10) NOT NULL,
	[nota_Nombre] varchar(80) NULL,
	[nota_Descripcion] text NULL,
	[nota_Estatus] char(1) NULL DEFAULT ('A'),
	[nota_Html] bit NULL DEFAULT ((1)),
	[nota_ImpGenPac] bit NULL DEFAULT ((1)),
	[nota_Justificar] bit NULL DEFAULT ((1)),
	[nota_MT] decimal(5, 2) NULL,
	[nota_MB] decimal(5, 2) NULL,
	[nota_ML] decimal(5, 2) NULL,
	[nota_MR] decimal(5, 2) NULL,
	CONSTRAINT [PK_s_Nota] PRIMARY KEY CLUSTERED ([nota_idNota] ASC)
);
GO

-- Tabla: s_NotaCreditoSeguro
CREATE TABLE [dbo].[s_NotaCreditoSeguro] (
	[Numero] bigint NOT NULL,
	[IdSeguro] varchar(10) NOT NULL,
	[Fecha] smalldatetime NULL,
	[DocRef] varchar(20) NULL,
	[Monto] float NULL,
	[Comentario] varchar(150) NULL,
	[RegistradoPor] varchar(15) NULL,
	[Estatus] char(1) NULL DEFAULT ('I'),
	[NumNcrSin] int NULL,
	[NumeroReclamacion] bigint NULL,
	[FechaReg] datetime NULL DEFAULT (getdate()),
	[IdTipoCF] varchar(2) NULL,
	[NCF] varchar(19) NULL,
	[FecVenNCF] datetime NULL,
	[IdSecCF] varchar(10) NULL,
	[NCFAsig] bit NULL DEFAULT ((0)),
	CONSTRAINT [PK_NotaCreditoSeguro] PRIMARY KEY CLUSTERED ([Numero] ASC)
);
GO

-- Tabla: s_NotaCreditoSeguro_Detalles
CREATE TABLE [dbo].[s_NotaCreditoSeguro_Detalles] (
	[Numero] bigint NOT NULL,
	[Tipo] varchar(3) NOT NULL,
	[hpag_IdCaja] varchar(10) NOT NULL DEFAULT ('COB'),
	[hpag_Numero] bigint NOT NULL,
	[Monto] decimal(12, 2) NULL,
	[Saldo] bit NULL DEFAULT ((1)),
	[PendienteRec] decimal(12, 2) NULL,
	CONSTRAINT [PK_s_NotaCreditoSeguro_Detalles] PRIMARY KEY CLUSTERED ([Numero] ASC, [Tipo] ASC, [hpag_IdCaja] ASC, [hpag_Numero] ASC)
);
GO

-- Tabla: s_NotaDebitoSeguro
CREATE TABLE [dbo].[s_NotaDebitoSeguro] (
	[Numero] bigint NOT NULL,
	[IdSeguro] varchar(10) NOT NULL,
	[Fecha] smalldatetime NULL,
	[DocRef] varchar(20) NULL,
	[Monto] float NULL,
	[Comentario] varchar(150) NULL,
	[RegistradoPor] varchar(15) NULL,
	[Estatus] char(1) NULL DEFAULT ('N'),
	[NumNdbSin] int NULL,
	[NumeroReclamacion] bigint NULL,
	[FechaReg] datetime NULL DEFAULT (getdate()),
	[IdTipoCF] varchar(2) NULL,
	[NCF] varchar(19) NULL,
	[FecVenNCF] datetime NULL,
	[IdSecCF] varchar(10) NULL,
	[NCFAsig] bit NULL DEFAULT ((0)),
	CONSTRAINT [PK_NotaDebitoSeguro] PRIMARY KEY CLUSTERED ([Numero] ASC)
);
GO

-- Tabla: s_NotaDebitoSeguro_Detalles
CREATE TABLE [dbo].[s_NotaDebitoSeguro_Detalles] (
	[Numero] bigint NOT NULL,
	[Tipo] varchar(3) NOT NULL,
	[hpag_IdCaja] varchar(10) NOT NULL,
	[hpag_Numero] bigint NOT NULL,
	[Monto] decimal(12, 2) NULL,
	[PendienteRec] decimal(12, 2) NULL,
	CONSTRAINT [PK_s_NotaDebitoSeguro_Detalles] PRIMARY KEY CLUSTERED ([Numero] ASC, [Tipo] ASC, [hpag_IdCaja] ASC, [hpag_Numero] ASC)
);
GO

-- Tabla: s_NotaExpediente
CREATE TABLE [dbo].[s_NotaExpediente] (
	[Numero] bigint NOT NULL,
	[Fecha] datetime NULL,
	[NumeroExpediente] bigint NULL,
	[Nombre] varchar(80) NULL,
	[IdNota] varchar(10) NOT NULL,
	[Descripcion] text NULL,
	[IdDoctor] varchar(15) NULL,
	[RegPor] varchar(15) NULL,
	[Privada] bit NULL DEFAULT ((0)),
	CONSTRAINT [PK_s_NotaExpediente] PRIMARY KEY CLUSTERED ([Numero] ASC)
);
GO

-- Tabla: s_Notificacion
CREATE TABLE [dbo].[s_Notificacion] (
	[Numero] int NOT NULL,
	[Titulo] varchar(50) NULL,
	[Descripcion] varchar(200) NULL,
	[Link] varchar(200) NULL,
	[DesLink] varchar(200) NULL,
	[DirigidoA] char(1) NULL DEFAULT ('U'),
	[IdUsuario] varchar(15) NULL,
	[IdPerfil] varchar(10) NULL,
	[TipoNot] char(1) NULL DEFAULT ('I'),
	[FecIni] datetime NULL,
	[FecFin] datetime NULL,
	[Estatus] char(1) NULL DEFAULT ('A'),
	[RegPor] varchar(15) NULL,
	CONSTRAINT [PK_s_Notificacion] PRIMARY KEY CLUSTERED ([Numero] ASC)
);
GO

-- Tabla: s_Ocupacion
CREATE TABLE [dbo].[s_Ocupacion] (
	[CodOcup] varchar(5) NOT NULL,
	[NomOcup] varchar(50) NULL,
	[Estatus] char(1) NULL DEFAULT ('A'),
	CONSTRAINT [PK_s_Ocupacion] PRIMARY KEY CLUSTERED ([CodOcup] ASC)
);
GO

-- Tabla: s_Optica
CREATE TABLE [dbo].[s_Optica] (
	[IdOptica] varchar(10) NOT NULL,
	[nombre] varchar(50) NULL,
	[Estatus] char(1) NULL,
	CONSTRAINT [PK_s_Optica] PRIMARY KEY CLUSTERED ([IdOptica] ASC)
);
GO

-- Tabla: s_PagoSeguro
CREATE TABLE [dbo].[s_PagoSeguro] (
	[pags_Numero] bigint NOT NULL,
	[pags_IdSeguro] varchar(10) NOT NULL,
	[pags_Fecha] smalldatetime NULL,
	[pags_DocRef] varchar(20) NULL,
	[pags_NumeroReclamacion] bigint NULL,
	[pags_ValorPago] float NULL,
	[pags_IdCaja] varchar(10) NULL,
	[pags_FormaPago] char(1) NULL,
	[pags_RefPago] varchar(20) NULL,
	[pags_Comentario] varchar(150) NULL,
	[pags_RegistradoPor] varchar(15) NULL,
	[pags_Estatus] char(1) NULL DEFAULT ('I'),
	[pags_MontoRetencion] float NULL DEFAULT (0),
	[pags_NetoPago] float NULL DEFAULT (0),
	[NumCobroSin] int NULL,
	[pags_IdBancoTransf] varchar(10) NULL,
	[pags_RefTransf] varchar(10) NULL,
	[NumNcrSin] int NULL,
	[pags_FechaReg] datetime NULL DEFAULT (getdate()),
	CONSTRAINT [PK_s_PagoSeguro] PRIMARY KEY CLUSTERED ([pags_Numero] ASC)
);
GO

-- Tabla: s_PagoSeguro_Detalles
CREATE TABLE [dbo].[s_PagoSeguro_Detalles] (
	[pags_Numero] bigint NOT NULL,
	[Tipo] varchar(3) NOT NULL,
	[hpag_IdCaja] varchar(10) NOT NULL DEFAULT ('COB'),
	[hpag_Numero] bigint NOT NULL,
	[Monto] decimal(12, 2) NULL,
	[Saldo] bit NULL DEFAULT ((1)),
	[PendienteRec] decimal(12, 2) NULL,
	CONSTRAINT [PK_s_PagoSeguro_Detalles] PRIMARY KEY CLUSTERED ([pags_Numero] ASC, [Tipo] ASC, [hpag_IdCaja] ASC, [hpag_Numero] ASC)
);
GO

-- Tabla: s_Pais
CREATE TABLE [dbo].[s_Pais] (
	[CodPais] varchar(5) NOT NULL,
	[NomPais] varchar(50) NULL,
	[Estatus] char(1) NULL DEFAULT ('A'),
	CONSTRAINT [PK_s_Pais] PRIMARY KEY CLUSTERED ([CodPais] ASC)
);
GO

-- Tabla: s_PerfilUsuario
CREATE TABLE [dbo].[s_PerfilUsuario] (
	[pusr_IdPerfil] varchar(10) NOT NULL,
	[pusr_Nombre] varchar(50) NOT NULL,
	[pusr_AccUsr] bit NULL DEFAULT (0),
	[pusr_AccRep] bit NULL DEFAULT (1),
	[pusr_AccMant] bit NULL DEFAULT (0),
	[pusr_AccRExp] bit NULL DEFAULT (0),
	[pusr_AccMExp] bit NULL DEFAULT (0),
	[pusr_AccRVis] bit NULL DEFAULT (0),
	[pusr_AccMVis] bit NULL DEFAULT (0),
	[pusr_AccRPro] bit NULL DEFAULT (0),
	[pusr_AccRCit] bit NULL DEFAULT (0),
	[pusr_AccRLpa] bit NULL DEFAULT (0),
	[pusr_AccRLep] bit NULL DEFAULT (0),
	[pusr_AccRDir] bit NULL DEFAULT (0),
	[pusr_AccRPag] bit NULL DEFAULT (0),
	[pusr_AccRPagS] bit NULL,
	[pusr_AccRRecS] bit NULL DEFAULT (0),
	[pusr_AccRRef] bit NULL DEFAULT (0),
	[pusr_AccRCert] bit NULL DEFAULT (0),
	[pusr_AccACaj] bit NULL DEFAULT (0),
	[pusr_AccCDia] bit NULL DEFAULT (0),
	[pusr_AccRPre] bit NULL,
	[pusr_AccTareas] bit NULL,
	[pusr_AccLProc] bit NULL DEFAULT (0),
	[pusr_Estatus] char(1) NULL DEFAULT ('A'),
	[pusr_AccRCobS] bit NULL,
	[pusr_AccRMed] bit NULL,
	[pusr_AccREntR] bit NULL,
	[pusr_AccRDiasNL] bit NULL,
	[pusr_AccMPag] bit NULL,
	[pusr_AccRInv] bit NULL,
	[pusr_AccRComL] bit NULL,
	[pusr_AccRNota] bit NULL,
	[pusr_AccCInv] bit NULL,
	[pusr_AccMInv] bit NULL,
	[pusr_AccCLpa] bit NULL,
	[pusr_AccCVis] bit NULL,
	[pusr_AccCExp] bit NULL,
	[pusr_AccRepExp] bit NULL,
	[pusr_AccRepSer] bit NULL,
	[pusr_AccRepSeg] bit NULL,
	[pusr_AccRepLista] bit NULL,
	[pusr_AccBLpa] bit NULL DEFAULT ((0)),
	[pusr_AccMHPag] bit NULL DEFAULT ((0)),
	[pusr_AccAVis] bit NULL DEFAULT ((0)),
	[pusr_AccREst] bit NULL DEFAULT ((0)),
	[pusr_AccMEst] bit NULL DEFAULT ((0)),
	[pusr_AccCEst] bit NULL DEFAULT ((0)),
	[pusr_AccRCitHE] bit NULL DEFAULT ((0)),
	[pusr_AccMoLPa] bit NULL DEFAULT ((0)),
	[pusr_AccCobP] bit NULL DEFAULT ((0)),
	[pusr_NivPre] int NULL,
	[pusr_AccRPreP] bit NULL DEFAULT ((0)),
	CONSTRAINT [PK_PerfilUsuario] PRIMARY KEY CLUSTERED ([pusr_IdPerfil] ASC)
);
GO

-- Tabla: s_Poliza
CREATE TABLE [dbo].[s_Poliza] (
	[NumPoliza] int NOT NULL,
	[Nombres] varchar(50) NULL,
	[Apellidos] varchar(50) NULL,
	[Cedula] varchar(11) NULL,
	[Sexo] char(1) NULL,
	[FechaNac] datetime NULL,
	[Direccion] varchar(200) NULL,
	[CodProv] varchar(5) NULL,
	[CodCiu] varchar(5) NULL,
	[Telefono] varchar(14) NULL,
	[Celular] varchar(14) NULL,
	[CodOcup] varchar(5) NULL,
	[Cargo] varchar(50) NULL,
	[LugarTrabajo] varchar(200) NULL,
	[TelTrabajo] varchar(14) NULL,
	[Email] varchar(80) NULL,
	[IdSeguro] varchar(10) NULL,
	[NumPolizaSeg] varchar(15) NULL,
	[IdCentro] varchar(5) NULL,
	[FechaInicio] datetime NULL,
	[NumPlan] int NULL,
	[MontoMensual] decimal(12, 2) NULL,
	[Observaciones] varchar(2000) NULL,
	[ArchivoFoto] varchar(80) NULL,
	[RegPor] varchar(15) NULL,
	[FechaCreacion] datetime NULL,
	[IdRazonNoActivo] varchar(10) NULL,
	[FechaNoActivo] datetime NULL,
	[IdInstitucion] varchar(10) NULL,
	[Estatus] char(1) NULL DEFAULT ('A'),
	CONSTRAINT [PK_s_Poliza] PRIMARY KEY CLUSTERED ([NumPoliza] ASC)
);
GO

-- Tabla: s_Poliza_Dependientes
CREATE TABLE [dbo].[s_Poliza_Dependientes] (
	[NumAfiliado] int NOT NULL,
	[NumPoliza] int NULL,
	[Nombres] varchar(50) NULL,
	[Apellidos] varchar(50) NULL,
	[Cedula] varchar(11) NULL,
	[Sexo] char(1) NULL,
	[FechaNac] datetime NULL,
	[Telefono] varchar(14) NULL,
	[Celular] varchar(14) NULL,
	[Email] varchar(80) NULL,
	[IdSeguro] varchar(10) NULL,
	[NumPolizaSeg] varchar(15) NULL,
	[NumPlan] int NULL,
	[IdRelacion] varchar(5) NULL,
	[FechaInicio] datetime NULL,
	[Estatus] char(1) NULL DEFAULT ('A'),
	[IdRazonNoActivo] varchar(10) NULL,
	[FechaNoActivo] datetime NULL,
	[RegPor] varchar(15) NULL,
	CONSTRAINT [PK_s_Poliza_Dependientes] PRIMARY KEY CLUSTERED ([NumAfiliado] ASC)
);
GO

-- Tabla: s_PRECita
CREATE TABLE [dbo].[s_PRECita] (
	[cita_IdDoctor] varchar(15) NOT NULL DEFAULT (''),
	[cita_Fecha] smalldatetime NOT NULL,
	[cita_hora] char(6) NOT NULL,
	[cita_NumeroExpediente] bigint NULL,
	[cita_HoraNumero] float NULL,
	[cita_Nombre] varchar(30) NULL,
	[cita_Apellido] varchar(30) NULL,
	[cita_Telefono] varchar(15) NULL,
	[cita_IdSeguro] varchar(10) NULL,
	[cita_IdPlanSeguro] int NULL,
	[cita_Comentario] varchar(50) NULL,
	[cita_EstatusConf] char(1) NULL DEFAULT (''),
	[cita_FechaEstatusConf] datetime NULL,
	[cita_RegPorEstatusConf] varchar(15) NULL,
	[cita_RegPor] varchar(15) NULL,
	[cita_FecReg] datetime NULL DEFAULT (getdate()),
	[cita_Email] varchar(50) NULL,
	[cita_StsEmail] char(1) NULL,
	[cita_FechaEmail] datetime NULL,
	[cita_ConfEmailUsada] int NULL,
	[cita_Nota] varchar(1500) NULL,
	[NumPreCita] bigint NOT NULL,
	[IdOrig] int NULL,
	[NumCita] bigint NULL,
	[IdConsultorio] varchar(10) NULL,
	[IdEntidadRef] varchar(10) NULL,
	[cita_Celular] varchar(15) NULL,
	CONSTRAINT [PK_s_PreCita] PRIMARY KEY CLUSTERED ([NumPreCita] ASC)
);
GO

-- Tabla: s_Presupuesto_Pagos
CREATE TABLE [dbo].[s_Presupuesto_Pagos] (
	[NumPre] bigint NOT NULL,
	[IdCaja] varchar(10) NOT NULL,
	[NumPago] bigint NOT NULL,
	CONSTRAINT [PK_s_Presupuesto_Pagos] PRIMARY KEY CLUSTERED ([NumPre] ASC, [IdCaja] ASC, [NumPago] ASC)
);
GO

-- Tabla: s_PrickTest
CREATE TABLE [dbo].[s_PrickTest] (
	[visi_NumeroExpediente] bigint NOT NULL,
	[visi_fechaVisita] datetime NOT NULL,
	[Secuencia] int NOT NULL,
	[IdAlergeno] varchar(10) NULL,
	[Eritema] int NULL,
	[Papula] int NULL,
	[Grado] char(4) NULL,
	[Comentario] varchar(50) NULL,
	CONSTRAINT [PK_s_PrickTest] PRIMARY KEY CLUSTERED ([visi_NumeroExpediente] ASC, [visi_fechaVisita] ASC, [Secuencia] ASC)
);
GO

-- Tabla: s_Provincia
CREATE TABLE [dbo].[s_Provincia] (
	[CodProv] varchar(5) NULL,
	[NomProv] varchar(50) NULL,
	[Estatus] char(1) NULL DEFAULT ('A')
);
GO

-- Tabla: s_Quirofano
CREATE TABLE [dbo].[s_Quirofano] (
	[IdQuirofano] varchar(10) NOT NULL,
	[NomQuirofano] varchar(50) NULL,
	[Estatus] char(1) NULL,
	CONSTRAINT [PK_s_Quirofano] PRIMARY KEY CLUSTERED ([IdQuirofano] ASC)
);
GO

-- Tabla: s_RazonNoActivo
CREATE TABLE [dbo].[s_RazonNoActivo] (
	[IdRazon] varchar(5) NOT NULL,
	[DesRazon] varchar(50) NULL,
	[Estatus] char(1) NULL DEFAULT ('A'),
	CONSTRAINT [PK_s_RazonNoActivo] PRIMARY KEY CLUSTERED ([IdRazon] ASC)
);
GO

-- Tabla: s_RecetaAnalisis
CREATE TABLE [dbo].[s_RecetaAnalisis] (
	[IdReceta] varchar(10) NOT NULL,
	[nombre] varchar(50) NULL,
	[Estatus] char(1) NULL,
	CONSTRAINT [PK_s_RecetaAnalisis] PRIMARY KEY CLUSTERED ([IdReceta] ASC)
);
GO

-- Tabla: s_RecetaAnalisis_Detalle
CREATE TABLE [dbo].[s_RecetaAnalisis_Detalle] (
	[IdReceta] varchar(10) NOT NULL,
	[IdAnalisis] varchar(10) NOT NULL,
	CONSTRAINT [PK_s_RecetaAnalisis_Detalle] PRIMARY KEY CLUSTERED ([IdReceta] ASC, [IdAnalisis] ASC)
);
GO

-- Tabla: s_RecetaPrehecha
CREATE TABLE [dbo].[s_RecetaPrehecha] (
	[recp_IdRecetaPrehecha] varchar(10) NOT NULL,
	[recp_Nombre] varchar(50) NULL,
	[recp_Contenido] text NULL,
	[recp_Estatus] char(1) NULL DEFAULT ('A'),
	[recp_IdEspecialidad] char(3) NOT NULL DEFAULT ('OFT'),
	CONSTRAINT [PK_s_RecetaPrehecha] PRIMARY KEY CLUSTERED ([recp_IdEspecialidad] ASC, [recp_IdRecetaPrehecha] ASC)
);
GO

-- Tabla: s_ReclamacionesSeguro
CREATE TABLE [dbo].[s_ReclamacionesSeguro] (
	[recs_Numero] bigint NOT NULL,
	[recs_IdSeguro] varchar(10) NOT NULL,
	[recs_Fecha] smalldatetime NULL,
	[recs_Valor] float NULL,
	[recs_Comentario] varchar(150) NULL,
	[recs_IdTipoCF] char(2) NULL,
	[recs_NCF] char(19) NULL,
	[recs_RegistradoPor] varchar(15) NULL,
	[recs_Estatus] char(1) NULL DEFAULT ('N'),
	[recs_NumeroPagoSeguro] bigint NULL,
	[recs_IdSecCF] varchar(10) NULL,
	[IdFacSin] int NULL,
	[recs_PendPago] bit NULL DEFAULT ((1)),
	[recs_IdCaja] varchar(10) NULL,
	[recs_FecVenNCF] datetime NULL,
	[recs_FechaReg] datetime NULL DEFAULT (getdate()),
	[recs_NCFAsig] bit NULL DEFAULT ((0)),
	CONSTRAINT [PK_s_ReclamacionesSeguro] PRIMARY KEY CLUSTERED ([recs_Numero] ASC)
);
GO

-- Tabla: s_Referimiento
CREATE TABLE [dbo].[s_Referimiento] (
	[refe_numeroExpediente] bigint NOT NULL,
	[refe_FechaReferimiento] datetime NOT NULL,
	[refe_IdEntidadReferimiento] varchar(10) NULL,
	[refe_IdTipoReferimiento] varchar(10) NULL,
	[refe_ReferidoA] varchar(50) NULL,
	[refe_ResumenReferimiento] varchar(2000) NULL,
	[refe_IdDoctor] varchar(15) NULL,
	[NumRef] bigint NOT NULL,
	CONSTRAINT [PK_s_Referimiento] PRIMARY KEY CLUSTERED ([NumRef] ASC)
);
GO

-- Tabla: s_Relacion
CREATE TABLE [dbo].[s_Relacion] (
	[IdRelacion] varchar(5) NOT NULL,
	[NomRelacion] varchar(50) NULL,
	[Estatus] char(1) NULL DEFAULT ('A'),
	CONSTRAINT [PK_s_Relacion] PRIMARY KEY CLUSTERED ([IdRelacion] ASC)
);
GO

-- Tabla: s_SecuenciaCF_Table
CREATE TABLE [dbo].[s_SecuenciaCF_Table] (
	[Id] varchar(10) NOT NULL,
	[Descripcion] varchar(50) NULL,
	[IdSerie] char(1) NULL,
	[IdDivisionNegocio] char(2) NULL,
	[IdPuntoEmision] char(3) NULL,
	[IdArea] char(3) NULL,
	[IdTipoCF] char(2) NULL,
	[Numero] bigint NULL,
	[Desde] bigint NULL,
	[Hasta] bigint NULL,
	[Minimo] bigint NULL,
	[ProximoDesde] bigint NULL,
	[ProximoHasta] bigint NULL,
	[ProximoMinimo] bigint NULL,
	[Estatus] char(1) NULL,
	[FechaVen] datetime NULL,
	[ProxFechaVen] datetime NULL,
	CONSTRAINT [PK_s_SecuenciaCF] PRIMARY KEY CLUSTERED ([Id] ASC)
);
GO

-- Tabla: s_Seguro
CREATE TABLE [dbo].[s_Seguro] (
	[segu_IdSeguro] varchar(10) NOT NULL,
	[segu_Nombre] varchar(100) NULL,
	[segu_Contacto] varchar(50) NULL,
	[segu_Telefono1] varchar(15) NULL,
	[segu_Telefono2] varchar(15) NULL,
	[segu_rnc] varchar(13) NULL,
	[segu_IdEnSeguro] varchar(20) NULL,
	[segu_FechaActPrecio] smalldatetime NULL,
	[segu_NombrePlan1] varchar(15) NULL,
	[segu_NombrePlan2] varchar(15) NULL,
	[segu_NombrePlan3] varchar(15) NULL,
	[segu_NombrePlan4] varchar(15) NULL,
	[segu_NombrePlan5] varchar(15) NULL,
	[segu_Estatus] char(1) NULL DEFAULT ('A'),
	[NumCuen] varchar(15) NULL,
	[segu_Logo] image NULL,
	[segu_Direccion] varchar(100) NULL,
	[segu_IdSecCF] varchar(10) NULL,
	[segu_AplicaDsc] bit NULL DEFAULT ((0)),
	[segu_NomCli] varchar(80) NULL,
	[CodCliSin] varchar(10) NULL,
	[segu_NocSeg] varchar(10) NULL,
	[segu_FmtTextFile] varchar(10) NULL,
	[segu_IdSeguroRec] varchar(10) NULL,
	[segu_PaginaAut] varchar(30) NULL,
	[segu_PorRetencion] decimal(5, 2) NULL,
	[segu_NombrePlan6] varchar(15) NULL,
	[segu_FmtReclamacion] varchar(50) NULL,
	[segu_FmtCirugia] varchar(50) NULL,
	[IdCentro] varchar(5) NULL,
	[segu_AutDup] bit NULL DEFAULT ((0)),
	[segu_MaxAno] int NULL DEFAULT ((0)),
	[segu_ReqAut] bit NULL DEFAULT ((1)),
	CONSTRAINT [PK_s_] PRIMARY KEY CLUSTERED ([segu_IdSeguro] ASC)
);
GO

-- Tabla: s_SeguroServicios
CREATE TABLE [dbo].[s_SeguroServicios] (
	[segs_IdSeguro] varchar(10) NOT NULL,
	[segs_IdServicio] varchar(10) NOT NULL,
	[segs_Precio] float NULL,
	[segs_CoberturaPlan1] float NULL,
	[segs_CoberturaPlan2] float NULL,
	[segs_CoberturaPlan3] float NULL,
	[segs_CoberturaPlan4] float NULL,
	[segs_CoberturaPlan5] float NULL,
	[segs_IdServicioEnSeguro] varchar(20) NULL,
	[segs_CoberturaPlan6] float NULL,
	[segs_CoPago] decimal(12, 2) NULL DEFAULT ((0)),
	CONSTRAINT [PK_s_SeguroServicios] PRIMARY KEY CLUSTERED ([segs_IdSeguro] ASC, [segs_IdServicio] ASC)
);
GO

-- Tabla: s_SubEspecialidad
CREATE TABLE [dbo].[s_SubEspecialidad] (
	[IdSubEspecialidad] varchar(10) NOT NULL,
	[NomSubEspecialidad] varchar(50) NULL,
	[IdEspecialidad] varchar(3) NULL,
	[Estatus] char(1) NULL DEFAULT ('A'),
	CONSTRAINT [PK_s_SubEspecialidad] PRIMARY KEY CLUSTERED ([IdSubEspecialidad] ASC)
);
GO

-- Tabla: s_Suplidor
CREATE TABLE [dbo].[s_Suplidor] (
	[IdSuplidor] varchar(10) NOT NULL,
	[Nombre] varchar(50) NULL,
	[Contacto] varchar(50) NULL,
	[RNC] varchar(13) NULL,
	[Telefono1] varchar(15) NULL,
	[Telefono2] varchar(15) NULL,
	[Estatus] char(1) NULL,
	CONSTRAINT [PK_s_Suplidor] PRIMARY KEY CLUSTERED ([IdSuplidor] ASC)
);
GO

-- Tabla: s_Tanda
CREATE TABLE [dbo].[s_Tanda] (
	[IdTanda] varchar(10) NOT NULL,
	[NomTanda] varchar(50) NULL,
	[HorIni] char(6) NULL,
	[HorFin] char(6) NULL,
	[Estatus] char(1) NULL DEFAULT ('A'),
	CONSTRAINT [PK_s_Turno] PRIMARY KEY CLUSTERED ([IdTanda] ASC)
);
GO

-- Tabla: s_Terminos
CREATE TABLE [dbo].[s_Terminos] (
	[term_Categoria] varchar(15) NOT NULL,
	[term_IdTermino] varchar(10) NOT NULL,
	[term_Nombre] varchar(2000) NULL,
	[term_Estatus] char(1) NULL DEFAULT ('A'),
	[term_IdEspecialidad] char(3) NOT NULL DEFAULT ('OFT'),
	CONSTRAINT [PK_s_Terminos] PRIMARY KEY CLUSTERED ([term_IdEspecialidad] ASC, [term_Categoria] ASC, [term_IdTermino] ASC)
);
GO

-- Tabla: s_TipoAlergeno
CREATE TABLE [dbo].[s_TipoAlergeno] (
	[IdTipoAlergeno] varchar(10) NOT NULL,
	[NomTipoAlergeno] varchar(50) NULL,
	[Estatus] char(1) NULL,
	CONSTRAINT [PK_s_TipoAlergeno] PRIMARY KEY CLUSTERED ([IdTipoAlergeno] ASC)
);
GO

-- Tabla: s_TipoArticulo
CREATE TABLE [dbo].[s_TipoArticulo] (
	[CodTArt] varchar(10) NOT NULL,
	[NomTArt] varchar(50) NULL,
	[Estatus] char(1) NULL,
	CONSTRAINT [PK_s_TipoArticulo] PRIMARY KEY CLUSTERED ([CodTArt] ASC)
);
GO

-- Tabla: s_TipoCertificado
CREATE TABLE [dbo].[s_TipoCertificado] (
	[IdTipoCert] varchar(10) NOT NULL,
	[NomTipoCert] varchar(50) NULL,
	[Constatado] varchar(3000) NULL,
	[Recomendacion] varchar(3000) NULL,
	[Estatus] char(1) NULL,
	[FormatoCert] varchar(50) NULL,
	CONSTRAINT [PK_s_TipoCertificado] PRIMARY KEY CLUSTERED ([IdTipoCert] ASC)
);
GO

-- Tabla: s_TipoComprobanteFiscal
CREATE TABLE [dbo].[s_TipoComprobanteFiscal] (
	[ticf_IdTipoCF] char(2) NOT NULL,
	[ticf_Nombre] varchar(50) NULL,
	[ticf_Estatus] char(1) NULL DEFAULT ('A'),
	CONSTRAINT [PK_s_TipoComprobanteFiscal] PRIMARY KEY CLUSTERED ([ticf_IdTipoCF] ASC)
);
GO

-- Tabla: s_TipoLente
CREATE TABLE [dbo].[s_TipoLente] (
	[tlen_IdTipoLente] varchar(10) NOT NULL,
	[tlen_Nombre] varchar(50) NULL,
	[tlen_Estatus] char(1) NULL,
	[tlen_UsoFrecuente] bit NULL DEFAULT ((0)),
	CONSTRAINT [PK_s_TipoLente] PRIMARY KEY CLUSTERED ([tlen_IdTipoLente] ASC)
);
GO

-- Tabla: s_TipoMovInv
CREATE TABLE [dbo].[s_TipoMovInv] (
	[codtmov] varchar(10) NOT NULL,
	[NomTMov] varchar(50) NULL,
	[Clase] char(1) NULL,
	[Estatus] char(1) NULL,
	CONSTRAINT [PK_s_TipoMovInv] PRIMARY KEY CLUSTERED ([codtmov] ASC)
);
GO

-- Tabla: s_TipoMovInv_Articulos
CREATE TABLE [dbo].[s_TipoMovInv_Articulos] (
	[Codtmov] varchar(10) NOT NULL,
	[Linea] int NOT NULL,
	[CodArt] varchar(15) NULL,
	[Cantidad] float NULL,
	[Comentario] varchar(50) NULL,
	CONSTRAINT [PK_s_TipoMovInv_Articulos] PRIMARY KEY CLUSTERED ([Codtmov] ASC, [Linea] ASC)
);
GO

-- Tabla: s_TipoReferimiento
CREATE TABLE [dbo].[s_TipoReferimiento] (
	[tipr_IdTipoReferimiento] varchar(10) NOT NULL,
	[tipr_Nombre] varchar(100) NULL,
	[tipr_Clase] char(1) NULL DEFAULT ('C'),
	[tipr_NotaASugerir] varchar(2000) NULL,
	[tipr_Estatus] char(1) NULL DEFAULT ('A'),
	CONSTRAINT [PK_s_TipoReferimiento] PRIMARY KEY CLUSTERED ([tipr_IdTipoReferimiento] ASC)
);
GO

-- Tabla: s_TipoServicio
CREATE TABLE [dbo].[s_TipoServicio] (
	[IdTipoSer] varchar(10) NOT NULL,
	[NomTipoSer] varchar(50) NULL,
	[Estatus] char(1) NULL,
	CONSTRAINT [PK_s_TipoServicio] PRIMARY KEY CLUSTERED ([IdTipoSer] ASC)
);
GO

-- Tabla: s_TipoTarjeta
CREATE TABLE [dbo].[s_TipoTarjeta] (
	[IdTipoTarjeta] varchar(10) NOT NULL,
	[NomTipoTarjeta] varchar(50) NULL,
	[IdTipoTarjetaSIN] varchar(10) NOT NULL,
	[Estatus] char(1) NULL DEFAULT ('A'),
	CONSTRAINT [PK_s_Tarjeta] PRIMARY KEY CLUSTERED ([IdTipoTarjeta] ASC)
);
GO

-- Tabla: s_TipoTerapia
CREATE TABLE [dbo].[s_TipoTerapia] (
	[IdTipoTerapia] varchar(5) NOT NULL,
	[NomTipoTerapia] varchar(50) NULL,
	[Estatus] char(1) NULL DEFAULT ('A'),
	CONSTRAINT [PK_TipoTerapia] PRIMARY KEY CLUSTERED ([IdTipoTerapia] ASC)
);
GO

-- Tabla: s_Usuario
CREATE TABLE [dbo].[s_Usuario] (
	[usua_IdUsuario] varchar(15) NOT NULL,
	[usua_Nombre] varchar(50) NULL,
	[usua_ClaveAcceso] varchar(15) NULL,
	[usua_IdPerfil] varchar(10) NULL,
	[usua_Email] varchar(80) NULL,
	[usua_Estatus] char(1) NULL DEFAULT ('A'),
	[usua_IdDoctorDef] varchar(15) NULL,
	[usua_EsDoctor] bit NULL DEFAULT ((0)),
	[usua_FechaReg] datetime NULL DEFAULT (getdate()),
	CONSTRAINT [PK_Usuario] PRIMARY KEY CLUSTERED ([usua_IdUsuario] ASC)
);
GO

-- Tabla: s_UsuarioCaja
CREATE TABLE [dbo].[s_UsuarioCaja] (
	[usuc_IdUsuario] varchar(15) NOT NULL,
	[usuc_IdCaja] varchar(10) NOT NULL,
	CONSTRAINT [PK_s_UsuarioCaja] PRIMARY KEY CLUSTERED ([usuc_IdUsuario] ASC, [usuc_IdCaja] ASC)
);
GO

-- Tabla: s_UsuarioCentros
CREATE TABLE [dbo].[s_UsuarioCentros] (
	[IdUsuario] varchar(15) NOT NULL,
	[IdCentro] varchar(5) NOT NULL,
	CONSTRAINT [PK_s_UsuarioCentros] PRIMARY KEY CLUSTERED ([IdUsuario] ASC, [IdCentro] ASC)
);
GO

-- Tabla: s_UsuarioDoctores
CREATE TABLE [dbo].[s_UsuarioDoctores] (
	[usud_IdUsuario] varchar(15) NOT NULL,
	[usud_IdDoctor] varchar(15) NOT NULL,
	[usud_TipoAcceso] char(1) NULL DEFAULT ('D'),
	[usud_Usarpordefecto] bit NULL DEFAULT (0),
	CONSTRAINT [PK_s_UsuarioDoctores] PRIMARY KEY CLUSTERED ([usud_IdUsuario] ASC, [usud_IdDoctor] ASC)
);
GO

-- Tabla: s_UsuarioEstudios
CREATE TABLE [dbo].[s_UsuarioEstudios] (
	[usue_IdUsuario] varchar(15) NOT NULL,
	[usue_IdEstudio] varchar(10) NOT NULL,
	[usue_MontoAUsar] char(1) NULL DEFAULT ('E'),
	[usue_MontoARecibir] float NULL,
	CONSTRAINT [PK_s_UsuarioEstudios] PRIMARY KEY CLUSTERED ([usue_IdUsuario] ASC, [usue_IdEstudio] ASC)
);
GO

-- Tabla: s_UsuarioListaServicios
CREATE TABLE [dbo].[s_UsuarioListaServicios] (
	[usul_IdUsuario] varchar(15) NOT NULL,
	[usul_IdListaServicio] varchar(10) NOT NULL,
	CONSTRAINT [PK_s_UsuarioListaServicios] PRIMARY KEY CLUSTERED ([usul_IdUsuario] ASC, [usul_IdListaServicio] ASC)
);
GO

-- Tabla: s_UsuarioListasEspeciales
CREATE TABLE [dbo].[s_UsuarioListasEspeciales] (
	[usul_IdUsuario] varchar(15) NOT NULL,
	[usul_IdListaEspecial] varchar(10) NOT NULL,
	[usul_TipoAcceso] char(1) NULL DEFAULT ('D'),
	[usul_Usarpordefecto] bit NULL DEFAULT (0),
	CONSTRAINT [PK_s_UsuarioListasEspeciales] PRIMARY KEY CLUSTERED ([usul_IdUsuario] ASC, [usul_IdListaEspecial] ASC)
);
GO

-- Tabla: s_UsuarioOptica
CREATE TABLE [dbo].[s_UsuarioOptica] (
	[IdUsuario] varchar(15) NOT NULL,
	[IdOptica] varchar(10) NOT NULL,
	CONSTRAINT [PK_s_UsuarioOptica] PRIMARY KEY CLUSTERED ([IdUsuario] ASC, [IdOptica] ASC)
);
GO

-- Tabla: s_ValoresPaquimetria
CREATE TABLE [dbo].[s_ValoresPaquimetria] (
	[paqu_GrosorCorneal] int NOT NULL,
	[paqu_ValorPIOCorregido] int NULL,
	CONSTRAINT [PK_s_ValoresPaquimetria] PRIMARY KEY CLUSTERED ([paqu_GrosorCorneal] ASC)
);
GO

-- Tabla: s_Visita
CREATE TABLE [dbo].[s_Visita] (
	[visi_NumeroExpediente] bigint NOT NULL,
	[visi_IdEspecialidad] char(3) NOT NULL DEFAULT ('OFT'),
	[visi_FechaVisita] datetime NOT NULL,
	[visi_IdDoctor] varchar(15) NULL,
	[visi_MotivoConsulta] text NULL,
	[visi_TipoVisita] char(1) NULL DEFAULT ('S'),
	[visi_DescripcionDiagnostico] text NULL,
	[visi_tratamiento] text NULL,
	[visi_Plan] text NULL,
	[visi_CantidadProximaCita] int NULL,
	[visi_PeriodoProximaCita] char(1) NULL DEFAULT ('D'),
	[visi_PresionArterial] varchar(7) NULL,
	[visi_tratamiento2] text NULL,
	[visi_tratamiento3] text NULL,
	[visi_tratamiento4] text NULL,
	[visi_tratamiento5] text NULL,
	[visi_FechaApertura] datetime NULL DEFAULT (getdate()),
	[NumVisita] bigint NOT NULL,
	[NumCita] bigint NULL,
	[IdOrig] int NULL,
	[visi_FrecCardiaca] int NULL,
	[visi_FrecResp] int NULL,
	[visi_SatOxigeno] int NULL,
	[TipoCita] char(1) NULL,
	[NumLista] bigint NULL,
	[NumListaE] bigint NULL,
	[visi_Glicemia] int NULL,
	[visi_hGlucosilada] decimal(4, 1) NULL,
	CONSTRAINT [PK_s_Visita] PRIMARY KEY CLUSTERED ([NumVisita] ASC)
);
GO

-- Tabla: s_Visita_Alergia
CREATE TABLE [dbo].[s_Visita_Alergia] (
	[one] int NULL,
	[fvc_preBronch] int NULL,
	[fvc_Pron] int NULL,
	[fvc_PostBronch] int NULL,
	[fvc_PorCambio] int NULL,
	[fev1_preBronch] int NULL,
	[fev1_Pron] int NULL,
	[fev1_PostBronch] int NULL,
	[fev1_PorCambio] int NULL,
	[fev1_fvc_preBronch] int NULL,
	[fev1_fvc_Pron] int NULL,
	[fev1_fvc_PostBronch] int NULL,
	[fev1_fvc_PorCambio] int NULL,
	[fef25_75_preBronch] int NULL,
	[fef25_75_Pron] int NULL,
	[fef25_75_PostBronch] int NULL,
	[fef25_75_PorCambio] int NULL,
	[provConjuntival] char(1) NULL DEFAULT (' '),
	[provConjuntival_Aque] varchar(100) NULL,
	[provNasal] char(1) NULL DEFAULT (' '),
	[provNasal_Aque] varchar(100) NULL,
	[provOral] char(1) NULL DEFAULT (' '),
	[provOral_Aque] varchar(100) NULL,
	[PrbLab_Hemograma] varchar(50) NULL,
	[PrbLab_Pcr] varchar(50) NULL,
	[PrbLab_Eonofilos] varchar(50) NULL,
	[PrbLab_IgA] float NULL,
	[PrbLab_IgG] float NULL,
	[PrbLab_IgM] float NULL,
	[PrbLab_IgE_Total] float NULL,
	[PrbLab_TestSudor] float NULL,
	[PrbLab_Ppd] float NULL,
	[Memo] text NULL,
	[PrbLab_IgE_Especifica1] float NULL,
	[PrbLab_IgE_Especifica1Alergeno] varchar(50) NULL,
	[PrbLab_IgE_Especifica2] float NULL,
	[PrbLab_IgE_Especifica2Alergeno] varchar(50) NULL,
	[PrbLab_IgE_Especifica3] float NULL,
	[PrbLab_IgE_Especifica3Alergeno] varchar(50) NULL,
	[PrbLab_IgE_Especifica4] float NULL,
	[PrbLab_IgE_Especifica4Alergeno] varchar(50) NULL,
	[PrbLab_IgE_Especifica5] float NULL,
	[PrbLab_IgE_Especifica5Alergeno] varchar(50) NULL,
	[PrbLab_C3] float NULL,
	[PrbLab_C4] float NULL,
	[NumVisita] bigint NOT NULL,
	CONSTRAINT [PK_s_Visita_Alergia] PRIMARY KEY CLUSTERED ([NumVisita] ASC)
);
GO

-- Tabla: s_Visita_Cardiologia
CREATE TABLE [dbo].[s_Visita_Cardiologia] (
	[visi_Antecedente] text NULL,
	[visi_ExploracionFisica] text NULL,
	[memo] text NULL,
	[NumVisita] bigint NOT NULL,
	CONSTRAINT [PK_s_Visita_Cardiologia] PRIMARY KEY CLUSTERED ([NumVisita] ASC)
);
GO

-- Tabla: s_Visita_Dermatologia
CREATE TABLE [dbo].[s_Visita_Dermatologia] (
	[visi_Antecedente] text NULL,
	[visi_ExploracionFisica] text NULL,
	[memo] text NULL,
	[NumVisita] bigint NOT NULL,
	CONSTRAINT [PK_s_s_Visita_Dermatologia] PRIMARY KEY CLUSTERED ([NumVisita] ASC)
);
GO

-- Tabla: s_Visita_General
CREATE TABLE [dbo].[s_Visita_General] (
	[NumVisita] bigint NOT NULL,
	[visi_Antecedente] text NULL,
	[visi_ExploracionFisica] text NULL,
	[Comentario] text NULL,
	CONSTRAINT [PK_s_Visita_General] PRIMARY KEY CLUSTERED ([NumVisita] ASC)
);
GO

-- Tabla: s_Visita_MedInt
CREATE TABLE [dbo].[s_Visita_MedInt] (
	[NumVisita] bigint NOT NULL,
	[PesoLb] numeric(5, 2) NULL,
	[TallaPie] int NULL,
	[TallaPulg] int NULL,
	[IndiceMasaCorporal] numeric(5, 2) NULL,
	[TensionArterial] varchar(7) NULL,
	[FrecCardiaca] numeric(5, 2) NULL,
	[Comentario] text NULL,
	[RevisionPorSistema] text NULL,
	[Tez] varchar(50) NULL,
	[Lengua] varchar(50) NULL,
	[Pulsos] varchar(50) NULL,
	[Iris] varchar(50) NULL,
	[MetabolismoBasal] decimal(10, 2) NULL,
	CONSTRAINT [PK_s_Visita_MedNatural] PRIMARY KEY CLUSTERED ([NumVisita] ASC)
);
GO

-- Tabla: s_Visita_Neumologia
CREATE TABLE [dbo].[s_Visita_Neumologia] (
	[NumVisita] bigint NOT NULL,
	[visi_ResultadoEstudios] text NULL,
	[visi_ExploracionFisica] text NULL,
	[Comentario] text NULL,
	[visi_AntecedenteExp] text NULL,
	CONSTRAINT [PK_s_Visita_Neumologia] PRIMARY KEY CLUSTERED ([NumVisita] ASC)
);
GO

-- Tabla: s_Visita_Odontologia
CREATE TABLE [dbo].[s_Visita_Odontologia] (
	[NumVisita] bigint NOT NULL,
	[visi_Antecedente] text NULL,
	[visi_ExploracionFisica] text NULL,
	[Comentario] text NULL,
	CONSTRAINT [PK_s_Visita_Odontologia] PRIMARY KEY CLUSTERED ([NumVisita] ASC)
);
GO

-- Tabla: s_Visita_Oftalmologia
CREATE TABLE [dbo].[s_Visita_Oftalmologia] (
	[odavsc] char(8) NULL,
	[odeje] float NULL,
	[odcil] float NULL,
	[odesf] float NULL,
	[odavcc] char(8) NULL,
	[odadd] float NULL,
	[odn] float NULL,
	[avao] char(8) NULL,
	[oiavsc] char(8) NULL,
	[oieje] float NULL,
	[oicil] float NULL,
	[oiesf] float NULL,
	[oiavcc] char(8) NULL,
	[oiadd] float NULL,
	[oiavc] char(8) NULL,
	[oin] float NULL,
	[DistanciaInterpupilar] int NULL,
	[Convergencia] int NULL,
	[Metamorpopsia] char(2) NULL,
	[memo] text NULL,
	[visi_SegmentoAnterior] text NULL,
	[visi_FondoDeOjo] text NULL,
	[rodeje] float NULL,
	[rodcil] float NULL,
	[rodesf] float NULL,
	[rodadd] float NULL,
	[roieje] float NULL,
	[roicil] float NULL,
	[roiesf] float NULL,
	[roiadd] float NULL,
	[lente1] varchar(300) NULL,
	[lente2] varchar(50) NULL,
	[prismaod] float NULL,
	[prismaoi] float NULL,
	[baseod] char(10) NULL,
	[baseoi] char(10) NULL,
	[tgOD] float NULL,
	[tgOI] float NULL,
	[tneuOD] float NULL,
	[tneuOI] float NULL,
	[horatg] char(6) NULL,
	[grosorcornealOD] int NULL,
	[grosorcornealOI] int NULL,
	[ValorPIOCorregidoOD] int NULL,
	[ValorPIOCorregidoOI] int NULL,
	[godavsc] char(8) NULL,
	[godeje] float NULL,
	[godcil] float NULL,
	[godesf] float NULL,
	[godavcc] char(8) NULL,
	[godadd] float NULL,
	[goiavsc] char(8) NULL,
	[goieje] float NULL,
	[goicil] float NULL,
	[goiesf] float NULL,
	[goiavcc] char(8) NULL,
	[goiadd] float NULL,
	[aodeje] float NULL,
	[aodcil] float NULL,
	[aodesf] float NULL,
	[adi] float NULL,
	[aoieje] float NULL,
	[aoicil] float NULL,
	[aoiesf] float NULL,
	[aodceje] float NULL,
	[aodccil] float NULL,
	[aodcesf] float NULL,
	[aoiceje] float NULL,
	[aoiccil] float NULL,
	[aoicesf] float NULL,
	[eodesf] float NULL,
	[eodcil] float NULL,
	[eodeje] float NULL,
	[eoiesf] float NULL,
	[eoicil] float NULL,
	[eoieje] float NULL,
	[eodcesf] float NULL,
	[eodccil] float NULL,
	[eodceje] float NULL,
	[eoicesf] float NULL,
	[eoiccil] float NULL,
	[eoiceje] float NULL,
	[kodk1] float NULL,
	[kodeje1] float NULL,
	[koik1] float NULL,
	[koieje1] float NULL,
	[kodast] float NULL,
	[kodk2] float NULL,
	[kodeje2] float NULL,
	[koik2] float NULL,
	[koieje2] float NULL,
	[koiast] float NULL,
	[codavsc] char(8) NULL,
	[codeje] float NULL,
	[codcil] float NULL,
	[codesf] float NULL,
	[codavcc] char(8) NULL,
	[codadd] float NULL,
	[codn] float NULL,
	[cavao] char(8) NULL,
	[coiavsc] char(8) NULL,
	[coieje] float NULL,
	[coicil] float NULL,
	[coiesf] float NULL,
	[coiavcc] char(8) NULL,
	[coiadd] float NULL,
	[coin] float NULL,
	[cleyeod] char(1) NULL,
	[cleyeoi] char(1) NULL,
	[cleyeodcc] char(1) NULL,
	[cleyeoicc] char(1) NULL,
	[cleyeavao] char(1) NULL,
	[visi_Jaeger] char(2) NULL DEFAULT (''),
	[rodeje2] float NULL,
	[rodcil2] float NULL,
	[rodesf2] float NULL,
	[rodadd2] float NULL,
	[prismaod2] float NULL,
	[baseod2] varchar(10) NULL,
	[roieje2] float NULL,
	[roicil2] float NULL,
	[roiesf2] float NULL,
	[roiadd2] float NULL,
	[prismaoi2] float NULL,
	[baseoi2] varchar(10) NULL,
	[lente12] varchar(300) NULL,
	[lente22] varchar(30) NULL,
	[rodeje3] float NULL,
	[rodcil3] float NULL,
	[rodesf3] float NULL,
	[rodadd3] float NULL,
	[prismaod3] float NULL,
	[baseod3] varchar(10) NULL,
	[roieje3] float NULL,
	[roicil3] float NULL,
	[roiesf3] float NULL,
	[roiadd3] float NULL,
	[prismaoi3] float NULL,
	[baseoi3] varchar(10) NULL,
	[lente13] varchar(300) NULL,
	[lente23] varchar(30) NULL,
	[NumVisita] bigint NOT NULL,
	[odavsc2] varchar(8) NULL,
	[odeje2] float NULL,
	[odcil2] float NULL,
	[odesf2] float NULL,
	[odavcc2] varchar(8) NULL,
	[odadd2] float NULL,
	[odn2] float NULL,
	[avao2] varchar(8) NULL,
	[oiavsc2] varchar(8) NULL,
	[oieje2] float NULL,
	[oicil2] float NULL,
	[oiesf2] float NULL,
	[oiavcc2] varchar(8) NULL,
	[oiadd2] float NULL,
	[oin2] float NULL,
	[odavsc3] varchar(8) NULL,
	[odeje3] float NULL,
	[odcil3] float NULL,
	[odesf3] float NULL,
	[odavcc3] varchar(8) NULL,
	[odadd3] float NULL,
	[odn3] float NULL,
	[avao3] varchar(8) NULL,
	[oiavsc3] varchar(8) NULL,
	[oieje3] float NULL,
	[oicil3] float NULL,
	[oiesf3] float NULL,
	[oiavcc3] varchar(8) NULL,
	[oiadd3] float NULL,
	[oin3] float NULL,
	[MO_Bielschowsky_Sup_OD] varchar(3) NULL,
	[MO_Bielschowsky_Inf_OD] varchar(3) NULL,
	[MO_Bielschowsky_Sup_OI] varchar(3) NULL,
	[MO_Bielschowsky_Inf_OI] varchar(3) NULL,
	[MO_Der_OD] varchar(3) NULL,
	[MO_Der_Sup_OD] varchar(3) NULL,
	[MO_Der_Inf_OD] varchar(3) NULL,
	[MO_Cen_Sup_OD] varchar(3) NULL,
	[MO_Cen_Inf_OD] varchar(3) NULL,
	[MO_Izq_OD] varchar(3) NULL,
	[MO_Izq_Sup_OD] varchar(3) NULL,
	[MO_Izq_Inf_OD] varchar(3) NULL,
	[MO_Der_OI] varchar(3) NULL,
	[MO_Der_Sup_OI] varchar(3) NULL,
	[MO_Der_Inf_OI] varchar(3) NULL,
	[MO_Cen_Sup_OI] varchar(3) NULL,
	[MO_Cen_Inf_OI] varchar(3) NULL,
	[MO_Izq_OI] varchar(3) NULL,
	[MO_Izq_Sup_OI] varchar(3) NULL,
	[MO_Izq_Inf_OI] varchar(3) NULL,
	[MO_Sin_Correccion] varchar(5) NULL,
	[MO_Con_Correccion] varchar(5) NULL,
	[MO_Lateroversion_OD] varchar(5) NULL,
	[MO_Lateroversion_OI] varchar(5) NULL,
	[MO_Comentarios] varchar(255) NULL,
	[MO_Estereopsis_OI] varchar(30) NULL,
	[MO_Estereopsis_OD] varchar(30) NULL,
	[OP_Apertura_Parpebral_OD] varchar(3) NULL,
	[OP_Apertura_Parpebral_OI] varchar(3) NULL,
	[OP_Funcion_Elevador_OD] varchar(3) NULL,
	[OP_Funcion_Elevador_OI] varchar(3) NULL,
	[OP_Distancia_Marginal_Refleja_OD] varchar(3) NULL,
	[OP_Distancia_Marginal_Refleja_OI] varchar(3) NULL,
	[OP_Fenileprina_OD] char(1) NULL,
	[OP_Fenileprina_OI] char(1) NULL,
	[OP_Irrigacion_Via_Lagrimal_OD] varchar(6) NULL,
	[OP_Irrigacion_Via_Lagrimal_OI] varchar(6) NULL,
	[OP_Optalmometria_D] varchar(20) NULL,
	[OP_Optalmometria_C] varchar(20) NULL,
	[OP_Optalmometria_I] varchar(20) NULL,
	[OP_Comentarios] varchar(255) NULL,
	[tgComentario] varchar(50) NULL,
	[Pio_Meta_OD] float NULL,
	[Pio_Meta_OI] float NULL,
	[DIP_OD] float NULL,
	[DIP_OI] float NULL,
	[gTipoLente] varchar(300) NULL,
	CONSTRAINT [PK_s_Visita_Oftalmologia] PRIMARY KEY CLUSTERED ([NumVisita] ASC)
);
GO

-- Tabla: s_Visita_Ortopedia
CREATE TABLE [dbo].[s_Visita_Ortopedia] (
	[NumVisita] bigint NOT NULL,
	[CAD_FlexionI_RA] int NULL,
	[CAD_FlexionD_RA] int NULL,
	[CAD_FlexionI_FM] int NULL,
	[CAD_FlexionD_FM] int NULL,
	[CAD_FlexionI_TM] int NULL,
	[CAD_FlexionD_TM] int NULL,
	[CAD_ExtensionI_RA] int NULL,
	[CAD_ExtensionD_RA] int NULL,
	[CAD_ExtensionI_FM] int NULL,
	[CAD_ExtensionD_FM] int NULL,
	[CAD_ExtensionI_TM] int NULL,
	[CAD_ExtensionD_TM] int NULL,
	[CAD_AbduccioNOI_RA] int NULL,
	[CAD_AbduccioNOD_RA] int NULL,
	[CAD_AbduccioNOI_FM] int NULL,
	[CAD_AbduccioNOD_FM] int NULL,
	[CAD_Abduccion90I_RA] int NULL,
	[CAD_Abduccion90D_RA] int NULL,
	[CAD_AbduccionI_RA] int NULL,
	[CAD_AbduccionD_RA] int NULL,
	[CAD_AbduccionI_FM] int NULL,
	[CAD_AbduccionD_FM] int NULL,
	[CAD_AbduccionI_TM] int NULL,
	[CAD_AbduccionD_TM] int NULL,
	[CAD_AntFemI] int NULL,
	[CAD_AntFemD] int NULL,
	[CAD_RotIntI] int NULL,
	[CAD_RotIntD] int NULL,
	[CAD_RotExtI] int NULL,
	[CAD_RotExtD] int NULL,
	[CAD_AngPMI] int NULL,
	[CAD_AngPMD] int NULL,
	[CAD_TroMayI] int NULL,
	[CAD_TroMayD] int NULL,
	[ROD_FlexionI_RA] int NULL,
	[ROD_FlexionD_RA] int NULL,
	[ROD_FlexionI_FM] int NULL,
	[ROD_FlexionD_FM] int NULL,
	[ROD_FlexionI_TM] int NULL,
	[ROD_FlexionD_TM] int NULL,
	[ROD_ExtensionI_RA] int NULL,
	[ROD_ExtensionD_RA] int NULL,
	[ROD_ExtensionI_FM] int NULL,
	[ROD_ExtensionD_FM] int NULL,
	[ROD_ExtensionI_TM] int NULL,
	[ROD_ExtensionD_TM] int NULL,
	[ROD_AngPopliteoI] int NULL,
	[ROD_AngPopliteoD] int NULL,
	[ROD_AngThomasI] int NULL,
	[ROD_AngThomasD] int NULL,
	[ROD_FlexCadRodI] int NULL,
	[ROD_FlexCadRodD] int NULL,
	[ROD_ExtensorLagI] int NULL,
	[ROD_ExtensorLagD] int NULL,
	[ROD_PatelaAltaI] char(1) NULL DEFAULT ('N'),
	[ROD_PatelaAltaD] char(1) NULL DEFAULT ('N'),
	[ROD_Dial30CPI] int NULL,
	[ROD_Dial30CPD] int NULL,
	[ROD_Dial30CSI] int NULL,
	[ROD_Dial30CSD] int NULL,
	[ROD_Dial90CPI] int NULL,
	[ROD_Dial90CPD] int NULL,
	[ROD_Dial90CSI] int NULL,
	[ROD_Dial90CSD] int NULL,
	[ROD_ApleyI] char(1) NULL DEFAULT ('-'),
	[ROD_ApleyD] char(1) NULL DEFAULT ('-'),
	[ROD_McMurrayI] char(1) NULL DEFAULT ('-'),
	[ROD_McMurrayD] char(1) NULL DEFAULT ('-'),
	[ROD_CajonAI] char(1) NULL DEFAULT ('-'),
	[ROD_CajonAD] char(1) NULL DEFAULT ('-'),
	[ROD_CajonPI] char(1) NULL DEFAULT ('-'),
	[ROD_CajonPD] char(1) NULL DEFAULT ('-'),
	[ROD_Lachman30I] char(1) NULL DEFAULT ('-'),
	[ROD_Lachman30D] char(1) NULL DEFAULT ('-'),
	[ROD_Lachman45I] char(1) NULL DEFAULT ('-'),
	[ROD_Lachman45D] char(1) NULL DEFAULT ('-'),
	[ROD_VaroI] char(3) NULL DEFAULT ('+'),
	[ROD_Varod] char(3) NULL DEFAULT ('+'),
	[ROD_ValgoI] char(3) NULL DEFAULT ('+'),
	[ROD_ValgoD] char(3) NULL DEFAULT ('+'),
	[ROD_RoceCrujidoI] char(1) NULL DEFAULT ('-'),
	[ROD_RoceCrujidoD] char(1) NULL DEFAULT ('-'),
	[ROD_DeamAsis] char(1) NULL DEFAULT ('-'),
	[ROD_MetCamina] int NULL,
	[TOB_DorsiFlexion90I_RA] int NULL,
	[TOB_DorsiFlexion90D_RA] int NULL,
	[TOB_DorsiFlexion90I_FM] int NULL,
	[TOB_DorsiFlexion90D_FM] int NULL,
	[TOB_DorsiFlexion90I_TM] int NULL,
	[TOB_DorsiFlexion90D_TM] int NULL,
	[TOB_DorsiFlexioNOI_RA] int NULL,
	[TOB_DorsiFlexioNOD_RA] int NULL,
	[TOB_FlexionPlantarI_RA] int NULL,
	[TOB_FlexionPlantarD_RA] int NULL,
	[TOB_FlexionPlantarI_FM] int NULL,
	[TOB_FlexionPlantarD_FM] int NULL,
	[TOB_FlexionPlantarI_TM] int NULL,
	[TOB_FlexionPlantarD_TM] int NULL,
	[TOB_InversionI_RA] int NULL,
	[TOB_InversionD_RA] int NULL,
	[TOB_InversionI_FM] int NULL,
	[TOB_InversionD_FM] int NULL,
	[TOB_InversionI_TM] int NULL,
	[TOB_InversionD_TM] int NULL,
	[TOB_EversionI_RA] int NULL,
	[TOB_EversionD_RA] int NULL,
	[TOB_EversionI_FM] int NULL,
	[TOB_EversionD_FM] int NULL,
	[TOB_EversionI_TM] int NULL,
	[TOB_EversionD_TM] int NULL,
	[PIE_AntepieI_SA] varchar(20) NULL,
	[PIE_AntepieD_SA] varchar(20) NULL,
	[PIE_AntepieI_CA] varchar(20) NULL,
	[PIE_AntepieD_CA] varchar(20) NULL,
	[PIE_ReductibleI] varchar(20) NULL,
	[PIE_ReductibleD] varchar(20) NULL,
	[PIE_QuiebreMPI] varchar(20) NULL,
	[PIE_QuiebreMPD] varchar(20) NULL,
	[PIE_Reductible2I] varchar(20) NULL,
	[PIE_Reductible2D] varchar(20) NULL,
	[PIE_RetropieI_SA] varchar(20) NULL,
	[PIE_RetropieD_SA] varchar(20) NULL,
	[PIE_RetropieI_CA] varchar(20) NULL,
	[PIE_RetropieD_CA] varchar(20) NULL,
	[PIE_EjeBimaleolarI] int NULL,
	[PIE_EjeBimaleolarD] int NULL,
	[PIE_AngPMI] int NULL,
	[PIE_AngPMD] int NULL,
	[LongMSISAI] float NULL,
	[LongMSISAD] float NULL,
	[LongMSISRI] float NULL,
	[LongMSISRD] float NULL,
	[RotulianoREFI] varchar(3) NULL,
	[RotulianoREFD] varchar(3) NULL,
	[RotulianoCLOI] varchar(3) NOT NULL,
	[RotulianoCLOD] varchar(3) NULL,
	[AquileoREFI] varchar(3) NULL,
	[AquileoREFD] varchar(3) NULL,
	[AquileoCLOI] varchar(3) NOT NULL,
	[AquileoCLOD] varchar(3) NULL,
	[GMFCS] int NULL,
	[PE_PsoasI] char(1) NULL,
	[PE_PsoasD] char(1) NULL,
	[PE_AdductoresI] char(1) NULL,
	[PE_AdductoresD] char(1) NULL,
	[PE_PhelpI] char(1) NULL,
	[PE_PhelpD] char(1) NULL,
	[PE_ElysDuncanI] char(1) NULL,
	[PE_ElysDuncanD] char(1) NULL,
	[PE_SilverSkioldI] char(1) NULL,
	[PE_SilverSkioldD] char(1) NULL,
	[PE_ConfusionI] char(1) NULL,
	[PE_ConfusionD] char(1) NULL,
	[PE_ImpigementTestI] char(1) NULL,
	[PE_ImpigementTestD] char(1) NULL,
	[PE_McCarthyI] char(1) NULL,
	[PE_McCarthyD] char(1) NULL,
	[PE_FaberI] char(1) NULL,
	[PE_FaberD] char(1) NULL,
	[PE_OberI] char(1) NULL,
	[PE_OberD] char(1) NULL,
	[PE_CaderaEnResorteI] char(1) NULL,
	[PE_CaderaEnResorteD] char(1) NULL,
	[CER_Nurick] int NULL,
	[CER_Modic] int NULL,
	[CER_VisualAnaloga] int NULL,
	[CER_Joa] int NULL,
	[CER_VisualAnalogaJoa] int NULL,
	[CER_DanielsC5] int NULL,
	[CER_ReflejoC5] char(1) NULL,
	[CER_SensibilidadC5] char(1) NULL,
	[CER_DanielsC6] int NULL,
	[CER_ReflejoC6] char(1) NULL,
	[CER_SensibilidadC6] char(1) NULL,
	[CER_DanielsC7] int NULL,
	[CER_ReflejoC7] char(1) NULL,
	[CER_SensibilidadC7] char(1) NULL,
	[CER_DanielsC8] int NULL,
	[CER_ReflejoC8] char(1) NULL,
	[CER_SensibilidadC8] char(1) NULL,
	[CER_DanielsT1] int NULL,
	[CER_ReflejoT1] char(1) NULL,
	[CER_SensibilidadT1] char(1) NULL,
	[LUM_Joa] int NULL,
	[LUM_ControlEsfin] char(1) NULL,
	[LUM_VisualAnaloga] int NULL,
	[LUM_Modif] int NULL,
	[LUM_DanielsT12L1] int NULL,
	[LUM_ReflejoT12L1] char(1) NULL,
	[LUM_SensibilidadT2L1] char(1) NULL,
	[LUM_DanielsL3L4] int NULL,
	[LUM_ReflejoL3L4] char(1) NULL,
	[LUM_SensibilidadL3L4] char(1) NULL,
	[LUM_DanielsL5] int NULL,
	[LUM_ReflejoL5] char(1) NULL,
	[LUM_SensibilidadL5] char(1) NULL,
	[LUM_DanielsS1] int NULL,
	[LUM_ReflejoS1] char(1) NULL,
	[LUM_SensibilidadS1] char(1) NULL,
	[DEF_King] int NULL,
	[DEF_Lenke] int NULL,
	[DEF_ModLumbar] char(1) NULL,
	[DEF_PlanoSagital] char(1) NULL,
	[FRA_Morfologia] char(2) NULL,
	[FRA_Neurologico] char(1) NULL,
	[FRA_ComplejoLiga] char(1) NULL,
	[FRA_OsteoGlobal] int NULL,
	[FRA_OsteoL1] int NULL,
	[FRA_OsteoL2] int NULL,
	[FRA_OsteoL3] int NULL,
	[FRA_OsteoL4] int NULL,
	[FRA_OsteoCuelloFem] int NULL,
	[FRA_OsteoTriWard] int NULL,
	[Comentario] varchar(2000) NULL,
	[HOM_DolorNocturno] char(1) NULL,
	[HOM_CrujidoHombro] char(1) NULL,
	[HOM_PrimoLuxacion] char(1) NULL,
	[HOM_LuxacionRecidivante] char(1) NULL,
	[HOM_ColCervical] char(1) NULL,
	[HOM_Hombro_PD] char(1) NULL,
	[HOM_Brazo_PD] char(1) NULL,
	[HOM_Codo_PD] char(1) NULL,
	[HOM_Antebrazo_PD] char(1) NULL,
	[HOM_Mano_PD] char(1) NULL,
	[HOM_Axilar_ND] char(1) NULL,
	[HOM_MusCutaneo_ND] char(1) NULL,
	[HOM_Radial_ND] char(1) NULL,
	[HOM_Cubital_ND] char(1) NULL,
	[HOM_Mediano_ND] char(1) NULL,
	[HOM_Hombro_PI] char(1) NULL,
	[HOM_Brazo_PI] char(1) NULL,
	[HOM_Codo_PI] char(1) NULL,
	[HOM_Antebrazo_PI] char(1) NULL,
	[HOM_Mano_PI] char(1) NULL,
	[HOM_Axilar_NI] char(1) NULL,
	[HOM_MusCutaneo_NI] char(1) NULL,
	[HOM_Radial_NI] char(1) NULL,
	[HOM_Cubital_NI] char(1) NULL,
	[HOM_Mediano_NI] char(1) NULL,
	[HOM_FlexionD] int NULL,
	[HOM_ExtensionD] int NULL,
	[HOM_RotExtD] int NULL,
	[HOM_RotIntD] int NULL,
	[HOM_AbduccionD] int NULL,
	[HOM_FlexionI] int NULL,
	[HOM_ExtensionI] int NULL,
	[HOM_RotExtI] int NULL,
	[HOM_RotIntI] int NULL,
	[HOM_AbduccionI] int NULL,
	[HOM_CicatricesD] char(1) NULL,
	[HOM_AtrofiaD] char(1) NULL,
	[HOM_CicatricesI] char(1) NULL,
	[HOM_AtrofiaI] char(1) NULL,
	[PH_NeerD] char(1) NULL,
	[PH_HawkinsD] char(1) NULL,
	[PH_YocumD] char(1) NULL,
	[PH_NeerI] char(1) NULL,
	[PH_HawkinsI] char(1) NULL,
	[PH_YocumI] char(1) NULL,
	[PH_JobeD] char(1) NULL,
	[PH_PatteD] char(1) NULL,
	[PH_HertelD] char(1) NULL,
	[PH_GerberD] char(1) NULL,
	[PH_NapoleonD] char(1) NULL,
	[PH_AbrazoOsoD] char(1) NULL,
	[PH_JobeI] char(1) NULL,
	[PH_PatteI] char(1) NULL,
	[PH_HertelI] char(1) NULL,
	[PH_GerberI] char(1) NULL,
	[PH_NapoleonI] char(1) NULL,
	[PH_AbrazoOsoI] char(1) NULL,
	[PH_ObrienD] char(1) NULL,
	[PH_SpeedD] char(1) NULL,
	[PH_YegarsonD] char(1) NULL,
	[PH_SignoPopeyeD] char(1) NULL,
	[PH_ObrienI] char(1) NULL,
	[PH_SpeedI] char(1) NULL,
	[PH_YegarsonI] char(1) NULL,
	[PH_SignoPopeyeI] char(1) NULL,
	[PH_AprehensionD] char(1) NULL,
	[PH_CajonD] char(1) NULL,
	[PH_SignoSurcoD] char(1) NULL,
	[PH_JerkTestD] char(1) NULL,
	[PH_KimD] char(1) NULL,
	[PH_AprehensionI] char(1) NULL,
	[PH_CajonI] char(1) NULL,
	[PH_SignoSurcoI] char(1) NULL,
	[PH_JerkTestI] char(1) NULL,
	[PH_KimI] char(1) NULL,
	[CODO_FlexionD] int NULL,
	[CODO_ExtensionD] int NULL,
	[CODO_SupinacionD] int NULL,
	[CODO_PronacionD] int NULL,
	[CODO_FlexionI] int NULL,
	[CODO_ExtensionI] int NULL,
	[CODO_SupinacionI] int NULL,
	[CODO_PronacionI] int NULL,
	[MUN_FlexionD] int NULL,
	[MUN_ExtensionD] int NULL,
	[MUN_DesvRadialD] int NULL,
	[MUN_DesvCubitalD] int NULL,
	[MUN_FlexionI] int NULL,
	[MUN_ExtensionI] int NULL,
	[MUN_DesvRadialI] int NULL,
	[MUN_DesvCubitalI] int NULL,
	[CAD_Abduccion0I_RA] int NULL,
	[CAD_Abduccion0D_RA] int NULL,
	[CAD_Abduccion0I_FM] int NULL,
	[CAD_Abduccion0D_FM] int NULL,
	[TOB_DorsiFlexion0I_RA] int NULL,
	[TOB_DorsiFlexion0D_RA] int NULL,
	CONSTRAINT [PK_s_Visita_Ortopedia] PRIMARY KEY CLUSTERED ([NumVisita] ASC)
);
GO

-- Tabla: s_Visita_Otorrino
CREATE TABLE [dbo].[s_Visita_Otorrino] (
	[visi_NarizSenos] text NULL,
	[visi_NasoFaringe] text NULL,
	[visi_CavidadBucal] text NULL,
	[visi_OrofaringeAmigdalas] text NULL,
	[visi_Laringe] text NULL,
	[visi_Oidos] text NULL,
	[visi_Cuello] text NULL,
	[NumVisita] bigint NOT NULL,
	CONSTRAINT [PK_s_Visita_Otorrino] PRIMARY KEY CLUSTERED ([NumVisita] ASC)
);
GO

-- Tabla: s_Visita_Psicologia
CREATE TABLE [dbo].[s_Visita_Psicologia] (
	[NumVisita] bigint NOT NULL,
	[Comentario] text NULL,
	CONSTRAINT [PK_s_Visita_Psicologia] PRIMARY KEY CLUSTERED ([NumVisita] ASC)
);
GO

-- Tabla: s_Visita_Terapia
CREATE TABLE [dbo].[s_Visita_Terapia] (
	[NumVisita] bigint NOT NULL,
	[IdTipoTerapia] varchar(5) NULL,
	[Comentario] text NULL,
	[RealizadoPor] varchar(15) NULL,
	CONSTRAINT [PK_s_Visita_Terapia] PRIMARY KEY CLUSTERED ([NumVisita] ASC)
);
GO

-- Tabla: s_Visita_Urologia
CREATE TABLE [dbo].[s_Visita_Urologia] (
	[visi_Antecedente] text NULL,
	[visi_ExploracionFisica] text NULL,
	[memo] text NULL,
	[NumVisita] bigint NOT NULL,
	CONSTRAINT [PK_s_Visita_Urologia] PRIMARY KEY CLUSTERED ([NumVisita] ASC)
);
GO

-- Tabla: s_VisitaAnalisis
CREATE TABLE [dbo].[s_VisitaAnalisis] (
	[visa_IdAnalisis] varchar(10) NOT NULL,
	[NumVisita] bigint NOT NULL,
	CONSTRAINT [PK_s_VisitaAnalisis] PRIMARY KEY CLUSTERED ([NumVisita] ASC, [visa_IdAnalisis] ASC)
);
GO

-- Tabla: s_VisitaAnalisisResultado
CREATE TABLE [dbo].[s_VisitaAnalisisResultado] (
	[NumVisita] bigint NOT NULL,
	[IdAnalisis] varchar(10) NOT NULL,
	[Resultado] varchar(50) NULL,
	CONSTRAINT [PK_s_VisitaAnalisisResultado] PRIMARY KEY CLUSTERED ([NumVisita] ASC, [IdAnalisis] ASC)
);
GO

-- Tabla: s_VisitaDiagnosticos
CREATE TABLE [dbo].[s_VisitaDiagnosticos] (
	[visd_IdDiagnostico] varchar(10) NOT NULL,
	[visd_Ojo] varchar(5) NULL,
	[NumVisita] bigint NOT NULL,
	CONSTRAINT [PK_s_VisitaDiagnosticos] PRIMARY KEY CLUSTERED ([NumVisita] ASC, [visd_IdDiagnostico] ASC)
);
GO

-- Tabla: s_VisitaEstudios
CREATE TABLE [dbo].[s_VisitaEstudios] (
	[vise_IdEstudio] varchar(10) NOT NULL,
	[vise_RealizadoPor] varchar(15) NULL,
	[vise_MontoARecibir] float NULL,
	[vise_ResultadoEstudio] text NULL,
	[NumVisita] bigint NOT NULL,
	CONSTRAINT [PK_s_visitaEstudios] PRIMARY KEY CLUSTERED ([NumVisita] ASC, [vise_IdEstudio] ASC)
);
GO

-- Tabla: SecuenciaCF
CREATE TABLE [dbo].[SecuenciaCF] (
	[IdSecCF] varchar(10) NOT NULL,
	[Descripcion] varchar(50) NULL,
	[IdSerie] char(1) NULL,
	[IdDivisionNegocio] char(2) NULL,
	[IdPuntoEmision] char(3) NULL,
	[IdArea] char(3) NULL,
	[IdTipoCF] char(2) NULL,
	[ProximoNumero] int NULL,
	[Desde] int NULL,
	[Hasta] int NULL,
	[Minimo] int NULL,
	[ProxSecDesde] int NULL,
	[ProxSecHasta] int NULL,
	[ProxSecMinimo] int NULL,
	[Estatus] char(1) NULL,
	CONSTRAINT [PK_SecuenciaCF] PRIMARY KEY CLUSTERED ([IdSecCF] ASC)
);
GO

-- Tabla: Suplidor_Referencias
CREATE TABLE [dbo].[Suplidor_Referencias] (
	[CodSup] varchar(10) NOT NULL,
	[CodRef] varchar(30) NOT NULL,
	[CostoFOB] decimal(8, 2) NULL,
	[IdMoneda] varchar(3) NULL,
	CONSTRAINT [PK_Suplidor_Referencias] PRIMARY KEY CLUSTERED ([CodSup] ASC, [CodRef] ASC)
);
GO

-- Tabla: TipoArticulo_Almacen
CREATE TABLE [dbo].[TipoArticulo_Almacen] (
	[codtart] varchar(5) NOT NULL,
	[codalm] varchar(2) NOT NULL,
	[numcuen] varchar(15) NULL,
	[ncuencos] varchar(15) NULL,
	[ncuening] varchar(15) NULL,
	CONSTRAINT [PK_TipoArticulo_Almacen] PRIMARY KEY CLUSTERED ([codtart] ASC, [codalm] ASC)
);
GO

-- Tabla: TipoIngresoCF
CREATE TABLE [dbo].[TipoIngresoCF] (
	[IdTipoIngresoCF] char(1) NOT NULL,
	[NomTipoIngresoCF] varchar(50) NULL,
	[Estatus] char(1) NULL DEFAULT ('A'),
	CONSTRAINT [PK_TipoIngresoCF] PRIMARY KEY CLUSTERED ([IdTipoIngresoCF] ASC)
);
GO

-- Tabla: TipoRetISR
CREATE TABLE [dbo].[TipoRetISR] (
	[IdTipoRetISR] varchar(2) NOT NULL,
	[NomTipoRetISR] varchar(50) NULL,
	[Estatus] char(1) NULL DEFAULT ('A'),
	CONSTRAINT [PK_TipoRetISR] PRIMARY KEY CLUSTERED ([IdTipoRetISR] ASC)
);
GO

-- Tabla: TipoTarjeta
CREATE TABLE [dbo].[TipoTarjeta] (
	[IdTipoTarjeta] varchar(10) NOT NULL,
	[NomTipoTarjeta] varchar(50) NULL,
	[Estatus] char(1) NULL,
	CONSTRAINT [PK_Tarjeta] PRIMARY KEY CLUSTERED ([IdTipoTarjeta] ASC)
);
GO

-- Tabla: Transferencia_Articulos
CREATE TABLE [dbo].[Transferencia_Articulos] (
	[NumTransf] int NOT NULL,
	[Linea] int NOT NULL,
	[CodArt] varchar(16) NULL,
	[Cantidad] decimal(12, 2) NULL,
	[Costo] decimal(12, 4) NULL,
	[Unidad] varchar(6) NULL,
	[Descripcion] varchar(2000) NULL,
	[TotUni] decimal(9, 2) NULL,
	[Lote] varchar(10) NULL,
	[FechaVenc] date NULL,
	CONSTRAINT [PK_Transferencia_Articulos] PRIMARY KEY CLUSTERED ([NumTransf] ASC, [Linea] ASC)
);
GO

-- Tabla: Vendedor_TipoArticulo
CREATE TABLE [dbo].[Vendedor_TipoArticulo] (
	[CodVen] varchar(5) NOT NULL,
	[CodTArt] varchar(5) NOT NULL,
	[PrCom] decimal(5, 2) NULL,
	CONSTRAINT [PK_Vendedor_TipoArticulo] PRIMARY KEY CLUSTERED ([CodVen] ASC, [CodTArt] ASC)
);
GO

-- Tabla: Zona
CREATE TABLE [dbo].[Zona] (
	[CodZona] varchar(5) NOT NULL,
	[Nomzona] varchar(50) NULL,
	[Estatus] char(1) NULL DEFAULT ('A'),
	CONSTRAINT [PK_Zona] PRIMARY KEY CLUSTERED ([CodZona] ASC)
);
GO


-- ==========================================================================
-- VISTAS (VIEWS)
-- ==========================================================================

-- Vista: s_CompraLente
--2022-01-05  Si el codigo del cliente es alfanumerico, el view daba un error
CREATE View s_CompraLente 
AS
SELECT IdFac AS Numero, FechaFac AS Fecha, SUBSTRING(CodCli, 2, 9) AS NumeroExpediente, NumFac, MontoLocal AS Monto, Desmov AS Descripcion, RegPor, Estatus, NULL AS IdOptica, SUBSTRING(CodVen, 2, 9) AS IdDoctor, NULL
                          AS FechaReceta
FROM  Sin.dbo.Factura
WHERE        (Estatus = 'P') AND (IsNumeric(SUBSTRING(CodCli, 2, 9)) = 1)
GO

-- Vista: s_SecuenciaCF
CREATE View s_SecuenciaCF 
AS
SELECT IdSecCF COLLATE DATABASE_DEFAULT AS Id, Descripcion, IdSerie, IdDivisionNegocio, IdPuntoEmision, IdArea, IdTipoCF COLLATE DATABASE_DEFAULT AS IdTipoCF, ProximoNumero AS Numero, Desde, Hasta, Minimo, 
                  ProxSecDesde AS ProximoDesde, ProxSecHasta AS ProximoHasta, ProxSecMinimo AS ProximoMinimo, FechaVen, ProxFechaVen, Estatus
FROM     Sin.dbo.SecuenciaCF
GO

-- Vista: v_DirectorioTelefonico
/****** Object:  View dbo.v_DirectorioTelefonico    Script Date: 28/11/2007 08:56:08 a.m. ******/
cREATE VIEW dbo.v_DirectorioTelefonico
AS
SELECT     'D' AS TipoEntrada, cast(dirt_Numero as char(10))  AS Id, dirt_Nombre AS Nombre, dirt_Ocupacion AS Ocupacion, dirt_Telef1 AS Telef1, dirt_Telef2 AS Telef2, dirt_LugarTrabajo as LugarTrabajo
FROM         dbo.s_DirectorioTelefonico
union
SELECT     'E' AS TipoEntrada, entr_IdEntidad AS Id, entr_Nombre AS Nombre, 
case when entr_TipoEntidad='D' then 'Doctor' when entr_TipoEntidad='s' then 'Seguro' when entr_TipoEntidad='L' then 'Laboratorio' end  AS Ocupacion, 
entr_Telefono AS Telef1, entr_Telefono2 AS Telef2, entr_LugarTrabajo as LugarTrabajo
FROM         dbo.s_EntidadReferimiento
union
SELECT     'S' AS TipoEntrada, segu_IdSeguro AS Id, segu_Nombre AS Nombre, 'Seguro' AS Ocupacion, segu_Telefono1 AS Telef1, segu_Telefono2 AS Telef2, segu_nombre as LugarTrabajo
FROM         dbo.s_Seguro
GO

-- Vista: v_ExpedienteUltimaVisita
CREATE VIEW [dbo].[v_ExpedienteUltimaVisita]
AS
SELECT        UV.NumeroExpediente, UV.CantidadVisita, UV.NumVisita, s_Visita_1.visi_FechaVisita AS UltimaVisita, s_Visita_1.visi_IdDoctor
FROM            (SELECT        visi_NumeroExpediente AS NumeroExpediente, COUNT(visi_FechaVisita) AS CantidadVisita, MAX(NumVisita) AS NumVisita
                          FROM            dbo.s_Visita
                          GROUP BY visi_NumeroExpediente) AS UV INNER JOIN
                         dbo.s_Visita AS s_Visita_1 ON UV.NumVisita = s_Visita_1.NumVisita
GO

-- Vista: v_FacturasSeguro
CREATE VIEW [dbo].[v_FacturasSeguro]
AS
SELECT        'COB' AS Tipo, hpag_IdCaja, hpag_numero, hpag_IdSeguro, hpag_Fecha, hpag_FechaAut, hpag_NumeroPoliza, hpag_NumeroExpediente, hpag_Nombre, hpag_Apellido, hpag_NumeroAfiliado, hpag_NumeroAutorizacion, 
                         hpag_CoberturaSeguro, hpag_MontoPagado, hpag_MontoDebito, hpag_NumeroReclamacionSeguro,  hpag_Estatus, hpag_IdDoctor, hpag_DesMov, hpag_FechaSaldo
FROM            s_HeaderPago
WHERE        ISNULL(hpag_CoberturaSeguro, 0.0) > 0 AND s_HeaderPago.hpag_Estatus ='P'
UNION
SELECT        'DSC' AS Tipo, s_HeaderPago_Descuento.hpag_IdCaja, s_HeaderPago_Descuento.hpag_numero, s_HeaderPago_Descuento.hpag_IdSeguro, hpag_Fecha, hpag_FechaAut, hpag_NumeroPoliza, hpag_NumeroExpediente, 
                         hpag_Nombre, hpag_Apellido, hpag_NumeroAfiliado, hpag_NumeroAutorizacion, s_HeaderPago_Descuento.hpag_Descuento AS hpag_CoberturaSeguro, s_HeaderPago_Descuento.hpag_MontoPagado, 0.0 as hpag_MontoDebito, s_HeaderPago_Descuento.hpag_NumeroReclamacionSeguro, 
                         hpag_Estatus, hpag_IdDoctor, hpag_DesMov, s_HeaderPago.hpag_FechaSaldo
FROM            s_HeaderPago_Descuento INNER JOIN
                         s_HeaderPago ON s_HeaderPago_Descuento.hpag_IdCaja = s_HeaderPago.hpag_IdCaja AND s_HeaderPago_Descuento.hpag_numero = s_HeaderPago.hpag_numero
WHERE        ISNULL(s_HeaderPago_Descuento.hpag_Descuento, 0.0) > 0 AND Isnull(s_HeaderPago_Descuento.hpag_IdSeguro, '') <> '' AND s_HeaderPago.hpag_Estatus  ='P'
GO

-- Vista: v_HeaderPago_DesCobertura
CREATE View v_HeaderPago_DesCobertura
as
SELECT DISTINCT D2.dpag_IdCaja as IdCaja, D2.dpag_numero as Numero, 
    SUBSTRING(
        (
            SELECT ', '+s_ConceptoPago.conc_nombre  AS [text()]
            FROM dbo.s_DetallePago D1
			Inner join s_ConceptoPago on D1.dpag_IdConcepto= s_ConceptoPago.conc_IdConcepto
            WHERE D1.dpag_IdCaja=D2.dpag_IdCaja and D1.dpag_numero = D2.dpag_numero 
			     and  (Isnull(D1.dpag_CoberturaSeguro,0)>0 or IsNull(D1.dpag_CoberturaSeguroInterno,0)>0)
            ORDER BY D1.dpag_numero
            FOR XML PATH ('')
        ), 3, 1000) Conceptos
FROM dbo.s_DetallePago D2
where  (Isnull(D2.dpag_CoberturaSeguro,0)>0 or IsNull(D2.dpag_CoberturaSeguroInterno,0)>0)
GO

-- Vista: v_HeaderPago_Doctor
CREATE VIEW [dbo].[v_HeaderPago_Doctor]
AS
SELECT        '1. COMO DOCTOR UNICO' AS PagoDoctor, hpag_IdCaja, hpag_Numero, hpag_fecha, hpag_NumeroExpediente, hpag_Nombre, hpag_apellido, hpag_IdSeguro, hpag_monto, hpag_CoberturaSeguro, hpag_Descuento, 
                         hpag_TipoPago, hpag_ValorRecibidoEfectivo, hpag_ValorRecibidoCheque, hpag_ValorRecibidoTarjeta, hpag_ValorRecibidoTransf, hpag_Estatus, hpag_IdEntidadRef, hpag_IdDoctor, hpag_IdDoctor2,hpag_Procedencia
FROM            s_HeaderPago
WHERE        IsNull(hpag_IdDoctor2, '') = ''
UNION
SELECT        '2. COMO PRINCIPAL   ' AS PagoDoctor, hpag_IdCaja, hpag_Numero, hpag_fecha, hpag_NumeroExpediente, hpag_Nombre, hpag_apellido, hpag_IdSeguro, hpag_monto, hpag_CoberturaSeguro, hpag_Descuento, 
                         hpag_TipoPago, hpag_ValorRecibidoEfectivo, hpag_ValorRecibidoCheque, hpag_ValorRecibidoTarjeta, hpag_ValorRecibidoTransf, hpag_Estatus, hpag_IdEntidadRef, hpag_IdDoctor, hpag_IdDoctor2,hpag_Procedencia
FROM            s_HeaderPago
WHERE        IsNull(hpag_IdDoctor, '') <> '' AND IsNull(hpag_IdDoctor2, '') <> ''
UNION
SELECT        '3. COMO ASISTENTE   ' AS PagoDoctor, hpag_IdCaja, hpag_Numero, hpag_fecha, hpag_NumeroExpediente, hpag_Nombre, hpag_apellido, hpag_IdSeguro, hpag_monto, hpag_CoberturaSeguro, hpag_Descuento, 
                         hpag_TipoPago, hpag_ValorRecibidoEfectivo, hpag_ValorRecibidoCheque, hpag_ValorRecibidoTarjeta, hpag_ValorRecibidoTransf, hpag_Estatus, hpag_IdEntidadRef, hpag_IdDoctor2 AS hpag_IdDoctor, hpag_IdDoctor AS hpag_IdDoctor2, hpag_Procedencia
FROM            s_HeaderPago
WHERE        IsNull(hpag_IdDoctor, '') <> '' AND IsNull(hpag_IdDoctor2, '') <> ''
GO

-- Vista: v_HeaderPago_PrecioLista
CREATE VIEW [dbo].[v_HeaderPago_PrecioLista]
AS
SELECT     dbo.s_HeaderPago.hpag_IdCaja, dbo.s_HeaderPago.hpag_Numero, SUM(dbo.s_ConceptoPago.conc_PrecioASugerir) AS MontoLista
FROM         dbo.s_DetallePago INNER JOIN
                      dbo.s_HeaderPago ON dbo.s_DetallePago.dpag_IdCaja = dbo.s_HeaderPago.hpag_IdCaja AND dbo.s_DetallePago.dpag_Numero = dbo.s_HeaderPago.hpag_Numero INNER JOIN
                      dbo.s_ConceptoPago ON dbo.s_DetallePago.dpag_IdConcepto = dbo.s_ConceptoPago.conc_IdConcepto
GROUP BY dbo.s_HeaderPago.hpag_IdCaja, dbo.s_HeaderPago.hpag_Numero
GO

-- Vista: v_HeaderPago_ReferidoPor
CREATE VIEW [dbo].[v_HeaderPago_ReferidoPor]
AS
SELECT     dbo.s_ListaPaciente.lisp_IdCaja, dbo.s_ListaPaciente.lisp_NumeroPago, MIN(dbo.s_EntidadReferimiento.entr_Procedencia) AS Procedencia, MIN(dbo.s_EntidadReferimiento.entr_nombre) 
                      AS ReferidoPor
FROM         dbo.s_ListaPaciente INNER JOIN
                      dbo.s_EntidadReferimiento ON dbo.s_ListaPaciente.lisp_IdEntidadRef = dbo.s_EntidadReferimiento.entr_IdEntidad
GROUP BY dbo.s_ListaPaciente.lisp_IdCaja, dbo.s_ListaPaciente.lisp_NumeroPago
HAVING      (dbo.s_ListaPaciente.lisp_NumeroPago IS NOT NULL)
GO

-- Vista: v_HeaderPago_TotalPagado
/****** Object:  View dbo.v_TotalesVisitaPorDoctor    Script Date: 28/11/2007 08:56:08 a.m. ******/
CREATE VIEW [dbo].[v_HeaderPago_TotalPagado]
AS
Select hpag_IdCaja, hpag_numero, Sum(Monto) as MontoPagado, Max(Fecha) as UltFechaPago
from (
Select 'P' as tipo, s_PagoSeguro.pags_Numero as Numero, hpag_IdCaja, hpag_numero, Monto, s_PagoSeguro.pags_Fecha as Fecha
from s_PagoSeguro_Detalles
Inner join s_PagoSeguro on s_PagoSeguro_Detalles.pags_Numero = s_PagoSeguro.pags_numero
where s_PagoSeguro.pags_Estatus='P'
Union
Select 'C' as Tipo, s_NotaCreditoSeguro.numero as Numero, hpag_IdCaja, hpag_numero, s_NotaCreditoSeguro_Detalles.Monto, s_NotaCreditoSeguro.fecha
from s_NotaCreditoSeguro_Detalles
Inner join s_NotaCreditoSeguro on s_NotaCreditoSeguro_Detalles.Numero = s_NotaCreditoSeguro.numero
where s_NotaCreditoSeguro.Estatus='P'
) s
Group by hpag_IdCaja, hpag_Numero
GO

-- Vista: v_ListaEspecial
CREATE VIEW [dbo].[v_ListaEspecial]
AS
SELECT s_ListaEspecial.lise_IdLista, s_ListaEspecial.lise_nombre, s_ListaEspecial.lise_TipoLista,  s_ListaEspecial.lise_Restringida, s_ListaEspecial.lise_estatus, cast(s_ListaEspecial.lise_DesProc  as varchar(2000)) as lise_DesProc FROM s_ListaEspecial 
Union
Select 'C' +  IdCombo as lise_IdLista, DesCombo as lise_Nombre, 'C' as lise_TipoLista, 0 as lise_Restrigida,  Estatus as lise_Estatus, NULL as lise_DesProc from s_ComboTipoCirugia
GO

-- Vista: v_NivelPrecio
CREATE view v_NivelPrecio 
as
Select 1 as NivPre, conf_lblNivPre1 as lblNivPre from s_Configuracion where IsNull(conf_lblNivPre1,'')<>''
union
Select 2 as NivPre, conf_lblNivPre2 as lblNivPre from s_Configuracion where IsNull(conf_lblNivPre2,'')<>''
union
Select 3 as NivPre, conf_lblNivPre3 as lblNivPre from s_Configuracion where IsNull(conf_lblNivPre3,'')<>''
union
Select 4 as NivPre, conf_lblNivPre4 as lblNivPre from s_Configuracion where IsNull(conf_lblNivPre4,'')<>''
GO

-- Vista: v_PagoSeguro_DetalleReclamacion
CREATE VIEW [dbo].[v_PagoSeguro_DetalleReclamacion]
AS

	Select s_PagoSeguro_detalles.pags_Numero, s_ReclamacionesSeguro.recs_Numero,  Min(s_ReclamacionesSeguro.recs_Fecha) as recs_Fecha, Min(s_ReclamacionesSeguro.recs_NCF) as recs_NCF, Min(IdFacSin) as IdFacSin, Min(s_ReclamacionesSeguro.recs_Valor) as recs_Valor, sum(s_PagoSeguro_Detalles.Monto) as MontoPago 
	from s_PagoSeguro_detalles
	Inner join v_FacturasSeguro on s_PagoSeguro_Detalles.tipo = v_FacturasSeguro.Tipo and s_PagoSeguro_Detalles.hpag_IdCaja = v_FacturasSeguro.hpag_IdCaja and s_PagoSeguro_Detalles.hpag_Numero = v_FacturasSeguro.hpag_numero
	Inner join s_ReclamacionesSeguro on v_FacturasSeguro.hpag_NumeroReclamacionSeguro = s_ReclamacionesSeguro.recs_numero
	Group by s_PagoSeguro_detalles.pags_Numero, recs_Numero
GO

-- Vista: v_PerfilProgEst
/****** Object:  View dbo.v_TotalesVisitaPorDoctor    Script Date: 28/11/2007 08:56:08 a.m. ******/
CREATE VIEW [dbo].[v_PerfilProgEst]
AS
Select conf_IdPerfilProgEst as IdPerfil from s_Configuracion where conf_IdPerfilProgEst is not null
union
Select conf_IdPerfilProgQuiE as IdPerfil from s_Configuracion where conf_IdPerfilProgQuiE is not null
GO

-- Vista: v_PerfilProgQui
/****** Object:  View dbo.v_TotalesVisitaPorDoctor    Script Date: 28/11/2007 08:56:08 a.m. ******/
CREATE VIEW [dbo].[v_PerfilProgQui]
AS
Select conf_IdPerfilProgQui as IdPerfil from s_Configuracion where conf_IdPerfilProgQui is not null
union
Select conf_IdPerfilProgQuiE as IdPerfil from s_Configuracion where conf_IdPerfilProgQuiE is not null
GO

-- Vista: v_PerfilProgQuiE
/****** Object:  View dbo.v_TotalesVisitaPorDoctor    Script Date: 28/11/2007 08:56:08 a.m. ******/
CREATE VIEW [dbo].[v_PerfilProgQuiE]
AS
Select conf_IdPerfilProgQui as IdPerfil from s_Configuracion where conf_IdPerfilProgQui is not null
union
Select conf_IdPerfilProgEst as IdPerfil from s_Configuracion where conf_IdPerfilProgEst is not null
union
Select conf_IdPerfilProgQuiE as IdPerfil from s_Configuracion where conf_IdPerfilProgQuiE is not null
GO

-- Vista: v_PolizaDependientes
CREATE VIEW [dbo].[v_PolizaDependientes]
AS
SELECT        NumPoliza, Nombres, Apellidos, Direccion, CodOcup, CodProv, CodCiu, Cedula, Sexo, FechaNac, telefono, celular, email, IdSeguro, NumPolizaSeg, NumPlan, Cargo, TelTrabajo, FechaInicio, 'Principal' AS NomRelacion, 'R' as IdRelacion
FROM            s_Poliza
WHERE        Estatus = 'A'
UNION
SELECT        NumPoliza, Nombres, Apellidos, '' AS Direccion, '' AS CodOcup, '' AS CodProv, '' AS CodCiu, Cedula, Sexo, FechaNac, telefono, celular, email, IdSeguro, NumPolizaSeg, NumPlan, '' AS Cargo, '' AS TelTrabajo, FechaInicio, 
                         NomRelacion, s_Poliza_Dependientes.IdRelacion
FROM            s_Poliza_dependientes LEFT OUTER JOIN
                         s_Relacion ON s_Poliza_Dependientes.IdRelacion = s_Relacion.IdRelacion
WHERE        s_Poliza_Dependientes.Estatus = 'A'
GO

-- Vista: v_Presupuesto
CREATE VIEW [dbo].[v_Presupuesto]
AS
SELECT        dbo.s_HeaderPresupuesto.hpre_Numero as NumPre, MIN(dbo.s_HeaderPresupuesto.hpre_Estatus) AS hpre_Estatus, MIN(dbo.s_HeaderPresupuesto.hpre_NumeroExpediente) AS NumeroExpediente, 
                         MIN(ISNULL(dbo.s_HeaderPresupuesto.hpre_Monto, 0.0) - ISNULL(dbo.s_HeaderPresupuesto.hpre_CoberturaSeguro, 0.0) - ISNULL(dbo.s_HeaderPresupuesto.hpre_Descuento, 0.0)) AS MontoPresupuesto, 
                         SUM(ISNULL(dbo.s_HeaderPago.hpag_Monto, 0.0)) AS MontoPagado, MIN(dbo.s_HeaderPago.hpag_Fecha) AS FecPrimerPago, MAX(dbo.s_HeaderPago.hpag_Fecha) AS FecUltimoPago,
						  MIN(ISNULL(dbo.s_HeaderPresupuesto.hpre_Monto, 0.0) - ISNULL(dbo.s_HeaderPresupuesto.hpre_CoberturaSeguro, 0.0) - ISNULL(dbo.s_HeaderPresupuesto.hpre_Descuento, 0.0)) -  SUM(ISNULL(dbo.s_HeaderPago.hpag_Monto, 0.0)) as MontoPendiente
FROM            dbo.s_HeaderPresupuesto LEFT OUTER JOIN
                         dbo.s_Presupuesto_Pagos ON dbo.s_HeaderPresupuesto.hpre_Numero = dbo.s_Presupuesto_Pagos.NumPre LEFT OUTER JOIN
                         dbo.s_HeaderPago ON dbo.s_Presupuesto_Pagos.NumPago = dbo.s_HeaderPago.hpag_Numero AND dbo.s_Presupuesto_Pagos.IdCaja = dbo.s_HeaderPago.hpag_IdCaja
GROUP BY dbo.s_HeaderPresupuesto.hpre_Numero
GO

-- Vista: v_Presupuesto_TotalEjecutado
CREATE view v_Presupuesto_TotalEjecutado 
as
SELECT  Numpre, dpag_IdConcepto as IdConcepto, Count(*) as CantEjec from s_Presupuesto_pagos 
INNER join s_HeaderPago on s_Presupuesto_pagos.IdCaja = s_HeaderPago.hpag_IdCaja and s_Presupuesto_pagos.NumPago = s_HeaderPago.hpag_Numero 
INNER JOIN s_DetallePago on s_HeaderPago.hpag_IdCaja = s_DetallePago.dpag_IdCaja and s_HeaderPago.hpag_Numero = s_DetallePago.dpag_Numero 
WHERE (hpag_TipoPago='R' and hpag_Estatus<>'A') 
Group by NumPre, dpag_IdConcepto
GO

-- Vista: v_SumListaPacPorDoctor
CREATE view v_SumListaPacPorDoctor 
as
Select lisp_Fecha as Fecha, lisp_IdDoctor as IdDoctor, sum(case when ISNULL(lisp_IdSeguro,'')='' then 1 else 0 end) as CantPrivado,   
                sum(case when ISNULL(lisp_IdSeguro,'')='' then 0 else 1 end) as CantSeguro, COUNT(lisp_NumeroExpediente) as Total,   
                Min(cast(rtrim(lisp_HoraFinConsulta) + 'm' as datetime)) as HoraMin,Max(cast(rtrim(lisp_HoraFinConsulta) + 'm' as datetime)) as HoraMax,  
                Min(case when DATEPART(weekday, lisp_Fecha)= 1 then Doct_CantConDom  
                         when DATEPART(weekday, lisp_Fecha)= 2 then Doct_CantConLun  
                         when DATEPART(weekday, lisp_Fecha)= 3 then Doct_CantConMar  
                         when DATEPART(weekday, lisp_Fecha)= 4 then Doct_CantConMie  
                         when DATEPART(weekday, lisp_Fecha)= 5 then Doct_CantConJue  
                         when DATEPART(weekday, lisp_Fecha)= 6 then Doct_CantConVie  
                         when DATEPART(weekday, lisp_Fecha)= 7 then Doct_CantConSab  
                 end) as MaxConDia  
                from s_ListaPaciente   
                Left outer join s_Doctor on s_ListaPaciente.lisp_IdDoctor= s_doctor.doct_IdDoctor   
                group by lisp_Fecha, lisp_IdDoctor
GO

-- Vista: v_TipoCF_Venc
CREATE VIEW [dbo].[v_TipoCF_Venc]
AS
Select IdTipoCF, FechaVen from s_SecuenciaCF where Estatus='A' and IdSerie='B'
GO

-- Vista: v_TotalesVisitaPorDoctor
/****** Object:  View dbo.v_TotalesVisitaPorDoctor    Script Date: 28/11/2007 08:56:08 a.m. ******/
CREATE VIEW [dbo].[v_TotalesVisitaPorDoctor]
AS
SELECT     dbo.s_Visita.visi_IdDoctor, s_Visita.visi_FechaVisita, COUNT(visi_NumeroExpediente) AS TotalPacientes, SUM(CASE WHEN DATEDIFF(yy, 
                      dbo.s_Expediente.expe_FechaNacimiento, dbo.s_Visita.visi_FechaVisita) < 20 THEN 1 ELSE 0 END) AS EdadMenor20, SUM(CASE WHEN DATEDIFF(yy,
                       dbo.s_Expediente.expe_FechaNacimiento, dbo.s_Visita.visi_FechaVisita) >= 20 AND DATEDIFF(yy, dbo.s_Expediente.expe_FechaNacimiento, 
                      dbo.s_Visita.visi_FechaVisita) < 40 THEN 1 ELSE 0 END) AS Edad2040, SUM(CASE WHEN DATEDIFF(yy, dbo.s_Expediente.expe_FechaNacimiento, 
                      dbo.s_Visita.visi_FechaVisita) >= 40 AND DATEDIFF(yy, dbo.s_Expediente.expe_FechaNacimiento, dbo.s_Visita.visi_FechaVisita) 
                      < 60 THEN 1 ELSE 0 END) AS Edad4060, SUM(CASE WHEN DATEDIFF(yy, dbo.s_Expediente.expe_FechaNacimiento, dbo.s_Visita.visi_FechaVisita) 
                      >= 60 THEN 1 ELSE 0 END) AS EdadMayor60
FROM         dbo.s_Visita INNER JOIN
                      dbo.s_Expediente ON dbo.s_Visita.visi_NumeroExpediente = dbo.s_Expediente.expe_NumeroExpediente
GROUP BY visi_IdDoctor, visi_FechaVisita
GO

-- Vista: v_TotalesVisitaPorDoctorRango2
/****** Object:  View dbo.v_TotalesVisitaPorDoctorRango2    Script Date: 28/11/2007 08:56:08 a.m. ******/
CREATE VIEW [dbo].[v_TotalesVisitaPorDoctorRango2]
AS
SELECT     dbo.s_Visita.visi_IdDoctor, s_Visita.visi_FechaVisita, COUNT(visi_NumeroExpediente) AS TotalPacientes, 
            SUM(CASE WHEN DATEDIFF(yy,dbo.s_Expediente.expe_FechaNacimiento, dbo.s_Visita.visi_FechaVisita) < 1 THEN 1 ELSE 0 END) AS EdadMenor1, 
			SUM(CASE WHEN DATEDIFF(yy,dbo.s_Expediente.expe_FechaNacimiento, dbo.s_Visita.visi_FechaVisita) >= 1 AND 
			              DATEDIFF(yy, dbo.s_Expediente.expe_FechaNacimiento,dbo.s_Visita.visi_FechaVisita) <= 4 THEN 1 ELSE 0 END) AS Edad0104, 
			SUM(CASE WHEN DATEDIFF(yy, dbo.s_Expediente.expe_FechaNacimiento,dbo.s_Visita.visi_FechaVisita) >= 5 AND 
			              DATEDIFF(yy, dbo.s_Expediente.expe_FechaNacimiento, dbo.s_Visita.visi_FechaVisita)<= 14 THEN 1 ELSE 0 END) AS Edad0514, 
			SUM(CASE WHEN DATEDIFF(yy, dbo.s_Expediente.expe_FechaNacimiento,dbo.s_Visita.visi_FechaVisita) >= 15 AND 
			              DATEDIFF(yy, dbo.s_Expediente.expe_FechaNacimiento, dbo.s_Visita.visi_FechaVisita)<= 64 THEN 1 ELSE 0 END) AS Edad1564, 
		    SUM(CASE WHEN DATEDIFF(yy, dbo.s_Expediente.expe_FechaNacimiento, dbo.s_Visita.visi_FechaVisita) > 64 THEN 1 ELSE 0 END) AS EdadMayor64
FROM         dbo.s_Visita INNER JOIN
                      dbo.s_Expediente ON dbo.s_Visita.visi_NumeroExpediente = dbo.s_Expediente.expe_NumeroExpediente
GROUP BY visi_IdDoctor, visi_FechaVisita
GO

-- Vista: v_Visita_Recetas
/****** Object:  View dbo.v_TotalesVisitaPorDoctor    Script Date: 28/11/2007 08:56:08 a.m. ******/
CREATE VIEW [dbo].[v_Visita_Recetas]
AS
Select NumVisita, 1 as NumReceta, cast(visi_Tratamiento as varchar(2000)) as visi_Tratamiento from s_visita where rtrim(IsNull(Cast(visi_tratamiento as varchar),''))<>''
union
Select NumVisita, 2 as NumReceta, cast(visi_Tratamiento2 as varchar(2000)) as visi_Tratamiento from s_visita where rtrim(IsNull(Cast(visi_tratamiento2 as varchar),''))<>''
union
Select NumVisita, 3 as NumReceta, cast(visi_Tratamiento3 as varchar(2000)) as visi_Tratamiento from s_visita where rtrim(IsNull(Cast(visi_tratamiento3 as varchar),''))<>''
union
Select NumVisita, 4 as NumReceta, cast(visi_Tratamiento4 as varchar(2000)) as visi_Tratamiento from s_visita where rtrim(IsNull(Cast(visi_tratamiento4 as varchar),''))<>''
union
Select NumVisita, 5 as NumReceta, cast(visi_Tratamiento5 as varchar(2000)) as visi_Tratamiento from s_visita where rtrim(IsNull(Cast(visi_tratamiento5 as varchar),''))<>''
GO

-- Vista: v_Visita_RecetasLente
CREATE VIEW [dbo].[v_Visita_RecetasLente]
AS
Select NumVisita, 1 as NumReceta, rodesf, rodcil, rodeje, rodadd, roiesf, roicil, roieje, roiadd, lente1, lente2, DistanciaInterpupilar    
from s_visita_Oftalmologia 
		where rodesf is not null or rodcil is not null or rodeje is not null or rodadd is not null or
	          roiesf is not null or roicil is not null or roieje is not null or roiadd is not null
union
Select NumVisita, 2 as NumReceta, rodesf2 as rodesf, rodcil2 as rodcil, rodeje2 as rodeje, rodadd2 as rodadd, roiesf2 as roiesf, roicil2 as roicil, roieje2 as roieje, roiadd2 as roiadd, lente12 as lente1, lente22 as lente2, DistanciaInterpupilar   
from s_visita_Oftalmologia 
       where rodesf2 is not null or rodcil2 is not null or rodeje2 is not null or rodadd2 is not null or
	         roiesf2 is not null or roicil2 is not null or roieje2 is not null or roiadd2 is not null
union
Select NumVisita, 3 as NumReceta, rodesf3 as rodesf, rodcil3 as rodcil, rodeje3 as rodeje, rodadd3 as rodadd, roiesf3 as roiesf, roicil3 as roicil, roieje3 as roieje, roiadd3 as roiadd, lente13 as lente1, lente23 as lente3, DistanciaInterpupilar   
from s_visita_Oftalmologia 
       where rodesf3 is not null or rodcil3 is not null or rodeje3 is not null or rodadd3 is not null or
	         roiesf3 is not null or roicil3 is not null or roieje3 is not null or roiadd3 is not null
GO

-- Vista: vPacientesACobrar
CREATE VIEW [dbo].[vPacientesACobrar]
AS
SELECT     dbo.s_ListaPaciente.lisp_NumeroExpediente AS CodigoPaciente, RTRIM(dbo.s_Expediente.expe_Nombres) + ' ' + RTRIM(dbo.s_Expediente.expe_Apellidos) 
                      AS NombrePaciente, dbo.s_Expediente.expe_Direccion AS Direccion, dbo.s_Expediente.expe_TelefonoCasa AS Telefono1, 
                      dbo.s_Expediente.expe_Celular AS Telefono2, dbo.s_Expediente.expe_Email AS Email, dbo.s_ListaPaciente.lisp_NumeroAfiliado AS NroAsegurado, 
                      CASE WHEN IsNull(dbo.s_ListaPaciente.lisp_IdSeguro, '') = '' THEN 'NO' ELSE 'SI' END AS EsAsegurado, dbo.s_Seguro.segu_Nombre AS NombreSeguro, 
                      dbo.s_ListaPaciente.lisp_IdSeguro AS IdSeguro
FROM         dbo.s_ListaPaciente INNER JOIN
                      dbo.s_Expediente ON dbo.s_ListaPaciente.lisp_NumeroExpediente = dbo.s_Expediente.expe_NumeroExpediente LEFT OUTER JOIN
                      dbo.s_Seguro ON dbo.s_ListaPaciente.lisp_IdSeguro = dbo.s_Seguro.segu_IdSeguro
WHERE     (dbo.s_ListaPaciente.lisp_Fecha = CONVERT(varchar(10), GETDATE(), 101))
GO


-- ==========================================================================
-- FUNCIONES (FUNCTIONS: ESCALARES Y DE TABLA)
-- ==========================================================================

-- Función (SQL_SCALAR_FUNCTION): CtoH
CREATE FUNCTION [dbo].[CtoH] ( @StrHora varchar(50) ) 
RETURNS DECIMAL(5,2)
AS
BEGIN

DECLARE @Hora Int, @Min Int, @Seg Int, @Valor decimal(5,2), @Mer as char(1), @Div Int

set @Hora = 0
set @Min = 0
set @Seg = 0
set @Valor = 0
set @Mer = ''

--  123456
--  HH:MMa
--  Colocar a formato con hora

if len(IsNull(@StrHora,''))>=6
Begin
	set @Div = CHARINDEX(':', @StrHora)
	if @Div>0 
	Begin
		set @Hora = SUBSTRING(@StrHora, 1, @Div - 1)
		set @Min  = SUBSTRING(@StrHora,  @Div + 1, 2)
		if Len(@StrHora)> @Div + 2
			set @Mer = Upper(Substring(@StrHOra, @Div + 3,1))
		else
			set @Mer = 'A'
	End
	else
	Begin
		set @Hora = SUBSTRING(@StrHora, 1, 2)
		set @Min  = SUBSTRING(@StrHora, 3, 2)
	End

	set @Valor = @Hora + cast(@Min as decimal(5,2)) / 60 
	if @Mer='P' and @Hora<12
		set @Valor = @Valor + 12

	-- Las doce de la media noche es hora cero
	if @Hora = 12 and @Mer='A' 
		set @Valor = @Valor - 12

End
 
RETURN @Valor
	

END
GO

-- Función (SQL_TABLE_VALUED_FUNCTION): fnSplitString
CREATE FUNCTION [dbo].[fnSplitString] 
( 
    @string NVARCHAR(MAX), 
    @delimiter CHAR(1) 
) 
RETURNS @output TABLE(splitdata NVARCHAR(MAX) 
) 
BEGIN 
    DECLARE @start INT, @end INT 
    SELECT @start = 1, @end = CHARINDEX(@delimiter, @string) 
    WHILE @start < LEN(@string) + 1 BEGIN 
        IF @end = 0  
            SET @end = LEN(@string) + 1
       
        INSERT INTO @output (splitdata)  
        VALUES(SUBSTRING(@string, @start, @end - @start)) 
        SET @start = @end + 1 
        SET @end = CHARINDEX(@delimiter, @string, @start)
        
    END 
    RETURN 
END
GO

-- Función (SQL_SCALAR_FUNCTION): InitCap
CREATE FUNCTION [dbo].[InitCap] ( @InputString varchar(4000) ) 
RETURNS VARCHAR(4000)
AS
BEGIN

DECLARE @Index          INT
DECLARE @Char           CHAR(1)
DECLARE @PrevChar       CHAR(1)
DECLARE @OutputString   VARCHAR(255)

SET @OutputString = LOWER(@InputString)
SET @Index = 1

WHILE @Index <= LEN(@InputString)
BEGIN
    SET @Char     = SUBSTRING(@InputString, @Index, 1)
    SET @PrevChar = CASE WHEN @Index = 1 THEN ' '
                         ELSE SUBSTRING(@InputString, @Index - 1, 1)
                    END

    IF @PrevChar IN (' ', ';', ':', '!', '?', ',', '.', '_', '-', '/', '&', '''', '(')
    BEGIN
        IF @PrevChar != '''' OR UPPER(@Char) != 'S'
            SET @OutputString = STUFF(@OutputString, @Index, 1, UPPER(@Char))
    END

    SET @Index = @Index + 1
END

RETURN @OutputString

END
GO


-- ==========================================================================
-- PROCEDIMIENTOS ALMACENADOS (STORED PROCEDURES)
-- ==========================================================================

-- Procedimiento: ActualizaFecha
CREATE PROCEDURE [dbo].[ActualizaFecha] AS

Update Diagnostico
set diag_FechaVisita =Substring(imp_FechaVisita,6,2) + '/' + Substring(imp_FechaVisita,9,2) + '/' + Substring(imp_FechaVisita,1,4)
where  Substring(imp_FechaVisita,1,4)> 1900 and IsNull(Imp_FechaVisita,'')<>''
GO

-- Procedimiento: GenerarNCF
CREATE Procedure [dbo].[GenerarNCF] (@IdSecuenciaCF varchar(10), @NCF Varchar(19) OUTPUT)
 as

Declare @IdSerie as char(1), @IdDivisionNegocio as varchar(2), @IdPuntoEmision as varchar(3), @IdArea as varchar(3), @IdTipoCF as  varchar(2)
Declare @ProximoNumero as integer, @Hasta as integer
declare @FechaVen as datetime, @ProximoDesde as integer, @ProximoHasta as Integer, @ProxFechaVen as datetime, @ProximoMinimo as integer
Select @IdSerie=IdSerie, @IdDivisionNegocio=IdDivisionNegocio, @IdPuntoEmision=IdPuntoEmision, 
       @IdArea= IdArea, @ProximoNumero=Numero, @Hasta=Hasta, @IdTipoCF = IdTipoCF,
	   @FechaVen = IsNull(FechaVen,'21000101'), @ProximoDesde = IsNull(ProximoDesde,0), @ProximoHasta = IsNull(ProximoHasta,0), @ProxFechaVen= IsNull(ProxFechaVen,'21000101'),
	   @ProximoMinimo = IsNull(ProximoMinimo,0)
from s_SecuenciaCF
where Id=@IdSecuenciaCF

-- Si el proximoNumero es mayor a cero y la fecha de vencimiento es mayor a la actual, retornar el comprobante fiscal
if @ProximoNumero>0 and @ProximoNumero <= @Hasta and @FechaVen>=GetDate()
Begin
	if @IdSerie='A'
		set @NCF =@IdSerie + @IdDivisionNegocio + @IdPuntoEmision + @IdArea + @IdTipoCF + right('00000000'+ rtrim(cast( @ProximoNumero as varchar)), 8)
	else if  @IdSerie='B'
		set @NCF =@IdSerie + @IdTipoCF + right('00000000'+ rtrim(cast( @ProximoNumero as varchar)), 8)
	else if  @IdSerie='E'
		set @NCF =@IdSerie + @IdTipoCF + right('0000000000'+ rtrim(cast( @ProximoNumero as varchar)), 10)
	else
		set @NCF =@IdSerie + @IdTipoCF + right('00000000'+ rtrim(cast( @ProximoNumero as varchar)), 8)

	-- Incrementar la secuencia
	Update s_SecuenciaCF set Numero = Numero + 1 where Id=@IdSecuenciaCF

	-- Si se uso el ultimo de la secuencia, ver si hay una secuencia siguiente almacenada
	if @ProximoNumero = @Hasta 
	Begin
		print 'Se acabó la secuencia.'
		if @ProximoDesde>0 and @ProximoHasta>0 and @ProximoMinimo>0 and @ProxFechaVen>=getDate()
		Begin
			-- Utiliza la siguiente secuencia almacenada
			Update s_SecuenciaCF set Desde = ProximoDesde, Hasta = ProximoHasta, Minimo=ProximoMinimo, FechaVen = ProxFechaVen,
					Numero = ProximoDesde
			where Id=@IdSecuenciaCF
    
			-- Limpiar la secuencia siguiente almacenada
			Update s_SecuenciaCF set ProximoDesde=NULL, ProximoHasta=NULL, ProximoMinimo=NULL, ProxFechaVen=NULL
			where Id=@IdSecuenciaCF

			print 'Se switcheo a la próxima secuencia'
		end
	end

end
else
	begin
		if @ProximoNumero > @Hasta 
			print 'Secuencia acabada. no hay numeros disponibles: Hasta: ' + cast(@Hasta as varchar)

		if @FechaVen<getDate()
			print 'La fecha de la secuencia está vencida: ' +  cast(@FechaVen as varchar)

	end
GO

-- Procedimiento: RecuperarNCF
CREATE Procedure [dbo].[RecuperarNCF] (@IdSecuenciaCF varchar(10), @NCF varchar(19), @Recuperado char(1) OUTPUT)
 as

--Determinar el tipo de comprobante y la secuencia del NCF
Declare @IdTipoCF as char(2), @SecuenciaCF as Integer, @ProximoNumero as integer
-- 1234567890123456789
-- A010010010100000001
set @IdTipoCF = substring(@NCF,10,2)
set @SecuenciaCF = cast(substring(@NCF,12,8) as Int)


Select @ProximoNumero=Numero
from s_SecuenciaCF 
where Id=@IdSecuenciaCF

-- Si fue el ultimo asignado
if @ProximoNumero = @SecuenciaCF + 1 
Begin
	-- Utiliza la siguiente secuencia almacenada
	Update s_SecuenciaCF set Numero = Numero -1
	where Id=@IdSecuenciaCF
    
    set @Recuperado= 'S'
end
else
	set @Recuperado= 'N'
GO

-- Procedimiento: sp_ActualizaEstatusReclamacion
CREATE procedure [dbo].[sp_ActualizaEstatusReclamacion] (
 @Numero int,
  @Result VARCHAR(900) OUTPUT )
as
Declare @PendientePago as decimal(12,2)

	set @Result =''
	Select @PendientePago= Sum(IsNull(hpag_CoberturaSeguro,0.0) - IsNull(hpag_MontoPagado,0.0) + IsNull(hpag_MontoDebito,0.0)) from v_FacturasSeguro where hpag_NumeroReclamacionSeguro=@Numero

	if @PendientePago>0 
		Update s_ReclamacionesSeguro set recs_PendPago=1 where recs_Numero=@Numero and recs_Estatus<>'A'
	else
		Update s_ReclamacionesSeguro set recs_PendPago=0 where recs_Numero=@Numero and recs_Estatus<>'A'
GO

-- Procedimiento: sp_AddDoctorAEntidadRef
CREATE procedure [dbo].[sp_AddDoctorAEntidadRef] (
 @IdDoctor varchar(15),
 @NomDoctor varchar(50),
 @IdEspecialidad varchar(3),
 @IdEstastus char(1) )
as


if exists(Select entr_IdEntidad from s_EntidadReferimiento where entr_IdDoctor=@IdDoctor)
	Update s_EntidadReferimiento set entr_nombre=rtrim(@NomDoctor), entr_TipoEntidad='D', entr_Procedencia='I', entr_Estatus=@IdEstastus, entr_IdEspecialidad=@IdEspecialidad where entr_IdDoctor=@IdDoctor
else
	Insert into s_EntidadReferimiento(entr_IdEntidad,entr_Nombre,entr_TipoEntidad, entr_Estatus, entr_IdEspecialidad, entr_RecibeRef, entr_EnviaRef, entr_Procedencia, entr_IdDoctor)
	Values( 'D' +  Left(@IdDoctor,9), rtrim(@NomDoctor),'D',@IdEstastus,@IdEspecialidad,0,1,'I', @IdDoctor)
GO

-- Procedimiento: sp_AddFromListaEspera
CREATE procedure sp_AddFromListaEspera(@NumLista as integer, @IdDoctor as varchar(15), @Fecha as date, @CodUsr as varchar(15), @Result varchar(2000) OUTPUT)
as

Declare @NuevaLista as integer, @NuevaSecuencia as integer, @IdSubEspecialidad as varchar(10), @HoraAct as varchar(6), @NomPaciente as varchar(100), @NomDoctor as varchar(100)

set @Result=''
-- Buscar la siguiente secuencia a adicionar
Select @NuevaLista =IsNull(Max(NumLista),0) from s_ListaPaciente
set @NuevaLista = @NuevaLista + 1

-- Buscar la próxima secuncia del doctor
Select @NuevaSecuencia = IsNull(max(lisp_Secuencia),0) from s_ListaPaciente where lisp_idDoctor=@IdDoctor and lisp_Fecha =@Fecha
set @NuevaSecuencia = @NuevaSecuencia + 1	

-- Conseguir la fecha actual
select @HoraAct = replace(replace(right(convert(varchar,getdate(),0),7),'PM','p'),'AM','a') 

-- Buscar la SubEspecialidad del nuevo Doctor
Select @IdSubEspecialidad = doct_IdSubEspecialidad, @NomDoctor=doct_Nombre  from s_Doctor where doct_IdDoctor=@IdDoctor


-- Buscar el nombre del paciente
Select @NomPaciente = rtrim(expe_Nombres)  + '' +  rtrim(expe_Apellidos)
from s_ListaPaciente
Inner join s_Expediente on s_ListaPaciente.lisp_NumeroExpediente = s_Expediente.expe_NumeroExpediente
where NumLista = @NumLista

-- Validar que el doctor no haya cerrado consulta y que no se sobrepase la cantidad Maxima de pacientes

-- Insertar en la lista del doctor
Insert into s_ListaPaciente(NumLista,lisp_IdDoctor,lisp_Fecha,lisp_Secuencia,lisp_NumeroExpediente,lisp_IdMotivo,lisp_IdSeguro,
                            lisp_IdPlanSeguro,lisp_NumeroAfiliado,lisp_NumeroPoliza,lisp_Estatus,lisp_HoraLlegada,lisp_RegPor, lisp_StsPag,lisp_HoraPago, lisp_Comentario)
Select @NuevaLista, @IdDoctor, @Fecha, @NuevaSecuencia,lisp_NumeroExpediente,lisp_IdMotivo,lisp_IdSeguro,
						    lisp_IdPlanSeguro,lisp_NumeroAfiliado,lisp_NumeroPoliza,'E',@HoraAct,@CodUsr, 
							case when lisp_StsPag='P' and doct_IdSubEspecialidad=@IdSubEspecialidad then 'N' else 'E' end,
							case when lisp_StsPag='P' and doct_IdSubEspecialidad=@IdSubEspecialidad then @HoraAct else NULL end, 'InterConsulta: ' + lisp_IdDoctor
from s_ListaPaciente
Inner join s_Doctor on s_ListaPaciente.lisp_IdDoctor = s_Doctor.doct_IdDoctor
where NumLista = @NumLista

set @Result= 'El paciente: ' + @NomPaciente + ' fue registrado en la lista de consulta del doctor: ' + @NomDoctor
GO

-- Procedimiento: sp_AddReciboCoPago
CREATE procedure [dbo].[sp_AddReciboCoPago] (@IdCaja varchar(10), @NumPago as int, @NuevoPago as Integer OUTPUT )
as

/*   Para probar
set @NumPago = 19033 
set @IdCaja='1'
*/

declare @Monto as decimal(12,2), @GenRecCoPago int

Select @GenRecCoPago= Isnull(GenRecCoPago,0) from s_Configuracion 
set @NuevoPago=0  -- Indica que no se generó recibo

if @GenRecCoPago=1 
Begin
	-- Buscar la siguiente secuencia a adicionar
	Select @NuevoPago =IsNull(Max(hpag_Numero),0) from s_HeaderPago where hpag_IdCaja=@IdCaja
	set @NuevoPago = @NuevoPago + 1

	--2023-01-12 se busca primero el copago de acuerdo al seguro
	Insert into s_DetallePago(dpag_IdCaja, dpag_Numero, dpag_Secuencia, dpag_IdConcepto, dpag_Monto, dpag_CoberturaSeguro, dpag_RealizadoPor,
							  dpag_CoberturaSeguroInterno, dpag_Descripcion, dpag_Cantidad )
	Select dpag_IdCaja, @NuevoPago, dpag_Secuencia, dpag_IdConcepto, isNull(s_SeguroServicios.segs_CoPago, 0.0) * dpag_Cantidad as dpag_Monto, 
	0.0 as dpag_CoberturaSeguro,dpag_RealizadoPor, 0.0 as dpag_CoberturaSeguroInterno, case when dpag_Descripcion is Null then 'CoPago' else ',' + rtrim(dpag_Descripcion) end as dpag_Descripcion, dpag_Cantidad
	from s_DetallePago 
	Inner join s_HeaderPago on s_DetallePago.dpag_IdCaja = s_HeaderPago.hpag_IdCaja and s_DetallePago.dpag_Numero = s_HeaderPago.hpag_Numero
	Inner join s_ConceptoPago on s_DetallePago.dpag_IdConcepto = s_ConceptoPago.conc_IdConcepto
	Inner join s_SeguroServicios on s_HeaderPago.hpag_IdSeguro = s_SeguroServicios.segs_IdSeguro and s_DetallePago.dpag_IdConcepto=s_SeguroServicios.segs_IdServicio
	where dpag_IdCaja=@IdCaja and dpag_Numero=@NumPago and isNull(s_SeguroServicios.segs_CoPago,0.0)>0 and IsNull(s_HeaderPago.hpag_IdSeguro,'')<>''
	-- 2023-03-09 se va a generar copago si la cobertura no es total
	      and (IsNull(dpag_Monto,0.0) - IsNull(dpag_CoberturaSeguro,0.0)) >0

	if @@ROWCOUNT>0
	Begin
		Select @Monto = sum(dpag_Monto)	from s_DetallePago Where dpag_IdCaja=@IdCaja and dpag_Numero=@NuevoPago

		-- Insertar el encabezado
		Insert into s_HeaderPago(hpag_IdCaja, hpag_Numero, hpag_Fecha, hpag_Hora, hpag_NumeroExpediente, hpag_Nombre, hpag_Apellido, hpag_Desmov, hpag_Monto, hpag_CoberturaSeguro, hpag_Descuento, 
							 hpag_TipoPago, hpag_ValorRecibidoEfectivo, hpag_IdUsuario, hpag_Estatus,  hpag_IdDoctor, hpag_Procedencia, hpag_IdEntidadRef,
							 hpag_FechaReg, NumRecOrigen )
		Select @IdCaja, @NuevoPago, hpag_Fecha, hpag_Hora, hpag_NumeroExpediente, hpag_Nombre, hpag_Apellido, hpag_Desmov, @Monto, 0.0, 0.0,
			  'E', @Monto, hpag_IdUsuario, 'P', hpag_IdDoctor, hpag_Procedencia,hpag_IdEntidadRef,
			  hpag_FechaReg, @NumPago
		from s_HeaderPago 
		where hpag_IdCaja=@IdCaja and hpag_Numero=@NumPago
	End
	else
		set @NuevoPago=0   -- Indica que no se generó recibo
end
GO

-- Procedimiento: sp_AddTurnoAListaEspera
CREATE procedure sp_AddTurnoAListaEspera(@IdDoctor as varchar(15), @IdTurno varchar(20) OUTPUT)
as

Declare @NuevaLista as integer, @NuevaSecuencia as integer,  @HoraAct as varchar(6)
declare @Iniciales as varchar(10), @IdMotivo as varchar(10), @Fecha as date

set @Fecha = cast(getdate() as date)

-- Buscar la siguiente secuencia a adicionar
Select @NuevaLista =IsNull(Max(NumLista),0) from s_ListaPaciente
set @NuevaLista = @NuevaLista + 1


Select @Iniciales = doct_Iniciales  from s_Doctor where doct_IdDoctor=@IdDoctor

-- Buscar la próxima secuncia del doctor
Select @NuevaSecuencia = IsNull(max(lisp_Secuencia),0) from s_ListaPaciente where lisp_idDoctor=@IdDoctor and lisp_Fecha =@Fecha
set @NuevaSecuencia = @NuevaSecuencia + 1	

-- Conseguir la fecha actual
select @HoraAct = replace(replace(right(convert(varchar,getdate(),0),7),'PM','p'),'AM','a') 

set @IdTurno = @Iniciales + rtrim(cast(@NuevaSecuencia as varchar))

Select Top 1 @IdMotivo=moti_IdMotivo from s_MotivoConsulta order by moti_IdMotivo

-- Validar que el doctor no haya cerrado consulta y que no se sobrepase la cantidad Maxima de pacientes

-- Insertar en la lista del doctor
Insert into s_ListaPaciente(NumLista,lisp_IdDoctor,lisp_Fecha,lisp_Secuencia,lisp_IdMotivo,lisp_Nombre,lisp_Estatus,lisp_HoraLlegada,lisp_RegPor)
Values(@NuevaLista, @IdDoctor, @Fecha, @NuevaSecuencia,@IdMotivo,'Turno: ' + @IdTurno, 'E',@HoraAct,'TURNO')
GO

-- Procedimiento: sp_AsignarListaEspera
CREATE Procedure sp_AsignarListaEspera(@Fecha as date, @Anos as integer, @IdDoctor varchar(15) OUTPUT)
as

Declare @Orden as integer, @OrdenAct as integer

set @IdDoctor=''

-- Buscar el ultimo asignado en el rango
Select  Top 1 @OrdenAct = Orden 
from s_Doctor_Disponibilidad 
Inner join s_Doctor on s_Doctor_Disponibilidad.IdDoctor = s_Doctor.doct_IdDoctor
where fecha=@Fecha and EstadoConsulta='A' and doct_UsarParaEstudio=0 and HoraIniConsulta is Not Null and UltAsig is not null
      and ((doct_EdadMinPac<=@Anos and doct_EdadMaxPac>=@Anos) or @Anos=0)   
Order by UltAsig Desc, Orden 

if @OrdenAct is Null 
	set @OrdenAct=0

-- Buscar el siguiente de la lista que no sea de estudio
Select  Top 1 @IdDoctor=IdDoctor, @Orden = Orden 
from s_Doctor_Disponibilidad 
Inner join s_Doctor on s_Doctor_Disponibilidad.IdDoctor = s_Doctor.doct_IdDoctor
where fecha=@Fecha and EstadoConsulta='A' and doct_UsarParaEstudio=0 and HoraIniConsulta is Not Null 
      and ((doct_EdadMinPac<=@Anos and doct_EdadMaxPac>=@Anos) or @Anos=0)   
	  and Orden>@OrdenAct 
order by Orden

-- Si no hay mas en la lista, buscar el primero disponible
if @Orden is Null 
	Select  Top 1 @IdDoctor=IdDoctor, @Orden = Orden 
	from s_Doctor_Disponibilidad 
	Inner join s_Doctor on s_Doctor_Disponibilidad.IdDoctor = s_Doctor.doct_IdDoctor
	where fecha=@Fecha and EstadoConsulta='A' and doct_UsarParaEstudio=0 and HoraIniConsulta is Not Null
	      and ((doct_EdadMinPac<=@Anos and doct_EdadMaxPac>=@Anos) or @Anos=0) 
	 order by Orden

-- Si no se ha logueado ningun doctor, tomar el primero de la lista
if @Orden is Null 
	Select  Top 1 @IdDoctor=IdDoctor, @Orden = Orden 
	from s_Doctor_Disponibilidad 
	Inner join s_Doctor on s_Doctor_Disponibilidad.IdDoctor = s_Doctor.doct_IdDoctor
	where fecha=@Fecha and EstadoConsulta='A'	 order by Orden

-- Marcar como seleccionado
Update s_Doctor_Disponibilidad set UltimoAsignado = case when Orden=@Orden then 1 else 0 end where fecha=@Fecha

-- incrementar la cantidad de pacientes asignados.  colocar la hora de la asignacion
Update s_Doctor_Disponibilidad set PacientesAsig= IsNull(PacientesAsig,0) + 1, UltAsig=GetDate() where fecha=@Fecha and UltimoAsignado=1
GO

-- Procedimiento: sp_BalanceArticulo
CREATE PROCEDURE [dbo].[sp_BalanceArticulo](@FechaHasta  smalldatetime, @TipoArt varchar) AS

-- @TipoArt --> A: Articulo, M: Material
--- Procedimiento que retorna el balance de los articulos a la fecha indicada
if (@TipoArt='')
	Select CodArt, Min(NomArt) as NomArt, Min(Costo) as Costo, SUM(cantidad) as Balance from (
	Select dpag_idconcepto as CodArt, Min(s_ConceptoPago.conc_nombre) as NomArt, min(s_ConceptoPago.conc_Costo) as Costo, SUM(-1) as cantidad from dbo.s_DetallePago D
	inner join dbo.s_HeaderPago H on D.dpag_idCaja = H.hpag_idCaja and D.dpag_numero = H.hpag_numero
	inner join s_ConceptoPago on D.dpag_IdConcepto = s_ConceptoPago.conc_IdConcepto
	where hpag_fecha<=@FechaHasta  and s_ConceptoPago.conc_Tipo in ('A','M') and hpag_Estatus='P'
	Group by dpag_idconcepto
	union
	Select CodArt, Min(s_ConceptoPago.conc_nombre) as NomArt, min(s_ConceptoPago.conc_Costo) as Costo, sum(case when T.Clase='E' then D.Cantidad else -D.Cantidad end) as Cantidad from s_DetalleMovInv D
	Inner join s_HeaderMovInv H on D.Numero=H.Numero 
	Inner join s_TipoMovInv T on H.codtmov=T.codtmov
	inner join s_ConceptoPago on D.CodArt = s_ConceptoPago.conc_IdConcepto
	where fecha<=@FechaHasta and H.Estatus<>'A'
	Group by Codart
	) S
	Group by S.codArt order by NomArt
else
	Select CodArt, Min(NomArt) as NomArt, Min(Costo) as Costo, SUM(cantidad) as Balance from (
	Select dpag_idconcepto as CodArt, Min(s_ConceptoPago.conc_nombre) as NomArt, min(s_ConceptoPago.conc_Costo) as Costo, SUM(-1) as cantidad from dbo.s_DetallePago D
	inner join dbo.s_HeaderPago H on D.dpag_idCaja = H.hpag_idCaja and D.dpag_numero = H.hpag_numero
	inner join s_ConceptoPago on D.dpag_IdConcepto = s_ConceptoPago.conc_IdConcepto
	where hpag_fecha<=@FechaHasta  and s_ConceptoPago.conc_Tipo in ('A','M') and s_ConceptoPago.conc_Tipo=@TipoArt and hpag_Estatus='P'
	Group by dpag_idconcepto
	union
	Select CodArt, Min(s_ConceptoPago.conc_nombre) as NomArt, min(s_ConceptoPago.conc_Costo) as Costo, sum(case when T.Clase='E' then D.Cantidad else -D.Cantidad end) as Cantidad from s_DetalleMovInv D
	Inner join s_HeaderMovInv H on D.Numero=H.Numero 
	Inner join s_TipoMovInv T on H.codtmov=T.codtmov
	inner join s_ConceptoPago on D.CodArt = s_ConceptoPago.conc_IdConcepto
	where fecha<=@FechaHasta and s_ConceptoPago.conc_Tipo=@TipoArt and H.Estatus<>'A'
	Group by Codart
	) S
	Group by S.codArt order by NomArt
GO

-- Procedimiento: sp_CambiaIdDoctor
CREATE procedure sp_CambiaIdDoctor (@IdAct varchar(15), @NuevoId varchar(15)) as

declare @Resultado varchar(300);

Begin
	-- Si el nuevo Id Existe, no continuar
	if Exists (Select doct_IdDoctor from s_doctor where doct_IdDoctor=@NuevoId)
	Begin
		set @Resultado='ERROR: El nuevo Id de doctor: ' + @NuevoId + ' ya existe.';
	end
	else
	begin
		Update s_AutorizacionesSeguro set IdDoctor=@NuevoId where IdDoctor=@IdAct;
		Update s_CertificacionMedica set cert_IdDoctor=@NuevoId where cert_IdDoctor=@IdAct;
		Update s_Cita set cita_IdDoctor=@NuevoId where cita_IdDoctor=@IdAct;
		Update s_CompraLente set IdDoctor=@NuevoId where IdDoctor=@IdAct;
			
		Update s_Doctor set doct_IdDoctor=@NuevoId where doct_IdDoctor=@IdAct;
		Update s_Doctor set doct_Iniciales=@NuevoId where doct_IdDoctor=@IdAct and ISNULL(doct_iniciales,doct_IdDoctor)=doct_IdDoctor;
	
		Update s_Doctor_Disponibilidad set IdDoctor=@NuevoId where IdDoctor=@IdAct;

		Update s_Expediente set expe_IdDoctor=@NuevoId where expe_IdDoctor=@IdAct;
		Update s_HeaderMovInv set IdDoctor=@NuevoId where IdDoctor=@IdAct;
		
		Update s_Headerpago set hpag_IdDoctor=@NuevoId where hpag_IdDoctor=@IdAct;
		Update s_Headerpago set hpag_IdDoctor2=@NuevoId where hpag_IdDoctor2=@IdAct;
		Update s_HeaderPrepago set hpag_IdDoctor=@NuevoId where hpag_IdDoctor=@IdAct;
		Update s_HeaderPrepago set hpag_IdDoctor2=@NuevoId where hpag_IdDoctor2=@IdAct;
		
		Update s_HeaderPresupuesto set hpre_IdDoctor=@NuevoId where hpre_IdDoctor=@IdAct;
	
		Update s_ListaEspecialPacientes set lisp_IdDoctor=@NuevoId where lisp_IdDoctor=@IdAct;
		
		Update s_ListaPaciente set lisp_IdDoctor=@NuevoId where lisp_IdDoctor=@IdAct;
		
		Update s_Log set IdUsuario=@NuevoId where IdUsuario=@IdAct;
		
		Update s_NotaExpediente set IdDoctor=@NuevoId where IdDoctor=@IdAct;
		
		Update s_PRECita set cita_IdDoctor=@NuevoId where cita_IdDoctor=@IdAct;
		Update s_Referimiento set refe_IdDoctor=@NuevoId where refe_IdDoctor=@IdAct;
		
		Update s_Usuario set usua_IdDoctorDef=@NuevoId where usua_IdDoctorDef=@IdAct;
		Update s_UsuarioDoctores set usud_IdDoctor=@NuevoId where usud_IdDoctor=@IdAct;
		
		Update s_Visita set visi_IdDoctor=@NuevoId where visi_IdDoctor=@IdAct;
		
		
		
	end

	Select @Resultado 
end
GO

-- Procedimiento: sp_CambiaIdUsuario
CREATE procedure sp_CambiaIdUsuario (@IdAct varchar(15), @NuevoId varchar(15)) as

declare @Resultado varchar(300);

Begin
	-- Si el nuevo Id Existe, no continuar
	if Exists (Select usua_Idusuario from s_Usuario where usua_IdUsuario=@NuevoId)
	Begin
		set @Resultado='ERROR: El nuevo Id de usuario: ' + @NuevoId + ' ya existe.';
	end
	else
	begin
		Update s_Cita set cita_RegPor=@NuevoId where cita_RegPor=@IdAct;
		Update s_Cita set cita_RegPorEstatusConf=@NuevoId where cita_RegPorEstatusConf=@IdAct;
		Update s_CompraLente set RegPor=@NuevoId where RegPor=@IdAct;
		
		Update s_CuadreCaja set cuac_IdUsuarioAbrioCaja=@NuevoId where cuac_IdUsuarioAbrioCaja=@IdAct;
		Update s_CuadreCaja set cuac_IdUsuarioCerroCaja=@NuevoId where cuac_IdUsuarioCerroCaja=@IdAct;
		
		Update s_DetallePago set dpag_RealizadoPor=@NuevoId where dpag_RealizadoPor=@IdAct;
		Update s_DetallePrePago set dpag_RealizadoPor=@NuevoId where dpag_RealizadoPor=@IdAct;
		
		Update s_EgresoCaja set egre_CodUsr=@NuevoId where egre_CodUsr=@IdAct;
		
		Update s_Expediente set expe_IdUsuarioRegistro=@NuevoId where expe_IdUsuarioRegistro=@IdAct;
		Update s_HeaderMovInv set RegPor=@NuevoId where RegPor=@IdAct;
		
		Update s_Headerpago set hpag_IdUsuario=@NuevoId where hpag_IdUsuario=@IdAct;
		Update s_Headerpago set hpag_ModPor=@NuevoId where hpag_ModPor=@IdAct;

		Update s_HeaderPoliticaCom set RegPor=@NuevoId where RegPor=@IdAct;
		
		Update s_HeaderPrepago set hpag_IdUsuario=@NuevoId where hpag_IdUsuario=@IdAct;
		
		Update s_HeaderPresupuesto set hpre_PreparadoPor=@NuevoId where hpre_PreparadoPor=@IdAct;
		
		Update s_HeaderPresupuestoSeguimiento set hprs_IdResponsable=@NuevoId where hprs_IdResponsable=@IdAct;
		
		Update s_ListaEspecialPacientes set lisp_RegistradoPor=@NuevoId where lisp_RegistradoPor=@IdAct;
		
		Update s_ListaPaciente set lisp_RegPor=@NuevoId where lisp_RegPor=@IdAct;
		Update s_ListaPaciente set lisp_UsrRegOpt=@NuevoId where lisp_UsrRegOpt=@IdAct;
		
		Update s_Log set IdUsuario=@NuevoId where IdUsuario=@IdAct;
		
		Update s_NotaCreditoSeguro set RegistradoPor=@NuevoId where RegistradoPor=@IdAct;
		Update s_NotaDebitoSeguro set RegistradoPor=@NuevoId where RegistradoPor=@IdAct;
		Update s_NotaExpediente set RegPor=@NuevoId where RegPor=@IdAct;

		Update s_Notificacion set RegPor=@NuevoId where RegPor=@IdAct;
		
		Update s_PagoSeguro set pags_RegistradoPor=@NuevoId where pags_RegistradoPor=@IdAct;

		Update s_Poliza set RegPor=@NuevoId where RegPor=@IdAct;
		
		Update s_PRECita set cita_RegPor=@NuevoId where cita_RegPor=@IdAct;
		Update s_PRECita set cita_RegPorEstatusConf=@NuevoId where cita_RegPorEstatusConf=@IdAct;

		Update s_ReclamacionesSeguro set recs_RegistradoPor=@NuevoId where recs_RegistradoPor=@IdAct;
		
		Update s_Usuario set usua_IdUsuario=@NuevoId where usua_IdUsuario=@IdAct;
		
		Update s_UsuarioCaja set usuc_IdUsuario=@NuevoId where usuc_IdUsuario=@IdAct;
		Update s_UsuarioDoctores set usud_IdUsuario=@NuevoId where usud_IdUsuario=@IdAct;
		
		Update s_UsuarioEstudios set usue_IdUsuario=@NuevoId where usue_IdUsuario=@IdAct;
		Update s_UsuarioListaServicios set usul_IdUsuario=@NuevoId where usul_IdUsuario=@IdAct;
		
		Update s_UsuarioListasEspeciales set usul_IdUsuario=@NuevoId where usul_IdUsuario=@IdAct;
		Update s_UsuarioOptica set IdUsuario=@NuevoId where IdUsuario=@IdAct;
	
		
	end

	Select @Resultado 
end
GO

-- Procedimiento: sp_ComisionDoctores
CREATE procedure [dbo].[sp_ComisionDoctores] (@FecIni as date, @FecFin as date, @IdDoctor as varchar(200), @IdSeguro as varchar(200), @IdCaja as varchar(200), @IdClase as varchar(200), @IdTipoSer as varchar(200),  @Categoria as char(1))
as 

-- Para prueba
--Declare @FecIni as date, @FecFin as date, @IdDoctor as varchar(200), @IdSeguro as varchar(200), @IdCaja as varchar(200), @IdClase as varchar(200), @IdTipoSer as varchar(200)
--Declare @Categoria as char(1)

--set @FecIni = '20180501'
--set @FecFin = '20180531'
--set @IdDoctor='ABLANCO-PFRANCO'
--set @IdSeguro ='000012-000017'
--set @IdCaja ='01'
--set @IdClase='E-P'
--set @IdTipoSer='1'
--set @Categoria='P'

--OJO:  Se adiciono al query 's_DetallePago.dpag_Secuencia', para que muestre todos los detalles, si hay dos detalles iguales solo mostraba uno ?????


--Buscar la FACTURADO:  Si la forma de Calculo es FAC, tomar ambos valores, Si es COB, tomar en cuenta solo el pago del paciente
SELECT        s_Doctor.doct_Nombre, case when isNull(hpag_IdDoctor2, '') = '' then '1. COMO DOCTOR UNICO' else '2. COMO PRINCIPAL (' + Cast(100.00-IsNull(s_DetallePoliticaCom.PrComAsis,0.0) as varchar) + '%)' end AS PagoDoctor,
						 s_HeaderPago.hpag_IdCaja, s_HeaderPago.hpag_Numero, s_HeaderPago.hpag_Fecha, s_DetallePago.dpag_Secuencia,
						 case when IsNull(expe_NumeroExpediente,0)=0 then rtrim(Isnull(hpag_Nombre,'')) + ' ' + rtrim(IsNull(hpag_Apellido,'')) else rtrim(IsNull(s_Expediente.expe_Nombres,'')) + ' ' + rtrim(IsNull(s_Expediente.expe_Apellidos,'')) end as NombrePaciente, 
						 s_ConceptoPago.conc_Nombre, 
                         conc_Tipo, conc_IdTipoSer, s_HeaderPago.hpag_Procedencia, hpag_IdSeguro,
						 case when round(hpag_monto-isNull(hpag_CoberturaSeguro,0.0),2)>0 then Round((dpag_Monto - IsNull(dpag_CoberturaSeguro,0.0)) *round( Round(hpag_monto-IsNull(hpag_CoberturaSeguro,0.0)-IsNull(hpag_ValorRecibidoCheque,0.0)-IsNull(hpag_ValorRecibidoTarjeta,0.0),2) / (hpag_monto -IsNull(hpag_CoberturaSeguro,0.0)),2),2) else Round(0,2) end as MontoEfectivo,
						 case when round(hpag_monto-isNull(hpag_CoberturaSeguro,0.0),2)>0 then Round((dpag_Monto - IsNull(dpag_CoberturaSeguro,0.0)) *round(IsNull(hpag_ValorRecibidoTarjeta,0.0) / (hpag_monto -IsNull(hpag_CoberturaSeguro,0.0)),2),2) else Round(0,2) end as MontoTarjeta,
						 case when round(hpag_monto-isNull(hpag_CoberturaSeguro,0.0),2)>0 then Round((dpag_Monto - IsNull(dpag_CoberturaSeguro,0.0)) *round(IsNull(hpag_ValorRecibidoCheque,0.0) / (hpag_monto -IsNull(hpag_CoberturaSeguro,0.0)),2),2) else Round(0,2) end as MontoCheque,
						 s_DetallePago.dpag_Monto as Monto,
						 -- Si la forma de calculo es Facturado o Cobrado. O es solo privado (Saldo)
						 case when s_DetallePoliticaCom.IdFormaCalculo='FAC' or s_DetallePoliticaCom.IdFormaCalculo='COB' or IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)=0 then
					          Cast(Round((s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Carrier.PorComision,0.0))/100,2) as decimal(12,2))
					     else 0.0 
						 end as MontoPrivado, 

						 -- Si la forma de calculo es Facturado
						 case when s_DetallePoliticaCom.IdFormaCalculo='FAC' then
									Cast(Round(IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Seguro.segu_PorRetencion,0.0))/100,2) as decimal(12,2))
							  else 0.0 
					     end  as MontoSeguro,

						 case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComExterno,0.0) end as PrComPrivado, 
					     case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComSegInterno,0.0) else isNull(s_DetallePoliticaCom.PrComSegExterno,0.0) end as PrComSeguro,
						 IsNull(s_Carrier.PorComision,0.0) as PrComTar, IsNull(s_Seguro.segu_PorRetencion,0.0) as PrComSeg, IsNull(s_DetallePoliticaCom.PrComCentro,0.0) as PrComCentro,
						 case when isNull(hpag_IdDoctor2, '') = '' then 100.00 else 100.00-IsNull(s_DetallePoliticaCom.PrComAsis,0.0)  end AS PrComGen,
						
						-- Si la forma de calculo es Facturado o Cobrado. O es solo privado (Saldo)
						 case when s_DetallePoliticaCom.IdFormaCalculo='FAC' or s_DetallePoliticaCom.IdFormaCalculo='COB' or IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)=0 then
							Cast(Round((s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Carrier.PorComision,0.0))/100 *  case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComExterno,0.0) end / 100,2) as decimal(12,2))
							else 0.0
						end as ComPrivado,
						
						  -- Si la forma de calculo es Facturado
						 case when s_DetallePoliticaCom.IdFormaCalculo='FAC' then
						           Cast(Round(IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Seguro.segu_PorRetencion,0.0))/100 * case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComSegInterno,0.0) else Isnull(s_DetallePoliticaCom.PrComSegExterno,0.0) end / 100,2) as decimal(12,2))
						      else 0.0
						 end as ComSeguro,
						 --2021/15/15 No Pagar mas de lo cobrado
						 case when s_DetallePoliticaCom.IdFormaCalculo='FAC' or (s_DetallePoliticaCom.IdFormaCalculo='SAL' and IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)=0 ) then
								     case when s_DetallePago.dpag_Monto <IsNull(s_DetallePoliticaCom.MontoFijo,0.0) then s_DetallePago.dpag_Monto else IsNull(s_DetallePoliticaCom.MontoFijo,0.0) end
							  when s_DetallePoliticaCom.IdFormaCalculo='COB' then
									 case when (s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0))<IsNull(s_DetallePoliticaCom.MontoFijo,0.0) then s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) else IsNull(s_DetallePoliticaCom.MontoFijo,0.0) end
						 end as MontoFijo,
						 case when hpag_NumeroPagoSeguro= Null then '' else 'S' end as EsCoberturaPagada, segu_NocSeg, 'F' as Origen, Null as pags_Numero, s_DetallePoliticaCom.IdFormaCalculo
FROM            s_DetallePago INNER JOIN
                         s_HeaderPago ON s_DetallePago.dpag_IdCaja = s_HeaderPago.hpag_IdCaja AND s_DetallePago.dpag_Numero = s_HeaderPago.hpag_Numero INNER JOIN
                         s_Doctor ON s_HeaderPago.hpag_IdDoctor = s_Doctor.doct_IdDoctor INNER JOIN
                         s_ConceptoPago ON s_DetallePago.dpag_IdConcepto = s_ConceptoPago.conc_IdConcepto LEFT OUTER JOIN
                         s_Expediente ON s_HeaderPago.hpag_NumeroExpediente = s_Expediente.expe_NumeroExpediente INNER JOIN
                         s_DetallePoliticaCom ON s_Doctor.doct_NumPolCom = s_DetallePoliticaCom.NumPol AND s_DetallePago.dpag_IdConcepto = s_DetallePoliticaCom.IdConcepto
						 LEFT OUTER JOIN s_Seguro on s_HeaderPago.hpag_IdSeguro = s_Seguro.segu_IdSeguro
						 LEFT OUTER JOIN s_Carrier s_Carrier on s_HeaderPago.hpag_IdCarrier = s_Carrier.IdCarrier
	where Cast(hpag_Fecha as Date)>=@FecIni and Cast(hpag_Fecha as Date)<=@FecFin and hpag_Estatus='P' 
	     and (doct_IdDoctor in (Select * from dbo.fnSplitString(@IdDoctor,'-')) or rtrim(@IdDoctor)='')
	     and (hpag_IdSeguro in (Select * from dbo.fnSplitString(@IdSeguro,'-')) or rtrim(@IdSeguro)='')
		 and (hpag_IdCaja in (Select * from dbo.fnSplitString(@IdCaja,'-')) or rtrim(@IdCaja)='')
		 and (conc_Tipo in (Select * from dbo.fnSplitString(@IdClase,'-')) or rtrim(@IdClase)='')
	     and (isNull(conc_IdTipoSer,'') = @IdTipoSer or rtrim(@IdTipoSer)='')
		 and ( (@Categoria='P' and s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0) or
			   (@Categoria='S' and IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0) or
			   rtrim(@Categoria)='')
		-- Incluir si la forma es FAC o si es COB y tiene monto cobrado Al paciente o si es SAL y no tiene cobertura
		and IsNull(s_DetallePago.dpag_Monto,0.0)>0
		and (s_DetallePoliticaCom.IdFormaCalculo='FAC' 
		     or (s_DetallePoliticaCom.IdFormaCalculo='COB' and (s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0))>0)
			 or (s_DetallePoliticaCom.IdFormaCalculo='SAL' and (IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0))=0) )		
union
SELECT        s_Doctor.doct_Nombre,  '3. COMO ASISTENTE (' + Cast(IsNull(s_DetallePoliticaCom.PrComAsis,0.0) as varchar) + '%)' AS PagoDoctor,
						 s_HeaderPago.hpag_IdCaja, s_HeaderPago.hpag_Numero, s_HeaderPago.hpag_Fecha, s_DetallePago.dpag_Secuencia,
						 case when IsNull(expe_NumeroExpediente,0)=0 then rtrim(Isnull(hpag_Nombre,'')) + ' ' + rtrim(IsNull(hpag_Apellido,'')) else rtrim(IsNull(s_Expediente.expe_Nombres,'')) + ' ' + rtrim(IsNull(s_Expediente.expe_Apellidos,'')) end as NombrePaciente, 
						 s_ConceptoPago.conc_Nombre, 
                         conc_Tipo, conc_IdTipoSer, s_HeaderPago.hpag_Procedencia, hpag_IdSeguro, 
						 case when round(hpag_monto-isNull(hpag_CoberturaSeguro,0.0),2)>0 then Round((dpag_Monto - IsNull(dpag_CoberturaSeguro,0.0)) *round( Round(hpag_monto-IsNull(hpag_CoberturaSeguro,0.0)-IsNull(hpag_ValorRecibidoCheque,0.0)-IsNull(hpag_ValorRecibidoTarjeta,0.0),2) / (hpag_monto -IsNull(hpag_CoberturaSeguro,0.0)),2),2) else Round(0,2) end as MontoEfectivo,
						 case when round(hpag_monto-isNull(hpag_CoberturaSeguro,0.0),2)>0 then Round((dpag_Monto - IsNull(dpag_CoberturaSeguro,0.0)) *round(IsNull(hpag_ValorRecibidoTarjeta,0.0) / (hpag_monto -IsNull(hpag_CoberturaSeguro,0.0)),2),2) else Round(0.0,2) end as MontoTarjeta,
						 case when round(hpag_monto-isNull(hpag_CoberturaSeguro,0.0),2)>0 then Round((dpag_Monto - IsNull(dpag_CoberturaSeguro,0.0)) *round(IsNull(hpag_ValorRecibidoCheque,0.0) / (hpag_monto -IsNull(hpag_CoberturaSeguro,0.0)),2),2) else Round(0,2) end as MontoCheque,
						 s_DetallePago.dpag_Monto as Monto,

						 -- Si la forma de calculo es Facturado o Cobrado. O es solo privado (Saldo)
						 case when s_DetallePoliticaCom.IdFormaCalculo='FAC' or s_DetallePoliticaCom.IdFormaCalculo='COB' or IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)=0 then
					          Cast(Round((s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Carrier.PorComision,0.0))/100,2) as decimal(12,2))
					     else 0.0 
						 end as MontoPrivado, 

						 -- Si la forma de calculo es Facturado
						 case when s_DetallePoliticaCom.IdFormaCalculo='FAC' then
									Cast(Round(IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Seguro.segu_PorRetencion,0.0))/100,2) as decimal(12,2))
							  else 0.0 
					     end  as MontoSeguro,

						 case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComExterno,0.0) end as PrComPrivado, 
					     case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComSegInterno,0.0) else isNull(s_DetallePoliticaCom.PrComSegExterno,0.0) end as PrComSeguro,
						 IsNull(s_Carrier.PorComision,0.0) as PrComTar, IsNull(s_Seguro.segu_PorRetencion,0.0) as PrComSeg, IsNull(s_DetallePoliticaCom.PrComCentro,0.0) as PrComCentro,
						 case when isNull(hpag_IdDoctor2, '') = '' then 0.00 else IsNull(s_DetallePoliticaCom.PrComAsis,0.0)  end AS PrComGen,
						
						-- Si la forma de calculo es Facturado o Cobrado. O es solo privado (Saldo)
						 case when s_DetallePoliticaCom.IdFormaCalculo='FAC' or s_DetallePoliticaCom.IdFormaCalculo='COB' or IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)=0 then
							Cast(Round((s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Carrier.PorComision,0.0))/100 *  case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComExterno,0.0) end / 100,2) as decimal(12,2))
							else 0.0
						end as ComPrivado,
						
						  -- Si la forma de calculo es Facturado
						 case when s_DetallePoliticaCom.IdFormaCalculo='FAC' then
						           Cast(Round(IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Seguro.segu_PorRetencion,0.0))/100 * case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComSegInterno,0.0) else Isnull(s_DetallePoliticaCom.PrComSegExterno,0.0) end / 100,2) as decimal(12,2))
						      else 0.0
						 end as ComSeguro,
						 --2021/15/15 No Pagar mas de lo cobrado
					     case when s_DetallePoliticaCom.IdFormaCalculo='FAC' or (s_DetallePoliticaCom.IdFormaCalculo='SAL' and IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)=0 ) then
								     case when s_DetallePago.dpag_Monto <IsNull(s_DetallePoliticaCom.MontoFijo,0.0) then s_DetallePago.dpag_Monto else IsNull(s_DetallePoliticaCom.MontoFijo,0.0) end
							  when s_DetallePoliticaCom.IdFormaCalculo='COB' then
									 case when (s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0))<IsNull(s_DetallePoliticaCom.MontoFijo,0.0) then s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) else IsNull(s_DetallePoliticaCom.MontoFijo,0.0) end
						 end as MontoFijo,
						  case when hpag_NumeroPagoSeguro= Null then '' else 'S' end as EsCoberturaPagada, segu_NocSeg, 'FA' as Origen, Null as pags_Numero,  s_DetallePoliticaCom.IdFormaCalculo
FROM            s_DetallePago INNER JOIN
                         s_HeaderPago ON s_DetallePago.dpag_IdCaja = s_HeaderPago.hpag_IdCaja AND s_DetallePago.dpag_Numero = s_HeaderPago.hpag_Numero INNER JOIN
                         s_Doctor ON s_HeaderPago.hpag_IdDoctor2 = s_Doctor.doct_IdDoctor INNER JOIN
                         s_ConceptoPago ON s_DetallePago.dpag_IdConcepto = s_ConceptoPago.conc_IdConcepto LEFT OUTER JOIN
                         s_Expediente ON s_HeaderPago.hpag_NumeroExpediente = s_Expediente.expe_NumeroExpediente INNER JOIN
                         s_DetallePoliticaCom ON s_Doctor.doct_NumPolCom = s_DetallePoliticaCom.NumPol AND s_DetallePago.dpag_IdConcepto = s_DetallePoliticaCom.IdConcepto
						 LEFT OUTER JOIN s_Seguro on s_HeaderPago.hpag_IdSeguro = s_Seguro.segu_IdSeguro
				         LEFT OUTER JOIN s_Carrier s_Carrier on s_HeaderPago.hpag_IdCarrier = s_Carrier.IdCarrier
	where Cast(hpag_Fecha as Date)>=@FecIni and Cast(hpag_Fecha as Date)<=@FecFin and hpag_Estatus='P' 
	   and (doct_IdDoctor in (Select * from dbo.fnSplitString(@IdDoctor,'-')) or rtrim(@IdDoctor)='')
	   and (hpag_IdSeguro in (Select * from dbo.fnSplitString(@IdSeguro,'-')) or rtrim(@IdSeguro)='')
	   and (hpag_IdCaja in (Select * from dbo.fnSplitString(@IdClase,'-')) or rtrim(@IdCaja)='')
	   and (conc_Tipo in (Select * from dbo.fnSplitString(@IdClase,'-')) or rtrim(@IdClase)='')
       and (isNull(conc_IdTipoSer,'') = @IdTipoSer or rtrim(@IdTipoSer)='')
	   and ( (@Categoria='P' and s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0) or
			   (@Categoria='S' and IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0) or
			   rtrim(@Categoria)='')
	   and IsNull(s_DetallePago.dpag_Monto,0.0)>0
	  -- Incluir si la forma es FAC o si es COB y tiene monto cobrado Al paciente o si es SAL y no tiene cobertura
		and (s_DetallePoliticaCom.IdFormaCalculo='FAC' 
		     or (s_DetallePoliticaCom.IdFormaCalculo='COB' and (s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0))>0)
			 or (s_DetallePoliticaCom.IdFormaCalculo='SAL' and (IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0))=0) )		
union
-----------------------------------------------------------------------------------------------------------
--Buscar lo COBRADO:  
-----------------------------------------------------------------------------------------------------------
SELECT        s_Doctor.doct_Nombre, case when isNull(hpag_IdDoctor2, '') = '' then '1. COMO DOCTOR UNICO' else '2. COMO PRINCIPAL (' + Cast(100.00-IsNull(s_DetallePoliticaCom.PrComAsis,0.0) as varchar) + '%)' end AS PagoDoctor,
						 s_HeaderPago.hpag_IdCaja, s_HeaderPago.hpag_Numero, s_HeaderPago.hpag_Fecha, s_DetallePago.dpag_Secuencia,
						 case when IsNull(expe_NumeroExpediente,0)=0 then rtrim(Isnull(hpag_Nombre,'')) + ' ' + rtrim(IsNull(hpag_Apellido,'')) else rtrim(IsNull(s_Expediente.expe_Nombres,'')) + ' ' + rtrim(IsNull(s_Expediente.expe_Apellidos,'')) end as NombrePaciente, 
						 s_ConceptoPago.conc_Nombre, 
                         conc_Tipo, conc_IdTipoSer, s_HeaderPago.hpag_Procedencia, hpag_IdSeguro,
						 case when s_PagoSeguro.pags_FormaPago='D'  then  
						      case when s_PagoSeguro_Detalles.Saldo = 1 then s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)  else  s_PagoSeguro_Detalles.Monto end  
							      else 0.0 end as MontoEfectivo,
						 0.0 as MontoTarjeta,
						 case when s_PagoSeguro.pags_FormaPago='C'  then  
						      case when s_PagoSeguro_Detalles.Saldo = 1 then s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)  else  s_PagoSeguro_Detalles.Monto end  
							      else 0.0 end as MontoCheque,
						 s_DetallePago.dpag_Monto as Monto,
						
						 -- Si la forma de calculo es SALDO y se saldo la factura
						 case when s_DetallePoliticaCom.IdFormaCalculo='SAL' and s_PagoSeguro_Detalles.Saldo = 1 then
								   cast(Round((s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) ) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Carrier.PorComision,0.0))/100,2) as decimal(12,2))
							  else 0.0
						  end as MontoPrivado, 

						 -- Si la forma de calculo es SALDO y se saldo la factura o e COBRO
						 case when (s_DetallePoliticaCom.IdFormaCalculo='SAL' and s_PagoSeguro_Detalles.Saldo = 1) or s_DetallePoliticaCom.IdFormaCalculo='COB'  
						            then Cast(Round(IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Seguro.segu_PorRetencion,0.0))/100,2) as decimal(12,2))
							         else 0.0 
					     end as MontoSeguro,

						 case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComExterno,0.0) end as PrComPrivado, 
					     case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComSegInterno,0.0) else s_DetallePoliticaCom.PrComSegExterno end as PrComSeguro,
						 IsNull(s_Carrier.PorComision,0.0) as PrComTar, IsNull(s_Seguro.segu_PorRetencion,0.0) as PrComSeg, IsNull(s_DetallePoliticaCom.PrComCentro,0.0) as PrComCentro,
						 case when isNull(hpag_IdDoctor2, '') = '' then 100.00 else 100.00-IsNull(s_DetallePoliticaCom.PrComAsis,0.0)  end AS PrComGen,
						  
						   -- Si la forma de calculo es SALDO y se saldo la factura
						 case when s_DetallePoliticaCom.IdFormaCalculo='SAL' and s_PagoSeguro_Detalles.Saldo = 1 then
						          Cast(Round((s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Carrier.PorComision,0.0))/100 *  case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComExterno,0.0) end / 100,2) as decimal(12,2))
						      else 0.0 end  as ComPrivado,

						 -- Si la forma de calculo es SALDO y se saldo la factura o es cobro
						 case when (s_DetallePoliticaCom.IdFormaCalculo='SAL' and s_PagoSeguro_Detalles.Saldo = 1) or s_DetallePoliticaCom.IdFormaCalculo='COB'   then
								  Cast(Round(IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Seguro.segu_PorRetencion,0.0))/100 * case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComSegInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComSegExterno,0.0) end / 100,2) as decimal(12,2))
							 else 0.0 
						 end as ComSeguro,

						  --2021/15/15 No Pagar mas de lo cobrado
						 case when s_DetallePoliticaCom.IdFormaCalculo='SAL' and s_PagoSeguro_Detalles.Saldo = 1 then
									case when s_DetallePago.dpag_Monto <IsNull(s_DetallePoliticaCom.MontoFijo,0.0) then s_DetallePago.dpag_Monto else IsNull(s_DetallePoliticaCom.MontoFijo,0.0) end
						      when s_DetallePoliticaCom.IdFormaCalculo='COB' and s_PagoSeguro_Detalles.Saldo = 1 then
									case when IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)<IsNull(s_DetallePoliticaCom.MontoFijo,0.0) then IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) else IsNull(s_DetallePoliticaCom.MontoFijo,0.0) end
							end as MontoFijo,
						 case when s_PagoSeguro_Detalles.Saldo = 0  then '' else 'S' end as EsCoberturaPagada, segu_NocSeg, 'C' as Origen, s_PagoSeguro.pags_Numero, s_DetallePoliticaCom.IdFormaCalculo
FROM  s_PagoSeguro_Detalles
	  INNER JOIN s_PagoSeguro on s_PagoSeguro_Detalles.pags_Numero= s_PagoSeguro.pags_Numero
	  INNER JOIN s_DetallePago on s_PagoSeguro_Detalles.hpag_IdCaja = s_DetallePago.dpag_IdCaja and s_PagoSeguro_Detalles.hpag_numero = s_DetallePago.dpag_numero
	  INNER JOIN s_HeaderPago ON s_DetallePago.dpag_IdCaja = s_HeaderPago.hpag_IdCaja AND s_DetallePago.dpag_Numero = s_HeaderPago.hpag_Numero 
	  INNER JOIN s_Doctor ON s_HeaderPago.hpag_IdDoctor = s_Doctor.doct_IdDoctor 
	  INNER JOIN s_ConceptoPago ON s_DetallePago.dpag_IdConcepto = s_ConceptoPago.conc_IdConcepto 
	  LEFT OUTER JOIN s_Expediente ON s_HeaderPago.hpag_NumeroExpediente = s_Expediente.expe_NumeroExpediente 
	  INNER JOIN s_DetallePoliticaCom ON s_Doctor.doct_NumPolCom = s_DetallePoliticaCom.NumPol AND s_DetallePago.dpag_IdConcepto = s_DetallePoliticaCom.IdConcepto 
	  LEFT OUTER JOIN s_Seguro on s_HeaderPago.hpag_IdSeguro = s_Seguro.segu_IdSeguro
						 LEFT OUTER JOIN s_Carrier s_Carrier on s_HeaderPago.hpag_IdCarrier = s_Carrier.IdCarrier
	where Cast(pags_Fecha as Date)>=@FecIni and Cast(pags_Fecha as Date)<=@FecFin and pags_Estatus='P' 
	     and (doct_IdDoctor in (Select * from dbo.fnSplitString(@IdDoctor,'-')) or rtrim(@IdDoctor)='')
	     and (hpag_IdSeguro in (Select * from dbo.fnSplitString(@IdSeguro,'-')) or rtrim(@IdSeguro)='')
		 and (s_HeaderPago.hpag_IdCaja in (Select * from dbo.fnSplitString(@IdCaja,'-')) or rtrim(@IdCaja)='')
		 and (conc_Tipo in (Select * from dbo.fnSplitString(@IdClase,'-')) or rtrim(@IdClase)='')
		 and (isNull(conc_IdTipoSer,'') = @IdTipoSer or rtrim(@IdTipoSer)='')
		 and ( (@Categoria='P' and s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0) or
			   (@Categoria='S' and IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0) or
			   rtrim(@Categoria)='')
		 and IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0 
		 and s_PagoSeguro_Detalles.Tipo='COB'       ----Solo tomar en cuenta los pagos de Cobertura,  no tomar en cuenta los descuentos
		  -- Si es saldo, tomar los recibos que saldaron en el período
		 and ((s_DetallePoliticaCom.IdFormaCalculo='SAL' and Cast(hpag_FechaSaldo as Date)>=@FecIni and Cast(hpag_FechaSaldo as Date)<=@FecFin) OR s_DetallePoliticaCom.IdFormaCalculo='COB')
union
SELECT        s_Doctor.doct_Nombre,  '3. COMO ASISTENTE (' + Cast(IsNull(s_DetallePoliticaCom.PrComAsis,0.0) as varchar) + '%)' AS PagoDoctor,
						 s_HeaderPago.hpag_IdCaja, s_HeaderPago.hpag_Numero, s_HeaderPago.hpag_Fecha, s_DetallePago.dpag_Secuencia,
						 case when IsNull(expe_NumeroExpediente,0)=0 then rtrim(Isnull(hpag_Nombre,'')) + ' ' + rtrim(IsNull(hpag_Apellido,'')) else rtrim(IsNull(s_Expediente.expe_Nombres,'')) + ' ' + rtrim(IsNull(s_Expediente.expe_Apellidos,'')) end as NombrePaciente, 
						 s_ConceptoPago.conc_Nombre, 
                         conc_Tipo, conc_IdTipoSer, s_HeaderPago.hpag_Procedencia, hpag_IdSeguro, 
						 case when s_PagoSeguro.pags_FormaPago='D'  then  
						      case when s_PagoSeguro_Detalles.Saldo = 1 then s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)  else  s_PagoSeguro_Detalles.Monto end  
							      else 0.0 end as MontoEfectivo,
						 0.0 as MontoTarjeta,
						 case when s_PagoSeguro.pags_FormaPago='C'  then  
						      case when s_PagoSeguro_Detalles.Saldo = 1 then s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)  else  s_PagoSeguro_Detalles.Monto end  
							      else 0.0 end as MontoCheque,
						s_DetallePago.dpag_Monto as Monto,
					
						-- Si la forma de calculo es SALDO y se saldo la factura
						 case when s_DetallePoliticaCom.IdFormaCalculo='SAL' and s_PagoSeguro_Detalles.Saldo = 1 then
								   cast(Round((s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) ) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Carrier.PorComision,0.0))/100,2) as decimal(12,2))
							  else 0.0
						  end as MontoPrivado, 

						 -- Si la forma de calculo es SALDO y se saldo la factura o e COBRO
						 case when (s_DetallePoliticaCom.IdFormaCalculo='SAL' and s_PagoSeguro_Detalles.Saldo = 1) or s_DetallePoliticaCom.IdFormaCalculo='COB'  
						            then Cast(Round(IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Seguro.segu_PorRetencion,0.0))/100,2) as decimal(12,2))
							         else 0.0 
					     end as MontoSeguro,

						 case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComExterno,0.0) end as PrComPrivado, 
					     case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComSegInterno,0.0) else s_DetallePoliticaCom.PrComSegExterno end as PrComSeguro,
						 IsNull(s_Carrier.PorComision,0.0) as PrComTar, IsNull(s_Seguro.segu_PorRetencion,0.0) as PrComSeg, IsNull(s_DetallePoliticaCom.PrComCentro,0.0) as PrComCentro,
						 IsNull(s_DetallePoliticaCom.PrComAsis,0.0) AS PrComGen,
						  
						   -- Si la forma de calculo es SALDO y se saldo la factura
						 case when s_DetallePoliticaCom.IdFormaCalculo='SAL' and s_PagoSeguro_Detalles.Saldo = 1 then
						          Cast(Round((s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Carrier.PorComision,0.0))/100 *  case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComExterno,0.0) end / 100,2) as decimal(12,2))
						      else 0.0 end  as ComPrivado,

						 -- Si la forma de calculo es SALDO y se saldo la factura o es cobro
						 case when (s_DetallePoliticaCom.IdFormaCalculo='SAL' and s_PagoSeguro_Detalles.Saldo = 1) or s_DetallePoliticaCom.IdFormaCalculo='COB'   then
								  Cast(Round(IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Seguro.segu_PorRetencion,0.0))/100 * case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComSegInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComSegExterno,0.0) end / 100,2) as decimal(12,2))
							 else 0.0 
						 end as ComSeguro,

						 --2021/15/15 No Pagar mas de lo cobrado
						 case when s_DetallePoliticaCom.IdFormaCalculo='SAL' and s_PagoSeguro_Detalles.Saldo = 1 then
									case when s_DetallePago.dpag_Monto <IsNull(s_DetallePoliticaCom.MontoFijo,0.0) then s_DetallePago.dpag_Monto else IsNull(s_DetallePoliticaCom.MontoFijo,0.0) end
						      when s_DetallePoliticaCom.IdFormaCalculo='COB' and s_PagoSeguro_Detalles.Saldo = 1 then
									case when IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)<IsNull(s_DetallePoliticaCom.MontoFijo,0.0) then IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) else IsNull(s_DetallePoliticaCom.MontoFijo,0.0) end
							end as MontoFijo,
						  case when hpag_NumeroPagoSeguro= Null then '' else 'S' end as EsCoberturaPagada, segu_NocSeg, 'CA' as Origen, s_PagoSeguro.pags_Numero, s_DetallePoliticaCom.IdFormaCalculo
FROM   s_PagoSeguro_Detalles
	   INNER JOIN s_PagoSeguro on s_PagoSeguro_Detalles.pags_Numero= s_PagoSeguro.pags_Numero
       INNER JOIN s_DetallePago on s_PagoSeguro_Detalles.hpag_IdCaja = s_DetallePago.dpag_IdCaja and s_PagoSeguro_Detalles.hpag_numero = s_DetallePago.dpag_numero
       INNER JOIN s_HeaderPago ON s_DetallePago.dpag_IdCaja = s_HeaderPago.hpag_IdCaja AND s_DetallePago.dpag_Numero = s_HeaderPago.hpag_Numero INNER JOIN
                         s_Doctor ON s_HeaderPago.hpag_IdDoctor2 = s_Doctor.doct_IdDoctor INNER JOIN
                         s_ConceptoPago ON s_DetallePago.dpag_IdConcepto = s_ConceptoPago.conc_IdConcepto LEFT OUTER JOIN
                         s_Expediente ON s_HeaderPago.hpag_NumeroExpediente = s_Expediente.expe_NumeroExpediente INNER JOIN
                         s_DetallePoliticaCom ON s_Doctor.doct_NumPolCom = s_DetallePoliticaCom.NumPol AND s_DetallePago.dpag_IdConcepto = s_DetallePoliticaCom.IdConcepto
						 LEFT OUTER JOIN s_Seguro on s_HeaderPago.hpag_IdSeguro = s_Seguro.segu_IdSeguro
						 LEFT OUTER JOIN s_Carrier s_Carrier on s_HeaderPago.hpag_IdCarrier = s_Carrier.IdCarrier
	where Cast(pags_Fecha as Date)>=@FecIni and Cast(pags_Fecha as Date)<=@FecFin and pags_Estatus='P' 
	   and (doct_IdDoctor in (Select * from dbo.fnSplitString(@IdDoctor,'-')) or rtrim(@IdDoctor)='')
	   and (hpag_IdSeguro in (Select * from dbo.fnSplitString(@IdSeguro,'-')) or rtrim(@IdSeguro)='')
	   and (s_HeaderPago.hpag_IdCaja in (Select * from dbo.fnSplitString(@IdClase,'-')) or rtrim(@IdCaja)='')
	   and (conc_Tipo in (Select * from dbo.fnSplitString(@IdClase,'-')) or rtrim(@IdClase)='')
	   and (isNull(conc_IdTipoSer,'') = @IdTipoSer or rtrim(@IdTipoSer)='')
	   and ( (@Categoria='P' and s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0) or
			   (@Categoria='S' and IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0) or
			   rtrim(@Categoria)='')
       and IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0
       and s_PagoSeguro_Detalles.Tipo='COB'       ----Solo tomar en cuenta los pagos de Cobertura,  no tomar en cuenta los descuentos
	    -- Si es saldo, tomar los recibos que saldaron en el período
		 and ((s_DetallePoliticaCom.IdFormaCalculo='SAL' and Cast(hpag_FechaSaldo as Date)>=@FecIni and Cast(hpag_FechaSaldo as Date)<=@FecFin) OR s_DetallePoliticaCom.IdFormaCalculo='COB')
Order by Doct_Nombre, PagoDoctor, conc_nombre, hpag_fecha, hpag_IdCaja, hpag_Numero
GO

-- Procedimiento: sp_ComisionDoctoresOld
CREATE procedure [dbo].[sp_ComisionDoctoresOld] (@FecIni as date, @FecFin as date, @IdDoctor as varchar(200), @IdSeguro as varchar(200), @IdCaja as varchar(200), @IdClase as varchar(200), @IdTipoSer as varchar(200),  @Categoria as char(1))
as 

-- Para prueba
--Declare @FecIni as date, @FecFin as date, @IdDoctor as varchar(200), @IdSeguro as varchar(200), @IdCaja as varchar(200), @IdClase as varchar(200), @IdTipoSer as varchar(200)
--Declare @Categoria as char(1)

--set @FecIni = '20180501'
--set @FecFin = '20180531'
--set @IdDoctor='ABLANCO-PFRANCO'
--set @IdSeguro ='000012-000017'
--set @IdCaja ='01'
--set @IdClase='E-P'
--set @IdTipoSer='1'
--set @Categoria='P'

--OJO:  Se adiciono al query 's_DetallePago.dpag_Secuencia', para que muestre todos los detalles, si hay dos detalles iguales solo mostraba uno ?????


--Buscar la FACTURADO:  Si la forma de Calculo es FAC, tomar ambos valores, Si es COB, tomar en cuenta solo el pago del paciente
SELECT        s_Doctor.doct_Nombre, case when isNull(hpag_IdDoctor2, '') = '' then '1. COMO DOCTOR UNICO' else '2. COMO PRINCIPAL (' + Cast(100.00-IsNull(s_DetallePoliticaCom.PrComAsis,0.0) as varchar) + '%)' end AS PagoDoctor,
						 s_HeaderPago.hpag_IdCaja, s_HeaderPago.hpag_Numero, s_HeaderPago.hpag_Fecha, s_DetallePago.dpag_Secuencia,
						 case when IsNull(expe_NumeroExpediente,0)=0 then rtrim(Isnull(hpag_Nombre,'')) + ' ' + rtrim(IsNull(hpag_Apellido,'')) else rtrim(IsNull(s_Expediente.expe_Nombres,'')) + ' ' + rtrim(IsNull(s_Expediente.expe_Apellidos,'')) end as NombrePaciente, 
						 s_ConceptoPago.conc_Nombre, 
                         conc_Tipo, conc_IdTipoSer, s_HeaderPago.hpag_Procedencia, hpag_IdSeguro,
						 case when round(hpag_monto-isNull(hpag_CoberturaSeguro,0.0),2)>0 then Round((dpag_Monto - IsNull(dpag_CoberturaSeguro,0.0)) *round( Round(hpag_monto-IsNull(hpag_CoberturaSeguro,0.0)-IsNull(hpag_ValorRecibidoCheque,0.0)-IsNull(hpag_ValorRecibidoTarjeta,0.0),2) / (hpag_monto -IsNull(hpag_CoberturaSeguro,0.0)),2),2) else Round(0,2) end as MontoEfectivo,
						 case when round(hpag_monto-isNull(hpag_CoberturaSeguro,0.0),2)>0 then Round((dpag_Monto - IsNull(dpag_CoberturaSeguro,0.0)) *round(IsNull(hpag_ValorRecibidoTarjeta,0.0) / (hpag_monto -IsNull(hpag_CoberturaSeguro,0.0)),2),2) else Round(0,2) end as MontoTarjeta,
						 case when round(hpag_monto-isNull(hpag_CoberturaSeguro,0.0),2)>0 then Round((dpag_Monto - IsNull(dpag_CoberturaSeguro,0.0)) *round(IsNull(hpag_ValorRecibidoCheque,0.0) / (hpag_monto -IsNull(hpag_CoberturaSeguro,0.0)),2),2) else Round(0,2) end as MontoCheque,
						 s_DetallePago.dpag_Monto as Monto,
					     -- Si la forma de calculo es Facturado o Pagado. O es solo privado
						 case when s_DetallePoliticaCom.IdFormaCalculo='FAC' or s_DetallePoliticaCom.IdFormaCalculo='COB' or IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)=0 then
								  Round((s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Carrier.PorComision,0.0))/100,2)
							  else 0.0 end 
							  as MontoPrivado, 
						 -- Si la forma de calculo es Facturado
						 case when s_DetallePoliticaCom.IdFormaCalculo='FAC' then
									Round(IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Seguro.segu_PorRetencion,0.0))/100,2) 
							  else 0.0 
							  end  as MontoSeguro,
						 case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComExterno,0.0) end as PrComPrivado, 
					     case when hpag_Procedencia='E' then IsNull(s_DetallePoliticaCom.PrComSegInterno,0.0) else isNull(s_DetallePoliticaCom.PrComSegExterno,0.0) end as PrComSeguro,
						 IsNull(s_Carrier.PorComision,0.0) as PrComTar, IsNull(s_Seguro.segu_PorRetencion,0.0) as PrComSeg, IsNull(s_DetallePoliticaCom.PrComCentro,0.0) as PrComCentro,
						 case when isNull(hpag_IdDoctor2, '') = '' then 100.00 else 100.00-IsNull(s_DetallePoliticaCom.PrComAsis,0.0)  end AS PrComGen,
						  -- Si la forma de calculo es Facturado o Cobrado  O es solo privado
						 case when s_DetallePoliticaCom.IdFormaCalculo='FAC' or s_DetallePoliticaCom.IdFormaCalculo='COB' or IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)=0 then
									Round((s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Carrier.PorComision,0.0))/100 *  case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComExterno,0.0) end / 100,2) + IsNull(s_DetallePoliticaCom.MontoFijo,0.0)
							   else 0.0
							   end as ComPrivado,
						  -- Si la forma de calculo es Facturado
						 case when s_DetallePoliticaCom.IdFormaCalculo='FAC' then
						           Round(IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Seguro.segu_PorRetencion,0.0))/100 * case when hpag_Procedencia='E' then IsNull(s_DetallePoliticaCom.PrComSegInterno,0.0) else Isnull(s_DetallePoliticaCom.PrComSegExterno,0.0) end / 100,2) 
						      else 0.0
						 end as ComSeguro,
						 IsNull(s_DetallePoliticaCom.MontoFijo,0.0) as MontoFijo, 
						 case when hpag_NumeroPagoSeguro= Null then '' else 'S' end as EsCoberturaPagada, segu_NocSeg, 'F' as Origen, Null as pags_Numero, s_DetallePoliticaCom.IdFormaCalculo
FROM            s_DetallePago INNER JOIN
                         s_HeaderPago ON s_DetallePago.dpag_IdCaja = s_HeaderPago.hpag_IdCaja AND s_DetallePago.dpag_Numero = s_HeaderPago.hpag_Numero INNER JOIN
                         s_Doctor ON s_HeaderPago.hpag_IdDoctor = s_Doctor.doct_IdDoctor INNER JOIN
                         s_ConceptoPago ON s_DetallePago.dpag_IdConcepto = s_ConceptoPago.conc_IdConcepto LEFT OUTER JOIN
                         s_Expediente ON s_HeaderPago.hpag_NumeroExpediente = s_Expediente.expe_NumeroExpediente INNER JOIN
                         s_DetallePoliticaCom ON s_Doctor.doct_NumPolCom = s_DetallePoliticaCom.NumPol AND s_DetallePago.dpag_IdConcepto = s_DetallePoliticaCom.IdConcepto
						 LEFT OUTER JOIN s_Seguro on s_HeaderPago.hpag_IdSeguro = s_Seguro.segu_IdSeguro
						 LEFT OUTER JOIN s_Carrier s_Carrier on s_HeaderPago.hpag_IdCarrier = s_Carrier.IdCarrier
	where Cast(hpag_Fecha as Date)>=@FecIni and Cast(hpag_Fecha as Date)<=@FecFin and hpag_Estatus='P' 
	     and (doct_IdDoctor in (Select * from dbo.fnSplitString(@IdDoctor,'-')) or rtrim(@IdDoctor)='')
	     and (hpag_IdSeguro in (Select * from dbo.fnSplitString(@IdSeguro,'-')) or rtrim(@IdSeguro)='')
		 and (hpag_IdCaja in (Select * from dbo.fnSplitString(@IdCaja,'-')) or rtrim(@IdCaja)='')
		 and (conc_Tipo in (Select * from dbo.fnSplitString(@IdClase,'-')) or rtrim(@IdClase)='')
	    and (isNull(conc_IdTipoSer,'') = @IdTipoSer or rtrim(@IdTipoSer)='')
		 and ( (@Categoria='P' and s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0) or
			   (@Categoria='S' and IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0) or
			   rtrim(@Categoria)='')
		-- Si la forma de calculo es FAC y tiene cobertura o Si pago el paciente y la forma de calculo es diferente a SAL o no tiene cobertura
		and  ( (s_DetallePoliticaCom.IdFormaCalculo='FAC' and IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0)  or
		       ( 
			      (s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0))>0 and (s_DetallePoliticaCom.IdFormaCalculo<>'SAL' or IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)=0) ) 
			   )
union
SELECT        s_Doctor.doct_Nombre,  '3. COMO ASISTENTE (' + Cast(IsNull(s_DetallePoliticaCom.PrComAsis,0.0) as varchar) + '%)' AS PagoDoctor,
						 s_HeaderPago.hpag_IdCaja, s_HeaderPago.hpag_Numero, s_HeaderPago.hpag_Fecha, s_DetallePago.dpag_Secuencia,
						 case when IsNull(expe_NumeroExpediente,0)=0 then rtrim(Isnull(hpag_Nombre,'')) + ' ' + rtrim(IsNull(hpag_Apellido,'')) else rtrim(IsNull(s_Expediente.expe_Nombres,'')) + ' ' + rtrim(IsNull(s_Expediente.expe_Apellidos,'')) end as NombrePaciente, 
						 s_ConceptoPago.conc_Nombre, 
                         conc_Tipo, conc_IdTipoSer, s_HeaderPago.hpag_Procedencia, hpag_IdSeguro, 
						 case when round(hpag_monto-isNull(hpag_CoberturaSeguro,0.0),2)>0 then Round((dpag_Monto - IsNull(dpag_CoberturaSeguro,0.0)) *round( Round(hpag_monto-IsNull(hpag_CoberturaSeguro,0.0)-IsNull(hpag_ValorRecibidoCheque,0.0)-IsNull(hpag_ValorRecibidoTarjeta,0.0),2) / (hpag_monto -IsNull(hpag_CoberturaSeguro,0.0)),2),2) else Round(0,2) end as MontoEfectivo,
						 case when round(hpag_monto-isNull(hpag_CoberturaSeguro,0.0),2)>0 then Round((dpag_Monto - IsNull(dpag_CoberturaSeguro,0.0)) *round(IsNull(hpag_ValorRecibidoTarjeta,0.0) / (hpag_monto -IsNull(hpag_CoberturaSeguro,0.0)),2),2) else Round(0.0,2) end as MontoTarjeta,
						 case when round(hpag_monto-isNull(hpag_CoberturaSeguro,0.0),2)>0 then Round((dpag_Monto - IsNull(dpag_CoberturaSeguro,0.0)) *round(IsNull(hpag_ValorRecibidoCheque,0.0) / (hpag_monto -IsNull(hpag_CoberturaSeguro,0.0)),2),2) else Round(0,2) end as MontoCheque,
						 s_DetallePago.dpag_Monto as Monto,
						  -- Si la forma de calculo es Facturado o Pagado. O es solo privado
						 case when s_DetallePoliticaCom.IdFormaCalculo='FAC' or s_DetallePoliticaCom.IdFormaCalculo='COB' or IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)=0 then
								   round((s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Carrier.PorComision,0.0))/100,2) 
							  else 0.0 end 
							  as MontoPrivado, 
						 -- Si la forma de calculo es Facturado
						 case when s_DetallePoliticaCom.IdFormaCalculo='FAC' then
									Round(IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Seguro.segu_PorRetencion,0.0))/100,2)
							  else 0.0 
							  end  as MontoSeguro, 
						 case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComExterno,0.0) end as PrComPrivado, 
					     case when hpag_Procedencia='E' then IsNull(s_DetallePoliticaCom.PrComSegInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComSegExterno,0.0) end as PrComSeguro,
						 IsNull(s_Carrier.PorComision,0.0) as PrComTar, IsNull(s_Seguro.segu_PorRetencion,0.0) as PrComSeg, IsNull(s_DetallePoliticaCom.PrComCentro,0.0) as PrComCentro,
						 IsNull(s_DetallePoliticaCom.PrComAsis,0.0) AS PrComGen,
						  -- Si la forma de calculo es Facturado o Cobrado  O es solo privado
						 case when s_DetallePoliticaCom.IdFormaCalculo='FAC' or s_DetallePoliticaCom.IdFormaCalculo='COB' or IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)=0 then
									Round((s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Carrier.PorComision,0.0))/100 *  case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComExterno,0.0) end / 100,2) + IsNull(s_DetallePoliticaCom.MontoFijo,0.0)
							   else 0.0
							   end as ComPrivado,
						  -- Si la forma de calculo es Facturado
						 case when s_DetallePoliticaCom.IdFormaCalculo='FAC' then
						           Round(IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Seguro.segu_PorRetencion,0.0))/100* case when hpag_Procedencia='E' then IsNull(s_DetallePoliticaCom.PrComSegInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComSegExterno,0.0) end / 100,2) 
						      else 0.0
						 end as ComSeguro,
						  IsNull(s_DetallePoliticaCom.MontoFijo,0.0) as MontoFijo, 
						  case when hpag_NumeroPagoSeguro= Null then '' else 'S' end as EsCoberturaPagada, segu_NocSeg, 'FA' as Origen, Null as pags_Numero,  s_DetallePoliticaCom.IdFormaCalculo
FROM            s_DetallePago INNER JOIN
                         s_HeaderPago ON s_DetallePago.dpag_IdCaja = s_HeaderPago.hpag_IdCaja AND s_DetallePago.dpag_Numero = s_HeaderPago.hpag_Numero INNER JOIN
                         s_Doctor ON s_HeaderPago.hpag_IdDoctor2 = s_Doctor.doct_IdDoctor INNER JOIN
                         s_ConceptoPago ON s_DetallePago.dpag_IdConcepto = s_ConceptoPago.conc_IdConcepto LEFT OUTER JOIN
                         s_Expediente ON s_HeaderPago.hpag_NumeroExpediente = s_Expediente.expe_NumeroExpediente INNER JOIN
                         s_DetallePoliticaCom ON s_Doctor.doct_NumPolCom = s_DetallePoliticaCom.NumPol AND s_DetallePago.dpag_IdConcepto = s_DetallePoliticaCom.IdConcepto
						 LEFT OUTER JOIN s_Seguro on s_HeaderPago.hpag_IdSeguro = s_Seguro.segu_IdSeguro
				         LEFT OUTER JOIN s_Carrier s_Carrier on s_HeaderPago.hpag_IdCarrier = s_Carrier.IdCarrier
	where Cast(hpag_Fecha as Date)>=@FecIni and Cast(hpag_Fecha as Date)<=@FecFin and hpag_Estatus='P' 
	   and (doct_IdDoctor in (Select * from dbo.fnSplitString(@IdDoctor,'-')) or rtrim(@IdDoctor)='')
	   and (hpag_IdSeguro in (Select * from dbo.fnSplitString(@IdSeguro,'-')) or rtrim(@IdSeguro)='')
	   and (hpag_IdCaja in (Select * from dbo.fnSplitString(@IdClase,'-')) or rtrim(@IdCaja)='')
	   and (conc_Tipo in (Select * from dbo.fnSplitString(@IdClase,'-')) or rtrim(@IdClase)='')
       and (isNull(conc_IdTipoSer,'') = @IdTipoSer or rtrim(@IdTipoSer)='')
	   and ( (@Categoria='P' and s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0) or
			   (@Categoria='S' and IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0) or
			   rtrim(@Categoria)='')
	    -- Si la forma de calculo es FAC y tiene cobertura o Si pago el paciente y la forma de calculo es diferente a SAL o no tiene cobertura
		and  ( (s_DetallePoliticaCom.IdFormaCalculo='FAC' and IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0)  or
		       ( 
			      (s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0))>0 and (s_DetallePoliticaCom.IdFormaCalculo<>'SAL' or IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)=0) ) 
			   )
union
-----------------------------------------------------------------------------------------------------------
--Buscar lo COBRADO:  
-----------------------------------------------------------------------------------------------------------
SELECT        s_Doctor.doct_Nombre, case when isNull(hpag_IdDoctor2, '') = '' then '1. COMO DOCTOR UNICO' else '2. COMO PRINCIPAL (' + Cast(100.00-IsNull(s_DetallePoliticaCom.PrComAsis,0.0) as varchar) + '%)' end AS PagoDoctor,
						 s_HeaderPago.hpag_IdCaja, s_HeaderPago.hpag_Numero, s_HeaderPago.hpag_Fecha, s_DetallePago.dpag_Secuencia,
						 case when IsNull(expe_NumeroExpediente,0)=0 then rtrim(Isnull(hpag_Nombre,'')) + ' ' + rtrim(IsNull(hpag_Apellido,'')) else rtrim(IsNull(s_Expediente.expe_Nombres,'')) + ' ' + rtrim(IsNull(s_Expediente.expe_Apellidos,'')) end as NombrePaciente, 
						 s_ConceptoPago.conc_Nombre, 
                         conc_Tipo, conc_IdTipoSer, s_HeaderPago.hpag_Procedencia, hpag_IdSeguro,
						 case when s_PagoSeguro.pags_FormaPago='D'  then  
						      case when s_PagoSeguro_Detalles.Saldo = 1 then s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)  else  s_PagoSeguro_Detalles.Monto end  
							      else 0.0 end as MontoEfectivo,
						 0.0 as MontoTarjeta,
						 case when s_PagoSeguro.pags_FormaPago='C'  then  
						      case when s_PagoSeguro_Detalles.Saldo = 1 then s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)  else  s_PagoSeguro_Detalles.Monto end  
							      else 0.0 end as MontoCheque,
						 s_DetallePago.dpag_Monto as Monto,
						 -- Si la forma de calculo es SALDO y se saldo la factura
						 case when s_DetallePoliticaCom.IdFormaCalculo='SAL' and s_PagoSeguro_Detalles.Saldo = 1 then
								   Round((s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) ) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Carrier.PorComision,0.0))/100,2)
							  else 0.0
						  end as MontoPrivado, 
						 -- Si la forma de calculo es SALDO y se saldo la factura
						 Round(case when s_DetallePoliticaCom.IdFormaCalculo='SAL' and s_PagoSeguro_Detalles.Saldo = 1  then IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) 
						       -- Si la forma de calculo PAGADO
							   when s_DetallePoliticaCom.IdFormaCalculo='COB' then	IsNull(s_PagoSeguro_Detalles.Monto,0.0) 
							   else 0.0 end * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Seguro.segu_PorRetencion,0.0))/100,2)  as MontoSeguro,
						 case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComExterno,0.0) end as PrComPrivado, 
					     case when hpag_Procedencia='E' then IsNull(s_DetallePoliticaCom.PrComSegInterno,0.0) else s_DetallePoliticaCom.PrComSegExterno end as PrComSeguro,
						 IsNull(s_Carrier.PorComision,0.0) as PrComTar, IsNull(s_Seguro.segu_PorRetencion,0.0) as PrComSeg, IsNull(s_DetallePoliticaCom.PrComCentro,0.0) as PrComCentro,
						 case when isNull(hpag_IdDoctor2, '') = '' then 100.00 else 100.00-IsNull(s_DetallePoliticaCom.PrComAsis,0.0)  end AS PrComGen,
						   -- Si la forma de calculo es SALDO y se saldo la factura
						 case when s_DetallePoliticaCom.IdFormaCalculo='SAL' and s_PagoSeguro_Detalles.Saldo = 1 then
						          Round((s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Carrier.PorComision,0.0))/100 *  case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComExterno,0.0) end / 100,2) 
						      else 0.0 end  as ComPrivado,
						 -- Si la forma de calculo es SALDO y se saldo la factura
						 case when s_DetallePoliticaCom.IdFormaCalculo='SAL' and s_PagoSeguro_Detalles.Saldo = 1  then
								  Round(IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Seguro.segu_PorRetencion,0.0))/100 * case when hpag_Procedencia='E' then IsNull(s_DetallePoliticaCom.PrComSegInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComSegExterno,0.0) end / 100,2)
							  -- Si la forma de calculo PAGADO
							   when s_DetallePoliticaCom.IdFormaCalculo='COB' then	
							      Round(IsNull(s_PagoSeguro_Detalles.Monto,0.0) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Seguro.segu_PorRetencion,0.0))/100 * case when hpag_Procedencia='E' then IsNull(s_DetallePoliticaCom.PrComSegInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComSegExterno,0.0) end / 100,2)
							  else 0.0 
							  end as ComSeguro,
							  IsNull(s_DetallePoliticaCom.MontoFijo,0.0) as MontoFijo, 
						 case when s_PagoSeguro_Detalles.Saldo = 0  then '' else 'S' end as EsCoberturaPagada, segu_NocSeg, 'C' as Origen, s_PagoSeguro.pags_Numero, s_DetallePoliticaCom.IdFormaCalculo
FROM  s_PagoSeguro_Detalles
	  INNER JOIN s_PagoSeguro on s_PagoSeguro_Detalles.pags_Numero= s_PagoSeguro.pags_Numero
	  INNER JOIN s_DetallePago on s_PagoSeguro_Detalles.hpag_IdCaja = s_DetallePago.dpag_IdCaja and s_PagoSeguro_Detalles.hpag_numero = s_DetallePago.dpag_numero
	  INNER JOIN s_HeaderPago ON s_DetallePago.dpag_IdCaja = s_HeaderPago.hpag_IdCaja AND s_DetallePago.dpag_Numero = s_HeaderPago.hpag_Numero INNER JOIN
                         s_Doctor ON s_HeaderPago.hpag_IdDoctor = s_Doctor.doct_IdDoctor INNER JOIN
                         s_ConceptoPago ON s_DetallePago.dpag_IdConcepto = s_ConceptoPago.conc_IdConcepto LEFT OUTER JOIN
                         s_Expediente ON s_HeaderPago.hpag_NumeroExpediente = s_Expediente.expe_NumeroExpediente INNER JOIN
                         s_DetallePoliticaCom ON s_Doctor.doct_NumPolCom = s_DetallePoliticaCom.NumPol AND s_DetallePago.dpag_IdConcepto = s_DetallePoliticaCom.IdConcepto 
						 LEFT OUTER JOIN s_Seguro on s_HeaderPago.hpag_IdSeguro = s_Seguro.segu_IdSeguro
						 LEFT OUTER JOIN s_Carrier s_Carrier on s_HeaderPago.hpag_IdCarrier = s_Carrier.IdCarrier
	where Cast(pags_Fecha as Date)>=@FecIni and Cast(pags_Fecha as Date)<=@FecFin and pags_Estatus='P' and (s_DetallePoliticaCom.IdFormaCalculo='SAL' OR s_DetallePoliticaCom.IdFormaCalculo='COB')
	     and (doct_IdDoctor in (Select * from dbo.fnSplitString(@IdDoctor,'-')) or rtrim(@IdDoctor)='')
	     and (hpag_IdSeguro in (Select * from dbo.fnSplitString(@IdSeguro,'-')) or rtrim(@IdSeguro)='')
		 and (s_HeaderPago.hpag_IdCaja in (Select * from dbo.fnSplitString(@IdCaja,'-')) or rtrim(@IdCaja)='')
		 and (conc_Tipo in (Select * from dbo.fnSplitString(@IdClase,'-')) or rtrim(@IdClase)='')
		 and (isNull(conc_IdTipoSer,'') = @IdTipoSer or rtrim(@IdTipoSer)='')
		 and ( (@Categoria='P' and s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0) or
			   (@Categoria='S' and IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0) or
			   rtrim(@Categoria)='')
		 and IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0 
		 and s_PagoSeguro_Detalles.Tipo='COB'       ----Solo tomar en cuenta los pagos de Cobertura,  no tomar en cuenta los descuentos
union
SELECT        s_Doctor.doct_Nombre,  '3. COMO ASISTENTE (' + Cast(IsNull(s_DetallePoliticaCom.PrComAsis,0.0) as varchar) + '%)' AS PagoDoctor,
						 s_HeaderPago.hpag_IdCaja, s_HeaderPago.hpag_Numero, s_HeaderPago.hpag_Fecha, s_DetallePago.dpag_Secuencia,
						 case when IsNull(expe_NumeroExpediente,0)=0 then rtrim(Isnull(hpag_Nombre,'')) + ' ' + rtrim(IsNull(hpag_Apellido,'')) else rtrim(IsNull(s_Expediente.expe_Nombres,'')) + ' ' + rtrim(IsNull(s_Expediente.expe_Apellidos,'')) end as NombrePaciente, 
						 s_ConceptoPago.conc_Nombre, 
                         conc_Tipo, conc_IdTipoSer, s_HeaderPago.hpag_Procedencia, hpag_IdSeguro, 
						 case when s_PagoSeguro.pags_FormaPago='D'  then  
						      case when s_PagoSeguro_Detalles.Saldo = 1 then s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)  else  s_PagoSeguro_Detalles.Monto end  
							      else 0.0 end as MontoEfectivo,
						 0.0 as MontoTarjeta,
						 case when s_PagoSeguro.pags_FormaPago='C'  then  
						      case when s_PagoSeguro_Detalles.Saldo = 1 then s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)  else  s_PagoSeguro_Detalles.Monto end  
							      else 0.0 end as MontoCheque,
						s_DetallePago.dpag_Monto as Monto,
						-- Si la forma de calculo es SALDO y se saldo la factura
						 case when s_DetallePoliticaCom.IdFormaCalculo='SAL' and s_PagoSeguro_Detalles.Saldo = 1 then
								   CAST(Round(s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Carrier.PorComision,0.0))/100,2) as decimal(12,2))
							  else 0.0
						  end as MontoPrivado, 
						 -- Si la forma de calculo es SALDO y se saldo la factura
						 CAST(Round(case when s_DetallePoliticaCom.IdFormaCalculo='SAL' and s_PagoSeguro_Detalles.Saldo = 1 then IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) 
						       -- Si la forma de calculo PAGADO
							   when s_DetallePoliticaCom.IdFormaCalculo='COB' then	IsNull(s_PagoSeguro_Detalles.Monto,0.0) 
							   else 0.0 end * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Seguro.segu_PorRetencion,0.0))/100,2) as decimal(12,2)) as MontoSeguro,
						 case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComExterno,0.0) end as PrComPrivado, 
					     case when hpag_Procedencia='E' then IsNull(s_DetallePoliticaCom.PrComSegInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComSegExterno,0.0) end as PrComSeguro,
						 IsNull(s_Carrier.PorComision,0.0) as PrComTar, IsNull(s_Seguro.segu_PorRetencion,0.0) as PrComSeg, IsNull(s_DetallePoliticaCom.PrComCentro,0.0) as PrComCentro,
						 IsNull(s_DetallePoliticaCom.PrComAsis,0.0) AS PrComGen,
						   -- Si la forma de calculo es SALDO y se saldo la factura
						 case when s_DetallePoliticaCom.IdFormaCalculo='SAL' and s_PagoSeguro_Detalles.Saldo = 1 then
						          Round((s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Carrier.PorComision,0.0))/100  *  case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComExterno,0.0) end / 100,2) 
						      else 0.0 end  as ComPrivado,
						 -- Si la forma de calculo es SALDO y se saldo la factura
						 case when s_DetallePoliticaCom.IdFormaCalculo='SAL' and s_PagoSeguro_Detalles.Saldo = 1  then
								  Round(IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Seguro.segu_PorRetencion,0.0))/100 * case when hpag_Procedencia='E' then IsNull(s_DetallePoliticaCom.PrComSegInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComSegExterno,0.0) end / 100,2)
							  -- Si la forma de calculo PAGADO
							   when s_DetallePoliticaCom.IdFormaCalculo='COB' then	
							      Round(IsNull(s_PagoSeguro_Detalles.Monto,0.0) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Seguro.segu_PorRetencion,0.0))/100 * case when hpag_Procedencia='E' then IsNull(s_DetallePoliticaCom.PrComSegInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComSegExterno,0.0) end / 100,2)
							  else 0.0 
							  end as ComSeguro,
							  IsNull(s_DetallePoliticaCom.MontoFijo,0.0) as MontoFijo, 
						  case when hpag_NumeroPagoSeguro= Null then '' else 'S' end as EsCoberturaPagada, segu_NocSeg, 'CA' as Origen, s_PagoSeguro.pags_Numero, s_DetallePoliticaCom.IdFormaCalculo
FROM   s_PagoSeguro_Detalles
	   INNER JOIN s_PagoSeguro on s_PagoSeguro_Detalles.pags_Numero= s_PagoSeguro.pags_Numero
       INNER JOIN s_DetallePago on s_PagoSeguro_Detalles.hpag_IdCaja = s_DetallePago.dpag_IdCaja and s_PagoSeguro_Detalles.hpag_numero = s_DetallePago.dpag_numero
       INNER JOIN s_HeaderPago ON s_DetallePago.dpag_IdCaja = s_HeaderPago.hpag_IdCaja AND s_DetallePago.dpag_Numero = s_HeaderPago.hpag_Numero INNER JOIN
                         s_Doctor ON s_HeaderPago.hpag_IdDoctor2 = s_Doctor.doct_IdDoctor INNER JOIN
                         s_ConceptoPago ON s_DetallePago.dpag_IdConcepto = s_ConceptoPago.conc_IdConcepto LEFT OUTER JOIN
                         s_Expediente ON s_HeaderPago.hpag_NumeroExpediente = s_Expediente.expe_NumeroExpediente INNER JOIN
                         s_DetallePoliticaCom ON s_Doctor.doct_NumPolCom = s_DetallePoliticaCom.NumPol AND s_DetallePago.dpag_IdConcepto = s_DetallePoliticaCom.IdConcepto
						 LEFT OUTER JOIN s_Seguro on s_HeaderPago.hpag_IdSeguro = s_Seguro.segu_IdSeguro
						 LEFT OUTER JOIN s_Carrier s_Carrier on s_HeaderPago.hpag_IdCarrier = s_Carrier.IdCarrier
	where Cast(pags_Fecha as Date)>=@FecIni and Cast(pags_Fecha as Date)<=@FecFin and pags_Estatus='P' and (s_DetallePoliticaCom.IdFormaCalculo='SAL' OR s_DetallePoliticaCom.IdFormaCalculo='COB')
	   and (doct_IdDoctor in (Select * from dbo.fnSplitString(@IdDoctor,'-')) or rtrim(@IdDoctor)='')
	   and (hpag_IdSeguro in (Select * from dbo.fnSplitString(@IdSeguro,'-')) or rtrim(@IdSeguro)='')
	   and (s_HeaderPago.hpag_IdCaja in (Select * from dbo.fnSplitString(@IdClase,'-')) or rtrim(@IdCaja)='')
	   and (conc_Tipo in (Select * from dbo.fnSplitString(@IdClase,'-')) or rtrim(@IdClase)='')
	   and (isNull(conc_IdTipoSer,'') = @IdTipoSer or rtrim(@IdTipoSer)='')
	   and ( (@Categoria='P' and s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0) or
			   (@Categoria='S' and IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0) or
			   rtrim(@Categoria)='')
       and IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0
       and s_PagoSeguro_Detalles.Tipo='COB'       ----Solo tomar en cuenta los pagos de Cobertura,  no tomar en cuenta los descuentos
Order by Doct_Nombre, PagoDoctor, conc_nombre, hpag_fecha, hpag_IdCaja, hpag_Numero
GO

-- Procedimiento: sp_ComisionReferidores
CREATE procedure [dbo].[sp_ComisionReferidores] (@FecIni as date, @FecFin as date, @IdReferidor as varchar(200), @IdSeguro as varchar(200), @IdCaja as varchar(200), @IdClase as varchar(200), @IdTipoSer as varchar(200),  @Categoria as char(1))
as 

-- Para prueba
--Declare @FecIni as date, @FecFin as date, @IdDoctor as varchar(200), @IdSeguro as varchar(200), @IdCaja as varchar(200), @IdClase as varchar(200), @IdTipoSer as varchar(200)
--Declare @Categoria as char(1)

--set @FecIni = '20180501'
--set @FecFin = '20180531'
--set @IdDoctor='ABLANCO-PFRANCO'
--set @IdSeguro ='000012-000017'
--set @IdCaja ='01'
--set @IdClase='E-P'
--set @IdTipoSer='1'
--set @Categoria='P'

--OJO:  Se adiciono al query 's_DetallePago.dpag_Secuencia', para que muestre todos los detalles, si hay dos detalles iguales solo mostraba uno ?????

--Buscar la FACTURADO:  Si la forma de Calculo es FAC, tomar ambos valores, Si es COB, tomar en cuenta solo el pago del paciente
SELECT        s_EntidadReferimiento.entr_nombre, s_HeaderPago.hpag_IdCaja, s_HeaderPago.hpag_Numero, s_HeaderPago.hpag_Fecha, s_DetallePago.dpag_Secuencia,
						 case when IsNull(expe_NumeroExpediente,0)=0 then rtrim(Isnull(hpag_Nombre,'')) + ' ' + rtrim(IsNull(hpag_Apellido,'')) else rtrim(IsNull(s_Expediente.expe_Nombres,'')) + ' ' + rtrim(IsNull(s_Expediente.expe_Apellidos,'')) end as NombrePaciente, 
						 s_ConceptoPago.conc_Nombre, 
                         conc_Tipo, conc_IdTipoSer, s_HeaderPago.hpag_Procedencia, hpag_IdSeguro,
						 case when round(hpag_monto-isNull(hpag_CoberturaSeguro,0.0),2)>0 then Round((dpag_Monto - IsNull(dpag_CoberturaSeguro,0.0)) *round( Round(hpag_monto-IsNull(hpag_CoberturaSeguro,0.0)-IsNull(hpag_ValorRecibidoCheque,0.0)-IsNull(hpag_ValorRecibidoTarjeta,0.0),2) / (hpag_monto -IsNull(hpag_CoberturaSeguro,0.0)),2),2) else Round(0,2) end as MontoEfectivo,
						 case when round(hpag_monto-isNull(hpag_CoberturaSeguro,0.0),2)>0 then Round((dpag_Monto - IsNull(dpag_CoberturaSeguro,0.0)) *round(IsNull(hpag_ValorRecibidoTarjeta,0.0) / (hpag_monto -IsNull(hpag_CoberturaSeguro,0.0)),2),2) else Round(0,2) end as MontoTarjeta,
						 case when round(hpag_monto-isNull(hpag_CoberturaSeguro,0.0),2)>0 then Round((dpag_Monto - IsNull(dpag_CoberturaSeguro,0.0)) *round(IsNull(hpag_ValorRecibidoCheque,0.0) / (hpag_monto -IsNull(hpag_CoberturaSeguro,0.0)),2),2) else Round(0,2) end as MontoCheque,
						 s_DetallePago.dpag_Monto as Monto,
						  
						 -- Si la forma de calculo es Facturado o Cobrado. O es solo privado (Saldo)
						 case when s_DetallePoliticaCom.IdFormaCalculo='FAC' or s_DetallePoliticaCom.IdFormaCalculo='COB' or IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)=0 then
					          Cast(Round((s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Carrier.PorComision,0.0))/100,2) as decimal(12,2))
					     else 0.0 
						 end as MontoPrivado, 

						 -- Si la forma de calculo es Facturado
						 case when s_DetallePoliticaCom.IdFormaCalculo='FAC' then
									Cast(Round(IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Seguro.segu_PorRetencion,0.0))/100,2) as decimal(12,2))
							  else 0.0 
					     end  as MontoSeguro,

						 case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComExterno,0.0) end as PrComPrivado, 
					     case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComSegInterno,0.0) else isNull(s_DetallePoliticaCom.PrComSegExterno,0.0) end as PrComSeguro,
						 IsNull(s_Carrier.PorComision,0.0) as PrComTar, IsNull(s_Seguro.segu_PorRetencion,0.0) as PrComSeg, IsNull(s_DetallePoliticaCom.PrComCentro,0.0) as PrComCentro,
						 100.00 AS PrComGen,
						
						-- Si la forma de calculo es Facturado o Cobrado. O es solo privado (Saldo)
						 case when s_DetallePoliticaCom.IdFormaCalculo='FAC' or s_DetallePoliticaCom.IdFormaCalculo='COB' or IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)=0 then
							Cast(Round((s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Carrier.PorComision,0.0))/100 *  case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComExterno,0.0) end / 100,2) as decimal(12,2))
							else 0.0
						end as ComPrivado,
						
						  -- Si la forma de calculo es Facturado
						 case when s_DetallePoliticaCom.IdFormaCalculo='FAC' then
						           Cast(Round(IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Seguro.segu_PorRetencion,0.0))/100 * case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComSegInterno,0.0) else Isnull(s_DetallePoliticaCom.PrComSegExterno,0.0) end / 100,2) as decimal(12,2))
						      else 0.0
						 end as ComSeguro,
						--2021/15/15 No Pagar mas de lo cobrado
						 case when s_DetallePoliticaCom.IdFormaCalculo='FAC' or s_DetallePoliticaCom.IdFormaCalculo='COB' or IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)=0 then
									case when s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)<IsNull(s_DetallePoliticaCom.MontoFijo,0.0) then s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) else IsNull(s_DetallePoliticaCom.MontoFijo,0.0) end
						      when s_DetallePoliticaCom.IdFormaCalculo='FAC' then
									case when IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)<IsNull(s_DetallePoliticaCom.MontoFijo,0.0) then IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) else IsNull(s_DetallePoliticaCom.MontoFijo,0.0) end
							end as MontoFijo,
						 case when hpag_NumeroPagoSeguro= Null then '' else 'S' end as EsCoberturaPagada, segu_NocSeg, 'F' as Origen, Null as pags_Numero, s_DetallePoliticaCom.IdFormaCalculo
FROM            s_DetallePago 
                   INNER JOIN s_HeaderPago ON s_DetallePago.dpag_IdCaja = s_HeaderPago.hpag_IdCaja AND s_DetallePago.dpag_Numero = s_HeaderPago.hpag_Numero 
				   INNER JOIN s_EntidadReferimiento ON s_HeaderPago.hpag_IdEntidadRef = s_EntidadReferimiento.entr_IdEntidad 
				   INNER JOIN s_ConceptoPago ON s_DetallePago.dpag_IdConcepto = s_ConceptoPago.conc_IdConcepto 
				   LEFT OUTER JOIN s_Expediente ON s_HeaderPago.hpag_NumeroExpediente = s_Expediente.expe_NumeroExpediente 
				   INNER JOIN s_DetallePoliticaCom ON s_EntidadReferimiento.entr_NumPolCom = s_DetallePoliticaCom.NumPol AND s_DetallePago.dpag_IdConcepto = s_DetallePoliticaCom.IdConcepto
				   LEFT OUTER JOIN s_Seguro on s_HeaderPago.hpag_IdSeguro = s_Seguro.segu_IdSeguro
				   LEFT OUTER JOIN s_Carrier s_Carrier on s_HeaderPago.hpag_IdCarrier = s_Carrier.IdCarrier
	where Cast(hpag_Fecha as Date)>=@FecIni and Cast(hpag_Fecha as Date)<=@FecFin and hpag_Estatus='P' 
	     and (entr_IdEntidad in (Select * from dbo.fnSplitString(@IdReferidor,'-')) or rtrim(@IdReferidor)='')
	     and (hpag_IdSeguro in (Select * from dbo.fnSplitString(@IdSeguro,'-')) or rtrim(@IdSeguro)='')
		 and (hpag_IdCaja in (Select * from dbo.fnSplitString(@IdCaja,'-')) or rtrim(@IdCaja)='')
		 and (conc_Tipo in (Select * from dbo.fnSplitString(@IdClase,'-')) or rtrim(@IdClase)='')
		 and (IsNull(conc_IdTipoSer,'')=@IdTipoSer or rtrim(@IdTipoSer)='')
		 and ( (@Categoria='P' and s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0) or
			   (@Categoria='S' and IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0) or
			   rtrim(@Categoria)='')
		and IsNull(s_DetallePago.dpag_Monto,0.0)>0
		-- Incluir si la forma es FAC o si es COB y tiene monto cobrado Al paciente o si es SAL y no tiene cobertura
		and (s_DetallePoliticaCom.IdFormaCalculo='FAC' 
		     or (s_DetallePoliticaCom.IdFormaCalculo='COB' and (s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0))>0)
			 or (s_DetallePoliticaCom.IdFormaCalculo='SAL' and (IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0))=0) )		
union 
--Buscar lo COBRADO: REcibos con cobertura
SELECT        s_EntidadReferimiento.entr_nombre, s_HeaderPago.hpag_IdCaja, s_HeaderPago.hpag_Numero, s_HeaderPago.hpag_Fecha, s_DetallePago.dpag_Secuencia,
						 case when IsNull(expe_NumeroExpediente,0)=0 then rtrim(Isnull(hpag_Nombre,'')) + ' ' + rtrim(IsNull(hpag_Apellido,'')) else rtrim(IsNull(s_Expediente.expe_Nombres,'')) + ' ' + rtrim(IsNull(s_Expediente.expe_Apellidos,'')) end as NombrePaciente,
						 s_ConceptoPago.conc_Nombre, 
                         conc_Tipo, conc_IdTipoSer, s_HeaderPago.hpag_Procedencia, hpag_IdSeguro,
						  case when s_PagoSeguro.pags_FormaPago='D'  then  
						      case when s_PagoSeguro_Detalles.Saldo = 1 then s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)  else  s_PagoSeguro_Detalles.Monto end  
							      else 0.0 end as MontoEfectivo,
						 0.0 as MontoTarjeta,
						 case when s_PagoSeguro.pags_FormaPago='C'  then  
						      case when s_PagoSeguro_Detalles.Saldo = 1 then s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)  else  s_PagoSeguro_Detalles.Monto end  
							      else 0.0 end as MontoCheque,
						 s_DetallePago.dpag_Monto as Monto,
					      
						 -- Si la forma de calculo es SALDO y se saldo la factura
						 case when s_DetallePoliticaCom.IdFormaCalculo='SAL' and s_PagoSeguro_Detalles.Saldo = 1 then
								   cast(Round((s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) ) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Carrier.PorComision,0.0))/100,2) as decimal(12,2))
							  else 0.0
						  end as MontoPrivado, 

						 -- Si la forma de calculo es SALDO y se saldo la factura o e COBRO
						 case when (s_DetallePoliticaCom.IdFormaCalculo='SAL' and s_PagoSeguro_Detalles.Saldo = 1) or s_DetallePoliticaCom.IdFormaCalculo='COB'  
						            then Cast(Round(IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Seguro.segu_PorRetencion,0.0))/100,2) as decimal(12,2))
							         else 0.0 
					     end as MontoSeguro,

						 case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComExterno,0.0) end as PrComPrivado, 
					     case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComSegInterno,0.0) else s_DetallePoliticaCom.PrComSegExterno end as PrComSeguro,
						 IsNull(s_Carrier.PorComision,0.0) as PrComTar, IsNull(s_Seguro.segu_PorRetencion,0.0) as PrComSeg, IsNull(s_DetallePoliticaCom.PrComCentro,0.0) as PrComCentro,
						 100.00 AS PrComGen,
						  
						   -- Si la forma de calculo es SALDO y se saldo la factura
						 case when s_DetallePoliticaCom.IdFormaCalculo='SAL' and s_PagoSeguro_Detalles.Saldo = 1 then
						          Cast(Round((s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Carrier.PorComision,0.0))/100 *  case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComExterno,0.0) end / 100,2) as decimal(12,2))
						      else 0.0 end  as ComPrivado,

						 -- Si la forma de calculo es SALDO y se saldo la factura o es cobro
						 case when (s_DetallePoliticaCom.IdFormaCalculo='SAL' and s_PagoSeguro_Detalles.Saldo = 1) or s_DetallePoliticaCom.IdFormaCalculo='COB'   then
								  Cast(Round(IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Seguro.segu_PorRetencion,0.0))/100 * case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComSegInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComSegExterno,0.0) end / 100,2) as decimal(12,2))
							 else 0.0 
						 end as ComSeguro,

						   --2021/15/15 No Pagar mas de lo cobrado
						 case when s_DetallePoliticaCom.IdFormaCalculo='SAL' and s_PagoSeguro_Detalles.Saldo = 1 then
									case when s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)<IsNull(s_DetallePoliticaCom.MontoFijo,0.0) then s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) else IsNull(s_DetallePoliticaCom.MontoFijo,0.0) end
						      when (s_DetallePoliticaCom.IdFormaCalculo='SAL' and s_PagoSeguro_Detalles.Saldo = 1) or s_DetallePoliticaCom.IdFormaCalculo='COB'   then
									case when IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)<IsNull(s_DetallePoliticaCom.MontoFijo,0.0) then IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) else IsNull(s_DetallePoliticaCom.MontoFijo,0.0) end
							end as MontoFijo,
						 case when hpag_NumeroPagoSeguro= Null then '' else 's' end as EsCoberturaPagada, segu_NocSeg,  'C' as Origen, s_PagoSeguro.pags_Numero, s_DetallePoliticaCom.IdFormaCalculo
FROM   s_PagoSeguro_Detalles
	  INNER JOIN s_PagoSeguro on s_PagoSeguro_Detalles.pags_Numero= s_PagoSeguro.pags_Numero
	  INNER JOIN s_DetallePago on s_PagoSeguro_Detalles.hpag_IdCaja = s_DetallePago.dpag_IdCaja and s_PagoSeguro_Detalles.hpag_numero = s_DetallePago.dpag_numero 
                   INNER JOIN s_HeaderPago ON s_DetallePago.dpag_IdCaja = s_HeaderPago.hpag_IdCaja AND s_DetallePago.dpag_Numero = s_HeaderPago.hpag_Numero 
				   INNER JOIN s_EntidadReferimiento ON s_HeaderPago.hpag_IdEntidadRef = s_EntidadReferimiento.entr_IdEntidad 
				   INNER JOIN s_ConceptoPago ON s_DetallePago.dpag_IdConcepto = s_ConceptoPago.conc_IdConcepto 
				   LEFT OUTER JOIN s_Expediente ON s_HeaderPago.hpag_NumeroExpediente = s_Expediente.expe_NumeroExpediente 
				   INNER JOIN s_DetallePoliticaCom ON s_EntidadReferimiento.entr_NumPolCom = s_DetallePoliticaCom.NumPol AND s_DetallePago.dpag_IdConcepto = s_DetallePoliticaCom.IdConcepto 
				   LEFT OUTER JOIN s_Seguro on s_HeaderPago.hpag_IdSeguro = s_Seguro.segu_IdSeguro
				   LEFT OUTER JOIN s_Carrier s_Carrier on s_HeaderPago.hpag_IdCarrier = s_Carrier.IdCarrier
	where Cast(pags_Fecha as Date)>=@FecIni and Cast(pags_Fecha as Date)<=@FecFin and pags_Estatus='P' 
	     and (entr_IdEntidad in (Select * from dbo.fnSplitString(@IdReferidor,'-')) or rtrim(@IdReferidor)='')
	     and (hpag_IdSeguro in (Select * from dbo.fnSplitString(@IdSeguro,'-')) or rtrim(@IdSeguro)='')
		 and (s_HeaderPago.hpag_IdCaja in (Select * from dbo.fnSplitString(@IdCaja,'-')) or rtrim(@IdCaja)='')
		 and (conc_Tipo in (Select * from dbo.fnSplitString(@IdClase,'-')) or rtrim(@IdClase)='')
		 and (isNull(conc_IdTipoSer,'') = @IdTipoSer or rtrim(@IdTipoSer)='')
		 and ( (@Categoria='P' and s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0) or
			   (@Categoria='S' and IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0) or
			   rtrim(@Categoria)='')
 		 and IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0 
	     and s_PagoSeguro_Detalles.Tipo='COB'       ----Solo tomar en cuenta los pagos de Cobertura,  no tomar en cuenta los descuentos
		 -- Si es saldo, tomar los recibos que saldaron en el período
		 and ((s_DetallePoliticaCom.IdFormaCalculo='SAL' and Cast(hpag_FechaSaldo as Date)>=@FecIni and Cast(hpag_FechaSaldo as Date)<=@FecFin) OR s_DetallePoliticaCom.IdFormaCalculo='COB')
Order by entr_nombre, conc_nombre, hpag_fecha, hpag_IdCaja, hpag_Numero
GO

-- Procedimiento: sp_ComisionReferidoresOld
CREATE procedure [dbo].[sp_ComisionReferidoresOld] (@FecIni as date, @FecFin as date, @IdReferidor as varchar(200), @IdSeguro as varchar(200), @IdCaja as varchar(200), @IdClase as varchar(200), @IdTipoSer as varchar(200),  @Categoria as char(1))
as 

-- Para prueba
--Declare @FecIni as date, @FecFin as date, @IdDoctor as varchar(200), @IdSeguro as varchar(200), @IdCaja as varchar(200), @IdClase as varchar(200), @IdTipoSer as varchar(200)
--Declare @Categoria as char(1)

--set @FecIni = '20180501'
--set @FecFin = '20180531'
--set @IdDoctor='ABLANCO-PFRANCO'
--set @IdSeguro ='000012-000017'
--set @IdCaja ='01'
--set @IdClase='E-P'
--set @IdTipoSer='1'
--set @Categoria='P'

--OJO:  Se adiciono al query 's_DetallePago.dpag_Secuencia', para que muestre todos los detalles, si hay dos detalles iguales solo mostraba uno ?????

--Buscar la FACTURADO:  Si la forma de Calculo es FAC, tomar ambos valores, Si es COB, tomar en cuenta solo el pago del paciente
SELECT        s_EntidadReferimiento.entr_nombre, s_HeaderPago.hpag_IdCaja, s_HeaderPago.hpag_Numero, s_HeaderPago.hpag_Fecha, s_DetallePago.dpag_Secuencia,
						 case when IsNull(expe_NumeroExpediente,0)=0 then rtrim(Isnull(hpag_Nombre,'')) + ' ' + rtrim(IsNull(hpag_Apellido,'')) else rtrim(IsNull(s_Expediente.expe_Nombres,'')) + ' ' + rtrim(IsNull(s_Expediente.expe_Apellidos,'')) end as NombrePaciente, 
						 s_ConceptoPago.conc_Nombre, 
                         conc_Tipo, conc_IdTipoSer, s_HeaderPago.hpag_Procedencia, hpag_IdSeguro,
						 case when round(hpag_monto-isNull(hpag_CoberturaSeguro,0.0),2)>0 then Round((dpag_Monto - IsNull(dpag_CoberturaSeguro,0.0)) *round( Round(hpag_monto-IsNull(hpag_CoberturaSeguro,0.0)-IsNull(hpag_ValorRecibidoCheque,0.0)-IsNull(hpag_ValorRecibidoTarjeta,0.0),2) / (hpag_monto -IsNull(hpag_CoberturaSeguro,0.0)),2),2) else Round(0,2) end as MontoEfectivo,
						 case when round(hpag_monto-isNull(hpag_CoberturaSeguro,0.0),2)>0 then Round((dpag_Monto - IsNull(dpag_CoberturaSeguro,0.0)) *round(IsNull(hpag_ValorRecibidoTarjeta,0.0) / (hpag_monto -IsNull(hpag_CoberturaSeguro,0.0)),2),2) else Round(0,2) end as MontoTarjeta,
						 case when round(hpag_monto-isNull(hpag_CoberturaSeguro,0.0),2)>0 then Round((dpag_Monto - IsNull(dpag_CoberturaSeguro,0.0)) *round(IsNull(hpag_ValorRecibidoCheque,0.0) / (hpag_monto -IsNull(hpag_CoberturaSeguro,0.0)),2),2) else Round(0,2) end as MontoCheque,
						 s_DetallePago.dpag_Monto as Monto,
						  -- Si la forma de calculo es Facturado o Pagado. O es solo privado
						 case when s_DetallePoliticaCom.IdFormaCalculo='FAC' or s_DetallePoliticaCom.IdFormaCalculo='COB' or IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)=0 then
								   Round(s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Carrier.PorComision,0.0))/100,2)
							  else 0.0 end 
							  as MontoPrivado, 
						 -- Si la forma de calculo es Facturado
						 case when s_DetallePoliticaCom.IdFormaCalculo='FAC' then
									Round(IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Seguro.segu_PorRetencion,0.0))/100,2) 
							  else 0.0 
							  end  as MontoSeguro, 
					     case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComExterno,0.0) end as PrComPrivado, 
					     case when hpag_Procedencia='E' then IsNull(s_DetallePoliticaCom.PrComSegInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComSegExterno,0.0) end as PrComSeguro,
						 IsNull(s_Carrier.PorComision,0.0) as PrComTar, IsNull(s_Seguro.segu_PorRetencion,0.0) as PrComSeg, IsNull(s_DetallePoliticaCom.PrComCentro,0.0) as PrComCentro,
						 100.00 AS PrComGen,
						   -- Si la forma de calculo es Facturado o Cobrado  O es solo privado
						 case when s_DetallePoliticaCom.IdFormaCalculo='FAC' or s_DetallePoliticaCom.IdFormaCalculo='COB' or IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)=0 then
									Round((s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Carrier.PorComision,0.0))/100 *  case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComExterno,0.0) end / 100,2) 
							   else 0.0
							   end as ComPrivado,
						  -- Si la forma de calculo es Facturado
						 case when s_DetallePoliticaCom.IdFormaCalculo='FAC' then
						            Round(IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Seguro.segu_PorRetencion,0.0))/100 * case when hpag_Procedencia='E' then IsNull(s_DetallePoliticaCom.PrComSegInterno,0.0) else Isnull(s_DetallePoliticaCom.PrComSegExterno,0.0) end / 100,2) 
						      else 0.0
						 end as ComSeguro,
						 case when hpag_NumeroPagoSeguro= Null then '' else 'S' end as EsCoberturaPagada, segu_NocSeg
FROM            s_DetallePago 
                   INNER JOIN s_HeaderPago ON s_DetallePago.dpag_IdCaja = s_HeaderPago.hpag_IdCaja AND s_DetallePago.dpag_Numero = s_HeaderPago.hpag_Numero 
				   INNER JOIN s_EntidadReferimiento ON s_HeaderPago.hpag_IdEntidadRef = s_EntidadReferimiento.entr_IdEntidad 
				   INNER JOIN s_ConceptoPago ON s_DetallePago.dpag_IdConcepto = s_ConceptoPago.conc_IdConcepto 
				   LEFT OUTER JOIN s_Expediente ON s_HeaderPago.hpag_NumeroExpediente = s_Expediente.expe_NumeroExpediente 
				   INNER JOIN s_DetallePoliticaCom ON s_EntidadReferimiento.entr_NumPolCom = s_DetallePoliticaCom.NumPol AND s_DetallePago.dpag_IdConcepto = s_DetallePoliticaCom.IdConcepto
				   LEFT OUTER JOIN s_Seguro on s_HeaderPago.hpag_IdSeguro = s_Seguro.segu_IdSeguro
				   LEFT OUTER JOIN s_Carrier s_Carrier on s_HeaderPago.hpag_IdCarrier = s_Carrier.IdCarrier
	where Cast(hpag_Fecha as Date)>=@FecIni and Cast(hpag_Fecha as Date)<=@FecFin and hpag_Estatus='P' 
	     and (entr_IdEntidad in (Select * from dbo.fnSplitString(@IdReferidor,'-')) or rtrim(@IdReferidor)='')
	     and (hpag_IdSeguro in (Select * from dbo.fnSplitString(@IdSeguro,'-')) or rtrim(@IdSeguro)='')
		 and (hpag_IdCaja in (Select * from dbo.fnSplitString(@IdCaja,'-')) or rtrim(@IdCaja)='')
		 and (conc_Tipo in (Select * from dbo.fnSplitString(@IdClase,'-')) or rtrim(@IdClase)='')
		 and (IsNull(conc_IdTipoSer,'')=@IdTipoSer or rtrim(@IdTipoSer)='')
		 and ( (@Categoria='P' and s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0) or
			   (@Categoria='S' and IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0) or
			   rtrim(@Categoria)='')
		-- Si la forma de calculo es FAC y tiene cobertura o Si pago el paciente y la forma de calculo es diferente a SAL o no tiene cobertura
		and  ( (s_DetallePoliticaCom.IdFormaCalculo='FAC' and IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0)  or
		       ( 
			      (s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0))>0 and (s_DetallePoliticaCom.IdFormaCalculo<>'SAL' or IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)=0) ) 
			   )
union 
--Buscar lo COBRADO: REcibos con cobertura
SELECT        s_EntidadReferimiento.entr_nombre, s_HeaderPago.hpag_IdCaja, s_HeaderPago.hpag_Numero, s_HeaderPago.hpag_Fecha, s_DetallePago.dpag_Secuencia,
						 case when IsNull(expe_NumeroExpediente,0)=0 then rtrim(Isnull(hpag_Nombre,'')) + ' ' + rtrim(IsNull(hpag_Apellido,'')) else rtrim(IsNull(s_Expediente.expe_Nombres,'')) + ' ' + rtrim(IsNull(s_Expediente.expe_Apellidos,'')) end as NombrePaciente,
						 s_ConceptoPago.conc_Nombre, 
                         conc_Tipo, conc_IdTipoSer, s_HeaderPago.hpag_Procedencia, hpag_IdSeguro,
						  case when s_PagoSeguro.pags_FormaPago='D'  then  
						      case when s_PagoSeguro_Detalles.Saldo = 1 then s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)  else  s_PagoSeguro_Detalles.Monto end  
							      else 0.0 end as MontoEfectivo,
						 0.0 as MontoTarjeta,
						 case when s_PagoSeguro.pags_FormaPago='C'  then  
						      case when s_PagoSeguro_Detalles.Saldo = 1 then s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)  else  s_PagoSeguro_Detalles.Monto end  
							      else 0.0 end as MontoCheque,
						 s_DetallePago.dpag_Monto as Monto,
					      case when s_DetallePoliticaCom.IdFormaCalculo='SAL' and s_PagoSeguro_Detalles.Saldo = 1 then
								   Round((s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) ) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Carrier.PorComision,0.0))/100,2)
							  else 0.0
						  end as MontoPrivado, 
						 -- Si la forma de calculo es SALDO y se saldo la factura
						  Round(case when s_DetallePoliticaCom.IdFormaCalculo='SAL' and s_PagoSeguro_Detalles.Saldo = 1  then IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) 
						       -- Si la forma de calculo PAGADO
							   when s_DetallePoliticaCom.IdFormaCalculo='COB' then	IsNull(s_PagoSeguro_Detalles.Monto,0.0) 
							   else 0.0 end * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Seguro.segu_PorRetencion,0.0))/100,2)  as MontoSeguro,
						 case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComExterno,0.0) end as PrComPrivado, 
					     case when hpag_Procedencia='E' then IsNull(s_DetallePoliticaCom.PrComSegInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComSegExterno,0.0) end as PrComSeguro,
						 IsNull(s_Carrier.PorComision,0.0) as PrComTar, IsNull(s_Seguro.segu_PorRetencion,0.0) as PrComSeg, IsNull(s_DetallePoliticaCom.PrComCentro,0.0) as PrComCentro,
						  100.00 AS PrComGen,
						    -- Si la forma de calculo es SALDO y se saldo la factura
						 case when s_DetallePoliticaCom.IdFormaCalculo='SAL' and s_PagoSeguro_Detalles.Saldo = 1 then
						          Round((s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Carrier.PorComision,0.0))/100 *  case when hpag_Procedencia='I' then IsNull(s_DetallePoliticaCom.PrComInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComExterno,0.0) end / 100,2) 
						      else 0.0 end  as ComPrivado,
						 -- Si la forma de calculo es SALDO y se saldo la factura
						case when s_DetallePoliticaCom.IdFormaCalculo='SAL' and s_PagoSeguro_Detalles.Saldo = 1  then
								  Round(IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Seguro.segu_PorRetencion,0.0))/100 * case when hpag_Procedencia='E' then IsNull(s_DetallePoliticaCom.PrComSegInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComSegExterno,0.0) end / 100,2)
							  -- Si la forma de calculo PAGADO
							   when s_DetallePoliticaCom.IdFormaCalculo='COB' then	
							      Round(IsNull(s_PagoSeguro_Detalles.Monto,0.0) * (100-IsNull(s_DetallePoliticaCom.PrComCentro,0.0))/100 * (100-IsNull(s_Seguro.segu_PorRetencion,0.0))/100 * case when hpag_Procedencia='E' then IsNull(s_DetallePoliticaCom.PrComSegInterno,0.0) else IsNull(s_DetallePoliticaCom.PrComSegExterno,0.0) end / 100,2)
							  else 0.0 
							  end as ComSeguro,
						 case when hpag_NumeroPagoSeguro= Null then '' else 's' end as EsCoberturaPagada, segu_NocSeg
FROM   s_PagoSeguro_Detalles
	  INNER JOIN s_PagoSeguro on s_PagoSeguro_Detalles.pags_Numero= s_PagoSeguro.pags_Numero
	  INNER JOIN s_DetallePago on s_PagoSeguro_Detalles.hpag_IdCaja = s_DetallePago.dpag_IdCaja and s_PagoSeguro_Detalles.hpag_numero = s_DetallePago.dpag_numero 
                   INNER JOIN s_HeaderPago ON s_DetallePago.dpag_IdCaja = s_HeaderPago.hpag_IdCaja AND s_DetallePago.dpag_Numero = s_HeaderPago.hpag_Numero 
				   INNER JOIN s_EntidadReferimiento ON s_HeaderPago.hpag_IdEntidadRef = s_EntidadReferimiento.entr_IdEntidad 
				   INNER JOIN s_ConceptoPago ON s_DetallePago.dpag_IdConcepto = s_ConceptoPago.conc_IdConcepto 
				   LEFT OUTER JOIN s_Expediente ON s_HeaderPago.hpag_NumeroExpediente = s_Expediente.expe_NumeroExpediente 
				   INNER JOIN s_DetallePoliticaCom ON s_EntidadReferimiento.entr_NumPolCom = s_DetallePoliticaCom.NumPol AND s_DetallePago.dpag_IdConcepto = s_DetallePoliticaCom.IdConcepto 
				   LEFT OUTER JOIN s_Seguro on s_HeaderPago.hpag_IdSeguro = s_Seguro.segu_IdSeguro
				   LEFT OUTER JOIN s_Carrier s_Carrier on s_HeaderPago.hpag_IdCarrier = s_Carrier.IdCarrier
	where Cast(hpag_Fecha as Date)>=@FecIni and Cast(hpag_Fecha as Date)<=@FecFin and pags_Estatus='P' and (s_DetallePoliticaCom.IdFormaCalculo='SAL' OR s_DetallePoliticaCom.IdFormaCalculo='COB')
	     and (entr_IdEntidad in (Select * from dbo.fnSplitString(@IdReferidor,'-')) or rtrim(@IdReferidor)='')
	     and (hpag_IdSeguro in (Select * from dbo.fnSplitString(@IdSeguro,'-')) or rtrim(@IdSeguro)='')
		 and (s_HeaderPago.hpag_IdCaja in (Select * from dbo.fnSplitString(@IdCaja,'-')) or rtrim(@IdCaja)='')
		 and (conc_Tipo in (Select * from dbo.fnSplitString(@IdClase,'-')) or rtrim(@IdClase)='')
		 and (isNull(conc_IdTipoSer,'') = @IdTipoSer or rtrim(@IdTipoSer)='')
		 and ( (@Categoria='P' and s_DetallePago.dpag_Monto - IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0) or
			   (@Categoria='S' and IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0) or
			   rtrim(@Categoria)='')
 		 and IsNull(s_DetallePago.dpag_CoberturaSeguro,0.0)>0 
	     and s_PagoSeguro_Detalles.Tipo='COB'       ----Solo tomar en cuenta los pagos de Cobertura,  no tomar en cuenta los descuentos
Order by entr_nombre, conc_nombre, hpag_fecha, hpag_IdCaja, hpag_Numero
GO

-- Procedimiento: sp_ConfiguraNuevoDoctor
CREATE procedure [dbo].[sp_ConfiguraNuevoDoctor](@IdDoctor varchar(15), @IdUsuario varchar(15)) as


-- Procedimiento que configura el nuevo doctor.
---Crea el usuario a utilizar el doctor
--- adiciona el nuevo doctor a los usuarios existentes.

Declare @IdEspecialidad char(3), @IdPerfilDoctor varchar(10), @ExisteUsuario int, @NomUsuario varchar(50)
Select @IdEspecialidad = conf_IdEspecialidad, @IdPerfilDoctor = conf_IdPerfilDoctor from s_Configuracion

Select @NomUsuario=doct_nombre from s_Doctor where doct_IdDoctor=@IdDoctor

--Adicionar el doctor a todos los usuarios que tienen acceso a registrar lista de pacientes
Insert into s_UsuarioDoctores(usud_IdUsuario, usud_IdDoctor, usud_TipoAcceso, usud_Usarpordefecto)
   SELECT s_Usuario.usua_IdUsuario, @IdDoctor, 'D', 0
       FROM s_Usuario 
       INNER JOIN s_PerfilUsuario ON s_Usuario.usua_IdPerfil = s_PerfilUsuario.pusr_IdPerfil
       WHERE     (s_PerfilUsuario.pusr_AccRLPa = 1) and s_Usuario.usua_IdUsuario<>@IdUsuario
 
--Crear el nuevo usuario
Select @ExisteUsuario=count(*) from s_Usuario where usua_IdUsuario=@IdUsuario
IF @ExisteUsuario=0
begin
	Insert into s_Usuario(usua_IdUsuario, usua_Nombre, usua_IdPerfil, usua_ClaveAcceso, usua_Estatus, usua_IdDoctorDef) 
	    Values(@IdUsuario, @NomUsuario, @IdPerfilDoctor,'1','A', @IdDoctor);
	
	-- Adicionar al usuario el doctor y colocarlo por defecto
	Insert into s_usuarioDoctores(usud_IdUsuario, usud_IdDoctor, usud_TipoAcceso, usud_Usarpordefecto) 
	   values(@IdUsuario, @IdDoctor, 'D', 1 )
	    
	-- Adicionar al usuario el doctor a usar por defecto.
	Insert into s_usuarioDoctores(usud_IdUsuario, usud_IdDoctor, usud_TipoAcceso, usud_Usarpordefecto) 
	   Select @IdUsuario, doct_IdDoctor, 'D', 0 
	   from s_Doctor where doct_UsarPorDefecto=1 and doct_IdDoctor not in (Select usud_IdDoctor from s_UsuarioDoctores where usud_IdUsuario=@IdUsuario);
	   
	-- Adicionar las listas especiales a manejar
	Insert into s_UsuarioListasEspeciales(usul_IdListaEspecial,usul_IdUsuario,usul_TipoAcceso, usul_Usarpordefecto)
	Select lise_idLista, @IdUsuario, 'D',0 from s_ListaEspecial where lise_estatus='A'
end
GO

-- Procedimiento: sp_ConfiguraNuevoUsuario
CREATE procedure [dbo].[sp_ConfiguraNuevoUsuario](@IdUsuario varchar(15), @IdDoctor varchar(15)) as

-- Procedimiento que configura el nuevo usuario.
---Crea el Doctor especificado en el usuario


Declare @IdEspecialidad char(3), @IdPerfilDoctor varchar(10), @ExisteDoctor int, @NomUsuario varchar(50)
Declare @AccRLPa as integer

Select @IdEspecialidad = conf_IdEspecialidad, @IdPerfilDoctor = conf_IdPerfilDoctor from s_Configuracion

-- Conseguir nombre del usuario y si tiene acceso a la lista de pacientes
Select @NomUsuario=usua_nombre, @AccRLPa = pusr_AccRLpa
from s_Usuario 
inner join s_Perfilusuario on s_usuario.usua_IdPerfil= s_PerfilUsuario.pusr_IdPerfil where usua_Idusuario=@IdUsuario

if @AccRLPa=1 
begin
	-- Adicionar todos los doctores activos al usuario
	Insert into s_UsuarioDoctores(usud_IdUsuario, usud_IdDoctor, usud_TipoAcceso, usud_Usarpordefecto)
	Select @IdUsuario, doct_IdDoctor,'D',0 from s_Doctor where doct_Estatus='A'
end

--Crear el nuevo doctor
if @IdDoctor<>''
begin
	Select @ExisteDoctor=count(*) from s_Doctor where doct_IdDoctor=@IdDoctor
	IF @ExisteDoctor=0
	begin
		Insert into s_Doctor(doct_IdDoctor,doct_Nombre,doct_Iniciales, doct_Estatus,doct_PorcientoARecibir,doct_UtilizarSoloCajaDefecto, doct_ManListaEspera,doct_UsarPorDefecto,doct_idEspecialidad,
			doct_ConsultaDom,doct_ConsultaLun,doct_ConsultaMar,doct_ConsultaMie,doct_ConsultaJue,doct_ConsultaVie,doct_ConsultaSab,
			doct_CirugiaDom,doct_CirugiaLun,doct_CirugiaMar,doct_CirugiaMie,doct_CirugiaJue,doct_CirugiaVie,doct_CirugiaSab,
			doct_CitaDom,doct_CitaLun,doct_CitaMar,doct_CitaMie,doct_CitaJue,doct_CitaVie,doct_CitaSab,
			doct_HorIniConDom, doct_HorIniConLun,doct_HorIniConMar,doct_HorIniConMie,doct_HorIniConJue,doct_HorIniConVie,doct_HorIniConSab,
			doct_HorFinConDom, doct_HorFinConLun,doct_HorFinConMar,doct_HorFinConMie,doct_HorFinConJue,doct_HorFinConVie,doct_HorFinConSab,
			doct_HorIniCirDom, doct_HorIniCirLun,doct_HorIniCirMar,doct_HorIniCirMie,doct_HorIniCirJue,doct_HorIniCirVie,doct_HorIniCirSab,
			doct_HorFinCirDom, doct_HorFinCirLun,doct_HorFinCirMar,doct_HorFinCirMie,doct_HorFinCirJue,doct_HorFinCirVie,doct_HorFinCirSab,
			doct_HorIniCitDom, doct_HorIniCitLun,doct_HorIniCitMar,doct_HorIniCitMie,doct_HorIniCitJue,doct_HorIniCitVie,doct_HorIniCitSab,
			doct_HorFinCitDom, doct_HorFinCitLun,doct_HorFinCitMar,doct_HorFinCitMie,doct_HorFinCitJue,doct_HorFinCitVie,doct_HorFinCitSab)
	    Values(@IdDoctor, @NomUsuario, @IdDoctor,'A', 100,0,1,0,@IdEspecialidad,
			0,1,1,1,1,1,0,
			0,0,0,0,0,0,0,
			0,1,1,1,1,1,0,
			'08:00a','08:00a','08:00a','08:00a','08:00a','08:00a','08:00a',
			'07:30p','07:30p','07:30p','07:30p','07:30p','07:30p','07:30p',
			'08:00a','08:00a','08:00a','08:00a','08:00a','08:00a','08:00a',
			'07:30p','07:30p','07:30p','07:30p','07:30p','07:30p','07:30p',
			'08:00a','08:00a','08:00a','08:00a','08:00a','08:00a','08:00a',
			'07:30p','07:30p','07:30p','07:30p','07:30p','07:30p','07:30p')
		
		-- Adicionar al usuario el doctor y colocarlo por defecto
		Insert into s_usuarioDoctores(usud_IdUsuario, usud_IdDoctor, usud_TipoAcceso, usud_Usarpordefecto) 
		   values(@IdUsuario, @IdDoctor, 'D', 1 )
		   
		-- Adicionar las listas especiales a manejar al usuario
		Insert into s_UsuarioListasEspeciales(usul_IdListaEspecial,usul_IdUsuario,usul_TipoAcceso, usul_Usarpordefecto)
		Select lise_idLista, @IdUsuario, 'D',0 from s_ListaEspecial where lise_estatus='A'
		
		--Adicionar el doctor a todos los usuarios que tienen acceso a registrar lista de pacientes
		Insert into s_UsuarioDoctores(usud_IdUsuario, usud_IdDoctor, usud_TipoAcceso, usud_Usarpordefecto)
		   SELECT s_Usuario.usua_IdUsuario, @IdDoctor, 'D', 0
			   FROM s_Usuario 
			   INNER JOIN s_PerfilUsuario ON s_Usuario.usua_IdPerfil = s_PerfilUsuario.pusr_IdPerfil
			   WHERE     (s_PerfilUsuario.pusr_AccRLPa = 1) and s_Usuario.usua_IdUsuario<>@IdUsuario
	end
end
GO

-- Procedimiento: sp_CreaListaDispDoctores
CREATE procedure [dbo].[sp_CreaListaDispDoctores](@Fecha as Date) 
as

Declare @wd as integer
set @wd = DATEPART(weekday, @Fecha)

Declare @CantDoctores as integer

Select @CantDoctores = Count(*)from s_Doctor_Disponibilidad where Fecha=@Fecha

-- Si no hay Doctores en la lista de disponibilidad, adicionar los doctores que trabajan ese dia, si es la fecha actual
--2022-07-25 Origen indica que fue registrado por la "Disponibilidad".  Otra Opcion es "Registrado"
if @CantDoctores =0 and @Fecha = Cast(getdate() as date)
Begin
	Insert into s_Doctor_Disponibilidad(Fecha, IdDoctor, Origen)
         Select @Fecha as Fecha, doct_IdDoctor as IdDoctor, 'D' 
         from s_Doctor 
		 where doct_Estatus='A' and doct_ManListaEspera=1 and 
		   case when @wd =1 then Doct_ConsultaDom 
				when @wd =2 then Doct_ConsultaLun
				when @wd =3 then Doct_ConsultaMar
				when @wd =4 then Doct_ConsultaMie
				when @wd =5 then Doct_ConsultaJue
				when @wd =6 then Doct_ConsultaVie
				when @wd =7 then Doct_ConsultaSab  end = 1
              and doct_IdDoctor not in (Select dinl_IdDoctor from s_DiasNoLaborable where dinl_DesdeFecha<=@Fecha and dinl_HastaFecha>=@Fecha) 
          
End
GO

-- Procedimiento: sp_ExpedienteCambiarNumero
CREATE procedure [dbo].[sp_ExpedienteCambiarNumero] (@NumeroExpediente as Bigint, @NuevoNumero as Bigint, @Result varchar(2000) OUTPUT) as

-- Merge Expediente Desde a Expediente Hasta
Declare @CantReg as Integer
set @Result=''

-- Validar que el nuevo numero no exista
IF EXISTS (Select * from s_Expediente where expe_NumeroExpediente=@NuevoNumero)
Begin
	set @Result='ERROR: Ya existe un expediente con el No. <b>' + cast(@NuevoNumero as varchar) + '</b>. Cambio de numero no ejecutado.'
End
else
Begin

	SET NOCOUNT ON
	set @Result='Registros modificados del Expediente No. <b>' + cast(@NumeroExpediente as varchar) + '</b> al Expediente No. <b>'+ cast(@NuevoNumero as varchar) + '</b>:'

	-- Mover los datos al nuevo Expediente
	Update dbo.s_CertificacionMedica set cert_NumeroExpediente=@NuevoNumero where cert_NumeroExpediente = @NumeroExpediente
	set @CantReg = @@ROWCOUNT
	if @CantReg>0   
		Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' Certificaciones Médicas.'

	Update dbo.s_Cita set cita_NumeroExpediente=@NuevoNumero where cita_NumeroExpediente= @NumeroExpediente
	set @CantReg = @@ROWCOUNT
	if @CantReg>0   
		Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' Citas.'


	-- Si no existe el View, ejecutar la actualización
	--OJO: La actualización cuando hay un view da un error y no lo permite.
	IF NOT EXISTS (SELECT * FROM sys.objects WHERE type = 'V' AND OBJECT_ID = OBJECT_ID('dbo.[s_CompraLente]'))
	Begin
		Declare @ResultCompraLente as varchar(900)
		exec sp_ExpedienteCambiarNumeroCompraLente @NumeroExpediente, @NuevoNumero, @ResultCompraLente

		if @ResultCompraLente<>'' 
			Set @Result = @Result + '<br>' + @ResultCompraLente

	end
	else
		Set @Result = @Result + '<br>' + 'La compra de lente no se unificó.  Es un view que viene de Soluciones Integradas'


	Update dbo.s_HeaderMovInv set NumeroExpediente=@NuevoNumero where NumeroExpediente= @NumeroExpediente
	set @CantReg = @@ROWCOUNT
	if @CantReg>0    
		Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' Movimiento Inventario.'

	Update dbo.s_HeaderPago set hpag_NumeroExpediente=@NuevoNumero where hpag_NumeroExpediente= @NumeroExpediente
	set @CantReg = @@ROWCOUNT
	if @CantReg>0 
	Begin   
		Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' Recibos de pagos.'
		Set @Result = @Result + '<br>' + 'Las facturas generadas en Soluciones no se unificaron.'
	end


	Update dbo.s_HeaderPresupuesto set hpre_NumeroExpediente=@NuevoNumero where hpre_NumeroExpediente= @NumeroExpediente
	set @CantReg = @@ROWCOUNT
	if @CantReg>0   
		Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' Presupuestos.'

	Update dbo.s_ListaEspecialPacientes set lisp_NumeroExpediente=@NuevoNumero where lisp_NumeroExpediente= @NumeroExpediente
	set @CantReg = @@ROWCOUNT
	if @CantReg>0   
		Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' Lista Especial Pacientes.'

	Update dbo.s_ListaPaciente set lisp_NumeroExpediente=@NuevoNumero where lisp_NumeroExpediente= @NumeroExpediente
	set @CantReg = @@ROWCOUNT
	if @CantReg>0    
		Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' Lista Paciente en Espera.'

	Update dbo.s_NotaExpediente set NumeroExpediente=@NuevoNumero where NumeroExpediente= @NumeroExpediente
	set @CantReg = @@ROWCOUNT
	if @CantReg>0    
		Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' Notas.'

	Update dbo.s_Visita set visi_NumeroExpediente=@NuevoNumero where visi_NumeroExpediente = @NumeroExpediente
	set @CantReg = @@ROWCOUNT
	if @CantReg>0    
		Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' Visitas.'

	-- 2023-02-01
	Update dbo.s_AutorizacionesSeguro set NumeroExpediente=@NuevoNumero where NumeroExpediente = @NumeroExpediente
	set @CantReg = @@ROWCOUNT
	if @CantReg>0    
		Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' Autorizaciones Seguro.'

	Update dbo.s_ExpedientePoliza set NumeroExpediente=@NuevoNumero where NumeroExpediente = @NumeroExpediente
	set @CantReg = @@ROWCOUNT
	if @CantReg>0    
		Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' Expediente Poliza.'

	Update dbo.s_NotaExpediente set NumeroExpediente=@NuevoNumero where NumeroExpediente = @NumeroExpediente
	set @CantReg = @@ROWCOUNT
	if @CantReg>0    
		Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' Nota Expediente.'

	Update dbo.s_PreCita set cita_NumeroExpediente=@NuevoNumero where cita_NumeroExpediente= @NumeroExpediente
	set @CantReg = @@ROWCOUNT
	if @CantReg>0   
		Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' PreCitas.'

	Update dbo.s_Referimiento set refe_numeroExpediente=@NuevoNumero where refe_numeroExpediente= @NumeroExpediente
	set @CantReg = @@ROWCOUNT
	if @CantReg>0   
		Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' Referimientos.'


	Update s_Expediente set expe_NumeroExpediente=@NuevoNumero where expe_NumeroExpediente=@NumeroExpediente
	set @CantReg = @@ROWCOUNT
	if @CantReg>0   
		Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' Datos Generales.'

	-- OJO puede dar error, expediente es  parte dela clave
	Update dbo.s_EstudioImagenes_SinVisita set NumeroExpediente=@NuevoNumero where NumeroExpediente= @NumeroExpediente
	set @CantReg = @@ROWCOUNT
	if @CantReg>0    
		Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' Imagenes Estudios sin Visitas.'

	SET NOCOUNT OFF
end
GO

-- Procedimiento: sp_ExpedienteCambiarNumeroCompraLente
CREATE procedure [dbo].[sp_ExpedienteCambiarNumeroCompraLente] (@NumeroExpediente as Bigint, @NuevoNumero as Bigint, @Result varchar(2000) OUTPUT) as

-- Merge Expediente Desde a Expediente Hasta
Declare @CantReg as Integer
Set @Result = ''

-- Si no existe el View, ejecutar la actualización
--OJO: La actualización cuando hay un view da un error y no lo permite.
IF NOT EXISTS (SELECT * FROM sys.objects WHERE type = 'V' AND OBJECT_ID = OBJECT_ID('dbo.[s_CompraLente]'))
Begin
	Update dbo.s_CompraLente set NumeroExpediente=@NuevoNumero where NumeroExpediente= @NumeroExpediente
	set @CantReg = @@ROWCOUNT
	if @CantReg>0     
		Set @Result =  cast(@CantReg as varchar) + ' Compra Lentes.'
end
GO

-- Procedimiento: sp_ExpedienteMerge
CREATE procedure [dbo].[sp_ExpedienteMerge] (@NumExpedienteDesde as Bigint, @NumExpedienteHasta as Bigint, @DatosGenMant as char(1), @Result varchar(2000) OUTPUT) as

-- Merge Expediente Desde a Expediente Hasta
Declare @CantReg as Integer

SET NOCOUNT ON
set @Result='Totales movidos del Expediente No. <b>' + cast(@NumExpedienteDesde as varchar) + '</b> al Expediente No. <b>'+ cast(@NumExpedienteHasta as varchar) + '</b>:'

-- Mover los datos al nuevo Expediente
Update dbo.s_CertificacionMedica set cert_NumeroExpediente=@NumExpedienteHasta where cert_NumeroExpediente = @NumExpedienteDesde
set @CantReg = @@ROWCOUNT
if @CantReg>0   
    Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' Certificaciones Médicas.'

Update dbo.s_Cita set cita_NumeroExpediente=@NumExpedienteHasta where cita_NumeroExpediente= @NumExpedienteDesde
set @CantReg = @@ROWCOUNT
if @CantReg>0   
    Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' Citas Movidas.'


-- Si no existe el View, ejecutar la actualización
--OJO: La actualización cuando hay un view da un error y no lo permite.
IF NOT EXISTS (SELECT * FROM sys.objects WHERE type = 'V' AND OBJECT_ID = OBJECT_ID('dbo.[s_CompraLente]'))
Begin
	Declare @ResultCompraLente as varchar(900)
	exec sp_ExpedienteMergeCompraLente @NumExpedienteDesde, @NumExpedienteHasta, @ResultCompraLente

	if @ResultCompraLente<>'' 
		Set @Result = @Result + '<br>' + @ResultCompraLente

end
else
	Set @Result = @Result + '<br>' + 'La compra de lente no se unificó.  Es un view que viene de Soluciones Integradas'


Update dbo.s_HeaderMovInv set NumeroExpediente=@NumExpedienteHasta where NumeroExpediente= @NumExpedienteDesde
set @CantReg = @@ROWCOUNT
if @CantReg>0    
    Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' Movimiento Inventario.'

Update dbo.s_HeaderPago set hpag_NumeroExpediente=@NumExpedienteHasta where hpag_NumeroExpediente= @NumExpedienteDesde
set @CantReg = @@ROWCOUNT
if @CantReg>0 
Begin   
    Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' Recibos de pagos.'
	Set @Result = @Result + '<br>' + 'Las facturas generadas en Soluciones no se unificaron.'
end


Update dbo.s_HeaderPresupuesto set hpre_NumeroExpediente=@NumExpedienteHasta where hpre_NumeroExpediente= @NumExpedienteDesde
set @CantReg = @@ROWCOUNT
if @CantReg>0   
    Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' Presupuestos Movidos.'

Update dbo.s_ListaEspecialPacientes set lisp_NumeroExpediente=@NumExpedienteHasta where lisp_NumeroExpediente= @NumExpedienteDesde
set @CantReg = @@ROWCOUNT
if @CantReg>0   
    Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' Lista Especial Pacientes.'

Update dbo.s_ListaPaciente set lisp_NumeroExpediente=@NumExpedienteHasta where lisp_NumeroExpediente= @NumExpedienteDesde
set @CantReg = @@ROWCOUNT
if @CantReg>0    
    Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' Lista Paciente en Espera.'

Update dbo.s_NotaExpediente set NumeroExpediente=@NumExpedienteHasta where NumeroExpediente= @NumExpedienteDesde
set @CantReg = @@ROWCOUNT
if @CantReg>0    
    Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' Notas Movidas.'

Update dbo.s_Visita set visi_NumeroExpediente=@NumExpedienteHasta where visi_NumeroExpediente = @NumExpedienteDesde
set @CantReg = @@ROWCOUNT
if @CantReg>0    
    Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' Visitas Movidas.'

SET NOCOUNT OFF

-- Eliminar el Expediente Desde
if (@DatosGenMant='D')
Begin
	Delete from dbo.s_Expediente where expe_NumeroExpediente=@NumExpedienteHasta

	Update s_Expediente set expe_NumeroExpediente=@NumExpedienteHasta where expe_NumeroExpediente=@NumExpedienteDesde
	
	Set @Result = @Result + '<br>' + 'Se conservó datos generales del expediente Origen No. <b>' + cast(@NumExpedienteDesde as varchar) + '</b> y se borraron del Destino No. <b>' + cast(@NumExpedienteHasta as varchar) + '</b>.'
end
else
Begin
	Delete from dbo.s_Expediente where expe_NumeroExpediente=@NumExpedienteDesde
	Set @Result = @Result + '<br>' + 'Se conservó datos generales del expediente Destino No. <b>' + cast(@NumExpedienteHasta as varchar) + '</b> y se borraron del Origen No. <b>' + cast(@NumExpedienteDesde as varchar) + '</b>.'
end

-- 2023-02-01
Update dbo.s_AutorizacionesSeguro set NumeroExpediente=@NumExpedienteHasta where NumeroExpediente = @NumExpedienteDesde
set @CantReg = @@ROWCOUNT
if @CantReg>0    
    Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' Autorizaciones Seguro.'

Update dbo.s_ExpedientePoliza set NumeroExpediente=@NumExpedienteHasta where NumeroExpediente = @NumExpedienteDesde
set @CantReg = @@ROWCOUNT
if @CantReg>0    
    Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' Expediente Poliza.'

Update dbo.s_NotaExpediente set NumeroExpediente=@NumExpedienteHasta where NumeroExpediente = @NumExpedienteDesde
set @CantReg = @@ROWCOUNT
if @CantReg>0    
    Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' Nota Expediente.'

Update dbo.s_PreCita set cita_NumeroExpediente=@NumExpedienteHasta where cita_NumeroExpediente= @NumExpedienteDesde
set @CantReg = @@ROWCOUNT
if @CantReg>0   
    Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' PreCitas.'

Update dbo.s_Referimiento set refe_numeroExpediente=@NumExpedienteHasta where refe_numeroExpediente= @NumExpedienteDesde
set @CantReg = @@ROWCOUNT
if @CantReg>0   
    Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' Referimientos.'


SET NOCOUNT ON
-- OJO puede dar error, expediente es  parte dela clave
Update dbo.s_EstudioImagenes_SinVisita set NumeroExpediente=@NumExpedienteHasta where NumeroExpediente= @NumExpedienteDesde
set @CantReg = @@ROWCOUNT
if @CantReg>0    
    Set @Result = @Result + '<br>' + cast(@CantReg as varchar) + ' Imagenes Estudios sin Visitas.'

SET NOCOUNT OFF
GO

-- Procedimiento: sp_ExpedienteMergeCompraLente
CREATE procedure [dbo].[sp_ExpedienteMergeCompraLente] (@NumExpedienteDesde as Bigint, @NumExpedienteHasta as Bigint, @Result varchar(2000) OUTPUT) as

-- Merge Expediente Desde a Expediente Hasta
Declare @CantReg as Integer
Set @Result = ''

-- Si no existe el View, ejecutar la actualización
--OJO: La actualización cuando hay un view da un error y no lo permite.
IF NOT EXISTS (SELECT * FROM sys.objects WHERE type = 'V' AND OBJECT_ID = OBJECT_ID('dbo.[s_CompraLente]'))
Begin
	Update dbo.s_CompraLente set NumeroExpediente=@NumExpedienteHasta where NumeroExpediente= @NumExpedienteDesde
	set @CantReg = @@ROWCOUNT
	if @CantReg>0     
		Set @Result =  cast(@CantReg as varchar) + ' Compra Lentes.'
end
GO

-- Procedimiento: sp_GetMovCajaChica
CREATE Procedure [dbo].[sp_GetMovCajaChica](@CodCaj char(2)) as
--Procedimiento que muestra los movimientos del articulo y su balance a la fecha

-- Para Probar
--declare @IdCuenta varchar(10)
--set @IdCuenta = '1013'

Declare @DesdeFecha date, @HastaFecha Date, @BalanceInicial float = 0, @PrimeraFecha date

--Buscar la ultima fecha del balance
Select @DesdeFecha = isNull(MAX(Fecha),'01/01/1900') from CajaChica_Balance
if @DesdeFecha='01/01/1900' 
Begin
	Select @DesdeFecha = isNull(MIN(Fecha),'01/01/1900') from CajaChica_Vale
	
	Select @PrimeraFecha = isNull(MIN(Fecha),'01/01/1900') from CajaChica_Rep
	
	if @PrimeraFecha<@DesdeFecha 
	begin
		set @DesdeFecha =@PrimeraFecha
	end
End
else
Begin
	Select @BalanceInicial = IsNull(BalanceInicial,0.0) from CajaChica_Balance where Fecha=@DesdeFecha and CodCaj=@CodCaj
End

set @HastaFecha = GETDATE()

--- Falta incluir facturacion 
DECLARE forward_cursor CURSOR FAST_FORWARD 
FOR 
select  @CodCaj as CodCaj, cast(@DesdeFecha as datetime) as Fecha, 'Balance Inicial' as DesTipMov, @BalanceInicial as MontoDB, NULL as MontoCR, '' as Descripcion, 'BI' as TipMov, 0 as NroMov
where @BalanceInicial>0
union
Select CodCaj, fecha, 'Vale' as DesTipMov, Case Tipo when 'E' then Monto else NULL end as MontoDB, Case Tipo when 'S' then Monto else NULL end as MontoCR , rtrim(NomConcepto) + ', ' + rtrim(NomConcepto) + ', ' + rtrim(DesMov) as Descripcion, 'V' as TipMov, Numero as NroMov
from CajaChica_Vale 
Left outer join ConceptoCaja on CajaChica_Vale.IdConcepto = ConceptoCaja.IdConcepto 
where CajaChica_Vale.Estatus='P' and codCaj=@CodCaj and cast(Fecha as DATE)>=@DesdeFecha and cast(Fecha as DATE)<=@HastaFecha
union
Select CodCaj, fecha, 'Reposición' as DesTipMov, Monto as MontoDB,  NULL as MontoCR,  DesMov as Descripcion, 'R' as TipMov, NumRep
from CajaChica_Rep
where CajaChica_Rep.Estatus='P' and codCaj=@CodCaj and cast(fecha as DATE)>=@DesdeFecha and cast(fecha as DATE)<=@HastaFecha
Order by Fecha, tipMov, NroMov


DECLARE @TotalTable table(NumReg int, CodCaj varchar(2), fecha Date, DesTipMov varchar(20), MontoDB float, MontoCR float, Balance Float, Descripcion varchar(100), TipMov char(2), NroMov Int, primary key (TipMov, NroMov))


OPEN forward_cursor 

DECLARE @Balance float, @NumReg int,
    @CodCaj2 varchar(16),
    @Fecha date,
    @DesTipMov varchar(50),
    @MontoDB float,
    @MontoCR Float,
    @Descripcion varchar(50),
    @TipMov varchar(10),
    @NroMov int
SET @Balance = 0
set @NumReg  = 0

FETCH NEXT FROM forward_cursor INTO  @CodCaj2,@Fecha,@DesTipMov,@MontoDB,@MontoCR,@Descripcion,@TipMov,@NroMov
WHILE (@@FETCH_STATUS = 0)
BEGIN
     SET @Balance = @Balance + IsNull(@MontoDB,0.0) - IsNull(@MontoCR,0.0)
     SET @NumReg = @NumReg + 1
     INSERT @TotalTable VALUES(@NumReg, @CodCaj, @Fecha, @DesTipMov, @MontoDB, @MontoCR, @Balance, @Descripcion,@TipMov, @NroMov)
     FETCH NEXT FROM forward_cursor INTO  @CodCaj2,@Fecha,@DesTipMov,@MontoDB,@MontoCR,@Descripcion,@TipMov,@NroMov

END

CLOSE forward_cursor
DEALLOCATE forward_cursor

SELECT * FROM @TotalTable order by NumReg Desc
GO

-- Procedimiento: sp_GetRecetasMedicamento
CREATE PROCEDURE [sp_GetRecetasMedicamento](@fecini  datetime, @fecfin datetime, @Medicamento varchar(200), @IdDoctor varchar(15)) AS
SELECT s_Visita.visi_FechaVisita, s_visita.visi_IdDoctor, doct_Nombre, v_Visita_Recetas.NumVisita, NumReceta, rtrim(expe_Nombres) + ' ' +  rtrim(expe_Apellidos) as NombrePaciente
      , expe_TelefonoCasa, expe_Celular, expe_email,  v_Visita_Recetas.visi_Tratamiento
  FROM v_Visita_Recetas 
  iNNER JOIN s_visita on v_Visita_Recetas.NumVisita = s_visita.NumVisita
  Inner join s_Expediente on s_visita.visi_NumeroExpediente = s_Expediente.expe_NumeroExpediente
  Inner join s_Doctor on s_visita.visi_IdDoctor = s_Doctor.doct_IdDoctor
  where v_Visita_Recetas.visi_Tratamiento like '%' + @Medicamento + '%'
  and cast(s_visita.visi_FechaVisita as date)>=cast(@fecini as date)
  and cast(s_visita.visi_FechaVisita as date)<=cast(@fecfin as date)
  and (s_visita.visi_IdDoctor= @IdDoctor or rtrim(@IdDoctor)='')
GO

-- Procedimiento: sp_ListaPacienteEnTurno
CREATE PROCEDURE [dbo].[sp_ListaPacienteEnTurno](@IdDoctor varchar(15)) AS

Select lisp_Estatus as Estatus, rtrim(doct_iniciales) + cast(lisp_Secuencia as varchar) as IdTurno, NomConsultorio, Preposicion, 
case when lisp_Estatus='L' then lisp_HoraLlamada else lisp_HoraIniConsulta end as Hora,
case when isnull(lisp_NumeroExpediente,0)=0 then rtrim(lisp_nombre) + ' ' + rtrim(lisp_Apellido) 
else rtrim(s_Expediente.expe_Nombres) + ' ' +  rtrim(s_Expediente.expe_Apellidos) end as NombrePaciente
from s_ListaPaciente
Left outer join s_Doctor on s_ListaPaciente.lisp_IdDoctor = s_Doctor.doct_IdDoctor
left outer Join s_Expediente on s_Listapaciente.lisp_NumeroExpediente = s_Expediente.expe_NumeroExpediente 
Left outer join s_Consultorio on s_ListaPaciente.lisp_IdConsultorio = s_Consultorio.IdConsultorio
where (lisp_Estatus='L' or lisp_Estatus='I') and cast(lisp_Fecha as date)= cast(getdate() as date)
and (lisp_IdDoctor=@IdDoctor or rtrim(@IdDoctor)='')
order by lisp_Estatus Desc, case when lisp_Estatus='L' then lisp_HoraLlamada else lisp_HoraIniConsulta end
GO

-- Procedimiento: sp_MovPorArticulo
CREATE PROCEDURE [dbo].[sp_MovPorArticulo](@CodArt as char(10), @fecini  smalldatetime, @fecfin smallDateTime) AS

--- Procedimiento que Retorna los Mov por articulo
Select dpag_idconcepto as CodArt, h.hpag_fecha as fecha, h.hpag_hora as Hora, dpag_secuencia as linea, -1 as cantidad, 'Venta' as NomTMov, H.hpag_desmov as Descripcion, 'V' as TipMov, hpag_idcaja as IdCaja, hpag_Numero as Numero,
'' as IdSuplidor, '' as NomSuplidor, hpag_IdDoctor as IdDoctor, doct_Nombre as NomDoctor, hpag_NumeroExpediente as NumeroExpediente,
rtrim(expe_nombres) + ' ' + rtrim(expe_Apellidos) as Nompaciente, '' as Referencia
from dbo.s_DetallePago D
inner join dbo.s_HeaderPago H on D.dpag_idCaja = H.hpag_idCaja and D.dpag_numero = H.hpag_numero
inner join s_ConceptoPago on D.dpag_IdConcepto = s_ConceptoPago.conc_IdConcepto
Left outer join s_Doctor on H.hpag_IdDoctor = s_Doctor.doct_IdDoctor
Left outer join s_Expediente on H.hpag_NumeroExpediente=s_Expediente.expe_NumeroExpediente
where dpag_idconcepto= @CodArt and hpag_Fecha >=@fecini and hpag_Fecha <= @fecfin and s_ConceptoPago.conc_Tipo in ('A','M') and H.hpag_Estatus='P'
union
Select CodArt, H.Fecha, '' as hora, linea, case when T.Clase='E' then D.Cantidad else -D.Cantidad end as Cantidad, T.NomTMov, H.Descripcion, 'I' as TipMov, '' as IdCaja, H.Numero,
H.IdSuplidor, s_Suplidor.Nombre as NomSuplidor, IdDoctor, doct_Nombre as NomDoctor, NumeroExpediente,
rtrim(expe_nombres) + ' ' +rtrim(expe_Apellidos) as Nompaciente, H.Referencia
from s_DetalleMovInv D
Inner join s_HeaderMovInv H on D.Numero=H.Numero 
Inner join s_TipoMovInv T on H.codtmov=T.codtmov
Left outer join s_Doctor on H.IdDoctor = s_Doctor.doct_IdDoctor
Left outer join s_Expediente on H.NumeroExpediente=s_Expediente.expe_NumeroExpediente
Left outer join s_suplidor on H.IdSuplidor = s_Suplidor.IdSuplidor
where CodArt=@CodArt and Fecha >=@fecini and Fecha <= @fecfin and H.Estatus<>'A'
order by Fecha, linea
GO

-- Procedimiento: sp_ProcesarNotaCreditoSeguro
CREATE procedure [dbo].[sp_ProcesarNotaCreditoSeguro] (
 @Numero int,
 @IdUsuario varchar(15),
  @Result VARCHAR(900) OUTPUT )
as

/*  Para probar 
Declare @NumCorte as Int
set @NumCorte = 1
set @Estatus='P'
 */
Declare @Tipo as char(1), @hpag_IdCaja as varchar(10), @hpag_Numero as integer, @MontoAAplicar as decimal(12,2), @MontoPendiente as decimal(12,2)

-- Verificar si hay s_HeaderPagos que no se puedan pagar
DECLARE s_HeaderPagosConProblemas_Cursor CURSOR LOCAL FOR 
   SELECT s_NotaCreditoSeguro_Detalles.Tipo, s_NotaCreditoSeguro_Detalles.hpag_IdCaja, s_NotaCreditoSeguro_Detalles.hpag_Numero, s_NotaCreditoSeguro_Detalles.Monto, IsNull(v_FacturasSeguro.hpag_CoberturaSeguro,0.0) + IsNull(v_FacturasSeguro.hpag_MontoDebito,0.0) - IsNull(v_FacturasSeguro.hpag_MontoPagado,0.0) as MontoPendiente
    FROM s_NotaCreditoSeguro_Detalles 
     Left outer Join v_FacturasSeguro on s_NotaCreditoSeguro_Detalles.Tipo = v_FacturasSeguro.Tipo and s_NotaCreditoSeguro_Detalles.hpag_IdCaja = v_FacturasSeguro.hpag_IdCaja and s_NotaCreditoSeguro_Detalles.hpag_Numero = v_FacturasSeguro.hpag_Numero
     WHERE Numero= @Numero and s_NotaCreditoSeguro_Detalles.Monto > (IsNull(v_FacturasSeguro.hpag_CoberturaSeguro,0.0) + IsNull(v_FacturasSeguro.hpag_MontoDebito,0.0) - IsNull(v_FacturasSeguro.hpag_MontoPagado,0.0))

DECLARE Reclamaciones_Cursor CURSOR LOCAL FOR
	Select distinct v_FacturasSeguro.hpag_NumeroReclamacionSeguro
	  FROM s_NotaCreditoSeguro_Detalles 
     Left outer Join v_FacturasSeguro on s_NotaCreditoSeguro_Detalles.Tipo = v_FacturasSeguro.Tipo and s_NotaCreditoSeguro_Detalles.hpag_IdCaja = v_FacturasSeguro.hpag_IdCaja and s_NotaCreditoSeguro_Detalles.hpag_Numero = v_FacturasSeguro.hpag_Numero
	  WHERE Numero= @Numero


set @Result =''


Declare @EstatusAct as char(1), @FechaPago as date, @IdTransCnt as Integer,  @NumNcrSin as integer, @NumeroReclamacion as integer, @IdSecCF as varchar(2), @NCF as varchar(19), @FecVenNCF datetime
	Select @EstatusAct = Estatus, @FechaPago= Fecha, @NumNcrSin=IsNull(NumNcrSin,0), @IdSecCF=IsNull(IdSecCF,''), @NCF=IsNull(NCF,'')  from s_NotaCreditoSeguro where Numero=@Numero
	
if @EstatusAct='N' 
Begin

	-- Validar si las facturas estan conrrectas
	OPEN s_HeaderPagosConProblemas_Cursor 

	FETCH NEXT FROM s_HeaderPagosConProblemas_Cursor into @Tipo, @hpag_IdCaja, @hpag_Numero, @MontoAAplicar,@MontoPendiente
	WHILE @@FETCH_STATUS = 0 
	BEGIN 
		set @Result = @Result + 'Para el recibo Tipo:' +  @Tipo + ' Caja:' + @hpag_IdCaja + ' #:'+  Cast(@hpag_Numero as varchar) + ' se sobrepasa el monto pendiente: ' + CAST(@MontoPendiente as varchar) + '. A Aplicar: ' + Cast(@MontoAAplicar as varchar) + '<br>'
		FETCH NEXT FROM s_HeaderPagosConProblemas_Cursor into @Tipo ,@hpag_IdCaja, @hpag_Numero, @MontoAAplicar,@MontoPendiente
	END 
	CLOSE s_HeaderPagosConProblemas_Cursor 
	DEALLOCATE s_HeaderPagosConProblemas_Cursor 

	if IsNull(@Result,'')=''
	Begin

		if @IdSecCF<>'' and @NCF=''
		Begin
			exec GenerarNCF @IdSecCF, @NCF OUTPUT	
			if @NCF='' 
				set @Result='No se generó el NCF para la secuencia Id:' + @IdSecCF +'.  Nota de Crédito no puede ser procesada.'
			else
				Begin
					Select @FecVenNCF = FechaVen  from s_SecuenciaCF where Id=@IdSecCF
					Update s_NotaCreditoSeguro set NCF=@NCF, NCFAsig=1, FecVenNCF = @FecVenNCF	where Numero=@Numero
				end
		end
		if IsNull(@Result,'')=''
		Begin
			-- Actualizar el balance pagado de las coberturas
			Update s_HeaderPago set hpag_MontoPagado = IsNull(hpag_MontoPagado,0.0) + s_NotaCreditoSeguro_Detalles.Monto,
									hpag_FechaSaldo = Case when Round(s_HeaderPago.hpag_CoberturaSeguro - IsNull(hpag_MontoPagado,0.0) - s_NotaCreditoSeguro_Detalles.Monto,2) <=0 then @FechaPago else NULL end
			from s_NotaCreditoSeguro_Detalles 
			Inner join s_HeaderPago on s_NotaCreditoSeguro_Detalles.hpag_IdCaja = s_HeaderPago.hpag_IdCaja and s_NotaCreditoSeguro_Detalles.hpag_Numero = s_HeaderPago.hpag_Numero
			where s_NotaCreditoSeguro_Detalles.Tipo='COB' and s_NotaCreditoSeguro_Detalles.Numero= @Numero
		
			-- actualizar el balance de los descuentos
			Update s_HeaderPago_Descuento set hpag_MontoPagado = IsNull(hpag_MontoPagado,0.0) + s_NotaCreditoSeguro_Detalles.Monto,
									hpag_FechaSaldo = Case when Round(s_HeaderPago_Descuento.hpag_Descuento - IsNull(hpag_MontoPagado,0.0) - s_NotaCreditoSeguro_Detalles.Monto,2) <=0 then @FechaPago else NULL end
			from s_NotaCreditoSeguro_Detalles 
			Inner join s_HeaderPago_Descuento on s_NotaCreditoSeguro_Detalles.hpag_IdCaja = s_HeaderPago_Descuento.hpag_IdCaja and s_NotaCreditoSeguro_Detalles.hpag_Numero = s_HeaderPago_Descuento.hpag_Numero
			where s_NotaCreditoSeguro_Detalles.Tipo='DSC' and s_NotaCreditoSeguro_Detalles.Numero= @Numero
		
		
			-- Guardar el monto pendiente de las s_HeaderPagos en el detale del s_NotaCreditoSeguro
			Update s_NotaCreditoSeguro_Detalles set PendienteRec=IsNull(v_FacturasSeguro.hpag_CoberturaSeguro,0.0) - IsNull(v_FacturasSeguro.hpag_MontoPagado,0.0), Saldo=case when IsNull(v_FacturasSeguro.hpag_CoberturaSeguro,0.0) - IsNull(v_FacturasSeguro.hpag_MontoPagado,0.0)=0 then 1 else 0 end
			from s_NotaCreditoSeguro_Detalles
			inner Join v_FacturasSeguro on s_NotaCreditoSeguro_Detalles.Tipo = v_FacturasSeguro.Tipo and s_NotaCreditoSeguro_Detalles.hpag_IdCaja = v_FacturasSeguro.hpag_IdCaja and s_NotaCreditoSeguro_Detalles.hpag_Numero = v_FacturasSeguro.hpag_Numero
			where s_NotaCreditoSeguro_Detalles.Numero= @Numero

			-- actualizar el Estatus del s_NotaCreditoSeguro
			Update s_NotaCreditoSeguro set Estatus='P'
			where Numero=@Numero

				-- Actualizar el estatus a las reclamacion
			OPEN Reclamaciones_Cursor 

			FETCH NEXT FROM Reclamaciones_Cursor into @NumeroReclamacion
			WHILE @@FETCH_STATUS = 0 
			BEGIN 
				if @NumeroReclamacion>0 
					exec sp_ActualizaEstatusReclamacion @NumeroReclamacion, @Result OUTPUT

				FETCH NEXT FROM Reclamaciones_Cursor into @NumeroReclamacion
			END 
			CLOSE Reclamaciones_Cursor 
			DEALLOCATE Reclamaciones_Cursor 
		end
  end

end
GO

-- Procedimiento: sp_ProcesarNotaDebitoSeguro
CREATE procedure [dbo].[sp_ProcesarNotaDebitoSeguro] (
 @Numero int,
 @IdUsuario varchar(15),
  @Result VARCHAR(900) OUTPUT )
as

/*  Para probar 
Declare @NumCorte as Int
set @NumCorte = 1
set @Estatus='P'
 */
Declare @Tipo as char(1), @hpag_IdCaja as varchar(10), @hpag_Numero as integer, @MontoAAplicar as decimal(12,2), @MontoPendiente as decimal(12,2)

DECLARE Reclamaciones_Cursor CURSOR LOCAL FOR
	Select distinct v_FacturasSeguro.hpag_NumeroReclamacionSeguro
	  FROM s_NotaDebitoSeguro_Detalles 
     Left outer Join v_FacturasSeguro on s_NotaDebitoSeguro_Detalles.Tipo = v_FacturasSeguro.Tipo and s_NotaDebitoSeguro_Detalles.hpag_IdCaja = v_FacturasSeguro.hpag_IdCaja and s_NotaDebitoSeguro_Detalles.hpag_Numero = v_FacturasSeguro.hpag_Numero
	  WHERE Numero= @Numero


set @Result =''


Declare @EstatusAct as char(1), @FechaPago as date, @IdTransCnt as Integer, @NumeroReclamacion as integer, @IdSecCF as varchar(2), @NCF as varchar(19), @FecVenNCF datetime
	Select @EstatusAct = Estatus, @FechaPago= Fecha,  @IdSecCF=IsNull(IdSecCF,''), @NCF=IsNull(NCF,'')  from s_NotaDebitoSeguro where Numero=@Numero
	
if @EstatusAct='N' 
Begin

		if @IdSecCF<>'' and @NCF=''
		Begin
			exec GenerarNCF @IdSecCF, @NCF OUTPUT	
			if @NCF='' 
				set @Result='No se generó el NCF para la secuencia Id:' + @IdSecCF +'.  Nota de Débito no puede ser procesada.'
			else
				Begin
					Select @FecVenNCF = FechaVen  from s_SecuenciaCF where Id=@IdSecCF
					Update s_NotaDebitoSeguro set NCF=@NCF, NCFAsig=1, FecVenNCF = @FecVenNCF	where Numero=@Numero
				end
		end
		if IsNull(@Result,'')=''
		Begin
			-- Actualizar el balance Debito de las coberturas
			Update s_HeaderPago set hpag_MontoDebito = IsNull(hpag_MontoDebito,0.0) + s_NotaDebitoSeguro_Detalles.Monto
			from s_NotaDebitoSeguro_Detalles 
			Inner join s_HeaderPago on s_NotaDebitoSeguro_Detalles.hpag_IdCaja = s_HeaderPago.hpag_IdCaja and s_NotaDebitoSeguro_Detalles.hpag_Numero = s_HeaderPago.hpag_Numero
			where s_NotaDebitoSeguro_Detalles.Tipo='COB' and s_NotaDebitoSeguro_Detalles.Numero= @Numero
		
			-- Guardar el monto pendiente de las s_HeaderPagos en el detale del s_NotaDebitoSeguro
			Update s_NotaDebitoSeguro_Detalles set PendienteRec=IsNull(v_FacturasSeguro.hpag_CoberturaSeguro,0.0) + IsNull(v_FacturasSeguro.hpag_MontoDebito,0.0) - IsNull(v_FacturasSeguro.hpag_MontoPagado,0.0)
			from s_NotaDebitoSeguro_Detalles
			inner Join v_FacturasSeguro on s_NotaDebitoSeguro_Detalles.Tipo = v_FacturasSeguro.Tipo and s_NotaDebitoSeguro_Detalles.hpag_IdCaja = v_FacturasSeguro.hpag_IdCaja and s_NotaDebitoSeguro_Detalles.hpag_Numero = v_FacturasSeguro.hpag_Numero
			where s_NotaDebitoSeguro_Detalles.Numero= @Numero

			-- actualizar el Estatus del s_NotaDebitoSeguro
			Update s_NotaDebitoSeguro set Estatus='P'
			where Numero=@Numero

				-- Actualizar el estatus a las reclamacion
			OPEN Reclamaciones_Cursor 

			FETCH NEXT FROM Reclamaciones_Cursor into @NumeroReclamacion
			WHILE @@FETCH_STATUS = 0 
			BEGIN 
				if @NumeroReclamacion>0 
					exec sp_ActualizaEstatusReclamacion @NumeroReclamacion, @Result OUTPUT

				FETCH NEXT FROM Reclamaciones_Cursor into @NumeroReclamacion
			END 
			CLOSE Reclamaciones_Cursor 
			DEALLOCATE Reclamaciones_Cursor 
		end


end
GO

-- Procedimiento: sp_ProcesarPagoSeguro
CREATE procedure [dbo].[sp_ProcesarPagoSeguro] (
 @Numero int,
 @IdUsuario varchar(15),
  @Result VARCHAR(900) OUTPUT )
as

/*  Para probar 
Declare @NumCorte as Int
set @NumCorte = 1
set @Estatus='P'
 */
Declare @Tipo as char(1), @hpag_IdCaja as varchar(10), @hpag_Numero as integer, @MontoAAplicar as decimal(12,2), @MontoPendiente as decimal(12,2)

-- Verificar si hay s_HeaderPagos que no se puedan pagar
DECLARE s_HeaderPagosConProblemas_Cursor CURSOR LOCAL FOR 
   SELECT s_PagoSeguro_Detalles.Tipo, s_PagoSeguro_Detalles.hpag_IdCaja, s_PagoSeguro_Detalles.hpag_Numero, s_PagoSeguro_Detalles.Monto, IsNull(v_FacturasSeguro.hpag_CoberturaSeguro,0.0) - IsNull(v_FacturasSeguro.hpag_MontoPagado,0.0) + IsNull(v_FacturasSeguro.hpag_MontoDebito,0.0) as MontoPendiente
    FROM s_PagoSeguro_Detalles 
     Left outer Join v_FacturasSeguro on s_PagoSeguro_Detalles.Tipo = v_FacturasSeguro.Tipo and s_PagoSeguro_Detalles.hpag_IdCaja = v_FacturasSeguro.hpag_IdCaja and s_PagoSeguro_Detalles.hpag_Numero = v_FacturasSeguro.hpag_Numero
     WHERE pags_Numero= @Numero and s_PagoSeguro_Detalles.Monto > (IsNull(v_FacturasSeguro.hpag_CoberturaSeguro,0.0) - IsNull(v_FacturasSeguro.hpag_MontoPagado,0.0) + IsNull(v_FacturasSeguro.hpag_MontoDebito,0.0))

DECLARE Reclamaciones_Cursor CURSOR LOCAL FOR
	Select distinct v_FacturasSeguro.hpag_NumeroReclamacionSeguro
	  FROM s_PagoSeguro_Detalles 
     Left outer Join v_FacturasSeguro on s_PagoSeguro_Detalles.Tipo = v_FacturasSeguro.Tipo and s_PagoSeguro_Detalles.hpag_IdCaja = v_FacturasSeguro.hpag_IdCaja and s_PagoSeguro_Detalles.hpag_Numero = v_FacturasSeguro.hpag_Numero
	  WHERE pags_Numero= @Numero


set @Result =''


Declare @EstatusAct as char(1), @FechaPago as date, @IdTransCnt as Integer, @NumeroReclamacion as integer, @NumCobroSin as integer, @NumNcrSin as integer
	Select @EstatusAct = pags_Estatus, @FechaPago= pags_Fecha, @NumeroReclamacion= IsNull(pags_NumeroReclamacion,0), @NumCobroSin = IsNull(NumCobroSin,0), @NumNcrSin=IsNull(NumNcrSin,0)  from s_PagoSeguro where pags_Numero=@Numero
	
if @EstatusAct='N' 
Begin

	-- 2022-05-19 Tomar en cuenta el monto debito de los recibos
	-- Validar si las facturas estan conrrectas
	OPEN s_HeaderPagosConProblemas_Cursor 

	FETCH NEXT FROM s_HeaderPagosConProblemas_Cursor into @Tipo, @hpag_IdCaja, @hpag_Numero, @MontoAAplicar,@MontoPendiente
	WHILE @@FETCH_STATUS = 0 
	BEGIN 
		set @Result = @Result + 'Para el recibo Caja: ' + @hpag_IdCaja + ' #:'+  Cast(@hpag_Numero as varchar) + ', Tipo:' +  @Tipo + ' se sobrepasa el monto pendiente: ' + CAST(@MontoPendiente as varchar) + '. A Aplicar: ' + Cast(@MontoAAplicar as varchar) + '<br>'
		FETCH NEXT FROM s_HeaderPagosConProblemas_Cursor into @Tipo ,@hpag_IdCaja, @hpag_Numero, @MontoAAplicar,@MontoPendiente
	END 
	CLOSE s_HeaderPagosConProblemas_Cursor 
	DEALLOCATE s_HeaderPagosConProblemas_Cursor 

	if IsNull(@Result,'')=''
	Begin
		-- Actualizar el balance pagado de las coberturas
		Update s_HeaderPago set hpag_MontoPagado = IsNull(hpag_MontoPagado,0.0) + s_PagoSeguro_Detalles.Monto,
		                        hpag_FechaSaldo = Case when Round(s_HeaderPago.hpag_CoberturaSeguro + IsNull(s_HeaderPago.hpag_MontoDebito,0.0) - IsNull(hpag_MontoPagado,0.0) - s_PagoSeguro_Detalles.Monto,2) <=0 then @FechaPago else NULL end
		from s_PagoSeguro_Detalles 
		Inner join s_HeaderPago on s_PagoSeguro_Detalles.hpag_IdCaja = s_HeaderPago.hpag_IdCaja and s_PagoSeguro_Detalles.hpag_Numero = s_HeaderPago.hpag_Numero
		where s_PagoSeguro_Detalles.Tipo='COB' and s_PagoSeguro_Detalles.pags_Numero= @Numero
		
		-- actualizar el balance de los descuentos
		Update s_HeaderPago_Descuento set hpag_MontoPagado = IsNull(hpag_MontoPagado,0.0) + s_PagoSeguro_Detalles.Monto,
		                        hpag_FechaSaldo = Case when Round(s_HeaderPago_Descuento.hpag_Descuento - IsNull(hpag_MontoPagado,0.0) - s_PagoSeguro_Detalles.Monto,2) <=0 then @FechaPago else NULL end
		from s_PagoSeguro_Detalles 
		Inner join s_HeaderPago_Descuento on s_PagoSeguro_Detalles.hpag_IdCaja = s_HeaderPago_Descuento.hpag_IdCaja and s_PagoSeguro_Detalles.hpag_Numero = s_HeaderPago_Descuento.hpag_Numero
		where s_PagoSeguro_Detalles.Tipo='DSC' and s_PagoSeguro_Detalles.pags_Numero= @Numero
		
		
		-- Guardar el monto pendiente de las s_HeaderPagos en el detale del s_PagoSeguro
		Update s_PagoSeguro_Detalles set PendienteRec=IsNull(v_FacturasSeguro.hpag_CoberturaSeguro,0.0) + IsNull(v_FacturasSeguro.hpag_MontoDebito,0.0) - IsNull(v_FacturasSeguro.hpag_MontoPagado,0.0), 
		       Saldo=case when IsNull(v_FacturasSeguro.hpag_CoberturaSeguro,0.0) + IsNull(v_FacturasSeguro.hpag_MontoDebito,0.0) - IsNull(v_FacturasSeguro.hpag_MontoPagado,0.0)=0 then 1 else 0 end
		from s_PagoSeguro_Detalles
		inner Join v_FacturasSeguro on s_PagoSeguro_Detalles.Tipo = v_FacturasSeguro.Tipo and s_PagoSeguro_Detalles.hpag_IdCaja = v_FacturasSeguro.hpag_IdCaja and s_PagoSeguro_Detalles.hpag_Numero = v_FacturasSeguro.hpag_Numero
	    where s_PagoSeguro_Detalles.pags_Numero= @Numero

			
		-- actualizar el pags_Estatus del s_PagoSeguro
		Update s_PagoSeguro set pags_Estatus='P'
		where pags_Numero=@Numero

		-- Actualizar el estatus a las reclamacion
		OPEN Reclamaciones_Cursor 

		FETCH NEXT FROM Reclamaciones_Cursor into @NumeroReclamacion
		WHILE @@FETCH_STATUS = 0 
		BEGIN 
			if @NumeroReclamacion>0 
				exec sp_ActualizaEstatusReclamacion @NumeroReclamacion, @Result OUTPUT

			FETCH NEXT FROM Reclamaciones_Cursor into @NumeroReclamacion
		END 
		CLOSE Reclamaciones_Cursor 
		DEALLOCATE Reclamaciones_Cursor 
  end

end
GO

-- Procedimiento: sp_QuitarProcNotaCreditoSeguro
CREATE procedure [dbo].[sp_QuitarProcNotaCreditoSeguro] (
 @Numero int,
 @IdUsuario varchar(15),
  @Result VARCHAR(900) OUTPUT )
as


DECLARE Reclamaciones_Cursor CURSOR LOCAL FOR
	Select distinct v_FacturasSeguro.hpag_NumeroReclamacionSeguro
	  FROM s_NotaCreditoSeguro_Detalles 
     Left outer Join v_FacturasSeguro on s_NotaCreditoSeguro_Detalles.Tipo = v_FacturasSeguro.Tipo and s_NotaCreditoSeguro_Detalles.hpag_IdCaja = v_FacturasSeguro.hpag_IdCaja and s_NotaCreditoSeguro_Detalles.hpag_Numero = v_FacturasSeguro.hpag_Numero
	  WHERE Numero= @Numero

set @Result =''

Declare @EstatusAct as char(1), @FechaPago as date,  @NumNcrSin as integer, @NumeroReclamacion as integer
	Select @EstatusAct = Estatus, @FechaPago=Fecha, @NumNcrSin=IsNull(NumNcrSin,0) from s_NotaCreditoSeguro where Numero=@Numero
	
if @EstatusAct='P' 
Begin
		-- Actualizar el balance pagado de las coberturas
		Update s_HeaderPago set hpag_MontoPagado = IsNull(hpag_MontoPagado,0.0) - s_NotaCreditoSeguro_Detalles.Monto,
		                        hpag_FechaSaldo = Case when Round(s_HeaderPago.hpag_CoberturaSeguro - IsNull(hpag_MontoPagado,0.0) + s_NotaCreditoSeguro_Detalles.Monto,2) <=0 then @FechaPago else NULL end
		from s_NotaCreditoSeguro_Detalles 
		Inner join s_HeaderPago on s_NotaCreditoSeguro_Detalles.hpag_IdCaja = s_HeaderPago.hpag_IdCaja and s_NotaCreditoSeguro_Detalles.hpag_Numero = s_HeaderPago.hpag_Numero
		where s_NotaCreditoSeguro_Detalles.Tipo='COB' and s_NotaCreditoSeguro_Detalles.Numero= @Numero
		

	
		-- actualizar el balance de los descuentos
		Update s_HeaderPago_Descuento set hpag_MontoPagado = IsNull(hpag_MontoPagado,0.0) - s_NotaCreditoSeguro_Detalles.Monto,
		                        hpag_FechaSaldo = Case when Round(s_HeaderPago_Descuento.hpag_Descuento - IsNull(hpag_MontoPagado,0.0) + s_NotaCreditoSeguro_Detalles.Monto,2) <=0 then @FechaPago else NULL end
		from s_NotaCreditoSeguro_Detalles 
		Inner join s_HeaderPago_Descuento on s_NotaCreditoSeguro_Detalles.hpag_IdCaja = s_HeaderPago_Descuento.hpag_IdCaja and s_NotaCreditoSeguro_Detalles.hpag_Numero = s_HeaderPago_Descuento.hpag_Numero
		where s_NotaCreditoSeguro_Detalles.Tipo='DSC' and s_NotaCreditoSeguro_Detalles.Numero= @Numero
		

	
		-- Quitar el monto pendiente de las facturas en el detalle del pago
		Update s_NotaCreditoSeguro_Detalles set PendienteRec=IsNull(v_FacturasSeguro.hpag_CoberturaSeguro,0.0) + IsNull(v_FacturasSeguro.hpag_MontoDebito,0.0) - IsNull(v_FacturasSeguro.hpag_MontoPagado,0.0), Saldo=case when IsNull(v_FacturasSeguro.hpag_CoberturaSeguro,0.0) + IsNull(v_FacturasSeguro.hpag_MontoDebito,0.0) - IsNull(v_FacturasSeguro.hpag_MontoPagado,0.0)=0 then 1 else 0 end
		from s_NotaCreditoSeguro_Detalles
		inner Join v_FacturasSeguro on s_NotaCreditoSeguro_Detalles.Tipo = v_FacturasSeguro.Tipo and s_NotaCreditoSeguro_Detalles.hpag_IdCaja = v_FacturasSeguro.hpag_IdCaja and s_NotaCreditoSeguro_Detalles.hpag_Numero = v_FacturasSeguro.hpag_Numero
	    where s_NotaCreditoSeguro_Detalles.Numero= @Numero

		
		-- actualizar el estatus del cobro
		Update s_NotaCreditoSeguro set Estatus='N' where Numero=@Numero

			-- Actualizar el estatus a las reclamacion
		OPEN Reclamaciones_Cursor 

		FETCH NEXT FROM Reclamaciones_Cursor into @NumeroReclamacion
		WHILE @@FETCH_STATUS = 0 
		BEGIN 
			if @NumeroReclamacion>0 
				exec sp_ActualizaEstatusReclamacion @NumeroReclamacion, @Result OUTPUT

			FETCH NEXT FROM Reclamaciones_Cursor into @NumeroReclamacion
		END 
		CLOSE Reclamaciones_Cursor 
		DEALLOCATE Reclamaciones_Cursor 
end
GO

-- Procedimiento: sp_QuitarProcNotaDebitoSeguro
CREATE procedure [dbo].[sp_QuitarProcNotaDebitoSeguro] (
 @Numero int,
 @IdUsuario varchar(15),
  @Result VARCHAR(900) OUTPUT )
as


DECLARE Reclamaciones_Cursor CURSOR LOCAL FOR
	Select distinct v_FacturasSeguro.hpag_NumeroReclamacionSeguro
	  FROM s_NotaDebitoSeguro_Detalles 
     Left outer Join v_FacturasSeguro on s_NotaDebitoSeguro_Detalles.Tipo = v_FacturasSeguro.Tipo and s_NotaDebitoSeguro_Detalles.hpag_IdCaja = v_FacturasSeguro.hpag_IdCaja and s_NotaDebitoSeguro_Detalles.hpag_Numero = v_FacturasSeguro.hpag_Numero
	  WHERE Numero= @Numero

set @Result =''

Declare @EstatusAct as char(1), @FechaPago as date,  @NumeroReclamacion as integer
	Select @EstatusAct = Estatus, @FechaPago=Fecha  from s_NotaDebitoSeguro where Numero=@Numero
	
if @EstatusAct='P' 
Begin
		-- Actualizar el balance debito de las coberturas
		Update s_HeaderPago set hpag_MontoDebito = IsNull(hpag_MontoDebito,0.0) - s_NotaDebitoSeguro_Detalles.Monto,
		                        hpag_FechaSaldo = Case when Round(s_HeaderPago.hpag_CoberturaSeguro - IsNull(hpag_MontoPagado,0.0) + s_NotaDebitoSeguro_Detalles.Monto,2) <=0 then @FechaPago else NULL end
		from s_NotaDebitoSeguro_Detalles 
		Inner join s_HeaderPago on s_NotaDebitoSeguro_Detalles.hpag_IdCaja = s_HeaderPago.hpag_IdCaja and s_NotaDebitoSeguro_Detalles.hpag_Numero = s_HeaderPago.hpag_Numero
		where s_NotaDebitoSeguro_Detalles.Tipo='COB' and s_NotaDebitoSeguro_Detalles.Numero= @Numero
		
			
	
		-- Quitar el monto pendiente de las facturas en el detalle del pago
		Update s_NotaDebitoSeguro_Detalles set PendienteRec=IsNull(v_FacturasSeguro.hpag_CoberturaSeguro,0.0) + IsNull(v_FacturasSeguro.hpag_MontoDebito,0.0) - IsNull(v_FacturasSeguro.hpag_MontoPagado,0.0)
		from s_NotaDebitoSeguro_Detalles
		inner Join v_FacturasSeguro on s_NotaDebitoSeguro_Detalles.Tipo = v_FacturasSeguro.Tipo and s_NotaDebitoSeguro_Detalles.hpag_IdCaja = v_FacturasSeguro.hpag_IdCaja and s_NotaDebitoSeguro_Detalles.hpag_Numero = v_FacturasSeguro.hpag_Numero
	    where s_NotaDebitoSeguro_Detalles.Numero= @Numero

		
		-- actualizar el estatus del cobro
		Update s_NotaDebitoSeguro set Estatus='N' where Numero=@Numero

			-- Actualizar el estatus a las reclamacion
		OPEN Reclamaciones_Cursor 

		FETCH NEXT FROM Reclamaciones_Cursor into @NumeroReclamacion
		WHILE @@FETCH_STATUS = 0 
		BEGIN 
			if @NumeroReclamacion>0 
				exec sp_ActualizaEstatusReclamacion @NumeroReclamacion, @Result OUTPUT

			FETCH NEXT FROM Reclamaciones_Cursor into @NumeroReclamacion
		END 
		CLOSE Reclamaciones_Cursor 
		DEALLOCATE Reclamaciones_Cursor 
end
GO

-- Procedimiento: sp_QuitarProcPagoSeguro
CREATE procedure [dbo].[sp_QuitarProcPagoSeguro] (
 @Numero int,
 @IdUsuario varchar(15),
  @Result VARCHAR(900) OUTPUT )
as


DECLARE Reclamaciones_Cursor CURSOR LOCAL FOR
	Select distinct v_FacturasSeguro.hpag_NumeroReclamacionSeguro
	  FROM s_PagoSeguro_Detalles 
     Left outer Join v_FacturasSeguro on s_PagoSeguro_Detalles.Tipo = v_FacturasSeguro.Tipo and s_PagoSeguro_Detalles.hpag_IdCaja = v_FacturasSeguro.hpag_IdCaja and s_PagoSeguro_Detalles.hpag_Numero = v_FacturasSeguro.hpag_Numero
	  WHERE pags_Numero= @Numero

set @Result =''

Declare @EstatusAct as char(1), @FechaPago as date,  @NumeroReclamacion as integer, @NumCobroSin as integer, @NumNcrSin as integer
	Select @EstatusAct = pags_Estatus, @FechaPago= pags_Fecha, @NumeroReclamacion= IsNull(pags_NumeroReclamacion,0), @NumCobroSin = IsNull(NumCobroSin,0), @NumNcrSin=IsNull(NumNcrSin,0) from s_PagoSeguro where pags_Numero=@Numero
	
if @EstatusAct='P' 
Begin
		-- Actualizar el balance pagado de las coberturas
		Update s_HeaderPago set hpag_MontoPagado = IsNull(hpag_MontoPagado,0.0) - s_PagoSeguro_Detalles.Monto,
		                        hpag_FechaSaldo = Case when Round(s_HeaderPago.hpag_CoberturaSeguro - IsNull(hpag_MontoPagado,0.0) + s_PagoSeguro_Detalles.Monto,2) <=0 then @FechaPago else NULL end
		from s_PagoSeguro_Detalles 
		Inner join s_HeaderPago on s_PagoSeguro_Detalles.hpag_IdCaja = s_HeaderPago.hpag_IdCaja and s_PagoSeguro_Detalles.hpag_Numero = s_HeaderPago.hpag_Numero
		where s_PagoSeguro_Detalles.Tipo='COB' and s_PagoSeguro_Detalles.pags_Numero= @Numero
		

	
		-- actualizar el balance de los descuentos
		Update s_HeaderPago_Descuento set hpag_MontoPagado = IsNull(hpag_MontoPagado,0.0) - s_PagoSeguro_Detalles.Monto,
		                        hpag_FechaSaldo = Case when Round(s_HeaderPago_Descuento.hpag_Descuento - IsNull(hpag_MontoPagado,0.0) + s_PagoSeguro_Detalles.Monto,2) <=0 then @FechaPago else NULL end
		from s_PagoSeguro_Detalles 
		Inner join s_HeaderPago_Descuento on s_PagoSeguro_Detalles.hpag_IdCaja = s_HeaderPago_Descuento.hpag_IdCaja and s_PagoSeguro_Detalles.hpag_Numero = s_HeaderPago_Descuento.hpag_Numero
		where s_PagoSeguro_Detalles.Tipo='DSC' and s_PagoSeguro_Detalles.pags_Numero= @Numero
		

	
		-- Quitar el monto pendiente de las facturas en el detalle del pago
		Update s_PagoSeguro_Detalles set PendienteRec=IsNull(v_FacturasSeguro.hpag_CoberturaSeguro,0.0) - IsNull(v_FacturasSeguro.hpag_MontoPagado,0.0), Saldo=case when IsNull(v_FacturasSeguro.hpag_CoberturaSeguro,0.0) - IsNull(v_FacturasSeguro.hpag_MontoPagado,0.0)=0 then 1 else 0 end
		from s_PagoSeguro_Detalles
		inner Join v_FacturasSeguro on s_PagoSeguro_Detalles.Tipo = v_FacturasSeguro.Tipo and s_PagoSeguro_Detalles.hpag_IdCaja = v_FacturasSeguro.hpag_IdCaja and s_PagoSeguro_Detalles.hpag_Numero = v_FacturasSeguro.hpag_Numero
	    where s_PagoSeguro_Detalles.pags_Numero= @Numero

		
		-- actualizar el estatus del cobro
		Update s_PagoSeguro set pags_Estatus='N' where pags_Numero=@Numero

			-- Actualizar el estatus a las reclamacion
		OPEN Reclamaciones_Cursor 

		FETCH NEXT FROM Reclamaciones_Cursor into @NumeroReclamacion
		WHILE @@FETCH_STATUS = 0 
		BEGIN 
			if @NumeroReclamacion>0 
				exec sp_ActualizaEstatusReclamacion @NumeroReclamacion, @Result OUTPUT

			FETCH NEXT FROM Reclamaciones_Cursor into @NumeroReclamacion
		END 
		CLOSE Reclamaciones_Cursor 
		DEALLOCATE Reclamaciones_Cursor 
end
GO

-- Procedimiento: sp_RegInicioConsulta
CREATE procedure sp_RegInicioConsulta(@IdUsuario as varchar(15) )
as

-- Si el usuario es un doctor, se registra el inicio de la consulta en la tabla disponbilidad
Declare @IdDoctor as varchar(15), @Orden as integer, @Hora as varchar(6), @Fecha as date

Select @IdDoctor=usud_IdDoctor from s_Usuario 
Inner Join s_UsuarioDoctores on s_usuario.usua_IdUsuario = s_UsuarioDoctores.usud_IdUsuario
where usua_EsDoctor=1 and usud_usarPordefecto= 1 and usua_IdUsuario = @IdUsuario

if @IdDoctor is not null 
Begin

	 Select @Hora=Lower(Substring(convert(varchar(20),getdate(),100),13,6))
	 Select @Orden=IsNull(Max(Orden),0) from s_Doctor_Disponibilidad where Fecha =cast(getdate() as date)

	 -- Crear la lista si no existe
	 set @Fecha = Cast(getdate() as date)
	 exec sp_CreaListaDispDoctores  @Fecha

	-- Insertar disponbilidad
	if not exists (select 1 from s_Doctor_Disponibilidad where IdDoctor = @IdDoctor and Fecha =cast(getdate() as date) )
	begin
		-- Registrar la disponibilidad del doctor
		 
	  Insert into s_Doctor_Disponibilidad(Fecha,IdDoctor, EstadoConsulta, HoraIniConsulta, Orden)
	  values(cast(getdate() as date), @IdDoctor, 'A', @Hora, @Orden+1)
	end
	else
	Begin
		-- Actualizar la hora de inicio de la consulta, si no se ha indicado
		Update s_Doctor_Disponibilidad set HoraIniConsulta=@Hora, Orden = @Orden + 1 where IdDoctor=@IdDoctor and Fecha=cast(getdate() as date) and Orden is Null
	End
end
GO

-- Procedimiento: sp_RenumerarDisponibilidad
CREATE Procedure sp_RenumerarDisponibilidad(@Fecha as date)
as
	Update s_Doctor_Disponibilidad set Orden= SEC.Row#
	from s_Doctor_Disponibilidad 
	Inner join (SELECT  ROW_NUMBER() OVER(ORDER BY Orden ASC) AS Row#, Orden FROM s_Doctor_Disponibilidad where fecha=@Fecha) SEC on s_Doctor_Disponibilidad.Orden= SEC.Orden
     where fecha=@Fecha
GO

-- Procedimiento: sp_RepCompFiscal
CREATE  procedure [dbo].[sp_RepCompFiscal](@FecIni date, @FecFin date, @IdSecCF varchar(10))
as

 SELECT expe_Nombres, expe_Apellidos, expe_Cedula, hpag_Numero, hpag_Fecha, isNull(hpag_NumeroExpediente,0) as hpag_NumeroExpediente, hpag_Nombre, hpag_Apellido, hpag_Monto, hpag_CoberturaSeguro, 
    hpag_IdCaja, hpag_Estatus, hpag_TipoPago, Descripcion, hpag_IdSecCF, hpag_NCF, hpag_ValorRecibidoCheque, hpag_ValorRecibidoTarjeta, IdTipoCF, ticf_Nombre
 FROM   s_HeaderPago
 LEFT OUTER JOIN s_Expediente ON s_HeaderPago.hpag_NumeroExpediente=s_Expediente.expe_NumeroExpediente
 LEFT OUTER JOIN s_SecuenciaCF   ON s_HeaderPago.hpag_IdSecCF COLLATE DATABASE_DEFAULT =s_SecuenciaCF.Id COLLATE DATABASE_DEFAULT 
 LEFT OUTER JOIN s_TipoComprobanteFiscal ON s_SecuenciaCF.IdTipoCF COLLATE DATABASE_DEFAULT =s_TipoComprobanteFiscal.ticf_IdTipoCF COLLATE DATABASE_DEFAULT 
 where cast(hpag_Fecha as date) >=@FecIni and cast(hpag_Fecha as date)<=@FecFin and --Rtrim(IsNull(hpag_NCF,''))<>'' and
    (hpag_IdSecCF=@IdSecCF or rtrim(@IdSecCF)='')
 ORDER BY s_SecuenciaCF.IdTipoCF, s_HeaderPago.hpag_NCF
GO

-- Procedimiento: sp_ViewAsistenciaPorTanda
CREATE PROCEDURE [dbo].[sp_ViewAsistenciaPorTanda]
	-- Add the parameters for the stored procedure here
	@FecIni as datetime,
	@FecFin as datetime,
	@DiaSem as int
AS
BEGIN
	    Select doct_nombre, IsNull(s_Tanda.NomTanda,'[ No especificada ]') as NomTanda, Count(*) as cantidad from s_ListaPaciente 
		left outer join s_Tanda on  CONVERT(datetime, s_ListaPaciente.lisp_horallegada+'m',120)>= CONVERT(datetime, s_Tanda.HorIni+'m',120)  and  CONVERT(datetime, s_ListaPaciente.lisp_horallegada+'m',120)<= CONVERT(datetime, s_Tanda.HorFin+'m',120) 
		left outer join s_Doctor on s_listaPaciente.lisp_IdDoctor = s_Doctor.doct_IdDoctor
		where s_ListaPaciente.lisp_Fecha >= @FecIni and s_listaPaciente.lisp_Fecha <=@FecFin
		 and (datepart(weekday,s_ListaPaciente.lisp_Fecha) = @DiaSem or @DiaSem=0)
		group by doct_nombre, s_Tanda.NomTanda
END
GO

-- Procedimiento: sp_ViewCitasSinAsistencia
CREATE procedure [dbo].[sp_ViewCitasSinAsistencia] @FecIni Datetime, @FecFin dateTime, @IdDoctor varchar(15), @RangoDias Int as
Select cita_IdDoctor, cita_Fecha, Cita_Hora,cita_HoraNumero, Cita_NumeroExpediente, cita_Nombre, cita_Apellido, cita_Telefono, cita_Comentario, cita_idSeguro,
min(doct_Nombre) as doct_nombre, Min(s_Expediente.expe_Nombres) as expe_Nombres,
Min(s_Expediente.expe_Apellidos) as expe_Apellidos, Min(segu_Nombre) as segu_Nombre,
min(s_Expediente.expe_TelefonoCasa) as expe_TelefonoCasa, Min(s_Expediente.expe_Celular) as Expe_Celular,
Max(visi_FechaVisita) as UltVisita , Abs(datediff(d,cita_Fecha,Max(visi_FechaVisita))) as DiasDif
from s_cita
Inner join s_Visita on s_cita.cita_NumeroExpediente = s_visita.visi_numeroExpediente 
and s_cita.cita_Fecha >= s_Visita.visi_FechaVisita
Left outer join s_Doctor on s_Cita.cita_IdDoctor = s_Doctor.doct_IdDoctor
left outer join s_Expediente on s_Cita.cita_NumeroExpediente = s_Expediente.expe_NumeroExpediente
left outer join s_Seguro on s_Cita.cita_idSeguro=s_Seguro.segu_IdSeguro
where cita_Fecha >= @FecIni and cita_Fecha <=@FecFin and 
(rtrim(@IdDoctor)='' or cita_IdDoctor=@IdDoctor)
Group by cita_IdDoctor, cita_Fecha, Cita_Hora,cita_HoraNumero, Cita_NumeroExpediente, cita_Nombre, cita_Apellido, cita_Telefono, cita_Comentario, cita_idSeguro
Having  Abs(datediff(d,cita_Fecha,Max(visi_FechaVisita)))>=@RangoDias
GO

-- Procedimiento: sp_ViewSumarioCompraLente
CREATE PROCEDURE [dbo].[sp_ViewSumarioCompraLente]
	-- Add the parameters for the stored procedure here
	@AnoIni as int,
	@MesIni as int,
	@AnoFin as int,
	@MesFin as int,
	@IdDoctor as varchar
AS
BEGIN

		if rtrim(@idDoctor)='' 
		Begin
		   Select IsNull(R.Ano,C.Ano) as Ano, IsNull(R.Mes,C.Mes) as Mes, @IdDoctor as IdDoctor, IsNull(R.CantRecetado,0) as CantRecetado, IsNull(C.CantComprado,0) as CantComprado, IsNull(C.TotalComprado,0) as TotalComprado, 
			  Case when IsNull(R.CantRecetado,0)>0 then round(CAST(IsNull(C.CantComprado,0) as float) / CAST(R.CantRecetado as float) * 100,2) else 0.0 end as PorcCompra
			from (
			SELECT    @IdDoctor as IdDoctor, YEAR(dbo.s_Visita.visi_fechaVisita) AS Ano, MONTH(dbo.s_Visita.visi_fechaVisita) AS Mes,                           
										  COUNT(dbo.s_Visita.visi_fechaVisita) AS CantRecetado             
	 			FROM         dbo.s_Visita_Oftalmologia INNER JOIN
										  dbo.s_Visita ON dbo.s_Visita_Oftalmologia.NumVisita = dbo.s_Visita.NumVisita                             
					WHERE     ((dbo.s_Visita_Oftalmologia.rodeje <> 0) or(dbo.s_Visita_Oftalmologia.roieje <> 0)  or (s_Visita_Oftalmologia.rodesf<>0) or (s_Visita_Oftalmologia.roiesf<>0)) 

							  AND (dbo.s_Visita.visi_IdEspecialidad = 'OFT')
							  AND YEAR(dbo.s_Visita.visi_fechaVisita)*12 + MONTH(dbo.s_Visita.visi_fechaVisita) >=@AnoIni*12 + @MesIni                          
							  AND YEAR(dbo.s_Visita.visi_fechaVisita)*12 + MONTH(dbo.s_Visita.visi_fechaVisita) <=@AnoFin*12 + @MesFin                          
					GROUP BY YEAR(dbo.s_Visita.visi_fechaVisita), MONTH(dbo.s_Visita.visi_fechaVisita)                          
					
			) R 
			full outer join (		
			SELECT    @IdDoctor as IdDoctor, YEAR(FechaReceta) AS Ano, MONTH(FechaReceta) AS Mes, 
										  COUNT(FechaReceta) AS CantComprado, SUM(monto) as TotalComprado
				FROM         dbo.s_CompraLente 		
					WHERE     YEAR(FechaReceta)*12 + MONTH(FechaReceta) >=@AnoIni*12 + @MesIni
							  AND YEAR(FechaReceta)*12 + MONTH(FechaReceta) <=@AnoFin*12 + @MesFin
					GROUP BY YEAR(FechaReceta), MONTH(FechaReceta)
				
			) C on R.ano = C.Ano and R.Mes = C.Mes
		END
		ELSE
		BEGIN
			SELECT     YEAR(dbo.s_Visita.visi_fechaVisita) AS Ano, MONTH(dbo.s_Visita.visi_fechaVisita) AS Mes,                           
								  COUNT(dbo.s_Visita.visi_fechaVisita) AS CantRecetado, COUNT(dbo.s_CompraLente.Numero) AS CantComprado, SUM(IsNull(dbo.s_CompraLente.Monto,0.0))              
								  AS TotalComprado, ROUND(CAST(COUNT(dbo.s_CompraLente.Numero) AS float) / CAST(COUNT(dbo.s_Visita.visi_fechaVisita) AS float) * 100, 2)              
								  AS PorcCompra
			FROM         dbo.s_Visita_Oftalmologia INNER JOIN
								  dbo.s_Visita ON dbo.s_Visita_Oftalmologia.NumVisita = dbo.s_Visita.NumVisita                             
					    LEFT OUTER JOIN                                                                            
								  dbo.s_CompraLente ON dbo.s_Visita.visi_NumeroExpediente = dbo.s_CompraLente.NumeroExpediente AND              
								  dbo.s_Visita.visi_fechaVisita = dbo.s_CompraLente.FechaReceta             
			WHERE     ((dbo.s_Visita_Oftalmologia.rodeje <> 0) OR (dbo.s_Visita_Oftalmologia.roieje <> 0)  or (s_Visita_Oftalmologia.rodesf<>0) or (s_Visita_Oftalmologia.roiesf<>0)) AND (dbo.s_Visita.visi_IdEspecialidad = 'OFT') and
								  s_Visita.visi_IdDoctor=@IdDoctor and
								  YEAR(dbo.s_Visita.visi_fechaVisita)*12 + MONTH(dbo.s_Visita.visi_fechaVisita)>=@AnoIni*12+@MesIni AND                          
								  YEAR(dbo.s_Visita.visi_fechaVisita)*12 + MONTH(dbo.s_Visita.visi_fechaVisita)<=@AnoFin*12+@MesFin                          
			GROUP BY YEAR(dbo.s_Visita.visi_fechaVisita), MONTH(dbo.s_Visita.visi_fechaVisita);                          

		  Select IsNull(R.Ano,C.Ano) as Ano, IsNull(R.Mes,C.Mes) as Mes, @IdDoctor as IdDoctor, IsNull(R.CantRecetado,0) as CantRecetado, IsNull(C.CantComprado,0) as CantComprado, IsNull(C.TotalComprado,0) as TotalComprado, 
			  Case when IsNull(R.CantRecetado,0)>0 then round(CAST(IsNull(C.CantComprado,0) as float) / CAST(R.CantRecetado as float) * 100,2) else 0.0 end as PorcCompra
			from (
			SELECT    @IdDoctor as IdDoctor, YEAR(dbo.s_Visita.visi_fechaVisita) AS Ano, MONTH(dbo.s_Visita.visi_fechaVisita) AS Mes,                           
										  COUNT(dbo.s_Visita.visi_fechaVisita) AS CantRecetado             
	 			FROM         dbo.s_Visita_Oftalmologia INNER JOIN
										  dbo.s_Visita ON dbo.s_Visita_Oftalmologia.NumVisita = dbo.s_Visita.NumVisita                             

					WHERE     ((dbo.s_Visita_Oftalmologia.rodeje <> 0) or(dbo.s_Visita_Oftalmologia.roieje <> 0)) 
							  AND (dbo.s_Visita.visi_IdEspecialidad = 'OFT') and s_Visita.visi_IdDoctor=@IdDoctor
							  AND YEAR(dbo.s_Visita.visi_fechaVisita)*12 + MONTH(dbo.s_Visita.visi_fechaVisita) >=@AnoIni*12 + @MesIni                          
							  AND YEAR(dbo.s_Visita.visi_fechaVisita)*12 + MONTH(dbo.s_Visita.visi_fechaVisita) <=@AnoFin*12 + @MesFin                          
					GROUP BY YEAR(dbo.s_Visita.visi_fechaVisita), MONTH(dbo.s_Visita.visi_fechaVisita)                          
					
			) R 
			full outer join (		
			SELECT    @IdDoctor as IdDoctor, YEAR(FechaReceta) AS Ano, MONTH(FechaReceta) AS Mes, 
										  COUNT(FechaReceta) AS CantComprado, SUM(monto) as TotalComprado
				FROM         dbo.s_CompraLente 		
					WHERE     YEAR(FechaReceta)*12 + MONTH(FechaReceta) >=@AnoIni*12 + @MesIni
							  AND YEAR(FechaReceta)*12 + MONTH(FechaReceta) <=@AnoFin*12 + @MesFin
							  AND IdDoctor=@IdDoctor
					GROUP BY YEAR(FechaReceta), MONTH(FechaReceta)
				
			) C on R.ano = C.Ano and R.Mes = C.Mes
		END
		
	END
GO

-- Procedimiento: sp_ViewSumarioCompraLenteFecha
CREATE PROCEDURE [dbo].[sp_ViewSumarioCompraLenteFecha]
		-- Add the parameters for the stored procedure here
		@FecIni as datetime,
		@FecFin as datetime,
		@IdDoctor as varchar
	AS
	BEGIN

		if rtrim(@idDoctor)='' 
		Begin
		   Select IsNull(R.IdDoctor,C.IdDoctor) as IdDoctor, IsNull(R.CantRecetado,0) as CantRecetado, IsNull(C.CantComprado,0) as CantComprado, IsNull(C.TotalComprado,0) as TotalComprado, 
			  Case when IsNull(R.CantRecetado,0)>0 then round(CAST(IsNull(C.CantComprado,0) as float) / CAST(R.CantRecetado as float) * 100,2) else 0.0 end as PorcCompra
			from (
			SELECT    visi_IdDoctor as IdDoctor, COUNT(dbo.s_Visita.visi_fechaVisita) AS CantRecetado
	 			FROM         dbo.s_Visita_Oftalmologia INNER JOIN
										  dbo.s_Visita ON dbo.s_Visita_Oftalmologia.NumVisita = dbo.s_Visita.NumVisita 
					WHERE     ((dbo.s_Visita_Oftalmologia.rodeje <> 0) or(dbo.s_Visita_Oftalmologia.roieje <> 0) or (s_Visita_Oftalmologia.rodesf<>0) or (s_Visita_Oftalmologia.roiesf<>0)) 
							  AND (dbo.s_Visita.visi_IdEspecialidad = 'OFT')
							  AND dbo.s_Visita.visi_fechaVisita >=@FecIni AND dbo.s_Visita.visi_fechaVisita<=@FecFin
			GROUP BY visi_Iddoctor
					
			) R 
			full outer join (		
			SELECT  IdDoctor, COUNT(FechaReceta) AS CantComprado, SUM(monto) as TotalComprado
				FROM         dbo.s_CompraLente 		
					WHERE     FechaReceta >=@FecIni and FechaReceta <=@FecFin
					GROUP BY IdDoctor
				
			) C on R.IdDoctor = C.IdDoctor
		END
		ELSE
		BEGIN
			Select IsNull(R.IdDoctor,C.IdDoctor) as IdDoctor, IsNull(R.CantRecetado,0) as CantRecetado, IsNull(C.CantComprado,0) as CantComprado, IsNull(C.TotalComprado,0) as TotalComprado, 
			  Case when IsNull(R.CantRecetado,0)>0 then round(CAST(IsNull(C.CantComprado,0) as float) / CAST(R.CantRecetado as float) * 100,2) else 0.0 end as PorcCompra
			from (
			SELECT    visi_IdDoctor as IdDoctor, COUNT(dbo.s_Visita.visi_fechaVisita) AS CantRecetado
	 			FROM         dbo.s_Visita_Oftalmologia INNER JOIN
										  dbo.s_Visita ON dbo.s_Visita_Oftalmologia.NumVisita = dbo.s_Visita.NumVisita 
					WHERE     ((dbo.s_Visita_Oftalmologia.rodeje <> 0) or(dbo.s_Visita_Oftalmologia.roieje <> 0) or (s_Visita_Oftalmologia.rodesf<>0) or (s_Visita_Oftalmologia.roiesf<>0)) 
							  AND (dbo.s_Visita.visi_IdEspecialidad = 'OFT') AND visi_IdDoctor=@IdDoctor
							  AND dbo.s_Visita.visi_fechaVisita >=@FecIni AND dbo.s_Visita.visi_fechaVisita<=@FecFin
			GROUP BY visi_Iddoctor
					
			) R 
			full outer join (		
			SELECT  IdDoctor, COUNT(FechaReceta) AS CantComprado, SUM(monto) as TotalComprado
				FROM         dbo.s_CompraLente 		
					WHERE     FechaReceta >=@FecIni and FechaReceta <=@FecFin and IdDoctor=@IdDoctor
					GROUP BY IdDoctor
				
			) C on R.IdDoctor = C.IdDoctor
		end
	END
GO

-- Procedimiento: sp_ViewTotalesListaEspecial
CREATE PROCEDURE sp_ViewTotalesListaEspecial(@fecini  smalldatetime, @fecfin smallDateTime, @IdDoctor as char)  AS 
--Procedimiento que retorna los totales por servicio
Select lisp_IdlistaEspecial, lise_nombre, 'En Espera' as Condicion, count(*) as Cantidad, 0 as PromedioDiaEspera 
from s_ListaEspecialPacientes 
inner join s_ListaEspecial on s_ListaEspecialPacientes.lisp_IdListaEspecial = s_ListaEspecial.lise_IdLista
where lisp_Estatus='E'
Group by lisp_IdlistaEspecial, lise_nombre
union
Select lisp_IdListaEspecial, lise_nombre, 'Ejecutado' as Condicion, count(*) as Cantidad, avg(dateDiff(day,lisp_FechaRegistro, lisp_FechaSalida)) as PromedioDiasEspera 
from s_ListaEspecialPacientes 
inner join s_ListaEspecial on s_ListaEspecialPacientes.lisp_IdListaEspecial = s_ListaEspecial.lise_IdLista
where lisp_Estatus='F' and lisp_Fechasalida>=@fecini  and lisp_FechaSalida<=@fecfin
Group by lisp_idlistaEspecial,lise_nombre
Union
Select lisp_IdListaEspecial, lise_nombre, 'Registro' as Condicion, count(*) as Cantidad, 0 as promedioDiaEspera
from s_ListaEspecialPacientes 
inner join s_ListaEspecial on s_ListaEspecialPacientes.lisp_IdListaEspecial = s_ListaEspecial.lise_IdLista
where lisp_FechaRegistro>=@fecini  and lisp_FechaRegistro<=@fecfin
Group by lisp_idlistaEspecial, lise_nombre
Order by lisp_idlistaEspecial, Condicion
GO

-- Procedimiento: sp_ViewTotalSeguro
CREATE PROCEDURE [dbo].[sp_ViewTotalSeguro](@fecini  smalldatetime, @fecfin smallDateTime) AS

-- Muestra el total facturado y pagado de los seguros en un periodo
Select IdSeguro, Sum(CantFacturas) as CantFacturas, Sum(TotalFacturado) as TotalFacturado,
     Sum(CantPagos) as CantPagos, Sum(TotalPagado) as TotalPagado
from (
Select hpag_IdSeguro as IdSeguro, Count(hpag_CoberturaSeguro)as CantFacturas, Sum(hpag_CoberturaSeguro) as TotalFacturado, 0 as CantPagos, 0.0 as TotalPagado from s_headerPago
where hpag_IdSeguro<>'' and hpag_Estatus='P' and IsNull(hpag_CoberturaSeguro,0)>0 and hpag_fecha>=@fecini and hpag_Fecha<=@fecfin
Group by hpag_IdSeguro
union 
Select pags_IdSeguro, 0 as CantFacturas, 0.0 as TotalFacturado, count(pags_ValorPago) as CantPagos, sum(pags_ValorPago) as TotalPagado
from s_PagoSeguro
where pags_IdSeguro<>'' and pags_Estatus='P' and IsNull(pags_ValorPago,0)>0 and pags_fecha>=@fecini and pags_fecha<=@fecfin
Group by pags_IdSeguro) T
Group by T.IdSeguro
GO

-- Procedimiento: sp_ViewTotalServicio
CREATE PROCEDURE [dbo].[sp_ViewTotalServicio](@fecini  datetime, @fecfin DateTime, @IdSeguro as char(10), @IdDoctor as char(10), @IdCaja as char(10),  @Tipo as char(1), @IdEntidadRef as varchar(10), @RealizadoPor as varchar(15)) AS

--- Procedimiento que Retorna los Totales por Servicio
Select dpag_idConcepto, conc_Nombre, conc_Tipo, Min(usua_Nombre) as RealizadoPor,  min(segu_Nombre) as Segu_Nombre, min(entr_nombre) as entr_Nombre,
       count(*)  as Cantidad, sum(IsNull(dpag_monto,0.0)) as TotalMonto, sum(Isnull(dpag_CoberturaSeguro,0.0)) as TotalCobertura,
       Sum(CASE when s_HeaderPago.hpag_TipoPago='E' then IsNull(dpag_monto,0.0) - Isnull(dpag_CoberturaSeguro,0.0) else 0.0 end) as TotalEfectivo,
	   Sum(CASE when s_HeaderPago.hpag_TipoPago='C' then IsNull(dpag_monto,0.0) - Isnull(dpag_CoberturaSeguro,0.0) else 0.0 end) as TotalCheque,
	   Sum(CASE when s_HeaderPago.hpag_TipoPago='T' then IsNull(dpag_monto,0.0) - Isnull(dpag_CoberturaSeguro,0.0) else 0.0 end) as TotalTarjeta,
	   Sum(CASE when s_HeaderPago.hpag_TipoPago='D' then IsNull(dpag_monto,0.0) - Isnull(dpag_CoberturaSeguro,0.0) else 0.0 end) as TotalTransferencia,
	   Sum(CASE when s_HeaderPago.hpag_TipoPago='M' then IsNull(dpag_monto,0.0) - Isnull(dpag_CoberturaSeguro,0.0) else 0.0 end) as TotalMixto,
	    Sum(CASE when s_HeaderPago.hpag_TipoPago='P' then IsNull(dpag_monto,0.0) - Isnull(dpag_CoberturaSeguro,0.0) else 0.0 end) as TotalPendiente,
	     Sum(CASE when s_HeaderPago.hpag_TipoPago='R' then IsNull(dpag_monto,0.0) - Isnull(dpag_CoberturaSeguro,0.0) else 0.0 end) as TotalCredito
from s_DetallePago
inner join s_HeaderPago on s_detallePago.dpag_idCaja = s_headerPago.hpag_IdCaja and s_DetallePago.dpag_Numero= s_HeaderPago.hpag_numero
Left outer join s_ConceptoPago on s_DetallePago.dpag_IdConcepto = s_ConceptoPago.conc_IdConcepto
Left outer join s_Usuario on s_DetallePago.dpag_RealizadoPor = s_Usuario.usua_IdUsuario
left outer join s_Seguro on s_HeaderPago.hpag_IdSeguro = s_seguro.segu_IdSeguro
Left outer join s_EntidadReferimiento on s_HeaderPago.hpag_IdEntidadRef = s_EntidadReferimiento.entr_IdEntidad
where cast(hpag_Fecha as date)>=@fecini and cast(hpag_Fecha as date)<=@fecfin and hpag_Estatus='P' and (hpag_IdDoctor=@IdDoctor or rtrim(@IdDoctor)='') and 
 (hpag_IdSeguro=@IdSeguro or rtrim(@IdSeguro)='') and
(Conc_Tipo=@Tipo or rtrim(@Tipo)='') and (hpag_IdCaja =@IdCaja or rtrim(@IdCaja)='') and (dpag_RealizadoPor =@RealizadoPor or rtrim(@RealizadoPor)='') 
 and (hpag_IdEntidadRef =@IdEntidadRef or rtrim(@IdEntidadRef)='')    
Group by dpag_idConcepto, conc_Nombre, conc_Tipo
GO

