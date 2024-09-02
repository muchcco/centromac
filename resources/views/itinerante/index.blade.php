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
                        <h4 class="page-title">Gestión de Itinerantes</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('inicio') }}">Inicio</a></li>
                            <li class="breadcrumb-item active">Itinerantes</li>
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
                    <h4 class="card-title text-white">Lista de Itinerantes</h4>
                </div>

                <div class="card-body bootstrap-select-1">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <button class="btn btn-success" onclick="btnAddItinerante()"><i class="fa fa-plus"
                                    aria-hidden="true"></i>
                                Agregar Itinerante</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive">
                                <div id="table_data">
                                    <!-- Aquí se carga la tabla de itinerantes -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para mostrar formularios de itinerantes -->
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
            cargarItinerantes();
        });

        function cargarItinerantes() {
            $.ajax({
                url: "{{ route('itinerante.tablas.tb_index') }}",
                type: 'GET',
                beforeSend: function() {
                    $('#table_data').html('<i class="fa fa-spinner fa-spin"></i> Cargando...');
                },
                success: function(data) {
                    $('#table_data').html(data);
                },
                error: function() {
                    $('#table_data').html('Error al cargar los itinerante.');
                }
            });
        }

        function btnAddItinerante() {
            $.ajax({
                type: 'post',
                url: "{{ route('itinerante.modals.md_add_itinerante') }}",
                dataType: "json",
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                success: function(data) {
                    $("#modal_show_modal").html(data.html);
                    $("#modal_show_modal").modal('show');
                },
                error: function() {
                    toastr.error('Error al cargar el formulario de añadir itinerante.');
                }
            });
        }

        function btnEditItinerante(idItinerante) {
            $.ajax({
                type: 'POST',
                url: "{{ route('itinerante.modals.md_edit_itinerante') }}", // Asegúrate de que esta ruta está correctamente configurada en tus rutas de Laravel
                dataType: "json",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id_itinerante": idItinerante // Envía el ID del itinerante que quieres editar
                },
                success: function(data) {
                    if (data.html) {
                        $("#modal_show_modal").html(data.html);
                        $("#modal_show_modal").modal('show');
                    } else {
                        Swal.fire({
                            icon: "error",
                            text: "No se pudo cargar la información del itinerante",
                            confirmButtonText: "Aceptar"
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: "error",
                        text: "No se pudo cargar la información del itinerante: " + error,
                        confirmButtonText: "Aceptar"
                    });
                }
            });
        }


        function btnStoreItinerante() {
            // Validación básica del frontend
            if ($('#IDCENTRO_MAC').val() == null || $('#IDCENTRO_MAC').val() == '') {
                $('#IDCENTRO_MAC').addClass("hasError");
            } else {
                $('#IDCENTRO_MAC').removeClass("hasError");
            }
            if ($('#NUM_DOC').val() == null || $('#NUM_DOC').val() == '') {
                $('#NUM_DOC').addClass("hasError");
            } else {
                $('#NUM_DOC').removeClass("hasError");
            }
            if ($('#IDMODULO').val() == null || $('#IDMODULO').val() == '') {
                $('#IDMODULO').addClass("hasError");
            } else {
                $('#IDMODULO').removeClass("hasError");
            }
            if ($('#fechainicio').val() == null || $('#fechainicio').val() == '') {
                $('#fechainicio').addClass("hasError");
            } else {
                $('#fechainicio').removeClass("hasError");
            }
            if ($('#fechafin').val() == null || $('#fechafin').val() == '') {
                $('#fechafin').addClass("hasError");
            } else {
                $('#fechafin').removeClass("hasError");
            }

            // Preparación de los datos para enviar
            var formData = new FormData();
            formData.append("IDCENTRO_MAC", $("#IDCENTRO_MAC").val());
            formData.append("NUM_DOC", $("#NUM_DOC").val());
            formData.append("IDMODULO", $("#IDMODULO").val());
            formData.append("FECHAINICIO", $("#fechainicio").val());
            formData.append("FECHAFIN", $("#fechafin").val());
            formData.append("_token", $("#_token").val());

            // Solicitud AJAX para almacenar el itinerante
            $.ajax({
                type: 'POST',
                url: "{{ route('itinerante.store_itinerante') }}", // Ruta correcta en tus rutas de Laravel
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
                        cargarItinerantes(); // Función para recargar la lista de itinerantes
                        Swal.fire({
                            icon: "success",
                            text: "Itinerante agregado exitosamente",
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
                        text: "Error al agregar el itinerante",
                        confirmButtonText: "Aceptar"
                    });
                }
            });
        }


        function btnUpdateItinerante(id) {
            var formData = new FormData();
            formData.append("IDCENTRO_MAC", $("#IDCENTRO_MAC").val()); // Recoge el centro MAC seleccionado
            formData.append("NUM_DOC", $("#NUM_DOC").val()); // Recoge el número de documento del personal seleccionado
            formData.append("IDMODULO", $("#IDMODULO").val()); // Recoge el módulo seleccionado
            formData.append("fechainicio", $("#fechainicio").val()); // Recoge la fecha de inicio
            formData.append("fechafin", $("#fechafin").val()); // Recoge la fecha de fin
            formData.append("ID", id); // Asegúrate de enviar el id_itinerante correctamente
            formData.append("_token", $("input[name=_token]").val()); // CSRF token

            $.ajax({
                type: 'POST',
                url: "{{ route('itinerante.update_itinerante') }}", // Asegúrate de que esta es la ruta correcta para la actualización del itinerante
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
                    cargarItinerantes(); // Llama a la función para recargar la tabla de itinerantes
                    Swal.fire({
                        icon: 'success',
                        title: 'Itinerante Actualizado',
                        text: 'El itinerante ha sido actualizado correctamente.',
                        confirmButtonText: 'Aceptar'
                    });
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo actualizar el itinerante. ' + xhr.responseJSON.message,
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        }

        function btnDeleteItinerante(idItinerante) {
            Swal.fire({
                title: "¿Seguro que desea eliminar el itinerante?",
                text: "El itinerante será eliminado totalmente de sus registros",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: "Aceptar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('itinerante.delete_itinerante') }}",
                        type: 'POST',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            "id": idItinerante
                        },
                        success: function(response) {
                            cargarItinerantes(); // Refresh the itinerants table
                            Swal.fire({
                                icon: "success",
                                title: 'Eliminado!',
                                text: "El itinerante ha sido eliminado con éxito.",
                                confirmButtonText: "Aceptar"
                            });
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: "error",
                                title: 'Error',
                                text: "No se pudo eliminar el itinerante.",
                                confirmButtonText: "Aceptar"
                            });
                        }
                    });
                }
            });
        }
    </script>
@endsection
