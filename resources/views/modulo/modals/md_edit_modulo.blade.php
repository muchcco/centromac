<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Editar Módulo</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="alerta"></div>
            <h5>Editar datos del módulo</h5>
            <form class="form-horizontal">
                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />

                <!-- ¿Es Administrativo? -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">¿Es Administrativo?</label>
                    <div class="col-9">
                        <select class="form-control" name="es_administrativo" id="es_administrativo"
                            onchange="toggleModuloNumber()" {{ $solo_fecha_fin ? 'disabled' : '' }}>
                            <option value="SI" {{ $modulo->ES_ADMINISTRATIVO == 'SI' ? 'selected' : '' }}>Sí
                            </option>
                            <option value="NO" {{ $modulo->ES_ADMINISTRATIVO == 'NO' ? 'selected' : '' }}>No
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Número del Módulo -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Número del Módulo</label>
                    <div class="col-9">
                        <input type="text" class="form-control" name="nombre_modulo" id="nombre_modulo"
                            value="{{ $modulo->N_MODULO }}" onkeyup="isMayus(this)"
                            {{ $solo_fecha_fin ? 'readonly' : '' }}>
                    </div>
                </div>

                <!-- Fecha de Inicio -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha de Inicio</label>
                    <div class="col-9">
                        <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio"
                            value="{{ $modulo->FECHAINICIO->format('Y-m-d') }}" {{ $solo_fecha_fin ? 'readonly' : '' }}>
                    </div>
                </div>

                <!-- Fecha de Fin -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha de Fin</label>
                    <div class="col-9">
                        <input type="date" class="form-control" name="fecha_fin" id="fecha_fin"
                            value="{{ $modulo->FECHAFIN->format('Y-m-d') }}">
                    </div>
                </div>

                <!-- Entidad -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Entidad</label>
                    <div class="col-9">
                        <select class="form-control" name="entidad_id" id="entidad_id"
                            {{ $solo_fecha_fin ? 'disabled' : '' }}>
                            @foreach ($entidades as $entidad)
                                <option value="{{ $entidad->IDENTIDAD }}"
                                    {{ $modulo->IDENTIDAD == $entidad->IDENTIDAD ? 'selected' : '' }}>
                                    {{ $entidad->NOMBRE_ENTIDAD }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- ID Centro MAC -->
                <input type="hidden" name="id_centromac" id="id_centromac" value="{{ $modulo->IDCENTRO_MAC }}">
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm"
                onclick="btnUpdateModulo('{{ $modulo->IDMODULO }}')">Guardar</button>
        </div>
    </div>
</div>
<script>
    function toggleModuloNumber() {
        var isAdministrativo = document.getElementById("es_administrativo").value;
        var moduloInput = document.getElementById("nombre_modulo");

        // Si el select está en "Sí" (Administrativo), deshabilitar el campo y poner "Administrativo" como valor
        if (isAdministrativo === "SI") {
            moduloInput.value = "Administrativo"; // Asignar "Administrativo" al campo
            moduloInput.disabled = true; // Deshabilitar el campo
        } else {
            // Si el select está en "No", habilitar el campo y limpiar el valor
            moduloInput.disabled = false; // Habilitar el campo
        }
    }

    // Asegurarse de que el estado del campo se inicialice correctamente al cargar la página
    $(document).ready(function() {
        toggleModuloNumber();
    });
</script>
