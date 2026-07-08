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
                    <h4 class="page-title">Monitoreo de Módulos Activos</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('inicio') }}">Inicio</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('modulos.index') }}">Módulos</a>
                        </li>
                        <li class="breadcrumb-item active">Monitoreo</li>
                    </ol>
                </div>

                <div class="col-auto">
                    <a href="{{ route('modulos.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- FILTROS --}}
<div class="card mb-3">
    <div class="card-header" style="background-color:#132842">
        <h4 class="card-title text-white mb-0">Filtro de Monitoreo</h4>
    </div>

    <div class="card-body">
        <div class="row align-items-end">

            {{-- Mes --}}
            <div class="col-md-2">
                <label class="fw-bold text-dark mb-2">Mes:</label>
                <select id="filtro_mes" class="form-control">
                    <option value="01">Enero</option>
                    <option value="02">Febrero</option>
                    <option value="03">Marzo</option>
                    <option value="04">Abril</option>
                    <option value="05">Mayo</option>
                    <option value="06">Junio</option>
                    <option value="07">Julio</option>
                    <option value="08">Agosto</option>
                    <option value="09">Septiembre</option>
                    <option value="10">Octubre</option>
                    <option value="11">Noviembre</option>
                    <option value="12">Diciembre</option>
                </select>
            </div>

            {{-- Año --}}
            <div class="col-md-2">
                <label class="fw-bold text-dark mb-2">Año:</label>
                <select id="filtro_anio" class="form-control">
                    @for ($y = date('Y') + 1; $y >= 2024; $y--)
                    <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>

            {{-- Centro MAC --}}
            <div class="col-md-3">
                <label class="fw-bold text-dark mb-2">Centro MAC:</label>

                @role('Administrador|Moderador')
                <select id="filtro_mac" class="form-control select2">
                    <option value="">-- Todos los MAC --</option>
                    @foreach ($macs as $mac)
                    <option value="{{ $mac->IDCENTRO_MAC }}">
                        {{ $mac->NOMBRE_MAC }}
                    </option>
                    @endforeach
                </select>
                @else
                <input type="text"
                    class="form-control text-uppercase"
                    value="{{ $centro_mac->name_mac ?? 'No asignado' }}"
                    readonly>

                <input type="hidden"
                    id="filtro_mac"
                    value="{{ $centro_mac->idmac ?? '' }}">
                @endrole
            </div>

            {{-- Entidad --}}
            <div class="col-md-3">
                <label class="fw-bold text-dark mb-2">Entidad:</label>
                <select id="filtro_entidad" class="form-control select2">
                    <option value="">-- Todas --</option>
                    @foreach ($entidades as $ent)
                    <option value="{{ $ent->IDENTIDAD }}">
                        {{ $ent->NOMBRE_ENTIDAD }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Administrativo --}}
            <div class="col-md-2">
                <label class="fw-bold text-dark mb-2">Administrativo:</label>
                <select id="filtro_admin" class="form-control">
                    <option value="">-- Todos --</option>
                    <option value="SI">Sí</option>
                    <option value="NO">No</option>
                </select>
            </div>

            {{-- Botones --}}
            <div class="col-md-12 mt-3">
                <div class="d-flex gap-1">
                    <button class="btn btn-primary" onclick="filtrarMonitoreo()">
                        <i class="fa fa-search"></i> Buscar
                    </button>

                    <button class="btn btn-dark" onclick="limpiarFiltroMonitoreo()">
                        <i class="fa fa-undo"></i> Limpiar
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- LISTA --}}
<div class="card">
    <div class="card-header" style="background-color:#132842">
        <h4 class="card-title text-white mb-0">Lista de Módulos Activos por Periodo</h4>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <div id="table_monitoreo"></div>
        </div>
    </div>
</div>
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
        $('.select2').select2({
            width: '100%'
        });

        const hoy = new Date();
        const mesActual = String(hoy.getMonth() + 1).padStart(2, '0');
        const anioActual = hoy.getFullYear();

        $('#filtro_mes').val(mesActual);
        $('#filtro_anio').val(anioActual);

        cargarMonitoreo();
    });

    function cargarMonitoreo(mac = '', entidad = '', admin = '') {
        $.ajax({
            url: "{{ route('modulos.tablas.tb_monitoreo') }}",
            type: 'GET',
            dataType: 'json',
            data: {
                mes: $('#filtro_mes').val(),
                anio: $('#filtro_anio').val(),
                id_mac: mac,
                id_entidad: entidad,
                es_admin: admin
            },
            beforeSend: function() {
                if ($.fn.DataTable.isDataTable('#tabla_monitoreo_modulos')) {
                    $('#tabla_monitoreo_modulos').DataTable().clear().destroy();
                }

                $('#table_monitoreo').html(
                    '<div class="text-center p-3">' +
                    '<i class="fa fa-spinner fa-spin"></i> Cargando...' +
                    '</div>'
                );
            },
            success: function(response) {
                $('#table_monitoreo').html(response.html);

                inicializarTablaMonitoreo();
            },
            error: function(xhr) {
                console.error(xhr.responseText);

                $('#table_monitoreo').html(
                    '<div class="alert alert-danger mb-0">' +
                    '<i class="fa fa-exclamation-triangle"></i> ' +
                    'Error al cargar el monitoreo.' +
                    '</div>'
                );
            }
        });
    }

    function inicializarTablaMonitoreo() {
        if (!$('#tabla_monitoreo_modulos').length) {
            return;
        }

        if ($.fn.DataTable.isDataTable('#tabla_monitoreo_modulos')) {
            $('#tabla_monitoreo_modulos').DataTable().clear().destroy();
        }

        $('#tabla_monitoreo_modulos').DataTable({
            responsive: true,
            pageLength: 10,
            ordering: true,
            autoWidth: false,
            bLengthChange: true,
            searching: true,
            info: true,
            language: {
                url: "{{ asset('js/Spanish.json') }}"
            },
            columns: [{
                    width: "5%"
                },
                {
                    width: "15%"
                },
                {
                    width: "15%"
                },
                {
                    width: "25%"
                },
                {
                    width: "12%"
                },
                {
                    width: "12%"
                },
                {
                    width: "10%"
                },
                {
                    width: "10%"
                }
            ]
        });
    }

    function filtrarMonitoreo() {
        const mac = $('#filtro_mac').val();
        const entidad = $('#filtro_entidad').val();
        const admin = $('#filtro_admin').val();

        cargarMonitoreo(mac, entidad, admin);
    }

    function limpiarFiltroMonitoreo() {
        const hoy = new Date();
        const mesActual = String(hoy.getMonth() + 1).padStart(2, '0');
        const anioActual = hoy.getFullYear();

        $('#filtro_mes').val(mesActual);
        $('#filtro_anio').val(anioActual);

        @role('Administrador|Moderador')
        $('#filtro_mac').val('').trigger('change');
        @endrole

        $('#filtro_entidad').val('').trigger('change');
        $('#filtro_admin').val('');

        cargarMonitoreo();
    }
</script>
@endsection