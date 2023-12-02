<table class="table table-hover table-bordered table-striped" id="table_almacen">
    <thead class="tenca">
        <tr>
            <th class="th" width="50px">N°</th>
            <th class="th">Nombre y Apellidos</th>
            <th class="th">Documento</th>
            <th class="th">Entidad</th>
            <th class="th">N° de bienes asignados</th>
            <th class="th">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($query as $i =>$que)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $que->NOMBREU }}</td>
                <td>{{ $que->NUM_DOCUMENTO }}</td>
                <td>{{ $que->NOMBRE_ENTIDAD }}</td>
                <td class="text-center">
                    @if ($que->CONT_ASIG == NULL)
                        <span class="text-danger">0</span>
                    @else
                        {{ $que->CONT_ASIG }}
                    @endif
                </td>
                <td>
                    <a href="{{ route('asignacion.asignacion_inventario', $que->IDPERSONAL) }}" class="btn btn-info">Asignar bienes</a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<script>
$(document).ready(function() {

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
            { "width": "" }
        ]
    });
});
</script>