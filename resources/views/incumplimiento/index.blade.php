@extends('layouts.layout')

@section('style')
    <link rel="stylesheet" href="{{ asset('Vendor/toastr/toastr.min.css') }}">
    <link href="{{ asset('nuevo/plugins/select2/select2.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" />
@endsection

@section('main')
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="row">
                    <div class="col">
                        <h4 class="page-title">Incidentes Operativos</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('inicio') }}">
                                    <i data-feather="home" class="align-self-center"></i>
                                </a>
                            </li>
                            <li class="breadcrumb-item">Gestión de Incidentes Operativos</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ========== FILTRO DE BÚSQUEDA ========== -->
    <div class="card mb-3">
        <div class="card-header" style="background-color:#132842">
            <h4 class="card-title text-white">Filtro de Búsqueda</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- CENTRO MAC -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="mb-2 fw-bold text-dark">Centro MAC:</label>
                        @if (auth()->user()->hasRole(['Administrador']))
                            <select id="filtro_mac" class="form-control select2">
                                <option value="">-- Seleccione un MAC --</option>
                                @foreach ($centros_mac as $mac)
                                    <option value="{{ $mac->idcentro_mac }}">{{ $mac->nombre_mac }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="text" class="form-control" value="{{ $centro_mac->name_mac ?? 'No asignado' }}"
                                readonly>
                            <input type="hidden" id="filtro_mac" value="{{ $centro_mac->idmac }}">
                        @endif
                    </div>
                </div>

                <!-- FECHA INICIO -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="mb-2 fw-bold text-dark">Fecha Inicio:</label>
                        <input type="date" id="filtro_fecha_inicio" class="form-control"
                            value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                    </div>
                </div>

                <!-- FECHA FIN -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="mb-2 fw-bold text-dark">Fecha Fin:</label>
                        <input type="date" id="filtro_fecha_fin" class="form-control"
                            value="{{ now()->format('Y-m-d') }}">
                    </div>
                </div>

                <!-- BOTONES -->
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-group">
                        <button class="btn btn-primary me-1" onclick="filtrarIncumplimientos()">
                            <i class="fa fa-search"></i> Buscar
                        </button>
                        <button class="btn btn-dark" onclick="limpiarFiltro()">
                            <i class="fa fa-undo"></i> Limpiar
                        </button>
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
                    <h4 class="card-title text-white">Listado de Incidentes Operativos</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <button class="btn btn-success" onclick="btnAddIncumplimiento()">
                            <i class="fa fa-plus"></i> Nuevo Incidente Operativo
                        </button>
                        <a href="{{ route('incumplimiento.export_excel') }}" class="btn btn-outline-primary">
                            <i class="fa fa-file-excel"></i> Exportar Excel
                        </a>
                    </div>
                    <div id="table_data" class="table-responsive"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal contenedor -->
    <div class="modal fade" id="modal_show_modal" tabindex="-1" role="dialog"></div>
@endsection

@section('script')
    <script src="{{ asset('Script/js/sweet-alert.min.js') }}"></script>
    <script src="{{ asset('Vendor/toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('//cdn.jsdelivr.net/npm/sweetalert2@11') }}"></script>
    <script src="{{ asset('nuevo/plugins/select2/select2.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            cargarTablaIncumplimientos();
        });

        // 🔄 Cargar tabla principal
        function cargarTablaIncumplimientos() {
            $.ajax({
                type: 'GET',
                url: "{{ route('incumplimiento.tablas.tb_index') }}",
                beforeSend: () => $('#table_data').html('<i class="fa fa-spinner fa-spin"></i> Cargando...'),
                success: data => $('#table_data').html(data),
                error: () => $('#table_data').html('Error al cargar los datos.')
            });
        }

        // ➕ Agregar nuevo incidente
        function btnAddIncumplimiento() {
            $.post("{{ route('incumplimiento.modals.md_add_incumplimiento') }}", {
                _token: "{{ csrf_token() }}"
            }, function(data) {
                $('#modal_show_modal').html(data.html).modal('show');
                setTimeout(() => {
                    $('.select2').select2({
                        dropdownParent: $('#modal_show_modal'),
                        width: '100%',
                        allowClear: true
                    });
                }, 300);
            });
        }

        // 💾 Guardar nuevo registro
        function btnStoreIncumplimiento() {
            let formData = new FormData($('#form_add_incumplimiento')[0]);
            $.ajax({
                type: 'POST',
                url: "{{ route('incumplimiento.store') }}",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: () => {
                    $("#btnEnviarForm").html('<i class="fa fa-spinner fa-spin"></i> Guardando...').prop(
                        "disabled", true);
                },
                success: function(data) {
                    $("#btnEnviarForm").html('Guardar').prop("disabled", false);
                    if (data.status == 201) {
                        cargarTablaIncumplimientos();
                        Swal.fire({
                            icon: "success",
                            text: data.message,
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
                        text: "Error al guardar el incumplimiento",
                        confirmButtonText: "Aceptar"
                    });
                }
            });
        }

        // ✏️ Editar incidente
        function btnEditarIncumplimiento(id) {
            $.post("{{ route('incumplimiento.modals.md_edit_incumplimiento') }}", {
                _token: "{{ csrf_token() }}",
                id_observacion: id
            }, function(data) {
                $('#modal_show_modal').html(data.html).modal('show');
                setTimeout(() => {
                    $('.select2').select2({
                        dropdownParent: $('#modal_show_modal'),
                        width: '100%',
                        allowClear: true
                    });
                }, 300);
            });
        }

        // 🔄 Actualizar registro (abrir/cerrar libremente)
        function btnUpdateIncumplimiento() {
            // Asegurar que el valor de "estado" se actualice según el check
            if ($('#incumplimiento_curso').length) {
                if ($('#incumplimiento_curso').is(':checked')) {
                    $('#estado').val('ABIERTO');
                } else {
                    $('#estado').val('CERRADO');
                }
            }

            // Validar fechas antes de enviar
            const fechaInc = $('input[name="fecha_observacion"]').val();
            const fechaCie = $('input[name="fecha_solucion"]').val();

            if (fechaCie && fechaCie < fechaInc) {
                Swal.fire({
                    icon: "warning",
                    text: "⚠️ La fecha de cierre no puede ser menor que la del incidente.",
                    confirmButtonText: "Aceptar"
                });
                return;
            }

            let formData = new FormData($('#form_edit_incumplimiento')[0]);

            $.ajax({
                type: 'POST',
                url: "{{ route('incumplimiento.update') }}",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: () => {
                    $("#btnEnviarForm").html('<i class="fa fa-spinner fa-spin"></i> Actualizando...').prop(
                        "disabled", true);
                },
                success: function(data) {
                    $("#btnEnviarForm").html('Guardar').prop("disabled", false);
                    if (data.status == 200) {
                        cargarTablaIncumplimientos();
                        Swal.fire({
                            icon: "success",
                            text: data.message,
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
                        text: "Error al actualizar",
                        confirmButtonText: "Aceptar"
                    });
                }
            });
        }

        // 🗑️ Eliminar registro
        function btnEliminarIncumplimiento(id) {
            Swal.fire({
                title: "¿Estás seguro?",
                text: "Se eliminará este incumplimiento permanentemente.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post("{{ route('incumplimiento.delete') }}", {
                        _token: "{{ csrf_token() }}",
                        id_observacion: id
                    }, function() {
                        cargarTablaIncumplimientos();
                        Swal.fire({
                            icon: "success",
                            title: "Eliminado",
                            text: "Incidente Operativo eliminado exitosamente."
                        });
                    }).fail(() => {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "No se pudo eliminar."
                        });
                    });
                }
            });
        }

        // 👁️ Ver detalles
        function btnVerIncumplimiento(id) {
            $.post("{{ route('incumplimiento.modals.md_ver_incumplimiento') }}", {
                _token: "{{ csrf_token() }}",
                id_observacion: id
            }, function(data) {
                $('#modal_show_modal').html(data.html).modal('show');
            });
        }

        // ✅ Cerrar incumplimiento (sin restricción)
        function btnCerrarGuardar() {
            let formData = new FormData($('#form_cerrar_incumplimiento')[0]);
            $.ajax({
                type: 'POST',
                url: "{{ route('incumplimiento.cerrar') }}",
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    if (data.status === 200) {
                        Swal.fire({
                            icon: "success",
                            text: data.message
                        });
                        $('#modal_show_modal').modal('hide');
                        cargarTablaIncumplimientos();
                    } else {
                        Swal.fire({
                            icon: "warning",
                            text: data.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: "error",
                        text: "Error al cerrar el incumplimiento."
                    });
                }
            });
        }
        // 🔍 Aplicar filtros de búsqueda (Centro MAC + Fecha Inicio/Fin)
        function filtrarIncumplimientos() {
            let idmac = $('#filtro_mac').val();
            let fechaInicio = $('#filtro_fecha_inicio').val();
            let fechaFin = $('#filtro_fecha_fin').val();

            $.ajax({
                url: "{{ route('incumplimiento.tablas.tb_index') }}",
                type: "GET",
                data: {
                    idmac: idmac,
                    fecha_inicio: fechaInicio,
                    fecha_fin: fechaFin
                },
                beforeSend: () => {
                    $('#table_data').html('<i class="fa fa-spinner fa-spin"></i> Filtrando...');
                },
                success: function(data) {
                    $('#table_data').html(data);
                },
                error: function() {
                    $('#table_data').html('<div class="text-danger">Error al aplicar el filtro.</div>');
                }
            });
        }

        // 🔄 Limpiar filtro (vuelve al mes actual o al MAC del usuario)
        function limpiarFiltro() {
            $('#filtro_fecha_inicio').val('{{ now()->startOfMonth()->format('Y-m-d') }}');
            $('#filtro_fecha_fin').val('{{ now()->format('Y-m-d') }}');

            @if (auth()->user()->hasRole(['Administrador']))
                $('#filtro_mac').val('').trigger('change');
            @endif

            filtrarIncumplimientos();
        }
    </script>
@endsection
