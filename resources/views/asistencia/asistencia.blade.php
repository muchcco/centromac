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
                    <h4 class="page-title">Asistencia</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('inicio') }}"><i data-feather="home" class="align-self-center" style="height: 70%; display: block;"></i></a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);" style="color: #7081b9;">Hoy <strong>(<?php setlocale(LC_TIME, 'es_PE', 'Spanish_Spain', 'Spanish'); echo strftime('%d de %B del %Y',strtotime("now"));  ?>)</strong></a></li>
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
                <h4 class="card-title text-white">Filtro de Búsqueda</h4>
            </div><!--end card-header-->
            <div class="card-body bootstrap-select-1">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="mb-3">Fecha:</label>
                            @php
                                $fecha6dias = date("d-m-Y",strtotime(now()));
                                $fecha6diasconvert = date("Y-m-d",strtotime($fecha6dias));
                            @endphp
                            <input type="date" name="fecha" id="fecha" class="form-control" value="{{$fecha6diasconvert}}">
                        </div>
                    </div><!-- end col -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="mb-3">Entidad:</label>
                            <select name="entidad" id="entidad" class="form-control col-sm-12 select2">
                                <option value="" disabled selected>-- Selecciones una opción --</option>
                                @forelse ($entidad as $ent)
                                    <option value="{{ $ent->IDENTIDAD }}">{{ $ent->NOMBRE_ENTIDAD }}</option>
                                @empty
                                    <option value="">No hay datos disponibles</option>
                                @endforelse
                            </select>
                        </div>
                    </div><!-- end col -->
                    <div class="col-md-4">
                        <div class="form-group" style="margin-top: 2.6em">
                            <button type="button" class="btn btn-primary" id="filtro" onclick="execute_filter()"><i class="fa fa-search" aria-hidden="true"></i> Buscar</button>
                            <button class="btn btn-dark" id="limpiar"><i class="fa fa-undo" aria-hidden="true"></i> Limpiar</button>
                        </div>
                    </div><!-- end col --> 
                </div>
            </div><!-- end card-body --> 
        </div> <!-- end card -->                               
    </div> <!-- end col -->
</div> <!-- end row -->

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header" style="background-color:#132842">
                <h4 class="card-title text-white">ASISTENCIA DEL CENTRO MAC -  
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
                        <button class="btn btn-success" data-toggle="modal" data-target="#large-Modal" onclick="btnAddAsistencia()"><i class="fa fa-plus" aria-hidden="true"></i>
                            Agregar Asistencia</button>
                        <a class="btn btn-info" href="{{ route('asistencia.det_entidad', $idmac) }}"> Asistencia por entidad</a>
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
    $(document).ready(function() {
        $('.select2').select2();
    });
});

function tabla_seccion(fecha = '', entidad = '', estado = '' ) {
    $.ajax({
        type: 'GET',
        url: "{{ route('asistencia.tablas.tb_asistencia') }}", // Ruta que devuelve la vista en HTML
        data: {fecha: fecha, entidad: entidad, estado: estado},
        beforeSend: function () {
            document.getElementById("table_data").innerHTML = '<i class="fa fa-spinner fa-spin"></i> ESPERE LA TABLA ESTA CARGANDO... ';
        },
        success: function(data) {
            $('#table_data').html(data); // Inserta la vista en un contenedor en tu página
        }
    });
}

$("#limpiar").on("click", function(e) {
    
    var fechaActual = new Date();

    var formatoFecha = fechaActual.toISOString().split('T')[0];

    document.getElementById('fecha').value = formatoFecha;
    document.getElementById('entidad').value = "";

    tabla_seccion();

})

// EJECUTA LOS FILTROS Y ENVIA AL CONTROLLADOR PARA  MOSTRAR EL RESULTADO EN LA TABLA
var execute_filter = () =>{
   var fecha = $('#fecha').val();
    var entidad = $('#entidad').val();
    var estado = $('#estado').val();

    // var proc_data = "fecha="+fecha+"&entidad="+entidad+"$estado="+estado;

    // console.log(proc_data);

   $.ajax({
        type:'get',
        url: "{{ route('asistencia.tablas.tb_asistencia') }}" ,
        dataType: "",
        data: {fecha : fecha, entidad : entidad , estado : estado},
        beforeSend: function () {
            document.getElementById("filtro").innerHTML = '<i class="fa fa-spinner fa-spin"></i> Buscando';
            document.getElementById("filtro").style.disabled = true;
        },
        success:function(data){
            document.getElementById("filtro").innerHTML = '<i class="fa fa-search"></i> Buscar';
            document.getElementById("filtro").style.disabled = false;
            tabla_seccion(fecha, entidad, estado);
        },
        error: function(xhr, status, error){
            console.log("error");
            console.log('Error:', error);
        }
   });
    
    // console.log('Fecha fecha: '+fecha,'Fecha Fin: ' +fechaFin,'Dependencia: ' +dependencia,'Estado: '+estado,'Usuario OEAS: '+us_oeas);
    //table_asistencia(fecha, entidad, estado);
}

function btnAddAsistencia ()  {

    $.ajax({
        type:'post',
        url: "{{ route('asistencia.modals.md_add_asistencia') }}",
        dataType: "json",
        data:{"_token": "{{ csrf_token() }}"},
        success:function(data){
            $("#modal_show_modal").html(data.html);
            $("#modal_show_modal").modal('show');
        }
    });
}


function btnStoreTxt () {
    var file_data = $("#txt_file").prop("files")[0];
    var currentDate = document.querySelector('input[id="fecha_reg"]');
    console.log(currentDate.value);
    var formData = new FormData();

    formData.append("fecha_reg", currentDate.value);
    formData.append("txt_file", file_data);
    formData.append("_token", $("input[name=_token]").val());

    $.ajax({
        type:'post',
        url: "{{ route('asistencia.store_asistencia') }}",
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

</script>

@endsection