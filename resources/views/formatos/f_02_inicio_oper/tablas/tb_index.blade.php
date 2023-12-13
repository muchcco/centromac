<table class="table table-hover table-bordered table-striped" id="table_formato">
    <tbody>
        <tr>
            <td >Supervisor(a)</td>
            <td ></td> 
            <td>Semana</td>
            <td></td>
        </tr>
    </tbody>
</table>

<div class="col-12">
    @if (count($resultado) > 0)
        <table class="table table-hover table-bordered table-striped">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>DESCRIPCION_F</th>
                    @foreach ($fechas as $fecha)
                        <th>{{ $fecha }}</th>
                        <th>{{ $fecha }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($resultado as $i => $item)
                    <tr>
                        <td>{{ $i + 1}}</td>
                        <td>{{ $item->DESCRIPCION_F }}</td>
                        @foreach ($fechas as $fecha)
                            <td>{{ $item["$fecha"] ?? '' }}</td>
                            <td>{{ $item["$fecha"] ?? '' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No se encontraron resultados.</p>
    @endif
</div>

<script>



</script>