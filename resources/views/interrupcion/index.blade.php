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
    <!-- 🔹 FILTRO -->
    <div class="card mb-3">
        <div class="card-header" style="background-color:#132842">
            <h4 class="card-title text-white mb-0">Filtro de Búsqueda</h4>
        </div>

        <div class="card-body">
            <div class="row align-items-end">
                <!-- CENTRO MAC -->
                <div class="col-md-4">
                    <div class="">
                        <label class="mb-2 fw-bold text-dark">Centro MAC:</label>
                        @role('Administrador|Moderador')
                            <select id="filtro_mac" class="form-control select2" style="width: 100%">
                                <option value="">-- Todos los MAC --</option>
                                @foreach ($centros_mac as $mac)
                                    <option value="{{ $mac->IDCENTRO_MAC }}">{{ $mac->NOMBRE_MAC }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="text" class="form-control text-uppercase"
                                value="{{ $nombre_mac_usuario ?? 'Sin asignar' }}" readonly>
                            <input type="hidden" id="filtro_mac" value="{{ auth()->user()->idcentro_mac }}">
                        @endrole
                    </div>
                </div>

                <!-- FECHA INICIO -->
                <div class="col-md-3">
                    <div class="">
                        <label class="mb-2 fw-bold text-dark">Fecha Inicio:</label>
                        <input type="date" id="filtro_fecha_inicio" class="form-control" value="{{ date('Y-m-01') }}">
                    </div>
                </div>

                <!-- FECHA FIN -->
                <div class="col-md-3">
                    <div class="">
                        <label class="mb-2 fw-bold text-dark">Fecha Fin:</label>
                        <input type="date" id="filtro_fecha_fin" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <!-- BOTONES BUSCAR / LIMPIAR -->
                <div class="col-md-2 d-flex align-items-end">
                    <div class=" d-flex gap-1 w-100">
                        <button id="btnBuscar" class="btn btn-primary w-50">
                            <i class="fa fa-search"></i> Buscar
                        </button>
                        <button class="btn btn-dark w-50" id="btnLimpiarFiltro">
                            <i class="fa fa-undo"></i> Limpiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- 🔹 FILTROS ADICIONALES -->
            <div id="extraFiltros" class="mt-2" style="display: none;">
                <div class="row g-3">
                    <!-- ENTIDAD -->
                    <div class="col-md-3">
                        <label class="fw-bold text-dark">Entidad:</label>
                        <select id="filtro_entidad" class="form-control select2" style="width: 100%;">
                            <option value="">-- Todas las entidades --</option>
                            @foreach ($entidades as $ent)
                                <option value="{{ $ent->nombre_entidad }}">
                                    {{ $ent->abrev_entidad }} - {{ $ent->nombre_entidad }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- TIPIFICACIÓN -->
                    <div class="col-md-3">
                        <label class="fw-bold text-dark">Tipificación:</label>
                        <select id="filtro_tipificacion" class="form-control select2" style="width: 100%;">
                            <option value="">-- Todas las tipificaciones --</option>
                            @foreach ($tipificaciones as $tip)
                                <option value="{{ $tip->nom_tipo_int_obs }}">
                                    {{ $tip->tipo }} {{ $tip->numeracion }} - {{ $tip->nom_tipo_int_obs }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- ESTADO -->
                    <div class="col-md-3">
                        <label class="fw-bold text-dark">Estado:</label>
                        <select id="filtro_estado" class="form-control select2" style="width: 100%;">
                            <option value="">-- Todos --</option>
                            <option value="ABIERTO">Abierto</option>
                            <option value="CERRADO">Cerrado</option>
                        </select>
                    </div>

                    <!-- REVISIÓN -->
                    <div class="col-md-3">
                        <label class="fw-bold text-dark">Revisión:</label>
                        <select id="filtro_revision" class="form-control select2" style="width: 100%;">
                            <option value="">-- Todos --</option>
                            <option value="observado">Observado</option>
                            <option value="no_observado">No observado</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 🔹 BOTÓN MÁS FILTROS -->
            <div class="mt-3 text-center">
                <button class="btn btn-outline-dark w-100" id="btnToggleFiltros" style="font-weight: 600;">
                    <i class="fa fa-filter"></i> Ver más filtros
                </button>
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
                            <button type="button" class="btn btn-outline-primary" onclick="exportarExcel()">
                                <i class="fa fa-file-excel"></i> Exportar Excel
                            </button>
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
            // inicializar select2
            $('.select2').select2();

            // cargar tabla al iniciar
            cargarTablaInterrupciones();

            //  activar botón BUSCAR
            $('#btnBuscar').on('click', function(e) {
                e.preventDefault();
                cargarTablaInterrupciones();
            });
        });

        // 🔹 Función principal para cargar la tabla
        function cargarTablaInterrupciones() {
            let idmac = $('#filtro_mac').val();
            let fecha_inicio = $('#filtro_fecha_inicio').val();
            let fecha_fin = $('#filtro_fecha_fin').val();

            // 🔸 Filtros adicionales (solo si están visibles o tienen valor)
            let entidad = $('#filtro_entidad').val();
            let tipificacion = $('#filtro_tipificacion').val();
            let estado = $('#filtro_estado').val();
            let revision = $('#filtro_revision').val();

            $.ajax({
                type: 'GET',
                url: "{{ route('interrupcion.tablas.tb_index') }}",
                data: {
                    idmac,
                    fecha_inicio,
                    fecha_fin,
                    entidad,
                    tipificacion,
                    estado,
                    revision
                },
                beforeSend: () => $('#table_data').html('<i class="fa fa-spinner fa-spin"></i> Cargando...'),
                success: data => $('#table_data').html(data),
                error: () => $('#table_data').html('Error al cargar los datos.')
            });
        }

        // 🔹 Mostrar / ocultar los filtros extra con animación y reseteo
        $(document).on('click', '#btnToggleFiltros', function() {
            const $extra = $('#extraFiltros');
            const visible = $extra.is(':visible');
            const $btn = $(this);

            // Si está visible, lo estamos cerrando
            if (visible) {
                // Ocultamos con animación
                $extra.slideUp(300, function() {
                    //  Resetear todos los filtros adicionales a "Todos"
                    $('#filtro_entidad, #filtro_tipificacion, #filtro_estado, #filtro_revision')
                        .val('')
                        .trigger('change.select2'); // evita lanzar evento 'change' normal

                    // 🔁 Actualizar tabla UNA sola vez después del reset
                    cargarTablaInterrupciones();
                });

                // Cambiar texto del botón
                $btn.html('<i class="fa fa-filter"></i> Ver más filtros');
            } else {
                // Mostrar filtros con animación
                $extra.slideDown(300);
                $btn.html('<i class="fa fa-chevron-up"></i> Ver menos filtros');
            }
        });

        // 🔹 Actualizar tabla automáticamente al cambiar filtros
        $('#extraFiltros input, #extraFiltros select').on('change keyup', function() {
            cargarTablaInterrupciones();
        });

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
                //  Detecta valores aunque los campos no existan o estén deshabilitados
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

            //  Llamamos a la función de validación
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

            //  Llamamos a la función de validación
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

            //  Validar fechas y horas antes de enviar
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

        } // ===============================================
        // 🔹 Exportar Excel con filtros aplicados
        // ===============================================
        // 🔹 Exportar Excel con todos los filtros aplicados
        function exportarExcel() {
            let idmac = $('#filtro_mac').val() || '';
            let fecha_inicio = $('#filtro_fecha_inicio').val() || '';
            let fecha_fin = $('#filtro_fecha_fin').val() || '';
            let entidad = $('#filtro_entidad').val() || '';
            let tipificacion = $('#filtro_tipificacion').val() || '';
            let estado = $('#filtro_estado').val() || '';
            let revision = $('#filtro_revision').val() || '';

            // 🔸 Construimos la URL con todos los parámetros
            const url = "{{ route('interrupcion.export_excel') }}" +
                `?idmac=${encodeURIComponent(idmac)}` +
                `&fecha_inicio=${encodeURIComponent(fecha_inicio)}` +
                `&fecha_fin=${encodeURIComponent(fecha_fin)}` +
                `&entidad=${encodeURIComponent(entidad)}` +
                `&tipificacion=${encodeURIComponent(tipificacion)}` +
                `&estado=${encodeURIComponent(estado)}` +
                `&revision=${encodeURIComponent(revision)}`;

            // 🔹 Ejecuta la descarga del Excel
            window.location.href = url;
        }

        //  Función para limpiar todos los filtros
        $(document).on('click', '#btnLimpiarFiltro', function(e) {
            e.preventDefault();

            // Resetear campos base
            $('#filtro_mac').val('').trigger('change');
            $('#filtro_fecha_inicio').val('{{ date('Y-m-01') }}');
            $('#filtro_fecha_fin').val('{{ date('Y-m-d') }}');

            // Resetear filtros adicionales
            $('#filtro_entidad, #filtro_tipificacion, #filtro_estado, #filtro_revision')
                .val('')
                .trigger('change.select2');

            // Cerrar panel de filtros extra (si está abierto)
            const $extra = $('#extraFiltros');
            if ($extra.is(':visible')) {
                $extra.slideUp(300);
                $('#btnToggleFiltros').html('<i class="fa fa-filter"></i> Ver más filtros');
            }

            // Recargar tabla con filtros limpios
            cargarTablaInterrupciones();
        });

        function btnVerInterrupcion(id) {
            $.ajax({
                type: 'POST',
                url: "{{ route('interrupcion.modals.md_ver_interrupcion') }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id_interrupcion": id
                },
                success: function(data) {
                    $("#modal_show_modal").html(data.html);
                    $("#modal_show_modal").modal('show');
                },
                error: function() {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "No se pudo cargar la información de la interrupción.",
                        confirmButtonText: "Aceptar"
                    });
                }
            });
        }

        function btnObservarInterrupcion(id) {
            $.post("{{ route('interrupcion.modals.md_observar_interrupcion') }}", {
                _token: "{{ csrf_token() }}",
                id_interrupcion: id
            }, function(data) {
                $('#modal_show_modal').html(data.html);
                $('#modal_show_modal').modal('show');
            }).fail(function() {
                Swal.fire("Error", "No se pudo cargar el formulario de observación.", "error");
            });
        }

        function guardarObservacion() {
            const form = $('#form_observar_interrupcion');

            $.ajax({
                url: "{{ route('interrupcion.observar') }}",
                type: "POST",
                data: form.serialize(),
                beforeSend: function() {
                    $('#btnGuardarObservacion').prop('disabled', true).html(
                        '<i class="fa fa-spinner fa-spin"></i> Guardando...');
                },
                success: function(res) {
                    $('#btnGuardarObservacion').prop('disabled', false).html('Guardar');

                    if (res.status === 200) {
                        Swal.fire(" Éxito", res.message, "success");
                        $('#modal_show_modal').modal('hide');
                        cargarTablaInterrupciones(); // 🔁 recarga dinámica
                    } else {
                        Swal.fire("⚠️ Atención", res.message || "No se pudo guardar los cambios.", "warning");
                    }
                },
                error: function(xhr) {
                    $('#btnGuardarObservacion').prop('disabled', false).html('Guardar');
                    console.error(xhr.responseText);
                    Swal.fire("❌ Error", "Ocurrió un problema al guardar la observación.", "error");
                }
            });
        }
    </script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();

            cargarTablaInterrupciones();

            $('#btnBuscar').on('click', function(e) {
                e.preventDefault();
                cargarTablaInterrupciones();
            });
        });

        function cargarTablaInterrupciones() {
            let idmac = $('#filtro_mac').val();
            let fecha_inicio = $('#filtro_fecha_inicio').val();
            let fecha_fin = $('#filtro_fecha_fin').val();

            let entidad = $('#filtro_entidad').val();
            let tipificacion = $('#filtro_tipificacion').val();
            let estado = $('#filtro_estado').val();
            let revision = $('#filtro_revision').val();

            $.ajax({
                type: 'GET',
                url: "{{ route('interrupcion.tablas.tb_index') }}",
                data: {
                    idmac,
                    fecha_inicio,
                    fecha_fin,
                    entidad,
                    tipificacion,
                    estado,
                    revision
                },
                beforeSend: () => $('#table_data').html('<i class="fa fa-spinner fa-spin"></i> Cargando...'),
                success: data => $('#table_data').html(data),
                error: () => $('#table_data').html('Error al cargar los datos.')
            });
        }

        // =============================
        // VALIDACIÓN GLOBAL
        // =============================
        function validarFechasHoras(form, estado) {
            if (estado && estado.toUpperCase() === 'CERRADO') {

                let fechaInicio = form.find('[name="fecha_inicio"]').val() || form.find('input[type="date"]:disabled')
                .val() || '';
                let horaInicio = form.find('[name="hora_inicio"]').val() || form.find('input[type="time"]:disabled')
                .val() || '';
                let fechaFin = form.find('[name="fecha_fin"]').val() || '';
                let horaFin = form.find('[name="hora_fin"]').val() || '';

                if (!fechaFin || !horaFin) {
                    Swal.fire({
                        icon: "warning",
                        text: "Debe ingresar la Fecha y Hora de Fin para un estado CERRADO.",
                        confirmButtonText: "Aceptar"
                    });
                    return false;
                }

                if (!fechaInicio || !horaInicio) {
                    return true;
                }

                if (fechaFin < fechaInicio) {
                    Swal.fire({
                        icon: "warning",
                        text: "La Fecha de Fin no puede ser anterior a la Fecha de Inicio.",
                        confirmButtonText: "Aceptar"
                    });
                    return false;
                }

                if (fechaFin === fechaInicio && horaFin <= horaInicio) {
                    Swal.fire({
                        icon: "warning",
                        text: "La Hora de Fin debe ser mayor a la Hora de Inicio cuando las fechas son iguales.",
                        confirmButtonText: "Aceptar"
                    });
                    return false;
                }

                const inicio = new Date(`${fechaInicio}T${horaInicio}`);
                const fin = new Date(`${fechaFin}T${horaFin}`);

                if (fin <= inicio) {
                    Swal.fire({
                        icon: "warning",
                        text: "La Fecha y Hora de Fin deben ser posteriores a las de Inicio.",
                        confirmButtonText: "Aceptar"
                    });
                    return false;
                }

                // ======================================
                // 🟦 NUEVA VALIDACIÓN: ¿CAMBIÓ DE DÍA?
                // ======================================
                if (fechaFin > fechaInicio) {

                    return Swal.fire({
                        title: "Interrupción mayor a un día",
                        text: "La interrupción ha pasado al día siguiente. ¿Desea continuar?",
                        icon: "question",
                        showCancelButton: true,
                        confirmButtonText: "Sí, continuar",
                        cancelButtonText: "Cancelar"
                    }).then((result) => {
                        return result.isConfirmed; // true para continuar / false para detener
                    });
                }
            }

            return true;
        }

        async function btnStoreInterrupcion() {
            let form = $('#form_add_interrupcion');
            let estado = $('#estado').val();

            let valido = await validarFechasHoras(form, estado);
            if (!valido) return;

            var formData = new FormData($('#form_add_interrupcion')[0]);

            $.ajax({
                type: 'POST',
                url: "{{ route('interrupcion.store') }}",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $("#btnEnviarForm").html('<i class="fa fa-spinner fa-spin"></i> ESPERE').prop(
                        "disabled", true);
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
                }
            });
        }

        async function btnUpdateInterrupcion() {
            let form = $('#form_edit_interrupcion');
            let estado = $('#estado').val();

            let valido = await validarFechasHoras(form, estado);
            if (!valido) return;

            var formData = new FormData($('#form_edit_interrupcion')[0]);

            $.ajax({
                type: 'POST',
                url: "{{ route('interrupcion.update') }}",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $("#btnEnviarForm").html('<i class="fa fa-spinner fa-spin"></i> ESPERE').prop(
                        "disabled", true);
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
                }
            });
        }
    </script>
@endsection
