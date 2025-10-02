@extends('layouts.layout')

@section('style')
    <link href="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('nuevo/plugins/datatables/buttons.bootstrap5.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" />
@endsection

@section('main')
    {{-- ───────── TITULO ───────── --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="row">
                    <div class="col">
                        <h4 class="page-title">Reporte de Ocupabilidad</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('inicio') }}">Inicio</a></li>
                            <li class="breadcrumb-item active">Ocupabilidad</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ───────── LEYENDA ───────── --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header" style="background-color:#132842">
                    <h4 class="card-title text-white">Leyenda</h4>
                </div>
                <div class="card-body bootstrap-select-1">
                    <div class="row">
                        <div class="col-md-5 border-end">
                            <table class="table table-bordered">
                                <tr>
                                    <th class="text-center" style="background:#198754">≥ 95 % – 100 %</th>
                                    <td>Cumple ANS</td>
                                </tr>
                                <tr>
                                    <th class="text-center" style="background:#ffc107">&gt; 84 % – &lt; 95 %</th>
                                    <td>Cerca de cumplir</td>
                                </tr>
                                <tr>
                                    <th class="text-center" style="background:#dc3545">≤ 84 %</th>
                                    <td>No cumple ANS</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ───────── FILTRO ───────── --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header" style="background-color:#132842">
                    <h4 class="card-title text-white">Filtro de Búsqueda</h4>
                </div>
                <div class="card-body bootstrap-select-1">
                    <div class="row">
                        {{-- MAC --}}
                        <div class="col-md-3">
                            <label class="mb-3">Centro MAC:</label>
                            <select id="mac" name="mac" class="form-select">
                                @role('Administrador|Moderador')
                                    <option value="" disabled selected>-- Seleccione --</option>
                                    @foreach ($mac as $m)
                                        <option value="{{ $m->IDCENTRO_MAC }}">{{ $m->NOMBRE_MAC }}</option>
                                    @endforeach
                                @else
                                    @foreach ($mac as $m)
                                        <option value="{{ $m->IDCENTRO_MAC }}" selected>{{ $m->NOMBRE_MAC }}</option>
                                    @endforeach
                                @endrole
                            </select>
                        </div>

                        {{-- MES --}}
                        <div class="col-md-3">
                            <label class="mb-3">Mes:</label>
                            <select id="mes" name="mes" class="form-select">
                                <option value="" disabled selected>-- Seleccione --</option>
                                @foreach (['01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto', '09' => 'Setiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'] as $val => $txt)
                                    <option value="{{ $val }}">{{ $txt }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- AÑO --}}
                        <div class="col-md-3">
                            <label class="mb-3">Año:</label>
                            <select id="año" name="año" class="form-select año"></select>
                        </div>

                        {{-- BOTONES --}}
                        <div class="col-md-3">
                            <div class="form-group" style="margin-top:2.6em;">
                                <button type="button" class="btn btn-primary" id="filtro" onclick="execute_filter()">
                                    <i class="fa fa-search"></i> Buscar
                                </button>
                                <button class="btn btn-dark" onclick="clear_filter()">
                                    <i class="fa fa-undo"></i> Limpiar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ───────── TABLA DINÁMICA ───────── --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header" style="background-color:#132842">
                    <h4 class="card-title text-white">Lista de Módulos</h4>
                </div>
                <div class="card-body bootstrap-select-1">
                    <div class="table-responsive" id="table_data"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL --}}
    <div class="modal fade" id="modal_show_modal" tabindex="-1"></div>
@endsection

@section('script')
    <script src="{{ asset('nuevo/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.js') }}"></script>

    <script>
        /* Poblar años */
        window.onload = () => {
            const sel = document.querySelector('.año');
            const cur = new Date().getFullYear();
            for (let y = cur; y >= 2023; y--) sel.options.add(new Option(y, y));
            document.getElementById('mes').selectedIndex = new Date().getMonth() + 1;
        };

        /* Buscar */
        function execute_filter() {
            const mac = $('#mac').val();
            const mes = parseInt($('#mes').val(), 10);
            const año = parseInt($('#año').val(), 10);

            if (!mac || !mes || !año) {
                alert('Seleccione MAC, Mes y Año.');
                return;
            }

            let url = "{{ route('ocupabilidad.tablas.tb_index') }}"; // default: otros años

            if (año === 2025) {
                if (mes < 10) {
                    // Enero a Julio 2025 → resumen
                    url = "{{ route('ocupabilidad.tablas.tb_index_resumen') }}";
                } else {
                    // Agosto a Diciembre 2025 → tb_index_sp
                    url = "{{ route('ocupabilidad.tablas.tb_index_sp') }}";
                }
            }

            $.ajax({
                type: 'GET',
                url: url,
                data: {
                    mac: mac,
                    mes: mes,
                    año: año
                },
                beforeSend: () => {
                    $('#filtro')
                        .html('<i class="fa fa-spinner fa-spin"></i> Buscando')
                        .prop('disabled', true);
                },
                success: res => {
                    $('#filtro').html('<i class="fa fa-search"></i> Buscar').prop('disabled', false);
                    $('#table_data').html(res);
                },
                error: () => {
                    alert('Error al filtrar.');
                    $('#filtro').html('<i class="fa fa-search"></i> Buscar').prop('disabled', false);
                }
            });
        }

        /* Limpiar */
        const clear_filter = () => {
            $('#mes').val('');
            $('#año').val('');
            $('#table_data').empty();
        };
    </script>
@endsection
