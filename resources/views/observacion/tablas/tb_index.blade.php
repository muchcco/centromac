<table class="table table-hover table-bordered table-striped" id="table_observaciones">
    <thead class="tenca">
        <tr>
            <th>N°</th>
            <th>Centro MAC</th>
            <th>Fecha Observación</th>
            <th>Tipificación</th>
            <th>Entidad</th>
            <th>Descripción</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($observaciones as $i => $observacion)
            <tr>
                <td>{{ $i + 1 }}</td>

                <td>{{ $observacion->centroMac->nombre_mac ?? 'No asignado' }}</td>

                <td>{{ \Carbon\Carbon::parse($observacion->fecha_observacion)->format('d-m-Y') }}</td>

                <td>
                    {{ $observacion->tipoIntObs->tipo ?? '' }}
                    {{ $observacion->tipoIntObs->numeracion ?? '' }} -
                    {{ $observacion->tipoIntObs->nom_tipo_int_obs ?? '' }}
                </td>

                <td>{{ $observacion->entidad->ABREV_ENTIDAD ?? 'No asignado' }}</td>
                <td class="text-wrap" style="max-width: 300px;">
                    {{ Str::limit($observacion->descripcion ?? 'Sin descripción', 100, '...') }}
                </td>
                <td>
                    @switch($observacion->estado)
                        @case('SUBSANADO')
                            <span class="badge bg-success">Subsanado</span>
                        @break

                        {{--                        @case('SUBSANADO SIN DOCUMENTO')
                            <span class="badge bg-success">Subsanado sin Documento</span>
                        @break

                        @case('NO APLICA')
                            <span class="badge bg-secondary">No Aplica</span>
                        @break
 --}}

                        @default
                            <span class="badge bg-danger">{{ $observacion->estado }}</span>
                    @endswitch
                </td>

                <td>
                    <button class="nobtn bandejTool" data-tippy-content="Ver Detalles"
                        onclick="btnVerObservacion('{{ $observacion->id_observacion }}')">
                        <i class="las la-eye text-primary font-16"></i>
                    </button>

                    @role('Administrador')
                        <button class="nobtn bandejTool" data-tippy-content="Editar Observación"
                            onclick="btnEditarObservacion('{{ $observacion->id_observacion }}')">
                            <i class="las la-pen text-secondary font-16 text-success"></i>
                        </button>
                        <button class="nobtn bandejTool" data-tippy-content="Eliminar Observación"
                            onclick="btnEliminarObservacion('{{ $observacion->id_observacion }}')">
                            <i class="las la-trash-alt text-secondary font-16 text-danger"></i>
                        </button>
                        <button class="nobtn bandejTool" data-tippy-content="Subsanar"
                            onclick="btnSubsanarObservacion('{{ $observacion->id_observacion }}')">
                            <i class="las la-file-medical text-success font-16"></i>
                        </button>
                    @endrole

                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<script>
    $(document).ready(function() {

        $('#table_observaciones').DataTable({
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
