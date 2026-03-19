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
                <h4 class="page-title">Horarios Diferenciados</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('inicio') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Horario Diferenciado</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Filtro de búsqueda</h5>
        </div>
        <div class="card-body">
            <div class="row">

                <div class="col-md-6">
                    <label class="fw-bold">Centro MAC</label>
                    @role('Administrador|Moderador')
                        <select id="filtro_mac" class="form-control select2">
                            <option value="">Todos</option>
                            @foreach ($macs as $mac)
                                <option value="{{ $mac->IDCENTRO_MAC }}">{{ $mac->NOMBRE_MAC }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" class="form-control" value="{{ $centro_mac->name_mac ?? '' }}" readonly>
                        <input type="hidden" id="filtro_mac" value="{{ $centro_mac->idmac ?? '' }}">
                    @endrole
                </div>

                <div class="col-md-6 d-flex align-items-end gap-2">
                    <button class="btn btn-primary" onclick="filtrarHorario()"><i class="fa fa-search"></i> Buscar</button>
                    <button class="btn btn-dark" onclick="limpiarFiltro()"><i class="fa fa-undo"></i></button>
                    <button class="btn btn-success ms-auto" onclick="btnAddHorario()"><i class="fa fa-plus"></i>
                        Nuevo</button>
                </div>

            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Lista de Horarios</h5>
        </div>
        <div class="card-body">
            <div id="table_data" class="table-responsive"></div>
        </div>
    </div>

    <div class="modal fade" id="modal_show_modal" tabindex="-1"></div>
@endsection

@section('script')
    <script src="{{ asset('Vendor/toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('//cdn.jsdelivr.net/npm/sweetalert2@11') }}"></script>
    <script src="{{ asset('nuevo/plugins/select2/select2.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('.select2').select2({
                width: '100%'
            });
            cargarHorarios();
        });

        function cargarHorarios(mac = '') {
            $.ajax({
                url: "{{ route('horario.tablas.tb_index') }}",
                type: 'GET',
                data: {
                    id_mac: mac
                },
                beforeSend: function() {
                    $('#table_data').html(
                        '<div class="text-center p-3"><i class="fa fa-spinner fa-spin"></i> Cargando...</div>'
                        );
                },
                success: function(res) {
                    $('#table_data').html(res);
                },
                error: function() {
                    $('#table_data').html('<div class="text-danger">Error al cargar</div>');
                }
            });
        }

        function filtrarHorario() {
            let mac = $('#filtro_mac').val();
            cargarHorarios(mac);
        }

        function limpiarFiltro() {
            $('#filtro_mac').val('').trigger('change');
            cargarHorarios();
        }

        function btnAddHorario() {
            $.ajax({
                type: 'POST',
                url: "{{ route('horario.modals.md_add_horario') }}",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                beforeSend: function() {
                    $('#modal_show_modal').html(
                        '<div class="p-5 text-center"><i class="fa fa-spinner fa-spin"></i></div>');
                },
                success: function(res) {
                    $('#modal_show_modal').html(res.html);
                    new bootstrap.Modal(document.getElementById('modal_show_modal')).show();
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo abrir el modal', 'error');
                }
            });
        }

        function btnEditHorario(id) {
            $.ajax({
                type: 'POST',
                url: "{{ route('horario.modals.md_edit_horario') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id
                },
                success: function(res) {
                    $('#modal_show_modal').html(res.html);
                    new bootstrap.Modal(document.getElementById('modal_show_modal')).show();
                }
            });
        }

        function btnDeleteHorario(id) {
            Swal.fire({
                title: 'Eliminar',
                text: '¿Seguro?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí',
                cancelButtonText: 'No'
            }).then(r => {
                if (r.isConfirmed) {
                    $.post("{{ route('horario.delete_horario') }}", {
                        _token: "{{ csrf_token() }}",
                        id: id
                    }, function() {
                        cargarHorarios();
                        Swal.fire('OK', 'Eliminado', 'success');
                    });
                }
            });
        }
    </script>
@endsection
