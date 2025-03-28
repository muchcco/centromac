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
                REPORTE DE OBSERVACIONES
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
            <td colspan="1" style="font-weight: bold; border: 1px solid black; text-align: center;">ANS3</td>
        </tr>
        <tr></tr>
        <tr>
            <td rowspan="3" style="font-weight: bold; vertical-align: top; text-align: right;">
                ESTADO
            </td>
            <td style="border: 1px solid black;" colspan="1">Subsanado con documento</td>
            <td colspan="3">
                (Para los casos en los que se envió un oficio a la entidad involucrada y se recibió una respuesta que
                subsanaba la observación advertida)
            </td>
            <td rowspan="3" style="font-weight: bold; vertical-align: top; text-align: right;">
                (**) TIPIFICACIÓN DE LA OBSERVACIÓN
            </td>
            <td colspan="1">Observación tipo A:</td>
            <td colspan="2">Incumplimiento relacionado a los indicadores de ANS</td>
        </tr>
        <tr>
            <td style="border: 1px solid black;">Subsanado sin documento</td>
            <td colspan="3">
                (Para los casos en los que no fue necesario el envío de un oficio a la entidad involucrada y la
                observación se levantó a través de una o más gestiones)
            </td>
            <td style="border: 1px solid black;">A1</td>
            <td colspan="2">Incumplimiento a la puntualidad en la ocupación de los módulos</td>
        </tr>
        <tr>
            <td style="border: 1px solid black;">No subsanado</td>
            <td colspan="3">
                (Para los casos donde la observación advertida no se ha solucionado a pesar de realizar gestiones
                internas y/o enviar documentos a la entidad involucrada)
            </td>
            <td style="border: 1px solid black;">A2</td>
            <td colspan="2">Incumplimiento a la Ocupabilidad de los módulos</td>
        </tr>
        <tr>
            <td colspan="6" style="border: none;"></td>
            <td style="border: 1px solid black;">A3</td>
            <td colspan="2">Incumplimiento del protocolo de atención al ciudadano</td>
        </tr>
        <tr>
            <td colspan="6" style="border: none;"></td>
            <td colspan="2">Observación tipo B:</td>
            <td></td>
        </tr>
        <tr>
            <td colspan="6" style="border: none;"></td>
            <td style="border: 1px solid black;">B1</td>
            <td colspan="2">Falta de insumos físicos (no TI)</td>
        </tr>
        <tr>
            <td colspan="6" style="border: none;"></td>
            <td style="border: 1px solid black;">B2</td>
            <td colspan="2">Cierre del servicio por falta de cupos o aforo</td>
        </tr>
        <tr>
            <td colspan="6" style="border: none;"></td>
            <td colspan="2">Observación tipo C:</td>
            <td></td>
        </tr>
        <tr>
            <td colspan="6" style="border: none;"></td>
            <td style="border: 1px solid black;">C</td>
            <td colspan="2">
                Falta de cumplimiento de algún protocolo de conducta laboral y/o procedimiento del servicio,
                relacionados a la información y al trato que se brinda al ciudadano
            </td>
        </tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <!-- FILA 4: Títulos -->
        <tr style="background-color: #4F81BD; color: white; font-weight: bold;">
            <th style="border: 1px solid black;">N°</th>
            <th style="border: 1px solid black;">ENTIDAD</th>
            <th style="border: 1px solid black;">TIPIFICACIÓN DE LA OBSERVACIÓN (**)</th>
            <th style="border: 1px solid black;">DESCRIPCIÓN DE LA OBSERVACIÓN</th>
            <th style="border: 1px solid black;">DESCRIPCIÓN DE LAS ACCIONES INMEDIATAS</th>
            <th style="border: 1px solid black;">FECHA EN LA QUE SE HIZO LA OBSERVACIÓN</th>
            <th style="border: 1px solid black;">FECHA DE SOLUCIÓN DE LA OBSERVACIÓN</th>
            <th style="border: 1px solid black;">ESTADO (*)</th>
            <th style="border: 1px solid black;">RESPONSABLE</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($observaciones as $i => $obs)
            <tr>
                <td style="border: 1px solid black;">{{ $i + 1 }}</td>
                <td style="border: 1px solid black;">{{ $obs->entidad->ABREV_ENTIDAD ?? '' }}</td>
                <td style="border: 1px solid black;">
                    {{ $obs->tipoIntObs->tipo ?? '' }} {{ $obs->tipoIntObs->numeracion ?? '' }}
                </td>
                <td style="border: 1px solid black;">{{ $obs->descripcion }}</td>
                <td style="border: 1px solid black;">{{ $obs->descripcion_accion }}</td>
                <td style="border: 1px solid black;">
                    {{ \Carbon\Carbon::parse($obs->fecha_observacion)->format('d-m-Y') }}
                </td>
                <td style="border: 1px solid black;">
                    {{ $obs->fecha_solucion ? \Carbon\Carbon::parse($obs->fecha_solucion)->format('d-m-Y') : '-' }}
                </td>
                <td style="border: 1px solid black;">{{ $obs->estado }}</td>
                <td style="border: 1px solid black;">{{ $obs->responsableUsuario->name ?? '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
