<table class="table table-hover table-bordered table-striped" id="table_modelo">
    <thead>
        <tr>
            <th>ID</th>
            <th>MARCA</th>
            <th>MODELO</th>
            <th>ACCIÓN</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($modelo as $m)
            <tr>
                <td>{{ $m->IDMODELO }}</td>
                <td>{{ $m->NOMBRE_MARCA }}</td>
                <td>{{ $m->NOMBRE_MODELO }}</td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="eliminarModelo({{ $m->IDMODELO }})">
                        <i class="las la-trash-alt"></i> Eliminar
                    </button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center">No hay modelos disponibles.</td>
            </tr>
        @endforelse
    </tbody>
</table>


<script>
    $(document).ready(function() {
    
        $('#table_modelo').DataTable({
            "responsive": true,
            "bLengthChange": false,
            "autoWidth": false,
            "searching": true,
            info: true,
            "ordering": false,
            language: {"url": "{{ asset('js/Spanish.json')}}"}, 
            "columns": [
                { "width": "" },            
                { "width": "" },
                { "width": "" },
                { "width": "" }
            ]
        });
    });
    </script>