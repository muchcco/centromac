@extends('layouts.layout')

@section('style')
    <link href="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
@endsection

@section('main')
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="row">
                    <div class="col">
                        <h4 class="page-title">Monitoreo PIVOT de Cierres</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('inicio') }}">
                                    <i data-feather="home"></i>
                                </a>
                            </li>
                            <li class="breadcrumb-item">Monitoreo</li>
                            <li class="breadcrumb-item active">Pivot Asistencia</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="card mb-3">
        <div class="card-header" style="background-color:#8B0000">
            <h4 class="card-title text-white">Filtro de búsqueda</h4>
        </div>
        <div class="card-body">
            <div class="row">
                {{-- CENTRO MAC --}}
                <div class="col-md-4">
                    <label class="fw-bold text-dark mb-2">Centro MAC:</label>
                    @hasanyrole('Administrador|Monitor')
                        <select id="idmac" name="idmac" class="form-select">
                            <option value="">-- Todos --</option>
                            @foreach ($macs as $m)
                                <option value="{{ $m->IDCENTRO_MAC }}">{{ $m->NOMBRE_MAC }}</option>
                            @endforeach
                        </select>
                        @elsehasanyrole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')
                        <input type="text" class="form-control bg-light text-uppercase"
                            value="{{ $macs->first()->NOMBRE_MAC ?? 'No asignado' }}" readonly>
                        <input type="hidden" id="idmac" value="{{ $macs->first()->IDCENTRO_MAC ?? '' }}">
                    @endhasanyrole
                </div>

                {{-- AÑO --}}
                <div class="col-md-3">
                    <label class="fw-bold text-dark mb-2">Año:</label>
                    <select id="anio" name="anio" class="form-select">
                        @for ($y = now()->year; $y >= 2024; $y--)
                            <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>

                {{-- MES --}}
                <div class="col-md-3">
                    <label class="fw-bold text-dark mb-2">Mes:</label>
                    <select id="mes" name="mes" class="form-select">
                        @php
                            $meses = [
                                1 => 'Enero',
                                2 => 'Febrero',
                                3 => 'Marzo',
                                4 => 'Abril',
                                5 => 'Mayo',
                                6 => 'Junio',
                                7 => 'Julio',
                                8 => 'Agosto',
                                9 => 'Setiembre',
                                10 => 'Octubre',
                                11 => 'Noviembre',
                                12 => 'Diciembre',
                            ];
                        @endphp
                        @foreach ($meses as $num => $nombre)
                            <option value="{{ $num }}" {{ $num == now()->month ? 'selected' : '' }}>
                                {{ $nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-outline-danger w-100" onclick="cargarPivot()">
                        <i class="fa fa-search"></i> Buscar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA AJAX -->
    <div class="card">
        <div class="card-header" style="background-color:#8B0000">
            <h4 class="card-title text-white">Cierres de Asistencia por Día</h4>
        </div>
        <div class="card-body">
            <div id="table_pivot" class="table-responsive"></div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(() => cargarPivot());

        function cargarPivot() {
            let idmac = $('#idmac').val();
            let anio = $('#anio').val();
            let mes = $('#mes').val();

            $.ajax({
                url: "{{ route('monitoreo.asistencia.tb_pivot') }}",
                data: {
                    idmac,
                    anio,
                    mes
                },
                beforeSend: () => $('#table_pivot').html('<i class="fa fa-spinner fa-spin"></i> Cargando...'),
                success: data => $('#table_pivot').html(data),
                error: () => $('#table_pivot').html('<div class="alert alert-danger">Error al cargar datos.</div>')
            });
        }
    </script>
@endsection
