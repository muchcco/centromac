<table class="table table-hover table-bordered table-striped" id="table_formato2" style="border: 1px solid black;">
    <thead class="tenca">
        <tr>
            <th colspan="7" class="text-center" style="border: 1px solid black; font-size: 18px; font-weight: bold;">
                Indicador de Ocupabilidad de todos los MAC del Mes de {{ $mesNombre }}
            </th>
        </tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr>
            <th style="border: 1px solid black;">CENTRO MAC</th>
            <th style="border: 1px solid black;">MODULOS</th>
            <th style="border: 1px solid black;">ENTIDAD</th>
            <th style="border: 1px solid black;">DIAS MARCADOS</th>
            <th style="border: 1px solid black;">DIAS HABILES</th>
            <th style="border: 1px solid black;">PORCENTAJE</th>
            <th style="border: 1px solid black;">OCUPABILIDAD</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($dias as $dia)
            <tr>
                <td style="border: 1px solid black;">{{ $dia['centromac'] }}</td>
                <!-- Mostramos el nombre del Centro MAC -->
                <td style="border: 1px solid black;">{{ $dia['modulo'] }}</td> <!-- Mostramos el nombre del módulo -->
                <td style="border: 1px solid black;">{{ $dia['entidad'] }}</td> <!-- Mostramos el nombre del módulo -->
                <td style="border: 1px solid black;">{{ $dia['dias_marcados'] }}</td>
                <!-- Mostramos los días marcados -->
                <td style="border: 1px solid black;">{{ $dia['dias_habiles'] }}</td> <!-- Mostramos los días hábiles -->
                <td style="border: 1px solid black;">{{ $dia['porcentaje'] }}%</td> <!-- Mostramos el porcentaje -->
                <td style="border: 1px solid black;">
                    <div class="progress" style="height: 25px;">
                        <!-- Barra de progreso basada en el porcentaje calculado -->
                        <div class="progress-bar {{ $dia['porcentaje'] >= 95 ? 'bg-success' : ($dia['porcentaje'] >= 84 ? 'bg-warning' : 'bg-danger') }}"
                            role="progressbar" style="width: {{ $dia['porcentaje'] }}%"
                            aria-valuenow="{{ $dia['porcentaje'] }}" aria-valuemin="0" aria-valuemax="100">
                            {{ $dia['porcentaje'] }}%</div>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center" style="border: 1px solid black;">No hay datos disponibles</td>
                <!-- Mensaje cuando no haya datos -->
            </tr>
        @endforelse
    </tbody>
</table>

@php
// Función para obtener el nombre del mes basado en el valor numérico (1 = Enero, 2 = Febrero, etc.)
function getMonthName($month)
{
    $months = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre',
    ];

    return $months[$month] ?? 'Mes no válido';
}
@endphp
