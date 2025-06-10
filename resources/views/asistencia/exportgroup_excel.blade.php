@if ($identidad == '17')


    @foreach ($datosAgrupados as $grupo)
        <table>
            <tr>
                <th style="border: 1px solid black; background: #D9E1F2;" colspan="7">
                    {{ $grupo['encabezado']->NOMBREU }}
                </th>
            </tr>
            <tr>
                <td style="border: 1px solid black">Día</td>
                <td style="border: 1px solid black">Fecha</td>
                <td style="border: 1px solid black">Ingreso</td>
                <td style="border: 1px solid black">Salida</td>
                <td style="border: 1px solid black">Ingreso programado</td>
                <td style="border: 1px solid black">Salida Programada</td>
                <td style="border: 1px solid black">Observación</td>
            </tr>
            @foreach ($fechasArray as $fecha)
                <tr>
                    <td style="border: 1px solid black; text-align: left !important;">
                        <?php
                        $nombreDia = utf8_encode(strftime('%A', strtotime($fecha)));
                        echo $nombreDia;
                        ?>
                    </td>
                    <td style="border: 1px solid black">
                        {{ date('d/m/Y', strtotime($fecha)) }}
                    </td>
                    @php
                        // Busca el detalle correspondiente a la fecha y al número de documento
                        $detalle = collect($grupo['detalle'])->first(function ($item) use ($fecha) {
                            return $item->FECHA == $fecha;
                        });
                    @endphp

                    <?php setlocale(LC_TIME, 'es_PE', 'es_ES', 'es');
                    $FECHA = utf8_decode(strftime('%A', strtotime($fecha))); ?>
                    @if ($detalle)
                        @php
                            $horaEntrada = $detalle->HORA_1;
                            $timestamp = strtotime($horaEntrada);
                            $nuevaFecha = date('H:i:s', $timestamp + 5 * 60); //  5 minutos representan 300 segundos
                            if ($FECHA == 's?bado') {
                                $confTimestamp = strtotime($hora_1->VALOR);
                            } else {
                                $confTimestamp = strtotime($hora_3->VALOR);
                            }
                            $confTimestamp += 5 * 60; // Aumentar 5 minutos
                            $confNuevaFecha = date('H:i:s', $confTimestamp);
                        @endphp
                        @if ($detalle->HORA_1 > $confNuevaFecha)
                            <td style="border: 1px solid black; color:#ca0606;">
                                {{ $detalle->HORA_1 }}
                            </td>
                        @else
                            <td style="border: 1px solid black">
                                {{ $detalle->HORA_1 }}
                            </td>
                        @endif
                    @else
                        <td style="border: 1px solid black">
                            --
                        </td>
                    @endif

                    @if ($detalle)
                        @if ($FECHA == 's?bado')
                            @if ($detalle->HORA_4 < $hora_5->SUM_SOLO)
                                <td style="border: 1px solid black; color:#ca0606;">
                                    {{ $detalle->HORA_4 }}
                                </td>
                            @else
                                <td style="border: 1px solid black">
                                    {{ $detalle->HORA_4 }}
                                </td>
                            @endif
                        @else
                            @if ($detalle->HORA_4 < $hora_4->SUM_SOLO)
                                <td style="border: 1px solid black;color:#ca0606;">
                                    {{ $detalle->HORA_4 }}
                                </td>
                            @else
                                <td style="border: 1px solid black">
                                    {{ $detalle->HORA_4 }}
                                </td>
                            @endif
                        @endif
                    @else
                        <td style="border: 1px solid black">
                            --
                        </td>
                    @endif
                    @if ($FECHA == 'domingo')
                        <td colspan="2" style="background: #FFFF00; border: 1px solid black">
                            Descanso Semanal
                        </td>
                    @elseif($FECHA == 's?bado' && (!$detalle || (!isset($detalle->hora1) && !isset($detalle->hora2))))
                        <td colspan="2" style="background: #FFFF00; border: 1px solid black">
                            Descanso Semanal
                        </td>
                    @else
                        <td style="border: 1px solid black">
                            @if ($FECHA == 's?bado')
                                {{ $hora_3->VALOR }}
                            @elseif($FECHA == 'domingo')
                                --
                            @else
                                {{ $hora_1->VALOR }}
                            @endif
                        </td>
                        <td style="border: 1px solid black">
                            @if ($FECHA == 's?bado')
                                {{ $hora_5->VALOR }}
                            @elseif($FECHA == 'domingo')
                                --
                            @else
                                {{ $hora_2->VALOR }}
                            @endif
                        </td>
                    @endif

                    <td style="border: 1px solid black">
                        <!-- Agrega aquí la lógica para obtener la Observación -->
                    </td>
                </tr>
            @endforeach
        </table>
    @endforeach
@else
    <table>
        <tr>
            <th style="border: 1px solid black" rowspan="3" colspan="2"></th>
            <th style="border: 1px solid black" colspan="8" rowspan="2">REPORTE DE ASISTENCIA</th>
            <th style="border: 1px solid black"> Código</th>
            <th style="border: 1px solid black" colspan="2">ANS2</th>
        </tr>
        <tr>
            <th style="border: 1px solid black">Versión</th>
            <th style="border: 1px solid black" colspan="2">1.0.0</th>
        </tr>
        <tr>
            <th style="border: 1px solid black">Centro MAC</th>
            <th style="border: 1px solid black">{{ $name_mac }} </th>
            @if ($tipo_desc == '1')
                <th style="border: 1px solid black" colspan="2">MES:</th>
                <th style="border: 1px solid black" colspan="7">{{ $nombreMES }}</th>
            @elseif($tipo_desc == '2')
                <th style="border: 1px solid black" colspan="2">FECHA:</th>
                <th style="border: 1px solid black" colspan="7"> De: {{ $fecha_inicial }} Hasta:
                    {{ $fecha_fin }}</th>
            @endif

        </tr>
    </table>



    <table class="table table-bor" style="border: 1px solid black">
        <thead style="background: #3D61B2; color:#fff;">
            <tr style="border: 1px solid black; color: #fff;">
                <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">N°</th>
                <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">Entidad
                </th>
                <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">DATOS DEL
                    COLABORADOR<br />(APELLIDOS Y NOMBRES)</th>
                <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">Cargo /
                    Asesor(a)</th>
                <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">Número
                    módulo</th>
                <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">DNI</th>
                <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">Fecha</th>
                <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">Hora de
                    <br />Ingreso</th>
                <th colspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; "
                    class="text-center">Refrigerio</th>
                <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">Hora de
                    <br />Salida</th>
                <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">Ingreso
                    <br />programado</th>
                <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">Salida
                    <br />Programada</th>
                <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">
                    Observación</th>
            </tr>
            <tr>
                <th class="text-center" style="background-color: #0B22B4; color: white;">Salida</th>
                <th class="text-center" style="background-color: #0B22B4; color: #white;">Ingreso</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($query as $i => $q)
                @php
                    $hora1 = strtotime($q->HORA_1); // Convierte la hora1 a un timestamp
                    $horaInicial1 = strtotime('06:00:00');
                    $horaFinal1 = strtotime('10:00:00');
                @endphp
                <tr>
                    <th style="border: 1px solid black;">{{ $i + 1 }}</th>
                    <th style="border: 1px solid black;">{{ $q->ABREV_ENTIDAD }}</th>
                    <th style="border: 1px solid black;">{{ $q->NOMBREU }}</th>
                    <th style="border: 1px solid black;">Asesor de Servicio</th>
                    <th style="border: 1px solid black;">{{ $q->N_MODULO }}</th>
                    <th style="border: 1px solid black;">{{ $q->NUM_DOC }}</th>
                    <th style="border: 1px solid black;">
                        {{ date('d/m/Y', strtotime($q->FECHA)) }} </th>
                    <th style="border: 1px solid black;">{{ $q->HORA_1 }}</th>
                    <th style="border: 1px solid black;">{{ $q->HORA_2 }}</th>
                    <th style="border: 1px solid black;">{{ $q->HORA_3 }}</th>
                    <th style="border: 1px solid black;">{{ $q->HORA_4 }}</th>
                    <th style="border: 1px solid black;">
                        <?php setlocale(LC_TIME, 'es_PE', 'es_ES', 'es');
                        $FECHA = utf8_decode(strftime('%A', strtotime($q->FECHA))); ?>
                        @if ($FECHA == 's?bado')
                            {{ $hora_3->VALOR }}
                        @else
                            {{ $hora_1->VALOR }}
                        @endif
                    </th>
                    <th style="border: 1px solid black">
                        <?php setlocale(LC_TIME, 'es_PE', 'es_ES', 'es');
                        $FECHA = utf8_decode(strftime('%A', strtotime($q->FECHA))); ?>

                        @if ($FECHA == 's?bado')
                            {{ $hora_4->VALOR }}
                        @else
                            {{ $hora_2->VALOR }}
                        @endif
                    </th>
                    <th style="border: 1px solid black">
                        <?php setlocale(LC_TIME, 'es_PE', 'es_ES', 'es');
                        $FECHA = utf8_decode(strftime('%A', strtotime($q->FECHA))); ?>

                        @if ($FECHA == 's?bado')
                            Sábado
                        @else
                        @endif
                        @if ($q->contador_obs > 0)
                            <ul style="margin:0; padding-left:1em;">
                                @foreach(explode(';', $q->observaciones) as $obs)
                                @if(trim($obs) !== '')
                                    <li>> {{ $obs }}</li>
                                @endif
                                @endforeach
                            </ul>
                        @endif

                    </th>

                    {{-- 
                    @if ($q->N_NUM_DOC > '2')
                        <th style="border: 1px solid black; color: {{ strtotime($q->hora1) > strtotime('08:15:00') ? 'red' : 'black' }}">
                            {{ $q->hora1 }}
                        </th>
                
                        <th style="border: 1px solid black">
                            {{ $q->hora2 }}
                        </th>
                        <th style="border: 1px solid black">
                            {{ $q->hora3 }}
                        </th>
                        <th style="border: 1px solid black">
                            {{ $q->hora4 }}
                        </th>
                        <th style="border: 1px solid black">
                            <?php setlocale(LC_TIME, 'es_PE', 'es_ES', 'es');
                            $FECHA = utf8_decode(strftime('%A', strtotime($q->FECHA))); ?>
                            @if ($FECHA == 's?bado')
                                {{ $hora_3->VALOR }}
                            @else
                                {{ $hora_1->VALOR }}
                            @endif
                        </th>
                        <th style="border: 1px solid black">
                            <?php setlocale(LC_TIME, 'es_PE', 'es_ES', 'es');
                            $FECHA = utf8_decode(strftime('%A', strtotime($q->FECHA))); ?>

                            @if ($FECHA == 's?bado')
                                {{ $hora_4->VALOR }}
                            @else
                                {{ $hora_2->VALOR }}
                            @endif
                        </th>
                        <th style="border: 1px solid black">
                            <?php setlocale(LC_TIME, 'es_PE', 'es_ES', 'es');
                            $FECHA = utf8_decode(strftime('%A', strtotime($q->FECHA))); ?>

                            @if ($FECHA == 's?bado')
                                Sábado
                            @else

                            @endif

                        </th>
                    @elseif($q->N_NUM_DOC == '2' )
                        <th style="border: 1px solid black; color: {{ strtotime($q->hora1) > strtotime('08:15:00') ? 'red' : 'black' }}">
                            {{ $q->hora1 }}
                        </th>
                        <th style="border: 1px solid black">
                            --
                        </th>
                        <th style="border: 1px solid black">
                            --
                        </th>
                        <th style="border: 1px solid black">
                            {{ $q->hora2 }}
                        </th>
                        <th style="border: 1px solid black">
                            <?php setlocale(LC_TIME, 'es_PE', 'es_ES', 'es');
                            $FECHA = utf8_decode(strftime('%A', strtotime($q->FECHA))); ?>

                            @if ($FECHA == 's?bado')
                                {{ $hora_3->VALOR }}
                            @else
                                {{ $hora_1->VALOR }}
                            @endif
                            
                        </th>
                        <th style="border: 1px solid black">
                            <?php setlocale(LC_TIME, 'es_PE', 'es_ES', 'es');
                            $FECHA = utf8_decode(strftime('%A', strtotime($q->FECHA))); ?>

                            @if ($FECHA == 's?bado')
                                {{ $hora_4->VALOR }}
                            @else
                                {{ $hora_2->VALOR }}
                            @endif
                        </th>
                        <th style="border: 1px solid black">
                            <?php setlocale(LC_TIME, 'es_PE', 'es_ES', 'es');
                            $FECHA = utf8_decode(strftime('%A', strtotime($q->FECHA))); ?>

                            @if ($FECHA == 's?bado')
                                Sábado
                            @else

                            @endif

                        </th>
                    @elseif($q->N_NUM_DOC == '1' )
                        <th style="border: 1px solid black; color: {{ strtotime($q->hora1) > strtotime('08:15:00') ? 'red' : 'black' }}">
                            {{ $q->hora1 }}
                        </th>
                        <th style="border: 1px solid black">
                            --
                        </th>
                        <th style="border: 1px solid black">
                            --
                        </th>
                        <th style="border: 1px solid black">
                            --
                        </th>
                        <th style="border: 1px solid black">
                            <?php setlocale(LC_TIME, 'es_PE', 'es_ES', 'es');
                            $FECHA = utf8_decode(strftime('%A', strtotime($q->FECHA))); ?>
                            @if ($FECHA == 's?bado')
                                {{ $hora_3->VALOR }}
                            @else
                                {{ $hora_1->VALOR }}
                            @endif
                        </th>
                        <th style="border: 1px solid black">
                            <?php setlocale(LC_TIME, 'es_PE', 'es_ES', 'es');
                            $FECHA = utf8_decode(strftime('%A', strtotime($q->FECHA))); ?>

                            @if ($FECHA == 's?bado')
                                {{ $hora_4->VALOR }}
                            @else
                                {{ $hora_2->VALOR }}
                            @endif
                        </th>
                        <th style="border: 1px solid black">
                            <?php setlocale(LC_TIME, 'es_PE', 'es_ES', 'es');
                            $FECHA = utf8_decode(strftime('%A', strtotime($q->FECHA))); ?>

                            @if ($FECHA == 's?bado')
                                Sábado
                            @else

                            @endif

                        </th>
                    @endif
                     --}}
                </tr>
            @empty
                <tr>
                    <th colspan="13" style="border: 1px solid black; color:red;">No hay datos disponibles para la
                        fecha seleccionada</th>
                </tr>
            @endforelse
        </tbody>

    </table>

@endif
