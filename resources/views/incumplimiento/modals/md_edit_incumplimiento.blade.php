<style>
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #132842 !important;
        color: white !important;
        border: 1px solid #132842 !important;
        font-weight: bold;
    }

    .text-error {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 4px;
    }
</style>

<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Editar Incidente</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="alerta"></div>
            <h5>Datos del Incidente</h5>

            <form id="form_edit_incumplimiento" class="form-horizontal">
                @csrf
                <input type="hidden" name="id_observacion" value="{{ $incumplimiento->id_observacion }}">
                <input type="hidden" name="responsable" value="{{ $incumplimiento->responsable }}">
                <input type="hidden" name="idcentro_mac" value="{{ $incumplimiento->idcentro_mac }}">
                <input type="hidden" name="estado" id="estado" value="{{ $incumplimiento->estado }}">

                <!-- Tipificación -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Tipificación</label>
                    <div class="col-9">
                        <select class="form-control select2" name="id_tipo_int_obs" id="id_tipo_int_obs">
                            <option value="">--Seleccione--</option>
                            @foreach ($tipos as $tipo)
                                <option value="{{ $tipo->id_tipo_int_obs }}"
                                    {{ $incumplimiento->id_tipo_int_obs == $tipo->id_tipo_int_obs ? 'selected' : '' }}>
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
                                    {{ $incumplimiento->identidad == $entidad->identidad ? 'selected' : '' }}>
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
                        <textarea class="form-control" name="descripcion" rows="3">{{ $incumplimiento->descripcion }}</textarea>
                    </div>
                </div>

                <!-- Archivo Adjunto -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Archivo / Sustento</label>
                    <div class="col-9">
                        <input type="file" class="form-control" name="archivo" accept=".pdf,image/*">
                        @if ($incumplimiento->archivo)
                            <div class="mt-2">
                                <a href="{{ asset($incumplimiento->archivo) }}" target="_blank">
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
                        <textarea class="form-control" name="descripcion_accion" rows="3">{{ $incumplimiento->descripcion_accion }}</textarea>
                    </div>
                </div>

                <!-- Fecha Incidente -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha Incidente</label>
                    <div class="col-9">
                        <input type="date" class="form-control" name="fecha_observacion" id="fecha_observacion"
                            value="{{ $incumplimiento->fecha_observacion }}">
                    </div>
                </div>

                <!-- Estado y cierre -->
                <div class="row mb-3" id="bloque_estado">
                    <label class="col-3 col-form-label">¿Incidente en curso?</label>
                    <div class="col-9">
                        <input type="checkbox" id="incumplimiento_curso"
                            {{ $incumplimiento->estado == 'ABIERTO' ? 'checked' : '' }}>
                        <span id="estado_label" class="ms-2">{{ $incumplimiento->estado }}</span>
                    </div>
                </div>

                <!-- Fecha cierre -->
                <div id="campos_cierre" style="{{ $incumplimiento->estado == 'CERRADO' ? '' : 'display:none;' }}">
                    <div class="row mb-1">
                        <label class="col-3 col-form-label">Fecha Cierre</label>
                        <div class="col-9">
                            <input type="date" class="form-control" name="fecha_solucion" id="fecha_solucion"
                                value="{{ $incumplimiento->fecha_solucion }}">
                            <small id="error_fecha_cierre" class="text-error" style="display:none;"></small>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm">Guardar</button>
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

        // ===============================
        // 🧩 CONTROL DE ESTADO MANUAL
        // ===============================
        function toggleCamposCierre() {
            if ($('#incumplimiento_curso').is(':checked')) {
                $('#estado').val('ABIERTO');
                $('#estado_label').text('ABIERTO');
                $('#campos_cierre').hide();
            } else {
                $('#estado').val('CERRADO');
                $('#estado_label').text('CERRADO');
                $('#campos_cierre').show();
            }
            $('#error_fecha_cierre').hide();
        }

        $('#incumplimiento_curso').change(toggleCamposCierre);
        toggleCamposCierre();

        // ===============================
        // 🟨 I5: CIERRE AUTOMÁTICO
        // ===============================
        $('#id_tipo_int_obs').on('change', function() {
            const tipo = parseInt($(this).val());
            const fechaIncidente = $('#fecha_observacion').val();

            if (tipo === 35) { // Tipología I5
                $('#bloque_estado').hide();
                $('#campos_cierre').show();
                $('#estado').val('CERRADO');
                $('#estado_label').text('CERRADO');

                if (fechaIncidente) {
                    $('#fecha_solucion').val(fechaIncidente);
                }

                $('#fecha_solucion').prop('readonly', true);
            } else {
                $('#bloque_estado').show();
                $('#fecha_solucion').prop('readonly', false);
                toggleCamposCierre();
            }
        });

        // Si ya es I5 al abrir, aplicar el mismo comportamiento
        const tipoActual = parseInt($('#id_tipo_int_obs').val());
        if (tipoActual === 35) {
            $('#bloque_estado').hide();
            $('#campos_cierre').show();
            $('#estado').val('CERRADO');
            $('#estado_label').text('CERRADO');
            $('#fecha_solucion').val($('#fecha_observacion').val());
            $('#fecha_solucion').prop('readonly', true);
        }

        // Si cambia la fecha del incidente y es tipo I5, se actualiza cierre
        $('#fecha_observacion').on('change', function() {
            const tipo = parseInt($('#id_tipo_int_obs').val());
            if (tipo === 35) {
                $('#fecha_solucion').val($(this).val());
            }
        });

        // ===============================
        // 🧩 VALIDACIÓN FINAL Y ENVÍO
        // ===============================
        $('#btnEnviarForm').on('click', function() {
            const fechaIncidente = new Date($('#fecha_observacion').val());
            const fechaCierre = new Date($('#fecha_solucion').val());
            const estado = $('#estado').val();
            const tipo = parseInt($('#id_tipo_int_obs').val());
            const errorMsg = $('#error_fecha_cierre');

            errorMsg.hide();

            if (estado === 'CERRADO' && tipo !== 35) {
                if (!$('#fecha_solucion').val()) {
                    errorMsg.text('Debe ingresar la fecha de cierre.').show();
                    return;
                }
                if (fechaCierre < fechaIncidente) {
                    errorMsg.text('⚠️ La fecha de cierre no puede ser menor que la del incidente.')
                        .show();
                    return;
                }
            }

            btnUpdateIncumplimiento();
        });
    });
</script>
