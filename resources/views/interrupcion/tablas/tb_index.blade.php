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
                <td>{{ $interrupcion->fecha_inicio }} - {{ $interrupcion->hora_inicio }}</td>

                <!-- Tipificación -->
                <td>
                    {{ $interrupcion->tipoIntObs->tipo ?? '' }}
                    {{ $interrupcion->tipoIntObs->numeracion ?? '' }}
                </td>

                <!-- Entidad -->
                <td>{{ $interrupcion->entidad->nombre_entidad ?? 'No asignado' }}</td>

                <!-- Servicio Involucrado -->
                <td>{{ $interrupcion->servicio_involucrado }}</td>

                <!-- Estado -->
                <td>
                    @switch($interrupcion->estado)
                        @case('SUBSANADO CON DOCUMENTO')
                            <span class="badge bg-success">Subsanado con Documento</span>
                            @break

                        @case('SUBSANADO SIN DOCUMENTO')
                            <span class="badge bg-success">Subsanado sin Documento</span>
                            @break

                        @case('NO APLICA')
                            <span class="badge bg-success">No Aplica</span>
                            @break

                        @default
                            <span class="badge bg-danger">{{ $interrupcion->estado }}</span>
                    @endswitch
                </td>

                <!-- Acciones -->
                <td>
                    @role('Administrador|Especialista TIC|Monitor')
                    <button class="nobtn bandejTool" data-tippy-content="Editar Interrupción"
                        onclick="btnEditarInterrupcion('{{ $interrupcion->id_interrupcion }}')">
                        <i class="las la-pen text-secondary font-16 text-success"></i>
                    </button>
                    @endrole
                    @role('Administrador|Especialista TIC|Monitor')
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
