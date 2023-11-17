<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>REPORTE DE ASISTENCIA MES CENTRO MAC 
        @php
            $user = App\Models\User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->first();

            echo '_'.$user->NOMBRE_MAC;
        @endphp
        {{ $datos_persona->nombre_entidad }}_{{ $nombreMES }} </title>
    <link href="{{ asset('https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css')}}" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <link rel="stylesheet" href="{{ asset('css/app.css')}}">

    <style>


        .container{
            max-width: 97.3%;
        }

        table, tr, th, td{
            margin-left: auto;
            margin-right: auto;
            width: 100%;
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
        
        .table-head , th, td {
            border: 1px solid black;
        }

        .table-bor{
            font-size: .8em;
            padding-left: 1em;
            padding-right: 1em;
        }

        .table-head{
            width: 100%;
        }

        .t-d-h {
            border: 1px solid #000;
            padding: .2em;
        }

        .footer{
            margin-top: 8em;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="">
                <table class="table table-bordered table-head">
                    <tr>                        
                        <th rowspan="3" style="width:10px"><img src="{{ asset('imagen/mac_logo_export.jpg') }}" alt="" class="img-h" ></th>
                        <th colspan="4" rowspan="2">REPORTE DE ASISTENCIA</th>
                        <th> Código</th>
                        <th>ANS2</th>
                    </tr>
                    <tr>
                        <th>Versión</th>
                        <th>1.0.0</th>
                    </tr>
                    <tr>
                        <th>Centro MAC</th>
                        <th>Junín</th>
                        <th>MES:</th>
                        <th colspan="3">{{ $nombreMES }}</th>
                    </tr>
                </table>
            </div>
        </div>
    </header>   

    <section>
        <table class="table table-bor">
            <thead style="background: #3D61B2; color:#fff;">
                <tr>
                    <th rowspan="2">N°</th>
                    <th rowspan="2">Entidad</th>
                    <th rowspan="2">Nombres y Apellidos</th>
                    <th rowspan="2">Cargo / Asesor(a)</th>
                    <th rowspan="2">DNI</th>
                    <th rowspan="2">Fecha</th>
                    <th rowspan="2">Hora de <br />Ingreso</th>
                    <th colspan="2" class="text-center">Refrigerio</th>
                    <th rowspan="2">Hora de <br />Salida</th>
                    <th rowspan="2">Salida <br />programado</th>
                    <th rowspan="2">Ingreso <br />Programada</th>
                    <th rowspan="2">Observación</th>
                </tr>
                <tr>
                    <th class="text-center">Salida</th>
                    <th class="text-center">Ingreso</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($query as $i => $q)
                @php
                    $hora1 = strtotime($q->hora1); // Convierte la hora1 a un timestamp
                    $horaInicial1 = strtotime('06:00:00');
                    $horaFinal1 = strtotime('10:00:00');
                @endphp
                <tr>
                    <th>{{ $i + 1 }}</th>
                    <th>{{ $datos_persona->NOMBRE_ENTIDAD }}</th>
                    <th>{{ $datos_persona->NOMBREU }}</th>
                    <th>Asesor de Servicio</th>
                    <th>{{ $datos_persona->NUM_DOC }}</th>
                    <th>{{ date("d/m/Y", strtotime($q->FECHA)) }}  </th>
                    @if($q->N_NUM_DOC > '2' )
                        <th>
                            {{-- @if ($hora1 >= $horaInicial1 && $hora1 <= $horaFinal1)
                                {{ $q->hora1 }}
                            @endif --}}
                            {{$q->hora1}}
                        </th>
                        <th>
                            {{ $q->hora2 }}
                        </th>
                        <th>
                            {{ $q->hora3 }}
                        </th>
                        <th>
                            {{ $q->hora4 }}
                        </th>
                        <th>08:15 am</th>
                        <th>06.30 pm</th>
                        <th>
                            <?php setlocale(LC_TIME, 'es_PE', 'es_ES', 'es'); $FECHA = utf8_decode(strftime('%A',strtotime($q->FECHA)));  ?>
            
                            @if ($FECHA == 's?bado')
                                Sábado
                            @else
            
                            @endif
            
                        </th>
                    @elseif($q->N_NUM_DOC == '2' )
                        <th>
                            {{-- @if ($hora1 >= $horaInicial1 && $hora1 <= $horaFinal1)
                                {{ $q->hora1 }}
                            @endif --}}
                            {{$q->hora1}}
                        </th>
                        <th>
                            --
                        </th>
                        <th>
                            --
                        </th>
                        <th>
                            {{ $q->hora2 }}
                        </th>
                        <th>08:15 am</th>
                        <th>06.30 pm</th>
                        <th>
                            <?php setlocale(LC_TIME, 'es_PE', 'es_ES', 'es'); $FECHA = utf8_decode(strftime('%A',strtotime($q->FECHA)));  ?>
            
                            @if ($FECHA == 's?bado')
                                Sábado
                            @else
            
                            @endif
            
                        </th>
                        @elseif($q->N_NUM_DOC == '1' )
                        <th>
                            {{-- @if ($hora1 >= $horaInicial1 && $hora1 <= $horaFinal1)
                                {{ $q->hora1 }}
                            @endif --}}
                            {{$q->hora1}}
                        </th>
                        <th>
                            --
                        </th>
                        <th>
                            --
                        </th>
                        <th>
                            --
                        </th>
                        <th>08:15 am</th>
                        <th>06.30 pm</th>
                        <th>
                            <?php setlocale(LC_TIME, 'es_PE', 'es_ES', 'es'); $FECHA = utf8_decode(strftime('%A',strtotime($q->FECHA)));  ?>
            
                            @if ($FECHA == 's?bado')
                                Sábado
                            @else
            
                            @endif
            
                        </th>
                    @endif
                    
                </tr>    
            @endforeach
            </tbody>
    
        </table>
    </section>



</body>
</html>