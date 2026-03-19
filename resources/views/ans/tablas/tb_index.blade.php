<table class="table table-hover table-bordered table-striped" id="table_ans">
    <thead class="tenca">
        <tr>
            <th style="width:5%">N°</th>
            <th style="width:20%">Entidad</th>
            <th style="width:30%">Servicio</th>
            <th style="width:10%">Límite Espera</th>
            <th style="width:10%">Límite Atención</th>
            <th style="width:10%">Se calcula</th>
            <th style="width:15%">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $i => $row)
            <tr>
                <td>{{ $i + 1 }}</td>

                <td class="text-uppercase">
                    {{ $row->entidad }}
                </td>

                <td class="text-uppercase">
                    {{ $row->servicio }}
                </td>

                <td>
                    {{ $row->limite_espera }}
                </td>

                <td>
                    {{ $row->limite_atencion }}
                </td>

                <td class="text-center">

                    @if ($row->se_calcula == 1)
                        <span class="badge bg-success">Sí</span>
                    @else
                        <span class="badge bg-danger">No</span>
                    @endif

                </td>

                <td class="text-center">

                    <button class="nobtn bandejTool" data-tippy-content="Editar servicio"
                        onclick="btnEditServicio({{ $row->id_servicio }})">

                        <i class="las la-pen text-success font-16"></i>

                    </button>

                    <button class="nobtn bandejTool" data-tippy-content="Eliminar servicio"
                        onclick="btnDeleteServicio({{ $row->id_servicio }})">

                        <i class="las la-trash-alt text-danger font-16"></i>

                    </button>

                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<script>
    function initDataTableANS() {

        $('#table_ans').DataTable({

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
                },
                {
                    width: "20%"
                },
                {
                    width: "30%"
                },
                {
                    width: "10%"
                },
                {
                    width: "10%"
                },
                {
                    width: "10%"
                },
                {
                    width: "15%"
                }
            ],

            drawCallback: function() {
                tippy(".bandejTool", {
                    allowHTML: true,
                    followCursor: true
                });
            }
        });
    }
    $(document).ready(function() {
        initDataTableANS();
    });
</script>
