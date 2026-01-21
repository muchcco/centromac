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
        <a href="{{ route('inicio') }}" class="logo">
            <span>
                <img src="{{ asset('imagen/logo-pcm.png') }}" alt="PCM Logo" class="logo-lg logo-light"
                    style="max-width: 80%;height: 40px;">
                <img src="{{ asset('imagen/logo-pcm.png') }}" alt="PCM Logo" class="logo-lg logo-dark"
                    style="max-width: 80%;height: 40px;">
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
    @php
        // LOS MODULOS ACTIVOS SE DATAN POR BASE DE DATOS DONDE 0 ESTA EN DESARROLLO Y 1 EN PRODUCCION

        $asistencia = Illuminate\Support\Facades\DB::table('ACTIVE')->where('NOMBRE_MODULO', 'ASISTENCIA')->first();
        $per_asesores = Illuminate\Support\Facades\DB::table('ACTIVE')
            ->where('NOMBRE_MODULO', 'PERSONAL ASESORES')
            ->first();
        $per_mac = Illuminate\Support\Facades\DB::table('ACTIVE')->where('NOMBRE_MODULO', 'PERSONAL MAC')->first();
        $asignacion_bien = Illuminate\Support\Facades\DB::table('ACTIVE')
            ->where('NOMBRE_MODULO', 'ASIGNACION BIEN')
            ->first();
        $servicio_mac = Illuminate\Support\Facades\DB::table('ACTIVE')
            ->where('NOMBRE_MODULO', 'SERVICIOS MAC')
            ->first();
        $externos = Illuminate\Support\Facades\DB::table('ACTIVE')->where('NOMBRE_MODULO', 'PAGINAS EXTERNAS')->first();
        $eval_mot = Illuminate\Support\Facades\DB::table('ACTIVE')
            ->where('NOMBRE_MODULO', 'EVAL MOTIVACIONAL')
            ->first();
        $inicio_oper = Illuminate\Support\Facades\DB::table('ACTIVE')->where('NOMBRE_MODULO', 'INICIO OPERA')->first();
        $fecilitaciones = Illuminate\Support\Facades\DB::table('ACTIVE')
            ->where('NOMBRE_MODULO', 'FELICITACIONES')
            ->first();
        $ocupablidad_k = Illuminate\Support\Facades\DB::table('ACTIVE')
            ->where('NOMBRE_MODULO', 'OCUPABILIDAD K')
            ->first();
        $puntualida_i = Illuminate\Support\Facades\DB::table('ACTIVE')
            ->where('NOMBRE_MODULO', 'PUNTUALIDAD I')
            ->first();
        $almacen = Illuminate\Support\Facades\DB::table('ACTIVE')->where('NOMBRE_MODULO', 'ALMACEN')->first();
        $servicio_ent = Illuminate\Support\Facades\DB::table('ACTIVE')
            ->where('NOMBRE_MODULO', 'SERVICIOS ENT')
            ->first();
        $dashboard = Illuminate\Support\Facades\DB::table('ACTIVE')->where('NOMBRE_MODULO', 'DASBOARD')->first();
        $usuario = Illuminate\Support\Facades\DB::table('ACTIVE')->where('NOMBRE_MODULO', 'USUARIOS')->first();
        $centros_mac = Illuminate\Support\Facades\DB::table('ACTIVE')->where('NOMBRE_MODULO', 'CENTROS MAC')->first();

    @endphp
    <div class="menu-content h-100" data-simplebar>
        <ul class="metismenu left-sidenav-menu">

            <!--Nombre de la Categoria-->


            <li class="menu-label mt-0">PANEL DE CONTROL</li>

            <li class="@if (Request::is('/')) mm-active @endif">
                <a href="{{ route('inicio') }}" class="@if (Request::is('/')) active @endif"> <i
                        data-feather="home" class="align-self-center menu-icon"></i><span>Inicio</span></a>
            </li>




            <li class="menu-label mt-0">Gestión de Personal</li>

            {{-- @hasanyrole('Administrador') --}}
            <!--if(Auth::user()->hasPermissionTo('Documentos SGD'))-->
            @if ($asistencia->VALOR == '1')
                <li class="@if (Request::is('asistencia/*')) mm-active @endif">
                    <a href="{{ route('asistencia.asistencia') }}"
                        class="@if (Request::is('asistencia/*')) active @endif"> <i data-feather="server"
                            class="align-self-center menu-icon"></i><span>Asistencia</span></a>
                </li>
            @endif
            <!--endif-->
            {{-- @endhasanyrole --}}

            <!--<li>
                    <a href="javascript: void(0);"><i data-feather="lock" class="align-self-center menu-icon @if (Request::is('documentos/bandeja*')) mm-active @endif"></i><span>Mi Bandeja</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                    <ul class="nav-second-level" aria-expanded="false">-->
            <li>
                <a href="javascript: void(0);"><i data-feather="lock"
                        class="align-self-center menu-icon @if (Request::is('personal*')) mm-active @endif"></i><span>Directorio
                        de personal </span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                <ul class="nav-second-level" aria-expanded="false">
                    @if ($per_mac->VALOR == '1')
                        <li class="nav-item @if (Request::is('directorio/*')) mm-active @endif"><a class="nav-link"
                                href="{{ route('directorio') }}"><i class="ti-control-record"></i>Centros MAC</a></li>
                    @endif
                    @if ($per_asesores->VALOR == '1')
                        <li class="nav-item @if (Request::is('personal/asesores*')) mm-active @endif"><a class="nav-link"
                                href="{{ route('personal.asesores') }}"><i class="ti-control-record"></i>Asesores</a>
                        </li>
                    @endif
                    @if ($per_mac->VALOR == '1')
                        <li class="nav-item @if (Request::is('personal/pcm*')) mm-active @endif"><a class="nav-link"
                                href="{{ route('personal.pcm') }}"><i class="ti-control-record"></i>Entidad
                                administradora</a></li>
                    @endif

                </ul>
            </li>

            <!--    </ul>
                </li>-->


            <li class="menu-label mt-0">Monitoreo y control </li>

            <li>
                <a href="javascript: void(0);">
                    <i data-feather="clipboard"
                        class="align-self-center menu-icon @if (Request::is('indicador*') || Request::is('formatos*')) mm-active @endif"></i>
                    <span>Formatos</span>
                    <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                </a>
                <ul class="nav-second-level" aria-expanded="false">

                    <li class="@if (Request::is('indicador.ocupabilidad*')) mm-active @endif">
                        <a href="{{ route('indicador.ocupabilidad.index') }}"
                            class="@if (Request::is('indicador/ocupabilidad/index*')) active @endif">
                            <i class="ti-control-record"></i><span>Ocupabilidad</span>
                        </a>
                    </li>
                    <li class="@if (Request::is('indicador.puntualidad*')) mm-active @endif">
                        <a href="{{ route('indicador.puntualidad.index') }}"
                            class="@if (Request::is('indicador/puntualidad/index*')) active @endif">
                            <i class="ti-control-record"></i><span>Puntualidad</span>
                        </a>
                    </li>

                    {{--        <li class="">
                        <a href="https://apps.powerapps.com/play/e/default-34b48e4e-2519-4060-a09e-5b05d901a4d7/a/a2036540-c323-4a79-8cf2-0a9330c23119?tenantId=34b48e4e-2519-4060-a09e-5b05d901a4d7&hint=7bbca76f-2366-4f77-82a7-401fa606b4c1&sourcetime=1720627181714&source=portal"
                            target="_blank">
                            <i class="ti-control-record"></i><span>Observaciones e Interrupciones</span>
                        </a>
                    </li> --}}

                    @role('Administrador|Coordinador|Supervisor|Moderador')
                        <li class="">
                            <a href="https://pcmgobperu-my.sharepoint.com/:x:/g/personal/sscs_06_pcm_gob_pe/EVYcqSpCEG1CoVMmnMcl6vQBUUe08LMNg8JC3Da32IxLhA?wdOrigin=TEAMS-MAGLEV.p2p_ns.rwc&wdExp=TEAMS-TREATMENT&wdhostclicktime=1731515745580&web=1"
                                target="_blank">
                                <i class="ti-control-record"></i><span>Módulos asignados</span>
                            </a>
                        </li>
                    @endrole
                    @if ($eval_mot->VALOR == '1')
                        <li class="@if (Request::is('formatos/evaluacion_motivacional*')) mm-active @endif">
                            <a href="{{ route('formatos.evaluacion_motivacional.index') }}"
                                class="@if (Request::is('formatos/evaluacion_motivacional*')) active @endif">
                                <i class="ti-control-record"></i><span>Evaluación Motivacional</span>
                            </a>
                        </li>
                    @endif
                    @if ($inicio_oper->VALOR == '1')
                        <li class="@if (Request::is('formatos/f_02_inicio_oper*')) mm-active @endif">
                            <a href="{{ route('formatos.f_02_inicio_oper.index') }}"
                                class="@if (Request::is('formatos/f_02_inicio_oper*')) active @endif">
                                <i class="ti-control-record"></i><span>Formato de Inicio de Operaciones</span>
                            </a>
                        </li>
                    @endif
                    @if ($fecilitaciones->VALOR == '1')
                        <li class="@if (Request::is('formatos.f_felicitaciones*')) mm-active @endif">
                            <a href="{{ route('formatos.f_felicitaciones.index') }}"
                                class="@if (Request::is('formatos/f_felicitaciones*')) active @endif">
                                <i class="ti-control-record"></i><span>Formato de Felicitaciones</span>
                            </a>
                        </li>
                        <li class="@if (Request::is('verificaciones.*')) mm-active @endif">
                            <a href="{{ route('verificaciones.index') }}"
                                class="@if (Request::is('verificaciones/index*')) active @endif">
                                <i class="ti-control-record"></i><span>Verificación de apertura (check list)</span>
                            </a>
                        </li>
                    @endif

                </ul>
            </li>


            @if ($ocupablidad_k->VALOR == '1')
                {{-- <li class="menu-label mt-0">INDICADORES</li> --}}

                <li>
                    <a href="javascript: void(0);">
                        <i data-feather="bar-chart-2"
                            class="align-self-center menu-icon @if (Request::is('ocupabilidad*') || Request::is('puntualidad*')) mm-active @endif"></i>
                        <span>Indicadores</span>
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">

                        <li class="@if (Request::is('ocupabilidad.*')) mm-active @endif">
                            <a href="{{ route('ocupabilidad.index') }}"
                                class="@if (Request::is('ocupabilidad/index*')) active @endif">
                                <i class="ti-control-record"></i><span>Ocupabilidad</span>
                            </a>
                        </li>
                        <li class="@if (Request::is('puntualidad.*')) mm-active @endif">
                            <a href="{{ route('puntualidad.index') }}"
                                class="@if (Request::is('puntualidad/index*')) active @endif">
                                <i class="ti-control-record"></i><span>Puntualidad</span>
                            </a>
                        </li>
                        @role('Administrador')
                            <li class="@if (Request::is('reporte.*')) mm-active @endif">
                                <a href="{{ route('reporte.ocupabilidad.index') }}"
                                    class="@if (Request::is('reporte/ocupabilidad*')) active @endif">
                                    <i class="ti-control-record"></i><span>Reporte Ocupabilidad</span>
                                </a>
                            </li>
                        @endrole

                    </ul>
                </li>
            @endif

            @if ($dashboard->VALOR == '1')
                <li class="menu-label mt-0">Tableros de Información</li>

                <li>
                    <a href="javascript:void(0);">
                        <i data-feather="lock"
                            class="align-self-center menu-icon @if (Request::is('dashboard*')) mm-active @endif"></i>
                        <span>Plataforma MAC</span>
                        <span class="menu-arrow">
                            <i class="mdi mdi-chevron-right"></i>
                        </span>
                    </a>

                    <ul class="nav-second-level" aria-expanded="false">

                        {{-- Atenciones --}}
                        <li class="nav-item @if (Request::is('dashboard/index')) mm-active @endif">
                            <a class="nav-link d-flex align-items-start" href="{{ route('dashboard.index') }}">
                                <i class="ti-control-record mt-1"></i>
                                <span class="ms-2">Atenciones</span>
                            </a>
                        </li>

                        {{-- CMAC y MAC Express - Implementación --}}
                        <li class="nav-item @if (Request::is('dashboard/getion_interna')) mm-active @endif">
                            <a class="nav-link d-flex align-items-start"
                                href="{{ route('dashboard.getion_interna') }}">
                                <i class="ti-control-record mt-1"></i>
                                <span class="ms-2">CMAC y MAC Express<br>
                                    <small class="text-muted">(Implementación y Módulos asignados)</small>
                                </span>
                            </a>
                        </li>

                        {{-- CMAC y MAC Express - Entidades --}}
                        <li class="nav-item @if (Request::is('dashboard/getion_interna')) mm-active @endif">
                            <a class="nav-link d-flex align-items-start"
                                href="{{ route('dashboard.getion_interna') }}">
                                <i class="ti-control-record mt-1"></i>
                                <span class="ms-2">CMAC y MAC Express<br>
                                    <small class="text-muted">(Entidades y servicios)</small>
                                </span>
                            </a>
                        </li>

                        {{-- Indicadores ANS --}}
                        {{--                      <li class="nav-item @if (Request::is('dashboard/indicadores_ans*')) mm-active @endif">
                            <a class="nav-link d-flex align-items-start"
                                href="{{ route('dashboard.indicadores_ans') }}">
                                <i class="ti-control-record mt-1"></i>
                                <span class="ms-2">Indicadores ANS</span>
                            </a>
                        </li> --}}

                        {{-- Reporte ALO MAC --}}
                        <li class="nav-item @if (Request::is('dashboard/alo_mac*')) mm-active @endif">
                            <a class="nav-link d-flex align-items-start" href="{{ route('dashboard.alo_mac') }}">
                                <i class="ti-control-record mt-1"></i>
                                <span class="ms-2">Reporte ALO MAC</span>
                            </a>
                        </li>

                        {{-- Incidencias MAC por Departamento --}}
                        <li class="nav-item @if (Request::is('dashboard/incidencias_mac*')) mm-active @endif">
                            <a class="nav-link d-flex align-items-start"
                                href="{{ route('dashboard.incidencias_mac') }}">
                                <i class="ti-control-record mt-1"></i>
                                <span class="ms-2">Incidencias MAC por Departamento</span>
                            </a>
                        </li>

                    </ul>
                </li>
            @endif

            {{--             <hr class="hr-dashed hr-menu"> --}}
            <li class="menu-label mt-0">PILOTO</li>
            @role('Supervisor|Coordinador|Especialista TIC|Moderador|Administrador')
                <li class="@if (Request::is('incumplimiento*')) mm-active @endif">
                    <a href="{{ route('incumplimiento.index') }}"
                        class="@if (Request::is('incumplimiento/index*')) active @endif">
                        <i class="ti-control-record"></i><span>Incidentes Operativos</span>
                    </a>
                </li>
            @endrole
            {{-- Observaciones (solo para Supervisor, Coordinador, Especialista_TIC) --}}
            @role('Supervisor|Coordinador|Especialista_TIC|Moderador|Administrador')
                <li class="@if (Request::is('observacion*')) mm-active @endif">
                    <a href="{{ route('observacion.index') }}"
                        class="@if (Request::is('observacion/index*')) active @endif d-flex align-items-center justify-content-between">
                        <div>
                            <i class="ti-control-record"></i>
                            <span>Observaciones</span>
                        </div>
                        <span class="badge bg-warning text-dark ms-2" style="font-size: 10px;">
                            EN DESUSO
                        </span>
                    </a>
                </li>
            @endrole

            {{-- Interrupciones (solo para Coordinador, Especialista_TIC) --}}
            @role('Coordinador|Especialista TIC|Moderador|Administrador')
                <li class="@if (Request::is('interrupcion*')) mm-active @endif">
                    <a href="{{ route('interrupcion.index') }}"
                        class="@if (Request::is('interrupcion/index*')) active @endif">
                        <i class="ti-control-record"></i><span>Interrupciones</span>
                    </a>
                </li>
            @endrole
            {{-- Interrupciones (solo para Coordinador, Especialista_TIC, Moderador, Administrador) --}}
            {{-- Tipificación de Observaciones e Interrupciones (solo para Administrador y Especialista_TIC) --}}
            @role('Administrador|Moderador')
                <li class="@if (Request::is('tipo_int_obs/index')) mm-active @endif">
                    <a href="{{ route('tipo_int_obs.index') }}"
                        class="@if (Request::is('tipo_int_obs/index')) active @endif">
                        <i class="ti-control-record"></i><span>Tipificación</span>
                    </a>
                </li>
            @endrole
            <hr class="hr-dashed hr-menu">

            <li class="menu-label mt-0">APOYO</li>

            @if ($externos->VALOR == '1')
                <li class="@if (Request::is('externo-int*')) mm-active @endif">
                    <a href="{{ route('externo-int') }}" class="@if (Request::is('externo-int*')) active @endif">
                        <i data-feather="check-square" class="align-self-center menu-icon"></i><span>Páginas
                            externas</span></a>
                </li>
            @endif
            {{-- @if ($externos->VALOR == '1')
                    <li class="@if (Request::is('directorio*')) mm-active @endif">
                        <a href="{{ route('directorio') }}" class="@if (Request::is('directorio*')) active @endif"> <i data-feather="check-square" class="align-self-center menu-icon"></i><span>Directorio</span></a>
                    </li>
                @endif --}}



            @role('Administrador|Especialista_TIC')
                @if ($almacen->VALOR == '1')
                    <li class="menu-label mt-0">PARÁMETROS</li>

                    <li class="@if (Request::is('almacen*')) mm-active @endif">
                        <a href="{{ route('almacen.index') }}" class="@if (Request::is('almacen*')) active @endif">
                            <i data-feather="clipboard" class="align-self-center menu-icon"></i><span>Almacen</span></a>
                    </li>
                    <li class="@if (Request::is('mantenimiento*')) mm-active @endif">
                        <a href="{{ route('mantenimiento.index') }}"
                            class="@if (Request::is('mantenimiento/index*')) active @endif"> <i data-feather="check-square"
                                class="align-self-center menu-icon"></i><span>Programación de Mantenimientos</span></a>
                    </li>
                @endif

                @if ($servicio_ent->VALOR == '1')
                    <li class="@if (Request::is('servicios*')) mm-active @endif">
                        <a href="{{ route('servicios.index') }}"
                            class="@if (Request::is('servicios/index*')) active @endif"> <i data-feather="check-square"
                                class="align-self-center menu-icon"></i><span>Servicios por Entidad</span></a>
                    </li>
                @endif


            @endrole
            @role('Administrador')

                <hr class="hr-dashed hr-menu">
                <!--Nombre de la Categoria-->
                <li class="menu-label my-2">Accesos</li>
                @if ($usuario->VALOR == '1')
                    <li class="@if (Request::is('usuarios*')) active @endif">
                        <a href="{{ route('usuarios.index') }}"><i data-feather="box"
                                class="align-self-center menu-icon"></i><span>Usuarios</span></a>
                    </li>
                @endif
            @endrole
            @role('Administrador|Especialista TIC')

                <hr class="hr-dashed hr-menu">
                <!--Nombre de la Categoria-->
                <li class="menu-label my-2">Configuración</li>

                @if ($centros_mac->VALOR == '1')
                    <li class="@if (Request::is('configuracion*')) active @endif">
                        <a href="{{ route('configuracion.nuevo_mac') }}"><i data-feather="box"
                                class="align-self-center menu-icon"></i><span>Centro MAC</span></a>
                    </li>
                @endif
            @endrole
            @role('Administrador|Especialista TIC')
                <li class="@if (Request::is('horariomac*')) mm-active @endif">
                    <a href="{{ route('horariomac.index') }}" class="@if (Request::is('horariomac/index*')) active @endif">
                        <i data-feather="clock" class="align-self-center menu-icon"></i><span>Horario MAC</span>
                    </a>
                </li>
            @endrole

            @role('Administrador|Especialista TIC')
                <li class="@if (Request::is('feriado*')) mm-active @endif">
                    <a href="{{ route('feriados.index') }}" class="@if (Request::is('feriado/index*')) active @endif">
                        <i data-feather="calendar" class="align-self-center menu-icon"></i><span>Días no
                            hábiles</span></a>
                </li>
            @endrole


            @role('Administrador|Especialista TIC|Supervisor|Coordinador|Moderador')
                <li class="@if (Request::is('modulos*')) mm-active @endif">
                    <a href="{{ route('modulos.index') }}" class="@if (Request::is('modulos/index*')) active @endif">
                        <i data-feather="monitor" class="align-self-center menu-icon"></i><span>Módulo</span></a>
                </li>
                <li class="@if (Request::is('personalModulo*')) mm-active @endif">
                    <a href="{{ route('personalModulo.index') }}"
                        class="@if (Request::is('personalModulo/index*')) active @endif">
                        <i data-feather="monitor" class="align-self-center menu-icon"></i><span>Personal -
                            Modulo</span></a>
                </li>
                <li class="@if (Request::is('personalModuloI*')) mm-active @endif">
                    <a href="{{ route('personalModuloI.index') }}"
                        class="@if (Request::is('personalModuloI/index*')) active @endif">
                        <i data-feather="map-pin" class="align-self-center menu-icon"></i><span>Itinerante</span>
                    </a>
                </li>
            @endrole

            @role('Administrador|Especialista_TIC|Supervisor|Coordinador')
                @if ($asignacion_bien->VALOR == '1')
                    <li class="@if (Request::is('asignacion*')) mm-active @endif">
                        <a href="{{ route('asignacion.index') }}"
                            class="@if (Request::is('asignacion*')) active @endif"> <i data-feather="clipboard"
                                class="align-self-center menu-icon"></i><span>Asignación de bienes</span></a>
                    </li>
                @endrole
            @endif
            @if ($servicio_mac->VALOR == '1')
                <li class="@if (Request::is('serv_mac*')) mm-active @endif">
                    <a href="{{ route('serv_mac.index') }}"
                        class="@if (Request::is('serv_mac/index*')) active @endif"> <i data-feather="check-square"
                            class="align-self-center menu-icon"></i><span>Servicios por MAC</span></a>
                </li>
            @endif
            @role('Administrador|Moderador|Supervisor|Especialista_TIC|Especialista TIC|Coordinador')
                <hr class="hr-dashed hr-menu">

                <!-- Nueva sección MONITOREO -->
                <li class="menu-label mt-0">MONITOREO</li>

                <li class="@if (Request::is('monitoreo/asistencia*')) mm-active @endif">
                    <a href="{{ route('monitoreo.asistencia.index') }}"
                        class="@if (Request::is('monitoreo/asistencia*')) active @endif">
                        <i data-feather="activity" class="align-self-center menu-icon"></i>
                        <span>Monitoreo de Asistencia</span>
                    </a>
                </li>
                <!-- Pivot de Cierres -->
                <li class="@if (Request::is('monitoreo/asistencia/pivot*')) mm-active @endif">
                    <a href="{{ route('monitoreo.asistencia.pivot') }}"
                        class="@if (Request::is('monitoreo/asistencia/pivot*')) active @endif">
                        <i data-feather="grid" class="align-self-center menu-icon"></i>
                        <span>Monitoreo de Cierres</span>
                    </a>
                </li>
            @endrole

        </ul>

    </div>
</div>
<!-- end left-sidenav-->
