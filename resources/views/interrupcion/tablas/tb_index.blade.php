<table class="table table-hover table-bordered table-striped" id="table_interrupciones">
    <thead class="tenca">
        <tr>
            <th width="50px">N°</th>
            <th>Centro MAC</th>
            <th>Fecha y Hora Inicio</th>
            <th>Tipificación</th>
            <th>Entidad</th>
            <th>Servicio</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($interrupciones as $i => $interrupcion)
            <tr>
                <td>{{ $i + 1 }}</td>

                <!-- Centro MAC -->
                <td>{{ $interrupcion->centroMac->nombre_mac ?? 'No asignado' }}</td>

                <!-- Fecha y Hora de Inicio -->
                <td>
                    {{ \Carbon\Carbon::parse($interrupcion->fecha_inicio)->format('d-m-Y') }}
                    -
                    {{ \Carbon\Carbon::parse($interrupcion->hora_inicio)->format('H:i') }}
                </td>
                <!-- Tipificación -->
                <td>
                    {{ $interrupcion->tipoIntObs->tipo ?? '' }}
                    {{ $interrupcion->tipoIntObs->numeracion ?? '' }} --
                    {{ $interrupcion->tipoIntObs->nom_tipo_int_obs ?? '' }}
                </td>

                <!-- Entidad -->
                <td>{{ $interrupcion->entidad->ABREV_ENTIDAD ?? 'No asignado' }}</td>

                <!-- Servicio Involucrado -->
                <td class="text-uppercase">{{ $interrupcion->servicio_involucrado }}</td>

                <!-- Estado -->
                <td>
                    @switch($interrupcion->estado)
                        @case('SUBSANADO')
                            <span class="badge bg-success">Subsanado</span>
                        @break

                       {{--  @case('SUBSANADO SIN DOCUMENTO')
                            <span class="badge bg-success">Subsanado sin Documento</span>
                        @break

                        @case('NO APLICA')
                            <span class="badge bg-success">No Aplica</span>
                        @break --}}

                        @default
                            <span class="badge bg-danger">{{ $interrupcion->estado }}</span>
                    @endswitch
                </td>

                <!-- Acciones -->
                <td>
                    @role('Administrador|Especialista TIC|Moderador')
                        <button class="nobtn bandejTool" data-tippy-content="Editar Interrupción"
                            onclick="btnEditarInterrupcion('{{ $interrupcion->id_interrupcion }}')">
                            <i class="las la-pen text-secondary font-16 text-success"></i>
                        </button>
                    @endrole
                    @role('Administrador|Especialista TIC|Moderador')
                        <button class="nobtn bandejTool" data-tippy-content="Eliminar Interrupción"
                            onclick="btnEliminarInterrupcion('{{ $interrupcion->id_interrupcion }}')">
                            <i class="las la-trash-alt text-secondary font-16 text-danger"></i>
                        </button>
                    @endrole

                    <button class="nobtn bandejTool" data-tippy-content="Subsanar"
                        onclick="btnSubsanarInterrupcion('{{ $interrupcion->id_interrupcion }}')">
                        <i class="las la-file-medical text-success font-16"></i>
                    </button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<script>
    $(document).ready(function() {

        $('#table_interrupciones').DataTable({
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
                },
                {
                    "width": ""
                }
            ],

            "columnDefs": [{
                    "targets": 0,
                    "className": "text-center"
                },
                {
                    "targets": 6,
                    "className": "text-center"
                },
                {
                    "targets": 7,
                    "orderable": false,
                    "searchable": false,
                    "className": "text-center"
                }
            ]
        });

        tippy(".bandejTool", {
            allowHTML: true,
            followCursor: true
        });

    });
</script>
