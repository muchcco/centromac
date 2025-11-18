<table class="table table-hover table-bordered table-striped" id="table_interrupciones">
    <thead class="tenca">
        <tr>
            <th width="50px">N°</th>
            <th>Centro MAC</th>
            <th>Fecha y Hora Inicio</th>
            <th>Tipificación</th>
            <th>Entidad</th>
            <th>Descripción</th>
            <th>Estado</th>
            <th>Tiempo de interrupción</th>
            <th>Acciones</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($interrupciones as $i => $interrupcion)
            @php
                $usuarioObservador = $interrupcion->observado_por
                    ? \App\Models\User::find($interrupcion->observado_por)
                    : null;
            @endphp

            <tr
                @if ($interrupcion->observado) style="background-color:#fff8dc;"
                    title="Observado por {{ $usuarioObservador->name ?? 'Administrador/Moderador' }} el {{ \Carbon\Carbon::parse($interrupcion->fecha_observado)->format('d-m-Y H:i') }}" @endif>

                <td>{{ $i + 1 }}</td>

                {{-- Centro MAC --}}
                <td>{{ $interrupcion->centroMac->nombre_mac ?? 'No asignado' }}</td>

                {{-- Fecha y Hora de Inicio --}}
                <td>
                    {{ \Carbon\Carbon::parse($interrupcion->fecha_inicio)->format('d-m-Y') }} -
                    {{ \Carbon\Carbon::parse($interrupcion->hora_inicio)->format('H:i') }}
                </td>

                {{-- Tipificación --}}
                <td>
                    {{ $interrupcion->tipoIntObs->tipo ?? '' }}
                    {{ $interrupcion->tipoIntObs->numeracion ?? '' }} --
                    {{ $interrupcion->tipoIntObs->nom_tipo_int_obs ?? '' }}
                </td>

                {{-- Entidad --}}
                <td>{{ $interrupcion->entidad->ABREV_ENTIDAD ?? 'No asignado' }}</td>

                {{-- Descripción --}}
                <td class="text-uppercase">
                    {{ Str::limit(strtoupper($interrupcion->descripcion ?? 'SIN DESCRIPCIÓN'), 100, '...') }}
                </td>

                {{-- Estado --}}
                <td class="text-center">
                    @switch(strtoupper($interrupcion->estado))
                        @case('ABIERTO')
                            <span class="badge bg-danger">ABIERTO</span>
                        @break

                        @case('CERRADO')
                            <span class="badge bg-success">CERRADO</span>
                        @break

                        @default
                            <span class="badge bg-warning text-dark">{{ strtoupper($interrupcion->estado) }}</span>
                    @endswitch
                </td>

                {{-- Tiempo --}}
                <td class="text-center">{{ $interrupcion->tiempo_horario }}</td>

                {{-- ACCIONES --}}
                <td class="text-center">

                    {{-- Iconos de Observación --}}
                    @if ($interrupcion->observado)
                        @php
                            $icono = $interrupcion->corregido
                                ? 'fa-check-circle text-success'
                                : 'fa-exclamation-triangle text-danger';

                            $tooltip = $interrupcion->corregido
                                ? 'Observación corregida'
                                : 'Ver observación / Retroalimentar';
                        @endphp

                        <button class="nobtn bandejTool" data-tippy-content="{{ $tooltip }}"
                            onclick="btnObservarInterrupcion('{{ $interrupcion->id_interrupcion }}')">
                            <i class="fa {{ $icono }} font-16"></i>
                        </button>
                    @endif

                    {{-- Crear observación --}}
                    @role('Administrador|Moderador')
                        @if (!$interrupcion->observado)
                            <button class="nobtn bandejTool" data-tippy-content="Marcar como Observado / Retroalimentar"
                                onclick="btnObservarInterrupcion('{{ $interrupcion->id_interrupcion }}')">
                                <i class="fa fa-exclamation-triangle text-info font-16"></i>
                            </button>
                        @endif
                    @endrole

                    {{-- Ver --}}
                    @role('Administrador|Especialista TIC|Moderador')
                        <button class="nobtn bandejTool" data-tippy-content="Ver detalle"
                            onclick="btnVerInterrupcion({{ $interrupcion->id_interrupcion }})">
                            <i class="las la-eye text-primary font-16"></i>
                        </button>

                        {{-- Editar --}}
                        <button class="nobtn bandejTool" data-tippy-content="Editar Interrupción"
                            onclick="btnEditarInterrupcion('{{ $interrupcion->id_interrupcion }}')">
                            <i class="las la-pen text-success font-16"></i>
                        </button>

                        {{-- Eliminar --}}
                        <button class="nobtn bandejTool" data-tippy-content="Eliminar Interrupción"
                            onclick="btnEliminarInterrupcion('{{ $interrupcion->id_interrupcion }}')">
                            <i class="las la-trash-alt text-danger font-16"></i>
                        </button>
                    @endrole

                    {{-- Subsanar --}}
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
            responsive: true,
            autoWidth: false,
            searching: true,
            ordering: true,
            info: true,
            pageLength: 20,
            bLengthChange: true,
            lengthMenu: [
                [10, 20, 40, -1],
                [10, 20, 40, "Todos"]
            ],
            language: {
                url: "{{ asset('js/Spanish.json') }}"
            },
            columnDefs: [{
                    targets: 0, // N°
                    className: "text-center"
                },
                {
                    targets: 6, // Estado
                    className: "text-center"
                },
                {
                    targets: 7, // Tiempo interrupción
                    className: "text-center"
                },
                {
                    targets: 8, // Acciones
                    orderable: false,
                    searchable: false,
                    className: "text-center"
                }
            ]
        });

        tippy(".bandejTool", {
            allowHTML: true,
            followCursor: true
        });
    });
</script>
