<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Configuración Inicial - Sanaresys</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="antialiased">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-white/10 backdrop-blur-md border-b border-white/20 py-4">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                            <span class="text-2xl">🏥</span>
                        </div>
                        <div>
                            <h1 class="text-white font-bold text-xl">Sanaresys</h1>
                            <p class="text-white/80 text-sm">Sistema de Gestión Médica</p>
                        </div>
                    </div>
                    
                    <div class="text-white/80 text-sm">
                        <span>👤 {{ auth()->user()->name }}</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Barra de progreso -->
        @if(isset($currentStep))
        <div class="bg-white/10 backdrop-blur-md border-b border-white/20">
            <div class="container mx-auto px-4 py-6">
                <div class="flex items-center justify-center space-x-4">
                    @foreach([1, 2, 3, 4] as $step)
                    <div class="flex items-center">
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold
                                {{ $currentStep >= $step ? 'bg-green-500 text-white' : 'bg-white/20 text-white/50' }}
                                transition-all duration-300">
                                @if($currentStep > $step)
                                    ✓
                                @else
                                    {{ $step }}
                                @endif
                            </div>
                            <span class="mt-2 text-xs text-white/80">
                                @if($step === 1) Datos básicos
                                @elseif($step === 2) Facturación
                                @elseif($step === 3) Servicios
                                @else ¡Listo!
                                @endif
                            </span>
                        </div>
                        @if($step < 4)
                        <div class="w-16 h-1 mx-2 
                            {{ $currentStep > $step ? 'bg-green-500' : 'bg-white/20' }}
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
                <div class="mb-6 bg-green-500/20 border border-green-500/50 text-white rounded-lg p-4">
                    <div class="flex items-center">
                        <span class="text-2xl mr-3">✓</span>
                        <p>{{ session('success') }}</p>
                    </div>
                </div>
                @endif

                @if(session('warning'))
                <div class="mb-6 bg-yellow-500/20 border border-yellow-500/50 text-white rounded-lg p-4">
                    <div class="flex items-center">
                        <span class="text-2xl mr-3">⚠️</span>
                        <p>{{ session('warning') }}</p>
                    </div>
                </div>
                @endif

                @if($errors->any())
                <div class="mb-6 bg-red-500/20 border border-red-500/50 text-white rounded-lg p-4">
                    <div class="flex items-start">
                        <span class="text-2xl mr-3">✕</span>
                        <div>
                            <p class="font-bold mb-2">Por favor corrige los siguientes errores:</p>
                            <ul class="list-disc list-inside space-y-1">
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
