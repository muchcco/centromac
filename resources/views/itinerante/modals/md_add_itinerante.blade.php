<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Añadir nuevo itinerante</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form class="form-horizontal">
                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />

                <!-- Centro MAC -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Centro MAC</label>
                    <div class="col-9">
                        <select class="form-control" name="IDCENTRO_MAC" id="IDCENTRO_MAC">
                            @foreach ($centros as $centro)
                                <option value="{{ $centro->IDCENTRO_MAC }}">{{ $centro->NOMBRE_MAC }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Personal -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Personal</label>
                    <div class="col-9">
                        <select class="form-control" name="NUM_DOC" id="NUM_DOC">
                            @foreach ($personales as $personal)
                                <option value="{{ $personal->NUM_DOC }}">
                                    {{ $personal->APE_PAT }} {{ $personal->APE_MAT }} {{ $personal->NOMBRE }} 
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Módulo -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Módulo</label>
                    <div class="col-9">
                        <select class="form-control" name="IDMODULO" id="IDMODULO">
                            @foreach ($modulos as $modulo)
                                <option value="{{ $modulo->IDMODULO }}">{{ $modulo->N_MODULO }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Fecha de Inicio -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha de Inicio</label>
                    <div class="col-9">
                        <input type="date" class="form-control" name="fechainicio" id="fechainicio">
                    </div>
                </div>

                <!-- Fecha de Fin -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha de Fin</label>
                    <div class="col-9">
                        <input type="date" class="form-control" name="fechafin" id="fechafin">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm"
                onclick="btnStoreItinerante()">Guardar</button>
        </div>
    </div>
</div>
