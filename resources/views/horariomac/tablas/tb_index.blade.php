<table class="table table-hover table-bordered table-striped" id="table_horarios">
    <thead class="tenca">
        <tr>
            <th>N°</th>
            <th>Centro MAC</th>
            <th>Módulo - Entidad</th>
            <th>Hora Ingreso</th>
            <th>Hora Salida</th>
            <th>Fecha Inicio</th>
            <th>Fecha Fin</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($horarios as $i => $horario)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $horario->centroMac->NOMBRE_MAC }}</td> <!-- Relación con el centro MAC -->
                <td>{{ $horario->modulo ? $horario->modulo->N_MODULO : 'Todos' }} -{{ $horario->nombre_entidad }}</td>
                <td>{{ $horario->horaingreso ? $horario->horaingreso : 'No hay datos' }}</td>
                <td>{{ $horario->horasalida ? $horario->horasalida : 'No hay datos' }}</td>
                <td>{{ $horario->fechainicio ? $horario->fechainicio : 'No hay datos' }}</td>
                <td>{{ $horario->fechafin ? $horario->fechafin : 'No hay datos' }}</td>

                <!-- Relación con el módulo -->
                <td>
                    <button class="nobtn bandejTool" data-tippy-content="Editar horario"
                        onclick="btnEditHorario('{{ $horario->idhorario }}')"><i
                            class="las la-pen text-secondary font-16 text-success"></i></button>
                    <button class="nobtn bandejTool" data-tippy-content="Eliminar horario"
                        onclick="btnDeleteHorario('{{ $horario->idhorario }}')"><i
                            class="las la-trash-alt text-secondary font-16 text-danger"></i></button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<script>
    $(document).ready(function() {
        $('#table_horarios').DataTable({
            "responsive": true,
            "bLengthChange": true,
            "autoWidth": false,
            "searching": true,
            info: true,
            "ordering": true,
            language: {
                "url": "{{ asset('js/Spanish.json') }}"
            },
            "columns": [{
                    "width": "5%"
                },
                {
                    "width": "15%"
                },
                {
                    "width": "15%"
                },
                {
                    "width": "15%"
                },
                {
                    "width": "15%"
                },
                {
                    "width": "15%"
                },
                {
                    "width": "15%"
                },
                {
                    "width": "15%"
                }
            ]
        });

        tippy(".bandejTool", {
            allowHTML: true,
            followCursor: true,
        });
    });
</script>
