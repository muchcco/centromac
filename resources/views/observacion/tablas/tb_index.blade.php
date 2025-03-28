<table class="table table-hover table-bordered table-striped" id="table_observaciones">
    <thead class="tenca">
        <tr>
            <th>N°</th>
            <th>Centro MAC</th>
            <th>Fecha Observación</th>
            <th>Tipificación</th>
            <th>Entidad</th>
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

                <td>{{ $observacion->entidad->nombre_entidad ?? 'No asignado' }}</td>

                <td>
                    @switch($observacion->estado)
                        @case('SUBSANADO CON DOCUMENTO')
                            <span class="badge bg-success">Subsanado con Documento</span>
                        @break

                        @case('SUBSANADO SIN DOCUMENTO')
                            <span class="badge bg-success">Subsanado sin Documento</span>
                        @break

                        @case('NO APLICA')
                            <span class="badge bg-secondary">No Aplica</span>
                        @break

                        @default
                            <span class="badge bg-danger">{{ $observacion->estado }}</span>
                    @endswitch
                </td>

                <td>
                    <button class="nobtn bandejTool" data-tippy-content="Ver Detalles"
                        onclick="btnVerObservacion('{{ $observacion->id_observacion }}')">
                        <i class="las la-eye text-primary font-16"></i>
                    </button>
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
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
