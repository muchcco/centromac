<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Añadir nueva Tipificacion</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="alerta"></div>
            <h5>Datos del Tipificación</h5>

            <form id="form_add_tipo_obs" class="form-horizontal">
                @csrf


                <!-- Campo tipo_obs (OBSERVACION o INTERRUPCION) -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Tipo de Registro</label>
                    <div class="col-9">
                        <select class="form-control" name="tipo_obs" id="tipo_obs">
                            <option value="" selected>--Seleccione--</option>
                            <option value="OBSERVACIÓN">OBSERVACIÓN</option>
                            <option value="INTERRUPCIÓN">INTERRUPCIÓN</option>
                            <option value="INCUMPLIMIENTO">INCIDENTE OPERTATIVO</option>
                        </select>
                    </div>
                </div>
                <!-- Campo tipo (A, B o C) -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Tipo</label>
                    <div class="col-9">
                        <select class="form-control" name="tipo" id="tipo">
                            <option value="" selected>--Seleccione--</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="I">I</option>
                        </select>
                    </div>
                </div>
                <!-- Campo numeracion -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Numeración</label>
                    <div class="col-9">
                        <input type="number" class="form-control" name="numeracion" id="numeracion">
                    </div>
                </div>

                <!-- Campo nombre del tipo -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Nombre</label>
                    <div class="col-9">
                        <input type="text" class="form-control" name="nom_tipo_int_obs" id="nom_tipo_int_obs"
                            onkeyup="isMayus(this)">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Descripcion</label>
                    <div class="col-9">
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="5" placeholder="Escribe una descripción(Opcional)"></textarea>
                    </div>
                </div>
            </form>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm"
                onclick="btnStoreTipoObs()">Guardar</button>
        </div>
    </div>
</div>
