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
                    <h4 class="page-title">Formatos</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('inicio') }}"><i data-feather="home" class="align-self-center" style="height: 70%; display: block;"></i></a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);" style="color: #7081b9;">Libro de felicitaciones</a></li>
                    </ol>
                </div><!--end col--> 
            </div><!--end row--> 
        </div><!--end page-title-box--> 
    </div><!--end col-->
</div><!--end row-->

<section id="eval_cambio">
    <div class="row" id="table_evalua">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header" style="background-color:#132842">
                    <h4 class="card-title text-white">LIBRO DE FELICITACIONES DEL CENTRO MAC -  
                        @php
                            $us_id = auth()->user()->idcentro_mac;
                            $user = App\Models\User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();
    
                            echo $user->NOMBRE_MAC;
                        @endphp
                    </h4>
                </div><!--end card-header-->
                <div class="card-body">
                    
                    <div class="row mb-2">
                        <div class="col-12 ">
                            <button class="btn btn-primary" data-toggle="modal" data-target="#large-Modal" onclick="btnAddFelicitacion()"><i class="fa fa-plus" aria-hidden="true"></i>
                                Agregar Felicitación</button> 

                            <button type="button" class="btn btn-success" id="filtro" onclick="exec_data_excel()">
                                <i class="fa fa-file-excel-o" aria-hidden="true"></i>
                                Exportar
                            </button>
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
    <div class="row" style="display: none" id="reporte_eval">
        @include('formatos.evaluacion_motivacional.reporte')
    </div>
</section>

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
    $(document).ready(function() {
        $('.select2').select2();
    });
});

function tabla_seccion( ) {
    $.ajax({
        type: 'GET',
        url: "{{ route('formatos.f_felicitaciones.tablas.tb_index') }}", // Ruta que devuelve la vista en HTML
        data: {},
        beforeSend: function () {
            document.getElementById("table_data").innerHTML = '<i class="fa fa-spinner fa-spin"></i> ESPERE LA TABLA ESTA CARGANDO... ';
        },
        success: function(data) {
            $('#table_data').html(data); // Inserta la vista en un contenedor en tu página
        }
    });
}


function btnAddFelicitacion(){

    $.ajax({
        type:'post',
        url: "{{ route('formatos.f_felicitaciones.modals.md_add_felicitacion') }}",
        dataType: "json",
        data:{"_token": "{{ csrf_token() }}"},
        success:function(data){
            $("#modal_show_modal").html(data.html);
            $("#modal_show_modal").modal('show');
        }
    });

}

function btSave(){

    if ($('#nombre').val() == null || $('#nombre').val() == '') {
        $('#nombre').addClass("hasError");
    } else {
        $('#nombre').removeClass("hasError");
    }
    if ($('#ape_pat').val() == null || $('#ape_pat').val() == '') {
        $('#ape_pat').addClass("hasError");
    } else {
        $('#ape_pat').removeClass("hasError");
    } 
    if ($('#ape_mat').val() == null || $('#ape_mat').val() == '') {
        $('#ape_mat').addClass("hasError");
    } else {
        $('#ape_mat').removeClass("hasError");
    } 
    if ($('#num_doc').val() == null || $('#num_doc').val() == '') {
        $('#num_doc').addClass("hasError");
    } else {
        $('#num_doc').removeClass("hasError");
    }
    if ($('#correo').val() == null || $('#correo').val() == '') {
        $('#correo').addClass("hasError");
    } else {
        $('#correo').removeClass("hasError");
    } 
    if ($('#entidad').val() == null || $('#entidad').val() == '') {
        $('#entidad').addClass("hasError");
    } else {
        $('#entidad').removeClass("hasError");
    } 
    if ($('#entidad').val() == null || $('#entidad').val() == '') {
        $('#entidad').addClass("hasError");
    } else {
        $('#entidad').removeClass("hasError");
    } 
    if ($('#descripcion').val() == null || $('#descripcion').val() == '') {
        $('#descripcion').addClass("hasError");
    } else {
        $('#descripcion').removeClass("hasError");
    } 
    if ($('#correlativo_mac').val() == null || $('#correlativo_mac').val() == '') {
        $('#correlativo_mac').addClass("hasError");
    } else {
        $('#correlativo_mac').removeClass("hasError");
    } 

    var file_data = $("#file_doc").prop("files")[0];
    var formData = new FormData();
    
    formData.append("file_doc", file_data);
    formData.append("tipo_doc", $("#tipo_doc").val());
    formData.append("correlativo_mac", $("#correlativo_mac").val());
    formData.append("num_doc", $("#num_doc").val());
    formData.append("nombre", $("#nombre").val());
    formData.append("ape_pat", $("#ape_pat").val());
    formData.append("ape_mat", $("#ape_mat").val());
    formData.append("fecha", $("#fecha").val());
    formData.append("correo", $("#correo").val());
    formData.append("entidad", $("#entidad").val());
    formData.append("asesor", $("#asesor").val());
    formData.append("descripcion", $("#descripcion").val());
    formData.append("_token", $("input[name=_token]").val());

    $.ajax({
        type:'post',
        url: "{{ route('formatos.f_felicitaciones.store') }}",
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
                text: "Se agregó exitosamente el registro!",
                className: "info",
                gravity: "bottom",
                style: {
                    background: "#47B257",
                }
            }).showToast();
        }
    });


}

function btnEditarFelicitacion(idfelicitacion) {
    $.ajax({
        type:'post',
        url: "{{ route('formatos.f_felicitaciones.modals.md_edit_felicitacion') }}",
        dataType: "json",
        data:{"_token": "{{ csrf_token() }}", idfelicitacion : idfelicitacion},
        success:function(data){
            $("#modal_show_modal").html(data.html);
            $("#modal_show_modal").modal('show');
        }
    });
}

function btnEdit(idfelicitacion){

    var file_data = $("#file_doc").prop("files")[0];
    var formData = new FormData();
    
    formData.append("file_doc", file_data);
    formData.append("idfelicitacion", idfelicitacion);
    formData.append("fecha", $("#fecha").val());
    formData.append("correlativo_mac", $("#correlativo_mac").val());
    formData.append("correo", $("#correo").val());
    formData.append("entidad", $("#entidad").val());
    formData.append("asesor", $("#asesor").val());
    formData.append("descripcion", $("#descripcion").val());
    formData.append("_token", $("input[name=_token]").val());

    $.ajax({
        type:'post',
        url: "{{ route('formatos.f_felicitaciones.update') }}",
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
                text: "Se agregó exitosamente el registro!",
                className: "info",
                gravity: "bottom",
                style: {
                    background: "#47B257",
                }
            }).showToast();
        }
    });

}

function btnElimnarFelicitacion(idfelicitacion){

    swal.fire({
        title: "Seguro que desea eliminar el registro?",
        text: "El registro será eliminado",
        icon: "info",
        showCancelButton: !0,
        confirmButtonText: "Aceptar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: "{{ route('formatos.f_felicitaciones.delete') }}",
                type: 'post',
                data: {"_token": "{{ csrf_token() }}", idfelicitacion: idfelicitacion},
                success: function(response){
                    tabla_seccion();
                    Swal.fire({ 
                        title: "Eliminado!",
                        icon: "success",
                        text: "El registro fue eliminado del sistema",
                        confirmButtonText: "Aceptar"
                    });
                }
            });
        }

    })
}

/****************************************************************************** EXCEL ************************************************************************/

function exec_data_excel(){

    // Definimos la vista dende se enviara
    var link_up = "{{ route('formatos.f_felicitaciones.export_excel') }}";

    // Crear la URL con las variables como parámetros de consulta
    var href = link_up;

    console.log(href);

    var blank = "_blank";

    window.open(href);

}


</script>

@endsection