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
            <h4 class="modal-title">Registrar Nuevo Incidente Operativo</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
            <div id="alerta"></div>
            <h5>Datos del Incidente</h5>

            <form id="form_add_incumplimiento" class="form-horizontal">
                @csrf
                <input type="hidden" name="responsable" value="{{ auth()->user()->id }}">
                <input type="hidden" name="idcentro_mac" value="{{ auth()->user()->idcentro_mac }}">
                <input type="hidden" name="estado" id="estado" value="ABIERTO">

                <!-- Tipificación -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Tipificación</label>
                    <div class="col-9">
                        <select class="form-control select2" name="id_tipo_int_obs" id="id_tipo_int_obs" required>
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
                        <select class="form-control select2" name="identidad" id="identidad" required>
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
                        <textarea class="form-control" name="descripcion" rows="3" required></textarea>
                    </div>
                </div>

                <!-- Sustento -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Sustento (PDF o Imagen)</label>
                    <div class="col-9">
                        <input type="file" class="form-control" name="archivo" accept=".pdf,image/*">
                    </div>
                </div>

                <!-- Acción -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Acción Tomada</label>
                    <div class="col-9">
                        <textarea class="form-control" name="descripcion_accion" rows="3"></textarea>
                    </div>
                </div>

                <!-- Fecha -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha Incidente</label>
                    <div class="col-9">
                        <input type="date" class="form-control" name="fecha_observacion" id="fecha_observacion"
                            required>
                    </div>
                </div>

                <!-- Estado -->
                <div class="row mb-3" id="bloque_estado">
                    <label class="col-3 col-form-label">Incidente en curso?</label>
                    <div class="col-9">
                        <input type="checkbox" id="incumplimiento_curso" checked>
                        <span id="estado_label" class="ms-2">ABIERTO</span>
                    </div>
                </div>

                <!-- Fecha cierre -->
                <div id="campos_cierre" style="display: none;">
                    <div class="row mb-1">
                        <label class="col-3 col-form-label">Fecha Cierre</label>
                        <div class="col-9">
                            <input type="date" class="form-control" name="fecha_solucion" id="fecha_solucion">
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
                $('#bloque_estado').hide(); // ocultar check de “en curso”
                $('#campos_cierre').show(); // mostrar campo de cierre
                $('#estado').val('CERRADO');
                $('#estado_label').text('CERRADO');

                // si ya tiene fecha incidente, igualar
                if (fechaIncidente) {
                    $('#fecha_solucion').val(fechaIncidente);
                }

                $('#fecha_solucion').prop('readonly', true); // bloquear campo
            } else {
                $('#bloque_estado').show();
                $('#fecha_solucion').prop('readonly', false);
                toggleCamposCierre();
            }
        });

        // si cambia la fecha del incidente y es tipo I5, se actualiza fecha cierre
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
            const errorMsg = $('#error_fecha_cierre');
            const tipo = parseInt($('#id_tipo_int_obs').val());

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

            // 🔹 Enviar
            btnStoreIncumplimiento();
        });
    });
</script>
