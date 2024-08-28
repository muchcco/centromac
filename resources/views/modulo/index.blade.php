@extends('layouts.layout')

@section('style')
    <link rel="stylesheet" href="{{ asset('Vendor/toastr/toastr.min.css') }}">
    <!-- Plugins css -->
    <link href="{{ asset('nuevo/plugins/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('nuevo/plugins/timepicker/bootstrap-material-datetimepicker.css') }}" rel="stylesheet">
    <link href="{{ asset('nuevo/plugins/bootstrap-touchspin/css/jquery.bootstrap-touchspin.min.css') }}" rel="stylesheet" />
    <!-- DataTables -->
    <link href="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('nuevo/plugins/datatables/buttons.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Responsive datatable examples -->
    <link href="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('main')
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="row">
                    <div class="col">
                        <h4 class="page-title">Gestión de Módulos</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('inicio') }}">Inicio</a></li>
                            <li class="breadcrumb-item active">Módulos</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header" style="background-color:#132842">
                    <h4 class="card-title text-white">Lista de Módulos</h4>
                </div>

                <div class="card-body bootstrap-select-1">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <button class="btn btn-success" onclick="btnAddModulo()"><i class="fa fa-plus"
                                    aria-hidden="true"></i>
                                Agregar Módulo</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive">
                                <div id="table_data">
                                    <!-- Aquí se carga la tabla de módulos -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para mostrar formularios de módulos -->
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
        $(document).ready(function() {
            cargarModulos();
        });

        function cargarModulos() {
            $.ajax({
                url: "{{ route('modulos.tablas.tb_index') }}",
                type: 'GET',
                beforeSend: function() {
                    $('#table_data').html('<i class="fa fa-spinner fa-spin"></i> Cargando...');
                },
                success: function(data) {
                    $('#table_data').html(data);
                },
                error: function() {
                    $('#table_data').html('Error al cargar los módulos.');
                }
            });
        }

        function btnAddModulo() {
            $.ajax({
                type: 'post',
                url: "{{ route('modulos.modals.md_add_modulo') }}", // Cambia la ruta a la correcta para abrir el modal de añadir módulo
                dataType: "json",
                data: {
                    "_token": "{{ csrf_token() }}" // Incluir CSRF token
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

        function btnStoreModulo() {
            // Validación básica del frontend
            if ($('#nombre_modulo').val() == null || $('#nombre_modulo').val() == '') {
                $('#nombre_modulo').addClass("hasError");
            } else {
                $('#nombre_modulo').removeClass("hasError");
            }
            if ($('#fecha_inicio').val() == null || $('#fecha_inicio').val() == '') {
                $('#fecha_inicio').addClass("hasError");
            } else {
                $('#fecha_inicio').removeClass("hasError");
            }
            if ($('#fecha_fin').val() == null || $('#fecha_fin').val() == '') {
                $('#fecha_fin').addClass("hasError");
            } else {
                $('#fecha_fin').removeClass("hasError");
            }

            // Preparación de los datos para enviar
            var formData = new FormData();
            formData.append("nombre_modulo", $("#nombre_modulo").val());
            formData.append("fecha_inicio", $("#fecha_inicio").val());
            formData.append("fecha_fin", $("#fecha_fin").val());
            formData.append("entidad_id", $("#entidad_id").val());
            formData.append("id_centromac", $("#id_centromac").val());
            formData.append("_token", $("input[name=_token]").val());

            // Solicitud AJAX para almacenar el módulo
            $.ajax({
                type: 'post',
                url: "{{ route('modulos.store_modulo') }}", // Ruta correcta en tus rutas de Laravel
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
                    if (data.status == 201) {
                        document.getElementById("btnEnviarForm").innerHTML = 'Guardar';
                        document.getElementById("btnEnviarForm").disabled = false;
                        cargarModulos(); // Función para recargar la lista de módulos
                        Swal.fire({
                            icon: "success",
                            text: "Módulo agregado exitosamente",
                            confirmButtonText: "Aceptar"
                        });
                        $('#modal_show_modal').modal('hide'); // Cerrar el modal
                    } else {
                        document.getElementById('alerta').innerHTML =
                            `<div class="alert alert-warning">${data.message}</div>`;
                    }
                },
                error: function(xhr, status, error) {
                    document.getElementById("btnEnviarForm").innerHTML = 'Guardar';
                    document.getElementById("btnEnviarForm").disabled = false;
                    Swal.fire({
                        icon: "error",
                        text: "Error al agregar el módulo",
                        confirmButtonText: "Aceptar"
                    });
                }
            });
        }

        function btnEditModulo(idModulo) {
            $.ajax({
                type: 'POST',
                url: "{{ route('modulos.modals.md_edit_modulo') }}", // Asegúrate de que esta ruta está correctamente configurada en tus rutas de Laravel
                dataType: "json",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id_modulo": idModulo // Envía el ID del módulo que quieres editar
                },
                success: function(data) {
                    if (data.html) {
                        $("#modal_show_modal").html(data.html);
                        $("#modal_show_modal").modal('show');
                    } else {
                        Swal.fire({
                            icon: "error",
                            text: "No se pudo cargar la información del módulo",
                            confirmButtonText: "Aceptar"
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: "error",
                        text: "No se pudo cargar la información del módulo "+error,
                        confirmButtonText: "Aceptar"
                    });
                }
            });
        }

        function btnUpdateModulo(id) {
            var formData = new FormData();
            formData.append("nombre_modulo", $("#nombre_modulo").val()); // Recoge el nombre del módulo
            formData.append("fecha_inicio", $("#fecha_inicio").val()); // Recoge la fecha de inicio
            formData.append("fecha_fin", $("#fecha_fin").val()); // Recoge la fecha de fin
            formData.append("entidad_id", $("#entidad_id").val()); // Recoge la entidad seleccionada
            formData.append("id_modulo", id); // Asegúrate de enviar el id_modulo correctamente
            formData.append("_token", $("input[name=_token]").val()); // CSRF token

            $.ajax({
                type: 'POST',
                url: "{{ route('modulos.update_modulo') }}", // Asegúrate de que esta es la ruta correcta para la actualización del módulo
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    document.getElementById("btnEnviarForm").innerHTML =
                        '<i class="fa fa-spinner fa-spin"></i> Espere';
                    document.getElementById("btnEnviarForm").disabled = true;
                },
                success: function(response) {
                    $("#modal_show_modal").modal('hide'); // Oculta el modal una vez la actualización es exitosa
                    cargarModulos(); // Llama a la función para recargar la tabla de módulos
                    Swal.fire({
                        icon: 'success',
                        title: 'Módulo Actualizado',
                        text: 'El módulo ha sido actualizado correctamente.',
                        confirmButtonText: 'Aceptar'
                    });
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo actualizar el módulo. ' + xhr.responseJSON.message,
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        }

        function btnDeleteModulo(idModulo) {
            Swal.fire({
                title: "¿Seguro que desea eliminar el módulo?",
                text: "El módulo será eliminado totalmente de sus registros",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: "Aceptar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('modulos.delete_modulo') }}",
                        type: 'POST',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            "id": idModulo
                        },
                        success: function(response) {
                            cargarModulos(); // Refresh the modules table
                            Swal.fire({
                                icon: "success",
                                title: 'Eliminado!',
                                text: "El módulo ha sido eliminado con éxito.",
                                confirmButtonText: "Aceptar"
                            });
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: "error",
                                title: 'Error',
                                text: "No se pudo eliminar el módulo.",
                                confirmButtonText: "Aceptar"
                            });
                        }
                    });
                }
            });
        }
    </script>
@endsection
