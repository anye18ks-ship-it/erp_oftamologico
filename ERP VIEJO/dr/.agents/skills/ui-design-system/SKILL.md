---
name: ui-design-system
description: Guía de arquitectura CSS Vanilla, componentes UI, modales, listas escalables, uso condicional de KPIs, badges de pestañas y directrices de diseño para este sistema y el nuevo ERP.
---

# Design System & UI Architecture (Vanilla CSS & JS)

Esta skill define la estructura visual, reglas de maquetación, estándares de componentes y ergonomía visual para la interfaz de usuario de este sistema clínico/ERP y la base arquitectónica del nuevo ERP.

---

## 1. Reglas Fundamentales de Estilo y Ergonomía

1. **Vanilla CSS Estricto y Modular**:
   - Todo el CSS debe residir en archivos `.css` organizados dentro de la carpeta `styles/` de cada módulo (ej: `views/Articulos/styles/articulos.css`, `views/Facturas/styles/factura.css`).
   - **Evitar CSS en línea (`style="..."`)** salvo para valores dinámicos estrictos (como anchos calculados por backend).
   - No introducir dependencias pesadas de Tailwind CSS ni sobreescribir Bootstrap de forma caótica.

2. **Cero Saturación Visual (Clean & Focused Layout)**:
   - Evitar saturar las pantallas con métricas o bloques decorativos que resten espacio a los datos operativos.
   - Las pantallas transaccionales y de directorio deben priorizar la agilidad de búsqueda, la claridad de lectura en tablas y la facilidad de entrada de datos.

3. **Paleta de Colores Corporativa**:
   - Primario / Branding: `#1b4332`, `#2d6a4f`, `#40916c` (Verdes clínicos premium) / `#1a365d`, `#2b6cb0` (Azules ERP).
   - Éxito / Guardado: `#10b981`, `#059669`.
   - Advertencia / Alerta: `#f59e0b`, `#d97706`.
   - Peligro / Anulación: `#ef4444`, `#dc2626`.
   - Fondos: `#f8fafc` (Canvas general), `#ffffff` (Cards y Paneles).
   - Bordes y Separadores: `#e2e8f0`, `#cbd5e1`.
   - Texto: `#0f172a` (Títulos), `#334155` (Cuerpo), `#64748b` (Secundario/Labels).

---

## 2. Componentes Estándar

### 2.1 Uso Condicional de Tarjetas KPI / Métricas
> **Directriz Estricta:** Las tarjetas KPI **NO deben colocarse por defecto en todas las pantallas**.
> - **Cuándo NO usar KPI:** En pantallas de directorio estándar, formularios de registro o vistas operativas simples (ej. Usuarios, Artículos, Socios, Consultas donde solo se repite el total de filas). Los conteos simples de registros pertenecen al badge de la pestaña (`.tab-badge`).
> - **Cuándo SÍ usar KPI:** Únicamente en dashboards ejecutivos, resúmenes de cierre de caja, arqueos o cuando la métrica aporta información financiera/operativa crítica y no redundante (ej. Monto Total en Caja, Facturas Vencidas, Glosas Pendientes).

```html
<!-- Solo en vistas ejecutivas o de balance donde aporten valor real -->
<div class="kpi-grid">
  <div class="kpi-card kpi-primary">
    <div class="kpi-icon"><i class="fas fa-cash-register"></i></div>
    <div class="kpi-content">
      <span class="kpi-label">Efectivo Esperado</span>
      <h3 class="kpi-value">RD$ 45,200.00</h3>
    </div>
  </div>
</div>
```

### 2.2 Pestañas con Conteo Minimalista (`.tab-badge`)
Para indicar volumen de registros sin ocupar espacio vertical ni saturar:
```html
<ul class="nav nav-tabs custom-tabs" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" data-bs-toggle="tab" href="#tab-list">
      <i class="fas fa-list"></i> Directorio
      <span class="tab-badge ms-2">1,248</span>
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link" data-bs-toggle="tab" href="#tab-form">
      <i class="fas fa-plus-circle"></i> Nuevo Registro
    </a>
  </li>
</ul>
```

### 2.3 Listas de Selección Escalables y Colapsables (Almacenes / Asignaciones)
Para relaciones uno-a-muchos (como almacenes permitidos para un artículo) donde pueden existir decenas de elementos:
- **No usar grids de tarjetas cuadradas apretadas**: provocan quiebres de línea erráticos y saturación.
- **Usar contenedor colapsable con buscador integrado y filas compactas**:
```html
<div class="alm-selector-container">
  <!-- Cabecera colapsable con resumen -->
  <div class="alm-collapse-header" onclick="toggleAlmacenes()">
    <div class="alm-collapse-info">
      <span class="alm-collapse-title"><i class="fas fa-warehouse"></i> Almacenes Permitidos</span>
      <span class="alm-badge-count" id="alm-count-badge">1 habilitado</span>
    </div>
    <i class="fas fa-chevron-down alm-collapse-chevron" id="alm-chevron"></i>
  </div>

  <div class="alm-collapse-body" id="alm-collapse-body">
    <!-- Buscador en tiempo real y botones de acción rápida -->
    <div class="alm-toolbar">
      <input type="text" class="alm-search-input" placeholder="Buscar almacén..." oninput="filtrarAlmacenes(this.value)">
      <div class="alm-toolbar-actions">
        <button type="button" class="btn-alm-action" onclick="toggleTodosAlmacenes(true)">Marcar Todos</button>
        <button type="button" class="btn-alm-action" onclick="toggleTodosAlmacenes(false)">Desmarcar</button>
      </div>
    </div>
    <!-- Lista scrollable con filas estructuradas -->
    <div class="alm-lista-scroll">
      <div class="alm-fila-item">
        <span class="alm-code-badge">01</span>
        <div class="alm-info-text">
          <span class="alm-name">Almacén General Central</span>
          <span class="badge-principal-tag">Principal</span>
        </div>
        <input type="checkbox" class="alm-toggle-switch" checked>
      </div>
    </div>
  </div>
</div>
```

### 2.4 Formularios Reactivos según Naturaleza de Entidad
Cuando un registro puede tener comportamientos diferenciados (ej: Artículo Físico vs Servicio Clínico):
- Proporcionar segmentador visual claro (radio-buttons estilizados o pills).
- **Deshabilitar/Ocultar campos no aplicables:**
  - Si es **Servicio Clínico**: Deshabilitar código de barras (`CodeBars`) ya que no utiliza lector POS, establecer `InvntItem = 'N'`, y fijar almacenes a sólo enlace lógico.
  - Si es **Artículo Físico**: Habilitar código de barras, permitir asignación física de almacenes, iniciando **por defecto solo con el almacén principal habilitado** y los demás deshabilitados.

### 2.5 Tablas de Datos con Headers Fijos y Hover Suave
- Contenedor con `overflow-x: auto` y bordes redondeados (`border-radius: 8px`).
- Header con fondo neutro sutil (`#f1f5f9`), fuente en mayúsculas pequeñas (`font-size: 0.75rem; text-transform: uppercase; font-weight: 600; color: #475569;`).
- Filas con `transition: background 0.15s ease;` y `:hover` en `#f8fafc` o `#f1f5f9`.

### 2.6 Modales
- Backdrop semi-transparente con desenfoque suave (`backdrop-filter: blur(4px); background: rgba(15, 23, 42, 0.6);`).
- Animación de entrada (`fade-in-up` de 200ms).
- Header con título claro y botón de cierre `&times;`.
- Footer fijo con botones de acción alineados a la derecha (`Cancelar` secundario, `Confirmar/Guardar` primario).

---

## 3. Paginación y Filtrado Dinámico (Vanilla JS)

Para tablas grandes en el cliente sin recarga de página:
- Paginación pura en JavaScript (10, 25, 50 registros por página).
- Filtro en tiempo real conectando el evento `input` del buscador con filtrado de filas mediante `display = ''` o `display = 'none'`.
- Botones de página dinámicos con estilos `.page-btn` y `.page-btn.active`.

---

## 4. Estándar de Alertas y Notificaciones (SweetAlert2 / Toast)

- Usar `Swal.fire` para confirmaciones destructivas (anular factura, eliminar entrada):
```javascript
Swal.fire({
  title: '¿Confirmar Anulación?',
  text: 'Esta acción revertirá la factura y devolverá el stock.',
  icon: 'warning',
  showCancelButton: true,
  confirmButtonColor: '#dc2626',
  cancelButtonColor: '#64748b',
  confirmButtonText: 'Sí, anular',
  cancelButtonText: 'Cancelar'
});
```
- Usar toasts sutiles para éxitos rápidos: `Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 })`.

