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

<style>
    .thead-b {
        color: #000 !important;
        background: #fff !important;
    }

    .per{
        font-size: 8px;
    }

    input[type="button"].seleccionado {
        background-color: #4caf50; /* Color de fondo cuando está seleccionado */
        color: white; /* Color del texto cuando está seleccionado */
    }

    .border-cell {
        border-right: 2px solid black !important;
        border-left: 2px solid black !important;
    }

    .border-cell-r {
        border-right: 2px solid black !important;
    }
    .border-cell-l {
        border-left: 2px solid black !important;
    }
</style>

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
                        <li class="breadcrumb-item"><a href="javascript:void(0);" style="color: #7081b9;">Formato 02 Inicio de Operaciones</a></li>
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
                <h4 class="card-title text-white">Leyenda</h4>
            </div><!--end card-header-->
            <div class="card-body bootstrap-select-1">
                <div class="row">
                    <div class="col-md-4 border-end">
                        <table class="table table-bordered ">
                            <thead>
                                <tr>
                                    <th class="text-center">N    Condición Operativa</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-left">Verificar si el recurso, equipo u otro, esta dañado, roto, desprolijo, inoperativo o funcionando inadecuadamente según aplique</td>
                                </tr>
                            </tbody>
    
                        </table>
                    </div>

                    <div class="col-md-8">
                        <h5>Ir al formulario </h5>
                        <div class="form-group col-3">
                            @php
                                $fecha6dias = date("d-m-Y",strtotime(now()));
                                $fecha6diasconvert = date("Y-m-d",strtotime($fecha6dias));
                            @endphp
                            <input type="date" class="form-control" name="fecha" id="fecha" value="{{$fecha6diasconvert}}"> <p>Seleccionar la fecha que desee registrar</p>
                        </div>                        
                        {{-- <a href="{{ route('formatos.f_02_inicio_oper.formulario', ingresar fecha del id fecha) }}" class="btn btn-success" target="_blank">Dar clic aqui</a> --}}
                        <a href="#" class="btn btn-success" id="btnIrAlFormulario" target="_blank">Dar clic aquí</a>

                    </div>
                    
                </div>
            </div><!-- end card-body --> 
        </div> <!-- end card -->                               
    </div> <!-- end col -->
</div> <!-- end row -->


<section id="eval_cambio">
    <div class="row" id="table_evalua">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header" style="background-color:#132842">
                    <h4 class="card-title text-white">REPORTE DE FORMATO 02 INICIO DE OPERACIONES DEL CENTRO MAC -  
                        @php
                            $us_id = auth()->user()->idcentro_mac;
                            $user = App\Models\User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();
    
                            echo $user->NOMBRE_MAC;
                        @endphp
                    </h4>
                </div><!--end card-header-->
                <div class="card-body">
                    <div class="alert custom-alert custom-alert-warning icon-custom-alert shadow-sm fade show d-flex justify-content-between" role="alert">  
                        <div class="media">
                            <i class="la la-exclamation-triangle alert-icon text-warning align-self-center font-30 me-3"></i>
                            <div class="media-body align-self-center">
                                <h5 class="mb-1 fw-bold mt-0">Importante</h5>
                                <span>Para una mejor visualización, seleccionar un rango de fechas no mayor a 6 días</span>
                            </div>
                        </div>                                  
                        {{-- <button type="button" class="btn-close align-self-center" data-bs-dismiss="alert" aria-label="Close"></button> --}}
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="mb-3">Inicio</label>
                                <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="mb-3">Fin</label>
                                <input type="date" class="form-control" name="fecha_fin" id="fecha_fin">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group" style="margin-top:2.6em;">
                                <input type="button" class="btn btn-primary" value="Buscar" id="filtro" onclick="execute_filter()">
                            </div>
                        </div><!-- end col -->
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
    $('.select2').select2();

    /***/
    $('#btnIrAlFormulario').on('click', function () {
        // Obtener la fecha seleccionada del input
        var fechaSeleccionada = $('#fecha').val();

        // Validar si se ha seleccionado una fecha
        if (fechaSeleccionada) {
            // Construir la URL con la fecha seleccionada
            var url = "{{ route('formatos.f_02_inicio_oper.formulario', ':fecha') }}";
            url = url.replace(':fecha', fechaSeleccionada);

            // Redirigir a la URL
            //window.location.href = url;
            // Abrir la URL en una nueva ventana
            window.open(url, '_blank');
        } else {
            // Manejar la situación donde no se ha seleccionado una fecha
            alert('Por favor, selecciona una fecha antes de ir al formulario.');
        }
    });
});

function tabla_seccion(fecha_inicio = '',  fecha_fin = '') {
    $.ajax({
        type: 'GET',
        url: "{{ route('formatos.f_02_inicio_oper.tablas.tb_index') }}", // Ruta que devuelve la vista en HTML
        data: {fecha_inicio : fecha_inicio, fecha_fin: fecha_fin},
        beforeSend: function () {
            document.getElementById("table_data").innerHTML = '<i class="fa fa-spinner fa-spin"></i> ESPERE LA TABLA ESTA CARGANDO... ';
        },
        success: function(data) {
            $('#table_data').html(data); // Inserta la vista en un contenedor en tu página
        },
        error: function(error){
            // tabla_seccion();
        }
    });
}


/**************************************************************** CARGAR COMBOS POR FECHA ACTUAL *************************************************************/

// EJECUTA LOS FILTROS Y ENVIA AL CONTROLLADOR PARA  MOSTRAR EL RESULTADO EN LA TABLA
var execute_filter = () =>{
   var fecha_inicio = $('#fecha_inicio').val();
    var fecha_fin = $('#fecha_fin').val();
   $.ajax({
        type:'get',
        url: "{{ route('formatos.f_02_inicio_oper.tablas.tb_index') }}" ,
        dataType: "",
        data: {fecha_inicio : fecha_inicio, fecha_fin : fecha_fin},
        beforeSend: function () {
            document.getElementById("filtro").innerHTML = '<i class="fa fa-spinner fa-spin"></i> Buscando';
            document.getElementById("filtro").style.disabled = true;
        },
        success:function(data){
            document.getElementById("filtro").innerHTML = '<i class="fa fa-search"></i> Buscar';
            document.getElementById("filtro").style.disabled = false;
            tabla_seccion(fecha_inicio, fecha_fin);
        },
        error: function(xhr, status, error){
            console.log("error");
            console.log('Error:', error);
        }
   });
}


/****************************************************************************** FIN ************************************************************************/




</script>

@endsection