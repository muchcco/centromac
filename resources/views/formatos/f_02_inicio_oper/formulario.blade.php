<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registro de Asesores</title>
    <link href="{{ asset('https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css')}}" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/app2.css')}}">
    {{-- <!-- jquery file upload Frame work -->
    <link href="{{ asset('assets/pages/jquery.filer/css/jquery.filer.css')}}" type="text/css" rel="stylesheet">
    <link href="{{ asset('assets/pages/jquery.filer/css/themes/jquery.filer-dragdropbox-theme.css')}}" type="text/css" rel="stylesheet"> --}}
    <link rel="stylesheet" href="{{ asset('https://use.fontawesome.com/releases/v5.15.3/css/all.css')}}"  integrity="sha384-SZXxX4whJ79/gErwcOYf+zWLeJdY/qpuqC4cAa9rOGUstPomtqpuNWT9wdPEn2fk" crossorigin="anonymous">

    {{-- preoad button --}}
    <link rel="stylesheet" href="{{ asset('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css')}}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.9.0/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.9.0/dist/sweetalert2.all.min.js"></script>

    <link href="{{ asset('css/toastr.min.css')}}" rel="stylesheet" type="text/css" /> 

    <style>
      .raya{
        width: 100%;
        border: 1px solid black;
        margin-bottom: 1em;
      }

      .container{
        max-width: 100% !important;
      }

      .cont_doble{
        display: flex;
        flex-direction: row;
        justify-content: space-around;
      }

      .carpt{
        border: 0 !important;
        padding: 0 !important;
        width: 100%;
      }

      .font-ss{
        font-size: 14px;
      }
    </style>

</head>
<body>
<article>
    <header>
        <div class="container">
            <div class="row cab">
                <div class="col">
                    <img src="{{ asset('img/200x75.png') }}" alt="">
                </div>
                <div class="col col-8 title">
                    <h3 style="text-align: center">FORMATO 03 VERIFICACION DIARIA DE INICIO DE OPERACIONES - CENTROS MAC {{ $centro_mac }} </h3>
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
          <div class="card col-sm-12">
            <div class="card-header">
              <center><h2>Encabezado</h2></center>
            </div>
            <div class="card-body">
                <div class="cont_doble">
                    <div class="dev">
                        <h5>Supervisor(a):</h5>
                        <p >{{ $personal->NOMBRE }} {{ $personal->APE_PAT }} {{ $personal->APE_MAT }}</p>
                    </div>
                    <div class="dev">
                        <h5>Fecha de registro:</h5>
                        <p>@php
                            setlocale(LC_TIME, 'es_PE', 'Spanish_Spain', 'Spanish'); echo strftime('%d de %B del %Y',strtotime("now"));
                            @endphp
                        </p>
                    </div>                    
                </div>
                
            </div>
          </div>          
        </div>
      </div>
      <div class="container">
        <div class="carp">
          <div class="card col-sm-12">
            <div class="card-header">
              <h2>Ficha:</h2>
            </div>
            <form action="" enctype="multipart/form-data">
              <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />
            <div class="card-body">
              <div class="carpt">
                <div class="">
                  <div class="row col-sm-12 buut">
                   
                    <table class="table table-bordered table-responsive" id="table_reporte">
                        <thead class="bg-danger">
                            <tr>
                                <th class="font-ss text-white">Descripción</th>
                                <th class="font-ss text-white">Apertura</th>
                                <th class="font-ss text-white">Cierre</th>
                                <th class="font-ss text-white">Observación</th>
                            </tr>                            
                        </thead>
                        <tbody>
                           @forelse ($resultado as $result)
                                @if ($result->IDPADRE_F == NULL)
                                    <tr>
                                        <td colspan="4" class="font-ss" style="background: #b3b3b3">{{ $result->DESCRIPCION_F }}</td>
                                    </tr>
                                @elseif($result->IDPADRE_F !== NULL)
                                    <tr>
                                        <td class="font-ss">{{ $result->DESCRIPCION_F }}</td>
                                        <td style="width: 80px">
                                            <input type="hidden" name="iddesc_form[]" value="{{ $result->IDDESC_FORM }}">
                                            <meta name="csrf-token" content="{{ csrf_token() }}">
                                            <select name="apertura[]" id="apertura_{{ $result->IDDESC_FORM }}" class="form-control font-ss">
                                                <option value="" disabled selected> -- </option>
                                                <option value="SI" {{ $result->CONFORMIDAD_I == 'SI' ? 'selected' : '' }}>SI</option>
                                                <option value="NO" {{ $result->CONFORMIDAD_I == 'NO' ? 'selected' : '' }}>NO</option>
                                            </select>
                                        </td>
                                        <td class="fs-6" style="width: 80px">
                                            <select name="cierre[]" id="cierre_{{ $result->IDDESC_FORM }}" class="form-control font-ss">
                                                <option value="" disabled selected> -- </option>
                                                <option value="SI" {{ $result->CONFORMIDAD_F == 'SI' ? 'selected' : '' }}>SI</option>
                                                <option value="NO" {{ $result->CONFORMIDAD_F == 'NO' ? 'selected' : '' }}>NO</option>
                                            </select>
                                        </td>
                                        <td>
                                            <textarea name="observacion[]" id="observacion_{{ $result->IDDESC_FORM }}" cols="40" rows="1">{{ $result->OBSERVACION_F02 }}</textarea>
                                        </td>
                                    </tr>
                                @endif
                               
                           @empty
                               <tr>
                                    <td colspan="4">No hay datos</td>
                               </tr>
                           @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4"><button type="button" class="btn btn-danger btn-block" id="btnEnviarForm" style="width: 100%" onclick="GuardarForm()">GUARDAR</button></td>
                            </tr>
                        </tfoot>
                    </table>
                    
                  </div>
                </div>
              </div>

            </div>            
          </div>
        </div>
      </div>

    </section>

</article>
<div id="error"></div>
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js')}}" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

<script src="{{ asset('js/toastr.min.js')}}"></script>



<script>

function GuardarForm() {
    // Create an array to store input values
    var formData = new FormData();

    // Obtener todas las filas de la tabla
    var filas = $('table tbody tr');

    // Recorrer cada fila y agregar los datos al array
    filas.each(function() {
        var fila = $(this);

        // Obtener valores de la fila
        var aperturaElement = fila.find("select[id^='apertura_']");
        var cierreElement = fila.find("select[id^='cierre_']");
        var observacionElement = fila.find("textarea[name^='observacion']");
        var iddescFormElement = fila.find("input[name^='iddesc_form']");

        // Verificar si los elementos existen
        if (aperturaElement.length > 0 && cierreElement.length > 0 && observacionElement.length > 0 && iddescFormElement.length > 0) {
            formData.append("apertura[]", aperturaElement.val());
            formData.append("cierre[]", cierreElement.val());
            formData.append("observacion[]", observacionElement.val());
            formData.append("iddesc_form[]", iddescFormElement.val());
        }
    });

        // Enviar los datos al servidor
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: 'post',
            url: "{{ route('formatos.f_02_inicio_oper.store_form') }}",
            dataType: "json",
            data: formData,  // Use the FormData object
            processData: false,
            contentType: false,
            beforeSend: function () {
                console.log('Enviando datos al servidor');
            },
            success: function (data) {
                console.log('Datos guardados correctamente', data);

                // Actualizar la tabla después de guardar
                $("#table_reporte").load(window.location.href + " #table_reporte");

                Toastify({
                    text: "Se guardaron los registros",
                    className: "info",
                    style: {
                        background: "#206AC8",
                    }
                }).showToast();
            },
            error: function (error) {
                console.error('Error al enviar datos al servidor', error);
            },
            complete: function () {
                console.log('Completado el envío de datos al servidor');
            }
        });
}


</script>


</body>
</html>