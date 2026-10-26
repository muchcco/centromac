{{-- Modal ANS: conserva exactamente se_calcula de BD; orden solo visual --}}
<div class="modal-dialog modal-dialog-centered" style="max-width:95%" role="document">
  <div class="modal-content" id="ans-modal-servicios">
    <div class="modal-header py-2 px-3" style="background:#172d48;color:#fff">
      <h6 class="modal-title fw-semibold mb-0">Servicios de {{ $servicios->first()->entidad ?? 'Entidad' }}</h6>
      <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal" aria-label="Cerrar"></button>
    </div>
    <div class="modal-body p-2">
      <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-2 px-1">
        <div class="d-flex gap-2 align-items-center flex-wrap">
          <small class="fw-semibold" id="contador_calculo">Cargando estado...</small>
          <small class="text-muted" id="ans_pendientes"></small>
        </div>
        <label class="d-flex align-items-center gap-2 mb-0 small text-muted" for="ans_orden_automatico">
          <input type="checkbox" class="form-check-input mt-0" id="ans_orden_automatico" checked>
          Ordenar por cálculo
        </label>
      </div>
      <div class="table-responsive" style="max-height:65vh;overflow-y:auto">
        <table class="table table-sm table-bordered align-middle mb-0 small" id="ans_tabla_servicios">
          <thead class="text-center sticky-top" style="background:#e9eef5;z-index:1">
            <tr>
              <th style="width:4%">#</th>
              <th style="width:38%">Servicio</th>
              <th style="width:12%">Inicio</th>
              <th style="width:12%">Fin</th>
              <th style="width:8%">Espera</th>
              <th style="width:8%">Atención</th>
              <th style="width:8%">Estado</th>
              <th style="width:8%">Calcular</th>
            </tr>
          </thead>
          <tbody id="ans_filas_servicios">
            @foreach ($servicios as $i => $s)
              <tr data-orden-original="{{ $i }}" data-servicio-id="{{ $s->id_servicio }}">
                <td class="text-center ans-numero">{{ $i + 1 }}</td>
                <td class="text-uppercase lh-sm">{{ $s->nome }}</td>
                <td><input type="date" class="form-control form-control-sm fecha_inicio" data-id="{{ $s->id_servicio }}" value="{{ $s->fecha_inicio ? \Carbon\Carbon::parse($s->fecha_inicio)->format('Y-m-d') : '' }}"></td>
                <td><input type="date" class="form-control form-control-sm fecha_fin" data-id="{{ $s->id_servicio }}" value="{{ $s->fecha_fin ? \Carbon\Carbon::parse($s->fecha_fin)->format('Y-m-d') : '' }}"></td>
                <td><input type="number" class="form-control form-control-sm text-center espera" data-id="{{ $s->id_servicio }}" value="{{ $s->limite_espera }}"></td>
                <td><input type="number" class="form-control form-control-sm text-center atencion" data-id="{{ $s->id_servicio }}" value="{{ $s->limite_atencion }}"></td>
                <td class="text-center">
                  @if ((int) $s->status === 1)
                    <span class="badge bg-success">Activo</span>
                  @else
                    <span class="badge bg-danger">Inactivo</span>
                  @endif
                </td>
                <td class="text-center">
                  <div class="form-check form-switch d-flex justify-content-center m-0">
                    {{-- IMPORTANTE: no se activa por defecto; solo si la BD tiene se_calcula = 1 --}}
                    <input class="form-check-input calcula" type="checkbox" data-id="{{ $s->id_servicio }}" data-bd-calcula="{{ (int) ($s->se_calcula ?? 0) }}" {{ (isset($s->se_calcula) && (int) $s->se_calcula === 1) ? 'checked="checked"' : '' }} title="Incluir en cálculo ANS">
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <small class="d-block text-muted mt-2 px-1">El orden es solo visual. Un servicio inactivo también puede tener cálculo habilitado.</small>
    </div>
    <div class="modal-footer py-2 px-3">
      <button type="button" class="btn btn-outline-primary btn-sm" onclick="activarCalculoTodos()">Activar todos</button>
      <button type="button" class="btn btn-outline-danger btn-sm" onclick="quitarCalculoTodos()">Quitar calcular a todos</button>
      <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
      <button type="button" class="btn btn-success btn-sm" onclick="guardarTiemposEntidad()"><i class="fa fa-save"></i> Guardar cambios</button>
    </div>
  </div>
</div>
