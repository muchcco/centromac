<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow-lg">

        <div class="modal-header bg-dark text-white">
            <h4 class="modal-title mb-0">
                Habilitar cierre fuera de plazo
            </h4>

            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

            <div class="alert alert-warning border-0 shadow-sm mb-4">
                <strong>Importante:</strong><br>
                Esta opción permitirá cerrar la asistencia fuera del plazo normal por rango de fechas.
                La habilitación será válida por 24 horas desde el registro.
            </div>

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Centro MAC</label>

                    <select id="ex_mac" name="idmac" class="form-control">
                        @foreach ($macs as $m)
                        <option value="{{ $m->id }}">
                            {{ $m->nom }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">Fecha inicio</label>
                    <input type="date" id="ex_fecha_inicio" name="fecha_inicio" class="form-control">
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">Fecha fin</label>
                    <input type="date" id="ex_fecha_fin" name="fecha_fin" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Válido hasta</label>
                    <input type="datetime-local" id="ex_valido_hasta" name="valido_hasta" class="form-control" readonly>

                    <small class="text-muted">
                        Se genera automáticamente con 24 horas de vigencia.
                    </small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Días seleccionados</label>
                    <input type="text" id="ex_total_dias" class="form-control bg-light" readonly>
                    <small class="text-muted">
                        Cantidad referencial según el rango elegido.
                    </small>
                </div>

                <div class="col-md-12 mb-3">
                    <div class="p-3 rounded border bg-light">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="ex_revertir_cerrado" value="1">

                            <label class="form-check-label fw-bold text-danger" for="ex_revertir_cerrado">
                                Revertir cierre si ya está cerrado
                            </label>
                        </div>

                        <small class="text-muted d-block mt-1">
                            Si una fecha ya fue cerrada, se quitará el cierre anterior y luego se registrará la excepción.
                            Usar solo cuando sea necesario.
                        </small>
                    </div>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label fw-bold">Motivo</label>

                    <textarea
                        id="ex_motivo"
                        name="motivo"
                        rows="3"
                        class="form-control"
                        placeholder="Explique el motivo de la habilitación"></textarea>
                </div>

                <div class="col-md-12">
                    <div id="ex_resumen" class="alert alert-info border-0 mb-0">
                        Complete los datos para ver el resumen de la excepción.
                    </div>
                </div>

            </div>

        </div>

        <div class="modal-footer bg-light">

            <button type="button" id="btn_guardar_excepcion" class="btn btn-success" onclick="guardarExcepcion()">
                Registrar excepción
            </button>

            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">
                Cerrar
            </button>

        </div>

    </div>
</div>

<script>
    function formatearFechaLocal(date) {
        const yyyy = date.getFullYear()
        const mm = String(date.getMonth() + 1).padStart(2, '0')
        const dd = String(date.getDate()).padStart(2, '0')

        return `${yyyy}-${mm}-${dd}`
    }

    function formatearDateTimeLocal(date) {
        const yyyy = date.getFullYear()
        const mm = String(date.getMonth() + 1).padStart(2, '0')
        const dd = String(date.getDate()).padStart(2, '0')
        const hh = String(date.getHours()).padStart(2, '0')
        const mi = String(date.getMinutes()).padStart(2, '0')

        return `${yyyy}-${mm}-${dd}T${hh}:${mi}`
    }

    function calcularDiasRango() {
        const inicio = $('#ex_fecha_inicio').val()
        const fin = $('#ex_fecha_fin').val()

        if (!inicio || !fin) {
            $('#ex_total_dias').val('')
            $('#ex_resumen').html('Complete los datos para ver el resumen de la excepción.')
            return
        }

        if (inicio > fin) {
            $('#ex_total_dias').val('Rango inválido')
            $('#ex_resumen').removeClass('alert-info').addClass('alert-danger')
            $('#ex_resumen').html('La fecha inicio no puede ser mayor que la fecha fin.')
            return
        }

        const fechaInicio = new Date(inicio + 'T00:00:00')
        const fechaFin = new Date(fin + 'T00:00:00')
        const diff = fechaFin - fechaInicio
        const dias = Math.floor(diff / (1000 * 60 * 60 * 24)) + 1

        $('#ex_total_dias').val(dias + ' día(s)')

        const macTexto = $('#ex_mac option:selected').text().trim()
        const validoHasta = $('#ex_valido_hasta').val()
        const revertir = $('#ex_revertir_cerrado').is(':checked')

        $('#ex_resumen').removeClass('alert-danger').addClass('alert-info')

        let html = `
            <strong>Resumen:</strong><br>
            Centro MAC: <strong>${macTexto}</strong><br>
            Rango: <strong>${inicio}</strong> al <strong>${fin}</strong><br>
            Total referencial: <strong>${dias} día(s)</strong><br>
            Válido hasta: <strong>${validoHasta.replace('T', ' ')}</strong>
        `

        if (revertir) {
            html += `
                <br>
                <span class="text-danger">
                    Se revertirán los días cerrados dentro del rango.
                </span>
            `
        }

        $('#ex_resumen').html(html)
    }

    function bloquearBotonExcepcion(bloquear = true) {
        const btn = $('#btn_guardar_excepcion')

        if (bloquear) {
            btn.prop('disabled', true)
            btn.html(`
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Registrando...
            `)
        } else {
            btn.prop('disabled', false)
            btn.html('Registrar excepción')
        }
    }

    $(document).ready(function() {
        const ahora = new Date()
        const validoHasta = new Date()
        validoHasta.setHours(validoHasta.getHours() + 24)

        $('#ex_valido_hasta').val(formatearDateTimeLocal(validoHasta))
        $('#ex_fecha_inicio').val(formatearFechaLocal(ahora))
        $('#ex_fecha_fin').val(formatearFechaLocal(ahora))

        calcularDiasRango()

        $('#ex_fecha_inicio, #ex_fecha_fin, #ex_mac, #ex_revertir_cerrado').on('change', function() {
            calcularDiasRango()
        })
    })
</script>