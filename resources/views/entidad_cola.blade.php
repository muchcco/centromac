<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registro de Asesores</title>
    <link href="{{ asset('https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css')}}" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/app2.css')}}">
    {{-- <!-- jquery file upload Frame work -->
    <link href="{{ asset('assets/pages/jquery.filer/css/jquery.filer.css')}}" type="text/css" rel="stylesheet">
    <link href="{{ asset('assets/pages/jquery.filer/css/themes/jquery.filer-dragdropbox-theme.css')}}" type="text/css" rel="stylesheet"> --}}
    <link rel="stylesheet" href="{{ asset('https://use.fontawesome.com/releases/v5.15.3/css/all.css')}}"  integrity="sha384-SZXxX4whJ79/gErwcOYf+zWLeJdY/qpuqC4cAa9rOGUstPomtqpuNWT9wdPEn2fk" crossorigin="anonymous">

    {{-- preoad button --}}
    <link rel="stylesheet" href="{{ asset('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css')}}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.9.0/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.9.0/dist/sweetalert2.all.min.js"></script>

    <style>
      .raya{
        width: 100%;
        border: 1px solid black;
        margin-bottom: 1em;
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
          <div class="card col-sm-8">
            <div class="card-header">
              <center><h2>Revisión de cuidadanos por entidad</h2></center>
            </div>
            {{-- <div class="card-body">
              <p >Para un registro correcto revisar la guia de usuario, click en Descargar Guia de Usuario</p>
            </div> --}}
          </div>          
        </div>
      </div>
      <div class="container">
        <div class="carp">
          <div class="card col-sm-8">
            <div class="card-header">
              <h2>Cuidadanos en Espera :</h2>
            </div>
            <form action="" enctype="multipart/form-data">
              <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />
            <div class="card-body">
              <div class="carp">
                <div class="form-p">
                  <div class="row col-sm-12 buut">
                    
                    
                    <div class="mb-3">
                        <div class="row col-12">
                            <table class="table" id="seleccion">
                                <thead>
                                    <tr>
                                        <th>PERFIL</th>
                                        <th>HORA DE LLEGADA</th>
                                        <th>ESTADO</th>
                                        <th>CUIDADANO</th>
                                        <th>N° DE DOCUMENTO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($query as $q)
                                        <tr>
                                            <td>{{ $q->Entidad }}</td>
                                            <td>{{ $q->hora_llegada }}</td>
                                            <td>{{ $q->Estado }}</td>
                                            <td>{{ $q->Ciudadano }}</td>
                                            <td>{{ $q->num_docu }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-danger text-center">No tiene cuidadanos en espera</td>
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
<script src="{{ asset('https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js')}}" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>


<script src="{{ asset('js/toastr.min.js')}}"></script>



<script>

$(document).ready(function() {
      var refreshId =  setInterval( function(){
            $( "#seleccion" ).load(window.location.href + " #seleccion" );
            console.log('asdas') ;
        }, 5000 );

     console.log(refreshId)
});


</script>


</body>
</html>