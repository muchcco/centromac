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
            <h4 class="modal-title">Editar Interrupción</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="alerta"></div>
            <h5>Editar datos de la Interrupción</h5>

            <form id="form_edit_interrupcion" class="form-horizontal">
                @csrf
                <input type="hidden" name="id_interrupcion" value="{{ $interrupcion->id_interrupcion }}">
                <input type="hidden" name="responsable" value="{{ $interrupcion->responsable }}">
                <input type="hidden" name="idcentro_mac" value="{{ $interrupcion->idcentro_mac }}">
                <input type="hidden" name="estado" id="estado" value="{{ $interrupcion->estado }}">

                <!-- Tipificación -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Tipificación</label>
                    <div class="col-9">
                        <select class="form-control select2" name="id_tipo_int_obs" id="id_tipo_int_obs">
                            <option value="">--Seleccione--</option>
                            @foreach ($tipos as $tipo)
                                <option value="{{ $tipo->id_tipo_int_obs }}"
                                    {{ $interrupcion->id_tipo_int_obs == $tipo->id_tipo_int_obs ? 'selected' : '' }}>
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
                                    {{ $interrupcion->identidad == $entidad->identidad ? 'selected' : '' }}>
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
                        <input type="text" class="form-control" name="servicio_involucrado"
                            value="{{ $interrupcion->servicio_involucrado }}">
                    </div>
                </div>

                <!-- Fecha Inicio y Hora -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha Inicio</label>
                    <div class="col-4">
                        <input type="date" class="form-control" name="fecha_inicio"
                            value="{{ $interrupcion->fecha_inicio }}">
                    </div>
                    <label class="col-2 col-form-label text-center">Hora Inicio</label>
                    <div class="col-3">
                        <input type="time" class="form-control" name="hora_inicio"
                            value="{{ \Carbon\Carbon::parse($interrupcion->hora_inicio)->format('H:i') }}">
                    </div>
                </div>

                <!-- Interrupción en curso -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">¿Interrupción en curso?</label>
                    <div class="col-9">
                        <input type="checkbox" id="interrupcion_curso"
                            {{ $interrupcion->estado == 'NO SUBSANADO' ? 'checked' : '' }}>
                        <span
                            id="estado_interrupcion">{{ $interrupcion->estado == 'NO SUBSANADO' ? 'Sí' : 'No' }}</span>
                    </div>
                </div>

                <!-- Descripción -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Descripción</label>
                    <div class="col-9">
                        <textarea class="form-control" name="descripcion">{{ $interrupcion->descripcion }}</textarea>
                    </div>
                </div>

                <!-- Campos finales -->
                <div id="campos_fin" style="{{ $interrupcion->estado == 'NO SUBSANADO' ? 'display:none;' : '' }}">
                    <div class="row mb-3">
                        <label class="col-3 col-form-label">Estado Final</label>
                        <div class="col-9">
                            <select class="form-control select2" name="estado_final" id="estado_final">
                                <option value="SUBSANADO CON DOCUMENTO"
                                    {{ $interrupcion->estado == 'SUBSANADO CON DOCUMENTO' ? 'selected' : '' }}>
                                    SUBSANADO CON DOCUMENTO</option>
                                <option value="SUBSANADO SIN DOCUMENTO"
                                    {{ $interrupcion->estado == 'SUBSANADO SIN DOCUMENTO' ? 'selected' : '' }}>
                                    SUBSANADO SIN DOCUMENTO</option>
                                <option value="NO APLICA" {{ $interrupcion->estado == 'NO APLICA' ? 'selected' : '' }}>
                                    NO APLICA</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-3 col-form-label">Acción Correctiva</label>
                        <div class="col-9">
                            <textarea class="form-control" name="accion_correctiva">{{ $interrupcion->accion_correctiva }}</textarea>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-3 col-form-label">Fecha Fin</label>
                        <div class="col-4">
                            <input type="date" class="form-control" name="fecha_fin"
                                value="{{ $interrupcion->fecha_fin }}">
                        </div>
                        <label class="col-2 col-form-label text-center">Hora Fin</label>
                        <div class="col-3">
                            <input type="time" class="form-control" name="hora_fin"
                                value="{{ $interrupcion->hora_fin ? \Carbon\Carbon::parse($interrupcion->hora_fin)->format('H:i') : '' }}">
                        </div>
                    </div>
                </div>

            </form>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" onclick="btnUpdateInterrupcion()">Guardar</button>
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

        function toggleCamposFin() {
            if ($('#interrupcion_curso').is(':checked')) {
                $('#estado_interrupcion').text('Sí');
                $('#estado').val('NO SUBSANADO');
                $('#campos_fin').hide();
            } else {
                $('#estado_interrupcion').text('No');
                $('#estado').val($('#estado_final').val());
                $('#campos_fin').show();
            }
        }

        toggleCamposFin();

        $('#interrupcion_curso').change(function() {
            toggleCamposFin();
        });

        $('#estado_final').change(function() {
            if (!$('#interrupcion_curso').is(':checked')) {
                $('#estado').val($(this).val());
            }
        });
    });
</script>
