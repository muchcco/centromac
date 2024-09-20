<table>
    <tr>
        <td></td>
    </tr>
</table>

<table>
    <tr>                        
        <th style="border: 1px solid black" rowspan="3" colspan="3"></th>
        <th style="border: 1px solid black" colspan="28" rowspan="2">
            REPORTE CONSOLIDADO DE OCUPABILIDAD DE LOS MÓDULOS DE LAS ENTIDADES PARTICIPANTES POR MES<br />
            Período evaluado Enero a diciembre {{ $fecha_año }}
        </th>
        <th style="border: 1px solid black"> Código</th>
        <th style="border: 1px solid black" colspan="2">ANS1</th>
    </tr>
    <tr>
        <th style="border: 1px solid black">Versión</th>
        <th style="border: 1px solid black" colspan="2">1.0.0</th>
    </tr>
    <tr>
        <th style="border: 1px solid black" colspan="2">Centro MAC</th>
        <th style="border: 1px solid rgb(0, 0, 0)" colspan="15">{{ $name_mac }} </th>
        <th style="border: 1px solid black" colspan="2">MES:</th>
        <th style="border: 1px solid black" colspan="12">{{ $nombre_mes }}</th>        
    </tr>
</table>

<table>
    <tr>
        <td rowspan="2" colspan="2" style="text-align: end; border: none;">Leyenda</td>
        <td style="border: 1px solid #2F75B5; text-align: center;">SI</td>
        <td colspan="19" style="text-align: start !important; border: 1px solid #2F75B5;">Módulo con presencia de asesor(a) de servicio de la entidad participante</td> 
    </tr>
    <tr>
        <td style="color: white; border: 1px solid #2F75B5; background: #2F75B5; text-align: center;">NO</td>
        <td colspan="19" style="text-align: start !important;border: 1px solid #2F75B5;">Módulo que estuvo sin presencia de asesor(a) de servicio, durante un día de operación del Centro MAC</td>      
    </tr>
</table>

<table class="table table-bor" style="border: 1px solid black">
    <thead style="background: #3D61B2; color:#fff;">
        <tr style="border: 1px solid black; color: #fff;">
            <th style="color: white; border: 1px solid black; background-color: #0B22B4;">MODULOS</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4;">NOMBRE DE LAS ENTIDADES</th>
            @for ($i = 1; $i <= $daysInMonth; $i++) {{-- Generar dinámicamente según el número de días del mes --}}
                <th style="color: white; border: 1px solid black; background-color: #0B22B4;">{{ $i }}</th>
            @endfor
            <th style="color: white; border: 1px solid black; background-color: #0B22B4;">OBSERVACIONES O COMENTARIOS</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($query as $q)
            <tr>
                <td style="border: 1px solid #2F75B5">{{ $q->N_MODULO }}</td>
                <td style="border: 1px solid #2F75B5">{{ $q->NOMBRE_ENTIDAD }}</td>
                @for ($i = 1; $i <= $daysInMonth; $i++) 
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
                        <td style="border: 1px solid #FFFFFF;min-width: 28px; background: #323232;">&nbsp;</td>
                    @elseif ($fechaActual < $fechaInicio || $fechaActual > $fechaFin)
                        {{-- Si la fecha actual está fuera del rango de FECHAINICIO y FECHAFIN, mostrar espacio en blanco --}}
                        <td style="border: 1px solid #2F75B5">&nbsp; &nbsp;</td> <!-- Espacio en blanco -->
                    @else
                        {{-- Mostrar SI o NO según si hubo asistencia en ese día --}}
                        <td style="border: 1px solid #2F75B5; min-width: 28px; {{ $q->{'DIA_' . $i} > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                            @if ($q->{'DIA_' . $i} > 0)
                                <span class="text-center">SI</span>
                            @else
                                <span class="text-center">NO</span>
                            @endif
                        </td>
                    @endif
                @endfor
                <td style="border: 1px solid #2F75B5"></td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ $daysInMonth + 3 }}" class="text-center" style="border: 1px solid black">No hay datos disponibles</td>
            </tr>
        @endforelse
    </tbody>
</table>
