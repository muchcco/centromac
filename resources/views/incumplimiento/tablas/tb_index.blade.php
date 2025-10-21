<table class="table table-hover table-bordered table-striped align-middle" id="table_incumplimientos">
    <thead class="tenca">
        <tr>
            <th width="50px">N°</th>
            <th>Centro MAC</th>
            <th>Fecha Incidente</th>
            <th>Tipificación</th>
            <th>Entidad</th>
            <th>Descripción</th>
            <th>Estado</th>
            <th width="150px">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($incumplimientos as $i => $incumplimiento)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $incumplimiento->centroMac->nombre_mac ?? 'No asignado' }}</td>
                <td>{{ \Carbon\Carbon::parse($incumplimiento->fecha_observacion)->format('d-m-Y') }}</td>
                <td>
                    {{ $incumplimiento->tipoIntObs->tipo ?? '' }}
                    {{ $incumplimiento->tipoIntObs->numeracion ?? '' }} -
                    {{ $incumplimiento->tipoIntObs->nom_tipo_int_obs ?? '' }}
                </td>
                <td>{{ $incumplimiento->entidad->ABREV_ENTIDAD ?? 'No asignado' }}</td>
                <td class="text-wrap text-uppercase" style="max-width:300px;">
                    {{ Str::limit(strtoupper($incumplimiento->descripcion ?? 'SIN DESCRIPCIÓN'), 100, '...') }}
                </td>
                <td class="text-center">
                    @switch(strtoupper($incumplimiento->estado))
                        @case('ABIERTO')
                            <span class="badge bg-success">ABIERTO</span>
                        @break

                        @case('CERRADO')
                            <span class="badge bg-danger">CERRADO</span>
                        @break

                        @default
                            <span class="badge bg-dark">{{ strtoupper($incumplimiento->estado) }}</span>
                    @endswitch
                </td>
                <td class="text-center">
                    <button class="nobtn bandejTool" data-tippy-content="Ver detalle"
                        onclick="btnVerIncumplimiento('{{ $incumplimiento->id_observacion }}')">
                        <i class="las la-eye text-primary font-16"></i>
                    </button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
