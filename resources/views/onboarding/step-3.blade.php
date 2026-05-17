@extends('onboarding.layout')

@php $currentStep = 3; @endphp

@section('content')
<div class="onb-split">

    {{-- ══════════════════════════════════════════════
         PANEL IZQUIERDO
    ══════════════════════════════════════════════ --}}
    <aside class="onb-panel-left">
        <div>
            <p class="onb-brand-label">Sanaresys</p>

            <div class="onb-step-counter onb-fade-up">
                <span class="onb-step-num">03</span>
                <span class="onb-step-total">/ 04</span>
            </div>

            <h1 class="onb-panel-headline onb-fade-up onb-delay-1">
                ¿Qué servicios ofrece tu clínica?
            </h1>

            <p class="onb-panel-subtext onb-fade-up onb-delay-1">
                Define tus servicios desde el inicio para agilizar el cobro y la generación de facturas desde la primera cita.
            </p>

            <div class="onb-panel-divider"></div>

            {{-- Sugerencias en panel --}}
            <p style="font-size:10px;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;color:rgba(255,255,255,0.5);margin-bottom:0.75rem;"
               class="onb-fade-up onb-delay-2">
                💡 Sugerencias comunes
            </p>

            <div style="display:flex;flex-wrap:wrap;gap:0.4rem;margin-bottom:1.5rem;" class="onb-fade-up onb-delay-2">
                @foreach(['Consulta General', 'Consulta Especialista', 'Control Prenatal', 'Pediatría', 'Curaciones', 'Laboratorio', 'Ultrasonido', 'Presión Arterial'] as $sug)
                <span style="font-size:11px;font-weight:600;padding:0.25rem 0.6rem;background:rgba(255,255,255,0.13);border:1px solid rgba(255,255,255,0.2);border-radius:999px;color:rgba(255,255,255,0.87);cursor:pointer;"
                      onclick="useSuggestion('{{ $sug }}')">
                    {{ $sug }}
                </span>
                @endforeach
            </div>
        </div>

        <div>
            <div class="onb-progress-dots onb-fade-up onb-delay-3">
                <span class="onb-dot done"></span>
                <span class="onb-dot done"></span>
                <span class="onb-dot active"></span>
                <span class="onb-dot"></span>
            </div>
            <p class="onb-panel-footer" style="margin-top:1rem;">© {{ date('Y') }} Sanaresys</p>
        </div>
    </aside>

    {{-- ══════════════════════════════════════════════
         PANEL DERECHO — Lista de servicios
    ══════════════════════════════════════════════ --}}
    <main class="onb-panel-right">
        <div class="onb-panel-right-inner">

            {{-- Barra de progreso --}}
            <div class="onb-progress-bar-wrap onb-fade-up">
                <div class="onb-progress-segments">
                    <div class="onb-progress-seg done"></div>
                    <div class="onb-progress-seg done"></div>
                    <div class="onb-progress-seg active"></div>
                    <div class="onb-progress-seg"></div>
                </div>
                <span class="onb-progress-label">Paso 3 de 4</span>
            </div>

            <h2 class="onb-screen-title onb-fade-up onb-delay-1">Catálogo de Servicios</h2>
            <p class="onb-screen-subtitle onb-fade-up onb-delay-1">
                Agrega al menos un servicio. Puedes añadir, editar o eliminar más desde tu panel en cualquier momento.
            </p>

            <form action="{{ route('onboarding.save-step-3') }}" method="POST" id="serviciosForm">
                @csrf

                {{-- Contenedor de tarjetas de servicio --}}
                <div id="servicios-container" class="onb-fade-up onb-delay-2" style="display:flex;flex-direction:column;gap:0.6rem;margin-bottom:0.75rem;">
                    {{-- Servicio 1 predefinido --}}
                    <div class="onb-service-card servicio-item" id="service-0">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
                            <span style="font-size:11px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--onb-primary);">
                                Servicio 1
                            </span>
                            {{-- El primer servicio no se puede eliminar --}}
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 160px;gap:0.875rem;">
                            <div class="onb-field">
                                <label class="onb-label">Nombre del servicio</label>
                                <input type="text"
                                       name="servicios[0][nombre]"
                                       required
                                       class="onb-input"
                                       placeholder="Ej: Consulta General">
                                @error('servicios.0.nombre')
                                    <span class="onb-field-error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="onb-field">
                                <label class="onb-label">Precio (L.)</label>
                                <input type="number"
                                       name="servicios[0][precio]"
                                       required
                                       min="0"
                                       step="0.01"
                                       class="onb-input"
                                       placeholder="450.00">
                                @error('servicios.0.precio')
                                    <span class="onb-field-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="onb-field" style="margin-top:0.6rem;">
                            <input type="text"
                                   name="servicios[0][descripcion]"
                                   class="onb-input"
                                   style="font-size:0.875rem;"
                                   placeholder="Descripción opcional (Ej: Medicina General)">
                        </div>
                    </div>
                </div>

                {{-- Botón agregar servicio --}}
                <button type="button"
                        id="add-servicio"
                        class="onb-add-service-btn onb-fade-up onb-delay-2">
                    <svg fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2.5" style="width:15px;height:15px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 4v12M4 10h12"/>
                    </svg>
                    Agregar otro servicio
                </button>

                {{-- Contador resumen --}}
                <div id="resumen-servicios"
                     style="display:flex;align-items:center;justify-content:space-between;padding:0.75rem 0;margin:0.75rem 0;border-top:1px solid var(--onb-border);border-bottom:1px solid var(--onb-border);"
                     class="onb-fade-up onb-delay-3">
                    <span id="contador-label" style="font-size:13px;color:var(--onb-ink-soft);">
                        <strong style="color:var(--onb-ink);">1 servicio</strong> agregado
                    </span>
                    <span style="font-size:12px;color:var(--onb-ink-muted);">
                        Mínimo 1 requerido
                    </span>
                </div>

                {{-- Navegación --}}
                <div class="onb-nav onb-fade-up onb-delay-3">
                    <a href="{{ route('onboarding.step-2') }}" class="onb-btn onb-btn-ghost">
                        <svg fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2.5" style="width:15px;height:15px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5l-7 5 7 5"/>
                        </svg>
                        Atrás
                    </a>
                    <button type="submit" class="onb-btn onb-btn-primary">
                        Guardar y Continuar
                        <svg fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2.5" style="width:15px;height:15px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 5-7 5"/>
                        </svg>
                    </button>
                </div>

            </form>
        </div>
    </main>

</div>
@endsection

@push('scripts')
<script>
    let servicioCount = 1;

    // Usar sugerencia del panel izquierdo
    function useSuggestion(nombre) {
        const container = document.getElementById('servicios-container');
        const firstInput = container.querySelector('input[name="servicios[0][nombre]"]');
        if (firstInput && firstInput.value.trim() === '') {
            firstInput.value = nombre;
            firstInput.focus();
            actualizarContador();
            return;
        }
        agregarServicio(nombre);
    }

    function actualizarContador() {
        const items = document.querySelectorAll('.servicio-item');
        const n = items.length;
        const label = document.getElementById('contador-label');
        if (label) {
            label.innerHTML = `<strong style="color:var(--onb-ink);">${n} servicio${n !== 1 ? 's' : ''}</strong> agregado${n !== 1 ? 's' : ''}`;
        }
    }

    function agregarServicio(nombreSugerido = '') {
        const container = document.getElementById('servicios-container');
        const idx = servicioCount;

        const card = document.createElement('div');
        card.className = 'onb-service-card servicio-item onb-fade-up';
        card.innerHTML = `
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
                <span style="font-size:11px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--onb-primary);">
                    Servicio ${idx + 1}
                </span>
                <button type="button"
                        onclick="this.closest('.servicio-item').remove(); actualizarContador();"
                        style="display:inline-flex;align-items:center;gap:4px;font-size:12px;font-weight:600;color:#C0392B;background:rgba(192,57,43,0.08);border:none;border-radius:6px;padding:3px 9px;cursor:pointer;">
                    <svg fill="none" viewBox="0 0 16 16" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2 4h12M5 4V3a1 1 0 011-1h4a1 1 0 011 1v1m2 0l-1 10a1 1 0 01-1 1H5a1 1 0 01-1-1L3 4"/>
                    </svg>
                    Eliminar
                </button>
            </div>
            <div style="display:grid;grid-template-columns:1fr 160px;gap:0.875rem;">
                <div class="onb-field">
                    <label class="onb-label">Nombre del servicio</label>
                    <input type="text"
                           name="servicios[${idx}][nombre]"
                           required
                           value="${nombreSugerido}"
                           class="onb-input"
                           placeholder="Ej: Consulta Especialista">
                </div>
                <div class="onb-field">
                    <label class="onb-label">Precio (L.)</label>
                    <input type="number"
                           name="servicios[${idx}][precio]"
                           required
                           min="0"
                           step="0.01"
                           class="onb-input"
                           placeholder="800.00">
                </div>
            </div>
            <div class="onb-field" style="margin-top:0.6rem;">
                <input type="text"
                       name="servicios[${idx}][descripcion]"
                       class="onb-input"
                       style="font-size:0.875rem;"
                       placeholder="Descripción opcional">
            </div>
        `;

        container.appendChild(card);
        servicioCount++;
        actualizarContador();

        // Enfocar el input de nombre
        const newInput = card.querySelector('input[type="text"]');
        if (newInput) setTimeout(() => newInput.focus(), 50);
    }

    document.getElementById('add-servicio').addEventListener('click', () => agregarServicio());
</script>
@endpush
