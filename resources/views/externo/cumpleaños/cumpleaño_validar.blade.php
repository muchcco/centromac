<div class="carp">
    <div class="card col-sm-8 col-12 col-lg-12 col-md-12">
      <div class="card-header">
        <h2>Centro MAC - {{  $mac->NOMBRE_MAC }}  <strong>(Próximos cumpleaños)</strong> para el año {{ Carbon\Carbon::now()->format('Y') }}  </h2>
      </div>
      <form action="" enctype="multipart/form-data">
        <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />
      <div class="card-body">
        <button class="nobtn" onclick="Regresar()">Regresar</button>
        <div class="carp">
          <div class="form-p">
            <div class="row col-sm-12 buut">
              
              
              <div class="mb-3 ">
                  <div class="row col-12"> 
                      
                      @foreach ($cumpleanos_por_mes as $mes => $personas)
                          @php $mes_latino = isset($meses_latino[$mes]) ? $meses_latino[$mes] : $mes; @endphp
                          <div class="alert alert-primary" role="alert">
                            {{ $mes_latino }}
                          </div>

                          @foreach ($personas as $p)

                              <div class="col-3 col-sm-3 col-md-3 cum-card">
                                <div class="card" style="width: 18rem;">
                                  @if ($p->NOMBRE_ARCHIVO == null)
                                      @if ($p->SEXO == '1')
                                        <img src="{{ asset('imagen/user/user-h.png') }}" class="card-img-top" alt="Imagen Hombre">
                                      @elseif ($p->SEXO == '2')
                                        <img src="{{ asset('imagen/user/user-m.png') }}" class="card-img-top" alt="Imagen Mujer">
                                      @else
                                        <img src="..." class="card-img-top" alt="...">
                                      @endif
                                      
                                  @else
                                      @php
                                          // Obtener la URL de las fotos definida en el .env (PHOTO_URL)
                                          $photoUrl = $url_photo->VALOR;
                                          // Asegúrate de que termine con una barra
                                          $photoUrl = rtrim($photoUrl, '/') . '/';
                                          // Construir la URL completa: se asume que las fotos se organizan en carpetas según el número de documento
                                          $im = $photoUrl . $p->NUM_DOC . '/' . $p->NOMBRE_ARCHIVO;
                                      @endphp
                                      
                                      <img src="{{ $im }}" alt="Foto personal" class="img-cumpleaños">                                                          
                                  @endif
                                  
                                  <div class="card-body">
                                    <h5 class="card-title text-uppercase">{{ $p->NOMBRES }}</h5>
                                    {{-- <p class="card-text">.</p> --}}
                                  </div>
                                  <ul class="list-group list-group-flush">
                                    <li class="list-group-item">Entidad: {{ $p->NOMBRE_ENTIDAD }}</li>
                                    <li class="list-group-item">Fecha: {{ $p->prox_cumpleanos->format('d/m/Y')}}</li>
                                  </ul>
                                </div>
                              </div>
                          @endforeach
                      @endforeach
                  </div>
              </div>
            </div>
          </div>
        </div>

      </div>            
    </div>
  </div>


<script>
$(document).ready(function() {
  console.log("{{ $url_photo->VALOR }}");
});

</script>