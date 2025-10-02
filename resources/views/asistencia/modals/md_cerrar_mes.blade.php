<div class="modal-dialog modal-md">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><i class="fa fa-calendar"></i> Cerrar Mes</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
            {{-- Año --}}
            <div class="form-group mb-3">
                <label for="cerrar-anio">Año</label>
                <input type="number" id="cerrar-anio" class="form-control" value="{{ date('Y') }}">
            </div>

            {{-- Mes --}}
            <div class="form-group mb-3">
                <label for="cerrar-mes">Mes</label>
                <select id="cerrar-mes" class="form-control">
                    <option value="">-- Seleccione --</option>
                    @php
                        $meses = [
                            1 => 'Enero',
                            2 => 'Febrero',
                            3 => 'Marzo',
                            4 => 'Abril',
                            5 => 'Mayo',
                            6 => 'Junio',
                            7 => 'Julio',
                            8 => 'Agosto',
                            9 => 'Septiembre',
                            10 => 'Octubre',
                            11 => 'Noviembre',
                            12 => 'Diciembre',
                        ];
                        $mesActual = date('n');
                        $anioActual = date('Y');

                        // Calcular mes anterior
                        $mesAnterior = $mesActual - 1;
                        $anioCierre = $anioActual;
                        if ($mesAnterior == 0) {
                            $mesAnterior = 12;
                            $anioCierre = $anioActual - 1;
                        }
                    @endphp
                    <option value="{{ $mesAnterior }}" selected>
                        {{ $meses[$mesAnterior] }}
                    </option>
                </select>
                <input type="hidden" id="cerrar-anio" value="{{ $anioCierre }}">
            </div>

        </div>

        <div class="modal-footer">
            <button class="btn btn-outline-danger" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-outline-success" onclick="confirmarCerrarMes()">
                <i class="fa fa-lock"></i> Cerrar Mes
            </button>
        </div>
    </div>
</div>
