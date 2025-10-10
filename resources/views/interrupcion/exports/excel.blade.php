<table>
    <thead>
        <!-- FILA 1: Espacio reservado para el logo en A1:B4 -->
        <tr></tr>
        <tr>
            <td colspan="2" rowspan="3" style="border: 1px solid black;">
                {{-- El logo se colocará aquí con Drawing --}}
            </td>
            <td colspan="6" rowspan="2"
                style="text-align: center; font-weight: bold; font-size: 14px; border: 1px solid black;">
                REPORTE DE DE INTERRUPCIONES DEL SERVICIO POR CAUSAS ATRIBUIBLES A LA ENTIDAD                
            </td>
            <td rowspan="2" style="text-align: center; font-weight: bold; border: 1px solid black;">CÓDIGO</td>
        </tr>

        <!-- FILA 2: Espaciado -->
        <tr></tr>

        <!-- FILA 3: Info de Centro MAC y Mes -->
        <tr>
            <td colspan="1" style="font-weight: bold; border: 1px solid black; text-align: center;">Centro MAC:</td>
            <td colspan="3" style="border: 1px solid black; text-align: center;">{{ $nombreMac }}</td>
            <td colspan="1" style="font-weight: bold; border: 1px solid black; text-align: center;">MES:</td>
            <td colspan="1" style="border: 1px solid black; text-align: center;">{{ strtoupper($nombreMes) }}</td>
            <td colspan="1" style="font-weight: bold; border: 1px solid black; text-align: center;">ANS4</td>
        </tr>
        <tr></tr>
        <tr>
            <td rowspan="3" style="font-weight: bold; vertical-align: top; text-align: right;">
                ESTADO
            </td>
            <td style="border: 1px solid black;" colspan="1">CERRADO</td>
            <td colspan="3">
                (Para los casos en los que se envió un oficio a la entidad involucrada y se recibió una respuesta que
                subsanaba la observación advertida)
            </td>
            <td rowspan="3" style="font-weight: bold; vertical-align: top; text-align: right;">
                (**) TIPIFICACIÓN DE LA OBSERVACIÓN
            </td>
            <td colspan="1">Interrupción tipo A:</td>
            <td colspan="2">Interrupciones vinculadas al software</td>
        </tr>
        <tr>
            {{-- <td style="border: 1px solid black;">Subsanado sin documento</td>
            <td colspan="3">
                (Para los casos en los que no fue necesario el envío de un oficio a la entidad involucrada y la
                observación se levantó a través de una o más gestiones)
            </td> --}}
            <td style="border: 1px solid black;">A1</td>
            <td colspan="2">Falla de configuración (internet, drivers, etc.)</td>
        </tr>
        <tr>
            <td style="border: 1px solid black;">ABIERTO</td>
            <td colspan="3">
                (Para los casos donde la observación advertida no se ha solucionado a pesar de realizar gestiones
                internas y/o enviar documentos a la entidad involucrada)
            </td>
            <td style="border: 1px solid black;">A2</td>
            <td colspan="2">Problema en el control de accesos (acceso a cuentas, por
                ejemplo)</td>
        </tr>
        <tr>
            <td colspan="6" style="border: none;"></td>
            <td style="border: 1px solid black;">A3</td>
            <td colspan="2">Licencia no actualizada</td>
        </tr>
        <tr>
            <td colspan="6" style="border: none;"></td>
            <td style="border: 1px solid black;">A4</td>
            <td colspan="2">Falla del sistema</td>
        </tr>
        <tr>
            <td colspan="6" style="border: none;"></td>
            <td colspan="2">Observación tipo B:</td>
            <td>Interrupciones vinculadas al hardware</td>
        </tr>
        <tr>
            <td colspan="6" style="border: none;"></td>
            <td style="border: 1px solid black;">B1</td>
            <td colspan="2">Mantenimiento de equipos</td>
        </tr>
        <tr>
            <td colspan="6" style="border: none;"></td>
            <td style="border: 1px solid black;">B2</td>
            <td colspan="2">Problemas en el funcionamiento de equipos</td>
        </tr>
        <tr>
            <td colspan="6" style="border: none;"></td>
            <td style="border: 1px solid black;">B3</td>
            <td colspan="2">Falta de insumos (tóner, corte del fluido eléctrico, entre otros)</td>
        </tr>
        <tr>
            <td colspan="6" style="border: none;"></td>
            <td style="border: 1px solid black;">B4</td>
            <td colspan="2">Corte en el servicio de internet (router, switch, otros)</td>
        </tr>
        <tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <!-- FILA 4: Títulos -->
        <tr style="background-color: #4F81BD; color: white; font-weight: bold;">
            <th style="border: 1px solid black;">N°</th>
            <th style="border: 1px solid black;">ENTIDAD INVOLUCRADA</th>
            <th style="border: 1px solid black;">SERVICIO INVOLUCRADO</th>
            <th style="border: 1px solid black;">TIPIFICACIÓN DE LA INTERRUPCIÓN (**)</th>
            <th style="border: 1px solid black;">DESCRIPCIÓN DE LA INTERRUPCIÓN</th>
            <th style="border: 1px solid black;" colspan="2">DESCRIPCIÓN DE LAS ACCIONES INMEDIATAS</th>
            <th style="border: 1px solid black;" colspan="2">ACCIÓN CORRECTIVA Y/O PREVENTIVA</th>
            <th style="border: 1px solid black;">FECHA DE INICIO DE LA INTERRUPCIÓN</th>
            <th style="border: 1px solid black;">HORA DE INICIO DE LA INTERRUPCIÓN</th>
            <th style="border: 1px solid black;">FECHA FIN DE INTERRUPCIÓN</th>
            <th style="border: 1px solid black;">HORA FIN DE INTERRUPCIÓN</th>
            <th style="border: 1px solid black;">ESTADO (*)</th>            
        </tr>
    </thead>
    <tbody>
        @foreach ($interrupcion as $i => $obs)
            <tr>
                <td style="border: 1px solid black;">{{ $i + 1 }}</td>
                <td style="border: 1px solid black;">{{ $obs->entidad->ABREV_ENTIDAD ?? '' }}</td>
                <td style="border: 1px solid black;">{{ $obs->servicio_involucrado ?? '' }}</td>
                <td style="border: 1px solid black;">
                    {{ $obs->tipoIntObs->tipo ?? '' }} {{ $obs->tipoIntObs->numeracion ?? '' }}
                </td>
                <td style="border: 1px solid black;">{{ $obs->descripcion }}</td>
                <td style="border: 1px solid black;" colspan="2">{{ $obs->descripcion_accion }}</td>
                <td style="border: 1px solid black;" colspan="2">{{ $obs->accion_correctiva }}</td>
                <td style="border: 1px solid black;">
                    {{ \Carbon\Carbon::parse($obs->fecha_inicio)->format('d-m-Y') }}
                </td>
                <td style="border: 1px solid black;">
                    {{ $obs->hora_inicio }}
                </td>
                <td style="border: 1px solid black;">
                    {{ \Carbon\Carbon::parse($obs->fecha_fin)->format('d-m-Y') }}
                </td>
                <td style="border: 1px solid black;">
                    {{ $obs->hora_fin }}
                </td>
                <td style="border: 1px solid black;">{{ $obs->estado }}</td>                
            </tr>
        @endforeach
    </tbody>
</table>
