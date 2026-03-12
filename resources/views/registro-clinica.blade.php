<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro de Clinica</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f7fb; margin: 0; padding: 24px; }
        .card { max-width: 760px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 10px 25px rgba(0, 0, 0, .08); }
        h1 { margin-top: 0; font-size: 24px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .full { grid-column: 1 / -1; }
        label { display: block; font-size: 14px; margin-bottom: 6px; color: #1f2937; }
        input, select { width: 100%; box-sizing: border-box; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; }
        .error { color: #b91c1c; font-size: 13px; margin-top: 4px; }
        .btn { border: 0; background: #0f766e; color: white; padding: 12px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .helper { color: #4b5563; font-size: 13px; margin-bottom: 16px; }
        .btn-cancel { display: inline-block; text-decoration: none; background: #dc2626; color: #fff; padding: 12px 16px; border-radius: 8px; font-weight: 600; font-size: 14px; transition: background 0.2s; }
        .btn-cancel:hover { background: #b91c1c; }
        .plan { background: #ecfeff; border: 1px solid #99f6e4; border-radius: 10px; padding: 12px; margin: 12px 0 18px; }
        .plan strong { color: #115e59; }
        @media (max-width: 768px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="card">
    <h1>Registro de Clinica</h1>
    <p class="helper">
        Flujo: <strong>registro -> verificacion de correo -> pago PayPal -> activacion automatica.</strong>
    </p>

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
                <input id="rtn" name="rtn" value="{{ old('rtn') }}" required>
                @error('rtn') <div class="error">{{ $message }}</div> @enderror
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

        <div style="margin-top: 16px; display: flex; gap: 12px; align-items: center;">
            <button class="btn" type="submit">Continuar a verificacion</button>
            <a href="{{ url('/') }}" class="btn-cancel">Cancelar</a>
        </div>
    </form>
</div>
</body>
</html>
