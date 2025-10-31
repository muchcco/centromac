<table style="width: 100%; border-collapse: collapse; font-size: 12px;">
    <thead>
        <!-- FILA 1: Espacio reservado para el logo -->
        <tr></tr>
        <tr>
            <td colspan="2" rowspan="3" style="border: 1px solid black; text-align: center;">
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
            <td style="font-weight: bold; border: 1px solid black; text-align: center;">Centro MAC:</td>
            <td colspan="3" style="border: 1px solid black; text-align: center;">{{ $nombreMac }}</td>
            <td style="font-weight: bold; border: 1px solid black; text-align: center;">MES:</td>
            <td style="border: 1px solid black; text-align: center;">{{ strtoupper($nombreMes) }}</td>
            <td style="font-weight: bold; border: 1px solid black; text-align: center;">ANS3</td>
        </tr>

        <tr></tr>

        <!-- LEYENDA DE ESTADOS -->
        <tr>
            <td style="font-weight: bold; vertical-align: top; text-align: right; padding: 4px; ">
                ESTADO
            </td>
            <td style="border: 1px solid black;  text-align: center;">ABIERTO</td>
            <td colspan="2" style="">

            </td>
            <td style="font-weight: bold; vertical-align: top; text-align: right; padding: 4px;">
                (**) TIPIFICACIÓN DEL INCUMPLIMIENTO
            </td>
            <td style="border: 1px solid black; text-align: center;">I1</td>
            <td colspan="3" style="border: 1px solid black;">
                INCUMPLIMIENTO AL HORARIO DE ATENCIÓN DEL CENTRO MAC (NO APLICA TARDANZAS NI FALTAS)
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="border: 1px solid black; text-align: center;">CERRADO</td>
            <td colspan="2"></td>
            <td></td>
            <td style="border: 1px solid black; text-align: center;">I2</td>
            <td colspan="3" style="border: 1px solid black;">
                INCUMPLIMIENTO DEL PROTOCOLO DE ATENCIÓN AL CIUDADANO (PARA EL SALUDO, DURANTE EL SERVICIO Y PARA LA
                DESPEDIDA) Y/O EN EL LISTADO DE SERVICIOS (ANS)
            </td>
        </tr>
        <tr>
           
            <td rowspan="5" colspan="5">

            </td>
            <td style="border: 1px solid black; text-align: center;">I3</td>
            <td colspan="3" style="border: 1px solid black;">
                INCUMPLIMIENTO DEL CÓDIGO DE VESTIMENTA, CONDUCTA LABORAL U OTRO
            </td>
        </tr>
        <tr>
            <td style="border: 1px solid black; text-align: center;">I4</td>
            <td colspan="3" style="border: 1px solid black;">
                FALTA DE INSUMOS FÍSICOS (NO TI) PARA LA OPERATIVIDAD DEL SERVICIO (COMO PAPEL, PASAPORTES, FORMATOS,
                CARNÉS Y OTROS SIMILARES – TÓNER NO APLICA PORQUE ES TI)
            </td>
        </tr>
        <tr>
           
            <td style="border: 1px solid black; text-align: center;">I5</td>
            <td colspan="3" style="border: 1px solid black;">
                CIERRE DEL SERVICIO POR FALTA DE CUPOS O AFORO
            </td>
        </tr>
        <tr>
            
            <td style="border: 1px solid black; text-align: center;">I6</td>
            <td colspan="3" style="border: 1px solid black;">
                HORARIO DIFERENCIADO
            </td>
        </tr>
        <tr>
           
            <td style="border: 1px solid black; text-align: center;">I7</td>
            <td colspan="3" style="border: 1px solid black;">
                OTROS
            </td>
        </tr>

        <tr></tr>

        <!-- FILA ENCABEZADOS DE TABLA -->
        <tr style="background-color: #1F4E79; color: white; font-weight: bold; text-align: center;">
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

    <tbody style="white-space: normal;">
        @foreach ($incumplimientos as $i => $inc)
            <tr style="vertical-align: top;">
                <td style="border: 1px solid black; text-align: center; white-space: nowrap; padding: 3px;">
                    {{ $i + 1 }}
                </td>
                <td style="border: 1px solid black; text-align: center; white-space: nowrap; padding: 3px;">
                    {{ $inc->entidad->ABREV_ENTIDAD ?? '' }}
                </td>
                <td style="border: 1px solid black; text-align: center; white-space: nowrap; padding: 3px;">
                    {{ $inc->tipoIntObs->tipo ?? '' }} {{ $inc->tipoIntObs->numeracion ?? '' }}
                </td>
                <td style="border: 1px solid black; text-align: justify; white-space: normal; padding: 4px;">
                    {{ $inc->descripcion }}
                </td>
                <td style="border: 1px solid black; text-align: justify; white-space: normal; padding: 4px;">
                    {{ $inc->descripcion_accion }}
                </td>
                <td style="border: 1px solid black; text-align: center; white-space: nowrap; padding: 3px;">
                    {{ \Carbon\Carbon::parse($inc->fecha_observacion)->format('d-m-Y') }}
                </td>
                <td style="border: 1px solid black; text-align: center; white-space: nowrap; padding: 3px;">
                    {{ $inc->fecha_solucion ? \Carbon\Carbon::parse($inc->fecha_solucion)->format('d-m-Y') : '-' }}
                </td>
                <td style="border: 1px solid black; text-align: center; white-space: nowrap; padding: 3px;">
                    {{ $inc->estado }}
                </td>
                <td style="border: 1px solid black; text-align: center; white-space: nowrap; padding: 3px;">
                    {{ $inc->responsableUsuario->name ?? '' }}
                </td>
            </tr>
        @endforeach
    </tbody>

</table>
