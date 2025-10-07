<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Subsanar Observación</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
            <div id="alerta"></div>

            <form id="form_subsanar_observacion" class="form-horizontal">
                @csrf
                <input type="hidden" name="id_observacion" value="{{ $observacion->id_observacion }}">
                <input type="hidden" name="estado" id="estado" value="{{ $observacion->estado }}">

                <!-- Tipificación (solo lectura) -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Tipificación</label>
                    <div class="col-9">
                        <select class="form-control" disabled>
                            @foreach ($tipos as $tipo)
                                <option {{ $observacion->id_tipo_int_obs == $tipo->id_tipo_int_obs ? 'selected' : '' }}>
                                    {{ $tipo->tipo }} {{ $tipo->numeracion }} - {{ $tipo->nom_tipo_int_obs }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Entidad (solo lectura) -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Entidad</label>
                    <div class="col-9">
                        <select class="form-control" disabled>
                            @foreach ($entidades as $entidad)
                                <option {{ $observacion->identidad == $entidad->identidad ? 'selected' : '' }}>
                                    {{ $entidad->abrev_entidad }} - {{ $entidad->nombre_entidad }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Descripción (solo lectura) -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Descripción</label>
                    <div class="col-9">
                        <textarea class="form-control" disabled>{{ $observacion->descripcion }}</textarea>
                    </div>
                </div>
                <!-- Sustento (archivo) -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Archivo Adjunto</label>
                    <div class="col-9">
                        <input type="file" class="form-control" name="archivo" accept=".pdf,.jpg,.jpeg,.png">
                        @if ($observacion->archivo)
                            <small class="text-muted">Archivo actual: <a href="{{ asset($observacion->archivo) }}"
                                    target="_blank">Ver archivo</a></small>
                        @endif
                    </div>
                </div>
                <!-- Acción Inmediata (solo lectura) -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Acción Inmediata</label>
                    <div class="col-9">
                        <textarea class="form-control" disabled>{{ $observacion->descripcion_accion }}</textarea>
                    </div>
                </div>

                <!-- Fecha Observación (solo lectura) -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha Observación</label>
                    <div class="col-9">
                        <input type="date" class="form-control" value="{{ $observacion->fecha_observacion }}"
                            disabled>
                    </div>
                </div>

                <!-- Estado Final -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Estado Final</label>
                    <div class="col-9">
                        <select class="form-control" name="estado_final" id="estado_final" required>
                            <option value="">Seleccione...</option>
                            <option value="SUBSANADO"
                                {{ $observacion->estado == 'SUBSANADO' ? 'selected' : '' }}>SUBSANADO</option>
                          {{--   <option value="SUBSANADO SIN DOCUMENTO"
                                {{ $observacion->estado == 'SUBSANADO SIN DOCUMENTO' ? 'selected' : '' }}>SUBSANADO SIN
                                DOCUMENTO</option>
                            <option value="NO APLICA" {{ $observacion->estado == 'NO APLICA' ? 'selected' : '' }}>NO
                                APLICA</option> --}}
                        </select>
                    </div>
                </div>

                <!-- Fecha de Subsanación (solo fecha) -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha de Subsanación</label>
                    <div class="col-9">
                        <input type="date" class="form-control" name="fecha_solucion"
                            value="{{ $observacion->fecha_solucion }}">
                    </div>
                </div>
            </form>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" onclick="btnSubsanarGuardar()">Guardar</button>
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
