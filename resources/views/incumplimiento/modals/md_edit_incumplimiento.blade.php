<style>
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #8B0000 !important;
        color: white !important;
        border: 1px solid #8B0000 !important;
        font-weight: bold;
    }
</style>

<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Editar Incumplimiento</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="alerta"></div>
            <h5>Datos del Incumplimiento</h5>

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
                        <input type="file" class="form-control" name="archivo">
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

                <!-- Fecha Incumplimiento -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha Incumplimiento</label>
                    <div class="col-9">
                        <input type="date" class="form-control" name="fecha_observacion"
                            value="{{ $incumplimiento->fecha_observacion }}">
                    </div>
                </div>

                <!-- Estado -->
                @role('Administrador|Monitor')
                    <div class="row mb-3">
                        <label class="col-3 col-form-label">¿Incumplimiento en curso?</label>
                        <div class="col-9">
                            <input type="checkbox" id="incumplimiento_curso"
                                {{ $incumplimiento->estado == 'ABIERTO' ? 'checked' : '' }}>
                            <span id="estado_label">{{ $incumplimiento->estado }}</span>
                        </div>
                    </div>

                    <!-- Fecha cierre -->
                    <div id="campos_cierre" style="{{ $incumplimiento->estado == 'CERRADO' ? '' : 'display:none;' }}">
                        <div class="row mb-3">
                            <label class="col-3 col-form-label">Fecha Cierre</label>
                            <div class="col-9">
                                <input type="date" class="form-control" name="fecha_solucion"
                                    value="{{ $incumplimiento->fecha_solucion }}">
                            </div>
                        </div>
                    </div>
                @else
                    <div class="row mb-3">
                        <label class="col-3 col-form-label">Estado</label>
                        <div class="col-9">
                            <span class="badge {{ $incumplimiento->estado == 'ABIERTO' ? 'bg-success' : 'bg-danger' }}">
                                {{ $incumplimiento->estado }}
                            </span>
                            <small class="text-muted">(solo Monitor o Administrador pueden cerrarlo)</small>
                        </div>
                    </div>
                @endrole
            </form>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm"
                onclick="btnUpdateIncumplimiento()">Guardar</button>
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
        }

        $('#incumplimiento_curso').change(toggleCamposCierre);

        toggleCamposCierre(); // Inicial
    });
</script>
