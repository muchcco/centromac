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
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="row">
                    <div class="col">
                        <h4 class="page-title">Gestión de Feriados</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('inicio') }}">Inicio</a></li>
                            <li class="breadcrumb-item active">Feriados</li>
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
                    <h4 class="card-title text-white">Lista de Feriados</h4>
                </div>

                <div class="card-body bootstrap-select-1">

                    <div class="row">
                        <div class="col-12 mb-3">
                            <button class="btn btn-success" data-toggle="modal" data-target="#large-Modal"
                                onclick="btnAddFeriado()"><i class="fa fa-plus" aria-hidden="true"></i>
                                Agregar Feriado</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive">
                                <div class="table-responsive" id="table_data">

                                </div>
                            </div>
                        </div>

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
    {{-- <script src="{{ asset('nuevo/plugins/huebee/huebee.pkgd.min.js') }}"></script> --}}
    <script src="{{ asset('nuevo/plugins/timepicker/bootstrap-material-datetimepicker.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/bootstrap-touchspin/js/jquery.bootstrap-touchspin.min.js') }}"></script>
    {{-- <script src="{{ asset('nuevo/assets/pages/jquery.forms-advanced.js') }}"></script> --}}
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
            tabla_seccion();
        });

        function tabla_seccion() {
            $.ajax({
                type: 'GET',
                url: "{{ route('feriados.tablas.tb_index') }}",
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
        var isMayus = (e) => {
            e.value = e.value.toUpperCase();
        }

        function btnAddFeriado() {
            $.ajax({
                type: 'post',
                url: "{{ route('feriados.modals.md_add_feriado') }}",
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

        function btnStoreFeriado() {
            // Validación básica del frontend
            if ($('#nombre_feriado').val() == null || $('#nombre_feriado').val() == '') {
                $('#nombre_feriado').addClass("hasError");
            } else {
                $('#nombre_feriado').removeClass("hasError");
            }
            if ($('#fecha_feriado').val() == null || $('#fecha_feriado').val() == '') {
                $('#fecha_feriado').addClass("hasError");
            } else {
                $('#fecha_feriado').removeClass("hasError");
            }

            // Preparación de los datos para enviar
            var formData = new FormData();
            formData.append("nombre_feriado", $("#nombre_feriado").val());
            formData.append("fecha_feriado", $("#fecha_feriado").val());
            formData.append("_token", $("input[name=_token]").val());

            // Solicitud AJAX para almacenar el feriado
            $.ajax({
                type: 'post',
                url: "{{ route('feriados.store_feriado') }}",
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
                        cargarFeriados();
                        Swal.fire({
                            icon: "success",
                            text: "Feriado agregado exitosamente",
                            confirmButtonText: "Aceptar"
                        });
                        $('#modal_show_modal').modal('hide'); // Cerrar el modal
                        // Aquí puedes añadir código para actualizar la vista o tabla si es necesario
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
                        text: "Error al agregar el feriado",
                        confirmButtonText: "Aceptar"
                    });
                }
            });
        }

        function btnEditFeriado(idFeriado) {
            $.ajax({
                type: 'POST',
                url: "{{ route('feriados.modals.md_edit_feriado') }}", // Asegúrate de que esta ruta está correctamente configurada en tus rutas de Laravel
                dataType: "json",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id_feriado": idFeriado // Envía el ID del feriado que quieres editar
                },
                success: function(data) {
                    $("#modal_show_modal").html(data.html);
                    $("#modal_show_modal").modal('show');
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: "error",
                        text: "No se pudo cargar la información del feriado",
                        confirmButtonText: "Aceptar"
                    });
                }
            });
        }

        function cargarFeriados() {
            $.ajax({
                url: "{{ route('feriados.tablas.tb_index') }}", // Asegúrate de que la ruta está bien definida en tus rutas de Laravel
                type: 'GET',
                success: function(data) {
                    $('#table_data').html(
                        data); // Asume que tienes un div con id='table_data' donde cargas la tabla
                },
                error: function() {
                    $('#table_data').html('Error al cargar los feriados.');
                }
            });
        }

        function btnUpdateFeriado(id) {
            var formData = new FormData();
            formData.append("nombre_feriado", $("#nombre_feriado")
                .val()); // Recoge el nombre del feriado del campo de entrada
            formData.append("fecha_feriado", $("#fecha_feriado").val()); // Recoge la fecha del feriado del campo de entrada
            formData.append("id_feriado", id); // Asegúrate de enviar el id_feriado correctamente
            formData.append("_token", $("input[name=_token]").val());

            $.ajax({
                type: 'POST',
                url: "{{ route('feriados.update_feriado') }}", // Asegúrate de que esta es la ruta correcta y que está definida en tus rutas de Laravel
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
                    // Aquí deberías recargar la sección o tabla donde se muestran los feriados

                    cargarFeriados(); // Llama a la función para recargar la tabla de feriados
                    Swal.fire({
                        icon: 'success',
                        title: 'Feriado Actualizado',
                        text: 'El feriado ha sido actualizado correctamente.',
                        confirmButtonText: 'Aceptar'
                    });
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo actualizar el feriado. ' + xhr.responseJSON.message,
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        }

        function btnDeleteFeriado(idFeriado) {
            Swal.fire({
                title: "¿Seguro que desea eliminar el feriado?",
                text: "El feriado será eliminado totalmente de sus registros",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: "Aceptar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('feriados.delete_feriado') }}",
                        type: 'post',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            "id": idFeriado
                        },
                        success: function(response) {
                            // Llama a la función para cargar de nuevo los feriados
                            cargarFeriados(); // Asegúrate de que esta función actualiza correctamente la tabla
                            Swal.fire({
                                icon: "success",
                                title: 'Eliminado!',
                                text: "El feriado ha sido eliminado con éxito.",
                                confirmButtonText: "Aceptar"
                            });
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: "error",
                                title: 'Error',
                                text: "No se pudo eliminar el feriado.",
                                confirmButtonText: "Aceptar"
                            });
                        }
                    });
                }
            });
        }

    </script>
@endsection
