@php
    use Carbon\Carbon;
    $hoy = Carbon::today()->format('Y-m-d');
@endphp

<table class="table table-hover table-bordered table-striped" id="table_formato">
    <tbody>
        <tr>
            <td style="border:1px solid black" rowspan="3" colspan="2">
                <img src="{{ asset('imagen/mac_logo_export.jpg') }}" width="230px">
            </td>
            <td style="border:1px solid black" colspan="8" rowspan="2">
                REPORTE CONSOLIDADO DE OCUPABILIDAD DE LOS MÓDULOS DE LAS ENTIDADES PARTICIPANTES POR MES<br>
                <span class="text-danger">
                    Período evaluado Enero a diciembre {{ $fecha_año }}
                </span>
            </td>
            <td style="border:1px solid black">Código</td>
            <td style="border:1px solid black" colspan="2">ANS2</td>
        </tr>
        <tr>
            <td style="border:1px solid black">Versión</td>
            <td style="border:1px solid black" colspan="2">1.0.0</td>
        </tr>
        <tr>
            <td style="border:1px solid black">Centro MAC</td>
            <td style="border:1px solid black">{{ $nombreMac }}</td>
            <td style="border:1px solid black" colspan="2">MES:</td>
            <td style="border:1px solid black" colspan="7">{{ $mesNombre }}</td>
        </tr>
    </tbody>
</table>

<table class="table table-hover table-bordered table-striped" id="table_formato2">
    <thead class="tenca">
        <tr>
            <th>MODULO</th>
            <th>ENTIDAD</th>
            @for ($d = 1; $d <= $numeroDias; $d++)
                <th>{{ $d }}</th>
            @endfor
            <th>OBS</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($modulos as $modulo)
            @php $contadorSi = 0; @endphp
            <tr>
                <td>{{ $modulo->n_modulo }}</td>
                <td>{{ $modulo->nombre_entidad }}</td>

                @for ($d = 1; $d <= $numeroDias; $d++)
                    @php
                        $fecha = Carbon::create($fecha_año, $fecha_mes, $d)->format('Y-m-d');
                        $esFuturo = $fecha > $hoy;
                        $esDomingo = Carbon::create($fecha_año, $fecha_mes, $d)->isSunday();
                        $esFeriado = in_array($fecha, $feriados);

                        $activo = $fecha >= $modulo->fechainicio && $fecha <= $modulo->fechafin;
                        $cerrado = in_array($d, $diasCerrados);

                        $valor = $final[$d][$modulo->idmodulo] ?? null;

                        $mostrarSi = !$esFuturo && !$esDomingo && !$esFeriado && $activo && !empty($valor);
                        if ($mostrarSi) {
                            $contadorSi++;
                        }
                    @endphp
                    {{-- FUTURO --}}
                    @if ($esFuturo)
                        <td style="background:#222; color:white;">-</td>

                        {{-- NO APLICA --}}
                    @elseif ($esDomingo || $esFeriado || !$activo)
                        <td style="background:#444; color:white;">-</td>

                        {{-- DÍA CERRADO (SP) --}}
                    @elseif ($cerrado)
                        <td
                            style="
            background: {{ $mostrarSi ? '#ffffff' : '#2F75B5' }};
            color: {{ $mostrarSi ? 'black' : 'white' }};
        ">
                            {!! $mostrarSi ? 'SI' : '<strong>NO</strong>' !!}
                        </td>

                        {{-- DÍA ABIERTO (VIVO) --}}
                    @else
                        <td
                            style="
            background: {{ $mostrarSi ? '#e9a2a9' : '#C00000' }};
            color:white;
        ">
                            {!! $mostrarSi ? 'SI' : '<strong>NO</strong>' !!}
                        </td>
                    @endif
                @endfor

                <td class="text-center fw-bold">{{ $contadorSi }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<script>
    $(document).ready(function() {
        $('#table_formato2').DataTable({
            paging: false,
            searching: false,
            info: false,
            ordering: true,
            order: [
                [0, 'asc']
            ],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.19/i18n/Spanish.json"
            }
        });
    });
</script>
