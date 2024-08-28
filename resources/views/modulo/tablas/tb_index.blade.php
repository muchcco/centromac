<table class="table table-hover table-bordered table-striped" id="table_modulos">
    <thead class="tenca">
        <tr>
            <th >N°</th>
            <th>Nombre del Módulo</th>
            <th>Entidad</th>
            <th>Fecha Inicio</th>
            <th>Fecha Fin</th>
            <th>Centro MAC</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($modulos as $i => $modulo)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $modulo->N_MODULO }}</td>
                <td>{{ $modulo->NOMBRE_ENTIDAD ? $modulo->NOMBRE_ENTIDAD  : 'no hay entidad' }}</td>
                <td>{{ $modulo->FECHAINICIO ? $modulo->FECHAINICIO : 'No hay datos' }}</td>
                <td>{{ $modulo->FECHAFIN ? $modulo->FECHAFIN : 'No hay datos' }}</td>
                <td>{{ $modulo->NOMBRE_MAC }}</td>
                <td>
                    <button class="nobtn bandejTool" data-tippy-content="Editar módulo" onclick="btnEditModulo('{{ $modulo->IDMODULO  }}')"><i class="las la-pen text-secondary font-16 text-success"></i></button>
                    <button class="nobtn bandejTool" data-tippy-content="Eliminar módulo" onclick="btnDeleteModulo('{{ $modulo->IDMODULO  }}')"><i class="las la-trash-alt text-secondary font-16 text-danger"></i></button>
                </td>
            </tr>
        @endforeach

    </tbody>
</table>

<script>
$(document).ready(function() {
    $('#table_modulos').DataTable({
        "responsive": true,
        "bLengthChange": false,
        "autoWidth": false,
        "searching": true,
        info: true,
        "ordering": false,
        language: {"url": "{{ asset('js/Spanish.json')}}"}, 
        "columns": [
            { "width": "5px" },
            { "width": "20%" },
            { "width": "15%" },
            { "width": "15%" },
            { "width": "15%" },
            { "width": "15%" },
            { "width": "15%" }
        ]
    });

    tippy(".bandejTool", {
        allowHTML: true,
        followCursor: true,
    });
});
</script>
