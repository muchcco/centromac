@extends('layouts.mobile')

@section('main')

<header class="m-3">
    <div class="row head">
      <div class="t-bienvenido col-8">
        <a href="{{ url()->previous() }}">
            <svg xmlns="http://www.w3.org/2000/svg" height="16" width="10" viewBox="0 0 320 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2023 Fonticons, Inc.--><path fill="#ffffff" d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l192 192c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L77.3 256 246.6 86.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-192 192z"/></svg>
        </a>
      </div>
      <div class="t-loggin col-4">
        <button class="nobtn">
          <svg xmlns="http://www.w3.org/2000/svg" height="24" width="21" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2023 Fonticons, Inc.--><path fill="#ffffff" d="M304 128a80 80 0 1 0 -160 0 80 80 0 1 0 160 0zM96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM49.3 464H398.7c-8.9-63.3-63.3-112-129-112H178.3c-65.7 0-120.1 48.7-129 112zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3z"/></svg>
        </button>
      </div>
    </div>
  </header>

  <section class="m-3">
    <div class="row">
        <center class="text-white">Bienvenido al Centro MAC {{ $mac->NOMBRE_MAC }} </center>
        <p class="text-white mt-2">Entidad: {{ $entidad->NOMBRE_ENTIDAD }}</p>
    </div>
  </section>

  <div class="m-3 dat_mac" id="centro_mac">
    <div class="row">
      <div class="col-12">
        <div class="card text-center">
          <div class="card-header">
            Estado de la Entidad
          </div>
          <div class="card-body">
            <p class="card-text text-start">El asesor se encuentra presente en el centro MAC.</p>
            <p class="card-text text-start">Modulo(s) de Atención: <span class="badge bg-info text-wrap"> {{ $ent_mac->MODULO === NULL ? ' - ' : $ent_mac->MODULO }} </span></p>
            <p class="card-text text-start">
              Hora de refrigerio: 
              @if ($ent_mac->REFRIGERIO_INT == '1')
                <span class="badge bg-info text-wrap"> {{ $ent_mac->VALOR }}</span>
              @else
                <span class="badge bg-info text-wrap">Horario corrido</span>
              @endif
            </p>
            <p class="card-text text-start">Atiende hasta las: <span class="badge bg-info text-wrap">05:00 pm</span></p>
            <p> </p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="m-3 dat_mac" id="centro_mac">
    <div class="row">
      <div class="col-12">
        <div class="card text-center">
          <div class="card-header">
            Servicios brindados
          </div>
          <div class="card-body">
            <div class="accordion accordion-flush" id="accordionFlushExample">
              @foreach ($serv_m_e as $serv)

                <div class="accordion-item">
                  <h2 class="accordion-header" id="flush-heading{{ $serv->IDENT_SERV }}">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse{{ $serv->IDENT_SERV }}" aria-expanded="false" aria-controls="flush-collapse{{ $serv->IDENT_SERV }}">
                      {{ $serv->NOMBRE_SERVICIO }}
                    </button>
                  </h2>
                  <div id="flush-collapse{{ $serv->IDENT_SERV }}" class="accordion-collapse collapse" aria-labelledby="flush-heading{{ $serv->IDENT_SERV }}" data-bs-parent="#accordionFlushExample">
                    <p class="card-text text-start">Costo: <span>{{ $serv->COSTO_SERV }}</span></p>
                    <p class="card-text text-start">Requisitos:
                       <span>
                         <textarea name="" id="" cols="30" rows="10" class="form-control" disabled>{{ $serv->REQUISITO_SERVICIO }}</textarea>
                      </span>
                      </p>
                  </div>
                </div>
              @endforeach   
            </div>
          </div>
          <div class="card-footer text-muted">
            2 days ago
          </div>
        </div>
      </div>
    </div>
  </div>
    
@endsection