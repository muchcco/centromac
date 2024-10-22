@extends('layouts.layout')

@section('style')
    <link rel="stylesheet" href="{{ asset('Vendor/toastr/toastr.min.css') }}">
    <!-- Plugins css -->
    <link href="{{ asset('nuevo/plugins/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('nuevo/plugins/huebee/huebee.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('nuevo/plugins/timepicker/bootstrap-material-datetimepicker.css') }}" rel="stylesheet">
    <link href="{{ asset('nuevo/plugins/bootstrap-touchspin/css/jquery.bootstrap-touchspin.min.css') }}" rel="stylesheet" />
    <!-- DataTables -->
    <link href="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('nuevo/plugins/datatables/buttons.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Responsive datatable examples -->
    <link href="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('main')
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <div class="row">
                        <div class="col">
                            <h4 class="page-title">Editar Verificación</h4>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('inicio') }}"><i data-feather="home"
                                            class="align-self-center" style="height: 70%; display: block;"></i></a></li>
                                <li class="breadcrumb-item"><a href="javascript:void(0);"
                                        style="color: #7081b9;">Verificaciones</a></li>
                            </ol>
                        </div><!--end col-->
                    </div><!--end row-->
                </div><!--end page-title-box-->
            </div><!--end col-->
        </div><!--end row-->

        <div class="d-flex justify-content-between mb-4">
            <div>
            </div>
            <div>
                <a href="{{ route('verificaciones.index') }}" class="btn btn-secondary d-flex align-items-center">
                    <i class="fa fa-arrow-left me-2"></i> <!-- Icono de flecha hacia la izquierda -->
                    Volver al Listado
                </a>
            </div>
        </div>
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="card">
            <div class="card-header" style="background-color:#132842">
                <h4 class="card-title text-white">Checklist Diario - Centro MAC {{ auth()->user()->idcentro_mac }}</h4>
            </div><!--end card-header-->

            <div class="py-12">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4">
                    <form action="{{ route('verificaciones.update', $verificacion->id) }}" method="POST">
                        @csrf
                        @method('PUT') <!-- Indica que esta es una solicitud de actualización -->
                        <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">

                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Pregunta</th>
                                    <th>Respuesta</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <thead>
                                    <tr>
                                        <th colspan="3" style="background-color: #233E99; color: white;">Zona de
                                            Recepción</th>
                                    </tr>
                                </thead>
                                <tr>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <label for="apertura_cierre" class="form-label">Apertura/Cierre</label>
                                                <select disabled class="form-control" id="apertura_cierre"
                                                    name="AperturaCierre">
                                                    <option value="0"
                                                        {{ $verificacion->AperturaCierre == 0 ? 'selected' : '' }}>Apertura
                                                    </option>
                                                    <option value="1"
                                                        {{ $verificacion->AperturaCierre == 1 ? 'selected' : '' }}>Relevo
                                                    </option>
                                                    <option value="2"
                                                        {{ $verificacion->AperturaCierre == 2 ? 'selected' : '' }}>Cierre
                                                    </option>
                                                </select>
                                                <input type="hidden" name="AperturaCierre"
                                                    value="{{ $verificacion->AperturaCierre }}">

                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <label for="fecha" class="form-label">Fecha</label>
                                                <input type="date" class="form-control" id="fecha" name="Fecha"
                                                    value="{{ \Carbon\Carbon::parse($verificacion->Fecha)->format('Y-m-d') }}"
                                                    readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <td>Modulo de Recepción</td>
                                    <td>
                                        <input type="hidden" name="ModuloDeRecepcion" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="ModuloDeRecepcionCheckbox"
                                                name="ModuloDeRecepcion" value="1"
                                                {{ $verificacion->ModuloDeRecepcion ? 'checked' : '' }}>
                                            <label class="form-check-label" for="ModuloDeRecepcionCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="observaciones_ModuloDeRecepcion"
                                            value="{{ $observaciones['ModuloDeRecepcion'] ?? '' }}"
                                            placeholder="Observaciones">
                                    </td>
                                </tr>

                                <tr>
                                    <td>Ordenadores de Fila</td>
                                    <td>
                                        <input type="hidden" name="OrdenadoresDeFila" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                id="OrdenadoresDeFilaCheckbox" name="OrdenadoresDeFila" value="1"
                                                {{ $verificacion->OrdenadoresDeFila ? 'checked' : '' }}>
                                            <label class="form-check-label" for="OrdenadoresDeFilaCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="observaciones_OrdenadoresDeFila"
                                            value="{{ $observaciones['OrdenadoresDeFila'] ?? '' }}"
                                            placeholder="Observaciones">
                                    </td>
                                </tr>

                                <tr>
                                    <td>Sillas de Orientadores</td>
                                    <td>
                                        <input type="hidden" name="SillasDeOrientadores" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                id="SillasDeOrientadoresCheckbox" name="SillasDeOrientadores"
                                                value="1" {{ $verificacion->SillasDeOrientadores ? 'checked' : '' }}>
                                            <label class="form-check-label" for="SillasDeOrientadoresCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control"
                                            name="observaciones_SillasDeOrientadores"
                                            value="{{ $observaciones['SillasDeOrientadores'] ?? '' }}"
                                            placeholder="Observaciones">
                                    </td>
                                </tr>

                                <tr>
                                    <td>Ticketera</td>
                                    <td>
                                        <input type="hidden" name="Ticketera" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="TicketeraCheckbox"
                                                name="Ticketera" value="1"
                                                {{ $verificacion->Ticketera ? 'checked' : '' }}>
                                            <label class="form-check-label" for="TicketeraCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="observaciones_Ticketera"
                                            value="{{ $observaciones['Ticketera'] ?? '' }}" placeholder="Observaciones">
                                    </td>
                                </tr>

                                <tr>
                                    <td>Lector de Código de Barras</td>
                                    <td>
                                        <input type="hidden" name="LectorDeCodBarras" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                id="LectorDeCodBarrasCheckbox" name="LectorDeCodBarras" value="1"
                                                {{ $verificacion->LectorDeCodBarras ? 'checked' : '' }}>
                                            <label class="form-check-label" for="LectorDeCodBarrasCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="observaciones_LectorDeCodBarras"
                                            value="{{ $observaciones['LectorDeCodBarras'] ?? '' }}"
                                            placeholder="Observaciones">
                                    </td>
                                </tr>

                                <tr>
                                    <td>Servicio de Telefonia 1800</td>
                                    <td>
                                        <input type="hidden" name="ServicioDeTelefonia1800" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                id="ServicioDeTelefonia1800Checkbox" name="ServicioDeTelefonia1800"
                                                value="1"
                                                {{ $verificacion->ServicioDeTelefonia1800 ? 'checked' : '' }}>
                                            <label class="form-check-label"
                                                for="ServicioDeTelefonia1800Checkbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control"
                                            name="observaciones_ServicioDeTelefonia1800"
                                            value="{{ $observaciones['ServicioDeTelefonia1800'] ?? '' }}"
                                            placeholder="Observaciones">
                                    </td>
                                </tr>

                                <tr>
                                    <td>Insumo de Recepción</td>
                                    <td>
                                        <input type="hidden" name="InsumoRecepcion" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="InsumoRecepcionCheckbox"
                                                name="InsumoRecepcion" value="1"
                                                {{ $verificacion->InsumoRecepcion ? 'checked' : '' }}>
                                            <label class="form-check-label" for="InsumoRecepcionCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="observaciones_InsumoRecepcion"
                                            value="{{ $observaciones['InsumoRecepcion'] ?? '' }}"
                                            placeholder="Observaciones">
                                    </td>
                                </tr>

                                <tr>
                                    <td>Silla de Ruedas</td>
                                    <td>
                                        <input type="hidden" name="SillaRuedas" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="SillaRuedasCheckbox"
                                                name="SillaRuedas" value="1"
                                                {{ $verificacion->SillaRuedas ? 'checked' : '' }}>
                                            <label class="form-check-label" for="SillaRuedasCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="observaciones_SillaRuedas"
                                            value="{{ $observaciones['SillaRuedas'] ?? '' }}"
                                            placeholder="Observaciones">
                                    </td>
                                </tr>

                                <thead>
                                    <tr>
                                        <th colspan="3" style="background-color: #233E99; color: white;">Zona de
                                            Atención</th>
                                    </tr>
                                </thead>

                                <tr>
                                    <td>TV Zona de Atención</td>
                                    <td>
                                        <input type="hidden" name="TvZonaAtencion" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="TvZonaAtencionCheckbox"
                                                name="TvZonaAtencion" value="1"
                                                {{ $verificacion->TvZonaAtencion ? 'checked' : '' }}>
                                            <label class="form-check-label" for="TvZonaAtencionCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="observaciones_TvZonaAtencion"
                                            value="{{ $observaciones['TvZonaAtencion'] ?? '' }}"
                                            placeholder="Observaciones">
                                    </td>
                                </tr>

                                <tr>
                                    <td>Sillas de Espera</td>
                                    <td>
                                        <input type="hidden" name="SillasEspera" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="SillasEsperaCheckbox"
                                                name="SillasEspera" value="1"
                                                {{ $verificacion->SillasEspera ? 'checked' : '' }}>
                                            <label class="form-check-label" for="SillasEsperaCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="observaciones_SillasEspera"
                                            value="{{ $observaciones['SillasEspera'] ?? '' }}"
                                            placeholder="Observaciones">
                                    </td>
                                </tr>

                                <tr>
                                    <td>Sillas de Asesor</td>
                                    <td>
                                        <input type="hidden" name="SillaAsesor" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="SillaAsesorCheckbox"
                                                name="SillaAsesor" value="1"
                                                {{ $verificacion->SillaAsesor ? 'checked' : '' }}>
                                            <label class="form-check-label" for="SillaAsesorCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="observaciones_SillaAsesor"
                                            value="{{ $observaciones['SillaAsesor'] ?? '' }}"
                                            placeholder="Observaciones">
                                    </td>
                                </tr>

                                <tr>
                                    <td>Sillas de Atención</td>
                                    <td>
                                        <input type="hidden" name="SillasAtencion" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="SillasAtencionCheckbox"
                                                name="SillasAtencion" value="1"
                                                {{ $verificacion->SillasAtencion ? 'checked' : '' }}>
                                            <label class="form-check-label" for="SillasAtencionCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="observaciones_SillasAtencion"
                                            value="{{ $observaciones['SillasAtencion'] ?? '' }}"
                                            placeholder="Observaciones">
                                    </td>
                                </tr>

                                <tr>
                                    <td>Módulo de Atención</td>
                                    <td>
                                        <input type="hidden" name="ModuloAtencion" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="ModuloAtencionCheckbox"
                                                name="ModuloAtencion" value="1"
                                                {{ $verificacion->ModuloAtencion ? 'checked' : '' }}>
                                            <label class="form-check-label" for="ModuloAtencionCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="observaciones_ModuloAtencion"
                                            value="{{ $observaciones['ModuloAtencion'] ?? '' }}"
                                            placeholder="Observaciones">
                                    </td>
                                </tr>

                                <tr>
                                    <td>PC Asesores</td>
                                    <td>
                                        <input type="hidden" name="PcAsesores" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="PcAsesoresCheckbox"
                                                name="PcAsesores" value="1"
                                                {{ $verificacion->PcAsesores ? 'checked' : '' }}>
                                            <label class="form-check-label" for="PcAsesoresCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="observaciones_PcAsesores"
                                            value="{{ $observaciones['PcAsesores'] ?? '' }}" placeholder="Observaciones">
                                    </td>
                                </tr>

                                <tr>
                                    <td>Impresoras Zona de Atención</td>
                                    <td>
                                        <input type="hidden" name="ImpresorasZonaAtencion" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                id="ImpresorasZonaAtencionCheckbox" name="ImpresorasZonaAtencion"
                                                value="1"
                                                {{ $verificacion->ImpresorasZonaAtencion ? 'checked' : '' }}>
                                            <label class="form-check-label"
                                                for="ImpresorasZonaAtencionCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control"
                                            name="observaciones_ImpresorasZonaAtencion"
                                            value="{{ $observaciones['ImpresorasZonaAtencion'] ?? '' }}"
                                            placeholder="Observaciones">
                                    </td>
                                </tr>

                                <tr>
                                    <td>Insumo de Materiales</td>
                                    <td>
                                        <input type="hidden" name="InsumoMateriales" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="InsumoMaterialesCheckbox"
                                                name="InsumoMateriales" value="1"
                                                {{ $verificacion->InsumoMateriales ? 'checked' : '' }}>
                                            <label class="form-check-label" for="InsumoMaterialesCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="observaciones_InsumoMateriales"
                                            value="{{ $observaciones['InsumoMateriales'] ?? '' }}"
                                            placeholder="Observaciones">
                                    </td>
                                </tr>

                                <thead>
                                    <tr>
                                        <th colspan="3" style="background-color: #233E99; color: white;">Zona
                                            Administrativa</th>
                                    </tr>
                                </thead>

                                <tr>
                                    <td>Módulo de Oficina</td>
                                    <td>
                                        <input type="hidden" name="ModuloOficina" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="ModuloOficinaCheckbox"
                                                name="ModuloOficina" value="1"
                                                {{ $verificacion->ModuloOficina ? 'checked' : '' }}>
                                            <label class="form-check-label" for="ModuloOficinaCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="observaciones_ModuloOficina"
                                            value="{{ $observaciones['ModuloOficina'] ?? '' }}"
                                            placeholder="Observaciones">
                                    </td>
                                </tr>

                                <tr>
                                    <td>Silla de Oficina</td>
                                    <td>
                                        <input type="hidden" name="SillaOficina" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="SillaOficinaCheckbox"
                                                name="SillaOficina" value="1"
                                                {{ $verificacion->SillaOficina ? 'checked' : '' }}>
                                            <label class="form-check-label" for="SillaOficinaCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="observaciones_SillaOficina"
                                            value="{{ $observaciones['SillaOficina'] ?? '' }}"
                                            placeholder="Observaciones">
                                    </td>
                                </tr>

                                <tr>
                                    <td>Insumo de Oficina</td>
                                    <td>
                                        <input type="hidden" name="InsumoOficina" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="InsumoOficinaCheckbox"
                                                name="InsumoOficina" value="1"
                                                {{ $verificacion->InsumoOficina ? 'checked' : '' }}>
                                            <label class="form-check-label" for="InsumoOficinaCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="observaciones_InsumoOficina"
                                            value="{{ $observaciones['InsumoOficina'] ?? '' }}"
                                            placeholder="Observaciones">
                                    </td>
                                </tr>

                                <thead>
                                    <tr>
                                        <th colspan="3" style="background-color: #233E99; color: white;">Generales</th>
                                    </tr>
                                </thead>

                                <tr>
                                    <td>Sistema de Iluminación</td>
                                    <td>
                                        <input type="hidden" name="SistemaIluminaria" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                id="SistemaIluminariaCheckbox" name="SistemaIluminaria" value="1"
                                                {{ $verificacion->SistemaIluminaria ? 'checked' : '' }}>
                                            <label class="form-check-label" for="SistemaIluminariaCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="observaciones_SistemaIluminaria"
                                            value="{{ $observaciones['SistemaIluminaria'] ?? '' }}"
                                            placeholder="Observaciones">
                                    </td>
                                </tr>

                                <tr>
                                    <td>Orden y Limpieza</td>
                                    <td>
                                        <input type="hidden" name="OrdenLimpieza" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="OrdenLimpiezaCheckbox"
                                                name="OrdenLimpieza" value="1"
                                                {{ $verificacion->OrdenLimpieza ? 'checked' : '' }}>
                                            <label class="form-check-label" for="OrdenLimpiezaCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="observaciones_OrdenLimpieza"
                                            value="{{ $observaciones['OrdenLimpieza'] ?? '' }}"
                                            placeholder="Observaciones">
                                    </td>
                                </tr>

                                <tr>
                                    <td>Señaléticas</td>
                                    <td>
                                        <input type="hidden" name="Senialeticas" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="SenialeticasCheckbox"
                                                name="Senialeticas" value="1"
                                                {{ $verificacion->Senialeticas ? 'checked' : '' }}>
                                            <label class="form-check-label" for="SenialeticasCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="observaciones_Senialeticas"
                                            value="{{ $observaciones['Senialeticas'] ?? '' }}"
                                            placeholder="Observaciones">
                                    </td>
                                </tr>

                                <tr>
                                    <td>Equipo de Aire Acondicionado</td>
                                    <td>
                                        <input type="hidden" name="EquipoAireAcondicionado" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                id="EquipoAireAcondicionadoCheckbox" name="EquipoAireAcondicionado"
                                                value="1"
                                                {{ $verificacion->EquipoAireAcondicionado ? 'checked' : '' }}>
                                            <label class="form-check-label"
                                                for="EquipoAireAcondicionadoCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control"
                                            name="observaciones_EquipoAireAcondicionado"
                                            value="{{ $observaciones['EquipoAireAcondicionado'] ?? '' }}"
                                            placeholder="Observaciones">
                                    </td>
                                </tr>

                                <tr>
                                    <td>Servicios Higiénicos</td>
                                    <td>
                                        <input type="hidden" name="ServiciosHigienicos" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                id="ServiciosHigienicosCheckbox" name="ServiciosHigienicos"
                                                value="1" {{ $verificacion->ServiciosHigienicos ? 'checked' : '' }}>
                                            <label class="form-check-label" for="ServiciosHigienicosCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control"
                                            name="observaciones_ServiciosHigienicos"
                                            value="{{ $observaciones['ServiciosHigienicos'] ?? '' }}"
                                            placeholder="Observaciones">
                                    </td>
                                </tr>

                                <tr>
                                    <td>Comedor</td>
                                    <td>
                                        <input type="hidden" name="Comedor" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="ComedorCheckbox"
                                                name="Comedor" value="1"
                                                {{ $verificacion->Comedor ? 'checked' : '' }}>
                                            <label class="form-check-label" for="ComedorCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="observaciones_Comedor"
                                            value="{{ $observaciones['Comedor'] ?? '' }}" placeholder="Observaciones">
                                    </td>
                                </tr>

                                <thead>
                                    <tr>
                                        <th colspan="3" style="background-color: #233E99; color: white;">Servicios TIC
                                        </th>
                                    </tr>
                                </thead>

                                <tr>
                                    <td>Internet</td>
                                    <td>
                                        <input type="hidden" name="Internet" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="InternetCheckbox"
                                                name="Internet" value="1"
                                                {{ $verificacion->Internet ? 'checked' : '' }}>
                                            <label class="form-check-label" for="InternetCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="observaciones_Internet"
                                            value="{{ $observaciones['Internet'] ?? '' }}" placeholder="Observaciones">
                                    </td>
                                </tr>

                                <tr>
                                    <td>Sistemas de Colas</td>
                                    <td>
                                        <input type="hidden" name="SistemasColas" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="SistemasColasCheckbox"
                                                name="SistemasColas" value="1"
                                                {{ $verificacion->SistemasColas ? 'checked' : '' }}>
                                            <label class="form-check-label" for="SistemasColasCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="observaciones_SistemasColas"
                                            value="{{ $observaciones['SistemasColas'] ?? '' }}"
                                            placeholder="Observaciones">
                                    </td>
                                </tr>

                                <tr>
                                    <td>Sistema de Citas</td>
                                    <td>
                                        <input type="hidden" name="SistemaDeCitas" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="SistemaDeCitasCheckbox"
                                                name="SistemaDeCitas" value="1"
                                                {{ $verificacion->SistemaDeCitas ? 'checked' : '' }}>
                                            <label class="form-check-label" for="SistemaDeCitasCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="observaciones_SistemaDeCitas"
                                            value="{{ $observaciones['SistemaDeCitas'] ?? '' }}"
                                            placeholder="Observaciones">
                                    </td>
                                </tr>

                                <tr>
                                    <td>Sistema de Audio</td>
                                    <td>
                                        <input type="hidden" name="SistemaAudio" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="SistemaAudioCheckbox"
                                                name="SistemaAudio" value="1"
                                                {{ $verificacion->SistemaAudio ? 'checked' : '' }}>
                                            <label class="form-check-label" for="SistemaAudioCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="observaciones_SistemaAudio"
                                            value="{{ $observaciones['SistemaAudio'] ?? '' }}"
                                            placeholder="Observaciones">
                                    </td>
                                </tr>

                                <tr>
                                    <td>Sistema de Videovigilancia</td>
                                    <td>
                                        <input type="hidden" name="SistemaVideovigilancia" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                id="SistemaVideovigilanciaCheckbox" name="SistemaVideovigilancia"
                                                value="1"
                                                {{ $verificacion->SistemaVideovigilancia ? 'checked' : '' }}>
                                            <label class="form-check-label"
                                                for="SistemaVideovigilanciaCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control"
                                            name="observaciones_SistemaVideovigilancia"
                                            value="{{ $observaciones['SistemaVideovigilancia'] ?? '' }}"
                                            placeholder="Observaciones">
                                    </td>
                                </tr>

                                <tr>
                                    <td>Correo Electrónico</td>
                                    <td>
                                        <input type="hidden" name="CorreoElectronico" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                id="CorreoElectronicoCheckbox" name="CorreoElectronico" value="1"
                                                {{ $verificacion->CorreoElectronico ? 'checked' : '' }}>
                                            <label class="form-check-label" for="CorreoElectronicoCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="observaciones_CorreoElectronico"
                                            value="{{ $observaciones['CorreoElectronico'] ?? '' }}"
                                            placeholder="Observaciones">
                                    </td>
                                </tr>

                                <tr>
                                    <td>Active Directory</td>
                                    <td>
                                        <input type="hidden" name="ActiveDirectory" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="ActiveDirectoryCheckbox"
                                                name="ActiveDirectory" value="1"
                                                {{ $verificacion->ActiveDirectory ? 'checked' : '' }}>
                                            <label class="form-check-label" for="ActiveDirectoryCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="observaciones_ActiveDirectory"
                                            value="{{ $observaciones['ActiveDirectory'] ?? '' }}"
                                            placeholder="Observaciones">
                                    </td>
                                </tr>

                                <tr>
                                    <td>File Server</td>
                                    <td>
                                        <input type="hidden" name="FileServer" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="FileServerCheckbox"
                                                name="FileServer" value="1"
                                                {{ $verificacion->FileServer ? 'checked' : '' }}>
                                            <label class="form-check-label" for="FileServerCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="observaciones_FileServer"
                                            value="{{ $observaciones['FileServer'] ?? '' }}" placeholder="Observaciones">
                                    </td>
                                </tr>

                                <tr>
                                    <td>Antivirus</td>
                                    <td>
                                        <input type="hidden" name="Antivirus" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="AntivirusCheckbox"
                                                name="Antivirus" value="1"
                                                {{ $verificacion->Antivirus ? 'checked' : '' }}>
                                            <label class="form-check-label" for="AntivirusCheckbox">Sí</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="observaciones_Antivirus"
                                            value="{{ $observaciones['Antivirus'] ?? '' }}" placeholder="Observaciones">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('Script/js/sweet-alert.min.js') }}"></script>
    <script src="{{ asset('Vendor/toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('//cdn.jsdelivr.net/npm/sweetalert2@11') }}"></script>
    <!-- Plugins js -->
    <script src="{{ asset('nuevo/plugins/select2/select2.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/timepicker/bootstrap-material-datetimepicker.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Muestra el modal automáticamente si hay errores de validación
            @if ($errors->any())
                $('#validationErrorModal').modal('show');
            @endif
        });
    </script>
@endsection
