<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header bg-dark text-white">
            <h5 class="modal-title fw-bold">Editar Horario</h5><button type="button" class="btn-close btn-close-white"
                data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <form id="formEditHorario"><input type="hidden" id="id_horario"
                    value="{{ $horario->idhorario_diferenciado }}"><input type="hidden" id="idcentro_mac"
                    value="{{ $horario->idcentro_mac }}">
                <div class="row mb-3"><label class="col-3 col-form-label fw-bold">Módulo</label>
                    <div class="col-9"><select class="form-control select2" id="idmodulo">
                            <option value="">Seleccione</option>
                            @foreach ($modulos as $m)
                                <option value="{{ $m->IDMODULO }}" data-entidad="{{ $m->IDENTIDAD }}"
                                    data-nombre="{{ $m->NOMBRE_ENTIDAD ?? '' }}"
                                    {{ $horario->idmodulo == $m->IDMODULO ? 'selected' : '' }}>{{ $m->N_MODULO }}
                                </option>
                            @endforeach
                        </select></div>
                </div>
                <div class="row mb-3"><label class="col-3 col-form-label fw-bold">Entidad</label>
                    <div class="col-9"><select class="form-control select2" id="identidad" disabled>
                            <option value="{{ $horario->identidad }}" selected>
                                {{ $horario->NOMBRE_ENTIDAD ?? 'Entidad' }}</option>
                        </select></div>
                </div>
                <div class="row mb-3"><label class="col-3 col-form-label fw-bold">Día</label>
                    <div class="col-9"><select class="form-control" id="DiaSemana">
                            <option value="">Seleccione</option>
                            <option value="1" {{ $horario->DiaSemana == 1 ? 'selected' : '' }}>Lunes</option>
                            <option value="2" {{ $horario->DiaSemana == 2 ? 'selected' : '' }}>Martes</option>
                            <option value="3" {{ $horario->DiaSemana == 3 ? 'selected' : '' }}>Miércoles</option>
                            <option value="4" {{ $horario->DiaSemana == 4 ? 'selected' : '' }}>Jueves</option>
                            <option value="5" {{ $horario->DiaSemana == 5 ? 'selected' : '' }}>Viernes</option>
                            <option value="6" {{ $horario->DiaSemana == 6 ? 'selected' : '' }}>Sábado</option>
                            <option value="7" {{ $horario->DiaSemana == 7 ? 'selected' : '' }}>Domingo</option>
                        </select></div>
                </div>
                <div class="row mb-3"><label class="col-3 col-form-label fw-bold">Hora Ingreso</label>
                    <div class="col-9"><input type="time" class="form-control" id="HoraIngreso"
                            value="{{ $horario->HoraIngreso }}"></div>
                </div>
                <div class="row mb-3"><label class="col-3 col-form-label fw-bold">Hora Salida</label>
                    <div class="col-9"><input type="time" class="form-control" id="HoraSalida"
                            value="{{ $horario->HoraSalida }}"></div>
                </div>
                <div class="row mb-3"><label class="col-3 col-form-label fw-bold">Fecha Inicio</label>
                    <div class="col-9"><input type="date" class="form-control" id="fecha_inicio"
                            value="{{ $horario->fecha_inicio }}"></div>
                </div>
                <div class="row mb-3"><label class="col-3 col-form-label fw-bold">Fecha Fin</label>
                    <div class="col-9"><input type="date" class="form-control" id="fecha_fin"
                            value="{{ $horario->fecha_fin }}"></div>
                </div>
                <div class="row mb-3"><label class="col-3 col-form-label fw-bold">Observaciones</label>
                    <div class="col-9">
                        <textarea class="form-control" id="Observaciones">{{ $horario->Observaciones }}</textarea>
                    </div>
                </div>
                <div id="alerta_conflicto_edit" class="fw-bold text-danger"></div>
            </form>
        </div>
        <div class="modal-footer"><button class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button><button
                class="btn btn-success" id="btnUpdate">Actualizar</button></div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.select2').select2({
            dropdownParent: $('#modal_show_modal'),
            width: '100%'
        });
        autoSetEntidad();
    });

    /* 🔥 AUTO ENTIDAD DESDE MODULO */
    function autoSetEntidad() {
        let selected = $('#idmodulo option:selected');
        let identidad = selected.data('entidad');
        let nombre = selected.data('nombre');
        if (!identidad) return;
        $('#identidad').html(`<option value="${identidad}" selected>${nombre}</option>`).trigger('change');
    }

    $(document).on('change', '#idmodulo', function() {
        autoSetEntidad();
        validarCruceEdit();
    });

    /* 🔥 VALIDACIÓN EN TIEMPO REAL */
    $('#HoraIngreso,#HoraSalida,#DiaSemana').on('change', function() {
        validarCruceEdit();
    });

    function validarCruceEdit() {
        let id = $('#id_horario').val();
        let idmac = $('#idcentro_mac').val();
        let idmodulo = $('#idmodulo').val();
        let dia = $('#DiaSemana').val();
        let hi = $('#HoraIngreso').val();
        let hs = $('#HoraSalida').val();
        if (!idmac || !idmodulo || !dia || !hi || !hs) return;
        $.post("{{ route('horario.update_horario') }}", {
            _token: "{{ csrf_token() }}",
            modo: 'validar',
            id: id,
            idcentro_mac: idmac,
            idmodulo: idmodulo,
            identidad: $('#identidad').val(),
            DiaSemana: dia,
            HoraIngreso: hi,
            HoraSalida: hs,
            fecha_inicio: '2000-01-01',
            fecha_fin: '2000-01-01'
        }, function() {
            $('#alerta_conflicto_edit').html('');
        }).fail(function(xhr) {
            if (xhr.responseJSON?.msg) {
                $('#alerta_conflicto_edit').html(xhr.responseJSON.msg);
            }
        });
    }

    /* 🔥 VALIDACIÓN FINAL */
    function validarEdit() {
        let ok = true;
        $('#formEditHorario select,#formEditHorario input').removeClass('is-invalid');
        if (!$('#idmodulo').val()) {
            ok = false;
            $('#idmodulo').addClass('is-invalid');
        }
        if (!$('#DiaSemana').val()) {
            ok = false;
            $('#DiaSemana').addClass('is-invalid');
        }
        let hi = $('#HoraIngreso').val();
        let hs = $('#HoraSalida').val();
        if (!hi || !hs || hi >= hs) {
            ok = false;
            $('#HoraIngreso,#HoraSalida').addClass('is-invalid');
        }
        return ok;
    }

    /* 🔥 UPDATE */
    $('#btnUpdate').off('click').on('click', function() {
        if (!validarEdit()) {
            Swal.fire('Campos inválidos', 'Verifica datos', 'warning');
            return;
        }
        let btn = $(this);
        let data = new FormData();
        data.append("id", $('#id_horario').val());
        data.append("idcentro_mac", $('#idcentro_mac').val());
        data.append("idmodulo", $('#idmodulo').val());
        data.append("identidad", $('#identidad').val());
        data.append("DiaSemana", $('#DiaSemana').val());
        data.append("HoraIngreso", $('#HoraIngreso').val());
        data.append("HoraSalida", $('#HoraSalida').val());
        data.append("fecha_inicio", $('#fecha_inicio').val());
        data.append("fecha_fin", $('#fecha_fin').val());
        data.append("Observaciones", $('#Observaciones').val());
        data.append("_token", "{{ csrf_token() }}");
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Actualizando');
        $.ajax({
            type: 'POST',
            url: "{{ route('horario.update_horario') }}",
            data: data,
            processData: false,
            contentType: false,
            success: function() {
                bootstrap.Modal.getInstance(document.getElementById('modal_show_modal')).hide();
                cargarHorarios();
                Swal.fire('OK', 'Actualizado', 'success');
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('Actualizar');
                Swal.fire('Error', xhr.responseJSON?.msg || 'No se pudo actualizar', 'error');
            }
        });
    });
</script>
