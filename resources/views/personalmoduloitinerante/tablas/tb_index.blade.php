<table class="table table-hover table-bordered table-striped" id="table_personal_modulo">
    <thead class="tenca">
        <tr>
            <th>N°</th>
            <th>DNI</th>
            <th>Nombre Completo</th>
            <th>Módulo</th>
            <th>Entidad</th>
            <th>Fecha Inicio</th>
            <th>Fecha Fin</th>
            <th>Centro MAC</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($personalModulos as $i => $personalModulo)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $personalModulo->num_doc }}</td>
                <td class="text-uppercase">{{ $personalModulo->NOMBRE }} {{ $personalModulo->APE_PAT }}
                    {{ $personalModulo->APE_MAT }}</td>
                <td>{{ $personalModulo->N_MODULO }}</td>
                <td  class="text-uppercase">{{ $personalModulo->NOMBRE_ENTIDAD }}</td>
                <td>{{ $personalModulo->fechainicio ? $personalModulo->fechainicio : 'No hay datos' }}</td>
                <td>{{ $personalModulo->fechafin ? $personalModulo->fechafin : 'No hay datos' }}</td>
                <td>{{ $personalModulo->NOMBRE_MAC }}</td>
                <td>
                    <button class="nobtn bandejTool" data-tippy-content="Editar Personal Módulo"
                        onclick="btnEditPersonalModulo('{{ $personalModulo->id }}')">
                        <i class="las la-pen text-secondary font-16 text-success"></i>
                    </button>
                    <button class="nobtn bandejTool" data-tippy-content="Eliminar Personal Módulo"
                        onclick="btnDeletePersonalModulo('{{ $personalModulo->id }}')">
                        <i class="las la-trash-alt text-secondary font-16 text-danger"></i>
                    </button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center">No hay datos disponibles.</td>
            </tr>
        @endforelse
    </tbody>
</table>
<script>
    $(document).ready(function() {
        // Inicializar DataTable
        $('#table_personal_modulo').DataTable({
            "responsive": true,
            "bLengthChange": true,
            "autoWidth": false,
            "searching": true,
            "info": true,
            "ordering": true,
            "language": {
                "url": "{{ asset('js/Spanish.json') }}" // Asegúrate de que la ruta del archivo JSON esté correcta
            },
            "columns": [{
                    "width": "5%"
                },
                {
                    "width": "10%"
                },
                {
                    "width": "20%"
                },
                {
                    "width": "15%"
                },
                {
                    "width": "15%"
                },
                {
                    "width": "10%"
                },
                {
                    "width": "10%"
                },
                {
                    "width": "10%"
                },
                {
                    "width": "15%"
                }
            ]
        });

        // Inicializar Tippy.js para los tooltips
        tippy(".bandejTool", {
            allowHTML: true,
            followCursor: true,
        });
    });
</script>
