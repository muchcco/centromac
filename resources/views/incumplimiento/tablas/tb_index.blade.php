<table class="table table-hover table-bordered table-striped align-middle" id="table_incumplimientos">
    <thead class="tenca">
        <tr>
            <th width="50px">N°</th>
            <th>Centro MAC</th>
            <th>Fecha Incidente</th>
            <th>Tipificación</th>
            <th>Entidad</th>
            <th>Descripción</th>
            <th>Revisión</th>
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
            @endphp

            <tr
                @if ($incumplimiento->observado) style="background-color:#fff8dc;"
                    title="Observado por {{ $usuarioObservador->name ?? 'Administrador/Moderador' }} el {{ \Carbon\Carbon::parse($incumplimiento->fecha_observado)->format('d-m-Y H:i') }}" @endif>
                <td class="text-center">{{ $i + 1 }}</td>

                {{-- Centro MAC --}}
                <td>{{ $incumplimiento->centroMac->nombre_mac ?? 'No asignado' }}</td>

                {{-- Fecha --}}
                <td>{{ \Carbon\Carbon::parse($incumplimiento->fecha_observacion)->format('d-m-Y') }}</td>

                {{-- Tipificación --}}
                <td>
                    {{ $incumplimiento->tipoIntObs->tipo ?? '' }}
                    {{ $incumplimiento->tipoIntObs->numeracion ?? '' }} -
                    {{ $incumplimiento->tipoIntObs->nom_tipo_int_obs ?? '' }}
                </td>

                {{-- Entidad --}}
                <td>{{ $incumplimiento->entidad->ABREV_ENTIDAD ?? 'No asignado' }}</td>

                {{-- Descripción --}}
                <td class="text-wrap text-uppercase" style="max-width:300px;">
                    {{ Str::limit(strtoupper($incumplimiento->descripcion ?? 'SIN DESCRIPCIÓN'), 100, '...') }}
                </td>

                {{-- Estado de revisión (observado / corregido) --}}
                <td class="text-center">
                    @if ($incumplimiento->corregido)
                        <span class="badge bg-success">Corregido</span>
                    @elseif ($incumplimiento->observado)
                        <span class="badge bg-warning text-dark">Observado</span>
                    @else
                        <span class="badge bg-secondary">Sin observación</span>
                    @endif
                </td>

                {{-- Estado general del incidente --}}
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

                    {{-- 🔹 Observación / Retroalimentación / Corrección --}}
                    @php
                        $puedeObservar = auth()
                            ->user()
                            ->hasAnyRole(['Administrador', 'Moderador']);
                        $puedeCorregir = auth()
                            ->user()
                            ->hasAnyRole(['Supervisor', 'Especialista TIC', 'Moderador', 'Administrador']);
                        $mostrarBoton = $puedeObservar || ($puedeCorregir && $incumplimiento->observado); // sólo si tiene permiso o está observado
                    @endphp

                    @if ($mostrarBoton)
                        @php
                            if ($incumplimiento->corregido) {
                                $icono = 'fa-check-circle text-success';
                                $tooltip = 'Observación corregida';
                            } elseif ($incumplimiento->observado) {
                                $icono = 'fa-exclamation-triangle text-danger';
                                $tooltip = 'Ver / Retroalimentar observación';
                            } else {
                                $icono = 'fa-exclamation-triangle text-info font-16';
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
                    @role('Administrador|Moderador|Especialista TIC')
                        <button class="nobtn bandejTool" data-tippy-content="Editar Incidente Operativo"
                            onclick="btnEditarIncumplimiento('{{ $incumplimiento->id_observacion }}')">
                            <i class="las la-pen text-success font-16"></i>
                        </button>
                    @endrole

                    {{-- 🔹 Eliminar --}}
                    @role('Administrador|Moderador')
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
@section('script')
    <script>
        $(document).ready(function() {
            $('#table_incumplimientos').DataTable({
                responsive: true,
                pageLength: 20,
                lengthMenu: [
                    [10, 20, 40, -1],
                    [10, 20, 40, "Todos"]
                ],
                autoWidth: false,
                searching: true,
                ordering: true,
                info: true,
                language: {
                    url: "{{ asset('js/Spanish.json') }}"
                },
                columnDefs: [{
                        targets: [0, 6, 7, 8],
                        className: "text-center"
                    },
                    {
                        targets: [8],
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Tooltip moderno
            tippy(".bandejTool", {
                allowHTML: true,
                theme: 'light-border',
                delay: [100, 50],
                placement: 'top'
            });
        });
    </script>
@endsection
