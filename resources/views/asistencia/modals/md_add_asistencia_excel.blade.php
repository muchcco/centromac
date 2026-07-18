<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Importar asistencia CSV</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form action="" class="form">
                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />

                <h5>
                    Archivo CSV
                    <span class="bandejTool" data-tippy-content="Se tomará la columna C como DNI y la columna D como fecha/hora.">
                        <i class="fa fa-file-excel-o" aria-hidden="true"></i>
                    </span>
                </h5>
                <div class="alert alert-warning mt-2">
                    Suba el archivo CSV exportado desde Excel. El proceso leerá solo las columnas C y D.
                </div>
                <div class="form-group">
                    <input type="file" class="form-control" name="txt_file" id="txt_file" accept=".csv,.txt">
                </div>
            </form>
            <div class="progress mt-3 d-none" id="uploadProgressWrapper">
                <div class="progress-bar" id="uploadProgressBar" role="progressbar" style="width: 0%;">0%</div>
            </div>
            <div class="mt-2 d-none" id="uploadQueueInfo"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" id="btnCancelarUpload">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm" onclick="btnStoreExcel()">Importar</button>
        </div>
    </div>
</div>

<script>
    tippy(".bandejTool", {
        allowHTML: true,
        followCursor: true,
    });
</script>
