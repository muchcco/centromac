<table>
    <tr>
        <td></td>
    </tr>
</table>

<table class="table table-hover table-bordered table-striped" id="table_formato">
    <tbody>
        <tr>
            <td style="border: 1px solid black" rowspan="3" colspan="2"></td>
            <td style="border: 1px solid black" colspan="8" rowspan="2">
                REPORTE CONSOLIDADO DE OCUPABILIDAD DE LOS MÓDULOS DE LAS ENTIDADES PARTICIPANTES POR
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
            <td style="border: 1px solid black">
                {{ $nombreMac }}
            </td>
            <td style="border: 1px solid black" colspan="2">MES:</td>
            <td style="border: 1px solid black" colspan="7">
                {{ $mesNombre }}
            </td>
        </tr>
    </tbody>
</table>



<table class="table table-bor" style="border: 1px solid black">
    <thead style="background: #3D61B2; color:#fff;">
        <tr style="border: 1px solid black; color: #fff;">
            <th style="color: white; border: 1px solid black; background-color: #0B22B4;">MODULOS</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4;">NOMBRE DE LAS ENTIDADES</th>
            @for ($i = 1; $i <= $numeroDias; $i++)
                <th style="color: white; border: 1px solid black; background-color: #0B22B4;">{{ $i }}</th>
            @endfor
            <th style="color: white; border: 1px solid black; background-color: #0B22B4;">OBSERVACIONES O COMENTARIOS</th>
        </tr>
    </thead>

    <tbody>
        @forelse ($modulos as $modulo)
            <tr>
                <td style="border: 1px solid #2F75B5">{{ $modulo->n_modulo }}</td>
                <td style="border: 1px solid #2F75B5">{{ $modulo->nombre_entidad }}</td>
                @php $contadorSi = 0; @endphp
                @for ($i = 1; $i <= $numeroDias; $i++)
                    @php
                        $fechaActual = Carbon\Carbon::create($fecha_año, $fecha_mes, $i)->format('Y-m-d');
                        $esDomingo = Carbon\Carbon::create($fecha_año, $fecha_mes, $i)->isSunday();
                        $esFeriado = in_array($fechaActual, $feriados);
                        $activo = $fechaActual >= $modulo->fechainicio && $fechaActual <= $modulo->fechafin;
                    @endphp

                    @if ($esDomingo || $esFeriado || !$activo)
                        <td style="border: 1px solid #ffffff; min-width: 28px; background:#323232;">&nbsp;</td>
                    @else
                        @php
                            $mostrarSi =
                                isset($dias[$i][$modulo->idmodulo]) && $dias[$i][$modulo->idmodulo]['hora_minima'];
                            if ($mostrarSi) {
                                $contadorSi++;
                            }
                        @endphp
                        <td
                            style="min-width: 28px; @if ($mostrarSi) color: #000 !important; background: none @else background: #2F75B5; color: #fff !important @endif">
                            @if ($mostrarSi)
                                <span class="text-center">SI</span>
                            @else
                                <span class="text-center">NO</span>
                            @endif
                        </td>
                    @endif
                @endfor
                <td style="border: 1px solid #2F75B5">{{ $contadorSi }}</td> <!-- Mostrar el contador de "SI" en la columna de observaciones -->
            </tr>
        @empty
            <tr>
                <td colspan="{{ $numeroDias + 3 }}" class="text-center">No hay datos disponibles</td>
            </tr>
        @endforelse
    </tbody>

</table>