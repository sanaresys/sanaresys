@extends('onboarding.layout')

@php
    $currentStep = 4;
@endphp

@section('content')
<div class="card-premium card-appear">
    <!-- Header de celebración con efectos animados -->
    <div class="relative bg-gradient-to-br from-green-600 via-emerald-600 to-teal-600 px-12 py-20 text-white text-center overflow-hidden">
        <!-- Partículas de fondo animadas -->
        <div class="absolute inset-0 opacity-20">
            <div class="absolute top-10 left-10 w-32 h-32 bg-white rounded-full blur-3xl animate-pulse" style="animation-duration: 3s"></div>
            <div class="absolute top-20 right-20 w-40 h-40 bg-white rounded-full blur-3xl animate-pulse" style="animation-duration: 4s; animation-delay: 1s"></div>
            <div class="absolute bottom-10 left-1/3 w-36 h-36 bg-white rounded-full blur-3xl animate-pulse" style="animation-duration: 3.5s; animation-delay: 0.5s"></div>
        </div>
        
        <div class="relative z-10">
            <div class="inline-flex items-center justify-center w-32 h-32 bg-white/20 backdrop-blur-md rounded-full mb-8 shadow-2xl animate-scale-in">
                <svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 class="text-6xl font-extrabold mb-4 tracking-tight animate-fade-in" style="animation-delay: 0.1s">¡Felicidades!</h1>
            <p class="text-2xl text-white/95 font-medium max-w-2xl mx-auto animate-fade-in" style="animation-delay: 0.2s">Tu centro médico está configurado y listo para usar</p>
        </div>
    </div>

    <div class="px-12 py-16">
        <!-- Resumen de configuración con cards premium -->
        <div class="mb-16">
            <h2 class="text-3xl font-bold text-gray-900 mb-10 text-center animate-fade-in" style="animation-delay: 0.3s">Configuración Completada</h2>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-300 rounded-2xl p-8 text-center shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 animate-scale-in" style="animation-delay: 0.4s">
                    <div class="w-16 h-16 mx-auto mb-5 bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl flex items-center justify-center shadow-xl">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-3 text-lg">Datos del Centro</h3>
                    <p class="text-base text-gray-800 font-semibold">{{ $centro->nombre_centro }}</p>
                    <p class="text-sm text-gray-600 mt-2">RTN: {{ $centro->rtn }}</p>
                </div>

                <div class="bg-gradient-to-br from-cyan-50 to-blue-50 border-2 border-cyan-300 rounded-2xl p-8 text-center shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 animate-scale-in" style="animation-delay: 0.5s">
                    <div class="w-16 h-16 mx-auto mb-5 bg-gradient-to-br from-cyan-600 to-cyan-700 rounded-2xl flex items-center justify-center shadow-xl">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($centro->onboarding_skipped_cai)
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            @endif
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-3 text-lg">Facturación CAI</h3>
                    <p class="text-base text-gray-700 font-medium">
                        @if($centro->onboarding_skipped_cai)
                            Configurar después
                        @else
                            Configurado correctamente
                        @endif
                    </p>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-300 rounded-2xl p-8 text-center shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 animate-scale-in" style="animation-delay: 0.6s">
                    <div class="w-16 h-16 mx-auto mb-5 bg-gradient-to-br from-green-600 to-green-700 rounded-2xl flex items-center justify-center shadow-xl">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-3 text-lg">Servicios</h3>
                    <p class="text-base text-gray-700 font-medium">Catálogo creado</p>
                </div>
            </div>
        </div>

        <!-- Próximos pasos con diseño premium -->
        <!-- Próximos pasos con diseño premium -->
        <div class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 border-2 border-blue-200 rounded-3xl p-10 mb-16 shadow-xl animate-fade-in" style="animation-delay: 0.7s">
            <div class="flex items-center mb-8">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3 class="text-3xl font-bold text-gray-900 ml-5">Próximos Pasos</h3>
            </div>
            
            <div class="grid md:grid-cols-2 gap-6">
                <div class="flex items-start bg-white rounded-xl p-6 shadow-md hover:shadow-xl transition-all duration-300 group">
                    <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-gradient-to-br from-blue-600 to-blue-700 text-white flex items-center justify-center font-bold text-lg shadow-md group-hover:scale-110 transition-transform">1</div>
                    <div class="ml-5">
                        <h4 class="font-bold text-gray-900 mb-2 text-lg">Agregar Médicos</h4>
                        <p class="text-sm text-gray-600 leading-relaxed">Registra al personal médico de tu clínica</p>
                    </div>
                </div>

                <div class="flex items-start bg-white rounded-xl p-6 shadow-md hover:shadow-xl transition-all duration-300 group">
                    <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-gradient-to-br from-cyan-600 to-cyan-700 text-white flex items-center justify-center font-bold text-lg shadow-md group-hover:scale-110 transition-transform">2</div>
                    <div class="ml-5">
                        <h4 class="font-bold text-gray-900 mb-2 text-lg">Registrar Pacientes</h4>
                        <p class="text-sm text-gray-600 leading-relaxed">Crea el perfil de tus pacientes</p>
                    </div>
                </div>

                <div class="flex items-start bg-white rounded-xl p-6 shadow-md hover:shadow-xl transition-all duration-300 group">
                    <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-gradient-to-br from-green-600 to-green-700 text-white flex items-center justify-center font-bold text-lg shadow-md group-hover:scale-110 transition-transform">3</div>
                    <div class="ml-5">
                        <h4 class="font-bold text-gray-900 mb-2 text-lg">Personalizar Facturas</h4>
                        <p class="text-sm text-gray-600 leading-relaxed">Diseña el formato de tus facturas</p>
                    </div>
                </div>

                <div class="flex items-start bg-white rounded-xl p-6 shadow-md hover:shadow-xl transition-all duration-300 group">
                    <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-gradient-to-br from-purple-600 to-purple-700 text-white flex items-center justify-center font-bold text-lg shadow-md group-hover:scale-110 transition-transform">4</div>
                    <div class="ml-5">
                        <h4 class="font-bold text-gray-900 mb-2 text-lg">Configurar Especialidades</h4>
                        <p class="text-sm text-gray-600 leading-relaxed">Define las especialidades que ofreces</p>
                    </div>
                </div>
            </div>
        </div>

        @if($centro->onboarding_skipped_cai)
        <!-- Advertencia CAI con mejor diseño -->
        <div class="bg-gradient-to-r from-yellow-50 to-amber-50 border-l-4 border-yellow-500 p-8 rounded-r-2xl mb-16 shadow-lg animate-slide-in" style="animation-delay: 0.8s">
            <div class="flex items-start">
                <div class="flex-shrink-0 w-12 h-12 bg-yellow-500 rounded-xl flex items-center justify-center shadow-md">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-5">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Recuerda configurar el CAI</h3>
                    <p class="text-sm text-gray-800 leading-relaxed">
                        No podrás emitir facturas fiscales hasta que configures tu autorización CAI. 
                        Puedes hacerlo desde el menú <strong>Facturación > CAI Autorizaciones</strong>.
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!--Características disponibles con grid mejorado -->
        <div class="mb-16 animate-fade-in" style="animation-delay: 0.9s">
            <h3 class="text-2xl font-bold text-gray-900 mb-10 text-center">Todo lo que Puedes Hacer Ahora</h3>
            
            <div class="grid md:grid-cols-3 gap-6">
                <div class="group p-8 bg-white border-2 border-gray-200 rounded-2xl hover:border-blue-500 hover:shadow-2xl transition-all duration-300 text-center cursor-pointer">
                    <div class="w-16 h-16 mx-auto mb-5 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 group-hover:rotate-3 transition-all">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h4 class="font-bold text-gray-900 text-base mb-2">Gestionar Citas</h4>
                    <p class="text-sm text-gray-600">Calendario inteligente</p>
                </div>
                <div class="group p-8 bg-white border-2 border-gray-200 rounded-2xl hover:border-cyan-500 hover:shadow-2xl transition-all duration-300 text-center cursor-pointer">
                    <div class="w-16 h-16 mx-auto mb-5 bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 group-hover:rotate-3 transition-all">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h4 class="font-bold text-gray-900 text-base mb-2">Consultas Médicas</h4>
                    <p class="text-sm text-gray-600">Expedientes digitales</p>
                </div>
                <div class="group p-8 bg-white border-2 border-gray-200 rounded-2xl hover:border-green-500 hover:shadow-2xl transition-all duration-300 text-center cursor-pointer">
                    <div class="w-16 h-16 mx-auto mb-5 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 group-hover:rotate-3 transition-all">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h4 class="font-bold text-gray-900 text-base mb-2">Recetas Médicas</h4>
                    <p class="text-sm text-gray-600">Prescripciones digitales</p>
                </div>
                <div class="group p-8 bg-white border-2 border-gray-200 rounded-2xl hover:border-purple-500 hover:shadow-2xl transition-all duration-300 text-center cursor-pointer">
                    <div class="w-16 h-16 mx-auto mb-5 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 group-hover:rotate-3 transition-all">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                    </div>
                    <h4 class="font-bold text-gray-900 text-base mb-2">Exámenes Laboratorio</h4>
                    <p class="text-sm text-gray-600">Resultados en línea</p>
                </div>
                <div class="group p-8 bg-white border-2 border-gray-200 rounded-2xl hover:border-green-500 hover:shadow-2xl transition-all duration-300 text-center cursor-pointer">
                    <div class="w-16 h-16 mx-auto mb-5 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 group-hover:rotate-3 transition-all">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h4 class="font-bold text-gray-900 text-base mb-2">Facturación Fiscal</h4>
                    <p class="text-sm text-gray-600">CAI autorizado</p>
                </div>
                <div class="group p-8 bg-white border-2 border-gray-200 rounded-2xl hover:border-indigo-500 hover:shadow-2xl transition-all duration-300 text-center cursor-pointer">
                    <div class="w-16 h-16 mx-auto mb-5 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 group-hover:rotate-3 transition-all">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h4 class="font-bold text-gray-900 text-base mb-2">Reportes y Estadísticas</h4>
                    <p class="text-sm text-gray-600">Análisis en tiempo real</p>
                </div>
            </div>
        </div>

        <!-- Botón principal con animación de celebración -->
        <form action="{{ route('onboarding.mark-completed') }}" method="POST">
            @csrf
            <div class="text-center animate-scale-in" style="animation-delay: 1s">
                <button type="submit" 
                        class="group relative inline-flex items-center px-16 py-6 bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 text-white font-extrabold text-xl rounded-2xl hover:from-blue-700 hover:via-blue-800 hover:to-indigo-800 transition-all duration-300 shadow-2xl hover:shadow-3xl hover:-translate-y-1 active:translate-y-0 btn-ripple">
                    <span class="absolute inset-0 w-full h-full bg-white rounded-2xl blur opacity-0 group-hover:opacity-25 transition-opacity"></span>
                    <span class="relative">Ir al Panel de Administración</span>
                    <svg class="relative w-7 h-7 ml-4 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
                
                <div class="mt-6 inline-flex items-center px-6 py-3 bg-white rounded-full shadow-lg border-2 border-gray-200">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm text-gray-700 font-bold">¡Estás listo para transformar tu clínica!</span>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
