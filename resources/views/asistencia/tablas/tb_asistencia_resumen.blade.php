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
            <th class="th">Ingreso</th>
            <th class="th">Receso 1</th>
            <th class="th">Receso 2</th>
            <th class="th">Salida</th>
            <th class="th">Obs</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($datos as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="text-uppercase">{{ $item->nombreu }}</td>
                <td>{{ $item->n_dni }}</td>
                <td>{{ $item->nombre_modulo }}</td>
                <td class="text-uppercase">{{ $item->abrev_entidad }}</td>
                <td>{{ $item->nombre_mac }}</td>
                <td>{{ \Carbon\Carbon::parse($item->fecha_asistencia)->format('d-m-Y') }}</td>
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
                <td colspan="12" class="text-center">No hay registros en el resumen.</td>
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
            "responsive": true,
            "bLengthChange": true,
            "autoWidth": false,
            "searching": true,
            "pageLength": 30,
            info: true,
            "ordering": true,
            "dom": "<'row'" +
                "<'col-sm-12 d-flex align-items-center justify-content-start'l>" +
                "<'col-sm-12 d-flex align-items-center justify-content-end'f>" +
                ">" +
                "<'table-responsive'tr>" +
                "<'row'" +
                "<'col-sm-12 col-md-12 d-flex align-items-center justify-content-center justify-content-md-start'i>" +
                "<'col-sm-12 col-md-12 d-flex align-items-center justify-content-center justify-content-md-end'p>" +
                ">",
            language: {
                "url": "{{ asset('js/Spanish.json') }}"
            }
        });
    });
</script>
