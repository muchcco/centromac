<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Añadir Asistencia</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form id="formAsistenciatest" class="form">
                @csrf
                <div class="form-group d-none">
                    <h5>Correlativo</h5>
                    <input type="number" class="form-control" name="correlativo" id="correlativo" readonly>
                </div>

                <div class="form-group">
                    <h5>DNI</h5>
                    <input type="text" class="form-control" name="DNI" id="DNI" required maxlength="8" 
                           oninput="this.value = this.value.replace(/[^0-9]/g, '')" placeholder="Ingrese DNI (8 dígitos)">
                </div>

                <div class="form-group">
                    <h5>Nombre</h5>
                    <input type="text" class="form-control" id="nombre_completo" readonly>
                </div>

                <div class="form-group">
                    <h5>Fecha</h5>
                    <input type="date" class="form-control" name="fecha" id="fecha" required>
                </div>

                <div class="form-group">
                    <h5>Hora 1</h5>
                    <input type="time" class="form-control" name="hora1" id="hora1">
                </div>

                <div class="form-group">
                    <h5>Hora 2</h5>
                    <input type="time" class="form-control" name="hora2" id="hora2">
                </div>

                <div class="form-group">
                    <h5>Hora 3</h5>
                    <input type="time" class="form-control" name="hora3" id="hora3">
                </div>

                <div class="form-group">
                    <h5>Hora 4</h5>
                    <input type="time" class="form-control" name="hora4" id="hora4">
                </div>

                <input type="hidden" name="id" id="id" value="{{ auth()->user()->idcentro_mac }}">
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarAsistenciatest" onclick="storeAsistenciatest()">Guardar</button>
        </div>
    </div>
</div>

<script>
    // Al ingresar el DNI y completar los 8 dígitos, buscar el nombre
    $('#DNI').on('input', function() {
        const dni = $(this).val();

        if (dni.length === 8) {
            // Usar la función storeAsistenciatest para buscar el nombre y mostrarlo
            const formData = new FormData();
            formData.append('DNI', dni);
            formData.append('_token', '{{ csrf_token() }}');

            fetch("{{ route('asistencia.store_agregar_asistencia') }}", {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    $('#nombre_completo').val(data.nombreCompleto);
                } else {
                    $('#nombre_completo').val('No encontrado');
                }
            })
            .catch(error => console.error('Error:', error));
        } else {
            $('#nombre_completo').val('');
        }
    });
</script>
