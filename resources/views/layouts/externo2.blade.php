<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MAC - PCM')</title>
    
    <!-- Estilos -->
    <link rel="stylesheet" href="{{ asset('externo/Content/fontawesome-all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('externo/Content/Menu/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
    <!-- <link rel="stylesheet" href="{{ asset('externo/Content/SiteLogin.css') }}"> -->
    <link rel="stylesheet" href="{{ asset('externo/Content/bootstrap4.css') }}">
    <!-- <link rel="stylesheet" href="{{ asset('externo/Content/libro-reclamaciones.css') }}"> -->
    <link rel="stylesheet" href="{{ asset('externo/Content/lreclamaciones.css') }}">
    <link rel="stylesheet" href="{{ asset('externo/Content/fuentes/fuentes-personalizadas.css') }}">
    <link rel="stylesheet" href="{{ asset('externo/Content/css/jquery-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('externo/Content/Menu/bower_components/Ionicons/css/ionicons.min.css') }}">
    
    <!-- Scripts -->
    <script src="{{ asset('externo/Scripts/Master/jquery-3.4.1.js') }}"></script>
    <script src="{{ asset('externo/Scripts/Master/bootstrap.min.js') }}"></script>
    <script src="{{ asset('externo/Scripts/site.js') }}"></script>
    <script src="{{ asset('externo/Scripts/jquery.filter_input.js') }}"></script>
    <script src="{{ asset('externo/Scripts/Table/bootbox.min.js') }}"></script>
    <script src="{{ asset('externo/Scripts/Chart.js') }}"></script>
    <script src="{{ asset('externo/Scripts/jquery-ui-1.12..js') }}"></script>
    
    @yield('head')
</head>
<body>
    <header>
        <div class="header__content container form-group">
            <div class="logo col-12 col-lg-8 center-block" style="height:50px; padding-top:7px;">
                <img src="{{ asset('externo/Content/svg/escudo-svg.svg') }}" class="logo__escudo" alt="Escudo Peruano" style="float:left; margin-right:7px;">
                <img src="{{ asset('externo/Content/svg/logo-gobpe.svg') }}" class="logo__gobpe" alt="gob.pe" style="float:left; padding-top:12px;">
                <div class="logo-texto" style="float:left; color:white; border-left:1px solid; margin:5px 10px 10px 15px; padding-left:15px; height:95%;">
                    {{-- Mejor Atención al Ciudadano<br />MAC EXPRESS --}}
                    @yield('title-head')
                </div>
            </div>
            <div class="wrap-ingresar col-12 col-lg-4 px-0">
                <nav class="navbar navbar-expand-lg" role="navigation">
                    <div class="container px-0">
                        <div class="collapse navbar-collapse px-0" id="exCollapsingNavbar">
                            <ul class="nav navbar-nav flex-row justify-content-between ml-auto float-right">
                                <li class="nav-item dropdown show pt-3">
                                    <a class="nav-link text-white p-0" data-toggle="dropdown" href="#" aria-expanded="true">
                                        <i class="fas fa-user"></i> dsadadad <i class="fas fa-caret-down"></i>
                                    </a>
                                    {{-- <div class="dropdown-menu dropdown-menu-right p-4" style="width: 300px!important;">
                                        <div class="media-body text-center">
                                            <p class="font-weight-bold">{{ session('nombreUsuario') }}</p>
                                            <p>{{ session('nombreEntidad') }}</p>
                                            <!-- <p>Area</p> -->
                                            <p>{{ session('nombreRol') }}</p>
                                        </div>
                                        <div class="dropdown-divider"></div>
                                        <div class="d-flex justify-content-between">
                                            <!-- <button class="btn btn-secondary btn-modal" data-toggle="modal" data-target="#modalClave">Cambiar clave</button> -->
                                            <a class="btn btn-secondary btn-modal" id="btnCambiarClave" href="#">
                                                <span class="glyphicon glyphicon-off"></span> Cambiar clave
                                            </a>
                                            <!-- <button class="btn btn-rojo">Salir</button> -->
                                            <a class="btn btn-rojo" href="{{ action('LoginController@index') }}">
                                                <span class="glyphicon glyphicon-off"></span> Salir
                                            </a>
                                        </div>
                                    </div> --}}
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <section class="main-content">
        <div class="content-wrapper">
            <div class="container">
                
                @yield('externom')
            </div>
        </div>
    </section>

    <footer class="text-left">
        <div class="container info-footer">
            <div class="row mt-2">
                <div class="col-sm-12 col-md-4 mb-4">
                    <a href="https://www.gob.pe/" target="_blank">SOBRE EL ESTADO PERUANO</a>
                </div>
                <div class="col-sm-12 col-md-4">
                    <h6>SÍGUENOS</h6>
                    <ul class="d-flex mb-0">
                        <li class="mr-2"><a href="https://es-la.facebook.com/PCMPERU/" target="_blank"><i class="fab fa-facebook-square footer__icons"></i></a></li>
                        <li class="mr-2"><a href="https://twitter.com/pcmperu" target="_blank"><i class="fab fa-twitter-square footer__icons"></i></a></li>
                        <li class="mr-2"><a href="https://www.instagram.com/pcmperu" target="_blank"><i class="fab fa-instagram footer__icons"></i></a></li>
                        <li class="mr-2"><a href="https://www.youtube.com/user/Audiovisualespcm" target="_blank"><i class="fab fa-youtube-square footer__icons"></i></a></li>
                        <li><a href="https://www.flickr.com/photos/prensapcm" target="_blank"><i class="fab fa-flickr footer__icons"></i></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    @yield('Scripts')
</body>
</html>
