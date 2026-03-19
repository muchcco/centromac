<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

        <div class="modal-header">
            <h4 class="modal-title">
                <i class="fa fa-pen text-primary"></i> Editar Servicio ANS
            </h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

            <div id="alerta"></div>

            <h5 class="mb-4">Actualizar datos del servicio</h5>

            <form class="form-horizontal">

                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />

                <div class="row mb-3">

                    <label class="col-3 col-form-label fw-bold">
                        Servicio
                    </label>

                    <div class="col-9">

                        <input type="text" class="form-control text-uppercase" value="{{ $servicio->nome }}"
                            readonly>

                        <small class="text-muted">
                            Identificador del servicio en el sistema
                        </small>

                    </div>

                </div>


                <div class="row mb-3">

                    <label class="col-3 col-form-label fw-bold">
                        Nombre del Servicio
                    </label>

                    <div class="col-9">

                        <input type="text" class="form-control" name="nombre_servicio" id="nombre_servicio"
                            value="{{ $servicio->nome }}" placeholder="Ejemplo: Pago de tasas" onkeyup="isMayus(this)"
                            maxlength="150">

                    </div>

                </div>


                <div class="row mb-3">

                    <label class="col-3 col-form-label fw-bold">
                        Tiempo máximo de espera
                    </label>

                    <div class="col-9">

                        <div class="input-group">

                            <input type="number" class="form-control" name="limite_espera" id="limite_espera"
                                value="{{ $servicio->limite_espera }}" min="0" max="120">

                            <span class="input-group-text">
                                min
                            </span>

                        </div>

                    </div>

                </div>


                <div class="row mb-3">

                    <label class="col-3 col-form-label fw-bold">
                        Tiempo máximo de atención
                    </label>

                    <div class="col-9">

                        <div class="input-group">

                            <input type="number" class="form-control" name="limite_atencion" id="limite_atencion"
                                value="{{ $servicio->limite_atencion }}" min="0" max="180">

                            <span class="input-group-text">
                                min
                            </span>

                        </div>

                    </div>

                </div>


                <div class="row mb-3">

                    <label class="col-3 col-form-label fw-bold">
                        Estado
                    </label>

                    <div class="col-9">

                        <select class="form-control" name="status" id="status">

                            <option value="1" {{ $servicio->status == 1 ? 'selected' : '' }}>
                                Activo
                            </option>

                            <option value="0" {{ $servicio->status == 0 ? 'selected' : '' }}>
                                Inactivo
                            </option>

                        </select>

                    </div>

                </div>

                <input type="hidden" id="id_servicio" value="{{ $servicio->id_servicio }}">

            </form>

        </div>


        <div class="modal-footer">

            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">
                Cerrar
            </button>

            <button type="button" class="btn btn-primary" id="btnEnviarForm"
                onclick="btnUpdateServicio('{{ $servicio->id_servicio }}')">
                <i class="fa fa-save"></i> Guardar Cambios
            </button>

        </div>

    </div>
</div>
