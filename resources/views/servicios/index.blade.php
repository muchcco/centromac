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

@endsection

@section('main')

<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row">
                <div class="col">
                    <h4 class="page-title">Servicos por Entidades</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('inicio') }}"><i data-feather="home" class="align-self-center" style="height: 70%; display: block;"></i></a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);" style="color: #7081b9;">Servicios</a></li>
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
                <h4 class="card-title text-white">SERVICOS POR ENTIDAD DEL CENTRO MAC -  
                    @php
                        $user = App\Models\User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->first();

                        echo $user->NOMBRE_MAC;
                    @endphp
                </h4>
            </div><!--end card-header-->
            <div class="card-body bootstrap-select-1">
                
                <div class="row">
                    <div class="col-12 mb-3">
                        <button class="btn btn-success" data-toggle="modal" data-target="#large-Modal" onclick="btnAddServicio()"><i class="fa fa-plus" aria-hidden="true"></i>
                            Agregar Servicio</button>
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

function tabla_seccion(fecha = '', entidad = '', estado = '' ) {
    $.ajax({
        type: 'GET',
        url: "{{ route('servicios.tablas.tb_index') }}", // Ruta que devuelve la vista en HTML
        data: {fecha: fecha, entidad: entidad, estado: estado},
        beforeSend: function () {
            document.getElementById("table_data").innerHTML = '<i class="fa fa-spinner fa-spin"></i> ESPERE LA TABLA ESTA CARGANDO... ';
        },
        success: function(data) {
            $('#table_data').html(data); // Inserta la vista en un contenedor en tu página
        }
    });
}

function btnAddServicio() {
    $.ajax({
        type:'post',
        url: "{{ route('servicios.modals.md_add_servicios') }}",
        dataType: "json",
        data:{"_token": "{{ csrf_token() }}"},
        success:function(data){
            $("#modal_show_modal").html(data.html);
            $("#modal_show_modal").modal('show');
        }
    });
}

var isMayus = (e) => {
    e.value = e.value.toUpperCase();
}

function btnStoreServicio(){

    if ($('#entidad').val() == null || $('#entidad').val() == '') {
        $('#entidad').addClass("hasError");
    } else {
        $('#entidad').removeClass("hasError");
    }
    if ($('#nombre_servicio').val() == null || $('#nombre_servicio').val() == '') {
        $('#nombre_servicio').addClass("hasError");
    } else {
        $('#nombre_servicio').removeClass("hasError");
    }
    if ($('input[name="tramite"]').val() == null || $('input[name="tramite"]').val() == '' || $('input[name="tramite"]').val() == "undefined") {
        $('input[name="tramite"]').addClass("hasError");
    } else {
        $('input[name="tramite"]').removeClass("hasError");
    }
    if ($('#orientacion').val() == null || $('#orientacion').val() == '') {
        $('#orientacion').addClass("hasError");
    } else {
        $('#orientacion').removeClass("hasError");
    }
    if ($('#costo_serv').val() == null || $('#costo_serv').val() == '') {
        $('#costo_serv').addClass("hasError");
    } else {
        $('#costo_serv').removeClass("hasError");
    }
    if ($('#req_cita').val() == null || $('#req_cita').val() == '') {
        $('#req_cita').addClass("hasError");
    } else {
        $('#req_cita').removeClass("hasError");
    }
    if ($('#requisito_servicio').val() == null || $('#requisito_servicio').val() == '') {
        $('#requisito_servicio').addClass("hasError");
    } else {
        $('#requisito_servicio').removeClass("hasError");
    }

    var formData = new FormData();
    formData.append("entidad", $("#entidad").val());
    formData.append("nombre_servicio", $("#nombre_servicio").val());
    formData.append("req_cita", $("#req_cita").val());
    formData.append("costo_serv", $("#costo_serv").val());
    formData.append("requisito_servicio", $("#requisito_servicio").val());
    var selectedValueTramite = $('input[name="tramite"]:checked').val();
    formData.append("tramite", selectedValueTramite);
    var selectedValueOrientacion = $('input[name="orientacion"]:checked').val();
    formData.append("orientacion", selectedValueOrientacion);
    formData.append("_token", $("input[name=_token]").val());

    $.ajax({
        type:'post',
        url: "{{ route('servicios.store_servicio') }}",
        dataType: "json",
        data:formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
            document.getElementById("btnEnviarForm").innerHTML = '<i class="fa fa-spinner fa-spin"></i> ESPERE';
            document.getElementById("btnEnviarForm").disabled = true;
        },
        success:function(data){

            if(data.status == '201'){
                
                document.getElementById("btnEnviarForm").innerHTML = 'Guardar';
                document.getElementById("btnEnviarForm").disabled = false;
                document.getElementById('alerta').innerHTML = `<div class="alert custom-alert custom-alert-warning icon-custom-alert shadow-sm fade show d-flex justify-content-between" role="alert"><div class="media">
                                                                    <i class="la la-exclamation-triangle alert-icon text-warning align-self-center font-30 me-3"></i>
                                                                    <div class="media-body align-self-center">
                                                                        <h5 class="mb-1 fw-bold mt-0">Importante</h5>
                                                                        <span>`+ data.message.replace(/\n/g, "<br>") +`.</span>
                                                                    </div>
                                                                </div></div>`;
            }else{
                $("#modal_show_modal").modal('hide');
                tabla_seccion();
                Swal.fire({
                    icon: "success",
                    text: "El servicio se agregó con Exito!",
                    confirmButtonText: "Aceptar"
                })
            }

            
            
        },
        error: function(){
            document.getElementById("btnEnviarForm").innerHTML = 'Guardar';
            document.getElementById("btnEnviarForm").disabled = false;
        }
    });
}

function btnEditarServicio(idservicios){

    $.ajax({
        type:'post',
        url: "{{ route('servicios.modals.md_edit_servicios') }}",
        dataType: "json",
        data:{"_token": "{{ csrf_token() }}",  idservicios : idservicios},
        success:function(data){
            $("#modal_show_modal").html(data.html);
            $("#modal_show_modal").modal('show');
        }
    });

}

function btnEditServicio(ident_serv, idservicios){

    var formData = new FormData();
    formData.append("ident_serv", ident_serv);
    formData.append("idservicios", idservicios);
    formData.append("entidad", $("#entidad").val());
    formData.append("nombre_servicio", $("#nombre_servicio").val());
    formData.append("req_cita", $("#req_cita").val());
    formData.append("costo_serv", $("#costo_serv").val());
    formData.append("requisito_servicio", $("#requisito_servicio").val());
    var selectedValueTramite = $('input[name="tramite"]:checked').val();
    formData.append("tramite", selectedValueTramite);
    var selectedValueOrientacion = $('input[name="orientacion"]:checked').val();
    formData.append("orientacion", selectedValueOrientacion);
    formData.append("_token", $("input[name=_token]").val());

    $.ajax({
        type:'post',
        url: "{{ route('servicios.update_servicio') }}",
        dataType: "json",
        data:formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
            document.getElementById("btnEnviarForm").innerHTML = '<i class="fa fa-spinner fa-spin"></i> ESPERE';
            document.getElementById("btnEnviarForm").disabled = true;
        },
        success:function(data){
            $("#modal_show_modal").modal('hide');
                tabla_seccion();
                Swal.fire({
                    icon: "info",
                    text: "El servicio se actualizo con Exito!",
                    confirmButtonText: "Aceptar"
                })            
        },
        error: function(){
            document.getElementById("btnEnviarForm").innerHTML = 'Guardar';
            document.getElementById("btnEnviarForm").disabled = false;
        }
    });

}

function btnElimnarServicio(ident_serv, idservicios){  

    swal.fire({
        title: "Seguro que desea eliminar el servicio?",
        text: "El servicio será eliminado totalmente de sus registros",
        icon: "error",
        showCancelButton: !0,
        confirmButtonText: "Aceptar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: "{{ route('servicios.delete_servicio') }}",
                type: 'post',
                data: {"_token": "{{ csrf_token() }}", ident_serv: ident_serv , idservicios : idservicios},
                success: function(response){
                    console.log(response);

                    tabla_seccion(); 

                    Swal.fire({
                        icon: "success",
                        text: "El servicio fue elimnado con Exito!",
                        confirmButtonText: "Aceptar"
                    })

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