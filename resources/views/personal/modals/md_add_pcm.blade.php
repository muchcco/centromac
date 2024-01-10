<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Añadir nuevo personal</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="alerta">
            </div> 
            <h5>Agregar datos</h5>
            <form class="form-horizontal">
                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />
                <div class="row mb-3">
                    <label  class="col-3 col-form-label">Número de Documento</label>
                    <div class="col-9">
                        <input type="text" class="form-control" name="dni" id="dni" placeholder="Número de Documento" onkeypress="return isNumber(event)">
                        <span class="text-center text-danger" id="mensaje_error_dni"></span>
                    </div>
                </div>
                <div class="row mb-3">
                    <label  class="col-3 col-form-label">Nombres</label>
                    <div class="col-9">
                        <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Nombres" onkeyup="isMayus(this)">
                    </div>
                </div>
                <div class="row mb-3">
                    <label  class="col-3 col-form-label">Apellido Paterno</label>
                    <div class="col-9">
                        <input type="text" class="form-control" name="ap_pat" id="ap_pat" placeholder="Apellido Paterno" onkeyup="isMayus(this)">
                    </div>
                </div>
                <div class="row mb-3">
                    <label  class="col-3 col-form-label">Apellido Materno</label>
                    <div class="col-9">
                        <input type="text" class="form-control" name="ap_mat" id="ap_mat" placeholder="Apellido Materno" onkeyup="isMayus(this)">
                    </div>
                </div>
                <div class="row mb-3">
                    <label  class="col-3 col-form-label">Cargo</label>
                    <div class="col-9">
                        <select id="cargo" name="cargo" class="form-control">
                            <option disabled selected>-- Seleccione una opción --</option>
                            @foreach($cargos as $c)
                                <option value="{{ $c->IDCARGO_PERSONAL }}">{{ $c->NOMBRE_CARGO }}</option>
                            @endforeach     
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <label  class="col-3 col-form-label">Correo</label>
                    <div class="col-9">
                        <input type="text" class="form-control" name="correo" id="correo" placeholder="Correo" >
                    </div>
                </div>
                <div class="row mb-3">
                    <label  class="col-3 col-form-label">Fecha de nacimiento</label>
                    <div class="col-9">
                        <input type="date" class="form-control" name="fech_nac" id="fech_nac" >
                    </div>
                </div>
                <div class="row mb-3">
                    <label  class="col-3 col-form-label">Entidad</label>
                    <div class="col-9">
                        <select id="entidad" name="entidad" class="form-control">
                            <option disabled selected>-- Seleccione una opción --</option>
                            @forelse ($entidad as $e)
                                <option value="{{ $e->IDENTIDAD }}">{{ $e->NOMBRE_ENTIDAD }}</option>                                
                            @empty
                                <option value="">No hay conexón</option>
                            @endforelse
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <label  class="col-3 col-form-label">Sexo</label>
                    <div class="col-9">
                        <select id="sexo" name="sexo" class="form-control">
                            <option value="0">Mujer</option>
                            <option value="1">Hombre</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <label  class="col-3 col-form-label">Teléfono</label>
                    <div class="col-9">
                        <input type="text" class="form-control" name="telefono" id="telefono" placeholder="Teléfono" onkeypress="return isNumber(event)">
                    </div>
                </div>  
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm" onclick="btnStorePcm()">Guardar</button>
        </div>
    </div>
</div>

<script>

    $(document).ready(function() {
        $('#dni').on('change', function(){
            var dni = $(this).val();
    
            $.ajax({
                type: 'POST',
                url: "{{ route('buscar_dni') }}", 
                data: {"_token": "{{ csrf_token() }}", dni: dni},
                success: function(response) {
                    // $('#subtipo').html(response);
                    console.log(response);
                    if(!(response.error)){
                        document.getElementById('mensaje_error_dni').textContent = '';
                        document.getElementById('nombre').value = response.nombres;
                        document.getElementById('ap_pat').value = response.apellidoPaterno;
                        document.getElementById('ap_mat').value = response.apellidoMaterno;
                    }else{
                        document.getElementById('nombre').value = '';
                        document.getElementById('mensaje_error_dni').textContent = response.data.error;
                    }
                    
                }
            });
        });
    });
    
    
    </script>