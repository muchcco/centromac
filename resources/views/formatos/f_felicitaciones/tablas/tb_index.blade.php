<table class="table table-hover table-bordered table-striped" id="table_asistencia">
    <thead class="tenca">
        <tr>
            <th class="text-white" width="50px">N°</th>
            <th class="text-white">Correlativo</th>
            <th class="text-white">Fecha de registro</th>
            <th class="text-white">Centro MAC</th>
            <th class="text-white">Entidad</th>            
            <th class="text-white">Asesor</th>
            <th class="text-white">Cuidadano</th>
            <th class="text-white">Documento</th>
            <th class="text-white">Descripcion</th>
            <th class="text-white">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($query as $i =>$que)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $que->CORRELATVIO }}</td>
                <td>{{ $que->R_FECHA }}</td>
                <td>{{ $que->NOMBRE_MAC }}</td>
                <td>{{ $que->ABREV_ENTIDAD }}</td>
                <td>{{ $que->ASESOR }}</td>
                <td>{{ $que->NOMBREU }}</td>
                <td>{{ $que->DOCUMENTO }}</td>
                <td>{{ $que->R_DESCRIPCION }}</td>
                <td>
                    
                </td>
        @endforeach
       
    </tbody>
</table>

<script>
$(document).ready(function() {

    $('#table_asistencia').DataTable({
        "responsive": true,
        "bLengthChange": true,
        "autoWidth": false,
        "searching": true,
        info: true,
        "ordering": false,
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
    tippy(".bandejTool", {
        allowHTML: true,
        followCursor: true,
    });
});

    

</script>