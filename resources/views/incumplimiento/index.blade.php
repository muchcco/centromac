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
                            @foreach ($centros_mac as $mac)
                                <option value="{{ $mac->idcentro_mac }}">{{ $mac->nombre_mac }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" class="form-control text-uppercase"
                            value="{{ $centro_mac->name_mac ?? 'No asignado' }}" readonly>
                        <input type="hidden" id="filtro_mac" value="{{ $centro_mac->idmac }}">
                    @endrole
                </div>

                <!-- Fechas -->
                <div class="col-md-3">
                    <label class="fw-bold text-dark mb-2">Fecha Inicio:</label>
                    <input type="date" id="filtro_fecha_inicio" class="form-control" value="{{ date('Y-m-01') }}">
                </div>

                <div class="col-md-3">
                    <label class="fw-bold text-dark mb-2">Fecha Fin:</label>
                    <input type="date" id="filtro_fecha_fin" class="form-control" value="{{ date('Y-m-d') }}">
                </div>

                <!-- Botones -->
                <div class="col-md-2 d-flex align-items-end">
                    <div class="d-flex gap-1 w-100">
                        <button id="btnBuscar" class="btn btn-primary w-50">
                            <i class="fa fa-search"></i> Buscar
                        </button>
                        <button class="btn btn-dark w-50" onclick="limpiarFiltro()">
                            <i class="fa fa-undo"></i> Limpiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- 🔹 FILTROS ADICIONALES -->
            <div id="extraFiltros" class="mt-3" style="display:none;">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="fw-bold text-dark">Entidad:</label>
                        <select id="filtro_entidad" class="form-control select2" style="width:100%;">
                            <option value="">-- Todas --</option>
                            @foreach ($entidades as $ent)
                                <option value="{{ $ent->nombre_entidad }}">{{ $ent->abrev_entidad }} -
                                    {{ $ent->nombre_entidad }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="fw-bold text-dark">Tipificación:</label>
                        <select id="filtro_tipificacion" class="form-control select2" style="width:100%;">
                            <option value="">-- Todas --</option>
                            @foreach ($tipos as $tip)
                                <option value="{{ $tip->nom_tipo_int_obs }}">{{ $tip->tipo }} {{ $tip->numeracion }} -
                                    {{ $tip->nom_tipo_int_obs }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="fw-bold text-dark">Estado:</label>
                        <select id="filtro_estado" class="form-control select2" style="width:100%;">
                            <option value="">-- Todos --</option>
                            <option value="ABIERTO">Abierto</option>
                            <option value="CERRADO">Cerrado</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="fw-bold text-dark">Revisión:</label>
                        <select id="filtro_revision" class="form-control select2" style="width:100%;">
                            <option value="">-- Todos --</option>
                            <option value="observado">Observado</option>
                            <option value="no_observado">No observado</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mt-3 text-center">
                <button id="btnToggleFiltros" class="btn btn-outline-dark w-100">
                    <i class="fa fa-filter"></i> Ver más filtros
                </button>
            </div>
        </div>
    </div>

    <!-- 🔹 TABLA PRINCIPAL -->
    <div class="card">
        <div class="card-header" style="background-color:#132842">
            <h4 class="card-title text-white">Listado de Incidentes Operativos</h4>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <button class="btn btn-success" onclick="btnAddIncumplimiento()">
                    <i class="fa fa-plus"></i> Nuevo Incidente
                </button>
                <button class="btn btn-outline-primary" onclick="exportarExcel()">
                    <i class="fa fa-file-excel"></i> Exportar Excel
                </button>
            </div>
            <div id="table_data" class="table-responsive"></div>
        </div>
    </div>

    <div class="modal fade" id="modal_show_modal" tabindex="-1"></div>
@endsection


@section('script')
    <script src="{{ asset('//cdn.jsdelivr.net/npm/sweetalert2@11') }}"></script>
    <script src="{{ asset('Vendor/toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/select2/select2.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('.select2').select2();
            cargarTablaIncumplimientos();

            //  Botón Buscar
            $('#btnBuscar').click(e => {
                e.preventDefault();
                cargarTablaIncumplimientos();
            });

            //  Cuando cambie cualquier filtro adicional, recarga automáticamente
            $('#extraFiltros select, #extraFiltros input').on('change keyup', function() {
                cargarTablaIncumplimientos();
            });
        });


        // 🔹 CARGAR TABLA
        function cargarTablaIncumplimientos() {
            $.get("{{ route('incumplimiento.tablas.tb_index') }}", {
                idmac: $('#filtro_mac').val(),
                fecha_inicio: $('#filtro_fecha_inicio').val(),
                fecha_fin: $('#filtro_fecha_fin').val(),
                entidad: $('#filtro_entidad').val(),
                tipificacion: $('#filtro_tipificacion').val(),
                estado: $('#filtro_estado').val(),
                revision: $('#filtro_revision').val()
            }, data => $('#table_data').html(data));
        }

        // 🔹 Mostrar/ocultar filtros adicionales
        $('#btnToggleFiltros').click(function() {
            const extra = $('#extraFiltros');
            const visible = extra.is(':visible');
            extra.slideToggle(300);
            $(this).html(visible ? '<i class="fa fa-filter"></i> Ver más filtros' :
                '<i class="fa fa-chevron-up"></i> Ver menos filtros');
            if (visible) {
                $('#filtro_entidad, #filtro_tipificacion, #filtro_estado, #filtro_revision').val('').trigger(
                    'change');
                cargarTablaIncumplimientos();
            }
        });

        // 🔹 CRUD BÁSICO
        function btnAddIncumplimiento() {
            $.post("{{ route('incumplimiento.modals.md_add_incumplimiento') }}", {
                _token: "{{ csrf_token() }}"
            }, function(data) {
                $('#modal_show_modal').html(data.html).modal('show');
                $('.select2').select2({
                    dropdownParent: $('#modal_show_modal')
                });
            });
        }

        function btnStoreIncumplimiento() {
            let form = $('#form_add_incumplimiento')[0];
            let data = new FormData(form);
            $.ajax({
                url: "{{ route('incumplimiento.store') }}",
                method: 'POST',
                data: data,
                processData: false,
                contentType: false,
                success: res => {
                    if (res.status === 201) {
                        Swal.fire('', res.message, 'success');
                        $('#modal_show_modal').modal('hide');
                        cargarTablaIncumplimientos();
                    }
                },
                error: () => Swal.fire('❌ Error', 'No se pudo guardar', 'error')
            });
        }

        function btnEditarIncumplimiento(id) {
            $.post("{{ route('incumplimiento.modals.md_edit_incumplimiento') }}", {
                _token: "{{ csrf_token() }}",
                id_observacion: id
            }, res => {
                $('#modal_show_modal').html(res.html).modal('show');
                $('.select2').select2({
                    dropdownParent: $('#modal_show_modal')
                });
            });
        }

        function btnUpdateIncumplimiento() {
            let data = new FormData($('#form_edit_incumplimiento')[0]);
            $.ajax({
                url: "{{ route('incumplimiento.update') }}",
                method: 'POST',
                data: data,
                processData: false,
                contentType: false,
                success: res => {
                    if (res.status === 200) {
                        Swal.fire('', res.message, 'success');
                        $('#modal_show_modal').modal('hide');
                        cargarTablaIncumplimientos();
                    }
                }
            });
        }

        function btnEliminarIncumplimiento(id) {
            Swal.fire({
                title: '¿Eliminar?',
                text: 'Se eliminará permanentemente.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar'
            }).then(result => {
                if (result.isConfirmed) {
                    $.post("{{ route('incumplimiento.delete') }}", {
                        _token: "{{ csrf_token() }}",
                        id_observacion: id
                    }, res => {
                        if (res.status === 200) {
                            Swal.fire(' Eliminado', res.message, 'success');
                            cargarTablaIncumplimientos();
                        } else {
                            Swal.fire('⚠️', 'No se pudo eliminar', 'warning');
                        }
                    }).fail(() => Swal.fire('❌', 'Error al eliminar', 'error'));
                }
            });
        }


        function btnVerIncumplimiento(id) {
            $.post("{{ route('incumplimiento.modals.md_ver_incumplimiento') }}", {
                _token: "{{ csrf_token() }}",
                id_observacion: id
            }, res => {
                $('#modal_show_modal').html(res.html).modal('show');
            });
        }

        // 🔹 OBSERVAR / RETROALIMENTAR
        function btnObservarIncumplimiento(id) {
            $.post("{{ route('incumplimiento.modals.md_observar_incumplimiento') }}", {
                _token: "{{ csrf_token() }}",
                id_observacion: id
            }, res => {
                $('#modal_show_modal').html(res.html).modal('show');
            }).fail(() => {
                Swal.fire('❌', 'No se pudo cargar el formulario de observación', 'error');
            });
        }

        function guardarObservacion() {
            const form = $('#form_observar_incumplimiento');

            // Leer estado real de los checkboxes
            const observado = $('#chk_observado').is(':checked') ? 1 : 0;
            const corregido = $('#chk_corregido').is(':checked') ? 1 : 0;

            // Serializar datos correctamente
            const data = form.serializeArray();
            const payload = {};
            data.forEach(item => payload[item.name] = item.value);
            payload['observado'] = observado;
            payload['corregido'] = corregido;

            $.post("{{ route('incumplimiento.observarGuardar') }}", payload, res => {
                if (res.status === 200) {
                    Swal.fire('', res.message, 'success');
                    $('#modal_show_modal').modal('hide');
                    cargarTablaIncumplimientos();
                } else {
                    Swal.fire('⚠️', res.message, 'warning');
                }
            }).fail(() => Swal.fire('❌', 'Error al guardar observación', 'error'));
        }

        // 🔹 OBSERVAR / RETROALIMENTAR
        function btnObservarIncumplimiento(id) {
            $.post("{{ route('incumplimiento.modals.md_observar_incumplimiento') }}", {
                _token: "{{ csrf_token() }}",
                id_observacion: id
            }, res => {
                $('#modal_show_modal').html(res.html).modal('show');
            }).fail(() => {
                Swal.fire('❌', 'No se pudo cargar el formulario de observación', 'error');
            });
        }

        // 🔹 EXPORTAR
        function exportarExcel() {
            const params = {
                idmac: $('#filtro_mac').val() || '',
                fecha_inicio: $('#filtro_fecha_inicio').val() || '',
                fecha_fin: $('#filtro_fecha_fin').val() || '',
                entidad: $('#filtro_entidad').val() || '',
                tipificacion: $('#filtro_tipificacion').val() || '',
                estado: $('#filtro_estado').val() || '',
                revision: $('#filtro_revision').val() || ''
            };
            window.location.href = "{{ route('incumplimiento.export_excel') }}" + '?' + $.param(params);
        }


        // 🔹 LIMPIAR
        function limpiarFiltro() {
            $('#filtro_mac').val('').trigger('change');
            $('#filtro_fecha_inicio').val('{{ date('Y-m-01') }}');
            $('#filtro_fecha_fin').val('{{ date('Y-m-d') }}');

            //  limpiar filtros adicionales
            $('#filtro_entidad, #filtro_tipificacion, #filtro_estado, #filtro_revision')
                .val('')
                .trigger('change');

            //  cerrar panel de filtros si está abierto
            if ($('#extraFiltros').is(':visible')) {
                $('#extraFiltros').slideUp(300);
                $('#btnToggleFiltros').html('<i class="fa fa-filter"></i> Ver más filtros');
            }

            cargarTablaIncumplimientos();
        } 
            // 🔹 Si se quita la observación, limpia automáticamente el corregido
            $('#chk_observado').on('change', function() {
                if (!$(this).is(':checked')) {
                    $('#chk_corregido').prop('checked', false);
                }
            });
   
    </script>
@endsection
