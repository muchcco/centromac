<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Añadir nuevo Personal Módulo</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="alerta"></div>
            <h5>Agregar datos del Personal Módulo</h5>
            <form id="form_personal_modulo" class="form-horizontal">
                @csrf
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Número de Documento</label>
                    <div class="col-9">
                        <select class="form-control select2" name="num_doc" id="num_doc">
                            <option value="" disabled selected>Seleccione un personal</option>
                            @foreach ($personal as $p)
                                <option value="{{ $p->num_doc }}">{{ $p->num_doc }} - {{ $p->nombre_completo }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Módulo</label>
                    <div class="col-9">
                        <select class="form-control select2" name="idmodulo" id="idmodulo">
                            <option value="" disabled selected>Seleccione un módulo</option>
                            @foreach ($modulos as $modulo)
                                <option value="{{ $modulo->IDMODULO }}">{{ $modulo->N_MODULO }} -
                                    {{ $modulo->NOMBRE_ENTIDAD }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-3 col-form-label">Fecha de Inicio</label>
                    <div class="col-9">
                        <input type="date" class="form-control" name="fechainicio" id="fechainicio">
                    </div>
                </div>
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
                onclick="btnStorePersonalModulo()">Guardar</button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#num_doc').select2({
            dropdownParent: $('#modal_show_modal'),
            placeholder: "Seleccione un personal",
            width: '100%',
            allowClear: true
        });

        $('#idmodulo').select2({
            dropdownParent: $('#modal_show_modal'),
            placeholder: "Seleccione un módulo",
            width: '100%',
            allowClear: true
        });

        $('#idmodulo').on('change', function() {
            var moduloId = $(this).val();
            if (moduloId) {
                $.ajax({
                    url: "{{ route('personalModulo.getFechasModulo', ':id') }}".replace(':id', moduloId),
                    type: 'GET',
                    success: function(data) {
                        console.log(
                        data); // Esto te ayudará a ver lo que se devuelve desde el servidor
                        if (data.fechainicio && data.fechafin) {
                            $('#fechainicio').val(data.fechainicio);
                            $('#fechafin').val(data.fechafin);
                        } else {
                            alert('Las fechas no están disponibles para este módulo.');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error al cargar las fechas: ' + textStatus + ' - ' +
                            errorThrown);
                    }
                });
            } else {
                $('#fechainicio, #fechafin').val(''); // Limpia las fechas si no hay módulo seleccionado
            }
        });
        
    });
</script>
