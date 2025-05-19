@extends('layouts.externo2')

@section('ext-styles')


@endsection

@section('title')
    CONSULTA DE USUARIOS EN ESPERA - NOVOSGA
@endsection

@section('externom')
  

<div class="container card">
  <div class="card-header text-bg-dark">
    <div class="text-center tex">Revisión de cuidadanos por entidad</div>
  </div>
</div>

<br />


{{-- <div class="container card">
  <div class="card-header text-bg-info">
    <div class="tex">Filtros</div>
  </div>
  <div class="card-body">
      
  </div>
  <div class="card-footer">
    <div class="col-2">
      <button class="btn btn-primary">
        Añadir nueva entidad
      </button>
    </div>   
  </div>
</div> --}}


<div class="container card">
  <div class="card-header text-bg-success">
    <div class="tex">Resultados</div>
  </div>
  <div class="card-body">
    <div class="form-p">
      <div class="row col-sm-12 buut">
        
        <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />
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
                    <button class="btn btn-primary" type="button" id="btn-ingresodoc" onclick="BtnModal()">Buscar</button>
                </div>
            </div>
        </div>
        <div class="alert alert-warning d-flex align-items-center col-11" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img" aria-label="Warning:">
              <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
            </svg>
            <div>
              Ingrese su entidad, para poder ver los cuidadanos que tiene en espera.
            </div>
        </div>
      </div>
    </div>
  </div>
</div>


{{-- Ver Modales --}}
<div class="modal fade" id="modal_show_modal" tabindex="-1" role="dialog" ></div>
@endsection

@section('ext-script')



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
              url: "{{ route('validar_entidad') }}",
              data: formData,
              processData: false,
              contentType: false,
              success: function(response){
                  console.log(response)
                  // document.getElementById("btn-ingresodoc").innerHTML = 'Buscar';
                  document.getElementById("btn-ingresodoc").disabled = false;
                  var identidad = response.NOMBRE_NOVO;
  
                  console.log(identidad)
  
                  var URLd = "{{ route('entidad_cola', [ 'identidad' => ':identidad']) }}"
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

  <script>
    function BtnModal(){
      const idmac     = $('#idmac').val();
      const identidad = $('#identidad').val();
      const _token    = $('#_token').val();

      if(!idmac || !identidad){
        return Swal.fire({
          icon: 'error',
          text: 'Debe seleccionar Centro MAC y Entidad',
          confirmButtonText: 'Aceptar'
        });
      }
      console.log(identidad);

      $.ajax({
          type:'post',
          url: "{{ route('vista_md') }}",
          dataType: "json",
          data:{"_token": "{{ csrf_token() }}", idmac : idmac, identidad: identidad},
          success:function(data){
              $("#modal_show_modal").html(data.html);
              $("#modal_show_modal").modal('show');
          }
      });
    }

    function btnStoreUsuario() {
      // 1) Recoger los IDs marcados (o si sólo es un select, su valor)
      const seleccionados = [];
      $('input.perfil-checkbox:checked').each(function() {
        seleccionados.push(this.value);
      });
      if (!seleccionados.length) {
        return Swal.fire({ icon: 'warning', text: 'Debes seleccionar al menos un perfil' });
      }

      // 2) Concatenar sin paréntesis para tu URL: "65,157,336"
      const cadenaPerfiles = seleccionados.join(',');

      // 3) Plantilla de URL generada por Blade, con placeholder :identidad
      const urlTemplate = "{{ route('entidad_cola', ['identidad' => ':identidad']) }}";

      // 4) Reemplaza correctamente el placeholder por la cadena real
      const url = urlTemplate.replace(':identidad', encodeURIComponent(cadenaPerfiles));

      // 5) Redirige el navegador
      window.location.href = url;
    }

  </script>

@endsection