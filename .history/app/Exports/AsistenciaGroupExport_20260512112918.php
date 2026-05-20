@if ($identidad == '17')
    <table class="table table-bordered" style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr>
                <th style="border: 1px solid black; background: #D9E1F2; text-align: center;" colspan="6">
                    REPORTE DE ASISTENCIA - {{ $name_mac }}
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($datosAgrupados as $grupo)
                <!-- Encabezado del colaborador -->
                <tr>
                    <th style="border: 1px solid black; background: #D9E1F2;" colspan="6">
                        {{ $grupo['encabezado']->NOMBREU ?? 'Sin nombre' }}
                    </th>
                </tr>
                <tr>
                    <td style="border: 1px solid black; font-weight: bold;">Día</td>
                    <td style="border: 1px solid black; font-weight: bold;">Fecha</td>
                    <td style="border: 1px solid black; font-weight: bold;">Ingreso</td>
                    <td style="border: 1px solid black; font-weight: bold;">Salida</td>
                    <td style="border: 1px solid black; font-weight: bold;">Ingreso programado</td>
                    <td style="border: 1px solid black; font-weight: bold;">Salida programada</td>
                </tr>

                @foreach ($fechasArray as $fecha)
                    @php
                        // Buscar el detalle correspondiente a la fecha actual
                        $detalle = collect($grupo['detalle'])->first(function ($item) use ($fecha) {
                            if (isset($item->FECHA)) {
                                $fechaItem = \Carbon\Carbon::parse($item->FECHA)->toDateString();
                                $fechaComparar = \Carbon\Carbon::parse($fecha)->toDateString();
                                return $fechaItem === $fechaComparar;
                            }
                            return false;
                        });

                        // Configurar locale para días de la semana
                        setlocale(LC_TIME, 'es_ES', 'es_PE', 'es');
                        $timestamp = strtotime($fecha);
                        $nombreDia = strftime('%A', $timestamp);
                        
                        // Manejar caracteres especiales
                        $nombreDia = mb_convert_encoding($nombreDia, 'UTF-8', 'auto');
                    @endphp

                    <tr>
                        <td style="border: 1px solid black; text-align: left;">{{ ucfirst($nombreDia) }}</td>
                        <td style="border: 1px solid black; text-align: center;">{{ date('d/m/Y', strtotime($fecha)) }}</td>

                        {{-- INGRESO --}}
                        @if ($detalle && isset($detalle->HORA_1) && $detalle->HORA_1)
                            @php
                                $horaEntrada = $detalle->HORA_1;
                                $fechaObj = \Carbon\Carbon::parse($fecha);
                                $nombreDiaComparar = strftime('%A', $fechaObj->timestamp);
                                
                                if ($nombreDiaComparar == 'sábado' || $nombreDiaComparar == 'sabado') {
                                    $confTimestamp = isset($hora_3->VALOR) ? strtotime($hora_3->VALOR) : 0;
                                } else {
                                    $confTimestamp = isset($hora_1->VALOR) ? strtotime($hora_1->VALOR) : 0;
                                }
                                $confTimestamp += 5 * 60; // tolerancia de 5 minutos
                                $confNuevaFecha = date('H:i:s', $confTimestamp);
                            @endphp

                            @if (strtotime($detalle->HORA_1) > strtotime($confNuevaFecha))
                                <td style="border: 1px solid black; color:#ca0606;">{{ $detalle->HORA_1 }}</td>
                            @else
                                <td style="border: 1px solid black;">{{ $detalle->HORA_1 }}</td>
                            @endif
                        @else
                            <td style="border: 1px solid black; text-align: center;">--</td>
                        @endif

                        {{-- SALIDA --}}
                        @if ($detalle && isset($detalle->N_NUM_DOC) && $detalle->N_NUM_DOC > 1 && isset($detalle->HORA_4) && $detalle->HORA_4)
                            @php
                                $fechaObj = \Carbon\Carbon::parse($fecha);
                                $nombreDiaComparar = strftime('%A', $fechaObj->timestamp);
                                
                                if ($nombreDiaComparar == 'sábado' || $nombreDiaComparar == 'sabado') {
                                    $limiteSalida = isset($hora_5->SUM_SOLO) ? $hora_5->SUM_SOLO : (isset($hora_5->VALOR) ? $hora_5->VALOR : '00:00:00');
                                } else {
                                    $limiteSalida = isset($hora_4->SUM_SOLO) ? $hora_4->SUM_SOLO : (isset($hora_4->VALOR) ? $hora_4->VALOR : '00:00:00');
                                }
                            @endphp

                            @if (strtotime($detalle->HORA_4) < strtotime($limiteSalida))
                                <td style="border: 1px solid black; color:#ca0606;">{{ $detalle->HORA_4 }}</td>
                            @else
                                <td style="border: 1px solid black;">{{ $detalle->HORA_4 }}</td>
                            @endif
                        @else
                            <td style="border: 1px solid black; text-align: center;">--</td>
                        @endif

                        {{-- PROGRAMACIÓN --}}
                        @if ($nombreDia == 'domingo')
                            <td colspan="2" style="background: #FFFF00; border: 1px solid black; text-align: center;">Descanso Semanal</td>
                        @elseif(($nombreDia == 'sábado' || $nombreDia == 'sabado') && (!$detalle || (!isset($detalle->HORA_1) && !isset($detalle->HORA_4))))
                            <td colspan="2" style="background: #FFFF00; border: 1px solid black; text-align: center;">Descanso Semanal</td>
                        @else
                            <td style="border: 1px solid black; text-align: center;">
                                @if ($nombreDia == 'sábado' || $nombreDia == 'sabado')
                                    {{ $hora_3->VALOR ?? '--' }}
                                @elseif($nombreDia == 'domingo')
                                    --
                                @else
                                    {{ $hora_1->VALOR ?? '--' }}
                                @endif
                            </td>
                            <td style="border: 1px solid black; text-align: center;">
                                @if ($nombreDia == 'sábado' || $nombreDia == 'sabado')
                                    {{ $hora_5->VALOR ?? '--' }}
                                @elseif($nombreDia == 'domingo')
                                    --
                                @else
                                    {{ $hora_2->VALOR ?? '--' }}
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
                
                <!-- Espacio entre colaboradores -->
                <tr><td colspan="6" style="border: none; height: 10px;"></td></tr>
            @endforeach
        </tbody>
    </table>
@else
    {{-- Resto de tu bloque tal cual --}}
    <table class="table table-bordered" style="border-collapse: collapse; width: 100%;">
        <!-- Tu código existente para identidad != 17 -->
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
                <th style="border: 1px solid black" colspan="7">De: {{ $fecha_inicial }} Hasta:
                    {{ $fecha_fin }}</th>
            @endif
        </tr>
    </table>

    <!-- Resto de tu tabla existente -->
    <table class="table table-bordered" style="border-collapse: collapse; width: 100%; border: 1px solid black">
        <thead style="background: #3D61B2; color:#fff;">
            <tr style="border: 1px solid black; color: #fff;">
                <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; text-align: center;">N°</th>
                <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; text-align: center;">Centro MAC</th>
                <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; text-align: center;">Entidad</th>
                <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; text-align: center;">DATOS DEL COLABORADOR<br />(APELLIDOS Y NOMBRES)</th>
                <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; text-align: center;">Cargo / Asesor(a)</th>
                <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; text-align: center;">Número módulo</th>
                <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; text-align: center;">DNI</th>
                <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; text-align: center;">Fecha</th>
                <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; text-align: center;">Hora de <br />Ingreso</th>
                <th colspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; text-align: center;">Refrigerio</th>
                <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; text-align: center;">Hora de <br />Salida</th>
                <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; text-align: center;">Ingreso <br />programado</th>
                <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; text-align: center;">Salida <br />Programada</th>
                <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; text-align: center;">Observación</th>
            </tr>
            <tr>
                <th class="text-center" style="background-color: #0B22B4; color: white; text-align: center;">Salida</th>
                <th class="text-center" style="background-color: #0B22B4; color: white; text-align: center;">Ingreso</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($query as $i => $q)
                @php
                    $color = $q->ESTADO == 'ABIERTO' ? 'color:red;' : '';
                @endphp
                <tr>
                    <th style="border: 1px solid black; {{ $color }} text-align: center;">{{ $i + 1 }}</th>
                    <th style="border: 1px solid black; {{ $color }} text-align: center;">{{ $q->NOMBRE_MAC ?? '' }}</th>
                    <th style="border: 1px solid black; {{ $color }} text-align: center;">{{ $q->ABREV_ENTIDAD ?? '' }}</th>
                    <th style="border: 1px solid black; {{ $color }} text-align: left;">{{ $q->NOMBREU ?? '' }}</th>
                    <th style="border: 1px solid black; {{ $color }} text-align: center;">Asesor de Servicio</th>
                    <th style="border: 1px solid black; {{ $color }} text-align: center;">{{ $q->N_MODULO ?? '' }}</th>
                    <th style="border: 1px solid black; {{ $color }} text-align: center;">{{ $q->NUM_DOC ?? '' }}</th>
                    <th style="border: 1px solid black; {{ $color }} text-align: center;">
                        {{ isset($q->FECHA) ? date('d/m/Y', strtotime($q->FECHA)) : '' }}
                    </th>
                    <th style="border: 1px solid black; {{ $color }} text-align: center;">{{ $q->HORA_1 ?? '' }}</th>
                    <th style="border: 1px solid black; {{ $color }} text-align: center;">{{ $q->HORA_2 ?? '' }}</th>
                    <th style="border: 1px solid black; {{ $color }} text-align: center;">{{ $q->HORA_3 ?? '' }}</th>
                    <th style="border: 1px solid black; {{ $color }} text-align: center;">{{ $q->HORA_4 ?? '' }}</th>
                    <th style="border: 1px solid black; {{ $color }} text-align: center;">
                        @php
                            setlocale(LC_TIME, 'es_PE', 'es_ES', 'es');
                            $nombreDia = isset($q->FECHA) ? strftime('%A', strtotime($q->FECHA)) : '';
                            $nombreDia = mb_convert_encoding($nombreDia, 'UTF-8', 'auto');
                        @endphp
                        @if ($nombreDia == 'sábado' || $nombreDia == 'sabado')
                            {{ $hora_3->VALOR ?? '' }}
                        @else
                            {{ $hora_1->VALOR ?? '' }}
                        @endif
                    </th>
                    <th style="border: 1px solid black; {{ $color }} text-align: center;">
                        @if ($nombreDia == 'sábado' || $nombreDia == 'sabado')
                            {{ $hora_4->VALOR ?? '' }}
                        @else
                            {{ $hora_2->VALOR ?? '' }}
                        @endif
                    </th>
                    <th style="border: 1px solid black; {{ $color }} text-align: left;">
                        @if (isset($q->contador_obs) && $q->contador_obs > 0 && isset($q->observaciones))
                            <ul style="margin:0; padding-left:1em;">
                                @foreach (explode(';', $q->observaciones) as $obs)
                                    @if (trim($obs) !== '')
                                        <li>{{ $obs }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        @endif
                    </th>
                </tr>
            @empty
                <tr>
                    <td colspan="15" style="border: 1px solid black; color:red; text-align: center;">
                        No hay datos disponibles para la fecha seleccionada
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endif