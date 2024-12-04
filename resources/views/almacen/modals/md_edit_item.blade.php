<div class="modal-dialog modal-lg" role="document" style="max-width: 80% !important">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Editar Item</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <h5>Actualizar Datos del Item</h5>
            <div class="row">
                <div class="form-group col-md-3">
                    <label for="">Categoria:</label>
                    <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}">
                    <select name="idcategoria" id="idcategoria" class="form-select">
                        @foreach ($categorias as $c)
                            <option 
                                value="{{ $c->IDCATEGORIA }}" 
                                {{ is_null($c->SUBCATEGORIA) ? 'disabled' : '' }} 
                                {{ isset($almacen) && $c->IDCATEGORIA == ($almacen->IDCATEGORIA ?? '') ? 'selected' : '' }}>
                                {{ $c->CODIGO_CATEGORIA }} - {{ $c->NOMBRE_CATEGORIA }}
                            </option>
                        @endforeach                        
                    </select>                    
                </div>
                <div class="form-group col-md-3">
                    <label for="">Código Interno PCM:</label>
                    <input type="text" class="form-control" id="cod_interno_pcm" placeholder="" value="{{ $almacen->COD_INTERNO_PCM ?? '' }}">
                </div>
                <div class="form-group col-md-3">
                    <label for="">Código SBN:</label>
                    <input type="text" class="form-control" id="cod_sbn" placeholder="" value="{{ $almacen->COD_SBN ?? '' }}">
                </div>
                <div class="form-group col-md-3">
                    <label for="">Código PROMSACE:</label>
                    <input type="text" class="form-control" id="cod_pronsace" placeholder="" value="{{ $almacen->COD_PRONSACE ?? '' }}">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-3">
                    <label for="">Descripción:</label>
                    <input type="text" class="form-control" id="descripcion" placeholder="Descripción del bien" value="{{ $almacen ? $almacen->DESCRIPCION :  '' }}">
                </div>
                <div class="form-group col-md-3">
                    <label for="">Marca:</label>
                    <select name="idmarca" id="idmarca" class="form-select">
                        <option value="0" disabled selected>-- SELECCIONE UNA OPCIÓN --</option>
                        @foreach ($marca as $c)
                            <option 
                                value="{{ $c->IDMARCA }}" 
                                {{ isset($almacen) && $c->IDMARCA == ($almacen->IDMARCA ?? '') ? 'selected' : '' }}>
                                {{ $c->NOMBRE_MARCA }}
                            </option>
                        @endforeach                        
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="">Modelo:</label>
                    <select name="idmodelo" id="idmodelo" class="form-select">
                        @if (isset($almacen))
                            <option value="{{ $almacen->IDMODELO ?? '' }}" selected>{{ $almacen->NOMBRE_MODELO ?? '-- SELECCIONE UNA OPCIÓN --' }}</option>
                        @else
                            <option value="0" disabled selected>-- SELECCIONE UNA OPCIÓN --</option>
                        @endif
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="">Serie / Medida:</label>
                    <input type="text" class="form-control" id="serie" placeholder="" value="{{ $almacen->SERIE_MEDIDA ?? '' }}">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-3">
                    <label for="">OC (Orden de Compra):</label>
                    <input type="text" class="form-control" id="oc" placeholder="" value="{{ $almacen->OC ?? '' }}">
                </div>
                <div class="form-group col-md-3">
                    <label for="">Fecha OC (Orden de Compra):</label>
                    <input type="date" class="form-control" id="fecha_oc" placeholder="" value="{{ $almacen->FECHA_OC ?? '' }}">
                </div>
                <div class="form-group col-md-3">
                    <label for="">Proveedor:</label>
                    <input type="text" class="form-control" id="proveedor" placeholder="" value="{{ $almacen->PROVEEDOR ?? '' }}">
                </div>
                <div class="form-group col-md-3">
                    <label for="">Color:</label>
                    <input type="text" class="form-control" id="color" placeholder="" value="{{ $almacen->COLOR ?? '' }}">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-3">
                    <label for="">Ubicación:</label>
                    <input type="text" class="form-control" id="ubicacion" placeholder="" value="{{ $almacen->UBICACION_EQUIPOS ?? '' }}">
                </div>
                <div class="form-group col-md-3">
                    <label for="">Cantidad:</label>
                    <input type="text" class="form-control" id="cantidad" placeholder="" value="{{ $almacen->CANTIDAD ?? '' }}">
                </div>
                <div class="form-group col-md-3">
                    <label for="">Estado:</label>
                    <select name="estado" id="estado" class="form-select">  
                        <option value="OPERATIVO" {{ ($almacen->ESTADO ?? '') == 'OPERATIVO' ? 'selected' : '' }}>OPERATIVO</option>
                        <option value="MALOGRADO" {{ ($almacen->ESTADO ?? '') == 'MALOGRADO' ? 'selected' : '' }}>MALOGRADO</option>
                        <option value="REPARACION" {{ ($almacen->ESTADO ?? '') == 'REPARACION' ? 'selected' : '' }}>REPARACION</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" onclick="btnUpdateItem('{{ $almacen->IDALMACEN ?? '' }}')">Guardar</button>
        </div>
    </div>
</div>
<div class="modal fade" id="modal_show_modal" tabindex="-1" role="dialog"></div>
<script>
$(document).ready(function() {
    $('#idmarca').on('change', function() {
        var idmarca = $(this).val();
        if (idmarca) {
            var url = "{{ route('almacen.modelo_marca', ':idmarca') }}".replace(':idmarca', idmarca);
            $.ajax({
                type: 'GET',
                url: url,
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
