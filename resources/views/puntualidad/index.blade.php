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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="mb-3">Fecha de Inicio:</label>
                                <input type="date" name="fechainicio" id="fechainicio" class="form-control">
                            </div>
                        </div><!-- end col -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="mb-3">Fecha de Fin:</label>
                                <input type="date" name="fechafin" id="fechafin" class="form-control">
                            </div>
                        </div><!-- end col -->
                        <div class="col-md-4">
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
            tabla_seccion(); // Cargar la tabla al cargar la página

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
            var fechainicio = $('#fechainicio').val();
            var fechafin = $('#fechafin').val();

            if (!fechainicio || !fechafin) {
                alert("Por favor, seleccione tanto la fecha de inicio como la fecha de fin.");
                return;
            }

            // Realiza la solicitud AJAX para filtrar los datos según las fechas seleccionadas
            $.ajax({
                type: 'GET',
                url: "{{ route('puntualidad.tablas.tb_index') }}",
                data: {
                    fechainicio: fechainicio,
                    fechafin: fechafin
                },
                success: function(response) {
                    $('#table_data').html(response);
                },
                error: function(xhr, status, error) {
                    console.error("Error: " + error);
                    alert("Ocurrió un error al filtrar los datos.");
                }
            });
        }

    </script>
@endsection
