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
                        <a href="{{ route('formatos.f_02_inicio_oper.formulario') }}" class="btn btn-success" target="_blank">Dar clic aqui</a>
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
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="mb-3">Día</label>
                                <select id="dia" name="dia" class="form-control" onchange="actualizarDias()"></select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="mb-3">Mes:</label>
                                <select name="mes" id="mes" class="form-control" onchange="SearchMes()">
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
                                <label class="mb-3">Año:</label>
                                <select name="año" id="año" class="form-control año" onchange="SearchAño()"></select>
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
});

function tabla_seccion(mes = '', año = '') {
    $.ajax({
        type: 'GET',
        url: "{{ route('formatos.evaluacion_motivacional.tablas.tb_index') }}", // Ruta que devuelve la vista en HTML
        data: {mes: mes, año : año},
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

function SearchMes(){
    var mes = $('#mes').val();

    tabla_seccion(mes);
}

function SearchAño(){
    console.log("año")
    var año = $('#año').val();

    tabla_seccion(año);
}

/**************************************************************** CARGAR COMBOS POR FECHA ACTUAL *************************************************************/
function ComboAno(){
   var n = (new Date()).getFullYear()
   var select = document.querySelector(".año");
   for(var i = n; i>=2023; i--)select.options.add(new Option(i,i)); 
};
window.onload = function () {
    ComboAno();
    // ComboMes();
    ComboDia(); // Agrega esta línea para cargar los días del mes actual
};

// Obtén el elemento select por su ID
var mesSelect = document.getElementById('mes');

// Obtén el mes actual (0 = enero, 1 = febrero, ..., 11 = diciembre)
var mesActual = new Date().getMonth() + 1;

console.log(mesActual);

// Selecciona el mes actual en el select
mesSelect.selectedIndex = mesActual

function ComboDia() {
    var diaSelect = document.getElementById('dia');

    // Obtén el año y mes seleccionados
    var añoSeleccionado = document.getElementById('año').value;
    var mesSeleccionado = document.getElementById('mes').value;

    // Obtén el número de días en el mes y año seleccionados
    var diasEnMes = new Date(añoSeleccionado, mesSeleccionado, 0).getDate();

    // Llenar el select de días con las opciones correspondientes
    for (var i = 1; i <= diasEnMes; i++) {
        var opcion = document.createElement('option');
        opcion.value = i < 10 ? '0' + i : '' + i;  // Agregar un cero delante si el día es menor a 10
        opcion.text = i < 10 ? '0' + i : '' + i;
        diaSelect.add(opcion);
    }
}

function actualizarDias() {
    console.log("asda");
    var mesSeleccionado = document.getElementById('mes').value;
    var añoSeleccionado = document.getElementById('año').value;
    var diasSelect = document.getElementById('dia');

    // Limpiar opciones anteriores
    diasSelect.innerHTML = '<option value="" disabled>Seleccione</option>';

    // Obtener el número de días en el mes y año seleccionados
    var diasEnMes = new Date(añoSeleccionado, mesSeleccionado, 0).getDate();

    // Llenar el select de días con las opciones correspondientes
    for (var i = 1; i <= diasEnMes; i++) {
        var opcion = document.createElement('option');
        opcion.value = i < 10 ? '0' + i : '' + i;  // Agregar un cero delante si el día es menor a 10
        opcion.text = i < 10 ? '0' + i : '' + i;
        diasSelect.add(opcion);
    }
}

/****************************************************************************** FIN ************************************************************************/




</script>

@endsection