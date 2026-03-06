@extends('onboarding.layout')

@php
    $currentStep = 4;
@endphp

@section('content')
<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <!-- Header -->
    <div class="bg-[#22C55E] p-12 text-white text-center">
        <div class="w-24 h-24 mx-auto mb-6 bg-white/20 rounded-full flex items-center justify-center">
            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1 class="text-4xl font-bold mb-3">¡Felicidades!</h1>
        <p class="text-xl text-white/90">Tu centro médico está configurado y listo para usar</p>
    </div>

    <div class="p-8 md:p-12">
        <!-- Resumen de configuración -->
        <div class="mb-10">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Configuración Completada</h2>
            
            <div class="grid md:grid-cols-3 gap-6 mb-8">
                <div class="bg-blue-50 border-2 border-[#2563EB] rounded-lg p-6 text-center">
                    <div class="w-12 h-12 mx-auto mb-3 bg-[#2563EB] rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Datos del Centro</h3>
                    <p class="text-sm text-gray-700 font-medium">{{ $centro->nombre_centro }}</p>
                    <p class="text-xs text-gray-500 mt-1">RTN: {{ $centro->rtn }}</p>
                </div>

                <div class="bg-cyan-50 border-2 border-[#0EA5E9] rounded-lg p-6 text-center">
                    <div class="w-12 h-12 mx-auto mb-3 bg-[#0EA5E9] rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($centro->onboarding_skipped_cai)
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            @endif
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Facturación CAI</h3>
                    <p class="text-sm text-gray-700">
                        @if($centro->onboarding_skipped_cai)
                            Configurar después
                        @else
                            Configurado correctamente
                        @endif
                    </p>
                </div>

                <div class="bg-green-50 border-2 border-[#22C55E] rounded-lg p-6 text-center">
                    <div class="w-12 h-12 mx-auto mb-3 bg-[#22C55E] rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Servicios</h3>
                    <p class="text-sm text-gray-700">Catálogo creado</p>
                </div>
            </div>
        </div>

        <!-- Próximos pasos -->
        <div class="bg-blue-50 border-l-4 border-[#2563EB] rounded-r-lg p-8 mb-10">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 bg-[#2563EB] rounded-lg flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900">Próximos Pasos (Opcionales)</h3>
            </div>
            
            <div class="grid md:grid-cols-2 gap-6">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-[#2563EB] text-white flex items-center justify-center font-semibold">1</div>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-1">Agregar Médicos</h4>
                        <p class="text-sm text-gray-600">Registra al personal médico de tu clínica</p>
                    </div>
                </div>

                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-[#0EA5E9] text-white flex items-center justify-center font-semibold">2</div>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-1">Registrar Pacientes</h4>
                        <p class="text-sm text-gray-600">Crea el perfil de tus pacientes</p>
                    </div>
                </div>

                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-[#22C55E] text-white flex items-center justify-center font-semibold">3</div>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-1">Personalizar Facturas</h4>
                        <p class="text-sm text-gray-600">Diseña el formato de tus facturas</p>
                    </div>
                </div>

                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-[#2563EB] text-white flex items-center justify-center font-semibold">4</div>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-1">Configurar Especialidades</h4>
                        <p class="text-sm text-gray-600">Define las especialidades que ofreces</p>
                    </div>
                </div>
            </div>
        </div>

        @if($centro->onboarding_skipped_cai)
        <!-- Advertencia CAI -->
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-6 rounded-r-lg mb-10">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-yellow-600 flex-shrink-0 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Recuerda configurar el CAI</h3>
                    <p class="text-sm text-gray-700 mt-1">
                        No podrás emitir facturas fiscales hasta que configures tu autorización CAI. 
                        Puedes hacerlo desde el menú <strong>Facturación > CAI Autorizaciones</strong>.
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Características disponibles -->
        <div class="mb-10">
            <h3 class="text-xl font-bold text-gray-900 mb-6 text-center">Todo lo que Puedes Hacer Ahora</h3>
            
            <div class="grid md:grid-cols-3 gap-4">
                <div class="p-6 bg-white border-2 border-gray-200 rounded-lg hover:border-[#2563EB] hover:shadow-md transition-all text-center">
                    <div class="w-12 h-12 mx-auto mb-3 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#2563EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-900 text-sm">Gestionar Citas</h4>
                </div>
                <div class="p-6 bg-white border-2 border-gray-200 rounded-lg hover:border-[#2563EB] hover:shadow-md transition-all text-center">
                    <div class="w-12 h-12 mx-auto mb-3 bg-cyan-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#0EA5E9]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-900 text-sm">Consultas Médicas</h4>
                </div>
                <div class="p-6 bg-white border-2 border-gray-200 rounded-lg hover:border-[#2563EB] hover:shadow-md transition-all text-center">
                    <div class="w-12 h-12 mx-auto mb-3 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#22C55E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-900 text-sm">Recetas Médicas</h4>
                </div>
                <div class="p-6 bg-white border-2 border-gray-200 rounded-lg hover:border-[#2563EB] hover:shadow-md transition-all text-center">
                    <div class="w-12 h-12 mx-auto mb-3 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#2563EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-900 text-sm">Exámenes de Laboratorio</h4>
                </div>
                <div class="p-6 bg-white border-2 border-gray-200 rounded-lg hover:border-[#2563EB] hover:shadow-md transition-all text-center">
                    <div class="w-12 h-12 mx-auto mb-3 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#22C55E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-900 text-sm">Facturación Fiscal</h4>
                </div>
                <div class="p-6 bg-white border-2 border-gray-200 rounded-lg hover:border-[#2563EB] hover:shadow-md transition-all text-center">
                    <div class="w-12 h-12 mx-auto mb-3 bg-cyan-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#0EA5E9]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-900 text-sm">Reportes y Estadísticas</h4>
                </div>
            </div>
        </div>

        <!-- Botón principal -->
        <form action="{{ route('onboarding.mark-completed') }}" method="POST">
            @csrf
            <div class="text-center">
                <button type="submit" 
                        class="inline-flex items-center px-12 py-5 bg-[#2563EB] text-white font-semibold text-lg rounded-lg hover:bg-[#1D4ED8] transition-all shadow-xl hover:shadow-2xl">
                    Ir al Panel de Administración
                    <svg class="w-6 h-6 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
                
                <p class="mt-4 text-sm text-gray-600">
                    ¡Estás a punto de comenzar una nueva era en la gestión de tu clínica!
                </p>
            </div>
        </form>
    </div>
</div>
@endsection
