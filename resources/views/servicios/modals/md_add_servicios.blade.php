<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Añadir nuevo servicio</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="alerta">
            </div>            
            <h5>Agregar datos</h5>
            <form class="form-horizontal">
                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />
                <div class="row mb-3">
                    <label  class="col-3 col-form-label">Entidad</label>
                    <div class="col-9">
                        <select id="entidad" name="entidad" class="form-control">
                            <option disabled selected>-- Seleccione una opción --</option>
                            @forelse ($entidad as $e)
                                <option value="{{ $e->IDENTIDAD }}">{{ $e->NOMBRE_ENTIDAD }}</option>                                
                            @empty
                                <option value="">No hay conexón</option>
                            @endforelse
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <label  class="col-3 col-form-label">Nombre del servicio</label>
                    <div class="col-9">
                        <input type="text" class="form-control" name="nombre_servicio" id="nombre_servicio"  onkeyup="isMayus(this)">
                    </div>
                </div>
                <div class="row mb-3">
                    <label  class="col-3 col-form-label">Trámite</label>
                    <div class="col-md-9 pt-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input tramite" type="radio" name="tramite" id="tramite_1" value="1">
                            <label class="form-check-label" for="tramite_1">SI</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="tramite" id="tramite_2" value="2">
                            <label class="form-check-label" for="tramite_2">NO</label>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <label  class="col-3 col-form-label">Orientación</label>
                    <div class="col-md-9 pt-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input orientacion" type="radio" name="orientacion" id="orientacion_1" value="1">
                            <label class="form-check-label" for="orientacion_1">SI</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="orientacion" id="orientacion_2" value="2">
                            <label class="form-check-label" for="orientacion_2">NO</label>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <label  class="col-3 col-form-label">Costo del servicio</label>
                    <div class="col-9">
                        <input type="text" class="form-control" name="costo_serv" id="costo_serv"  onkeyup="isMayus(this)">
                    </div>
                </div>
                <div class="row mb-3">
                    <label  class="col-3 col-form-label">Requiere cita</label>
                    <div class="col-9">
                        <input type="text" class="form-control" name="req_cita" id="req_cita"  onkeyup="isMayus(this)">
                    </div>
                </div>
                <div class="row mb-3">
                    <label  class="col-3 col-form-label">Requisitos</label>
                    <div class="col-9">
                        <textarea name="requisito_servicio" id="requisito_servicio" cols="30" rows="10" class="form-control"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm" onclick="btnStoreServicio()">Guardar</button>
        </div>
    </div>
</div>