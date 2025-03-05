<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Añadir nuevo módulo</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="alerta">
            </div>
            <h5>Agregar datos del módulo</h5>
            <form class="form-horizontal">
                <div class="row mb-3">
                    <label class="col-3 col-form-label">¿Es Administrativo?</label>
                    <div class="col-9">
                        <!-- Establecemos el value para que sea "SI" cuando esté marcado y "NO" cuando no esté marcado -->
                        <input type="checkbox" class="form-check-input" id="es_administrativo" value="SI"
                            onchange="toggleModuloNumber()">
                        <label for="es_administrativo">Sí</label>
                    </div>
                </div>

                <!-- Campo oculto para enviar "NO" por defecto cuando el checkbox no está marcado -->
                <input type="hidden" name="es_administrativo" id="es_administrativo_value" value="NO">


                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Número del Módulo</label>
                    <div class="col-9">
                        <input type="text" class="form-control" name="nombre_modulo" id="nombre_modulo"
                            onkeyup="isMayus(this)">
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha de Inicio</label>
                    <div class="col-9">
                        <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha de Fin</label>
                    <div class="col-9">
                        <input type="date" class="form-control" name="fecha_fin" id="fecha_fin">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Entidad</label>
                    <div class="col-9">
                        <select class="form-control" name="entidad_id" id="entidad_id">
                            @foreach ($entidades as $entidad)
                                <option value="{{ $entidad->IDENTIDAD }}">{{ $entidad->NOMBRE_ENTIDAD }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <input type="hidden" name="id_centromac" id="id_centromac" value="{{ auth()->user()->idcentro_mac }}">
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm"
                onclick="btnStoreModulo()">Guardar</button>
        </div>
    </div>
</div>

<script>
    function toggleModuloNumber() {
        var isAdministrativo = document.getElementById("es_administrativo").checked;
        var moduloInput = document.getElementById("nombre_modulo");
        var administrativoValueInput = document.getElementById("es_administrativo_value");

        if (isAdministrativo) {
            // Si el checkbox está marcado (es administrativo), actualizar el valor del campo oculto a "SI"
            moduloInput.value = "Administrativo"; // Asignar "Administrativo" al campo
            moduloInput.disabled = true; // Deshabilitar el campo
            administrativoValueInput.value = "SI"; // Enviar "SI"
        } else {
            // Si el checkbox no está marcado (no es administrativo), actualizar el valor del campo oculto a "NO"
            moduloInput.disabled = false; // Habilitar el campo
            moduloInput.value = ""; // Limpiar el valor del campo
            administrativoValueInput.value = "NO"; // Enviar "NO"
        }
    }

    $(document).ready(function() {
        // Llamar a la función cuando el formulario se cargue para inicializar el estado correcto
        toggleModuloNumber();
    });
</script>
