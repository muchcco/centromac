<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registro de Asesores</title>
    <link href="{{ asset('css/bootstrap/bootstrap.min.css')}}" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">    
    {{-- <!-- jquery file upload Frame work -->
    <link href="{{ asset('assets/pages/jquery.filer/css/jquery.filer.css')}}" type="text/css" rel="stylesheet">
    <link href="{{ asset('assets/pages/jquery.filer/css/themes/jquery.filer-dragdropbox-theme.css')}}" type="text/css" rel="stylesheet"> --}}
    <link rel="stylesheet" href="{{ asset('css/all.css')}}"  integrity="sha384-SZXxX4whJ79/gErwcOYf+zWLeJdY/qpuqC4cAa9rOGUstPomtqpuNWT9wdPEn2fk" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/app2.css')}}">

    {{-- preoad button --}}
    <link rel="stylesheet" href="{{ asset('font-awesome-4.7.0/css/font-awesome.min.css')}}">
    <link rel="stylesheet" href="{{ asset('css/sweetalert2.min.css')}}">
    <script src="{{ asset('js/sweetalert2.all.min.js')}}"></script>

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
                <h2 style="text-align: center">FORMULARIO DE REGISTRO - CENTRO MAC {{ $personal->NOMBRE_MAC }}</h2>
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
                  <div class="row col-sm-10 buut">
                    <div class="mb-3">
                      <label for="IdTipoPersona" class="control-label">Nombre de la Entidad <span class="text-danger fw-bolder">(*)</span> </label>
                      <select name="identidad" id="identidad" class="form-select">
                        <option value="0" disabled selected>Seleccione el tipo de entidad</option>
                        @foreach ($entidad as $ent)
                            <option value="{{ $ent->IDENTIDAD }}" {{ $personal->IDENTIDAD == $ent->IDENTIDAD ? 'selected' : '' }} >{{ $ent->NOMBRE_ENTIDAD }}</option>                                                      
                        @endforeach
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
                      <label for="IdTipoPersona" class="control-label">Talla Polo<span class="text-danger fw-bolder">(*)</span> <i class="fa fa-info bandejTool" data-tippy-content="Talla para polos, por ejemplo: S, M, L, XL ..." ></i> </label>
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
              <h2>En caso de Emergencia llamar a:</h2>
            </div>
            <div class="card-body">
              <div class="carp">
                <div class="form-p">
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="TDoc" class="control-label">Nombres y Apellidos <span class="text-danger fw-bolder">(*)</span></label>
                      <input type="text" class="form-control" name="e_nomape" id="e_nomape" value="{{ $personal->E_NOMAPE }}">
                    </div>
                  </div>
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="RSocial" class="control-label">Teléfono Fijo:</label>
                      <input type="text" class="form-control" name="e_telefono" id="e_telefono" value="{{ $personal->E_TELEFONO }}">
                    </div>
                  </div>
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="RSocial" class="control-label">Celular: <span class="text-danger fw-bolder">(*)</span></label>
                      <input type="text" class="form-control" name="e_celular" id="e_celular" value="{{ $personal->E_CELULAR }}">
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
              <h2>Estado Civil :</h2>
            </div>
            <div class="card-body">
              <div class="carp">
                <div class="form-p">
                  <div class="row col-sm-3 buut">
                    <div class="mb-3">
                      <input class="form-check-input" type="radio" name="ecivil" id="ecivil_1"  value="1" {{ $personal->ESTADO_CIVIL == '1' ? 'checked' : '' }}>
                      <label for="ecivil_1" class="control-label">Soltero(a) <span class="text-danger fw-bolder">(*)</span></label>                      
                    </div>
                  </div>
                  <div class="row col-sm-3 buut">
                    <div class="mb-3">
                      <input class="form-check-input" type="radio" name="ecivil" id="ecivil_2"  value="2" {{ $personal->ESTADO_CIVIL == '2' ? 'checked' : '' }}>
                      <label for="ecivil_2" class="control-label">Casado(a): <span class="text-danger fw-bolder">(*)</span></label>                      
                    </div>
                  </div>
                  <div class="row col-sm-3 buut">
                    <div class="mb-3">
                      <input class="form-check-input" type="radio" name="ecivil" id="ecivil_3"  value="3" {{ $personal->ESTADO_CIVIL == '3' ? 'checked' : '' }}>
                      <label for="ecivil_3" class="control-label">Divorciado(a): <span class="text-danger fw-bolder">(*)</span></label>
                    </div>
                  </div>
                  <div class="row col-sm-3 buut">
                    <div class="mb-3">
                      <input class="form-check-input" type="radio" name="ecivil" id="ecivil_4"  value="4" {{ $personal->ESTADO_CIVIL == '4' ? 'checked' : '' }}>
                      <label for="ecivil_4" class="control-label">Viudo(a): <span class="text-danger fw-bolder">(*)</span></label>                      
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
              <h2>Datos Familiares(Dependientes directos) :</h2>
            </div>
            <div class="card-body">
              <div class="carp">
                <div class="form-p">
                  <div class="row col-sm-8 buut">
                    <div class="mb-3">
                      <label for="TDoc" class="control-label">N° de hijos<span class="text-danger fw-bolder">(*)</span></label>
                      <input type="text" class="form-control" name="df_n_hijos" id="df_n_hijos" onkeypress="return isNumber(event)" value="{{ $personal->DF_N_HIJOS }}">            
                    </div>
                  </div>
                </div>
              </div>
              <div class="raya"></div>
              <div class="carp">
                <div class="form-p">
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="TDoc" class="control-label">Nombres y apellidos<span class="text-danger fw-bolder">(*)</span></label>
                      <input type="text" class="form-control" name="datos_nombre" id="datos_nombre" >            
                    </div>
                  </div>
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="TDoc" class="control-label">Parentesco<span class="text-danger fw-bolder">(*)</span></label>
                      <input type="text" class="form-control" name="datos_parentesco" id="datos_parentesco" >            
                    </div>
                  </div>
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="TDoc" class="control-label">Actividad / Profesión<span class="text-danger fw-bolder">(*)</span></label>
                      <input type="text" class="form-control" name="datos_actividad" id="datos_actividad" >            
                    </div>
                  </div>
                </div>
              </div>
              <div class="carp">
                <div class="form-p">
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="TDoc" class="control-label">Edad<span class="text-danger fw-bolder">(*)</span></label>
                      <input type="text" class="form-control" name="datos_edad" id="datos_edad" >            
                    </div>
                  </div>
                  <div class="row col-sm-8 buut">
                    <div class="mb-3">
                      <button type="button" class="btn btn-primary mt-4" style="width: 100%" id="btn-add-filas" onclick="btnAgregarDetall('{{ $personal->IDPERSONAL }}')">Agregar</button>           
                    </div>
                  </div>
                </div>
              </div>
              <div class="carp">
                <div class="form-p">
                  <div class="row col-sm-12 buut">
                    <div class="mb-3">
                        <table class="table table-bordered" id="tabla_datos_fam">
                            <thead>
                              <tr>
                                <th>Apellidos y Nombres</th>
                                <th>Parentescos</th>
                                <th>Actividades / Profesión </th>
                                <th>Edad</th>
                                <th>Eliminar</th>
                              </tr>
                            </thead>
                            <tbody>
                              @forelse ($detall_fam as $detall)
                                  <tr>
                                    <td>{{  $detall->DATOS_NOMBRES }}</td>
                                    <td>{{  $detall->DATOS_PARENTESCO }}</td>
                                    <td>{{  $detall->DATOS_ACTIVIDAD }}</td>
                                    <td>{{  $detall->DATOS_EDAD }}</td>
                                    <td class="text-center">
                                      <button type="button" onclick="btnEliminarDetall({{ $detall->IDDATOS_PERSONAL }})" class="nobtn" title="Eliminar"><svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"/></svg></button>
                                    </td>
                                  </tr>
                              @empty
                                  <tr>
                                    <td colspan="5" class="text-center text-danger">No hay datos registrados</td>
                                  </tr>
                              @endforelse
                            </tbody>
                          </table>       
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
              <h2>Puesto a desempeñar en el Centro de Atención MAC:</h2>
            </div>
            <div class="card-body">
              <div class="carp">
                <div class="form-p">
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="TDoc" class="control-label">Fecha de Ingreso <span class="text-danger fw-bolder">(*)</span></label>
                      <input type="date" class="form-control" name="dp_fecha_ingreso" id="dp_fecha_ingreso" value="{{ $personal->PD_FECHA_INGRESO }}" >
                    </div>
                  </div>
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="RSocial" class="control-label">Puesto de Trabajo:</label>
                      <input type="text" class="form-control" name="dp_puesto_trabajo" id="dp_puesto_trabajo" value="{{ $personal->PD_PUESTO_TRABAJO }}">
                    </div>
                  </div>
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="RSocial" class="control-label">Tiempo en el puesto de trabajo: <span class="text-danger fw-bolder">(*)</span></label>
                      <input type="text" class="form-control" name="dp_tiempo_ptrabajo" id="dp_tiempo_ptrabajo" value="{{ $personal->PD_TIEMPO_PTRABAJO }}">
                    </div>
                  </div>
                </div>
              </div>
              <div class="carp">
                <div class="form-p">
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="TDoc" class="control-label">Centro de atención <span class="text-danger fw-bolder">(*)</span></label>
                      <select name="dp_centro_atencion" id="dp_centro_atencion" class="form-select">
                        <option value="1" {{ $personal->PD_CENTRO_ATENCION == '1' ? 'selecetd' : '' }} >Fijo</option>
                        <option value="2" {{ $personal->PD_CENTRO_ATENCION == '2' ? 'selecetd' : '' }} >Itinerante</option>
                      </select>
                    </div>
                  </div>
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="RSocial" class="control-label">Código de identificación:  <i class="fa fa-info" data-bs-toggle="tooltip" data-bs-placement="top" title="(A ser por llenado por el área de Gestión de Talento Humano)"></i> </label>
                      <input type="text" class="form-control" name="dp_codigo_identificacion" id="dp_codigo_identificacion" value="{{ $personal->PD_CODIGO_IDENTIFICACION }}">
                    </div>
                  </div>
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="RSocial" class="control-label">Número de Módulo de atención: <span class="text-danger fw-bolder">(*)</span></label>
                      <input type="text" class="form-control" name="num_modulo" id="num_modulo" value="{{ $personal->NUMERO_MODULO }}">
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
              <h2>Datos laborales y profesionales :</h2>
            </div>
            <div class="card-body">
              <div class="carp">
                <div class="form-p">
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="IdTipoPersona" class="control-label">Fecha de ingreso a la entidad <span class="text-danger fw-bolder">(*)</span></label>
                      <input type="date" class="form-control" name="dlp_fecha_ingreso" id="dlp_fecha_ingreso" value="{{ $personal->DLP_FECHA_INGRESO }}">
                    </div>
                  </div>
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="IdTipoPersona" class="control-label">Puesto de trabajo <span class="text-danger fw-bolder">(*)</span></label>
                      <input type="text" class="form-control" name="dlp_puesto_trabajo" id="dlp_puesto_trabajo" value="{{ $personal->DLP_PUESTO_TRABAJO }}">
                    </div>
                  </div>
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="IdTipoPersona" class="control-label">Tiempo en el puesto de trabajo <span class="text-danger fw-bolder">(*)</span></label>
                      <input type="text" class="form-control" name="dlp_tiempo_puesto" id="dlp_tiempo_puesto" value="{{ $personal->DLP_TIEMPO_PTRABAJO }}">
                    </div>
                  </div>
                </div>
              </div>
              <div class="carp">
                <div class="form-p">
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="IdTipoPersona" class="control-label">Area de trabajo <span class="text-danger fw-bolder">(*)</span></label>
                      <input type="text" class="form-control" name="dlp_area_trabajo" id="dlp_area_trabajo" value="{{ $personal->DLP_AREA_TRABAJO }}">
                    </div>
                  </div>
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="IdTipoPersona" class="control-label">Jefe inmediato superior <span class="text-danger fw-bolder">(*)</span></label>
                      <input type="text" class="form-control" name="dlp_jefe_inmediato" id="dlp_jefe_inmediato" value="{{ $personal->DLP_JEFE_INMEDIATO }}">
                    </div>
                  </div>
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="IdTipoPersona" class="control-label">Cargo <span class="text-danger fw-bolder">(*)</span></label>
                      <input type="text" class="form-control" name="dlp_cargo" id="dlp_cargo" value="{{ $personal->DLP_CARGO }}">
                    </div>
                  </div>
                </div>
              </div>
              <div class="carp">
                <div class="form-p">
                  <div class="row col-sm-4 buut">
                    <div class="mb-3">
                      <label for="IdTipoPersona" class="control-label">Teléfono<span class="text-danger fw-bolder">(*)</span></label>
                      <input type="text" class="form-control" name="dlp_telefono" id="dlp_telefono" value="{{ $personal->DLP_TELEFONO }}">
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
              <h2>Tipo de vinculación laboral :</h2>
            </div>
            <div class="card-body">
              <div class="carp">
                <div class="form-p">
                  <div class="row col-sm-6 buut">
                    <div class="mb-3">
                      <input class="form-check-input" type="radio" name="tlv_id" id="tlv_id_1" onclick="otros_tvp()" {{ $personal->TVL_ID == '1' ? 'checked' : '' }} value="1">
                      <label for="" class="control-label">CAS (Contrato administrativo de servicios) <span class="text-danger fw-bolder">(*)</span></label>                      
                    </div>
                  </div>
                  <div class="row col-sm-6 buut">
                    <div class="mb-3">
                      <input class="form-check-input" type="radio" name="tlv_id" id="tlv_id_2" onclick="otros_tvp()" {{ $personal->TVL_ID == '2' ? 'checked' : '' }} value="2">
                      <label for="" class="control-label">Régimen laboral de actividad privada: <span class="text-danger fw-bolder">(*)</label>                      
                    </div>
                  </div>
                </div>
              </div>

              <div class="carp">
                <div class="form-p">
                  <div class="row col-sm-6 buut">
                    <div class="mb-3">
                      <input class="form-check-input" type="radio" name="tlv_id" id="tlv_id_3" onclick="otros_tvp()" {{ $personal->TVL_ID == '3' ? 'checked' : '' }} value="3">
                      <label for="" class="control-label">Empleado público: <span class="text-danger fw-bolder">(*)</span></label>                      
                    </div>
                  </div>
                  <div class="row col-sm-6 buut">
                    <div class="mb-3">
                      <input class="dato_otros form-check-input" type="radio" name="tlv_id" id="tlv_id_4" onclick="otros_tvp()" {{ $personal->TVL_ID == '4' ? 'checked' : '' }} value="4">
                      <label for="" class="control-label">Otros: <span class="text-danger fw-bolder">(*)</label>                      
                    </div>
                  </div>
                </div>
              </div>
              <div class="carp" id="tvl_otros_esp">
                <div class="form-p">
                  <div class="row col-sm-12 buut">
                    <div class="mb-3">
                      <label for="IdTipoPersona" class="control-label">Especifique: <span class="text-danger fw-bolder">(*)</span></label>
                      <textarea name="tvl_otro" id="tvl_otro" cols="30" rows="5" class="form-control">{{ $personal->TVL_OTRO }}</textarea>
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
              <h2>Grado de instrucción :</h2>
            </div>
            <div class="card-body">
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
                        <label for="TipoDoc" class="control-label">Copia de DNI <span class="text-danger fw-bolder">(*)</span></label>
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
                                <div class="sub-title">Example 2</div>
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
<script src="{{ asset('js/bootstrap/bootstrap.bundle.min.js')}}" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script src="{{ asset('js/form.js') }}"></script>


<script src="{{ asset('js/toastr.min.js')}}"></script>

<script src="{{ asset('js\tipify5\popper.js')}}"></script>
<script src="{{ asset('js\tipify5\tippy.js')}}"></script>

<script>

$(document).ready(function() {
  TPersona();
  $('#divRuc').hide();
  TipoDocumento();
  // $('#n_documento').attr("maxlength", "8");
  tippy(".bandejTool", {
    allowHTML: true,
    followCursor: true,
  });

  document.getElementById('tvl_otros_esp').style.display = 'none';
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
    if ($('#identidad').val() == null  || $('#identidad').val() == '0' ) {
        $('#identidad').addClass("hasError");
    }else {
        $('#identidad').removeClass("hasError");
    }
    if ($('#pcm_talla').val() == null  || $('#pcm_talla').val() == '0' ) {
        $('#pcm_talla').addClass("hasError");
    }else {
        $('#pcm_talla').removeClass("hasError");
    }
    if ($('#num_modulo').val() == null  || $('#num_modulo').val() == '0' ) {
        $('#num_modulo').addClass("hasError");
    }else {
        $('#num_modulo').removeClass("hasError");
    }
    if ($('#idtipo_doc').val() == null || $('#idtipo_doc').val() == '') {
        $('#idtipo_doc').addClass("hasError");
    } else {
        $('#idtipo_doc').removeClass("hasError");
    }
    if ($('#num_doc').val() == null || $('#num_doc').val() == '') {
        $('#num_doc').addClass("hasError");
    } else {
        $('#num_doc').removeClass("hasError");
    }
    if ($('#sexo').val() == null || $('#sexo').val() == '') {
        $('#sexo').addClass("hasError");
    } else {
        $('#sexo').removeClass("hasError");
    }
    if ($('#ape_pat').val() == null || $('#ape_pat').val() == '') {
        $('#ape_pat').addClass("hasError");
    } else {
        $('#ape_pat').removeClass("hasError");
    }
    if ($('#ape_mat').val() == null || $('#ape_mat').val() == '') {
        $('#ape_mat').addClass("hasError");
    } else {
        $('#ape_mat').removeClass("hasError");
    }
    if ($('#nombre').val() == null || $('#nombre').val() == '') {
        $('#nombre').addClass("hasError");
    } else {
        $('#nombre').removeClass("hasError");
    }
    if ($('#telefono').val() == null || $('#telefono').val() == '') {
        $('#telefono').addClass("hasError");
    } else {
        $('#telefono').removeClass("hasError");
    }
    if ($('#celular').val() == null || $('#celular').val() == '') {
        $('#celular').addClass("hasError");
    } else {
        $('#celular').removeClass("hasError");
    }
    if ($('#correo').val() == null || $('#correo').val() == '') {
        $('#correo').addClass("hasError");
    } else {
        $('#correo').removeClass("hasError");
    }
    if ($('#direccion').val() == null || $('#direccion').val() == '') {
        $('#direccion').addClass("hasError");
    } else {
        $('#direccion').removeClass("hasError");
    }
    if ($('#distrito').val() == null || $('#distrito').val() == '') {
        $('#distrito').addClass("hasError");
    } else {
        $('#distrito').removeClass("hasError");
    }
    if ($('#fech_nacimiento').val() == null || $('#fech_nacimiento').val() == '') {
        $('#fech_nacimiento').addClass("hasError");
    } else {
        $('#fech_nacimiento').removeClass("hasError");
    }
    if ($('#grupo_sanguineo').val() == null || $('#grupo_sanguineo').val() == '') {
        $('#grupo_sanguineo').addClass("hasError");
    } else {
        $('#grupo_sanguineo').removeClass("hasError");
    }
    if ($('#distrito2').val() == null || $('#distrito2').val() == '') {
        $('#distrito2').addClass("hasError");
    } else {
        $('#distrito2').removeClass("hasError");
    }
    if ($('#e_nomape').val() == null || $('#e_nomape').val() == '') {
        $('#e_nomape').addClass("hasError");
    } else {
        $('#e_nomape').removeClass("hasError");
    }
    if ($('#e_telefono').val() == null || $('#e_telefono').val() == '') {
        $('#e_telefono').addClass("hasError");
    } else {
        $('#e_telefono').removeClass("hasError");
    }
    if ($('#e_celular').val() == null || $('#e_celular').val() == '') {
        $('#e_celular').addClass("hasError");
    } else {
        $('#e_celular').removeClass("hasError");
    }
    if ($('#ecivil').val() == null || $('#ecivil').val() == '' || $('#ecivil').val() == "undefined") {
        $('#ecivil').addClass("hasError");
    } else {
        $('#ecivil').removeClass("hasError");
    }
    if ($('#df_n_hijos').val() == null || $('#df_n_hijos').val() == '') {
        $('#df_n_hijos').addClass("hasError");
    } else {
        $('#df_n_hijos').removeClass("hasError");
    }
    if ($('#dp_fecha_ingreso').val() == null || $('#dp_fecha_ingreso').val() == '') {
        $('#dp_fecha_ingreso').addClass("hasError");
    } else {
        $('#dp_fecha_ingreso').removeClass("hasError");
    }
    if ($('#dp_puesto_trabajo').val() == null || $('#dp_puesto_trabajo').val() == '') {
        $('#dp_puesto_trabajo').addClass("hasError");
    } else {
        $('#dp_puesto_trabajo').removeClass("hasError");
    }
    if ($('#dp_tiempo_ptrabajo').val() == null || $('#dp_tiempo_ptrabajo').val() == '') {
        $('#dp_tiempo_ptrabajo').addClass("hasError");
    } else {
        $('#dp_tiempo_ptrabajo').removeClass("hasError");
    }
    if ($('#dp_centro_atencion').val() == null || $('#dp_centro_atencion').val() == '') {
        $('#dp_centro_atencion').addClass("hasError");
    } else {
        $('#dp_centro_atencion').removeClass("hasError");
    }
    if ($('#dp_codigo_identificacion').val() == null || $('#dp_codigo_identificacion').val() == '') {
        $('#dp_codigo_identificacion').addClass("hasError");
    } else {
        $('#dp_codigo_identificacion').removeClass("hasError");
    }
    if ($('#dlp_fecha_ingreso').val() == null || $('#dlp_fecha_ingreso').val() == '') {
        $('#dlp_fecha_ingreso').addClass("hasError");
    } else {
        $('#dlp_fecha_ingreso').removeClass("hasError");
    }
    if ($('#dlp_puesto_trabajo').val() == null || $('#dlp_puesto_trabajo').val() == '') {
        $('#dlp_puesto_trabajo').addClass("hasError");
    } else {
        $('#dlp_puesto_trabajo').removeClass("hasError");
    }
    if ($('#dlp_area_trabajo').val() == null || $('#dlp_area_trabajo').val() == '') {
        $('#dlp_area_trabajo').addClass("hasError");
    } else {
        $('#dlp_area_trabajo').removeClass("hasError");
    }
    if ($('#dlp_jefe_inmediato').val() == null || $('#dlp_jefe_inmediato').val() == '') {
        $('#dlp_jefe_inmediato').addClass("hasError");
    } else {
        $('#dlp_jefe_inmediato').removeClass("hasError");
    }
    if ($('#dlp_tiempo_puesto').val() == null || $('#dlp_tiempo_puesto').val() == '') {
        $('#dlp_tiempo_puesto').addClass("hasError");
    } else {
        $('#dlp_tiempo_puesto').removeClass("hasError");
    }
    if ($('#dlp_telefono').val() == null || $('#dlp_telefono').val() == '') {
        $('#dlp_telefono').addClass("hasError");
    } else {
        $('#dlp_telefono').removeClass("hasError");
    }
    if ($('#tlv_id').val() == null || $('#tlv_id').val() == '') {
        $('#tlv_id').addClass("hasError");
    } else {
        $('#tlv_id').removeClass("hasError");
    }
    if ($('#tvl_otro').val() == null || $('#tvl_otro').val() == '') {
        $('#tvl_otro').addClass("hasError");
    } else {
        $('#tvl_otro').removeClass("hasError");
    }
    if ($('#gi_id').val() == null || $('#gi_id').val() == '') {
        $('#gi_id').addClass("hasError");
    } else {
        $('#gi_id').removeClass("hasError");
    }
    if ($('#gi_otro').val() == null || $('#gi_otro').val() == '') {
        $('#gi_otro').addClass("hasError");
    } else {
        $('#gi_otro').removeClass("hasError");
    }
    if ($('#gi_carrera').val() == null || $('#gi_carrera').val() == '') {
        $('#gi_carrera').addClass("hasError");
    } else {
        $('#gi_carrera').removeClass("hasError");
    }
    if ($('#gi_desde').val() == null || $('#gi_desde').val() == '') {
        $('#gi_desde').addClass("hasError");
    } else {
        $('#gi_desde').removeClass("hasError");
    }
    if ($('#gi_hasta').val() == null || $('#gi_hasta').val() == '') {
        $('#gi_hasta').addClass("hasError");
    } else {
        $('#gi_hasta').removeClass("hasError");
    }


    var file_dni = $("#dni").prop("files")[0];
    // var file_cv = $("#cv").prop("files")[0];
    var formData = new FormData();

    formData.append("idpersonal" ,idpersonal);
    formData.append("identidad", $("#identidad").val());
    formData.append("pcm_talla", $("#pcm_talla").val());
    formData.append("num_modulo", $("#num_modulo").val());
    formData.append("idtipo_doc", $("#idtipo_doc").val());
    formData.append("num_doc", $("#num_doc").val());
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

    formData.append("e_nomape", $("#e_nomape").val());
    formData.append("e_telefono", $("#e_telefono").val());
    formData.append("e_celular", $("#e_celular").val());

    var selectedValue = $('input[name="ecivil"]:checked').val();
    formData.append("ecivil", selectedValue);
    //formData.append("ecivil", $("#ecivil").val());

    formData.append("df_n_hijos", $("#df_n_hijos").val());

    formData.append("dp_fecha_ingreso", $("#dp_fecha_ingreso").val());
    formData.append("dp_puesto_trabajo", $("#dp_puesto_trabajo").val());
    formData.append("dp_tiempo_ptrabajo", $("#dp_tiempo_ptrabajo").val());
    formData.append("dp_centro_atencion", $("#dp_centro_atencion").val());
    formData.append("dp_codigo_identificacion", $("#dp_codigo_identificacion").val());

    formData.append("dlp_fecha_ingreso", $("#dlp_fecha_ingreso").val());
    formData.append("dlp_puesto_trabajo", $("#dlp_puesto_trabajo").val());
    formData.append("dlp_tiempo_puesto", $("#dlp_tiempo_puesto").val());
    formData.append("dlp_area_trabajo", $("#dlp_area_trabajo").val());
    formData.append("dlp_jefe_inmediato", $("#dlp_jefe_inmediato").val());
    formData.append("dlp_cargo", $("#dlp_cargo").val());
    formData.append("dlp_telefono", $("#dlp_telefono").val());

    var selectedValueTLV = $('input[name="tlv_id"]:checked').val();
    formData.append("tlv_id", selectedValueTLV);
    // formData.append("tlv_id", $("#tlv_id").val());
    formData.append("tvl_otro", $("#tvl_otro").val());

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