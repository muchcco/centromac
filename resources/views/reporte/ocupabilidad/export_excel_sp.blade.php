{{-- resources/views/reporte/ocupabilidad/export_excel.blade.php --}}
@php use Carbon\Carbon; @endphp

<table border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse; width:100%; table-layout:fixed;">
    <tr>
        <td colspan="6"
            style="border:1px solid #000;
                   font-size:16px;
                   font-weight:bold;
                   text-align:center;
                   padding:6px;">
            Reporte de Ocupabilidad – {{ $mesNombre }} {{ $fecha_año }}
        </td>
    </tr>
    <thead>
        <tr>
            <th style="
                background-color:#132842;
                color:#ffffff;
                border:1px solid #000000;
                padding:4px;
                text-align:center;
                width:200px;
                min-width:200px;
                white-space:normal;
                word-wrap:break-word;
            ">
                MAC
            </th>
            <th style="
                background-color:#132842;
                color:#ffffff;
                border:1px solid #000000;
                padding:4px;
                text-align:center;
                width:100px;
                min-width:100px;
                white-space:normal;
                word-wrap:break-word;
            ">
                MÓDULO
            </th>
            <th style="
                background-color:#132842;
                color:#ffffff;
                border:1px solid #000000;
                padding:4px;
                text-align:center;
                width:500px;
                min-width:500px;
                white-space:normal;
                word-wrap:break-word;
            ">
                ENTIDAD
            </th>
            <th style="
                background-color:#132842;
                color:#ffffff;
                border:1px solid #000000;
                padding:4px;
                text-align:center;
                width:120px;
                min-width:120px;
                white-space:normal;
                word-wrap:break-word;
            ">
                DÍAS MARCADOS
            </th>
            <th style="
                background-color:#132842;
                color:#ffffff;
                border:1px solid #000000;
                padding:4px;
                text-align:center;
                width:120px;
                min-width:120px;
                white-space:normal;
                word-wrap:break-word;
            ">
                DÍAS HÁBILES
            </th>
            <th style="
                background-color:#132842;
                color:#ffffff;
                border:1px solid #000000;
                padding:4px;
                text-align:center;
                width:120px;
                min-width:120px;
                white-space:normal;
                word-wrap:break-word;
            ">
                PORCENTAJE
            </th>
        </tr>
    </thead>
    <tbody>
        @forelse($modulos as $modulo)
            @php
                $modId      = $modulo->idmodulo;
                $macId      = $modulo->idmac;
                $feriados   = $feriadosPorMac[$macId] ?? [];
                $habiles    = $diasHabilesPorModulo[$macId][$modId] ?? 0;
                $contadorSi = 0;
                for ($d = 1; $d <= $numeroDias; $d++) {
                    $f      = Carbon::create($fecha_año, $fecha_mes, $d)->format('Y-m-d');
                    $esDom  = Carbon::parse($f)->isSunday();
                    $esFer  = in_array($f, $feriados, true);
                    $activo = $f >= $modulo->fechainicio && $f <= $modulo->fechafin;
                    if (!$esDom && !$esFer && $activo && isset($dias[$d][$macId][$modId]['hora_minima'])) {
                        $contadorSi++;
                    }
                }
                $pct = $habiles > 0 ? round(($contadorSi / $habiles) * 100, 2) : 0;
            @endphp
            <tr>
                <td style="
                    border:1px solid #000000;
                    padding:4px;
                    text-align:center;
                    width:200px;
                    white-space:normal;
                    word-wrap:break-word;
                ">
                    {{ $modulo->nombre_mac }}
                </td>
                <td style="
                    border:1px solid #000000;
                    padding:4px;
                    text-align:center;
                    width:100px;
                    white-space:normal;
                    word-wrap:break-word;
                ">
                    {{ $modulo->n_modulo }}
                </td>
                <td style="
                    border:1px solid #000000;
                    padding:4px;
                    text-align:left;
                    width:500px;
                    white-space:normal;
                    word-wrap:break-word;
                ">
                    {{ $modulo->nombre_entidad }}
                </td>
                <td style="
                    border:1px solid #000000;
                    padding:4px;
                    text-align:center;
                    width:120px;
                    white-space:normal;
                    word-wrap:break-word;
                ">
                    {{ $contadorSi }}
                </td>
                <td style="
                    border:1px solid #000000;
                    padding:4px;
                    text-align:center;
                    width:120px;
                    white-space:normal;
                    word-wrap:break-word;
                ">
                    {{ $habiles }}
                </td>
                <td style="
                    border:1px solid #000000;
                    padding:4px;
                    text-align:right;
                    width:120px;
                    white-space:normal;
                    word-wrap:break-word;
                ">
                    {{ $pct }}%
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="
                    border:1px solid #000000;
                    padding:4px;
                    text-align:center;
                ">
                    No hay datos disponibles
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
