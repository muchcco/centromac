<table class="table table-hover table-bordered table-striped" id="table_formato2">
    <thead class="tenca">
        <tr>
            <th>CENTRO MAC</th>
            <th>MODULOS</th>
            <th>ENTIDAD</th>
            <th>DIAS MARCADOS</th>
            <th>DIAS HABILES</th>
            <th>PORCENTAJE</th>
            <th>OCUPABILIDAD</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($dias as $dia)
            <tr>
                <td>{{ $dia['centromac'] }}</td>  
                <td>{{ $dia['modulo'] }}</td>    
                <td>{{ $dia['entidad'] }}</td>    
                <td>{{ $dia['dias_marcados'] }}</td>  
                <td>{{ $dia['dias_habiles'] }}</td>  
                <td>{{ $dia['porcentaje'] }}%</td>   
                <td>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar {{ $dia['porcentaje'] >= 95 ? 'bg-success' : ($dia['porcentaje'] >= 84 ? 'bg-warning' : 'bg-danger') }}"
                            role="progressbar" style="width: {{ $dia['porcentaje'] }}%"
                            aria-valuenow="{{ $dia['porcentaje'] }}" aria-valuemin="0" aria-valuemax="100">
                            {{ $dia['porcentaje'] }}%</div>
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
