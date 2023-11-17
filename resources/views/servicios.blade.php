<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registro de Asesores</title>
    <link href="{{ asset('https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css')}}" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="{{('css/app2.css')}}">
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
                <h2 style="text-align: center">SERVICIOS BRINDADOS EN LOS - CENTROS MAC</h2>
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
              <center><h2>Revisión de Servicios por entidad</h2></center>
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
              <h2>Datos de la entidad a consultar:</h2>
            </div>
            <form action="" enctype="multipart/form-data">
              <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />
            <div class="card-body">
              <div class="carp">
                <div class="form-p">
                  <div class="row col-sm-12 buut">
                    
                    
                    <div class="mb-3">
                        <div class="row col-12">
                            <label for="IdTipoPersona" class="control-label">Ingresar el centro MAC y luego la entidad <span class="text-danger fw-bolder">(*)</span> </label>
                            <div class="col-sm-5">
                                <select name="idmac" id="idmac" class="form-select col-6">
                                    <option value="" isabled selected>-- Seleccione un Centro MAC --</option>
                                    @foreach ($macs as $mac)
                                        <option value="{{ $mac->IDCENTRO_MAC }}">{{ $mac->NOMBRE_MAC }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-5">
                                <select name="identidad" id="identidad" class="form-select">
                                    <option value="" disabled selected>-- Seleccione un centro mac --</option>
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <button class="btn btn-primary" type="button" id="btn-ingresodoc" onclick="BtnNumDoc()">Buscar</button>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-warning d-flex align-items-center col-11" role="alert">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img" aria-label="Warning:">
                          <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                        </svg>
                        <div>
                          Ingrese su entidad, para poder ver los servicios que brinda.
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




<!-- jquery file upload js -->
{{-- <script src="{{ asset('assets/pages/jquery.filer/js/jquery.filer.min.js')}}"></script>
<script src="{{ asset('assets/pages/filer/custom-filer.js')}}" type="text/javascript"></script>
<script src="{{ asset('assets/pages/filer/jquery.fileuploads.init.js')}}" type="text/javascript"></script> --}}

{{-- <script src="{{ asset('js/bundle-boots.min.js')}}"></script>
<script src="{{ asset('js/bundle-boot.min.js')}}"></script>
<script src="{{ asset('js/pop.js')}}"></script> --}}

<script src="{{ asset('js/toastr.min.js')}}"></script>



<script>

$(document).ready(function() {

    /* ===================== ENTIDAD ASOCIADA ==========================================*/
      $('#idmac').on('change', function() {
          var idmac = $(this).val();
          if (idmac) {
              var url = "{{ route('centro_mac', ['idcentro_mac' => ':idcentro_mac']) }}"; // Cambia 'tipo' a 'tipoid'
              url = url.replace(':idcentro_mac', idmac);

              $.ajax({
                  type: 'GET',
                  url: url, // Utiliza la URL generada con tipo
                  success: function(data) {
                      $('#identidad').html(data);
                  }
              });
          } else {
              $('#identidad').empty();
          }
      });

    /* ======================================================================================*/
  
});

function Validar_Archivos_NUMDOC(){

    var r = { flag: true, mensaje: "" }

    if ($("#idmac").val() == "" || $("#identidad").val() == "" ) {
        r.flag = false;
        r.mensaje = "Debe completar los campos!";
        return r;
    }

    return r;
}

function BtnNumDoc() {
    console.log("sad");

    r = Validar_Archivos_NUMDOC();
    console.log(r);
    if(r.flag){
        document.getElementById("btn-ingresodoc").innerHTML = '<i class="fa fa-spinner fa-spin"></i> Buscando... ';
        document.getElementById("btn-ingresodoc").disabled = true;

        var formData = new FormData();
        formData.append("identidad", $("#identidad").val());
        formData.append("idmac", $("#idmac").val());
        formData.append("_token", $("input[name=_token]").val());
        console.log(formData);
        $.ajax({
            type: "POST",
            dataType: "json",
            cache: false,
            url: "{{ route('validar_servicio') }}",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response){
                console.log(response)
                document.getElementById("btn-ingresodoc").innerHTML = 'Buscar';
                document.getElementById("btn-ingresodoc").disabled = false;
                var identidad = response.IDENTIDAD;
                var idcentro_mac = response.IDMAC;

                var URLd = "{{ route('list_serv', ['idcentro_mac' => ':idcentro_mac', 'identidad' => ':identidad']) }}"
                .replace(':idcentro_mac', idcentro_mac)
                .replace(':identidad', identidad);

                window.location.href = URLd;

            },
            error: function(error){
                //location.reload();
                console.log("error");
                console.log(error);
            }
        });
        
    }else{
        Swal.fire({
            icon: "error",
            text: r.mensaje,
            confirmButtonText: "Aceptar"
        })
    }

}

</script>


</body>
</html>