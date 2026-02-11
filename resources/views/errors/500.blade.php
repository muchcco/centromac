<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Error del servidor</title>
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
                <!-- Cuerpo -->
                <ellipse cx="100" cy="140" rx="55" ry="40" fill="#c8a87c"/>
                <!-- Cabeza -->
                <circle cx="100" cy="80" r="42" fill="#d4b896"/>
                <!-- Orejas caidas -->
                <ellipse cx="58" cy="62" rx="14" ry="26" fill="#a0785a" transform="rotate(-25 58 62)"/>
                <ellipse cx="142" cy="62" rx="14" ry="26" fill="#a0785a" transform="rotate(25 142 62)"/>
                <!-- Ojos con X (mareado) -->
                <g stroke="#333" stroke-width="3" stroke-linecap="round">
                    <line x1="80" y1="70" x2="90" y2="80"/>
                    <line x1="90" y1="70" x2="80" y2="80"/>
                    <line x1="110" y1="70" x2="120" y2="80"/>
                    <line x1="120" y1="70" x2="110" y2="80"/>
                </g>
                <!-- Nariz -->
                <ellipse cx="100" cy="92" rx="6" ry="4.5" fill="#333"/>
                <!-- Lengua afuera -->
                <path d="M 96 100 Q 100 112 104 100" fill="#e74c3c" stroke="#c0392b" stroke-width="1"/>
                <!-- Patas delanteras -->
                <ellipse cx="75" cy="175" rx="12" ry="8" fill="#d4b896"/>
                <ellipse cx="125" cy="175" rx="12" ry="8" fill="#d4b896"/>
                <!-- Cola caida -->
                <path d="M 155 140 Q 170 150 165 165" stroke="#c8a87c" stroke-width="8" fill="none" stroke-linecap="round"/>
                <!-- Collar -->
                <path d="M 68 110 Q 100 125 132 110" stroke="#3498db" stroke-width="4" fill="none" stroke-linecap="round"/>
                <circle cx="100" cy="118" r="4" fill="#f1c40f"/>
                <!-- Estrellas de mareo -->
                <text x="60" y="45" font-size="14" fill="#f39c12">&#9733;</text>
                <text x="130" y="42" font-size="10" fill="#f39c12">&#9733;</text>
                <text x="145" y="55" font-size="12" fill="#f39c12">&#9733;</text>
            </svg>
        </div>
        <div class="error-code">500</div>
        <div class="error-title">Error interno del servidor</div>
        <div class="error-message">
            Algo salio mal en nuestro servidor. Estamos trabajando para solucionarlo.
        </div>
        <a href="{{ url('/') }}" class="btn-home">Volver al inicio</a>
    </div>
</body>
</html>
