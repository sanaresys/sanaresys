<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recordatorio de renovacion</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; background: #f9fafb; padding: 20px;">
<div style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px;">
    <h2 style="margin-top: 0;">Renovacion proxima de modulo</h2>
    <p>
        La clinica <strong>{{ $centro?->nombre_centro }}</strong> tiene el modulo
        <strong>{{ $module?->name ?? strtoupper($module?->code ?? 'N/A') }}</strong>
        con vencimiento en <strong>{{ $daysBeforeExpiry }} dia(s)</strong>.
    </p>

    <p>
        Fecha de vencimiento: <strong>{{ $renewsAt?->timezone(config('billing.module_billing.schedule_timezone', 'America/Tegucigalpa'))->format('d/m/Y H:i') }}</strong>
    </p>

    <p style="margin-top: 18px;">
        Ingresa al tenant y renueva el modulo desde la seccion de billing para evitar interrupciones.
    </p>
</div>
</body>
</html>

