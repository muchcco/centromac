<table>
    <tr>
        <td></td>
    </tr>
</table>

<table>
    <tr>                        
        <th style="border: 1px solid black" rowspan="3" colspan="3"></th>
        <th style="border: 1px solid black" colspan="28" rowspan="2">
            REPORTE CONSOLIDADO DE PUNTUALIDAD DE OCUPABILIDAD DE LOS MÓDULOS DE LAS ENTIDADES PARTICIPANTES POR MES<br />
            Período evaluado Enero a diciembre {{ $fecha_año }}
        </th>
        <th style="border: 1px solid black"> Código</th>
        <th style="border: 1px solid black" colspan="2">ANS2</th>
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
        <td colspan="19" style="text-align: start !important; border: 1px solid #2F75B5;">Módulo ocupado 15 minutos antes del inicio de atención al público del Centro MAC.</td> 
    </tr>
    <tr>
        <td style="color: white; border: 1px solid #2F75B5; background: #2F75B5; text-align: center;">NO</td>
        <td colspan="19" style="text-align: start !important;border: 1px solid #2F75B5;">Módulo que no estuvo ocupado 15 minutos antes del inicio de atención al público del Centro MAC.</td>      
    </tr>
</table>

<table class="table table-bor" style="border: 1px solid black">
    <thead style="background: #3D61B2; color:#fff;">
        <tr style="border: 1px solid black; color: #fff;">
            <th style="color: white; border: 1px solid black; background-color: #0B22B4;">MODULOS</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4;">NOMBRE DE LAS ENTIDADES</th>
            @for ($i = 1; $i <= 31; $i++) {{-- Generar dinámicamente los días del mes --}}
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
                @for ($i = 1; $i <= 31; $i++)
                    @php
                        $fechaActual = Carbon\Carbon::create($fecha_año, $fecha_mes, $i)->format('Y-m-d');
                        $fechaInicio = Carbon\Carbon::parse($q->FECHAINICIO)->format('Y-m-d');
                        $fechaFin = Carbon\Carbon::parse($q->FECHAFIN)->format('Y-m-d');
                        $esDomingo = Carbon\Carbon::create($fecha_año, $fecha_mes, $i)->isSunday();
                        $esFeriado = in_array($fechaActual, $feriados);
                    @endphp

                    @if ($esDomingo || $esFeriado)
                        {{-- Si es domingo o feriado, celda en blanco con estilo de fondo --}}
                        <td style="border: 1px solid #ffffff; min-width: 28px; background:#323232;">&nbsp;</td>
                    @elseif ($fechaActual < $fechaInicio || $fechaActual > $fechaFin)
                        {{-- Si la fecha está fuera del rango, dejar en blanco --}}
                        <td style="border: 1px solid #2F75B5; min-width: 28px;">&nbsp; &nbsp;</td>
                    @else
                        {{-- Mostrar SI, NO o en blanco dependiendo de la hora de marcación --}}
                        <td style="border: 1px solid #2F75B5; min-width: 28px; {{ isset($q->{'DIA_' . $i}) && $q->{'DIA_' . $i} < '08:16:00' ? 'color: black !important; background: none;' : (isset($q->{'DIA_' . $i}) ? 'color: white; background: #2F75B5;' : 'background:#323232;') }}">
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
                <td style="border: 1px solid #2F75B5"></td>
            </tr>
        @empty
            <tr>
                <td colspan="34" class="text-center" style="border: 1px solid black">No hay datos disponibles</td>
            </tr>
        @endforelse
    </tbody>
</table>
