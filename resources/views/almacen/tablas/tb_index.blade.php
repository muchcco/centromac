<table class="table table-hover table-bordered table-striped" id="table_almacen">
    <thead class="tenca">
        <tr>
            <th class="th" width="50px">N°</th>
            <th class="th">Orden de Compra</th>
            <th class="th">Código SBN</th>
            <th class="th">Código PROMSACE</th>
            <th class="th">Código Interno de PCM</th>
            <th class="th">Categoria del bien</th>
            <th class="th">Descripcion</th>
            <th class="th">Marca</th>
            <th class="th">Modelo</th>
            <th class="th">Serie / Medida</th>
            <th class="th">Ubicación</th>
            <th class="th">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($query as $i =>$que)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $que->OC }}</td>
                <td>{{ $que->COD_SBN }}</td>
                <td>{{ $que->COD_PRONSACE }}</td>
                <td>{{ $que->COD_INTERNO_PCM}}</td>
                <td>{{ $que->CODIGO_CATEGORIA}} - {{ $que->NOMBRE_CATEGORIA}}</td>
                <td>{{ $que->DESCRIPCION }}</td>
                <td>{{ $que->NOMBRE_MARCA }}</td>
                <td>{{ $que->NOMBRE_MODELO }}</td>
                <td>{{ $que->SERIE_MEDIDA }}</td>
                <td>{{ $que->UBICACION_EQUIPOS }}</td>
                <td>
                    <button class="nobtn bandejTool" data-tippy-content="Editar Item" onclick="btnEditarItem('{{ $que->IDALMACEN }}' )"><i class="las la-pen text-secondary font-16 text-success"></i></button>
                    <button class="nobtn bandejTool" data-tippy-content="Eliminar Item" onclick="btnElimnarItem('{{ $que->IDALMACEN }}' )"><i class="las la-trash-alt text-secondary font-16 text-danger"></i></button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<script>
$(document).ready(function() {
    tippy(".bandejTool", {
        allowHTML: true,
        followCursor: true,
    });

    $('#table_almacen').DataTable({
        "responsive": true,
        "bLengthChange": false,
        "autoWidth": false,
        "searching": true,
        info: true,
        "ordering": false,
        "dom":
                "<'row'" +
                "<'col-sm-12 d-flex align-items-center justify-conten-start'l>" +
                "<'col-sm-12 d-flex align-items-center justify-content-end'f>" +
                ">" +

                "<'table-responsive'tr>" +

                "<'row'" +
                "<'col-sm-12 col-md-12 d-flex align-items-center justify-content-center justify-content-md-start'i>" +
                "<'col-sm-12 col-md-12 d-flex align-items-center justify-content-center justify-content-md-end'p>" +
                ">",
        language: {"url": "{{ asset('js/Spanish.json')}}"}, 
        "columns": [
            { "width": "" },            
            { "width": "" },
            { "width": "" },
            { "width": "" },
            { "width": "" },
            { "width": "" },
            { "width": "" },
            { "width": "" },
            { "width": "" },
            { "width": "" },
            { "width": "" },
            { "width": "" }
        ]
    });
});
</script>