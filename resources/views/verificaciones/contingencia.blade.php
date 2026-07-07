@extends('layouts.layout')

@section('style')
<style>
    .table thead th {
        background: #233E99;
        color: white;
        font-size: 11px;
        text-align: center;
        vertical-align: middle;
    }

    .table td {
        text-align: center;
        font-size: 11px;
        padding: 4px;
        vertical-align: middle;
    }

    .ok {
        color: #198754;
        font-weight: bold;
    }

    .fail {
        color: #dc3545;
        font-weight: bold;
    }

    .sticky {
        position: sticky;
        left: 0;
        background: #fff;
        font-weight: bold;
        z-index: 1;
    }

    .table thead .sticky {
        background: #233E99;
        z-index: 2;
    }

    .btn-detalle-dia {
        font-size: 11px;
        font-weight: bold;
        border-radius: 0;
    }

    .btn-detalle-dia:hover {
        background: rgba(255, 255, 255, .15);
        color: #fff;
    }

    @media print {

        .page-title-box,
        .card,
        .btn,
        .modal {
            display: none !important;
        }

        .sticky {
            position: static !important;
        }
    }
</style>
@endsection

@section('main')
<div class="container">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="row">
                    <div class="col">
                        <h4 class="page-title">Contingencia de Verificaciones</h4>

                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('inicio') }}">Inicio</a>
                            </li>

                            <li class="breadcrumb-item">
                                <a href="{{ route('verificaciones.index') }}">Verificaciones</a>
                            </li>

                            <li class="breadcrumb-item active">
                                Contingencia Mensual
                            </li>
                        </ol>
                    </div>

                    <div class="col-auto">
                        <a href="{{ route('verificaciones.index') }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header" style="background-color:#132842">
            <h4 class="card-title text-white mb-0">
                Filtro de Contingencia
            </h4>
        </div>

        <div class="card-body">
            <form method="GET">
                <div class="row align-items-end">

                    <div class="col-md-4">
                        <label class="fw-bold text-dark mb-2">
                            Centro MAC:
                        </label>

                        @if ($puedeElegirMac)
                        <select name="idmac" class="form-control">
                            @foreach ($centrosMac as $centro)
                            <option value="{{ $centro->id }}"
                                {{ (int) $idmac === (int) $centro->id ? 'selected' : '' }}>
                                {{ $centro->nom }}
                            </option>
                            @endforeach
                        </select>
                        @else
                        <input
                            type="text"
                            class="form-control bg-light"
                            value="{{ $name_mac }}"
                            readonly>

                        <input
                            type="hidden"
                            name="idmac"
                            value="{{ $idmac }}">
                        @endif
                    </div>

                    <div class="col-md-3">
                        <label class="fw-bold text-dark mb-2">
                            Mes:
                        </label>

                        <input
                            type="month"
                            name="mes"
                            value="{{ $mes }}"
                            class="form-control">
                    </div>

                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-search"></i> Filtrar
                        </button>
                    </div>

                    <div class="col-md-2 d-grid">
                        <button
                            type="button"
                            class="btn btn-success"
                            onclick="window.print()">
                            <i class="fa fa-print"></i> Imprimir
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <div class="mb-2">
        <strong>Centro MAC:</strong> {{ $name_mac }}

        <span class="ms-3">
            <strong>Mes:</strong>
            {{ \Carbon\Carbon::createFromFormat('Y-m', $mes)->translatedFormat('F Y') }}
        </span>
    </div>

    <div style="overflow-x:auto;">
        <table class="table table-bordered table-sm">

            <thead>
                <tr>
                    <th class="sticky">Campo</th>

                    @foreach (range(1, 31) as $d)
                    <th class="p-0">
                        <button
                            type="button"
                            class="btn btn-link text-white text-decoration-none w-100 py-2 btn-detalle-dia"
                            data-dia="{{ $d }}"
                            title="Ver detalle del día {{ $d }}">
                            {{ $d }}
                        </button>
                    </th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @foreach ($campos as $campo)
                @php
                $nombre = preg_replace('/([a-z])([A-Z])/', '$1 $2', $campo);
                @endphp

                <tr>
                    <td class="sticky">
                        {{ $nombre }}
                    </td>

                    @foreach (range(1, 31) as $d)
                    @php
                    $val = $matriz[$campo][$d] ?? '-';
                    @endphp

                    <td class="{{ $val == '✅' ? 'ok' : ($val != '-' ? 'fail' : '') }}">
                        {{ $val }}
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>

        </table>
    </div>

</div>

<div class="modal fade" id="modalDetalleContingencia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header" style="background-color:#132842">
                <h5 class="modal-title text-white">
                    Detalle de Verificación Diaria
                </h5>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"></button>
            </div>

            <div class="modal-body" id="contenidoDetalleContingencia">
                <div class="text-center py-5">
                    <i class="fa fa-spinner fa-spin"></i>
                    Cargando información...
                </div>
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>

        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).on('click', '.btn-detalle-dia', function() {
        const dia = $(this).data('dia');
        const mes = @json($mes);
        const idmac = @json($idmac);

        $('#contenidoDetalleContingencia').html(`
            <div class="text-center py-5">
                <i class="fa fa-spinner fa-spin"></i>
                Cargando información...
            </div>
        `);

        const modalElemento = document.getElementById('modalDetalleContingencia');
        const modal = new bootstrap.Modal(modalElemento);

        modal.show();

        $.ajax({
            url: '{{ route("verificaciones.contingencia.detalle-dia") }}',
            type: 'GET',
            data: {
                dia: dia,
                mes: mes,
                idmac: idmac
            },
            success: function(response) {
                $('#contenidoDetalleContingencia').html(response.html);
            },
            error: function(xhr) {
                let mensaje = 'No se pudo cargar el detalle de este día.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    mensaje = xhr.responseJSON.message;
                }

                $('#contenidoDetalleContingencia').html(`
                    <div class="alert alert-danger mb-0">
                        ${mensaje}
                    </div>
                `);
            }
        });
    });
</script>
@endsection