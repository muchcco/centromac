<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Editar Itinerante</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form class="form-horizontal">
                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />
                <input type="hidden" name="id_itinerante" id="id_itinerante" value="{{ $itinerante->id }}" /> <!-- Agregado -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Centro MAC</label>
                    <div class="col-9">
                        <select class="form-control" name="IDCENTRO_MAC" id="IDCENTRO_MAC">
                            @foreach ($centros as $centro)
                                <option value="{{ $centro->IDCENTRO_MAC }}" {{ $centro->IDCENTRO_MAC == $itinerante->IDCENTRO_MAC ? 'selected' : '' }}>
                                    {{ $centro->NOMBRE_MAC }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Personal</label>
                    <div class="col-9">
                        <select class="form-control" name="NUM_DOC" id="NUM_DOC">
                            @foreach ($personales as $personal)
                                <option value="{{ $personal->NUM_DOC }}" {{ $personal->NUM_DOC == $itinerante->NUM_DOC ? 'selected' : '' }}>
                                    {{ $personal->APE_PAT }} {{ $personal->APE_MAT }} {{ $personal->NOMBRE }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Módulo</label>
                    <div class="col-9">
                        <select class="form-control" name="IDMODULO" id="IDMODULO">
                            @foreach ($modulos as $modulo)
                                <option value="{{ $modulo->IDMODULO }}" {{ $modulo->IDMODULO == $itinerante->IDMODULO ? 'selected' : '' }}>
                                    {{ $modulo->N_MODULO }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha de Inicio</label>
                    <div class="col-9">
                        <input type="date" class="form-control" name="fechainicio" id="fechainicio" value="{{ $itinerante->fechainicio }}">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha de Fin</label>
                    <div class="col-9">
                        <input type="date" class="form-control" name="fechafin" id="fechafin" value="{{ $itinerante->fechafin }}">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm" onclick="btnUpdateItinerante()">Guardar</button>
        </div>
    </div>
</div>
