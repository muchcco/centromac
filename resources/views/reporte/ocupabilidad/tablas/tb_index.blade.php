@php use Carbon\Carbon; @endphp

<table class="table table-hover table-bordered table-striped" id="table_formato2">
    <thead class="tenca">
        <tr>
            <th>MÓDULO</th>
            <th>CENTRO MAC</th>
            <th>ENTIDAD</th>
            <th>DÍAS MARCADOS</th>
            <th>DÍAS HÁBILES</th>
            <th>PORCENTAJE</th>
            <th>OCUPABILIDAD</th>
        </tr>
    </thead>
    <tbody>
        @forelse($modulos as $modulo)
            @php
                $modId = $modulo->idmodulo;
                $macId = $modulo->idmac;
                $habiles = $diasHabilesPorModulo[$macId][$modId] ?? 0;
                $contadorSi = 0;

                for ($d = 1; $d <= $numeroDias; $d++) {
                    $f = Carbon::create($fecha_año, $fecha_mes, $d)->format('Y-m-d');
                    $esDom = Carbon::parse($f)->isSunday();
                    $esFer = in_array($f, $feriadosPorMac[$macId] ?? [], true);
                    $activo = $f >= $modulo->fechainicio && $f <= $modulo->fechafin;

                    // Sólo si NO es domingo, NO es feriado y está dentro del rango
                    if (!$esDom && !$esFer && $activo && isset($dias[$d][$macId][$modId]['hora_minima'])) {
                        $contadorSi++;
                    }
                }

                $pct = $habiles > 0 ? round(($contadorSi / $habiles) * 100, 2) : 0;
                $barClass = $pct >= 95 ? 'bg-success' : ($pct >= 84 ? 'bg-warning' : 'bg-danger');
            @endphp

            <tr>
                <td>{{ $modulo->n_modulo }}</td>
                <td>{{ $modulo->nombre_mac }}</td>
                <td>{{ $modulo->nombre_entidad }}</td>
                <td>{{ $contadorSi }}</td>
                <td>{{ $habiles }}</td>
                <td>{{ $pct }}%</td>
                <td>
                    <div class="progress" style="height:25px">
                        <div class="progress-bar {{ $barClass }}" role="progressbar"
                            style="width:{{ $pct }}%;" aria-valuenow="{{ $pct }}" aria-valuemin="0"
                            aria-valuemax="100">
                            {{ $pct }}%
                        </div>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No hay datos disponibles</td>
            </tr>
        @endforelse
    </tbody>
</table>
