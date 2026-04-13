<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro de Clinica</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-1: #eff4ff;
            --bg-2: #f7fafc;
            --surface: #ffffff;
            --line: #d6deea;
            --line-strong: #b8c5d8;
            --text: #0f172a;
            --muted: #475569;
            --primary: #0f766e;
            --primary-soft: #e6fffb;
            --warn: #b45309;
            --warn-soft: #fff7ed;
            --danger: #b91c1c;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 26px 20px;
            font-family: "Manrope", "Segoe UI", sans-serif;
            color: var(--text);
            background:
                radial-gradient(1300px 400px at 10% -10%, #d9ecff 0%, transparent 55%),
                linear-gradient(180deg, var(--bg-1), var(--bg-2));
        }

        .page-shell {
            max-width: 880px;
            margin: 0 auto;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 26px 40px -28px rgba(15, 23, 42, 0.35);
        }

        .card-head {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 20px;
        }

        .eyebrow {
            margin: 0 0 4px 0;
            font-size: 11px;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #0f766e;
            font-weight: 700;
        }

        h1 {
            margin: 0;
            font-size: 30px;
            line-height: 1.1;
        }

        .helper {
            margin: 10px 0 0 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.55;
        }

        .flow-pill {
            border: 1px solid var(--line);
            background: #f8fbff;
            border-radius: 14px;
            padding: 10px 12px;
            max-width: 270px;
            font-size: 12px;
            line-height: 1.45;
            color: #334155;
            align-self: flex-start;
        }

        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .full { grid-column: 1 / -1; }

        label {
            display: block;
            font-size: 13px;
            margin-bottom: 6px;
            color: #1e293b;
            font-weight: 700;
        }

        input, select {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 11px 13px;
            font-size: 14px;
            font-family: inherit;
            color: #0f172a;
            background: #fff;
            transition: border-color 0.16s ease, box-shadow 0.16s ease;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #14b8a6;
            box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.14);
        }

        .error {
            color: var(--danger);
            font-size: 12px;
            margin-top: 5px;
            font-weight: 600;
        }

        .slug-preview {
            margin-top: 10px;
            border: 1px solid #c9d8f4;
            background: #f6f9ff;
            border-radius: 14px;
            padding: 12px 14px;
        }

        .slug-preview.is-warning {
            border-color: #fdba74;
            background: var(--warn-soft);
        }

        .slug-title {
            margin: 0 0 6px 0;
            font-size: 12px;
            color: #334155;
            font-weight: 700;
        }

        .slug-line {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 4px;
            font-size: 14px;
            line-height: 1.45;
            color: #1e293b;
        }

        .slug-scheme { color: #64748b; }
        .slug-domain { font-weight: 800; color: #0f172a; }
        .slug-route { color: #0f766e; font-weight: 700; }

        .slug-note {
            margin: 6px 0 0 0;
            font-size: 12px;
            color: #475569;
        }

        .slug-preview.is-warning .slug-note {
            color: var(--warn);
            font-weight: 600;
        }

        .actions {
            margin-top: 18px;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn {
            border: 0;
            background: linear-gradient(135deg, #0f766e, #0e7490);
            color: #fff;
            padding: 12px 16px;
            border-radius: 11px;
            cursor: pointer;
            font-weight: 700;
            font-family: inherit;
            font-size: 14px;
            box-shadow: 0 12px 20px -14px rgba(14, 116, 144, 0.75);
        }

        .btn:hover { filter: brightness(1.05); }

        .btn-cancel {
            display: inline-block;
            text-decoration: none;
            border: 1px solid var(--line-strong);
            color: #334155;
            padding: 11px 15px;
            border-radius: 11px;
            font-weight: 700;
            font-size: 14px;
            background: #fff;
            transition: background 0.16s ease, border-color 0.16s ease;
        }

        .btn-cancel:hover {
            background: #f8fafc;
            border-color: #94a3b8;
        }

        @media (max-width: 880px) {
            .card-head { flex-direction: column; }
            .flow-pill { max-width: none; width: 100%; }
        }

        @media (max-width: 768px) {
            body { padding: 14px; }
            .card { padding: 18px; border-radius: 16px; }
            .grid { grid-template-columns: 1fr; gap: 12px; }
            h1 { font-size: 25px; }
            .actions { flex-direction: column; align-items: stretch; }
            .btn, .btn-cancel { text-align: center; width: 100%; }
        }
    </style>
</head>
<body>
<main class="page-shell">
<div class="card">
    <div class="card-head">
        <div>
            <p class="eyebrow">Onboarding de Clinica</p>
            <h1>Registro de Clinica</h1>
            <p class="helper">
                Completa los datos para iniciar tu alta. Te mostraremos desde ahora como quedaria la direccion de acceso de tu clinica.
            </p>
        </div>
        <div class="flow-pill">
            <strong>Flujo:</strong> registro -> verificacion por correo -> 30 dias gratis -> pago al vencimiento -> renovacion.
        </div>
    </div>

    <form method="POST" action="{{ route('clinica.registro.store') }}">
        @csrf
        <input type="hidden" name="plan_code" value="{{ old('plan_code', $selectedPlanCode) }}">

        <div class="grid">
            <div class="full">
                <label for="plan_code_view">Plan de suscripcion</label>
                <select id="plan_code_view" onchange="document.querySelector('input[name=plan_code]').value = this.value;">
                    @foreach($plans as $code => $plan)
                        <option value="{{ $code }}" {{ old('plan_code', $selectedPlanCode) === $code ? 'selected' : '' }}>
                            {{ $plan['name'] ?? strtoupper($code) }} - USD {{ number_format((float) ($plan['price'] ?? 0), 2) }}
                        </option>
                    @endforeach
                </select>
                @error('plan_code') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="full">
                <label for="nombre_centro">Nombre de la clinica</label>
                <input id="nombre_centro" name="nombre_centro" value="{{ old('nombre_centro') }}" required>
                @error('nombre_centro') <div class="error">{{ $message }}</div> @enderror
                <div id="slug_preview_box" class="slug-preview">
                    <p class="slug-title">Previsualizacion de ruta</p>
                    <div class="slug-line">
                        <span class="slug-scheme">{{ strtolower((string) config('tenancy.tenant_scheme', 'https')) }}://</span>
                        <span class="slug-domain"><span id="slug_preview_value">tu-clinica</span>.<span id="slug_preview_base">{{ strtolower((string) config('tenancy.base_domain', 'sanaresys.com')) }}</span></span>
                   
                    </div>
                    <p id="slug_preview_note" class="slug-note">La disponibilidad final del slug se valida automaticamente al continuar.</p>
                </div>
            </div>

            <div class="full">
                <label for="direccion">Direccion</label>
                <input id="direccion" name="direccion" value="{{ old('direccion') }}" required>
                @error('direccion') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div>
                <label for="telefono">Telefono</label>
                <input id="telefono" name="telefono" value="{{ old('telefono') }}" required>
                @error('telefono') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div>
                <label for="rtn">RTN</label>
                <input id="rtn" name="rtn" value="{{ old('rtn') }}">
                @error('rtn') <div class="error">{{ $message }}</div> @enderror
                <p class="slug-note" style="margin-top:6px;">Opcional, puedes agregarlo mas adelante en la configuracion inicial.</p>
            </div>

            <div class="full">
                <label for="owner_name">Nombre del administrador</label>
                <input id="owner_name" name="owner_name" value="{{ old('owner_name') }}" required>
                @error('owner_name') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="full">
                <label for="owner_email">Correo del administrador</label>
                <input id="owner_email" type="email" name="owner_email" value="{{ old('owner_email') }}" required>
                @error('owner_email') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div>
                <label for="password">Contrasena</label>
                <input id="password" type="password" name="password" required>
                @error('password') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div>
                <label for="password_confirmation">Confirmar contrasena</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required>
            </div>
        </div>

        <div class="actions">
            <button class="btn" type="submit">Continuar a verificacion</button>
            <a href="{{ url('/') }}" class="btn-cancel">Cancelar</a>
        </div>
    </form>
</div>
</main>
<script>
    (function () {
        const clinicNameInput = document.getElementById('nombre_centro');
        const slugPreview = document.getElementById('slug_preview_value');
        const slugPreviewBox = document.getElementById('slug_preview_box');
        const slugNote = document.getElementById('slug_preview_note');
        const hiddenPlanInput = document.querySelector('input[name="plan_code"]');
        const planSelect = document.getElementById('plan_code_view');
        const fallbackSlug = 'tu-clinica';
        const reservedSlugs = ['www', 'admin', 'api', 'app', 'mail', 'ftp', 'smtp', 'sanaresys'];

        function slugify(value) {
            const base = String(value || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '')
                .replace(/-{2,}/g, '-');

            return base.slice(0, 63).replace(/-+$/g, '');
        }

        function syncPlan() {
            if (hiddenPlanInput && planSelect) {
                hiddenPlanInput.value = planSelect.value;
            }
        }

        function updateSlugPreview() {
            const slug = slugify(clinicNameInput ? clinicNameInput.value : '');
            const finalSlug = slug || fallbackSlug;
            slugPreview.textContent = finalSlug;

            if (slug && reservedSlugs.includes(slug)) {
                slugPreviewBox.classList.add('is-warning');
                slugNote.textContent = 'Este slug esta reservado. Te recomendamos cambiar el nombre para evitar rechazo al continuar.';
                return;
            }

            slugPreviewBox.classList.remove('is-warning');
            slugNote.textContent = slug
                ? 'Asi se veria tu acceso principal. Se confirmara disponibilidad exacta al enviar.'
                : 'Escribe el nombre de la clinica para generar una previsualizacion del slug.';
        }

        if (planSelect) {
            planSelect.addEventListener('change', syncPlan);
            syncPlan();
        }

        if (clinicNameInput) {
            clinicNameInput.addEventListener('input', updateSlugPreview);
            clinicNameInput.addEventListener('blur', updateSlugPreview);
            updateSlugPreview();
        }
    })();
</script>
</body>
</html>
