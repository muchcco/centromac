{{-- <div id="sidebar-collapse">
    <div class="admin-block d-flex">
        <div>
            <img src="{{ asset('assets/img/admin-avatar.png')}}" width="45px" />
        </div>
        <div class="admin-info">
            <div class="font-strong">{{ auth()->user()->name }}</div><small> 
                @php
                    $user = App\Models\User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->first();

                    echo 'Centro MAC - '.$user->NOMBRE_MAC;
                @endphp
             </small></div>
    </div>
    <ul class="side-menu metismenu">
        <li>
            <a class="@if (Request::is('/')) active @endif" href="{{ route('inicio') }}"><i class="sidebar-item-icon fa fa-th-large"></i>
                <span class="nav-label">Panel de Inicio</span>
            </a>
        </li>
        <li class="heading">MODULO</li>
        <li>
            <a class="@if (Request::is('asistencia/asistencia')) active @endif"  href="{{ route('asistencia.asistencia') }}"><i class="sidebar-item-icon fa fa-th-large"></i>
                <span class="nav-label">Asistencia</span>
            </a>
        </li>
        <li class="@if (Request::is('personal*')) active @endif">
            <a href="javascript:;"><i class="sidebar-item-icon fa fa-bookmark"></i>
                <span class="nav-label">Personal</span><i class="fa fa-angle-left arrow"></i></a>
            <ul class="nav-2-level collapse">
                <li>
                    <a class="@if (Request::is('personal/asesores*')) active @endif" href="{{route('personal.asesores')}}">Asesores</a>
                </li>
                <li>
                    <a href="typography.html">PCM</a>
                </li>
            </ul>
        </li>
        <li class="heading">ADMINISTRADOR</li>
        <li>
            <li>
                <a class="@if (Request::is('usuarios*')) active @endif" href="{{ route('usuarios.index') }}"><i class="sidebar-item-icon fa fa-users"></i>
                    <span class="nav-label">Usuarios</span>
                </a>
            </li>
        </li>
    </ul>
</div> --}}

<!--             ************************************************************     -->

<style type="text/css">
    .left-sidenav .brand {
        text-align: center;
        height: auto;
        padding-top: 5px;
    }
</style>
<!-- Left Sidenav -->
    <div class="left-sidenav">
        <!-- LOGO -->
        <div class="brand">
            <a href="{{route('inicio')}}" class="logo">
                <span>
                    <img src="{{ asset('imagen/logo-pcm.png') }}" alt="PCM Logo" class="logo-lg logo-light" style="width: 80%;height: 5%;">
                    <img src="{{ asset('imagen/logo-pcm.png') }}" alt="PCM Logo" class="logo-lg logo-dark" style="width: 80%;height: 5%;">
                </span>
            </a>
        </div>
        <!--end logo-->
        <div class="update-msg text-center" style="margin: 16px 16px 16px;">
            <div class="pad-btm mb-1">
                <!--<img class="img-circle img-md" src="{{ asset('Img\profile-photos\1.png')}}" alt="Profile Picture">-->
                    <div class="avatar-box thumb-xxl align-self-center me-2" style="margin-bottom: 10px;">
                        <span class="avatar-title bg-soft-primary rounded">{{auth()->user()->name[0]}}</span>
                    </div>
            </div>
            <p class="mb-2">{{ auth()->user()->name }}</p>
            <p class="mb-0">{{ auth()->user()->email }}</p>
            {{-- <p class="mb-0">Rol: {{ auth()->user()->roles()->first()->descripcion }}</p> --}}
        </div>
        <div class="menu-content h-100" data-simplebar>
            <ul class="metismenu left-sidenav-menu">

                <!--Nombre de la Categoria-->


                <!--<li class="menu-label mt-0">PANEL DE CONTROL</li>-->

                <li class="@if (Request::is('/')) mm-active @endif">
                    <a href="{{ route('inicio') }}" class="@if (Request::is('/')) active @endif"> <i data-feather="home" class="align-self-center menu-icon"></i><span>Inicio</span></a>
                </li>


                <li class="menu-label mt-0">MODULOS</li>
               
                {{-- @hasanyrole('Administrador') --}}
                <!--if(Auth::user()->hasPermissionTo('Documentos SGD'))-->
                    <li class="@if (Request::is('asistencia/asistencia*')) mm-active @endif">
                        <a href="{{ route('asistencia.asistencia') }}" class="@if (Request::is('asistencia/asistencia*')) active @endif"> <i data-feather="server" class="align-self-center menu-icon"></i><span>Asistencia</span></a>
                    </li>
                <!--endif-->
                {{-- @endhasanyrole --}}

                <!--<li>
                    <a href="javascript: void(0);"><i data-feather="lock" class="align-self-center menu-icon @if (Request::is('documentos/bandeja*')) mm-active @endif"></i><span>Mi Bandeja</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                    <ul class="nav-second-level" aria-expanded="false">-->
                        <li>
                            <a href="javascript: void(0);"><i data-feather="lock" class="align-self-center menu-icon @if (Request::is('personal*')) mm-active @endif"></i><span>Personal</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                            <ul class="nav-second-level" aria-expanded="false">
                                <li class="nav-item @if (Request::is('personal/asesores*')) mm-active @endif"><a class="nav-link" href="{{ route('personal.asesores') }}"><i class="ti-control-record"></i>Asesores</a></li>
                            </ul>
                        </li>
                <!--    </ul>
                </li>-->

                <li class="@if (Request::is('servicios*')) mm-active @endif">
                    <a href="{{ route('servicios.index') }}" class="@if (Request::is('servicios/index*')) active @endif"> <i data-feather="check-square" class="align-self-center menu-icon"></i><span>Servicios por Entidad</span></a>
                </li>

                <li class="menu-label mt-0">PARAMETROS</li>
                {{-- <li class="@if (Request::is('consultas/comprobantespago*')) mm-active @endif">
                    <a href="{{ route('consultas.comprobantespago') }}" class="@if (Request::is('consultas/comprobantespago*')) active @endif"> <i data-feather="search" class="align-self-center menu-icon"></i><span>Comprobantes de Pago</span></a>
                </li>
                <li class="@if (Request::is('/')) mm-active @endif">
                    <a href="{{ route('inicio') }}" class="@if (Request::is('/')) active @endif"> <i data-feather="bar-chart" class="align-self-center menu-icon"></i><span>Resúmenes</span></a>
                </li> --}}

                {{-- @role('Administrador') --}}
                    {{-- <hr class="hr-dashed hr-menu">
                    <!--Nombre de la Categoria-->
                    <li class="menu-label my-2">Parametros</li>
                    <li class="@if (Request::is('maestros/dependencia*')) active @endif">
                        <a href="{{ route('maestros.dependencia.index') }}"><i data-feather="box" class="align-self-center menu-icon"></i><span>Dependencias</span></a>
                    </li>
                    <li class="@if (Request::is('maestros/Series*')) active  @endif">
                        <a href="{{ route('maestros.series.index') }}"><i data-feather="box" class="align-self-center menu-icon"></i><span>Series</span></a>
                    </li>
                    <li>
                        <a href="javascript: void(0);"><i data-feather="box" class="align-self-center menu-icon"></i><span>Personal</span></a>
                    </li> --}}

                    <hr class="hr-dashed hr-menu">
                    <!--Nombre de la Categoria-->
                    <li class="menu-label my-2">Configuración</li>
                    <li class="@if (Request::is('usuarios*')) active  @endif">
                        <a href="{{ route('usuarios.index') }}"><i data-feather="box" class="align-self-center menu-icon"></i><span>Usuarios</span></a>
                    </li>
                    {{-- <li class="@if (Request::is('roles*')) active  @endif">
                        <a href="{{ route('roles.index') }}"><i data-feather="box" class="align-self-center menu-icon"></i><span>Roles</span></a>
                    </li>
                    <li>
                        <a href="javascript: void(0);"><i data-feather="box" class="align-self-center menu-icon"></i><span>Mi Cuenta</span></a>
                    </li> --}}
                {{-- @endrole           --}}
            </ul>
        </div>
    </div>
    <!-- end left-sidenav-->
