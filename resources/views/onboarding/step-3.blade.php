@extends('onboarding.layout')

@php
    $currentStep = 3;
@endphp

@section('content')
<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <!-- Header -->
    <div class="bg-[#22C55E] p-8 text-white">
        <div class="flex items-center">
            <div class="w-16 h-16 bg-white/20 rounded-xl flex items-center justify-center mr-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold">Servicios Médicos</h1>
                <p class="text-white/90 mt-1 text-lg">Catálogo inicial de consultas y servicios</p>
            </div>
        </div>
    </div>

    <div class="p-8">
        <!-- Info -->
        <div class="bg-green-50 border-l-4 border-[#22C55E] p-6 mb-8 rounded-r-lg">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-[#22C55E] flex-shrink-0 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm text-gray-700">
                    Agrega al menos <strong>1 servicio</strong> para comenzar. Puedes agregar más servicios 
                    después desde el panel de administración.
                </p>
            </div>
        </div>

        <form action="{{ route('onboarding.save-step-3') }}" method="POST" id="serviciosForm" class="space-y-8">
            @csrf

            <!-- Servicios dinámicos -->
            <div id="servicios-container" class="space-y-6">
                <!-- Servicio 1 (predefinido) -->
                <div class="servicio-item border-2 border-gray-200 rounded-lg p-6 bg-white shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-semibold text-lg text-gray-900">Servicio #1</h3>
                    </div>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="md:col-span-2 space-y-2">
                            <label class="block text-sm font-semibold text-gray-900">
                                <span class="text-red-500">*</span> Nombre del Servicio
                            </label>
                            <input type="text" 
                                   name="servicios[0][nombre]" 
                                   required
                                   class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#22C55E] focus:border-[#22C55E] transition-all text-gray-900"
                                   placeholder="Ej: Consulta General">
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-900">
                                <span class="text-red-500">*</span> Precio (L)
                            </label>
                            <input type="number" 
                                   name="servicios[0][precio]" 
                                   required
                                   min="0"
                                   step="0.01"
                                   class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#22C55E] focus:border-[#22C55E] transition-all text-gray-900"
                                   placeholder="500.00">
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-900">
                                Descripción (opcional)
                            </label>
                            <input type="text" 
                                   name="servicios[0][descripcion]" 
                                   class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#22C55E] focus:border-[#22C55E] transition-all text-gray-900"
                                   placeholder="Ej: Medicina General">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón agregar servicio -->
            <div>
                <button type="button" 
                        id="add-servicio"
                        class="inline-flex items-center px-6 py-3 border-2 border-dashed border-[#22C55E] text-[#22C55E] font-medium rounded-lg hover:bg-green-50 hover:border-[#16A34A] transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Agregar Otro Servicio
                </button>
            </div>

            <!-- Servicios sugeridos -->
            <div class="bg-blue-50 border-l-4 border-[#0EA5E9] rounded-r-lg p-6">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-[#0EA5E9] flex-shrink-0 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-3">Servicios Comunes:</h3>
                        <div class="grid md:grid-cols-2 gap-2 text-sm text-gray-700">
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
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-between items-center pt-8 mt-8 border-t border-gray-200">
                <a href="{{ route('onboarding.step-2') }}" 
                   class="inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Atrás
                </a>

                <button type="submit" 
                        class="inline-flex items-center px-8 py-4 bg-[#22C55E] text-white font-semibold rounded-lg hover:bg-[#16A34A] transition-all shadow-lg hover:shadow-xl">
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
            <div class="servicio-item border-2 border-gray-200 rounded-lg p-6 bg-white shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-semibold text-lg text-gray-900">Servicio #${servicioCount + 1}</h3>
                    <button type="button" 
                            class="remove-servicio inline-flex items-center px-3 py-2 text-red-600 hover:text-white hover:bg-red-600 border border-red-600 font-medium rounded-lg transition-all"
                            onclick="this.closest('.servicio-item').remove()">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Eliminar
                    </button>
                </div>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="md:col-span-2 space-y-2">
                        <label class="block text-sm font-semibold text-gray-900">
                            <span class="text-red-500">*</span> Nombre del Servicio
                        </label>
                        <input type="text" 
                               name="servicios[${servicioCount}][nombre]" 
                               required
                               class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#22C55E] focus:border-[#22C55E] transition-all text-gray-900"
                               placeholder="Ej: Consulta Especializada">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-900">
                            <span class="text-red-500">*</span> Precio (L)
                        </label>
                        <input type="number" 
                               name="servicios[${servicioCount}][precio]" 
                               required
                               min="0"
                               step="0.01"
                               class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#22C55E] focus:border-[#22C55E] transition-all text-gray-900"
                               placeholder="800.00">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-900">
                            Descripción (opcional)
                        </label>
                        <input type="text" 
                               name="servicios[${servicioCount}][descripcion]" 
                               class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#22C55E] focus:border-[#22C55E] transition-all text-gray-900"
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
