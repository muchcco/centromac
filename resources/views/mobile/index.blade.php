@extends('layouts.mobile')

@section('estilo')
    
  <style>
    .dat_mac{
      border: 1px solid #7e7e7e;
      border-radius: .5em;
      box-shadow: 1px 1px  #7e7e7e;
      background: #fff;
      padding: .5em;
    }

    .text-list-ent{
      display: flex;
      flex-direction: column;
    }

    .list_empl{
      display: flex;
      flex-direction: column;
      
      list-style-position: outside !important;
      /* padding-right: 1em; */
    }

    .text-simpl{
      display: flex;
      flex-direction: row;
      justify-content: space-between;
      border-top: 1px solid #919191;
      border-bottom: 1px solid #919191;
    }

    .text-list-ent{
      display: flex;
      justify-content: start;
      align-items: center;
    }

    .mostrar{
      display: none;
    }

    .text-min{
      font-size: 8px;
    }

    .text-min-2{
      font-size: 12px;
    }

    .type_dato {
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .ul-ent{
      /* border: 1px solid red; */
      margin: 0 !important;
      padding: 0 !important;
    }

  </style>


@endsection

@section('main')
  <header class="m-3">
    <div class="row head">
      <div class="t-bienvenido col-8">
        <h2 class="text-white">Hola, bienvenido a los centros MAC</h2>
      </div>
      <div class="t-loggin col-4">
        <button class="nobtn">
          <svg xmlns="http://www.w3.org/2000/svg" height="24" width="21" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2023 Fonticons, Inc.--><path fill="#ffffff" d="M304 128a80 80 0 1 0 -160 0 80 80 0 1 0 160 0zM96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM49.3 464H398.7c-8.9-63.3-63.3-112-129-112H178.3c-65.7 0-120.1 48.7-129 112zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3z"/></svg>
        </button>
      </div>
    </div>
  </header>

  <section class="m-3 ">
    <div class="row">
      
      <p class="text-white">Que centro MAC desea revisar?</p>
      <div class=" col-12">
        <div class="form-group">
          
          <select name="idcentro_mac" id="idcentro_mac" class="form-select col-12" onchange="PickMac(this.value)">
            <option value="0" selected > -- Seleccione un MAC -- </option>
            @forelse ($macs as $mac)
                <option value="{{ $mac->IDCENTRO_MAC }}" >MAC - {{ $mac->NOMBRE_MAC }}</option>
            @empty
                
            @endforelse
          </select>
          
        </div>
      </div>
    </div>
  </section>


  <div class="m-3 dat_mac mostrar" id="centro_mac">
    <div id="cargando_dat"></div>
  </div>
@endsection


@section('script')
    
<script>

$(document).ready(function() {
    

});


function PickMac(idcentro_mac) {
        if (idcentro_mac == "0") {
            $("#centro_mac").addClass("mostrar");
        } else {
            $("#centro_mac").removeClass("mostrar");

            $.ajax({
                type: 'GET',
                url: "{{ route('mobile.entidad_dat') }}",
                data: {idcentro_mac: idcentro_mac},
                beforeSend: function () {
                    $("#cargando_dat").html('<i class="fa fa-spinner fa-spin"></i> Un momento por favor, retornando datos... ');
                },
                success: function (data) {
                    $('#centro_mac').html(data);
                    $("#cargando_dat").html(""); // Clear the loading message after success
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                    $("#cargando_dat").html("Error al cargar los datos, vuelva a intentar más tarde");
                }
            });
        }
    }


</script>

@endsection