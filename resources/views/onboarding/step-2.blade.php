@extends('onboarding.layout')

@php
    $currentStep = 2;
@endphp

@section('content')
<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <!-- Header -->
    <div class="bg-[#0EA5E9] p-8 text-white">
        <div class="flex items-center">
            <div class="w-16 h-16 bg-white/20 rounded-xl flex items-center justify-center mr-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold">Configuración Fiscal (CAI)</h1>
                <p class="text-white/90 mt-1 text-lg">Autorización para facturar legalmente</p>
            </div>
        </div>
    </div>

    <div class="p-8">
        <!-- Info -->
        <div class="bg-blue-50 border-l-4 border-[#0EA5E9] p-6 mb-8 rounded-r-lg">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-[#0EA5E9] flex-shrink-0 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">¿Qué es el CAI?</h3>
                    <p class="text-sm text-gray-700 mt-2">
                        El <strong>CAI (Código de Autorización de Impresión)</strong> es un código único proporcionado por la SAR 
                        que autoriza a tu clínica a emitir facturas fiscales válidas en Honduras.
                    </p>
                </div>
            </div>
        </div>

        <form action="{{ route('onboarding.save-step-2') }}" method="POST" class="space-y-8">
            @csrf

            <!-- Código CAI -->
            <div class="space-y-2">
                <label for="cai_codigo" class="block text-sm font-semibold text-gray-900">
                    <span class="text-red-500">*</span> Código CAI
                </label>
                <input type="text" 
                       id="cai_codigo" 
                       name="cai_codigo" 
                       value="{{ old('cai_codigo') }}"
                       required
                       maxlength="50"
                       class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0EA5E9] focus:border-[#0EA5E9] transition-all font-mono text-gray-900"
                       placeholder="Ej: A1B2C3-D4E5F6-G7H8I9-J0K1L2-M3N4O5">
                @error('cai_codigo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500">Este código te lo proporciona la SAR al autorizar tus facturas</p>
            </div>

            <!-- Rango de Facturación -->
            <div class="grid md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label for="rango_inicial" class="block text-sm font-semibold text-gray-900">
                        <span class="text-red-500">*</span> Rango Inicial
                    </label>
                    <input type="number" 
                           id="rango_inicial" 
                           name="rango_inicial" 
                           value="{{ old('rango_inicial') }}"
                           required
                           min="1"
                           class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0EA5E9] focus:border-[#0EA5E9] transition-all text-gray-900"
                           placeholder="Ej: 1">
                    @error('rango_inicial')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="rango_final" class="block text-sm font-semibold text-gray-900">
                        <span class="text-red-500">*</span> Rango Final
                    </label>
                    <input type="number" 
                           id="rango_final" 
                           name="rango_final" 
                           value="{{ old('rango_final') }}"
                           required
                           min="1"
                           class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0EA5E9] focus:border-[#0EA5E9] transition-all text-gray-900"
                           placeholder="Ej: 10000">
                    @error('rango_final')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <p class="text-xs text-gray-500 -mt-4">El sistema numerará tus facturas automáticamente dentro de este rango</p>

            <!-- Fecha Límite -->
            <div class="space-y-2">
                <label for="fecha_limite" class="block text-sm font-semibold text-gray-900">
                    <span class="text-red-500">*</span> Fecha Límite de Emisión
                </label>
                <input type="date" 
                       id="fecha_limite" 
                       name="fecha_limite" 
                       value="{{ old('fecha_limite') }}"
                       required
                       min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                       class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0EA5E9] focus:border-[#0EA5E9] transition-all text-gray-900"
                       >
                @error('fecha_limite')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500">Fecha hasta la cual puedes emitir facturas con este CAI</p>
            </div>

            <!-- Warning sobre vencimiento -->
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-6 rounded-r-lg">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-yellow-600 flex-shrink-0 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Importante</h3>
                        <p class="text-sm text-gray-700 mt-2">
                            El sistema te notificará cuando estés cerca de agotar el rango de facturas o cuando 
                            la fecha límite esté próxima a vencer.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-between items-center pt-8 mt-8 border-t border-gray-200">
                <a href="{{ route('onboarding.step-1') }}" 
                   class="inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-all">
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
                                class="inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-all">
                            Configurar después
                        </button>
                    </form>

                    <button type="submit" 
                            class="inline-flex items-center px-8 py-4 bg-[#0EA5E9] text-white font-semibold rounded-lg hover:bg-[#0284C7] transition-all shadow-lg hover:shadow-xl">
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
