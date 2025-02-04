<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Editar Módulo</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="alerta">
            </div>
            <h5>Editar datos del módulo</h5>
            <form class="form-horizontal">
                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Número del Módulo</label>
                    <div class="col-9">
                        <input type="text" class="form-control" name="nombre_modulo" id="nombre_modulo" onkeyup="isMayus(this)" value="{{ $modulo->N_MODULO }}">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha de Inicio</label>
                    <div class="col-9">
                        <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio" value="{{ $modulo->FECHAINICIO->format('Y-m-d') }}">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha de Fin</label>
                    <div class="col-9">
                        <input type="date" class="form-control" name="fecha_fin" id="fecha_fin" value="{{ $modulo->FECHAFIN->format('Y-m-d') }}">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Entidad</label>
                    <div class="col-9">
                        <select class="form-control" name="entidad_id" id="entidad_id">
                            @foreach ($entidades as $entidad)
                                <option value="{{ $entidad->IDENTIDAD }}" {{ $modulo->IDENTIDAD == $entidad->IDENTIDAD ? 'selected' : '' }}>
                                    {{ $entidad->NOMBRE_ENTIDAD }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <input type="hidden" name="id_centromac" id="id_centromac" value="{{ $modulo->IDCENTRO_MAC }}">

            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm" onclick="btnUpdateModulo('{{ $modulo->IDMODULO }}')">Guardar</button>
        </div>
    </div>
</div>
