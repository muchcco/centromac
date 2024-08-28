<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Añadir nuevo feriado</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="alerta">
            </div>            
            <h5>Agregar datos del feriado</h5>
            <form class="form-horizontal">
                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Nombre del Feriado</label>
                    <div class="col-9">
                        <input type="text" class="form-control" name="nombre_feriado" id="nombre_feriado"  onkeyup="isMayus(this)">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha</label>
                    <div class="col-9">
                        <input type="date" class="form-control" name="fecha_feriado" id="fecha_feriado">
                    </div>
                </div>
                <input type="hidden" name="id_centromac" id="id_centromac" value="1">

            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm" onclick="btnStoreFeriado()">Guardar</button>
        </div>
    </div>
</div>
