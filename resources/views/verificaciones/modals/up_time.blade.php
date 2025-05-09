<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Cambiar Horarios</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <h5>Importante: requiere permiso de administrador, {{ $hora_inicio->id }} - {{ $hora_fin->id }}</h5>
        <form id="formUpdateTime">
          @csrf
          <input type="hidden" name="id_inicio" value="{{ $hora_inicio->id }}">
          <input type="hidden" name="id_fin"    value="{{ $hora_fin->id }}">
          <input type="hidden" name="fecha_inicio"     value="{{ $hora_inicio->hora_registro }}">
          <input type="hidden" name="fecha_fin"     value="{{ $hora_fin->hora_registro }}">
          <div class="row mb-3">
            <label class="col-3 col-form-label">Hora Inicio</label>
            <div class="col-4">
              <input type="time" class="form-control" name="hora_inicio"
                value="{{ \Carbon\Carbon::parse($hora_inicio->hora_registro)->format('H:i:s') }}">
            </div>
            <label class="col-2 col-form-label text-center">Hora Fin</label>
            <div class="col-3">
              <input type="time" class="form-control" name="hora_fin"
                value="{{ \Carbon\Carbon::parse($hora_fin->hora_registro)->format('H:i:s') }}">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
        <button id="btnEnviarForm" class="btn btn-outline-success">Guardar</button>
      </div>
    </div>
  </div>
  