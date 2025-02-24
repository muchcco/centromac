<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Añadir archivo TXT </h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            
            <form action="" class="form">                
                <div class="alert alert-danger border-0" role="alert">
                    <strong>Importante!</strong> Primero el archivo access debe colocar los permisos al archivo antes de subirlo.
                </div>
                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />

                <div class="form-group">
                    @php
                        $fecha_hoy = Carbon\Carbon::now()->format('Y-m-d');
                        $fecha6dias = Carbon\Carbon::now()->subDays(6)->format('d-m-Y');
                        $fecha6diasconvert = Carbon\Carbon::now()->subDays(6)->format('Y-m-d');
                    @endphp
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label" for="fecha_inicio">Fecha de inicio</label>
                            <input type="date" class="form-control" id="fecha_inicio" required="" value="{{$fecha6diasconvert}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label" for="fecha_fin">Fecha de fin</label>
                            <input type="date" class="form-control" id="fecha_fin" required="" value="{{$fecha_hoy}}">
                        </div>
                    </div>
                </div>
                
                <h5>Documentos Adjuntos - Access <span  class="bandejTool" data-tippy-content="Primero indique los permisos necesarios en el archivo acceess antes de subir la información" target="_blank"><i class="fa fa-database" aria-hidden="true"></i></span></h5>
                <div class="form-group">
                    <input type="file" class="form-control" name="txt_file" id="txt_file" accept=".mdb">
                </div>              
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm" onclick="btnStoreAccess()">Importar</button>
        </div>
    </div>
</div>

<script>
    tippy(".bandejTool", {
        allowHTML: true,
        followCursor: true,
    });
</script>