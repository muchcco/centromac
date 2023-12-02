<table class="table table-hover table-bordered table-striped" id="table_almacen">
    <thead class="tenca">
        <tr>
            <th class="th" width="50px">N°</th>
            <th class="th">Código Interno PCM</th>
            <th class="th">Descripción</th>
            <th class="th">Marca</th>
            <th class="th">Modelo</th>
            <th class="th">Serie - Medida</th>
            <th class="th">Estado del bien</th>
            <th class="th">Ubicación  </th>
            <th class="th">Observación</th>
            <th class="th">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($query as $i =>$que)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $que->COD_INTERNO_PCM }}</td>
                <td>{{ $que->DESCRIPCION }}</td>
                <td>{{ $que->MARCA }}</td>
                <td>{{ $que->MODELO }}</td>
                <td>{{ $que->SERIE_MEDIDA }}</td>
                <td>
                    @if ($que->ESTADO_BIEN == NULL)
                        <button class="btn btn-info btn-small" onclick="ModalEstado('{{ $que->IDASIGNACION }}')">Agregar Estado</button>
                    @else
                        {{ $que->ESTADO_BIEN }}
                    @endif                    
                </td>
                <td>{{ $que->UBICACION_EQUIPOS }}</td>
                <td>
                    @if ($que->OBSERVACION == NULL)
                        <button class="btn btn-info btn-small" onclick="ModalObservacion('{{ $que->IDASIGNACION }}')">Agregar Observación</button>
                    @else
                        {{ $que->OBSERVACION }}
                    @endif  
                </td>
                <td class="text-center">
                    {{-- <a href="{{ route('asignacion.asignacion_inventario', $que->IDPERSONAL) }}" class="btn btn-info">Asignar bienes</a> --}}
                    <button class="nobtn bandejTool" data-tippy-content="Eliminar Registro" onclick="BtnElimnar('{{ $que->IDASIGNACION }}')"><i class="fas fa-trash text-danger"></i></button>
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
            { "width": "" }
        ]
    });
});
</script>