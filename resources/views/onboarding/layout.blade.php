<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Configuracion inicial | Sanaresys</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/onboarding-premium.css') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,800&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --ink: #10212a;
            --ink-soft: #48606f;
            --paper: #f6f1e7;
            --paper-strong: #fffaf1;
            --accent: #0f8a8d;
            --accent-strong: #0a5f61;
            --gold: #c68c2f;
            --line: #d8cab4;
            --radius-xl: 1rem;
            --radius-2xl: 1.5rem;
        }

        body {
            font-family: 'Public Sans', sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at 10% 10%, rgba(15, 138, 141, 0.08), transparent 35%),
                radial-gradient(circle at 92% 18%, rgba(198, 140, 47, 0.12), transparent 26%),
                linear-gradient(180deg, var(--paper-strong) 0%, var(--paper) 100%);
            min-height: 100vh;
        }

        .brand-title,
        .display-title {
            font-family: 'Fraunces', serif;
            letter-spacing: -0.02em;
        }

        .card-premium {
            background: rgba(255, 251, 242, 0.84);
            border: 1px solid var(--line);
            border-radius: var(--radius-2xl);
            box-shadow: 0 12px 35px rgba(16, 33, 42, 0.08);
            backdrop-filter: blur(6px);
        }
    </style>
</head>
<body>
    <div class="min-h-screen flex flex-col">
        <header class="border-b" style="border-color: var(--line); background: rgba(255, 250, 241, 0.9); backdrop-filter: blur(4px);">
            <div class="w-full px-4 md:px-6 lg:px-8 py-4 md:py-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, var(--accent) 0%, var(--accent-strong) 100%); box-shadow: 0 8px 20px rgba(10,95,97,.25);">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 6.477V16h2a1 1 0 110 2H7a1 1 0 110-2h2V6.477L6.237 7.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L9 4.323V3a1 1 0 011-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="brand-title text-xl font-extrabold">Sanaresys</h1>
                        <p class="text-sm" style="color: var(--ink-soft);">Implementacion inicial</p>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 w-full px-2 md:px-4 lg:px-6 py-4 md:py-6">
            <div class="max-w-[1760px] mx-auto">
                @if(session('success'))
                <div class="mb-6 rounded-xl border-l-4 p-4" style="background: #ecfdf5; border-color: #10b981;">
                    <p class="font-semibold" style="color: #065f46;">{{ session('success') }}</p>
                </div>
                @endif

                @if(session('warning'))
                <div class="mb-6 rounded-xl border-l-4 p-4" style="background: #fff7ed; border-color: #f59e0b;">
                    <p class="font-semibold" style="color: #9a3412;">{{ session('warning') }}</p>
                </div>
                @endif

                @if($errors->any())
                <div class="mb-6 rounded-xl border-l-4 p-4" style="background: #fef2f2; border-color: #ef4444;">
                    <p class="font-semibold mb-2" style="color: #991b1b;">Corrige estos errores:</p>
                    <ul class="list-disc list-inside text-sm" style="color: #7f1d1d;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @yield('content')
            </div>
        </main>

        <footer class="py-4 border-t" style="border-color: var(--line);">
            <div class="w-full px-4 md:px-6 lg:px-8 text-center text-sm" style="color: var(--ink-soft);">
                <p>© {{ date('Y') }} Sanaresys. Plataforma de gestion para clinicas medicas.</p>
            </div>
        </footer>
    </div>

    <script src="{{ asset('js/onboarding-effects.js') }}"></script>
    @stack('scripts')
</body>
</html>
