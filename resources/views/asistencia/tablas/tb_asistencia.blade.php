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
                    <a href="{{ route('asistencia.det_us', $dato->n_dni) }}">{{ $dato->nombreu }}</a>                    
                </td>
                <td>{{ $dato->n_dni }}</td>
                <td>{{ $dato->ABREV_ENTIDAD }}</td>
                <td>{{ $dato->NOMBRE_MAC }}</td>                
                <td>
                    @if ( date("H:i:s", strtotime($dato->fecha_biometrico)) > '08:16:00')
                        <span class="badge badge-danger m-l-5">TARDE</span>
                    @else
                        <span class="badge badge-success m-l-5">EN HORA</span>
                    @endif
                </td>
                <td>{{ $dato->fecha_biometrico }}</td>
                <td></td>
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