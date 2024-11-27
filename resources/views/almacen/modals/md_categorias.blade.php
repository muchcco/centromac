<div class="modal-dialog modal-lg" role="document" >
    <div class="modal-content" class="width: 100% !important">
        <div class="modal-header">
            <h4 class="modal-title">Categorias </h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="alert alert-info border-0" role="alert">
                <strong>Importante!</strong> Reemplar los valores ID donde correspondan en su archivo excel antes de importarlo.
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <table class="table table-hover table-bordered table-striped" id="table_almacen">
                        <thead class="tenca">
                            <tr>
                                <th>ID</th>
                                <th>CODIGO</th>
                                <th>NOMBRE COMPLETO</th>                                
                                <th>ACCION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categorias as $c)
                                <tr class="{{ is_null($c->SUBCATEGORIA) ? 'padre_dat' : '' }}"> 
                                    <td class="{{ is_null($c->SUBCATEGORIA) ? 'fw-bold' : '' }}">{{ $c->IDCATEGORIA }}</td>
                                    <td class="{{ is_null($c->SUBCATEGORIA) ? 'fw-bold' : '' }}">{{ $c->CODIGO_CATEGORIA }}</td>
                                    <td class="{{ is_null($c->SUBCATEGORIA) ? 'fw-bold' : '' }}">{{ $c->NOMBRE_CATEGORIA }}</td>
                                    <td class="{{ is_null($c->SUBCATEGORIA) ? 'fw-bold' : '' }}">
                                        <button class="nobtn bandejTool" data-tippy-content="No disponible"><i class="las la-trash-alt text-secondary font-16 text-secondary"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No hay categorías disponibles.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                </div>
            </div>

        </div>
    </div>
</div>
<div class="modal fade" id="modal_show_modal" tabindex="-1" role="dialog" ></div>
<script>
tippy(".bandejTool", {
    allowHTML: true,
    followCursor: true,
});

</script>