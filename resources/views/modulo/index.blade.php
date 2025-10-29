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

    <!-- 🔹 FILTROS PRINCIPALES -->
    <div class="card mb-3">
        <div class="card-header" style="background-color:#132842">
            <h4 class="card-title text-white mb-0">Filtro de Búsqueda</h4>
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                <!-- Centro MAC -->
                <div class="col-md-4">
                    <label class="fw-bold text-dark mb-2">Centro MAC:</label>
                    @role('Administrador|Moderador')
                        <select id="filtro_mac" class="form-control select2">
                            <option value="">-- Todos los MAC --</option>
                            @foreach ($macs as $mac)
                                <option value="{{ $mac->IDCENTRO_MAC }}">{{ $mac->NOMBRE_MAC }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" class="form-control text-uppercase"
                            value="{{ $centro_mac->name_mac ?? 'No asignado' }}" readonly>
                        <input type="hidden" id="filtro_mac" value="{{ $centro_mac->idmac }}">
                    @endrole
                </div>

                <!-- Entidad -->
                <div class="col-md-4">
                    <label class="fw-bold text-dark mb-2">Entidad:</label>
                    <select id="filtro_entidad" class="form-control select2">
                        <option value="">-- Todas --</option>
                        @foreach ($entidades as $ent)
                            <option value="{{ $ent->IDENTIDAD }}">{{ $ent->NOMBRE_ENTIDAD }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Administrativo -->
                <div class="col-md-2">
                    <label class="fw-bold text-dark mb-2">Administrativo:</label>
                    <select id="filtro_admin" class="form-control">
                        <option value="">-- Todos --</option>
                        <option value="SI">Sí</option>
                        <option value="NO">No</option>
                    </select>
                </div>

                <!-- Botones -->
                <div class="col-md-2 d-flex align-items-end">
                    <div class="d-flex gap-1 w-100">
                        <button class="btn btn-primary w-50" onclick="filtrarModulos()">
                            <i class="fa fa-search"></i> Buscar
                        </button>
                        <button class="btn btn-dark w-50" onclick="limpiarFiltro()">
                            <i class="fa fa-undo"></i> Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 🔹 LISTA DE MÓDULOS -->
    <div class="card">
        <div class="card-header" style="background-color:#132842">
            <h4 class="card-title text-white mb-0">Lista de Módulos</h4>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <button class="btn btn-success" onclick="btnAddModulo()">
                    <i class="fa fa-plus"></i> Agregar Módulo
                </button>
            </div>

            <div class="table-responsive">
                <div id="table_data"></div>
            </div>
        </div>
    </div>

    <!-- Modal -->
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
            $('.select2').select2();
            cargarModulos();
        });

        $(document).ready(function() {
            cargarModulos();
        });

        function cargarModulos(mac = '', entidad = '', admin = '') {
            $.ajax({
                url: "{{ route('modulos.tablas.tb_index') }}",
                type: 'GET',
                data: {
                    id_mac: mac,
                    id_entidad: entidad,
                    es_admin: admin
                },
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

        function filtrarModulos() {
            const mac = $('#filtro_mac').val();
            const entidad = $('#filtro_entidad').val();
            const admin = $('#filtro_admin').val();
            cargarModulos(mac, entidad, admin);
        }

        function btnAddModulo() {
            $.ajax({
                type: 'post',
                url: "{{ route('modulos.modals.md_add_modulo') }}",
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
            formData.append("es_administrativo", $("#es_administrativo").val()); // Agregar el valor de ES_ADMINISTRATIVO
            formData.append("_token", $("input[name=_token]").val());
            var inicio = new Date($("#fecha_inicio").val());
            var fin = new Date($("#fecha_fin").val());
            if (fin < inicio) {
                Swal.fire({
                    icon: "warning",
                    title: "Fechas inválidas",
                    text: "La fecha de fin no puede ser menor que la fecha de inicio.",
                    confirmButtonText: "Entendido"
                });
                return; // 🚫 Detener envío
            }

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
                        text: "No se pudo cargar la información del módulo " + error,
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
            formData.append("es_administrativo", $("#es_administrativo").val()); // Agregar el valor de ES_ADMINISTRATIVO
            formData.append("_token", $("input[name=_token]").val()); // CSRF token
            var inicio = new Date($("#fecha_inicio").val());
            var fin = new Date($("#fecha_fin").val());
            if (fin < inicio) {
                Swal.fire({
                    icon: "warning",
                    title: "Fechas inválidas",
                    text: "La fecha de fin no puede ser menor que la fecha de inicio.",
                    confirmButtonText: "Entendido"
                });
                return; // 🚫 Detener envío
            }

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
        } // Abrir modal para cambiar entidad
        function btnCambiarEntidad(idModulo) {
            $.ajax({
                type: 'POST',
                url: "{{ route('modulos.modals.md_cambiar_entidad') }}", // ✅ esta es la correcta
                dataType: "json",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id_modulo": idModulo
                },
                beforeSend: function() {
                    $("#modal_show_modal").html(
                        '<div class="text-center p-4"><i class="fa fa-spinner fa-spin fa-2x text-secondary"></i><br>Cargando...</div>'
                    );
                },
                success: function(data) {
                    if (data.html) {
                        $("#modal_show_modal").html(data.html);
                        $("#modal_show_modal").modal('show');
                    } else {
                        Swal.fire({
                            icon: "error",
                            text: "No se pudo cargar el formulario de cambio de entidad",
                            confirmButtonText: "Aceptar"
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Error al cargar el modal de cambio de entidad.",
                        confirmButtonText: "Aceptar"
                    });
                }
            });
        }

        // Guardar cambio de entidad
        function btnGuardarCambioEntidad(idModulo) {
            var formData = new FormData(document.getElementById("form_cambio_entidad"));
            formData.append("_token", "{{ csrf_token() }}");

            // Validar entidad
            if ($("#nueva_entidad_id").val() === "") {
                Swal.fire({
                    icon: "warning",
                    title: "Seleccione una entidad",
                    text: "Debe elegir una nueva entidad antes de continuar.",
                    confirmButtonText: "Entendido"
                });
                return;
            }

            // Validar fecha
            var fechaFin = new Date($("#fecha_fin").val());
            if (isNaN(fechaFin.getTime())) {
                Swal.fire({
                    icon: "warning",
                    title: "Fecha no válida",
                    text: "Debe seleccionar una fecha de fin correcta para cerrar el módulo actual.",
                    confirmButtonText: "Entendido"
                });
                return;
            }

            $.ajax({
                type: "POST",
                url: "{{ route('modulos.cambiar_entidad') }}", // ✅ la ruta correcta
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $("#btnConfirmarCambio").html('<i class="fa fa-spinner fa-spin"></i> Procesando...');
                    $("#btnConfirmarCambio").prop("disabled", true);
                },
                success: function(response) {
                    $("#btnConfirmarCambio").html('Confirmar Cambio').prop("disabled", false);
                    $("#modal_show_modal").modal('hide');
                    cargarModulos();
                    Swal.fire({
                        icon: "success",
                        title: "Cambio de Entidad Exitoso",
                        html: "El módulo fue cerrado correctamente y se generó un nuevo registro con la nueva entidad.<br><br>" +
                            "<b>Nueva Entidad:</b> " + response.nuevo_modulo.nueva_entidad +
                            "<br><b>Fecha de inicio:</b> " + response.nuevo_modulo.fecha_inicio,
                        confirmButtonText: "Aceptar"
                    });
                },
                error: function(xhr) {
                    $("#btnConfirmarCambio").html('Confirmar Cambio').prop("disabled", false);
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: xhr.responseJSON?.message || "No se pudo realizar el cambio de entidad.",
                        confirmButtonText: "Aceptar"
                    });
                }
            });
        }
    </script>
@endsection
