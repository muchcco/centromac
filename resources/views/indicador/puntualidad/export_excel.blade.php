<table>
    <tr>
        <td></td>
    </tr>
</table>

<table>
    <tr>
        <th style="border: 1px solid black" rowspan="3" colspan="3"></th>
        <th style="border: 1px solid black" colspan="28" rowspan="2">
            REPORTE CONSOLIDADO DE PUNTUALIDAD DE OCUPABILIDAD DE LOS MÓDULOS DE LAS ENTIDADES PARTICIPANTES POR
            MES<br />
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
        <th style="border: 1px solid rgb(0, 0, 0)" colspan="15">{{ $nombreMac }} </th>
        <th style="border: 1px solid black" colspan="2">MES:</th>
        <th style="border: 1px solid black" colspan="12">{{ $mesNombre }}</th>
    </tr>
</table>

<table>
    <tr>
        <td rowspan="2" colspan="2" style="text-align: end; border: none;">Leyenda</td>
        <td style="border: 1px solid #2F75B5; text-align: center;">SI</td>
        <td colspan="19" style="text-align: start !important; border: 1px solid #2F75B5;">Módulo ocupado 15 minutos
            antes del inicio de atención al público del Centro MAC.</td>
    </tr>
    <tr>
        <td style="color: white; border: 1px solid #2F75B5; background: #2F75B5; text-align: center;">NO</td>
        <td colspan="19" style="text-align: start !important;border: 1px solid #2F75B5;">Módulo que no estuvo ocupado
            15 minutos antes del inicio de atención al público del Centro MAC.</td>
    </tr>
</table>

<table class="table table-bor" style="border: 1px solid black">
    <thead style="background: #3D61B2; color:#fff;">
        <tr style="border: 1px solid black; color: #fff;">
            <th style="color: white; border: 1px solid black; background-color: #0B22B4;">MODULOS</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4;">NOMBRE DE LAS ENTIDADES</th>
            @for ($i = 1; $i <= $numeroDias; $i++)
                <th style="color: white; border: 1px solid black; background-color: #0B22B4;">{{ $i }}</th>
            @endfor
            <th style="color: white; border: 1px solid black; background-color: #0B22B4;">OBSERVACIONES O COMENTARIOS
            </th>
        </tr>
    </thead>

    <tbody>
        @forelse ($modulos as $modulo)
            <tr>
                <td style="border: 1px solid #2F75B5">{{ $modulo->n_modulo }}</td>
                <td style="border: 1px solid #2F75B5">{{ $modulo->nombre_entidad }}</td>
                @for ($i = 1; $i <= $numeroDias; $i++)
                    @php
                        $fechaActual = Carbon\Carbon::create($fecha_año, $fecha_mes, $i)->format('Y-m-d');
                        $esDomingo = Carbon\Carbon::create($fecha_año, $fecha_mes, $i)->isSunday();
                        $esFeriado = in_array($fechaActual, $feriados);
                        $esActivo = $fechaActual >= $modulo->fechainicio && $fechaActual <= $modulo->fechafin;
                    @endphp

                    @if ($esDomingo || $esFeriado)
                        <td style="border: 1px solid #ffffff; min-width: 28px; background:#323232;">&nbsp;</td>
                    @elseif ($esActivo)
                        <td
                            style="min-width: 28px; 
                            @if (isset($dias[$i][$modulo->idmodulo]['hora_minima'])) @php
                                    $horaMinima = $dias[$i][$modulo->idmodulo]['hora_minima'];
                                    $horaLimite = '08:16';
                                    $horaRegistro = new DateTime($horaMinima);
                                    $horaLimiteObj = new DateTime($horaLimite);
                                    $esTarde = $horaRegistro >= $horaLimiteObj; // Cambié la comparación para incluir 08:16
                            @endphp
                                @if (!$esTarde) 
                                    background: #FFFFFF; color: #000; /* SI */
                                @else
                                    background: #2F75B5; color: #fff; /* NO */ @endif
@else
background: #474747; color: #fff; /* - */
                            @endif">
                            @if (isset($dias[$i][$modulo->idmodulo]['hora_minima']))
                                @php
                                    $horaMinima = $dias[$i][$modulo->idmodulo]['hora_minima'];
                                    $esTarde = new DateTime($horaMinima) >= new DateTime('08:16');
                                @endphp
                                <span class="text-center">{{ !$esTarde ? 'SI' : 'NO' }}</span>
                            @else
                                <span class="text-center">-</span> <!-- Si no hay registro y está activo -->
                            @endif
                        </td>
                    @else
                        <td style="border: 1px solid #2F75B5"></td> <!-- Celda vacía fuera del rango activo -->
                    @endif
                @endfor
                <td style="border: 1px solid #2F75B5"></td> <!-- Columna de observaciones vacía -->
            </tr>
        @empty
            <tr>
                <td colspan="{{ $numeroDias + 3 }}" class="text-center">No hay datos disponibles</td>
            </tr>
        @endforelse

    </tbody>
</table>
