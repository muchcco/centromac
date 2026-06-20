@extends('layouts.layout')

@section('style')
    <link href="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <link href="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet">
@endsection

@section('main')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row">
                <div class="col">
                    <h4 class="page-title">API · Historial de Sincronizaciones</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('inicio') }}"><i data-feather="home" class="align-self-center" style="height:70%;display:block;"></i></a></li>
                        <li class="breadcrumb-item">Asistencia API</li>
                        <li class="breadcrumb-item active">Sincronizaciones</li>
                    </ol>
                </div>
                <div class="col-auto">
                    <a href="{{ route('asistencia-api.tokens.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i data-feather="key" style="width:14px;height:14px;"></i> Gestionar Tokens
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filtros --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('asistencia-api.logs.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label form-label-sm mb-1">Centro MAC</label>
                <select name="id_mac" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach($macs as $mac)
                        <option value="{{ $mac->IDCENTRO_MAC }}" {{ request('id_mac') == $mac->IDCENTRO_MAC ? 'selected' : '' }}>
                            {{ $mac->NOMBRE_MAC }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label form-label-sm mb-1">Estado</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="ok"      {{ request('status') === 'ok'      ? 'selected' : '' }}>OK</option>
                    <option value="error"   {{ request('status') === 'error'   ? 'selected' : '' }}>Error</option>
                    <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Parcial</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label form-label-sm mb-1">Desde</label>
                <input type="date" name="desde" class="form-control form-control-sm" value="{{ request('desde') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label form-label-sm mb-1">Hasta</label>
                <input type="date" name="hasta" class="form-control form-control-sm" value="{{ request('hasta') }}">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-feather="filter" style="width:14px;height:14px;"></i> Filtrar
                </button>
                <a href="{{ route('asistencia-api.logs.index') }}" class="btn btn-outline-secondary btn-sm">
                    Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Resumen rápido --}}
@php
    $totalInsertados = $logs->sum('total_insertados');
    $totalDuplicados = $logs->sum('total_duplicados');
    $totalRecibidos  = $logs->sum('total_recibidos');
@endphp
<div class="row mb-3">
    <div class="col-sm-4">
        <div class="card bg-success bg-opacity-10 border-success border-opacity-25">
            <div class="card-body py-2 text-center">
                <div class="h4 mb-0 text-success">{{ number_format($totalInsertados) }}</div>
                <small class="text-muted">Insertados (página)</small>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card bg-warning bg-opacity-10 border-warning border-opacity-25">
            <div class="card-body py-2 text-center">
                <div class="h4 mb-0 text-warning">{{ number_format($totalDuplicados) }}</div>
                <small class="text-muted">Duplicados (página)</small>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card bg-info bg-opacity-10 border-info border-opacity-25">
            <div class="card-body py-2 text-center">
                <div class="h4 mb-0 text-info">{{ number_format($totalRecibidos) }}</div>
                <small class="text-muted">Recibidos (página)</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tbl-logs" class="table table-hover table-sm align-middle dt-responsive" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Fecha y hora</th>
                        <th>MAC</th>
                        <th>Token</th>
                        <th class="text-end">Recibidos</th>
                        <th class="text-end">Insertados</th>
                        <th class="text-end">Duplicados</th>
                        <th>Estado</th>
                        <th>IP origen</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->id }}</td>
                        <td>
                            <small>{{ $log->created_at->format('d/m/Y H:i:s') }}</small>
                        </td>
                        <td>
                            <span class="badge bg-secondary">id={{ $log->id_mac }}</span>
                        </td>
                        <td>
                            @if($log->token)
                                <span title="{{ $log->token->nombre }}" style="cursor:default;">
                                    {{ Str::limit($log->token->nombre, 20) }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end">{{ number_format($log->total_recibidos) }}</td>
                        <td class="text-end text-success fw-semibold">{{ number_format($log->total_insertados) }}</td>
                        <td class="text-end text-warning">{{ number_format($log->total_duplicados) }}</td>
                        <td>
                            @if($log->status === 'ok')
                                <span class="badge bg-success">OK</span>
                            @elseif($log->status === 'error')
                                <span class="badge bg-danger">Error</span>
                            @else
                                <span class="badge bg-warning text-dark">Parcial</span>
                            @endif
                        </td>
                        <td><small class="font-monospace text-muted">{{ $log->ip_origen }}</small></td>
                        <td>
                            @if($log->mensaje)
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0"
                                        onclick="showMensaje('{{ addslashes($log->mensaje) }}')">
                                    <i data-feather="info" style="width:13px;height:13px;"></i>
                                </button>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">No hay registros de sincronización.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-2">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{ asset('nuevo/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('nuevo/assets/js/sweetalert2.min.js') }}"></script>
<script>
$(function () {
    $('#tbl-logs').DataTable({
        paging:    false,
        info:      false,
        searching: false,
        order:     [[0, 'desc']],
        columnDefs: [{ targets: [9], orderable: false }],
        language:  { emptyTable: 'Sin registros.' }
    });
    feather.replace();
});

function showMensaje(msg) {
    Swal.fire({
        icon: 'info',
        title: 'Detalle del registro',
        text: msg,
        confirmButtonText: 'Cerrar'
    });
}
</script>
@endsection
