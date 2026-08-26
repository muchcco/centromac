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
                        <input type="text" class="form-control" name="servicio_involucrado">
                    </div>
                </div>

                <!-- Atribuible a la Entidad -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Atribuible a la Entidad</label>
                    <div class="col-9">
                        <input type="hidden" name="atribuible_entidad" id="atribuible_entidad_valor" value="1">
                        <input type="checkbox" id="atribuible_e" checked>
                        <span id="atribuible_entidad">SI</span>
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

        function toggleCamposFin() {
            if ($('#interrupcion_curso').is(':checked')) {
                $('#atribuible_entidad').text('SI');
                $('#estado_interrupcion').text('ABIERTO');
                $('#estado').val('ABIERTO');
                $('#campos_fin').hide();
            } else {
                $('#estado_interrupcion').text($('#estado_final option:selected').text());
                $('#estado').val($('#estado_final').val());
                $('#campos_fin').show();
            }
        }

        $('#interrupcion_curso').change(toggleCamposFin);

        $('#estado_final').change(function() {
            if (!$('#interrupcion_curso').is(':checked')) {
                $('#estado').val($(this).val());
                $('#estado_interrupcion').text($(this).find("option:selected").text());
            }
        });

        function toggleAtribuible() {
            if ($('#atribuible_e').is(':checked')) {
                $('#atribuible_entidad').text('SI');
                $('#atribuible_entidad_valor').val(1);
            } else {
                $('#atribuible_entidad').text('NO');
                $('#atribuible_entidad_valor').val(0);
            }
        }
        $('#atribuible_e').change(toggleAtribuible);

        toggleCamposFin();
        toggleAtribuible();
    });

    $('#identidad').change(function () {
        let identidad = $(this).val();
        $('#identidad_hijo').html('<option>Cargando...</option>');
        $.ajax({
            url: "{{ route('interrupcion.entidades_hijo') }}",
            type: "GET",
            data: {
                identidad: identidad
            },
            success: function(data){
                // console.log(data);
                let opciones = '<option value="">--Seleccione--</option>';
                $.each(data, function(i,item){
                    opciones += `
                        <option value="${item.identidad}">
                            ${item.abrev_entidad} - ${item.nombre_entidad}
                        </option>`;
                });
                $('#identidad_hijo').html(opciones).trigger('change');
            }
        });
    });
</script>
