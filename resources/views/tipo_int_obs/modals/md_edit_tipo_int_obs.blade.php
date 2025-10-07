<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Editar Tipificación</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="alerta"></div>
            <h5>Editar Tipificación</h5>
            <form class="form-horizontal">
                @csrf

                <!-- Campo tipo_obs (INTERRUPCION u OBSERVACION) -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Tipo de Registro</label>
                    <div class="col-9">
                        <select class="form-control" name="tipo_obs" id="tipo_obs">
                            <option value="">--Seleccione--</option>
                            <option value="OBSERVACIÓN" {{ $tipo->tipo_obs === 'OBSERVACIÓN' ? 'selected' : '' }}>
                                OBSERVACIÓN</option>
                            <option value="INTERRUPCION" {{ $tipo->tipo_obs === 'INTERRUPCIÓN' ? 'selected' : '' }}>
                                INTERRUPCIÓN</option>
                            <option value="INCUMPLIMIENTO" {{ $tipo->tipo_obs === 'INCUMPLIMIENTO' ? 'selected' : '' }}>
                                INCIDENTE OPERTATIVO</option>
                        </select>
                    </div>
                </div>
                <!-- Campo tipo (A, B, C) -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Tipo (A, B o C)</label>
                    <div class="col-9">
                        <select class="form-control" name="tipo" id="tipo">
                            <option value="">--Seleccione--</option>
                            <option value="A" {{ $tipo->tipo === 'A' ? 'selected' : '' }}>A</option>
                            <option value="B" {{ $tipo->tipo === 'B' ? 'selected' : '' }}>B</option>
                            <option value="C" {{ $tipo->tipo === 'C' ? 'selected' : '' }}>C</option>
                            <option value="I" {{ $tipo->tipo === 'I' ? 'selected' : '' }}>I</option>
                        </select>
                    </div>
                </div>

                <!-- Campo numeracion (puede ser null) -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Numeración</label>
                    <div class="col-9">
                        <input type="number" class="form-control" name="numeracion" id="numeracion"
                            value="{{ $tipo->numeracion }}">
                    </div>
                </div>

                <!-- Campo nombre del tipo -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Nombre</label>
                    <div class="col-9">
                        <input type="text" class="form-control" name="nom_tipo_int_obs" id="nom_tipo_int_obs"
                            onkeyup="isMayus(this)" value="{{ $tipo->nom_tipo_int_obs }}">
                    </div>
                </div>
                <!-- Campo descripcion -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Descripción</label>
                    <div class="col-9">
                        <textarea class="form-control" name="descripcion" id="descripcion" rows="5" placeholder="Escribe una descripción">{{ $tipo->descripcion }}</textarea>
                    </div>
                </div>

            </form>
        </div>
        <div class="modal-footer">
            <!-- Reemplaza 'id_tipo_int_obs' por la clave primaria real que uses -->
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm"
                onclick="btnUpdateTipoObs('{{ $tipo->id_tipo_int_obs }}')">Guardar</button>
        </div>
    </div>
</div>
