<table class="table table-hover table-bordered table-striped" id="table_horarios_asignados">
    <thead class="tenca">
        <tr>
            <th>Personal</th>
            <th>DNI</th>
            <th>Horario</th>
            <th>Vigencia</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($horarios as $h)
            <tr>
                <td class="text-uppercase">{{ $h->nombre_completo }}</td>
                <td>{{ $h->NUM_DOC }}</td>
                <td>{{ substr($h->hora_ingreso, 0, 5) }} - {{ substr($h->hora_salida, 0, 5) }}</td>
                <td>
                    {{ \Carbon\Carbon::parse($h->fecha_inicio)->format('d-m-Y') }}
                    -
                    @if ((int) $h->sin_fin === 1 || !$h->fecha_fin)
                        Sin fin
                    @else
                        {{ \Carbon\Carbon::parse($h->fecha_fin)->format('d-m-Y') }}
                    @endif
                </td>
                <td class="text-center">
                    <button type="button" class="nobtn bandejTool me-2" data-tippy-content="Editar"
                        onclick="abrirModalAsignacion('{{ $h->id }}')">
                        <i class="las la-pen text-success font-16"></i>
                    </button>
                    <button type="button" class="nobtn bandejTool" data-tippy-content="Eliminar"
                        onclick="eliminarAsignacionHorario('{{ $h->id }}')">
                        <i class="las la-trash-alt text-danger font-16"></i>
                    </button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<script>
    $(document).ready(function() {
        $('#table_horarios_asignados').DataTable({
            destroy: true,
            responsive: true,
            bLengthChange: false,
            autoWidth: false,
            searching: true,
            pageLength: 8,
            info: false,
            ordering: true,
            language: {
                url: "{{ asset('js/Spanish.json') }}"
            },
            drawCallback: function() {
                tippy(".bandejTool", {
                    allowHTML: true,
                    followCursor: true
                });
            }
        });
    });
</script>
