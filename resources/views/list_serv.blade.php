@extends('layouts.externo')

@section('ext-styles')
<!-- DataTables -->
<link href="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{ asset('nuevo/plugins/datatables/buttons.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
<!-- Responsive datatable examples -->
<link href="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.css')}}" rel="stylesheet" type="text/css" /> 


<style>
  .raya{
    width: 100%;
    border: 1px solid black;
    margin-bottom: 1em;
  }

  table.dataTable td.dataTables_empty, table.dataTable th.dataTables_empty {
        text-align: center;
        color: red;
    }
    .bg-soft-primary {
        background: linear-gradient(#B8F2FC, #16DFFF);
    }
    .nav-logo{max-width: 100%;}

    thead{
        background-color: #3656ac!important;

    }

  tr th{
        color: #fff !important;
        vertical-align: middle !important;
        font-family: "Roboto",sans-serif;
        font-size: 14px;
        line-height: 20px;

  }

  tr td, td a{
      color: #474747;
      font-family: "Roboto",sans-serif;
      font-size: 13px;
      line-height: 20px;
  }

  .nobtn{
      background: none !important;
      border: none !important;
      margin: 0 !important;
      padding: 0 !important;
      cursor: pointer;            
      color: blue;
      font-size: 1em;
  }

  #requisitos {
    border: none !important;
    outline: none;
    resize: none;
    width: 100%;
    /* height: 200px; */
    padding: 10px;
    font-family: Arial, sans-serif;
    background-color: transparent;
    }
</style>

@endsection

@section('title')
    SERVICIOS BRINDADOS EN LOS - CENTROS MAC
@endsection

@section('externom')
<div class="container">
  <div class="carp">
    <div class="card col-sm-12">
      <div class="card-header">
        <center><h2>Revisión de Servicios para la entidad {{ $entidad->NOMBRE_ENTIDAD }} del centro MAC - {{ $mac->NOMBRE_MAC }}</h2></center>
      </div>
      {{-- <div class="card-body">
        <p >Para un registro correcto revisar la guia de usuario, click en Descargar Guia de Usuario</p>
      </div> --}}
    </div>          
  </div>
</div>
<div class="container">
  <div class="carp">
    <div class="card col-sm-12">
      <div class="card-header">
        <h2>Revisar servicios asignados a su entidad:</h2>
      </div>
      <div class="card-body">

        <a href="{{ url()->previous() }}" class="btn btn-danger mb-3">Regresar</a>

        <table class="table table-striped table-bordered table-hover" id="table_servicios">
          <thead class="tenca">
              <tr class="bg-dark">
                  <th  width="50px">N°</th>
                  <th >Entidad</th>
                  <th >Nombre del Servicio</th>
                  <th style="width: 125px">Tipo de Servicio</th>
                  <th >Costo (en soles)<br /> S/.</th>
                  <th >Centro MAC</th>
                  <th >Requisitos</th>
                  <th >Requiere cita?</th>
                  <th >Observación</th>
              </tr>
          </thead>
          <tbody>
              @foreach ($servicios as $i =>$servicio)
                  <tr>
                      <td>{{ $i + 1 }}</td>
                      <td>{{ $servicio->NOMBRE_ENTIDAD }}</td>
                      <td>{{ $servicio->NOMBRE_SERVICIO }}</td>
                      <td style="width: 125px" class="pr-1">
                          <ul>
                              <li>Trámite: 
                                  @if ($servicio->TRAMITE == 1)
                                      SI
                                  @else
                                      NO
                                  @endif
                              </li>
                              <li>Orientación:
                                  @if ($servicio->ORIENTACION == 1)
                                      SI
                                  @else
                                      NO
                                  @endif
                              </li>
                          </ul>
                      </td>
                      <td>{{ $servicio->COSTO_SERV }}</td>
                      <td>{{ $servicio->NOMBRE_MAC }}</td>
                      <td>                                
                          <textarea class="requisitos" id="requisitos" oninput="autoResize(this)">{{ $servicio->REQUISITO_SERVICIO }}</textarea>
                      </td>               
                      <td>
                          {{ $servicio->REQ_CITA }}
                      </td>
                      <td id="btnobserv">
                          @if ( $servicio->OBSERVACION == NULL )
                              <button type="button" class="btn btn-info" onclick="btnObservar('{{ $servicio->IDSERVICIOS }}')">Observar</button>    
                          @else
                              <a class="" style="cursor: pointer" onclick="btnObservar('{{ $servicio->IDSERVICIOS }}')">{{ $servicio->OBSERVACION }}</a>
                          @endif                                             
                          
                      </td>
                      {{-- <td class="unalinea">
                          <button type="button"  name="boton" id="devolver" data-target="#modal-devolucion" data-toggle="modal"  class="btn btn-dark btn-sm bandejTool" disabled title="No puede realizar esta acción"><i class="fa fa-reply" aria-hidden="true"></i></button>
                          <button type="button"  name="boton" id="transferir" data-target="#modal-expediente" data-toggle="modal"  class="btn btn-info btn-sm bandejTool" data-tippy-content="transferir Documento Corregido" onclick="DevDocCorregido('{{ $servicio->IDENT_SERV }}', '{{ $servicio->IDSERVICIOS }}' )"><i class="fa fa-share" aria-hidden="true"></i></button>
                      </td> --}}
                  </tr>
              @endforeach
          </tbody>
        </table>
      </div>           
    </div>
  </div>
</div>

@endsection

@section('ext-script')

<!-- Required datatable js -->
<script src="{{asset('nuevo/plugins/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.js')}}"></script>



<script>

  $(document).ready(function() {
  
    $('#table_servicios').DataTable({
        "responsive": true,
        "bLengthChange": true,
        "autoWidth": false,
        "searching": true,
        info: true,
        "ordering": false,
        language: {"url": "{{ asset('js/Spanish.json')}}"}, 
        "columns": [
            { "width": "5px" },
            { "width": "" },
            { "width": "" },
            { "width": "25" },
            { "width": "" },
            { "width": "" },
            { "width": "680px" },
            { "width": "" },
            { "width": "" }
        ]
    });
  
    $('.requisitos').each(function() {
        console.log(this.scrollHeight);
        this.style.height = 'auto';
        const originalHeight = this.scrollHeight;
        let maxHeight = originalHeight / 2; // Establece la altura maxima como la mitad de la altura original
        if (originalHeight > 650) {
            maxHeight = originalHeight * (1/4); // Reduzca a 1/4 de la altura original si supera los 500 px
        }
        if (originalHeight > 1000) {
            maxHeight = originalHeight * (1/3); // Reduzca a 1/4 de la altura original si supera los 500 px
        }
        if (originalHeight > 1700) {
            maxHeight = originalHeight * (1/5); // Reduzca a 1/4 de la altura original si supera los 500 px
        }
        this.style.height = Math.min(this.scrollHeight, maxHeight) + 'px';
    });
    
  });
  
  function btnObservar(idservicios){
  
    $.ajax({
        type:'post',
        url: "{{ route('md_edit_servicios_ext') }}",
        dataType: "json",
        data:{"_token": "{{ csrf_token() }}",  idservicios : idservicios},
        success:function(data){
            $("#modal_show_modal").html(data.html);
            $("#modal_show_modal").modal('show');
        }
    });
  
  }
  
  function btnEditServicio(idservicios){
  console.log(idservicios);
  var formData = new FormData();
  formData.append("idservicios", idservicios);
  formData.append("observacion", $("#observacion").val());
  formData.append("_token", $("input[name=_token]").val());
  
  $.ajax({
      type:'post',
      url: "{{ route('update_obsev') }}",
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
          $( "#table_servicios" ).load(window.location.href + " #table_servicios" ); 
          location.reload();
              
              Swal.fire({
                  icon: "info",
                  text: "Se agregó la observación con Exito!",
                  confirmButtonText: "Aceptar"  
              })            
      },
      error: function(){
          document.getElementById("btnEnviarForm").innerHTML = 'Guardar';
          document.getElementById("btnEnviarForm").disabled = false;
      }
  });
  
  }
  
  document.addEventListener('DOMContentLoaded', function() {
    console.log("asda")
      // Función para ocultar los bordes del textarea (opcional)
      document.getElementById('requisitos').style.border = 'none';
  
      // Asignar la función autoResize al evento input del textarea
      document.getElementById('requisitos').addEventListener('input', function() {
          autoResize(this);
      });
  
      // Función para ajustar dinámicamente la altura del textarea
      function autoResize(textarea) {
          console.log("assssss");
          textarea.style.height = 'auto';
          textarea.style.height = (textarea.scrollHeight) + 'px';
      }
  });
  
  </script>
  
  

@endsection