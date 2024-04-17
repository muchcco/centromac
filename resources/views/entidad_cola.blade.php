<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registro de Asesores</title>
    <link href="{{ asset('css/bootstrap/bootstrap.min.css')}}" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">    
    {{-- <!-- jquery file upload Frame work -->
    <link href="{{ asset('assets/pages/jquery.filer/css/jquery.filer.css')}}" type="text/css" rel="stylesheet">
    <link href="{{ asset('assets/pages/jquery.filer/css/themes/jquery.filer-dragdropbox-theme.css')}}" type="text/css" rel="stylesheet"> --}}
    <link rel="stylesheet" href="{{ asset('css/all.css')}}"  integrity="sha384-SZXxX4whJ79/gErwcOYf+zWLeJdY/qpuqC4cAa9rOGUstPomtqpuNWT9wdPEn2fk" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/app2.css')}}">

    {{-- preoad button --}}
    <link rel="stylesheet" href="{{ asset('font-awesome-4.7.0/css/font-awesome.min.css')}}">
    <link rel="stylesheet" href="{{ asset('css/sweetalert2.min.css')}}">
    <script src="{{ asset('js/sweetalert2.all.min.js')}}"></script>


    <style>
      .raya{
        width: 100%;
        border: 1px solid black;
        margin-bottom: 1em;
      }

      .color{
        border: 1px solid black !important;
      }
    </style>

</head>
<body>
<article>
    <header>
        <div class="container ">
            <div class="row cab">
              <div class="col">
                <img src="{{ asset('img/200x75.png') }}" alt="">
              </div>
              <div class="col  title">
                <h2 style="text-align: center">CONSULTA DE USUARIOS EN ESPERA - NOVOSGA</h2>
              </div>
              <div class="col">
                <img src="{{ asset('img/200x75.png') }}" alt="">
              </div>
            </div>
          </div>
    </header>

    <section id="main">
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

    </section>

</article>
<div id="error"></div>
{{-- 
@include('modal_err') --}}

{{-- <script src="{{ asset('js/ext.js') }}"></script> --}}
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/bootstrap/bootstrap.bundle.min.js')}}" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>


<script src="{{ asset('js/toastr.min.js')}}"></script>



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


</body>
</html>