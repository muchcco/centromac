<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

        <div class="modal-header">
            <h4 class="modal-title">
                <i class="fa fa-unlock"></i>
                Habilitar cierre fuera de plazo
            </h4>

            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>


        <div class="modal-body">

            <div class="alert alert-warning">
                <i class="fa fa-info-circle"></i>
                Esta opción permitirá cerrar la asistencia fuera del plazo normal.
            </div>


            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Centro MAC</label>

                    <select id="ex_mac" class="form-control">

                        @foreach ($macs as $m)
                            <option value="{{ $m->id }}">
                                {{ $m->nom }}
                            </option>
                        @endforeach

                    </select>

                </div>


                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Fecha de asistencia</label>

                    <input type="date" id="ex_fecha" class="form-control">

                </div>


                <div class="col-md-6 mb-3">

                    <label class="form-label fw-bold">
                        Válido hasta
                    </label>

                    <input type="datetime-local" id="ex_valido_hasta" class="form-control">

                </div>


                <div class="col-md-12 mb-3">

                    <label class="form-label fw-bold">
                        Motivo
                    </label>

                    <textarea id="ex_motivo" rows="3" class="form-control" placeholder="Explique el motivo de la habilitación"></textarea>

                </div>


            </div>

        </div>


        <div class="modal-footer">

            <button class="btn btn-success" onclick="guardarExcepcion()">

                <i class="fa fa-save"></i>
                Registrar excepción

            </button>

            <button class="btn btn-outline-danger" data-bs-dismiss="modal">
                Cerrar
            </button>

        </div>

    </div>
</div>
