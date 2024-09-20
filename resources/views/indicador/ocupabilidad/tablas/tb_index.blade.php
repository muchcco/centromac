<table class="table table-hover table-bordered table-striped" id="table_formato">
    <thead class="tenca">
        <tr>
            <th>MODULOS</th>
            <th>NOMBRE DE LAS ENTIDADES</th>
            @for ($i = 1; $i <= $daysInMonth; $i++) {{-- Generar dinámicamente según el número de días del mes --}}
                <th>{{ $i }}</th>
            @endfor
            <th>OBSERVACIONES O COMENTARIOS</th>
        </tr>
    </thead>
    
    <tbody>
        @forelse ($query as $q)
            <tr>
                <td>{{ $q->N_MODULO }}</td>
                <td>{{ $q->NOMBRE_ENTIDAD }}</td>
                @for ($i = 1; $i <= $daysInMonth; $i++) {{-- Generar dinámicamente según el número de días del mes --}}
                    @php
                        // Crear fecha del día actual en el bucle
                        $fechaActual = Carbon\Carbon::create($fecha_año, $fecha_mes, $i)->format('Y-m-d');
                        $fechaInicio = Carbon\Carbon::parse($q->FECHAINICIO)->format('Y-m-d');
                        $fechaFin = Carbon\Carbon::parse($q->FECHAFIN)->format('Y-m-d');
                        // Determinar si es domingo (Carbon::dayOfWeek devuelve 0 si es domingo)
                        $esDomingo = Carbon\Carbon::create($fecha_año, $fecha_mes, $i)->isSunday();
                        // Verificar si el día es feriado
                        $esFeriado = in_array($fechaActual, $feriados);
                    @endphp

                    @if ($esDomingo || $esFeriado)
                        {{-- Si es domingo o feriado, dejar en blanco y aplicar el estilo de fondo --}}
                        <td style="min-width: 28px; background-color: rgba(50,50,50,.8);">&nbsp;</td>
                    @elseif ($fechaActual < $fechaInicio || $fechaActual > $fechaFin)
                        {{-- Si la fecha actual está fuera del rango de FECHAINICIO y FECHAFIN, mostrar espacio en blanco --}}
                        <td>&nbsp; &nbsp;</td> <!-- Espacio en blanco -->
                    @else
                        {{-- Mostrar SI o NO según si hubo asistencia en ese día --}}
                        <td style="min-width: 28px; {{ $q->{'DIA_' . $i} > 0 ? 'color: black !important; background: none' : 'background: #2F75B5; color: white !important' }}">
                            @if ($q->{'DIA_' . $i} > 0)
                                <span class="text-center">SI</span>
                            @else
                                <span class="text-center">NO</span>
                            @endif
                        </td>
                    @endif
                @endfor
                <td></td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ $daysInMonth + 3 }}" class="text-center">No hay datos disponibles</td>
            </tr>
        @endforelse
    </tbody>
</table>
