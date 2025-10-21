@extends('layouts.layout')

@section('style')
    <link href="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" />
@endsection

@section('main')
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="row">
                    <div class="col">
                        <h4 class="page-title">Monitoreo de Asistencia</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('inicio') }}">
                                    <i data-feather="home" class="align-self-center"></i>
                                </a>
                            </li>
                            <li class="breadcrumb-item">Monitoreo</li>
                            <li class="breadcrumb-item active">Asistencia</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="card mb-3">
        <div class="card-header" style="background-color:#132842">
            <h4 class="card-title text-white">Filtro de búsqueda</h4>
        </div>
        <div class="card-body">
            <div class="row">
                {{-- CENTRO MAC --}}
                <div class="col-md-4">
                    <label class="fw-bold text-dark mb-2">Centro MAC:</label>

                    {{--  Solo Administrador o Monitor pueden cambiar de MAC --}}
                    @hasanyrole('Administrador|Monitor')
                        <select id="idmac" name="idmac" class="form-select">
                            <option value="">-- Todos --</option>
                            @foreach ($macs as $m)
                                <option value="{{ $m->IDCENTRO_MAC }}"
                                    {{ auth()->user()->idcentro_mac == $m->IDCENTRO_MAC ? 'selected' : '' }}>
                                    {{ $m->NOMBRE_MAC }}
                                </option>
                            @endforeach
                        </select>
                        @elsehasanyrole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')
                        {{-- 🚫 Otros roles: solo ven su MAC --}}
                        <input type="text" class="form-control text-uppercase bg-light"
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

                {{-- BOTÓN BUSCAR --}}
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-danger w-100" onclick="cargarTabla()">
                        <i class="fa fa-search"></i> Buscar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header" style="background-color:#132842">
                    <h4 class="card-title text-white">Cierres de Asistencia por Centro MAC</h4>
                </div>
                <div class="card-body">
                    <div id="table_data" class="table-responsive"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('nuevo/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            cargarTabla();
        });

        // 🔄 Cargar tabla dinámica
        function cargarTabla() {
            let anio = $('#anio').val();
            let mes = $('#mes').val();
            let idmac = $('#idmac').length ? $('#idmac').val() : ''; // solo si existe el select

            $.ajax({
                url: "{{ route('monitoreo.asistencia.tabla') }}",
                type: "GET",
                data: {
                    anio,
                    mes,
                    idmac
                },
                beforeSend: function() {
                    $('#table_data').html('<i class="fa fa-spinner fa-spin"></i> Cargando datos...');
                },
                success: function(data) {
                    $('#table_data').html(data);
                },
                error: function() {
                    $('#table_data').html('<div class="alert alert-danger">Error al cargar los datos</div>');
                }
            });
        }
    </script>
@endsection
