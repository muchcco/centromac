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
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="mb-3">Centro MAC:</label>
                            <select name="mac" id="mac" class="form-select" onchange="SearchMac()">
                                @role('Administrador|Moderador')
                                    <option value="" disabled selected>-- Seleccione una opción --</option>
                                    @forelse ($mac as $m)
                                        <option value="{{ $m->IDCENTRO_MAC }}">{{ $m->NOMBRE_MAC }}</option>
                                    @empty
                                        <option value="">SIN RESULTADOS</option>
                                    @endforelse
                                @else
                                    @forelse ($mac as $m)
                                        <option value="{{ $m->IDCENTRO_MAC }}" disabled selected>{{ $m->NOMBRE_MAC }}</option>
                                    @empty
                                        <option value="">SIN RESULTADOS</option>
                                    @endempty
                                @endrole
                            </select>
                            
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="mb-3">Mes:</label>
                            <select name="mes" id="mes" class="form-select" onchange="SearchMes()">
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
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="mb-3">Año:</label>
                            <select name="año" id="año" class="form-select select2 año" onchange="SearchAño()"></select>
                        </div>
                    </div><!-- end col -->
                    <div class="col-md-4">
                        <div class="form-group" style="margin-top: 2.6em">
                            
                                <p>El mes y año se verá reflejado cuando se descarga el archivo Excel</p>
                            
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
                <div class="alert custom-alert custom-alert-warning icon-custom-alert shadow-sm fade show d-flex justify-content-between" role="alert">  
                    <div class="media">
                        <i class="la la-exclamation-triangle alert-icon text-warning align-self-center font-30 me-3"></i>
                        <div class="media-body align-self-center">
                            <h5 class="mb-1 fw-bold mt-0">Importante</h5>
                            <span>Si una entidad no se visualiza, es porque el usuario no ha culminado de realizar su registro.</span>
                        </div>
                    </div>                                  
                    {{-- <button type="button" class="btn-close align-self-center" data-bs-dismiss="alert" aria-label="Close"></button> --}}
                </div>
                <div class="row">
                    <div class="col-12 mb-3">
                        <a class="btn btn-danger" href="{{ url()->previous() }}"><i class="fa fa-arrow-left" aria-hidden="true"></i> Volver</a>
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
    tippy(".bandejTool", {
        allowHTML: true,
        followCursor: true,
    });
});

function SearchMes(){
    var mes = $('#mes').val();
    var año = $('#año').val();
    tabla_seccion(mes, año);
}

function SearchAño(){
    var mes = $('#mes').val();
    var año = $('#año').val();
    tabla_seccion(mes, año);
}

function tabla_seccion( mes = '0'+ mesSelect.selectedIndex, año = new Date().getFullYear()) {

    // Obtener el valor del MAC
    var mac = '';

    // Si el usuario tiene el rol, seleccionar el mac asignado
    @if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador'))
        mac = '{{ auth()->user()->idcentro_mac }}'; // ID del MAC del usuario
    @else
        // Si no tiene los roles, obtener el valor seleccionado del select de MAC
        mac = $('#mac').val();
    @endif


    $.ajax({
        type: 'GET',
        url: "{{ route('asistencia.tablas.tb_det_entidad') }}", // Ruta que devuelve la vista en HTML
        data: {mes: mes, año: año, mac: mac},
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

console.log(mesActual);

// Selecciona el mes actual en el select
mesSelect.selectedIndex = mesActual

function btnExcelPersonalizado (identidad)  {
    var mac = document.getElementById('mac').value;
    $.ajax({
        type:'post',
        url: "{{ route('asistencia.modals.md_det_entidad_perso') }}",
        dataType: "json",
        data:{"_token": "{{ csrf_token() }}", identidad : identidad, mac: mac},
        success:function(data){
            $("#modal_show_modal").html(data.html);
            $("#modal_show_modal").modal('show');
        }
    });
}

function SearchMac() {
    var mac = $('#mac').val();
    var mes = $('#mes').val();
    var año = $('#año').val();
    
    if (!mac) {
        @if (!auth()->user()->hasRole('Administrador|Moderador'))
            mac = {{ auth()->user()->idcentro_mac }}; // Asignar el MAC por defecto si el rol no es Admin/Moderador
        @endif
    }

    tabla_seccion(mac, mes, año);  // Añadimos el mac a la llamada de la tabla
}

/******************************************* METODOS PARA EXPORTAR DATOS ***********************************************************/


function BtnDowloadExcel(identidad) {
    console.log("datos aqui")
    swal.fire({
        title: "Seguro que desea descargar el archivo?",
        text: "El archivo será descargará con fecha seleccionada en la sección de filtro de búsqueda",
        icon: "info",
        showCancelButton: !0,
        confirmButtonText: "Descargar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.value) {
            console.log(identidad);
            var mac = document.getElementById('mac').value;
            var año = document.getElementById('año').value;
            var mes = document.getElementById('mes').value;

            // Definimos la vista dende se enviara
            var link_up = "{{ route('asistencia.exportgroup_excel') }}";

            // Crear la URL con las variables como parámetros de consulta
            var href = link_up +'?año=' + año + '&mes=' + mes + '&identidad=' + identidad + '&mac=' + mac;

            window.open(href);

            Swal.fire({
                icon: "success",
                text: "El archivo se descargo con Exito!",
                confirmButtonText: "Aceptar"
            })
        }

    })
}

function BtnDowloadExcelPers(identidad){

    if ($('#fecha_inicio').val() == null || $('#fecha_inicio').val() == '') {
        $('#fecha_inicio').addClass("hasError");
    } else {
        $('#fecha_inicio').removeClass("hasError");
    }

    if ($('#fecha_fin').val() == null || $('#fecha_fin').val() == '') {
        $('#fecha_fin').addClass("hasError");
    } else {
        $('#fecha_fin').removeClass("hasError");
    }
    

    console.log(identidad);
    var fecha_inicio = document.getElementById('fecha_inicio').value;
    var fecha_fin = document.getElementById('fecha_fin').value;
    var mac = document.getElementById('mac').value;

    // Definimos la vista dende se enviara
    var link_up = "{{ route('asistencia.exportgroup_excel_pr') }}";

    // Crear la URL con las variables como parámetros de consulta
    var href = link_up +'?fecha_inicio=' + fecha_inicio + '&fecha_fin=' + fecha_fin + '&identidad=' + identidad + '&mac=' + mac;

    window.open(href);

    Swal.fire({
                icon: "success",
                text: "El archivo se descargo con Exito!",
                confirmButtonText: "Aceptar"
            })

}

/******************************************************  EXPORTAR DE FORMA GENERAL ******************************************************/

function BtnDowloadExcelGeneral(){
    console.log("asdadad")
    swal.fire({
                title: "Seguro que desea descargar el archivo?",
                text: "El archivo será descargará con fecha seleccionada en la sección de filtro de búsqueda",
                icon: "info",
                showCancelButton: !0,
                confirmButtonText: "Descargar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.value) {
                    var mac = document.getElementById('mac').value;
                    var año = document.getElementById('año').value;
                    var mes = document.getElementById('mes').value;

                    // Definimos la vista dende se enviara
                    var link_up = "{{ route('asistencia.exportgroup_excel_general') }}";

                    // Crear la URL con las variables como parámetros de consulta
                    var href = link_up +'?mac=' + mac + '&año=' + año + '&mes=' + mes;

                    window.open(href);

                    Swal.fire({
                        icon: "success",
                        text: "El archivo se descargo con Exito!",
                        confirmButtonText: "Aceptar"
                    })
                }

            })
}
        
</script>

@endsection