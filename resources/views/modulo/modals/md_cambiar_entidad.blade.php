<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <!-- CABECERA -->
        <div class="modal-header">
            <h4 class="modal-title"><i class="las la-random me-1"></i> Cambio de Entidad</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <!-- CUERPO -->
        <div class="modal-body">
            <div id="alerta"></div>
            <h5>Actualizar Entidad del Módulo</h5>
            <form class="form-horizontal" id="form_cambio_entidad">
                @csrf
                <input type="hidden" name="id_modulo" value="{{ $modulo->IDMODULO }}">

                <!-- Número del Módulo -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Número del Módulo</label>
                    <div class="col-9">
                        <input type="text" class="form-control" value="{{ $modulo->N_MODULO }}" readonly>
                    </div>
                </div>

                <!-- Entidad Actual -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Entidad Actual</label>
                    <div class="col-9">
                        <input type="text" class="form-control"
                            value="{{ $modulo->entidad->NOMBRE_ENTIDAD ?? 'Sin entidad asignada' }}" readonly>
                    </div>
                </div>

                <!-- Fecha Inicio Actual -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha Inicio Actual</label>
                    <div class="col-9">
                        <input type="date" class="form-control" value="{{ $modulo->FECHAINICIO->format('Y-m-d') }}"
                            readonly>
                    </div>
                </div>

                <!-- Nueva Fecha de Fin -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Nueva Fecha de Fin</label>
                    <div class="col-9">
                        <input type="date" class="form-control" name="fecha_fin" id="fecha_fin"
                            value="{{ $modulo->FECHAFIN && $modulo->FECHAFIN->format('Y-m-d') != '2050-12-31'
                                ? $modulo->FECHAFIN->format('Y-m-d')
                                : now()->format('Y-m-d') }}">
                    </div>
                </div>

                <!-- Nueva Entidad -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Nueva Entidad</label>
                    <div class="col-9">
                        <select name="nueva_entidad_id" id="nueva_entidad_id" class="form-control select2">
                            <option value="">-- Seleccione una entidad --</option>
                            @foreach ($entidades as $ent)
                                <option value="{{ $ent->IDENTIDAD }}">{{ $ent->NOMBRE_ENTIDAD }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <!-- PIE -->
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnConfirmarCambio"
                onclick="btnGuardarCambioEntidad('{{ $modulo->IDMODULO }}')">Confirmar Cambio</button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%',
            dropdownParent: $('#modal_show_modal')
        });
    });
</script>
