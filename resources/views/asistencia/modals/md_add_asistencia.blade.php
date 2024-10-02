<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Añadir archivo TXT </h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            
            <form action="" class="form">                
                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />
                
                <h5>Documentos Adjuntos - descargar archivo modelo TXT <span  class="bandejTool" data-tippy-content="Primero descargue el TXT del biometrico, ingrese y elimine la ultima fila donde sale este caracter " target="_blank"><i class="fa fa-file-excel-o" aria-hidden="true"></i></span></h5>
                <div class="form-group">
                    <input type="file" class="form-control" name="txt_file" id="txt_file" accept=".txt">
                </div>              
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm" onclick="btnStoreTxt()">Importar</button>
        </div>
    </div>
</div>

<script>
    tippy(".bandejTool", {
        allowHTML: true,
        followCursor: true,
    });
</script>