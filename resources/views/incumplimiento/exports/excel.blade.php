<table>
    <thead>
        <!-- FILA 1: Espacio reservado para el logo -->
        <tr></tr>
        <tr>
            <td colspan="2" rowspan="3" style="border: 1px solid black;">
                {{-- Logo se coloca aquí con Drawing --}}
            </td>
            <td colspan="6" rowspan="2"
                style="text-align: center; font-weight: bold; font-size: 14px; border: 1px solid black;">
                REPORTE DE INCUMPLIMIENTOS
            </td>
            <td rowspan="2" style="text-align: center; font-weight: bold; border: 1px solid black;">CÓDIGO</td>
        </tr>

        <!-- Fila espaciado -->
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

        <!-- LEYENDA DE ESTADOS -->
        <tr>
            <td rowspan="2" style="font-weight: bold; vertical-align: top; text-align: right;">
                ESTADO
            </td>
            <td style="border: 1px solid black;">ABIERTO</td>
            <td colspan="3">(Incumplimiento registrado y aún sin resolución)</td>
            <td rowspan="2" style="font-weight: bold; vertical-align: top; text-align: right;">
                (**) TIPIFICACIÓN DEL INCUMPLIMIENTO
            </td>
            <td style="border: 1px solid black;">I1</td>
            <td colspan="2">Incumplimiento a la puntualidad</td>
        </tr>
        <tr>
            <td style="border: 1px solid black;">CERRADO</td>
            <td colspan="3">(Incumplimiento que ya fue atendido o resuelto)</td>
            <td style="border: 1px solid black;">I2</td>
            <td colspan="2">Incidente a la vestimenta o protocolo</td>
        </tr>

        <tr></tr>
        <!-- FILA ENCABEZADOS DE TABLA -->
        <tr style="background-color: #9C0006; color: white; font-weight: bold;">
            <th style="border: 1px solid black;">N°</th>
            <th style="border: 1px solid black;">ENTIDAD</th>
            <th style="border: 1px solid black;">TIPIFICACIÓN DEL INCIDENTE (**)</th>
            <th style="border: 1px solid black;">DESCRIPCIÓN DEL INCIDENTE</th>
            <th style="border: 1px solid black;">DESCRIPCIÓN DE LAS ACCIONES</th>
            <th style="border: 1px solid black;">FECHA DEL INCIDENTE</th>
            <th style="border: 1px solid black;">FECHA DE CIERRE</th>
            <th style="border: 1px solid black;">ESTADO</th>
            <th style="border: 1px solid black;">RESPONSABLE</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($incumplimientos as $i => $inc)
            <tr>
                <td style="border: 1px solid black;">{{ $i + 1 }}</td>
                <td style="border: 1px solid black;">{{ $inc->entidad->ABREV_ENTIDAD ?? '' }}</td>
                <td style="border: 1px solid black;">
                    {{ $inc->tipoIntObs->tipo ?? '' }} {{ $inc->tipoIntObs->numeracion ?? '' }}
                </td>
                <td style="border: 1px solid black;">{{ $inc->descripcion }}</td>
                <td style="border: 1px solid black;">{{ $inc->descripcion_accion }}</td>
                <td style="border: 1px solid black;">
                    {{ \Carbon\Carbon::parse($inc->fecha_observacion)->format('d-m-Y') }}
                </td>
                <td style="border: 1px solid black;">
                    {{ $inc->fecha_solucion ? \Carbon\Carbon::parse($inc->fecha_solucion)->format('d-m-Y') : '-' }}
                </td>
                <td style="border: 1px solid black;">{{ $inc->estado }}</td>
                <td style="border: 1px solid black;">{{ $inc->responsableUsuario->name ?? '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
