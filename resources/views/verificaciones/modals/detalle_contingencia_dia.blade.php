<div class="row mb-3">
    <div class="col-md-6">
        <strong>Centro MAC:</strong><br>
        {{ $name_mac }}
    </div>

    <div class="col-md-6">
        <strong>Fecha:</strong><br>
        {{ $fecha->format('d/m/Y') }}
    </div>
</div>

@php
$apertura = $registros->firstWhere('AperturaCierre', 0);
$relevo = $registros->firstWhere('AperturaCierre', 1);
$cierre = $registros->firstWhere('AperturaCierre', 2);

$observacionApertura = $apertura->Observaciones ?? '';
$observacionRelevo = $relevo->Observaciones ?? '';
$observacionCierre = $cierre->Observaciones ?? '';

$formatearObservacion = function ($texto) {
$textoSeguro = e($texto);

return preg_replace(
'/(-\s*Observación de[^:]*:)/iu',
'<strong>$1</strong>',
nl2br($textoSeguro)
);
};
@endphp

@if (!$apertura && !$relevo && !$cierre)
<div class="alert alert-warning mb-0">
    No existen verificaciones registradas para este día.
</div>
@else

<div class="table-responsive">
    <table class="table table-bordered table-sm mb-3">
        <thead>
            <tr>
                <th style="width: 55%;">Campo</th>
                <th style="width: 15%;" class="bg-primary">Apertura</th>

                @if ($relevo)
                <th style="width: 15%;" class="bg-warning text-dark">Relevo</th>
                @endif

                <th style="width: 15%;" class="bg-dark">Cierre</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($campos as $campo)
            @php
            $nombreCampo = preg_replace('/([a-z])([A-Z])/', '$1 $2', $campo);

            $valorApertura = $apertura ? (int) ($apertura->$campo ?? 0) : null;
            $valorRelevo = $relevo ? (int) ($relevo->$campo ?? 0) : null;
            $valorCierre = $cierre ? (int) ($cierre->$campo ?? 0) : null;
            @endphp

            <tr>
                <td class="text-start">
                    {{ $nombreCampo }}
                </td>

                <td class="text-center">
                    @if (is_null($valorApertura))
                    <span class="text-muted">-</span>
                    @elseif ($valorApertura === 1)
                    <span class="badge bg-success">Correcto</span>
                    @else
                    <span class="badge bg-danger">Observado</span>
                    @endif
                </td>

                @if ($relevo)
                <td class="text-center">
                    @if (is_null($valorRelevo))
                    <span class="text-muted">-</span>
                    @elseif ($valorRelevo === 1)
                    <span class="badge bg-success">Correcto</span>
                    @else
                    <span class="badge bg-danger">Observado</span>
                    @endif
                </td>
                @endif

                <td class="text-center">
                    @if (is_null($valorCierre))
                    <span class="text-muted">-</span>
                    @elseif ($valorCierre === 1)
                    <span class="badge bg-success">Correcto</span>
                    @else
                    <span class="badge bg-danger">Observado</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="row">

    <div class="col-md-{{ $relevo ? '4' : '6' }}">
        <div class="card h-100 border-primary">
            <div class="card-header bg-primary text-white">
                <strong>Apertura</strong>

                @if ($apertura && $apertura->created_at)
                <span class="ms-2">
                    - {{ \Carbon\Carbon::parse($apertura->created_at)->format('h:i A') }}
                </span>
                @endif

                @if ($apertura && $apertura->user)
                <span class="float-end">
                    {{ $apertura->user->name }}
                </span>
                @endif
            </div>

            <div class="card-body">
                <strong>Observaciones:</strong>

                @if (trim($observacionApertura) !== '')
                <div class="mt-2 p-2 bg-light border rounded">
                    {!! $formatearObservacion($observacionApertura) !!}
                </div>
                @else
                <div class="text-muted mt-2">
                    No se registró observación.
                </div>
                @endif
            </div>
        </div>
    </div>

    @if ($relevo)
    <div class="col-md-4">
        <div class="card h-100 border-warning">
            <div class="card-header bg-warning text-dark">
                <strong>Relevo</strong>

                @if ($relevo && $relevo->created_at)
                <span class="ms-2">
                    - {{ \Carbon\Carbon::parse($relevo->created_at)->format('h:i A') }}
                </span>
                @endif

                @if ($relevo && $relevo->user)
                <span class="float-end">
                    {{ $relevo->user->name }}
                </span>
                @endif
            </div>

            <div class="card-body">
                <strong>Observaciones:</strong>

                @if (trim($observacionRelevo) !== '')
                <div class="mt-2 p-2 bg-light border rounded">
                    {!! $formatearObservacion($observacionRelevo) !!}
                </div>
                @else
                <div class="text-muted mt-2">
                    No se registró observación.
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <div class="col-md-{{ $relevo ? '4' : '6' }}">
        <div class="card h-100 border-dark">
            <div class="card-header bg-dark text-white">
                <strong>Cierre</strong>

                @if ($cierre && $cierre->created_at)
                <span class="ms-2">
                    - {{ \Carbon\Carbon::parse($cierre->created_at)->format('h:i A') }}
                </span>
                @endif

                @if ($cierre && $cierre->user)
                <span class="float-end">
                    {{ $cierre->user->name }}
                </span>
                @endif
            </div>

            <div class="card-body">
                <strong>Observaciones:</strong>

                @if (trim($observacionCierre) !== '')
                <div class="mt-2 p-2 bg-light border rounded">
                    {!! $formatearObservacion($observacionCierre) !!}
                </div>
                @else
                <div class="text-muted mt-2">
                    No se registró observación.
                </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endif