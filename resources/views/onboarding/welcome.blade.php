@extends('onboarding.layout')

@section('content')
<!-- Card principal blanco con sombra suave -->
<div class="bg-white rounded-2xl shadow-lg overflow-hidden">
    
    <!-- Header sólido y limpio (azul médico) -->
    <div class="bg-medical-blue px-8 py-10 text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur-sm rounded-2xl mb-6">
            <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
            </svg>
        </div>
        <h1 class="text-4xl font-bold text-white mb-3">¡Bienvenido a Sanaresys!</h1>
        <p class="text-lg text-white/90 font-medium">Tu sistema de gestión médica profesional</p>
    </div>

    <!-- Contenido -->
    <div class="px-8 py-10">
        
        <!-- Alert elegante y minimalista -->
        <div class="mb-10 bg-blue-50 border-l-4 border-medical-blue rounded-r-lg p-5">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-medical-blue mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-medical-dark mb-1">Configuración inicial requerida</p>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Para comenzar a usar Sanaresys, necesitamos configurar algunos datos básicos de tu centro médico. Este proceso toma solo <strong>5 minutos</strong> y podrás modificar todo después.
                    </p>
                </div>
            </div>
        </div>

        <!-- Pasos en cards limpias -->
        <div class="mb-10">
            <h2 class="text-2xl font-bold text-medical-dark mb-6">Qué vamos a configurar</h2>
            
            <div class="grid md:grid-cols-3 gap-6">
                <!-- Paso 1 -->
                <div class="group hover:shadow-lg transition-all duration-300 bg-gray-50 rounded-xl p-6 border-2 border-transparent hover:border-medical-blue">
                    <div class="w-12 h-12 bg-medical-blue rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-medical-dark mb-2 text-lg">Datos del Centro</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">Nombre, RTN, dirección y contacto de tu clínica</p>
                </div>

                <!-- Paso 2 -->
                <div class="group hover:shadow-lg transition-all duration-300 bg-gray-50 rounded-xl p-6 border-2 border-transparent hover:border-medical-cyan">
                    <div class="w-12 h-12 bg-medical-cyan rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-medical-dark mb-2 text-lg">Configuración Fiscal</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">CAI para facturación legal en Honduras (opcional)</p>
                </div>

                <!-- Paso 3 -->
                <div class="group hover:shadow-lg transition-all duration-300 bg-gray-50 rounded-xl p-6 border-2 border-transparent hover:border-medical-green">
                    <div class="w-12 h-12 bg-medical-green rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-medical-dark mb-2 text-lg">Servicios Médicos</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">Catálogo inicial de consultas y precios</p>
                </div>
            </div>
        </div>

        <!-- Beneficios -->
        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6 mb-10">
            <div class="flex items-start mb-4">
                <div class="w-10 h-10 bg-medical-green rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-medical-dark text-lg mb-3">¿Por qué configurar esto ahora?</h3>
                    <ul class="space-y-2.5">
                        <li class="flex items-start text-sm text-gray-700">
                            <svg class="w-5 h-5 text-medical-green mr-2.5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Podrás <strong>facturar legalmente</strong> desde el primer día</span>
                        </li>
                        <li class="flex items-start text-sm text-gray-700">
                            <svg class="w-5 h-5 text-medical-green mr-2.5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Tus datos estarán <strong>completos y profesionales</strong></span>
                        </li>
                        <li class="flex items-start text-sm text-gray-700">
                            <svg class="w-5 h-5 text-medical-green mr-2.5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Evitarás <strong>errores y retrabajos</strong> posteriores</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Botón principal destacado -->
        <div class="flex flex-col items-center">
            <a href="{{ route('onboarding.step-1') }}" 
               class="inline-flex items-center justify-center px-10 py-4 bg-medical-blue text-white text-lg font-semibold rounded-xl hover:bg-medical-blue-light transition-all duration-200 shadow-lg shadow-medical-blue/30 hover:shadow-xl hover:shadow-medical-blue/40 hover:-translate-y-0.5">
                Comenzar Configuración
                <svg class="w-5 h-5 ml-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </a>
            
            <!-- Tiempo estimado -->
            <div class="mt-6 flex items-center text-sm text-gray-500">
                <svg class="w-4 h-4 mr-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                </svg>
                <span class="font-medium">Tiempo estimado: <strong class="text-medical-dark">5 minutos</strong></span>
            </div>
        </div>

    </div>
</div>
@endsection
