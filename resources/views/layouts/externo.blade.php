<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registro de Asesores</title>
    <link href="{{ asset('css/bootstrap/bootstrap.min.css')}}" rel="stylesheet" >    
    <link rel="stylesheet" href="{{ asset('css/app2.css')}}">
    <link rel="stylesheet" href="{{ asset('css/loading.css')}}">
    {{-- <!-- jquery file upload Frame work -->
        {{-- preoad button --}}
    <link rel="stylesheet" href="{{ asset('css/font-awesome.min.css')}}">

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

<script src="{{ asset('js/bootstrap/bootstrap.bundle.min.js')}}"></script>


<script src="{{ asset('js/toastr.min.js')}}"></script>


<script src="{{ asset('js\tipify5\popper.js')}}"></script>
<script src="{{ asset('js\tipify5\tippy.js')}}"></script>

<script>
$(document).ready(function() {
    $("#loading-ext").css("display", "none");
    
});



</script>

@yield('ext-script')

</body>
</html>