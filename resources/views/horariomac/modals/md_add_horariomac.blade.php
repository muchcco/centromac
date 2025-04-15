    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Añadir nuevo horario MAC</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="alerta"></div>
                <h5>Agregar datos del horario</h5>
                <form class="form-horizontal">
                    <!-- CSRF Token -->
                    <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />

                    <!-- Campo para seleccionar el centro MAC -->
                    <div class="row mb-3">
                        <label class="col-3 col-form-label">Centro MAC</label>
                        <div class="col-9">
                            <select class="form-control" name="idcentromac" id="idcentro_mac">
                                <option value=""></option>
                                @foreach ($centrosMac as $centro)
                                    <option value="{{ $centro->IDCENTRO_MAC }}">{{ $centro->NOMBRE_MAC }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Campo para seleccionar el módulo -->
                    <div class="row mb-3">
                        <label class="col-3 col-form-label">Módulo</label>
                        <div class="col-9">
                            <select class="form-control" name="idmodulo" id="idmodulo">
                                <option value=""></option>
                                @foreach ($modulos as $modulo)
                                    <option value="{{ $modulo->IDMODULO }}">{{ $modulo->N_MODULO }} -
                                        {{ $modulo->ABREV_ENTIDAD  }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger" style="font-size: 0.8rem;">*Opcional</small>
                        </div>
                    </div>
                    <!-- Campo para hora ingreso -->
                    <div class="row mb-3">
                        <label class="col-3 col-form-label">Hora de Ingreso</label>
                        <div class="col-9">
                            <input type="time" class="form-control" name="horaingreso" id="horaingreso">
                        </div>
                    </div>

                    <!-- Campo para hora salida -->
                    <div class="row mb-3">
                        <label class="col-3 col-form-label">Hora de Salida</label>
                        <div class="col-9">
                            <input type="time" class="form-control" name="horasalida" id="horasalida">
                            <small class="text-danger" style="font-size: 0.8rem;">*Opcional</small>
                        </div>
                    </div>

                    <!-- Campo para fecha inicio -->
                    <div class="row mb-3">
                        <label class="col-3 col-form-label">Fecha de Inicio</label>
                        <div class="col-9">
                            <input type="date" class="form-control" name="fechainicio" id="fechainicio">
                        </div>
                    </div>

                    <!-- Campo para fecha fin -->
                    <div class="row mb-3">
                        <label class="col-3 col-form-label">Fecha de Fin</label>
                        <div class="col-9">
                            <input type="date" class="form-control" name="fechafin" id="fechafin">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-outline-success" id="btnEnviarForm"
                    onclick="btnStoreHorario()">Guardar</button>
            </div>
        </div>
    </div>
    <script>
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
        // Cuando el centro MAC cambie, obtenemos los módulos correspondientes
        $('#idcentro_mac').on('change', function() {
            var centroMacId = $(this).val();
            if (centroMacId) {
                $.ajax({
                    url: "{{ route('horariomac.getModulosByCentroMac', '') }}/" + centroMacId,
                    type: 'GET',
                    success: function(data) {
                        // Limpia los módulos actuales
                        $('#idmodulo').empty();
                        $('#idmodulo').append('<option value="">Seleccione un módulo</option>');

                        // Agrega los nuevos módulos
                        $.each(data, function(index, modulo) {
                            $('#idmodulo').append('<option value="' + modulo.IDMODULO + '">' +
                                modulo.N_MODULO + ' - ' + modulo.ABREV_ENTIDAD + '</option>');
                        });

                        // Re-inicializa select2 para que actualice el dropdown
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
                // Si no se selecciona un Centro MAC, vaciar el módulo
                $('#idmodulo').empty();
                $('#idmodulo').append('<option value="">Seleccione un módulo</option>');
                $('#idmodulo').select2();
            }
        });
    </script>
