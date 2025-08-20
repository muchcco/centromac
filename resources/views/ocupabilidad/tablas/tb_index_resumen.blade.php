<table class="table table-hover table-bordered table-striped" id="table_formato2">
    <thead class="tenca">
        <tr>
            <th>MODULO</th>
            <th>ENTIDAD</th>
            <th>DIAS MARCADOS</th>
            <th>DIAS HABILES</th>
            <th>PORCENTAJE</th>
            <th>OCUPABILIDAD</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($resultados as $r)
            @php
                $porcentaje = $r->DIAS_HABILES > 0 ? ($r->DIAS_ASISTENCIA / $r->DIAS_HABILES) * 100 : 0;
                $barClass = $porcentaje >= 95 ? 'bg-success' : ($porcentaje >= 84 ? 'bg-warning' : 'bg-danger');
            @endphp
            <tr>
                <td>{{ $r->N_MODULO }}</td>
                <td>{{ $r->NOMBRE_ENTIDAD }} - {{ $r->NOMBRE_MAC }}</td>
                <td>{{ $r->DIAS_ASISTENCIA }}</td>
                <td>{{ $r->DIAS_HABILES }}</td>
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
