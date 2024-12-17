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
                @for ($i = 1; $i <= $numeroDias; $i++)
                    @php
                        // Crear fecha del día actual en el bucle
                        $fechaActual = Carbon\Carbon::create($fecha_año, $fecha_mes, $i)->format('Y-m-d');
                        // Determinar si es domingo
                        $esDomingo = Carbon\Carbon::create($fecha_año, $fecha_mes, $i)->isSunday();
                        // Verificar si el día es feriado
                        $esFeriado = in_array($fechaActual, $feriados);
                    @endphp

                    @if ($esDomingo || $esFeriado)
                        <td style="min-width: 28px; background-color: rgba(50,50,50,.8);">&nbsp;</td>
                    @else
                        <td style="min-width: 28px; 
                            @if (isset($dias[$i][$modulo->idmodulo])) 
                                @php
                                    $horaMinima = $dias[$i][$modulo->idmodulo]['hora_minima'] ?? null;
                                @endphp
                                @if ($horaMinima)
                                    @php
                                        // Comparación de la hora mínima
                                        $horaLimite = '08:15';
                                        $horaRegistro = new DateTime($horaMinima);
                                        $horaLimiteObj = new DateTime($horaLimite);
                                    @endphp
                                    @if ($horaRegistro < $horaLimiteObj)
                                        background: #FFFFFF !important; color: black !important; /* SI */
                                    @else
                                        background: #2F75B5 !important; color: white !important; /* NO */
                                    @endif
                                @else
                                    background: #474747 !important; color: white !important; /* - */
                                @endif
                            @else
                                background: #474747 !important; color: white !important; /* - */
                            @endif
                        ">
                            @if (isset($dias[$i][$modulo->idmodulo]) && $dias[$i][$modulo->idmodulo]['hora_minima'])
                                @php
                                    // Mostrar "SI" o "NO" basado en la hora comparada
                                    $horaMinima = $dias[$i][$modulo->idmodulo]['hora_minima'];
                                    $horaLimite = '08:15';
                                    $horaRegistro = new DateTime($horaMinima);
                                    $horaLimiteObj = new DateTime($horaLimite);
                                    if ($horaRegistro < $horaLimiteObj) {
                                        $horaMostrar = 'SI';
                                    } else {
                                        $horaMostrar = 'NO';
                                    }
                                @endphp
                                <span class="text-center">{{ $horaMostrar }}</span>
                            @else
                                <span class="text-center">-</span> <!-- Si no hay hora mínima -->
                            @endif
                        </td>
                    @endif
                @endfor
                <td></td>
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
            "info": false,  // Desactiva la información de paginación
            "order": [
                [0, 'asc']
            ], // Ordena ascendente por la primera columna (asumiendo que es la de módulos)
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.19/i18n/Spanish.json" // Traducción al español
            }
        });
    });
</script>
