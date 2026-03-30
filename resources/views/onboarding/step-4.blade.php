@extends('onboarding.layout')

@php
    $currentStep = 4;
@endphp

@section('content')
<div class="card-premium overflow-hidden min-h-[88vh]">
    <div class="px-8 pt-4 pb-5 md:px-12 md:pt-5 md:pb-6 border-b" style="border-color: #e8e5df; background: #ffffff;">
        <div class="flex items-start justify-between gap-6 mb-4">
            <div>
                <h1 class="display-title text-3xl md:text-4xl font-bold" style="color: var(--onb-ink);">Agregar Medico</h1>
                <p class="mt-1 text-sm leading-relaxed" style="color: var(--onb-ink); opacity: 0.7;">Este paso es opcional. Puedes crear tu primer medico ahora para iniciar mas rapido.</p>
            </div>
            <div class="text-right min-w-[80px]">
                <p class="text-2xl font-bold" style="color: var(--onb-accent);">80%</p>
                <p class="text-xs mt-1" style="color: var(--onb-ink); opacity: 0.6;">Avance</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <div class="w-16 h-1.5 rounded-full" style="background: var(--onb-accent);"></div>
            <span class="text-xs font-bold" style="color: #b0a99a;">1. DATOS BASICOS</span>
            <div class="flex-1 h-1 rounded-full" style="background: var(--onb-accent);"></div>
            <span class="text-xs font-bold" style="color: #b0a99a;">2. FACTURACION</span>
            <div class="flex-1 h-1 rounded-full" style="background: var(--onb-accent);"></div>
            <span class="text-xs font-bold" style="color: #b0a99a;">3. SERVICIOS</span>
            <div class="flex-1 h-1 rounded-full" style="background: var(--onb-accent);"></div>
            <span class="text-xs font-bold" style="color: var(--onb-accent);">4. MEDICO</span>
            <div class="flex-1 h-1 rounded-full" style="background: #e8e5df;"></div>
            <span class="text-xs font-bold" style="color: #b0a99a;">5. COMPLETO</span>
        </div>
    </div>

    <div class="px-8 py-6 md:px-12 md:py-8" style="background: #fafaf8;">
        <div class="p-5 rounded-xl mb-6" style="background: linear-gradient(135deg, rgba(15,138,141,0.06) 0%, rgba(248,243,234,0.3) 100%); border: 1.5px dashed #bfb8a5;">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background: rgba(15,138,141,0.15);">
                    <svg class="w-5 h-5" style="color: var(--onb-accent);" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-extrabold" style="color: var(--onb-ink);">Paso opcional</p>
                    <p class="text-xs mt-1.5 leading-relaxed font-medium" style="color: var(--onb-ink); opacity: 0.7;">Si todavia no deseas registrar medicos, puedes omitir este paso y finalizar el onboarding.</p>
                </div>
            </div>
        </div>

        <form action="{{ route('onboarding.save-step-4') }}" method="POST">
            @csrf

            <div style="background: #ffffff; border: 1px solid #e8e5df; border-radius: 0.75rem;">
                <div class="px-6 md:px-8 py-6 md:py-8 border-b" style="border-color: #e8e5df;">
                    <h2 class="text-lg font-bold" style="color: var(--onb-ink);">Datos del Medico</h2>
                    <p class="text-sm mt-1" style="color: var(--onb-ink); opacity: 0.6;">Completa los datos para registrar al primer medico de la clinica.</p>
                </div>

                <div class="px-8 md:px-12 py-8 md:py-10 space-y-10">
                    <div class="p-6 rounded-xl" style="background: linear-gradient(135deg, rgba(15,138,141,0.03) 0%, rgba(248,243,234,0.5) 100%); border: 1px solid #d0cab5;">
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="text-sm font-extrabold" style="color: var(--onb-ink);">Primer nombre</label>
                                <input type="text" name="primer_nombre" value="{{ old('primer_nombre') }}" required class="w-full mt-2 px-4 py-4 border-2 rounded-lg" style="border-color: #bfb8a5; background: #ffffff; color: var(--onb-ink);" />
                            </div>
                            <div>
                                <label class="text-sm font-extrabold" style="color: var(--onb-ink);">Primer apellido</label>
                                <input type="text" name="primer_apellido" value="{{ old('primer_apellido') }}" required class="w-full mt-2 px-4 py-4 border-2 rounded-lg" style="border-color: #bfb8a5; background: #ffffff; color: var(--onb-ink);" />
                            </div>
                            <div>
                                <label class="text-sm font-extrabold" style="color: var(--onb-ink);">DNI</label>
                                <input type="text" name="dni" value="{{ old('dni') }}" required class="w-full mt-2 px-4 py-4 border-2 rounded-lg" style="border-color: #bfb8a5; background: #ffffff; color: var(--onb-ink);" />
                            </div>
                            <div>
                                <label class="text-sm font-extrabold" style="color: var(--onb-ink);">Telefono</label>
                                <input type="text" name="telefono" value="{{ old('telefono') }}" required class="w-full mt-2 px-4 py-4 border-2 rounded-lg" style="border-color: #bfb8a5; background: #ffffff; color: var(--onb-ink);" />
                            </div>
                            <div>
                                <label class="text-sm font-extrabold" style="color: var(--onb-ink);">Sexo</label>
                                <select name="sexo" required class="w-full mt-2 px-4 py-4 border-2 rounded-lg" style="border-color: #bfb8a5; background: #ffffff; color: var(--onb-ink);">
                                    <option value="">Selecciona</option>
                                    <option value="M" {{ old('sexo') === 'M' ? 'selected' : '' }}>Masculino</option>
                                    <option value="F" {{ old('sexo') === 'F' ? 'selected' : '' }}>Femenino</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-sm font-extrabold" style="color: var(--onb-ink);">Fecha de nacimiento</label>
                                <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" required class="w-full mt-2 px-4 py-4 border-2 rounded-lg" style="border-color: #bfb8a5; background: #ffffff; color: var(--onb-ink);" />
                            </div>
                            <div>
                                <label class="text-sm font-extrabold" style="color: var(--onb-ink);">Nacionalidad</label>
                                <select name="nacionalidad_id" required class="w-full mt-2 px-4 py-4 border-2 rounded-lg" style="border-color: #bfb8a5; background: #ffffff; color: var(--onb-ink);">
                                    <option value="">Selecciona</option>
                                    @foreach($nacionalidades as $nacionalidad)
                                        <option value="{{ $nacionalidad->id }}" {{ (string) old('nacionalidad_id') === (string) $nacionalidad->id ? 'selected' : '' }}>
                                            {{ $nacionalidad->nacionalidad }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-sm font-extrabold" style="color: var(--onb-ink);">Numero de colegiacion</label>
                                <input type="text" name="numero_colegiacion" value="{{ old('numero_colegiacion') }}" required class="w-full mt-2 px-4 py-4 border-2 rounded-lg" style="border-color: #bfb8a5; background: #ffffff; color: var(--onb-ink);" />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-8 border-t" style="border-color: #e8e5df;">
                        <a href="{{ route('onboarding.step-3') }}" class="px-6 py-3 text-sm font-semibold rounded-lg transition-all inline-flex items-center gap-2" style="color: var(--onb-ink); background: transparent; border: 1.5px solid #d8d3c8;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Atras
                        </a>
                        <div class="flex items-center gap-3">
                            <button type="submit" formaction="{{ route('onboarding.skip-medico') }}" formmethod="POST" class="px-6 py-3 text-sm font-bold rounded-lg transition-all" style="color: var(--onb-ink); background: #f3efe6; border: 1px solid #d8d3c8;">
                                Omitir por ahora
                            </button>
                            <button type="submit" class="px-8 py-3 text-sm font-bold rounded-lg text-white transition-all inline-flex items-center gap-2" style="background: var(--onb-accent);">
                                Guardar y continuar
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
