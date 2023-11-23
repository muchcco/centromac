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
                        <input type="text" class="form-control" name="" id="" value="{{ $user->name }}" disabled>
                    </div>
                </div>
                <div class="row mb-3">
                    <label  class="col-3 col-form-label">Nueva Contraseña</label>
                    <div class="mb-3 col-9" style="display: flex">
                        <input type="password" class="form-control"  id="password" name="password">
                        <button type="button" class="input-group-text" onclick="MostrarPassword()">
                            <i id="iconoPassword" class="las la-low-vision"></i>
                        </button>
                    </div>                    
                </div>
                <div class="row mb-3">
                    <label  class="col-3 col-form-label">Repetir Contraseña </label>
                    <div class="col-9">
                        <input type="password" class="form-control" name="confirmPassword" id="confirmPassword" >
                        <span class="text-danger" style="display: none" id="text-confirm">Las contraseñas no son iguales</span>
                    </div>
                </div>
                
                
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" onclick="btnStoreUpdatePassword('{{ $user->id }}')" id="btnEnviarForm">Actualizar</button>
        </div>
    </div>
</div>
<script>
    function MostrarPassword() {
        var passwordInput = document.getElementById('password');
        var iconoPassword = document.getElementById('iconoPassword');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            iconoPassword.className = 'las la-eye';
        } else {
            passwordInput.type = 'password';
            iconoPassword.className = 'las la-low-vision';
        }
    }
</script>