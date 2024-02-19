<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registro de Asesores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">    
    {{-- <!-- jquery file upload Frame work -->
    <link href="{{ asset('assets/pages/jquery.filer/css/jquery.filer.css')}}" type="text/css" rel="stylesheet">
    <link href="{{ asset('assets/pages/jquery.filer/css/themes/jquery.filer-dragdropbox-theme.css')}}" type="text/css" rel="stylesheet"> --}}
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.3/css/all.css"  integrity="sha384-SZXxX4whJ79/gErwcOYf+zWLeJdY/qpuqC4cAa9rOGUstPomtqpuNWT9wdPEn2fk" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/app2.css')}}">

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
    </style>

    <script>
       
    </script>

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
                <h2 style="text-align: center">FORMULARIO DE REGISTRO - SEDE {{ $personal->NOMBRE_MAC }} - PCM</h2>
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
              <center><h2>Ficha de Datos Servidor Público de Entidad de Servicio</h2></center>
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
              <h2>Datos de la Entidad:</h2>              
            </div>
            <form action="" enctype="multipart/form-data">
              <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />
            <div class="card-body">

              <div class="carp">
                
                <div class="form-p">
                  <a href="{{ url()->previous() }}" class="btn btn-danger mb-3 mt-4 ">Regresar</a>
                  <div class="row col-sm-6 buut">
                    <div class="mb-3">
                      <label for="IdTipoPersona" class="control-label">Nombre de la Entidad <span class="text-danger fw-bolder">(*)</span> </label>
                      <select name="identidad" id="identidad" class="form-select" disabled>
                        <option value="0" disabled selected>Seleccione el tipo de entidad</option>
                        @foreach ($entidad as $ent)
                            <option value="{{ $ent->IDENTIDAD }}" {{ $personal->IDENTIDAD == $ent->IDENTIDAD ? 'selected' : '' }} >{{ $ent->NOMBRE_ENTIDAD }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="row col-sm-5 buut">
                    <div class="mb-3">
                      <label for="IdTipoPersona" class="control-label">Sede <span class="text-danger fw-bolder">(*)</span> </label>
                      <input type="text" value="{{ $personal->NOMBRE_MAC }}" class="form-select" disabled>
                    </div>
                  </div>
                </div>
              </div>

            </div>            
          </div>
        </div>
      </div>

      <div class="container">
        <div class="carp">
          <div class="card col-sm-8">
            <div class="card-header">
              <h2>Datos Personales:</h2>
            </div>
            <div class="card-body">
              <div class="carp">
                <div class="form-p">
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="TDoc" class="control-label">Tipo de Documento <span class="text-danger fw-bolder">(*)</span></label>
                      <select name="idtipo_doc" id="idtipo_doc" class="form-select" aria-label="Tipo de Persona" maxlength="8" onchange="TipoDocumento(this.value);">
                        <option value="1" {{ $personal->IDTIPO_DOC == '1' ? 'selected' : '' }} >DNI</option>
                        <option value="2" {{ $personal->IDTIPO_DOC == '2' ? 'selected' : '' }} >Carnet de Extranjería</option>
                        <option value="3" {{ $personal->IDTIPO_DOC == '3' ? 'selected' : '' }} >Pasaporte</option>
                      </select>
                    </div>
                  </div>
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="NDoc" class="control-label">Nro. Documento <span class="text-danger fw-bolder">(*)</span></label>
                      <input type="text" class="form-control" name="num_doc" id="num_doc" onkeypress="return isNumber(event)" value="{{ $personal->NUM_DOC }}" disabled>
                    </div>
                  </div>
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="NDoc" class="control-label">Sexo <span class="text-danger fw-bolder">(*)</span></label>
                      <select name="sexo" id="sexo" class="form-select">
                        <option value="1" {{ $personal->SEXO == '1' ? 'selected' : '' }} >Hombre</option>
                        <option value="0" {{ $personal->SEXO == '0' ? 'selected' : '' }} >Mujer</option>                        
                      </select>
                    </div>
                  </div>
                </div>
              </div>
              <div class="carp">
                <div class="form-p">
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="TDoc" class="control-label">Apellido Paterno <span class="text-danger fw-bolder">(*)</span></label>
                      <input type="text" class="form-control" name="ape_pat" id="ape_pat" value="{{ $personal->APE_PAT }}">
                    </div>
                  </div>
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="RSocial" class="control-label">Apellido Materno <span id="sMaterno" class="text-danger fw-bolder">(*)</span></label>
                      <input type="text" class="form-control" name="ape_mat" id="ape_mat" value="{{ $personal->APE_MAT }}">
                    </div>
                  </div>
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="RSocial" class="control-label">Nombres <span class="text-danger fw-bolder">(*)</span></label>
                      <input type="text" class="form-control" name="nombre" id="nombre" value="{{ $personal->NOMBRE }}">
                    </div>
                  </div>
                </div>
              </div>
              <div class="carp">
                <div class="form-p">
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="IdTipoPersona" class="control-label">Teléfono <span class="text-danger fw-bolder">(*)</span></label>
                      <input type="text" class="form-control" name="telefono" id="telefono" onkeypress="return isNumber(event)" value="{{ $personal->TELEFONO }}">
                    </div>
                  </div>
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="IdTipoPersona" class="control-label">Celular <span class="text-danger fw-bolder">(*)</span></label>
                      <input type="text" class="form-control" name="celular" id="celular" onkeypress="return isNumber(event)" value="{{ $personal->CELULAR }}">
                    </div>
                  </div>
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="IdTipoPersona" class="control-label">Correo <span class="text-danger fw-bolder">(*)</span></label>
                      <input type="text" class="form-control" name="correo" id="correo" value="{{ $personal->CORREO }}">
                    </div>
                  </div>
                </div>
              </div>
              <div class="carp">
                <div class="form-p">
                  <div class="row col-sm-12 buut">
                    <div class="mb-3">
                      <label for="IdTipoPersona" class="control-label">Dirección Actual <span class="text-danger fw-bolder">(*)</span></label>
                      <input type="text" class="form-control" name="direccion" id="direccion" value="{{ $personal->DIRECCION }}">
                    </div>
                  </div>
                </div>
              </div>
              <div class="carp">
                <div class="form-p">
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="IdTipoPersona" class="control-label">Departamento (actual)<span class="text-danger fw-bolder">(*)</span></label>
                      <select name="departamento" id="departamento" class="form-select">
                        <option value="0" disabled selected>-- SELECCIONE UNA OPCION --</option>
                            @foreach ($departamentos as $departamento)
                                <option 
                                  value="{{ $departamento->IDDEPARTAMENTO }}"
                                  @if (isset($dis_act))
                                    {{ $departamento->IDDEPARTAMENTO == $dis_act->IDDEPARTAMENTO ? 'selected' : '' }}     
                                  @endif                                  
                                   >
                                   {{ $departamento->NAME_DEPARTAMENTO }}
                                  </option>
                            @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="IdTipoPersona" class="control-label">Provincia (actual)<span class="text-danger fw-bolder">(*)</span></label>
                      <select class="form-select form-select-solid" name="provincia" id="provincia" >                          
                          @if (isset($dis_act))
                            <option value="{{ $dis_act->IDPROVINCIA }}"  selected>{{ $dis_act->NAME_PROVINCIA }}</option>
                          @else
                            <option value="0" disabled selected>-- SELECCIONE UNA OPCION --</option>
                          @endif
                      </select>
                    </div>
                  </div>
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="IdTipoPersona" class="control-label">Distrito (actual)<span class="text-danger fw-bolder">(*)</span></label>
                      <select class="form-select form-select-solid" name="distrito" id="distrito" >
                          @if (isset($dis_act))
                            <option value="{{ $dis_act->IDDISTRITO }}"  selected>{{ $dis_act->NAME_DISTRITO }}</option>
                          @else
                            <option value="0" disabled selected>-- SELECCIONE UNA OPCION --</option>
                          @endif
                      </select>
                    </div>
                  </div>
                </div>
              </div>
              <div class="raya"></div>
              <div class="carp">
                <div class="form-p">
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="IdTipoPersona" class="control-label">Fecha de Nacimiento <span class="text-danger fw-bolder">(*)</span></label>
                      <input type="date" class="form-control" name="fech_nacimiento" id="fech_nacimiento" value="{{ $personal->FECH_NACIMIENTO }}">
                    </div>
                  </div>
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="IdTipoPersona" class="control-label">Grupo SanguÍneo <span class="text-danger fw-bolder">(*)</span></label>
                      <input type="text" class="form-control" name="grupo_sanguineo" id="grupo_sanguineo"  value="{{ $personal->GRUPO_SANGUINEO }}">
                    </div>
                  </div>
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="IdTipoPersona" class="control-label">Talla<span class="text-danger fw-bolder">(*)</span></label>
                      <input type="text" class="form-control" name="pcm_talla" id="pcm_talla"  value="{{ $personal->PCM_TALLA }}">
                    </div>
                  </div>
                </div>
              </div>
              <div class="carp">
                <div class="form-p">
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="IdTipoPersona" class="control-label">Departamento (nacimiento) <span class="text-danger fw-bolder">(*)</span></label>
                      <select name="departamento2" id="departamento2" class="form-select">
                        <option value="0" disabled selected>-- SELECCIONE UNA OPCION --</option>
                            @foreach ($departamentos as $departamento)
                                <option 
                                    value="{{ $departamento->IDDEPARTAMENTO }}" 
                                    @if (isset($dis_nac))
                                    {{ $departamento->IDDEPARTAMENTO == $dis_nac->IDDEPARTAMENTO ? 'selected' : '' }}    
                                    @endif                                      
                                    >
                                    {{ $departamento->NAME_DEPARTAMENTO }}
                                </option>
                            @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="IdTipoPersona" class="control-label">Provincia (nacimiento) <span class="text-danger fw-bolder">(*)</span></label>
                      <select class="form-select form-select-solid" name="provincia2" id="provincia2" >
                        @if (isset($dis_nac))
                          <option value="{{ $dis_nac->IDPROVINCIA }}"  selected>{{ $dis_nac->NAME_PROVINCIA }}</option>
                        @else
                          <option value="0" disabled selected>-- SELECCIONE UNA OPCION --</option>
                        @endif
                      </select>
                    </div>
                  </div>
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="IdTipoPersona" class="control-label">Distrito (nacimiento) <span class="text-danger fw-bolder">(*)</span></label>
                      <select class="form-select form-select-solid" name="distrito2" id="distrito2" >
                        @if (isset($dis_nac))
                          <option value="{{ $dis_nac->IDDISTRITO }}"  selected>{{ $dis_nac->NAME_DISTRITO }}</option>
                        @else
                          <option value="0" disabled selected>-- SELECCIONE UNA OPCION --</option>
                        @endif
                    </select>
                      </select>
                    </div>
                  </div>
                </div>
              </div>

            </div>            
          </div>
        </div>
      </div>

      <div class="container">
        <div class="carp">
          <div class="card col-sm-8">
            <div class="card-header">
              <h2>Datos de Trabajador:</h2>
            </div>
            <div class="card-body">
              <div class="carp">
                <div class="form-p">
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="TDoc" class="control-label">Cargo<span class="text-danger fw-bolder">(*)</span></label>                      
                      <select name="cargo_pcm" id="cargo_pcm" class="form-select" aria-label="Tipo de Persona" >
                        <option value="" disabled> -- Seleccione una opción --</option>
                        @foreach ($cargo as $c)
                            <option value="{{ $c->IDCARGO_PERSONAL }}" {{ $c->IDCARGO_PERSONAL == $personal->IDCARGO_PERSONAL ? 'selected' : '' }} >{{ $c->NOMBRE_CARGO }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="NDoc" class="control-label">Fecha de Ingreso <span class="text-danger fw-bolder">(*)</span></label>
                      <input type="date" class="form-control" name="finicio" id="finicio" value="{{ $personal->PCM_FINICIO }}">
                    </div>
                  </div>
                  {{-- <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="NDoc" class="control-label">Cargo <span class="text-danger fw-bolder">(*)</span></label>
                      <select name="" id=""  class="form-select" aria-label="Cargo">

                      </select>
                    </div>
                  </div> --}}
                </div>
              </div>
              <div class="raya"></div>
              <h5>Datos profesionales</h5>
              <div class="carp">
                <div class="form-p">
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <input class="form-check-input" type="radio" name="gi_id" id="gi_id_1" onclick="otros_gi()" value="1" {{ $personal->GI_ID == '1' ? 'checked' : '' }}>
                      <label for="gi_id_1" class="control-label">Técnico incompleto <span class="text-danger fw-bolder">(*)</span></label>                      
                    </div>
                  </div>
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <input class="form-check-input" type="radio" name="gi_id" id="gi_id_2" onclick="otros_gi()" value="2" {{ $personal->GI_ID == '2' ? 'checked' : '' }}>
                      <label for="gi_id_2" class="control-label">Técnico Completo: <span class="text-danger fw-bolder">(*)</label>                      
                    </div>
                  </div>
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <input class="form-check-input" type="radio" name="gi_id" id="gi_id_3" onclick="otros_gi()" value="3" {{ $personal->GI_ID == '3' ? 'checked' : '' }}>
                      <label for="gi_id_3" class="control-label">Superior Incompleto: <span class="text-danger fw-bolder">(*)</label>                      
                    </div>
                  </div>
                </div>
              </div>
              <div class="carp">
                <div class="form-p">
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <input class="form-check-input" type="radio" name="gi_id" id="gi_id_4" onclick="otros_gi()" value="4" {{ $personal->GI_ID == '4' ? 'checked' : '' }}>
                      <label for="gi_id_4" class="control-label">Superior Completo <span class="text-danger fw-bolder">(*)</span></label>                      
                    </div>
                  </div>
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <input class="form-check-input" type="radio" name="gi_id" id="gi_id_5" onclick="otros_gi()" value="5" {{ $personal->GI_ID == '5' ? 'checked' : '' }}>
                      <label for="gi_id_5" class="control-label">Post Grado <span class="text-danger fw-bolder">(*)</label>                      
                    </div>
                  </div>
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <input class="form-check-input otros_gi" type="radio" name="gi_id" id="gi_id_6" onclick="otros_gi()" value="6" {{ $personal->GI_ID == '6' ? 'checked' : '' }}>
                      <label for="otros_gi" class="control-label">Otros <span class="text-danger fw-bolder">(*)</label>                      
                    </div>
                  </div>
                </div>
              </div>

              <div class="carp" id="gi_otro_compl">
                <div class="form-p">
                  <div class="row col-sm-12 buut">
                    <div class="mb-3">
                      <label for="IdTipoPersona" class="control-label">Especifique grado de instrucción: <span class="text-danger fw-bolder">(*)</span></label>
                      <input type="text" name="gi_otro" id="gi_otro" class="form-control" value="{{ $personal->GI_OTRO }}" >
                    </div>
                  </div>
                </div>
              </div>

              <div class="carp">
                <div class="form-p">
                  <div class="row col-sm-12 buut">
                    <div class="mb-3">
                      <label for="IdTipoPersona" class="control-label">Carrera / Profesión <span class="text-danger fw-bolder">(*)</span></label>
                      <input type="text" class="form-control" name="gi_carrera" id="gi_carrera" value="{{ $personal->GI_CARRERA }}">
                    </div>
                  </div>
                </div>
              </div>
              <div class="carp">
                <div class="form-p">
                  <div class="row col-sm-6 buut">
                    <div class="mb-3">
                      <label for="IdTipoPersona" class="control-label">Desde <span class="text-danger fw-bolder">(*)</span></label>
                      <input type="date" class="form-control" name="gi_desde" id="gi_desde" value="{{ $personal->GI_DESDE }}">
                    </div>
                  </div>
                  <div class="row col-sm-6 buut">
                    <div class="mb-3">
                      <label for="IdTipoPersona" class="control-label">hasta <span class="text-danger fw-bolder">(*)</span></label>
                      <input type="date" class="form-control" name="gi_hasta" id="gi_hasta" value="{{ $personal->GI_HASTA }}">
                    </div>
                  </div>
                </div>
              </div>

            </div>            
          </div>
        </div>
      </div>


      <div class="container">
        <div class="carp">
          <div class="card col-sm-8">
            <div class="card-header">
              <h2>Documentos Adjuntos:</h2>
            </div>

            <div class="card-body">
               <div class="carp">
                <div class="form-p">
                    <div class="row col-sm-6 buut">
                        <div class="mb-3">
                        <label for="TipoDoc" class="control-label mb-1">Copia de DNI </label>
                            <div class="col-sm-12">
                                {{-- <div class="sub-title">Example 2</div> --}}
                                <input type="file" class="form-control" name="dni" id="dni">
                                {{-- <span class="messages text-danger" id="msm-file-error">Tiene que cargar un Archivo</span> --}}
                            </div>
                        </div>
                    </div>
                    {{-- <div class="row col-sm-6 buut">
                        <div class="mb-3">
                        <label for="TipoDoc" class="control-label">Curriculum no documentado <span class="text-danger fw-bolder">(*)</span></label>
                            <div class="col-sm-12">
                                <input type="file" class="form-control" name="cv" id="cv">
                                <span class="messages text-danger" id="msm-file-error">Tiene que cargar un Archivo</span>
                            </div>
                        </div>
                    </div> --}}
                </div>
              </div>
              
              <div class="carp">
                <div class="form-p btn_enviar">
                  <div class="row col-sm-2 buut">
                    <div class="">
                      <button type="button" class="form-control btn btn-danger" id="btnEnviarForm" data-toggle="modal" data-target="#large-Modal"onclick="btnEnviar('{{ $personal->IDPERSONAL }}')">ENVIAR</button>
                    </div>
                  </div>
                </div>
              </div>
              <br />
              <div class="carp">
                <div class="form-p">
                  <div class="row col-sm-3 buut">
                    <div class="mb-3">
                      <label for="TipoDoc" class="control-label">Nota </label>
                      
                    </div>
                  </div>
                  <div class="row col-sm-8 buut">
                    <div class="mb-3">
                      <ul>
                        <li>Para el documento principal el formato debe ser pdf, y tamaño máximo de 50MB</li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>

            </form>
            </div>

             

            </div>
          </div>          
        </div>
      </div>
    </section>

</article>
<div id="error"></div>

<!-- Modal -->

<div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" style="max-width: 50%">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Faltan datos</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p id="errorModalMessage"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Salir</button>
      </div>
    </div>
  </div>
</div>

<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js')}}" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script src="{{ asset('js/form.js') }}"></script>


<script src="{{ asset('js/toastr.min.js')}}"></script>

<script>

$(document).ready(function() {
  TPersona();
  $('#divRuc').hide();
  TipoDocumento();
  // $('#n_documento').attr("maxlength", "8");

  document.getElementById('gi_otro_compl').style.display = 'none';

  /* ===================== DIRTECCION ACTUAL ==========================================*/
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
      /* ======================================================================================*/
      /* ===================== DIRTECCION NACIMIENTO ==========================================*/

      $('#departamento2').on('change', function() {
          var departamento_id = $(this).val();
          if (departamento_id) {
              var url = "{{ route('provincias', ['departamento_id' => ':departamento_id']) }}"; // Cambia 'tipo' a 'tipoid'
              url = url.replace(':departamento_id', departamento_id);

              $.ajax({
                  type: 'GET',
                  url: url, // Utiliza la URL generada con tipo
                  success: function(data) {
                      $('#provincia2').html(data);
                  }
              });
          } else {
              $('#provincia2').empty();
          }
      });

      $('#provincia2').on('change', function() {
          var provincia_id = $(this).val();
          if (provincia_id) {
              var url = "{{ route('distritos', ['provincia_id' => ':provincia_id']) }}"; // Cambia 'tipo' a 'tipoid'
              url = url.replace(':provincia_id', provincia_id);

              $.ajax({
                  type: 'GET',
                  url: url, // Utiliza la URL generada con tipo
                  success: function(data) {
                      $('#distrito2').html(data);
                  }
              });
          } else {
              $('#distrito2').empty();
          }
      });
  
});

/* ==================================== VALIDAR CAMPOS =========================================== */

function  validarDetalle () {

  r = { flag: true, mensaje: "" }

  if ($("#datos_nombre").val() == "" || $("#datos_parentesco").val() == "" || $("#datos_actividad").val() == "" || $("#datos_edad").val() == "") {
        r.flag = false;
        r.mensaje = "Debe ingresar los campos vacios";
        return r;
    }

    return r;


}


/* ================================================================================================ */

function btnAgregarDetall (idpersonal) {

  r = validarDetalle();

  if(r.flag){
    document.getElementById("btn-add-filas").innerHTML = '<i class="fa fa-spinner fa-spin"></i> Buscando... ';
    document.getElementById("btn-add-filas").disabled = true;

    var formData = new FormData();
    formData.append("idpersonal", idpersonal);
    formData.append("datos_nombre", $("#datos_nombre").val());
    formData.append("datos_parentesco", $("#datos_parentesco").val());
    formData.append("datos_actividad", $("#datos_actividad").val());
    formData.append("datos_edad", $("#datos_edad").val());
    formData.append("_token", $("input[name=_token]").val());

    $.ajax({
        type: "POST",
        dataType: "json",
        cache: false,
        url: "{{ route('add_datosfamiliares') }}",
        data: formData,
        processData: false,
        contentType: false,
        success: function(response){
          document.getElementById("btn-add-filas").innerHTML = 'Agregar';
          document.getElementById("btn-add-filas").disabled = false;
          $( "#tabla_datos_fam" ).load(window.location.href + " #tabla_datos_fam" );
          
        },
        error: function(error){
            //location.reload();
            console.log("error");
            console.log(error);
        }
    });
  }else{
    Swal.fire({
        icon: "warning",
        text: r.mensaje,
        confirmButtonText: "Aceptar"
    })
  }

}

function btnEliminarDetall (iddatos_personal) {

  swal.fire({
        title: "Seguro que desea eliminar el registro?",
        text: "El registro será eliminado totalmente",
        icon: "error",
        showCancelButton: !0,
        confirmButtonText: "Aceptar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: "{{ route('delete_datosfamiliares') }}",
                type: 'post',
                data: {"_token": "{{ csrf_token() }}", iddatos_personal: iddatos_personal},
                success: function(response){
                    console.log(response);

                    $( "#tabla_datos_fam" ).load(window.location.href + " #tabla_datos_fam" ); 

                    Swal.fire({
                        icon: "success",
                        text: "El archivo fue elimnado con Exito!",
                        confirmButtonText: "Aceptar"
                    })

                },
                error: function(error){
                    console.log('Error '+error);
                }
            });
        }

    })


}

//AGREGAR FORMULARIO
/* ================================================================================================ */

var btnEnviar = ( idpersonal ) =>{
    console.log("success")

    var file_dni = $("#dni").prop("files")[0];
    // var file_cv = $("#cv").prop("files")[0];
    var formData = new FormData();

    formData.append("idpersonal" ,idpersonal);
    formData.append("identidad", $("#identidad").val());
    formData.append("idtipo_doc", $("#idtipo_doc").val());
    formData.append("num_doc", $("#num_doc").val());pcm_talla
    formData.append("pcm_talla", $("#pcm_talla").val());
    formData.append("cargo_pcm", $("#cargo_pcm").val());
    formData.append("finicio", $("#finicio").val());
    formData.append("sexo", $("#sexo").val());
    formData.append("ape_pat", $("#ape_pat").val());
    formData.append("ape_mat", $("#ape_mat").val());
    formData.append("nombre", $("#nombre").val());
    formData.append("telefono", $("#telefono").val());
    formData.append("celular", $("#celular").val());
    formData.append("correo", $("#correo").val());
    formData.append("direccion", $("#direccion").val());
    formData.append("distrito", $("#distrito").val());
    formData.append("fech_nacimiento", $("#fech_nacimiento").val());
    formData.append("grupo_sanguineo", $("#grupo_sanguineo").val());
    formData.append("distrito2", $("#distrito2").val());


    var selectedValueGI = $('input[name="gi_id"]:checked').val();
    formData.append("gi_id", selectedValueGI);
    // formData.append("gi_id", $("#gi_id").val());
    formData.append("gi_otro", $("#gi_otro").val());
    formData.append("gi_carrera", $("#gi_carrera").val());
    formData.append("gi_desde", $("#gi_desde").val());
    formData.append("gi_hasta", $("#gi_hasta").val());

    formData.append("dni", file_dni);
    // formData.append("cv", file_cv);
    
    formData.append("_token", $("#_token").val());

    console.log(formData);

    var tipo = "";
    var titulo = "Enviar documento";

    $.ajax({
        type: "POST",
        dataType: "json",
        cache: false,
        url: "{{ route('store_data') }}",
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
            document.getElementById("btnEnviarForm").innerHTML = '<i class="fa fa-spinner fa-spin"></i> ESPERE';
            document.getElementById("btnEnviarForm").disabled = true;
        },
        success: function(result){
          document.getElementById("btnEnviarForm").innerHTML = 'ENVIAR';
          document.getElementById("btnEnviarForm").disabled = false;
          if(result.status == '201'){
            console.log(result);
            var Message = result.message.replace(/\n/g, "<br>");
            $("#errorModalMessage").html(Message);
            $("#errorModal").modal('show');
          }else{
            
            console.log(result.exp);
            
            Swal.fire({
                icon: "info",
                text: 'Se actualizaron los datos',
                confirmButtonText: "Aceptar"
            })
          }

          
        },
        error: function (xhr, status, error) {
          document.getElementById("btnEnviarForm").innerHTML = 'ENVIAR';
          document.getElementById("btnEnviarForm").disabled = false;
        }
    });
}

/* ================================================================================================ */


</script>


</body>
</html>