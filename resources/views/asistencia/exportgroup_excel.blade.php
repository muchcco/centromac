@php
use Carbon\Carbon;

$fechaExcel = function ($fecha) {
if (!$fecha) return '';
try {
return Carbon::parse($fecha)->format('d/m/Y');
} catch (\Exception $e) {
return $fecha;
}
};

$fechaLargaExcel = function ($fecha) {
if (!$fecha) return '';

$meses = [
1 => 'Enero',
2 => 'Febrero',
3 => 'Marzo',
4 => 'Abril',
5 => 'Mayo',
6 => 'Junio',
7 => 'Julio',
8 => 'Agosto',
9 => 'Setiembre',
10 => 'Octubre',
11 => 'Noviembre',
12 => 'Diciembre',
];

try {
$f = Carbon::parse($fecha);
return $f->format('d') . ' de ' . $meses[$f->month] . ' ' . $f->format('Y');
} catch (\Exception $e) {
return $fecha;
}
};

$nombreDiaExcel = function ($fecha) {
$dias = [
Carbon::MONDAY => 'lunes',
Carbon::TUESDAY => 'martes',
Carbon::WEDNESDAY => 'miércoles',
Carbon::THURSDAY => 'jueves',
Carbon::FRIDAY => 'viernes',
Carbon::SATURDAY => 'sábado',
Carbon::SUNDAY => 'domingo',
];

try {
$f = Carbon::parse($fecha);
return $dias[$f->dayOfWeek] ?? '';
} catch (\Exception $e) {
return '';
}
};

$regimenLaboralExcel = function ($persona) {
$id = isset($persona->TVL_ID) ? (int) $persona->TVL_ID : 0;

switch ($id) {
case 1:
return 'D.LEG. N° 1057';
case 2:
return 'D.LEG. N° 276';
case 3:
return 'D.LEG. N° 728';
case 4:
return 'SNP';
case 5:
return 'OS';
case 6:
return 'Tercerización';
case 7:
return $persona->TVL_OTRO ?? 'OTRO';
default:
return 'D.LEG. N° 1057';
}
};

$horaCortaExcel = function ($hora) {
if (!$hora) return '';

$horaLower = mb_strtolower(trim($hora));

if ($horaLower === 'descanso') return 'Descanso';
if ($horaLower === 'vacaciones') return 'Vacaciones';

try {
return Carbon::parse($hora)->format('H:i');
} catch (\Exception $e) {
return substr($hora, 0, 5);
}
};

$calcularHorasTrabajadasExcel = function ($fecha, $horaIngreso, $horaSalida) {
if (!$fecha || !$horaIngreso || !$horaSalida) return '';

$ingresoLower = mb_strtolower(trim($horaIngreso));
$salidaLower = mb_strtolower(trim($horaSalida));

if ($ingresoLower === 'descanso' || $salidaLower === 'descanso') return '';
if ($ingresoLower === 'vacaciones' || $salidaLower === 'vacaciones') return '';

try {
$f = Carbon::parse($fecha);
$inicio = Carbon::parse($f->format('Y-m-d') . ' ' . $horaIngreso);
$fin = Carbon::parse($f->format('Y-m-d') . ' ' . $horaSalida);

if ($fin->lessThanOrEqualTo($inicio)) return '';

$minutos = $inicio->diffInMinutes($fin);

if (!$f->isSaturday() && !$f->isSunday() && $minutos >= 360) {
$minutos -= 60;
}

return sprintf('%02d:%02d', intdiv($minutos, 60), $minutos % 60);
} catch (\Exception $e) {
return '';
}
};

$estiloTitulo = 'border: 1px solid #808080; text-align: center; vertical-align: middle; font-weight: bold; color: #002060; background-color: #F2F2F2;';
$estiloFechaHeader = 'border: 1px solid #808080; font-weight: bold; color: #002060; text-align: center; vertical-align: middle;';
$estiloCabecera = 'border: 1px solid #000000; background-color: #D9E1F2; color: #002060; font-weight: bold; text-align: center; vertical-align: middle; height: 42px;';
$estiloCelda = 'border-left: 1px solid #000000; border-right: 1px solid #000000; border-top: 1px dotted #808080; border-bottom: 1px dotted #808080; text-align: center; vertical-align: middle; height: 18px;';
$estiloCeldaTexto = 'border-left: 1px solid #000000; border-right: 1px solid #000000; border-top: 1px dotted #808080; border-bottom: 1px dotted #808080; text-align: left; vertical-align: middle; height: 18px;';
$estiloDescanso = 'color: #FF0000; font-weight: bold;';
@endphp

@if ((string) $identidad === '17')

<table>
    <tr>
        <td colspan="17" style="background-color:#FFFFFF; height:15px;"></td>
    </tr>

    <tr>
        <td style="background-color:#FFFFFF;"></td>

        <td colspan="2" rowspan="3" style="background-color:#FFFFFF; text-align:center; vertical-align:middle;">
            <img src="{{ public_path('imagen/mac_logo_export.jpg') }}" width="150">
        </td>

        <td colspan="6" rowspan="3" style="border:1px solid #808080; background-color:#F2F2F2; text-align:center; vertical-align:middle; font-weight:bold; color:#002060;">
            CONTROL DE ASISTENCIA<br>
            Centro MAC:{{ strtoupper($name_mac) }}
        </td>

        <td style="background-color:#FFFFFF;"></td>

        <td style="border:1px solid #808080; background-color:#FFFFFF; font-weight:bold; color:#002060; text-align:center; vertical-align:middle;">
            Inicio:
        </td>

        <td style="border:1px solid #808080; background-color:#FFFFFF;"></td>

        <td colspan="3" style="border:1px solid #808080; background-color:#FFFFFF; font-weight:bold; color:#002060; text-align:center; vertical-align:middle;">
            {{ $fechaLargaExcel($fecha_inicial) }}
        </td>

        <td colspan="3" style="background-color:#FFFFFF;"></td>
    </tr>

    <tr>
        <td style="background-color:#FFFFFF;"></td>

        <td style="background-color:#FFFFFF;"></td>

        <td style="border:1px solid #808080; background-color:#FFFFFF; font-weight:bold; color:#002060; text-align:center; vertical-align:middle;">
            Fin:
        </td>

        <td style="border:1px solid #808080; background-color:#FFFFFF;"></td>

        <td colspan="3" style="border:1px solid #808080; background-color:#FFFFFF; font-weight:bold; color:#002060; text-align:center; vertical-align:middle;">
            {{ $fechaLargaExcel($fecha_fin) }}
        </td>

        <td colspan="3" style="background-color:#FFFFFF;"></td>
    </tr>

    <tr>
        <td style="background-color:#FFFFFF;"></td>
        <td colspan="8" style="background-color:#FFFFFF;"></td>
    </tr>

    <tr>
        <td colspan="17" style="background-color:#FFFFFF; border-top:2px solid #808080; height:12px;"></td>
    </tr>

    <tr>
        <td colspan="17" style="background-color:#FFFFFF; height:12px;"></td>
    </tr>
</table>
<table>
    <thead>
        <tr>
            <th style="background-color:#FFFFFF;"></th>


            <th style="border:1px solid #000000; background-color:#D9E1F2; color:#002060; font-weight:bold; text-align:center; vertical-align:middle; width:85px;">DNI</th>
            <th style="border:1px solid #000000; background-color:#D9E1F2; color:#002060; font-weight:bold; text-align:center; vertical-align:middle; width:230px;">APELLIDOS Y NOMBRES</th>
            <th style="border:1px solid #000000; background-color:#D9E1F2; color:#002060; font-weight:bold; text-align:center; vertical-align:middle; width:85px;">RÉG.<br>LABORAL</th>
            <th style="border:1px solid #000000; background-color:#D9E1F2; color:#002060; font-weight:bold; text-align:center; vertical-align:middle; width:80px;">CENTRO<br>MAC</th>
            <th style="border:1px solid #000000; background-color:#D9E1F2; color:#002060; font-weight:bold; text-align:center; vertical-align:middle; width:125px;">CARGO</th>
            <th style="border:1px solid #000000; background-color:#D9E1F2; color:#002060; font-weight:bold; text-align:center; vertical-align:middle; width:75px;">DÍA</th>
            <th style="border:1px solid #000000; background-color:#D9E1F2; color:#002060; font-weight:bold; text-align:center; vertical-align:middle; width:85px;">FECHA</th>
            <th style="border:1px solid #000000; background-color:#D9E1F2; color:#002060; font-weight:bold; text-align:center; vertical-align:middle; width:95px;">Hora<br>Ingreso<br>Programada</th>
            <th style="border:1px solid #000000; background-color:#D9E1F2; color:#002060; font-weight:bold; text-align:center; vertical-align:middle; width:95px;">Hora de<br>Ingreso<br>registrado</th>
            <th style="border:1px solid #000000; background-color:#D9E1F2; color:#002060; font-weight:bold; text-align:center; vertical-align:middle; width:95px;">Hora<br>Salida<br>Programada</th>
            <th style="border:1px solid #000000; background-color:#D9E1F2; color:#002060; font-weight:bold; text-align:center; vertical-align:middle; width:95px;">Hora de<br>salida<br>registrado</th>
            <th style="border:1px solid #000000; background-color:#D9E1F2; color:#002060; font-weight:bold; text-align:center; vertical-align:middle; width:90px;">Horas<br>Programadas</th>
            <th style="border:1px solid #000000; background-color:#D9E1F2; color:#002060; font-weight:bold; text-align:center; vertical-align:middle; width:90px;">Horas<br>trabajadas</th>
            <th style="border:1px solid #000000; background-color:#D9E1F2; color:#002060; font-weight:bold; text-align:center; vertical-align:middle; width:230px;">Observaciones</th>

            <th style="background-color:#FFFFFF;"></th>
            <th style="background-color:#FFFFFF;"></th>
        </tr>
    </thead>

    <tbody>
        @foreach ((is_array($datosAgrupados) || is_object($datosAgrupados)) ? $datosAgrupados : [] as $grupo)

        @php
        $persona = $grupo['encabezado'];

        $detallePersona = collect($grupo['detalle'])->keyBy(function ($item) {
        return Carbon::parse($item->FECHA)->format('Y-m-d');
        });

        $regimen = $regimenLaboralExcel($persona);
        $cargo = $persona->NOMBRE_CARGO ?? 'Orientador';

        $centroMacTexto = strtoupper($name_mac);
        $centroMacTexto = str_replace('CENTRO MAC ', '', $centroMacTexto);
        $centroMacTexto = str_replace('MAC ', '', $centroMacTexto);

        $estiloBorde = 'border-left:1px solid #000000; border-right:1px solid #000000; border-top:1px dotted #808080; border-bottom:1px dotted #808080; background-color:#FFFFFF; text-align:center; vertical-align:middle;';
        $estiloBordeTexto = 'border-left:1px solid #000000; border-right:1px solid #000000; border-top:1px dotted #808080; border-bottom:1px dotted #808080; background-color:#FFFFFF; text-align:left; vertical-align:middle;';
        @endphp

        @foreach ((is_array($fechasArray) || is_object($fechasArray)) ? $fechasArray : [] as $fecha)

        @php
        $fechaKey = Carbon::parse($fecha)->format('Y-m-d');
        $fechaCarbon = Carbon::parse($fechaKey);
        $detalle = $detallePersona->get($fechaKey);

        $esDomingo = $fechaCarbon->isSunday();
        $esSabado = $fechaCarbon->isSaturday();

        $nombreDia = $nombreDiaExcel($fechaKey);

        $horaIngresoReal = '';
        $horaSalidaReal = '';

        if ($detalle && !empty($detalle->HORAS)) {
        $marcaciones = array_values(array_filter(array_map('trim', explode(',', $detalle->HORAS))));
        sort($marcaciones);

        if (count($marcaciones) >= 1) {
        $horaIngresoReal = $marcaciones[0];
        }

        if (count($marcaciones) >= 2) {
        $horaSalidaReal = $marcaciones[count($marcaciones) - 1];
        }
        }

        $ingresoProgramado = '08:15';
        $salidaProgramada = $esSabado ? '13:30' : '17:15';
        $horasProgramadas = $esSabado ? '5:15' : '8:00';
        $observacion = $detalle->OBSERVACION ?? '';

        if ($esDomingo) {
        $ingresoProgramado = 'Descanso';
        $horaIngresoReal = 'Descanso';
        $salidaProgramada = 'Descanso';
        $horaSalidaReal = 'Descanso';
        $horasProgramadas = 'Descanso';
        $observacion = '';
        }

        $horaIngresoMostrar = $horaCortaExcel($horaIngresoReal);
        $horaSalidaMostrar = $horaCortaExcel($horaSalidaReal);
        $horasTrabajadas = $calcularHorasTrabajadasExcel($fechaKey, $horaIngresoMostrar, $horaSalidaMostrar);

        $colorDescanso = $esDomingo ? 'color:#FF0000; font-weight:bold;' : '';

        $estiloObs = $estiloBordeTexto;

        if (strtoupper(trim($observacion)) === 'FERIADO') {
        $estiloObs .= ' background-color:#FFFF00; font-weight:bold;';
        }
        @endphp

        <tr>
            <td style="background-color:#FFFFFF;"></td>

            <td style="{{ $estiloBorde }} mso-number-format:'\@';">'{{ $persona->NUM_DOC }}</td>
            <td style="{{ $estiloBordeTexto }}">{{ $persona->NOMBREU }}</td>
            <td style="{{ $estiloBordeTexto }}">{{ $regimen }}</td>
            <td style="{{ $estiloBorde }}">{{ $centroMacTexto }}</td>
            <td style="{{ $estiloBorde }}">{{ $cargo }}</td>
            <td style="{{ $estiloBorde }}">{{ $nombreDia }}</td>
            <td style="{{ $estiloBorde }}">{{ $fechaExcel($fechaKey) }}</td>
            <td style="{{ $estiloBorde }} {{ $colorDescanso }}">{{ $ingresoProgramado }}</td>
            <td style="{{ $estiloBorde }} {{ $colorDescanso }}">{{ $horaIngresoMostrar }}</td>
            <td style="{{ $estiloBorde }} {{ $colorDescanso }}">{{ $salidaProgramada }}</td>
            <td style="{{ $estiloBorde }} {{ $colorDescanso }}">{{ $horaSalidaMostrar }}</td>
            <td style="{{ $estiloBorde }} {{ $colorDescanso }}">{{ $horasProgramadas }}</td>
            <td style="{{ $estiloBorde }}">{{ $horasTrabajadas }}</td>
            <td style="{{ $estiloObs }}">{{ $observacion }}</td>

            <td style="background-color:#FFFFFF;"></td>
            <td style="background-color:#FFFFFF;"></td>
        </tr>

        @endforeach

        @endforeach
    </tbody>
</table>

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
        <th style="border: 1px solid black" colspan="7">De: {{ $fecha_inicial }} Hasta: {{ $fecha_fin }}</th>
        @endif
    </tr>
</table>

<table class="table table-bor" style="border: 1px solid black">
    <thead style="background: #3D61B2; color:#fff;">
        <tr style="border: 1px solid black; color: #fff;">
            <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">N°</th>
            <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4;">Centro MAC</th>
            <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">Entidad</th>
            <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">DATOS DEL COLABORADOR<br />(APELLIDOS Y NOMBRES)</th>
            <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">Cargo / Asesor(a)</th>
            <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">Número módulo</th>
            <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">DNI</th>
            <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">Fecha</th>
            <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">Hora de<br />Ingreso</th>
            <th colspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; " class="text-center">Refrigerio</th>
            <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">Hora de<br />Salida</th>
            <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">Ingreso<br />programado</th>
            <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">Salida<br />Programada</th>
            <th rowspan="2" style="color: white; border: 1px solid black; background-color: #0B22B4; ">Observación</th>
        </tr>
        <tr>
            <th class="text-center" style="background-color: #0B22B4; color: white;">Salida</th>
            <th class="text-center" style="background-color: #0B22B4; color: #white;">Ingreso</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($query as $i => $q)
        @php
        $color = ($q->ESTADO ?? '') == 'ABIERTO' ? 'color:red;' : '';
        @endphp

        <tr>
            <th style="border: 1px solid black;{{ $color }}">{{ $i + 1 }}</th>
            <th style="border: 1px solid black;{{ $color }}">{{ $q->NOMBRE_MAC }}</th>
            <th style="border: 1px solid black;{{ $color }}">{{ $q->ABREV_ENTIDAD }}</th>
            <th style="border: 1px solid black;{{ $color }}">{{ $q->NOMBREU }}</th>
            <th style="border: 1px solid black;{{ $color }}">Asesor de Servicio</th>
            <th style="border: 1px solid black;{{ $color }}">{{ $q->N_MODULO }}</th>
            <th style="border: 1px solid black;{{ $color }}">{{ $q->NUM_DOC }}</th>
            <th style="border: 1px solid black;{{ $color }}">{{ date('d/m/Y', strtotime($q->FECHA)) }}</th>
            <th style="border: 1px solid black;{{ $color }}">{{ $q->HORA_1 }}</th>
            <th style="border: 1px solid black;{{ $color }}">{{ $q->HORA_2 }}</th>
            <th style="border: 1px solid black;{{ $color }}">{{ $q->HORA_3 }}</th>
            <th style="border: 1px solid black;{{ $color }}">{{ $q->HORA_4 }}</th>
            <th style="border: 1px solid black;{{ $color }}">
                <?php
                setlocale(LC_TIME, 'es_PE', 'es_ES', 'es');
                $FECHA = utf8_decode(strftime('%A', strtotime($q->FECHA)));
                ?>
                @if ($FECHA == 's?bado')
                {{ $hora_3->VALOR }}
                @else
                {{ $hora_1->VALOR }}
                @endif
            </th>
            <th style="border: 1px solid black;{{ $color }}">
                <?php
                setlocale(LC_TIME, 'es_PE', 'es_ES', 'es');
                $FECHA = utf8_decode(strftime('%A', strtotime($q->FECHA)));
                ?>
                @if ($FECHA == 's?bado')
                {{ $hora_4->VALOR }}
                @else
                {{ $hora_2->VALOR }}
                @endif
            </th>
            <th style="border: 1px solid black;{{ $color }}">
                <?php
                setlocale(LC_TIME, 'es_PE', 'es_ES', 'es');
                $FECHA = utf8_decode(strftime('%A', strtotime($q->FECHA)));
                ?>
                @if ($FECHA == 's?bado')
                Sábado
                @endif

                @if (($q->contador_obs ?? 0) > 0)
                <ul style="margin:0; padding-left:1em;">
                    @foreach (explode(';', $q->observaciones ?? '') as $obs)
                    @if (trim($obs) !== '')
                    <li>> {{ $obs }}</li>
                    @endif
                    @endforeach
                </ul>
                @endif
            </th>
        </tr>
        @empty
        <tr>
            <td colspan="15" style="border: 1px solid black; text-align: center;">Sin registros</td>
        </tr>
        @endforelse
    </tbody>
</table>

@endif