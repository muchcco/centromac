<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Cambiar módulo para - {{ $personal->NOMBRE }}  {{ $personal->APE_PAT }} {{ $personal->APE_MAT }}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="alerta">
            </div> 
            <h5>Seleccionar un módulo de su centro MAC</h5>
            <form class="form-horizontal">
                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />
                <div class="row mb-3">
                    <label  class="col-3 col-form-label">Módulo</label>
                    <div class="col-9">
                        <select id="modulo" name="modulo" class="form-control">
                            <option value="" disabled selected>-- SELECCIONE UNA OPCION --</option>
                            @forelse ($modulo as $mod)
                                <option value="{{ $mod->IDMODULO }}" {{ $personal->IDMODULO == $mod->IDMODULO ? 'selected  disabled' : '' }} >{{ $mod->N_MODULO }}</option>
                            @empty
                                <option value="">No hay datos disponibles</option>
                            @endforelse
                        </select>
                        
                        
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm" onclick="btnUpdateEntidad('{{ $personal->IDPERSONAL }}')">Guardar</button>
        </div>
    </div>
</div>