<div class="modal-dialog modal-dialog-centered" style="max-width:95%">

    <div class="modal-content">

        <div class="modal-header py-2 px-3">
            <h6 class="modal-title fw-semibold">
                Servicios de {{ $servicios->first()->entidad }}
            </h6>
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body p-2">

            <!-- 🔥 CONTADOR -->
            <div class="d-flex justify-content-between align-items-center mb-2 px-2">
                <small id="contador_calculo" class="fw-bold text-primary">
                    0 activos / 0 total
                </small>
            </div>

            <table class="table table-sm table-bordered align-middle mb-0 small">

                <thead class="tenca text-center">
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

                <tbody>

                    @foreach ($servicios as $i => $s)
                        <tr>

                            <td class="text-center">{{ $i + 1 }}</td>

                            <td class="text-uppercase lh-sm">
                                {{ $s->nome }}
                            </td>

                            <td>
                                <input type="date" class="form-control form-control-sm fecha_inicio"
                                    data-id="{{ $s->id_servicio }}"
                                    value="{{ $s->fecha_inicio ? \Carbon\Carbon::parse($s->fecha_inicio)->format('Y-m-d') : '' }}">
                            </td>

                            <td>
                                <input type="date" class="form-control form-control-sm fecha_fin"
                                    data-id="{{ $s->id_servicio }}"
                                    value="{{ $s->fecha_fin ? \Carbon\Carbon::parse($s->fecha_fin)->format('Y-m-d') : '' }}">
                            </td>

                            <td>
                                <input type="number" class="form-control form-control-sm text-center espera"
                                    data-id="{{ $s->id_servicio }}" value="{{ $s->limite_espera }}">
                            </td>

                            <td>
                                <input type="number" class="form-control form-control-sm text-center atencion"
                                    data-id="{{ $s->id_servicio }}" value="{{ $s->limite_atencion }}">
                            </td>

                            <td class="text-center">
                                @if ($s->status == 1)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-danger">Inactivo</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="form-check form-switch d-flex justify-content-center m-0">
                                    <input class="form-check-input calcula" type="checkbox"
                                        data-id="{{ $s->id_servicio }}" {{ $s->se_calcula == 1 ? 'checked' : '' }}>
                                </div>
                            </td>

                        </tr>
                    @endforeach

                </tbody>

            </table>

        </div>

        <div class="modal-footer py-2 px-3">

            <button class="btn btn-primary btn-sm" onclick="activarCalculoTodos()">
                <i class="fa fa-check"></i> Activar todos
            </button>

            <button class="btn btn-danger btn-sm" onclick="quitarCalculoTodos()">
                <i class="fa fa-ban"></i> Quitar calcular a todos
            </button>

            <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                Cerrar
            </button>

            <button class="btn btn-success btn-sm" onclick="guardarTiemposEntidad()">
                Guardar Cambios
            </button>

        </div>

    </div>

</div>
@section('script')
    <script>
        // 🔥 CONTADOR + CONTROL BOTONES
        function actualizarEstadoCalculo() {

            let total = $(".calcula").length;
            let activos = $(".calcula:checked").length;

            $("#contador_calculo").text(`${activos} activos / ${total} total`);

            if (activos === total) {
                $("button[onclick='activarCalculoTodos()']").prop("disabled", true);
                $("button[onclick='quitarCalculoTodos()']").prop("disabled", false);
            } else if (activos === 0) {
                $("button[onclick='activarCalculoTodos()']").prop("disabled", false);
                $("button[onclick='quitarCalculoTodos()']").prop("disabled", true);
            } else {
                $("button[onclick='activarCalculoTodos()']").prop("disabled", false);
                $("button[onclick='quitarCalculoTodos()']").prop("disabled", false);
            }
        }

        // 🔥 CAMBIO MANUAL
        $(document).on("change", ".calcula", function() {
            actualizarEstadoCalculo();
        });

        // 🔥 INICIALIZAR AL ABRIR MODAL
        setTimeout(() => {
            actualizarEstadoCalculo();
        }, 300);

        // 🔥 BOTONES
        function activarCalculoTodos() {
            $(".calcula").prop("checked", true);
            actualizarEstadoCalculo();
        }

        function quitarCalculoTodos() {

            Swal.fire({
                title: "¿Seguro?",
                text: "Se quitará el cálculo a todos los servicios",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, quitar",
                cancelButtonText: "Cancelar"
            }).then((result) => {

                if (result.isConfirmed) {
                    $(".calcula").prop("checked", false);
                    actualizarEstadoCalculo();
                }

            });

        }
    </script>
@endsection
