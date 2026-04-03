<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Configuración inicial | Sanaresys</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/onboarding-premium.css') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* Override global body styles para onboarding */
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: #FDF9F3;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* Ocultar header/footer del layout de Filament si se escapan */
        #onb-app { min-height: 100vh; }
    </style>
</head>
<body id="onb-app">
    {{-- Alertas de sesión --}}
    @if(session('success') || session('warning') || $errors->any())
    <div style="position: fixed; top: 1rem; right: 1rem; z-index: 9999; max-width: 380px; display: flex; flex-direction: column; gap: 0.5rem;">
        @if(session('success'))
        <div class="onb-alert onb-alert-success onb-fade-up">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="flex-shrink:0;color:#1A7A4A;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p style="margin:0;font-weight:600;">{{ session('success') }}</p>
        </div>
        @endif

        @if(session('warning'))
        <div class="onb-alert onb-alert-warning onb-fade-up">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="flex-shrink:0;color:#5C4300;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <p style="margin:0;font-weight:600;">{{ session('warning') }}</p>
        </div>
        @endif

        @if($errors->any())
        <div class="onb-alert onb-alert-error onb-fade-up">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="flex-shrink:0;color:#C0392B;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p style="margin:0 0 0.25rem;font-weight:700;color:#C0392B;">Corrige los siguientes errores:</p>
                <ul style="margin:0;padding-left:1.1rem;">
                    @foreach($errors->all() as $error)
                    <li style="font-size:13px;color:#C0392B;">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>
    @endif

    @yield('content')

    <script src="{{ asset('js/onboarding-effects.js') }}"></script>
    @stack('scripts')

    {{-- Auto-dismiss alertas --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.onb-alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                alert.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateX(12px)';
                setTimeout(() => alert.remove(), 400);
            }, 4500);
        });
    });
    </script>
</body>
</html>
