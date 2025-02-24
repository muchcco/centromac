<table class="table table-hover table-bordered table-striped" id="table_formato">
    <thead class="tenca">
        <tr>
            <th>MODULOS</th>
            <th>ENTIDAD</th>
            <th>DIAS MARCADOS</th>
            <th>DIAS HABILES</th>
            <th>PORCENTAJE</th>
            <th>OCUPABILIDAD</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($modulos as $modulo)
            <tr>
                <td>{{ $modulo->n_modulo }}</td>
                <td>{{ $modulo->nombre_entidad }} - -{{ $nombreMac }}</td>
                @php
                    $contadorSi = 0;
                    for ($i = 1; $i <= $numeroDias; $i++) {
                        $fechaActual = Carbon\Carbon::create($fecha_año, $fecha_mes, $i)->format('Y-m-d');
                        $esDomingo = Carbon\Carbon::create($fecha_año, $fecha_mes, $i)->isSunday();
                        $esFeriado = in_array($fechaActual, $feriados);

                        $mostrarSi = isset($dias[$i][$modulo->idmodulo]) && $dias[$i][$modulo->idmodulo]['hora_minima'];
                        if ($mostrarSi) {
                            $contadorSi++;
                        }
                    }
                    $porcentaje = $diasHabiles > 0 ? ($contadorSi / $diasHabiles) * 100 : 0;
                    $barClass = $porcentaje >= 95 ? 'bg-success' : ($porcentaje >= 84 ? 'bg-warning' : 'bg-danger');
                @endphp
                <td>{{ $contadorSi }}</td>
                <td>{{ $diasHabiles }}</td>
                <td>{{ number_format($porcentaje, 2) }}%</td>
                <td>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar {{ $barClass }}" role="progressbar"
                            style="width: {{ $porcentaje }}%" aria-valuenow="{{ $porcentaje }}" aria-valuemin="0"
                            aria-valuemax="100">{{ number_format($porcentaje, 2) }}%</div>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center">No hay datos disponibles</td>
            </tr>
        @endforelse
    </tbody>
</table>
