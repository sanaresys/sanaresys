@extends('onboarding.layout')

@section('content')
<section class="card-premium overflow-hidden min-h-[85vh]">
    <div class="grid lg:grid-cols-12">
        <aside class="lg:col-span-4 pt-4 md:pt-5 lg:pt-6 px-6 md:px-8 lg:px-9 pb-6 md:pb-8 lg:pb-9 border-b lg:border-b-0 lg:border-r" style="border-color: rgba(255,255,255,0.18); background: linear-gradient(165deg, #0e4b4d 0%, #0a5f61 48%, #2b8f90 100%); color: #f8f3ea;">
            <div class="h-full flex flex-col justify-between">
                <div>
                    <p class="uppercase tracking-[0.24em] text-xs font-bold mb-5 text-white/85">Sanaresys</p>
                    <h2 class="display-title text-4xl md:text-5xl font-extrabold leading-tight mb-7">Una base sólida para gestionar tu clínica con orden y control.</h2>
                    <p class="text-sm leading-relaxed text-white/85 mb-8 max-w-sm">
                        Antes de comenzar a atender pacientes y emitir facturas, configura lo esencial para trabajar con orden, seguridad y una mejor experiencia para tus pacientes.
                    </p>
                    <ul class="space-y-4 text-sm text-white/90">
                        <li class="flex items-start gap-3">
                            <span class="font-black text-white/70">01</span>
                            <span><strong class="text-white">Operacion mas eficiente</strong><br><small class="text-white/80">Procesos más claros en recepción, cobros y administración.</small></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="font-black text-white/70">02</span>
                            <span><strong class="text-white">Control y seguridad de datos</strong><br><small class="text-white/80">Configuracion fiscal y datos clave en un solo flujo guiado.</small></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="font-black text-white/70">03</span>
                            <span><strong class="text-white">Mejor experiencia del paciente</strong><br><small class="text-white/80">Cobros claros y atencion mas fluida desde la primera cita.</small></span>
                        </li>
                    </ul>
                </div>
                <p class="text-xs text-white/65 mt-8">© {{ date('Y') }} Sanaresys</p>
            </div>
        </aside>

        <div class="lg:col-span-8 pt-4 md:pt-5 lg:pt-6 px-6 md:px-8 lg:px-9 xl:px-10 pb-6 md:pb-8 lg:pb-9 xl:pb-10" style="background: #f7f5f0;">
            <div class="mb-7">
                <p class="text-xs font-semibold uppercase tracking-[0.18em]" style="color: var(--accent-strong);">Configuracion inicial</p>
                <h3 class="display-title text-3xl md:text-4xl font-extrabold leading-tight mt-3">Configura tu clinica</h3>
                <p class="text-sm mt-2 max-w-xl" style="color: var(--ink-soft);">Completa los siguientes pasos para empezar a utilizar Sanaresys con una base administrativa confiable.</p>
            </div>

            <div class="space-y-4">
                <article class="rounded-xl border p-5" style="border-color: var(--accent); background: #ecf7f7; box-shadow: 0 8px 20px rgba(10,95,97,0.10);">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] font-bold" style="color: var(--accent-strong);">Paso 1</p>
                            <h4 class="font-bold text-lg mt-1" style="color: var(--ink);">Datos del centro</h4>
                        </div>
                        <span class="text-[10px] px-2 py-1 rounded-full font-bold" style="background: var(--accent); color: #fff;">En proceso</span>
                    </div>
                    <p class="text-sm mb-4" style="color: var(--ink-soft);">Ingresa el nombre de la clínica, RTN, ubicación, horarios y datos de contacto.</p>
                    <a href="{{ route('onboarding.step-1') }}" class="onb-button onb-button-primary text-sm px-5 py-3 rounded-lg font-bold inline-flex">
                        Configurar mi clinica
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </article>

                <article class="rounded-xl border p-5" style="border-color: #d6dce2; background: rgba(255,255,255,0.65);">
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] font-bold" style="color: #7c8a98;">Paso 2</p>
                            <h4 class="font-bold text-lg mt-1" style="color: #5c6a78;">Configuracion fiscal (CAI)</h4>
                        </div>
                        <span class="text-xs" style="color: #9ba8b4;">Bloqueado</span>
                    </div>
                    <p class="text-sm" style="color: #7c8a98;">Definira su CAI, rangos y vigencia para emitir facturas sin errores operativos.</p>
                </article>

                <article class="rounded-xl border p-5" style="border-color: #d6dce2; background: rgba(255,255,255,0.65);">
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] font-bold" style="color: #7c8a98;">Paso 3</p>
                            <h4 class="font-bold text-lg mt-1" style="color: #5c6a78;">Catalogo de servicios</h4>
                        </div>
                        <span class="text-xs" style="color: #9ba8b4;">Bloqueado</span>
                    </div>
                    <p class="text-sm" style="color: #7c8a98;">Creara servicios, especialidades y precios para agilizar agenda y cobro.</p>
                </article>

                <article class="rounded-xl border p-5" style="border-color: #d6dce2; background: rgba(255,255,255,0.65);">
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] font-bold" style="color: #7c8a98;">Paso 4 (Opcional)</p>
                            <h4 class="font-bold text-lg mt-1" style="color: #5c6a78;">Agregar medico</h4>
                        </div>
                        <span class="text-xs" style="color: #9ba8b4;">Bloqueado</span>
                    </div>
                    <p class="text-sm" style="color: #7c8a98;">Podras registrar tu primer medico y dejar lista la operacion clinica desde el onboarding.</p>
                </article>
            </div>

            <div class="mt-6 p-4 rounded-xl border flex items-center justify-between gap-4" style="border-color: #cbd8de; background: #edf6f7;">
                <p class="text-sm" style="color: #2d4c59;"><strong>¿Es tu primera vez usando software médico?</strong><br><span class="text-xs" style="color: #4e6a76;">Puedes completar esta configuración ahora y ajustar los datos más adelante si lo necesitas.</span></p>
                <span class="text-xs px-3 py-2 rounded-lg font-semibold" style="background: #ffffff; color: #2d4c59; border: 1px solid #cbd8de;">Asistente guiado</span>
            </div>
        </div>
    </div>
</section>
@endsection
