<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Detalle del biometrico registrado</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <h5>Detalle de los ingresos del día <?php echo strftime('%d de %B del %Y', strtotime($fecha_)); ?></h5>
            <table class="table table-bordered">
                <thead class="bg-dark" style="color: #fff;">
                    <tr>
                        <th>N°</th>
                        <th class="d-none">ID</th>
                        <th>DNI</th>
                        <th>Hora</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-marcaciones">
                    @forelse ($query as $q)
                        @php
                            $horas = explode(',', $q->HORAS);
                            $num_horas = count($horas);
                        @endphp
                        @foreach ($horas as $index => $hora)
                            <tr>
                                <td>{{ $loop->parent->index + 1 }}.{{ $index + 1 }}</td>
                                <td class="d-none">{{ $q->IDASISTENCIA }}</td>
                                <td>{{ $q->NUM_DOC }}</td>
                                <td>{{ $hora }}</td>
                                <td>
                                    <!-- Botón para eliminar -->
                                    <button class="btn btn-danger btn-sm"
                                        onclick="eliminarHora({{ $q->IDASISTENCIA }})">Eliminar</button>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="4">No hay datos disponibles</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
        </div>
    </div>
</div>

<script>
    tippy(".bandejTool", {
        allowHTML: true,
        followCursor: true,
    });

    function eliminarHora(idAsistencia) {
        console.log("elimnar tabla");
        Swal.fire({
            title: '¿Estás seguro?',
            text: "No podrás revertir esta acción!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('asistencia.eliminar_hora') }}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        idAsistencia: idAsistencia
                    },
                    success: function(response) {
                        recargarTablaAlCerrarModal = true; 

                        if (response.success) {
                            Toastify({
                                text: response.message,
                                className: "info",
                                gravity: "bottom",
                                style: {
                                    background: "#47B257",
                                }
                            }).showToast();
                            // Aquí identificas la fila a eliminar usando el ID de la asistencia
                            $('#tabla-marcaciones tr').each(function() {
                                if ($(this).find('td.d-none').text() == idAsistencia) {
                                    $(this).remove();
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: 'Error!',
                                text: response.message,
                                confirmButtonText: "Aceptar"
                            });
                        }
                    },
                    error: function(error) {
                        Swal.fire({
                            icon: "error",
                            title: 'Error!',
                            text: "No se pudo eliminar la hora.",
                            confirmButtonText: "Aceptar"
                        });
                    }
                });
            }
        });
    }
</script>
