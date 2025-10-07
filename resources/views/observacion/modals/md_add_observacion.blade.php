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
            <h4 class="modal-title">Registrar Nueva Observación</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="alerta"></div>
            <h5>Datos de la Observación</h5>

            <form id="form_add_observacion" class="form-horizontal">
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

                <!-- Descripción -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Descripción</label>
                    <div class="col-9">
                        <textarea class="form-control" name="descripcion" rows="3"></textarea>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Sustento (PDF o Imagen)</label>
                    <div class="col-9">
                        <input type="file" class="form-control" name="archivo" accept=".pdf,image/*">
                    </div>
                </div>
                <!-- Acción Inmediata -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Acción Tomada</label>
                    <div class="col-9">
                        <textarea class="form-control" name="descripcion_accion" rows="3"></textarea>
                    </div>
                </div>

                <!-- Fecha y hora de la observación -->
                <!-- Solo fecha de la observación -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha Observación</label>
                    <div class="col-9">
                        <input type="date" class="form-control" name="fecha_observacion" required>
                    </div>
                </div>
                <!-- ¿Observación en curso? -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">¿Observación en curso?</label>
                    <div class="col-9">
                        <input type="checkbox" id="observacion_curso" checked>
                        <span id="estado_label" class="ms-2">NO SUBSANADO</span>
                    </div>
                </div>

                <!-- Campos de solución (ocultos por defecto) -->
                <div id="campos_solucion" style="display: none;">
                    <div class="row mb-3">
                        <label class="col-3 col-form-label">Estado Final</label>
                        <div class="col-9">
                            <select class="form-control select2" name="estado_final" id="estado_final">
                                <option value="SUBSANADO">Subsanado</option>
                               {{--  <option value="SUBSANADO SIN DOCUMENTO">Subsanado sin Documento</option>
                                <option value="NO APLICA">No Aplica</option> --}}
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-3 col-form-label">Fecha Solución</label>
                        <div class="col-9">
                            <input type="date" class="form-control" name="fecha_solucion">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <input type="hidden" name="idcentro_mac" value="{{ auth()->user()->idcentro_mac }}">

        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm"
                onclick="btnStoreObservacion()">Guardar</button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.select2').select2({
            dropdownParent: $('#modal_show_modal'),
            width: '100%',
            allowClear: true
        });

        function toggleCamposSolucion() {
            if ($('#observacion_curso').is(':checked')) {
                $('#estado').val('NO SUBSANADO');
                $('#estado_label').text('NO SUBSANADO');
                $('#campos_solucion').hide();
            } else {
                $('#campos_solucion').show();
                const estadoFinal = $('#estado_final').val();
                $('#estado').val(estadoFinal);
                $('#estado_label').text($('#estado_final option:selected').text());
            }
        }

        $('#observacion_curso').change(toggleCamposSolucion);

        $('#estado_final').change(function() {
            if (!$('#observacion_curso').is(':checked')) {
                $('#estado').val($(this).val());
                $('#estado_label').text($(this).find("option:selected").text());
            }
        });

        toggleCamposSolucion(); // Inicial
    });
</script>
