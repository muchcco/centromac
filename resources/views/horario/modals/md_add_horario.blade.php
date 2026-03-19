<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header bg-dark text-white">
            <h5 class="modal-title fw-bold">Agregar Horario Diferenciado</h5><button type="button"
                class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <form id="formHorario"><input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="row mb-3"><label class="col-3 col-form-label fw-bold">Centro MAC</label>
                    <div class="col-9"><select class="form-control select2" id="idcentro_mac">
                            <option value="">Seleccione</option>
                            @foreach ($macs as $m)
                                <option value="{{ $m->IDCENTRO_MAC }}">{{ $m->NOMBRE_MAC }}</option>
                            @endforeach
                        </select></div>
                </div>
                <div class="row mb-3"><label class="col-3 col-form-label fw-bold">Módulo</label>
                    <div class="col-9"><select class="form-control select2" id="idmodulo" disabled>
                            <option value="">Seleccione</option>
                        </select></div>
                </div>
                <div class="row mb-3"><label class="col-3 col-form-label fw-bold">Entidad</label>
                    <div class="col-9"><select class="form-control select2" id="identidad" disabled>
                            <option value="">Seleccione</option>
                        </select></div>
                </div>
                <div class="row mb-3"><label class="col-3 col-form-label fw-bold">Día</label>
                    <div class="col-9"><select class="form-control" id="DiaSemana">
                            <option value="">Seleccione</option>
                            <option value="1">Lunes</option>
                            <option value="2">Martes</option>
                            <option value="3">Miércoles</option>
                            <option value="4">Jueves</option>
                            <option value="5">Viernes</option>
                            <option value="6">Sábado</option>
                            <option value="7">Domingo</option>
                        </select></div>
                </div>
                <div class="row mb-3"><label class="col-3 col-form-label fw-bold">Hora Ingreso</label>
                    <div class="col-9"><input type="time" class="form-control" id="HoraIngreso"></div>
                </div>
                <div class="row mb-3"><label class="col-3 col-form-label fw-bold">Hora Salida</label>
                    <div class="col-9"><input type="time" class="form-control" id="HoraSalida"></div>
                </div>
                <div class="row mb-3"><label class="col-3 col-form-label fw-bold">Fecha Inicio</label>
                    <div class="col-9"><input type="date" class="form-control" id="fecha_inicio"></div>
                </div>
                <div class="row mb-3"><label class="col-3 col-form-label fw-bold">Fecha Fin</label>
                    <div class="col-9"><input type="date" class="form-control" id="fecha_fin"></div>
                </div>
                <div class="row mb-3"><label class="col-3 col-form-label fw-bold">Observaciones</label>
                    <div class="col-9">
                        <textarea class="form-control" id="Observaciones"></textarea>
                    </div>
                </div>
                <div id="alerta_conflicto" class="text-danger fw-bold"></div>
            </form>
        </div>
        <div class="modal-footer"><button class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button><button
                class="btn btn-success" id="btnGuardar">Guardar</button></div>
    </div>
</div>

<script>
    let modulosCache = [];

    $(document).ready(function() {
        $('.select2').select2({
            dropdownParent: $('#modal_show_modal'),
            width: '100%'
        });
    });

    $(document).on('change', '#idcentro_mac', function() {
        let idmac = $(this).val();
        resetModulo();
        resetEntidad();
        if (!idmac) return;
        $('#idmodulo').html('<option>Cargando...</option>');
        $.post("{{ route('horario.get_modulos') }}", {
            _token: "{{ csrf_token() }}",
            idmac
        }, function(res) {
            modulosCache = res;
            let html = '<option value="">Seleccione</option>';
            res.forEach(m => {
                html +=
                    `<option value="${m.IDMODULO}" data-entidad="${m.IDENTIDAD}" data-nombre="${m.NOMBRE_ENTIDAD}">${m.N_MODULO}</option>`;
            });
            $('#idmodulo').html(html).prop('disabled', false).trigger('change');
        });
    });

    $(document).on('change', '#idmodulo', function() {
        let selected = $(this).find(':selected');
        let identidad = selected.data('entidad');
        let nombre = selected.data('nombre');
        resetEntidad();
        if (!identidad) return;
        $('#identidad').html(`<option value="${identidad}" selected>${nombre}</option>`).prop('disabled', true)
            .trigger('change');
    });

    function resetModulo() {
        $('#idmodulo').html('<option value="">Seleccione</option>').prop('disabled', true);
    }

    function resetEntidad() {
        $('#identidad').html('<option value="">Seleccione</option>').prop('disabled', true);
    }

    /* 🔥 VALIDACIÓN EN TIEMPO REAL */
    $('#HoraIngreso,#HoraSalida,#DiaSemana,#idmodulo').on('change', function() {
        validarCruce();
    });

    function validarCruce() {
        let idmac = $('#idcentro_mac').val();
        let idmodulo = $('#idmodulo').val();
        let dia = $('#DiaSemana').val();
        let hi = $('#HoraIngreso').val();
        let hs = $('#HoraSalida').val();
        if (!idmac || !idmodulo || !dia || !hi || !hs) return;
        $.post("{{ route('horario.store_horario') }}", {
            _token: "{{ csrf_token() }}",
            idcentro_mac: idmac,
            idmodulo: idmodulo,
            identidad: $('#identidad').val(),
            DiaSemana: dia,
            HoraIngreso: hi,
            HoraSalida: hs,
            fecha_inicio: '2000-01-01',
            fecha_fin: '2000-01-01',
            modo: 'validar'
        }, function(res) {
            $('#alerta_conflicto').html('');
        }).fail(function(xhr) {
            if (xhr.responseJSON?.msg) {
                $('#alerta_conflicto').html(xhr.responseJSON.msg);
            }
        });
    }

    /* 🔥 VALIDACIÓN FINAL */
    function validar() {
        let ok = true;
        $('#formHorario select,#formHorario input').removeClass('is-invalid');
        if (!$('#idcentro_mac').val()) {
            ok = false;
            $('#idcentro_mac').addClass('is-invalid');
        }
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

    /* 🔥 GUARDAR */
    $('#btnGuardar').off('click').on('click', function() {
        if (!validar()) {
            Swal.fire('Campos inválidos', 'Verifica los datos', 'warning');
            return;
        }
        let btn = $(this);
        let data = new FormData();
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
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando');
        $.ajax({
            type: 'POST',
            url: "{{ route('horario.store_horario') }}",
            data: data,
            processData: false,
            contentType: false,
            success: function() {
                bootstrap.Modal.getInstance(document.getElementById('modal_show_modal')).hide();
                $('#formHorario')[0].reset();
                $('.select2').val('').trigger('change');
                cargarHorarios();
                Swal.fire('OK', 'Guardado correctamente', 'success');
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('Guardar');
                Swal.fire('Error', xhr.responseJSON?.msg || 'No se pudo guardar', 'error');
            }
        });
    });
</script>
