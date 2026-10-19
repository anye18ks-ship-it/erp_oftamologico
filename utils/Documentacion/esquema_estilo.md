# 👁️ SISTEMA DE DISEÑO & GUÍA MAESTRA DE ESTILO (UI / UX)
## MICRO-ERP VERTICAL SAAS: SALUD VISUAL & OFTALMOLOGÍA
### Regla Estricta y Vinculante para Creación de Pantallas, Componentes y Gráficos

> **DIRECTRIZ SUPREMA PARA EL AGENTE Y DESARROLLADORES:**
> Este documento define el estándar de interfaz de usuario (UI), experiencia de usuario (UX), psicología del color, arquitectura CSS, sistemas tipográficos y responsividad para el nuevo **ERP Oftalmológico**.
> **Toda pantalla, vista, modal, tabla, gráfica o estado vacío desarrollado en este proyecto DEBE cumplir al 100% con las reglas, tokens y especificaciones aquí establecidas. Queda estrictamente prohibido generar interfaces genéricas, colores saturados estridentes, contrastes forzados que cansen la vista o componentes estáticos sin vida.**

---

# 1. Manifiesto de Diseño y Ergonomía Visual Oftalmológica

El personal médico, optómetras, enfermeros quirúrgicos y cajeros administrativos pasan entre **8 y 12 horas diarias continuas** interactuando con esta plataforma. En una clínica oftalmológica, la fatiga visual (**astenia visual** / *Computer Vision Syndrome*) es inaceptable.

### 1.1 La Regla de Oro: Prohibición de `#000000` y `#FFFFFF` Puros
* **En Modo Claro (Light Mode):**
  - **NUNCA** utilizar fondos `#FFFFFF` puros combinados con textos `#000000` puros. Este contraste extremo (21:1) genera reflectancia lumínica directa en la retina (*glare*), provocando cefaleas y contracción pupilar excesiva.
  - El fondo de la pantalla (*Canvas*) debe ser un blanco cálido clínico atenuado: **`#F8FAFC`** (Slate 50) o **`#F1F5F9`** (Slate 100).
  - El texto primario debe ser un grafito profundo y sereno: **`#0F172A`** (Slate 900) o **`#1E293B`** (Slate 800), logrando un contraste perfecto de ~14:1 (Clase AAA WCAG) sin deslumbrar.
* **En Modo Oscuro (Dark Mode):**
  - **NUNCA** utilizar fondos `#000000` absoluto estilo OLED puro para trabajo operativo prolongado, ya que el texto brillante sobre fondo negro absoluto produce el efecto óptico de **halación** (las letras parecen flotar con un halo borroso debido a la aberración esférica del cristalino del ojo dilatado).
  - El fondo de lienzo oscuro debe ser un obsidiana-azulado profundo y absorbente: **`#0B0F17`** (Navy Obsidian) con superficies elevadas en **`#111827`** (Gray 900) y tarjetas en **`#1E293B`** (Slate 800).
  - El texto en modo oscuro debe ser blanco perla atenuado: **`#F1F5F9`** (Slate 100) y secundarios en **`#94A3B8`** (Slate 400).

### 1.2 Psicología Cromática Clínica: Salud Visual, Confianza y Calma
1. **Bio-Teal & Cirugía Refractiva (Color Primario Clínico):**
   - Tonos: `#0D9488` (Teal 600) / `#14B8A6` (Teal 500) / `#042F2E` (Deep Teal).
   - *Fundamento Óptico:* En quirófanos y consultas oftalmológicas, el verde-azulado es el color complementario al rojo vascular de la retina y la córnea. Relaja los fotorreceptores (conos y bastones), reduce el estrés del paciente y transmite máxima asepsia e innovación tecnológica (láser excímer, OCT, facoemulsificación).
2. **Deep Sapphire & Royal Navy (Color Secundario / Estabilidad Financiera):**
   - Tonos: `#1E3A8A` (Blue 900) / `#2563EB` (Blue 600) / `#3B82F6` (Blue 500).
   - *Fundamento Psicológico:* Transmite rigor contable, seriedad corporativa y precisión en cobros, liquidaciones de seguros ARS y control de inventarios.
3. **Coral Suave / Rojo Médico (Alertas & Rechazos):**
   - Tonos: `#EF4444` (Red 500) / `#991B1B` (Red 800).
   - Diseñado para situaciones de alta prioridad: glaucoma descompensado, glosa rechazada por ARS, descuadre de caja o stock agotado.
4. **Ámbar Clínico (Advertencia & En Proceso):**
   - Tonos: `#F59E0B` (Amber 500) / `#D97706` (Amber 600).
   - Para estados pendientes de autorización, cheques por conciliar o citas próximas.
5. **Esmeralda Suave (Éxito & Salud):**
   - Tonos: `#10B981` (Emerald 500) / `#059669` (Emerald 600).
   - Para agudeza visual 20/20 alcanzada, cobro asentado, e-CF aceptado por DGII y turno cerrado cuadrado.

---

# 2. Diccionario de Tokens y Variables CSS Nativas

El sistema implementa arquitectura **CSS nativa (Variables CSS)** soportada tanto con Tailwind CSS como con Vanilla CSS puro. El switch entre modo claro y oscuro es fluido mediante el atributo `data-theme="light"` o `data-theme="dark"`.

```css
:root,
[data-theme="light"] {
  /* Lienzo y Superficies (Light Mode Ergonomico) */
  --bg-canvas: #f8fafc;           /* Fondo principal de la ventana (Slate 50) */
  --bg-surface: #ffffff;          /* Tarjetas, paneles principales y modales */
  --bg-surface-elevated: #ffffff; /* Dropdowns, popovers flotantes */
  --bg-surface-subtle: #f1f5f9;   /* Pozos, inputs secundarios, fondos de tabs */
  --bg-surface-hover: #e2e8f0;    /* Filas de tablas en hover, botones ghost */

  /* Bordes y Separadores */
  --border-subtle: #e2e8f0;       /* Separadores de sección, líneas de tabla */
  --border-default: #cbd5e1;      /* Bordes de inputs, cards en reposo */
  --border-focus: #0d9488;        /* Borde en foco primario */

  /* Textos y Contrastes */
  --text-primary: #0f172a;        /* Titulares H1-H6, datos clave (Slate 900) */
  --text-secondary: #334155;      /* Cuerpo de texto, valores de formulario (Slate 700) */
  --text-muted: #64748b;          /* Subtítulos, labels secundarios, placeholders (Slate 500) */
  --text-disabled: #94a3b8;       /* Controles inactivos */

  /* Marca Primaria: Bio-Teal Oftalmológico */
  --primary-50: #f0fdfa;
  --primary-100: #ccfbf1;
  --primary-200: #99f6e4;
  --primary-500: #14b8a6;
  --primary-600: #0d9488;         /* Botón primario, acentos interactivos */
  --primary-700: #0f766e;
  --primary-900: #134e4a;
  --primary-glow: rgba(13, 148, 136, 0.18);

  /* Marca Secundaria: Royal Navy */
  --secondary-50: #eff6ff;
  --secondary-500: #3b82f6;
  --secondary-600: #2563eb;
  --secondary-700: #1d4ed8;
  --secondary-900: #1e3a8a;

  /* Estados Semánticos */
  --success: #059669;
  --success-bg: #ecfdf5;
  --success-border: #a7f3d0;
  
  --warning: #d97706;
  --warning-bg: #fffbeb;
  --warning-border: #fde68a;

  --danger: #dc2626;
  --danger-bg: #fef2f2;
  --danger-border: #fecaca;

  --info: #0284c7;
  --info-bg: #f0f9ff;
  --info-border: #bae6fd;

  /* Sombras Difusas Orgánicas (Multi-capa sin manchas negras) */
  --shadow-xs: 0 1px 2px rgba(15, 23, 42, 0.04);
  --shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.06), 0 1px 2px rgba(15, 23, 42, 0.04);
  --shadow-md: 0 4px 6px -1px rgba(15, 23, 42, 0.07), 0 2px 4px -2px rgba(15, 23, 42, 0.04);
  --shadow-lg: 0 10px 15px -3px rgba(15, 23, 42, 0.08), 0 4px 6px -4px rgba(15, 23, 42, 0.03);
  --shadow-xl: 0 20px 25px -5px rgba(15, 23, 42, 0.09), 0 8px 10px -6px rgba(15, 23, 42, 0.03);
}

[data-theme="dark"] {
  /* Lienzo y Superficies (Dark Mode Anti-Halacion) */
  --bg-canvas: #0b0f17;           /* Lienzo azul obsidiana muy oscuro */
  --bg-surface: #111827;          /* Paneles, cards y modales (Gray 900) */
  --bg-surface-elevated: #1e293b; /* Dropdowns, popovers flotantes (Slate 800) */
  --bg-surface-subtle: #172033;   /* Pozos, inputs y fondos secundarios */
  --bg-surface-hover: #1e293b;    /* Filas hover */

  /* Bordes y Separadores */
  --border-subtle: rgba(255, 255, 255, 0.08);
  --border-default: rgba(255, 255, 255, 0.14);
  --border-focus: #14b8a6;

  /* Textos y Contrastes */
  --text-primary: #f8fafc;        /* Titulares H1-H6 (Slate 50) */
  --text-secondary: #cbd5e1;      /* Cuerpo de texto (Slate 300) */
  --text-muted: #94a3b8;          /* Subtítulos, labels secundarios (Slate 400) */
  --text-disabled: #475569;

  /* Primario en Dark Mode (Más luminoso para compensar fondo) */
  --primary-50: #042f2e;
  --primary-100: #0f766e;
  --primary-200: #115e59;
  --primary-500: #14b8a6;
  --primary-600: #2dd4bf;
  --primary-glow: rgba(45, 212, 191, 0.22);

  /* Secundario en Dark Mode */
  --secondary-500: #60a5fa;
  --secondary-600: #3b82f6;

  /* Estados Semánticos (Fondos translúcidos) */
  --success: #34d399;
  --success-bg: rgba(5, 150, 105, 0.16);
  --success-border: rgba(52, 211, 153, 0.3);

  --warning: #fbbf24;
  --warning-bg: rgba(217, 119, 6, 0.16);
  --warning-border: rgba(251, 191, 36, 0.3);

  --danger: #f87171;
  --danger-bg: rgba(220, 38, 38, 0.18);
  --danger-border: rgba(248, 113, 113, 0.3);

  --info: #38bdf8;
  --info-bg: rgba(2, 132, 199, 0.16);
  --info-border: rgba(56, 189, 248, 0.3);

  /* Sombras Suaves con Tinte de Elevación */
  --shadow-xs: 0 1px 2px rgba(0, 0, 0, 0.25);
  --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.35);
  --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.45);
  --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.55);
  --shadow-xl: 0 20px 25px rgba(0, 0, 0, 0.65);
}
```

---

# 3. Arquitectura Tipográfica de Élite

La tipografía debe seleccionarse con rigor científico para garantizar **lectura instantánea de recetas, dioptrías, códigos de seguros y números financieros**.

### 3.1 Las Dos Familias Oficiales Obligatorias

1. **Fuente Primaria (UI, Textos, Títulos y Controles):**  
   **`Plus Jakarta Sans`** (Google Fonts / Variable Font).
   - *¿Por qué es la elección perfecta?* Diseñada específicamente con proporciones geométricas humanistas, curvas limpias, altura de x generosa (*tall x-height*) y aperturas amplias en caracteres críticos (`e`, `c`, `s`, `a`). Esto evita que un oftalmólogo confunda una `c` con una `o` o un `8` con un `3` en una pantalla de diagnóstico.
   - *Fallback:* `Inter`, `system-ui`, `-apple-system`, `sans-serif`.

2. **Fuente Secundaria (Datos Clínicos, Prescripciones, NCF, Monedas y Gráficos):**  
   **`JetBrains Mono`** o **`Space Mono`**.
   - *¿Por qué es indispensable?* Las recetas optométricas contienen fórmulas complejas:  
     `OD: -2.75 -1.25 x 90° | ADD +2.00 | AV: 20/25`  
     `OI: -3.00 -0.75 x 180° | ADD +2.00 | AV: 20/20`
   - La fuente monoespaciada garantiza que cada signo positivo `+`, negativo `-`, grado `°` y dígito ocupe el mismo ancho horizontal exacto. En tablas financieras y de comprobantes e-CF (`E3100000001`), las columnas numéricas permanecen perfectamente alineadas sin vibrar cuando cambian en tiempo real.

```html
<!-- Importación Oficial en el <head> de index.html -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
```

### 3.2 Escala Modular de Tamaños y Pesos

| Nivel | Tamaño Fluid (`clamp`) | Peso | Tracking | Uso Exclusivo |
| :--- | :--- | :---: | :---: | :--- |
| **Display / H1** | `clamp(1.65rem, 1.4rem + 1.2vw, 2.15rem)` | 800 (Extrabold) | `-0.025em` | Títulos principales de página / Balance total del día |
| **Section / H2** | `clamp(1.35rem, 1.2rem + 0.75vw, 1.65rem)` | 700 (Bold) | `-0.02em` | Títulos de panel o módulo (ej: *Historial Clínico*) |
| **Card / H3** | `1.15rem` (18.4px) | 700 (Bold) | `-0.015em` | Cabeceras de tarjetas, nombres de pacientes en ficha |
| **Subhead / H4** | `1.00rem` (16.0px) | 600 (Semibold) | `-0.01em` | Títulos de pestañas, grupos de formulario |
| **Body (Texto)** | `0.925rem` (14.8px) | 500 (Medium) | `0em` | Texto de lectura, párrafos descriptivos, filas de tabla |
| **Body Small** | `0.825rem` (13.2px) | 500 (Medium) | `+0.005em` | Labels de inputs, notas de pie, descripciones secundarias |
| **Badge / Caption**| `0.725rem` (11.6px) | 700 (Bold) | `+0.04em` | Badges de estado, tags, micro-indicadores (Mayúsculas) |
| **Data Mono** | `0.875rem` (14.0px) | 600 (Semibold) | `0em` | Monedas, NCF, dioptrías, códigos de barra, agudeza visual |

---

# 4. Data Visualization: Gráficos Clínicos y Financieros

Los gráficos médicos en oftalmología (presión intraocular IOP, paquimetría, refracción a lo largo de los años) y financieros (ventas por medio de pago, recaudación ARS) deben ser **limpios, trazables y accesibles para daltónicos**.

### 4.1 La Paleta para Visualización de Datos (Colorblind-Safe)

Se implementa una paleta categórica optimizada para evitar confusiones en personas con **Deuteranopía, Protanopía o Tritanopía**:

```javascript
export const MedicalChartPalette = {
  // Paleta Categórica (Series independientes)
  categorical: [
    '#0D9488', // Serie 1: Bio-Teal (Consultas / Procedimientos estrella)
    '#2563EB', // Serie 2: Royal Blue (Cirugías / Estudios)
    '#8B5CF6', // Serie 3: Iris Violet (Óptica / Monturas)
    '#F59E0B', // Serie 4: Warm Amber (Farmacia / Colirios)
    '#EC4899', // Serie 5: Rose (Lentes de Contacto)
    '#06B6D4', // Serie 6: Cyan (Estudios Especiales OCT)
  ],

  // Paleta Secuencial (Intensidad / Progresión de gravedad)
  sequentialTeal: ['#CCFBF1', '#5EEAD4', '#14B8A6', '#0D9488', '#115E59'],
  
  // Paleta Divergente (Desviación respecto a la normal clínica)
  // Ejemplo: Presión Intraocular (Normal = Verde 14 mmHg, Alerta Alta = Rojo > 21 mmHg)
  divergentIOP: {
    low: '#3B82F6',    // Hipotonía ocular (< 10 mmHg)
    normal: '#10B981', // Presión perfecta (11 - 18 mmHg)
    warning: '#F59E0B',// Sospecha de Glaucoma (19 - 21 mmHg)
    critical: '#EF4444'// Hipertensión ocular aguda (> 21 mmHg)
  }
};
```

### 4.2 Reglas Inquebrantables para Gráficos
1. **Tipografía en Gráficos:**
   - Ejes X e Y: `Plus Jakarta Sans`, tamaño `11px`, peso `500`, color `var(--text-muted)`.
   - Números en ejes y tooltips: `JetBrains Mono` con `font-variant-numeric: tabular-nums`.
2. **Líneas de Guía (Gridlines):**
   - **NUNCA** utilizar líneas negras o sólidas densas. Usar líneas de trazo discontinuo (*dashed*) con color `rgba(148, 163, 184, 0.15)`. En modo oscuro: `rgba(255, 255, 255, 0.06)`.
3. **Tooltips Flotantes (Glassmorphism):**
   - Fondo: `rgba(15, 23, 42, 0.88)` (Dark) / `rgba(255, 255, 255, 0.94)` (Light) con `backdrop-filter: blur(8px)`.
   - Borde: `1px solid var(--border-default)`.
   - Sombra: `var(--shadow-lg)`.
   - Mostrar indicador de color (*color dot*), nombre métrico y valor monetario o clínico en negrita monoespaciada.
4. **Animación de Dibujo:**
   - Transición de entrada suave tipo *easeOutQuart* de 650ms. Prohibido saltar de 0 a 100 de forma brusca.

---

# 5. Estándar para Páginas y Estados Vacíos (Empty States)

> **REGLA ESTRICTA:** Queda terminantemente prohibido mostrar una tabla vacía con mensaje en texto plano *"No hay datos"*, o dejar una pantalla en blanco.
> Un **Empty State** es una oportunidad pedagógica para educar al usuario y ofrecerle un camino de acción directo (*Call To Action*).

### 5.1 Anatomía Obligatoria de un Empty State

```
┌────────────────────────────────────────────────────────┐
│                                                        │
│                    [ Ícono Clínico ]                   │
│              (Aura de color suave / Glow)              │
│                                                        │
│             Título Afirmativo y Cálido                 │
│         (Ej: "Aún no hay recetas registradas")         │
│                                                        │
│       Texto explicativo breve que guía al usuario      │
│   (Ej: "Cuando el especialista guarde la refracción,   │
│     aparecerá aquí con su fórmula optométrica.")       │
│                                                        │
│         [ + Botón de Acción Principal (CTA) ]          │
│                                                        │
│               [ Enlace o Atajo Secundario ]            │
│                                                        │
└────────────────────────────────────────────────────────┘
```

### 5.2 Implementación CSS / HTML Estándar del Empty State

```html
<div class="empty-state-card">
  <div class="empty-state-icon-wrapper">
    <div class="empty-state-icon-aura"></div>
    <i class="lucide-eye text-primary"></i>
  </div>
  <h3 class="empty-state-title">No hay pacientes en espera</h3>
  <p class="empty-state-desc">
    Todos los pacientes del turno han sido atendidos o facturados. Ingrese una nueva cita o reciba a un paciente por ventanilla.
  </p>
  <div class="empty-state-actions">
    <button type="button" class="btn-primary-glow">
      <i class="lucide-user-plus"></i> Registrar Paciente
    </button>
    <button type="button" class="btn-secondary-outline">
      <i class="lucide-calendar"></i> Ver Agenda Médica
    </button>
  </div>
</div>
```

```css
.empty-state-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 3.5rem 1.5rem;
  background: var(--bg-surface);
  border: 1.5px dashed var(--border-default);
  border-radius: 16px;
  margin: 1.5rem 0;
  transition: all 0.3s ease;
}

.empty-state-icon-wrapper {
  position: relative;
  width: 68px;
  height: 68px;
  border-radius: 20px;
  background: var(--primary-50);
  color: var(--primary-600);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  margin-bottom: 1.25rem;
}

.empty-state-icon-aura {
  position: absolute;
  inset: -6px;
  border-radius: 24px;
  background: var(--primary-glow);
  filter: blur(10px);
  z-index: 0;
  opacity: 0.6;
}

.empty-state-title {
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 1.2rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 0.4rem;
}

.empty-state-desc {
  max-width: 420px;
  font-size: 0.88rem;
  color: var(--text-muted);
  line-height: 1.55;
  margin-bottom: 1.5rem;
}

.empty-state-actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
}
```

---

# 6. Sistema de Componentes UI de Alto Nivel

### 6.1 Header y Navegación Superior
* **Estructura:** Fija superior (*Sticky* con `top: 0`), altura `64px`, `backdrop-filter: blur(12px)`.
* **Fondo:** Translúcido con `background: rgba(255, 255, 255, 0.85)` (Modo Claro) / `rgba(17, 24, 39, 0.85)` (Modo Oscuro).
* **Borde Inferior:** Sutil `1px solid var(--border-subtle)`.
* **Elementos Obligatorios:**
  - Selector de Clínica/Sucursal actual (*Multi-Tenant Indicator*).
  - Buscador Rápido Global (con atajo de teclado `Ctrl + K` / `Cmd + K`).
  - Indicador de Conexión en Vivo (Punto de pulso verde que palpita si hay conexión activa con la API y base de datos).
  - Conmutador de Modo Oscuro/Claro nativo (*Dark Mode Toggle* con micro-animación de sol/luna).
  - Perfil del Usuario: Avatar circular, Nombre, Rol (*Oftalmólogo*, *Optómetra*, *Cajero*) y estado.

### 6.2 Bordes y Radios Modernos (Orgánicos y No Rígidos)
Se prohíben esquinas cuadradas `0px` estilo Windows 95 y bordes exagerados circulares tipo juguete:

```css
--radius-xs: 6px;   /* Micro-badges, tags de tabla */
--radius-sm: 8px;   /* Botones pequeños, campos compactos */
--radius-md: 12px;  /* Inputs estándar, botones primarios, selects */
--radius-lg: 16px;  /* Tarjetas, paneles de contenido, popovers */
--radius-xl: 22px;  /* Modales grandes, contenedores principales de vista */
--radius-full: 9999px; /* Pills de estado, avatares circulares */
```

### 6.3 Tablas de Datos Clínicos y Financieros
Las tablas representan el 70% del tiempo de operación. Deben ser **ultrarrápidas, no saturar y permitir lectura en diagonal**:

1. **Header de Tabla Fijo (*Sticky Header*):**
   - Fondo: `var(--bg-surface-subtle)`.
   - Tipografía: `Plus Jakarta Sans`, mayúsculas pequeñas `0.72rem`, peso `700`, `letter-spacing: 0.05em`.
   - Color de texto: `var(--text-muted)`.
   - Altura: `44px`.
2. **Filas de Tabla (*Table Rows*):**
   - Altura mínima: `52px` (permite interacción táctil en tablets).
   - Separador: Borde inferior `1px solid var(--border-subtle)`.
   - Hover suave: `transition: background-color 0.15s ease-out`. Al pasar el cursor, el fondo cambia a `var(--bg-surface-hover)`.
3. **Alineación de Contenidos:**
   - Textos descriptivos (Nombre de paciente, seguro): **Izquierda**.
   - Fechas, NCF, Números de Aprobación ARS: **Centro**.
   - Valores Monetarios (RD$), Cantidades, Dioptrías: **Derecha** en fuente `JetBrains Mono` con `font-weight: 600`.
4. **Píldoras de Estado (*Status Badges*):**
   - Altura `24px`, padding `0 10px`, bordes redondeados `var(--radius-full)`.
   - Contienen un **punto de pulso de 6px** a la izquierda.
   - *Aprobado / Pagado:* Fondo verde translúcido, texto verde esmeralda.
   - *Pendiente ARS:* Fondo ámbar translúcido, texto ámbar.
   - *Rechazado / Glosado:* Fondo rojo translúcido, texto coral.
   - *Cobertura 100%:* Fondo azul cian translúcido, texto cian.

### 6.4 Historiales y Auditoría (Audit Timeline)
Para ver trazabilidad clínica o financiera (quién modificó un historial médico, quién cobró una factura o autorizó un seguro):
- Línea vertical izquierda continua de `2px solid var(--border-subtle)`.
- Icono de nodo circular flotante centrado sobre la línea, con color según la acción:
  - *Azul:* Creación / Registro.
  - *Ámbar:* Modificación de datos.
  - *Verde:* Cobro / Facturación.
  - *Rojo:* Anulación / Nota de crédito.
- Cada tarjeta de evento muestra:
  - Usuario responsable con avatar miniatura.
  - Timestamp relativo ("hace 12 min") con fecha exacta al hover ("15/10/2026 03:22 PM").
  - Descripción de la acción y etiqueta *Diff* visual (marcando en rojo el valor anterior y en verde el nuevo valor).

### 6.5 Formularios Inteligentes y Entradas de Dioptrías
1. **Inputs Estándar:**
   - Altura `44px`, esquinas `12px`, borde `1.5px solid var(--border-default)`.
   - Estado Focus: Borde cambia a `var(--primary-600)` con un anillo de resplandor suave:
     `box-shadow: 0 0 0 4px var(--primary-glow); outline: none;`
2. **Entrada de Fórmulas Optométricas (OD / OI):**
   - Diseñada en grid visual de 2 filas (Ojo Derecho e Izquierdo) con columnas alineadas:
     `[ ESFERA (SPH) ] [ CILINDRO (CYL) ] [ EJE (AXIS) ] [ ADICIÓN (ADD) ] [ AGUDEZA VISUAL ]`
   - Los inputs numéricos admiten incrementos en pasos de `0.25` con botones rápidos `+` y `-`.

---

# 7. Arquitectura Responsiva y Adaptabilidad Total

El ERP debe operar sin deformarse en 4 escenarios reales:
1. **Móvil (375px - 640px):** Teléfono del médico para consultar su agenda de citas, ver recetas o revisar el total facturado del día.
2. **Tablet (768px - 1024px):** iPad / Tablet Android en consultorio médico para que el paciente firme o el optometrista ingrese datos al lado del foróptero.
3. **Laptop / Estación de Caja (1280px - 1440px):** Pantalla común de recepción y facturación POS.
4. **Monitor Clínico / Ultrawide (1920px - 2560px):** Pantalla del oftalmólogo en consulta con doble panel (Historial del paciente a la izquierda + Visor de imágenes de topografía corneal a la derecha).

### 7.1 Matriz de Breakpoints Oficial

```css
/* Breakpoints del ERP */
--breakpoint-xs: 480px;   /* Móviles pequeños */
--breakpoint-sm: 640px;   /* Móviles grandes / Phablets */
--breakpoint-md: 768px;   /* Tablets verticales */
--breakpoint-lg: 1024px;  /* Tablets horizontales / Laptops estándar */
--breakpoint-xl: 1280px;  /* Monitores de clínica (1080p estándar) */
--breakpoint-2xl: 1536px; /* Monitores amplios / Pantallas quirúrgicas */
```

### 7.2 El Patrón Obligatorio "Tabla a Tarjeta" (*Responsive Table-to-Card*)
En pantallas menores a `768px` (móviles y tablets verticales), las tablas de datos de más de 4 columnas **NO deben forzar scroll horizontal eterno**.
Deben transformarse mediante CSS en **tarjetas verticales apiladas**:

```css
@media (max-width: 768px) {
  .responsive-table, 
  .responsive-table tbody, 
  .responsive-table tr, 
  .responsive-table td {
    display: block;
    width: 100%;
  }

  .responsive-table thead {
    display: none; /* Ocultar cabecera rígida en móvil */
  }

  .responsive-table tr {
    background: var(--bg-surface);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-lg);
    padding: 1rem;
    margin-bottom: 0.85rem;
    box-shadow: var(--shadow-xs);
  }

  .responsive-table td {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.45rem 0;
    border-bottom: 1px solid var(--border-subtle);
  }

  .responsive-table td:last-child {
    border-bottom: none;
    padding-top: 0.75rem;
  }

  /* El label de la columna se inyecta dinámicamente desde el atributo data-label */
  .responsive-table td::before {
    content: attr(data-label);
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
  }
}
```

---

# 8. Micro-Animaciones, Interactividad y Reactividad Viva

Una aplicación moderna no se siente "plana" ni "muerta"; responde al tacto y da retroalimentación física visual a cada acción del usuario.

### 8.1 Curvas de Transición Física (Spring Physics)
Se prohíben animaciones lineales aburridas (`transition: all 0.3s linear`). Se adoptan curvas suaves de desaceleración natural:

```css
:root {
  /* Curva de rebote suave / Smooth Spring Deceleration */
  --ease-spring: cubic-bezier(0.16, 1, 0.3, 1);
  --ease-smooth: cubic-bezier(0.4, 0, 0.2, 1);
  --ease-bounce: cubic-bezier(0.34, 1.56, 0.64, 1);

  --duration-fast: 150ms;
  --duration-normal: 250ms;
  --duration-slow: 400ms;
}
```

### 8.2 Micro-Interacciones en Botones y Controles
```css
/* Botón Primario con Resplandor Clínico */
.btn-primary-glow {
  background: var(--primary-600);
  color: #ffffff;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 0.90rem;
  font-weight: 600;
  border-radius: var(--radius-md);
  padding: 0.65rem 1.25rem;
  border: 1px solid transparent;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  box-shadow: 0 2px 4px var(--primary-glow);
  transition: transform var(--duration-fast) var(--ease-spring),
              background-color var(--duration-fast) ease,
              box-shadow var(--duration-fast) ease;
  cursor: pointer;
}

.btn-primary-glow:hover {
  background: var(--primary-700);
  transform: translateY(-1.5px);
  box-shadow: 0 6px 14px var(--primary-glow);
}

.btn-primary-glow:active {
  transform: scale(0.97);
  box-shadow: 0 1px 2px var(--primary-glow);
}
```

### 8.3 Cargas Asíncronas: Prohibido Bloquear la Pantalla (Skeleton Shimmer)
- **NUNCA** usar spinners gigantes en el centro de la pantalla que congelen la vista.
- Usar **Skeleton Loaders** que replican la forma de las tarjetas y tablas mientras cargan los datos, con un gradiente animado en movimiento (*Shimmer effect*):

```css
@keyframes shimmer {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}

.skeleton-box {
  position: relative;
  overflow: hidden;
  background-color: var(--bg-surface-hover);
  border-radius: var(--radius-sm);
}

.skeleton-box::after {
  content: '';
  position: absolute;
  top: 0; right: 0; bottom: 0; left: 0;
  background: linear-gradient(
    90deg,
    rgba(255, 255, 255, 0) 0%,
    rgba(255, 255, 255, 0.4) 50%,
    rgba(255, 255, 255, 0) 100%
  );
  animation: shimmer 1.5s infinite;
}

[data-theme="dark"] .skeleton-box::after {
  background: linear-gradient(
    90deg,
    rgba(255, 255, 255, 0) 0%,
    rgba(255, 255, 255, 0.08) 50%,
    rgba(255, 255, 255, 0) 100%
  );
}
```

### 8.4 Indicadores de Pulso en Vivo (Live Status Dots)
Para saber instantáneamente si la cola de facturación electrónica e-CF está activa o si una máquina de campo visual está conectada:

```html
<span class="live-pulse-badge status-online">
  <span class="pulse-dot"></span>
  DGII e-CF Conectado
</span>
```

```css
.live-pulse-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: var(--radius-full);
}

.live-pulse-badge.status-online {
  background: var(--success-bg);
  color: var(--success);
  border: 1px solid var(--success-border);
}

.pulse-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background-color: currentColor;
  position: relative;
}

.pulse-dot::before {
  content: '';
  position: absolute;
  inset: -3px;
  border-radius: 50%;
  background-color: currentColor;
  opacity: 0.75;
  animation: pulse-ring 1.8s cubic-bezier(0.24, 0, 0.38, 1) infinite;
}

@keyframes pulse-ring {
  0% { transform: scale(0.95); opacity: 0.8; }
  70% { transform: scale(2.2); opacity: 0; }
  100% { transform: scale(2.2); opacity: 0; }
}
```

---

# 9. Lista de Verificación Obligatoria para el Agente (Checklist de Calidad)

Antes de dar por finalizada la construcción de cualquier pantalla, componente o gráfico en el nuevo ERP, el agente de IA **DEBE** auto-verificar esta lista de control:

- [ ] **Ergonomía de Color:** ¿El fondo evita `#ffffff` o `#000000` puros? ¿Se siente suave y descansado a la vista?
- [ ] **Tipografía Correcta:** ¿Los textos y títulos usan `Plus Jakarta Sans`? ¿Las monedas, NCF, fórmulas optométricas y dioptrías usan `JetBrains Mono`?
- [ ] **Soporte Nativo Dark/Light:** ¿Todos los colores usan variables CSS (`var(--...)`) asegurando que al cambiar el tema nada quede ilegible o con fondo blanco pegado?
- [ ] **Tablas Claras:** ¿Las cabeceras son pegajosas (*sticky*)? ¿Los importes están alineados a la derecha en monoespaciado?
- [ ] **Estado Vacío Amigable:** Si la lista o consulta no tiene registros, ¿se muestra el componente de `Empty State` con ilustración/ícono y botón de acción en lugar de una pantalla en blanco?
- [ ] **Diseño Responsivo:** ¿La pantalla funciona en móvil sin desbordes horizontales? ¿Las tablas complejas se adaptan a tarjetas verticales?
- [ ] **Micro-interacciones:** ¿Los botones tienen efecto hover suave y escala al clic? ¿Las cargas usan skeleton shimmer?
- [ ] **Accesibilidad en Gráficos:** ¿Los colores de las series son distinguibles para daltónicos y usan tipografía monoespaciada para las cifras?
