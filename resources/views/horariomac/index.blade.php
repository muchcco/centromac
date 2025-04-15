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
                        <h4 class="page-title">Gestión de Horarios MAC</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('inicio') }}">Inicio</a></li>
                            <li class="breadcrumb-item active">Horarios MAC</li>
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
                    <h4 class="card-title text-white">Lista de Horarios MAC</h4>
                </div>

                <div class="card-body bootstrap-select-1">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <button class="btn btn-success" onclick="btnAddHorario()"><i class="fa fa-plus"
                                    aria-hidden="true"></i>
                                Agregar Horario</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive">
                                <div id="table_data">
                                    <!-- Aquí se carga la tabla de horarios -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para mostrar formularios de horarios -->
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
            cargarHorarios();
        });

        function cargarHorarios() {
            $.ajax({
                url: "{{ route('horariomac.tablas.tb_index') }}",
                type: 'GET',
                beforeSend: function() {
                    $('#table_data').html('<i class="fa fa-spinner fa-spin"></i> Cargando...');
                },
                success: function(data) {
                    $('#table_data').html(data);
                },
                error: function() {
                    $('#table_data').html('Error al cargar los horarios.');
                }
            });
        }


        function btnAddHorario() {
            $.ajax({
                type: 'POST',
                url: "{{ route('horariomac.modals.md_add_horarioMac') }}",
                dataType: "json",
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                success: function(data) {
                    // Carga el HTML del modal y abre el modal
                    $("#modal_show_modal").html(data.html);
                    $("#modal_show_modal").modal('show');
                },
                error: function(xhr, status, error) {
                    console.error("Error: " + status + " " + error);
                }
            });
        }




        function btnStoreHorario() {
            // Validación básica del frontend
            if ($('#horaingreso').val() == null || $('#horaingreso').val() == '') {
                $('#horaingreso').addClass("hasError");
            } else {
                $('#horaingreso').removeClass("hasError");
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
            //console.log("Centro MAC seleccionado:", $("#idcentro_mac").val()); // Esto debe mostrar el ID del centro MAC

            // Verifica que el valor de idcentromac esté siendo correctamente capturado
            // console.log($("#idcentromac").val()); // Asegúrate de que este valor sea el correcto

            // Preparación de los datos para enviar
            var formData = new FormData();
            formData.append("horaingreso", $("#horaingreso").val());
            formData.append("horasalida", $("#horasalida").val());
            formData.append("fechainicio", $("#fechainicio").val());
            formData.append("fechafin", $("#fechafin").val());
            formData.append("idcentro_mac", $("#idcentro_mac").val()); // Este valor debe estar presente aquí
            formData.append("idmodulo", $("#idmodulo").val());
            formData.append("_token", $("input[name=_token]").val());

            // Solicitud AJAX para almacenar el horario
            $.ajax({
                type: 'post',
                url: "{{ route('horariomac.store_horarioMac') }}", // Correct route
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
                        cargarHorarios(); // Function to reload the list of schedules
                        Swal.fire({
                            icon: "success",
                            text: "Horario agregado exitosamente",
                            confirmButtonText: "Aceptar"
                        });
                        $('#modal_show_modal').modal('hide'); // Close the modal
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
                        text: "Error al agregar el horario",
                        confirmButtonText: "Aceptar"
                    });
                }
            });
        }

        function btnEditHorario(idHorario) {
            $.ajax({
                type: 'POST',
                url: "{{ route('horariomac.modals.md_edit_horarioMac') }}", // Asegúrate de que esta ruta está correctamente configurada
                dataType: "json",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "idhorario": idHorario // Enviar el ID del horario que quieres editar
                },
                success: function(data) {
                    if (data.html) {
                        $("#modal_show_modal").html(data.html);
                        $("#modal_show_modal").modal('show');
                    } else {
                        Swal.fire({
                            icon: "error",
                            text: "No se pudo cargar la información del horario",
                            confirmButtonText: "Aceptar"
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.log(error);
                    Swal.fire({
                        icon: "error",
                        text: "No se pudo cargar la información del horario: " + error,
                        confirmButtonText: "Aceptar"
                    });
                }
            });
        }

        function btnUpdateHorario() {
            // Validación básica del frontend
            if ($('#horaingreso').val() == null || $('#horaingreso').val() == '') {
                $('#horaingreso').addClass("hasError");
            } else {
                $('#horaingreso').removeClass("hasError");
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
            formData.append("horaingreso", $("#horaingreso").val());
            formData.append("horasalida", $("#horasalida").val());
            formData.append("fechainicio", $("#fechainicio").val());
            formData.append("fechafin", $("#fechafin").val());
            formData.append("idcentro_mac", $("#idcentro_mac").val()); // Este valor debe estar presente aquí
            formData.append("idmodulo", $("#idmodulo").val());
            formData.append("idhorario", $("#idhorario").val()); // ID del horario a actualizar
            formData.append("_token", $("input[name=_token]").val());

            // Solicitud AJAX para actualizar el horario
            $.ajax({
                type: 'POST',
                url: "{{ route('horariomac.update_horarioMac') }}", // Correct route for update
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
                    if (data.status == 200) {
                        document.getElementById("btnEnviarForm").innerHTML = 'Actualizar';
                        document.getElementById("btnEnviarForm").disabled = false;
                        Swal.fire({
                            icon: "success",
                            text: "Horario actualizado exitosamente",
                            confirmButtonText: "Aceptar"
                        });
                        $('#modal_show_modal').modal('hide'); // Cerrar el modal
                        cargarHorarios(); // Recargar la lista de horarios
                    } else {
                        document.getElementById('alerta').innerHTML =
                            `<div class="alert alert-warning">${data.message}</div>`;
                    }
                },
                error: function(xhr, status, error) {
                    document.getElementById("btnEnviarForm").innerHTML = 'Actualizar';
                    document.getElementById("btnEnviarForm").disabled = false;
                    Swal.fire({
                        icon: "error",
                        text: "Error al actualizar el horario",
                        confirmButtonText: "Aceptar"
                    });
                }
            });
        }

        function btnDeleteHorario(idHorario) {
            Swal.fire({
                title: "¿Seguro que desea eliminar el horario?",
                text: "El horario será eliminado totalmente de sus registros",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: "Aceptar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('horariomac.delete_horarioMac') }}", // Ruta correcta para eliminar el horario
                        type: 'POST',
                        data: {
                            "_token": "{{ csrf_token() }}", // Token CSRF para seguridad
                            "id": idHorario // Asegúrate de pasar el id correcto
                        },
                        success: function(response) {
                            cargarHorarios(); // Recargar la tabla de horarios
                            Swal.fire({
                                icon: "success",
                                title: 'Eliminado!',
                                text: "El horario ha sido eliminado con éxito.",
                                confirmButtonText: "Aceptar"
                            });
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: "error",
                                title: 'Error',
                                text: "No se pudo eliminar el horario.",
                                confirmButtonText: "Aceptar"
                            });
                        }
                    });
                }
            });
        }
    </script>
@endsection
