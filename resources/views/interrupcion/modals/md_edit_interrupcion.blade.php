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

                <!-- Unidad de Organización -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Unidad de Organización</label>
                    <div class="col-9">
                        <select class="form-control select2" name="identidad_hijo" id="identidad_hijo">
                            <option value="">--Seleccione--</option>
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

                <!-- Atribuible a la Entidad -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Atribuible a la Entidad</label>
                    <div class="col-9">
                        <input type="hidden" name="atribuible_entidad" id="atribuible_entidad_valor"
                            value="{{ $interrupcion->atribuible_entidad }}">
                        <input type="checkbox" id="atribuible_e"
                            {{ $interrupcion->atribuible_entidad ? 'checked' : '' }}>
                        <span id="atribuible_entidad">SI</span>
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
        $('.select2').select2({
            dropdownParent: $('#modal_show_modal'),
            width: '100%',
            allowClear: true
        });

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

        function toggleAtribuible() {
            if ($('#atribuible_e').is(':checked')) {
                $('#atribuible_entidad').text('SI');
                $('#atribuible_entidad_valor').val(1);
            } else {
                $('#atribuible_entidad').text('NO');
                $('#atribuible_entidad_valor').val(0);
            }
        }

        function cargarEntidadesHijo(identidad, seleccionado = null) {
            if (!identidad) {
                $('#identidad_hijo').html('<option value="">--Seleccione--</option>');
                return;
            }
            $('#identidad_hijo').html('<option value="">Cargando...</option>');
            $.ajax({
                url: "{{ route('interrupcion.entidades_hijo') }}",
                type: "GET",
                data: {
                    identidad: identidad
                },
                success: function(data) {
                    let opciones = '<option value="">--Seleccione--</option>';
                    $.each(data, function(i, item) {
                        let selected = '';
                        if (seleccionado == item.identidad) {
                            selected = 'selected';
                        }
                        opciones += `
                            <option value="${item.identidad}" ${selected}>
                                ${item.abrev_entidad} - ${item.nombre_entidad}
                            </option>`;
                    });
                    $('#identidad_hijo')
                        .html(opciones)
                        .trigger('change');
                }
            });
        }

        $('#interrupcion_curso').change(toggleCamposFin);
        $('#estado_final').change(function() {
            if (!$('#interrupcion_curso').is(':checked')) {
                $('#estado').val($(this).val());
            }
        });

        $('#atribuible_e').change(toggleAtribuible);
        $('#identidad').change(function() {
            cargarEntidadesHijo($(this).val());
        });

        toggleCamposFin();
        toggleAtribuible();

        cargarEntidadesHijo(
            $('#identidad').val(),
            "{{ $interrupcion->identidad_hijo }}"
        );
    });
</script>
