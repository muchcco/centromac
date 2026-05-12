@extends('layouts.layout')

@section('style')
    <link href="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .mi-card-header {
            background-color: #132842;
        }

        .mi-metrics {
            display: grid;
            grid-template-columns: repeat(4, minmax(150px, 1fr));
            gap: .75rem;
            margin-bottom: 1rem;
        }

        .mi-metric {
            border: 1px solid #d7dde8;
            border-left: 4px solid #132842;
            border-radius: 6px;
            padding: .85rem;
            background: #fff;
            min-height: 82px;
        }

        .mi-metric span {
            display: block;
            color: #667085;
            font-size: 12px;
            text-transform: uppercase;
        }

        .mi-metric strong {
            color: #132842;
            font-size: 22px;
            line-height: 1.2;
        }

        .mi-actions {
            display: flex;
            gap: .5rem;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .mi-source-row {
            border: 1px solid #d7dde8;
            border-radius: 6px;
            padding: .65rem .75rem;
            margin-bottom: .5rem;
            cursor: pointer;
            background: #fff;
        }

        .mi-source-row:has(input:checked) {
            border-color: #198754;
            background: #edf8f1;
        }

        .mi-source-list {
            max-height: 420px;
            overflow: auto;
        }

        .mi-table-compact th,
        .mi-table-compact td {
            vertical-align: middle;
            white-space: nowrap;
        }

        .mi-table-compact .mi-cell-wrap {
            min-width: 210px;
            white-space: normal;
        }

        .mi-person-line {
            color: #132842;
            font-weight: 700;
            line-height: 1.25;
        }

        .mi-muted-line {
            color: #667085;
            display: block;
            font-size: 12px;
            line-height: 1.3;
            margin-top: .15rem;
        }

        .mi-icon-btn {
            align-items: center;
            display: inline-flex;
            gap: .35rem;
            justify-content: center;
            min-width: 96px;
        }

        .mi-detail-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(120px, 1fr));
            gap: .75rem;
            margin-bottom: 1rem;
        }

        .mi-detail-item {
            background: #f8fafc;
            border: 1px solid #d7dde8;
            border-radius: 6px;
            padding: .75rem .85rem;
        }

        .mi-detail-item span {
            color: #667085;
            display: block;
            font-size: 12px;
            text-transform: uppercase;
        }

        .mi-detail-item strong {
            color: #132842;
            display: block;
            font-size: 16px;
            line-height: 1.3;
            margin-top: .2rem;
        }

        @media (max-width: 992px) {
            .mi-metrics {
                grid-template-columns: repeat(2, minmax(150px, 1fr));
            }

            .mi-detail-grid {
                grid-template-columns: repeat(2, minmax(120px, 1fr));
            }
        }

        @media (max-width: 576px) {
            .mi-metrics {
                grid-template-columns: 1fr;
            }

            .mi-detail-grid {
                grid-template-columns: 1fr;
            }

            .mi-actions .btn {
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
                        <h4 class="page-title">Mi asistencia</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('inicio') }}">
                                    <i data-feather="home" class="align-self-center" style="height: 70%; display: block;"></i>
                                </a>
                            </li>
                            <li class="breadcrumb-item">Horas compensables</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (!$tablaDisponible)
        <div class="alert alert-warning">
            Falta crear la tabla <strong>d_personal_asistencia_consumo</strong>. La vista puede mostrar horas, pero no registrar solicitudes.
        </div>
    @endif

    @if ($tablaDisponible && !$tablaDetalleDisponible)
        <div class="alert alert-warning">
            Falta crear la tabla <strong>d_personal_asistencia_consumo_det</strong>. Es necesaria para elegir de que dias se consumen las horas.
        </div>
    @endif

    @if (!$personal)
        <div class="alert alert-danger">
            Su usuario no esta vinculado a un registro de personal. Revise el campo <strong>users.idpersonal</strong>.
        </div>
    @endif

    @php
        $puedeRegistrar = $tablaDisponible && $tablaDetalleDisponible && $personal && $fuentesDisponibles->isNotEmpty();
        $detalleHorasModal = $solicitudes
            ->concat($solicitudesRevision)
            ->unique('id')
            ->mapWithKeys(function ($s) use ($detalleSolicitudes) {
                $detalles = ($detalleSolicitudes[$s->id] ?? collect())
                    ->map(function ($det) {
                        return [
                            'fecha' => date('d-m-Y', strtotime($det->fecha_origen)),
                            'generadas' => sprintf('%02d:%02d', intdiv((int) $det->minutos_generados, 60), (int) $det->minutos_generados % 60),
                            'usadas' => sprintf('%02d:%02d', intdiv((int) $det->minutos_usados, 60), (int) $det->minutos_usados % 60),
                        ];
                    })
                    ->values();

                return [
                    (string) $s->id => [
                        'personal' => $s->nombre_completo ?? 'Mi solicitud',
                        'documento' => $s->NUM_DOC ?? '',
                        'entidad' => $s->entidad ?? '',
                        'fecha' => date('d-m-Y', strtotime($s->fecha_consumo)),
                        'tipo' => $s->tipo_consumo === 'DIA' ? 'Dia completo' : 'Por horas',
                        'horario' => substr($s->hora_inicio, 0, 5) . ' - ' . substr($s->hora_fin, 0, 5),
                        'horas' => sprintf('%02d:%02d', intdiv((int) $s->minutos_solicitados, 60), (int) $s->minutos_solicitados % 60),
                        'estado' => $s->estado,
                        'motivo' => $s->motivo ?? '',
                        'observacion_solicitud' => $s->observacion ?? '',
                        'validador' => $s->validador ?? '',
                        'observacion_validador' => $s->observacion_validador ?? '',
                        'detalles' => $detalles,
                    ],
                ];
            });
    @endphp

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-header mi-card-header">
                    <h4 class="card-title text-white mb-0">Filtro</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('miasistencia.index') }}">
                        <div class="row align-items-end">
                            @hasanyrole('Administrador|Moderador')
                                <div class="col-md-3">
                                    <label class="mb-2 fw-semibold">Centro MAC</label>
                                    <select class="form-control" name="mac" id="mac">
                                        @foreach ($macs as $mac)
                                            <option value="{{ $mac->id }}" {{ (int) $idmac === (int) $mac->id ? 'selected' : '' }}>
                                                {{ $mac->nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endhasanyrole

                            <div class="col-md-3">
                                <label class="mb-2 fw-semibold">Desde</label>
                                <input type="date" name="fecha_inicio" class="form-control" value="{{ $fechaInicio }}">
                            </div>
                            <div class="col-md-3">
                                <label class="mb-2 fw-semibold">Hasta</label>
                                <input type="date" name="fecha_fin" class="form-control" value="{{ $fechaFin }}">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary mt-3">
                                    <i class="fa fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="mi-metrics">
        <div class="mi-metric">
            <span>Generadas en rango</span>
            <strong>{{ $summary['generadas_rango'] }}</strong>
        </div>
        <div class="mi-metric">
            <span>Total generadas</span>
            <strong>{{ $summary['generadas_total'] }}</strong>
        </div>
        <div class="mi-metric">
            <span>Pendiente/aprobado</span>
            <strong>{{ $summary['comprometidas'] }}</strong>
        </div>
        <div class="mi-metric">
            <span>Saldo disponible</span>
            <strong>{{ $summary['saldo'] }}</strong>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header mi-card-header">
                    <h4 class="card-title text-white mb-0">Solicitar consumo</h4>
                </div>
                <div class="card-body">
                    <form id="formMiAsistencia" enctype="multipart/form-data">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tipo</label>
                            <select name="tipo_consumo" id="tipo_consumo" class="form-control" {{ !$puedeRegistrar ? 'disabled' : '' }}>
                                <option value="HORAS">Por horas</option>
                                <option value="DIA">Por dia completo</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Fecha a compensar</label>
                            <input type="date" name="fecha_consumo" class="form-control" {{ !$puedeRegistrar ? 'disabled' : '' }}>
                        </div>

                        <div id="bloque_horas" class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Hora inicio</label>
                                <input type="time" name="hora_inicio" class="form-control" value="08:15" {{ !$puedeRegistrar ? 'disabled' : '' }}>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Hora fin</label>
                                <input type="time" name="hora_fin" class="form-control" value="10:15" {{ !$puedeRegistrar ? 'disabled' : '' }}>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Dias origen a consumir</label>
                            <div id="fechas_origen_inputs"></div>
                            <button type="button" class="btn btn-outline-primary w-100" onclick="abrirModalFuentesOrigen()"
                                {{ !$puedeRegistrar ? 'disabled' : '' }}>
                                Seleccionar horas disponibles
                            </button>
                            <div class="border rounded p-2 mt-2" id="resumen_fuentes_origen">
                                <span class="text-muted">No hay dias seleccionados.</span>
                            </div>
                            <small class="text-muted">
                                Solo se muestran horas disponibles de los ultimos 3 meses. Seleccionado:
                                <strong id="total_fuente_seleccionada">00:00</strong>
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Motivo</label>
                            <select name="motivo" class="form-control" {{ !$puedeRegistrar ? 'disabled' : '' }} required>
                                <option value="">Seleccione un motivo</option>
                                @foreach ($motivosConsumo as $motivo)
                                    <option value="{{ $motivo }}">{{ $motivo }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Observacion</label>
                            <textarea name="observacion" class="form-control" rows="3" maxlength="1500"
                                placeholder="Detalle breve para sustentar la solicitud" {{ !$puedeRegistrar ? 'disabled' : '' }}></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Evidencia PDF</label>
                            <input type="file" name="evidencia" class="form-control" accept="application/pdf" {{ !$puedeRegistrar ? 'disabled' : '' }}>
                        </div>

                        <button type="button" id="btnGuardarMiAsistencia" class="btn btn-success w-100"
                            onclick="guardarMiAsistencia()" {{ !$puedeRegistrar ? 'disabled' : '' }}>
                            Registrar solicitud
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header mi-card-header">
                    <h4 class="card-title text-white mb-0">Mis solicitudes</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-bordered table-striped mi-table-compact" id="table_mis_solicitudes">
                            <thead class="tenca">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Horas</th>
                                    <th>Estado</th>
                                    <th>Detalle</th>
                                    <th>Evidencia</th>
                                    <th>Validador</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($solicitudes as $s)
                                    <tr>
                                        <td data-order="{{ $s->fecha_consumo }}">{{ date('d-m-Y', strtotime($s->fecha_consumo)) }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                {{ $s->tipo_consumo === 'DIA' ? 'Dia' : 'Horas' }}
                                            </span>
                                        </td>
                                        <td><strong>{{ sprintf('%02d:%02d', intdiv((int) $s->minutos_solicitados, 60), (int) $s->minutos_solicitados % 60) }}</strong></td>
                                        <td>
                                            <span class="badge {{ $s->estado === 'APROBADO' ? 'bg-success' : ($s->estado === 'RECHAZADO' ? 'bg-danger' : 'bg-warning') }}">
                                                {{ $s->estado }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-primary mi-icon-btn" onclick="abrirDetalleHoras('{{ $s->id }}')">
                                                <i class="fa fa-clock-o"></i> Ver horas
                                            </button>
                                        </td>
                                        <td>
                                            @if ($s->archivo_evidencia)
                                                <a href="{{ asset($s->archivo_evidencia) }}" target="_blank" class="btn btn-sm btn-outline-secondary mi-icon-btn">
                                                    <i class="fa fa-file-pdf-o"></i> PDF
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $s->validador }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @hasanyrole('Administrador|Moderador')
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-header mi-card-header">
                        <h4 class="card-title text-white mb-0">Solicitudes por validar</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover table-bordered table-striped mi-table-compact" id="table_validacion">
                                <thead class="tenca">
                                    <tr>
                                        <th>Personal</th>
                                        <th>DNI</th>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Horas</th>
                                        <th>Estado</th>
                                        <th>Detalle</th>
                                        <th>Evidencia</th>
                                        <th>Accion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($solicitudesRevision as $s)
                                        <tr>
                                            <td class="mi-cell-wrap">
                                                <div class="mi-person-line">{{ $s->nombre_completo }}</div>
                                                <span class="mi-muted-line">{{ $s->entidad }}</span>
                                            </td>
                                            <td>{{ $s->NUM_DOC }}</td>
                                            <td data-order="{{ $s->fecha_consumo }}">{{ date('d-m-Y', strtotime($s->fecha_consumo)) }}</td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    {{ $s->tipo_consumo === 'DIA' ? 'Dia' : 'Horas' }}
                                                </span>
                                            </td>
                                            <td><strong>{{ sprintf('%02d:%02d', intdiv((int) $s->minutos_solicitados, 60), (int) $s->minutos_solicitados % 60) }}</strong></td>
                                            <td>
                                                <span class="badge {{ $s->estado === 'APROBADO' ? 'bg-success' : ($s->estado === 'RECHAZADO' ? 'bg-danger' : 'bg-warning') }}">
                                                    {{ $s->estado }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-primary mi-icon-btn" onclick="abrirDetalleHoras('{{ $s->id }}')">
                                                    <i class="fa fa-clock-o"></i> Ver horas
                                                </button>
                                            </td>
                                            <td>
                                                @if ($s->archivo_evidencia)
                                                    <a href="{{ asset($s->archivo_evidencia) }}" target="_blank" class="btn btn-sm btn-outline-secondary mi-icon-btn">
                                                        <i class="fa fa-file-pdf-o"></i> PDF
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($s->estado === 'PENDIENTE')
                                                    <button type="button" class="btn btn-sm btn-success" onclick="validarSolicitud('{{ $s->id }}', 'APROBADO')">
                                                        Aprobar
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger" onclick="validarSolicitud('{{ $s->id }}', 'RECHAZADO')">
                                                        Rechazar
                                                    </button>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endhasanyrole

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-header mi-card-header">
                    <h4 class="card-title text-white mb-0">Detalle de horas generadas</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-bordered table-striped mi-table-compact" id="table_horas_generadas">
                            <thead class="tenca">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Programado</th>
                                    <th>Real</th>
                                    <th>Marcaciones</th>
                                    <th>Horas extra</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rowsCompensables as $row)
                                    <tr class="{{ $row->minutos_extra > 0 ? 'table-success' : '' }}">
                                        <td data-order="{{ $row->FECHA }}">{{ date('d-m-Y', strtotime($row->FECHA)) }}</td>
                                        <td>{{ substr($row->ingreso_programado, 0, 5) }} - {{ substr($row->salida_programada, 0, 5) }}</td>
                                        <td>
                                            {{ $row->asistencia_ingreso ? substr($row->asistencia_ingreso, 0, 5) : '-' }}
                                            -
                                            {{ $row->asistencia_salida ? substr($row->asistencia_salida, 0, 5) : '-' }}
                                        </td>
                                        <td>{{ $row->total_marcaciones }}</td>
                                        <td>{{ $row->horas_extra }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetalleHoras" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header mi-card-header">
                    <h5 class="modal-title text-white">Detalle de horas</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="border rounded p-3 mb-3">
                        <div class="mi-person-line" id="detalle_personal">Mi solicitud</div>
                        <span class="mi-muted-line" id="detalle_personal_meta"></span>
                    </div>

                    <div class="mi-detail-grid">
                        <div class="mi-detail-item">
                            <span>Fecha</span>
                            <strong id="detalle_fecha">-</strong>
                        </div>
                        <div class="mi-detail-item">
                            <span>Tipo</span>
                            <strong id="detalle_tipo">-</strong>
                        </div>
                        <div class="mi-detail-item">
                            <span>Horario</span>
                            <strong id="detalle_horario">-</strong>
                        </div>
                        <div class="mi-detail-item">
                            <span>Total usado</span>
                            <strong id="detalle_horas">00:00</strong>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0 mi-table-compact">
                            <thead class="tenca">
                                <tr>
                                    <th>Dia origen</th>
                                    <th>Horas generadas</th>
                                    <th>Horas usadas</th>
                                </tr>
                            </thead>
                            <tbody id="detalle_horas_body">
                                <tr>
                                    <td colspan="3" class="text-muted text-center">Sin detalle registrado.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-semibold">Motivo</label>
                        <div class="border rounded p-2" id="detalle_motivo">-</div>
                    </div>

                    <div class="mt-3 d-none" id="detalle_observacion_solicitud_wrap">
                        <label class="form-label fw-semibold">Observacion del solicitante</label>
                        <div class="border rounded p-2" id="detalle_observacion_solicitud"></div>
                    </div>

                    <div class="mt-3 d-none" id="detalle_observacion_wrap">
                        <label class="form-label fw-semibold">Observacion de validacion</label>
                        <div class="border rounded p-2" id="detalle_observacion"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalFuentesOrigen" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header mi-card-header">
                    <h5 class="modal-title text-white">Horas disponibles para compensar</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h6 class="mb-1 fw-semibold">Ultimos 3 meses</h6>
                            <p class="text-muted mb-0 font-13">Seleccione los dias origen que desea consumir.</p>
                        </div>
                        <div class="text-end">
                            <span class="text-muted d-block font-13">Seleccionado</span>
                            <strong id="total_fuente_modal">00:00</strong>
                        </div>
                    </div>

                    <div class="mi-source-list">
                        @forelse ($fuentesDisponibles as $fuente)
                            <label class="mi-source-row d-flex align-items-center justify-content-between">
                                <span>
                                    <input type="checkbox" class="me-2 fuente-origen-modal"
                                        value="{{ $fuente->fecha_origen }}"
                                        data-minutos="{{ $fuente->minutos_disponibles }}"
                                        data-label="{{ date('d-m-Y', strtotime($fuente->fecha_origen)) }}"
                                        data-horas="{{ $fuente->horas_disponibles }}"
                                        {{ !$puedeRegistrar ? 'disabled' : '' }}>
                                    {{ date('d-m-Y', strtotime($fuente->fecha_origen)) }}
                                </span>
                                <span class="text-end">
                                    <strong>{{ $fuente->horas_disponibles }}</strong>
                                    <small class="text-muted d-block">disp. de {{ $fuente->horas_extra }}</small>
                                </span>
                            </label>
                        @empty
                            <div class="text-muted border rounded p-3">No hay horas disponibles en los ultimos 3 meses.</div>
                        @endforelse
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-success" onclick="aplicarFuentesOrigen()" {{ !$puedeRegistrar ? 'disabled' : '' }}>
                        Usar seleccion
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.js') }}"></script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        const detalleHorasSolicitudes = @json($detalleHorasModal);

        $(document).ready(function() {
            const dataTableBase = {
                destroy: true,
                pageLength: 10,
                autoWidth: false,
                language: {
                    url: "{{ asset('js/Spanish.json') }}"
                }
            };

            if ($('#table_mis_solicitudes').length) {
                $('#table_mis_solicitudes').DataTable($.extend(true, {}, dataTableBase, {
                    order: [[0, 'desc']],
                    columnDefs: [
                        { targets: [4, 5], orderable: false, searchable: false }
                    ]
                }));
            }

            if ($('#table_validacion').length) {
                $('#table_validacion').DataTable($.extend(true, {}, dataTableBase, {
                    order: [[2, 'desc']],
                    columnDefs: [
                        { targets: [6, 7, 8], orderable: false, searchable: false }
                    ]
                }));
            }

            if ($('#table_horas_generadas').length) {
                $('#table_horas_generadas').DataTable($.extend(true, {}, dataTableBase, {
                    order: [[0, 'desc']]
                }));
            }

            $('#tipo_consumo').on('change', function() {
                $('#bloque_horas').toggle($(this).val() === 'HORAS');
            }).trigger('change');

            $('.fuente-origen-modal').on('change', actualizarTotalFuente);
            actualizarTotalFuente();
        });

        function formatoMinutosVista(minutos) {
            minutos = Math.max(0, parseInt(minutos || 0, 10));
            const horas = String(Math.floor(minutos / 60)).padStart(2, '0');
            const resto = String(minutos % 60).padStart(2, '0');

            return `${horas}:${resto}`;
        }

        function escapeHtml(value) {
            return $('<div>').text(value || '').html();
        }

        function abrirDetalleHoras(id) {
            const detalle = detalleHorasSolicitudes[String(id)];

            if (!detalle) {
                Swal.fire('Detalle no disponible', 'No se encontro el detalle de horas para esta solicitud.', 'warning');
                return;
            }

            const meta = [
                detalle.documento ? `DNI ${detalle.documento}` : '',
                detalle.entidad || '',
                detalle.estado ? `Estado: ${detalle.estado}` : ''
            ].filter(Boolean).join(' | ');

            $('#detalle_personal').text(detalle.personal || 'Mi solicitud');
            $('#detalle_personal_meta').text(meta);
            $('#detalle_fecha').text(detalle.fecha || '-');
            $('#detalle_tipo').text(detalle.tipo || '-');
            $('#detalle_horario').text(detalle.horario || '-');
            $('#detalle_horas').text(detalle.horas || '00:00');
            $('#detalle_motivo').text(detalle.motivo || '-');

            let filas = '';
            (detalle.detalles || []).forEach(function(item) {
                filas += `
                    <tr>
                        <td>${escapeHtml(item.fecha)}</td>
                        <td>${escapeHtml(item.generadas)}</td>
                        <td><strong>${escapeHtml(item.usadas)}</strong></td>
                    </tr>
                `;
            });

            $('#detalle_horas_body').html(filas || `
                <tr>
                    <td colspan="3" class="text-muted text-center">Sin detalle registrado.</td>
                </tr>
            `);

            if (detalle.observacion_solicitud) {
                $('#detalle_observacion_solicitud').text(detalle.observacion_solicitud);
                $('#detalle_observacion_solicitud_wrap').removeClass('d-none');
            } else {
                $('#detalle_observacion_solicitud').text('');
                $('#detalle_observacion_solicitud_wrap').addClass('d-none');
            }

            if (detalle.observacion_validador) {
                $('#detalle_observacion').text(detalle.observacion_validador);
                $('#detalle_observacion_wrap').removeClass('d-none');
            } else {
                $('#detalle_observacion').text('');
                $('#detalle_observacion_wrap').addClass('d-none');
            }

            $('#modalDetalleHoras').modal('show');
        }

        function actualizarTotalFuente() {
            let total = 0;
            $('.fuente-origen-modal:checked').each(function() {
                total += parseInt($(this).data('minutos') || 0, 10);
            });
            $('#total_fuente_seleccionada').text(formatoMinutosVista(total));
            $('#total_fuente_modal').text(formatoMinutosVista(total));
        }

        function abrirModalFuentesOrigen() {
            actualizarTotalFuente();
            $('#modalFuentesOrigen').modal('show');
        }

        function aplicarFuentesOrigen() {
            const contenedor = $('#fechas_origen_inputs');
            const resumen = $('#resumen_fuentes_origen');
            let htmlResumen = '';

            contenedor.html('');

            $('.fuente-origen-modal:checked').each(function() {
                const fecha = $(this).val();
                const label = $(this).data('label');
                const horas = $(this).data('horas');

                contenedor.append(`<input type="hidden" name="fechas_origen[]" value="${fecha}">`);
                htmlResumen += `
                    <div class="d-flex justify-content-between border-bottom py-1">
                        <span>${label}</span>
                        <strong>${horas}</strong>
                    </div>
                `;
            });

            resumen.html(htmlResumen || '<span class="text-muted">No hay dias seleccionados.</span>');
            actualizarTotalFuente();
            $('#modalFuentesOrigen').modal('hide');
        }

        function guardarMiAsistencia() {
            const form = document.getElementById('formMiAsistencia');
            const formData = new FormData(form);
            const btn = $('#btnGuardarMiAsistencia');

            if ($('#fechas_origen_inputs input[name="fechas_origen[]"]').length === 0) {
                Swal.fire('Error', 'Seleccione al menos un dia origen para consumir horas.', 'error');
                return;
            }

            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando');

            $.ajax({
                type: 'POST',
                url: "{{ route('miasistencia.store') }}",
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    Swal.fire('OK', res.message, 'success').then(function() {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('Registrar solicitud');
                    Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo registrar la solicitud.', 'error');
                }
            });
        }

        function validarSolicitud(id, estado) {
            Swal.fire({
                title: estado === 'APROBADO' ? 'Aprobar solicitud' : 'Rechazar solicitud',
                input: 'textarea',
                inputLabel: 'Observacion',
                inputPlaceholder: 'Detalle de validacion',
                showCancelButton: true,
                confirmButtonText: estado === 'APROBADO' ? 'Aprobar' : 'Rechazar',
                cancelButtonText: 'Cancelar'
            }).then(function(result) {
                if (!result.isConfirmed) {
                    return;
                }

                $.post("{{ route('miasistencia.validar') }}", {
                    id: id,
                    estado: estado,
                    mac: $('#mac').val(),
                    observacion_validador: result.value
                }).done(function(res) {
                    Swal.fire('OK', res.message, 'success').then(function() {
                        window.location.reload();
                    });
                }).fail(function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo validar la solicitud.', 'error');
                });
            });
        }
    </script>
@endsection
