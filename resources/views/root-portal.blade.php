<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal Root</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f3f4f6; padding: 24px; }
        .wrapper { max-width: 900px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 10px 24px rgba(0, 0, 0, .08); }
        h1 { margin: 0 0 18px; }
        .row { display: grid; grid-template-columns: 1fr auto; gap: 8px; align-items: center; border: 1px solid #e5e7eb; border-radius: 10px; padding: 12px; margin-bottom: 10px; }
        .meta { color: #6b7280; font-size: 13px; margin-top: 2px; }
        button { border: 0; background: #0f766e; color: #fff; padding: 10px 12px; border-radius: 8px; cursor: pointer; }
    </style>
</head>
<body>
<div class="wrapper">
    <h1>Portal Root</h1>
    <p>Selecciona un centro para entrar a su tenant.</p>

    @foreach($centros as $centro)
        <div class="row">
            <div>
                <div><strong>{{ $centro->nombre_centro }}</strong></div>
                <div class="meta">
                    Modo: {{ $centro->tenancy_mode ?? 'legacy' }} |
                    Slug: {{ $centro->slug ?? 'N/A' }}
                </div>
            </div>
            <form method="POST" action="{{ route('portal.root.enter-tenant', $centro) }}">
                @csrf
                <button type="submit">Entrar</button>
            </form>
        </div>
    @endforeach
</div>
</body>
</html>

