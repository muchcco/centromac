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
                    <h4 class="page-title">API · Tokens de Acceso</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('inicio') }}"><i data-feather="home" class="align-self-center" style="height:70%;display:block;"></i></a></li>
                        <li class="breadcrumb-item">Asistencia API</li>
                        <li class="breadcrumb-item active">Tokens</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Token recién creado — se muestra una sola vez --}}
@if(session('token_plain'))
<div class="row mb-3">
    <div class="col-12">
        <div class="alert alert-warning border border-warning shadow-sm" role="alert">
            <div class="d-flex align-items-start gap-2">
                <i data-feather="alert-triangle" style="min-width:22px;margin-top:2px;"></i>
                <div class="w-100">
                    <strong>Token generado — cópialo ahora, no se volverá a mostrar</strong>
                    <div class="input-group mt-2">
                        <input id="plain-token" type="text" class="form-control font-monospace"
                               value="{{ session('token_plain') }}" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyToken()">
                            <i data-feather="copy" style="width:16px;height:16px;"></i> Copiar
                        </button>
                    </div>
                    <small class="text-muted">Usa este valor como <code>Authorization: Bearer &lt;token&gt;</code> en el agente Python.</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if(session('success') && !session('token_plain'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row">
    {{-- Crear nuevo token --}}
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0 text-white">
                    <i data-feather="plus-circle" style="width:18px;height:18px;"></i> Nuevo Token
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('asistencia-api.tokens.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre descriptivo</label>
                        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                               placeholder="Ej: Agente Lima Norte Producción"
                               value="{{ old('nombre') }}" required>
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Centro MAC</label>
                        <select name="id_mac" class="form-select @error('id_mac') is-invalid @enderror" required>
                            <option value="">Seleccionar...</option>
                            @foreach($macs as $mac)
                                <option value="{{ $mac->IDCENTRO_MAC }}"
                                    {{ old('id_mac') == $mac->IDCENTRO_MAC ? 'selected' : '' }}
                                    {{ $mac->IDCENTRO_MAC == 13 ? 'selected' : '' }}>
                                    {{ $mac->NOMBRE_MAC }} (ID: {{ $mac->IDCENTRO_MAC }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_mac')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i data-feather="key" style="width:16px;height:16px;"></i> Generar Token
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Info del endpoint --}}
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">Endpoints disponibles</h6>
            </div>
            <div class="card-body p-3">
                <table class="table table-sm table-borderless mb-0" style="font-size:0.82rem;">
                    <tbody>
                        <tr>
                            <td><span class="badge bg-success">GET</span></td>
                            <td class="font-monospace">/api/asistencia/ping</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-primary">POST</span></td>
                            <td class="font-monospace">/api/asistencia/sync</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-success">GET</span></td>
                            <td class="font-monospace">/api/asistencia/ultimo-item</td>
                        </tr>
                    </tbody>
                </table>
                <hr class="my-2">
                <small class="text-muted">
                    Header requerido:<br>
                    <code>Authorization: Bearer &lt;token&gt;</code>
                </small>
            </div>
        </div>
    </div>

    {{-- Tabla de tokens --}}
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Tokens registrados</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tbl-tokens" class="table table-hover table-sm align-middle dt-responsive" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>MAC</th>
                                <th>Estado</th>
                                <th>Último uso</th>
                                <th>Creado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tokens as $token)
                            <tr>
                                <td>{{ $token->id }}</td>
                                <td>{{ $token->nombre }}</td>
                                <td>
                                    <span class="badge bg-secondary">id_mac={{ $token->id_mac }}</span>
                                </td>
                                <td>
                                    @if($token->activo)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <small>
                                        {{ $token->ultimo_uso ? $token->ultimo_uso->format('d/m/Y H:i') : '—' }}
                                    </small>
                                </td>
                                <td>
                                    <small>{{ $token->created_at->format('d/m/Y H:i') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <form action="{{ route('asistencia-api.tokens.toggle', $token->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $token->activo ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                    title="{{ $token->activo ? 'Desactivar' : 'Activar' }}">
                                                <i data-feather="{{ $token->activo ? 'pause' : 'play' }}" style="width:14px;height:14px;"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('asistencia-api.tokens.destroy', $token->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('¿Eliminar el token \"{{ $token->nombre }}\"? Esta acción no se puede deshacer.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                <i data-feather="trash-2" style="width:14px;height:14px;"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">
                    {{ $tokens->links() }}
                </div>
            </div>
        </div>

        {{-- Link a logs --}}
        <div class="text-end">
            <a href="{{ route('asistencia-api.logs.index') }}" class="btn btn-outline-secondary btn-sm">
                <i data-feather="list" style="width:14px;height:14px;"></i> Ver historial de sincronizaciones
            </a>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{ asset('nuevo/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.js') }}"></script>
<script>
$(function () {
    $('#tbl-tokens').DataTable({
        paging: false,
        info: false,
        searching: false,
        order: [[0, 'desc']],
        columnDefs: [{ targets: [6], orderable: false }],
        language: { emptyTable: 'No hay tokens registrados.' }
    });

    feather.replace();
});

function copyToken() {
    var el = document.getElementById('plain-token');
    el.select();
    el.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(el.value).then(function () {
        alert('Token copiado al portapapeles.');
    }).catch(function () {
        document.execCommand('copy');
        alert('Token copiado al portapapeles.');
    });
}
</script>
@endsection
