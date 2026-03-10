<table>
    <tr>
        <td></td>
    </tr>
</table>

@php
    $hoy = \Carbon\Carbon::today()->format('Y-m-d');
    $totalColumnas = $numeroDias + 3;
    $restantes = $totalColumnas - 4;
    $mitad = floor($restantes / 2);
    $mitad2 = $restantes - $mitad;
@endphp

<table style="border-collapse:collapse;width:100%;font-family:Calibri,Arial;font-size:12px">
    <tbody>
        <tr>
            <td rowspan="3" colspan="2" style="border:1px solid #000"></td>
            <td colspan="{{ $numeroDias }}" rowspan="2"
                style="border:1px solid #000;text-align:center;font-weight:bold;font-size:12px">
                REPORTE CONSOLIDADO DE OCUPABILIDAD DE LOS MÓDULOS
                DE LAS ENTIDADES PARTICIPANTES POR MES
                <br>
                <span style="color:#c00000">
                    Período evaluado Enero a diciembre {{ $fecha_año }}
                </span>
            </td>
            <td style="border:1px solid #000;text-align:center">Código</td>
            <td style="border:1px solid #000;text-align:center">ANS2</td>
        </tr>
        <tr>
            <td style="border:1px solid #000;text-align:center">Versión</td>
            <td style="border:1px solid #000;text-align:center">1.0.0</td>
        </tr>
        <tr>
            <td colspan="4" style="border:1px solid #000;text-align:center;font-weight:bold;background:#e7edf7">
                Centro MAC
            </td>
            <td colspan="{{ $mitad - 1 }}" style="border:1px solid #000;text-align:center">
                {{ $nombreMac }}
            </td>
            <td style="border:1px solid #000;text-align:center;font-weight:bold;background:#e7edf7">
                MES
            </td>
            <td colspan="{{ $mitad2 - 1 }}" style="border:1px solid #000;text-align:center">
                {{ $mesNombre }}
            </td>
        </tr>
    </tbody>
</table>
<table style="border-collapse:collapse;width:100%;border:1px solid #000;font-family:Calibri">
    <thead>
        <tr>
            <th style="border:1px solid #000;background:#0B22B4;color:#fff;text-align:center;width:60px">
                MODULOS
            </th>
            <th style="border:1px solid #000;background:#0B22B4;color:#fff;text-align:center;width:260px">
                NOMBRE DE LAS ENTIDADES
            </th>
            @for ($i = 1; $i <= $numeroDias; $i++)
                <th style="border:1px solid #000;background:#0B22B4;color:#fff;text-align:center;width:28px">
                    {{ $i }}
                </th>
            @endfor
            <th style="border:1px solid #000;background:#0B22B4;color:#fff;text-align:center;width:90px">
                OBSERVACIONES
            </th>
        </tr>
    </thead>

    <tbody style="border:2px solid #173A7E">
        @foreach ($modulos as $modulo)
            @php $contadorSi = 0; @endphp
            <tr>
                <td style="border:1px solid #173A7E;text-align:center">
                    {{ $modulo->n_modulo }}
                </td>
                <td style="border:1px solid #173A7E;padding:4px">
                    {{ $modulo->nombre_entidad }}
                </td>
                @for ($d = 1; $d <= $numeroDias; $d++)
                    @php
                        $fechaObj = \Carbon\Carbon::create($fecha_año, $fecha_mes, $d);
                        $fecha = $fechaObj->format('Y-m-d');
                        $esFuturo = $fecha > $hoy;
                        $esDomingo = $fechaObj->isSunday();
                        $esFeriado = in_array($fecha, $feriados);
                        $activo = $fecha >= $modulo->fechainicio && $fecha <= $modulo->fechafin;
                        $cerrado = in_array($d, $diasCerrados);
                        $valor = $final[$d][$modulo->idmodulo] ?? null;
                        $mostrarSi = !$esFuturo && !$esDomingo && !$esFeriado && $activo && !empty($valor);
                        if ($mostrarSi) {
                            $contadorSi++;
                        }
                    @endphp
                    {{-- FUTURO --}}
                    @if ($esFuturo)
                        <td style="border:1px solid #173A7E;background:#666;color:white;text-align:center">*</td>
                        {{-- NO APLICA --}}
                    @elseif($esDomingo || $esFeriado || !$activo)
                        <td style="border:1px solid #173A7E;background:#8a8a8a;color:white;text-align:center">*</td>
                        {{-- DÍA CERRADO (SP HISTÓRICO) --}}
                    @elseif($cerrado)
                        <td
                            style=" border:1px solid #173A7E; background:{{ $mostrarSi ? '#ffffff' : '#2F75B5' }}; color:{{ $mostrarSi ? 'black' : 'white' }}; text-align:center;">
                            {!! $mostrarSi ? 'SI' : 'NO' !!}
                        </td>
                        {{-- DÍA ABIERTO --}}
                    @else
                        <td
                            style=" border:1px solid #173A7E; background:{{ $mostrarSi ? '#e9a2a9' : '#C00000' }}; color:white; text-align:center;">
                            {!! $mostrarSi ? 'SI' : 'NO' !!}
                        </td>
                    @endif
                @endfor
                <td style="border:1px solid #173A7E;text-align:center;font-weight:bold">
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
