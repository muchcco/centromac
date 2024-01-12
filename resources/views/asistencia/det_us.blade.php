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
                            <label for="">Mes</label>
                            <select name="mes" id="mes" class="form-control">
                                <option value="" disabled selected>-- Seleccione una opción --</option>
                                <option value="01">Enero</option>
                                <option value="02">Febrero</option>
                                <option value="03">Marzo</option>
                                <option value="04">Abril</option>
                                <option value="05">Mayo</option>
                                <option value="06">Junio</option>
                                <option value="07">Julio</option>
                                <option value="08">Agosto</option>
                                <option value="09">Setiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12">Diciembre</option>
                            </select>
                        </div>
                    </div><!-- end col -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">Año</label>
                            <select name="año" id="año" class="form-control select2 año"></select>
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
                        {{-- <button class="btn btn-success" data-toggle="modal" data-target="#large-Modal" onclick="btnAddAsistencia()"><i class="fa fa-plus" aria-hidden="true"></i>
                            Agregar Asistencia</button>
                        <a class="btn btn-info" href="{{ route('asistencia.det_entidad', $idmac) }}"> Asistencia por entidad</a> --}}
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

function tabla_seccion(mes = '', año = '' ) {

    var num_doc = "{{ $personal->NUM_DOC }}";

    $.ajax({
        type: 'GET',
        url: "{{ route('asistencia.tablas.tb_det_us') }}", // Ruta que devuelve la vista en HTML
        data: { num_doc: num_doc, mes: mes, año: año },
        beforeSend: function () {
            document.getElementById("table_data").innerHTML = '<i class="fa fa-spinner fa-spin"></i> ESPERE LA TABLA ESTA CARGANDO... ';
        },
        success: function(data) {
            $('#table_data').html(data); // Inserta la vista en un contenedor en tu página
        }
    });
}

function ComboAno(){
   var n = (new Date()).getFullYear()
   var select = document.querySelector(".año");
   for(var i = n; i>=2023; i--)select.options.add(new Option(i,i)); 
};
window.onload = ComboAno;

// Obtén el elemento select por su ID
var mesSelect = document.getElementById('mes');

// Obtén el mes actual (0 = enero, 1 = febrero, ..., 11 = diciembre)
var mesActual = new Date().getMonth() + 1;
var año = (new Date()).getFullYear()
console.log(año);

// Selecciona el mes actual en el select
mesSelect.selectedIndex = mesActual

$("#limpiar").on("click", function(e) {
    document.getElementById('mes').value = mesActual;
    document.getElementById('año').value = año;

    tabla_seccion();

})

// EJECUTA LOS FILTROS Y ENVIA AL CONTROLLADOR PARA  MOSTRAR EL RESULTADO EN LA TABLA
var execute_filter = () =>{
   var mes = $('#mes').val();
    var año = $('#año').val();
    var estado = $('#estado').val();

    // var proc_data = "fecha="+fecha+"&entidad="+entidad+"$estado="+estado;

    console.log(mes, año);

   $.ajax({
        type:'get',
        url: "{{ route('asistencia.tablas.tb_asistencia') }}" ,
        dataType: "",
        data: {mes : mes, año : año , estado : estado},
        beforeSend: function () {
            document.getElementById("filtro").innerHTML = '<i class="fa fa-spinner fa-spin"></i> Buscando';
            document.getElementById("filtro").style.disabled = true;
        },
        success:function(data){
            document.getElementById("filtro").innerHTML = '<i class="mdi mdi-card-search-outline"></i> Buscar';
            document.getElementById("filtro").style.disabled = false;
            tabla_seccion(mes, año, estado);
        },
        error: function(xhr, status, error){
            console.log("error");
            console.log('Error:', error);
        }
   });
    
    // console.log('Fecha fecha: '+fecha,'Fecha Fin: ' +fechaFin,'Dependencia: ' +dependencia,'Estado: '+estado,'Usuario OEAS: '+us_oeas);
    //table_asistencia(fecha, entidad, estado);
}



/******************************************* METODOS PARA EXPORTAR DATOS ***********************************************************/

var ExportPDF = () => {

var año = document.getElementById('año').value;
var mes = document.getElementById('mes').value;
var num_doc = "{{ $personal->NUM_DOC }}";

// Definimos la vista dende se enviara
var link_up = "{{ route('asistencia.asistencia_pdf') }}";

// Crear la URL con las variables como parámetros de consulta
var href = link_up +'?año=' + año + '&mes=' + mes + '&num_doc=' + num_doc;

console.log(href);

var blank = "_blank";

window.open(href, blank);
}

var ExportEXCEL = () => {

var año = document.getElementById('año').value;
var mes = document.getElementById('mes').value;
var num_doc = "{{ $personal->NUM_DOC }}";

// Definimos la vista dende se enviara
var link_up = "{{ route('asistencia.asistencia_excel') }}";

// Crear la URL con las variables como parámetros de consulta
var href = link_up +'?año=' + año + '&mes=' + mes + '&num_doc=' + num_doc;

console.log(href);

var blank = "_blank";

window.open(href);


}

</script>

@endsection