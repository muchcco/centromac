<table class="table table-hover table-bordered table-striped" id="table_incumplimientos">
    <thead class="tenca">
        <tr>
            <th>N°</th>
            <th>Centro MAC</th>
            <th>Fecha Incidente</th>
            <th>Tipificación</th>
            <th>Entidad</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($incumplimientos as $i => $incumplimiento)
            <tr>
                <td>{{ $i + 1 }}</td>

                <td>{{ $incumplimiento->centroMac->nombre_mac ?? 'No asignado' }}</td>

                <td>{{ \Carbon\Carbon::parse($incumplimiento->fecha_observacion)->format('d-m-Y') }}</td>

                <td>
                    {{ $incumplimiento->tipoIntObs->tipo ?? '' }}
                    {{ $incumplimiento->tipoIntObs->numeracion ?? '' }} -
                    {{ $incumplimiento->tipoIntObs->nom_tipo_int_obs ?? '' }}
                </td>

                <td>{{ $incumplimiento->entidad->ABREV_ENTIDAD ?? 'No asignado' }}</td>

                <td>
                    @if ($incumplimiento->estado === 'CERRADO')
                        <span class="badge bg-success">Cerrado</span>
                    @else
                        <span class="badge bg-danger">Abierto</span>
                    @endif
                </td>

                <td>
                    <!-- Ver siempre disponible -->
                    <button class="nobtn bandejTool" data-tippy-content="Ver Detalles"
                        onclick="btnVerIncumplimiento('{{ $incumplimiento->id_observacion }}')">
                        <i class="las la-eye text-primary font-16"></i>
                    </button>

                    @if (
                        $incumplimiento->estado === 'CERRADO' &&
                            !auth()->user()->hasRole(['Administrador', 'Monitor']))
                        <!-- Cerrado para usuarios comunes -->
                        <button class="nobtn bandejTool" data-tippy-content="Incumplimiento Cerrado" disabled>
                            <i class="las la-lock text-secondary font-16"></i>
                        </button>
                    @else
                        <!-- Editar -->
                        <button class="nobtn bandejTool" data-tippy-content="Editar Incumplimiento"
                            onclick="btnEditarIncumplimiento('{{ $incumplimiento->id_observacion }}')">
                            <i class="las la-pen text-success font-16"></i>
                        </button>

                        <!-- Eliminar -->
                        <button class="nobtn bandejTool" data-tippy-content="Eliminar Incumplimiento"
                            onclick="btnEliminarIncumplimiento('{{ $incumplimiento->id_observacion }}')">
                            <i class="las la-trash-alt text-danger font-16"></i>
                        </button>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<script>
    $(document).ready(function() {
        $('#table_incumplimientos').DataTable({
            "responsive": true,
            "bLengthChange": true,
            "pageLength": 20,
            "lengthMenu": [
                [10, 20, 40, -1],
                [10, 20, 40, "Todos"]
            ],
            "autoWidth": false,
            "searching": true,
            "ordering": true,
            "info": true,
            "language": {
                "url": "{{ asset('js/Spanish.json') }}"
            },
            "columns": [{
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
                },
                {
                    "width": ""
                },
                {
                    "width": ""
                }
            ]
        });

        tippy(".bandejTool", {
            allowHTML: true,
            followCursor: true
        });
    });
</script>
