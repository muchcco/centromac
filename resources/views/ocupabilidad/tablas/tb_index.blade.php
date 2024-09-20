<style>
    .progress-bar-custom {
        height: 30px; /* Ajusta este valor según el grosor deseado */
        font-size: 16px; /* Ajusta el tamaño de la fuente si es necesario */
    }
</style>

<table class="table table-hover table-bordered table-striped">
    <thead>
        <tr>
            <th>Item</th>
            <th>Módulo</th>
            <th>Entidad</th>
            <th>Días Marcados</th>
            <th>Días Hábiles</th>
            <th>Porcentaje</th>
            <th>Ocupabilidad</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($resultados as $resultado)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $resultado['modulo']->N_MODULO }}</td>
                <td>{{ $resultado['entidad'] }} - {{ $resultado['mac'] }}</td>
                <td>{{ $resultado['diasMarcados'] }}</td>
                <td>{{ $resultado['diasHabiles'] }}</td>
                <td>{{ number_format($resultado['porcentaje'], 2) }}%</td>
                <td>
                    <!-- Barra de progreso -->
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar 
                            @if($resultado['porcentaje'] < 80) bg-danger 
                            @elseif($resultado['porcentaje'] < 95) bg-warning 
                            @else bg-success 
                            @endif" 
                            role="progressbar" 
                            style="width: {{ $resultado['porcentaje'] }}%;" 
                            aria-valuenow="{{ $resultado['porcentaje'] }}" 
                            aria-valuemin="0" 
                            aria-valuemax="100">
                            {{ number_format($resultado['porcentaje'], 2) }}%
                        </div>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
