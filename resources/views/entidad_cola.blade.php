@extends('layouts.externo')

@section('title')
    CONSULTA DE USUARIOS EN ESPERA - NOVOSGA
@endsection

@section('externom')
  <div class="container">
    <div class="carp">
      <div class="card col-sm-8 col-12 col-lg-12 col-md-12">
        <div class="card-header">
          <center><h2>Revisión de ciudadanos por entidad</h2></center>
        </div>
        {{-- <div class="card-body">
          <p >Para un registro correcto revisar la guia de usuario, click en Descargar Guia de Usuario</p>
        </div> --}}
      </div>          
    </div>
  </div>
  <div class="container">
    <div class="carp">
      <div class="card col-sm-8 col-12 col-lg-12 col-md-12">
        <div class="card-header">
          <div class="r-t">
            <a href="{{ url()->previous() }}" class="btn btn-danger">Regresar</a>
          </div>
          <div class="r-t">
            Tiempo estimado: <span id="minutos"> {{ $sumaTotalTiempo }} </span> minutos.<br /> Hora fin programada: <span id="hora-programda">{{ $horaProgramadaFormato }}</span> <strong>!Tener en cuenta la hora de almuerzo y salida del asesor</strong>
          </div>
          <div class="r-t">
            Esta en hora? <span id="tarde">{{ $estaEnTarde ? 'Sí' : 'No' }}</span> 
          </div>
        </div>
      </div>
    </div>
  </div>      
  <div class="container">
    <div class="carp">
      <div class="card col-sm-8 col-12 col-lg-12 col-md-12">
        <div class="card-header">
          <h2>Ciudadanos en Espera :</h2>
        </div>
        <form action="" enctype="multipart/form-data">
          <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />
        <div class="card-body">
          <div class="carp">
            
            <div class="form-p">
              <div class="row col-sm-12 buut">
                
                <p>Total de cuidadanos: <span id="cantidad">{{ $cantidadTotal }} </span> </p>
                <div class="mb-3">
                    <div class="row col-12">
                        <table class="table table-bordered table-hover" id="seleccion">
                            <thead class="bg-dark">
                                <tr>
                                    <th class="text-white">N°</th>
                                    <th class="text-white">PERFIL</th>
                                    <th class="text-white">HORA DE LLEGADA</th>
                                    <th class="text-white">TIEMPO DE ESPERA</th>
                                    <th class="text-white">ESTADO</th>
                                    <th class="text-white">CUIDADANO</th>
                                    <th class="text-white">N° DE DOCUMENTO</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($query as $i => $q)
                                    <tr >                                          
                                        <td style="color: {{ $q->prioridade_id == '1' ? 'blue' : 'red' }}">{{ $i + 1 }}</td>
                                        <td style="color: {{ $q->prioridade_id == '1' ? 'blue' : 'red' }}">{{ $q->Entidad }}</td>
                                        <td style="color: {{ $q->prioridade_id == '1' ? 'blue' : 'red' }}">{{ $q->hora_llegada }}</td>
                                        <td style="color: {{ $q->prioridade_id == '1' ? 'blue' : 'red' }}">{{ $q->Tiempo_espera }}</td>
                                        <td style="color: {{ $q->prioridade_id == '1' ? 'blue' : 'red' }}">{{ $q->Estado }}</td>
                                        <td style="color: {{ $q->prioridade_id == '1' ? 'blue' : 'red' }}">{{ $q->Ciudadano }}</td>
                                        <td style="color: {{ $q->prioridade_id == '1' ? 'blue' : 'red' }}">{{ $q->num_docu }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-danger text-center">No tiene cuidadanos en espera</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>


                    </div>
                </div>
              </div>
            </div>
          </div>

        </div>            
      </div>
    </div>
  </div>

@endsection

@section('ext-script')


<script>

  $(document).ready(function() {
        var refreshId =  setInterval( function(){
              $( "#seleccion" ).load(window.location.href + " #seleccion" );
        }, 1000 );
        var refreshId =  setInterval( function(){
            $( "#minutos" ).load(window.location.href + " #minutos" );
        }, 1000 );
        var refreshId =  setInterval( function(){
            $( "#tarde" ).load(window.location.href + " #tarde" );
        }, 1000 );
        var refreshId =  setInterval( function(){
            $( "#cantidad" ).load(window.location.href + " #cantidad" );
        }, 1000 );
        var refreshId =  setInterval( function(){
            $( "#hora-programda" ).load(window.location.href + " #hora-programda" );
        }, 1000 );
  
       console.log(refreshId);
  });
  
  
  
  </script>
  

@endsection