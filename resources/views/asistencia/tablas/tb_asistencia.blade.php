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
            <th class="th">Estado</th>
            <th class="th">Ingreso</th>
            <th class="th">Receso</th>
            <th class="th">Receso</th>
            <th class="th">Salida</th>
            <th class="th">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($datos as $i => $dato)
            @php
                use Carbon\Carbon;
                $esHoy = Carbon::parse($dato->fecha_asistencia)->isToday();

                // CONTAR MARCACIONES
                $horas = array_filter(
                    [$dato->HORA_1, $dato->HORA_2, $dato->HORA_3, $dato->HORA_4],
                    fn($h) => !is_null($h) && trim($h) !== '',
                );

                $numHoras = count($horas);

                // CLASE + TOOLTIP
                $rowClass = '';
                $rowTitle = '';

                if (!empty($dato->flag_tardanza_grupal)) {
                    $rowClass = 'table-warning';
                    $rowTitle = 'Tardanza grupal del módulo';
                } elseif (!empty($dato->flag_tarde)) {
                    $rowClass = 'table-info';
                    $rowTitle = 'Tardanza';
                } elseif (!empty($dato->flag_exceso)) {
                    $rowClass = 'table-danger';
                    $rowTitle = 'Exceso de marcaciones';
                } elseif (!$esHoy && in_array($numHoras, [1, 3])) {
                    // ❗ SOLO fechas PASADAS
                    $rowClass = 'table-primary';
                    $rowTitle = 'Marcaciones incompletas';
                }
            @endphp

            <tr class="{{ $rowClass }}"
                @if ($rowTitle) data-bs-toggle="tooltip"
            data-bs-placement="top"
            title="{{ $rowTitle }}" @endif>
                <td>{{ $i + 1 }}</td>

                <td class="text-uppercase">
                    <a href="javascript:void(0);" class="d-flex justify-content-around align-items-start"
                        data-dni="{{ $dato->n_dni }}"
                        onclick="abrirModalAgregarObservacion(
                    '{{ $dato->idpersonal }}',
                    '{{ $dato->fecha_asistencia }}',
                    '{{ $dato->n_dni }}',
                    '{{ $dato->idmac }}'
               )">
                        {{ $dato->nombreu }}

                        @if ($dato->contador_obs > 0)
                            <span class="bandejTool text-dark"
                                data-tippy-content="Este usuario tiene ({{ $dato->contador_obs }}) observación(es)">
                                <i class="fa fa-comment"></i>
                            </span>
                        @endif
                    </a>
                </td>

                <td>
                    <a href="javascript:void(0);"
                        onclick="abrirModalAgregarAsistencia(
                    '{{ $dato->n_dni }}',
                    '{{ $dato->nombreu }}',
                    '{{ $dato->fecha_asistencia }}'
               )">
                        {{ $dato->n_dni }}
                    </a>
                </td>

                <td>
                    <a href="javascript:void(0);"
                        onclick="abrirModalModificar(
                    '{{ $dato->n_dni }}',
                    '{{ $dato->nombreu }}',
                    '{{ $dato->nombre_modulo }}',
                    '{{ $dato->fecha_asistencia }}'
               )">
                        {{ $dato->nombre_modulo }}
                    </a>
                </td>

                <td class="text-uppercase">{{ $dato->ABREV_ENTIDAD }}</td>
                <td>{{ $dato->NOMBRE_MAC }}</td>
                <td>{{ \Carbon\Carbon::parse($dato->fecha_asistencia)->format('d-m-Y') }}</td>
                <td class="text-center">
                    @if (!empty($dato->flag_tardanza_grupal))
                        <span class="badge bg-warning text-dark">
                            Tardanza grupal
                        </span>
                    @elseif (!empty($dato->flag_tarde))
                        <span class="badge bg-info text-dark">
                            Tardanza
                        </span>
                    @elseif (!empty($dato->flag_exceso))
                        <span class="badge bg-danger">
                            Exceso de marcaciones
                        </span>
                    @elseif (!$esHoy && in_array($numHoras, [1, 3]))
                        <span class="badge bg-primary">
                            Marcación incompleta
                        </span>
                    @else
                        <span class="badge bg-success">
                            Puntual
                        </span>
                    @endif
                </td>

                <td>{{ $dato->HORA_1 }}</td>
                <td>{{ $dato->HORA_2 }}</td>
                <td>{{ $dato->HORA_3 }}</td>
                <td>{{ $dato->HORA_4 }}</td>

                <td>
                    <button class="btn btn-primary btn-sm"
                        onclick="btnModalView('{{ $dato->n_dni }}', '{{ $dato->fecha_asistencia }}')">
                        Ver completo (Hoy)
                    </button>
                </td>
            </tr>
        @endforeach
    </tbody>

</table>

<script>
    $(document).ready(function() {
        tippy(".bandejTool", {
            allowHTML: true,
            followCursor: true,
        });
        $('#table_asistencia').DataTable({
            destroy: true,
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
            drawCallback: function() {
                initBootstrapTooltips(); // 👈 CLAVE
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
                }, {
                    "width": ""
                }
            ]
        });
    });

    function initBootstrapTooltips() {
        const tooltipTriggerList = [].slice.call(
            document.querySelectorAll('[data-bs-toggle="tooltip"]')
        );

        tooltipTriggerList.forEach(el => {
            if (!bootstrap.Tooltip.getInstance(el)) {
                new bootstrap.Tooltip(el);
            }
        });
    }

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

    function abrirModalAgregarObservacion(idpersonal, fecha, dni, mac) {
        console.log("click modal observaciones");
        $.ajax({
            type: 'POST',
            url: "{{ route('asistencia.modals.md_add_comment_user') }}",
            data: {
                "_token": "{{ csrf_token() }}",
                "IDPERSONAL": idpersonal,
                "FECHA": fecha,
                "NUM_DOC": dni,
                "IDCENTRO_MAC": mac
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



    function updateObservationIcon(dni, count) {
        // Busca el <a> de la fila principal
        const $link = $('#table_asistencia').find(`a[data-dni="${dni}"]`);
        if (!$link.length) return;

        // Busca el icono existente
        let $icon = $link.find('.bandejTool');

        if (count > 0) {
            // Si ya existe, actualiza el tooltip y su contenido
            if ($icon.length) {
                $icon.attr('data-tippy-content', `Este usuario tiene (${count}) observación(es)`);
                $icon.find('i').off().tippy({
                    content: $icon.attr('data-tippy-content')
                });
            } else {
                // Si no existe, créalo y añádelo
                $icon = $(`
                <span class="bandejTool text-warning"
                    data-tippy-content="Este usuario tiene (${count}) observación(es)">
                <i class="fa fa-comment"></i>
                </span>
            `);
                $link.append($icon);
                tippy($icon[0], {
                    allowHTML: true,
                    followCursor: true
                });
            }
        } else {
            // Si no hay observaciones, elimina el icono
            $icon.remove();
        }
    }
</script>
