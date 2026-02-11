<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>503 - Servicio no disponible</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }
        .error-container {
            text-align: center;
            padding: 40px 20px;
            max-width: 500px;
        }
        .dog-illustration {
            width: 220px;
            height: 220px;
            margin: 0 auto 30px;
        }
        .error-code {
            font-size: 72px;
            font-weight: 800;
            color: #112762;
            line-height: 1;
            margin-bottom: 10px;
        }
        .error-title {
            font-size: 22px;
            font-weight: 600;
            color: #112762;
            margin-bottom: 12px;
        }
        .error-message {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        .btn-home {
            display: inline-block;
            padding: 12px 32px;
            background: #112762;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            transition: background 0.2s;
        }
        .btn-home:hover {
            background: #0C213A;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="dog-illustration">
            <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                <!-- Cuerpo echado -->
                <ellipse cx="100" cy="150" rx="60" ry="30" fill="#c8a87c"/>
                <!-- Cabeza -->
                <circle cx="100" cy="85" r="42" fill="#d4b896"/>
                <!-- Orejas caidas -->
                <ellipse cx="60" cy="65" rx="15" ry="25" fill="#a0785a" transform="rotate(-20 60 65)"/>
                <ellipse cx="140" cy="65" rx="15" ry="25" fill="#a0785a" transform="rotate(20 140 65)"/>
                <!-- Ojos cerrados (durmiendo) -->
                <path d="M 78 77 Q 85 72 92 77" stroke="#333" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                <path d="M 108 77 Q 115 72 122 77" stroke="#333" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                <!-- Nariz -->
                <ellipse cx="100" cy="92" rx="6" ry="4.5" fill="#333"/>
                <!-- Boca tranquila -->
                <path d="M 92 100 Q 100 104 108 100" stroke="#5a4a3a" stroke-width="2" fill="none" stroke-linecap="round"/>
                <!-- Patas estiradas -->
                <ellipse cx="60" cy="175" rx="14" ry="7" fill="#d4b896"/>
                <ellipse cx="140" cy="175" rx="14" ry="7" fill="#d4b896"/>
                <!-- Cola -->
                <path d="M 160 145 Q 178 130 172 115" stroke="#c8a87c" stroke-width="8" fill="none" stroke-linecap="round"/>
                <!-- Collar -->
                <path d="M 68 115 Q 100 130 132 115" stroke="#27ae60" stroke-width="4" fill="none" stroke-linecap="round"/>
                <circle cx="100" cy="123" r="4" fill="#f1c40f"/>
                <!-- Zzzs -->
                <text x="135" y="55" font-size="16" fill="#112762" font-weight="bold">Z</text>
                <text x="150" y="42" font-size="12" fill="#3656ac" font-weight="bold">z</text>
                <text x="160" y="32" font-size="9" fill="#6e8bc9" font-weight="bold">z</text>
            </svg>
        </div>
        <div class="error-code">503</div>
        <div class="error-title">Servicio no disponible</div>
        <div class="error-message">
            El sistema esta en mantenimiento. Vuelve a intentarlo en unos minutos.
        </div>
        <a href="{{ url('/') }}" class="btn-home">Volver al inicio</a>
    </div>
</body>
</html>
