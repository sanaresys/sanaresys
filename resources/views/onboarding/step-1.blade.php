@extends('onboarding.layout')

@php
    $currentStep = 1;
@endphp

@section('content')
<div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-6 text-white">
        <div class="flex items-center">
            <div class="text-4xl mr-4">🏥</div>
            <div>
                <h1 class="text-3xl font-bold">Datos del Centro Médico</h1>
                <p class="text-white/90 mt-1">Información básica de tu clínica</p>
            </div>
        </div>
    </div>

    <div class="p-8">
        <form action="{{ route('onboarding.save-step-1') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Nombre del Centro -->
            <div>
                <label for="nombre_centro" class="block text-sm font-bold text-gray-700 mb-2">
                    <span class="text-red-500">*</span> Nombre del Centro Médico
                </label>
                <input type="text" 
                       id="nombre_centro" 
                       name="nombre_centro" 
                       value="{{ old('nombre_centro', $centro->nombre_centro) }}"
                       required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                       placeholder="Ej: Clínica San Lucas">
                @error('nombre_centro')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Este será el nombre oficial que aparecerá en tus facturas y documentos</p>
            </div>

            <!-- RTN -->
            <div>
                <label for="rtn" class="block text-sm font-bold text-gray-700 mb-2">
                    <span class="text-red-500">*</span> RTN (Registro Tributario Nacional)
                </label>
                <input type="text" 
                       id="rtn" 
                       name="rtn" 
                       value="{{ old('rtn', $centro->rtn) }}"
                       required
                       maxlength="20"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                       placeholder="Ej: 08019876543219">
                @error('rtn')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">14 dígitos sin guiones ni espacios (requerido para facturación fiscal)</p>
            </div>

            <!-- Dirección -->
            <div>
                <label for="direccion" class="block text-sm font-bold text-gray-700 mb-2">
                    <span class="text-red-500">*</span> Dirección
                </label>
                <textarea id="direccion" 
                          name="direccion" 
                          rows="3"
                          required
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                          placeholder="Ej: Col. Loma Linda, Blvd. Morazán, Casa 123, Tegucigalpa">{{ old('direccion', $centro->direccion) }}</textarea>
                @error('direccion')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Teléfono -->
            <div>
                <label for="telefono" class="block text-sm font-bold text-gray-700 mb-2">
                    <span class="text-red-500">*</span> Teléfono
                </label>
                <input type="text" 
                       id="telefono" 
                       name="telefono" 
                       value="{{ old('telefono', $centro->telefono) }}"
                       required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                       placeholder="Ej: 2222-3333 o 9999-8888">
                @error('telefono')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email (opcional) -->
            <div>
                <label for="email" class="block text-sm font-bold text-gray-700 mb-2">
                    Email de Contacto (opcional)
                </label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="{{ old('email') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                       placeholder="Ej: info@clinicasanlucas.hn">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Podrás agregar el logo y más información después desde el panel</p>
            </div>

            <!-- Botones -->
            <div class="flex justify-between items-center pt-6 border-t">
                <a href="{{ route('onboarding.welcome') }}" 
                   class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Atrás
                </a>

                <button type="submit" 
                        class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
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
