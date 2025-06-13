@php
    use Carbon\Carbon;
@endphp

<table class="table table-hover table-bordered table-striped" id="table_formato2">
    <thead class="tenca">
        <tr>
            <th>MÓDULO</th>
            <th>ENTIDAD / MAC</th>
            <th>DÍAS MARCADOS</th>
            <th>DÍAS HÁBILES</th>
            <th>PORCENTAJE</th>
            <th>OCUPABILIDAD</th>
        </tr>
    </thead>

    <tbody>
        @forelse ($modulos as $modulo)
            @php
                /* ─── Días hábiles de ESTE módulo (viene del SP) ─── */
                $habilesModulo = $spHabiles[$modulo->idmodulo]->DIAS_HABILES ?? 0;

                /* ─── Contar días marcados (“SI”) ─── */
                $contadorSi = 0;
                for ($d = 1; $d <= $numeroDias; $d++) {

                    $f = Carbon::create($fecha_año, $fecha_mes, $d)->format('Y-m-d');
                    $esDom  = Carbon::create($fecha_año, $fecha_mes, $d)->isSunday();
                    $esFer  = in_array($f, $feriados);
                    $activo = $f >= $modulo->fechainicio && $f <= $modulo->fechafin;

                    if (!$esDom && !$esFer && $activo) {
                        if (isset($dias[$d][$modulo->idmodulo]) &&
                            $dias[$d][$modulo->idmodulo]['hora_minima']) {
                            $contadorSi++;
                        }
                    }
                }

                /* ─── Porcentaje + barra de color ─── */
                $pct      = $habilesModulo > 0
                            ? round(($contadorSi / $habilesModulo) * 100, 2)
                            : 0;
                $barClass = $pct >= 95 ? 'bg-success'
                           : ($pct >= 84 ? 'bg-warning' : 'bg-danger');
            @endphp

            <tr>
                <td>{{ $modulo->n_modulo }}</td>
                <td>{{ $modulo->nombre_entidad }} — {{ $nombreMac }}</td>

                <td>{{ $contadorSi }}</td>
                <td>{{ $habilesModulo }}</td>
                <td>{{ $pct }}%</td>

                <td>
                    <div class="progress" style="height:25px;">
                        <div class="progress-bar {{ $barClass }}"
                             role="progressbar"
                             style="width:{{ $pct }}%;"
                             aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                             {{ $pct }}%
                        </div>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center">No hay datos disponibles</td></tr>
        @endforelse
    </tbody>
</table>
