<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Acta de Entrega</title>
    <link href="{{ asset('https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css')}}" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <link rel="stylesheet" href="{{ asset('css/app.css')}}">

    <style>

        .table-head{
            /* border: 1px solid red; */
            width: 100%;
            margin-bottom: 2em;
        }

        .td-mid {
            text-align:center;
            padding-top: .3em;
        }

        .texto {
            font-size: .8em;
            padding-left: 1em;
            padding-right: 1em;
            text-align: justify;
            text-justify: inter-word;
        }

        .table-bor{
            font-size: .8em;
            padding-left: 1em;
            padding-right: 1em;
        }

        .t-d-h {
            border: 1px solid #000;
            padding: .2em;
        }

        .footer{
            margin-top: 8em;
        }

        .watermark {
            position: fixed;
            top: 40%;
            left: 20%;
            transform: translate(-50%, -50%);
            font-size: 36px;
            opacity: 0.5;
            color: #CCCCCC; /* Color de la marca de agua */
            transform: rotate(-45deg); /* Rotar la marca de agua */
        }
    </style>

</head>
<body>
<div class="watermark">ESTE ES UN BORRADOR SIN VALIDEZ</div>
<header>
    <div class=" ">
        <div class="">
            <table class="table-head">
                <tr>
                    <td style="text-align: right; padding-right: 1em;" class="col-2"><img src="{{ asset('imagen/logo-pcm.png') }}" alt="" height="40" ></td>                    
                    <th class="td-mid col-8">ACTA DE BIENES EN CUSTODIA DE LA ENTIDAD "{{ $datos_persona->NOMBRE_ENTIDAD }}"</th>
                    <td style="text-align: left;" class="col-2" ><img src="{{ asset('imagen/mac-general.png') }}" alt="" height="40" ></td>                    
                </tr>
            </table>
        </div>
    </div>
</header>

<section>
    <div class="">
        <p class="texto">Con fecha <?php setlocale(LC_TIME, 'es_PE', 'Spanish_Spain', 'Spanish'); echo strftime('%d de %B del %Y',strtotime("now"));  ?>,  {{ $datos_persona->SEXO === '1' ? 'el Sr.' : 'la Srta.' }}  {{ $datos_persona->NOMBRE }} {{ $datos_persona->APE_PAT }} {{ $datos_persona->APE_MAT }} {{ $datos_persona->SEXO === '1' ? 'identificado' : 'identificada' }} con DNI {{ $datos_persona->NUM_DOC }}, realiza la entrega de bienes asignados para la ejecución de sus labores asignados como asesora de la <strong>{{ $datos_persona->NOMBRE_ENTIDAD }}</strong> en el Centro Mac {{ $centro_mac }} de la Subsecretaría de Calidad de Servicios de la Secretaría de Gestión Pública de la Presidencia del Consejo de Ministros. 
        <br />
        <br />
            Cabe recalcar que todos los bienes asignados se encuentran en perfectas condiciones. </p>
        <br />
    </div>
</section>


<section>
    <table class="table table-bor">
        <thead style="background: #3D61B2; color:#fff;">
            <tr>
                <th class="t-d-h">N°</th>
                <th class="t-d-h">Cod. Patrimonial</th>
                <th class="t-d-h">Descripción </th>
                <th class="t-d-h">Marca </th>
                <th class="t-d-h">Modelo </th>
                <th class="t-d-h">Número de Serie</th>
                <th class="t-d-h">Color</th>
                <th class="t-d-h">Estado</th>
                <th class="t-d-h">Obervación</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($query as $i => $q)
                <tr>                    
                    <td class="t-d-h">{{ $i + 1}}</td>
                    <td class="t-d-h">{{ $q->COD_INTERNO_PCM }}</td>
                    <td class="t-d-h">{{ $q->DESCRIPCION }}</td>
                    <td class="t-d-h">{{ $q->MARCA }}</td>
                    <td class="t-d-h">{{ $q->MODELO }}</td>
                    <td class="t-d-h">{{ $q->SERIE_MEDIDA }}</td>
                    <td class="t-d-h">{{ $q->COLOR }}</td>
                    <td class="t-d-h">
                        @if ($q->ESTADO_BIEN  == '1')
                            BIEN
                        @elseif($q->ESTADO_BIEN  == '2')
                            MAL ESTADO
                        @elseif($q->ESTADO_BIEN  == '3')
                            NO SIRVE
                        @endif
                    </td>
                    <td class="t-d-h">
                        @if ($q->OBSERVACION == NULL)
                            S/O
                        @else
                            {{ $q->OBSERVACION }}
                        @endif
                    </td>                    
                </tr>
            @empty
                <tr><td colspan="9">NO HAY DATOS DISPONIBLES PARA ESTE USUARIO</td></tr> 
            @endforelse
        </tbody>

    </table>
</section>
<p class="texto">Con un total de <strong>{{ $count[0]->NUM_C }}</strong> bienes asignados</p>
<section class="footer">
    <div class="row">
        <table class="table">
            <tr>
                <td style="text-align: center; padding-left: 1em;" class="col-4" >
                    ___________________________________<br />
                    Coordinador(a)
                </td>
                <td class="td-mid col-4">
                    ___________________________________<br />
                    Especialista TIC
                </td>
                <td style="text-align: center; padding-right: 1em;" class="col-4">
                    ___________________________________<br />
                    {{ $datos_persona->SEXO === '1' ? 'Asesor' : 'Asesora' }}
                </td>
            </tr>
        </table>
    </div>

</section>



<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js')}}" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

</body>
</html>