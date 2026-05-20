<table class="table table-bor" style="border: 1px solid black">
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

<table class="table table-bor" style="border: 1px solid black">
    <thead style="background: #3D61B2; color:#fff;">
        <tr style="border: 1px solid black; color: #fff;">
            <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">N°</th>
            <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4;">Centro MAC</th>
            <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">Entidad</th>
            <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">DATOS DEL
                COLABORADOR<br />(APELLIDOS Y NOMBRES)</th>
            <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">Cargo /
                Asesor(a)</th>
            <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">Número módulo
            </th>
            <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">DNI</th>
            <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">Fecha</th>
            <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">Hora
                de<br />Ingreso</th>
            <th colspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; "
                class="text-center">Refrigerio</th>
            <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">Hora
                de<br />Salida</th>
            <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">
                Ingreso<br />programado</th>
            <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">
                Salida<br />Programada</th>
            <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">Observación
            </th>
        </tr>
        <tr>
            <th class="text-center" style="background-color: #0B22B4; color: white;">Salida</th>
            <th class="text-center" style="background-color: #0B22B4; color: white;">Ingreso</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($query as $i => $q)
            @php
                setlocale(LC_TIME, 'es_PE', 'es_ES', 'es');
                $nombreDia = strftime('%A', strtotime($q->FECHA));
                // Convertir a UTF-8 para evitar problemas con tildes
                $nombreDia = mb_convert_encoding($nombreDia, 'UTF-8', 'auto');
                $color = $q->ESTADO == 'ABIERTO' ? 'color:red;' : '';
            @endphp
            <tr>
                <td style="border: 1px solid black; {{ $color }}">{{ $i + 1 }}</td>
                <td style="border: 1px solid black; {{ $color }}">{{ $q->NOMBRE_MAC }}</td>
                <td style="border: 1px solid black; {{ $color }}">{{ $q->ABREV_ENTIDAD }}</td>
                <td style="border: 1px solid black; {{ $color }}">{{ $q->NOMBREU }}</td>
                <td style="border: 1px solid black; {{ $color }}">Asesor de Servicio</td>
                <td style="border: 1px solid black; {{ $color }}">{{ $q->N_MODULO }}</td>
                <td style="border: 1px solid black; {{ $color }}">{{ $q->NUM_DOC }}</td>
                <td style="border: 1px solid black; {{ $color }}">{{ date('d/m/Y', strtotime($q->FECHA)) }}
                </td>

                {{-- HORA 1 (Ingreso) --}}
                @if ($q->HORA_1)
                    <td style="border: 1px solid black; {{ $color }}">{{ $q->HORA_1 }}</td>
                @else
                    <td style="border: 1px solid black; {{ $color }}">--</td>
                @endif

                {{-- HORA 2 (Salida Refrigerio) --}}
                @if ($q->HORA_2)
                    <td style="border: 1px solid black; {{ $color }}">{{ $q->HORA_2 }}</td>
                @else
                    <td style="border: 1px solid black; {{ $color }}">--</td>
                @endif

                {{-- HORA 3 (Ingreso Refrigerio) --}}
                @if ($q->HORA_3)
                    <td style="border: 1px solid black; {{ $color }}">{{ $q->HORA_3 }}</td>
                @else
                    <td style="border: 1px solid black; {{ $color }}">--</td>
                @endif

                {{-- HORA 4 (Salida) --}}
                @if ($q->HORA_4)
                    <td style="border: 1px solid black; {{ $color }}">{{ $q->HORA_4 }}</td>
                @else
                    <td style="border: 1px solid black; {{ $color }}">--</td>
                @endif

                {{-- Ingreso Programado --}}
                <td style="border: 1px solid black; {{ $color }}">
                    @if ($nombreDia == 'sábado' || $nombreDia == 'sabado')
                        {{ $hora_3->VALOR ?? '' }}
                    @elseif ($nombreDia == 'domingo')
                        --
                    @else
                        {{ $hora_1->VALOR ?? '' }}
                    @endif
                </td>

                {{-- Salida Programada --}}
                <td style="border: 1px solid black; {{ $color }}">
                    @if ($nombreDia == 'sábado' || $nombreDia == 'sabado')
                        {{ $hora_4->VALOR ?? '' }}
                    @elseif ($nombreDia == 'domingo')
                        --
                    @else
                        {{ $hora_2->VALOR ?? '' }}
                    @endif
                </td>

                {{-- Observaciones --}}
                <td style="border: 1px solid black; {{ $color }}">
                    @if ($q->contador_obs > 0)
                        <ul style="margin:0; padding-left:1em;">
                            @foreach (explode(';', $q->observaciones) as $obs)
                                @if (trim($obs) !== '')
                                    <li>> {{ trim($obs) }}</li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="15" style="border: 1px solid black; color:red; text-align:center;">
                    No hay datos disponibles para la fecha seleccionada
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
