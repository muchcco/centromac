<table>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <th colspan="6">
            CONTROL DE ASISTENCIA<br>
            Centro MAC: {{ $nameMac }}
        </th>
        <td></td>
        <td></td>
        <td></td>
        <th>Inicio:</th>
        <td></td>
        <td>{{ $fechaInicioTexto }}</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td colspan="6"></td>
        <td></td>
        <td></td>
        <td></td>
        <th>Fin:</th>
        <td></td>
        <td>{{ $fechaFinTexto }}</td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <th>DNI</th>
        <th>APELLIDOS Y NOMBRES</th>
        <th>REG. LABORAL</th>
        <th>CENTRO MAC</th>
        <th>CARGO</th>
        <th>DIA</th>
        <th>FECHA</th>
        <th>Hora Ingreso Programada</th>
        <th>Hora de Ingreso registrado</th>
        <th>Hora Salida Programada</th>
        <th>Hora de salida registrado</th>
        <th>Horas Programadas</th>
        <th>Horas trabajadas</th>
        <th>Observaciones</th>
    </tr>
    @forelse ($rows as $row)
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td>{{ $row->dni }}</td>
            <td>{{ $row->nombre_completo }}</td>
            <td>{{ $row->regimen_laboral }}</td>
            <td>{{ $row->centro_mac }}</td>
            <td>{{ $row->cargo }}</td>
            <td>{{ $row->dia }}</td>
            <td>{{ $row->fecha_excel }}</td>
            <td>{{ $row->ingreso_programado }}</td>
            <td>{{ $row->ingreso_real }}</td>
            <td>{{ $row->salida_programada }}</td>
            <td>{{ $row->salida_real }}</td>
            <td>{{ $row->horas_programadas }}</td>
            <td>{{ $row->horas_trabajadas }}</td>
            <td>{{ $row->observaciones }}</td>
        </tr>
    @empty
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="14">Sin datos para el rango seleccionado.</td>
        </tr>
    @endforelse
</table>
