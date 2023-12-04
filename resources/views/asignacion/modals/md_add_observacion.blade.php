<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Estado del bien</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="alerta">
            </div>            
            <h5></h5>
            <form class="form-horizontal">
                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />
                <div class="row mb-3">
                    <label  class="col-3 col-form-label">Estado</label>
                    <div class="col-9">
                        <textarea name="observacion" id="observacion" cols="30" rows="3" class="form-control">{{ $asignacion->OBSERVACION }}</textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm" onclick="btnStoreObservacion('{{ $idasginacion }}')">Guardar</button>
        </div>
    </div>
</div>