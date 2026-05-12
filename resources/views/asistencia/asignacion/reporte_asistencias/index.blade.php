@extends('layouts.layout')

@section('style')
    <link href="{{ asset('nuevo/plugins/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .reporte-header {
            background-color: #132842;
        }

        .reporte-actions {
            display: flex;
            gap: .5rem;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .reporte-metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: .75rem;
            margin-bottom: 1rem;
        }

        .reporte-metric {
            background: #fff;
            border: 1px solid #d7dde8;
            border-left: 4px solid #132842;
            border-radius: 6px;
            min-height: 74px;
            padding: .75rem;
        }

        .reporte-metric span {
            color: #667085;
            display: block;
            font-size: 12px;
            text-transform: uppercase;
        }

        .reporte-metric strong {
            color: #132842;
            display: block;
            font-size: 20px;
            line-height: 1.2;
            margin-top: .2rem;
        }

        .table-asistencia th,
        .table-asistencia td {
            vertical-align: middle;
            white-space: nowrap;
        }

        .table-asistencia .cell-personal,
        .table-asistencia .cell-observacion {
            min-width: 220px;
            white-space: normal;
        }

        .text-primary-strong {
            color: #132842;
            font-weight: 700;
        }

        @media (max-width: 576px) {
            .reporte-actions .btn {
                width: 100%;
            }
        }
    </style>
@endsection

@section('main')
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="row">
                    <div class="col">
                        <h4 class="page-title">Reporte de asistencia</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('inicio') }}">
                                    <i data-feather="home" class="align-self-center" style="height: 70%; display: block;"></i>
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('asistencia.asignacion.index') }}">Asignacion</a>
                            </li>
                            <li class="breadcrumb-item">Control de asistencia</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header reporte-header">
            <h4 class="card-title text-white mb-0">Filtros del reporte</h4>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('asistencia.asignacion.reporte_asistencias.index') }}" id="formReporteAsistencias">
                <div class="row align-items-end">
                    @hasanyrole('Administrador|Moderador')
                        <div class="col-md-4 col-lg-3">
                            <label class="mb-2 fw-semibold">Centro MAC</label>
                            <select name="mac" id="mac" class="form-control select2">
                                @foreach ($macs as $mac)
                                    <option value="{{ $mac->id }}" {{ (int) $idmac === (int) $mac->id ? 'selected' : '' }}>
                                        {{ $mac->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="mac" id="mac" value="{{ $idmac }}">
                        <div class="col-md-4 col-lg-3">
                            <label class="mb-2 fw-semibold">Centro MAC</label>
                            <input type="text" class="form-control" value="{{ $name_mac }}" readonly>
                        </div>
                    @endhasanyrole

                    <div class="col-md-4 col-lg-3">
                        <label class="mb-2 fw-semibold">Personal</label>
                        <select name="idpersonal" class="form-control select2">
                            <option value="">Todas las personas</option>
                            @foreach ($personalAsignado as $persona)
                                <option value="{{ $persona->IDPERSONAL }}" {{ (int) $idpersonal === (int) $persona->IDPERSONAL ? 'selected' : '' }}>
                                    {{ $persona->NUM_DOC }} - {{ $persona->nombre_completo }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 col-lg-2">
                        <label class="mb-2 fw-semibold">Desde</label>
                        <input type="date" name="fecha_inicio" class="form-control" value="{{ $fechaInicio }}">
                    </div>

                    <div class="col-md-4 col-lg-2">
                        <label class="mb-2 fw-semibold">Hasta</label>
                        <input type="date" name="fecha_fin" class="form-control" value="{{ $fechaFin }}">
                    </div>

                    <div class="col-md-8 col-lg-2">
                        <div class="reporte-actions mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-search"></i> Buscar
                            </button>
                            <button type="button" class="btn btn-success" onclick="exportarReporteAsistencias()">
                                <i class="fa fa-file-excel-o"></i> Excel
                            </button>
                            <a href="{{ route('asistencia.asignacion.index') }}" class="btn btn-dark">
                                <i class="fa fa-arrow-left"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="reporte-metrics">
        <div class="reporte-metric">
            <span>Registros</span>
            <strong>{{ $summary['registros'] }}</strong>
        </div>
        <div class="reporte-metric">
            <span>Personas</span>
            <strong>{{ $summary['personas'] }}</strong>
        </div>
        <div class="reporte-metric">
            <span>Horas programadas</span>
            <strong>{{ $summary['horas_programadas'] }}</strong>
        </div>
        <div class="reporte-metric">
            <span>Horas trabajadas</span>
            <strong>{{ $summary['horas_trabajadas'] }}</strong>
        </div>
        <div class="reporte-metric">
            <span>Sin registro</span>
            <strong>{{ $summary['sin_registro'] }}</strong>
        </div>
        <div class="reporte-metric">
            <span>Sin salida</span>
            <strong>{{ $summary['sin_salida'] }}</strong>
        </div>
        <div class="reporte-metric">
            <span>Con permiso</span>
            <strong>{{ $summary['con_permiso'] }}</strong>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header reporte-header">
            <h4 class="card-title text-white mb-0">Control de asistencia</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover table-bordered table-striped table-asistencia" id="table_reporte_asistencias">
                    <thead class="tenca">
                        <tr>
                            <th>DNI</th>
                            <th>Apellidos y nombres</th>
                            <th>Reg. laboral</th>
                            <th>Centro MAC</th>
                            <th>Cargo</th>
                            <th>Dia</th>
                            <th>Fecha</th>
                            <th>Ingreso prog.</th>
                            <th>Ingreso reg.</th>
                            <th>Salida prog.</th>
                            <th>Salida reg.</th>
                            <th>H. prog.</th>
                            <th>H. trab.</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr class="{{ $row->observaciones ? 'table-warning' : '' }}">
                                <td>{{ $row->dni }}</td>
                                <td class="cell-personal text-uppercase text-primary-strong">{{ $row->nombre_completo }}</td>
                                <td>{{ $row->regimen_laboral }}</td>
                                <td>{{ $row->centro_mac }}</td>
                                <td>{{ $row->cargo }}</td>
                                <td>{{ $row->dia }}</td>
                                <td data-order="{{ $row->fecha }}">{{ $row->fecha_excel }}</td>
                                <td>{{ $row->ingreso_programado }}</td>
                                <td>{{ $row->ingreso_real ?: '-' }}</td>
                                <td>{{ $row->salida_programada }}</td>
                                <td>{{ $row->salida_real ?: '-' }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $row->horas_programadas }}</span></td>
                                <td><span class="badge bg-primary">{{ $row->horas_trabajadas }}</span></td>
                                <td class="cell-observacion">{{ $row->observaciones ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('nuevo/plugins/select2/select2.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                width: '100%'
            });

            $('#table_reporte_asistencias').DataTable({
                destroy: true,
                pageLength: 25,
                autoWidth: false,
                order: [[6, 'asc'], [1, 'asc']],
                language: {
                    url: "{{ asset('js/Spanish.json') }}"
                }
            });
        });

        function exportarReporteAsistencias() {
            const params = $('#formReporteAsistencias').serialize();
            window.location.href = "{{ route('asistencia.asignacion.reporte_asistencias.export') }}" + '?' + params;
        }
    </script>
@endsection
