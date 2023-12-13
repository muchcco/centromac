<table class="table table-hover table-bordered table-striped" id="table_formato">
    <tbody>
        <tr>
            <td >Supervisor(a)</td>
            <td ></td> 
            <td>Semana</td>
            <td></td>
        </tr>
    </tbody>
</table>

<div class="col-12">
    @if (count($resultado) > 0)
        <table class="table table-hover table-bordered table-striped">
            <thead>
                <tr>
                    <th colspan="2" >N Condición Operativa</th>
                    @foreach ($fechas as $fecha)
                        <th colspan="2" class="text-center">Fecha de Inspección</th>
                    @endforeach
                    
                </tr>
                <tr class="bg-white">
                    <th colspan="2" rowspan="4" class="text-dark" style="width: 50px">Verificar si el recurso, equipo u otro, esta dañado, roto, desprolijo, inoperativo o funcionando inadecuadamente según aplique</th>                 
                    @foreach ($fechas as $fecha)
                        <th colspan="2" class="text-center text-dark border-cell">{{ \Carbon\Carbon::parse($fecha)->locale('es_ES')->isoFormat('DD \d\e MMMM') }}</th>
                        {{-- <th>{{ $fecha }}</th> --}}
                    @endforeach
                </tr>
                <tr class="bg-white">
                    @foreach ($fechas as $fecha)
                        <th colspan="2" class="text-center text-dark border-cell">{{ \Carbon\Carbon::parse($fecha)->locale('es_ES')->isoFormat('dddd') }}</th>
                        {{-- <th>{{ $fecha }}</th> --}}
                    @endforeach
                </tr>
                <tr>
                    @foreach ($fechas as $fecha)
                        <th class="text-center ">Apertura</th>
                        <th class="text-center ">Cierre</th>
                    @endforeach
                </tr>
                <tr class="bg-white">
                    @foreach ($fechas as $fecha)
                        <th class="text-center text-dark border-cell-l">¿Conforme?</th>
                        <th class="text-center text-dark border-cell-r">¿Conforme?</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($resultado as $i => $item)
                    @if ($item->IDPADRE_F == NULL)
                        @php
                            $fecha_count = (count($fechas)*2) + 2;
                        @endphp
                        <tr>
                            <td colspan="{{ $fecha_count }}" class="font-ss" style="background: #b3b3b3"> <strong>{{ $item->DESCRIPCION_F }}</strong></td>
                        </tr>
                    @elseif($item->IDPADRE_F !== NULL)
                        <tr>
                            <td>{{ $i + 1}}</td>
                            <td>{{ $item->DESCRIPCION_F }}</td>
                            @foreach ($fechas as $fecha)
                                <td class="text-center border-cell-l">{{ $item["$fecha"] ?? '' }}</td>
                                <td class="text-center border-cell-r">{{ $item["$fecha"] ?? '' }}</td>
                            @endforeach
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    @else
        <p>No se encontraron resultados.</p>
    @endif
</div>

<script>



</script>