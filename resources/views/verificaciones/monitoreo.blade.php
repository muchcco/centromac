@extends('layouts.layout')

@section('style')
<style>
    .card {
        border-radius: 10px;
    }

    .card-header {
        font-weight: 600;
    }

    .btn {
        border-radius: 6px;
    }

    .kpi-card {
        border: 0;
        border-radius: 10px;
        color: #fff;
        min-height: 100px;
    }

    .kpi-numero {
        font-size: 28px;
        font-weight: bold;
        line-height: 1;
    }

    .kpi-texto {
        font-size: 12px;
        margin-top: 8px;
        opacity: .95;
    }

    .kpi-total {
        background: #233E99;
    }

    .kpi-completo {
        background: #198754;
    }

    .kpi-falta-apertura {
        background: #dc3545;
    }

    .kpi-falta-cierre {
        background: #fd7e14;
    }

    .kpi-sin-registro {
        background: #6c757d;
    }

    .kpi-feriado {
        background: #495057;
    }

    .tabla-monitoreo {
        white-space: nowrap;
        margin-bottom: 0;
    }

    .tabla-monitoreo thead th {
        background: #233E99;
        color: white;
        text-align: center;
        font-size: 11px;
        vertical-align: middle;
        min-width: 48px;
    }

    .tabla-monitoreo tbody td {
        text-align: center;
        font-size: 11px;
        vertical-align: middle;
        padding: 7px 4px;
    }

    .tabla-monitoreo .col-mac {
        min-width: 230px;
        text-align: left;
    }

    .sticky-nro {
        position: sticky;
        left: 0;
        z-index: 4;
        background: #fff;
    }

    .sticky-mac {
        position: sticky;
        left: 48px;
        z-index: 4;
        background: #fff;
    }

    .tabla-monitoreo thead .sticky-nro {
        background: #233E99;
        z-index: 6;
    }

    .tabla-monitoreo thead .sticky-mac {
        background: #233E99;
        z-index: 6;
    }

    .badge-estado {
        min-width: 30px;
        font-size: 10px;
    }

    .dia-domingo {
        background: #eeeeee !important;
        color: #777 !important;
    }

    .dia-futuro {
        color: #999;
    }

    .estado-feriado {
        background: #e9ecef !important;
        color: #495057 !important;
        font-weight: bold;
        font-size: 10px;
    }

    .estado-domingo {
        background: #f1f3f5 !important;
        color: #8a8f94 !important;
    }

    .estado-futuro {
        color: #adb5bd;
    }

    .leyenda-item {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-right: 16px;
        margin-bottom: 7px;
        font-size: 12px;
    }

    @media print {

        .page-title-box,
        .card-filtro,
        .btn {
            display: none !important;
        }

        .sticky-nro,
        .sticky-mac {
            position: static !important;
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
                    <h4 class="page-title">Monitoreo Mensual de Verificaciones</h4>

                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('inicio') }}">
                                <i data-feather="home" style="height:70%"></i>
                            </a>
                        </li>

                        <li class="breadcrumb-item">
                            <a href="{{ route('verificaciones.index') }}">
                                Verificaciones
                            </a>
                        </li>

                        <li class="breadcrumb-item active">
                            Monitoreo Mensual
                        </li>
                    </ol>
                </div>

                <div class="col-auto">
                    <a href="{{ route('verificaciones.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-3 card-filtro">
    <div class="card-header" style="background-color:#132842">
        <h4 class="card-title text-white mb-0">
            Filtro de Monitoreo
        </h4>
    </div>

    <div class="card-body">
        <form method="GET">
            <div class="row align-items-end">

                <div class="col-md-4">
                    <label class="mb-2 fw-semibold">Mes</label>

                    <input
                        type="month"
                        name="mes"
                        value="{{ $mes }}"
                        class="form-control">
                </div>

                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search"></i> Buscar
                    </button>
                </div>

                <div class="col-md-2 d-grid">
                    <button
                        type="button"
                        class="btn btn-success"
                        onclick="window.print()">
                        <i class="fa fa-print"></i> Imprimir
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

<div class="row mb-3">

    <div class="col-md mb-2">
        <div class="card kpi-card kpi-total">
            <div class="card-body">
                <div class="kpi-numero">{{ $totalMacs }}</div>
                <div class="kpi-texto">Centros MAC</div>
            </div>
        </div>
    </div>

    <div class="col-md mb-2">
        <div class="card kpi-card kpi-completo">
            <div class="card-body">
                <div class="kpi-numero">{{ $resumen['completos'] }}</div>
                <div class="kpi-texto">Días completos</div>
            </div>
        </div>
    </div>

    <div class="col-md mb-2">
        <div class="card kpi-card kpi-falta-apertura">
            <div class="card-body">
                <div class="kpi-numero">{{ $resumen['faltaApertura'] }}</div>
                <div class="kpi-texto">Falta apertura</div>
            </div>
        </div>
    </div>

    <div class="col-md mb-2">
        <div class="card kpi-card kpi-falta-cierre">
            <div class="card-body">
                <div class="kpi-numero">{{ $resumen['faltaCierre'] }}</div>
                <div class="kpi-texto">Falta cierre</div>
            </div>
        </div>
    </div>

    <div class="col-md mb-2">
        <div class="card kpi-card kpi-sin-registro">
            <div class="card-body">
                <div class="kpi-numero">{{ $resumen['sinRegistro'] }}</div>
                <div class="kpi-texto">Sin registro</div>
            </div>
        </div>
    </div>

    <div class="col-md mb-2">
        <div class="card kpi-card kpi-feriado">
            <div class="card-body">
                <div class="kpi-numero">{{ $resumen['feriados'] }}</div>
                <div class="kpi-texto">Feriados / cierres</div>
            </div>
        </div>
    </div>

</div>

<div class="card shadow-sm">
    <div class="card-header" style="background-color:#132842">
        <h4 class="card-title text-white mb-0">
            Estado por Centro MAC -
            {{ $inicioMes->translatedFormat('F Y') }}
        </h4>
    </div>

    <div class="card-body">

        <div class="mb-3">
            <span class="leyenda-item">
                <span class="badge bg-success badge-estado">OK</span>
                Apertura y cierre registrados
            </span>

            <span class="leyenda-item">
                <span class="badge bg-warning text-dark badge-estado">A</span>
                Solo apertura
            </span>

            <span class="leyenda-item">
                <span class="badge bg-info text-dark badge-estado">C</span>
                Solo cierre
            </span>

            <span class="leyenda-item">
                <span class="badge bg-danger badge-estado">F</span>
                Sin registro
            </span>

            <span class="leyenda-item">
                <span class="badge estado-feriado badge-estado">FER</span>
                Feriado o cierre del MAC
            </span>

            <span class="leyenda-item">
                <span class="text-muted">-</span>
                Domingo o fecha futura
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover tabla-monitoreo">
                <thead>
                    <tr>
                        <th class="sticky-nro" style="min-width:48px;">N°</th>
                        <th class="sticky-mac col-mac">Centro MAC</th>

                        @foreach ($dias as $dia)
                        @php
                        $fechaDia = $inicioMes->copy()->day($dia);
                        $esDomingo = $fechaDia->isSunday();
                        @endphp

                        <th class="{{ $esDomingo ? 'dia-domingo' : '' }}">
                            <div>{{ $dia }}</div>
                            <small>
                                {{ strtoupper(substr($fechaDia->translatedFormat('D'), 0, 3)) }}
                            </small>
                        </th>
                        @endforeach

                        <th style="min-width:80px;">Ver</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($macs as $indice => $mac)
                    <tr>
                        <td class="sticky-nro">
                            {{ $indice + 1 }}
                        </td>

                        <td class="sticky-mac col-mac">
                            <strong>{{ $mac->name_mac }}</strong>
                        </td>

                        @foreach ($dias as $dia)
                        @php
                        $celda = $matriz[$mac->idmac][$dia];
                        @endphp

                        <td class="
                                    {{ $celda['es_feriado'] ? 'estado-feriado' : '' }}
                                    {{ $celda['es_domingo'] ? 'estado-domingo' : '' }}
                                ">
                            @if ($celda['es_feriado'])
                            <span
                                class="badge estado-feriado badge-estado"
                                title="{{ $celda['feriado_nombre'] }}">
                                FER
                            </span>

                            @elseif ($celda['es_domingo'])
                            <span class="estado-futuro">-</span>

                            @elseif ($celda['es_futuro'])
                            <span class="estado-futuro">-</span>

                            @elseif ($celda['apertura'] && $celda['cierre'])
                            <span
                                class="badge bg-success badge-estado"
                                title="Apertura y cierre registrados">
                                OK
                            </span>

                            @elseif ($celda['apertura'] && !$celda['cierre'])
                            <span
                                class="badge bg-warning text-dark badge-estado"
                                title="Apertura registrada, falta cierre">
                                A
                            </span>

                            @elseif (!$celda['apertura'] && $celda['cierre'])
                            <span
                                class="badge bg-info text-dark badge-estado"
                                title="Cierre registrado, falta apertura">
                                C
                            </span>

                            @else
                            <span
                                class="badge bg-danger badge-estado"
                                title="No registró apertura ni cierre">
                                F
                            </span>
                            @endif
                        </td>
                        @endforeach

                        <td>
                            <a
                                href="{{ route('verificaciones.contingencia', [
                                        'mes' => $mes,
                                        'idmac' => $mac->idmac
                                    ]) }}"
                                class="btn btn-sm btn-primary"
                                title="Ver tabla de contingencia">
                                <i class="fa fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ count($dias) + 3 }}" class="text-center text-muted">
                            No existen Centros MAC registrados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection