<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Añadir un usuario </h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            
            <h5>Verificar al usuario que se desee agregar al sistema</h5>
            <form action="" class="form">
                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />
                <div class="form-group">
                    <select name="id_usuario" id="id_usuario" class="form-control select2 col-12" style="width: 100%">
                        <option value="">Seleccione una opcion</option>
                        @forelse ($query as $q)
                            <option value="{{ $q->IDPERSONAL }}">{{ $q->NOMBRE }} {{ $q->APE_PAT }} {{ $q->APE_MAT }} - {{ $q->NUM_DOC }}</option>
                        @empty
                            <option value="">No hay usuarios disponibles...</option>
                        @endforelse
                    </select>
                </div>
                <h5>Seleccionar el Perfil del usuario</h5>
                <div class="form-group">
                    <div class="mt-2">
                        @foreach ($roles as $id => $name)
                            <div class="form-check form-check-inline">
                                <input type="checkbox" name="roles[]" id="roles" class="form-check-input" value="{{ $name }}">
                                <label class="form-check-label" >{{ $name }}</label>
                            </div>
                        @endforeach
                        
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" onclick="btnStoreUsuario()" id="btnEnviarForm">Crear</button>
        </div>
    </div>
</div>
<script>

$(document).ready(function() {
    $('.select2').select2({
        dropdownParent: $('#modal_show_modal')
    });
});

</script>