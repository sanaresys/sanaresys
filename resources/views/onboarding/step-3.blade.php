@extends('onboarding.layout')

@php
    $currentStep = 3;
@endphp

@section('content')
<div class="card-premium overflow-hidden min-h-[88vh]">
    <!-- Header limpio -->
    <div class="px-8 pt-4 pb-5 md:px-12 md:pt-5 md:pb-6 border-b" style="border-color: #e8e5df; background: #ffffff;">
        <div class="flex items-start justify-between gap-6 mb-4">
            <div>
                <h1 class="display-title text-3xl md:text-4xl font-bold" style="color: var(--onb-ink);">Servicios Médicos</h1>
                <p class="mt-1 text-sm leading-relaxed" style="color: var(--onb-ink); opacity: 0.7;">Define los servicios que ofreces a tus pacientes.</p>
            </div>
            <div class="text-right min-w-[80px]">
                <p class="text-2xl font-bold" style="color: var(--onb-accent);">75%</p>
                <p class="text-xs mt-1" style="color: var(--onb-ink); opacity: 0.6;">Avance</p>
            </div>
        </div>

        <!-- Barra de progreso -->
        <div class="flex items-center gap-2">
            <div class="w-16 h-1.5 rounded-full" style="background: var(--onb-accent);"></div>
            <span class="text-xs font-bold" style="color: #b0a99a;">1. DATOS BÁSICOS</span>
            <div class="flex-1 h-1 rounded-full" style="background: var(--onb-accent);"></div>
            <span class="text-xs font-bold" style="color: #b0a99a;">2. FACTURACIÓN</span>
            <div class="flex-1 h-1 rounded-full" style="background: var(--onb-accent);"></div>
            <span class="text-xs font-bold" style="color: var(--onb-accent);">3. SERVICIOS</span>
            <div class="flex-1 h-1 rounded-full" style="background: #e8e5df;"></div>
            <span class="text-xs font-bold" style="color: #b0a99a;">4. COMPLETO</span>
        </div>
    </div>

    <div class="px-8 py-6 md:px-12 md:py-8" style="background: #fafaf8;">
        <form action="{{ route('onboarding.save-step-3') }}" method="POST" id="serviciosForm">
            @csrf

            <!-- Tarjeta principal -->
            <div style="background: #ffffff; border: 1px solid #e8e5df; border-radius: 0.75rem;">
                <!-- Header -->
                <div class="px-6 md:px-8 py-6 md:py-8 border-b" style="border-color: #e8e5df;">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background: rgba(15, 138, 141, 0.1);">
                            <svg class="w-6 h-6" style="color: var(--onb-accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-lg font-bold" style="color: var(--onb-ink);">Catálogo de Servicios</h2>
                            <p class="text-sm mt-1" style="color: var(--onb-ink); opacity: 0.6;">Agrega al menos un servicio. Puedes añadir más después desde tu panel.</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-semibold flex items-center gap-1 transition-opacity" id="save-indicator" style="color: var(--onb-accent); opacity: 0;">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                                </svg>
                                Guardado
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Contenido del formulario -->
                <div class="px-8 md:px-12 py-8 md:py-10 space-y-10">
                    <!-- Servicios dinámicos -->
                    <div id="servicios-container" class="space-y-8">
                        <!-- Servicio 1 predefinido -->
                        <div class="servicio-item p-6 rounded-xl" style="background: linear-gradient(135deg, rgba(15,138,141,0.03) 0%, rgba(248,243,234,0.5) 100%); border: 1px solid #d0cab5;">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: var(--onb-accent); box-shadow: 0 2px 8px rgba(15,138,141,0.25);">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-extrabold text-sm uppercase tracking-wide" style="color: var(--onb-ink); opacity: 0.95;">SERVICIO 1</h3>
                                    <p class="text-xs font-medium mt-0.5" style="color: var(--onb-ink); opacity: 0.55;">Información del servicio</p>
                                </div>
                            </div>
                            
                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="md:col-span-2 space-y-2">
                                    <label class="font-extrabold text-sm block" style="color: var(--onb-ink);">Nombre del Servicio</label>
                                    <input type="text" 
                                           name="servicios[0][nombre]" 
                                           required
                                           onchange="showSaveIndicator()"
                                           class="w-full px-4 py-4 border-2 rounded-lg transition-all text-base" 
                                           style="border-color: #bfb8a5; background: #ffffff; color: var(--onb-ink); font-weight: 600;"
                                           placeholder="Ej: Consulta General"
                                           onfocus="this.style.borderColor='var(--onb-accent)'; this.style.boxShadow='0 4px 16px rgba(15,138,141,0.2)'"
                                           onblur="this.style.borderColor='#bfb8a5'; this.style.boxShadow='none'"
                                           onmouseover="this.style.borderColor='#9a9280'"
                                           onmouseout="if(this !== document.activeElement) this.style.borderColor='#bfb8a5'">
                                    <p class="text-xs font-medium" style="color: var(--onb-ink); opacity: 0.6;">Nombre del servicio que verán tus pacientes</p>
                                </div>

                                <div class="space-y-2">
                                    <label class="font-extrabold text-sm block" style="color: var(--onb-ink);">Precio en Lempiras</label>
                                    <input type="number" 
                                           name="servicios[0][precio]" 
                                           required
                                           min="0"
                                           step="0.01"
                                           onchange="showSaveIndicator()"
                                           class="w-full px-4 py-4 border-2 rounded-lg transition-all text-base" 
                                           style="border-color: #bfb8a5; background: #ffffff; color: var(--onb-ink); font-weight: 600;"
                                           placeholder="500.00"
                                           onfocus="this.style.borderColor='var(--onb-accent)'; this.style.boxShadow='0 4px 16px rgba(15,138,141,0.2)'"
                                           onblur="this.style.borderColor='#bfb8a5'; this.style.boxShadow='none'"
                                           onmouseover="this.style.borderColor='#9a9280'"
                                           onmouseout="if(this !== document.activeElement) this.style.borderColor='#bfb8a5'">
                                    <p class="text-xs font-medium" style="color: var(--onb-ink); opacity: 0.6;">Costo del servicio (L)</p>
                                </div>

                                <div class="space-y-2">
                                    <label class="font-extrabold text-sm block" style="color: var(--onb-ink);">Descripción</label>
                                    <input type="text" 
                                           name="servicios[0][descripcion]" 
                                           onchange="showSaveIndicator()"
                                           class="w-full px-4 py-4 border-2 rounded-lg transition-all text-base" 
                                           style="border-color: #bfb8a5; background: #ffffff; color: var(--onb-ink); font-weight: 600;"
                                           placeholder="Medicina General"
                                           onfocus="this.style.borderColor='var(--onb-accent)'; this.style.boxShadow='0 4px 16px rgba(15,138,141,0.2)'"
                                           onblur="this.style.borderColor='#bfb8a5'; this.style.boxShadow='none'"
                                           onmouseover="this.style.borderColor='#9a9280'"
                                           onmouseout="if(this !== document.activeElement) this.style.borderColor='#bfb8a5'">
                                    <p class="text-xs font-medium" style="color: var(--onb-ink); opacity: 0.6;">Opcional - Detalles adicionales</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botón agregar servicio -->
                    <div class="flex justify-center">
                        <button type="button" 
                                id="add-servicio"
                                class="inline-flex items-center gap-2 px-6 py-3 border-2 border-dashed rounded-lg font-bold transition-all text-sm"
                                style="border-color: #bfb8a5; color: var(--onb-accent); background: rgba(15,138,141,0.05);"
                                onmouseover="this.style.background='rgba(15,138,141,0.1)'; this.style.borderColor='var(--onb-accent)'"
                                onmouseout="this.style.background='rgba(15,138,141,0.05)'; this.style.borderColor='#bfb8a5'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Agregar Otro Servicio
                        </button>
                    </div>

                    <!-- Sugerencias -->
                    <div class="p-5 rounded-xl border-2 border-dashed" 
                         style="border-color: #bfb8a5; background: linear-gradient(135deg, rgba(15,138,141,0.04) 0%, rgba(248,243,234,0.3) 100%);">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: rgba(15,138,141,0.15);">
                                <svg class="w-5 h-5" style="color: var(--onb-accent);" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-extrabold text-sm uppercase tracking-wide" style="color: var(--onb-ink); opacity: 0.95;">Ejemplos Comunes</p>
                                <p class="text-xs font-medium mt-1.5 mb-4" style="color: var(--onb-ink); opacity: 0.6;">Servicios médicos frecuentes en clínicas y consultorios</p>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                    <span class="text-xs px-3 py-2 rounded-lg font-semibold" style="background: rgba(15,138,141,0.12); color: var(--onb-ink);">Consulta General</span>
                                    <span class="text-xs px-3 py-2 rounded-lg font-semibold" style="background: rgba(15,138,141,0.12); color: var(--onb-ink);">Especializada</span>
                                    <span class="text-xs px-3 py-2 rounded-lg font-semibold" style="background: rgba(15,138,141,0.12); color: var(--onb-ink);">Control Prenatal</span>
                                    <span class="text-xs px-3 py-2 rounded-lg font-semibold" style="background: rgba(15,138,141,0.12); color: var(--onb-ink);">Chequeo Médico</span>
                                    <span class="text-xs px-3 py-2 rounded-lg font-semibold" style="background: rgba(15,138,141,0.12); color: var(--onb-ink);">Pediátrica</span>
                                    <span class="text-xs px-3 py-2 rounded-lg font-semibold" style="background: rgba(15,138,141,0.12); color: var(--onb-ink);">Inyecciones</span>
                                    <span class="text-xs px-3 py-2 rounded-lg font-semibold" style="background: rgba(15,138,141,0.12); color: var(--onb-ink);">Curaciones</span>
                                    <span class="text-xs px-3 py-2 rounded-lg font-semibold" style="background: rgba(15,138,141,0.12); color: var(--onb-ink);">Presión Arterial</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de navegación -->
                    <div class="flex justify-between items-center pt-10 border-t" style="border-color: #e8e5df;">
                        <a href="{{ route('onboarding.step-2') }}" 
                           class="px-6 py-3 text-sm rounded-lg font-bold transition-all border-2" 
                           style="border-color: #d8d3c8; color: var(--onb-ink); background: transparent;"
                           onmouseover="this.style.background='#f5f3ee'"
                           onmouseout="this.style.background='transparent'">
                            ← Atrás
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center gap-2 px-8 py-3 text-sm rounded-lg font-bold text-white transition-all"
                                style="background: var(--onb-accent); box-shadow: 0 4px 12px rgba(15,138,141,0.25);"
                                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 24px rgba(15,138,141,0.35)'"
                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(15,138,141,0.25)'">
                            Finalizar
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let servicioCount = 1;

    // Función para mostrar indicador de guardado
    function showSaveIndicator() {
        const indicator = document.getElementById('save-indicator');
        if (indicator) {
            indicator.style.opacity = '1';
            setTimeout(() => {
                indicator.style.opacity = '0';
            }, 3000);
        }
    }

    document.getElementById('add-servicio').addEventListener('click', function() {
        const container = document.getElementById('servicios-container');
        const newServicio = `
            <div class="servicio-item p-6 rounded-xl" style="background: linear-gradient(135deg, rgba(15,138,141,0.03) 0%, rgba(248,243,234,0.5) 100%); border: 1px solid #d0cab5;">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: var(--onb-accent); box-shadow: 0 2px 8px rgba(15,138,141,0.25);">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-sm uppercase tracking-wide" style="color: var(--onb-ink); opacity: 0.95;">SERVICIO ${servicioCount + 1}</h3>
                            <p class="text-xs font-medium mt-0.5" style="color: var(--onb-ink); opacity: 0.55;">Información del servicio</p>
                        </div>
                    </div>
                    <button type="button" 
                            class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all" 
                            style="background: #ed6a5a; color: white;"
                            onmouseover="this.style.background='#d55a4a'"
                            onmouseout="this.style.background='#ed6a5a'"
                            onclick="this.closest('.servicio-item').remove()">
                        Eliminar
                    </button>
                </div>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="md:col-span-2 space-y-2">
                        <label class="font-extrabold text-sm block" style="color: var(--onb-ink);">Nombre del Servicio</label>
                        <input type="text" 
                               name="servicios[${servicioCount}][nombre]" 
                               required
                               onchange="showSaveIndicator()"
                               class="w-full px-4 py-4 border-2 rounded-lg transition-all text-base" 
                               style="border-color: #bfb8a5; background: #ffffff; color: var(--onb-ink); font-weight: 600;"
                               placeholder="Ej: Consulta Especializada"
                               onfocus="this.style.borderColor='var(--onb-accent)'; this.style.boxShadow='0 4px 16px rgba(15,138,141,0.2)'"
                               onblur="this.style.borderColor='#bfb8a5'; this.style.boxShadow='none'"
                               onmouseover="this.style.borderColor='#9a9280'"
                               onmouseout="if(this !== document.activeElement) this.style.borderColor='#bfb8a5'">
                        <p class="text-xs font-medium" style="color: var(--onb-ink); opacity: 0.6;">Nombre del servicio que verán tus pacientes</p>
                    </div>

                    <div class="space-y-2">
                        <label class="font-extrabold text-sm block" style="color: var(--onb-ink);">Precio en Lempiras</label>
                        <input type="number" 
                               name="servicios[${servicioCount}][precio]" 
                               required
                               min="0"
                               step="0.01"
                               onchange="showSaveIndicator()"
                               class="w-full px-4 py-4 border-2 rounded-lg transition-all text-base" 
                               style="border-color: #bfb8a5; background: #ffffff; color: var(--onb-ink); font-weight: 600;"
                               placeholder="800.00"
                               onfocus="this.style.borderColor='var(--onb-accent)'; this.style.boxShadow='0 4px 16px rgba(15,138,141,0.2)'"
                               onblur="this.style.borderColor='#bfb8a5'; this.style.boxShadow='none'"
                               onmouseover="this.style.borderColor='#9a9280'"
                               onmouseout="if(this !== document.activeElement) this.style.borderColor='#bfb8a5'">
                        <p class="text-xs font-medium" style="color: var(--onb-ink); opacity: 0.6;">Costo del servicio (L)</p>
                    </div>

                    <div class="space-y-2">
                        <label class="font-extrabold text-sm block" style="color: var(--onb-ink);">Descripción</label>
                        <input type="text" 
                               name="servicios[${servicioCount}][descripcion]" 
                               onchange="showSaveIndicator()"
                               class="w-full px-4 py-4 border-2 rounded-lg transition-all text-base" 
                               style="border-color: #bfb8a5; background: #ffffff; color: var(--onb-ink); font-weight: 600;"
                               placeholder="Descripción"
                               onfocus="this.style.borderColor='var(--onb-accent)'; this.style.boxShadow='0 4px 16px rgba(15,138,141,0.2)'"
                               onblur="this.style.borderColor='#bfb8a5'; this.style.boxShadow='none'"
                               onmouseover="this.style.borderColor='#9a9280'"
                               onmouseout="if(this !== document.activeElement) this.style.borderColor='#bfb8a5'">
                        <p class="text-xs font-medium" style="color: var(--onb-ink); opacity: 0.6;">Opcional - Detalles adicionales</p>
                    </div>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', newServicio);
        servicioCount++;
    });
</script>
@endpush
@endsection
