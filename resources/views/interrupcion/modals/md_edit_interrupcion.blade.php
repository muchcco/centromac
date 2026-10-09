<style>
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #010220 !important;
        color: white !important;
        border: 1px solid #010220 !important;
        font-weight: bold;
    }
</style>

<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Editar Interrupción</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
            <div id="alerta"></div>
            <h5>Editar datos de la Interrupción</h5>

            <form id="form_edit_interrupcion" class="form-horizontal">
                @csrf
                <input type="hidden" name="id_interrupcion" value="{{ $interrupcion->id_interrupcion }}">
                <input type="hidden" name="responsable" value="{{ $interrupcion->responsable }}">
                <input type="hidden" name="idcentro_mac" value="{{ $interrupcion->idcentro_mac }}">
                <input type="hidden" name="estado" id="estado" value="{{ $interrupcion->estado }}">

                <!-- Tipificación -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Tipificación</label>
                    <div class="col-9">
                        <select class="form-control select2" name="id_tipo_int_obs" id="id_tipo_int_obs">
                            <option value="">--Seleccione--</option>
                            @foreach ($tipos as $tipo)
                            <option value="{{ $tipo->id_tipo_int_obs }}"
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
                        <select class="form-control select2" name="identidad" id="identidad">
                            <option value="">--Seleccione--</option>
                            @foreach ($entidades as $entidad)
                            <option value="{{ $entidad->identidad }}"
                                {{ $interrupcion->identidad == $entidad->identidad ? 'selected' : '' }}>
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
                        <input type="text" class="form-control" name="servicio_involucrado"
                            value="{{ $interrupcion->servicio_involucrado }}">
                    </div>
                </div>

                <!-- Fecha Inicio y Hora -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha Inicio</label>
                    <div class="col-4">
                        <input type="date" class="form-control" name="fecha_inicio"
                            value="{{ $interrupcion->fecha_inicio }}">
                    </div>
                    <label class="col-2 col-form-label text-center">Hora Inicio</label>
                    <div class="col-3">
                        <input type="time" class="form-control" name="hora_inicio"
                            value="{{ \Carbon\Carbon::parse($interrupcion->hora_inicio)->format('H:i') }}">
                    </div>
                </div>

                <!-- Interrupción en curso -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">¿Interrupción en curso?</label>
                    <div class="col-9">
                        <input type="checkbox" id="interrupcion_curso"
                            {{ $interrupcion->estado == 'ABIERTO' ? 'checked' : '' }}>
                        <span id="estado_interrupcion">{{ $interrupcion->estado == 'ABIERTO' ? 'Sí' : 'No' }}</span>
                    </div>
                </div>

                <!-- Descripción -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Descripción</label>
                    <div class="col-9">
                        <textarea class="form-control" name="descripcion">{{ $interrupcion->descripcion }}</textarea>
                    </div>
                </div>

                <!-- Acción Tomada -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Acción Tomada</label>
                    <div class="col-9">
                        <textarea class="form-control" name="descripcion_accion">{{ $interrupcion->descripcion_accion }}</textarea>
                    </div>
                </div>

                <!-- Campos finales -->
                <div id="campos_fin" style="{{ $interrupcion->estado == 'ABIERTO' ? 'display:none;' : '' }}">
                    <div class="row mb-3">
                        <label class="col-3 col-form-label">Estado Final</label>
                        <div class="col-9">
                            <select class="form-control select2" name="estado_final" id="estado_final">
                                <option value="CERRADO" {{ $interrupcion->estado == 'CERRADO' ? 'selected' : '' }}>
                                    CERRADO
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
                </div>
            </form>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" onclick="btnUpdateInterrupcion()">Guardar</button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        const form = $('#form_edit_interrupcion');
        const fechaInicio = form.find('[name="fecha_inicio"]');
        const fechaFin = form.find('[name="fecha_fin"]');
        const fechaMinima = '2026-10-01';
        const hoy = new Date();
        const fechaMaxima = [
            hoy.getFullYear(),
            String(hoy.getMonth() + 1).padStart(2, '0'),
            String(hoy.getDate()).padStart(2, '0')
        ].join('-');

        $('.select2').select2({
            dropdownParent: $('#modal_show_modal'),
            width: '100%',
            allowClear: true
        });

        fechaInicio.attr({
            min: fechaMinima,
            max: fechaMaxima
        });
        fechaFin.attr({
            min: fechaMinima,
            max: fechaMaxima
        });

        function alertaFecha(mensaje) {
            Swal.fire({
                icon: 'warning',
                title: 'Fecha no permitida',
                text: mensaje,
                confirmButtonText: 'Aceptar'
            });
        }

        function toggleCamposFin() {
            if ($('#interrupcion_curso').is(':checked')) {
                $('#estado_interrupcion').text('Sí');
                $('#estado').val('ABIERTO');
                $('#campos_fin').hide();
            } else {
                $('#estado_interrupcion').text('No');
                $('#estado').val($('#estado_final').val());
                $('#campos_fin').show();
            }
        }

        fechaInicio.on('blur', function() {
            const fecha = $(this).val();
            if (fecha && (fecha < fechaMinima || fecha > fechaMaxima)) {
                $(this).val('');
                alertaFecha('La fecha de inicio debe estar entre el 01/10/2026 y la fecha actual.');
                return;
            }
            fechaFin.attr('min', fecha || fechaMinima);
            if (fechaFin.val() && fecha && fechaFin.val() < fecha) {
                fechaFin.val('');
                alertaFecha('La fecha de fin no puede ser anterior a la fecha de inicio.');
            }
        });

        fechaFin.on('blur', function() {
            const inicio = fechaInicio.val();
            const fin = $(this).val();
            if (fin && (fin < fechaMinima || fin > fechaMaxima || (inicio && fin < inicio))) {
                $(this).val('');
                alertaFecha('La fecha de fin debe ser igual o posterior a la fecha de inicio y no puede ser futura.');
            }
        });

        $('#interrupcion_curso').on('change', toggleCamposFin);

        $('#estado_final').on('change', function() {
            if (!$('#interrupcion_curso').is(':checked')) {
                $('#estado').val($(this).val());
            }
        });

        toggleCamposFin();

        window.btnUpdateInterrupcion = async function() {
            const inicio = fechaInicio.val();
            const fin = fechaFin.val();
            const horaInicio = form.find('[name="hora_inicio"]').val();
            const horaFin = form.find('[name="hora_fin"]').val();
            const estado = $('#estado').val();
            const boton = form.closest('.modal-content').find('.modal-footer button:last');

            if (!inicio || inicio < fechaMinima || inicio > fechaMaxima) {
                alertaFecha('Solo se pueden editar interrupciones desde el 01/10/2026 hasta la fecha actual.');
                return;
            }

            if (!horaInicio) {
                Swal.fire('Datos incompletos', 'Ingrese la hora de inicio.', 'warning');
                return;
            }

            if (estado === 'CERRADO') {
                if (!fin || !horaFin) {
                    alertaFecha('Debe ingresar la fecha y hora de fin.');
                    return;
                }
                if (fin < inicio || fin > fechaMaxima) {
                    alertaFecha('La fecha de fin no puede ser anterior a la fecha de inicio ni posterior a hoy.');
                    return;
                }
                if (fin === inicio && horaFin <= horaInicio) {
                    alertaFecha('La hora de fin debe ser posterior a la hora de inicio.');
                    return;
                }
            }

            if (typeof validarFechasHoras === 'function') {
                const valido = await validarFechasHoras(form, estado);
                if (!valido) return;
            }

            const formData = new FormData(form[0]);

            $.ajax({
                type: 'POST',
                url: "{{ route('interrupcion.update') }}",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    boton.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> ESPERE');
                },
                success: function(data) {
                    if (data.status == 200) {
                        $('#modal_show_modal').modal('hide');
                        cargarTablaInterrupciones();
                        Swal.fire('Actualización exitosa', data.message || 'Interrupción actualizada correctamente.', 'success');
                    } else {
                        Swal.fire('Atención', data.message || 'No se pudo actualizar la interrupción.', 'warning');
                    }
                },
                error: function(xhr) {
                    const respuesta = xhr.responseJSON || {};
                    let mensaje = 'No se pudo actualizar la interrupción.';
                    if (typeof respuesta.message === 'string') {
                        mensaje = respuesta.message;
                    } else if (respuesta.message && typeof respuesta.message === 'object') {
                        mensaje = Object.values(respuesta.message).flat().join('\n');
                    } else if (respuesta.errors) {
                        mensaje = Object.values(respuesta.errors).flat().join('\n');
                    }
                    Swal.fire({
                        icon: 'warning',
                        title: xhr.status === 403 ? 'Acceso restringido' : 'No se pudo actualizar',
                        text: mensaje,
                        confirmButtonText: 'Aceptar'
                    });
                },
                complete: function() {
                    boton.prop('disabled', false).html('Guardar');
                }
            });
        };
    });
</script>