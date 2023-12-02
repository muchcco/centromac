<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Importar Data </h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            
            <form action="" class="form">                
                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />
                <h5>Adjuntar archivo  <strong>(descargar archivo modelo xls => <a href="{{ asset('archivo_modelo/asistencia.xlsx') }}" class="bandejTool" data-tippy-content="Modelo de carga... copiar informacion en las columnas indicadas" target="_blank"><i class="fa fa-file-excel-o text-success" aria-hidden="true"></i></a>)</strong></h5>
                <div class="form-group">
                    <input type="file" class="form-control" name="excel_file" id="excel_file">
                </div>                
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" onclick="btnStoreExcel()">Importar</button>
        </div>
    </div>
</div>

<script>
    tippy(".bandejTool", {
        allowHTML: true,
        followCursor: true,
    });
</script>