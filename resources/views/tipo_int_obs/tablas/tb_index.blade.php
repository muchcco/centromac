<table class="table table-hover table-bordered table-striped" id="table_tipo_obs">
    <thead class="tenca">
        <tr>
            <th width="50px">N°</th>
            <th>Tipo de Registro</th> <!-- tipo_obs -->
            <th>Tipo</th> <!-- A, B o C -->
            <th>Numeración</th>
            <th>Nombre del Tipificación</th>
            <th>Estado</th> <!-- Nueva columna para status -->
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($tipos as $i => $tipo)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $tipo->tipo_obs }}</td>
                <td>{{ $tipo->tipo }}</td>
                <td>{{ $tipo->numeracion }}</td>
                <td>{{ $tipo->nom_tipo_int_obs }}</td>

                <!-- Mostrar Status: 1=Activo, 2=Inactivo -->
                <td>
                    @if ($tipo->status == 1)
                        <span class="badge bg-success">Activo</span>
                    @else
                        <span class="badge bg-danger">Inactivo</span>
                    @endif
                </td>

                <td>
                    @role('Administrador|Moderador')
                        <button class="nobtn bandejTool" data-tippy-content="Editar tipo"
                            onclick="btnEditarTipoObs('{{ $tipo->id_tipo_int_obs }}')">
                            <i class="las la-pen text-secondary font-16 text-success"></i>
                        </button>
                    @endrole

                    @role('Administrador|Moderador')
                        <button class="nobtn bandejTool" data-tippy-content="Eliminar tipo"
                            onclick="btnEliminarTipoObs('{{ $tipo->id_tipo_int_obs }}')">
                            <i class="las la-trash-alt text-secondary font-16 text-danger"></i>
                        </button>
                    @endrole

                    @role('Administrador|Moderador')
                        <button class="nobtn bandejTool" data-tippy-content="Cambiar estado"
                            onclick="btnToggleStatusTipoObs('{{ $tipo->id_tipo_int_obs }}')">
                            <i class="las la-sync text-secondary font-16 text-info"></i>
                        </button>
                    @endrole

                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<script>
    $(document).ready(function() {
        $('#table_tipo_obs').DataTable({
            responsive: true,
            bLengthChange: true,
            autoWidth: false,
            searching: true,
            info: true,
            ordering: false,
            language: {
                "url": "{{ asset('js/Spanish.json') }}"
            },
            columns: [{
                    "width": "5px"
                },
                {
                    "width": ""
                },
                {
                    "width": ""
                },
                {
                    "width": ""
                },
                {
                    "width": ""
                },
                {
                    "width": ""
                }, // Para el estado
                {
                    "width": ""
                } // Acciones
            ]
        });

        tippy(".bandejTool", {
            allowHTML: true,
            followCursor: true,
        });
    });
</script>
