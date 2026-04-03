@extends('onboarding.layout')

@php $currentStep = 5; @endphp

@section('content')
{{-- Pantalla de completado: hero + resumen de 3 columnas + próximos pasos --}}
<div style="min-height:100vh;display:flex;flex-direction:column;background:var(--onb-surface);">

    {{-- ══════════════════════════════════════════════
         HERO — Celebración profesional
    ══════════════════════════════════════════════ --}}
    <div style="background:linear-gradient(155deg,#004547 0%,#083D3F 55%,#0D5E60 100%);padding:3.5rem 2rem;text-align:center;position:relative;overflow:hidden;">
        {{-- Textura de fondo --}}
        <div style="position:absolute;inset:0;background:radial-gradient(ellipse at 20% 80%,rgba(255,191,6,0.08) 0%,transparent 50%),radial-gradient(ellipse at 80% 10%,rgba(142,210,212,0.10) 0%,transparent 50%);pointer-events:none;"></div>

        <div style="position:relative;z-index:1;max-width:640px;margin:0 auto;">
            {{-- Icono de éxito --}}
            <div class="onb-success-check onb-fade-up" style="margin-bottom:1.5rem;">
                <svg fill="none" viewBox="0 0 32 32" stroke="currentColor" stroke-width="2.5" style="width:32px;height:32px;color:#fff;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 16l7 7 13-13"/>
                </svg>
            </div>

            <h1 class="onb-fade-up onb-delay-1"
                style="font-family:'Plus Jakarta Sans',sans-serif;font-size:clamp(2rem,4vw,2.8rem);font-weight:800;color:#fff;letter-spacing:-0.03em;margin-bottom:0.75rem;line-height:1.1;">
                ¡Tu clínica está lista!
            </h1>

            <p class="onb-fade-up onb-delay-1"
               style="font-size:1rem;color:rgba(255,255,255,0.78);line-height:1.6;margin-bottom:1.75rem;">
                <strong style="color:rgba(255,255,255,0.95);">{{ $centro->nombre_centro }}</strong>
                está configurada y lista para atender pacientes desde hoy.
            </p>

            {{-- Badges de estado --}}
            <div class="onb-fade-up onb-delay-2"
                 style="display:flex;justify-content:center;flex-wrap:wrap;gap:0.5rem;">
                <span style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.35rem 0.9rem;background:rgba(255,255,255,0.14);border:1px solid rgba(255,255,255,0.22);border-radius:999px;font-size:12px;font-weight:700;color:#fff;">
                    <svg fill="none" viewBox="0 0 14 14" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px;color:#6EE7B7;flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2 7l3.5 3.5 6.5-7"/>
                    </svg>
                    Datos del centro
                </span>
                <span style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.35rem 0.9rem;background:rgba(255,255,255,0.14);border:1px solid rgba(255,255,255,0.22);border-radius:999px;font-size:12px;font-weight:700;color:#fff;">
                    @if($centro->onboarding_skipped_cai ?? false)
                    <svg fill="none" viewBox="0 0 14 14" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;color:#FFBF06;flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 4v4M7 10h.01"/>
                        <circle cx="7" cy="7" r="5.5"/>
                    </svg>
                    Facturación (pendiente)
                    @else
                    <svg fill="none" viewBox="0 0 14 14" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px;color:#6EE7B7;flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2 7l3.5 3.5 6.5-7"/>
                    </svg>
                    Facturación fiscal
                    @endif
                </span>
                <span style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.35rem 0.9rem;background:rgba(255,255,255,0.14);border:1px solid rgba(255,255,255,0.22);border-radius:999px;font-size:12px;font-weight:700;color:#fff;">
                    <svg fill="none" viewBox="0 0 14 14" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px;color:#6EE7B7;flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2 7l3.5 3.5 6.5-7"/>
                    </svg>
                    Catálogo de servicios
                </span>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         CONTENIDO — Tarjetas resumen + Próximos pasos
    ══════════════════════════════════════════════ --}}
    <div style="flex:1;padding:2.5rem 1.5rem 3rem;max-width:1080px;margin:0 auto;width:100%;">

        {{-- Grid de 3 tarjetas --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1rem;margin-bottom:2rem;">

            {{-- Tarjeta: Tu Clínica --}}
            <div class="onb-summary-card onb-fade-up onb-delay-1">
                <div class="onb-summary-icon-wrap">
                    <svg fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h11M9 21V3M3 6l6-3 6 3M3 10v11M15 10v11M3 21h18"/>
                    </svg>
                </div>
                <p style="font-size:11px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--onb-primary);margin-bottom:0.4rem;">Tu Clínica</p>
                <h3 style="font-size:1.0625rem;font-weight:800;color:var(--onb-ink);margin-bottom:0.5rem;line-height:1.2;">
                    {{ $centro->nombre_centro }}
                </h3>
                <p style="font-size:13px;color:var(--onb-ink-soft);">RTN: <span style="font-family:monospace;">{{ $centro->rtn }}</span></p>
                @if($centro->telefono)
                <p style="font-size:13px;color:var(--onb-ink-soft);">{{ $centro->telefono }}</p>
                @endif
                @if($centro->direccion)
                <p style="font-size:12px;color:var(--onb-ink-muted);margin-top:0.25rem;line-height:1.4;">{{ Str::limit($centro->direccion, 80) }}</p>
                @endif
                <div style="margin-top:0.875rem;padding-top:0.875rem;border-top:1px solid var(--onb-border);display:flex;align-items:center;gap:0.5rem;">
                    <span style="font-size:12px;color:var(--onb-ink-muted);">Estado del onboarding</span>
                    <span class="onb-badge green">Completado</span>
                </div>
            </div>

            {{-- Tarjeta: Facturación --}}
            <div class="onb-summary-card onb-fade-up onb-delay-2">
                <div class="onb-summary-icon-wrap">
                    <svg fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p style="font-size:11px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--onb-primary);margin-bottom:0.4rem;">Facturación</p>
                @if($centro->onboarding_skipped_cai ?? false)
                    <h3 style="font-size:1.0625rem;font-weight:800;color:var(--onb-ink);margin-bottom:0.5rem;">CAI Pendiente</h3>
                    <p style="font-size:13px;color:var(--onb-ink-soft);line-height:1.5;">Configura tu CAI después para emitir facturas fiscales válidas.</p>
                    <div class="onb-banner amber" style="margin-top:0.875rem;margin-bottom:0;padding:0.6rem 0.75rem;">
                        <svg style="width:14px;height:14px;flex-shrink:0;color:var(--onb-amber);margin-top:1px;" fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                        <div class="onb-banner-body" style="padding:0;">
                            <p style="font-size:11.5px;">Configúralo desde Panel → Configuración</p>
                        </div>
                    </div>
                @else
                    <h3 style="font-size:1.0625rem;font-weight:800;color:var(--onb-ink);margin-bottom:0.5rem;">CAI Configurado</h3>
                    @if($centro->cai_rango_inicial && $centro->cai_rango_final)
                    <p style="font-size:13px;color:var(--onb-ink-soft);">Desde factura #{{ $centro->cai_rango_inicial }} hasta #{{ number_format($centro->cai_rango_final) }}</p>
                    @endif
                    @if($centro->cai_fecha_limite)
                    <p style="font-size:13px;color:var(--onb-ink-soft);margin-top:0.2rem;">Válido hasta: {{ \Carbon\Carbon::parse($centro->cai_fecha_limite)->format('d/m/Y') }}</p>
                    @endif
                    <div class="onb-banner amber" style="margin-top:0.875rem;margin-bottom:0;padding:0.6rem 0.75rem;">
                        <svg style="width:14px;height:14px;flex-shrink:0;color:var(--onb-amber);margin-top:1px;" fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01"/>
                        </svg>
                        <div class="onb-banner-body" style="padding:0;">
                            <p style="font-size:11.5px;">Recuerda renovar el CAI antes de que venza</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Tarjeta: Servicios --}}
            <div class="onb-summary-card onb-fade-up onb-delay-3">
                <div class="onb-summary-icon-wrap">
                    <svg fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <p style="font-size:11px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--onb-primary);margin-bottom:0.4rem;">Servicios</p>
                <h3 style="font-size:1.0625rem;font-weight:800;color:var(--onb-ink);margin-bottom:0.75rem;">
                    {{ $cantidadServicios ?? 0 }} Servicio{{ ($cantidadServicios ?? 0) !== 1 ? 's' : '' }} registrado{{ ($cantidadServicios ?? 0) !== 1 ? 's' : '' }}
                </h3>
                <div style="display:flex;flex-direction:column;gap:0.35rem;">
                    @forelse($servicios ?? [] as $serv)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:0.3rem 0;">
                        <span style="font-size:13px;color:var(--onb-ink-soft);">{{ $serv->nombre }}</span>
                        <span style="font-size:13px;font-weight:700;color:var(--onb-ink);">L. {{ number_format($serv->precio_unitario, 2) }}</span>
                    </div>
                    @empty
                    <p style="font-size:13px;color:var(--onb-ink-muted);">Los servicios se cargarán en el panel.</p>
                    @endforelse
                </div>
                <a href="#" style="display:inline-block;margin-top:0.875rem;font-size:12.5px;font-weight:600;color:var(--onb-primary);text-decoration:underline;text-underline-offset:2px;">
                    + Ver todos los servicios →
                </a>
            </div>
        </div>

        {{-- Próximos pasos --}}
        <div style="max-width:640px;margin:0 auto 2.5rem;">
            <p style="font-size:10.5px;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;color:var(--onb-primary);text-align:center;margin-bottom:1rem;"
               class="onb-fade-up onb-delay-3">
                Próximos pasos sugeridos
            </p>

            <div style="background:var(--onb-surface-card);border-radius:var(--onb-radius-lg);box-shadow:var(--onb-shadow-md);overflow:hidden;"
                 class="onb-fade-up onb-delay-3">

                <a href="#" style="display:flex;align-items:center;gap:1rem;padding:1rem 1.25rem;text-decoration:none;transition:background 150ms ease;"
                   onmouseover="this.style.background='var(--onb-surface-low)'"
                   onmouseout="this.style.background='transparent'">
                    <div style="width:36px;height:36px;border-radius:50%;background:var(--onb-primary-light);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg fill="none" viewBox="0 0 18 18" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--onb-primary);">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4a4 4 0 11-8 0 4 4 0 018 0zM2 18a7 7 0 0114 0"/>
                        </svg>
                    </div>
                    <div style="flex:1;">
                        <p style="font-size:0.9rem;font-weight:700;color:var(--onb-ink);margin:0 0 1px;">Registrar tu primer paciente</p>
                        <p style="font-size:12px;color:var(--onb-ink-muted);margin:0;">Crea el expediente y agenda una cita</p>
                    </div>
                    <svg fill="none" viewBox="0 0 16 16" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px;color:var(--onb-ink-muted);flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3l6 5-6 5"/>
                    </svg>
                </a>

                <div style="height:1px;background:var(--onb-border);margin:0 1.25rem;"></div>

                <a href="#" style="display:flex;align-items:center;gap:1rem;padding:1rem 1.25rem;text-decoration:none;background:var(--onb-amber-bg);transition:background 150ms ease;"
                   onmouseover="this.style.background='rgba(255,191,6,0.18)'"
                   onmouseout="this.style.background='var(--onb-amber-bg)'">
                    <div style="width:36px;height:36px;border-radius:50%;background:rgba(255,191,6,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg fill="none" viewBox="0 0 18 18" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--onb-amber-text);">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4a4 4 0 11-8 0 4 4 0 018 0zM2 18a7 7 0 0114 0M14 9v6M17 12h-6"/>
                        </svg>
                    </div>
                    <div style="flex:1;">
                        <p style="font-size:0.9rem;font-weight:700;color:var(--onb-ink);margin:0 0 1px;">Agregar médicos al equipo</p>
                        <p style="font-size:12px;color:var(--onb-ink-muted);margin:0;">Panel → Equipo médico → Nuevo médico</p>
                    </div>
                    <svg fill="none" viewBox="0 0 16 16" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px;color:var(--onb-ink-muted);flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3l6 5-6 5"/>
                    </svg>
                </a>

                <div style="height:1px;background:var(--onb-border);margin:0 1.25rem;"></div>

                <a href="#" style="display:flex;align-items:center;gap:1rem;padding:1rem 1.25rem;text-decoration:none;transition:background 150ms ease;"
                   onmouseover="this.style.background='var(--onb-surface-low)'"
                   onmouseout="this.style.background='transparent'">
                    <div style="width:36px;height:36px;border-radius:50%;background:var(--onb-primary-light);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg fill="none" viewBox="0 0 18 18" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--onb-primary);">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h12v12H3zM9 3v12M3 9h12"/>
                        </svg>
                    </div>
                    <div style="flex:1;">
                        <p style="font-size:0.9rem;font-weight:700;color:var(--onb-ink);margin:0 0 1px;">Configurar tipos de pago</p>
                        <p style="font-size:12px;color:var(--onb-ink-muted);margin:0;">Efectivo, tarjeta, transferencia y más</p>
                    </div>
                    <svg fill="none" viewBox="0 0 16 16" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px;color:var(--onb-ink-muted);flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3l6 5-6 5"/>
                    </svg>
                </a>
            </div>
        </div>

        {{-- CTA principal --}}
        <div style="text-align:center;" class="onb-fade-up onb-delay-4">
            <form action="{{ route('onboarding.mark-completed') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit"
                        class="onb-btn onb-btn-primary"
                        style="font-size:1rem;padding:0.95rem 2.5rem;min-width:240px;justify-content:center;">
                    Ir al Dashboard
                    <svg fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2.5" style="width:16px;height:16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H2"/>
                    </svg>
                </button>
            </form>
            <p style="margin-top:0.875rem;font-size:12.5px;color:var(--onb-ink-muted);">
                Ya tienes acceso completo a todas las funcionalidades de Sanaresys.
            </p>
        </div>

    </div>
</div>
@endsection
