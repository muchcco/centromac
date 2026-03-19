<div class="modal-dialog modal-xl">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Cambio de tiempos de servicio</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Servicio</th>
                        <th>Fin actual</th>
                        <th>Nueva espera</th>
                        <th>Nueva atención</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($servicios as $i => $s)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $s->nome }}</td>
                            <td>
                                <input type="date" class="form-control fecha_fin" data-id="{{ $s->id_servicio }}"
                                    value="{{ $s->fecha_fin }}">
                            </td>
                            <td>
                                <input type="number" class="form-control espera" data-id="{{ $s->id_servicio }}">
                            </td>
                            <td>
                                <input type="number" class="form-control atencion" data-id="{{ $s->id_servicio }}">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="modal-footer">
            <button class="btn btn-danger" data-bs-dismiss="modal">
                Cerrar
            </button>
            <button class="btn btn-success" onclick="guardarCambioTiempos()">
                Guardar cambio
            </button>
        </div>
    </div>
</div>
