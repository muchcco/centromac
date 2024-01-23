<div class="modal-dialog modal-lg" role="document" style="max-width: 80%">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Añadir una nueva felicitación </h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="alert custom-alert custom-alert-warning icon-custom-alert shadow-sm fade show d-flex justify-content-between" role="alert">  
                <div class="media">
                    <i class="la la-exclamation-triangle alert-icon text-warning align-self-center font-30 me-3"></i>
                    <div class="media-body align-self-center">
                        <h5 class="mb-1 fw-bold mt-0">Importante</h5>
                        <span>Se debe ingresar los datos exactos al cual el cuidadano ingreso en el formato físico de su Felicitación</span>
                    </div>
                </div>                                  
                {{-- <button type="button" class="btn-close align-self-center" data-bs-dismiss="alert" aria-label="Close"></button> --}}
            </div>
            <form>
                <div class="form-group">
                    <div class="row">
                        <div class="col-lg-3 col-6 mb-2 mb-lg-0">
                            <label class="form-label" for="">Tipo de Documento</label>
                            <select name="tipo_doc" id="tipo_doc" class="form-select" onclick="CambioDoc()">
                                <option value="" disabled selected>-- Seleccione una opción --</option>
                                @forelse ($tip_doc as $doc)
                                    <option value="{{ $doc->IDTIPO_DOC }}">{{ $doc->TIPODOC_ABREV }}</option>
                                @empty
                                    <option value="e"> No hay datos disponibles </option>
                                @endforelse
                            </select>
                        </div>
                        <div class="col-lg-9 col-6 mb-2 mb-lg-0">
                            <label class="form-label" for="">Número de Documento</label>
                            <input type="text" class="form-control" id="num_doc" name="num_doc" placeholder="Ingrese el dúmero de documento" >
                            <span class="text-center text-danger" id="mensaje_error_dni"></span>
                        </div>
                    </div>
                </div><!--end form-group-->
                <div class="form-group">
                    <div class="row">
                        <div class="col-lg-3 col-6 mb-2 mb-lg-0">
                            <label class="form-label" for="nombre">Nombres</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombres">
                        </div><!--end col-->
                        <div class="col-lg-3 col-6 mb-2 mb-lg-0">
                            <label class="form-label" for="ape_pat">Apellido Paterno</label>
                            <input type="text" class="form-control" id="ape_pat" name="ape_pat" placeholder="Apellido Paterno">
                        </div><!--end col-->
                        <div class="col-lg-3 col-6">
                            <label class="form-label" for="ape_mat">Apellido Materno</label>
                            <input type="text" class="form-control" id="ape_mat" name="ape_mat" placeholder="Apellido Materno">
                        </div><!--end col-->
                        <div class="col-lg-3 col-6">
                            <label class="form-label" for="fecha">Fecha (Que registra) <span class="bandejTool" data-tippy-content="La fecha ha ingresar tiene que estar reflejada con la fecha que se ingreso la felicitación"> <svg xmlns="http://www.w3.org/2000/svg" height="16" width="16" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path fill="#f50f0f" d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/></svg> </span> </label>
                            <input type="date" class="form-control" id="fecha" name="fecha">
                        </div><!--end col-->                                                        
                    </div><!--end row-->
                </div><!--end form-group-->
                <div class="form-group">
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <label class="form-label" for="correo">Correo</label>
                            <input type="text" class="form-control" id="correo" name="correo" placeholder="Correo">
                        </div><!--end col-->
                        <div class="col-lg-3 col-6">
                            <label class="form-label" for="pro-end-date">Entidad</label>
                            <select class="form-select" id="entidad" name="entidad">
                                <option value="" disabled selected>-- Seleccione una opción --</option>
                                @forelse ($entidad as $e)
                                    <option value="{{ $e->IDENTIDAD }}">{{ $e->ABREV_ENTIDAD }}</option>
                                @empty
                                    <option value="e"> No hay datos disponibles </option>
                                @endforelse
                            </select>
                        </div><!--end col-->
                        <div class="col-lg-3 col-6">
                            <label class="form-label" for="pro-end-date">Asesor</label>
                            <select class="form-select" id="asesor" name="asesor">
                                <option value="" disabled selected>-- Seleccione una opción --</option>
                                @forelse ($asesor as $a)
                                    <option value="{{ $a->IDPERSONAL }}">{{ $a->APE_PAT }} {{ $a->APE_MAT }}, {{ $a->NOMBRE }}</option>
                                @empty
                                    <option value="e"> No hay datos disponibles </option>
                                @endforelse>                                                                
                            </select>
                        </div><!--end col-->
                    </div><!--end row-->
                </div><!--end form-group-->
                <div class="form-group">
                    <label class="form-label" for="descripcion">Descripción</label>
                    <textarea class="form-control" rows="5" id="descripcion" name="descripcion" placeholder="Escribir aqui.."></textarea>
                </div><!--end form-group-->
                <div class="form-group">
                    <label class="form-label" for="">Ingrese el archivo de felicitación de forma escaneada</label>
                    <input type="file" class="form-control" id="file_doc" name="file_doc">
                </div>
                
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm" onclick="btSave()">Guardar</button>
        </div>
    </div>
</div>

<script>

$(document).ready(function() {
    tippy(".bandejTool", {
        allowHTML: true,
        followCursor: true,
    });

    // Agrega un controlador de eventos para el cambio en el elemento con id 'num_doc'
   
});

function CambioDoc() {
    
    var doc = document.getElementById('tipo_doc').value;

    if (doc == '1') {
        // var dni = document.getElementById('num_doc').value;

        $('#num_doc').on('change', function(){
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
                        document.getElementById('ape_pat').value = response.apellidoPaterno;
                        document.getElementById('ape_mat').value = response.apellidoMaterno;
                    }else{
                        document.getElementById('nombre').value = '';
                        document.getElementById('mensaje_error_dni').textContent = response.data.error;
                    }
                    
                }
            });
        });
    } else {
        // Corrección: Cambia '$num_doc' a '#num_doc' y usa .val('') para establecer el valor al vacío
        $('#num_doc').val('');
    }
}
    
</script>