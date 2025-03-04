<table class="table table-hover table-bordered table-striped" id="table_asistencia">
    <thead class="tenca">
        <tr>
            <th class="th" width="50px">N°</th>
            <th class="th">Asesor</th>
            <th class="th">Número de Documento</th>
            <th class="th">Modulo</th>
            <th class="th">Entidad</th>
            <th class="th">Centro MAC</th>
            <th class="th">Fecha</th>
            <th class="th">Ingreso</th>
            <th class="th">Receso</th>
            <th class="th">Receso</th>
            <th class="th">Salida</th>
            <th class="th">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($datos as $i => $dato)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="text-uppercase">
                    {{-- <a href="{{ route('asistencia.det_us', $dato->n_dni) }}">{{ $dato->nombreu }}</a> --}}
                    {{ $dato->nombreu }}
                </td>
                <td>
                    <a href="javascript:void(0);"
                        onclick="abrirModalAgregarAsistencia('{{ $dato->n_dni }}', '{{ $dato->nombreu }}', '{{ $dato->fecha_asistencia }}')">
                        {{ $dato->n_dni }}
                    </a>
                </td>
                <td>
                    <a href="javascript:void(0);"
                        onclick="abrirModalModificar('{{ $dato->n_dni }}', '{{ $dato->nombreu }}', '{{ $dato->nombre_modulo }}', '{{ $dato->fecha_asistencia }}')">
                        {{ $dato->nombre_modulo }}
                    </a>
                </td>
                <td class="text-uppercase">{{ $dato->ABREV_ENTIDAD }}</td>
                {{--  <td>
                    @if ($dato->mostrar == 'itinerante')
                        {{ $dato->nombre_modulo }} (Itinerante)
                    @elseif($dato->mostrar == 'fijo')
                        {{ $dato->nombre_modulo }} (Fijo)
                    @else
                        <!-- Si no tiene el status "itinerante" o "fijo", no mostrar nada -->
                        No disponible
                    @endif
                </td> --}}
                <td>{{ $dato->NOMBRE_MAC }}</td>
                <td>{{ \Carbon\Carbon::parse($dato->fecha_asistencia)->format('d-m-Y') }}</td>
                <td>{{ $dato->HORA_1 }}</td>
                <td>{{ $dato->HORA_2 }}</td>
                <td>{{ $dato->HORA_3 }}</td>
                <td>{{ $dato->HORA_4 }}</td>
                <td>
                    <button class="btn btn-primary btn-sm"
                        onclick="btnModalView('{{ $dato->n_dni }}', '{{ $dato->fecha_asistencia }}')">Ver completo
                        (Hoy)
                    </button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<script>
    $(document).ready(function() {

        $('#table_asistencia').DataTable({
            "responsive": true,
            "bLengthChange": true,
            "autoWidth": false,
            "searching": true,
            "pageLength": 30, // Número predeterminado de filas por página
            info: true,
            "ordering": true,

            "dom": "<'row'" +
                "<'col-sm-12 d-flex align-items-center justify-conten-start'l>" +
                "<'col-sm-12 d-flex align-items-center justify-content-end'f>" +
                ">" +

                "<'table-responsive'tr>" +

                "<'row'" +
                "<'col-sm-12 col-md-12 d-flex align-items-center justify-content-center justify-content-md-start'i>" +
                "<'col-sm-12 col-md-12 d-flex align-items-center justify-content-center justify-content-md-end'p>" +
                ">",
            language: {
                "url": "{{ asset('js/Spanish.json') }}"
            },
            "columns": [{
                    "width": ""
                },
                {
                    "width": ""
                },
                {
                    "width": ""
                }, {
                    "width": ""
                }, {
                    "width": ""
                },
                {
                    "width": ""
                },
                {
                    "width": ""
                },
                {
                    "width": ""
                },
                {
                    "width": ""
                },
                {
                    "width": ""
                },
                {
                    "width": ""
                },
                {
                    "width": ""
                }
            ]
        });
    });

    function abrirModalModificar(num_doc, nombre_asesor, nombre_modulo, fecha_asistencia) {
        // Enviar los datos a través de AJAX para mostrar el modal
        $.ajax({
            type: 'POST',
            url: "{{ route('asistencia.modals.md_moficicar_modulo') }}", // Ruta del controlador
            data: {
                "_token": "{{ csrf_token() }}",
                "num_doc": num_doc,
                "nombre_modulo": nombre_modulo,
                "fecha_asistencia": fecha_asistencia
            },
            success: function(response) {
                // Cargar el HTML del modal con los datos recibidos
                $("#modal_show_modal").html(response.html);
                $("#modal_show_modal").modal('show'); // Mostrar el modal
            },
            error: function(xhr, status, error) {
                // Mostrar un mensaje de error si ocurre algún problema
                alert('Hubo un error al cargar el modal');
            }
        });
    }

    function abrirModalAgregarAsistencia(dni, nombre, fecha) {
        $.ajax({
            type: 'POST',
            url: "{{ route('asistencia.modals.md_add_dni_asistencia') }}",
            data: {
                "_token": "{{ csrf_token() }}",
                "DNI": dni,
                "nombre": nombre,
                "fecha_asistencia": fecha
            },
            beforeSend: function() {
                console.log("Cargando modal...");
            },
            success: function(response) {
                $("#modal_show_modal").html(response.html); // Cargar el modal en el contenedor
                $("#modal_show_modal").modal('show'); // Mostrar el modal
            },
            error: function(error) {
                console.log("Error al cargar el modal", error);
            }
        });
    }
</script>
