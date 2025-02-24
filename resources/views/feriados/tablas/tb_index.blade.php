    <table class="table table-hover table-bordered table-striped" id="table_feriados">
        <thead class="tenca">
            <tr>
                <th width="50px">N°</th>
                <th>Nombre del Feriado</th>
                <th>Fecha</th>
                <th>Centro MAC</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($feriados as $i => $feriado)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td class="text-uppercase">{{ $feriado->name }}</td>
                    <td>{{ $feriado->fecha->format('Y-m-d') }}</td> <!-- Formato de solo fecha -->
                    <td class="text-uppercase">{{ $feriado->nombre_centromac }}</td>                    <td>
                        <button class="nobtn bandejTool" data-tippy-content="Editar feriado" onclick="btnEditFeriado('{{ $feriado->id }}')"><i class="las la-pen text-secondary font-16 text-success"></i></button>
                        <button class="nobtn bandejTool" data-tippy-content="Eliminar feriado" onclick="btnDeleteFeriado('{{ $feriado->id }}')"><i class="las la-trash-alt text-secondary font-16 text-danger"></i></button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <script>
    $(document).ready(function() {
        $('#table_feriados').DataTable({
            "responsive": true,
            "bLengthChange": true,
            "autoWidth": false,
            "searching": true,
            info: true,
            "ordering": true,
            language: {"url": "{{ asset('js/Spanish.json')}}"}, 
            "columns": [
                { "width": "5px" },
                { "width": "20%" },
                { "width": "20%" },
                { "width": "25%" },
                { "width": "15%" }
            ]
        });

        tippy(".bandejTool", {
            allowHTML: true,
            followCursor: true,
        });
    });
    </script>
