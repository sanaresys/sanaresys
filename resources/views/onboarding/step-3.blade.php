@extends('onboarding.layout')

@php
    $currentStep = 3;
@endphp

@section('content')
<div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-500 to-teal-600 p-6 text-white">
        <div class="flex items-center">
            <div class="text-4xl mr-4">💰</div>
            <div>
                <h1 class="text-3xl font-bold">Servicios Médicos</h1>
                <p class="text-white/90 mt-1">Catálogo inicial de consultas y servicios</p>
            </div>
        </div>
    </div>

    <div class="p-8">
        <!-- Info -->
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0 text-2xl">💡</div>
                <div class="ml-3">
                    <p class="text-sm text-green-800">
                        Agrega al menos <strong>1 servicio</strong> para comenzar. Puedes agregar más servicios 
                        después desde el panel de administración.
                    </p>
                </div>
            </div>
        </div>

        <form action="{{ route('onboarding.save-step-3') }}" method="POST" id="serviciosForm" class="space-y-6">
            @csrf

            <!-- Servicios dinámicos -->
            <div id="servicios-container" class="space-y-4">
                <!-- Servicio 1 (predefinido) -->
                <div class="servicio-item border border-gray-200 rounded-lg p-4 bg-gray-50">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-gray-700">Servicio #1</h3>
                    </div>
                    
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <span class="text-red-500">*</span> Nombre del Servicio
                            </label>
                            <input type="text" 
                                   name="servicios[0][nombre]" 
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                   placeholder="Ej: Consulta General">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <span class="text-red-500">*</span> Precio (L)
                            </label>
                            <input type="number" 
                                   name="servicios[0][precio]" 
                                   required
                                   min="0"
                                   step="0.01"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                   placeholder="500.00">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Descripción (opcional)
                            </label>
                            <input type="text" 
                                   name="servicios[0][descripcion]" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                   placeholder="Ej: Medicina General">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón agregar servicio -->
            <div>
                <button type="button" 
                        id="add-servicio"
                        class="inline-flex items-center px-6 py-3 border-2 border-dashed border-green-300 text-green-700 font-medium rounded-lg hover:bg-green-50 hover:border-green-500 transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Agregar Otro Servicio
                </button>
            </div>

            <!-- Servicios sugeridos -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-bold text-blue-900 mb-3">📋 Servicios Comunes:</h3>
                <div class="grid md:grid-cols-2 gap-2 text-sm text-blue-800">
                    <div>• Consulta General</div>
                    <div>• Consulta Especializada</div>
                    <div>• Control Prenatal</div>
                    <div>• Chequeo Médico</div>
                    <div>• Consulta Pediátrica</div>
                    <div>• Inyecciones</div>
                    <div>• Curaciones</div>
                    <div>• Toma de Presión</div>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-between items-center pt-6 border-t">
                <a href="{{ route('onboarding.step-2') }}" 
                   class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Atrás
                </a>

                <button type="submit" 
                        class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-green-600 to-teal-600 text-white font-bold rounded-lg hover:from-green-700 hover:to-teal-700 transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                    Finalizar
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let servicioCount = 1;

    document.getElementById('add-servicio').addEventListener('click', function() {
        const container = document.getElementById('servicios-container');
        const newServicio = `
            <div class="servicio-item border border-gray-200 rounded-lg p-4 bg-gray-50">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-700">Servicio #${servicioCount + 1}</h3>
                    <button type="button" 
                            class="remove-servicio text-red-500 hover:text-red-700 font-bold"
                            onclick="this.closest('.servicio-item').remove()">
                        ✕ Eliminar
                    </button>
                </div>
                
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <span class="text-red-500">*</span> Nombre del Servicio
                        </label>
                        <input type="text" 
                               name="servicios[${servicioCount}][nombre]" 
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               placeholder="Ej: Consulta Especializada">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <span class="text-red-500">*</span> Precio (L)
                        </label>
                        <input type="number" 
                               name="servicios[${servicioCount}][precio]" 
                               required
                               min="0"
                               step="0.01"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               placeholder="800.00">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Descripción (opcional)
                        </label>
                        <input type="text" 
                               name="servicios[${servicioCount}][descripcion]" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               placeholder="Ej: Descripción del servicio">
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
