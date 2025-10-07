<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Subsanar Interrupción</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="alerta"></div>

            <form id="form_subsanar_interrupcion" class="form-horizontal">
                @csrf
                <input type="hidden" name="id_interrupcion" value="{{ $interrupcion->id_interrupcion }}">
                <input type="hidden" name="responsable" value="{{ $interrupcion->responsable }}">
                <input type="hidden" name="idcentro_mac" value="{{ $interrupcion->idcentro_mac }}">
                <input type="hidden" name="estado" id="estado" value="{{ $interrupcion->estado }}">

                <!-- Tipificación -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Tipificación</label>
                    <div class="col-9">
                        <select class="form-control" disabled>
                            @foreach ($tipos as $tipo)
                                <option
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
                        <select class="form-control" disabled>
                            @foreach ($entidades as $entidad)
                                <option {{ $interrupcion->identidad == $entidad->identidad ? 'selected' : '' }}>
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
                        <input type="text" class="form-control" value="{{ $interrupcion->servicio_involucrado }}"
                            disabled>
                    </div>
                </div>

                <!-- Fecha y Hora de Inicio -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha Inicio</label>
                    <div class="col-4">
                        <input type="date" class="form-control" value="{{ $interrupcion->fecha_inicio }}" disabled>
                    </div>
                    <label class="col-2 col-form-label text-center">Hora Inicio</label>
                    <div class="col-3">
                        <input type="time" class="form-control"
                            value="{{ \Carbon\Carbon::parse($interrupcion->hora_inicio)->format('H:i') }}" disabled>
                    </div>
                </div>

                <!-- Descripción -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Descripción</label>
                    <div class="col-9">
                        <textarea class="form-control" disabled>{{ $interrupcion->descripcion }}</textarea>
                    </div>
                </div>

                <!-- Datos para Subsanar -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Estado Final</label>
                    <div class="col-9">
                        <select class="form-control" name="estado_final" id="estado_final">
                            <option value="#">Seleccione...</option>
                            <option value="SUBSANADO"
                                {{ $interrupcion->estado == 'SUBSANADO' ? 'selected' : '' }}>SUBSANADO</option>
                            {{--  <option value="SUBSANADO SIN DOCUMENTO" {{ $interrupcion->estado == 'SUBSANADO SIN DOCUMENTO' ? 'selected' : '' }}>SUBSANADO SIN DOCUMENTO</option>
                            <option value="NO APLICA" {{ $interrupcion->estado == 'NO APLICA' ? 'selected' : '' }}>NO APLICA</option>
                        --}}
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
                            value="{{ \Carbon\Carbon::parse($interrupcion->hora_fin)->format('H:i') }}">
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
