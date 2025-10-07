<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header bg-dark text-white">
            <h4 class="modal-title">Cerrar Incidente</h4>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
            <div id="alerta"></div>

            <form id="form_cerrar_incumplimiento" class="form-horizontal">
                @csrf
                <input type="hidden" name="id_observacion" value="{{ $incumplimiento->id_observacion }}">
                <input type="hidden" name="estado" id="estado" value="{{ $incumplimiento->estado }}">

                <!-- Tipificación -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Tipificación</label>
                    <div class="col-9">
                        <input type="text" class="form-control"
                            value="{{ $incumplimiento->tipoIntObs->tipo ?? '' }} {{ $incumplimiento->tipoIntObs->numeracion ?? '' }} - {{ $incumplimiento->tipoIntObs->nom_tipo_int_obs ?? '' }}"
                            disabled>
                    </div>
                </div>

                <!-- Entidad -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Entidad</label>
                    <div class="col-9">
                        <input type="text" class="form-control"
                            value="{{ $incumplimiento->entidad->abrev_entidad ?? '' }} - {{ $incumplimiento->entidad->nombre_entidad ?? '' }}"
                            disabled>
                    </div>
                </div>

                <!-- Descripción -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Descripción</label>
                    <div class="col-9">
                        <textarea class="form-control" disabled>{{ $incumplimiento->descripcion }}</textarea>
                    </div>
                </div>

                <!-- Archivo de Cierre -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Archivo de Cierre</label>
                    <div class="col-9">
                        <input type="file" class="form-control" name="archivo" accept=".pdf,.jpg,.jpeg,.png">
                        @if ($incumplimiento->archivo)
                            <small class="text-muted">Archivo actual:
                                <a href="{{ asset($incumplimiento->archivo) }}" target="_blank">Ver archivo</a>
                            </small>
                        @endif
                    </div>
                </div>

                <!-- Fecha Incumplimiento -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha Incidente</label>
                    <div class="col-9">
                        <input type="date" class="form-control" value="{{ $incumplimiento->fecha_observacion }}"
                            disabled>
                    </div>
                </div>

                <!-- Estado Final -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Estado Final</label>
                    <div class="col-9">
                        <select class="form-control" name="estado_final" id="estado_final" required>
                            <option value="">Seleccione...</option>
                            <option value="CERRADO" {{ $incumplimiento->estado == 'CERRADO' ? 'selected' : '' }}>
                                CERRADO</option>
                            <option value="ABIERTO" {{ $incumplimiento->estado == 'ABIERTO' ? 'selected' : '' }}>
                                ABIERTO</option>
                        </select>
                    </div>
                </div>

                <!-- Fecha de Cierre -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha de Cierre</label>
                    <div class="col-9">
                        <input type="date" class="form-control" name="fecha_solucion"
                            value="{{ $incumplimiento->fecha_solucion }}">
                    </div>
                </div>
            </form>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-outline-success" onclick="btnCerrarGuardar()">Guardar</button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#estado_final').change(function() {
            $('#estado').val($(this).val());
        });
    });
</script>
