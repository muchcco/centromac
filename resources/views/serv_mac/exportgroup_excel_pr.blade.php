<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    {{-- <title>Document</title> --}}
    <link href="{{ asset('https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css')}}" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        table tr th, td{
            border: 1px solid #000;
        }
    </style>

</head>
<body>
    <table>
        <thead style="background: #D9D9D9;">
            <tr>
                <th colspan="6" style="font-weight: bold; text-align: center; background: #D9D9D9; border: 1px solid #000;">{{ $entidad->NOMBRE_ENTIDAD }}</th>
            </tr>
            <tr>
                <th style="font-weight: bold; background: #D9D9D9; border: 1px solid #000;">Servicios</th>
                <th style="font-weight: bold; background: #D9D9D9; border: 1px solid #000;">Orientación</th>
                <th style="font-weight: bold; background: #D9D9D9; border: 1px solid #000;">Trámite</th>
                <th style="font-weight: bold; background: #D9D9D9; border: 1px solid #000;">Costo</th>
                <th style="font-weight: bold; background: #D9D9D9; border: 1px solid #000;">Requisitos</th>
                <th style="font-weight: bold; background: #D9D9D9; border: 1px solid #000;">Requiere cita</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($servicios as $servicio)
                <tr>
                    <td style="font-size: 8px; width: 200px; text-align: left; border: 1px solid #000; ">{{ $servicio->NOMBRE_SERVICIO }}</td>
                    <td style="font-size: 8px; width: 100px; text-align: center; border: 1px solid #000; ">
                        @if ($servicio->ORIENTACION == 1)
                            SI
                        @else
                            NO
                        @endif
                    </td>
                    <td style="font-size: 8px; width: 100px; text-align: center; border: 1px solid #000; ">
                        @if ($servicio->TRAMITE == 1)
                            SI
                        @else
                            NO
                        @endif
                    </td>
                    <td style="font-size: 8px; width: 200px; text-align: left; border: 1px solid #000; ">{{ $servicio->COSTO_SERV }}</td>
                    <td style="font-size: 8px; width: 500px; text-align: left; border: 1px solid #000; ">                  
                        {{ $servicio->REQUISITO_SERVICIO }}
                    </td>
                    <td style="font-size: 8px; width: 80px; text-align: left; center: 1px solid #000;" >{{ $servicio->REQ_CITA }}</td>
                </tr>
            @empty
                
            @endforelse
        </tbody>
    </table>
</body>
</html>