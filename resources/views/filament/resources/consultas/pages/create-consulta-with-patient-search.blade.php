<x-filament-panels::page>
    @if (!$this->showConsultaForm)
        {{-- Formulario de búsqueda de paciente --}}
        <div class="space-y-6">
            <x-filament::section>
                <x-slot name="heading">
                    Paso 1: Seleccionar Paciente
                </x-slot>

                <x-slot name="description">
                    Busque y seleccione el paciente para quien desea crear la consulta médica.
                </x-slot>

                <div class="space-y-6">
                    {{ $this->patientSearchForm }}

                    <div class="flex justify-between items-center">
                        <x-filament::button
                            wire:click="redirectToCreatePatient"
                            type="button"
                            color="success"
                            outlined
                            size="lg"
                        >
                            <x-heroicon-o-user-plus class="w-4 h-4 mr-2" />
                            Crear Nuevo Paciente
                        </x-filament::button>

                        <x-filament::button
                            wire:click="selectPatient"
                            type="button"
                            size="lg"
                        >
                            <x-heroicon-m-arrow-right class="w-4 h-4 mr-2" />
                            Continuar con este paciente
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::section>
        </div>
    @else
        {{-- Formulario de creación de consulta en layout dividido --}}
        <div class="consulta-layout-controls" role="toolbar" aria-label="Ajustar ancho de paneles">
            <button type="button" class="consulta-layout-btn" data-layout-action="focus-left">
                Enfocar consulta
            </button>
            <button type="button" class="consulta-layout-btn" data-layout-action="reset">
                Vista equilibrada
            </button>
            <button type="button" class="consulta-layout-btn" data-layout-action="focus-right">
                Enfocar contexto
            </button>
        </div>

        <div class="consulta-dual-layout items-start" id="consultaSplitLayout">
            <div class="consulta-form-panel space-y-6">
                <x-filament::section>
                    <x-slot name="heading">
                        Paso 2: Crear Consulta Médica
                    </x-slot>

                    <x-slot name="description">
                        Complete la consulta sin perder de vista el historial clínico del paciente.
                    </x-slot>

                    <div class="space-y-6 consultation-paper-body">
                        {{ $this->consultaForm }}

                        <div class="flex justify-between">
                            <x-filament::button
                                wire:click="changePatient"
                                type="button"
                                color="gray"
                                outlined
                            >
                                <x-heroicon-m-arrow-left class="w-4 h-4 mr-2" />
                                Cambiar paciente
                            </x-filament::button>

                            <x-filament::button
                                wire:click="create"
                                type="button"
                                size="lg"
                            >
                                <x-heroicon-m-clipboard-document-list class="w-4 h-4 mr-2" />
                                Crear Consulta
                            </x-filament::button>
                        </div>
                    </div>
                </x-filament::section>
            </div>

            <div
                class="consulta-resizer"
                id="consultaResizer"
                role="separator"
                aria-label="Redimensionar paneles"
                aria-orientation="vertical"
                aria-valuemin="32"
                aria-valuemax="68"
                aria-valuenow="50"
                tabindex="0"
            ></div>

            <aside class="consulta-context-panel space-y-4">
                <x-filament::section>
                    <x-slot name="heading">Paciente en contexto</x-slot>
                    <x-slot name="description">Vista clínica rápida para decidir sin perder foco en la consulta.</x-slot>

                    <div class="space-y-4 context-pdf-sheet">
                        @php
                            $nombrePaciente = $this->selectedPatient?->persona?->nombre_completo ?? 'Paciente no disponible';
                            $sexoPaciente = $this->selectedPatient?->persona?->sexo === 'M' ? 'Masculino' : ($this->selectedPatient?->persona?->sexo === 'F' ? 'Femenino' : 'N/D');
                            $partesNombre = preg_split('/\s+/', trim($nombrePaciente));
                            $iniciales = 'P';
                            if (! empty($partesNombre[0])) {
                                $iniciales = mb_substr($partesNombre[0], 0, 1);
                            }
                            if (! empty($partesNombre[1])) {
                                $iniciales .= mb_substr($partesNombre[1], 0, 1);
                            }
                            $iniciales = strtoupper($iniciales);
                            $enfermedades = $this->selectedPatient?->enfermedades ?? collect();
                            $medicamentosActivos = $this->getMedicamentosActivos();
                        @endphp

                        <div class="context-alert-box">
                            <div class="context-alert-title-wrap">
                                <p class="context-alert-title">Antecedentes patológicos del paciente</p>
                            </div>
                            @if($enfermedades->isEmpty())
                                <p class="context-empty-state">Sin enfermedades registradas.</p>
                            @else
                                <div class="flex flex-wrap gap-2">
                                    @foreach($enfermedades->take(8) as $enfermedad)
                                        <span class="context-chip-alert">
                                            {{ $enfermedad->enfermedades ?? 'Enfermedad' }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="context-hero-card context-hero-card--summary">
                            <div class="context-hero-top">
                                <div class="context-avatar">{{ $iniciales }}</div>
                                <div class="context-title-wrap">
                                    <p class="context-name">{{ $nombrePaciente }}</p>
                                    <p class="context-subtitle">Paciente activo en esta consulta</p>
                                </div>
                            </div>

                            <div class="context-kv-grid mt-4 context-kv-grid--summary">
                                <div class="context-kv-item">
                                    <p class="context-kv-label">DNI</p>
                                    <p class="context-kv-value">{{ $this->selectedPatient?->persona?->dni ?? 'N/D' }}</p>
                                </div>
                                <div class="context-kv-item">
                                    <p class="context-kv-label">Sexo</p>
                                    <p class="context-kv-value">{{ $sexoPaciente }}</p>
                                </div>
                                <div class="context-kv-item">
                                    <p class="context-kv-label">Edad</p>
                                    <p class="context-kv-value">{{ $this->selectedPatient?->persona?->fecha_nacimiento ? \Carbon\Carbon::parse($this->selectedPatient->persona->fecha_nacimiento)->age . ' años' : 'N/D' }}</p>
                                </div>
                                <div class="context-kv-item">
                                    <p class="context-kv-label">Teléfono</p>
                                    <p class="context-kv-value">{{ $this->selectedPatient?->persona?->telefono ?? 'N/D' }}</p>
                                </div>
                                <div class="context-kv-item">
                                    <p class="context-kv-label">Grupo sanguíneo</p>
                                    <p class="context-kv-value">{{ $this->selectedPatient?->grupo_sanguineo ?? 'N/D' }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="context-section-title">Tratamiento y medicación base</h4>
                            @if($medicamentosActivos->isEmpty())
                                <p class="context-empty-state">Sin medicación o tratamiento registrado.</p>
                            @else
                                <div class="flex flex-wrap gap-2">
                                    @foreach($medicamentosActivos as $medicamento)
                                        <span class="context-chip-medication">{{ $medicamento }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div>
                            <h4 class="context-section-title">Últimas consultas</h4>
                            @php $consultas = $this->getRecentConsultas(); @endphp
                            @if($consultas->isEmpty())
                                <p class="context-empty-state">No hay consultas previas.</p>
                            @else
                                <div class="space-y-2">
                                    @foreach($consultas as $consulta)
                                        <div class="context-timeline-item">
                                            <div class="flex items-center justify-between gap-2">
                                                <p class="context-timeline-date">{{ $consulta->created_at?->format('d/m/Y') ?? 'N/D' }}</p>
                                                <p class="context-timeline-id">#{{ $consulta->id }}</p>
                                            </div>
                                            <p class="context-timeline-dx">
                                                {{ $consulta->diagnostico ?: 'Sin diagnóstico registrado' }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 gap-3">
                            <div class="context-counter-card context-counter-card--blue">
                                <div class="flex items-center justify-between">
                                    <p class="context-counter-label">Recetas recientes</p>
                                    <span class="context-counter-number">{{ $this->getRecentRecetas()->count() }}</span>
                                </div>
                            </div>

                            <div class="context-counter-card context-counter-card--indigo">
                                <div class="flex items-center justify-between">
                                    <p class="context-counter-label">Exámenes recientes</p>
                                    <span class="context-counter-number">{{ $this->getRecentExamenes()->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-filament::section>
            </aside>
        </div>

        <style>
            .consulta-dual-layout {
                --cx-bg-main: #1b1b1b;
                --cx-bg-panel: #ffffff;
                --cx-bg-soft: #f8fafc;
                --cx-border: #d5dbe3;
                --cx-text: #0f172a;
                --cx-muted: #64748b;
                --cx-accent: #be123c;
                --cx-accent-soft: rgba(190, 24, 93, 0.1);
                --cx-cyan: #ccf2ea;
                --cx-paper: #fdfcf8;
                --cx-paper-line: rgba(15, 23, 42, 0.06);
                --cx-sheet: #ffffff;
                --cx-sheet-line: #e2e8f0;
            }

            .dark .consulta-dual-layout {
                --cx-bg-main: #1b1b1b;
                --cx-bg-panel: #222222;
                --cx-bg-soft: #2b2b2b;
                --cx-border: #3b3b3b;
                --cx-text: #f4f4f5;
                --cx-muted: #b5b5b8;
                --cx-accent: #f3b5b5;
                --cx-accent-soft: rgba(243, 181, 181, 0.14);
                --cx-cyan: #bfe6df;
                --cx-paper: #222222;
                --cx-paper-line: rgba(148, 163, 184, 0.14);
                --cx-sheet: #1f1f1f;
                --cx-sheet-line: #3b3b3b;
            }

            .consulta-dual-layout {
                display: grid;
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .consulta-layout-controls {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                margin-bottom: 0.9rem;
            }

            .consulta-layout-btn {
                border: 1px solid var(--cx-border);
                border-radius: 999px;
                padding: 0.38rem 0.8rem;
                font-size: 0.78rem;
                font-weight: 600;
                color: var(--cx-text);
                background: color-mix(in srgb, var(--cx-bg-soft) 82%, transparent 18%);
                transition: all 0.15s ease;
            }

            .consulta-layout-btn:hover,
            .consulta-layout-btn:focus-visible {
                border-color: color-mix(in srgb, var(--cx-text) 35%, var(--cx-border) 65%);
                background: color-mix(in srgb, var(--cx-bg-soft) 92%, transparent 8%);
                outline: none;
            }

            .consulta-resizer {
                display: none;
            }

            @media (min-width: 1024px) {
                .consulta-dual-layout {
                    --cx-left-width: 50%;
                    grid-template-columns: minmax(0, var(--cx-left-width)) 12px minmax(0, calc(100% - var(--cx-left-width) - 12px));
                    align-items: start;
                    gap: 0.75rem;
                }

                .consulta-resizer {
                    display: block;
                    width: 12px;
                    border-radius: 999px;
                    cursor: col-resize;
                    border: 1px solid var(--cx-border);
                    background: linear-gradient(180deg, color-mix(in srgb, var(--cx-bg-soft) 92%, transparent 8%), color-mix(in srgb, var(--cx-bg-soft) 82%, transparent 18%));
                    box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--cx-text) 6%, transparent 94%);
                    min-height: 320px;
                }

                .consulta-resizer:focus-visible {
                    outline: 2px solid color-mix(in srgb, var(--cx-text) 30%, #ffffff 70%);
                    outline-offset: 2px;
                }

                .consulta-context-panel {
                    position: sticky;
                    top: 1.25rem;
                    max-height: calc(100vh - 2.5rem);
                    overflow: auto;
                    padding-right: 0.25rem;
                }
            }

            @media (max-width: 1023px) {
                .consulta-layout-controls {
                    display: none;
                }
            }

            .consulta-form-panel .fi-section,
            .consulta-context-panel .fi-section {
                border-radius: 14px;
                border: 1px solid var(--cx-border) !important;
                background: var(--cx-bg-panel) !important;
                box-shadow: none !important;
            }

            .consulta-form-panel .fi-section {
                background: linear-gradient(180deg, var(--cx-paper) 0%, color-mix(in srgb, var(--cx-paper) 82%, #ffffff 18%) 100%) !important;
            }

            .consulta-context-panel .fi-section {
                background: linear-gradient(180deg, var(--cx-sheet) 0%, color-mix(in srgb, var(--cx-sheet) 92%, #ffffff 8%) 100%) !important;
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08) !important;
            }

            .consulta-form-panel .fi-section-header,
            .consulta-context-panel .fi-section-header {
                background: transparent !important;
                border-bottom: 1px solid var(--cx-border) !important;
            }

            .consulta-form-panel .fi-section-header-heading,
            .consulta-context-panel .fi-section-header-heading,
            .consulta-form-panel .fi-section-header-description,
            .consulta-context-panel .fi-section-header-description,
            .consulta-form-panel .fi-fo-field-wrp-label,
            .consulta-form-panel .fi-input-wrp-label,
            .consulta-form-panel .fi-fo-placeholder,
            .consulta-form-panel .fi-fo-field-wrp-helper-text {
                color: var(--cx-text) !important;
            }

            .consulta-form-panel .fi-input,
            .consulta-form-panel .fi-textarea,
            .consulta-form-panel .fi-select-input,
            .consulta-form-panel .fi-input-wrp,
            .consulta-form-panel .fi-fo-placeholder > div {
                background: color-mix(in srgb, var(--cx-paper) 86%, #ffffff 14%) !important;
                border-color: var(--cx-border) !important;
                color: var(--cx-text) !important;
            }

            .consulta-form-panel .fi-input,
            .consulta-form-panel .fi-textarea,
            .consulta-form-panel .fi-select-input {
                border-color: var(--cx-border) !important;
            }

            .consulta-form-panel .fi-input::placeholder,
            .consulta-form-panel .fi-textarea::placeholder {
                color: var(--cx-muted) !important;
                opacity: 0.75;
            }

            .context-hero-card {
                border: 1px solid var(--cx-border);
                border-radius: 14px;
                padding: 14px;
                background: color-mix(in srgb, var(--cx-sheet) 94%, #ffffff 6%);
            }

            .context-hero-card--summary {
                border-color: color-mix(in srgb, var(--cx-text) 10%, var(--cx-border) 90%);
            }

            .context-alert-box {
                border: 1px solid color-mix(in srgb, #ef4444 35%, var(--cx-border) 65%);
                border-radius: 12px;
                padding: 10px 12px;
                background: color-mix(in srgb, #fee2e2 26%, var(--cx-sheet) 74%);
            }

            .dark .context-alert-box {
                background: color-mix(in srgb, #7f1d1d 20%, var(--cx-sheet) 80%);
            }

            .context-alert-title-wrap {
                margin-bottom: 8px;
            }

            .context-alert-title {
                margin: 0;
                font-size: 12px;
                font-weight: 800;
                letter-spacing: 0.01em;
                color: color-mix(in srgb, #b91c1c 70%, var(--cx-text) 30%);
                text-transform: uppercase;
            }

            .context-hero-top {
                display: flex;
                align-items: center;
                gap: 12px;
            }

            .context-avatar {
                width: 46px;
                height: 46px;
                border-radius: 999px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                font-size: 14px;
                color: #173a38;
                background: linear-gradient(145deg, var(--cx-cyan), #d8f2ed);
                box-shadow: 0 6px 16px rgba(191, 230, 223, 0.25);
            }

            .context-title-wrap {
                min-width: 0;
            }

            .context-name {
                margin: 0;
                font-size: 1rem;
                line-height: 1.35;
                font-weight: 700;
                color: var(--cx-text);
            }

            .context-subtitle {
                margin: 2px 0 0;
                font-size: 12px;
                color: var(--cx-muted);
            }

            .context-kv-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }

            .context-kv-grid--summary {
                grid-template-columns: 1fr 1fr;
            }

            @media (max-width: 640px) {
                .context-kv-grid,
                .context-kv-grid--summary {
                    grid-template-columns: 1fr;
                }
            }

            .context-kv-item {
                border: 1px solid var(--cx-sheet-line);
                border-radius: 10px;
                padding: 8px 10px;
                background: color-mix(in srgb, var(--cx-sheet) 90%, #ffffff 10%);
            }

            .context-kv-label {
                margin: 0;
                font-size: 11px;
                color: var(--cx-muted);
            }

            .context-kv-value {
                margin: 2px 0 0;
                font-size: 13px;
                font-weight: 700;
                color: var(--cx-text);
            }

            .context-section-title {
                margin: 0 0 8px;
                font-size: 13px;
                font-weight: 700;
                color: var(--cx-text);
                letter-spacing: 0.01em;
                text-transform: uppercase;
            }

            .context-empty-state {
                margin: 0;
                font-size: 12px;
                color: var(--cx-muted);
                font-style: italic;
            }

            .context-chip-alert {
                display: inline-flex;
                align-items: center;
                border-radius: 999px;
                padding: 4px 9px;
                font-size: 11px;
                font-weight: 600;
                background: var(--cx-accent-soft);
                color: var(--cx-accent);
                border: 1px solid rgba(243, 181, 181, 0.45);
            }

            .context-chip-medication {
                display: inline-flex;
                align-items: center;
                border-radius: 999px;
                padding: 4px 10px;
                font-size: 11px;
                font-weight: 600;
                color: var(--cx-text);
                background: color-mix(in srgb, var(--cx-sheet) 86%, #ffffff 14%);
                border: 1px solid var(--cx-sheet-line);
            }

            .context-timeline-item {
                border: 1px solid var(--cx-sheet-line);
                border-radius: 10px;
                padding: 9px 10px;
                background: color-mix(in srgb, var(--cx-sheet) 90%, #ffffff 10%);
            }

            .context-timeline-date {
                margin: 0;
                font-size: 11px;
                font-weight: 700;
                color: var(--cx-text);
            }

            .context-timeline-id {
                margin: 0;
                font-size: 11px;
                color: var(--cx-muted);
            }

            .context-timeline-dx {
                margin: 5px 0 0;
                font-size: 12px;
                color: var(--cx-muted);
            }

            .context-counter-card {
                border-radius: 10px;
                border: 1px solid var(--cx-sheet-line);
                padding: 10px 12px;
                background: color-mix(in srgb, var(--cx-sheet) 88%, #ffffff 12%);
            }

            .context-counter-card--blue {
                border-color: rgba(191, 230, 223, 0.45);
                background: rgba(191, 230, 223, 0.08);
            }

            .context-counter-card--indigo {
                border-color: rgba(243, 181, 181, 0.45);
                background: rgba(243, 181, 181, 0.08);
            }

            .context-counter-label {
                margin: 0;
                font-size: 12px;
                font-weight: 600;
                color: var(--cx-text);
            }

            .context-counter-number {
                font-size: 14px;
                font-weight: 800;
                color: var(--cx-text);
            }

            .consultation-paper-body {
                position: relative;
                border-radius: 12px;
                padding: 4px;
                background-image: repeating-linear-gradient(
                    to bottom,
                    transparent 0,
                    transparent 30px,
                    var(--cx-paper-line) 31px
                );
            }

            .context-pdf-sheet {
                border-radius: 12px;
                border: 1px dashed var(--cx-sheet-line);
                padding: 12px;
                background: linear-gradient(180deg, color-mix(in srgb, var(--cx-sheet) 95%, #ffffff 5%) 0%, color-mix(in srgb, var(--cx-sheet) 89%, #ffffff 11%) 100%);
            }

            .context-pdf-sheet::before {
                content: 'EXPEDIENTE EN VISTA';
                display: inline-block;
                margin-bottom: 8px;
                font-size: 10px;
                letter-spacing: 0.08em;
                font-weight: 700;
                color: var(--cx-muted);
            }

            .consulta-context-panel .fi-section,
            .consulta-form-panel .fi-section {
                color: var(--cx-text);
            }

            .consulta-form-panel .consultation-recetas-section {
                border: 1px solid color-mix(in srgb, #334155 22%, var(--cx-border) 78%) !important;
                background: linear-gradient(165deg, color-mix(in srgb, #f8fafc 84%, var(--cx-paper) 16%), color-mix(in srgb, #f1f5f9 76%, var(--cx-paper) 24%)) !important;
            }

            .dark .consulta-form-panel .consultation-recetas-section {
                background: linear-gradient(165deg, color-mix(in srgb, #1f2937 34%, var(--cx-paper) 66%), color-mix(in srgb, #111827 30%, var(--cx-paper) 70%)) !important;
            }

            .consulta-form-panel .consultation-recetas-repeater {
                border: 1px solid color-mix(in srgb, #334155 24%, var(--cx-border) 76%);
                border-radius: 10px;
                padding: 14px;
                background: color-mix(in srgb, #f8fafc 78%, var(--cx-paper) 22%);
            }

            .dark .consulta-form-panel .consultation-recetas-repeater {
                background: color-mix(in srgb, #1f2937 28%, var(--cx-paper) 72%);
            }

            .consulta-form-panel .consultation-recetas-preview {
                border: 1px solid color-mix(in srgb, #10b981 30%, var(--cx-border) 70%);
                border-radius: 10px;
                background: linear-gradient(180deg, color-mix(in srgb, #f8fafc 78%, var(--cx-paper) 22%), color-mix(in srgb, #f1f5f9 72%, var(--cx-paper) 28%));
            }

            .dark .consulta-form-panel .consultation-recetas-preview {
                background: linear-gradient(180deg, color-mix(in srgb, #1f2937 34%, var(--cx-paper) 66%), color-mix(in srgb, #111827 26%, var(--cx-paper) 74%));
            }

            .consulta-form-panel .consultation-examenes-section {
                border: 1px solid color-mix(in srgb, #334155 22%, var(--cx-border) 78%) !important;
                background: linear-gradient(165deg, color-mix(in srgb, #f8fafc 84%, var(--cx-paper) 16%), color-mix(in srgb, #f1f5f9 76%, var(--cx-paper) 24%)) !important;
            }

            .dark .consulta-form-panel .consultation-examenes-section {
                background: linear-gradient(165deg, color-mix(in srgb, #1f2937 34%, var(--cx-paper) 66%), color-mix(in srgb, #111827 30%, var(--cx-paper) 70%)) !important;
            }

            .consulta-form-panel .consultation-examenes-repeater {
                border: 1px solid color-mix(in srgb, #334155 24%, var(--cx-border) 76%);
                border-radius: 10px;
                padding: 14px;
                background: color-mix(in srgb, #f8fafc 78%, var(--cx-paper) 22%);
            }

            .dark .consulta-form-panel .consultation-examenes-repeater {
                background: color-mix(in srgb, #1f2937 28%, var(--cx-paper) 72%);
            }
        </style>

        <script>
            (function () {
                const MIN_LEFT = 32;
                const MAX_LEFT = 68;
                const STORAGE_KEY = 'consulta-split-left-width';

                const clamp = (value) => Math.min(MAX_LEFT, Math.max(MIN_LEFT, value));

                const initSplitLayout = () => {
                    const layout = document.getElementById('consultaSplitLayout');
                    const resizer = document.getElementById('consultaResizer');

                    if (!layout || !resizer || layout.dataset.splitReady === '1') {
                        return;
                    }

                    layout.dataset.splitReady = '1';

                    const applyWidth = (leftPercent) => {
                        const width = clamp(leftPercent);
                        layout.style.setProperty('--cx-left-width', width + '%');
                        resizer.setAttribute('aria-valuenow', String(Math.round(width)));
                        localStorage.setItem(STORAGE_KEY, String(width));
                    };

                    const initial = Number(localStorage.getItem(STORAGE_KEY));
                    if (Number.isFinite(initial) && initial > 0) {
                        applyWidth(initial);
                    } else {
                        applyWidth(50);
                    }

                    document.querySelectorAll('[data-layout-action]').forEach((button) => {
                        button.addEventListener('click', () => {
                            const action = button.getAttribute('data-layout-action');

                            if (action === 'focus-left') {
                                applyWidth(64);
                            } else if (action === 'focus-right') {
                                applyWidth(36);
                            } else {
                                applyWidth(50);
                            }
                        });
                    });

                    let dragging = false;

                    const updateFromPointer = (clientX) => {
                        const bounds = layout.getBoundingClientRect();
                        const relative = ((clientX - bounds.left) / bounds.width) * 100;
                        applyWidth(relative);
                    };

                    resizer.addEventListener('pointerdown', (event) => {
                        dragging = true;
                        resizer.setPointerCapture(event.pointerId);
                    });

                    resizer.addEventListener('pointermove', (event) => {
                        if (!dragging) {
                            return;
                        }

                        updateFromPointer(event.clientX);
                    });

                    resizer.addEventListener('pointerup', (event) => {
                        dragging = false;
                        resizer.releasePointerCapture(event.pointerId);
                    });

                    resizer.addEventListener('keydown', (event) => {
                        const current = Number(resizer.getAttribute('aria-valuenow')) || 50;

                        if (event.key === 'ArrowLeft') {
                            event.preventDefault();
                            applyWidth(current - 2);
                        } else if (event.key === 'ArrowRight') {
                            event.preventDefault();
                            applyWidth(current + 2);
                        } else if (event.key === 'Home') {
                            event.preventDefault();
                            applyWidth(MIN_LEFT);
                        } else if (event.key === 'End') {
                            event.preventDefault();
                            applyWidth(MAX_LEFT);
                        }
                    });
                };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initSplitLayout, { once: true });
                } else {
                    initSplitLayout();
                }

                document.addEventListener('livewire:navigated', initSplitLayout);
            })();
        </script>
    @endif
</x-filament-panels::page>
