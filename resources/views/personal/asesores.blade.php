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
<link href="{{ asset('css/toastr.min.css')}}" rel="stylesheet" type="text/css" /> 



@endsection

@section('main')

<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row">
                <div class="col">
                    <h4 class="page-title">Registro de personal</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('inicio') }}"><i data-feather="home" class="align-self-center" style="height: 70%; display: block;"></i></a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);" style="color: #7081b9;">Asesores</a></li>
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
                <h4 class="card-title text-white">PERSONAL ASESORES CENTRO MAC -  
                    @php
                        $us_id = auth()->user()->idcentro_mac;
                        $user = App\Models\User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

                        echo $user->NOMBRE_MAC;
                    @endphp
                </h4>
            </div><!--end card-header-->
            <div class="card-body bootstrap-select-1">
                <div class="row">
                    <div class="col-12 mb-3">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#large-Modal" onclick="btnAddAsesores()"><i class="fa fa-plus" aria-hidden="true"></i>
                            Agregar Personal</button>

                        <button class="btn btn-success" data-toggle="modal" data-target="#large-Modal" onclick="btnExportAsesores()"><i class="fa fa-file-excel"></i>
                                Exportar</button>
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
    
<script src="{{asset('js/toastr.min.js')}}"></script>
<script>
$(document).ready(function() {
    tabla_seccion();
});

function tabla_seccion() {
    $.ajax({
        type: 'GET',
        url: "{{ route('personal.tablas.tb_asesores') }}", // Ruta que devuelve la vista en HTML
        data: {},
        beforeSend: function () {
            document.getElementById("table_data").innerHTML = '<i class="fa fa-spinner fa-spin"></i> ESPERE LA TABLA ESTA CARGANDO... ';
        },
        success: function(data) {
            $('#table_data').html(data); // Inserta la vista en un contenedor en tu página
        }
    });
}

function btnAddAsesores ()  {

    $.ajax({
        type:'post',
        url: "{{ route('personal.modals.md_add_asesores') }}",
        dataType: "json",
        data:{"_token": "{{ csrf_token() }}"},
        success:function(data){
            $("#modal_show_modal").html(data.html);
            $("#modal_show_modal").modal('show');
        }
    });
}


var btnStoreAsesor = () => {

if ($('#nombre').val() == null || $('#nombre').val() == '') {
    $('#nombre').addClass("hasError");
} else {
    $('#nombre').removeClass("hasError");
}
if ($('#ap_pat').val() == null || $('#ap_pat').val() == '') {
    $('#ap_pat').addClass("hasError");
} else {
    $('#ap_pat').removeClass("hasError");
} 
if ($('#ap_mat').val() == null || $('#ap_mat').val() == '') {
    $('#ap_mat').addClass("hasError");
} else {
    $('#ap_mat').removeClass("hasError");
} 
if ($('#dni').val() == null || $('#dni').val() == '') {
    $('#dni').addClass("hasError");
} else {
    $('#dni').removeClass("hasError");
}
if ($('#entidad').val() == null || $('#entidad').val() == '') {
    $('#entidad').addClass("hasError");
} else {
    $('#entidad').removeClass("hasError");
} 
if ($('#modulos').val() == null || $('#modulos').val() == '') {
    $('#modulos').addClass("hasError");
} else {
    $('#modulos').removeClass("hasError");
} 
    
var formData = new FormData();
formData.append("nombre", $("#nombre").val());
formData.append("ap_pat", $("#ap_pat").val());
formData.append("ap_mat", $("#ap_mat").val());
formData.append("dni", $("#dni").val());
formData.append("entidad", $("#entidad").val());
formData.append("modulos", $("#modulos").val());
formData.append("_token", $("input[name=_token]").val());

$.ajax({
    type:'post',
    url: "{{ route('personal.store_asesores') }}",
    dataType: "json",
    data:formData,
    processData: false,
    contentType: false,
    beforeSend: function () {
        document.getElementById("btnEnviarForm").innerHTML = '<i class="fa fa-spinner fa-spin"></i> ESPERE';
        document.getElementById("btnEnviarForm").disabled = true;
    },
    success:function(data){ 
        console.log(data);
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
            Toastify({
                text: "Se guardo exitosamente el registro",
                className: "info",
                gravity: "bottom",
                style: {
                    background: "#47B257",
                }
            }).showToast();
        }

        
    },
    error: function(){
        document.getElementById("btnEnviarForm").innerHTML = '<i class="fa fa-spinner fa-spin"></i> ESPERE';
        document.getElementById("btnEnviarForm").disabled = true;
    }
});

}


var isNumber = (evt) =>{
  evt = (evt) ? evt : window.event;
  var charCode = (evt.which) ? evt.which : evt.keyCode;
  if (charCode > 31 && (charCode < 48 || charCode > 57)) {
      return false;
  }
  return true;
}

var isMayus = (e) => {
    e.value = e.value.toUpperCase();
}

function btnElimnarServicio (idpersonal){

    $.ajax({
        type:'post',
        url: "{{ route('personal.modals.md_baja_asesores') }}",
        dataType: "json",
        data:{"_token": "{{ csrf_token() }}", idpersonal : idpersonal},
        success:function(data){
            $("#modal_show_modal").html(data.html);
            $("#modal_show_modal").modal('show');
        }
    });

}

function btnBajaAsesor(idpersonal){

    var tipo = $("#baja").val();
    console.log(tipo)
    
    if (tipo === ""){ //tell you if the array is empty
        $('#baja').addClass("hasError");
    }
    else {
        var formData = new FormData();
        formData.append("baja", $("#baja").val());
        formData.append('idpersonal', idpersonal);
        formData.append("_token", $("input[name=_token]").val());

        $.ajax({
            type:'post',
            url: "{{ route('personal.baja_asesores') }}",
            dataType: "json",
            data:formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                document.getElementById("btnEnviarForm").innerHTML = '<i class="fa fa-spinner fa-spin"></i> Espere';
                document.getElementById("btnEnviarForm").disabled = true;
            },
            success:function(data){                
                $("#modal_show_modal").modal('hide');
                tabla_seccion();
                Toastify({
                    text: "Se altero el estado del personal",
                    className: "info",
                    style: {
                        background: "#206AC8",
                    }
                }).showToast();
            }
        });
    }

}


function btnExportAsesores(identidad){
    
    var fecha_inicio = document.getElementById('fecha_inicio').value;
    var fecha_fin = document.getElementById('fecha_fin').value;

    // Definimos la vista dende se enviara
    var link_up = "{{ route('asistencia.exportgroup_excel_pr') }}";

    // Crear la URL con las variables como parámetros de consulta
    var href = link_up +'?fecha_inicio=' + fecha_inicio + '&fecha_fin=' + fecha_fin + '&identidad=' + identidad;

    window.open(href);

    Swal.fire({
                icon: "success",
                text: "El archivo se descargo con Exito!",
                confirmButtonText: "Aceptar"
            })

}


</script>

@endsection