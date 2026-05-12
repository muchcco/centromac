<div class="modal-dialog modal-xl">
    <div class="modal-content">
        <div class="modal-header" style="background-color:#132842">
            <h5 class="modal-title text-white">
                {{ $horario ? 'Editar horario de trabajo' : 'Asignar horario de trabajo' }}
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
            <form id="formAsignacionHorario">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="mac" value="{{ $idmac }}">
                <input type="hidden" name="id" value="{{ $horario->id ?? '' }}">

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Centro MAC</label>
                        <input type="text" class="form-control" value="{{ $nameMac }}" readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Personal</label>
                        <select name="idpersonal" id="idpersonal_asignacion" class="form-control select2-modal" required>
                            @if ($selectedPersonal)
                                <option value="{{ $selectedPersonal['id'] }}" selected>{{ $selectedPersonal['text'] }}</option>
                            @else
                                <option value="">Busque por DNI o nombre</option>
                            @endif
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Hora ingreso</label>
                        <input type="time" name="hora_ingreso" id="hora_ingreso_asignacion" class="form-control"
                            value="{{ $horario ? substr($horario->hora_ingreso, 0, 5) : '08:15' }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Hora salida</label>
                        <input type="time" name="hora_salida" id="hora_salida_asignacion" class="form-control"
                            value="{{ $horario ? substr($horario->hora_salida, 0, 5) : '17:15' }}" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Fecha inicio</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio_asignacion" class="form-control"
                            value="{{ $horario ? $horario->fecha_inicio : now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Fecha fin</label>
                        <input type="date" name="fecha_fin" id="fecha_fin_asignacion" class="form-control"
                            value="{{ $horario->fecha_fin ?? '' }}"
                            {{ !$horario || (int) $horario->sin_fin === 1 ? 'disabled' : '' }}>
                    </div>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="sin_fin" id="sin_fin_asignacion"
                        value="1" {{ !$horario || (int) $horario->sin_fin === 1 ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="sin_fin_asignacion">
                        Sin fecha fin
                    </label>
                </div>
            </form>

            @if ($horario)
                <hr>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h6 class="mb-1 fw-semibold">Dias especiales</h6>
                        <p class="text-muted mb-0 font-13">Sabados u horarios excepcionales.</p>
                    </div>
                </div>

                @if ($diasEspecialesDisponible)
                    <form id="formCalendarioSabadosAsignacion">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="mac" value="{{ $idmac }}">
                        <input type="hidden" name="id_asignacion" value="{{ $horario->id }}">

                        <div class="row align-items-end mb-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Mes</label>
                                <input type="month" name="mes" id="mes_calendario_asignacion" class="form-control"
                                    value="{{ $mesCalendario }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Ingreso sabado</label>
                                <input type="time" name="hora_ingreso" id="hora_ingreso_sabado" class="form-control"
                                    value="08:15">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Salida sabado</label>
                                <input type="time" name="hora_salida" id="hora_salida_sabado" class="form-control"
                                    value="13:30">
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-primary w-100" id="btnGuardarSabados"
                                    onclick="guardarSabadosAsignacion()">
                                    Guardar sabados
                                </button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <div class="asignacion-calendar-panel">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h6 class="mb-0 fw-semibold">Dias normales</h6>
                                        <span class="badge bg-primary">L-V</span>
                                    </div>
                                    <div id="calendario_dias_normales" class="asignacion-calendar-grid"></div>
                                </div>
                            </div>
                            <div class="col-lg-6 mb-3">
                                <div class="asignacion-calendar-panel">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h6 class="mb-0 fw-semibold">Sabados programados</h6>
                                        <span class="badge bg-success">Seleccion</span>
                                    </div>
                                    <div id="calendario_sabados" class="asignacion-calendar-grid"></div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div id="tabla_dias_especiales">
                        <div class="text-center p-3">
                            <i class="fa fa-spinner fa-spin"></i> Cargando...
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning mb-0">
                        Falta crear la tabla <strong>d_personal_asistencia_dia</strong>.
                    </div>
                @endif
            @else
                <hr>
                <div class="alert alert-info mb-0">
                    Guarde primero el horario base para agregar sabados u otros dias especiales.
                </div>
            @endif
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-success" id="btnGuardarAsignacion"
                onclick="guardarAsignacionHorario()">
                {{ $horario ? 'Actualizar' : 'Guardar' }}
            </button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.select2-modal').select2({
            dropdownParent: $('#modal_show_modal'),
            width: '100%',
            minimumInputLength: 2,
            placeholder: 'Busque por DNI o nombre',
            ajax: {
                url: "{{ route('asistencia.asignacion.buscar_personal') }}",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        mac: '{{ $idmac }}',
                        q: params.term
                    };
                },
                processResults: function(data) {
                    return data;
                },
                cache: true
            }
        });

        @if ($horario && $diasEspecialesDisponible)
            $('#mes_calendario_asignacion').on('change', function() {
                cargarCalendarioAsignacion();
            });
            cargarCalendarioAsignacion();
            cargarDiasEspecialesAsignacion();
        @endif
    });

    $('#sin_fin_asignacion').on('change', function() {
        const sinFin = $(this).is(':checked');
        $('#fecha_fin_asignacion').prop('disabled', sinFin);
        if (sinFin) {
            $('#fecha_fin_asignacion').val('');
        }
    });

    @if ($horario && $diasEspecialesDisponible)
        function escapeAsignacionHtml(value) {
            return $('<div>').text(value || '').html();
        }

        function renderDiaNormal(dia) {
            const title = dia.es_feriado ? escapeAsignacionHtml(dia.feriado) : dia.fecha;
            const badge = dia.es_feriado ? '<small>Feriado</small>' : `<small>${dia.nombre_dia}</small>`;
            const classes = dia.es_feriado ? 'asignacion-calendar-day is-disabled' : 'asignacion-calendar-day is-normal';

            return `
                <div class="${classes}" title="${title}">
                    <strong>${dia.dia}</strong>
                    ${badge}
                </div>
            `;
        }

        function renderSabado(dia) {
            const disabled = dia.es_feriado ? 'disabled' : '';
            const checked = dia.seleccionado && !dia.es_feriado ? 'checked' : '';
            const title = dia.es_feriado ? escapeAsignacionHtml(dia.feriado) : dia.fecha;
            const selectedClass = checked ? ' is-selected' : '';
            const classes = dia.es_feriado ? 'asignacion-calendar-day is-disabled' :
                'asignacion-calendar-day is-saturday' + selectedClass;

            return `
                <label class="${classes}" title="${title}">
                    <input type="checkbox" name="fechas[]" value="${dia.fecha}" ${checked} ${disabled}>
                    <strong>${dia.dia}</strong>
                    <small>${dia.es_feriado ? 'Feriado' : dia.nombre_dia}</small>
                </label>
            `;
        }

        function cargarCalendarioAsignacion() {
            $.ajax({
                type: 'GET',
                url: "{{ route('asistencia.asignacion.calendario_horario') }}",
                dataType: 'json',
                data: {
                    mac: '{{ $idmac }}',
                    id_asignacion: '{{ $horario->id }}',
                    mes: $('#mes_calendario_asignacion').val()
                },
                beforeSend: function() {
                    $('#calendario_dias_normales').html(
                        '<div class="text-muted p-2">Cargando...</div>'
                    );
                    $('#calendario_sabados').html(
                        '<div class="text-muted p-2">Cargando...</div>'
                    );
                },
                success: function(res) {
                    const normales = res.normales || [];
                    const sabados = res.sabados || [];
                    const primerSeleccionado = sabados.find(function(dia) {
                        return dia.seleccionado && !dia.es_feriado;
                    });

                    if (primerSeleccionado) {
                        $('#hora_ingreso_sabado').val(primerSeleccionado.hora_ingreso);
                        $('#hora_salida_sabado').val(primerSeleccionado.hora_salida);
                    }

                    $('#calendario_dias_normales').html(
                        normales.length ? normales.map(renderDiaNormal).join('') :
                        '<div class="text-muted p-2">Sin dias normales en este mes.</div>'
                    );
                    $('#calendario_sabados').html(
                        sabados.length ? sabados.map(renderSabado).join('') :
                        '<div class="text-muted p-2">Sin sabados dentro de la vigencia.</div>'
                    );
                },
                error: function() {
                    $('#calendario_dias_normales').html(
                        '<div class="text-danger p-2">No se pudo cargar el calendario.</div>'
                    );
                    $('#calendario_sabados').html(
                        '<div class="text-danger p-2">No se pudo cargar el calendario.</div>'
                    );
                }
            });
        }

        $(document)
            .off('change.asignacionSabados', '#calendario_sabados input[name="fechas[]"]')
            .on('change.asignacionSabados', '#calendario_sabados input[name="fechas[]"]', function() {
                $(this).closest('.asignacion-calendar-day').toggleClass('is-selected', $(this).is(':checked'));
            });

        function cargarDiasEspecialesAsignacion() {
            $.ajax({
                type: 'GET',
                url: "{{ route('asistencia.asignacion.dias_especiales') }}",
                data: {
                    mac: '{{ $idmac }}',
                    id_asignacion: '{{ $horario->id }}'
                },
                success: function(html) {
                    $('#tabla_dias_especiales').html(html);
                },
                error: function() {
                    $('#tabla_dias_especiales').html(
                        '<div class="text-danger p-3">No se pudo cargar los dias especiales.</div>'
                    );
                }
            });
        }

        function guardarSabadosAsignacion() {
            let formData = new FormData(document.getElementById('formCalendarioSabadosAsignacion'));
            let btn = $('#btnGuardarSabados');

            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando');

            $.ajax({
                type: 'POST',
                url: "{{ route('asistencia.asignacion.sync_sabados') }}",
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    cargarCalendarioAsignacion();
                    cargarDiasEspecialesAsignacion();
                    cargarReporteAsignacion();
                    Swal.fire('OK', res.message, 'success');
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo guardar los sabados.', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).html('Guardar sabados');
                }
            });
        }

        function eliminarDiaEspecial(id) {
            Swal.fire({
                title: 'Eliminar dia especial',
                text: 'Esta fecha dejara de contarse como dia programado.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.post("{{ route('asistencia.asignacion.delete_dia_especial') }}", {
                    id: id,
                    mac: '{{ $idmac }}'
                }).done(function(res) {
                    cargarCalendarioAsignacion();
                    cargarDiasEspecialesAsignacion();
                    cargarReporteAsignacion();
                    Swal.fire('OK', res.message, 'success');
                }).fail(function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo eliminar.', 'error');
                });
            });
        }
    @endif
</script>
