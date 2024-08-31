<table class="table table-hover table-bordered table-striped" id="table_itinerantes">
    <thead>
        <tr>
            <th>#</th>
            <th>Centro MAC</th>
            <th>Documento</th>
            <th>Personal</th>
            <th>Módulo</th>
            <th>Fecha de Inicio</th>
            <th>Fecha de Fin</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($itinerantes as $i => $itinerante)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $itinerante->centroMac->NOMBRE_MAC }}</td>
            <td>{{ $itinerante->NUM_DOC }}</td>
            <td>{{ $itinerante->personal->NOMBRE_COMPLETO }}</td>
            <td>{{ $itinerante->modulo->N_MODULO }}</td>
            <td>{{ $itinerante->fechainicio }}</td>
            <td>{{ $itinerante->fechafin }}</td>
            <td>
                <button class="nobtn bandejTool" data-tippy-content="Editar itinerante"
                    onclick="btnEditItinerante('{{ $itinerante->id_itinerante }}')">
                    <i class="las la-pen text-secondary font-16 text-success"></i>
                </button>

                <button class="nobtn bandejTool" data-tippy-content="Eliminar itinerante"
                    onclick="btnDeleteItinerante('{{ $itinerante->IDCENTRO_MAC }}', '{{ $itinerante->NUM_DOC }}', '{{ $itinerante->IDMODULO }}')">
                    <i class="las la-trash-alt text-secondary font-16 text-danger"></i>
                </button>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>