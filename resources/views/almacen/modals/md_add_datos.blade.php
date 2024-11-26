<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Importar Data </h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="alert alert-danger border-0" role="alert">
                <strong>Importante!</strong> Para que puedan importar la data deben revisar los <strong>ID</strong> de cada bien que este categorizado y que el modelo, para ello deben seleccionar en la parsuperior categoria o modelo para ver la cascada de ID para reemplazarlo en el formato y de esa manera se puedan relacionar.
            </div>

            <div class="row">
                <div class="form-group">
                    <button type="button" class="btn btn-success" onclick="btnCategoria()">Categorias</button>
                    <button type="button" class="btn btn-info">Marca y Modelo</button>
                </div>
            </div>
            
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
<div class="modal fade" id="modal_show_modal_sub" tabindex="-1" role="dialog" ></div>
<script>
tippy(".bandejTool", {
    allowHTML: true,
    followCursor: true,
});


function btnCategoria ()  {

    $.ajax({
        type:'post',
        url: "{{ route('almacen.modals.md_categorias') }}",
        dataType: "json",
        data:{"_token": "{{ csrf_token() }}"},
        success:function(data){
            $("#modal_show_modal_sub").html(data.html);
            $("#modal_show_modal_sub").modal('show');
        }
    });
}

</script>