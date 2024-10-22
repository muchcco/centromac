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
    <style>
        .header-color {
            background-color: #1C3C9A !important;
            color: white !important;
            border-color: #1C3C9A;
        }

        .background-blue {
            background-color: #32B0E4 !important;
            border-color: #32B0E4 !important;
        }

        .custom-border th,
        .custom-border td {
            border-width: 3px !important;
        }
    </style>
@endsection

@section('main')
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <div class="row">
                        <div class="col">
                            <h4 class="page-title">Vista de Check List Operativo</h4>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('inicio') }}">
                                        <i data-feather="home" class="align-self-center"
                                            style="height: 70%; display: block;"></i>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('verificaciones.index') }}" style="color: #7081b9;">Verificaciones</a>
                                    <!-- Cambiado para que redirija a index -->
                                </li>
                            </ol>
                        </div><!--end col-->

                        <div class="d-flex justify-content-between mb-4">
                            <div>
                            </div>
                            <div>
                                <a href="{{ route('verificaciones.index') }}"
                                    class="btn btn-secondary d-flex align-items-center">
                                    <i class="fa fa-arrow-left me-2"></i> <!-- Icono de flecha hacia la izquierda -->
                                    Volver al Listado
                                </a>
                            </div>
                        </div>

                    </div><!--end row-->
                </div><!--end page-title-box-->
            </div><!--end col-->
        </div><!--end row-->

        <div class="mb-4">
            <table class="table table-bordered table-font-size custom-border">
                <tbody>
                    <tr>
                        <td class="col-4 text-center" rowspan="2">
                            <img src="http://www.mac.pe/wp-content/themes/bridge/images/alomac.png" alt="">
                        </td>
                        <td class="col-8 text-center fw-bold fs-4" colspan="8">FORMATO</td>
                    </tr>
                    <tr>
                        <td class="col-8 text-center fw-bold fs-4" colspan="8">VERIFICACIÓN DIARIA DE OPERACIONES DEL
                            CENTRO MAC HUÁNUCO</td>
                    </tr>
                </tbody>
            </table>
            <table class="table table-bordered table-font-size custom-border">
                <tbody>
                    <tr>
                        <td class="col-2"><strong>Fecha de inspección</strong></td>
                        <td class="col-2">{{ $fechaCarbon->format('d/m/Y') }}</td>
                        <td class="col-9"
                            style="border-right-style: hidden; border-top-style: hidden; border-bottom-style: hidden;"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <table class="table table-bordered table-font-size custom-border">
            <thead style="background-color:white !important;">
                <tr>
                    <th class="header-color text-center">N°</th>
                    <th class="header-color col-2 text-center">Condición Operativa</th>
                    <th class="header-color col-3 text-center" colspan="2">Apertura</th>
                    <th class="header-color col-3 text-center" colspan="2">Relevo</th> <!-- Nueva columna para Relevo -->
                    <th class="header-color col-3 text-center" colspan="2">Cierre</th>
                </tr>
                <tr>
                    <td colspan="2" rowspan="3" class="text-center align-middle">Verifica si el recurso u otro, está
                        dañado, roto, depósito, inoperativo o funcionando inadecuadamente según aplique</td>
                    <td>Hora de registro</td>
                    <td>8:15</td>
                    <td>Hora de registro</td>
                    <td>15:00</td> <!-- Hora para el Relevo -->
                    <td>Hora de registro</td>
                    <td>17:15</td>
                </tr>
                <tr>
                    <td>Supervisor</td>
                    <td>
                        @foreach ($verificaciones as $verificacion)
                            @if ($verificacion->AperturaCierre == 0)
                                {{ $verificacion->user->name ?? 'No asignado' }}
                                <!-- Aquí se muestra el nombre del usuario -->
                            @endif
                        @endforeach
                    </td>
                    <td>Supervisor</td>
                    <td>
                        @foreach ($verificaciones as $verificacion)
                            @if ($verificacion->AperturaCierre == 1)
                                {{ $verificacion->user->name ?? 'No asignado' }} <!-- Nombre para Relevo -->
                            @endif
                        @endforeach
                    </td>
                    <td>Supervisor</td>
                    <td>
                        @foreach ($verificaciones as $verificacion)
                            @if ($verificacion->AperturaCierre == 2)
                                {{ $verificacion->user->name ?? 'No asignado' }} <!-- Nombre para Cierre -->
                            @endif
                        @endforeach
                    </td>
                </tr>

                <tr>
                    <td>Firma</td>
                    <td></td>
                    <td>Firma</td>
                    <td></td>
                    <td>Firma</td> <!-- Firma para Relevo -->
                    <td></td>
                </tr>
                <tr>
                    <td class="background-blue"></td>
                    <td class="background-blue"><strong>Zona de Recepción</strong></td>
                    <td class="background-blue"><strong>¿Conforme?</strong></td>
                    <td class="background-blue"><strong>Observación</strong></td>
                    <td class="background-blue"><strong>¿Conforme?</strong></td>
                    <td class="background-blue"><strong>Observación</strong></td>
                    <td class="background-blue"><strong>¿Conforme?</strong></td> <!-- Nueva columna -->
                    <td class="background-blue"><strong>Observación</strong></td> <!-- Nueva columna -->
                </tr>
            </thead>
            <tbody>
                @php
                    $contador = 1; // Inicializamos el contador
                    $zonas = [
                        9 => 'Zona de Atención',
                        17 => 'Zona Administrativa',
                        20 => 'Generales',
                        26 => 'Servicios TIC',
                    ];
                @endphp
                @foreach ($campos as $campo => $nombreCompleto)
                    @if (array_key_exists($contador, $zonas))
                        <tr>
                            <td class="background-blue"></td>
                            <td class="background-blue"><strong>{{ $zonas[$contador] }}</strong></td>
                            <td class="background-blue"></td>
                            <td class="background-blue"></td>
                            <td class="background-blue"></td>
                            <td class="background-blue"></td>
                            <td class="background-blue"></td>
                            <td class="background-blue"></td> <!-- Nueva celda -->
                        </tr>
                    @endif
                    <tr>
                        <td class="custom-padding-font text-center">{{ $contador }}</td>
                        <td class="custom-padding-font col-1">{{ $nombreCompleto }}</td>

                        <td class="custom-padding-font text-center">
                            @foreach ($verificaciones as $verificacion)
                                @if ($verificacion->AperturaCierre == 0)
                                    {{ $verificacion->$campo == 1 ? 'OK' : ($verificacion->$campo == 0 ? 'Falta' : '-') }}<br>
                                @endif
                            @endforeach
                        </td>
                        <td class="custom-padding-font col-3">
                            {{ $observacionesApertura[$campo] ?? '' }}
                        </td>

                        <td class="custom-padding-font text-center">
                            @foreach ($verificaciones as $verificacion)
                                @if ($verificacion->AperturaCierre == 1)
                                    <!-- Para Relevo -->
                                    {{ $verificacion->$campo == 1 ? 'OK' : ($verificacion->$campo == 0 ? 'Falta' : '-') }}<br>
                                @endif
                            @endforeach
                        </td>
                        <td class="custom-padding-font col-3">
                            {{ $observacionesRelevo[$campo] ?? '' }} <!-- Nueva celda para Observaciones de Relevo -->
                        </td>

                        <td class="custom-padding-font text-center">
                            @foreach ($verificaciones as $verificacion)
                                @if ($verificacion->AperturaCierre == 2)
                                    {{ $verificacion->$campo == 1 ? 'OK' : ($verificacion->$campo == 0 ? 'Falta' : '-') }}<br>
                                @endif
                            @endforeach
                        </td>
                        <td class="custom-padding-font col-3">
                            {{ $observacionesCierre[$campo] ?? '' }}
                        </td>
                    </tr>
                    @php
                        $contador++; // Incrementamos el contador en cada iteración
                    @endphp
                @endforeach
            </tbody>
        </table>

    </div>
@endsection

@section('script')
    <!-- Enlaces a jQuery y DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <!-- Script para inicializar DataTables -->
@endsection
