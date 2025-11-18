<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">
                Manejo de observaciones
                {{-- Mostrar conteo si quieres --}}
                {{-- <span class="badge bg-primary">{{ $count_observaciones }}</span> --}}
            </h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
            <form id="formObservaciones">
                @csrf

                <input type="hidden" id="num_doc" value="{{ $num_doc }}">
                <input type="hidden" id="fecha" value="{{ $fecha_d }}">
                <input type="hidden" id="idcentro_mac" value="{{ $mac_d }}">

                <div class="mb-3">
                    <h5>DATOS DEL USUARIO</h5>
                    <input type="text" class="form-control" value="{{ $personal->nombreu }}" readonly>
                </div>

                <div class="mb-3">
                    <h5>AGREGAR OBSERVACIÓN</h5>
                    <textarea id="txtObservacion" class="form-control" rows="2" placeholder="Escribir aquí la observación…"></textarea>

                    <div class="d-grid gap-2 mt-2">
                        <button id="btnAddObs" class="btn btn-info" type="button">
                            Añadir observación
                        </button>
                    </div>
                </div>

                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Observación</th>
                            <th>Acción</th>
                        </tr>
                    </thead>

                    <tbody id="obsTableBody">
                        @forelse ($observacion as $i => $dat)
                            <tr data-id="{{ $dat->id_asistencia_obv }}">
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $dat->observacion }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger btn-del-obs"
                                        data-id="{{ $dat->id_asistencia_obv }}">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-danger">
                                    No hay observaciones registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </form>
        </div>

        <div class="modal-footer">
            <button class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
        </div>
    </div>
</div>
<script>
    $(function() {

        // Añadir observación
        $('#btnAddObs').on('click', function() {
            const observacion = $('#txtObservacion').val().trim();
            if (!observacion) return;

            const data = {
                _token: $('#formObservaciones input[name="_token"]').val(),
                NUM_DOC: $('#num_doc').val(),
                FECHA: $('#fecha').val(),
                OBSERVACION: observacion
            };

            $.post("{{ route('asistencia.store_agregar_observacion') }}", data)
                .done(function(res) {
                    if (res.success) {
                        const id = res.id;
                        const $tbody = $('#obsTableBody');

                        // Si solo existe el row "No hay registros"
                        if ($tbody.find('tr').length === 1 && $tbody.find('td').length === 1) {
                            $tbody.empty();
                        }

                        const rowCount = $tbody.find('tr').length + 1;

                        const $tr = $(`
                        <tr data-id="${id}">
                            <td>${rowCount}</td>
                            <td>${observacion}</td>
                            <td>
                                <button class="btn btn-sm btn-danger btn-del-obs" data-id="${id}">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    `);

                        $tbody.append($tr);
                        $('#txtObservacion').val('');

                        Toastify({
                            text: "El registro se ingresó correctamente",
                            className: "success",
                            gravity: "top",
                            position: "right",
                            style: {
                                background: "#1d9c19"
                            }
                        }).showToast();

                        const newCount = $('#obsTableBody tr').length;
                        updateObservationIcon($('#num_doc').val(), newCount);
                    }
                })
                .fail(function(err) {
                    console.error('Error al agregar observación', err);
                });
        });
    });


    // Eliminar observación
    $(document).off('click', '.btn-del-obs');

    $(document).on('click', '.btn-del-obs', function(e) {
        e.preventDefault();
        const id = parseInt($(this).data('id'), 10);
        if (isNaN(id)) return;

        $.post("{{ route('asistencia.eliminar_observacion') }}", {
                _token: $('#formObservaciones input[name="_token"]').val(),
                id: id
            })
            .done(function(res) {
                if (res.success) {
                    const $row = $(`tr[data-id="${id}"]`);
                    $row.remove();

                    $('#obsTableBody tr').each(function(i) {
                        $(this).find('td:first').text(i + 1);
                    });

                    const newCount = $('#obsTableBody tr').length;
                    updateObservationIcon($('#num_doc').val(), newCount);

                    Toastify({
                        text: "Se eliminó el registro con éxito",
                        className: "success",
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "#1d9c19"
                        }
                    }).showToast();

                } else {
                    Toastify({
                        text: "Paso algo, por favor vuelve a intentar",
                        className: "error",
                        gravity: "bottom",
                        position: "right",
                        style: {
                            background: "#D9534F"
                        }
                    }).showToast();
                }
            })
            .fail(function(err) {
                console.error('Error al eliminar observación', err);
            });
    });
</script>
