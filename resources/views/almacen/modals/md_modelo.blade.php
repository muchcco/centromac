<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Categorías</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="alert alert-info border-0" role="alert">
                <strong>Importante!</strong> Reemplace los valores ID donde correspondan en su archivo excel antes de importarlo.
            </div>
            <div class="row">
                <h4>Ingrese los datos de Marca y Modelo si no existen</h4>
            </div>

            <div class="row">
                <div class="form-group col-md-4">
                    <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}">
                    <input type="text" class="form-control" id="idmarca" placeholder="Nombre de la Marca">
                </div>
                <div class="form-group col-md-4">
                    <input type="text" class="form-control" id="idmodelo" placeholder="Nombre del Modelo">
                </div>
                <div class="form-group col-md-4">
                    <button class="btn btn-primary" id="btnEnviarForm" onclick="btnGuardar();">Guardar</button>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-lg-12">
                    <div class="table-responsive" id="table_data_models">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    // Autocompletar para el campo "idmarca"
    $('#idmarca').autocomplete({
        source: "{{ route('almacen.buscar-marca') }}", // Ruta para buscar marcas
        minLength: 2,
        select: function (event, ui) {
            $('#idmarca').val(ui.item.value);
        },
    });

    // Configuración de DataTables
    table();

    // Convierte los valores de los inputs a mayúsculas
    $('#idmarca, #idmodelo').on('input', function () {
        this.value = this.value.toUpperCase();
    });
});

function table(){
    $.ajax({
        type: 'GET',
        url: "{{ route('almacen.tablas.tb_modelos') }}", // Ruta que devuelve la vista en HTML
        data: {},
        beforeSend: function () {
            document.getElementById("table_data_models").innerHTML = '<i class="fa fa-spinner fa-spin"></i> ESPERE LA TABLA ESTA CARGANDO... ';
        },
        success: function(data) {
            $('#table_data_models').html(data); // Inserta la vista en un contenedor en tu página
        }
    });
}

// Función para guardar
function btnGuardar() {
    if (!$('#idmarca').val() || !$('#idmodelo').val()) {
        $('#idmarca, #idmodelo').addClass('is-invalid');
        return;
    }

    let formData = new FormData();
    formData.append('idmarca', $('#idmarca').val());
    formData.append('idmodelo', $('#idmodelo').val());
    formData.append('_token', $('#_token').val());

    $.ajax({
        type: 'POST',
        url: "{{ route('almacen.store_modelo') }}",
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
            $('#btnEnviarForm').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');
        },
        success: function (response) {
            $('#btnEnviarForm').prop('disabled', false).html('Guardar');

            // Insertar el nuevo registro directamente en la tabla
            table();
            $('#idmarca').val('');
            $('#idmodelo').val('');

            Toastify({
                text: response.message || "Datos guardados correctamente",
                backgroundColor: "#28a745",
            }).showToast();

            // Limpiar los campos
            $('#idmarca, #idmodelo').val('').removeClass('is-invalid');
        },
        error: function (xhr) {
            console.error(xhr.responseText);
            Toastify({
                text: "Error al guardar los datos",
                backgroundColor: "#dc3545",
            }).showToast();
        },
        complete: function () {
            $('#btnEnviarForm').prop('disabled', false).html('Guardar');
        }
    });
}

function eliminarModelo(id) {
    if (confirm("¿Estás seguro de que deseas eliminar este modelo?")) {
        $.ajax({
            type: 'DELETE',
            url: "{{ url('almacen/eliminar_modelo') }}/" + id,
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function (response) {
                // Mostrar mensaje de éxito
                table();
            },
            error: function (xhr) {
                // Manejar errores
                Toastify({
                    text: "Error al eliminar el modelo.",
                    backgroundColor: "#dc3545",
                }).showToast();
            }
        });
    }
    // table();
}


</script>
