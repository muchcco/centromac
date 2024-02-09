<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Ingresar un nuevo centro MAC</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="alerta">
            </div> 
            <form class="form-horizontal">
                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />

                <h5>Dato General</h5>
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label" for="centro_mac">Centro MAC</label>                           
                            <div class="input-group">
                                <div class="input-group-text">MAC - </div>
                                <input type="text" class="form-control" id="centro_mac" required onkeyup="isMayus(this)">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label" for="fecha_apertura">Fecha de apertura</label>
                            <input type="date" class="form-control" id="fecha_apertura" required="">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label" for="fecha_inaguracion">Fecha de inaguración</label>
                            <input type="date" class="form-control" id="fecha_inaguracion" required="">
                        </div>
                    </div>
                </div>

                <h5>Datos de la Ubicación</h5>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label" for="username">Departamento</label>
                            <select name="departamento" id="departamento" class="form-select">
                                <option value="0" disabled selected>-- SELECCIONE UNA OPCION --</option>
                                @foreach ($departamento as $dep)
                                    <option value="{{$dep->IDDEPARTAMENTO}}"> {{$dep->NAME_DEPARTAMENTO}} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label" for="useremail">Provincia</label>
                            <select name="provincia" id="provincia" class="form-select">
                                <option value="0" disabled selected>-- SELECCIONE UNA OPCION --</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label" for="useremail">Distrito</label>
                            <select name="distrito" id="distrito" class="form-select">
                                <option value="0" disabled selected>-- SELECCIONE UNA OPCION --</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label" for="ubicacion">Dirección</label>
                            <input type="text" class="form-control" id="ubicacion" required onkeyup="isMayus(this)">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm" onclick="btnStoreMac()">Guardar</button>
        </div>
    </div>
</div>

<script>

$(document).ready(function() {
    /* ======================================================================================*/
      /* ===================== DIRECCION ==========================================*/

      $('#departamento').on('change', function() {
          var departamento_id = $(this).val();
          if (departamento_id) {
              var url = "{{ route('provincias', ['departamento_id' => ':departamento_id']) }}"; // Cambia 'tipo' a 'tipoid'
              url = url.replace(':departamento_id', departamento_id);

              $.ajax({
                  type: 'GET',
                  url: url, // Utiliza la URL generada con tipo
                  success: function(data) {
                      $('#provincia').html(data);
                  }
              });
          } else {
              $('#provincia').empty();
          }
      });

      $('#provincia').on('change', function() {
          var provincia_id = $(this).val();
          if (provincia_id) {
              var url = "{{ route('distritos', ['provincia_id' => ':provincia_id']) }}"; // Cambia 'tipo' a 'tipoid'
              url = url.replace(':provincia_id', provincia_id);

              $.ajax({
                  type: 'GET',
                  url: url, // Utiliza la URL generada con tipo
                  success: function(data) {
                      $('#distrito').html(data);
                  }
              });
          } else {
              $('#distrito').empty();
          }
      });
});


</script>