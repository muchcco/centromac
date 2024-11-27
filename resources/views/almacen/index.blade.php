@extends('layouts.layout')

@section('style')

<link rel="stylesheet" href="{{ asset('Vendor/toastr/toastr.min.css') }}">
<!-- Plugins css -->
<link href="{{ asset('nuevo/plugins/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('nuevo/plugins/huebee/huebee.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('nuevo/plugins/timepicker/bootstrap-material-datetimepicker.css') }}" rel="stylesheet">
<link href="{{ asset('nuevo/plugins/bootstrap-touchspin/css/jquery.bootstrap-touchspin.min.css') }}" rel="stylesheet" />
<!-- DataTables -->
<link href="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{ asset('nuevo/plugins/datatables/buttons.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
<!-- Responsive datatable examples -->
<link href="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.css')}}" rel="stylesheet" type="text/css" /> 

<style>
    .padre_dat {
        background-color: #f0f8ff  !important; /* Azul claro */
        font-weight: 900 !important;
        color: #000000 !important;
    }

    .hasError{
        border: 1px solid #f00 !important;
    }

</style>
@endsection

@section('main')

<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row">
                <div class="col">
                    <h4 class="page-title">Almacen</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('inicio') }}"><i data-feather="home" class="align-self-center" style="height: 70%; display: block;"></i></a></li>
                    </ol>
                </div><!--end col--> 
            </div><!--end row-->                                                              
        </div><!--end page-title-box-->
    </div><!--end col-->
</div><!--end row-->


<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header" style="background-color:#132842">
                <h4 class="card-title text-white">ALMACEN DEL CENTRO MAC -  
                    @php
                        $us_id = auth()->user()->idcentro_mac;
                        $user = App\Models\User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

                        echo $user->NOMBRE_MAC;
                    @endphp
                </h4>
            </div><!--end card-header-->
            <div class="card-body bootstrap-select-1">
                <div class="row">
                    <div class="col-12">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#large-Modal" onclick="btnAddItem()"><i class="fa fa-plus" aria-hidden="true"></i>
                            Nuevo Registro</button>
                        <button class="btn btn-outline-info" data-toggle="modal" data-target="#large-Modal" onclick="btnAddExcel()"><i class="fa fa-database" aria-hidden="true"></i>
                                Importar Data</button>
                        <button class="btn btn-outline-danger" data-toggle="modal" data-target="#large-Modal" onclick="btnDeleteCompleto()"><i class="fa fa-database" aria-hidden="true"></i>
                                    Eliminar Data Completa</button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <div class="table-responsive" id="table_data">
            
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>



{{-- Ver Modales --}}
<div class="modal fade" id="modal_show_modal" tabindex="-1" role="dialog" ></div>
@endsection

@section('script')

<script src="{{ asset('Script/js/sweet-alert.min.js') }}"></script>
<script src="{{ asset('Vendor/toastr/toastr.min.js') }}"></script>
<script src="{{ asset('//cdn.jsdelivr.net/npm/sweetalert2@11') }}"></script>

<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>


<!-- Plugins js -->
<script src="{{ asset('nuevo/plugins/select2/select2.min.js') }}"></script>
{{-- <script src="{{ asset('nuevo/plugins/huebee/huebee.pkgd.min.js') }}"></script> --}}
<script src="{{ asset('nuevo/plugins/timepicker/bootstrap-material-datetimepicker.js') }}"></script>
<script src="{{ asset('nuevo/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js') }}"></script>
<script src="{{ asset('nuevo/plugins/bootstrap-touchspin/js/jquery.bootstrap-touchspin.min.js') }}"></script>
{{-- <script src="{{ asset('nuevo/assets/pages/jquery.forms-advanced.js') }}"></script> --}}
<!-- Required datatable js -->
<script src="{{asset('nuevo/plugins/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.js')}}"></script>
<!-- Buttons examples -->
<script src="{{asset('nuevo/plugins/datatables/dataTables.buttons.min.js')}}"></script>
<script src="{{asset('nuevo/plugins/datatables/buttons.bootstrap5.min.js')}}"></script>
<script src="{{asset('nuevo/plugins/datatables/jszip.min.js')}}"></script>
<script src="{{asset('nuevo/plugins/datatables/pdfmake.min.js')}}"></script>
<script src="{{asset('nuevo/plugins/datatables/vfs_fonts.js')}}"></script>
<script src="{{asset('nuevo/plugins/datatables/buttons.html5.min.js')}}"></script>
<script src="{{asset('nuevo/plugins/datatables/buttons.print.min.js')}}"></script>
<script src="{{asset('nuevo/plugins/datatables/buttons.colVis.min.js')}}"></script>
<!-- Responsive examples -->
<script src="{{asset('nuevo/plugins/datatables/dataTables.responsive.min.js')}}"></script>
<script src="{{asset('nuevo/plugins/datatables/responsive.bootstrap4.min.js')}}"></script>
<script src="{{asset('nuevo/assets/pages/jquery.datatable.init.js')}}"></script>

<script>
$(document).ready(function() {
    tabla_seccion();
});

function tabla_seccion() {
    $.ajax({
        type: 'GET',
        url: "{{ route('almacen.tablas.tb_index') }}", // Ruta que devuelve la vista en HTML
        data: {},
        beforeSend: function () {
            document.getElementById("table_data").innerHTML = '<i class="fa fa-spinner fa-spin"></i> ESPERE LA TABLA ESTA CARGANDO... ';
        },
        success: function(data) {
            $('#table_data').html(data); // Inserta la vista en un contenedor en tu página
        }
    });
}

function btnAddItem() {
    $.ajax({
        type:'post',
        url: "{{ route('almacen.modals.md_add_item') }}",
        dataType: "json",
        data:{"_token": "{{ csrf_token() }}"},
        success:function(data){
            $("#modal_show_modal").html(data.html);
            $("#modal_show_modal").modal('show');
        }
    });
}

function btnEditarItem(id) {
    $.ajax({
        type: 'post',
        url: "{{ route('almacen.modals.md_edit_item', ':id') }}".replace(':id', id), // Reemplaza el placeholder con el ID
        dataType: "json",
        data: { "_token": "{{ csrf_token() }}", id: id },
        success: function(data) {
            $("#modal_show_modal").html(data.html);
            $("#modal_show_modal").modal('show');
        },
        error: function(xhr) {
            console.error("Error en la solicitud:", xhr.responseText);
            Toastify({
                text: "Error al cargar el modal",
                backgroundColor: "#dc3545",
            }).showToast();
        }
    });
}



function btnAddExcel ()  {

    $.ajax({
        type:'post',
        url: "{{ route('almacen.modals.md_add_datos') }}",
        dataType: "json",
        data:{"_token": "{{ csrf_token() }}"},
        success:function(data){
            $("#modal_show_modal").html(data.html);
            $("#modal_show_modal").modal('show');
        }
    });
}


function btnStoreExcel () {
    var file_data = $("#excel_file").prop("files")[0];    
    var formData = new FormData();

    formData.append("excel_file", file_data);
    formData.append("_token", $("input[name=_token]").val());

    $.ajax({
        type:'post',
        url: "{{ route('almacen.store_datos') }}",
        dataType: "json",
        data:formData,
        processData: false,
        contentType: false,
        success:function(data){        
            $("#modal_show_modal").modal('hide');
            tabla_seccion();
            Toastify({
                text: "Se agregó exitosamente los registros",
                className: "info",
                gravity: "bottom",
                style: {
                    background: "#47B257",
                }
            }).showToast();
        }
    });

}

function btnStoreItem(){
    var formData = new FormData();
    formData.append("idcategoria", $("#idcategoria").val());
    formData.append("cod_interno_pcm", $("#cod_interno_pcm").val());
    formData.append("cod_sbn", $("#cod_sbn").val());
    formData.append("cod_pronsace", $("#cod_pronsace").val());
    formData.append("descripcion", $("#descripcion").val());
    formData.append("idmarca", $("#idmarca").val());
    formData.append("idmodelo", $("#idmodelo").val());
    formData.append("serie", $("#serie").val());
    formData.append("oc", $("#oc").val());
    formData.append("fecha_oc", $("#fecha_oc").val());
    formData.append("proveedor", $("#proveedor").val());
    formData.append("ubicacion", $("#ubicacion").val());
    formData.append("cantidad", $("#cantidad").val());
    formData.append("estado", $("#estado").val());
    formData.append("color", $("#color").val());
    formData.append("_token", $("input[name=_token]").val());

    $.ajax({
        type:'post',
        url: "{{ route('almacen.store_item') }}",
        dataType: "json",
        data:formData,
        processData: false,
        contentType: false,
        success:function(data){        
            $("#modal_show_modal").modal('hide');
            tabla_seccion();
            Toastify({
                text: "Se agregó exitosamente los registros",
                className: "info",
                gravity: "bottom",
                style: {
                    background: "#47B257",
                }
            }).showToast();
        }
    });
}

function btnUpdateItem(id) {
    let formData = new FormData();
    formData.append("idcategoria", $("#idcategoria").val());
    formData.append("cod_interno_pcm", $("#cod_interno_pcm").val());
    formData.append("cod_sbn", $("#cod_sbn").val());
    formData.append("cod_pronsace", $("#cod_pronsace").val());
    formData.append("descripcion", $("#descripcion").val());
    formData.append("idmodelo", $("#idmodelo").val());
    formData.append("serie", $("#serie").val());
    formData.append("oc", $("#oc").val());
    formData.append("fecha_oc", $("#fecha_oc").val());
    formData.append("proveedor", $("#proveedor").val());
    formData.append("ubicacion", $("#ubicacion").val());
    formData.append("cantidad", $("#cantidad").val());
    formData.append("estado", $("#estado").val());
    formData.append("color", $("#color").val());
    formData.append("_token", $("input[name=_token]").val());

    $.ajax({
        type: 'POST',
        url: "{{ route('almacen.update_item', ':id') }}".replace(':id', id), // Reemplaza el ID dinámicamente en la ruta
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            $("#modal_show_modal").modal('hide'); // Cerrar el modal
            tabla_seccion(); // Actualizar la tabla
            Toastify({
                text: response.message || "Item actualizado correctamente.",
                backgroundColor: "#28a745",
            }).showToast();
        },
        error: function (xhr) {
            console.error("Error al actualizar:", xhr.responseText);
            Toastify({
                text: "Error al actualizar el item.",
                backgroundColor: "#dc3545",
            }).showToast();
        }
    });
}


function btnElimnarItem(id){
    swal.fire({
        title: "Seguro que desea eliminar el item?",
        text: "El item será eliminado totalmente con su perfil asignado",
        icon: "error",
        showCancelButton: !0,
        confirmButtonText: "Aceptar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: "{{ route('almacen.delete_item') }}",
                type: 'post',
                data: {"_token": "{{ csrf_token() }}", id: id},
                success: function(response){
                    console.log(response);

                    tabla_seccion(); 

                    Toastify({
                        text: "Se eliminó el item",
                        className: "danger",
                        style: {
                            background: "#DF1818",
                        }
                    }).showToast();

                },
                error: function(error){
                    console.log('Error '+error);
                }
            });
        }

    })
}

function btnDeleteCompleto() {

    swal.fire({
        title: "Seguro que desea eliminar todos los bienes?",
        text: "Se eliminara todos los bienes asignados a su centro mac",
        icon: "error",
        showCancelButton: !0,
        confirmButtonText: "Aceptar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: "{{ route('almacen.delete_masivo') }}",
                type: 'post',
                data: {"_token": "{{ csrf_token() }}"},
                success: function(response){
                    console.log(response);

                    tabla_seccion(); 

                    Toastify({
                        text: "Se eliminó la data",
                        className: "danger",
                        style: {
                            background: "#DF1818",
                        }
                    }).showToast();

                },
                error: function(error){
                    console.log('Error '+error);
                }
            });
        }

    })

}

</script>

@endsection