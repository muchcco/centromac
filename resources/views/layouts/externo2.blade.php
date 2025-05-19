<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MAC - PCM')</title>

    <link rel="stylesheet" href="{{ asset('externo/kevin/style.css') }}">
    <link href="{{ asset('Vendor\plugins\font-awesome\css\font-awesome.min.css')}}" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>

    
    @yield('ext-styles')
</head>
<body>
    <header class="app-header">
        <div class="head-primary">
            <div class="brand">
            <img class="h-logo-img" src="{{ asset('externo/images/logo_escudo.svg')}}" alt="SRAI" />
            <img class="h-logo-name" src="{{ asset('externo/images/logo_gobpe.svg')}}" alt="SRAI" />
            <span class="div-title"></span>
            <span>
                @yield('title')
            </span>
            </div>
        
            <div class="user-actions">
            </div>
        </div>
    </header>



    <main class="layout-content">
    <!-- Este es el router-outlet *interno* donde cargarán Home, Masters, etc -->
    @yield('externom')
    </main>

    <footer class="app-footer">
        <div class="text-start"><p>© 2025 Subsecretaria de Calidad de Servicio SCSC – SGP <br />Presidencia del Consejo de Ministros</p></div>
        <div class="foot-comp">Compatible con: Google Chrome <img src="{{ asset('externo/images/chrome.png')}}" alt="CHROME" width="24" class="px-2">   Mozilla Firefox <img src="{{ asset('externo/images/firefox.png')}}" alt="CHROME" width="24" class="px-2"></div>
        <div>v1.0.0</div>
    </footer>
    

    <!-- jQuery  -->
    <script src="{{asset('nuevo/assets/js/jquery.min.js')}}"></script>
    @yield('ext-script')
</body>
</html>
