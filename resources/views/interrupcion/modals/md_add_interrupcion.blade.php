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
            <h4 class="modal-title">Registrar Nueva Interrupción</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
            <div id="alerta"></div>
            <h5>Datos de la Interrupción</h5>

            <form id="form_add_interrupcion" class="form-horizontal">
                @csrf

                <input type="hidden" name="responsable" value="{{ auth()->user()->id }}">
                <input type="hidden" name="idcentro_mac" value="{{ auth()->user()->idcentro_mac }}">
                <input type="hidden" name="estado" id="estado" value="ABIERTO">

                <!-- Tipificación -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Tipificación</label>
                    <div class="col-9">
                        <select class="form-control select2" name="id_tipo_int_obs" id="id_tipo_int_obs">
                            <option value="">--Seleccione--</option>
                            @foreach ($tipos as $tipo)
                            <option value="{{ $tipo->id_tipo_int_obs }}">
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
                            <option value="{{ $entidad->identidad }}">
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
                        <input type="text" class="form-control" name="servicio_involucrado">
                    </div>
                </div>

                <!-- Fecha y hora inicio -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha Inicio</label>
                    <div class="col-4">
                        <input type="date" class="form-control" name="fecha_inicio">
                    </div>
                    <label class="col-2 col-form-label text-center">Hora Inicio</label>
                    <div class="col-3">
                        <input type="time" class="form-control" name="hora_inicio">
                    </div>
                </div>

                <!-- ¿Interrupción en curso? -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">¿Interrupción en curso?</label>
                    <div class="col-9">
                        <input type="checkbox" id="interrupcion_curso" checked>
                        <span id="estado_interrupcion">ABIERTO</span>
                    </div>
                </div>

                <!-- Descripción -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Descripción</label>
                    <div class="col-9">
                        <textarea class="form-control" name="descripcion" rows="3"></textarea>
                    </div>
                </div>

                <!-- Acción tomada -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Acción Tomada</label>
                    <div class="col-9">
                        <textarea class="form-control" name="descripcion_accion" rows="3"></textarea>
                    </div>
                </div>

                <!-- Campos ocultos (cuando se desmarca el checkbox) -->
                <div id="campos_fin" style="display: none;">
                    <div class="row mb-3">
                        <label class="col-3 col-form-label">Estado Final</label>
                        <div class="col-9">
                            <select class="form-control select2" name="estado_final" id="estado_final">
                                <option value="CERRADO">CERRADO</option>
                            </select>
                        </div>
                    </div>

                    <!-- 🔹 Acción Correctiva ELIMINADA -->

                    <div class="row mb-3">
                        <label class="col-3 col-form-label">Fecha Fin</label>
                        <div class="col-4">
                            <input type="date" class="form-control" name="fecha_fin">
                        </div>
                        <label class="col-2 col-form-label text-center">Hora Fin</label>
                        <div class="col-3">
                            <input type="time" class="form-control" name="hora_fin">
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm"
                onclick="btnStoreInterrupcion()">Guardar</button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.select2').select2({
            dropdownParent: $('#modal_show_modal'),
            width: '100%',
            allowClear: true
        });

        const fechaMinima = '2026-10-01';
        const hoy = new Date();
        const fechaMaxima = [
            hoy.getFullYear(),
            String(hoy.getMonth() + 1).padStart(2, '0'),
            String(hoy.getDate()).padStart(2, '0')
        ].join('-');

        const form = $('#form_add_interrupcion');
        const fechaInicio = form.find('[name="fecha_inicio"]');
        const fechaFin = form.find('[name="fecha_fin"]');

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
                $('#estado_interrupcion').text('ABIERTO');
                $('#estado').val('ABIERTO');
                $('#campos_fin').hide();
            } else {
                $('#estado_interrupcion').text($('#estado_final option:selected').text());
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
                $('#estado_interrupcion').text($(this).find('option:selected').text());
            }
        });

        toggleCamposFin();

        window.btnStoreInterrupcion = async function() {
            const inicio = fechaInicio.val();
            const fin = fechaFin.val();
            const estado = $('#estado').val();
            const boton = $('#btnEnviarForm');

            if (!inicio || inicio < fechaMinima || inicio > fechaMaxima) {
                alertaFecha('Solo se permiten interrupciones desde el 01/10/2026 hasta la fecha actual.');
                return;
            }

            if (estado === 'CERRADO') {
                if (!fin || !form.find('[name="hora_fin"]').val()) {
                    alertaFecha('Debe ingresar la fecha y hora de fin.');
                    return;
                }

                if (fin < inicio || fin > fechaMaxima) {
                    alertaFecha('La fecha de fin no puede ser anterior a la fecha de inicio ni posterior a hoy.');
                    return;
                }

                const horaInicio = form.find('[name="hora_inicio"]').val();
                const horaFin = form.find('[name="hora_fin"]').val();

                if (fin === inicio && horaInicio && horaFin && horaFin <= horaInicio) {
                    alertaFecha('La hora de fin debe ser posterior a la hora de inicio.');
                    return;
                }
            }

            if (!form.find('[name="hora_inicio"]').val()) {
                Swal.fire('Datos incompletos', 'Ingrese la hora de inicio.', 'warning');
                return;
            }

            if (typeof validarFechasHoras === 'function') {
                const valido = await validarFechasHoras(form, estado);
                if (!valido) return;
            }

            const formData = new FormData(form[0]);

            $.ajax({
                type: 'POST',
                url: "{{ route('interrupcion.store') }}",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    boton.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> ESPERE');
                },
                success: function(data) {
                    if (data.status == 201) {
                        $('#modal_show_modal').modal('hide');
                        cargarTablaInterrupciones();
                        Swal.fire('Registro exitoso', data.message || 'Interrupción registrada correctamente.', 'success');
                    } else {
                        Swal.fire('Atención', data.message || 'No se pudo guardar la interrupción.', 'warning');
                    }
                },
                error: function(xhr) {
                    const respuesta = xhr.responseJSON || {};
                    let mensaje = 'No se pudo registrar la interrupción.';

                    if (typeof respuesta.message === 'string') {
                        mensaje = respuesta.message;
                    } else if (respuesta.message && typeof respuesta.message === 'object') {
                        mensaje = Object.values(respuesta.message).flat().join('\n');
                    } else if (respuesta.errors) {
                        mensaje = Object.values(respuesta.errors).flat().join('\n');
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: xhr.status === 403 ? 'Acceso restringido' : 'No se pudo guardar',
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