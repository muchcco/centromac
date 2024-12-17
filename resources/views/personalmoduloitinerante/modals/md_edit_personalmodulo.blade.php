<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Editar Personal Módulo</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="alerta"></div>
            <h5>Editar datos del Personal Módulo Itinerante</h5>
            <form id="form_personal_modulo" class="form-horizontal">
                @csrf
                <input type="hidden" name="id" id="id" value="{{ $personalModulo->id }}">
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Número de Documento</label>
                    <div class="col-9">
                        <select class="form-control select2" name="num_doc" id="num_doc">
                            <option value="" disabled>Seleccione un personal</option>
                            @foreach ($personal as $p)
                                <option value="{{ $p->num_doc }}" {{ $p->num_doc == $personalModulo->num_doc ? 'selected' : '' }}>
                                    {{ $p->num_doc }} - {{ $p->nombre_completo }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Módulo</label>
                    <div class="col-9">
                        <select class="form-control select2" name="idmodulo" id="idmodulo">
                            <option value="" disabled>Seleccione un módulo</option>
                            @foreach ($modulos as $modulo)
                                <option value="{{ $modulo->IDMODULO }}" {{ $modulo->IDMODULO == $personalModulo->idmodulo ? 'selected' : '' }}>
                                    {{ $modulo->N_MODULO }} - {{ $modulo->NOMBRE_ENTIDAD }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha de Inicio</label>
                    <div class="col-9">
                        <input type="date" class="form-control" name="fechainicio" id="fechainicio" value="{{ $personalModulo->fechainicio }}">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha de Fin</label>
                    <div class="col-9">
                        <input type="date" class="form-control" name="fechafin" id="fechafin" value="{{ $personalModulo->fechafin }}">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm" onclick="btnUpdatePersonalModulo('{{ $personalModulo->id }}')">Guardar</button>
        </div>
    </div>
</div>

<script>
    // Inicializar select2
    $(document).ready(function() {
        $('#num_doc, #idmodulo').select2({
            dropdownParent: $('#modal_show_modal'),
            width: '100%',
            allowClear: true
        });
    });
</script>
