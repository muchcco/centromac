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
                        <h4 class="page-title">Gestión de Personal Módulo</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('inicio') }}">Inicio</a></li>
                            <li class="breadcrumb-item active">Personal Módulo</li>
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
                    <h4 class="card-title text-white">Lista de Personal Módulo</h4>
                </div>

                <div class="card-body bootstrap-select-1">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <button class="btn btn-success" onclick="btnAddPersonalModulo()"><i class="fa fa-plus"
                                    aria-hidden="true"></i>
                                Agregar Personal Módulo</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive">
                                <div id="table_data">
                                    <!-- Aquí se carga la tabla de personal módulo -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para mostrar formularios de personal módulo -->
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
            cargarPersonalModulo();
        });

        function cargarPersonalModulo() {
            $.ajax({
                url: "{{ route('personalModuloI.tablas.tb_index') }}", // Cambia la ruta por la correcta para cargar la tabla
                type: 'GET',
                beforeSend: function() {
                    $('#table_data').html('<i class="fa fa-spinner fa-spin"></i> Cargando...');
                },
                success: function(data) {
                    $('#table_data').html(data);
                },
                error: function() {
                    $('#table_data').html('Error al cargar el personal módulo.');
                }
            });
        }

        function btnAddPersonalModulo() {
            $.ajax({
                type: 'post',
                url: "{{ route('personalModuloI.modals.md_add_personalModuloI') }}", // Cambia la ruta por la correcta para abrir el modal
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

        function btnStorePersonalModulo() {
            var formData = new FormData($('#form_personal_modulo')[0]);

            // Verificar que los campos necesarios estén presentes
            if (!formData.get('num_doc') || !formData.get('idmodulo') || !formData.get('fechainicio') || !formData.get(
                    'fechafin')) {
                alert("Todos los campos son obligatorios.");
                return;
            }

            $.ajax({
                type: 'POST',
                url: "{{ route('itinerante.store_itinerante') }}", // Ajustar la ruta según corresponda
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    document.getElementById("btnEnviarForm").innerHTML =
                        '<i class="fa fa-spinner fa-spin"></i> ESPERE';
                    document.getElementById("btnEnviarForm").disabled = true;
                },
                success: function(response) {
                    if (response.status == 201) {
                        // Mostrar el mensaje de éxito
                        Swal.fire({
                            icon: "success",
                            text: response.message,
                            confirmButtonText: "Aceptar"
                        });
                        cargarPersonalModulo();
                        // Cerrar el modal
                        $('#modal_show_modal').modal('hide');
                    } else {
                        // Si hay un mensaje de error, mostrarlo
                        document.getElementById('alerta').innerHTML =
                            `<div class="alert alert-warning">${response.message}</div>`;
                    }

                    // Restaurar el botón después de la solicitud
                    document.getElementById("btnEnviarForm").innerHTML = 'Guardar';
                    document.getElementById("btnEnviarForm").disabled = false;
                },
                error: function(xhr, status, error) {
                    // Capturamos la respuesta del backend
                    var response = xhr.responseJSON;

                    console.log(response); // Aquí estamos depurando la respuesta

                    if (response && response.message) {
                        // Mostrar el mensaje de error específico desde el backend
                        Swal.fire({
                            icon: "error",
                            text: response.message, // Mensaje de error del backend
                            confirmButtonText: "Aceptar"
                        });
                    } else {
                        // Si no hay mensaje específico, mostramos uno genérico
                        Swal.fire({
                            icon: "error",
                            text: "Hubo un error al procesar la solicitud. Intentar nuevamente.",
                            confirmButtonText: "Aceptar"
                        });
                    }

                    // Restaurar el botón después de la solicitud
                    document.getElementById("btnEnviarForm").innerHTML = 'Guardar';
                    document.getElementById("btnEnviarForm").disabled = false;
                }
            });
        }


        function btnEditPersonalModulo(id) {
            $.ajax({
                type: 'POST',
                url: "{{ route('personalModuloI.modals.md_edit_personalModuloI') }}",
                dataType: "json",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id": id
                },
                success: function(data) {
                    if (data.html) {
                        $("#modal_show_modal").html(data.html);
                        $("#modal_show_modal").modal('show');
                    } else {
                        Swal.fire({
                            icon: "error",
                            text: "No se pudo cargar la información",
                            confirmButtonText: "Aceptar"
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: "error",
                        text: "No se pudo cargar la información",
                        confirmButtonText: "Aceptar"
                    });
                }
            });
        }

        function btnUpdatePersonalModulo(id) {
            var formData = new FormData($('#form_personal_modulo')[0]);

            $.ajax({
                type: 'POST',
                url: "{{ route('personalModuloI.update_personalModuloI') }}",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    document.getElementById("btnEnviarForm").innerHTML =
                        '<i class="fa fa-spinner fa-spin"></i> Espere';
                    document.getElementById("btnEnviarForm").disabled = true;
                },
                success: function(response) {
                    $("#modal_show_modal").modal('hide');
                    cargarPersonalModulo();
                    Swal.fire({
                        icon: 'success',
                        title: 'Actualizado',
                        text: 'Registro actualizado correctamente.',
                        confirmButtonText: 'Aceptar'
                    });
                },
                error: function(xhr, status, error) {
                    document.getElementById("btnEnviarForm").innerHTML = 'Guardar';
                    document.getElementById("btnEnviarForm").disabled = false;

                    // Verificamos si el backend ha enviado el flag 'show_modal' para mostrar el modal de error
                    if (xhr.status === 422 && xhr.responseJSON.show_modal) {
                        let errorMessage = xhr.responseJSON.message;

                        // Mostrar el mensaje de error en el modal
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage, // Mostrar el mensaje de error que se envía desde el backend
                            confirmButtonText: 'Aceptar'
                        });
                    } else {
                        // Si no es el caso de error de validación, mostrar el error genérico
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: "Hubo un error al procesar la solicitud. Intentar nuevamente.",
                            confirmButtonText: 'Aceptar'
                        });
                    }
                }
            });
        }

        function btnDeletePersonalModulo(id) {
            Swal.fire({
                title: "¿Seguro que desea eliminar el registro?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: "Aceptar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('personalModuloI.delete_personalModuloI') }}",
                        type: 'POST',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            "id": id
                        },
                        success: function(response) {
                            cargarPersonalModulo();
                            Swal.fire({
                                icon: "success",
                                title: 'Eliminado!',
                                text: "Registro eliminado correctamente.",
                                confirmButtonText: "Aceptar"
                            });
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: "error",
                                title: 'Error',
                                text: "No se pudo eliminar.",
                                confirmButtonText: "Aceptar"
                            });
                        }
                    });
                }
            });
        }
    </script>
@endsection
