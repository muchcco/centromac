@extends('layouts.layout')

@section('style')
    <link rel="stylesheet" href="{{ asset('Vendor/toastr/toastr.min.css') }}">
    <link href="{{ asset('nuevo/plugins/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('main')
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="row">
                    <div class="col">
                        <h4 class="page-title">Tipificaciones</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('inicio') }}">
                                    <i data-feather="home" class="align-self-center"
                                        style="height: 70%; display: block;"></i>
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="javascript:void(0);" style="color: #7081b9;">Tipificaciones</a>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenedor principal -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header" style="background-color:#132842">
                    <h4 class="card-title text-white">Listado de Tipificaciones</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <!-- Botón para abrir modal de creación -->
                            <button class="btn btn-success" data-toggle="modal" data-target="#modal_add_tipo_obs"
                                onclick="btnAddTipoObs()">
                                <i class="fa fa-plus" aria-hidden="true"></i>
                                Agregar Tipo
                            </button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <!-- Aquí se mostrará la tabla con AJAX -->
                            <div class="table-responsive" id="table_data"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal contenedor general: se llena con AJAX -->
    <div class="modal fade" id="modal_show_modal" tabindex="-1" role="dialog"></div>
@endsection


@section('script')
    <script src="{{ asset('Script/js/sweet-alert.min.js') }}"></script>
    <script src="{{ asset('Vendor/toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('//cdn.jsdelivr.net/npm/sweetalert2@11') }}"></script>

    <!-- Plugins js -->
    <script src="{{ asset('nuevo/plugins/select2/select2.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/timepicker/bootstrap-material-datetimepicker.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/bootstrap-touchspin/js/jquery.bootstrap-touchspin.min.js') }}"></script>
    <!-- Required datatable js -->
    <script src="{{ asset('nuevo/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.js') }}"></script>
    <!-- Buttons examples -->
    <script src="{{ asset('nuevo/plugins/datatables/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/buttons.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/jszip.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/pdfmake.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/vfs_fonts.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/buttons.print.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/buttons.colVis.min.js') }}"></script>
    <!-- Responsive examples -->
    <script src="{{ asset('nuevo/plugins/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('nuevo/assets/pages/jquery.datatable.init.js') }}"></script>

    <script>
        // Al cargar la página
        $(document).ready(function() {
            tablaTipoObs();
        });

        // Cargar la tabla con AJAX
        function tablaTipoObs() {
            $.ajax({
                type: 'GET',
                url: "{{ route('tipo_int_obs.tablas.tb_index') }}",
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

        // Convertir texto a mayúsculas
        var isMayus = (e) => {
            e.value = e.value.toUpperCase();
        }

        // Llamar al modal de CREACIÓN por AJAX
        function btnAddTipoObs() {
            $.ajax({
                type: 'POST',
                url: "{{ route('tipo_int_obs.modals.md_add_tipo_obs') }}",
                dataType: "json",
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                success: function(data) {
                    $("#modal_show_modal").html(data.html);
                    $("#modal_show_modal").modal('show');
                },
                error: function(xhr, status, error) {
                    console.error("Error: " + status + " " + error);
                }
            });
        }

        // Recargar tabla
        function cargarTipoObs() {
            $.ajax({
                url: "{{ route('tipo_int_obs.tablas.tb_index') }}",
                type: 'GET',
                success: function(data) {
                    $('#table_data').html(data);
                },
                error: function() {
                    $('#table_data').html('Error al cargar los datos.');
                }
            });
        }

        // CREAR (store) un nuevo tipo de observación
        function btnStoreTipoObs() {
            // Validación simple en frontend
            if ($('#tipo').val() == null || $('#tipo').val() == '') {
                $('#tipo').addClass("hasError");
            } else {
                $('#tipo').removeClass("hasError");
            }

            // Validar "tipo_obs"
            if ($('#tipo_obs').val() == null || $('#tipo_obs').val() == '') {
                $('#tipo_obs').addClass("hasError");
            } else {
                $('#tipo_obs').removeClass("hasError");
            }

            // Validar "status" (1=Activo, 2=Inactivo)
            if ($('#status').val() == null || $('#status').val() == '') {
                $('#status').addClass("hasError");
            } else {
                $('#status').removeClass("hasError");
            }

            if ($('#nom_tipo_int_obs').val() == null || $('#nom_tipo_int_obs').val() == '') {
                $('#nom_tipo_int_obs').addClass("hasError");
            } else {
                $('#nom_tipo_int_obs').removeClass("hasError");
            }

            // Armar data para envío
            var formData = new FormData();
            formData.append("tipo", $("#tipo").val());
            formData.append("tipo_obs", $("#tipo_obs").val());
            formData.append("status", "1");
            formData.append("numeracion", $("#numeracion").val());
            formData.append("nom_tipo_int_obs", $("#nom_tipo_int_obs").val());
            formData.append("descripcion", $("#descripcion").val()); // Agregado el campo descripcion
            formData.append("_token", "{{ csrf_token() }}");


            $.ajax({
                type: 'POST',
                url: "{{ route('tipo_int_obs.store_tipo_obs') }}",
                dataType: "json",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    document.getElementById("btnEnviarForm").innerHTML =
                        '<i class="fa fa-spinner fa-spin"></i> ESPERE';
                    document.getElementById("btnEnviarForm").disabled = true;
                },
                success: function(data) {
                    document.getElementById("btnEnviarForm").innerHTML = 'Guardar';
                    document.getElementById("btnEnviarForm").disabled = false;

                    if (data.status == 201) {
                        cargarTipoObs(); // Actualizar la tabla
                        Swal.fire({
                            icon: "success",
                            text: "La tipificación agregado exitosamente",
                            confirmButtonText: "Aceptar"
                        });
                        $('#modal_show_modal').modal('hide');
                    } else {
                        // Mostrar errores del servidor
                        document.getElementById('alerta').innerHTML =
                            `<div class="alert alert-warning">${data.message}</div>`;
                    }
                },
                error: function(xhr, status, error) {
                    document.getElementById("btnEnviarForm").innerHTML = 'Guardar';
                    document.getElementById("btnEnviarForm").disabled = false;
                    Swal.fire({
                        icon: "error",
                        text: "Error al agregar la tipificación",
                        confirmButtonText: "Aceptar"
                    });
                }
            });
        }

        function btnToggleStatusTipoObs(idTipo) {
            Swal.fire({
                title: "¿Desea cambiar el estado?",
                text: "Si está activo, pasará a inactivo; y viceversa.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: "Sí, cambiar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('tipo_int_obs.toggle_status') }}",
                        type: "POST",
                        data: {
                            "_token": "{{ csrf_token() }}",
                            "id_tipo_int_obs": idTipo
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: "success",
                                title: "Hecho",
                                text: "El estado se actualizó correctamente.",
                                confirmButtonText: "Aceptar"
                            });
                            // Recargar la tabla
                            tablaTipoObs();
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: "No se pudo actualizar el estado.",
                                confirmButtonText: "Aceptar"
                            });
                        }
                    });
                }
            });
        }

        // Abrir modal de EDICIÓN por AJAX
        function btnEditarTipoObs(idTipo) {
            $.ajax({
                type: 'POST',
                url: "{{ route('tipo_int_obs.modals.md_edit_tipo_obs') }}",
                dataType: "json",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id_tipo_int_obs": idTipo
                },
                success: function(data) {
                    $("#modal_show_modal").html(data.html);
                    $("#modal_show_modal").modal('show');
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: "error",
                        text: "No se pudo cargar la información de la tipificación",
                        confirmButtonText: "Aceptar"
                    });
                }
            });
        }

        // ACTUALIZAR (update) un tipo de observación
        function btnUpdateTipoObs(idTipo) {
            var formData = new FormData();
            formData.append("tipo", $("#tipo").val());
            formData.append("tipo_obs", $("#tipo_obs").val());
            formData.append("status", "1");
            formData.append("numeracion", $("#numeracion").val());
            formData.append("nom_tipo_int_obs", $("#nom_tipo_int_obs").val());
            formData.append("descripcion", $("#descripcion").val()); // Agregado el campo descripcion
            formData.append("id_tipo_int_obs", idTipo);
            formData.append("_token", "{{ csrf_token() }}");


            $.ajax({
                type: 'POST',
                url: "{{ route('tipo_int_obs.update_tipo_obs') }}",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    document.getElementById("btnEnviarForm").innerHTML =
                        '<i class="fa fa-spinner fa-spin"></i> ESPERE';
                    document.getElementById("btnEnviarForm").disabled = true;
                },
                success: function(response) {
                    $("#modal_show_modal").modal('hide');
                    tablaTipoObs(); // Recargar la tabla
                    Swal.fire({
                        icon: 'success',
                        title: 'Tipo de Incidencia Actualizado',
                        text: 'El tipo ha sido actualizado correctamente.',
                        confirmButtonText: 'Aceptar'
                    });
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo actualizar el Tipo de Incidencia. ' + xhr.responseJSON
                            .message,
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        }

    // ELIMINAR un tipo de observación
    function btnEliminarTipoObs(idTipo) {
        Swal.fire({
            title: "¿Seguro que desea eliminar este tipo?",
            text: "Se eliminará completamente de los registros",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: "Aceptar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('tipo_int_obs.delete_tipo_obs') }}",
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "id_tipo_int_obs": idTipo
                    },
                    success: function(response) {
                        tablaTipoObs();
                        Swal.fire({
                            icon: "success",
                            title: 'Eliminado!',
                            text: "La tipificación ha sido eliminado con éxito.",
                            confirmButtonText: "Aceptar"
                        });
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: "error",
                            title: 'Error',
                            text: "No se pudo eliminar la tipificación.",
                            confirmButtonText: "Aceptar"
                        });
                    }
                });
            }
        });
    }
    </script>
@endsection
