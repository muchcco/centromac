@extends('layouts.layout')

@section('style')
<link href="{{ asset('assets/vendors/DataTables/datatables.min.css')}}" rel="stylesheet">
@endsection

@section('main')

<div class="page-heading">
    <h1 class="page-title">Asistencia</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="index.html"><i class="la la-home font-20"></i></a>
        </li>
        <li class="breadcrumb-item">Hoy <strong>(<?php setlocale(LC_TIME, 'es_PE', 'Spanish_Spain', 'Spanish'); echo strftime('%d de %B del %Y',strtotime("now"));  ?>)</strong></li>
    </ol>
</div>

<div class="page-content fade-in-up">
    <div class="ibox">
        <div class="ibox-head">
            <div class="ibox-title">Filtros de búsqueda avanzado</div>
        </div>
        <div class="ibox-body">
            <div class="row">
                <div class="col-4">
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
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label for="">Año</label>
                        <select name="año" id="año" class="form-control select2 año"></select>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group" style="margin-top: 2em;">
                        <button type="button" class="btn btn-primary" id="filtro" onclick="execute_filter()"><i class="fa fa-search" aria-hidden="true"></i> Buscar</button>
                        <button class="btn btn-dark" id="limpiar"><i class="fa fa-undo" aria-hidden="true"></i> Limpiar</button>
                        <button class="btn btn-danger" id="pdf" onclick="ExportPDF()"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> PDF</button>
                        <button class="btn btn-success" id="excel" onclick="ExportEXCEL()"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Excel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="ibox">
    <div class="ibox-head">
        <div class="ibox-title">ASISTENCIA DEL CENTRO MAC -                 
            @foreach (auth()->user()->locales as $local)
                {{ $local->NOMBRE_MAC }}
            @endforeach                
    </div>
    </div>
    <div class="ibox-body">
        <div class="box-tools" style="margin-left: 1.2em;">
            {{-- <button class="btn btn-success" data-toggle="modal" data-target="#large-Modal" onclick="btnAddAsistencia()"><i class="fa fa-plus" aria-hidden="true"></i>
                Agregar Asistencia</button> --}}
            {{-- <a class="btn btn-info" href="{{ route('asistencia.det_entidad') }}"> Asistencia por entidad</a> --}}
        </div>
        <br />
        <div class="table-responsive" id="table_data">
            
        </div>
    </div>
</div>

{{-- Ver Modales --}}
<div class="modal fade" id="modal_show_modal" tabindex="-1" role="dialog" ></div>
@endsection

@section('script')

<script src="{{ asset('assets/vendors/DataTables/datatables.min.js')}}" type="text/javascript"></script>
    
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