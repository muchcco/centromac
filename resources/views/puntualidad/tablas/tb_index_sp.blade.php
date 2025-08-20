<table class="table table-hover table-bordered table-striped" id="table_formato2">
    <thead class="tenca">
        <tr>
            <th>MÓDULO</th>
            <th>ENTIDAD</th>
            <th>DÍAS PUNTUALES</th>
            <th>DÍAS MARCADOS</th>
            <th>PORCENTAJE</th>
            <th>PUNTUALIDAD</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($resultados as $r)
            @php
                $diasMarcados = $r->DIAS_ASISTENCIA;
                $diasPuntuales = $r->PUNTUALES_816;
                $porcentaje = $r->PCT_PUNTUALIDAD_816 * 100;
                $barClass = $porcentaje >= 95 ? 'bg-success' : ($porcentaje >= 84 ? 'bg-warning' : 'bg-danger');
            @endphp
            <tr>
                <td>{{ $r->N_MODULO }}</td>
                <td>{{ $r->NOMBRE_ENTIDAD }} - {{ $r->NOMBRE_MAC }}</td>
                <td>{{ $diasPuntuales }}</td>
                <td>{{ $diasMarcados }}</td>
                <td>{{ number_format($porcentaje, 2) }}%</td>
                <td>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar {{ $barClass }}" role="progressbar"
                            style="width: {{ $porcentaje }}%" aria-valuenow="{{ $porcentaje }}" aria-valuemin="0"
                            aria-valuemax="100">
                            {{ number_format($porcentaje, 2) }}%
                        </div>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center">No hay datos disponibles</td>
            </tr>
        @endforelse
    </tbody>
</table>
