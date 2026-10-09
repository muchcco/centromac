<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Subsanar Interrupción</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
            <div id="alerta"></div>

            <form id="form_subsanar_interrupcion" class="form-horizontal">
                @csrf
                <input type="hidden" name="id_interrupcion" value="{{ $interrupcion->id_interrupcion }}">
                <input type="hidden" name="responsable" value="{{ $interrupcion->responsable }}">
                <input type="hidden" name="idcentro_mac" value="{{ $interrupcion->idcentro_mac }}">
                <input type="hidden" name="estado" id="estado" value="{{ $interrupcion->estado }}">

                <!-- Tipificación -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Tipificación</label>
                    <div class="col-9">
                        <select class="form-control" disabled>
                            @foreach ($tipos as $tipo)
                            <option
                                {{ $interrupcion->id_tipo_int_obs == $tipo->id_tipo_int_obs ? 'selected' : '' }}>
                                {{ $tipo->tipo }} {{ $tipo->numeracion }} - {{ $tipo->nom_tipo_int_obs }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Entidad -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Entidad</label>
                    <div class="col-9">
                        <select class="form-control" disabled>
                            @foreach ($entidades as $entidad)
                            <option {{ $interrupcion->identidad == $entidad->identidad ? 'selected' : '' }}>
                                {{ $entidad->abrev_entidad }} - {{ $entidad->nombre_entidad }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Servicio Involucrado -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Servicio Involucrado</label>
                    <div class="col-9">
                        <input type="text" class="form-control" value="{{ $interrupcion->servicio_involucrado }}"
                            disabled>
                    </div>
                </div>

                <!-- Fecha y Hora de Inicio -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha Inicio</label>
                    <div class="col-4">
                        <input type="date" class="form-control" value="{{ $interrupcion->fecha_inicio }}" disabled>
                    </div>
                    <label class="col-2 col-form-label text-center">Hora Inicio</label>
                    <div class="col-3">
                        <input type="time" class="form-control"
                            value="{{ \Carbon\Carbon::parse($interrupcion->hora_inicio)->format('H:i') }}" disabled>
                    </div>
                </div>

                <!-- Descripción -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Descripción</label>
                    <div class="col-9">
                        <textarea class="form-control" disabled>{{ $interrupcion->descripcion }}</textarea>
                    </div>
                </div>

                <!-- Datos para Subsanar -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Estado Final</label>
                    <div class="col-9">
                        <select class="form-control" name="estado_final" id="estado_final">
                            <option value="#">Seleccione...</option>
                            <option value="CERRADO" {{ $interrupcion->estado == 'CERRADO' ? 'selected' : '' }}>CERRADO
                            </option>
                        </select>
                    </div>
                </div>

                <!-- 🔹 Campo Acción Correctiva ELIMINADO -->

                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha Fin</label>
                    <div class="col-4">
                        <input type="date" class="form-control" name="fecha_fin"
                            value="{{ $interrupcion->fecha_fin }}">
                    </div>
                    <label class="col-2 col-form-label text-center">Hora Fin</label>
                    <div class="col-3">
                        <input type="time" class="form-control" name="hora_fin"
                            value="{{ $interrupcion->hora_fin ? \Carbon\Carbon::parse($interrupcion->hora_fin)->format('H:i') : '' }}">
                    </div>
                </div>
            </form>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" onclick="btnSubsanarGuardar()">Guardar</button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        const form = $('#form_subsanar_interrupcion');
        const fechaMinima = '2026-10-01';
        const fechaInicio = "{{ \Carbon\Carbon::parse($interrupcion->fecha_inicio)->format('Y-m-d') }}";
        const horaInicio = "{{ \Carbon\Carbon::parse($interrupcion->hora_inicio)->format('H:i') }}";
        const hoy = new Date();
        const fechaMaxima = [
            hoy.getFullYear(),
            String(hoy.getMonth() + 1).padStart(2, '0'),
            String(hoy.getDate()).padStart(2, '0')
        ].join('-');

        const fechaFin = form.find('[name="fecha_fin"]');
        fechaFin.attr({
            min: fechaInicio > fechaMinima ? fechaInicio : fechaMinima,
            max: fechaMaxima
        });

        $('#estado_final').on('change', function() {
            $('#estado').val($(this).val());
        });

        function mostrarAlerta(mensaje) {
            Swal.fire({
                icon: 'warning',
                title: 'Validación de interrupción',
                text: mensaje,
                confirmButtonText: 'Aceptar'
            });
        }

        window.btnSubsanarGuardar = async function() {
            const estado = $('#estado_final').val();
            const fin = fechaFin.val();
            const horaFin = form.find('[name="hora_fin"]').val();
            const boton = form.closest('.modal-content').find('.modal-footer button:last');

            if (fechaInicio < fechaMinima) {
                mostrarAlerta('No se pueden subsanar interrupciones con fecha de inicio anterior al 01/10/2026.');
                return;
            }

            if (estado !== 'CERRADO') {
                mostrarAlerta('Debe seleccionar el estado CERRADO.');
                return;
            }

            if (!fin || !horaFin) {
                mostrarAlerta('Debe ingresar la fecha y hora de fin.');
                return;
            }

            if (fin < fechaInicio || fin > fechaMaxima) {
                mostrarAlerta('La fecha de fin no puede ser anterior a la fecha de inicio ni posterior a la fecha actual.');
                return;
            }

            if (fin === fechaInicio && horaFin <= horaInicio) {
                mostrarAlerta('La hora de fin debe ser posterior a la hora de inicio.');
                return;
            }

            const formData = new FormData(form[0]);
            formData.set('estado', estado);

            $.ajax({
                url: "{{ route('interrupcion.subsanar') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    boton.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');
                },
                success: function(data) {
                    if (data.status == 200) {
                        $('#modal_show_modal').modal('hide');
                        cargarTablaInterrupciones();
                        Swal.fire('Éxito', data.message || 'Interrupción subsanada correctamente.', 'success');
                    } else {
                        mostrarAlerta(data.message || 'No se pudo guardar la subsanación.');
                    }
                },
                error: function(xhr) {
                    const respuesta = xhr.responseJSON || {};
                    let mensaje = 'No se pudo guardar la subsanación.';

                    if (typeof respuesta.message === 'string') {
                        mensaje = respuesta.message;
                    } else if (respuesta.message && typeof respuesta.message === 'object') {
                        mensaje = Object.values(respuesta.message).flat().join('\n');
                    } else if (respuesta.errors) {
                        mensaje = Object.values(respuesta.errors).flat().join('\n');
                    }

                    mostrarAlerta(mensaje);
                },
                complete: function() {
                    boton.prop('disabled', false).html('Guardar');
                }
            });
        };
    });
</script>