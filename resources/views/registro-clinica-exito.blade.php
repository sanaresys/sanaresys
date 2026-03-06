<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>¡Clínica Registrada!</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            margin: 0;
            padding: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .card {
            max-width: 560px;
            width: 100%;
            background: #fff;
            border-radius: 16px;
            padding: 40px 32px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .08);
            text-align: center;
        }
        .icon-success {
            width: 80px;
            height: 80px;
            background: #d1fae5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }
        .icon-success i {
            font-size: 36px;
            color: #059669;
        }
        h1 {
            font-size: 26px;
            color: #111827;
            margin: 0 0 8px;
        }
        .subtitle {
            color: #6b7280;
            font-size: 15px;
            margin-bottom: 28px;
        }
        .domain-box {
            background: #f0fdf4;
            border: 2px solid #86efac;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 28px;
        }
        .domain-label {
            font-size: 13px;
            color: #374151;
            font-weight: 600;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .domain-url {
            font-size: 20px;
            font-weight: 700;
            color: #065f46;
            word-break: break-all;
            margin-bottom: 12px;
        }
        .copy-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #059669;
            color: white;
            border: 0;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: background 0.2s;
        }
        .copy-btn:hover {
            background: #047857;
        }
        .copy-btn.copied {
            background: #6b7280;
        }
        .info-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 28px;
            text-align: left;
        }
        .info-box p {
            margin: 0 0 6px;
            font-size: 14px;
            color: #1e40af;
        }
        .info-box p:last-child {
            margin-bottom: 0;
        }
        .info-box i {
            margin-right: 6px;
            color: #3b82f6;
        }
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #0f766e, #059669);
            color: white;
            border: 0;
            padding: 14px 28px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s;
            box-shadow: 0 4px 14px rgba(5, 150, 105, 0.3);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(5, 150, 105, 0.4);
        }
        .countdown {
            color: #6b7280;
            font-size: 13px;
            margin-top: 16px;
        }
    </style>
</head>
<body>
<div class="card">
    <div class="icon-success">
        <i class="fas fa-check"></i>
    </div>

    <h1>¡Clínica Creada Exitosamente!</h1>
    <p class="subtitle">
        <strong>{{ $clinic }}</strong> ya está lista. Tu subdominio y base de datos han sido configurados.
    </p>

    <div class="domain-box">
        <div class="domain-label">Tu dominio exclusivo</div>
        <div class="domain-url" id="domainUrl">{{ $domain }}</div>
        <button class="copy-btn" id="copyBtn" onclick="copyDomain()">
            <i class="fas fa-copy"></i>
            <span id="copyText">Copiar dominio</span>
        </button>
    </div>

    <div class="info-box">
        <p><i class="fas fa-bookmark"></i> <strong>Guarda este dominio</strong> — lo necesitarás para acceder siempre a tu clínica.</p>
        <p><i class="fas fa-envelope"></i> También recibirás esta información en tu correo electrónico.</p>
        <p><i class="fas fa-lock"></i> Usa las credenciales que registraste para iniciar sesión.</p>
    </div>

    <a href="{{ $redirect }}" class="btn-primary" id="enterBtn">
        <i class="fas fa-arrow-right"></i>
        Entrar a mi Clínica
    </a>

    <p class="countdown">Serás redirigido automáticamente en <strong id="timer">15</strong> segundos</p>
</div>

<script>
    function copyDomain() {
        const domain = document.getElementById('domainUrl').textContent.trim();
        navigator.clipboard.writeText(domain).then(function() {
            const btn = document.getElementById('copyBtn');
            const text = document.getElementById('copyText');
            btn.classList.add('copied');
            text.textContent = '¡Copiado!';
            setTimeout(function() {
                btn.classList.remove('copied');
                text.textContent = 'Copiar dominio';
            }, 2000);
        });
    }

    // Auto-redirect countdown
    let seconds = 15;
    const timerEl = document.getElementById('timer');
    const redirectUrl = document.getElementById('enterBtn').href;

    const interval = setInterval(function() {
        seconds--;
        timerEl.textContent = seconds;
        if (seconds <= 0) {
            clearInterval(interval);
            window.location.href = redirectUrl;
        }
    }, 1000);
</script>
</body>
</html>
