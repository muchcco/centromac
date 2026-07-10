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

                <button type="button"
                    class="nobtn bandejTool"
                    data-tippy-content="Editar módulo"
                    onclick="btnEditModulo('{{ $modulo->IDMODULO }}')">
                    <i class="las la-pen text-success font-16"></i>
                </button>

                <button type="button"
                    class="nobtn bandejTool"
                    data-tippy-content="Cambiar entidad del módulo"
                    onclick="btnCambiarEntidad('{{ $modulo->IDMODULO }}')">
                    <i class="las la-random text-warning font-16"></i>
                </button>

                @role('Administrador')
                <button type="button"
                    class="nobtn bandejTool"
                    data-tippy-content="Eliminar módulo"
                    onclick="btnDeleteModulo('{{ $modulo->IDMODULO }}')">
                    <i class="las la-trash-alt text-danger font-16"></i>
                </button>
                @endrole

            </td>
        </tr>
        @endforeach
    </tbody>
</table>