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
                        <h4 class="page-title">Interrupciones</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('inicio') }}">
                                    <i data-feather="home" class="align-self-center"></i>
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="javascript:void(0);" style="color: #7081b9;">Gestión de Interrupciones</a>
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
                    <h4 class="card-title text-white">Listado de Interrupciones</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <button class="btn btn-success" data-toggle="modal" data-target="#modal_add_interrupcion"
                                onclick="btnAddInterrupcion()">
                                <i class="fa fa-plus"></i> Nueva Interrupción
                            </button>
                            <a href="{{ route('interrupcion.export_excel') }}" class="btn btn-outline-primary">
                                <i class="fa fa-file-excel"></i> Exportar Excel
                            </a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive" id="table_data"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal contenedor general -->
    <div class="modal fade" id="modal_show_modal" tabindex="-1" role="dialog"></div>
@endsection

@section('script')
    <script src="{{ asset('Script/js/sweet-alert.min.js') }}"></script>
    <script src="{{ asset('Vendor/toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('//cdn.jsdelivr.net/npm/sweetalert2@11') }}"></script>

    <!-- Plugins -->
    <script src="{{ asset('nuevo/plugins/select2/select2.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            cargarTablaInterrupciones();
        });

        function cargarTablaInterrupciones() {
            $.ajax({
                type: 'GET',
                url: "{{ route('interrupcion.tablas.tb_index') }}",
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

        function btnAddInterrupcion() {
            $.ajax({
                type: 'POST',
                url: "{{ route('interrupcion.modals.md_add_interrupcion') }}",
                dataType: "json",
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                success: function(data) {
                    $("#modal_show_modal").html(data.html);
                    $("#modal_show_modal").modal('show');
                    setTimeout(() => {
                        $('#id_tipo_int_obs, #identidad, #estado_final').select2({
                            dropdownParent: $('#modal_show_modal'),
                            width: '100%',
                            allowClear: true
                        });
                    }, 300);
                },
                error: function(xhr, status, error) {
                    console.error("Error: " + status + " " + error);
                }
            });
        }
        // =====================================================
        // ===============  VALIDACIÓN GLOBAL ==================
        // =====================================================
        function validarFechasHoras(form, estado) {
            if (estado && estado.toUpperCase() === 'CERRADO') {
                // ✅ Detecta valores aunque los campos no existan o estén deshabilitados
                let fechaInicio = form.find('[name="fecha_inicio"]').val() || form.find('input[type="date"]:disabled')
                .val() || '';
                let horaInicio = form.find('[name="hora_inicio"]').val() || form.find('input[type="time"]:disabled')
                .val() || '';
                let fechaFin = form.find('[name="fecha_fin"]').val() || '';
                let horaFin = form.find('[name="hora_fin"]').val() || '';

                // 1️⃣ Verificar campos completos
                if (!fechaFin || !horaFin) {
                    Swal.fire({
                        icon: "warning",
                        text: "Debe ingresar la Fecha y Hora de Fin para un estado CERRADO.",
                        confirmButtonText: "Aceptar"
                    });
                    return false;
                }

                // ⚠️ Si no hay inicio (subsanar), solo valida existencia de fin
                if (!fechaInicio || !horaInicio) {
                    // no hay inicio => no comparar, solo verificar que fin no esté vacío
                    return true;
                }

                // 2️⃣ Validar fechas
                if (fechaFin < fechaInicio) {
                    Swal.fire({
                        icon: "warning",
                        text: "La Fecha de Fin no puede ser anterior a la Fecha de Inicio.",
                        confirmButtonText: "Aceptar"
                    });
                    return false;
                }

                // 3️⃣ Si son iguales, comparar horas
                if (fechaFin === fechaInicio && horaFin <= horaInicio) {
                    Swal.fire({
                        icon: "warning",
                        text: "La Hora de Fin debe ser mayor a la Hora de Inicio cuando las fechas son iguales.",
                        confirmButtonText: "Aceptar"
                    });
                    return false;
                }

                // 4️⃣ Validación total combinada
                const inicio = new Date(`${fechaInicio}T${horaInicio}`);
                const fin = new Date(`${fechaFin}T${horaFin}`);

                if (isNaN(inicio.getTime()) || isNaN(fin.getTime())) {
                    Swal.fire({
                        icon: "warning",
                        text: "Las fechas u horas no son válidas.",
                        confirmButtonText: "Aceptar"
                    });
                    return false;
                }

                if (fin <= inicio) {
                    Swal.fire({
                        icon: "warning",
                        text: "La Fecha y Hora de Fin deben ser posteriores a las de Inicio.",
                        confirmButtonText: "Aceptar"
                    });
                    return false;
                }
            }
            return true;
        }

        function btnStoreInterrupcion() {
            let form = $('#form_add_interrupcion');
            let estado = $('#estado').val();

            // ✅ Llamamos a la función de validación
            if (!validarFechasHoras(form, estado)) return;

            var formData = new FormData($('#form_add_interrupcion')[0]);

            $.ajax({
                type: 'POST',
                url: "{{ route('interrupcion.store') }}",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $("#btnEnviarForm").html('<i class="fa fa-spinner fa-spin"></i> ESPERE')
                        .prop("disabled", true);
                },
                success: function(data) {
                    $("#btnEnviarForm").html('Guardar').prop("disabled", false);
                    if (data.status == 201) {
                        cargarTablaInterrupciones();
                        Swal.fire({
                            icon: "success",
                            text: "Interrupción creada exitosamente",
                            confirmButtonText: "Aceptar"
                        });
                        $('#modal_show_modal').modal('hide');
                    } else {
                        $('#alerta').html(`<div class="alert alert-warning">${data.message}</div>`);
                    }
                },
                error: function(xhr, status, error) {
                    $("#btnEnviarForm").html('Guardar').prop("disabled", false);
                    Swal.fire({
                        icon: "error",
                        text: "Error al crear la Interrupción",
                        confirmButtonText: "Aceptar"
                    });
                }
            });
        }

        function btnEditarInterrupcion(id) {
            $.ajax({
                type: 'POST',
                url: "{{ route('interrupcion.edit') }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id_interrupcion": id
                },
                success: function(response) {
                    $("#modal_show_modal").html(response.html);
                    $("#modal_show_modal").modal('show');
                    setTimeout(() => {
                        $('#responsable, #identidad, #id_tipo_int_obs, #estado_final').select2({
                            dropdownParent: $('#modal_show_modal'),
                            width: '100%',
                            allowClear: true
                        });
                    }, 300);
                },
                error: function() {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "No se pudo cargar la información para editar.",
                        confirmButtonText: "Aceptar"
                    });
                }
            });
        }

        function btnUpdateInterrupcion() {
            let form = $('#form_edit_interrupcion');
            let estado = $('#estado').val();

            // ✅ Llamamos a la función de validación
            if (!validarFechasHoras(form, estado)) return;

            var formData = new FormData($('#form_edit_interrupcion')[0]);

            $.ajax({
                type: 'POST',
                url: "{{ route('interrupcion.update') }}",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $("#btnEnviarForm").html('<i class="fa fa-spinner fa-spin"></i> ESPERE')
                        .prop("disabled", true);
                },
                success: function(data) {
                    $("#btnEnviarForm").html('Guardar').prop("disabled", false);
                    if (data.status == 200) {
                        cargarTablaInterrupciones();
                        Swal.fire({
                            icon: "success",
                            text: "Interrupción actualizada exitosamente",
                            confirmButtonText: "Aceptar"
                        });
                        $('#modal_show_modal').modal('hide');
                    } else {
                        $('#alerta').html(`<div class="alert alert-warning">${data.message}</div>`);
                    }
                },
                error: function() {
                    $("#btnEnviarForm").html('Guardar').prop("disabled", false);
                    Swal.fire({
                        icon: "error",
                        text: "Error al actualizar la interrupción",
                        confirmButtonText: "Aceptar"
                    });
                }
            });
        }

        function btnEliminarInterrupcion(id) {
            Swal.fire({
                title: "¿Estás seguro?",
                text: "Se eliminará esta interrupción de forma permanente.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('interrupcion.delete') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            id_interrupcion: id
                        },
                        success: function(response) {
                            cargarTablaInterrupciones();
                            Swal.fire({
                                icon: "success",
                                title: "Eliminado",
                                text: "La interrupción fue eliminada exitosamente."
                            });
                        },
                        error: function() {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: "No se pudo eliminar la interrupción. Intenta más tarde."
                            });
                        }
                    });
                }
            });
        }

        function btnSubsanarInterrupcion(id) {
            $.post("{{ route('interrupcion.modals.md_subsanar_interrupcion') }}", {
                _token: "{{ csrf_token() }}",
                id_interrupcion: id
            }, function(data) {
                $('#modal_show_modal').html(data.html);
                $('#modal_show_modal').modal('show');
            }).fail(function() {
                Swal.fire({
                    icon: "error",
                    text: "Error al cargar el formulario de subsanación.",
                    confirmButtonText: "Aceptar"
                });
            });
        }

        function btnSubsanarGuardar() {
            let form = $('#form_subsanar_interrupcion');

            // 🔍 Buscar el campo de estado (en cualquiera de sus variantes)
            let estado = form.find('[name="estado"]').val() ||
                form.find('[name="estado_final"]').val() ||
                form.find('#estado').val() ||
                form.find('#estado_final').val() ||
                ''; // si no hay campo, queda vacío

            // ✅ Validar fechas y horas antes de enviar
            if (!validarFechasHoras(form, estado)) return;

            let formData = new FormData(form[0]);

            $.ajax({
                url: "{{ route('interrupcion.subsanar') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $('#btnEnviarForm')
                        .html('<i class="fa fa-spinner fa-spin"></i> Guardando...')
                        .prop('disabled', true);
                },
                success: function(data) {
                    $('#btnEnviarForm').html('Guardar').prop('disabled', false);
                    if (data.status === 200) {
                        $('#modal_show_modal').modal('hide');
                        cargarTablaInterrupciones();
                        Swal.fire({
                            icon: 'success',
                            text: 'Interrupción cerrado correctamente',
                            confirmButtonText: 'Aceptar'
                        });
                    } else {
                        $('#alerta').html(`<div class="alert alert-warning">${data.message}</div>`);
                    }
                },
                error: function() {
                    $('#btnEnviarForm').html('Guardar').prop('disabled', false);
                    Swal.fire({
                        icon: 'error',
                        text: 'Error al guardar la subsanación',
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        }
    </script>
@endsection
