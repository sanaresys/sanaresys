@extends('onboarding.layout')

@section('content')
<div class="onb-split">

    {{-- ══════════════════════════════════════════════
         PANEL IZQUIERDO — Contexto emocional + marca
    ══════════════════════════════════════════════ --}}
    <aside class="onb-panel-left">
        <div>
            <p class="onb-brand-label">Sanaresys</p>

            <h1 class="onb-panel-headline onb-fade-up">
                Tu clínica, lista para operar desde hoy.
            </h1>

            <p class="onb-panel-subtext onb-fade-up onb-delay-1">
                Configura lo esencial en 4 pasos y empieza a atender pacientes con orden, seguridad y control.
            </p>

            <div class="onb-panel-divider"></div>

            <ul class="onb-benefits onb-fade-up onb-delay-2">
                <li class="onb-benefit-item">
                    <span class="onb-benefit-icon">
                        <svg fill="none" viewBox="0 0 12 12" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2 6l3 3 5-5"/>
                        </svg>
                    </span>
                    <span>Cumplimiento fiscal con SAR Honduras</span>
                </li>
                <li class="onb-benefit-item">
                    <span class="onb-benefit-icon">
                        <svg fill="none" viewBox="0 0 12 12" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2 6l3 3 5-5"/>
                        </svg>
                    </span>
                    <span>Servicios y precios listos desde la primera cita</span>
                </li>
                <li class="onb-benefit-item">
                    <span class="onb-benefit-icon">
                        <svg fill="none" viewBox="0 0 12 12" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2 6l3 3 5-5"/>
                        </svg>
                    </span>
                    <span>Datos protegidos y aislados por clínica</span>
                </li>
            </ul>
        </div>

        <p class="onb-panel-footer">© {{ date('Y') }} Sanaresys</p>
    </aside>

    {{-- ══════════════════════════════════════════════
         PANEL DERECHO — Contenido de acción
    ══════════════════════════════════════════════ --}}
    <main class="onb-panel-right">
        <div class="onb-panel-right-inner">

            {{-- Badge de configuración inicial --}}
            <div style="margin-bottom:1.5rem;" class="onb-fade-up">
                <span class="onb-badge teal">
                    <svg fill="none" viewBox="0 0 16 16" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 1v14M1 8h14"/>
                    </svg>
                    Configuración inicial
                </span>
            </div>

            <h2 class="onb-screen-title onb-fade-up onb-delay-1">
                Bienvenido a Sanaresys
            </h2>
            <p style="font-size:1rem;font-weight:600;color:var(--onb-primary);margin-bottom:0.5rem;" class="onb-fade-up onb-delay-1">
                {{ $centro->nombre_centro ?? 'Tu Clínica' }}
            </p>
            <p class="onb-screen-subtitle onb-fade-up onb-delay-2">
                ¿Listo? Vamos a configurar tu clínica paso a paso. Es rápido y puedes retomarlo cuando quieras.
            </p>

            {{-- ── Lista de pasos ── --}}
            <div style="display:flex;flex-direction:column;gap:0.6rem;margin-bottom:2rem;" class="onb-fade-up onb-delay-2">

                {{-- Paso 1 — ACTIVO --}}
                <div class="onb-step-card active">
                    <span class="onb-step-circle active">1</span>
                    <div class="onb-step-card-body">
                        <p class="onb-step-card-title">Datos del centro</p>
                        <p class="onb-step-card-desc">Dirección, contacto y logos de la clínica</p>
                    </div>
                    <svg fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--onb-primary);flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 5-7 5"/>
                    </svg>
                </div>

                {{-- Paso 2 — Bloqueado --}}
                <div class="onb-step-card locked">
                    <span class="onb-step-circle locked">
                        <svg fill="none" viewBox="0 0 14 14" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px;">
                            <rect x="2" y="6" width="10" height="7" rx="1" stroke-linecap="round" stroke-linejoin="round"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 6V4a2 2 0 014 0v2"/>
                        </svg>
                    </span>
                    <div class="onb-step-card-body">
                        <p class="onb-step-card-title">Configuración Fiscal (CAI)</p>
                        <p class="onb-step-card-desc">Facturación legal y registros tributarios</p>
                    </div>
                    <span class="onb-badge locked">2</span>
                </div>

                {{-- Paso 3 — Bloqueado --}}
                <div class="onb-step-card locked">
                    <span class="onb-step-circle locked">
                        <svg fill="none" viewBox="0 0 14 14" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px;">
                            <rect x="2" y="6" width="10" height="7" rx="1" stroke-linecap="round" stroke-linejoin="round"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 6V4a2 2 0 014 0v2"/>
                        </svg>
                    </span>
                    <div class="onb-step-card-body">
                        <p class="onb-step-card-title">Catálogo de servicios</p>
                        <p class="onb-step-card-desc">Define consultas, precios y especialidades</p>
                    </div>
                    <span class="onb-badge locked">3</span>
                </div>

                {{-- Paso 4 — Bloqueado + Opcional --}}
                <div class="onb-step-card locked">
                    <span class="onb-step-circle locked">
                        <svg fill="none" viewBox="0 0 14 14" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px;">
                            <rect x="2" y="6" width="10" height="7" rx="1" stroke-linecap="round" stroke-linejoin="round"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 6V4a2 2 0 014 0v2"/>
                        </svg>
                    </span>
                    <div class="onb-step-card-body">
                        <p class="onb-step-card-title">Agregar médico</p>
                        <p class="onb-step-card-desc">Configura el primer profesional de la salud</p>
                    </div>
                    <span class="onb-badge amber">Opcional</span>
                </div>
            </div>

            {{-- CTA principal --}}
            <a href="{{ route('onboarding.step-1') }}"
               class="onb-btn onb-btn-primary onb-fade-up onb-delay-3"
               style="width:100%;justify-content:center;font-size:1rem;padding:0.9rem 1.5rem;">
                Comenzar configuración
                <svg fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2.5" style="width:18px;height:18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 5-7 5"/>
                </svg>
            </a>

            <p style="text-align:center;margin-top:1rem;font-size:0.8125rem;color:var(--onb-ink-muted);" class="onb-fade-up onb-delay-4">
                <span style="display:inline-flex;align-items:center;gap:0.35rem;">
                    <svg fill="none" viewBox="0 0 16 16" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;color:#FFBF06">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 1.5a6.5 6.5 0 100 13 6.5 6.5 0 000-13zM8 5v3.5M8 10.5v.5"/>
                    </svg>
                    ¿Primera vez usando software médico? No te preocupes, te guiamos.
                </span>
            </p>

        </div>
    </main>

</div>
@endsection
