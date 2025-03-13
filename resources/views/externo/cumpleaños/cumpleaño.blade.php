@extends('layouts.externo2')

@section('head')

<style>
    .dat-mid{
      display: flex;
      justify-content: center;
      justify-items: center;
      justify-self: center;
      align-content: center;
      align-items: center;
      align-self: center;
    }

    .cum-card{
      margin-bottom: 2em;
    }

    .card-img-top{
      background-color: aliceblue;
      width: 150px;
      height: 150px;

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

    .nobtn:hover{
        text-decoration: underline; 
    }

    /******************************* CUMPLEAÑOS ***********************************************/

    canvas{display:block}
    h1 {
      position: absolute;
      top: 20%;
      left: 50%;
      transform: translate(-50%, -50%);
      color: #000;
      font-family: "Source Sans Pro";
      font-size: 5em;
      font-weight: 900;
      -webkit-user-select: none;
      user-select: none;
    }

    .img-cumpleaños{
      max-width: 100%;
      /* border: 1px solid red; */
      display: flex;
      justify-content: center;
    }

    .img-cumpleaños>img{
      /* border: 1px solid red; */
      width: 50%;
    }

</style>
    
@endsection

@section('title-head')
    MODULO DE CUMPLEAÑOS <br />MAC
@endsection

@section('externom')

<div class="container mt-4" id="bus-dato">
    <div class="carp">
      <div class="card col-sm-8 col-12 col-lg-12 col-md-12">
        <div class="card-header">
          
          <center><h2>Ingresar al centro MAC que  desea visitar</h2></center>
        </div>
        {{-- <div class="card-body">
          <p >Para un registro correcto revisar la guia de usuario, click en Descargar Guia de Usuario</p>
        </div> --}}
      </div>          
    </div>
</div>  
  

  <div class="container" id="bus-dato-search">
    <div class="carp">
      <div class="card col-sm-8 col-12 col-lg-12 col-md-12">
        <div class="card-header">
          <h2>Seleccionar centro MAC:</h2>
        </div>
        <form action="" enctype="multipart/form-data">
          <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />
        <div class="card-body">
          <div class="carp">
            <div class="form-p">
              <div class="row col-sm-12 buut">
                
                
                <div class="mb-3 ">
                    <div class="row col-12 dat-mid">
                        {{-- <label for="IdTipoPersona" class="control-label">Ingresar el centro MAC y luego la entidad <span class="text-danger fw-bolder">(*)</span> </label> --}}
                        <div class="col-sm-5">
                            <select name="idmac" id="idmac" class="form-select col-6">
                                <option value="" isabled selected>-- Seleccione un Centro MAC --</option>
                                @foreach ($macs as $mac)
                                    <option value="{{ $mac->IDCENTRO_MAC }}">{{ $mac->NOMBRE_MAC }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <button class="btn btn-primary" type="button" id="btn-ingresodoc" onclick="BtnNumDoc()">Buscar</button>
                        </div>
                    </div>
                </div>
              </div>
            </div>
          </div>

        </div>            
      </div>
    </div>
  </div>

  <div class="container" id="find-dato">

  </div>
    
@endsection

@section('Scripts')
<script>



function Validar_Archivos_NUMDOC(){

  var r = { flag: true, mensaje: "" }

  if ($("#idmac").val() == "" ) {
      r.flag = false;
      r.mensaje = "Debe completar los campos!";
      return r;
  }

  return r;
}

function BtnNumDoc() {
  console.log("sad");

  r = Validar_Archivos_NUMDOC();
  // console.log(r);
  if(r.flag){
      document.getElementById("btn-ingresodoc").innerHTML = '<i class="fa fa-spinner fa-spin"></i> Buscando... ';
      document.getElementById("btn-ingresodoc").disabled = true;
      $("#loading-ext").css("display", "flex");

      var formData = new FormData();
      formData.append("idmac", $("#idmac").val());
      formData.append("_token", $("input[name=_token]").val());
      console.log(formData);
      $.ajax({
          type: "POST",
          dataType: "html",
          cache: false,
          url: "{{ route('externo.cumpleaños.cumpleaño_validar') }}",
          data: formData,
          processData: false,
          contentType: false,
          success: function(response){
              console.log(response)
              document.getElementById("btn-ingresodoc").innerHTML = 'Buscar';
              document.getElementById("btn-ingresodoc").disabled = false;
              $("#bus-dato").css("display", "none");
              $("#loading-ext").css("display", "none");
              $("#bus-dato-search").css("display", "none");

              $("#find-dato").html(response);


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


function Regresar(){

    $( ".container" ).load(window.location.href + " .container" ); 

}


</script>
@endsection