<div class="table-responsive">
    <table class="table table-hover table-bordered table-striped" id="table_formato2" style="width:100%">
        <thead class="tenca">
            <tr>
                <th>N°</th>
                <th>Centro MAC</th>
                <th>Entidad</th>
                <th>Colaborador</th>
                <th>Cargo</th>
                <th>Módulo</th>
                <th>DNI</th>
                <th>Fecha</th>
                <th>Día</th>
                <th>Ingreso</th>
                <th>Salida</th>
                <th>Estado</th>
                <th>Puntual</th>
                <th>Feriado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($detalle as $fila)
                <tr>
                    <td>{{ $fila['numero'] ?? '' }}</td>
                    <td>{{ $fila['centro_mac'] ?? '' }}</td>
                    <td>{{ $fila['entidad'] ?? '' }}</td>
                    <td>{{ $fila['colaborador'] ?? '' }}</td>
                    <td>{{ $fila['cargo'] ?? '' }}</td>
                    <td>{{ $fila['modulo'] ?? '' }}</td>
                    <td>{{ $fila['dni'] ?? '' }}</td>
                    <td>{{ !empty($fila['fecha']) ? \Carbon\Carbon::parse($fila['fecha'])->format('d-m-Y') : '' }}</td>
                    <td>{{ $fila['dia'] ?? '' }}</td>
                    <td>{{ $fila['ingreso'] ?? '' }}</td>
                    <td>{{ $fila['salida'] ?? '' }}</td>
                    <td>{{ $fila['estado'] ?? '' }}</td>
                    <td>{{ $fila['puntual'] ?? '' }}</td>
                    <td>{{ $fila['feriado'] ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
