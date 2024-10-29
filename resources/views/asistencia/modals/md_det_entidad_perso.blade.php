<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Descarga Personalizada por Fecha</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            
            <form action="" class="form">                
                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />
                <input type="hidden" name="mac" id="mac" value="{{ $mac }}" />

                <div class="row">
                    <div class="col">
                        <label for="">Fecha de Inicio</label>
                      <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio" >
                    </div>
                    <div class="col">
                        <label for="">Fecha Fin</label>
                      <input type="date" class="form-control" name="fecha_fin" id="fecha_fin">
                    </div>
                  </div>               
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-outline-success" onclick="BtnDowloadExcelPers('{{ $identidad }}')">Buscar</button>
        </div>
    </div>
</div>
