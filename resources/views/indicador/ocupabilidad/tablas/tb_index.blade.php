<table class="table table-hover table-bordered table-striped" id="table_formato">
    <tbody>
        <tr>
            <td style="border: 1px solid black" rowspan="3" colspan="2"><img
                    src="{{ asset('imagen/mac_logo_export.jpg') }}" alt="" width="230px"></td>
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
<table class="table table-hover table-bordered table-striped" id="table_formato2">
    <thead class="tenca">
        <tr>
            <th>MODULOS</th>
            <th>NOMBRE DE LAS ENTIDADES</th>
            @for ($i = 1; $i <= $numeroDias; $i++)
                <th>{{ $i }}</th>
            @endfor
            <th>OBSERVACIONES O COMENTARIOS</th>
        </tr>
    </thead>

    <tbody>
        @forelse ($modulos as $modulo)
            <tr>
                <td>{{ $modulo->n_modulo }}</td>
                <td>{{ $modulo->nombre_entidad }}</td>
                @php $contadorSi = 0; @endphp
                @for ($i = 1; $i <= $numeroDias; $i++)
                    @php
                        $fechaActual = Carbon\Carbon::create($fecha_año, $fecha_mes, $i)->format('Y-m-d');
                        $esDomingo = Carbon\Carbon::create($fecha_año, $fecha_mes, $i)->isSunday();
                        $esFeriado = in_array($fechaActual, $feriados);
                        // Reglas manuales adicionales de feriados
                        if ($fechaActual === '2025-09-06' && $modulo->identidad == 6) {
                            $esFeriado = true;
                        }
                        
                        $activo = $fechaActual >= $modulo->fechainicio && $fechaActual <= $modulo->fechafin;
                    @endphp

                    @if ($esDomingo || $esFeriado || !$activo)
                        <td style="min-width: 28px; background-color: rgba(50,50,50,.8);">&nbsp;</td>
                    @else
                        @php
                            $mostrarSi =
                                isset($dias[$i][$modulo->idmodulo]) && $dias[$i][$modulo->idmodulo]['hora_minima'];
                            if ($mostrarSi) {
                                $contadorSi++;
                            }
                        @endphp
                        <td
                            style="min-width: 28px; @if ($mostrarSi) color: black !important; background: none @else background: #2F75B5; color: white !important @endif">
                            @if ($mostrarSi)
                                <span class="text-center">SI</span>
                            @else
                                <span class="text-center">NO</span>
                            @endif
                        </td>
                    @endif
                @endfor
                <td>{{ $contadorSi }}</td> <!-- Mostrar el contador de "SI" en la columna de observaciones -->
            </tr>
        @empty
            <tr>
                <td colspan="{{ $numeroDias + 3 }}" class="text-center">No hay datos disponibles</td>
            </tr>
        @endforelse
    </tbody>

</table>
<script>
    $(document).ready(function() {
        $('#table_formato2').DataTable({
            "paging": false, // Desactiva la paginación
            "searching": false, // Desactiva la búsqueda
            "info": false, // Desactiva la información de paginación
            "order": [
                [0, 'asc']
            ], // Ordena ascendente por la primera columna (asumiendo que es la de módulos)
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.19/i18n/Spanish.json" // Traducción al español
            }
        });
    });
</script>
