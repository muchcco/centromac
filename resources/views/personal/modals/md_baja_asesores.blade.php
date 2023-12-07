<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Baja para {{ $personal->NOMBRE }}  {{ $personal->APE_PAT }} {{ $personal->APE_MAT }}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="alerta">
            </div> 
            <h5>Seleccionar que tipo de baja se realizará al asesor de servicio</h5>
            <form class="form-horizontal">
                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />
                <div class="row mb-3">
                    <label  class="col-3 col-form-label">Tipo de baja</label>
                    <div class="col-9">
                        <select id="baja" name="baja" class="form-control">
                            <option value="" disabled selected>-- SELECCIONE UNA OPCION --</option>
                            <option value="0">Baja del centro MAC</option>
                            <option value="1">Elimiinar del centro MAC</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm" onclick="btnBajaAsesor('{{ $personal->IDPERSONAL }}')">Guardar</button>
        </div>
    </div>
</div>