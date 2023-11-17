<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Editar el servicio</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="alerta">
            </div>            
            <h5>Editar dato</h5>
            <form class="form-horizontal">
                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />
                
                <div class="row mb-3">                    
                    <div class="col-12">
                        <label  class="col-3 col-form-label">Añadir observación</label>
                        <textarea name="observacion" id="observacion" class="form-control" cols="30" rows="10">
                            {{ $servicios->OBSERVACION }}
                        </textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm" onclick="btnEditServicio('{{ $servicios->IDSERVICIOS}}')">Guardar</button>
        </div>
    </div>
</div>

<script>
 var textarea = document.getElementById("observacion");
 var text = textarea.value;
 var trimmedText = text.trim();
 textarea.value = trimmedText;
</script>