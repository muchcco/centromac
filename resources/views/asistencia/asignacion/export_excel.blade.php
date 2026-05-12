<table>
    <tr>
        <th colspan="14" style="font-weight: bold; font-size: 16px;">REPORTE DE HORAS COMPENSABLES</th>
    </tr>
    <tr>
        <td colspan="2" style="font-weight: bold;">Centro MAC</td>
        <td colspan="4">{{ $nameMac }}</td>
        <td colspan="2" style="font-weight: bold;">Rango</td>
        <td colspan="6">{{ date('d/m/Y', strtotime($fechaInicio)) }} al {{ date('d/m/Y', strtotime($fechaFin)) }}</td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight: bold;">Horas compensables</td>
        <td colspan="2">{{ $summary['total_horas'] }}</td>
        <td colspan="2" style="font-weight: bold;">Dias con extra</td>
        <td colspan="2">{{ $summary['registros_extra'] }}</td>
        <td colspan="2" style="font-weight: bold;">Personas</td>
        <td colspan="2">{{ $summary['personas'] }}</td>
        <td style="font-weight: bold;">Registros</td>
        <td>{{ $summary['registros'] }}</td>
    </tr>
    <tr>
        <td colspan="14"></td>
    </tr>
    <tr>
        <th style="border: 1px solid black; background-color: #132842; color: white;">N</th>
        <th style="border: 1px solid black; background-color: #132842; color: white;">Centro MAC</th>
        <th style="border: 1px solid black; background-color: #132842; color: white;">Entidad</th>
        <th style="border: 1px solid black; background-color: #132842; color: white;">Modulo</th>
        <th style="border: 1px solid black; background-color: #132842; color: white;">Personal</th>
        <th style="border: 1px solid black; background-color: #132842; color: white;">DNI</th>
        <th style="border: 1px solid black; background-color: #132842; color: white;">Fecha</th>
        <th style="border: 1px solid black; background-color: #132842; color: white;">Ingreso programado</th>
        <th style="border: 1px solid black; background-color: #132842; color: white;">Salida programada</th>
        <th style="border: 1px solid black; background-color: #132842; color: white;">Ingreso real</th>
        <th style="border: 1px solid black; background-color: #132842; color: white;">Salida real</th>
        <th style="border: 1px solid black; background-color: #132842; color: white;">Marcaciones</th>
        <th style="border: 1px solid black; background-color: #132842; color: white;">Horas extra</th>
        <th style="border: 1px solid black; background-color: #132842; color: white;">Minutos extra</th>
    </tr>
    @forelse ($rows as $i => $row)
        <tr>
            <td style="border: 1px solid black;">{{ $i + 1 }}</td>
            <td style="border: 1px solid black;">{{ $row->nombre_mac }}</td>
            <td style="border: 1px solid black;">{{ $row->entidad }}</td>
            <td style="border: 1px solid black;">{{ $row->modulo }}</td>
            <td style="border: 1px solid black;">{{ $row->nombre_completo }}</td>
            <td style="border: 1px solid black;">{{ $row->NUM_DOC }}</td>
            <td style="border: 1px solid black;">{{ date('d/m/Y', strtotime($row->FECHA)) }}</td>
            <td style="border: 1px solid black;">{{ substr($row->ingreso_programado, 0, 5) }}</td>
            <td style="border: 1px solid black;">{{ substr($row->salida_programada, 0, 5) }}</td>
            <td style="border: 1px solid black;">{{ $row->asistencia_ingreso ? substr($row->asistencia_ingreso, 0, 5) : '-' }}</td>
            <td style="border: 1px solid black;">{{ $row->asistencia_salida ? substr($row->asistencia_salida, 0, 5) : '-' }}</td>
            <td style="border: 1px solid black;">{{ $row->total_marcaciones }}</td>
            <td style="border: 1px solid black;">{{ $row->horas_extra }}</td>
            <td style="border: 1px solid black;">{{ $row->minutos_extra }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="14" style="border: 1px solid black;">Sin datos para el rango seleccionado.</td>
        </tr>
    @endforelse
</table>
