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
                            <h4 class="page-title">Registro de Check List Operativo</h4>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('inicio') }}">
                                        <i data-feather="home" class="align-self-center"
                                            style="height: 70%; display: block;"></i>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="javascript:void(0);" style="color: #7081b9;">Verificaciones</a>
                                </li>
                            </ol>
                        </div><!--end col-->
                    </div><!--end row-->
                </div><!--end page-title-box-->
            </div><!--end col-->
        </div><!--end row-->

        <div class="d-flex justify-content-between mb-4">
            <div>
            </div>
            <div>
                <a href="{{ route('verificaciones.index') }}" class="btn btn-secondary d-flex align-items-center">
                    <i class="fa fa-arrow-left me-2"></i> <!-- Icono de flecha hacia la izquierda -->
                    Volver al Listado
                </a>
            </div>
        </div>

        <!-- Card de Filtro de Búsqueda -->
        <div class="card mb-4">
            <div class="card-header" style="background-color:#132842">
                <h4 class="card-title text-white">Filtro de Búsqueda</h4>
            </div><!--end card-header-->
            <div class="card-body">
                <form action="{{ route('verificaciones.observaciones') }}" method="GET"
                    class="d-flex justify-content-around">
                    <div class="form-group col-md-4">
                        <label for="fecha_inicio">Fecha de Inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="fecha_fin">Fecha Final</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                    </div>
                    <div class="form-group col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Buscar</button>
                    </div>
                    <!-- Botón de Exportar -->
                    <div class="form-group col-md-2 d-flex align-items-end">
                        <button
                            type="button"
                            id="btnExport"
                            class="btn btn-success w-100"
                            onclick="exportToExcel()"
                            >
                            <i class="fa fa-download me-2"></i> Exportar a Excel
                        </button>
                    </div>

                </form>
            </div><!--end card-body-->
        </div><!--end card-->

        <table class="table table-bordered table-hover" id="miTabla">
            <thead>
                <tr>
                    <td>
                        <img src="http://www.mac.pe/wp-content/themes/bridge/images/alomac.png" alt=""
                            class="img-fluid">
                    </td>
                    <td colspan="5" class="text-center align-middle font-weight-bold h2" style="color:azure;">REGISTRO DE
                        CHECK LIST OPERATIVO
                    </td>
                </tr>
                <tr>
                    <th class="text-center col-3">Tipo de Ejecuciónaa</th>
                    <th class="text-center col-3">Observaciones</th>
                    <th class="text-center col-2">Fecha</th>
                    <th class="text-center col-1">Hora</th>
                    <th class="text-center col-1">Acción Resultado</th>
                    <th class="text-center col-2">Responsable</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($verificacionesInfo as $info)
                    <tr>
                        <td class="text-center">Check list ejecutado en {{ $info['tipoEjecucion'] }}</td>
                        <td>{{ $info['observaciones'] }}</td>
                        <td class="text-center">{{ $info['fecha'] }}</td>
                        <td class="text-center">{{ $info['hora'] }}</td>
                        <td class="text-center">{{ $info['porcentajeSi'] }}%</td>
                        <td class="text-center">{{ $info['responsable'] }}</td> <!-- Cambiar a la variable responsable -->
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
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

    <!-- Inicializar DataTables -->
    <script>
        $(document).ready(function() {
            $('#miTabla').DataTable({
                dom: 'Bfrtip',
                buttons: [{
                    extend: 'excelHtml5',
                    text: 'Exportar a Excel',
                    className: 'btn btn-success'
                }],
                pageLength: 20,
            });
        });

        async function exportToExcel() {
            const fi = document.getElementById('fecha_inicio').value;
            const ff = document.getElementById('fecha_fin').value;
            if (!fi || !ff) {
            alert('Debes seleccionar ambas fechas para exportar.');
            return;
            }

            const btn = document.getElementById('btnExport');
            // 1) Deshabilitar y mostrar spinner
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i> Exportando…';

            // 2) Construir URL de exportación
            const url = "{{ route('verificaciones.export') }}"
                    + "?fecha_inicio=" + encodeURIComponent(fi)
                    + "&fecha_fin="   + encodeURIComponent(ff);

            try {
            // 3) Traer el archivo como blob
            const resp = await fetch(url, {
                method: 'GET',
                credentials: 'same-origin'
            });
            if (!resp.ok) throw new Error('Error en la descarga');

            const blob = await resp.blob();

            // 4) Forzar descarga
            const blobUrl = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = blobUrl;
            // Puedes extraer el nombre del fichero desde Content-Disposition si quieres
            a.download = `verificaciones_${fi}_${ff}.xlsx`;
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(blobUrl);

            } catch (e) {
            console.error(e);
            alert('Hubo un error al exportar. Intenta de nuevo.');
            } finally {
            // 5) Restaurar el botón
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-download me-2"></i> Exportar a Excel';
            }
        }
    </script>
@endsection
