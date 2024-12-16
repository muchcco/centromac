<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Editar usuario</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <h5></h5>
            <form id="passwordForm" class="form">
                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" /> 
                <div class="alert alert-warning border-0" role="alert">
                    <strong>Importante!</strong> <br />para el cambio de contraseña tiene que ser mínimo 10 caracteres incluyendo un número y un caracter especial como !, @, #, $, etc.
                </div>
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Nueva Contraseña</label>
                    <div class="mb-3 col-9" style="display: flex">
                        <input type="password" class="form-control" id="password" name="password" onblur="validatePasswordInput(this)">
                        <button type="button" class="input-group-text" onclick="togglePasswordVisibility()">
                            <i id="iconoPassword" class="las la-low-vision"></i>
                        </button>
                    </div>                    
                </div>
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Repetir Contraseña</label>
                    <div class="col-9">
                        <input type="password" class="form-control" name="confirmPassword" id="confirmPassword" onblur="validatePasswordInput(this)">
                        <span class="text-danger" style="display: none" id="text-confirm">Las contraseñas no son iguales</span>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" onclick="validateAndSubmitPassword_n()" id="btnEnviarForm">Actualizar</button>
        </div>
    </div>
</div>

<script>
    // Alternar visibilidad de contraseñas
    function togglePasswordVisibility() {
        var inputs = [document.getElementById('password'), document.getElementById('confirmPassword')];
        var iconoPassword = document.getElementById('iconoPassword');

        inputs.forEach(input => {
            if (input.type === 'password') {
                input.type = 'text';
                iconoPassword.className = 'las la-eye';
            } else {
                input.type = 'password';
                iconoPassword.className = 'las la-low-vision';
            }
        });
    }

    // Validar entrada de contraseña
    function validatePasswordInput(input) {
        var passwordRegex = /^(?=.*[!@#$%^&*(),.?":{}|<>])(?=.*[0-9]).{10,}$/;

        if (!passwordRegex.test(input.value)) {
            input.classList.add('hasError');
        } else {
            input.classList.remove('hasError');
        }
    }

    // Validar y enviar formulario
    function validateAndSubmitPassword() {
        var password = document.getElementById('password').value;
        var confirmPassword = document.getElementById('confirmPassword').value;
        var confirmText = document.getElementById('text-confirm');

        // Expresión regular para validar contraseña
        var passwordRegex = /^(?=.*[!@#$%^&*(),.?":{}|<>])(?=.*[0-9]).{10,}$/;

        if (!passwordRegex.test(password)) {
            alert("La contraseña debe tener al menos 10 caracteres, incluir un carácter especial y un número.");
            return;
        }

        if (password !== confirmPassword) {
            confirmText.style.display = "block";
            return;
        } else {
            confirmText.style.display = "none";
        }

        // Si todo es válido, puedes enviar la contraseña
        alert("Contraseña válida y lista para enviar.");
        // Aquí puedes enviar el formulario usando AJAX o redirigir según tus necesidades.
    }
</script>
