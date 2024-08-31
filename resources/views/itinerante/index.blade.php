@extends('layouts.layout')

@section('style')
<link href="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('nuevo/plugins/datatables/buttons.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('main')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row">
                <div class="col">
                    <h4 class="page-title">Gestión de Itinerantes</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('inicio') }}">Inicio</a></li>
                        <li class="breadcrumb-item active">Itinerantes</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header" style="background-color:#132842">
                <h4 class="card-title text-white">Lista de Itinerantes</h4>
            </div>

            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-12">
                        <button class="btn btn-success" data-toggle="modal" data-target="#large-Modal"
                            onclick="btnAddItinerante()"><i class="fa fa-plus" aria-hidden="true"></i> Agregar
                            Itinerante</button>
                    </div>
                </div>
                <div class="table-responsive" id="table_data">
                    <!-- Aquí se cargará la tabla desde la función `tb_index` -->
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal para agregar o editar itinerante --}}
<div class="modal fade" id="modal_show_modal" tabindex="-1" role="dialog"></div>
@endsection

@section('script')
<script src="{{ asset('nuevo/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('nuevo/plugins/datatables/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.js') }}"></script>
<script>
    $(document).ready(function() {
        tabla_seccion();
    });

    function tabla_seccion() {
        $.ajax({
            type: 'GET',
            url: "{{ route('itinerante.tablas.tb_index') }}",
            beforeSend: function() {
                $('#table_data').html('<i class="fa fa-spinner fa-spin"></i> Cargando...');
            },
            success: function(data) {
                $('#table_data').html(data);
            },
            error: function() {
                $('#table_data').html('Error al cargar los datos.');
            }
        });
    }

    function btnAddItinerante() {
        $.ajax({
            type: 'post',
            url: "{{ route('itinerante.modals.md_add_itinerante') }}", // Asegúrate de que esta ruta está correctamente configurada en tus rutas de Laravel
            dataType: "json",
            data: {
                "_token": "{{ csrf_token() }}" // Incluir CSRF token
            },
            success: function(data) {
                $("#modal_show_modal").html(data.html); // Insertar el contenido del modal en el div modal
                $("#modal_show_modal").modal('show'); // Mostrar el modal
            },
            error: function(xhr, status, error) {
                console.error("Error: " + status + " " + error); // Manejo de errores
            }
        });
    }

    $(document).ready(function() {
        $('#table_itinerantes').DataTable({
            "responsive": true,
            "bLengthChange": true,
            "autoWidth": false,
            "searching": true,
            "ordering": true,
            "info": true,
            "language": {
                "url": "{{ asset('js/Spanish.json') }}"
            },
        });
    });

    function btnEditItinerante(idItinerante) {
        $.ajax({
            type: 'POST',
            url: "{{ route('itinerante.modals.md_edit_itinerante') }}", // Asegúrate de que esta ruta esté correctamente configurada en tus rutas de Laravel
            dataType: "json",
            data: {
                "_token": "{{ csrf_token() }}", // Incluye el token CSRF para la protección contra ataques CSRF
                "id_itinerante": idItinerante // Envía el ID del itinerante que quieres editar
            },
            success: function(data) {
                $("#modal_show_modal").html(data.html); // Carga la vista del modal con el contenido recibido
                $("#modal_show_modal").modal('show'); // Muestra el modal
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: "error",
                    text: "No se pudo cargar la información del itinerante",
                    confirmButtonText: "Aceptar"
                });
            }
        });
    }



    function cargarItinerantes() {
        $.ajax({
            url: "{{ route('itinerante.tablas.tb_index') }}", // Asegúrate de que la ruta está bien definida en tus rutas de Laravel
            type: 'GET',
            success: function(data) {
                $('#table_data').html(data); // Asume que tienes un div con id='table_data' donde cargas la tabla
            },
            error: function() {
                $('#table_data').html('Error al cargar los itinerantes.');
            }
        });
    }

    function btnUpdateItinerante(id) {
        var formData = new FormData();
        formData.append("idcentro_mac", $("#idcentro_mac").val()); // Recoge el ID del centro MAC del campo de entrada
        formData.append("num_doc", $("#num_doc").val()); // Recoge el número de documento del campo de entrada
        formData.append("idmodulo", $("#idmodulo").val()); // Recoge el ID del módulo del campo de entrada
        formData.append("fechainicio", $("#fechainicio").val()); // Recoge la fecha de inicio del campo de entrada
        formData.append("fechafin", $("#fechafin").val()); // Recoge la fecha de fin del campo de entrada
        formData.append("id_itinerante", id); // Asegúrate de enviar el id_itinerante correctamente
        formData.append("_token", $("input[name=_token]").val());

        $.ajax({
            type: 'POST',
            url: "{{ route('itinerante.update_itinerante') }}", // Asegúrate de que esta es la ruta correcta y que está definida en tus rutas de Laravel
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                document.getElementById("btnEnviarForm").innerHTML =
                    '<i class="fa fa-spinner fa-spin"></i> Espere';
                document.getElementById("btnEnviarForm").disabled = true;
            },
            success: function(response) {
                $("#modal_show_modal").modal('hide'); // Oculta el modal una vez la actualización es exitosa
                cargarItinerantes(); // Llama a la función para recargar la tabla de itinerantes
                Swal.fire({
                    icon: 'success',
                    title: 'Itinerante Actualizado',
                    text: 'El itinerante ha sido actualizado correctamente.',
                    confirmButtonText: 'Aceptar'
                });
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo actualizar el itinerante. ' + xhr.responseJSON.message,
                    confirmButtonText: 'Aceptar'
                });
            }
        });
    }

    function btnDeleteItinerante(idItinerante) {
        Swal.fire({
            title: "¿Seguro que desea eliminar el itinerante?",
            text: "El itinerante será eliminado totalmente de sus registros",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: "Aceptar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('itinerante.delete_itinerante') }}",
                    type: 'post',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "id": idItinerante
                    },
                    success: function(response) {
                        cargarItinerantes(); // Asegúrate de que esta función actualiza correctamente la tabla
                        Swal.fire({
                            icon: "success",
                            title: 'Eliminado!',
                            text: "El itinerante ha sido eliminado con éxito.",
                            confirmButtonText: "Aceptar"
                        });
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: "error",
                            title: 'Error',
                            text: "No se pudo eliminar el itinerante.",
                            confirmButtonText: "Aceptar"
                        });
                    }
                });
            }
        });
    }
</script>
@endsection