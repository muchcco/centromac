<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title"><i class="fa fa-plus-circle text-success"></i> Añadir nuevo servicio ANS</h4><button
                type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div id="alerta"></div>
            <h5 class="mb-4">Registrar servicio en el catálogo ANS</h5>
            <form id="formServicio" class="form-horizontal"><input type="hidden" name="_token" id="_token"
                    value="{{ csrf_token() }}" />
                <div class="row mb-3"><label class="col-3 col-form-label fw-bold">Entidad</label>
                    <div class="col-9"><select class="form-control select2" name="macro_id" id="macro_id">
                            @foreach ($entidades as $entidad)
                                <option value="{{ $entidad->id_entidad }}">{{ $entidad->nome }}</option>
                            @endforeach
                        </select></div>
                </div>
                <div class="row mb-3"><label class="col-3 col-form-label fw-bold">Servicio</label>
                    <div class="col-9"><input type="text" class="form-control" name="nombre_servicio"
                            id="nombre_servicio" placeholder="Ejemplo: Pago de tasas" maxlength="150"><small
                            class="text-muted">Nombre del servicio que se evaluará en ANS</small></div>
                </div>
                <div class="row mb-3"><label class="col-3 col-form-label fw-bold">Tiempo máximo de espera</label>
                    <div class="col-9">
                        <div class="input-group"><input type="number" class="form-control" name="limite_espera"
                                id="limite_espera" placeholder="Minutos" min="0" max="120"><span
                                class="input-group-text">min</span></div>
                    </div>
                </div>
                <div class="row mb-3"><label class="col-3 col-form-label fw-bold">Tiempo máximo de atención</label>
                    <div class="col-9">
                        <div class="input-group"><input type="number" class="form-control" name="limite_atencion"
                                id="limite_atencion" placeholder="Minutos" min="0" max="180"><span
                                class="input-group-text">min</span></div>
                    </div>
                </div>
                <div class="row mb-3"><label class="col-3 col-form-label fw-bold">Estado</label>
                    <div class="col-9"><select class="form-control" name="status" id="status">
                            <option value="1" selected>Activo</option>
                            <option value="0">Inactivo</option>
                        </select></div>
                </div>
            </form>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-danger"
                data-bs-dismiss="modal">Cerrar</button><button type="button" class="btn btn-success"
                id="btnEnviarForm"><i class="fa fa-save"></i> Guardar Servicio</button></div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            dropdownParent: $('#modal_show_modal'),
            width: '100%'
        });
    });

    function validarServicio() {
        let ok = true;
        $('#formServicio input,#formServicio select').removeClass('is-invalid');
        if (!$('#macro_id').val()) {
            ok = false;
            $('#macro_id').addClass('is-invalid');
        }
        if (!$('#nombre_servicio').val()) {
            ok = false;
            $('#nombre_servicio').addClass('is-invalid');
        }
        if (!$('#limite_espera').val()) {
            ok = false;
            $('#limite_espera').addClass('is-invalid');
        }
        if (!$('#limite_atencion').val()) {
            ok = false;
            $('#limite_atencion').addClass('is-invalid');
        }
        return ok;
    }
    $('#btnEnviarForm').off('click').on('click', function() {
        if (!validarServicio()) {
            Swal.fire('Campos incompletos', 'Verifica los datos', 'warning');
            return;
        }
        let btn = $(this);
        let data = new FormData(document.getElementById('formServicio'));
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando');
        $.ajax({
            type: 'POST',
            url: "{{ route('servicio.store') }}",
            data: data,
            processData: false,
            contentType: false,
            success: function() {
                bootstrap.Modal.getInstance(document.getElementById('modal_show_modal')).hide();
                $('#formServicio')[0].reset();
                $('.select2').val('').trigger('change');
                if (typeof cargarServicios === 'function') {
                    cargarServicios();
                }
                Swal.fire('OK', 'Servicio registrado', 'success');
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar Servicio');
                Swal.fire('Error', xhr.responseJSON?.msg || 'No se pudo guardar', 'error');
            }
        });
    });
</script>
