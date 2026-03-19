@extends('layouts.layout')

@section('style')
    <link rel="stylesheet" href="{{ asset('Vendor/toastr/toastr.min.css') }}">
    <link href="{{ asset('nuevo/plugins/select2/select2.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('nuevo/plugins/datatables/buttons.bootstrap5.min.css') }}" rel="stylesheet" />

    <style>
        /* 🔷 TABLA MAC */
        .table-mac thead {
            background-color: #132842;
            color: #fff;
        }

        .table-mac thead th {
            text-align: center;
            vertical-align: middle;
            font-weight: 600;
            border-color: #1e355c;
        }

        /* 🔥 HOVER PRO */
        .table-mac tbody tr:hover {
            background-color: #f5f8ff;
            transition: 0.2s;
        }

        /* 🔹 ZEBRA */
        .table-mac tbody tr:nth-child(even) {
            background-color: #fafbff;
        }
    </style>
@endsection

@section('main')
    <div class="container-fluid">

        <!-- 🔷 HEADER -->
        <div class="row mb-3">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <div class="row align-items-center">

                        <div class="col">
                            <h4 class="page-title">
                                Registro de Check List Operativo
                            </h4>

                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('inicio') }}">
                                        <i data-feather="home" style="width:16px;height:16px;"></i>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('verificaciones.index') }}" class="text-muted">
                                        Verificaciones
                                    </a>
                                </li>
                                <li class="breadcrumb-item active">
                                    Observaciones
                                </li>
                            </ol>
                        </div>

                        <div class="col-auto">
                            <a href="{{ route('verificaciones.index') }}" class="btn btn-dark">
                                <i class="fa fa-arrow-left"></i> Volver
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- 🔷 FILTRO -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header card-header-mac">
                <strong>Filtro de Búsqueda</strong>
            </div>

            <div class="card-body">
                <form id="formFiltro" action="{{ route('verificaciones.observaciones') }}" method="GET"
                    class="row g-3 align-items-end">

                    <div class="col-md-4">
                        <label class="fw-bold mb-2">Fecha Inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                    </div>

                    <div class="col-md-4">
                        <label class="fw-bold mb-2">Fecha Fin</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                    </div>

                    <div class="col-md-2 d-grid">
                        <button class="btn btn-primary">
                            <i class="fa fa-search"></i> Buscar
                        </button>
                    </div>

                    <div class="col-md-2 d-grid">
                        <button type="button" id="btnExport" class="btn btn-success" onclick="exportToExcel()">
                            <i class="fa fa-download"></i> Excel
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <!-- 🔷 TABLA -->
        <div class="card shadow-sm">

            <!-- 🔵 HEADER -->
            <div class="card-header text-white" style="background:#132842;">
                <h5 class="mb-0 fw-bold text-uppercase text-white">
                    Registro de Check List Operativo
                </h5>
            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered table-hover align-middle table-mac" id="miTabla">

                        <!-- 🔷 CABECERA -->
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Observaciones</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>% Cumplimiento</th>
                                <th>Responsable</th>
                            </tr>
                        </thead>

                        <!-- 🔷 CUERPO -->
                        <tbody>
                            @forelse ($verificacionesInfo as $info)
                                <tr>

                                    <td class="text-center fw-bold">
                                        {{ $info['tipoEjecucion'] }}
                                    </td>

                                    <td style="white-space: pre-line;">
                                        {!! $info['observaciones'] !!}
                                    </td>

                                    <td class="text-center">
                                        {{ $info['fecha'] }}
                                    </td>

                                    <td class="text-center">
                                        {{ $info['hora'] }}
                                    </td>

                                    <td class="text-center">
                                        <span
                                            class="badge px-3 py-2
                                    {{ $info['porcentajeSi'] >= 95
                                        ? 'bg-success'
                                        : ($info['porcentajeSi'] >= 85
                                            ? 'bg-warning text-dark'
                                            : 'bg-danger') }}">
                                            {{ $info['porcentajeSi'] }}%
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        {{ $info['responsable'] }}
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No hay registros
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>

                </div>

            </div>
        </div>

    </div>
@endsection

@section('script')
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $('#miTabla').DataTable({
                pageLength: 15,
                order: [
                    [2, 'desc']
                ],
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
                }
            });
        });

        // 🔥 VALIDAR FECHAS
        document.getElementById('formFiltro').addEventListener('submit', function(e) {
            const fi = document.getElementById('fecha_inicio').value;
            const ff = document.getElementById('fecha_fin').value;

            if (fi > ff) {
                e.preventDefault();
                Swal.fire('Error', 'La fecha inicio no puede ser mayor que la final', 'warning');
            }
        });

        // 🔥 EXPORTAR
        async function exportToExcel() {

            const fi = document.getElementById('fecha_inicio').value;
            const ff = document.getElementById('fecha_fin').value;

            if (!fi || !ff) {
                Swal.fire('Atención', 'Selecciona ambas fechas', 'warning');
                return;
            }

            const btn = document.getElementById('btnExport');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Exportando...';

            try {

                const url = "{{ route('verificaciones.export') }}" +
                    "?fecha_inicio=" + fi +
                    "&fecha_fin=" + ff;

                const res = await fetch(url);
                const blob = await res.blob();

                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = `verificaciones_${fi}_${ff}.xlsx`;
                a.click();

            } catch (e) {
                Swal.fire('Error', 'No se pudo exportar', 'error');
            }

            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-download"></i> Excel';
        }
    </script>
@endsection
