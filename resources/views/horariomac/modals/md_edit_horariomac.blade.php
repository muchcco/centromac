<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Editar horario MAC</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="alerta"></div>
            <h5>Editar datos del horario</h5>
            <form class="form-horizontal">
                <!-- CSRF Token -->
                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />

                <!-- Campo oculto para el ID del horario -->
                <input type="hidden" name="idhorario" id="idhorario" value="{{ $horario->idhorario }}" />

                <!-- Campo para seleccionar el centro MAC -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Centro MAC</label>
                    <div class="col-9">
                        <select class="form-control" name="idcentromac" id="idcentro_mac">
                            @foreach ($centrosMac as $centro)
                                <option value="{{ $centro->IDCENTRO_MAC }}"
                                    {{ $centro->IDCENTRO_MAC == $horario->idcentro_mac ? 'selected' : '' }}>
                                    {{ $centro->NOMBRE_MAC }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Campo para seleccionar el módulo -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Módulo</label>
                    <div class="col-9">
                        <select class="form-control" name="idmodulo" id="idmodulo">
                            <option value="">Opcional Seleccione módulo</option>
                            @foreach ($modulos as $modulo)
                                <option value="{{ $modulo->IDMODULO }}"
                                    {{ $modulo->IDMODULO == $horario->idmodulo ? 'selected' : '' }}>
                                    {{ $modulo->N_MODULO }} - {{ $modulo->ABREV_ENTIDAD }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-danger" style="font-size: 0.8rem;">*Opcional</small>
                    </div>
                </div>

                <!-- Campo para hora ingreso -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Hora de Ingreso</label>
                    <div class="col-9">
                        <input type="time" class="form-control" name="horaingreso" id="horaingreso"
                            value="{{ $horario->horaingreso }}">
                    </div>
                </div>

                <!-- Campo para hora salida -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Hora de Salida</label>
                    <div class="col-9">
                        <input type="time" class="form-control" name="horasalida" id="horasalida"
                            value="{{ $horario->horasalida }}">
                        <small class="text-danger" style="font-size: 0.8rem;">*Opcional</small>
                    </div>
                </div>

                <!-- Campo para fecha inicio -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha de Inicio</label>
                    <div class="col-9">
                        <input type="date" class="form-control" name="fechainicio" id="fechainicio"
                            value="{{ $horario->fechainicio }}">
                    </div>
                </div>

                <!-- Campo para fecha fin -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha de Fin</label>
                    <div class="col-9">
                        <input type="date" class="form-control" name="fechafin" id="fechafin"
                            value="{{ $horario->fechafin }}">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm"
                onclick="btnUpdateHorario()">Guardar cambios</button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Inicializar select2 para los campos de Centro MAC y Módulo
        $('#idcentro_mac').select2({
            dropdownParent: $('#modal_show_modal'),
            placeholder: "Seleccione un Centro MAC",
            width: '100%',
            allowClear: true
        });

        $('#idmodulo').select2({
            dropdownParent: $('#modal_show_modal'),
            placeholder: "Seleccione un módulo",
            width: '100%',
            allowClear: true
        });

        // Cuando el centro MAC cambie, obtener los módulos correspondientes
        $('#idcentro_mac').on('change', function() {
            var centroMacId = $(this).val();
            if (centroMacId) {
                $.ajax({
                    url: "{{ route('horariomac.getModulosByCentroMac', '') }}/" + centroMacId,
                    type: 'GET',
                    success: function(data) {
                        $('#idmodulo').empty();
                        $('#idmodulo').append(
                            '<option value="">Seleccione un módulo</option>');
                        $.each(data, function(index, modulo) {
                            $('#idmodulo').append('<option value="' + modulo
                                .IDMODULO + '">' +
                                modulo.N_MODULO + ' - ' + modulo.ABREV_ENTIDAD +
                                '</option>');
                        });
                        $('#idmodulo').select2({
                            dropdownParent: $('#modal_show_modal'),
                            placeholder: "Seleccione un módulo",
                            width: '100%',
                            allowClear: true
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log("Error al obtener los módulos: " + error);
                    }
                });
            } else {
                $('#idmodulo').empty();
                $('#idmodulo').append('<option value="">Seleccione un módulo</option>');
                $('#idmodulo').select2();
            }
        });

        // Llamar la función cuando se abre el modal para cargar los módulos por el centro MAC actual
        var idCentroMac = $('#idcentro_mac').val(); // Si ya tiene un valor en el campo de Centro MAC
        if (idCentroMac) {
            // Si ya hay un centro MAC seleccionado, actualizar los módulos correspondientes
            $.ajax({
                url: "{{ route('horariomac.getModulosByCentroMac', '') }}/" + idCentroMac,
                type: 'GET',
                success: function(data) {
                    $('#idmodulo').empty();
                    $('#idmodulo').append('<option value="">Seleccione un módulo</option>');
                    $.each(data, function(index, modulo) {
                        $('#idmodulo').append('<option value="' + modulo.IDMODULO + '" ' +
                            (modulo.IDMODULO == '{{ $horario->idmodulo }}' ?
                                'selected' : '') + '>' +
                            modulo.N_MODULO + ' - ' + modulo.ABREV_ENTIDAD + '</option>'
                            );
                    });
                    $('#idmodulo').select2({
                        dropdownParent: $('#modal_show_modal'),
                        placeholder: "Seleccione un módulo",
                        width: '100%',
                        allowClear: true
                    });
                },
                error: function(xhr, status, error) {
                    console.log("Error al obtener los módulos: " + error);
                }
            });
        }
    });
</script>
    