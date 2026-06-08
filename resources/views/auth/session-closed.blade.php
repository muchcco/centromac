<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intranet CENTROS MAC</title>
    <link href="{{ asset('css/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <script>
        function getRandomImage() {
            var images = ['anden.jpg', 'arequipa.jpg', 'lima-barranco.jpg'];
            return images[Math.floor(Math.random() * images.length)];
        }
        var randomImageUrl = '{{ asset('imagen/auth/') }}/' + getRandomImage();
        document.documentElement.style.setProperty('--random-image', 'url(' + randomImageUrl + ')');
    </script>
    <script src="{{ asset('js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <style>
        #bod_comp {
            position: relative;
            width: 100%;
            height: 100vh;
            overflow: hidden;
        }
        #bod_comp::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-image: var(--random-image);
            z-index: -1;
        }
        #body {
            font-family: 'Arial', sans-serif;
            margin: 0; padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            width: 100%;
            background-color: rgba(0, 0, 0, 0.55);
            z-index: 1;
        }
        .card-session {
            background-color: rgba(255, 255, 255, 0.95);
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
            padding: 40px 36px;
            border-radius: 10px;
            width: 380px;
            text-align: center;
            animation: slide-up 0.4s ease;
        }
        @keyframes slide-up {
            from { transform: translateY(40px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }
        .img-login {
            border-bottom: 1px solid rgb(70,70,70);
            padding-bottom: 1.2em;
            margin-bottom: 1.5em;
            display: flex;
            justify-content: center;
        }
        .icon-lock {
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 12px;
        }
        .btn-auth {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 15px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s ease;
            margin-top: 18px;
        }
        .btn-auth:hover {
            background-color: #0056b3;
            color: #fff;
        }
        .msg-reason {
            font-size: 13px;
            color: #888;
            margin-top: 10px;
        }
    </style>
</head>
<body id="bod_comp">
    <div id="body">
        <div class="card-session">
            <div class="img-login">
                <img src="{{ asset('imagen/PCM-PCM.png') }}" width="200">
            </div>

            <div class="icon-lock">&#128274;</div>

            <h5 class="fw-semibold text-dark mb-1">Sesión cerrada</h5>
            <p class="text-muted mb-0" style="font-size:14px;">
                @if($reason ?? null)
                    {{ $reason }}
                @else
                    Tu sesión ha finalizado. Para continuar, inicia sesión nuevamente desde el Auth-Server.
                @endif
            </p>

            <a href="{{ $loginUrl }}" class="btn-auth">
                Iniciar sesión
            </a>

            <p class="msg-reason">
                Serás dirigido al servidor de autenticación.
            </p>
        </div>
    </div>
</body>
</html>
