<table class="table table-hover table-bordered table-striped" id="table_formato2">
    <thead class="tenca">
        <tr>
            <th>MODULOS</th>
            <th>ENTIDAD</th>
            <th>DIAS PUNTUALES</th>
            <th>DIAS MARCADOS</th>
            <th>PORCENTAJE</th>
            <th>PUNTUALIDAD</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($modulos as $modulo)
            <tr>
                <td>{{ $modulo->n_modulo }}</td>
                <td>{{ $modulo->nombre_entidad }} - {{ $nombreMac }}</td>
                @php 
                    $contadorSi = 0; 
                    $contadorPuntuales = 0; // Para contar los días puntuales
                    $porcentaje = 0;
                @endphp
                @for ($i = 1; $i <= $numeroDias; $i++)
                    @php
                        $fechaActual = Carbon\Carbon::create($fecha_año, $fecha_mes, $i)->format('Y-m-d');
                        $esDomingo = Carbon\Carbon::create($fecha_año, $fecha_mes, $i)->isSunday();
                        $esFeriado = in_array($fechaActual, $feriados);
                        $activo = $fechaActual >= $modulo->fechainicio && $fechaActual <= $modulo->fechafin;
                    @endphp

                    @if ($esDomingo || $esFeriado || !$activo)
                    @else
                        @php
                            $mostrarSi =
                                isset($dias[$i][$modulo->idmodulo]) && $dias[$i][$modulo->idmodulo]['hora_minima'];
                            if ($mostrarSi) {
                                $contadorSi++; // Días Marcados
                            }

                            // Calcular los días puntuales (horas antes de las 08:16)
                            $horaMinima = isset($dias[$i][$modulo->idmodulo]) ? $dias[$i][$modulo->idmodulo]['hora_minima'] : null;
                            if ($horaMinima && $horaMinima < '08:16') {
                                $contadorPuntuales++; // Días Puntuales
                            }

                            // Calcular el porcentaje de puntualidad
                            $porcentaje = $contadorSi > 0 ? ($contadorPuntuales / $contadorSi) * 100 : 0;
                            $barClass = $porcentaje >= 95 ? 'bg-success' : ($porcentaje >= 84 ? 'bg-warning' : 'bg-danger');
                        @endphp
                    @endif
                @endfor

                <!-- Verificar si no hay días marcados -->
                @php
                    if ($contadorSi == 0) {
                        $contadorPuntuales = "-";
                        $porcentaje = "-";
                    }
                @endphp

                <td>{{ $contadorPuntuales }}</td> <!-- Días Puntuales -->
                <td>{{ $contadorSi }}</td> <!-- Días Marcados -->
                <td>{{ is_numeric($porcentaje) ? number_format($porcentaje, 2) . '%' : $porcentaje }}</td> <!-- Porcentaje de Puntualidad -->
                <td>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar {{ $barClass }}" role="progressbar"
                            style="width: {{ $porcentaje !== '-' ? $porcentaje : 0 }}%" aria-valuenow="{{ $porcentaje }}"
                            aria-valuemin="0" aria-valuemax="100">{{ $porcentaje !== '-' ? number_format($porcentaje, 2) . '%' : '-' }}</div>
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
