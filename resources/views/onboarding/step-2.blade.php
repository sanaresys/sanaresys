@extends('onboarding.layout')

@php $currentStep = 2; @endphp

@section('content')
<div class="onb-split">

    {{-- ══════════════════════════════════════════════
         PANEL IZQUIERDO
    ══════════════════════════════════════════════ --}}
    <aside class="onb-panel-left">
        <div>
            <p class="onb-brand-label">Sanaresys</p>

            <div class="onb-step-counter onb-fade-up">
                <span class="onb-step-num">02</span>
                <span class="onb-step-total">/ 04</span>
            </div>

            <h1 class="onb-panel-headline onb-fade-up onb-delay-1">
                Configura tu autorización fiscal.
            </h1>

            <p class="onb-panel-subtext onb-fade-up onb-delay-1">
                El CAI es requerido por la SAR de Honduras para emitir facturas válidas y cumplir con la normativa tributaria.
            </p>

            <div class="onb-panel-divider"></div>

            {{-- Card informativa ámbar --}}
            <div class="onb-amber-card onb-fade-up onb-delay-2">
                <p class="onb-amber-label">¿Qué es el CAI?</p>
                <p>Es el Código de Autorización de Impresión que el SAR asigna a tu clínica para emitir facturas fiscales con validez legal.</p>
            </div>

            <p class="onb-optional-note onb-fade-up onb-delay-3">
                Este paso es <strong style="color:rgba(255,255,255,0.7);">OPCIONAL</strong> — puedes configurarlo después desde el panel de administración.
            </p>
        </div>

        <div>
            <div class="onb-progress-dots onb-fade-up onb-delay-3">
                <span class="onb-dot done"></span>
                <span class="onb-dot active"></span>
                <span class="onb-dot"></span>
                <span class="onb-dot"></span>
            </div>
            <p class="onb-panel-footer" style="margin-top:1rem;">© {{ date('Y') }} Sanaresys</p>
        </div>
    </aside>

    {{-- ══════════════════════════════════════════════
         PANEL DERECHO — Formulario CAI
    ══════════════════════════════════════════════ --}}
    <main class="onb-panel-right">
        <div class="onb-panel-right-inner">

            {{-- Barra de progreso --}}
            <div class="onb-progress-bar-wrap onb-fade-up">
                <div class="onb-progress-segments">
                    <div class="onb-progress-seg done"></div>
                    <div class="onb-progress-seg active"></div>
                    <div class="onb-progress-seg"></div>
                    <div class="onb-progress-seg"></div>
                </div>
                <span class="onb-progress-label">Paso 2 de 4</span>
            </div>

            <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.4rem;" class="onb-fade-up onb-delay-1">
                <h2 class="onb-screen-title" style="margin:0;">Configuración Fiscal (CAI)</h2>
                <span class="onb-badge amber">Opcional</span>
            </div>
            <p class="onb-screen-subtitle onb-fade-up onb-delay-1">
                Estos datos autorizan la emisión de facturas fiscales válidas en tu clínica.
            </p>

            {{-- Banner: puedes omitir --}}
            <div class="onb-banner amber onb-fade-up onb-delay-2">
                <svg class="onb-banner-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="onb-banner-body">
                    <p>Si aún no tienes tu CAI, puedes omitir este paso y configurarlo después desde el panel de administración.</p>
                    <a href="{{ route('onboarding.save-step-2') }}" class="onb-banner-link"
                       onclick="event.preventDefault(); document.getElementById('skip-form').submit();">
                        Ir al siguiente paso sin configurar →
                    </a>
                    <form id="skip-form" action="{{ route('onboarding.save-step-2') }}" method="POST" style="display:none;">
                        @csrf
                        <input type="hidden" name="skip" value="1">
                    </form>
                </div>
            </div>

            <form action="{{ route('onboarding.save-step-2') }}" method="POST">
                @csrf

                <div class="onb-form-card onb-fade-up onb-delay-2">

                    {{-- Sección: Código CAI --}}
                    <div class="onb-form-section">
                        <p class="onb-section-label">Código de Autorización</p>

                        <div class="onb-field">
                            <label for="cai_codigo" class="onb-label">Código CAI</label>
                            <input type="text"
                                   id="cai_codigo"
                                   name="cai_codigo"
                                   class="onb-input onb-mono"
                                   value="{{ old('cai_codigo') }}"
                                   maxlength="50"
                                   placeholder="A1B2C3-D4E5F6-G7H8I9-J0K1L2">
                            @error('cai_codigo')
                                <span class="onb-field-error">{{ $message }}</span>
                            @enderror
                            <span class="onb-field-hint">Este código lo proporciona la SAR para autorizar la emisión de facturas</span>
                        </div>
                    </div>

                    {{-- Sección: Rango de facturas --}}
                    <div class="onb-form-section">
                        <p class="onb-section-label">Rango de Facturación</p>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                            <div class="onb-field">
                                <label for="rango_inicial" class="onb-label">Número Inicial</label>
                                <input type="number"
                                       id="rango_inicial"
                                       name="rango_inicial"
                                       class="onb-input"
                                       value="{{ old('rango_inicial') }}"
                                       min="1"
                                       placeholder="1">
                                @error('rango_inicial')
                                    <span class="onb-field-error">{{ $message }}</span>
                                @enderror
                                <span class="onb-field-hint">Primera factura del rango</span>
                            </div>

                            <div class="onb-field">
                                <label for="rango_final" class="onb-label">Número Final</label>
                                <input type="number"
                                       id="rango_final"
                                       name="rango_final"
                                       class="onb-input"
                                       value="{{ old('rango_final') }}"
                                       min="1"
                                       placeholder="10000">
                                @error('rango_final')
                                    <span class="onb-field-error">{{ $message }}</span>
                                @enderror
                                <span class="onb-field-hint">Última factura del rango</span>
                            </div>
                        </div>

                        <div class="onb-banner info" style="margin-top:0.875rem;margin-bottom:0;">
                            <svg class="onb-banner-icon" fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;margin-top:0;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="onb-banner-body">
                                <p style="font-size:0.8125rem;">El sistema numerará tus facturas automáticamente dentro de este rango</p>
                            </div>
                        </div>
                    </div>

                    {{-- Sección: Vigencia --}}
                    <div class="onb-form-section">
                        <p class="onb-section-label">Vigencia</p>

                        <div class="onb-field">
                            <label for="fecha_limite" class="onb-label">Fecha Límite de Emisión</label>
                            <input type="date"
                                   id="fecha_limite"
                                   name="fecha_limite"
                                   class="onb-input"
                                   value="{{ old('fecha_limite') }}"
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            @error('fecha_limite')
                                <span class="onb-field-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="onb-banner amber" style="margin-top:0.875rem;margin-bottom:0;">
                            <svg class="onb-banner-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;margin-top:0;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div class="onb-banner-body">
                                <p style="font-size:0.8125rem;">Después de esta fecha no podrás emitir nuevas facturas con este CAI. Recuerda renovarlo a tiempo.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Navegación --}}
                <div class="onb-nav onb-fade-up onb-delay-3">
                    <a href="{{ route('onboarding.step-1') }}" class="onb-btn onb-btn-ghost">
                        <svg fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2.5" style="width:15px;height:15px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5l-7 5 7 5"/>
                        </svg>
                        Atrás
                    </a>
                    <div class="onb-nav-right">
                        <button type="button"
                                class="onb-btn-text"
                                onclick="document.getElementById('skip-form').submit()">
                            Omitir por ahora →
                        </button>
                        <button type="submit" class="onb-btn onb-btn-primary">
                            Continuar
                            <svg fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2.5" style="width:15px;height:15px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 5-7 5"/>
                            </svg>
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </main>

</div>
@endsection
