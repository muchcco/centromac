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
                    <img src="{{ asset('imagen/logo-pcm.png') }}" alt="PCM Logo" class="logo-lg logo-light" style="max-width: 80%;height: 50px;">
                    <img src="{{ asset('imagen/logo-pcm.png') }}" alt="PCM Logo" class="logo-lg logo-dark" style="max-width: 80%;height: 50px;">
                </span>
            </a>
        </div>
        <!--end logo-->
        {{-- <div class="update-msg text-center" style="margin: 16px 16px 16px;">
            <div class="pad-btm mb-1">
                <!--<img class="img-circle img-md" src="{{ asset('Img\profile-photos\1.png')}}" alt="Profile Picture">-->
                    <div class="avatar-box thumb-xxl align-self-center me-2" style="margin-bottom: 10px;">
                        <span class="avatar-title bg-soft-primary rounded">{{auth()->user()->name[0]}}</span>
                    </div>
            </div>
            <p class="mb-2">{{ auth()->user()->name }}</p>
            <p class="mb-0">{{ auth()->user()->email }}</p>
            <p id="act_role_sidebar" class="mb-0">Rol: {{ auth()->user()->roles->pluck('name')->implode(', ') }} </p>
        </div> --}}
        <div class="menu-content h-100" data-simplebar>
            <ul class="metismenu left-sidenav-menu">

                <!--Nombre de la Categoria-->


                <li class="menu-label mt-0">PANEL DE CONTROL</li>

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
                            <li class="nav-item @if (Request::is('personal/pcm*')) mm-active @endif"><a class="nav-link" href="{{ route('personal.pcm') }}"><i class="ti-control-record"></i>Pcm</a></li>
                        </ul>
                    </li>
                <!--    </ul>
                </li>-->
                @role('Administrador|Especialista_TIC|Supervisor|Coordinador')
                <li class="@if (Request::is('asignacion*')) mm-active @endif">
                    <a href="{{ route('asignacion.index') }}" class="@if (Request::is('asignacion*')) active @endif"> <i data-feather="clipboard" class="align-self-center menu-icon"></i><span>Asignación de bienes</span></a>
                </li>
                @endrole

                <li class="@if (Request::is('serv_mac*')) mm-active @endif">
                    <a href="{{ route('serv_mac.index') }}" class="@if (Request::is('serv_mac/index*')) active @endif"> <i data-feather="check-square" class="align-self-center menu-icon"></i><span>Servicios por MAC</span></a>
                </li>

                <li class="@if (Request::is('externo*')) mm-active @endif">
                    <a href="{{ route('externo') }}" class="@if (Request::is('externo*')) active @endif"> <i data-feather="check-square" class="align-self-center menu-icon"></i><span>Páginas externas</span></a>
                </li>

                <li class="menu-label mt-0">FORMATOS</li>
                
                <li class="@if (Request::is('formatos/evaluacion_motivacional*')) mm-active @endif">
                    <a href="{{ route('formatos.evaluacion_motivacional.index') }}" class="@if (Request::is('formatos/evaluacion_motivacional*')) active @endif"> <i data-feather="clipboard" class="align-self-center menu-icon"></i><span>Evaluación Motivacional</span></a>
                </li>
                <li class="@if (Request::is('formatos/f_02_inicio_oper*')) mm-active @endif">
                    <a href="{{ route('formatos.f_02_inicio_oper.index') }}" class="@if (Request::is('formatos/f_02_inicio_oper*')) active @endif"> <i data-feather="clipboard" class="align-self-center menu-icon"></i><span>Formato de Inicio de Operaciones</span></a>
                </li>
                <li class="@if (Request::is('formatos/f_felicitaciones*')) mm-active @endif">
                    <a href="{{ route('formatos.f_felicitaciones.index') }}" class="@if (Request::is('formatos/f_felicitaciones*')) active @endif"> <i data-feather="clipboard" class="align-self-center menu-icon"></i><span>Formato de Felicitaciones</span></a>
                </li>
                @role('Administrador|Especialista_TIC')

                <li class="menu-label mt-0">PARAMETROS</li>
                <li class="@if (Request::is('almacen*')) mm-active @endif">
                    <a href="{{ route('almacen.index') }}" class="@if (Request::is('almacen*')) active @endif"> <i data-feather="clipboard" class="align-self-center menu-icon"></i><span>Almacen</span></a>
                </li>

                <li class="@if (Request::is('servicios*')) mm-active @endif">
                    <a href="{{ route('servicios.index') }}" class="@if (Request::is('servicios/index*')) active @endif"> <i data-feather="check-square" class="align-self-center menu-icon"></i><span>Servicios por Entidad</span></a>
                </li>
                {{-- <li class="@if (Request::is('consultas/comprobantespago*')) mm-active @endif">
                    <a href="{{ route('consultas.comprobantespago') }}" class="@if (Request::is('consultas/comprobantespago*')) active @endif"> <i data-feather="search" class="align-self-center menu-icon"></i><span>Comprobantes de Pago</span></a>
                </li>
                <li class="@if (Request::is('/')) mm-active @endif">
                    <a href="{{ route('inicio') }}" class="@if (Request::is('/')) active @endif"> <i data-feather="bar-chart" class="align-self-center menu-icon"></i><span>Resúmenes</span></a>
                </li> --}}

                {{-- @role('Administrador|Especialista TIC') --}}
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
                @endrole          
            </ul>
        </div>
    </div>
    <!-- end left-sidenav-->
