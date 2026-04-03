@extends('onboarding.layout')

@php $currentStep = 1; @endphp

@section('content')
<div class="onb-split">

    {{-- ══════════════════════════════════════════════
         PANEL IZQUIERDO
    ══════════════════════════════════════════════ --}}
    <aside class="onb-panel-left">
        <div>
            <p class="onb-brand-label">Sanaresys</p>

            <div class="onb-step-counter onb-fade-up">
                <span class="onb-step-num">01</span>
                <span class="onb-step-total">/ 04</span>
            </div>

            <h1 class="onb-panel-headline onb-fade-up onb-delay-1">
                Dinos cómo se llama tu clínica.
            </h1>

            <p class="onb-panel-subtext onb-fade-up onb-delay-1">
                Esta información aparecerá en tus facturas, recibos y documentos oficiales emitidos a tus pacientes.
            </p>

            <div class="onb-panel-divider"></div>

            <p style="font-size:10px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;color:rgba(255,255,255,0.5);margin-bottom:0.75rem;">
                ¿Por qué es importante?
            </p>

            <ul class="onb-benefits onb-fade-up onb-delay-2">
                <li class="onb-benefit-item">
                    <span class="onb-benefit-icon">
                        <svg fill="none" viewBox="0 0 12 12" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2 6l3 3 5-5"/>
                        </svg>
                    </span>
                    <span>Tus pacientes verán este nombre en cada factura</span>
                </li>
                <li class="onb-benefit-item">
                    <span class="onb-benefit-icon">
                        <svg fill="none" viewBox="0 0 12 12" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2 6l3 3 5-5"/>
                        </svg>
                    </span>
                    <span>El SAR requiere datos exactos para validación fiscal</span>
                </li>
            </ul>
        </div>

        <div>
            <div class="onb-progress-dots onb-fade-up onb-delay-3">
                <span class="onb-dot active"></span>
                <span class="onb-dot"></span>
                <span class="onb-dot"></span>
                <span class="onb-dot"></span>
            </div>
            <p class="onb-panel-footer" style="margin-top:1rem;">© {{ date('Y') }} Sanaresys</p>
        </div>
    </aside>

    {{-- ══════════════════════════════════════════════
         PANEL DERECHO — Formulario
    ══════════════════════════════════════════════ --}}
    <main class="onb-panel-right">
        <div class="onb-panel-right-inner">

            {{-- Barra de progreso --}}
            <div class="onb-progress-bar-wrap onb-fade-up">
                <div class="onb-progress-segments">
                    <div class="onb-progress-seg active"></div>
                    <div class="onb-progress-seg"></div>
                    <div class="onb-progress-seg"></div>
                    <div class="onb-progress-seg"></div>
                </div>
                <span class="onb-progress-label">Paso 1 de 4</span>
            </div>

            <h2 class="onb-screen-title onb-fade-up onb-delay-1">Datos del Centro Médico</h2>
            <p class="onb-screen-subtitle onb-fade-up onb-delay-1">
                Confirma y completa la información básica de tu clínica.
            </p>

            <form action="{{ route('onboarding.save-step-1') }}" method="POST">
                @csrf

                {{-- ── TARJETA: Información Legal ── --}}
                <div class="onb-form-card onb-fade-up onb-delay-2">

                    {{-- Sección: Info Legal --}}
                    <div class="onb-form-section">
                        <p class="onb-section-label">Información Legal</p>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">

                            {{-- Nombre (readonly) --}}
                            <div class="onb-field">
                                <label class="onb-label">
                                    Nombre de la clínica
                                    <span class="onb-locked-badge">
                                        <svg fill="none" viewBox="0 0 12 12" stroke="currentColor" stroke-width="2.5"><rect x="2" y="5.5" width="8" height="5.5" rx="1"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5V4a2 2 0 014 0v1.5"/></svg>
                                        Sólo lectura
                                    </span>
                                </label>
                                <input type="text"
                                       class="onb-input"
                                       value="{{ $centro->nombre_centro ?? 'No disponible' }}"
                                       readonly
                                       tabindex="-1">
                                <span class="onb-field-hint">Editable desde Configuración del sistema</span>
                            </div>

                            {{-- RTN (readonly) --}}
                            <div class="onb-field">
                                <label class="onb-label">
                                    RTN Fiscal
                                    <span class="onb-locked-badge">
                                        <svg fill="none" viewBox="0 0 12 12" stroke="currentColor" stroke-width="2.5"><rect x="2" y="5.5" width="8" height="5.5" rx="1"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5V4a2 2 0 014 0v1.5"/></svg>
                                        Sólo lectura
                                    </span>
                                </label>
                                <input type="text"
                                       class="onb-input onb-mono"
                                       value="{{ $centro->rtn ?? 'No disponible' }}"
                                       readonly
                                       tabindex="-1">
                                <span class="onb-field-hint">Requerido por SAR para validar facturas</span>
                            </div>
                        </div>
                    </div>

                    {{-- Sección: Contacto --}}
                    <div class="onb-form-section">
                        <p class="onb-section-label">Contacto</p>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">

                            {{-- Teléfono --}}
                            <div class="onb-field">
                                <label for="telefono" class="onb-label">Teléfono de Contacto</label>
                                <div class="onb-input-wrap">
                                    <span class="onb-input-icon">
                                        <svg fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
                                    </span>
                                    <input type="text"
                                           id="telefono"
                                           name="telefono"
                                           class="onb-input"
                                           value="{{ old('telefono', $centro->telefono ?? '') }}"
                                           required
                                           placeholder="+504 2222-3434"
                                           autocomplete="tel">
                                </div>
                                @error('telefono')
                                    <span class="onb-field-error">{{ $message }}</span>
                                @enderror
                                <span class="onb-field-hint">Los pacientes llamarán aquí para citas</span>
                            </div>

                            {{-- Email --}}
                            <div class="onb-field">
                                <label for="email" class="onb-label">Correo Electrónico</label>
                                <div class="onb-input-wrap">
                                    <span class="onb-input-icon">
                                        <svg fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
                                    </span>
                                    <input type="email"
                                           id="email"
                                           name="email"
                                           class="onb-input"
                                           value="{{ old('email', $centro->email ?? '') }}"
                                           placeholder="contacto@clinica.com"
                                           autocomplete="email">
                                </div>
                                @error('email')
                                    <span class="onb-field-error">{{ $message }}</span>
                                @enderror
                                <span class="onb-field-hint">Para envío de notificaciones y comprobantes</span>
                            </div>
                        </div>
                    </div>

                    {{-- Sección: Ubicación --}}
                    <div class="onb-form-section">
                        <p class="onb-section-label">Ubicación</p>

                        <div class="onb-field">
                            <label for="direccion" class="onb-label">Dirección Física</label>
                            <textarea id="direccion"
                                      name="direccion"
                                      rows="3"
                                      required
                                      class="onb-input"
                                      style="resize:vertical;line-height:1.6;"
                                      placeholder="Ej. Colonia Palmira, Ave. República de Chile, frente a Plaza Médica, 3er piso, Tegucigalpa">{{ old('direccion', $centro->direccion ?? '') }}</textarea>
                            @error('direccion')
                                <span class="onb-field-error">{{ $message }}</span>
                            @enderror
                            <span class="onb-field-hint">Sé específico: incluye referencias visuales y número de piso si aplica</span>
                        </div>
                    </div>
                </div>

                {{-- Navegación --}}
                <div class="onb-nav onb-fade-up onb-delay-3">
                    <a href="{{ route('onboarding.welcome') }}" class="onb-btn onb-btn-ghost">
                        <svg fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2.5" style="width:15px;height:15px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5l-7 5 7 5"/>
                        </svg>
                        Atrás
                    </a>
                    <button type="submit" class="onb-btn onb-btn-primary">
                        Continuar a Facturación
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
