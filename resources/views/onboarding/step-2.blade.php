@extends('onboarding.layout')

@php
    $currentStep = 2;
@endphp

@section('content')
<div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-500 to-pink-600 p-6 text-white">
        <div class="flex items-center">
            <div class="text-4xl mr-4">📄</div>
            <div>
                <h1 class="text-3xl font-bold">Configuración Fiscal (CAI)</h1>
                <p class="text-white/90 mt-1">Autorización para facturar legalmente</p>
            </div>
        </div>
    </div>

    <div class="p-8">
        <!-- Info -->
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0 text-2xl">ℹ️</div>
                <div class="ml-3">
                    <h3 class="text-sm font-bold text-blue-900">¿Qué es el CAI?</h3>
                    <p class="text-sm text-blue-800 mt-1">
                        El <strong>CAI (Código deAutorización de Impresión)</strong> es un código único proporcionado por la SAR 
                        que autoriza a tu clínica a emitir facturas fiscales válidas en Honduras.
                    </p>
                </div>
            </div>
        </div>

        <form action="{{ route('onboarding.save-step-2') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Código CAI -->
            <div>
                <label for="cai_codigo" class="block text-sm font-bold text-gray-700 mb-2">
                    <span class="text-red-500">*</span> Código CAI
                </label>
                <input type="text" 
                       id="cai_codigo" 
                       name="cai_codigo" 
                       value="{{ old('cai_codigo') }}"
                       required
                       maxlength="50"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all font-mono"
                       placeholder="Ej: A1B2C3-D4E5F6-G7H8I9-J0K1L2-M3N4O5">
                @error('cai_codigo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Este código te lo proporciona la SAR al autorizar tus facturas</p>
            </div>

            <!-- Rango de Facturación -->
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label for="rango_inicial" class="block text-sm font-bold text-gray-700 mb-2">
                        <span class="text-red-500">*</span> Rango Inicial
                    </label>
                    <input type="number" 
                           id="rango_inicial" 
                           name="rango_inicial" 
                           value="{{ old('rango_inicial') }}"
                           required
                           min="1"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                           placeholder="Ej: 1">
                    @error('rango_inicial')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="rango_final" class="block text-sm font-bold text-gray-700 mb-2">
                        <span class="text-red-500">*</span> Rango Final
                    </label>
                    <input type="number" 
                           id="rango_final" 
                           name="rango_final" 
                           value="{{ old('rango_final') }}"
                           required
                           min="1"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                           placeholder="Ej: 10000">
                    @error('rango_final')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <p class="text-xs text-gray-500">El sistema numerará tus facturas automáticamente dentro de este rango</p>

            <!-- Fecha Límite -->
            <div>
                <label for="fecha_limite" class="block text-sm font-bold text-gray-700 mb-2">
                    <span class="text-red-500">*</span> Fecha Límite de Emisión
                </label>
                <input type="date" 
                       id="fecha_limite" 
                       name="fecha_limite" 
                       value="{{ old('fecha_limite') }}"
                       required
                       min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                       >
                @error('fecha_limite')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Fecha hasta la cual puedes emitir facturas con este CAI</p>
            </div>

            <!-- Warning sobre vencimiento -->
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4">
                <div class="flex">
                    <div class="text-2xl">⚠️</div>
                    <div class="ml-3">
                        <h3 class="text-sm font-bold text-yellow-900">Importante</h3>
                        <p class="text-sm text-yellow-800 mt-1">
                            El sistema te notificará cuando estés cerca de agotar el rango de facturas o cuando 
                            la fecha límite esté próxima a vencer.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-between items-center pt-6 border-t">
                <a href="{{ route('onboarding.step-1') }}" 
                   class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Atrás
                </a>

                <div class="flex gap-3">
                    <form action="{{ route('onboarding.skip-cai') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                onclick="return confirm('¿Estás seguro de que deseas omitir la configuración CAI? No podrás facturar hasta configurarlo.')"
                                class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-all">
                            Configurar después
                        </button>
                    </form>

                    <button type="submit" 
                            class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold rounded-lg hover:from-purple-700 hover:to-pink-700 transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                        Continuar
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Validar que rango final sea mayor que inicial
    document.getElementById('rango_final').addEventListener('input', function() {
        const inicial = parseInt(document.getElementById('rango_inicial').value) || 0;
        const final = parseInt(this.value) || 0;
        
        if (final <= inicial) {
            this.setCustomValidity('El rango final debe ser mayor que el rango inicial');
        } else {
            this.setCustomValidity('');
        }
    });
</script>
@endpush
@endsection
