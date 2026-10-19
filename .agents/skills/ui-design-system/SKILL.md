---
name: ui-design-system
description: Sistema de diseño y ergonomía visual para el nuevo ERP en React, TypeScript y Tailwind CSS / Vanilla CSS. Directrices estrictas contra la saturación visual, uso condicional de KPIs únicamente cuando aportan valor real, badges minimalistas, componentes colapsables escalables e iconos Lucide.
---

# UI Design System & Ergonomía Visual (React + TypeScript)

Esta skill define los estándares de diseño, la paleta cromática, las reglas de composición visual y los patrones de componentes en **React, Vite, TypeScript y Tailwind CSS / Vanilla CSS** para el nuevo ERP clínico.

---

## 1. Filosofía de Diseño: Claridad Operativa y Cero Saturación

Un sistema médico y administrativo no debe distraer con elementos ornamentales innecesarios. El personal de recepción, facturación y consultorio médico debe trabajar con máxima agilidad:

1. **Uso Condicional y Riguroso de Tarjetas KPI / Métricas**:
   > ⚠️ **Regla Cardinal de Diseño**: Las tarjetas KPI **NO deben colocarse indiscriminadamente en todas las pantallas**.
   > * **Prohibido colocar KPIs**: En listados maestros, catálogos, formularios de registro o vistas operativas simples (ej: Artículos, Pacientes, Usuarios, Proveedores) donde solo se repetiría el conteo de filas. Los conteos pertenecen al badge de la pestaña (`.tab-badge`).
   > * **Permitido colocar KPIs**: Únicamente en dashboards ejecutivos de alto nivel, cierres y arqueos de caja (donde informan balance real vs esperado), o en auditoría de glosas ARS (donde alertan sobre dinero retenido).
2. **Prioridad al Flujo de Trabajo (Fitts's Law)**:
   - Los campos más utilizados en la facturación y la consulta deben estar al alcance directo del foco del teclado.
   - Acceso rápido por atajos (`F2` para cobrar, `Esc` para cancelar, `Enter` para avanzar).
3. **Paleta de Colores Clínica y Premium**:
   - **Canvas General**: `#f8fafc` (Slate 50).
   - **Superficie de Tarjetas**: `#ffffff` con bordes sutiles `#e2e8f0` y sombra suave `shadow-sm`.
   - **Acentos Primarios (Clínico / Quirúrgico)**: `#059669` (Emerald 600) y `#0f766e` (Teal 700).
   - **Tipografía y Textos**:
     - Títulos e Importes: `#0f172a` (Slate 900) con `font-semibold`.
     - Texto de Lectura: `#334155` (Slate 700).
     - Etiquetas y Metadatos: `#64748b` (Slate 500) con `text-xs uppercase tracking-wider`.
   - **Estados y Semáforos**:
     - Éxito / Vigente: `#10b981` (Emerald 500).
     - Alerta / Próximo a Vencer: `#f59e0b` (Amber 500).
     - Peligro / Agotado / Rechazado: `#ef4444` (Rose 500).

---

## 2. Componentes Clave en React + TypeScript

### 2.1 Pestañas con Conteo Minimalista (`TabBadge`)
Para indicar el volumen total de elementos sin ocupar espacio vertical:

```tsx
import React from 'react';

interface TabItemProps {
  label: string;
  count?: number;
  active: boolean;
  onClick: () => void;
  icon?: React.ReactNode;
}

export const TabItem: React.FC<TabItemProps> = ({ label, count, active, onClick, icon }) => {
  return (
    <button
      onClick={onClick}
      className={`flex items-center gap-2 px-4 py-2.5 font-medium text-sm rounded-t-lg transition-colors border-b-2 ${
        active
          ? 'border-emerald-600 text-emerald-700 bg-white'
          : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100'
      }`}
    >
      {icon}
      <span>{label}</span>
      {typeof count === 'number' && (
        <span className="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
          {count.toLocaleString()}
        </span>
      )}
    </button>
  );
};
```

---

### 2.2 Selector de Almacenes / Entidades Escalable y Colapsable
Evita rejillas densas de tarjetas cuadradas que saturan la pantalla cuando existen decenas de almacenes o entidades:

```tsx
import React, { useState } from 'react';
import { ChevronDown, Search, Warehouse } from 'lucide-react';

interface WarehouseOption {
  id: string;
  code: string;
  name: string;
  isDefault?: boolean;
}

interface WarehouseSelectorProps {
  warehouses: WarehouseOption[];
  selectedIds: string[];
  onChange: (ids: string[]) => void;
}

export const ScalableWarehouseSelector: React.FC<WarehouseSelectorProps> = ({
  warehouses,
  selectedIds,
  onChange
}) => {
  const [isOpen, setIsOpen] = useState(false);
  const [filter, setFilter] = useState('');

  const filtered = warehouses.filter(w =>
    w.name.toLowerCase().includes(filter.toLowerCase()) || w.code.toLowerCase().includes(filter.toLowerCase())
  );

  const toggleAll = (select: boolean) => {
    onChange(select ? warehouses.map(w => w.id) : []);
  };

  const toggleOne = (id: string) => {
    onChange(
      selectedIds.includes(id)
        ? selectedIds.filter(item => item !== id)
        : [...selectedIds, id]
    );
  };

  return (
    <div className="border border-slate-200 rounded-lg bg-white overflow-hidden shadow-sm">
      <div
        onClick={() => setIsOpen(!isOpen)}
        className="flex items-center justify-between p-3 cursor-pointer bg-slate-50 hover:bg-slate-100 transition-colors"
      >
        <div className="flex items-center gap-2">
          <Warehouse className="w-5 h-5 text-emerald-600" />
          <span className="font-medium text-sm text-slate-800">Almacenes Permitidos</span>
          <span className="text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-semibold">
            {selectedIds.length} habilitado{selectedIds.length === 1 ? '' : 's'}
          </span>
        </div>
        <ChevronDown className={`w-4 h-4 text-slate-400 transition-transform ${isOpen ? 'rotate-180' : ''}`} />
      </div>

      {isOpen && (
        <div className="p-3 border-t border-slate-200 space-y-2">
          <div className="flex items-center gap-2">
            <div className="relative flex-1">
              <Search className="w-4 h-4 absolute left-3 top-2.5 text-slate-400" />
              <input
                type="text"
                placeholder="Filtrar almacén..."
                value={filter}
                onChange={e => setFilter(e.target.value)}
                className="w-full pl-9 pr-3 py-1.5 text-sm border border-slate-300 rounded-md focus:ring-1 focus:ring-emerald-500 focus:outline-none"
              />
            </div>
            <button
              type="button"
              onClick={() => toggleAll(true)}
              className="px-2.5 py-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-md"
            >
              Marcar Todos
            </button>
            <button
              type="button"
              onClick={() => toggleAll(false)}
              className="px-2.5 py-1.5 text-xs font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-md"
            >
              Desmarcar
            </button>
          </div>

          <div className="max-h-48 overflow-y-auto space-y-1 divide-y divide-slate-100">
            {filtered.map(w => (
              <label
                key={w.id}
                className="flex items-center justify-between py-2 px-1 hover:bg-slate-50 rounded cursor-pointer"
              >
                <div className="flex items-center gap-2">
                  <input
                    type="checkbox"
                    checked={selectedIds.includes(w.id)}
                    onChange={() => toggleOne(w.id)}
                    className="rounded text-emerald-600 focus:ring-emerald-500 h-4 w-4"
                  />
                  <span className="text-xs font-mono bg-slate-200 text-slate-700 px-1.5 py-0.5 rounded">
                    {w.code}
                  </span>
                  <span className="text-sm text-slate-800">{w.name}</span>
                </div>
                {w.isDefault && (
                  <span className="text-xs text-emerald-600 font-medium">Predeterminado</span>
                )}
              </label>
            ))}
          </div>
        </div>
      )}
    </div>
  );
};
```

---

### 2.3 Semáforo de Comprobantes Fiscales (`NCFTrafficLightBadge`)
Muestra el estado visual de secuencias NCF sin abrumar:

```tsx
import React from 'react';
import { AlertTriangle, CheckCircle, XCircle } from 'lucide-react';

interface TrafficLightProps {
  tipoNcf: string;
  disponibles: number;
  diasParaVencer: number;
}

export const NCFTrafficLightBadge: React.FC<TrafficLightProps> = ({ tipoNcf, disponibles, diasParaVencer }) => {
  if (disponibles === 0 || diasParaVencer <= 0) {
    return (
      <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
        <XCircle className="w-3.5 h-3.5 text-rose-500" />
        {tipoNcf}: Agotado / Vencido
      </span>
    );
  }

  if (disponibles <= 50 || diasParaVencer <= 15) {
    return (
      <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
        <AlertTriangle className="w-3.5 h-3.5 text-amber-500" />
        {tipoNcf}: {disponibles} restantes ({diasParaVencer}d)
      </span>
    );
  }

  return (
    <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
      <CheckCircle className="w-3.5 h-3.5 text-emerald-500" />
      {tipoNcf}: {disponibles} disp.
    </span>
  );
};
```

---

## 3. Notificaciones y Diálogos

* **Notificaciones Transitorias (Toasts)**:
  - Usar para confirmar acciones exitosas en segundo plano (ej: *"e-CF transmitido con TrackId #..."* o *"Artículo guardado"*).
* **Diálogos de Confirmación (SweetAlert2 o Modal Headless)**:
  - Exclusivamente para acciones destructivas o irreversibles (ej: *"¿Está seguro de emitir una Nota de Crédito por anulación total?"* o *"¿Desea cerrar el turno de caja definitivamente?"*).
