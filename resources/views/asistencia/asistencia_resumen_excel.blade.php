<table>
    <tr>
        <th colspan="15" style="text-align:center; font-size:16px; font-weight:bold;">
            REPORTE DE ASISTENCIA RESUMIDA - {{ $name_mac }} ({{ strtoupper($nombreMES) }})
        </th>
    </tr>
</table>

<table style="border:1px solid black; margin-top:10px;">
    <thead>
        <tr style="background-color:#0B22B4; color:#fff;">
            <th style="border:1px solid black;">N°</th>
            <th style="border:1px solid black;">Centro MAC</th>
            <th style="border:1px solid black;">Entidad</th>
            <th style="border:1px solid black;">DATOS DEL COLABORADOR<br>(APELLIDOS Y NOMBRES)</th>
            <th style="border:1px solid black;">Cargo / Asesor(a)</th>
            <th style="border:1px solid black;">Número módulo</th>
            <th style="border:1px solid black;">DNI</th>
            <th style="border:1px solid black;">Fecha</th>
            <th style="border:1px solid black;">Hora de Ingreso</th>
            <th style="border:1px solid black;">Refrigerio Salida</th>
            <th style="border:1px solid black;">Refrigerio Ingreso</th>
            <th style="border:1px solid black;">Hora de Salida</th>
            <th style="border:1px solid black;">Ingreso Programado</th>
            <th style="border:1px solid black;">Salida Programada</th>
            <th style="border:1px solid black;">Observación</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($query as $i => $q)
            <tr>
                <td style="border:1px solid black;">{{ $i + 1 }}</td>
                <td style="border:1px solid black;">{{ $q->nombre_mac }}</td>
                <td style="border:1px solid black;">{{ $q->abrev_entidad }}</td>
                <td style="border:1px solid black;">{{ $q->nombreu }}</td>
                <td style="border:1px solid black;">{{ $q->status_modulo }}</td>
                <td style="border:1px solid black;">{{ $q->nombre_modulo }}</td>
                <td style="border:1px solid black;">{{ $q->n_dni }}</td>
                <td style="border:1px solid black;">{{ date('d/m/Y', strtotime($q->fecha_asistencia)) }}</td>
                <td style="border:1px solid black;">{{ $q->HORA_1 }}</td>
                <td style="border:1px solid black;">{{ $q->HORA_2 }}</td>
                <td style="border:1px solid black;">{{ $q->HORA_3 }}</td>
                <td style="border:1px solid black;">{{ $q->HORA_4 }}</td>
                <td style="border:1px solid black;">08:00</td>
                <td style="border:1px solid black;">17:00</td>
                <td style="border:1px solid black;">
                    @if ($q->contador_obs > 0)
                        Con Observación
                    @else
                        --
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
