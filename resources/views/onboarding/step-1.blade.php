@extends('onboarding.layout')

@php
    $currentStep = 1;
@endphp

@section('content')
<!-- Card principal blanco con sombra moderna -->
<div class="bg-white rounded-2xl shadow-lg overflow-hidden">
    
    <!-- Header limpio con color médico -->
    <div class="bg-medical-blue px-8 py-6">
        <div class="flex items-center text-white">
            <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center mr-4">
                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold">Datos del Centro Médico</h1>
                <p class="text-white/90 text-sm font-medium mt-0.5">Paso 1 de 3 · Información básica</p>
            </div>
        </div>
    </div>

    <div class="px-8 py-8">
        
        <!-- Info Banner mejorado -->
        <div class="mb-8 bg-blue-50 border-l-4 border-medical-blue rounded-r-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-medical-blue mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-medical-dark mb-1">Información del Registro</p>
                    <p class="text-sm text-gray-600">
                        Hemos pre-llenado los datos que capturaste al registrarte. Verifica que sean correctos y agrega el email de contacto.
                    </p>
                </div>
            </div>
        </div>

        <form action="{{ route('onboarding.save-step-1') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Nombre del Centro -->
            <div class="space-y-2">
                <label for="nombre_centro" class="block text-sm font-semibold text-gray-900">
                    <span class="text-red-500">*</span> Nombre del Centro Médico
                </label>
                <input type="text" 
                       id="nombre_centro" 
                       name="nombre_centro" 
                       value="{{ old('nombre_centro', $centro->nombre_centro ?? '') }}"
                       required
                       class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2563EB] focus:border-[#2563EB] transition-all text-gray-900"
                       placeholder="Ej: Clínica San Lucas">
                @error('nombre_centro')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500">Este será el nombre oficial que aparecerá en tus facturas y documentos</p>
            </div>

            <!-- RTN -->
            <div class="space-y-2">
                <label for="rtn" class="block text-sm font-semibold text-gray-900">
                    <span class="text-red-500">*</span> RTN (Registro Tributario Nacional)
                </label>
                <input type="text" 
                       id="rtn" 
                       name="rtn" 
                       value="{{ old('rtn', $centro->rtn ?? '') }}"
                       required
                       maxlength="20"
                       class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2563EB] focus:border-[#2563EB] transition-all text-gray-900"
                       placeholder="Ej: 08019876543219">
                @error('rtn')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500">14 dígitos sin guiones ni espacios (requerido para facturación fiscal)</p>
            </div>

            <!-- Dirección -->
            <div class="space-y-2">
                <label for="direccion" class="block text-sm font-semibold text-gray-900">
                    <span class="text-red-500">*</span> Dirección
                </label>
                <textarea id="direccion" 
                          name="direccion" 
                          rows="3"
                          required
                          class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2563EB] focus:border-[#2563EB] transition-all text-gray-900"
                          placeholder="Ej: Col. Loma Linda, Blvd. Morazán, Casa 123, Tegucigalpa">{{ old('direccion', $centro->direccion ?? '') }}</textarea>
                @error('direccion')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Teléfono -->
            <div class="space-y-2">
                <label for="telefono" class="block text-sm font-semibold text-gray-900">
                    <span class="text-red-500">*</span> Teléfono
                </label>
                <input type="text" 
                       id="telefono" 
                       name="telefono" 
                       value="{{ old('telefono', $centro->telefono ?? '') }}"
                       required
                       class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2563EB] focus:border-[#2563EB] transition-all text-gray-900"
                       placeholder="Ej: 2222-3333 o 9999-8888">
                @error('telefono')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email (nuevo campo destacado) -->
            <div class="p-6 bg-[#ECFDF5] border-l-4 border-[#22C55E] rounded-lg space-y-3">
                <label for="email" class="flex items-center text-sm font-semibold text-gray-900">
                    <svg class="w-5 h-5 mr-2 text-[#22C55E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Email de Contacto
                </label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="{{ old('email', $centro->email ?? '') }}"
                       class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#22C55E] focus:border-[#22C55E] transition-all text-gray-900"
                       placeholder="Ej: info@clinicasanlucas.hn">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <div class="flex items-start">
                    <svg class="w-5 h-5 mr-2 text-[#22C55E] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-xs text-gray-700">Este email aparecerá en tus facturas para que los pacientes puedan contactarte</p>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-between items-center pt-8 mt-8 border-t border-gray-200">
                <a href="{{ route('onboarding.welcome') }}" 
                   class="inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Atrás
                </a>

                <button type="submit" 
                        class="inline-flex items-center px-8 py-4 bg-[#2563EB] text-white font-semibold rounded-lg hover:bg-[#1D4ED8] transition-all shadow-lg hover:shadow-xl">
                    Continuar
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
