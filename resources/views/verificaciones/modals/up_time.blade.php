<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header" style="background:#132842;color:white;">
            <h4 class="modal-title">Cambiar Horarios</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            @if ($inicio && $fin)
                <div class="alert alert-warning">
                    ⚠️ Esta acción requiere permisos de administrador
                </div>
                <form id="formUpdateTime">
                    @csrf
                    <input type="hidden" name="id_inicio" value="{{ $inicio->id ?? '' }}">
                    <input type="hidden" name="id_fin" value="{{ $fin->id ?? '' }}">
                    <input type="hidden" name="fecha_inicio" value="{{ $inicio->fecha_formateada ?? '' }}">
                    <input type="hidden" name="fecha_fin" value="{{ $fin->fecha_formateada ?? '' }}">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="fw-bold mb-2">Hora Inicio</label>
                            <input type="time" id="hora_inicio" name="hora_inicio" class="form-control"
                                value="{{ $inicio->hora_formateada ?? '' }}" required>
                        </div>
                                               <div class="col-md-6">
                            <label class="fw-bold mb-2">Hora Fin</label>
                            <input type="time" id="hora_fin" name="hora_fin" class="form-control"
                                value="{{ $fin->hora_formateada ?? '' }}" required>
                        </div>
                    </div>
                    <div id="alertaHoras" class="alert alert-danger mt-3 d-none">
                        La hora inicio no puede ser mayor o igual a la hora fin
                    </div>
                </form>
            @else
                <div class="alert alert-danger">
                    No existe apertura o cierre
                </div>
            @endif
        </div>

        <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            @if ($inicio && $fin)
                <button id="btnGuardar" class="btn btn-success">Guardar</button>
            @endif
        </div>

    </div>
</div>
