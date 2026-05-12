@extends('layouts.layout')

@section('style')
    <link href="{{ asset('nuevo/plugins/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .asignacion-header {
            background-color: #132842;
        }

        .asignacion-actions {
            display: flex;
            gap: .5rem;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .metric-strip {
            display: grid;
            grid-template-columns: repeat(4, minmax(130px, 1fr));
            gap: .75rem;
            margin-bottom: 1rem;
        }

        .metric-item {
            border: 1px solid #d7dde8;
            border-left: 4px solid #132842;
            border-radius: 6px;
            padding: .75rem;
            background: #fff;
            min-height: 76px;
        }

        .metric-label {
            color: #667085;
            font-size: 12px;
            margin-bottom: .25rem;
            text-transform: uppercase;
        }

        .metric-value {
            color: #132842;
            font-size: 20px;
            font-weight: 700;
            line-height: 1.2;
        }

        .asignacion-calendar-panel {
            border: 1px solid #d7dde8;
            border-radius: 6px;
            padding: .75rem;
            background: #fff;
            min-height: 180px;
        }

        .asignacion-calendar-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(44px, 1fr));
            gap: .4rem;
        }

        .asignacion-calendar-day {
            border: 1px solid #d7dde8;
            border-radius: 6px;
            min-height: 54px;
            padding: .45rem .35rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: .1rem;
            background: #f8fafc;
            color: #132842;
            line-height: 1.1;
            text-align: center;
            user-select: none;
        }

        .asignacion-calendar-day strong {
            font-size: 16px;
        }

        .asignacion-calendar-day small {
            color: #667085;
            font-size: 11px;
        }

        .asignacion-calendar-day.is-normal {
            border-color: #8fb5e5;
            background: #eef6ff;
        }

        .asignacion-calendar-day.is-saturday {
            cursor: pointer;
        }

        .asignacion-calendar-day.is-selected,
        .asignacion-calendar-day.is-saturday:has(input:checked) {
            border-color: #198754;
            background: #e9f8ef;
        }

        .asignacion-calendar-day.is-disabled {
            background: #f1f3f5;
            color: #98a2b3;
            border-style: dashed;
        }

        .asignacion-calendar-day input {
            margin: 0;
        }

        @media (max-width: 992px) {
            .metric-strip {
                grid-template-columns: repeat(2, minmax(130px, 1fr));
            }

            .asignacion-calendar-grid {
                grid-template-columns: repeat(4, minmax(44px, 1fr));
            }
        }

        @media (max-width: 576px) {
            .metric-strip {
                grid-template-columns: 1fr;
            }

            .asignacion-actions {
                justify-content: stretch;
            }

            .asignacion-actions .btn {
                width: 100%;
            }
        }
    </style>
@endsection

@section('main')
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="row">
                    <div class="col">
                        <h4 class="page-title">Asistencia</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('inicio') }}">
                                    <i data-feather="home" class="align-self-center" style="height: 70%; display: block;"></i>
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('asistencia.asistencia') }}">Asistencia</a>
                            </li>
                            <li class="breadcrumb-item">Modulo de asignacion</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-header asignacion-header">
                    <h4 class="card-title text-white mb-0">Filtro de Busqueda</h4>
                </div>

                <div class="card-body bootstrap-select-1">
                    <div class="row align-items-end">
                        @hasanyrole('Administrador|Moderador')
                            <div class="col-md-4 col-lg-3">
                                <div class="form-group">
                                    <label class="mb-2 fw-semibold">Centro MAC</label>
                                    <select name="mac" id="mac" class="form-control select2">
                                        @foreach ($macs as $mac)
                                            <option value="{{ $mac->id }}" {{ $idmac == $mac->id ? 'selected' : '' }}>
                                                {{ $mac->nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @else
                            <input type="hidden" id="mac" value="{{ $idmac }}">
                            <div class="col-md-4 col-lg-3">
                                <div class="form-group">
                                    <label class="mb-2 fw-semibold">Centro MAC</label>
                                    <input type="text" class="form-control" value="{{ $name_mac }}" readonly>
                                </div>
                            </div>
                        @endhasanyrole

                        <div class="col-md-4 col-lg-3">
                            <div class="form-group">
                                <label class="mb-2 fw-semibold">Desde</label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control"
                                    value="{{ $fecha_inicio }}">
                            </div>
                        </div>

                        <div class="col-md-4 col-lg-3">
                            <div class="form-group">
                                <label class="mb-2 fw-semibold">Hasta</label>
                                <input type="date" name="fecha_fin" id="fecha_fin" class="form-control"
                                    value="{{ $fecha_fin }}">
                            </div>
                        </div>

                        <div class="col-md-12 col-lg-3">
                            <div class="form-group asignacion-actions">
                                <button type="button" class="btn btn-primary" id="filtro" onclick="cargarAsignacion()">
                                    <i class="fa fa-search"></i> Buscar
                                </button>
                                <button type="button" class="btn btn-dark" id="limpiar" onclick="limpiarAsignacion()">
                                    <i class="fa fa-undo"></i> Limpiar
                                </button>
                                <button type="button" class="btn btn-success" onclick="exportarAsignacion()">
                                    <i class="fa fa-file-excel-o"></i> Excel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header asignacion-header d-flex align-items-center justify-content-between">
                    <h4 class="card-title text-white mb-0">Horarios Asignados</h4>
                    <button type="button" class="btn btn-sm btn-light" onclick="abrirModalAsignacion()">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">Personal con turno</h5>
                            <p class="text-muted mb-0 font-13">Centro MAC: <span id="mac_nombre_actual">{{ $name_mac }}</span></p>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" onclick="abrirModalAsignacion()">
                            <i class="fa fa-clock-o"></i> Asignar
                        </button>
                    </div>
                    <div id="tabla_horarios" class="table-responsive">
                        <div class="text-center p-3">
                            <i class="fa fa-spinner fa-spin"></i> Cargando...
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header asignacion-header d-flex align-items-center justify-content-between">
                    <h4 class="card-title text-white mb-0">Horas Compensables</h4>
                    <button type="button" class="btn btn-sm btn-light" onclick="cargarReporteAsignacion()">
                        <i class="fa fa-refresh"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="row align-items-end mb-3">
                        <div class="col-md-8">
                            <label class="mb-2 fw-semibold">Personal asignado</label>
                            <select id="filtro_personal_asignado" class="form-control select2">
                                <option value="">Todas las personas asignadas</option>
                            </select>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="button" class="btn btn-primary mt-3" onclick="cargarReporteAsignacion()">
                                <i class="fa fa-search"></i> Filtrar
                            </button>
                        </div>
                    </div>

                    <div id="tabla_reporte">
                        <div class="text-center p-3">
                            <i class="fa fa-spinner fa-spin"></i> Cargando reporte...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_show_modal" tabindex="-1" role="dialog"></div>
@endsection

@section('script')
    <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/select2/select2.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.js') }}"></script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        let cargandoPersonasAsignadas = false;
        let macPersonasAsignadas = null;

        $(document).ready(function() {
            $('.select2').select2({
                width: '100%'
            });
            $('#filtro_personal_asignado').on('change', function() {
                if (!cargandoPersonasAsignadas) {
                    cargarReporteAsignacion();
                }
            });
            $('#mac').on('change', function() {
                cargarAsignacion();
            });
            cargarAsignacion();
        });

        function filtrosAsignacion() {
            return {
                mac: $('#mac').val(),
                fecha_inicio: $('#fecha_inicio').val(),
                fecha_fin: $('#fecha_fin').val(),
                idpersonal: $('#filtro_personal_asignado').val()
            };
        }

        function cargarAsignacion() {
            actualizarNombreMac();
            cargarHorariosAsignados();
            cargarPersonasAsignadas(function() {
                cargarReporteAsignacion();
            });
        }

        function actualizarNombreMac() {
            const selected = $('#mac option:selected').text();
            if (selected) {
                $('#mac_nombre_actual').text(selected.trim());
            }
        }

        function cargarHorariosAsignados() {
            $.ajax({
                type: 'GET',
                url: "{{ route('asistencia.asignacion.horarios') }}",
                data: filtrosAsignacion(),
                beforeSend: function() {
                    $('#tabla_horarios').html(
                        '<div class="text-center p-3"><i class="fa fa-spinner fa-spin"></i> Cargando...</div>'
                    );
                },
                success: function(html) {
                    $('#tabla_horarios').html(html);
                },
                error: function() {
                    $('#tabla_horarios').html('<div class="text-danger p-3">No se pudo cargar horarios.</div>');
                }
            });
        }

        function cargarPersonasAsignadas(callback = null) {
            const mac = $('#mac').val();
            const selected = macPersonasAsignadas === mac ? $('#filtro_personal_asignado').val() : '';
            cargandoPersonasAsignadas = true;

            $.ajax({
                type: 'GET',
                url: "{{ route('asistencia.asignacion.personas_asignadas') }}",
                data: {
                    mac: mac
                },
                beforeSend: function() {
                    $('#filtro_personal_asignado')
                        .html('<option value="">Cargando personal...</option>')
                        .val('')
                        .trigger('change.select2');
                },
                success: function(resp) {
                    let html = '<option value="">Todas las personas asignadas</option>';
                    let selectedExists = false;

                    (resp.results || []).forEach(function(item) {
                        selectedExists = selectedExists || String(item.id) === String(selected);
                        html += `<option value="${item.id}">${item.text}</option>`;
                    });

                    $('#filtro_personal_asignado')
                        .html(html)
                        .val(selectedExists ? selected : '')
                        .trigger('change.select2');

                    macPersonasAsignadas = mac;
                },
                error: function() {
                    $('#filtro_personal_asignado')
                        .html('<option value="">Todas las personas asignadas</option>')
                        .val('')
                        .trigger('change.select2');
                },
                complete: function() {
                    cargandoPersonasAsignadas = false;
                    if (callback) {
                        callback();
                    }
                }
            });
        }

        function cargarReporteAsignacion() {
            $.ajax({
                type: 'GET',
                url: "{{ route('asistencia.asignacion.reporte') }}",
                data: filtrosAsignacion(),
                beforeSend: function() {
                    $('#tabla_reporte').html(
                        '<div class="text-center p-3"><i class="fa fa-spinner fa-spin"></i> Calculando...</div>'
                    );
                },
                success: function(html) {
                    $('#tabla_reporte').html(html);
                },
                error: function() {
                    $('#tabla_reporte').html('<div class="text-danger p-3">No se pudo calcular el reporte.</div>');
                }
            });
        }

        function limpiarAsignacion() {
            const hoy = new Date();
            const inicio = fechaLocal(new Date(hoy.getFullYear(), hoy.getMonth(), 1));
            const fin = fechaLocal(hoy);

            $('#fecha_inicio').val(inicio);
            $('#fecha_fin').val(fin);
            $('#filtro_personal_asignado').val('').trigger('change.select2');
            cargarAsignacion();
        }

        function fechaLocal(date) {
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return `${date.getFullYear()}-${month}-${day}`;
        }

        function abrirModalAsignacion(id = '', editar = false) {
            if (editar && !id) {
                Swal.fire('Error', 'No se recibio el ID del horario para editar.', 'error');
                return;
            }

            $.ajax({
                type: 'POST',
                url: "{{ route('asistencia.asignacion.modal_horario') }}",
                dataType: 'json',
                data: {
                    mac: $('#mac').val(),
                    id: id,
                    editar: editar ? 1 : 0,
                    fecha_inicio: $('#fecha_inicio').val()
                },
                beforeSend: function() {
                    $('#modal_show_modal').html(
                        '<div class="modal-dialog"><div class="modal-content"><div class="p-5 text-center"><i class="fa fa-spinner fa-spin"></i></div></div></div>'
                    );
                },
                success: function(res) {
                    $('#modal_show_modal').html(res.html);
                    $('#modal_show_modal').modal('show');
                },
                error: function(xhr) {
                    $('#modal_show_modal').modal('hide').html('');
                    Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo abrir el modal.', 'error');
                }
            });
        }

        function guardarAsignacionHorario() {
            let form = document.getElementById('formAsignacionHorario');
            let formData = new FormData(form);
            const esNuevo = !formData.get('id');

            let btn = $('#btnGuardarAsignacion');
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando');

            $.ajax({
                type: 'POST',
                url: "{{ route('asistencia.asignacion.store_horario') }}",
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    cargarAsignacion();
                    if (esNuevo && res.id) {
                        abrirModalAsignacion(res.id);
                        Swal.fire('OK', 'Horario guardado. Ahora puede marcar los sabados del mes.', 'success');
                        return;
                    }

                    $('#modal_show_modal').modal('hide');
                    Swal.fire('OK', res.message, 'success');
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(esNuevo ? 'Guardar' : 'Actualizar');
                    Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo guardar.', 'error');
                }
            });
        }

        function eliminarAsignacionHorario(id) {
            Swal.fire({
                title: 'Eliminar asignacion',
                text: 'Esta accion quitara el horario del personal.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.post("{{ route('asistencia.asignacion.delete_horario') }}", {
                    id: id,
                    mac: $('#mac').val()
                }).done(function(res) {
                    cargarAsignacion();
                    Swal.fire('OK', res.message, 'success');
                }).fail(function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo eliminar.', 'error');
                });
            });
        }

        function exportarAsignacion() {
            const params = $.param(filtrosAsignacion());
            window.location.href = "{{ route('asistencia.asignacion.export_excel') }}" + '?' + params;
        }
    </script>
@endsection
