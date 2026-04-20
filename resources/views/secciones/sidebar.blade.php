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
                <img src="{{ asset('imagen/logo-programa.jpg') }}" alt="PROGRAMA Logo" class="logo-lg logo-light"
                    style="max-width: 80%;height: 40px;">
                <img src="{{ asset('imagen/logo-programa.jpg') }}" alt="PROGRAMA Logo" class="logo-lg logo-dark"
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


            <li class="menu-label mt-0">ÍNDICE</li>

            <li class="@if (Request::is('/')) mm-active @endif">
                <a href="{{ route('inicio') }}" class="@if (Request::is('/')) active @endif"> <i
                        data-feather="home" class="align-self-center menu-icon"></i><span>Inicio</span></a>
            </li>



<!-- Dashboard-->

                        @if ($dashboard->VALOR == '1')
                <li class="menu-label mt-0">Tableros de Información - MAC</li>

                <li>


                        {{-- Atenciones --}}
                        <li class="nav-item @if (Request::is('dashboard/index')) mm-active @endif">
                            <a class="nav-link d-flex align-items-start" href="{{ route('dashboard.index') }}">
                               <i data-feather="bar-chart" class="align-self-center menu-icon"></i> <span class="ms-2">Atenciones Plataforma MAC</span>
                            </a>
                        </li>

                        {{-- CMAC y MAC Express - Implementación --}}
                        <li class="nav-item @if (Request::is('dashboard/getion_interna')) mm-active @endif">
                            <a class="nav-link d-flex align-items-start"
                                href="{{ route('dashboard.getion_interna') }}">

                               <i data-feather="bar-chart-2" class="align-self-center menu-icon"></i> <span class="ms-2">Entidades y Servicios<br>
                                </span>
                            </a>
                        </li>

                        {{-- Indicadores ANS --}}
                        {{--                      <li class="nav-item @if (Request::is('dashboard/indicadores_ans*')) mm-active @endif">
                            <a class="nav-link d-flex align-items-start"
                                href="{{ route('dashboard.indicadores_ans') }}">                 
                                     <i data-feather="pie-chart" class="align-self-center menu-icon"></i>    <span class="ms-2">Indicadores ANS</span>
                            </a>
                        </li> --}}

                        {{-- Reporte Sumarizado - ALO MAC --}}
                        <li class="nav-item @if (Request::is('dashboard/alo_mac*')) mm-active @endif">
                            <a class="nav-link d-flex align-items-start" href="{{ route('dashboard.alo_mac') }}">
                               <i data-feather="pie-chart" class="align-self-center menu-icon"></i>  
                                <span class="ms-2">Reporte Sumarizado - ALO MAC</span>
                            </a>
                        </li>

                        {{-- Incidencias MAC por Departamento --}}
                        <li class="nav-item @if (Request::is('dashboard/incidencias_mac*')) mm-active @endif">
                            <a class="nav-link d-flex align-items-start"
                                href="{{ route('dashboard.incidencias_mac') }}">
                              <i data-feather="map" class="align-self-center menu-icon"></i>
                                <span class="ms-2">Mapa de Incidencias – ALO MAC</span>
                            </a>
                        </li>

                </li>
            @endif

          <hr class="hr-dashed hr-menu">
                <li class="menu-label mt-0">REGISTROS, CONTROLES E INDICADORES</li>


                    <li class="@if (Request::is('indicador.ocupabilidad*')) mm-active @endif">
                        <a href="{{ route('indicador.ocupabilidad.index') }}"
                            class="@if (Request::is('indicador/ocupabilidad/index*')) active @endif">
                             <i data-feather="users" class="align-self-center menu-icon"></i><span>Ocupabilidad (Asistencia)</span>
                        </a>
                    </li>
                    <li class="@if (Request::is('indicador.puntualidad*')) mm-active @endif">
                        <a href="{{ route('indicador.puntualidad.index') }}"
                            class="@if (Request::is('indicador/puntualidad/index*')) active @endif">
                            <i data-feather="clock" class="align-self-center menu-icon"></i><span>Puntualidad</span>
                        </a>
                    </li>

            @role('Administrador|Moderador|Supervisor|Especialista_TIC|Especialista TIC|Coordinador')
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




          <hr class="hr-dashed hr-menu">
                <li class="menu-label mt-0">GESTIÓN OPERATIVA</li>

            @if ($ocupablidad_k->VALOR == '1')
                {{-- <li class="menu-label mt-0">GESTIÓN OPERATIVA</li> --}}

                <li>
             

                    @if ($fecilitaciones->VALOR == '1')
                        <li class="@if (Request::is('verificaciones.*')) mm-active @endif">
                            <a href="{{ route('verificaciones.index') }}"
                                class="@if (Request::is('verificaciones/index*')) active @endif">
                                 <i data-feather="check-circle"  style="width: 20px; height: 20px;"></i><span class="ms-2">Verificación de apertura (check list)</span>
                            </a>
                        </li>
                    @endif


              <li>
                    <a href="javascript:void(0);">
                        <i data-feather="lock"
                            class="align-self-center menu-icon @if (Request::is('dashboard*')) mm-active @endif"></i>
                        <span>Ocupabilidad (Asistencia)</span>
                        <span class="menu-arrow">
                            <i class="mdi mdi-chevron-right"></i>
                        </span>
                    </a>

                    <ul class="nav-second-level" aria-expanded="false">


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



                <li class="">
                            <a class="nav-link d-flex align-items-start"
                                href="https://sismac.mac.pe/carga_asistencia/carga_manual/cargardatos.php" target="_blank">
                                <i data-feather="upload" style="width: 30px; height: 30px;" class="align-self-center menu-icon"></i><span >Carga de registro de asistencia (opcional)</span>
                            </a>
                        </li> 



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


                        {{-- Indicadores ANS --}}
                         <li >
                            <a class="nav-link d-flex align-items-start"
                                href="https://sismac.mac.pe/external-mac/assists" target="_blank">
                                <i data-feather="edit" style="width: 25px; height: 25px;"
                            class="align-self-center menu-icon"></i>
                                <span >Registro de asistencia manual</span>
                            </a>
                        </li> 


                    </ul>
                </li>



            @role('Supervisor|Coordinador|Especialista TIC|Moderador|Administrador')
                <li class="@if (Request::is('incumplimiento*')) mm-active @endif">
                    <a href="{{ route('incumplimiento.index') }}"
                        class="@if (Request::is('incumplimiento/index*')) active @endif">
                        <i data-feather="file-plus" class="align-self-center menu-icon"></i><span>Incidentes</span>
                    </a>
                </li>
            @endrole


            {{-- Interrupciones (solo para Coordinador, Especialista_TIC) --}}
            @role('Coordinador|Especialista TIC|Moderador|Administrador')
                <li class="@if (Request::is('interrupcion*')) mm-active @endif">
                    <a href="{{ route('interrupcion.index') }}"
                        class="@if (Request::is('interrupcion/index*')) active @endif">
                          <i data-feather="wifi-off" class="align-self-center menu-icon"></i><span>Interrupciones (TIC)</span>
                    </a>
                </li>
            @endrole

                    @if ($fecilitaciones->VALOR == '1')
                        <li class="@if (Request::is('formatos.f_felicitaciones*')) mm-active @endif">
                            <a href="{{ route('formatos.f_felicitaciones.index') }}"
                                class="@if (Request::is('formatos/f_felicitaciones*')) active @endif">
                                <i data-feather="smile" class="align-self-center menu-icon"></i><span>Felicitaciones</span>
                            </a>
                        </li>
                    @endif

                    @role('Administrador|Coordinador|Supervisor|Moderador')
                        <li class="">
                            <a href="https://pcmgobperu-my.sharepoint.com/:x:/g/personal/sscs_powerbi_pcm_gob_pe/IQDLDRwmcsKrQqmCtHGBlwU3Af17ORB5i9JzO0NY2zR1D84?e=BswVrv"
                                target="_blank">
                                 <i data-feather="box" class="align-self-center menu-icon"></i><span>Módulos asignados</span>
                            </a>
                        </li>
                    @endrole

                </li>
            @endif



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

<BR>


          <hr class="hr-dashed hr-menu">

            <li class="menu-label mt-0">Enlaces de interes</li>


            @if ($externos->VALOR == '1')

                        <li class="">
                            <a href="https://sismac.mac.pe/external-mac/personal"
                                target="_blank">
                                <i data-feather="chevron-right" class="align-self-center menu-icon"></i><span>Formulario de registro</span>
                            </a>
                        </li>


                        <li class="">
                            <a href="https://pcmgobperu-my.sharepoint.com/:f:/g/personal/lguevara_pcm_gob_pe/EgglsNEv59pFkSiWYaqznH8BtdJMNyqeOZKDcXNOOZ4qDw?e=BMdHdI"
                                target="_blank">
                                <i data-feather="chevron-right" class="align-self-center menu-icon"></i><span>Tutoriales</span>
                            </a>
                        </li>
       
            @endif
          <hr class="hr-dashed hr-menu">
                <!-- Nueva sección DIRECTORIO -->
                <li class="menu-label mt-0">DIRECTORIO</li>


            <!--<li>
                    <a href="javascript: void(0);"><i data-feather="lock" class="align-self-center menu-icon @if (Request::is('documentos/bandeja*')) mm-active @endif"></i><span>Mi Bandeja</span><span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span></a>
                    <ul class="nav-second-level" aria-expanded="false">-->
            <li>


                    @if ($per_mac->VALOR == '1')
                        <li class="nav-item @if (Request::is('directorio/*')) mm-active @endif"><a class="nav-link"
                                href="{{ route('directorio') }}"> <i data-feather="chevron-right" class="align-self-center menu-icon"></i>Centros MAC</a></li>
                    @endif
                    @if ($per_asesores->VALOR == '1')
                        <li class="nav-item @if (Request::is('personal/asesores*')) mm-active @endif"><a class="nav-link"
                                href="{{ route('personal.asesores') }}"><i data-feather="chevron-right" class="align-self-center menu-icon"></i>Asesores</a>
                        </li>
                    @endif
                    @if ($per_mac->VALOR == '1')
                        <li class="nav-item @if (Request::is('personal/pcm*')) mm-active @endif"><a class="nav-link"
                                href="{{ route('personal.pcm') }}"><i data-feather="chevron-right" class="align-self-center menu-icon"></i>Entidades
                                administradoras</a></li>
                    @endif


            </li>

            <!--    </ul>
                </li>-->


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

            {{-- Interrupciones (solo para Coordinador, Especialista_TIC, Moderador, Administrador) --}}
            {{-- Tipificación de Observaciones e Interrupciones (solo para Administrador y Especialista_TIC) --}}
            @role('Administrador|Moderador')
                <li class="@if (Request::is('tipo_int_obs/index')) mm-active @endif">
                    <a href="{{ route('tipo_int_obs.index') }}"
                        class="@if (Request::is('tipo_int_obs/index')) active @endif">
                       <i data-feather="tag" class="align-self-center menu-icon"></i><span>Tipificación (Incidentes e interrupciones) </span>
                    </a>
                </li>
            @endrole
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







                <!-- Nueva sección OBSOLETOS -->

            {{-- Observaciones (solo para Supervisor, Coordinador, Especialista_TIC) --}}
            @role('Supervisor|Coordinador|Especialista_TIC|Moderador|Administrador')
                            <li class="menu-label mt-0">Obsoletos</li>
                <li class="@if (Request::is('observacion*')) mm-active @endif">
                    <a href="{{ route('observacion.index') }}"
                        class="@if (Request::is('observacion/index*')) active @endif d-flex align-items-center justify-content-between">
                        <div>
                            <i data-feather="eye" class="align-self-center menu-icon"></i>
                            <span>Observaciones</span>
                        </div>
                        <span class="badge bg-warning text-dark ms-2" style="font-size: 10px;">
                            EN DESUSO
                        </span>
                    </a>
                </li>
            @endrole




        </ul>

    </div>
</div>
<!-- end left-sidenav-->

