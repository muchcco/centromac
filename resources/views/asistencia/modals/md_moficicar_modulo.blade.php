<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Modificar Módulo</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form id="form-modificar-modulo" class="form">
                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />

                <h5>Modificar los detalles del módulo del asesor</h5>

                <div class="form-group">
                    <label for="num_doc">Número de Documento y Nombre</label>
                    <!-- Campo de solo lectura que muestra num_doc y nombre del asesor -->
                    <input type="text" class="form-control" id="num_doc_readonly"
                        value="{{ $num_doc }} - {{ $nombre_asesor }}" readonly>
                    <!-- Campo oculto que solo se usará para enviar el num_doc al servidor -->
                    <input type="hidden" name="num_doc" id="num_doc" value="{{ $num_doc }}">
                    <!-- Campo oculto que contiene el ID del centro MAC -->
                    <input type="hidden" name="idcentro_mac" id="idcentro_mac" value="{{ $idcentro_mac }}">
                </div>

                <!-- Módulo -->
                <div class="form-group">
                    <label class="">Módulo</label>
                    <div>
                        <select class="form-control" name="idmodulo" id="idmodulo">
                            @foreach ($modulos as $modulo)
                                <option value="{{ $modulo->IDMODULO }}">
                                    {{ $modulo->N_MODULO }} - {{ $modulo->NOMBRE_ENTIDAD }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group" style="display: none;">
                    <label for="fechainicio">Fecha de Inicio</label>
                    <input type="hidden" class="form-control" id="fechainicio" name="fechainicio"
                        value="{{ $fecha_asistencia }}">
                </div>

                <div class="form-group" style="display: none;">
                    <label for="fechafin">Fecha de Fin</label>
                    <input type="hidden" class="form-control" id="fechafin" name="fechafin"
                        value="{{ $fecha_asistencia }}">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm"
                onclick="storeModuloChanges()">Guardar cambios</button>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-error" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Error</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- El mensaje de error se insertará aquí -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<script>
    tippy(".bandejTool", {
        allowHTML: true,
        followCursor: true,
    });

    // Inicializa Select2 en el campo select de módulo
    $('#idmodulo').select2({
        dropdownParent: $('#modal_show_modal'), // Establece el contenedor del dropdown dentro del modal
        placeholder: "Seleccione un módulo",
        width: '100%', // Ajusta el ancho para que sea responsivo
        allowClear: true
    });

    // Función para manejar el envío de los datos del formulario
   
</script>
