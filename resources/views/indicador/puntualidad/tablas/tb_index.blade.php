<table class="table table-hover table-bordered table-striped" id="table_formato">
    <tbody>
        <tr>
            <td style="border: 1px solid black" rowspan="3" colspan="2"><img
                    src="{{ asset('imagen/mac_logo_export.jpg') }}" alt="" width="230px"></td>
            <td style="border: 1px solid black" colspan="8" rowspan="2">
                REPORTE CONSOLIDADO DE PUNTUALIDAD DE OCUPABILIDAD DE LOS MÓDULOS DE LAS ENTIDADES PARTICIPANTES POR
                MES<br />
                <span class="text-danger text-center">Período evaluado Enero a diciembre {{ $fecha_año }}</span>
            </td>
            <td style="border: 1px solid black"> Código</td>
            <td style="border: 1px solid black" colspan="2">ANS2</td>
        </tr>
        <tr>
            <td style="border: 1px solid black">Versión</td>
            <td style="border: 1px solid black" colspan="2">1.0.0</td>
        </tr>
        <tr>
            <td style="border: 1px solid black">Centro MAC</td>
            <td style="border: 1px solid black">{{ $name_mac }}</td>
            <td style="border: 1px solid black" colspan="2">MES:</td>
            <td style="border: 1px solid black" colspan="7">
                {{ $nombre_mes }}
            </td>
        </tr>
    </tbody>
</table>

<table class="table table-hover table-bordered table-striped" id="table_formato">
    <thead class="tenca">
        <tr>
            <th>MODULOS</th>
            <th>NOMBRE DE LAS ENTIDADES</th>
            @for ($i = 1; $i <= 31; $i++)
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
                @for ($i = 1; $i <= 31; $i++)
                @php
                    // Crear fecha del día actual en el bucle
                    $fechaActual = Carbon\Carbon::create($fecha_año, $fecha_mes, $i)->format('Y-m-d');
                    $fechaInicio = Carbon\Carbon::parse($q->FECHAINICIO)->format('Y-m-d');
                    $fechaFin = Carbon\Carbon::parse($q->FECHAFIN)->format('Y-m-d');
                    // Determinar si es domingo
                    $esDomingo = Carbon\Carbon::create($fecha_año, $fecha_mes, $i)->isSunday();
                    // Verificar si el día es feriado
                    $esFeriado = in_array($fechaActual, $feriados);
                @endphp
            
                @if ($esDomingo || $esFeriado)
                    {{-- Si es domingo o feriado, dejar en blanco y aplicar el estilo de fondo --}}
                    <td style="border: 1px solid #fffff; min-width: 28px; background-color: rgba(50,50,50,.8);">&nbsp;</td>
                @elseif ($fechaActual < $fechaInicio || $fechaActual > $fechaFin)
                    {{-- Si la fecha está fuera del rango, dejar en blanco --}}
                    <td style="border: 1px solid #fffff; min-width: 28px;">&nbsp; &nbsp;</td>
                @else
                    {{-- Mostrar SI, NO o en blanco dependiendo de la hora de marcación --}}
                    <td style="border: 1px solid #fffff; min-width: 28px; {{ isset($q->{'DIA_' . $i}) && $q->{'DIA_' . $i} < '08:16:00' ? 'color: black !important; background: none;' : (isset($q->{'DIA_' . $i}) ? 'color: white; background: #2F75B5;' : 'background-color: rgba(50,50,50,.8);') }}">
                        @if (isset($q->{'DIA_' . $i}) && $q->{'DIA_' . $i} < '08:16:00')
                            <span class="text-center">SI</span>
                        @elseif (isset($q->{'DIA_' . $i}) && $q->{'DIA_' . $i} >= '08:16:00')
                            <span class="text-center">NO</span>
                        @else
                            &nbsp; <!-- Dejar en blanco si no hay marcación con el estilo aplicado -->
                        @endif
                    </td>
                @endif
            @endfor
            
                <td style="border: 1px solid #ffffff;"></td>
            </tr>
        @empty
            <tr>
                <td colspan="34" class="text-center" style="border: 1px solid black">No hay datos disponibles</td>
            </tr>
        @endforelse
    </tbody>
</table>
