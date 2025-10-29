<table class="table table-hover table-bordered table-striped" id="table_modulos">
    <thead class="tenca">
        <tr>
            <th style="width: 5%">N°</th>
            <th style="width: 15%">Número del Módulo</th>
            <th style="width: 20%">Entidad</th>
            <th style="width: 15%">Fecha Inicio</th>
            <th style="width: 15%">Fecha Fin</th>
            <th style="width: 15%">Centro MAC</th>
            <th style="width: 10%">Es Administrativo</th>
            <th style="width: 15%">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($modulos as $i => $modulo)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="text-uppercase">{{ $modulo->N_MODULO }}</td>
                <td class="text-uppercase">{{ $modulo->NOMBRE_ENTIDAD ?? 'Sin entidad' }}</td>
                <td>
                    {{ $modulo->FECHAINICIO ? \Carbon\Carbon::parse($modulo->FECHAINICIO)->format('d-m-Y') : 'Sin datos' }}
                </td>
                <td>
                    {{ $modulo->FECHAFIN ? \Carbon\Carbon::parse($modulo->FECHAFIN)->format('d-m-Y') : 'Sin datos' }}
                </td>
                <td>{{ $modulo->NOMBRE_MAC }}</td>
                <td>{{ $modulo->ES_ADMINISTRATIVO == 'SI' ? 'Sí' : 'No' }}</td>
                <td class="text-center">

                    <!-- ✏️ Editar -->
                    <button class="nobtn bandejTool" data-tippy-content="Editar módulo"
                        onclick="btnEditModulo({{ $modulo->IDMODULO }})">
                        <i class="las la-pen text-success font-16"></i>
                    </button>

                    <!-- 🔁 Cambiar Entidad -->
                    <button class="nobtn bandejTool" data-tippy-content="Cambiar entidad del módulo"
                        onclick="btnCambiarEntidad({{ $modulo->IDMODULO }})">
                        <i class="las la-random text-warning font-16"></i>
                    </button>

                    <!-- 🗑️ Eliminar -->
                    <button class="nobtn bandejTool" data-tippy-content="Eliminar módulo"
                        onclick="btnDeleteModulo({{ $modulo->IDMODULO }})">
                        <i class="las la-trash-alt text-danger font-16"></i>
                    </button>

                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<script>
    function initDataTableModulos() {
        $('#table_modulos').DataTable({
            responsive: true,
            bLengthChange: true,
            autoWidth: false,
            searching: true,
            info: true,
            ordering: true,
            language: {
                "url": "{{ asset('js/Spanish.json') }}"
            },
            // 🔹 8 columnas correctamente definidas
            columns: [{
                    width: "5%"
                }, // N°
                {
                    width: "15%"
                }, // Número del módulo
                {
                    width: "20%"
                }, // Entidad
                {
                    width: "15%"
                }, // Fecha inicio
                {
                    width: "15%"
                }, // Fecha fin
                {
                    width: "15%"
                }, // Centro MAC
                {
                    width: "10%"
                }, // Es administrativo
                {
                    width: "15%"
                } // Acciones
            ],
            drawCallback: function() {
                tippy(".bandejTool", {
                    allowHTML: true,
                    followCursor: true,
                });
            }
        });
    }

    // 🔹 Inicializa DataTable al cargar este fragmento
    $(document).ready(function() {
        initDataTableModulos();
    });
</script>
