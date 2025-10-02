<div class="modal-dialog modal-md">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><i class="fa fa-undo"></i> Revertir Día de Asistencia</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
            @if (!empty($macs) && count($macs) > 0)
                <div class="form-group mb-3">
                    <label for="rev-idmac">Centro MAC</label>
                    <select id="rev-idmac" class="form-control">
                        <option value="">-- Seleccione --</option>
                        @foreach ($macs as $mac)
                            <option value="{{ $mac->id }}">{{ $mac->nom }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" id="rev-idmac" value="{{ auth()->user()->idcentro_mac }}">
                <div class="form-group mb-3">
                    <label>Centro MAC</label>
                    <input type="text" class="form-control" value="{{ auth()->user()->centroMac->NOMBRE_MAC ?? '' }}"
                        disabled>
                </div>
            @endif

            <div class="form-group mb-3">
                <label for="rev-fecha">Fecha</label>
                <input type="date" id="rev-fecha" class="form-control">
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn btn-outline-danger" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-outline-success" onclick="storeRevertir()">
                <i class="fa fa-undo"></i> Revertir
            </button>
        </div>
    </div>
</div>
