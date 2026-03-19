@extends('layouts.layout')

@section('style')
    <link rel="stylesheet" href="{{ asset('Vendor/toastr/toastr.min.css') }}">
    <link href="{{ asset('nuevo/plugins/select2/select2.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('nuevo/plugins/huebee/huebee.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('nuevo/plugins/timepicker/bootstrap-material-datetimepicker.css') }}" rel="stylesheet">
    <link href="{{ asset('nuevo/plugins/bootstrap-touchspin/css/jquery.bootstrap-touchspin.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('nuevo/plugins/datatables/buttons.bootstrap5.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" />
@endsection

@section('main')
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="row">
                    <div class="col">
                        <h4 class="page-title">Gestión de Servicios ANS</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('inicio') }}">Inicio</a></li>
                            <li class="breadcrumb-item active">Servicios ANS</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header" style="background-color:#132842">
            <h4 class="card-title text-white mb-0">Filtro de Servicios ANS</h4>
        </div>
        <div class="card-body">
            <div class="row align-items-end">

                <div class="col-md-6">
                    <label class="fw-bold text-dark mb-2">Entidad:</label>
                    <select id="filtro_entidad" class="form-control select2">
                        <option value="">-- Seleccione entidad --</option>
                        @foreach ($entidades as $ent)
                            <option value="{{ $ent->id_entidad }}">{{ $ent->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 d-grid">
                    <button class="btn btn-primary" onclick="abrirServiciosEntidad()">
                        <i class="fa fa-search"></i> Ver Servicios
                    </button>
                </div>

                <div class="col-md-3 d-grid">
                    <button class="btn btn-warning text-dark" onclick="abrirCambioTiempos()">
                        <i class="fa fa-clock"></i> Cambiar tiempos
                    </button>
                </div>

            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">

                <div class="card-header" style="background-color:#132842">
                    <h4 class="card-title text-white">Catálogo de Servicios ANS</h4>
                </div>

                <div class="card-body bootstrap-select-1">

                    <div class="row">
                        <div class="col-12 mb-3">
                            <button class="btn btn-success" data-toggle="modal" data-target="#large-Modal"
                                onclick="btnAddServicio()">
                                <i class="fa fa-plus"></i> Agregar Servicio ANS
                            </button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive">
                                <div id="table_data"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_show_modal" tabindex="-1" role="dialog"></div>
@endsection

@section('script')
    <script src="{{ asset('Script/js/sweet-alert.min.js') }}"></script>
    <script src="{{ asset('Vendor/toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('//cdn.jsdelivr.net/npm/sweetalert2@11') }}"></script>

    <script src="{{ asset('nuevo/plugins/select2/select2.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/timepicker/bootstrap-material-datetimepicker.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/bootstrap-touchspin/js/jquery.bootstrap-touchspin.min.js') }}"></script>

    <script src="{{ asset('nuevo/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/buttons.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/jszip.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/pdfmake.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/vfs_fonts.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/buttons.print.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/buttons.colVis.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('nuevo/assets/pages/jquery.datatable.init.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('.select2').select2();
            tabla_servicios();
        });

        function tabla_servicios() {
            $.ajax({
                type: 'GET',
                url: "{{ route('ans.tablas.tb_index') }}",
                beforeSend: function() {
                    $('#table_data').html('<i class="fa fa-spinner fa-spin"></i> Cargando...');
                },
                success: function(data) {
                    $('#table_data').html(data);
                },
                error: function() {
                    $('#table_data').html('Error al cargar los datos.');
                }
            });
        }

        function btnAddServicio() {
            $.ajax({
                type: 'post',
                url: "{{ route('ans.modals.md_add_servicio') }}",
                dataType: "json",
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                success: function(data) {
                    $("#modal_show_modal").html(data.html);
                    $("#modal_show_modal").modal('show');
                }
            });
        }

        function btnEditServicio(idServicio) {
            $.ajax({
                type: 'POST',
                url: "{{ route('ans.modals.md_edit_servicio') }}",
                dataType: "json",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id_servicio": idServicio
                },
                success: function(data) {
                    $("#modal_show_modal").html(data.html);
                    $("#modal_show_modal").modal('show');
                },
                error: function() {
                    Swal.fire({
                        icon: "error",
                        text: "No se pudo cargar la información",
                        confirmButtonText: "Aceptar"
                    });
                }
            });
        }

        function cargarServicios() {
            tabla_servicios();
        }

        function abrirServiciosEntidad() {

            var entidad = $("#filtro_entidad").val();

            if (entidad == "") {
                Swal.fire({
                    icon: "warning",
                    text: "Seleccione una entidad",
                    confirmButtonText: "Aceptar"
                });
                return;
            }

            $.ajax({
                type: 'POST',
                url: "{{ route('ans.modals.md_servicios_entidad') }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id_entidad": entidad
                },
                dataType: "json",
                beforeSend: function() {
                    $("#modal_show_modal").html(
                        '<div class="text-center p-4"><i class="fa fa-spinner fa-spin"></i> Cargando...</div>'
                    );
                },
                success: function(data) {
                    $("#modal_show_modal").html(data.html);
                    $("#modal_show_modal").modal('show');
                }
            });
        }

        function btnUpdateServicio(id) {

            if ($("#nombre_servicio").val() == "" ||
                $("#limite_espera").val() == "" ||
                $("#limite_atencion").val() == "") {

                Swal.fire({
                    icon: "warning",
                    title: "Campos incompletos",
                    text: "Debe completar todos los campos",
                    confirmButtonText: "Aceptar"
                });

                return;

            }

            var formData = new FormData();

            formData.append("nombre_servicio", $("#nombre_servicio").val());
            formData.append("limite_espera", $("#limite_espera").val());
            formData.append("limite_atencion", $("#limite_atencion").val());
            formData.append("status", $("#status").val());
            formData.append("id_servicio", id);
            formData.append("_token", $("input[name=_token]").val());

            $.ajax({

                type: 'POST',
                url: "{{ route('ans.update_servicio') }}",
                data: formData,
                processData: false,
                contentType: false,

                beforeSend: function() {

                    $("#btnEnviarForm").html('<i class="fa fa-spinner fa-spin"></i> Guardando...');
                    $("#btnEnviarForm").prop("disabled", true);

                },

                success: function(response) {

                    $("#modal_show_modal").modal('hide');

                    tabla_servicios();

                    Swal.fire({
                        icon: 'success',
                        title: 'Servicio actualizado',
                        text: 'El servicio ANS fue actualizado correctamente'
                    });

                },

                error: function() {

                    $("#btnEnviarForm").html('<i class="fa fa-save"></i> Guardar Cambios');
                    $("#btnEnviarForm").prop("disabled", false);

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo actualizar el servicio'
                    });

                }

            });

        }

        function btnDeleteServicio(idServicio) {

            Swal.fire({
                title: "¿Seguro que desea eliminar el servicio?",
                text: "El servicio será eliminado del catálogo",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: "Aceptar",
                cancelButtonText: "Cancelar"
            }).then((result) => {

                if (result.isConfirmed) {

                    $.ajax({
                        url: "{{ route('ans.delete_servicio') }}",
                        type: 'post',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            "id_servicio": idServicio
                        },
                        success: function() {
                            tabla_servicios();
                            Swal.fire({
                                icon: "success",
                                title: "Eliminado",
                                text: "Servicio eliminado correctamente",
                                confirmButtonText: "Aceptar"
                            });
                        },
                        error: function() {
                            Swal.fire({
                                icon: "error",
                                text: "No se pudo eliminar el servicio",
                                confirmButtonText: "Aceptar"
                            });
                        }
                    });

                }

            });

        }

        function guardarTiemposEntidad() {
            var data = [];
            $(".espera").each(function() {
                var id = $(this).data("id");
                var espera = $(this).val();
                var atencion = $(".atencion[data-id='" + id + "']").val();
                var fecha_inicio = $(".fecha_inicio[data-id='" + id + "']").val();
                var fecha_fin = $(".fecha_fin[data-id='" + id + "']").val();
                var calcula = $(".calcula[data-id='" + id + "']").is(":checked") ? 1 : 0;

                data.push({
                    id_servicio: id,
                    fecha_inicio: fecha_inicio,
                    fecha_fin: fecha_fin,
                    limite_espera: espera,
                    limite_atencion: atencion,
                    se_calcula: calcula
                });
            });
            $.ajax({
                type: 'POST',
                url: "{{ route('ans.update_tiempos_entidad') }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    data: data
                },

                beforeSend: function() {
                    Swal.fire({
                        title: 'Guardando',
                        text: 'Actualizando tiempos...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },

                success: function() {
                    $("#modal_show_modal").modal('hide');
                    tabla_servicios();
                    Swal.fire({
                        icon: 'success',
                        title: 'Actualizado',
                        text: 'Los tiempos fueron actualizados correctamente'
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo actualizar'
                    });
                }
            });
        }

        function abrirCambioTiempos() {

            var entidad = $("#filtro_entidad").val();

            if (entidad == "") {

                Swal.fire({
                    icon: "warning",
                    text: "Seleccione una entidad"
                });

                return;

            }

            $.ajax({

                type: 'POST',

                url: "{{ route('ans.modals.md_cambiar_tiempos') }}",

                data: {
                    "_token": "{{ csrf_token() }}",
                    "id_entidad": entidad
                },

                dataType: "json",

                success: function(data) {

                    $("#modal_show_modal").html(data.html);

                    $("#modal_show_modal").modal('show');

                }

            });

        }

        function guardarCambioTiempos() {
            var data = [];
            $(".fecha_fin").each(function() {
                var id = $(this).data("id");
                var fecha_fin = $(this).val();
                var espera = $(".espera[data-id='" + id + "']").val();
                var atencion = $(".atencion[data-id='" + id + "']").val();
                if (espera == "" || atencion == "") return;
                data.push({
                    id_servicio: id,
                    fecha_fin: fecha_fin,
                    limite_espera: espera,
                    limite_atencion: atencion
                });
            });
            $.ajax({
                type: 'POST',
                url: "{{ route('ans.cambiar_tiempos_servicio') }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    data: data
                },
                success: function() {
                    $("#modal_show_modal").modal('hide');
                    tabla_servicios();
                    Swal.fire({
                        icon: 'success',
                        title: 'Actualizado',
                        text: 'Se generó el nuevo histórico correctamente'
                    });
                }
            });
        }
    </script>
@endsection
