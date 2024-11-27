<div class="modal-dialog modal-lg" role="document" style="max-width: 80% !important">
    <div class="modal-content" >
        <div class="modal-header">
            <h4 class="modal-title">Añadir nuevo Item </h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <h5>Ingresar Datos del Item</h5>
            <div class="row">
                <div class="form-group col-md-3">
                    <label for="">Categoria:</label>
                    <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}">
                    <select name="idcategoria" id="idcategoria" class="form-select">
                        @foreach ($categorias as $c)
                            <option value="{{ $c->IDCATEGORIA }}" {{ is_null($c->SUBCATEGORIA) ? 'disabled' : ''  }}>{{ $c->CODIGO_CATEGORIA }} - {{ $c->NOMBRE_CATEGORIA }}</option>
                        @endforeach                        
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="">Código Interno PCM:</label>
                    <input type="text" class="form-control" id="cod_interno_pcm" placeholder="">
                </div>
                <div class="form-group col-md-3">
                    <label for="">Código SBN:</label>
                    <input type="text" class="form-control" id="cod_sbn" placeholder="">
                </div>
                <div class="form-group col-md-3">
                    <label for="">Código PROMSACE:</label>
                    <input type="text" class="form-control" id="cod_pronsace" placeholder="">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-3">
                    <label for="">Descripción:</label>
                    <input type="text" class="form-control" id="descripcion" placeholder="Descripción del bien">
                </div>
                <div class="form-group col-md-3">
                    <label for="">Marca:</label>
                    <select name="idmarca" id="idmarca" class="form-select">
                        <option value="0" disabled selected>-- SELECCIONE UNA OPCION --</option>
                        @foreach ($marca as $c)
                            <option value="{{ $c->IDMARCA }}" >{{ $c->NOMBRE_MARCA }}</option>
                        @endforeach                        
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="">Modelo:</label>
                    <select name="idmodelo" id="idmodelo" class="form-select">                      
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="">Serie / Medida:</label>
                    <input type="text" class="form-control" id="serie" placeholder="">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-3">
                    <label for="">OC (Orden de Compra):</label>
                    <input type="text" class="form-control" id="oc" placeholder="">
                </div>
                <div class="form-group col-md-3">
                    <label for="">Fecha OC (Orden de Compra):</label>
                    <input type="date" class="form-control" id="fecha_oc" placeholder="">
                </div>
                <div class="form-group col-md-3">
                    <label for="">Proveedor:</label>
                    <input type="text" class="form-control" id="proveedor" placeholder="">
                </div>
                <div class="form-group col-md-3">
                    <label for="">Color:</label>
                    <input type="text" class="form-control" id="color" placeholder="">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-3">
                    <label for="">Ubicación:</label>
                    <input type="text" class="form-control" id="ubicacion" placeholder="">
                </div>
                <div class="form-group col-md-3">
                    <label for="">Cantidad:</label>
                    <input type="text" class="form-control" id="cantidad" placeholder="">
                </div>
                <div class="form-group col-md-3">
                    <label for="">Estado:</label>
                    <select name="estado" id="estado" class="form-select">  
                        <option value="OPERATIVO">OPERATIVO</option>
                        <option value="MALOGRADO">MALOGRADO</option>
                        <option value="REPARACION">REPARACION</option>
                    </select>
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" onclick="btnStoreItem()">Guardar</button>
        </div>
    </div>
</div>
<div class="modal fade" id="modal_show_modal" tabindex="-1" role="dialog" ></div>
<script>
$(document).ready(function() {
    
    tippy(".bandejTool", {
        allowHTML: true,
        followCursor: true,
    });

    $('#idmarca').on('change', function() {
            var idmarca = $(this).val();
            if (idmarca) {
                var url = "{{ route('almacen.modelo_marca', ['idmarca' => ':idmarca']) }}"; // Cambia 'tipo' a 'tipoid'
                url = url.replace(':idmarca', idmarca);
  
                $.ajax({
                    type: 'GET',
                    url: url, // Utiliza la URL generada con tipo
                    success: function(data) {
                        $('#idmodelo').html(data);
                    }
                });
            } else {
                $('#idmodelo').empty();
            }
        });

});


</script>