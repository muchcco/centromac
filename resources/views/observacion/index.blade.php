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
                        <h4 class="page-title">Observaciones</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('inicio') }}">
                                    <i data-feather="home" class="align-self-center"></i>
                                </a>
                            </li>
                            <li class="breadcrumb-item">Gestión de Observaciones</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header" style="background-color:#132842">
            <h4 class="card-title text-white mb-0">Filtro de Búsqueda</h4>
        </div>

        <div class="card-body">
            <div class="row align-items-end">
                <!-- CENTRO MAC -->
                <div class="col-md-4">
                    <div class="form-group">
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
                    <div class="form-group">
                        <label class="mb-2 fw-bold text-dark">Fecha Inicio:</label>
                        <input type="date" id="filtro_fecha_inicio" class="form-control" value="{{ date('Y-m-01') }}">
                    </div>
                </div>

                <!-- FECHA FIN -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="mb-2 fw-bold text-dark">Fecha Fin:</label>
                        <input type="date" id="filtro_fecha_fin" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <!-- BOTONES -->
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-group d-flex gap-1 w-100">
                        <button id="btnBuscar" class="btn btn-primary w-50">
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

    <!-- Contenedor principal -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header" style="background-color:#132842">
                    <h4 class="card-title text-white">Listado de Observaciones</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        {{--  <button class="btn btn-success" onclick="btnAddObservacion()">
                            <i class="fa fa-plus"></i> Nueva Observación
                        </button> --}}
                        <button type="button" class="btn btn-outline-primary" onclick="exportarExcel()">
                            <i class="fa fa-file-excel"></i> Exportar Excel
                        </button>
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
            cargarTablaObservaciones();
            $('#btnBuscar').on('click', function() {
                cargarTablaObservaciones();
            });
        });

        function cargarTablaObservaciones() {
            let idmac = $('#filtro_mac').val();
            let fecha_inicio = $('#filtro_fecha_inicio').val();
            let fecha_fin = $('#filtro_fecha_fin').val();

            $.ajax({
                type: 'GET',
                url: "{{ route('observacion.tablas.tb_index') }}",
                data: {
                    idmac,
                    fecha_inicio,
                    fecha_fin
                },
                beforeSend: () => $('#table_data').html('<i class="fa fa-spinner fa-spin"></i> Cargando...'),
                success: data => $('#table_data').html(data),
                error: () => $('#table_data').html('Error al cargar los datos.')
            });
        }


        function cargarTablaObservaciones() {
            let idmac = $('#filtro_mac').val();
            let fecha_inicio = $('#filtro_fecha_inicio').val();
            let fecha_fin = $('#filtro_fecha_fin').val();

            $.ajax({
                type: 'GET',
                url: "{{ route('observacion.tablas.tb_index') }}",
                data: {
                    idmac,
                    fecha_inicio,
                    fecha_fin
                },
                beforeSend: () => $('#table_data').html('<i class="fa fa-spinner fa-spin"></i> Cargando...'),
                success: data => $('#table_data').html(data),
                error: () => $('#table_data').html('Error al cargar los datos.')
            });
        }

        function btnAddObservacion() {
            $.post("{{ route('observacion.modals.md_add_observacion') }}", {
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

        function btnStoreObservacion() {
            let formData = new FormData($('#form_add_observacion')[0]);
            $.ajax({
                type: 'POST',
                url: "{{ route('observacion.store') }}",
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
                        cargarTablaObservaciones();
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
                        text: "Error al guardar la observación",
                        confirmButtonText: "Aceptar"
                    });
                }
            });
        }

        function btnEditarObservacion(id) {
            $.post("{{ route('observacion.modals.md_edit_observacion') }}", {
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

        function btnUpdateObservacion() {
            let formData = new FormData($('#form_edit_observacion')[0]);
            $.ajax({
                type: 'POST',
                url: "{{ route('observacion.update') }}",
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
                        cargarTablaObservaciones();
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

        function btnEliminarObservacion(id) {
            Swal.fire({
                title: "¿Estás seguro?",
                text: "Se eliminará esta observación permanentemente.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post("{{ route('observacion.delete') }}", {
                        _token: "{{ csrf_token() }}",
                        id_observacion: id
                    }, function() {
                        cargarTablaObservaciones();
                        Swal.fire({
                            icon: "success",
                            title: "Eliminado",
                            text: "Observación eliminada exitosamente."
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

        function btnSubsanarObservacion(id) {
            $.post("{{ route('observacion.modals.md_subsanar_observacion') }}", {
                _token: "{{ csrf_token() }}",
                id_observacion: id
            }, function(data) {
                $('#modal_show_modal').html(data.html).modal('show');
            }).fail(() => {
                Swal.fire({
                    icon: "error",
                    text: "Error al cargar el formulario de subsanación",
                    confirmButtonText: "Aceptar"
                });
            });
        }

        function btnSubsanarGuardar() {
            let formData = new FormData($('#form_subsanar_observacion')[0]);
            $.ajax({
                type: 'POST',
                url: "{{ route('observacion.subsanar') }}",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: () => {
                    $("#btnEnviarForm").html('<i class="fa fa-spinner fa-spin"></i> Guardando...').prop(
                        "disabled", true);
                },
                success: function(data) {
                    $("#btnEnviarForm").html('Guardar').prop("disabled", false);
                    if (data.status === 200) {
                        $('#modal_show_modal').modal('hide');
                        cargarTablaObservaciones();
                        Swal.fire({
                            icon: "success",
                            text: data.message,
                            confirmButtonText: "Aceptar"
                        });
                    } else {
                        $('#alerta').html(`<div class="alert alert-warning">${data.message}</div>`);
                    }
                },
                error: function() {
                    $("#btnEnviarForm").html('Guardar').prop("disabled", false);
                    Swal.fire({
                        icon: "error",
                        text: "Error al guardar la subsanación",
                        confirmButtonText: "Aceptar"
                    });
                }
            });
        }

        function btnVerObservacion(id) {
            $.post("{{ route('observacion.modals.md_ver_observacion') }}", {
                _token: "{{ csrf_token() }}",
                id_observacion: id
            }, function(data) {
                $('#modal_show_modal').html(data.html);
                $('#modal_show_modal').modal('show');
            });
        }

        function exportarExcel() {
            let idmac = $('#filtro_mac').val();
            let fecha_inicio = $('#filtro_fecha_inicio').val();
            let fecha_fin = $('#filtro_fecha_fin').val();

            // construir la URL con parámetros
            let url = "{{ route('observacion.export_excel') }}" +
                `?idmac=${idmac ?? ''}&fecha_inicio=${fecha_inicio ?? ''}&fecha_fin=${fecha_fin ?? ''}`;

            window.location.href = url;
        }
    </script>
@endsection
