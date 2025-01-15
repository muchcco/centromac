<table>
    <thead>
        <tr>
            <th colspan="10" style="text-align:center; text-size: 24px; font-weight: bold;">FORMATO LIBRO DE FELICITACIONES  CENTRO MAC - {{ $name_mac }}</th>
        </tr>
    </thead>
</table>

<table class="table table-bor" style="border: 1px solid black">
    <thead style="background: #3D61B2; color:#fff;">
        <tr style="border: 1px solid black; color: #fff;">
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; text-align:center; ">N°</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; text-align:center; ">CORRELATIVO</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; text-align:center; ">FECHA DE REGISTRO</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; text-align:center; ">CENTRO MAC</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; text-align:center; ">ENTIDAD</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; text-align:center; ">ASESOR(A)</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; text-align:center; ">CUIDADANO(A)</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; text-align:center; ">DOCUMENTO</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; text-align:center; ">DESCRIPCION</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; text-align:center; ">REGISTRADO POR</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($query as $i => $q)
            <tr>
                <td style="border: 1px solid black">{{ $i + 1 }}</td>
                <td style="border: 1px solid black">{{ $q->CORRELATIVO_MAC }}</td>
                <td style="border: 1px solid black">{{ $q->R_FECHA }}</td>
                <td style="border: 1px solid black">{{ $q->NOMBRE_MAC }}</td>
                <td style="border: 1px solid black">{{ $q->ABREV_ENTIDAD }}</td>
                <td style="border: 1px solid black">{{ $q->ASESOR }}</td>
                <td style="border: 1px solid black">{{ $q->NOMBREU }}</td>
                <td style="border: 1px solid black">{{ $q->DOCUMENTO }}</td>
                <td style="border: 1px solid black; width: 250px;">{{ $q->R_DESCRIPCION }}</td>
                <td style="border: 1px solid black">{{ $q->NOM_REGISTRA}}</td>
            </tr>
        @empty
            <tr>
                <td colspan="34" class="text-center" style="border: 1px solid black">No hay datos disponibles</td>
            </tr>
        @endforelse
    </tbody>
</table>