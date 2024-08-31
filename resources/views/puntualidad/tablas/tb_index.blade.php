<table class="table table-hover table-bordered table-striped" id="table_puntualidad">
    <thead class="tenca">
        <tr>
            <th width="50px">N°</th>
            <th>Módulo</th>
            <th>Entidad</th>
            <th>Días Puntuales</th>
            <th>Días Marcados</th>
            <th>Porcentaje</th>
            <th>Puntualidad</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item['modulo'] }}</td>
                <td>{{ $item['entidad'] }} - {{ $item['mac'] }}</td>
                <td>{{ $item['dias_puntuales'] }}</td>
                <td>{{ $item['dias_marcados'] }}</td>
                <td>{{ $item['porcentaje'] }}%</td>
                <td>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar
                            @if ($item['porcentaje'] < 80) bg-danger
                            @elseif ($item['porcentaje'] < 95) bg-warning
                            @else bg-success
                            @endif"
                            role="progressbar"
                            style="width: {{ $item['porcentaje'] }}%;"
                            aria-valuenow="{{ $item['porcentaje'] }}"
                            aria-valuemin="0"
                            aria-valuemax="100">
                            {{ $item['porcentaje'] }}%
                        </div>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<!-- 
<script>
    $(document).ready(function() {
        $('#table_puntualidad').DataTable({
            "responsive": true,
            "bLengthChange": true,
            "autoWidth": false,
            "searching": true,
            "info": true,
            "ordering": false,
            "language": { "url": "{{ asset('js/Spanish.json') }}" },
            "columns": [
                { "width": "5px" },
                { "width": "20%" },
                { "width": "20%" },
                { "width": "15%" },
                { "width": "15%" },
                { "width": "10%" },
                { "width": "15%" }
            ]
        });
    });
</script>
 -->