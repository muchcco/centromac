<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registro de Asesores</title>
    <link href="{{ asset('https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css')}}" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/app2.css')}}">
    <link rel="stylesheet" href="{{ asset('css/loading.css')}}">
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

      .color{
        border: 1px solid black !important;
      }
      /* #loading-ext{
        display: none;
      } */
    </style>

    @yield('ext-styles')

</head>
<body>
  <div id="loading-ext">
    <div class="lds-ripple" ><div></div><div></div></div>
  </div>  
<article>
    <header>
      <div class="container ">
        <div class="row cab">
          <div class="col">
            <img src="{{ asset('imagen/logo-pcm.png') }}" alt="" width="200">
          </div>
          <div class="col  title">
            <h2 style="text-align: center">
                @yield('title')
            </h2>
          </div>
          <div class="col">
            <img src="{{ asset('imagen/mac-general.png') }}" alt="" width="200" >
            
          </div>
        </div>
      </div>
    </header>

    <section id="main">
      
        @yield('externom')

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
    $("#loading-ext").css("display", "none");
    
});



</script>

@yield('ext-script')

</body>
</html>