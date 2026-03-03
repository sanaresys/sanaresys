<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Configuración Inicial - Sanaresys</title>
    
    <!-- Google Fonts - Inter (moderna y profesional) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'medical-blue': '#2563EB',
                        'medical-blue-light': '#3B82F6',
                        'medical-cyan': '#0EA5E9',
                        'medical-green': '#22C55E',
                        'medical-gray': '#F1F5F9',
                        'medical-dark': '#1E293B',
                    },
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    
    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: #F8FAFC;
            min-height: 100vh;
        }
    </style>
</head>
<body class="antialiased">
    <div class="min-h-screen flex flex-col">
        <!-- Header limpio y profesional -->
        <header class="bg-white border-b border-gray-200 shadow-sm">
            <div class="container mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-11 h-11 bg-medical-blue rounded-xl flex items-center justify-center shadow-sm">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 6.477V16h2a1 1 0 110 2H7a1 1 0 110-2h2V6.477L6.237 7.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L9 4.323V3a1 1 0 011-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-medical-dark font-bold text-xl tracking-tight">Sanaresys</h1>
                            <p class="text-gray-500 text-sm font-medium">Sistema de Gestión Médica</p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Barra de progreso elegante -->
        @if(isset($currentStep))
        <div class="bg-white border-b border-gray-200">
            <div class="container mx-auto px-6 py-8">
                <div class="flex items-center justify-center space-x-3">
                    @foreach([1, 2, 3, 4] as $step)
                    <div class="flex items-center">
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center font-semibold text-sm
                                {{ $currentStep >= $step ? 'bg-medical-green text-white shadow-lg shadow-medical-green/30' : 'bg-gray-100 text-gray-400 border-2 border-gray-200' }}
                                transition-all duration-300">
                                @if($currentStep > $step)
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    {{ $step }}
                                @endif
                            </div>
                            <span class="mt-3 text-xs font-medium
                                {{ $currentStep >= $step ? 'text-medical-dark' : 'text-gray-400' }}">
                                @if($step === 1) Datos básicos
                                @elseif($step === 2) Facturación
                                @elseif($step === 3) Servicios
                                @else ¡Listo!
                                @endif
                            </span>
                        </div>
                        @if($step < 4)
                        <div class="w-20 h-0.5 mx-2 rounded-full
                            {{ $currentStep > $step ? 'bg-medical-green' : 'bg-gray-200' }}
                            transition-all duration-300"></div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Contenido principal -->
        <main class="flex-1 container mx-auto px-4 py-8">
            <div class="max-w-4xl mx-auto">
                @if(session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-[#22C55E] rounded-r-lg p-4">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-[#22C55E] mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-gray-900 font-medium">{{ session('success') }}</p>
                    </div>
                </div>
                @endif

                @if(session('warning'))
                <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-500 rounded-r-lg p-4">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-yellow-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-gray-900 font-medium">{{ session('warning') }}</p>
                    </div>
                </div>
                @endif

                @if($errors->any())
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 rounded-r-lg p-4">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-red-600 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="font-semibold text-gray-900 mb-2">Por favor corrige los siguientes errores:</p>
                            <ul class="list-disc list-inside space-y-1 text-gray-700">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif

                @yield('content')
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white/10 backdrop-blur-md border-t border-white/20 py-4 mt-auto">
            <div class="container mx-auto px-4 text-center text-white/60 text-sm">
                <p>© {{ date('Y') }} Sanaresys. Sistema de Gestión para Clínicas Médicas.</p>
            </div>
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
