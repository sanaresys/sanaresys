<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifica tu correo</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f5f7fb; margin:0; padding:24px;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px; margin:0 auto; background:#ffffff; border-radius:12px; border:1px solid #e5e7eb;">
        <tr>
            <td style="padding:24px;">
                <h1 style="margin:0 0 12px; font-size:22px; color:#111827;">Verifica tu correo para continuar</h1>
                <p style="margin:0 0 10px; color:#374151;">
                    Hola {{ $ownerName }}, recibimos una solicitud para crear la clinica <strong>{{ $clinicName }}</strong> en Sanaresys.
                </p>
                <p style="margin:0 0 20px; color:#374151;">
                    Para continuar con la creacion del subdominio y base de datos, confirma tu correo usando el siguiente boton.
                </p>

                <p style="margin:0 0 20px;">
                    <a href="{{ $verificationUrl }}" style="display:inline-block; background:#0f766e; color:#ffffff; text-decoration:none; padding:12px 18px; border-radius:8px; font-weight:700;">
                        Verificar correo y continuar
                    </a>
                </p>

                <p style="margin:0 0 8px; color:#6b7280; font-size:13px;">
                    Este enlace vence el {{ optional($expiresAt)->format('d/m/Y H:i') }}.
                </p>
                <p style="margin:0; color:#6b7280; font-size:13px;">
                    Si no solicitaste este registro, puedes ignorar este correo.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>

