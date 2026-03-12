<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sanare simplifica la gestion de clinicas, medicos, citas, recetas y reportes en un solo lugar.">
    <title>Sanare | Gestion medica moderna para clinicas</title>

    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/welcome.js'])

    <style>
        :root {
            --font-display: 'Sora', 'Segoe UI', sans-serif;
            --font-body: 'Source Sans 3', 'Segoe UI', sans-serif;
            --color-primary: #0f766e;
            --color-primary-deep: #115e59;
            --color-accent: #0284c7;
            --color-accent-soft: #e0f2fe;
            --color-support: #f59e0b;
            --color-support-soft: #fffbeb;
            --neutral-950: #0f172a;
            --neutral-900: #1e293b;
            --neutral-700: #475569;
            --neutral-500: #64748b;
            --neutral-200: #e2e8f0;
            --neutral-100: #f1f5f9;
            --surface: #ffffff;
            --surface-subtle: #f8fafc;
            --ring: #16a34a;
            --shadow-soft: 0 18px 40px rgba(15, 23, 42, 0.08);
            --shadow-focus: 0 0 0 3px rgba(34, 197, 94, 0.35);
            --radius-xl: 1.25rem;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            font-family: var(--font-body);
            color: var(--neutral-900);
            background:
                radial-gradient(circle at 12% 8%, rgba(14, 165, 233, 0.18), transparent 45%),
                radial-gradient(circle at 88% 2%, rgba(15, 118, 110, 0.15), transparent 35%),
                linear-gradient(180deg, #f8fbff 0%, #f8fafc 38%, #ffffff 100%);
        }

        h1, h2, h3, h4 {
            font-family: var(--font-display);
            color: var(--neutral-950);
        }

        .skip-link {
            position: absolute;
            top: -44px;
            left: 12px;
            background: var(--neutral-950);
            color: #fff;
            padding: 0.6rem 0.9rem;
            border-radius: 0.65rem;
            z-index: 100;
            transition: top 180ms ease;
        }

        .skip-link:focus {
            top: 12px;
        }

        .glass-nav {
            backdrop-filter: blur(12px);
            background: rgba(255, 255, 255, 0.85);
            border-bottom: 1px solid rgba(226, 232, 240, 0.9);
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 0.85rem;
            background: linear-gradient(135deg, var(--color-primary), var(--color-primary-deep));
            color: #fff;
            font-weight: 700;
            text-decoration: none;
            border: 1px solid transparent;
            min-height: 46px;
            transition: transform 180ms ease, box-shadow 180ms ease, filter 180ms ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            filter: brightness(1.03);
            box-shadow: var(--shadow-soft);
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 0.85rem;
            background: #fff;
            color: var(--neutral-900);
            font-weight: 700;
            text-decoration: none;
            border: 1px solid var(--neutral-200);
            min-height: 46px;
            transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
        }

        .btn-secondary:hover {
            transform: translateY(-1px);
            border-color: #cbd5e1;
            box-shadow: var(--shadow-soft);
        }

        .hero-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            border-radius: 9999px;
            padding: 0.35rem 0.8rem;
            font-size: 0.86rem;
            font-weight: 600;
            color: #0369a1;
            background: var(--color-accent-soft);
            border: 1px solid #bae6fd;
        }

        .surface-card {
            background: var(--surface);
            border: 1px solid var(--neutral-200);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-soft);
        }

        .subtle-card {
            background: var(--surface-subtle);
            border: 1px solid #dbeafe;
            border-radius: 1rem;
        }

        .feature-icon {
            width: 2.8rem;
            height: 2.8rem;
            border-radius: 0.95rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #ecfeff;
            color: var(--color-primary);
        }

        .mockup-frame {
            position: relative;
            border-radius: 1.4rem;
            overflow: hidden;
            border: 1px solid #dbeafe;
            background: #fff;
            box-shadow: 0 28px 56px rgba(2, 132, 199, 0.18);
        }

        .pricing-recommended {
            border: 2px solid #38bdf8;
            position: relative;
            overflow: hidden;
        }

        .pricing-recommended::before {
            content: 'Plan recomendado';
            position: absolute;
            top: 0.8rem;
            right: 0.8rem;
            background: #0ea5e9;
            color: #fff;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            padding: 0.28rem 0.55rem;
            border-radius: 9999px;
        }

        .paypal-note {
            display: inline-flex;
            width: 100%;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            border-radius: 0.75rem;
            border: 1px dashed #7dd3fc;
            background: #f0f9ff;
            color: #075985;
            padding: 0.7rem 0.9rem;
            font-size: 0.84rem;
            font-weight: 600;
        }

        .faq-item {
            border-radius: 1rem;
            border: 1px solid var(--neutral-200);
            background: #fff;
            padding: 0.15rem 1rem;
        }

        .faq-item summary {
            list-style: none;
            cursor: pointer;
            font-weight: 700;
            padding: 1rem 0;
            color: var(--neutral-900);
        }

        .faq-item summary::-webkit-details-marker {
            display: none;
        }

        .faq-item p {
            color: var(--neutral-700);
            margin-top: 0;
            margin-bottom: 1rem;
        }

        .reveal-up {
            opacity: 0;
            transform: translateY(12px);
            animation: revealUp 620ms ease forwards;
        }

        @keyframes revealUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        a:focus-visible,
        button:focus-visible,
        summary:focus-visible {
            outline: none;
            box-shadow: var(--shadow-focus);
        }

        @media (max-width: 640px) {
            .btn-primary,
            .btn-secondary {
                width: 100%;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }
    </style>
</head>
<body class="antialiased">
    <a href="#main-content" class="skip-link">Saltar al contenido principal</a>

    <header class="glass-nav sticky top-0 z-50">
        <nav class="mx-auto flex w-full max-w-6xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-3" aria-label="Sanare inicio">
                <img src="{{ asset('images/logo.png') }}" alt="Logo de Sanare" class="h-11 w-11 rounded-xl border border-slate-200 bg-white p-1">
                <span>
                    <span class="block text-lg font-extrabold tracking-tight text-slate-900">Sanare</span>
                    <span class="block text-xs font-semibold uppercase tracking-[0.1em] text-slate-500">Gestion medica</span>
                </span>
            </a>
            <div class="flex items-center gap-2">
                <a href="{{ \App\Support\CentralUrl::route('clinica.registro', ['plan' => 'monthly']) }}" class="btn-primary px-4 py-2 text-sm sm:text-base">Registrar Clinica</a>
                <a href="{{ \App\Support\CentralUrl::route('filament.admin.auth.login') }}" class="btn-secondary px-4 py-2 text-sm sm:text-base">
                    <span data-lucide="log-in" class="h-4 w-4" aria-hidden="true"></span>
                    Iniciar Sesion
                </a>
            </div>
        </nav>
    </header>

    <main id="main-content">
        <section class="mx-auto w-full max-w-6xl px-4 pb-20 pt-14 sm:px-6 lg:px-8 lg:pt-16">
            <div class="grid items-center gap-12 lg:grid-cols-[1.05fr,0.95fr]">
                <div class="space-y-7">
                    

                    <div class="space-y-4">
                        <h1 class="reveal-up text-4xl font-extrabold leading-tight sm:text-5xl" style="animation-delay: 150ms;">
                            Gestiona tu clinica con orden clinico, velocidad operativa y trato humano.
                        </h1>
                        <p class="reveal-up max-w-2xl text-lg leading-relaxed text-slate-600 sm:text-xl" style="animation-delay: 210ms;">
                            Sanare unifica agenda, consultas, recetas y reportes para que tu equipo dedique menos tiempo a tareas manuales y mas tiempo al paciente.
                        </p>
                    </div>

                    <ul class="space-y-3 text-slate-700">
                        <li class="reveal-up flex items-start gap-3" style="animation-delay: 280ms;">
                            <span class="feature-icon h-9 w-9 rounded-lg">
                                <span data-lucide="calendar-days" class="h-5 w-5" aria-hidden="true"></span>
                            </span>
                            <span>Control de citas con agenda centralizada y vista diaria para recepcion y medicos.</span>
                        </li>
                        <li class="reveal-up flex items-start gap-3" style="animation-delay: 340ms;">
                            <span class="feature-icon h-9 w-9 rounded-lg">
                                <span data-lucide="file-text" class="h-5 w-5" aria-hidden="true"></span>
                            </span>
                            <span>Recetas digitales y registro clinico en un flujo claro para consultas mas rapidas.</span>
                        </li>
                        <li class="reveal-up flex items-start gap-3" style="animation-delay: 400ms;">
                            <span class="feature-icon h-9 w-9 rounded-lg">
                                <span data-lucide="bar-chart-3" class="h-5 w-5" aria-hidden="true"></span>
                            </span>
                            <span>Reportes utiles para medir citas atendidas, productividad medica y crecimiento mensual.</span>
                        </li>
                    </ul>

                </div>

                <div class="reveal-up lg:justify-self-end" style="animation-delay: 260ms;">
                    <div class="mockup-frame max-w-xl">
                        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3">
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full bg-sky-300"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-300"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-amber-300"></span>
                            </div>
                            <p class="text-sm font-semibold text-slate-600">Panel operativo Sanare</p>
                        </div>

                        <img
                            src="{{ asset('images/medical.jpg') }}"
                            alt="Vista de escritorio de una clinica trabajando con Sanare"
                            width="880"
                            height="500"
                            class="h-56 w-full object-cover sm:h-64"
                        >

                        <div class="grid grid-cols-3 gap-3 p-4">
                            <article class="subtle-card p-3">
                                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Hoy</p>
                                <p class="mt-1 text-2xl font-extrabold text-slate-900">38</p>
                                <p class="text-xs text-slate-600">Citas programadas</p>
                            </article>
                            <article class="subtle-card p-3">
                                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Tiempo medio</p>
                                <p class="mt-1 text-2xl font-extrabold text-slate-900">13m</p>
                                <p class="text-xs text-slate-600">Por consulta</p>
                            </article>
                            <article class="subtle-card p-3">
                                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Estado</p>
                                <p class="mt-1 text-2xl font-extrabold text-emerald-700">OK</p>
                                <p class="text-xs text-slate-600">Operacion estable</p>
                            </article>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto w-full max-w-6xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="mb-10 text-center">
                <p class="text-sm font-semibold uppercase tracking-[0.14em] text-sky-700">Como ayuda Sanare</p>
                <h2 class="mt-3 text-3xl font-extrabold sm:text-4xl">Menos friccion operativa, mas tiempo para atender bien.</h2>
            </div>

            <div class="grid gap-5 md:grid-cols-3">
                <article class="surface-card p-6">
                    <span class="feature-icon">
                        <span data-lucide="activity" class="h-5 w-5" aria-hidden="true"></span>
                    </span>
                    <h3 class="mt-4 text-xl font-bold">Flujo clinico continuo</h3>
                    <p class="mt-2 text-slate-600">Desde la recepcion hasta la receta, cada paso queda conectado para evitar dobles registros.</p>
                </article>
                <article class="surface-card p-6">
                    <span class="feature-icon">
                        <span data-lucide="users" class="h-5 w-5" aria-hidden="true"></span>
                    </span>
                    <h3 class="mt-4 text-xl font-bold">Trabajo en equipo claro</h3>
                    <p class="mt-2 text-slate-600">Recepcion, asistencia y medicos consultan la misma informacion en tiempo real.</p>
                </article>
                <article class="surface-card p-6">
                    <span class="feature-icon">
                        <span data-lucide="clipboard-list" class="h-5 w-5" aria-hidden="true"></span>
                    </span>
                    <h3 class="mt-4 text-xl font-bold">Decision basada en datos</h3>
                    <p class="mt-2 text-slate-600">Indicadores simples para detectar cuellos de botella y mejorar la atencion cada semana.</p>
                </article>
            </div>
        </section>

        <section id="modulos" class="mx-auto w-full max-w-6xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="mb-10 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.14em] text-emerald-700">Modulos principales</p>
                    <h2 class="mt-2 text-3xl font-extrabold sm:text-4xl">Todo lo esencial en un solo sistema.</h2>
                </div>
                <p class="max-w-lg text-slate-600">Diseño orientado a clinicas pequeñas y medianas que necesitan orden sin agregar complejidad.</p>
            </div>

            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                <article class="surface-card p-6">
                    <span data-lucide="calendar-days" class="h-6 w-6 text-emerald-700" aria-hidden="true"></span>
                    <h3 class="mt-3 text-xl font-bold">Agenda inteligente</h3>
                    <p class="mt-2 text-slate-600">Visualiza turnos, reasigna citas y reduce espacios vacios con recordatorios claros.</p>
                </article>
                <article class="surface-card p-6">
                    <span data-lucide="heart-pulse" class="h-6 w-6 text-emerald-700" aria-hidden="true"></span>
                    <h3 class="mt-3 text-xl font-bold">Consulta medica digital</h3>
                    <p class="mt-2 text-slate-600">Registra sintomas, diagnostico y tratamiento con un historial facil de consultar.</p>
                </article>
                <article class="surface-card p-6">
                    <span data-lucide="file-text" class="h-6 w-6 text-emerald-700" aria-hidden="true"></span>
                    <h3 class="mt-3 text-xl font-bold">Recetas profesionales</h3>
                    <p class="mt-2 text-slate-600">Genera recetas ordenadas y consistentes para mejorar la experiencia del paciente.</p>
                </article>
                <article class="surface-card p-6">
                    <span data-lucide="bar-chart-3" class="h-6 w-6 text-emerald-700" aria-hidden="true"></span>
                    <h3 class="mt-3 text-xl font-bold">Reportes accionables</h3>
                    <p class="mt-2 text-slate-600">Conoce citas atendidas, tiempos por medico y tendencias para tomar decisiones oportunas.</p>
                </article>
                <article class="surface-card p-6">
                    <span data-lucide="clock-3" class="h-6 w-6 text-emerald-700" aria-hidden="true"></span>
                    <h3 class="mt-3 text-xl font-bold">Operacion diaria agil</h3>
                    <p class="mt-2 text-slate-600">Menos tareas repetitivas, menos errores manuales y mas tiempo para atencion humana.</p>
                </article>
                <article class="surface-card p-6">
                    <span data-lucide="message-square" class="h-6 w-6 text-emerald-700" aria-hidden="true"></span>
                    <h3 class="mt-3 text-xl font-bold">Comunicacion interna</h3>
                    <p class="mt-2 text-slate-600">Comparte contexto clinico entre areas para que cada paciente reciba seguimiento continuo.</p>
                </article>
            </div>
        </section>

        <section id="planes" class="mx-auto w-full max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="mb-10 text-center">
                <p class="text-sm font-semibold uppercase tracking-[0.14em] text-sky-700">Planes</p>
                <h2 class="mt-3 text-3xl font-extrabold sm:text-4xl">Comienza facil y escala a tu ritmo.</h2>
                 </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <article class="surface-card pricing-recommended p-7">
                    <p class="text-sm font-semibold uppercase tracking-[0.12em] text-sky-700">Mensual</p>
                    <h3 class="mt-3 text-2xl font-bold">Plan mensual</h3>
                    <p class="mt-2 text-slate-600">Flexibilidad mes a mes. Ideal para clinicas en crecimiento.</p>
                    <p class="mt-6 text-4xl font-extrabold text-slate-900">$89.90 <span class="text-lg font-semibold text-slate-500">/mes</span></p>
                    <ul class="mt-6 space-y-3 text-slate-700">
                        <li class="flex items-start gap-2"><span data-lucide="check" class="mt-0.5 h-4 w-4 text-emerald-700"></span><span>Pacientes ilimitados</span></li>
                        <li class="flex items-start gap-2"><span data-lucide="check" class="mt-0.5 h-4 w-4 text-emerald-700"></span><span>Multiples medicos y recepcion</span></li>
                        <li class="flex items-start gap-2"><span data-lucide="check" class="mt-0.5 h-4 w-4 text-emerald-700"></span><span>Reportes operativos y recetas digitales</span></li>
                    </ul>
                    <a href="{{ \App\Support\CentralUrl::route('clinica.registro', ['plan' => 'monthly']) }}" class="btn-primary mt-12 w-full px-4 py-3 text-base">Seleccionar mensual</a>
                    <div class="paypal-note mt-3" aria-label="PayPal disponible">
                        <span data-lucide="credit-card" class="h-4 w-4"></span>
                        Pago recurrente con PayPal
                    </div>
                    <p class="mt-2 text-center text-xs font-semibold text-sky-700">Activa tu clinica luego de verificar correo y aprobar el pago.</p>
                </article>

                <article class="surface-card p-7">
                    <p class="text-sm font-semibold uppercase tracking-[0.12em] text-amber-700">Anual</p>
                    <h3 class="mt-3 text-2xl font-bold">Plan anual</h3>
                    <p class="mt-2 text-slate-600">Mejor valor. Ahorra 10% y despreocupa el ano.</p>
                    <p class="mt-6 text-4xl font-extrabold text-slate-900">$919 <span class="text-lg font-semibold text-slate-500">/año</span></p>
                    <p class="mt-1 text-sm font-semibold text-emerald-700">Equivale a $76.58/mes</p>
                    <ul class="mt-5 space-y-3 text-slate-700">
                        <li class="flex items-start gap-2"><span data-lucide="check" class="mt-0.5 h-4 w-4 text-emerald-700"></span><span>Todo lo del plan mensual</span></li>
                        <li class="flex items-start gap-2"><span data-lucide="check" class="mt-0.5 h-4 w-4 text-emerald-700"></span><span>2 meses incluidos sin costo adicional</span></li>
                        <li class="flex items-start gap-2"><span data-lucide="check" class="mt-0.5 h-4 w-4 text-emerald-700"></span><span>Prioridad en acompañamiento y soporte</span></li>
                    </ul>
                    <a href="{{ \App\Support\CentralUrl::route('clinica.registro', ['plan' => 'annual']) }}" class="btn-primary mt-7 w-full px-4 py-3 text-base">Seleccionar anual</a>
                    <div class="paypal-note mt-3" aria-label="PayPal disponible">
                        <span data-lucide="credit-card" class="h-4 w-4"></span>
                        Pago recurrente con PayPal
                    </div>
                    <p class="mt-2 text-center text-xs font-semibold text-sky-700">Mejor costo anual con renovacion automatica.</p>
                </article>
            </div>

        </section>

        <section id="seguridad" class="mx-auto w-full max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="mb-10 text-center">
                <p class="text-sm font-semibold uppercase tracking-[0.14em] text-emerald-700">Seguridad y privacidad</p>
                <h2 class="mt-3 text-3xl font-extrabold sm:text-4xl">Tu informacion clinica bajo controles claros.</h2>
            </div>

            <div class="grid gap-5 md:grid-cols-3">
                <article class="surface-card p-6">
                    <span data-lucide="lock" class="h-6 w-6 text-sky-700"></span>
                    <h3 class="mt-3 text-xl font-bold">Control de acceso por usuario</h3>
                    <p class="mt-2 text-slate-600">Cada perfil entra solo a lo que necesita para trabajar y reduce exposiciones innecesarias.</p>
                </article>
                <article class="surface-card p-6">
                    <span data-lucide="shield-check" class="h-6 w-6 text-sky-700"></span>
                    <h3 class="mt-3 text-xl font-bold">Proteccion en transito</h3>
                    <p class="mt-2 text-slate-600">Las conexiones usan canales seguros para proteger datos clinicos durante su envio.</p>
                </article>
                <article class="surface-card p-6">
                    <span data-lucide="building-2" class="h-6 w-6 text-sky-700"></span>
                    <h3 class="mt-3 text-xl font-bold">Operacion preparada para crecer</h3>
                    <p class="mt-2 text-slate-600">Arquitectura pensada para multiples clinicas con gestion central y orden operativo.</p>
                </article>
            </div>
        </section>

        <section id="faq" class="mx-auto w-full max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="mb-10 text-center">
                <p class="text-sm font-semibold uppercase tracking-[0.14em] text-sky-700">FAQ</p>
                <h2 class="mt-3 text-3xl font-extrabold sm:text-4xl">Preguntas frecuentes</h2>
            </div>

            <div class="space-y-3">
                <details class="faq-item" open>
                    <summary>Que tipo de clinicas pueden usar Sanare?</summary>
                    <p>Clinicas generales y especializadas que quieran ordenar agenda, consulta, receta y seguimiento sin depender de procesos manuales.</p>
                </details>
                <details class="faq-item">
                    <summary>Cuanto tarda la configuracion inicial?</summary>
                    <p>Normalmente minutos. El alta de clinica se hace desde registro y el acceso administrativo queda listo al completar datos base.</p>
                </details>
                <details class="faq-item">
                    <summary>Puedo empezar solo con un medico y luego ampliar?</summary>
                    <p>Si. Puedes iniciar con un equipo pequeno y agregar usuarios o areas a medida que aumente la operacion.</p>
                </details>
                <details class="faq-item">
                    <summary>La plataforma incluye recetas digitales?</summary>
                    <p>Si. Puedes crear recetas desde consulta y mantener trazabilidad en el historial del paciente.</p>
                </details>
                <details class="faq-item">
                    <summary>Ya se puede pagar con PayPal?</summary>
                    <p>Si. El flujo actual valida correo, envía al checkout de PayPal y activa la clinica al confirmar la suscripcion.</p>
                </details>
                <details class="faq-item">
                    <summary>Como ingreso si ya tengo cuenta?</summary>
                    <p>Usa el boton de acceso ubicado en el navbar para entrar al panel administrativo con tus credenciales.</p>
                </details>
            </div>
        </section>

        <section class="mx-auto w-full max-w-6xl px-4 pb-20 pt-6 sm:px-6 lg:px-8">
            <div class="surface-card overflow-hidden border-sky-100 bg-gradient-to-br from-cyan-50 via-white to-emerald-50 p-8 text-center sm:p-12">
                <p class="text-sm font-semibold uppercase tracking-[0.14em] text-sky-700">Listo para comenzar</p>
                <h2 class="mx-auto mt-3 max-w-3xl text-3xl font-extrabold sm:text-4xl">Dale a tu equipo una plataforma clara para atender mejor desde hoy.</h2>
                <p class="mx-auto mt-3 max-w-2xl text-lg text-slate-600">Empieza con plan mensual o anual y activa tu tenant con suscripcion segura en PayPal.</p>
                <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row">
                    <a href="{{ \App\Support\CentralUrl::route('clinica.registro', ['plan' => 'monthly']) }}" class="btn-primary px-6 py-3 text-base">
                        Registrar Clinica
                        <span data-lucide="arrow-right" class="h-4 w-4"></span>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-slate-950 py-14 text-slate-200">
        <div class="mx-auto grid w-full max-w-6xl gap-10 px-4 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8">
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo_BLANCO.png') }}" alt="Logo Sanare en blanco" class="h-11 w-11 rounded-lg border border-white/20 bg-white/10 p-1" loading="lazy">
                    <div>
                        <p class="text-lg font-bold text-white">Sanare</p>
                        <p class="text-xs uppercase tracking-[0.12em] text-slate-400">Software medico</p>
                    </div>
                </div>
                <p class="text-sm leading-relaxed text-slate-400">Tecnologia para clinicas que quieren ordenar su operacion y cuidar mejor la experiencia de cada paciente.</p>
            </div>

            <div>
                <p class="text-sm font-bold uppercase tracking-[0.14em] text-slate-300">Producto</p>
                <ul class="mt-4 space-y-2 text-sm text-slate-400">
                    <li><a href="#modulos" class="hover:text-white">Modulos</a></li>
                    <li><a href="#planes" class="hover:text-white">Planes</a></li>
                    <li><a href="#seguridad" class="hover:text-white">Seguridad</a></li>
                    <li><a href="#faq" class="hover:text-white">FAQ</a></li>
                </ul>
            </div>

            <div>
                
            </div>

            <div>
                <p class="text-sm font-bold uppercase tracking-[0.14em] text-slate-300">Contacto</p>
                <ul class="mt-4 space-y-3 text-sm text-slate-400">
                    <li class="inline-flex items-center gap-2">
                        <span data-lucide="mail" class="h-4 w-4"></span>
                        soporte@sanare.app
                    </li>
                    <li class="inline-flex items-center gap-2">
                        <span data-lucide="phone" class="h-4 w-4"></span>
                        +504 0000-0000
                    </li>
                    <li class="inline-flex items-center gap-2">
                        <span data-lucide="map-pin" class="h-4 w-4"></span>
                        Tegucigalpa, Honduras
                    </li>
                </ul>
            </div>
        </div>

        <div class="mx-auto mt-10 w-full max-w-6xl border-t border-white/10 px-4 pt-6 text-center text-sm text-slate-500 sm:px-6 lg:px-8">
            &copy; {{ date('Y') }} Sanare. Todos los derechos reservados.
        </div>
    </footer>
</body>
</html>
