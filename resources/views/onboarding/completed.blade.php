@extends('onboarding.layout')

@php
    $currentStep = 4;
@endphp

@section('content')
<div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
    <!-- Header -->
    <div class="bg-gradient-to-r from-emerald-500 to-green-600 p-8 text-white text-center">
        <div class="text-8xl mb-4 animate-bounce">🎉</div>
        <h1 class="text-4xl font-bold mb-2">¡Felicidades!</h1>
        <p class="text-lg text-white/90">Tu centro médico está configurado y listo para usar</p>
    </div>

    <div class="p-8 md:p-12">
        <!-- Resumen de configuración -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4 text-center">Configuración Completada</h2>
            
            <div class="grid md:grid-cols-3 gap-6 mb-8">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-6 text-center">
                    <div class="text-4xl mb-3">✅</div>
                    <h3 class="font-bold text-gray-800 mb-2">Datos del Centro</h3>
                    <p class="text-sm text-gray-600">{{ $centro->nombre_centro }}</p>
                    <p class="text-xs text-gray-500 mt-1">RTN: {{ $centro->rtn }}</p>
                </div>

                <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-lg p-6 text-center">
                    <div class="text-4xl mb-3">{{ $centro->onboarding_skipped_cai ? '⏭️' : '✅' }}</div>
                    <h3 class="font-bold text-gray-800 mb-2">Facturación CAI</h3>
                    <p class="text-sm text-gray-600">
                        @if($centro->onboarding_skipped_cai)
                            Configurar después
                        @else
                            Configurado correctamente
                        @endif
                    </p>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-lg p-6 text-center">
                    <div class="text-4xl mb-3">✅</div>
                    <h3 class="font-bold text-gray-800 mb-2">Servicios</h3>
                    <p class="text-sm text-gray-600">Catálogo creado</p>
                </div>
            </div>
        </div>

        <!-- Próximos pasos -->
        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 border-2 border-indigo-200 rounded-xl p-6 mb-8">
            <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <span class="text-3xl mr-3">🚀</span>
                Próximos Pasos (Opcionales)
            </h3>
            
            <div class="grid md:grid-cols-2 gap-4">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-500 text-white flex items-center justify-center text-sm font-bold">1</div>
                    <div>
                        <h4 class="font-bold text-gray-800">Agregar Médicos</h4>
                        <p class="text-sm text-gray-600">Registra al personal médico de tu clínica</p>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-purple-500 text-white flex items-center justify-center text-sm font-bold">2</div>
                    <div>
                        <h4 class="font-bold text-gray-800">Registrar Pacientes</h4>
                        <p class="text-sm text-gray-600">Crea el perfil de tus pacientes</p>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-pink-500 text-white flex items-center justify-center text-sm font-bold">3</div>
                    <div>
                        <h4 class="font-bold text-gray-800">Personalizar Facturas</h4>
                        <p class="text-sm text-gray-600">Diseña el formato de tus facturas</p>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-teal-500 text-white flex items-center justify-center text-sm font-bold">4</div>
                    <div>
                        <h4 class="font-bold text-gray-800">Configurar Especialidades</h4>
                        <p class="text-sm text-gray-600">Define las especialidades que ofreces</p>
                    </div>
                </div>
            </div>
        </div>

        @if($centro->onboarding_skipped_cai)
        <!-- Advertencia CAI -->
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-8">
            <div class="flex">
                <div class="text-2xl mr-3">⚠️</div>
                <div>
                    <h3 class="text-sm font-bold text-yellow-900">Recuerda configurar el CAI</h3>
                    <p class="text-sm text-yellow-800 mt-1">
                        No podrás emitir facturas fiscales hasta que configures tu autorización CAI. 
                        Puedes hacerlo desde el menú <strong>Facturación > CAI Autorizaciones</strong>.
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Características disponibles -->
        <div class="mb-8">
            <h3 class="text-xl font-bold text-gray-800 mb-4 text-center">Todo lo que Puedes Hacer Ahora</h3>
            
            <div class="grid md:grid-cols-3 gap-4 text-center">
                <div class="p-4 border border-gray-200 rounded-lg hover:shadow-lg transition-all">
                    <div class="text-3xl mb-2">📅</div>
                    <h4 class="font-bold text-gray-800 text-sm">Gestionar Citas</h4>
                </div>
                <div class="p-4 border border-gray-200 rounded-lg hover:shadow-lg transition-all">
                    <div class="text-3xl mb-2">🩺</div>
                    <h4 class="font-bold text-gray-800 text-sm">Consultas Médicas</h4>
                </div>
                <div class="p-4 border border-gray-200 rounded-lg hover:shadow-lg transition-all">
                    <div class="text-3xl mb-2">💊</div>
                    <h4 class="font-bold text-gray-800 text-sm">Recetas Médicas</h4>
                </div>
                <div class="p-4 border border-gray-200 rounded-lg hover:shadow-lg transition-all">
                    <div class="text-3xl mb-2">🔬</div>
                    <h4 class="font-bold text-gray-800 text-sm">Exámenes de Laboratorio</h4>
                </div>
                <div class="p-4 border border-gray-200 rounded-lg hover:shadow-lg transition-all">
                    <div class="text-3xl mb-2">💵</div>
                    <h4 class="font-bold text-gray-800 text-sm">Facturación Fiscal</h4>
                </div>
                <div class="p-4 border border-gray-200 rounded-lg hover:shadow-lg transition-all">
                    <div class="text-3xl mb-2">📊</div>
                    <h4 class="font-bold text-gray-800 text-sm">Reportes y Estadísticas</h4>
                </div>
            </div>
        </div>

        <!-- Botón principal -->
        <form action="{{ route('onboarding.mark-completed') }}" method="POST">
            @csrf
            <div class="text-center">
                <button type="submit" 
                        class="inline-flex items-center px-12 py-4 bg-gradient-to-r from-emerald-600 to-green-600 text-white font-bold text-lg rounded-xl hover:from-emerald-700 hover:to-green-700 transition-all shadow-2xl hover:shadow-3xl transform hover:scale-105">
                    <span class="text-2xl mr-3">🎯</span>
                    Ir al Panel de Administración
                    <svg class="w-6 h-6 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
                
                <p class="mt-4 text-sm text-gray-500">
                    ¡Estás a punto de comenzar una nueva era en la gestión de tu clínica! 🚀
                </p>
            </div>
        </form>
    </div>
</div>
@endsection
