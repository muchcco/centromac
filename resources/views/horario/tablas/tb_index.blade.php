<table class="table table-hover table-bordered table-striped" id="table_horarios">
    <thead class="tenca">
        <tr>
            <th style="width:5%">N°</th>
            <th style="width:15%">Centro MAC</th>
            <th style="width:15%">Entidad</th>
            <th style="width:10%">Módulo</th>
            <th style="width:10%">Día</th>
            <th style="width:10%">Hora Ingreso</th>
            <th style="width:10%">Hora Salida</th>
            <th style="width:10%">Fecha Inicio</th>
            <th style="width:10%">Fecha Fin</th>
            <th style="width:15%">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($horarios as $i => $h)
            @php                $dias = [   1 => 'Lunes',   2 => 'Martes',   3 => 'Miércoles',   4 => 'Jueves',   5 => 'Viernes',   6 => 'Sábado',   7 => 'Domingo',                ];            @endphp <tr>
                <td>{{ $i + 1 }}</td>
                <td class="text-uppercase"> {{ $h->NOMBRE_MAC ?? '-' }} </td>
                <td class="text-uppercase"> {{ $h->NOMBRE_ENTIDAD ?? '-' }} </td>
                <td> {{ $h->N_MODULO ?? 'General' }} </td>
                <td> {{ $dias[$h->DiaSemana] ?? '-' }} </td>
                <td> {{ \Carbon\Carbon::parse($h->HoraIngreso)->format('H:i') }} </td>
                <td> {{ \Carbon\Carbon::parse($h->HoraSalida)->format('H:i') }} </td>
                <td> {{ \Carbon\Carbon::parse($h->fecha_inicio)->format('d-m-Y') }} </td>
                <td> {{ \Carbon\Carbon::parse($h->fecha_fin)->format('d-m-Y') }} </td>
                <td class="text-center"> <!-- ✏️ EDITAR --> <button class="nobtn bandejTool"
                        data-tippy-content="Editar horario" onclick="btnEditHorario({{ $h->idhorario_diferenciado }})">
                        <i class="las la-pen text-success font-16"></i> </button> <!-- 🗑️ ELIMINAR --> <button
                        class="nobtn bandejTool" data-tippy-content="Eliminar horario"
                        onclick="btnDeleteHorario({{ $h->idhorario_diferenciado }})"> <i
                            class="las la-trash-alt text-danger font-16"></i> </button> </td>
            </tr>
        @endforeach
    </tbody>
</table>
<script>
    function initDataTableHorarios() {
        $('#table_horarios').DataTable({
            responsive: true,
            bLengthChange: true,
            autoWidth: false,
            searching: true,
            info: true,
            ordering: true,
            language: {
                "url": "{{ asset('js/Spanish.json') }}"
            },
            columns: [{
                width: "5%"
            }, {
                width: "15%"
            }, {
                width: "15%"
            }, {
                width: "10%"
            }, {
                width: "10%"
            }, {
                width: "10%"
            }, {
                width: "10%"
            }, {
                width: "10%"
            }, {
                width: "10%"
            }, {
                width: "15%"
            }],
            drawCallback: function() {
                tippy(".bandejTool", {
                    allowHTML: true,
                    followCursor: true
                })
            }
        })
    }
    $(document).ready(function() {
        initDataTableHorarios()
    })
</script>
