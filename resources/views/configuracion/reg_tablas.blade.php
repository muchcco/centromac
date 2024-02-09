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
                    <h4 class="page-title">Configuración</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('inicio') }}"><i data-feather="home" class="align-self-center" style="height: 70%; display: block;"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{route('configuracion.nuevo_mac') }}" style="color: #7081b9;">MAC</a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);" style="color: #7081b9;">MAC - {{ $mac->NOMBRE_MAC }}</a></li>
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
                <h4 class="card-title text-white"> CONFIGURACION DEL SISTEMA DEL CENTRO MAC - {{ $mac->NOMBRE_MAC }}</h4>
            </div><!--end card-header-->
            <div class="card-body bootstrap-select-1">
                <ul>
                    <li>CONFIGURACION DE TABLAS</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">                                        
                <div class="media mb-3">
                    <img src="{{ asset('imagen/logo-novosga.png') }}" alt="" class="thumb-md rounded-circle">                                      
                    <div class="media-body align-self-center text-truncate ms-3">                                            
                        <h4 class="m-0 fw-semibold text-dark font-15">Configuración para accesos al novosga</h4>   
                        <p class="text-muted  mb-0 font-13"><span class="text-dark">Acceso : </span>Por el personal TIC</p>                                         
                    </div><!--end media-body-->
                </div>   
                <hr class="hr-dashed">
                <div class="d-flex justify-content-between mb-3">  
                    <h6 class="fw-semibold m-0">Accesos:</h6>                      
                </div> 
                <div class="row">
                    <div class="col align-self-left">
                        <div class="form-group row">
                            <label class="col-xl-3 col-lg-3 text-end mb-lg-0 align-self-center">Conexión:</label>
                            <div class="col-lg-9 col-xl-8">
                                <input class="form-control" type="text" value="MySql" disabled>
                            </div>
                        </div>
                    </div>                
                <div> 
                <div class="row">
                    <div class="col align-self-left">
                        <div class="form-group row">
                            <label class="col-xl-3 col-lg-3 text-end mb-lg-0 align-self-center">IP </label>
                            <div class="col-lg-9 col-xl-8">
                                <input class="form-control" type="text" value="192.168.xxx.xxx">
                            </div>
                        </div>
                    </div>                
                <div> 
                <div class="row">
                    <div class="col align-self-left">
                        <div class="form-group row">
                            <label class="col-xl-3 col-lg-3 text-end mb-lg-0 align-self-center">Usuario</label>
                            <div class="col-lg-9 col-xl-8">
                                <input class="form-control" type="text" value="novosga2019" >
                            </div>
                        </div>
                    </div>                
                <div>
                <div class="row">
                    <div class="col align-self-left">
                        <div class="form-group row">
                            <label class="col-xl-3 col-lg-3 text-end mb-lg-0 align-self-center">Pasword  </label>
                            <div class="col-lg-9 col-xl-8">
                                <input class="form-control" type="password" >
                            </div>
                        </div>
                    </div>                
                <div>
                <div class="row">
                    <div class="col align-self-left">
                        <div class="form-group row">
                            <label class="col-xl-3 col-lg-3 text-end mb-lg-0 align-self-center">Base de Datos </label>
                            <div class="col-lg-9 col-xl-8">
                                <input class="form-control" type="text" value="novosga2019" >
                            </div>
                        </div>
                    </div>              
        </div><!--end card-->
    </div><!--end col-->
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

function tabla_seccion() {
    $.ajax({
        type: 'GET',
        url: "{{ route('configuracion.tablas.tb_nuevo_mac') }}", // Ruta que devuelve la vista en HTML
        data: {},
        beforeSend: function () {
            document.getElementById("table_data").innerHTML = '<i class="fa fa-spinner fa-spin"></i> ESPERE LA TABLA ESTA CARGANDO... ';
        },
        success: function(data) {
            $('#table_data').html(data); // Inserta la vista en un contenedor en tu página
        }
    });
}

</script>

@endsection