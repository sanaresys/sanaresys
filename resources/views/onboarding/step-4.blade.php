@extends('onboarding.layout')

@php $currentStep = 4; @endphp

@section('content')
<div class="onb-split">

    {{-- ══════════════════════════════════════════════
         PANEL IZQUIERDO
    ══════════════════════════════════════════════ --}}
    <aside class="onb-panel-left">
        <div>
            <p class="onb-brand-label">Sanaresys</p>

            <div class="onb-step-counter onb-fade-up">
                <span class="onb-step-num">04</span>
                <span class="onb-step-total">/ 04</span>
            </div>

            <h1 class="onb-panel-headline onb-fade-up onb-delay-1">
                Agrega al primer médico de tu equipo.
            </h1>

            <p class="onb-panel-subtext onb-fade-up onb-delay-1">
                Tener un médico registrado te permite agendar citas y generar facturas de forma inmediata.
            </p>

            <div class="onb-panel-divider"></div>

            {{-- Card ámbar: paso opcional --}}
            <div class="onb-amber-card onb-fade-up onb-delay-2">
                <p class="onb-amber-label">Paso opcional</p>
                <p>Puedes agregar médicos más tarde desde el panel de administración → Equipo médico.</p>
            </div>

            <ul class="onb-benefits onb-fade-up onb-delay-2">
                <li class="onb-benefit-item">
                    <span class="onb-benefit-icon">
                        <svg fill="none" viewBox="0 0 12 12" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2 6l3 3 5-5"/>
                        </svg>
                    </span>
                    <span>Asigna citas al médico desde el primer día</span>
                </li>
                <li class="onb-benefit-item">
                    <span class="onb-benefit-icon">
                        <svg fill="none" viewBox="0 0 12 12" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2 6l3 3 5-5"/>
                        </svg>
                    </span>
                    <span>Sus datos aparecerán en facturas y expedientes</span>
                </li>
            </ul>
        </div>

        <div>
            <div class="onb-progress-dots onb-fade-up onb-delay-3">
                <span class="onb-dot done"></span>
                <span class="onb-dot done"></span>
                <span class="onb-dot done"></span>
                <span class="onb-dot active"></span>
            </div>
            <p class="onb-panel-footer" style="margin-top:1rem;">© {{ date('Y') }} Sanaresys</p>
        </div>
    </aside>

    {{-- ══════════════════════════════════════════════
         PANEL DERECHO — Formulario médico
    ══════════════════════════════════════════════ --}}
    <main class="onb-panel-right">
        <div class="onb-panel-right-inner">

            {{-- Barra de progreso --}}
            <div class="onb-progress-bar-wrap onb-fade-up">
                <div class="onb-progress-segments">
                    <div class="onb-progress-seg done"></div>
                    <div class="onb-progress-seg done"></div>
                    <div class="onb-progress-seg done"></div>
                    <div class="onb-progress-seg active"></div>
                </div>
                <span class="onb-progress-label">Paso 4 de 4</span>
            </div>

            <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.4rem;" class="onb-fade-up onb-delay-1">
                <h2 class="onb-screen-title" style="margin:0;">Agregar Médico</h2>
                <span class="onb-badge amber">Opcional</span>
            </div>
            <p class="onb-screen-subtitle onb-fade-up onb-delay-1">
                Completa los datos del primer médico o profesional de salud de la clínica.
            </p>

            {{-- Banner: puedes omitir --}}
            <div class="onb-banner amber onb-fade-up onb-delay-2">
                <svg class="onb-banner-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="onb-banner-body">
                    <p>Si todavía no tienes los datos del médico, puedes omitir este paso y agregarlo después.</p>
                    <a href="#" class="onb-banner-link"
                       onclick="event.preventDefault(); document.getElementById('skip-medico-form').submit();">
                        Finalizar sin agregar médico →
                    </a>
                </div>
            </div>

            {{-- Formulario de omisión oculto --}}
            <form id="skip-medico-form" action="{{ route('onboarding.skip-medico') }}" method="POST" style="display:none;">
                @csrf
            </form>

            <form action="{{ route('onboarding.save-step-4') }}" method="POST">
                @csrf

                <div class="onb-form-card onb-fade-up onb-delay-2">

                    {{-- Sección: Nombre completo --}}
                    <div class="onb-form-section">
                        <p class="onb-section-label">Nombre Completo</p>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                            <div class="onb-field">
                                <label for="primer_nombre" class="onb-label">Primer nombre</label>
                                <input type="text"
                                       id="primer_nombre"
                                       name="primer_nombre"
                                       required
                                       class="onb-input"
                                       value="{{ old('primer_nombre') }}"
                                       placeholder="María">
                                @error('primer_nombre')
                                    <span class="onb-field-error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="onb-field">
                                <label for="primer_apellido" class="onb-label">Primer apellido</label>
                                <input type="text"
                                       id="primer_apellido"
                                       name="primer_apellido"
                                       required
                                       class="onb-input"
                                       value="{{ old('primer_apellido') }}"
                                       placeholder="González">
                                @error('primer_apellido')
                                    <span class="onb-field-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Sección: Identificación --}}
                    <div class="onb-form-section">
                        <p class="onb-section-label">Identificación</p>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                            <div class="onb-field">
                                <label for="dni" class="onb-label">DNI / Identidad</label>
                                <input type="text"
                                       id="dni"
                                       name="dni"
                                       required
                                       class="onb-input onb-mono"
                                       value="{{ old('dni') }}"
                                       placeholder="0801-1990-12345"
                                       maxlength="20">
                                @error('dni')
                                    <span class="onb-field-error">{{ $message }}</span>
                                @enderror
                                <span class="onb-field-hint">Número de identidad hondureña</span>
                            </div>
                            <div class="onb-field">
                                <label for="numero_colegiacion" class="onb-label">N° de Colegiación</label>
                                <input type="text"
                                       id="numero_colegiacion"
                                       name="numero_colegiacion"
                                       required
                                       class="onb-input onb-mono"
                                       value="{{ old('numero_colegiacion') }}"
                                       placeholder="CMH-12345">
                                @error('numero_colegiacion')
                                    <span class="onb-field-error">{{ $message }}</span>
                                @enderror
                                <span class="onb-field-hint">Colegio Médico de Honduras</span>
                            </div>
                        </div>
                    </div>

                    {{-- Sección: Datos personales --}}
                    <div class="onb-form-section">
                        <p class="onb-section-label">Datos Personales</p>
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;">
                            <div class="onb-field">
                                <label for="sexo" class="onb-label">Sexo</label>
                                <select id="sexo"
                                        name="sexo"
                                        required
                                        class="onb-input"
                                        style="cursor:pointer;">
                                    <option value="">Selecciona</option>
                                    <option value="M" {{ old('sexo') === 'M' ? 'selected' : '' }}>Masculino</option>
                                    <option value="F" {{ old('sexo') === 'F' ? 'selected' : '' }}>Femenino</option>
                                </select>
                                @error('sexo')
                                    <span class="onb-field-error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="onb-field">
                                <label for="fecha_nacimiento" class="onb-label">Fecha de nacimiento</label>
                                <input type="date"
                                       id="fecha_nacimiento"
                                       name="fecha_nacimiento"
                                       required
                                       class="onb-input"
                                       value="{{ old('fecha_nacimiento') }}"
                                       max="{{ date('Y-m-d', strtotime('-18 years')) }}">
                                @error('fecha_nacimiento')
                                    <span class="onb-field-error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="onb-field">
                                <label for="nacionalidad_id" class="onb-label">Nacionalidad</label>
                                <select id="nacionalidad_id"
                                        name="nacionalidad_id"
                                        required
                                        class="onb-input"
                                        style="cursor:pointer;">
                                    <option value="">Selecciona</option>
                                    @foreach($nacionalidades as $nac)
                                        <option value="{{ $nac->id }}" {{ (string)old('nacionalidad_id') === (string)$nac->id ? 'selected' : '' }}>
                                            {{ $nac->nacionalidad }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('nacionalidad_id')
                                    <span class="onb-field-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Sección: Contacto --}}
                    <div class="onb-form-section">
                        <p class="onb-section-label">Contacto</p>
                        <div class="onb-field">
                            <label for="telefono_medico" class="onb-label">Teléfono</label>
                            <div class="onb-input-wrap">
                                <span class="onb-input-icon">
                                    <svg fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                                    </svg>
                                </span>
                                <input type="text"
                                       id="telefono_medico"
                                       name="telefono"
                                       required
                                       class="onb-input"
                                       value="{{ old('telefono') }}"
                                       placeholder="+504 9999-8888">
                            </div>
                            @error('telefono')
                                <span class="onb-field-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Navegación --}}
                <div class="onb-nav onb-fade-up onb-delay-3">
                    <a href="{{ route('onboarding.step-3') }}" class="onb-btn onb-btn-ghost">
                        <svg fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2.5" style="width:15px;height:15px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5l-7 5 7 5"/>
                        </svg>
                        Atrás
                    </a>
                    <div class="onb-nav-right">
                        <button type="button"
                                class="onb-btn-text"
                                onclick="document.getElementById('skip-medico-form').submit()">
                            Omitir por ahora →
                        </button>
                        <button type="submit" class="onb-btn onb-btn-primary">
                            Guardar y Finalizar
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
