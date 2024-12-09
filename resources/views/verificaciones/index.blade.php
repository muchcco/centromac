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
@endsection

@section('main')
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <div class="row">
                        <div class="col">
                            <h4 class="page-title">Verificaciones</h4>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('inicio') }}"><i data-feather="home"
                                            class="align-self-center" style="height: 70%; display: block;"></i></a></li>
                                <li class="breadcrumb-item"><a href="javascript:void(0);"
                                        style="color: #7081b9;">Verificaciones</a></li>
                            </ol>
                        </div><!--end col-->
                    </div><!--end row-->
                </div><!--end page-title-box-->
            </div><!--end col-->
        </div><!--end row-->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header" style="background-color:#132842">
                        <h4 class="card-title text-white">Filtro de Búsqueda</h4>
                    </div><!--end card-header-->
                    <div class="card-body bootstrap-select-1">
                        <div class="mb-4 d-flex align-items-center gap-2">
                            <form action="{{ route('verificaciones.create') }}" method="GET" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-primary">Crear Verificación</button>
                            </form>
                            <!-- Botón Formato -->
                            <form action="{{ route('verificaciones.observaciones') }}" method="GET" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-info">Formato</button>
                            </form>
                            <!-- Botón Contingencia -->
                            <form action="{{ route('verificaciones.contingencia') }}" method="GET" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-warning">Tabla</button>
                            </form>
                        </div>
                        <!-- Formulario de Búsqueda por Rango de Fechas -->
                        <form action="{{ route('verificaciones.index') }}" method="GET" class="mb-4">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Fecha Inicio:</span>
                                </div>
                                <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Fecha Fin:</span>
                                </div>
                                <input type="date" name="fecha_fin" id="fecha_fin" class="form-control">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">Buscar</button>
                                </div>
                            </div>
                        </form>
                        <!-- Tabla de Verificaciones -->
                        @if ($verificaciones->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped" id="verificaciones-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Fecha</th>
                                            <th>Apertura</th>
                                            <th>Relevo</th>
                                            <th>Cierre</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $contador = 1;
                                            $fechas = $verificaciones->pluck('Fecha')->unique()->toArray();
                                        @endphp
                                        @foreach ($fechas as $fecha)
                                            <tr>
                                                <td>{{ $contador++ }}</td>
                                                <td>{{ date('d/m/Y', strtotime($fecha)) }}</td>
                                                <td>
                                                    @if ($verificaciones->where('Fecha', $fecha)->where('AperturaCierre', 0)->isNotEmpty())
                                                        OK
                                                    @else
                                                        Falta
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($verificaciones->where('Fecha', $fecha)->where('AperturaCierre', 1)->isNotEmpty())
                                                        OK
                                                    @else
                                                        Falta
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($verificaciones->where('Fecha', $fecha)->where('AperturaCierre', 2)->isNotEmpty())
                                                        OK
                                                    @else
                                                        Falta
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $verificacion = $verificaciones
                                                            ->where('Fecha', $fecha)
                                                            ->first();
                                                    @endphp
                                                    @if ($verificacion)
                                                        <!-- Opción para Ver -->
                                                        <a href="{{ route('verificaciones.show', date('Y-m-d', strtotime($fecha))) }}"
                                                            class="btn btn-secondary" title="Ver">
                                                            <i class="fas fa-eye"></i> Ver
                                                            <!-- Puedes personalizar la letra aquí si es necesario -->
                                                        </a>
                                                        <!-- Botón para Apertura -->
                                                        @if ($verificaciones->where('Fecha', $fecha)->where('AperturaCierre', 0)->isNotEmpty())
                                                            <a href="{{ route('verificaciones.edit', ['AperturaCierre' => 0, 'Fecha' => $verificacion->Fecha]) }}"
                                                                class="btn btn-success" title="Editar Apertura">
                                                                <i class="fas fa-pencil-alt"></i> Apertura
                                                                <!-- Letra para Apertura -->
                                                            </a>
                                                        @endif

                                                        <!-- Botón para Relevo -->
                                                        @if ($verificaciones->where('Fecha', $fecha)->where('AperturaCierre', 1)->isNotEmpty())
                                                            <a href="{{ route('verificaciones.edit', ['AperturaCierre' => 1, 'Fecha' => $verificacion->Fecha]) }}"
                                                                class="btn btn-primary" title="Editar Relevo">
                                                                <i class="fas fa-pencil-alt"></i> Relevo
                                                                <!-- Letra para Relevo -->
                                                            </a>
                                                        @endif

                                                        <!-- Botón para Cierre -->
                                                        @if ($verificaciones->where('Fecha', $fecha)->where('AperturaCierre', 2)->isNotEmpty())
                                                            <a href="{{ route('verificaciones.edit', ['AperturaCierre' => 2, 'Fecha' => $verificacion->Fecha]) }}"
                                                                class="btn btn-danger" title="Editar Cierre">
                                                                <i class="fas fa-pencil-alt"></i> Cierre
                                                                <!-- Letra para Cierre -->
                                                            </a>
                                                        @endif
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p>No hay verificaciones registradas.</p>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Ver Modales --}}
    <div class="modal fade" id="modal_show_modal" tabindex="-1" role="dialog"></div>
@endsection

@section('script')
    <script src="{{ asset('Script/js/sweet-alert.min.js') }}"></script>
    <script src="{{ asset('Vendor/toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('//cdn.jsdelivr.net/npm/sweetalert2@11') }}"></script>

    <!-- Plugins js -->
    <script src="{{ asset('nuevo/plugins/select2/select2.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/timepicker/bootstrap-material-datetimepicker.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('#verificaciones-table').DataTable();
        });

        // Función para filtrar las verificaciones
        function execute_filter() {
            var fecha_inicio = $('#fecha_inicio').val();
            var fecha_fin = $('#fecha_fin').val();

            if (!fecha_inicio || !fecha_fin) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Faltan fechas',
                    text: 'Por favor ingresa las fechas de inicio y fin.'
                });
                return;
            }

            $.ajax({
                type: 'GET',
                url: "{{ route('verificaciones.index') }}",
                data: {
                    fecha_inicio: fecha_inicio,
                    fecha_fin: fecha_fin
                },
                success: function(data) {
                    $('#table_data').html(data);
                },
                error: function(xhr, status, error) {
                    console.log("Error:", error);
                }
            });
        }

        // Limpiar los campos de filtro
        $("#limpiar").on("click", function() {
            $('#fecha_inicio').val('');
            $('#fecha_fin').val('');
            execute_filter(); // Cargar nuevamente todos los datos
        });

        function btnAddVerificacion() {
            // Aquí puedes implementar la lógica para abrir un modal y agregar una nueva verificación
        }
    </script>
@endsection
