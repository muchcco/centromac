<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Editar usuario </h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            
            <h5></h5>
            <form action="" class="form">
                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />                
                <div class="row mb-3">
                    <label  class="col-3 col-form-label">Nombre completo</label>
                    <div class="col-9">
                        <input type="text" class="form-control" name="name" id="name" value="{{ $user->name }}">
                    </div>
                </div>
                <div class="row mb-3">
                    <label  class="col-3 col-form-label">Nombre de usuario</label>
                    <div class="col-9">
                        <input type="text" class="form-control" name="" id="" value="{{ $user->email }}" disabled>
                    </div>
                </div>
                <div class="row mb-3">
                    <label  class="col-3 col-form-label">Estado</label>
                    <div class="col-9">
                        <select name="flag" id="flag" class="form-control">
                            <option value="1" {{ $user->flag == '1' ? 'selected' : '' }} >Activo</option>
                            <option value="0" {{ $user->flag == '0' ? 'selected' : '' }} >Inactivo</option>
                        </select>
                    </div>
                </div>
                <h5>Actualizar Perfil del usuario</h5>
                <div class="form-group">
                    <div class="mt-2">
                        @foreach ($roles as $id => $name)
                            <div class="form-check form-check-inline">
                                <input type="checkbox" name="roles[]" id="roles" class="form-check-input" {{ $user->roles->contains($id) ? 'checked': ''}} value="{{ $name }}">
                                <label class="form-check-label" >{{ $name }}</label>
                            </div>
                        @endforeach                        
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" onclick="btnStoreUpdate('{{ $user->id }}')" id="btnEnviarForm">Actualizar</button>
        </div>
    </div>
</div>
