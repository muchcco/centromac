@php use Carbon\Carbon; @endphp

<table class="table table-hover table-bordered table-striped" id="table_formato2">
    <thead class="tenca">
        <tr>
            <th>MAC</th>
            <th>MÓDULO</th>
            <th>ENTIDAD</th>
            <th>DÍAS HÁBILES</th>
            <th>OCUPADOS</th>
            <th>PORCENTAJE</th>
            <th>OCUPABILIDAD</th>
        </tr>
    </thead>
    <tbody>
        {{-- Agrupamos módulos por MAC --}}
        @forelse($modulos->groupBy('idmac') as $macId => $group)
            @php $macName = $group->first()->nombre_mac; @endphp

            {{-- Fila de encabezado por MAC --}}
            <tr class="table-secondary">
                <td colspan="7" class="fw-bold">{{ $macName }}</td>
            </tr>

            {{-- Filas de cada módulo para este MAC --}}
            @foreach ($group as $modulo)
                @php
                    $modId = $modulo->idmodulo;
                    $habiles = $diasHabilesPorModulo[$macId][$modId] ?? 0;
                    $ocupados = 0;

                    for ($d = 1; $d <= $numeroDias; $d++) {
                        $fecha = sprintf('%04d-%02d-%02d', $fecha_año, $fecha_mes, $d);
                        $esDom = Carbon::parse($fecha)->isSunday();
                        $esFer = in_array($fecha, $feriadosPorMac[$macId] ?? []);
                        $activo = $fecha >= $modulo->fechainicio && $fecha <= $modulo->fechafin;

                        // Sólo contamos si NO es domingo, NO es feriado y está activo
                        if (!$esDom && !$esFer && $activo && isset($dias[$d][$macId][$modId]['hora_minima'])) {
                            $ocupados++;
                        }
                    }

                    $pct = $habiles > 0 ? round(($ocupados / $habiles) * 100, 2) : 0;
                    $barClass = $pct >= 95 ? 'bg-success' : ($pct >= 84 ? 'bg-warning' : 'bg-danger');
                @endphp

                <tr>
                    <td></td>
                    <td>{{ $modulo->n_modulo }}</td>
                    <td>{{ $modulo->nombre_entidad }}</td>
                    <td>{{ $habiles }}</td>
                    <td>{{ $ocupados }}</td>
                    <td>{{ $pct }}%</td>
                    <td>
                        <div class="progress" style="height:25px">
                            <div class="progress-bar {{ $barClass }}" role="progressbar"
                                style="width:{{ $pct }}%;" aria-valuenow="{{ $pct }}"
                                aria-valuemin="0" aria-valuemax="100">
                                {{ $pct }}%
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        @empty
            <tr>
                <td colspan="7" class="text-center">No hay datos disponibles</td>
            </tr>
        @endforelse
    </tbody>
</table>
