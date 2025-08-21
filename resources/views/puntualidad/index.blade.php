@extends('layouts.layout')

@section('style')
    <link href="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('nuevo/plugins/datatables/buttons.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('main')
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="row">
                    <div class="col">
                        <h4 class="page-title">Reporte de Puntualidad</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('inicio') }}">Inicio</a></li>
                            <li class="breadcrumb-item active">Puntualidad</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtro de búsqueda -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header" style="background-color:#132842">
                    <h4 class="card-title text-white">Filtro de Búsqueda</h4>
                </div><!--end card-header-->
                <div class="card-body bootstrap-select-1">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="mb-3">Centro MAC:</label>
                                <select name="mac" id="mac" class="form-select" onchange="SearchMac()">
                                    @role('Administrador|Moderador')
                                        <option value="" disabled selected>-- Seleccione una opción --</option>
                                        @forelse ($mac as $m)
                                            <option value="{{ $m->IDCENTRO_MAC }}">{{ $m->NOMBRE_MAC }}</option>
                                        @empty
                                            <option value="">SIN RESULTADOS</option>
                                        @endforelse
                                    @else
                                        @forelse ($mac as $m)
                                            <option value="{{ $m->IDCENTRO_MAC }}" selected>{{ $m->NOMBRE_MAC }}</option>
                                        @empty
                                            <option value="">SIN RESULTADOS</option>
                                        @endempty
                                    @endrole
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="mb-3">Mes:</label>
                            <select name="mes" id="mes" class="form-select" onchange="SearchMes()">
                                <option value="" disabled selected>-- Seleccione una opción --</option>
                                <option value="01">Enero</option>
                                <option value="02">Febrero</option>
                                <option value="03">Marzo</option>
                                <option value="04">Abril</option>
                                <option value="05">Mayo</option>
                                <option value="06">Junio</option>
                                <option value="07">Julio</option>
                                <option value="08">Agosto</option>
                                <option value="09">Setiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12">Diciembre</option>
                            </select>
                        </div>
                    </div><!-- end col -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="mb-3">Año:</label>
                            <select name="año" id="año" class="form-select año"
                                onchange="SearchAño()"></select>
                        </div>
                    </div><!-- end col -->
                    <div class="col-md-3">
                        <div class="form-group" style="margin-top: 2.6em">
                            <button type="button" class="btn btn-primary" id="filtro" onclick="execute_filter()">
                                <i class="fa fa-search" aria-hidden="true"></i> Buscar
                            </button>
                            <button class="btn btn-dark" id="limpiar"><i class="fa fa-undo" aria-hidden="true"></i>
                                Limpiar</button>
                        </div>
                    </div><!-- end col -->
                </div>
            </div><!-- end card-body -->
        </div> <!-- end card -->
    </div> <!-- end col -->
</div> <!-- end row -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header" style="background-color:#132842">
                <h4 class="card-title text-white">Leyenda</h4>
            </div><!--end card-header-->
            <div class="card-body bootstrap-select-1">
                <div class="row">
                    <div class="col-md-5 border-end">
                        <table class="table table-bordered">
                            <tr>
                                <th class="text-center" style="background: #198754">≥ 95% </th>
                                <td>Cumple el % del ANS</td>
                            </tr>

                            <tr>
                                <th class="text-center" style="background: #ffc107">≥ 84% hasta < 95%</th>
                                <td>Cerca de cumplir el ANS</td>
                            </tr>
                            <tr>
                                <th class="text-center" style="background: #dc3545">
                                    < 84%</th>
                                <td>No cumple el % de ANS</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div><!-- end card-body -->
        </div> <!-- end card -->
    </div> <!-- end col -->
</div> <!-- end row -->
<!-- Tabla de resultados -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header" style="background-color:#132842">
                <h4 class="card-title text-white">Lista de Puntualidad</h4>
            </div>

            <div class="card-body bootstrap-select-1">
                <div class="table-responsive" id="table_data">
                    <!-- Aquí se cargará la tabla desde la función `tb_index` -->
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal para editar o agregar puntualidad --}}
<div class="modal fade" id="modal_show_modal" tabindex="-1" role="dialog"></div>
@endsection

@section('script')
<script src="{{ asset('nuevo/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('nuevo/plugins/datatables/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.js') }}"></script>
<script>
    $(document).ready(function() {
        // tabla_seccion(); // Cargar la tabla al cargar la página

        // Funcionalidad del botón Limpiar
        $("#limpiar").on("click", function(e) {
            var fechaActual = new Date();
            var formatoFecha = fechaActual.toISOString().split('T')[0];

            document.getElementById('fechainicio').value = formatoFecha;
            document.getElementById('fechafin').value = formatoFecha;

            tabla_seccion();
        });
    });

    // Función para aplicar los filtros
    function execute_filter() {
        var mac = parseInt($('#mac').val());
        var mes = parseInt($('#mes').val(), 10); // asegurar número
        var anio = parseInt($('#año').val());

        if (!mac || !mes || !anio) {
            alert("Por favor, seleccione todos los campos obligatorios: MAC, Mes y Año.");
            return;
        }

        // Decidir qué ruta usar
        let url = "{{ route('puntualidad.tablas.tb_index') }}"; // por defecto la manual

        if (anio === 2025 && mes < 8) { // 👈 ahora usamos "anio"
            url = "{{ route('puntualidad.tablas.tb_index_sp') }}"; // SP solo para enero-julio 2025
        }

        $.ajax({
            type: 'GET',
            url: url,
            data: {
                mac: mac,
                mes: mes,
                anio: anio // 👈 enviar como "anio"
            },
            beforeSend: function() {
                $("#filtro").html('<i class="fa fa-spinner fa-spin"></i> Buscando').prop('disabled', true);
            },
            success: function(response) {
                $("#filtro").html('Buscar').prop('disabled', false);
                $('#table_data').html(response);
            },
            error: function(xhr, status, error) {
                console.error("Error: " + error);
                alert("Ocurrió un error al filtrar los datos. Por favor, actualice la página.");
                $("#filtro").html('Buscar').prop('disabled', false);
            }
        });
    }

    /**************************************************************** CARGAR COMBOS POR FECHA ACTUAL *************************************************************/
    function ComboAno() {
        var n = (new Date()).getFullYear()
        var select = document.querySelector(".año");
        for (var i = n; i >= 2023; i--) select.options.add(new Option(i, i));
    };
    window.onload = ComboAno;

    // Obtén el elemento select por su ID
    var mesSelect = document.getElementById('mes');

    // Obtén el mes actual (0 = enero, 1 = febrero, ..., 11 = diciembre)
    var mesActual = new Date().getMonth() + 1;

    console.log(mesActual);

    // Selecciona el mes actual en el select
    mesSelect.selectedIndex = mesActual
</script>
@endsection
