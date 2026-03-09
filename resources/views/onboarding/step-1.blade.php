@extends('onboarding.layout')

@php
    $currentStep = 1;
@endphp

@section('content')
<div class="card-premium overflow-hidden min-h-[88vh]">
    <!-- Header limpio -->
    <div class="px-8 pt-4 pb-5 md:px-12 md:pt-5 md:pb-6 border-b" style="border-color: #e8e5df; background: #ffffff;">
        <div class="flex items-start justify-between gap-6 mb-4">
            <div>
                <h1 class="display-title text-3xl md:text-4xl font-bold" style="color: var(--onb-ink);">Configura tu clínica</h1>
                <p class="mt-1 text-sm leading-relaxed" style="color: var(--onb-ink); opacity: 0.7;">Bienvenido a Sanaresys. Vamos a preparar tu espacio de trabajo para que puedas empezar a atender pacientes hoy mismo.</p>
            </div>
            <div class="text-right min-w-[80px]">
                <p class="text-2xl font-bold" style="color: var(--onb-accent);">25%</p>
                <p class="text-xs mt-1" style="color: var(--onb-ink); opacity: 0.6;">Avance</p>
            </div>
        </div>

        <!-- Barra de progreso -->
        <div class="flex items-center gap-2">
            <div class="w-16 h-1.5 rounded-full" style="background: var(--onb-accent);"></div>
            <span class="text-xs font-bold" style="color: var(--onb-accent);">1. DATOS BÁSICOS</span>
            <div class="flex-1 h-1 rounded-full" style="background: #e8e5df;"></div>
            <span class="text-xs font-bold" style="color: #b0a99a;">2. FACTURACIÓN</span>
            <div class="flex-1 h-1 rounded-full" style="background: #e8e5df;"></div>
            <span class="text-xs font-bold" style="color: #b0a99a;">3. SERVICIOS</span>
            <div class="flex-1 h-1 rounded-full" style="background: #e8e5df;"></div>
            <span class="text-xs font-bold" style="color: #b0a99a;">4. COMPLETO</span>
        </div>
    </div>

    <div class="px-8 py-6 md:px-12 md:py-8" style="background: #fafaf8;">
        
        <form action="{{ route('onboarding.save-step-1') }}" method="POST">
            @csrf

            <!-- Información General Section -->
            <div style="background: #ffffff; border: 1px solid #e8e5df; border-radius: 0.75rem;">
                <!-- Header -->
                <div class="px-6 md:px-8 py-6 md:py-8 border-b" style="border-color: #e8e5df;">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background: rgba(15, 138, 141, 0.1);">
                            <svg class="w-6 h-6" style="color: var(--onb-accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-lg font-bold" style="color: var(--onb-ink);">Información General</h2>
                            <p class="text-sm mt-1" style="color: var(--onb-ink); opacity: 0.6;">Define la información básica de tu clínica que aparecerá en documentos y facturación.</p>
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
                    
                    <!-- SECCIÓN 1: Información Legal -->
                    <div class="p-6 rounded-xl" style="background: linear-gradient(135deg, rgba(15,138,141,0.03) 0%, rgba(248,243,234,0.5) 100%); border: 1px solid #d0cab5;">
                        <div class="flex items-center gap-3 mb-7">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: var(--onb-accent); box-shadow: 0 2px 8px rgba(15,138,141,0.25);">
                                <svg class="w-5 h-5" style="color: #ffffff;" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold uppercase tracking-wide" style="color: var(--onb-ink); opacity: 0.95;">Información Legal</h3>
                                <p class="text-xs mt-0.5 font-medium" style="color: var(--onb-ink); opacity: 0.55;">Datos oficiales de tu clínica</p>
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="space-y-2.5">
                                <label for="nombre_centro" class="text-sm font-extrabold" style="color: var(--onb-ink); display: block;">
                                    Nombre de la Clínica
                                </label>
                                <input type="text" 
                                       id="nombre_centro" 
                                       name="nombre_centro" 
                                       value="{{ old('nombre_centro', $centro->nombre_centro ?? '') }}"
                                       required
                                       class="w-full px-4 py-4 border-2 rounded-lg transition-all text-base" 
                                       style="border-color: #bfb8a5; background: #ffffff; color: var(--onb-ink); font-weight: 600;"
                                       placeholder="Ej. Clínica San José"
                                       onfocus="this.style.borderColor='var(--onb-accent)'; this.style.background='#fefefe'; this.style.boxShadow='0 4px 16px rgba(15,138,141,0.2)'"
                                       onblur="this.style.borderColor='#bfb8a5'; this.style.background='#ffffff'; this.style.boxShadow='none'"
                                       onchange="showSaveIndicator()"
                                       onmouseover="if(this!==document.activeElement) this.style.borderColor='#9a9280'"
                                       onmouseout="if(this!==document.activeElement) this.style.borderColor='#bfb8a5'">
                                @error('nombre_centro')
                                    <p class="mt-1 text-xs font-semibold" style="color: #ed6a5a;">{{ $message }}</p>
                                @enderror
                                <p class="text-xs leading-relaxed font-medium" style="color: var(--onb-ink); opacity: 0.6;">Este es el nombre que tus pacientes verán en facturas y recibos</p>
                            </div>

                            <div class="space-y-2.5">
                                <label for="rtn" class="text-sm font-extrabold" style="color: var(--onb-ink); display: block;">
                                    RTN / Identificación Fiscal
                                </label>
                                <input type="text" 
                                       id="rtn" 
                                       name="rtn" 
                                       value="{{ old('rtn', $centro->rtn ?? '') }}"
                                       required
                                       maxlength="20"
                                       class="w-full px-4 py-4 border-2 rounded-lg transition-all font-mono text-base" 
                                       style="border-color: #bfb8a5; background: #ffffff; color: var(--onb-ink); font-weight: 600;"
                                       placeholder="Ej. 08011990123456"
                                       onfocus="this.style.borderColor='var(--onb-accent)'; this.style.background='#fefefe'; this.style.boxShadow='0 4px 16px rgba(15,138,141,0.2)'"
                                       onblur="this.style.borderColor='#bfb8a5'; this.style.background='#ffffff'; this.style.boxShadow='none'"
                                       onchange="showSaveIndicator()"
                                       onmouseover="if(this!==document.activeElement) this.style.borderColor='#9a9280'"
                                       onmouseout="if(this!==document.activeElement) this.style.borderColor='#bfb8a5'">
                                @error('rtn')
                                    <p class="mt-1 text-xs font-semibold" style="color: #ed6a5a;">{{ $message }}</p>
                                @enderror
                                <p class="text-xs leading-relaxed font-medium" style="color: var(--onb-ink); opacity: 0.6;">SAR requiere este número para validar tus facturas</p>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN 2: Contacto -->
                    <div class="p-6 rounded-xl" style="background: linear-gradient(135deg, rgba(15,138,141,0.03) 0%, rgba(248,243,234,0.5) 100%); border: 1px solid #d0cab5;">
                        <div class="flex items-center gap-3 mb-7">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: var(--onb-accent); box-shadow: 0 2px 8px rgba(15,138,141,0.25);">
                                <svg class="w-5 h-5" style="color: #ffffff;" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold uppercase tracking-wide" style="color: var(--onb-ink); opacity: 0.95;">Contacto</h3>
                                <p class="text-xs mt-0.5 font-medium" style="color: var(--onb-ink); opacity: 0.55;">Canales de comunicación con pacientes</p>
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="space-y-2.5">
                                <label for="telefono" class="text-sm font-extrabold" style="color: var(--onb-ink); display: block;">
                                    Teléfono de Contacto
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="w-5 h-5" style="color: var(--onb-ink); opacity: 0.3;" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                                        </svg>
                                    </div>
                                    <input type="text" 
                                           id="telefono" 
                                           name="telefono" 
                                           value="{{ old('telefono', $centro->telefono ?? '') }}"
                                           required
                                           class="w-full pl-12 pr-4 py-4 border-2 rounded-lg transition-all text-base" 
                                           style="border-color: #bfb8a5; background: #ffffff; color: var(--onb-ink); font-weight: 600;"
                                           placeholder="Ej. +504 2222-3434"
                                           onfocus="this.style.borderColor='var(--onb-accent)'; this.style.background='#fefefe'; this.style.boxShadow='0 4px 16px rgba(15,138,141,0.2)'"
                                           onblur="this.style.borderColor='#bfb8a5'; this.style.background='#ffffff'; this.style.boxShadow='none'"
                                           onchange="showSaveIndicator()"
                                           onmouseover="if(this!==document.activeElement) this.style.borderColor='#9a9280'"
                                           onmouseout="if(this!==document.activeElement) this.style.borderColor='#bfb8a5'">
                                </div>
                                @error('telefono')
                                    <p class="mt-1 text-xs font-semibold" style="color: #ed6a5a;">{{ $message }}</p>
                                @enderror
                                <p class="text-xs leading-relaxed font-medium" style="color: var(--onb-ink); opacity: 0.6;">Los pacientes llamarán aquí para citas y consultas</p>
                            </div>

                            <div class="space-y-2.5">
                                <label for="email" class="text-sm font-extrabold" style="color: var(--onb-ink); display: block;">
                                    Correo Electrónico
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="w-5 h-5" style="color: var(--onb-ink); opacity: 0.3;" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                                        </svg>
                                    </div>
                                    <input type="email" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email', $centro->email ?? '') }}"
                                           class="w-full pl-12 pr-4 py-4 border-2 rounded-lg transition-all text-base" 
                                           style="border-color: #bfb8a5; background: #ffffff; color: var(--onb-ink); font-weight: 600;"
                                           placeholder="Ej. contacto@clinica.com"
                                           onfocus="this.style.borderColor='var(--onb-accent)'; this.style.background='#fefefe'; this.style.boxShadow='0 4px 16px rgba(15,138,141,0.2)'"
                                           onblur="this.style.borderColor='#bfb8a5'; this.style.background='#ffffff'; this.style.boxShadow='none'"
                                           onchange="showSaveIndicator()"
                                           onmouseover="if(this!==document.activeElement) this.style.borderColor='#9a9280'"
                                           onmouseout="if(this!==document.activeElement) this.style.borderColor='#bfb8a5'">
                                </div>
                                @error('email')
                                    <p class="mt-1 text-xs font-semibold" style="color: #ed6a5a;">{{ $message }}</p>
                                @enderror
                                <p class="text-xs leading-relaxed font-medium" style="color: var(--onb-ink); opacity: 0.6;">Para envío de notificaciones y comprobantes electrónicos</p>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN 3: Ubicación -->
                    <div class="p-6 rounded-xl" style="background: linear-gradient(135deg, rgba(15,138,141,0.03) 0%, rgba(248,243,234,0.5) 100%); border: 1px solid #d0cab5;">
                        <div class="flex items-center gap-3 mb-7">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: var(--onb-accent); box-shadow: 0 2px 8px rgba(15,138,141,0.25);">
                                <svg class="w-5 h-5" style="color: #ffffff;" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold uppercase tracking-wide" style="color: var(--onb-ink); opacity: 0.95;">Ubicación</h3>
                                <p class="text-xs mt-0.5 font-medium" style="color: var(--onb-ink); opacity: 0.55;">¿Dónde te encuentran tus pacientes?</p>
                            </div>
                        </div>
                        <div class="space-y-2.5">
                            <label for="direccion" class="text-sm font-extrabold" style="color: var(--onb-ink); display: block;">
                                Dirección Física
                            </label>
                        <textarea id="direccion" 
                                  name="direccion" 
                                  rows="5"
                                  required
                                  class="w-full px-4 py-4 border-2 rounded-lg transition-all resize-none text-base" 
                                  style="border-color: #bfb8a5; background: #ffffff; color: var(--onb-ink); line-height: 1.7; font-weight: 600;"
                                  placeholder="Ej. Colonia Palmira, Ave. República de Chile, frente a Plaza Medica, 3er piso, Tegucigalpa"
                                  onfocus="this.style.borderColor='var(--onb-accent)'; this.style.background='#fefefe'; this.style.boxShadow='0 4px 16px rgba(15,138,141,0.2)'"
                                  onblur="this.style.borderColor='#bfb8a5'; this.style.background='#ffffff'; this.style.boxShadow='none'"
                                  onchange="showSaveIndicator()"
                                  onmouseover="if(this!==document.activeElement) this.style.borderColor='#9a9280'"
                                  onmouseout="if(this!==document.activeElement) this.style.borderColor='#bfb8a5'">{{ old('direccion', $centro->direccion ?? '') }}</textarea>
                        @error('direccion')
                            <p class="mt-1 text-xs font-semibold" style="color: #ed6a5a;">{{ $message }}</p>
                        @enderror
                        <p class="text-xs leading-relaxed font-medium" style="color: var(--onb-ink); opacity: 0.6;">Sé específico: incluye colonia, referencias visuales y número de piso si aplica</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de navegación -->
            <div class="flex justify-between items-center pt-8 mt-8 border-t" style="border-color: #e8e5df;">
                <a href="{{ route('onboarding.welcome') }}" 
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
                    Continuar a Facturación
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
