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
            <h4 class="modal-title">Editar Observación</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="alerta"></div>
            <h5>Datos de la Observación</h5>

            <form id="form_edit_observacion" class="form-horizontal">
                @csrf
                <input type="hidden" name="id_observacion" value="{{ $observacion->id_observacion }}">
                <input type="hidden" name="responsable" value="{{ $observacion->responsable }}">
                <input type="hidden" name="id_centromac" value="{{ $observacion->id_centromac }}">
                <input type="hidden" name="estado" id="estado" value="{{ $observacion->estado }}">

                <!-- Tipificación -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Tipificación</label>
                    <div class="col-9">
                        <select class="form-control select2" name="id_tipo_int_obs" id="id_tipo_int_obs">
                            <option value="">--Seleccione--</option>
                            @foreach ($tipos as $tipo)
                                <option value="{{ $tipo->id_tipo_int_obs }}"
                                    {{ $observacion->id_tipo_int_obs == $tipo->id_tipo_int_obs ? 'selected' : '' }}>
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
                                <option value="{{ $entidad->identidad }}"
                                    {{ $observacion->identidad == $entidad->identidad ? 'selected' : '' }}>
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
                        <textarea class="form-control" name="descripcion" rows="3">{{ $observacion->descripcion }}</textarea>
                    </div>
                </div>
                <!-- Archivo Adjunto -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Archivo / Sustento</label>
                    <div class="col-9">
                        <input type="file" class="form-control" name="archivo">
                        @if ($observacion->archivo)
                            <div class="mt-2">
                                <a href="{{ asset($observacion->archivo) }}" target="_blank">
                                    Ver archivo actual
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Acción Tomada -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Acción Tomada</label>
                    <div class="col-9">
                        <textarea class="form-control" name="descripcion_accion" rows="3">{{ $observacion->descripcion_accion }}</textarea>
                    </div>
                </div>

                <!-- Fecha Observación -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha Observación</label>
                    <div class="col-9">
                        <input type="date" class="form-control" name="fecha_observacion"
                            value="{{ $observacion->fecha_observacion }}">
                    </div>
                </div>

                <!-- ¿Observación en curso? -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">¿Observación en curso?</label>
                    <div class="col-9">
                        <input type="checkbox" id="observacion_curso"
                            {{ $observacion->estado == 'NO SUBSANADO' ? 'checked' : '' }}>
                        <span id="estado_label">{{ $observacion->estado }}</span>
                    </div>
                </div>

                <!-- Campos de solución -->
                <div id="campos_solucion" style="{{ $observacion->estado == 'NO SUBSANADO' ? 'display:none;' : '' }}">
                    <div class="row mb-3">
                        <label class="col-3 col-form-label">Estado Final</label>
                        <div class="col-9">
                            <select class="form-control select2" name="estado_final" id="estado_final">
                                <option value="SUBSANADO CON DOCUMENTO"
                                    {{ $observacion->estado == 'SUBSANADO CON DOCUMENTO' ? 'selected' : '' }}>Subsanado
                                    con Documento</option>
                                <option value="SUBSANADO SIN DOCUMENTO"
                                    {{ $observacion->estado == 'SUBSANADO SIN DOCUMENTO' ? 'selected' : '' }}>Subsanado
                                    sin Documento</option>
                                <option value="NO APLICA" {{ $observacion->estado == 'NO APLICA' ? 'selected' : '' }}>
                                    No Aplica</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-3 col-form-label">Fecha Solución</label>
                        <div class="col-9">
                            <input type="date" class="form-control" name="fecha_solucion"
                                value="{{ $observacion->fecha_solucion }}">
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm"
                onclick="btnUpdateObservacion()">Guardar</button>
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
                let estadoFinal = $('#estado_final').val();
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
