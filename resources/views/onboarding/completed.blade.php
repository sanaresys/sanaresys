@extends('onboarding.layout')

@php
    $currentStep = 5;
@endphp

@section('content')
<div class="card-premium overflow-hidden min-h-[88vh]" style="background: radial-gradient(circle at 12% 8%, rgba(15,138,141,0.09), transparent 36%), radial-gradient(circle at 88% 22%, rgba(198,140,47,0.08), transparent 34%), linear-gradient(180deg, #f1f5f7 0%, #eef3f6 100%);">
    <div class="px-5 md:px-10 py-8 md:py-10">
        <div class="max-w-5xl mx-auto rounded-2xl" style="background: linear-gradient(180deg, #ffffff 0%, #fbfdfe 100%); border: 1px solid #dbe4ea; box-shadow: 0 18px 38px rgba(16, 33, 42, 0.1);">
            <div class="px-6 md:px-10 pt-8 md:pt-10 pb-7 border-b text-center" style="border-color: #e8edf1;">
                <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-4" style="background: rgba(15,138,141,0.12); box-shadow: 0 6px 14px rgba(15,138,141,0.18);">
                    <svg class="w-7 h-7" style="color: var(--onb-accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-extrabold" style="color: #0f2230;">Configuración Completa!</h1>
                <div class="max-w-md mx-auto mt-4 p-2 rounded-md" style="background: #f7fbfc; border: 1px solid #e3ecef;">
                    <div class="flex justify-between text-[11px] font-semibold mb-1" style="color: #3f5661;">
                        <span>Completado</span>
                        <span>100%</span>
                    </div>
                    <div class="h-2 rounded-full" style="background: #dcecef;">
                        <div class="h-2 rounded-full" style="width: 100%; background: linear-gradient(90deg, #0f8a8d 0%, #17a2a5 100%);"></div>
                    </div>
                </div>
                <p class="text-sm mt-4" style="color: #5f727d;">Sistema listo. Ya puedes ir a tu panel y empezar a trabajar con operación diaria.</p>
            </div>

            <div class="px-6 md:px-10 py-7 md:py-8 space-y-7" style="background: linear-gradient(180deg, rgba(15,138,141,0.02) 0%, rgba(255,255,255,0) 30%);">
                <div class="grid md:grid-cols-3 gap-4 md:gap-5">
                    <article class="rounded-xl overflow-hidden" style="border: 1px solid #d0dde6; background: linear-gradient(180deg, #ffffff 0%, #f7fbfd 100%); box-shadow: 0 6px 18px rgba(16,33,42,0.06);">
                        <div class="h-14" style="background: linear-gradient(120deg, #8bc7d9 0%, #6fb0c6 45%, #4a8ca7 100%);"></div>
                        <div class="p-4">
                            <p class="text-[11px] font-extrabold uppercase tracking-wide" style="color: #0f8a8d;">Centro</p>
                            <h3 class="font-bold text-lg mt-1" style="color: #182c39;">{{ $centro->nombre_centro }}</h3>
                            <p class="text-xs mt-1" style="color: #647985;">RTN: {{ $centro->rtn }}</p>
                        </div>
                    </article>

                    <article class="rounded-xl overflow-hidden" style="border: 1px solid #d0dde6; background: linear-gradient(180deg, #ffffff 0%, #f7fbfd 100%); box-shadow: 0 6px 18px rgba(16,33,42,0.06);">
                        <div class="h-14" style="background: linear-gradient(120deg, #9fd5e3 0%, #82bdcf 50%, #5c9cb2 100%);"></div>
                        <div class="p-4">
                            <p class="text-[11px] font-extrabold uppercase tracking-wide" style="color: #0f8a8d;">Facturación</p>
                            @if($centro->onboarding_skipped_cai)
                                <h3 class="font-bold text-lg mt-1" style="color: #182c39;">Pendiente</h3>
                                <p class="text-xs mt-1" style="color: #647985;">CAI se configurará después.</p>
                            @else
                                <h3 class="font-bold text-lg mt-1" style="color: #182c39;">Activa</h3>
                                <p class="text-xs mt-1" style="color: #647985;">CAI configurado correctamente.</p>
                            @endif
                        </div>
                    </article>

                    <article class="rounded-xl overflow-hidden" style="border: 1px solid #d0dde6; background: linear-gradient(180deg, #ffffff 0%, #f7fbfd 100%); box-shadow: 0 6px 18px rgba(16,33,42,0.06);">
                        <div class="h-14" style="background: linear-gradient(120deg, #8ad0ce 0%, #73bcbc 50%, #4b9ea0 100%);"></div>
                        <div class="p-4">
                            <p class="text-[11px] font-extrabold uppercase tracking-wide" style="color: #0f8a8d;">Servicios</p>
                            <h3 class="font-bold text-lg mt-1" style="color: #182c39;">Catálogo Inicial</h3>
                            <p class="text-xs mt-1" style="color: #647985;">Listo para asignar en citas.</p>
                        </div>
                    </article>
                </div>

                <div class="grid md:grid-cols-2 gap-4 md:gap-5">
                    <section class="rounded-xl p-5" style="border: 1px solid #d0dde6; background: linear-gradient(135deg, rgba(15,138,141,0.06) 0%, #f8fbfd 55%); box-shadow: 0 6px 16px rgba(16,33,42,0.05);">
                        <div class="flex items-center gap-2.5 mb-3">
                            <div class="w-7 h-7 rounded-md flex items-center justify-center" style="background: #0f8a8d;">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <h3 class="text-sm font-extrabold uppercase tracking-wide" style="color: #1a303d;">Próximos Pasos</h3>
                        </div>
                        <ul class="space-y-2.5 text-sm">
                            <li class="flex items-start gap-2"><span style="color: #0f8a8d;">•</span><span style="color: #4f6672;">Registrar médicos y personal de apoyo.</span></li>
                            <li class="flex items-start gap-2"><span style="color: #0f8a8d;">•</span><span style="color: #4f6672;">Crear pacientes y expedientes iniciales.</span></li>
                            <li class="flex items-start gap-2"><span style="color: #0f8a8d;">•</span><span style="color: #4f6672;">Configurar horarios y disponibilidad de citas.</span></li>
                        </ul>
                    </section>

                    <section class="rounded-xl p-5" style="border: 1px solid #d0dde6; background: linear-gradient(135deg, rgba(15,138,141,0.06) 0%, #f8fbfd 55%); box-shadow: 0 6px 16px rgba(16,33,42,0.05);">
                        <div class="flex items-center gap-2.5 mb-3">
                            <div class="w-7 h-7 rounded-md flex items-center justify-center" style="background: #0f8a8d;">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                            <h3 class="text-sm font-extrabold uppercase tracking-wide" style="color: #1a303d;">Valor Inmediato</h3>
                        </div>
                        <ul class="space-y-2.5 text-sm">
                            <li class="flex items-start gap-2"><span style="color: #0f8a8d;">•</span><span style="color: #4f6672;">Agenda ordenada y control de flujo diario.</span></li>
                            <li class="flex items-start gap-2"><span style="color: #0f8a8d;">•</span><span style="color: #4f6672;">Base administrativa unificada y trazable.</span></li>
                            <li class="flex items-start gap-2"><span style="color: #0f8a8d;">•</span><span style="color: #4f6672;">Preparado para reportes y facturación.</span></li>
                        </ul>
                    </section>
                </div>

                <form action="{{ route('onboarding.mark-completed') }}" method="POST" class="pt-3 text-center">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-7 py-3 text-sm rounded-lg font-bold text-white transition-all"
                            style="background: linear-gradient(90deg, #0f8a8d 0%, #167f92 100%); box-shadow: 0 6px 16px rgba(15,138,141,0.28);"
                            onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 10px 20px rgba(15,138,141,0.35)'"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 6px 16px rgba(15,138,141,0.28)'">
                        Ir al Panel de Administración
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Intentionally left minimal to avoid distracting effects on completion page.
</script>
@endpush
