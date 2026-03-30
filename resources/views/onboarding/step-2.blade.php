@extends('onboarding.layout')

@php
    $currentStep = 2;
@endphp

@section('content')
<div class="card-premium overflow-hidden min-h-[88vh]">
    <!-- Header limpio -->
    <div class="px-8 pt-4 pb-5 md:px-12 md:pt-5 md:pb-6 border-b" style="border-color: #e8e5df; background: #ffffff;">
        <div class="flex items-start justify-between gap-6 mb-4">
            <div>
                <h1 class="display-title text-3xl md:text-4xl font-bold" style="color: var(--onb-ink);">Configuración Fiscal</h1>
                <p class="mt-1 text-sm leading-relaxed" style="color: var(--onb-ink); opacity: 0.7;">Configura tu CAI para emitir facturas fiscales válidas aprobadas por la SAR.</p>
            </div>
            <div class="text-right min-w-[80px]">
                <p class="text-2xl font-bold" style="color: var(--onb-accent);">40%</p>
                <p class="text-xs mt-1" style="color: var(--onb-ink); opacity: 0.6;">Avance</p>
            </div>
        </div>

        <!-- Barra de progreso -->
        <div class="flex items-center gap-2">
            <div class="w-16 h-1.5 rounded-full" style="background: var(--onb-accent);"></div>
            <span class="text-xs font-bold" style="color: #b0a99a;">1. DATOS BÁSICOS</span>
            <div class="flex-1 h-1 rounded-full" style="background: var(--onb-accent);"></div>
            <span class="text-xs font-bold" style="color: var(--onb-accent);">2. FACTURACIÓN</span>
            <div class="flex-1 h-1 rounded-full" style="background: #e8e5df;"></div>
            <span class="text-xs font-bold" style="color: #b0a99a;">3. SERVICIOS</span>
            <div class="flex-1 h-1 rounded-full" style="background: #e8e5df;"></div>
            <span class="text-xs font-bold" style="color: #b0a99a;">4. MEDICO</span>
            <div class="flex-1 h-1 rounded-full" style="background: #e8e5df;"></div>
            <span class="text-xs font-bold" style="color: #b0a99a;">5. COMPLETO</span>
        </div>
    </div>

    <div class="px-8 py-6 md:px-12 md:py-8" style="background: #fafaf8;">
        <form action="{{ route('onboarding.save-step-2') }}" method="POST">
            @csrf

            <!-- Sección única con todo -->
            <div style="background: #ffffff; border: 1px solid #e8e5df; border-radius: 0.75rem;">
                <!-- Header -->
                <div class="px-6 md:px-8 py-6 md:py-8 border-b" style="border-color: #e8e5df;">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background: rgba(15, 138, 141, 0.1);">
                            <svg class="w-6 h-6" style="color: var(--onb-accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-lg font-bold" style="color: var(--onb-ink);">Información Fiscal</h2>
                            <p class="text-sm mt-1" style="color: var(--onb-ink); opacity: 0.6;">El CAI autoriza a tu clínica a emitir facturas fiscales válidas aprobadas por la SAR.</p>
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
                    <!-- Banner informativo: Paso opcional -->
                    <div class="p-5 rounded-xl" style="background: linear-gradient(135deg, rgba(15,138,141,0.06) 0%, rgba(248,243,234,0.3) 100%); border: 1.5px dashed #bfb8a5;">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background: rgba(15,138,141,0.15);">
                                <svg class="w-5 h-5" style="color: var(--onb-accent);" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-extrabold" style="color: var(--onb-ink);">Este paso es opcional</p>
                                <p class="text-xs mt-1.5 leading-relaxed font-medium" style="color: var(--onb-ink); opacity: 0.7;">Si aún no tienes tu CAI, no te preocupes. Puedes omitir este paso y configurarlo más adelante desde el panel de administración cuando lo tengas disponible.</p>
                            </div>
                        </div>
                    </div>
                    <!-- SECCIÓN 1: Código CAI -->
                    <div class="p-6 rounded-xl" style="background: linear-gradient(135deg, rgba(15,138,141,0.03) 0%, rgba(248,243,234,0.5) 100%); border: 1px solid #d0cab5;">
                        <div class="flex items-center gap-3 mb-7">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: var(--onb-accent); box-shadow: 0 2px 8px rgba(15,138,141,0.25);">
                                <svg class="w-5 h-5" style="color: #ffffff;" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold uppercase tracking-wide" style="color: var(--onb-ink); opacity: 0.95;">Código CAI</h3>
                                <p class="text-xs mt-0.5 font-medium" style="color: var(--onb-ink); opacity: 0.55;">Autorización de impresión fiscal</p>
                            </div>
                        </div>
                        <div class="space-y-2.5">
                            <label for="cai_codigo" class="text-sm font-extrabold" style="color: var(--onb-ink); display: block;">
                                Código de Autorización (CAI)
                            </label>
                            <input type="text" 
                                   id="cai_codigo" 
                                   name="cai_codigo" 
                                   value="{{ old('cai_codigo') }}"
                                   maxlength="50"
                                   class="w-full px-4 py-4 border-2 rounded-lg transition-all font-mono text-base" 
                                   style="border-color: #bfb8a5; background: #ffffff; color: var(--onb-ink); font-weight: 600;"
                                   placeholder="A1B2C3-D4E5F6-G7H8I9"
                                   onfocus="this.style.borderColor='var(--onb-accent)'; this.style.background='#fefefe'; this.style.boxShadow='0 4px 16px rgba(15,138,141,0.2)'"
                                   onblur="this.style.borderColor='#bfb8a5'; this.style.background='#ffffff'; this.style.boxShadow='none'"
                                   onchange="showSaveIndicator()"
                                   onmouseover="if(this!==document.activeElement) this.style.borderColor='#9a9280'"
                                   onmouseout="if(this!==document.activeElement) this.style.borderColor='#bfb8a5'">
                            @error('cai_codigo')
                                <p class="mt-1 text-xs font-semibold" style="color: #ed6a5a;">{{ $message }}</p>
                            @enderror
                            <p class="text-xs leading-relaxed font-medium" style="color: var(--onb-ink); opacity: 0.6;">Este código lo proporciona la SAR para autorizar la emisión de facturas</p>
                        </div>
                    </div>

                    <!-- SECCIÓN 2: Rango de Facturación -->
                    <div class="p-6 rounded-xl" style="background: linear-gradient(135deg, rgba(15,138,141,0.03) 0%, rgba(248,243,234,0.5) 100%); border: 1px solid #d0cab5;">
                        <div class="flex items-center gap-3 mb-7">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: var(--onb-accent); box-shadow: 0 2px 8px rgba(15,138,141,0.25);">
                                <svg class="w-5 h-5" style="color: #ffffff;" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold uppercase tracking-wide" style="color: var(--onb-ink); opacity: 0.95;">Rango de Facturación</h3>
                                <p class="text-xs mt-0.5 font-medium" style="color: var(--onb-ink); opacity: 0.55;">Números de factura autorizados</p>
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="space-y-2.5">
                                <label for="rango_inicial" class="text-sm font-extrabold" style="color: var(--onb-ink); display: block;">
                                    Número Inicial
                                </label>
                                <input type="number" 
                                       id="rango_inicial" 
                                       name="rango_inicial" 
                                       value="{{ old('rango_inicial') }}"
                                       min="1"
                                       class="w-full px-4 py-4 border-2 rounded-lg transition-all text-base" 
                                       style="border-color: #bfb8a5; background: #ffffff; color: var(--onb-ink); font-weight: 600;"
                                       placeholder="1"
                                       onfocus="this.style.borderColor='var(--onb-accent)'; this.style.background='#fefefe'; this.style.boxShadow='0 4px 16px rgba(15,138,141,0.2)'"
                                       onblur="this.style.borderColor='#bfb8a5'; this.style.background='#ffffff'; this.style.boxShadow='none'"
                                       onchange="showSaveIndicator()"
                                       onmouseover="if(this!==document.activeElement) this.style.borderColor='#9a9280'"
                                       onmouseout="if(this!==document.activeElement) this.style.borderColor='#bfb8a5'">
                                @error('rango_inicial')
                                    <p class="mt-1 text-xs font-semibold" style="color: #ed6a5a;">{{ $message }}</p>
                                @enderror
                                <p class="text-xs leading-relaxed font-medium" style="color: var(--onb-ink); opacity: 0.6;">Primera factura del rango</p>
                            </div>

                            <div class="space-y-2.5">
                                <label for="rango_final" class="text-sm font-extrabold" style="color: var(--onb-ink); display: block;">
                                    Número Final
                                </label>
                                <input type="number" 
                                       id="rango_final" 
                                       name="rango_final" 
                                       value="{{ old('rango_final') }}"
                                       min="1"
                                       class="w-full px-4 py-4 border-2 rounded-lg transition-all text-base" 
                                       style="border-color: #bfb8a5; background: #ffffff; color: var(--onb-ink); font-weight: 600;"
                                       placeholder="10000"
                                       onfocus="this.style.borderColor='var(--onb-accent)'; this.style.background='#fefefe'; this.style.boxShadow='0 4px 16px rgba(15,138,141,0.2)'"
                                       onblur="this.style.borderColor='#bfb8a5'; this.style.background='#ffffff'; this.style.boxShadow='none'"
                                       onchange="showSaveIndicator()"
                                       onmouseover="if(this!==document.activeElement) this.style.borderColor='#9a9280'"
                                       onmouseout="if(this!==document.activeElement) this.style.borderColor='#bfb8a5'">
                                @error('rango_final')
                                    <p class="mt-1 text-xs font-semibold" style="color: #ed6a5a;">{{ $message }}</p>
                                @enderror
                                <p class="text-xs leading-relaxed font-medium" style="color: var(--onb-ink); opacity: 0.6;">Última factura del rango</p>
                            </div>
                        </div>
                        <div class="mt-4 p-3 rounded-lg" style="background: rgba(15,138,141,0.06);">
                            <p class="text-xs font-medium" style="color: var(--onb-ink); opacity: 0.7;">🔢 El sistema numerará tus facturas automáticamente dentro de este rango</p>
                        </div>
                    </div>

                    <!-- SECCIÓN 3: Vigencia -->
                    <div class="p-6 rounded-xl" style="background: linear-gradient(135deg, rgba(15,138,141,0.03) 0%, rgba(248,243,234,0.5) 100%); border: 1px solid #d0cab5;">
                        <div class="flex items-center gap-3 mb-7">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: var(--onb-accent); box-shadow: 0 2px 8px rgba(15,138,141,0.25);">
                                <svg class="w-5 h-5" style="color: #ffffff;" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold uppercase tracking-wide" style="color: var(--onb-ink); opacity: 0.95;">Vigencia</h3>
                                <p class="text-xs mt-0.5 font-medium" style="color: var(--onb-ink); opacity: 0.55;">Período de validez del CAI</p>
                            </div>
                        </div>
                        <div class="space-y-2.5">
                            <label for="fecha_limite" class="text-sm font-extrabold" style="color: var(--onb-ink); display: block;">
                                Fecha Límite de Emisión
                            </label>
                            <input type="date" 
                                   id="fecha_limite" 
                                   name="fecha_limite" 
                                   value="{{ old('fecha_limite') }}"
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                   class="w-full px-4 py-4 border-2 rounded-lg transition-all text-base" 
                                   style="border-color: #bfb8a5; background: #ffffff; color: var(--onb-ink); font-weight: 600;"
                                   onfocus="this.style.borderColor='var(--onb-accent)'; this.style.background='#fefefe'; this.style.boxShadow='0 4px 16px rgba(15,138,141,0.2)'"
                                   onblur="this.style.borderColor='#bfb8a5'; this.style.background='#ffffff'; this.style.boxShadow='none'"
                                   onchange="showSaveIndicator()"
                                   onmouseover="if(this!==document.activeElement) this.style.borderColor='#9a9280'"
                                   onmouseout="if(this!==document.activeElement) this.style.borderColor='#bfb8a5'">
                            @error('fecha_limite')
                                <p class="mt-1 text-xs font-semibold" style="color: #ed6a5a;">{{ $message }}</p>
                            @enderror
                            <p class="text-xs leading-relaxed font-medium" style="color: var(--onb-ink); opacity: 0.6;">Después de esta fecha no podrás emitir facturas con este CAI</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de navegación -->
            <div class="flex justify-between items-center pt-8 mt-8 border-t" style="border-color: #e8e5df;">
                <a href="{{ route('onboarding.step-1') }}" 
                   class="px-6 py-3 text-sm font-semibold rounded-lg transition-all inline-flex items-center gap-2" 
                   style="color: var(--onb-ink); background: transparent; border: 1.5px solid #d8d3c8;"
                   onmouseover="this.style.background='#f5f3ee'"
                   onmouseout="this.style.background='transparent'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Atrás
                </a>
                <button type="submit" 
                        class="px-8 py-3 text-sm font-bold rounded-lg text-white transition-all inline-flex items-center gap-2"
                        style="background: var(--onb-accent);"
                        onmouseover="this.style.background='#0d7578'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(15,138,141,0.3)'"
                        onmouseout="this.style.background='var(--onb-accent)'; this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    Continuar
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showSaveIndicator() {
    const indicator = document.getElementById('save-indicator');
    indicator.style.opacity = '1';
    
    // Ocultar el indicador después de 3 segundos
    setTimeout(() => {
        indicator.style.opacity = '0';
    }, 3000);
}
</script>
@endsection
