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
            @php
                $usuarioObservador = $incumplimiento->observado_por
                    ? \App\Models\User::find($incumplimiento->observado_por)
                    : null;

                $puedeObservar = auth()
                    ->user()
                    ->hasAnyRole(['Administrador', 'Moderador']);
                $puedeCorregir = auth()
                    ->user()
                    ->hasAnyRole(['Supervisor', 'Especialista TIC', 'Moderador', 'Administrador']);
                $mostrarBoton = $puedeObservar || ($puedeCorregir && $incumplimiento->observado);
            @endphp

            <tr
                @if ($incumplimiento->observado) style="background-color:#fff8dc;"
                    title="Observado por {{ $usuarioObservador->name ?? 'Administrador/Moderador' }} el {{ \Carbon\Carbon::parse($incumplimiento->fecha_observado)->format('d-m-Y H:i') }}" @endif>
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

                {{-- Estado --}}
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

                {{-- Acciones --}}
                <td class="text-center">

                    {{-- 🔹 Observar / Retroalimentar --}}
                    @if ($mostrarBoton)
                        @php
                            if ($incumplimiento->corregido) {
                                $icono = 'fa-check-circle text-success';
                                $tooltip = 'Observación corregida';
                            } elseif ($incumplimiento->observado) {
                                $icono = 'fa-exclamation-triangle text-danger';
                                $tooltip = 'Ver / Retroalimentar observación';
                            } else {
                                $icono = 'fa-exclamation-triangle text-info';
                                $tooltip = 'Marcar como observado';
                            }
                        @endphp
                        <button class="nobtn bandejTool" data-tippy-content="{{ $tooltip }}"
                            onclick="btnObservarIncumplimiento('{{ $incumplimiento->id_observacion }}')">
                            <i class="fa {{ $icono }} font-16"></i>
                        </button>
                    @endif

                    {{-- 🔹 Ver detalle --}}
                    <button class="nobtn bandejTool" data-tippy-content="Ver detalle"
                        onclick="btnVerIncumplimiento('{{ $incumplimiento->id_observacion }}')">
                        <i class="las la-eye text-primary font-16"></i>
                    </button>

                    {{-- 🔹 Editar --}}
                    @role('Administrador|Moderador|Especialista TIC|Supervisor')
                        <button class="nobtn bandejTool" data-tippy-content="Editar Incidente Operativo"
                            onclick="btnEditarIncumplimiento('{{ $incumplimiento->id_observacion }}')">
                            <i class="las la-pen text-success font-16"></i>
                        </button>
                    @endrole

                    {{-- 🔹 Eliminar --}}
                    @role('Administrador|Moderador|Supervisor')
                        <button class="nobtn bandejTool" data-tippy-content="Eliminar Incidente Operativo"
                            onclick="btnEliminarIncumplimiento('{{ $incumplimiento->id_observacion }}')">
                            <i class="las la-trash-alt text-danger font-16"></i>
                        </button>
                    @endrole
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
