<table class="table table-hover table-bordered table-striped" id="table_asistencia">
    <thead class="tenca">
        <tr>
            <th class="text-white" width="50px">N°</th>
            <th class="text-white">Nombre del Centro MAC</th>
            <th class="text-white">Ubicación</th>
            <th class="text-white">Fecha de apertura</th>
            <th class="text-white">Fecha de inaguración</th>
            <th class="text-white">Estado</th>
            <th class="text-white">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($mac as $i =>$m)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>MAC - {{ $m->NOMBRE_MAC }}</td>
                <td><span class="bandejTool" data-tippy-content="Departamento: {{ $m->NAME_DEPARTAMENTO }}<br /> Provincia: {{ $m->NAME_PROVINCIA }} <br /> Distrito: {{ $m->NAME_DISTRITO }} <br /> Ubicación: {{ $m->DIRECCION_MAC }} ">{{ $m->NAME_DISTRITO }}</span> </td>
                <td>{{ $m->FECHA_APERTURA }}</td>
                <td>{{ $m->FECHA_INAGURACION }}</td>
                <td>{{ $m->FLAG }}</td>
                <td>
                    <a href="{{ route('configuracion.reg_tablas', $m->IDCENTRO_MAC) }}" class="nobtn bandejTool" data-tippy-content="Ingresar a permisos internos" ><i class="las la-external-link-alt text-secondary font-16 text-info"></i></a>
                    <button class="nobtn bandejTool" data-tippy-content="Editar datos del centro MAC {{ $m->NOMBRE_MAC }}" onclick="btnEditMac('{{ $m->IDCENTRO_MAC }}')"><i class="las la-pen text-secondary font-16 text-success"></i></button>
                    <button class="nobtn bandejTool" data-tippy-content="Dar de baja" onclick="btnDeleteMac('{{ $m->IDCENTRO_MAC }}')"><i class="las la-trash-alt text-secondary font-16 text-danger"></i></button>
                </td>
            </tr>
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
            { "width": "" }
        ]
    });
    tippy(".bandejTool", {
        allowHTML: true,
        followCursor: true,
    });
});

    

</script>