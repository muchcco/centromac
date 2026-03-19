<table class="table table-hover table-bordered table-striped" id="table_asistencia_resumen">
    <thead class="tenca">
        <tr>
            <th class="th" width="50px">N°</th>
            <th class="th">Asesor</th>
            <th class="th">Número de Documento</th>
            <th class="th">Módulo</th>
            <th class="th">Entidad</th>
            <th class="th">Centro MAC</th>
            <th class="th">Fecha</th>
            <th class="th">Estado</th>
            <th class="th">Ingreso</th>
            <th class="th">Receso 1</th>
            <th class="th">Receso 2</th>
            <th class="th">Salida</th>
            <th class="th">Obs</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($datos as $i => $item)

            @php
                $esHoy = \Carbon\Carbon::parse($item->fecha_asistencia)->isToday();
                $numHoras = collect([$item->HORA_1, $item->HORA_2, $item->HORA_3, $item->HORA_4])
                    ->filter()
                    ->count();
                $requiereObs = ($item->contador_obs ?? 0) == 0;
            @endphp

            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="text-uppercase">{{ $item->nombreu }}</td>
                <td>{{ $item->n_dni }}</td>
                <td>{{ $item->nombre_modulo }}</td>
                <td class="text-uppercase">{{ $item->abrev_entidad }}</td>
                <td>{{ $item->nombre_mac }}</td>
                <td>{{ \Carbon\Carbon::parse($item->fecha_asistencia)->format('d-m-Y') }}</td>

                <td>

                    @if (!$esHoy && !empty($item->flag_retiro_anticipado))
                        <span class="badge {{ $requiereObs ? 'bg-danger' : 'bg-secondary' }} bandejTool"
                            data-tippy-content="Retiro anticipado">
                            Retiro anticipado
                            @if ($requiereObs)
                                <i class="fa fa-exclamation-triangle ms-1"></i>
                            @endif
                        </span>
                    @elseif (!$esHoy && !empty($item->flag_llegada_fuera_rango))
                        <span class="badge {{ $requiereObs ? 'bg-danger' : 'bg-secondary' }} bandejTool"
                            data-tippy-content="Llegada fuera de rango">
                            Llegada fuera de rango
                            @if ($requiereObs)
                                <i class="fa fa-exclamation-triangle ms-1"></i>
                            @endif
                        </span>
                    @elseif (!empty($item->flag_tardanza_grupal))
                        <span class="badge bg-warning text-dark bandejTool" data-tippy-content="Tardanza grupal">
                            Tardanza grupal
                        </span>
                    @elseif (!empty($item->flag_tarde))
                        <span class="badge bg-info text-dark bandejTool" data-tippy-content="Tardanza">
                            Tardanza
                        </span>
                    @elseif (!empty($item->flag_exceso))
                        <span class="badge bg-danger bandejTool" data-tippy-content="Exceso de marcaciones">
                            Exceso de marcaciones
                        </span>
                    @elseif (!$esHoy && in_array($numHoras, [1, 3]))
                        <span class="badge bg-primary bandejTool" data-tippy-content="Marcación incompleta">
                            Marcación incompleta
                        </span>
                    @else
                        <span class="badge bg-success bandejTool" data-tippy-content="Asistencia correcta">
                            Puntual
                        </span>
                    @endif

                </td>
                <td>{{ $item->HORA_1 ?? '' }}</td>
                <td>{{ $item->HORA_2 ?? '' }}</td>
                <td>{{ $item->HORA_3 ?? '' }}</td>
                <td>{{ $item->HORA_4 ?? '' }}</td>


                <td class="text-center">
                    <i class="fa fa-lock text-danger"></i>

                    <a href="javascript:void(0);"
                        onclick="abrirModalAgregarObservacion(
                                    '{{ $item->idpersonal }}',
                                    '{{ $item->fecha_asistencia }}',
                                    '{{ $item->n_dni }}',
                                    '{{ $item->idmac }}'
                                )"
                        class="text-primary ms-2">

                        <i class="fa fa-eye"></i>

                        @if ($item->contador_obs > 0)
                            <span class="badge bg-danger">{{ $item->contador_obs }}</span>
                        @endif

                    </a>
                </td>

            </tr>

        @empty
            <tr>
                <td colspan="13" class="text-center">
                    No hay registros en el resumen.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>


<script>
    $(document).ready(function() {

        tippy(".bandejTool", {
            allowHTML: true,
            followCursor: true,
        });

        $('#table_asistencia_resumen').DataTable({
            destroy: true,
            responsive: true,
            bLengthChange: true,
            autoWidth: false,
            searching: true,
            pageLength: 30,
            info: true,
            ordering: true,
            dom: "<'row'" +
                "<'col-sm-12 d-flex align-items-center justify-content-start'l>" +
                "<'col-sm-12 d-flex align-items-center justify-content-end'f>" +
                ">" +
                "<'table-responsive'tr>" +
                "<'row'" +
                "<'col-sm-12 col-md-12 d-flex align-items-center justify-content-center justify-content-md-start'i>" +
                "<'col-sm-12 col-md-12 d-flex align-items-center justify-content-center justify-content-md-end'p>" +
                ">",
            language: {
                url: "{{ asset('js/Spanish.json') }}"
            }
        });

    });
</script>
