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
            @php
                $usuarioObservador = $interrupcion->observado_por
                    ? \App\Models\User::find($interrupcion->observado_por)
                    : null;
            @endphp

            <tr {{-- 🔹 Fondo amarillo si fue observado --}}
                @if ($interrupcion->observado) style="background-color:#fff8dc;"
                    title="Observado por {{ $usuarioObservador->name ?? 'Administrador/Moderador' }} el {{ \Carbon\Carbon::parse($interrupcion->fecha_observado)->format('d-m-Y H:i') }}" @endif>
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
                <td class="text-center">
                    @switch(strtoupper($interrupcion->estado))
                        @case('ABIERTO')
                            <span class="badge bg-success">ABIERTO</span>
                        @break

                        @case('CERRADO')
                            <span class="badge bg-danger">CERRADO</span>
                        @break

                        @default
                            <span class="badge bg-warning text-dark">{{ strtoupper($interrupcion->estado) }}</span>
                    @endswitch
                </td>

                <!-- Acciones -->
                <td class="text-center">

                    {{-- 🔹 Ícono de observación o corrección SOLO si existe una observación --}}
                    @if ($interrupcion->observado)
                        @php
                            $icono = 'fa-exclamation-triangle text-danger';
                            $tooltip = 'Ver observación / Retroalimentar';
                            if ($interrupcion->corregido) {
                                $icono = 'fa-check-circle text-success';
                                $tooltip = 'Observación corregida';
                            }
                        @endphp

                        <button class="nobtn bandejTool" data-tippy-content="{{ $tooltip }}"
                            onclick="btnObservarInterrupcion('{{ $interrupcion->id_interrupcion }}')">
                            <i class="fa {{ $icono }} font-16"></i>
                        </button>
                    @endif

                    {{-- 🔹 Solo el Administrador o Moderador pueden crear una observación (si no existe aún) --}}
                    @role('Administrador|Moderador')
                        @if (!$interrupcion->observado)
                            <button class="nobtn bandejTool" data-tippy-content="Marcar como Observado / Retroalimentar"
                                onclick="btnObservarInterrupcion('{{ $interrupcion->id_interrupcion }}')">
                                <i class="fa fa-exclamation-triangle text-info font-16"></i>
                            </button>
                        @endif
                    @endrole

                    {{-- Botones de gestión (solo roles permitidos) --}}
                    @role('Administrador|Especialista TIC|Moderador')
                        <button class="nobtn bandejTool" data-tippy-content="Ver detalle"
                            onclick="btnVerInterrupcion({{ $interrupcion->id_interrupcion }})">
                            <i class="las la-eye text-primary font-16"></i>
                        </button>

                        <button class="nobtn bandejTool" data-tippy-content="Editar Interrupción"
                            onclick="btnEditarInterrupcion('{{ $interrupcion->id_interrupcion }}')">
                            <i class="las la-pen text-success font-16"></i>
                        </button>

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

    // 🔹 Ver solo la observación (sin editar)
    function btnVerObservacion(id) {
        $.post("{{ route('interrupcion.modals.md_observar_interrupcion') }}", {
            _token: "{{ csrf_token() }}",
            id_interrupcion: id
        }, function(data) {
            $('#modal_show_modal').html(data.html);
            $('#modal_show_modal').modal('show');
        }).fail(function() {
            Swal.fire("Error", "No se pudo cargar la observación.", "error");
        });
    }
</script>
