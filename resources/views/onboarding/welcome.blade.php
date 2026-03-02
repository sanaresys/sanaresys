@extends('onboarding.layout')

@section('content')
<div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
    <!-- Header con gradiente -->
    <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-8 text-white text-center">
        <div class="text-6xl mb-4">🎉</div>
        <h1 class="text-4xl font-bold mb-2">¡Bienvenido a Sanaresys!</h1>
        <p class="text-lg text-white/90">Tu sistema de gestión médica profesional</p>
    </div>

    <div class="p-8 md:p-12">
        <!-- Descripción -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Configuremos tu clínica en 5 minutos</h2>
            <p class="text-gray-600 leading-relaxed">
                Para comenzar a usar Sanaresys, necesitamos que configures algunos datos básicos de tu centro médico.
                Este proceso es rápido y sencillo, y podrás modificar la información en cualquier momento.
            </p>
        </div>

        <!-- Características -->
        <div class="grid md:grid-cols-2 gap-6 mb-8">
            <div class="flex items-start space-x-4 p-4 bg-blue-50 rounded-lg">
                <div class="text-3xl">📋</div>
                <div>
                    <h3 class="font-bold text-gray-800 mb-1">Datos del Centro</h3>
                    <p class="text-sm text-gray-600">Información básica y datos de contacto</p>
                </div>
            </div>

            <div class="flex items-start space-x-4 p-4 bg-purple-50 rounded-lg">
                <div class="text-3xl">📄</div>
                <div>
                    <h3 class="font-bold text-gray-800 mb-1">Configuración Fiscal</h3>
                    <p class="text-sm text-gray-600">CAI para facturación legal en Honduras</p>
                </div>
            </div>

            <div class="flex items-start space-x-4 p-4 bg-green-50 rounded-lg">
                <div class="text-3xl">💰</div>
                <div>
                    <h3 class="font-bold text-gray-800 mb-1">Servicios Médicos</h3>
                    <p class="text-sm text-gray-600">Catálogo inicial de consultas y servicios</p>
                </div>
            </div>

            <div class="flex items-start space-x-4 p-4 bg-yellow-50 rounded-lg">
                <div class="text-3xl">🚀</div>
                <div>
                    <h3 class="font-bold text-gray-800 mb-1">¡Listo para Usar!</h3>
                    <p class="text-sm text-gray-600">Acceso completo al sistema en minutos</p>
                </div>
            </div>
        </div>

        <!-- Ventajas -->
        <div class="bg-gradient-to-r from-emerald-50 to-teal-50 border-l-4 border-emerald-500 p-6 rounded-lg mb-8">
            <h3 class="font-bold text-gray-800 mb-3 flex items-center">
                <span class="text-2xl mr-2">💡</span>
                ¿Por qué configurar esto ahora?
            </h3>
            <ul class="space-y-2 text-gray-700">
                <li class="flex items-start">
                    <span class="text-green-500 mr-2">✓</span>
                    <span>Podrás facturar legalmente desde el primer día</span>
                </li>
                <li class="flex items-start">
                    <span class="text-green-500 mr-2">✓</span>
                    <span>Tus datos estarán completos y profesionales</span>
                </li>
                <li class="flex items-start">
                    <span class="text-green-500 mr-2">✓</span>
                    <span>Evitarás errores y retrabajos posteriores</span>
                </li>
                <li class="flex items-start">
                    <span class="text-green-500 mr-2">✓</span>
                    <span>El sistema estará optimizado para tu clínica</span>
                </li>
            </ul>
        </div>

        <!-- Botones de acción -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('onboarding.step-1') }}" 
               class="inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                <span class="text-xl mr-2">🚀</span>
                Comenzar Configuración
            </a>
        </div>

        <!-- Tiempo estimado -->
        <div class="mt-6 text-center text-sm text-gray-500">
            <span class="inline-flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Tiempo estimado: 5 minutos
            </span>
        </div>
    </div>
</div>
@endsection
