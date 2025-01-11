<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Añadir nuevo personal</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="alerta"></div>
            <h5>Agregar datos</h5>
            <form class="form-horizontal">
                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />

                <!-- Campos Personales -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Número de Documento</label>
                    <div class="col-9">
                        <input type="text" class="form-control" name="dni" id="dni"
                            placeholder="Número de Documento" onkeypress="return isNumber(event)">
                        <span class="text-center text-danger" id="mensaje_error_dni"></span>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-3 col-form-label">Nombres</label>
                    <div class="col-9">
                        <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Nombres"
                            onkeyup="isMayus(this)">
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-3 col-form-label">Apellido Paterno</label>
                    <div class="col-9">
                        <input type="text" class="form-control" name="ap_pat" id="ap_pat"
                            placeholder="Apellido Paterno" onkeyup="isMayus(this)">
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-3 col-form-label">Apellido Materno</label>
                    <div class="col-9">
                        <input type="text" class="form-control" name="ap_mat" id="ap_mat"
                            placeholder="Apellido Materno" onkeyup="isMayus(this)">
                    </div>
                </div>

                <!-- Campo combinado de n_modulo y n_entidad con Select2 -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Módulo y Entidad</label>
                    <div class="col-9">
                        <select id="modulos_entidades" name="modulos_entidades" class="form-control select2">
                            <option disabled selected>-- Seleccione una opción --</option>
                            @forelse ($modulos_entidades as $item)
                                <option value="{{ $item->IDMODULO }}">{{ $item->nombre_completo }}</option>
                            @empty
                                <option value="">No hay datos disponibles</option>
                            @endforelse
                        </select>
                    </div>
                </div>
                <!-- Campos de Fecha -->
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha Inicio</label>
                    <div class="col-9">
                        <input type="date" class="form-control" name="fechainicio" id="fechainicio" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha Fin</label>
                    <div class="col-9">
                        <input type="date" class="form-control" name="fechafin" id="fechafin" required>
                    </div>
                </div>

            </form>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm"
                onclick="btnStoreAsesor()">Guardar</button>
        </div>
    </div>
</div>

<!-- Script para inicializar Select2 y autocompletar fechas -->
<script>
    $(document).ready(function() {

        // Inicializar Select2 con un control de modal
        $('#modulos_entidades').select2({
            dropdownParent: $('#modal_show_modal'),
            placeholder: "Seleccione un módulo",
            width: '100%',
            allowClear: true
        });

        // Llenar automáticamente las fechas al seleccionar un módulo
        $('#modulos_entidades').on('change', function() {
            var moduloId = $(this).val();
            if (moduloId) {
                $.ajax({
                    url: "{{ route('personalModulo.getFechasModulo', ':id') }}".replace(':id',
                        moduloId),
                    type: 'GET',
                    success: function(data) {
                        if (data.fechainicio && data.fechafin) {
                            $('#fechainicio').val(data.fechainicio);
                            $('#fechafin').val(data.fechafin);
                        } else {
                            alert('Las fechas no están disponibles para este módulo.');
                            $('#fechainicio, #fechafin').val('');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error al cargar las fechas: ' + textStatus + ' - ' +
                            errorThrown);
                        $('#fechainicio, #fechafin').val('');
                    }
                });
            } else {
                $('#fechainicio, #fechafin').val('');
            }
        });

        // Validación de DNI
        $('#dni').on('change', function() {
            var dni = $(this).val();
            $.ajax({
                type: 'POST',
                url: "{{ route('buscar_dni') }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    dni: dni
                },
                success: function(response) {
                    if (!(response.error)) {
                        $('#mensaje_error_dni').text('');
                        $('#nombre').val(response.nombres);
                        $('#ap_pat').val(response.apellidoPaterno);
                        $('#ap_mat').val(response.apellidoMaterno);
                    } else {
                        $('#nombre').val('');
                        $('#mensaje_error_dni').text(response.data.error);
                    }
                }
            });
        });
    });
</script>

<!-- Importación de estilos y scripts de Select2 -->
<link rel="stylesheet" href="{{ asset('nuevo/plugins/select2/select2.min.css') }}">
<script src="{{ asset('nuevo/plugins/select2/select2.min.js') }}"></script>
