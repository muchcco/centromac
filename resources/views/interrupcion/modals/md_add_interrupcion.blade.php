<style>
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #010220 !important;
        color: white !important;
        border: 1px solid #010220 !important;
        font-weight: bold;
    }
</style>

<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Registrar Nueva Interrupción</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="alerta"></div>
            <h5>Datos de la Interrupción</h5>

            <form id="form_add_interrupcion" class="form-horizontal">
                @csrf

                <input type="hidden" name="responsable" value="{{ auth()->user()->id }}">
                <input type="hidden" name="idcentro_mac" value="{{ auth()->user()->idcentro_mac }}">
                <input type="hidden" name="estado" id="estado" value="NO SUBSANADO">

                <!-- Tipificación -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Tipificación</label>
                    <div class="col-9">
                        <select class="form-control select2" name="id_tipo_int_obs" id="id_tipo_int_obs">
                            <option value="">--Seleccione--</option>
                            @foreach ($tipos as $tipo)
                                <option value="{{ $tipo->id_tipo_int_obs }}">
                                    {{ $tipo->tipo }} {{ $tipo->numeracion }} - {{ $tipo->nom_tipo_int_obs }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Entidad -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Entidad</label>
                    <div class="col-9">
                        <select class="form-control select2" name="identidad" id="identidad">
                            <option value="">--Seleccione--</option>
                            @foreach ($entidades as $entidad)
                                <option value="{{ $entidad->identidad }}">
                                    {{ $entidad->abrev_entidad }} - {{ $entidad->nombre_entidad }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Servicio Involucrado -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Servicio Involucrado</label>
                    <div class="col-9">
                        <input type="text" class="form-control" name="servicio_involucrado">
                    </div>
                </div>

                <!-- Fecha y hora inicio -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha Inicio</label>
                    <div class="col-4">
                        <input type="date" class="form-control" name="fecha_inicio">
                    </div>
                    <label class="col-2 col-form-label text-center">Hora Inicio</label>
                    <div class="col-3">
                        <input type="time" class="form-control" name="hora_inicio">
                    </div>
                </div>

                <!-- ¿Interrupción en curso? -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">¿Interrupción en curso?</label>
                    <div class="col-9">
                        <input type="checkbox" id="interrupcion_curso" checked>
                        <span id="estado_interrupcion">NO SUBSANADO</span>
                    </div>
                </div>

                <!-- Descripción -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Descripción</label>
                    <div class="col-9">
                        <textarea class="form-control" name="descripcion" rows="3"></textarea>
                    </div>
                </div>

                <!-- Acción tomada -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Acción Tomada</label>
                    <div class="col-9">
                        <textarea class="form-control" name="descripcion_accion" rows="3"></textarea>
                    </div>
                </div>

                <!-- Campos ocultos (cuando se desmarca el checkbox) -->
                <div id="campos_fin" style="display: none;">
                    <div class="row mb-3">
                        <label class="col-3 col-form-label">Estado Final</label>
                        <div class="col-9">
                            <select class="form-control select2" name="estado_final" id="estado_final">
                                <option value="SUBSANADO CON DOCUMENTO">Subsanado con Documento</option>
                                <option value="SUBSANADO SIN DOCUMENTO">Subsanado sin Documento</option>
                                <option value="NO APLICA">No Aplica</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-3 col-form-label">Acción Correctiva</label>
                        <div class="col-9">
                            <textarea class="form-control" name="accion_correctiva" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-3 col-form-label">Fecha Fin</label>
                        <div class="col-4">
                            <input type="date" class="form-control" name="fecha_fin">
                        </div>
                        <label class="col-2 col-form-label text-center">Hora Fin</label>
                        <div class="col-3">
                            <input type="time" class="form-control" name="hora_fin">
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm" onclick="btnStoreInterrupcion()">Guardar</button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('.select2').select2({
            dropdownParent: $('#modal_show_modal'),
            width: '100%',
            allowClear: true
        });

        function toggleCamposFin() {
            if ($('#interrupcion_curso').is(':checked')) {
                $('#estado_interrupcion').text('NO SUBSANADO');
                $('#estado').val('NO SUBSANADO');
                $('#campos_fin').hide();
            } else {
                $('#estado_interrupcion').text($('#estado_final option:selected').text());
                $('#estado').val($('#estado_final').val());
                $('#campos_fin').show();
            }
        }

        $('#interrupcion_curso').change(toggleCamposFin);
        $('#estado_final').change(function () {
            if (!$('#interrupcion_curso').is(':checked')) {
                $('#estado').val($(this).val());
                $('#estado_interrupcion').text($(this).find("option:selected").text());
            }
        });

        toggleCamposFin();
    });
</script>
