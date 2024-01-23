<div class="modal-dialog modal-lg" role="document" style="max-width: 80%">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Editar felicitación </h4>
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
                            <select name="tipo_doc" id="tipo_doc" class="form-select" disabled>
                                @forelse ($tip_doc as $doc)
                                    <option value="{{ $doc->IDTIPO_DOC }}" {{ $felicitacion->IDTIPO_DOC == $doc->IDTIPO_DOC ? 'selected' : '' }} >{{ $doc->TIPODOC_ABREV }}</option>
                                @empty
                                    <option value="e"> No hay datos disponibles </option>
                                @endforelse
                            </select>
                        </div>
                        <div class="col-lg-9 col-6 mb-2 mb-lg-0">
                            <label class="form-label" for="">Número de Documento</label>
                            <input type="text" class="form-control" id="num_doc" name="num_doc" value="{{ $felicitacion->R_NUM_DOC }}" disabled>
                        </div>
                    </div>
                </div><!--end form-group-->
                <div class="form-group">
                    <div class="row">
                        <div class="col-lg-3 col-6 mb-2 mb-lg-0">
                            <label class="form-label" for="nombre">Nombres</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="{{ $felicitacion->R_NOMBRE }}" disabled>
                        </div><!--end col-->
                        <div class="col-lg-3 col-6 mb-2 mb-lg-0">
                            <label class="form-label" for="ape_pat">Apellido Paterno</label>
                            <input type="text" class="form-control" id="ape_pat" name="ape_pat" value="{{ $felicitacion->R_APE_PAT }}" disabled>
                        </div><!--end col-->
                        <div class="col-lg-3 col-6">
                            <label class="form-label" for="ape_mat">Apellido Materno</label>
                            <input type="text" class="form-control" id="ape_mat" name="ape_mat" value="{{ $felicitacion->R_APE_MAT }}" disabled>
                        </div><!--end col-->
                        <div class="col-lg-3 col-6">
                            <label class="form-label" for="fecha">Fecha (Que registra) <span class="bandejTool" data-tippy-content="La fecha ha ingresar tiene que estar reflejada con la fecha que se ingreso la felicitación"> <svg xmlns="http://www.w3.org/2000/svg" height="16" width="16" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path fill="#f50f0f" d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/></svg> </span> </label>
                            <input type="date" class="form-control" id="fecha" name="fecha" value="{{ $felicitacion->R_FECHA }}">
                        </div><!--end col-->                                                        
                    </div><!--end row-->
                </div><!--end form-group-->
                <div class="form-group">
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <label class="form-label" for="correo">Correo</label>
                            <input type="text" class="form-control" id="correo" name="correo" placeholder="Correo" value="{{ $felicitacion->R_CORREO }}">
                        </div><!--end col-->
                        <div class="col-lg-3 col-6">
                            <label class="form-label" for="pro-end-date">Entidad</label>
                            <select class="form-select" id="entidad" name="entidad">
                                <option value="" disabled selected>-- Seleccione una opción --</option>
                                @forelse ($entidad as $e)
                                    <option value="{{ $e->IDENTIDAD }}" {{ $felicitacion->IDENTIDAD == $e->IDENTIDAD ? 'selected' : '' }} >{{ $e->ABREV_ENTIDAD }}</option>
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
                                    <option value="{{ $a->IDPERSONAL }}" {{ $felicitacion->IDPERSONAL == $a->IDPERSONAL ? 'selected' : '' }}>{{ $a->APE_PAT }} {{ $a->APE_MAT }}, {{ $a->NOMBRE }}</option>
                                @empty
                                    <option value="e"> No hay datos disponibles </option>
                                @endforelse>                                                                
                            </select>
                        </div><!--end col-->
                    </div><!--end row-->
                </div><!--end form-group-->
                <div class="form-group">
                    <label class="form-label" for="descripcion">Descripción</label>
                    <textarea class="form-control" rows="5" id="descripcion" name="descripcion" placeholder="Escribir aqui..">{{ $felicitacion->R_DESCRIPCION }}</textarea>
                </div><!--end form-group-->
                <div class="form-group">
                    <label class="form-label" for="">Ingrese el archivo de felicitación de forma escaneada</label>
                    <input type="file" class="form-control" id="file_doc" name="file_doc">
                </div>
                <div class="form-group">
                    <label class="form-label" for="">Archivo subido</label>
                    @if ($felicitacion->R_ARCHIVO_NOM == null)
                    <div class="alert custom-alert custom-alert-warning icon-custom-alert shadow-sm fade show d-flex justify-content-between" role="alert">  
                        <div class="media">
                            <i class="la la-exclamation-triangle alert-icon text-warning align-self-center font-30 me-3"></i>
                            <div class="media-body align-self-center">
                                <h5 class="mb-1 fw-bold mt-0">Importante</h5>
                                <span>No hay datos disponibles. El archivo fue eliminado o hay problemas al visualizarlo, por favor cargue nuevamente el documento y actualize todos los cambios</span>
                            </div>
                        </div>                                  
                        <button type="button" class="btn-close align-self-center" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @else
                        <table class="table table-bordered" id="idtabla_archivo">
                            <tr>
                                <td class="">{{ $felicitacion->R_ARCHIVO_NOM }}</td>
                                <td class="text-center">
                                    <a href="{{ asset($felicitacion->R_ARCHIVO_RUT .'/'. $felicitacion->R_ARCHIVO_NOM) }}" target="_blank">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16" width="16" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path fill="#e90707" d="M0 64C0 28.7 28.7 0 64 0L224 0l0 128c0 17.7 14.3 32 32 32l128 0 0 144-208 0c-35.3 0-64 28.7-64 64l0 144-48 0c-35.3 0-64-28.7-64-64L0 64zm384 64l-128 0L256 0 384 128zM176 352l32 0c30.9 0 56 25.1 56 56s-25.1 56-56 56l-16 0 0 32c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-48 0-80c0-8.8 7.2-16 16-16zm32 80c13.3 0 24-10.7 24-24s-10.7-24-24-24l-16 0 0 48 16 0zm96-80l32 0c26.5 0 48 21.5 48 48l0 64c0 26.5-21.5 48-48 48l-32 0c-8.8 0-16-7.2-16-16l0-128c0-8.8 7.2-16 16-16zm32 128c8.8 0 16-7.2 16-16l0-64c0-8.8-7.2-16-16-16l-16 0 0 96 16 0zm80-112c0-8.8 7.2-16 16-16l48 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-32 0 0 32 32 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-32 0 0 48c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-64 0-64z"/></svg>
                                    </a>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger" onclick="EliminarArchivo('{{ $felicitacion->IDLIBRO_FELICITACION }}')">Eliminar</button>
                                </td>
                            </tr>
                        </table>
                    @endif
                    
                    <div id="alerta">
                    </div> 
                </div>
                
                
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm" onclick="btnEdit('{{ $felicitacion->IDLIBRO_FELICITACION }}')">Actualizar</button>
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

function EliminarArchivo(idfelicitacion) {
   
    $.ajax({
        type:'post',
        url: "{{ route('formatos.f_felicitaciones.eliminar_archivo') }}",
        dataType: "json",
        data:{"_token": "{{ csrf_token() }}", idfelicitacion : idfelicitacion},
        success:function(data){
            $( "#idtabla_archivo" ).load(window.location.href + " #idtabla_archivo" ); 
            ocument.getElementById('alerta').innerHTML = `<div class="alert custom-alert custom-alert-warning icon-custom-alert shadow-sm fade show d-flex justify-content-between" role="alert"><div class="media">
                                                                    <i class="la la-exclamation-triangle alert-icon text-warning align-self-center font-30 me-3"></i>
                                                                    <div class="media-body align-self-center">
                                                                        <h5 class="mb-1 fw-bold mt-0">Importante</h5>
                                                                        <span> No hay datos disponibles.</span>
                                                                    </div>
                                                                </div></div>`;
        }
    });

}
    
</script>