<table class="table table-hover table-bordered table-striped" id="table_asistencia">
    <thead class="tenca">
        <tr>
            <th class="th" width="50px">N°</th>
            <th class="th">Asesor</th>
            <th class="th">Número de Documento</th>
            <th class="th">Entidad</th>
            <th class="th">Centro MAC</th>
            <th class="th">Estado</th>
            <th class="th">Fecha y Hora de Ingreso</th>
            <th class="th">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($datos as $i =>$dato)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>
                    {{-- <a href="{{ route('asistencia.det_us', $dato->n_dni) }}">{{ $dato->nombreu }}</a> --}}
                    {{ $dato->nombreu }}                    
                </td>
                <td>{{ $dato->n_dni }}</td>
                <td>{{ $dato->ABREV_ENTIDAD }}</td>
                <td>{{ $dato->NOMBRE_MAC }}</td>                
                <td>
                    @php
                        $fechaBiometrico = $dato->fecha_biometrico;
                        $timestamp = strtotime($fechaBiometrico);
                        $nuevaFecha = date("H:i:s", $timestamp + 60); // 60 segundos representan un minuto
                        $confTimestamp = strtotime($conf->NUM_SOLO);
                        $confTimestamp += 60;
                        $confNuevaFecha = date("H:i:s", $confTimestamp);
                    @endphp
                
                    @if ($nuevaFecha > $confNuevaFecha)
                        <span class="badge badge-soft-danger px-2">TARDE </span>
                    @else
                        <span class="badge badge-soft-success px-2">EN HORA</span>
                    @endif
                </td>
                
                <td>{{ $dato->fecha_biometrico }}</td>
                <td>
                    <button class="btn btn-primary btn-sm" onclick="btnModalView('{{ $dato->n_dni }}', '{{ $dato->fecha_asistencia }}')">Ver completo (Hoy)</button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<script>
$(document).ready(function() {

    $('#table_asistencia').DataTable({
        "responsive": true,
        "bLengthChange": false,
        "autoWidth": false,
        "searching": true,
        info: true,
        "ordering": false,
        "dom":
                "<'row'" +
                "<'col-sm-12 d-flex align-items-center justify-conten-start'l>" +
                "<'col-sm-12 d-flex align-items-center justify-content-end'f>" +
                ">" +

                "<'table-responsive'tr>" +

                "<'row'" +
                "<'col-sm-12 col-md-12 d-flex align-items-center justify-content-center justify-content-md-start'i>" +
                "<'col-sm-12 col-md-12 d-flex align-items-center justify-content-center justify-content-md-end'p>" +
                ">",
        language: {"url": "{{ asset('js/Spanish.json')}}"}, 
        "columns": [
            { "width": "" },
            { "width": "" },
            { "width": "" },
            { "width": "" },
            { "width": "" },
            { "width": "" },
            { "width": "" },
            { "width": "" }
        ]
    });
});
</script>