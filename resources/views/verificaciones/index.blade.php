@extends('layouts.layout')

@section('style')
    <link rel="stylesheet" href="{{ asset('Vendor/toastr/toastr.min.css') }}">
    <link href="{{ asset('nuevo/plugins/select2/select2.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" />

    <style>
        .card {
            border-radius: 10px;
        }

        .card-header {
            font-weight: 600;
        }

        .btn {
            border-radius: 6px;
        }
    </style>
@endsection

@section('main')
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="row">
                    <div class="col">
                        <h4 class="page-title">Verificaciones</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('inicio') }}">
                                    <i data-feather="home" style="height:70%"></i>
                                </a>
                            </li>
                            <li class="breadcrumb-item">Módulo de Verificaciones</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 🔹 FILTRO -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm">

                <div class="card-header" style="background-color:#132842">
                    <h4 class="card-title text-white mb-0">Filtro de Búsqueda</h4>
                </div>

                <div class="card-body bootstrap-select-1">

                    <div class="row align-items-end">

                        <div class="col-md-4">
                            <label class="mb-2 fw-semibold">Fecha Inicio</label>
                            <input type="date" id="fecha_inicio" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="mb-2 fw-semibold">Fecha Fin</label>
                            <input type="date" id="fecha_fin" class="form-control">
                        </div>

                        <div class="col-md-4 text-end">
                            <button class="btn btn-primary me-2" onclick="execute_filter()">
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
    </div>

    <!-- 🔹 LISTA -->
    <div class="row">
        <div class="col-lg-12">

            <div class="card">

                <div class="card-header" style="background-color:#132842">
                    <h4 class="card-title text-white">
                        LISTA DE VERIFICACIONES
                    </h4>
                </div>

                <div class="card-body">

                    <!-- 🔥 BOTONES IGUAL QUE ASISTENCIA -->
                    <div class="mb-3">

                        <form action="{{ route('verificaciones.create') }}" method="GET" class="d-inline">
                            <button class="btn btn-primary">
                                <i class="fa fa-plus"></i> Crear Verificación
                            </button>
                        </form>

                        <form action="{{ route('verificaciones.observaciones') }}" method="GET" class="d-inline">
                            <button class="btn btn-info">
                                <i class="fa fa-file-alt"></i> Formato
                            </button>
                        </form>

                        <form action="{{ route('verificaciones.contingencia') }}" method="GET" class="d-inline">
                            <button class="btn btn-warning">
                                <i class="fa fa-table"></i> Tabla
                            </button>
                        </form>

                    </div>

                    <!-- 🔴 TU TABLA (INTACTA) -->
                    @if ($data->count() > 0)
                        <div class="table-responsive">

                            <table class="table table-striped" id="verificaciones-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Fecha</th>
                                        <th>Apertura</th>
                                        <th>Relevo</th>
                                        <th>Cierre</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($data as $fecha => $items)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>

                                            <td>{{ date('d/m/Y', strtotime($fecha)) }}</td>

                                            <td>
                                                {{ $items->where('AperturaCierre', 0)->isNotEmpty() ? 'OK' : 'Falta' }}
                                            </td>

                                            <td>
                                                {{ $items->where('AperturaCierre', 1)->isNotEmpty() ? 'OK' : 'Falta' }}
                                            </td>

                                            <td>
                                                {{ $items->where('AperturaCierre', 2)->isNotEmpty() ? 'OK' : 'Falta' }}
                                            </td>

                                            <td>
                                                @php
                                                    $verificacion = $items->first();
                                                @endphp

                                                @if ($verificacion)
                                                    <!-- VER -->
                                                    <a href="{{ route('verificaciones.show', \Carbon\Carbon::parse($fecha)->format('Y-m-d')) }}"
                                                        class="btn btn-secondary">
                                                        <i class="fas fa-eye"></i> Ver
                                                    </a>
                                                    <!-- APERTURA -->
                                                    @if ($items->where('AperturaCierre', 0)->isNotEmpty())
                                                        <a href="{{ route('verificaciones.edit', [
                                                            'AperturaCierre' => 0,
                                                            'Fecha' => $fecha,
                                                        ]) }}"
                                                            class="btn btn-success">
                                                            Apertura
                                                        </a>
                                                    @endif

                                                    <!-- RELEVO -->
                                                    @if ($items->where('AperturaCierre', 1)->isNotEmpty())
                                                        <a href="{{ route('verificaciones.edit', [
                                                            'AperturaCierre' => 1,
                                                            'Fecha' => $fecha,
                                                        ]) }}"
                                                            class="btn btn-primary">
                                                            Relevo
                                                        </a>
                                                    @endif

                                                    <!-- CIERRE -->
                                                    @if ($items->where('AperturaCierre', 2)->isNotEmpty())
                                                        <a href="{{ route('verificaciones.edit', [
                                                            'AperturaCierre' => 2,
                                                            'Fecha' => $fecha,
                                                        ]) }}"
                                                            class="btn btn-danger">
                                                            Cierre
                                                        </a>
                                                    @endif

                                                    <!-- CAMBIO HORA -->
                                                    @can('Update_basico_1')
                                                        <button class="btn btn-info"
                                                            onclick="btnModalHora('{{ $fecha }}')">
                                                            Cambio hora
                                                        </button>
                                                    @endcan
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                        </div>
                    @else
                        <div class="alert alert-warning text-center">
                            No hay verificaciones registradas.
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="modal_show_modal"></div>
@endsection

@section('script')
    <script src="{{ asset('Script/js/sweet-alert.min.js') }}"></script>
    <script src="{{ asset('Vendor/toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('//cdn.jsdelivr.net/npm/sweetalert2@11') }}"></script>

    <!-- Plugins js -->
    <script src="{{ asset('nuevo/plugins/select2/select2.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/timepicker/bootstrap-material-datetimepicker.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.js') }}"></script>

    <script>
        /* =========================
       🔷 DATATABLE
    ========================= */
        $(document).ready(function() {
            $('#verificaciones-table').DataTable({
                pageLength: 10,
                ordering: false,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
                }
            });
        });


        /* =========================
           🔷 FILTRO (SIN AJAX)
        ========================= */
        function execute_filter() {

            const fi = $('#fecha_inicio').val();
            const ff = $('#fecha_fin').val();

            if (!fi || !ff) {
                Swal.fire('Atención', 'Selecciona ambas fechas', 'warning');
                return;
            }

            window.location.href = "{{ route('verificaciones.index') }}" +
                "?fecha_inicio=" + fi +
                "&fecha_fin=" + ff;
        }


        /* =========================
           🔷 LIMPIAR FILTRO
        ========================= */
        function limpiarFiltro() {
            $('#fecha_inicio').val('');
            $('#fecha_fin').val('');
            location.reload();
        }


        /* =========================
           🔷 ABRIR MODAL CAMBIO HORA
        ========================= */
        function btnModalHora(fecha) {
            $.ajax({
                type: 'POST',
                url: "{{ route('verificaciones.modals.up_time') }}",
                dataType: "json",
                data: {
                    "_token": "{{ csrf_token() }}",
                    fecha: fecha
                },
                success: function(data) {
                    $("#modal_show_modal").html(data.html);
                    $("#modal_show_modal").modal('show');
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo cargar el modal', 'error');
                }
            });
        }
        /* =========================
           🔥 VALIDACIÓN EN VIVO
        ========================= */
        document.addEventListener('input', function(e) {
            if (e.target.id === 'hora_inicio' || e.target.id === 'hora_fin') {
                const hi = document.getElementById('hora_inicio')?.value;
                const hf = document.getElementById('hora_fin')?.value;
                if (!hi || !hf) return;
                const alerta = document.getElementById('alertaHoras');
                const btn = document.getElementById('btnGuardar');
                if (hi >= hf) {
                    alerta.classList.remove('d-none');
                    btn.disabled = true;
                } else {
                    alerta.classList.add('d-none');
                    btn.disabled = false;
                }
            }
        });

        /* =========================
           🔥 GUARDAR (AJAX FETCH PRO)
        ========================= */
        document.addEventListener('click', async function(e) {
            if (e.target.id === 'btnGuardar') {
                const btn = e.target;
                const form = document.getElementById('formUpdateTime');
                if (!form) return;
                const formData = new FormData(form);
                const hi = formData.get('hora_inicio');
                const hf = formData.get('hora_fin');
                if (hi >= hf) {
                    Swal.fire('Error', 'La hora inicio debe ser menor que la hora fin', 'warning');
                    return;
                }
                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Guardando...';
                try {
                    const res = await fetch("{{ route('verificaciones.update_time') }}", {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    const data = await res.json();
                    if (data.success) {
                        await Swal.fire({
                            title: 'Éxito',
                            text: 'Horarios actualizados correctamente',
                            icon: 'success'
                        });
                        // 🔥 cerrar modal
                        const modalEl = document.querySelector('.modal.show');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        modal.hide();
                        // 🔥 recargar tabla
                        location.reload();
                    } else {
                        throw new Error();
                    }
                } catch (error) {
                    Swal.fire('Error', 'No se pudo actualizar', 'error');
                }
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-save"></i> Guardar';
            }
        });
    </script>
@endsection
