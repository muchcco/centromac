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
                <td>{{ $itinerante->NOMBRE_MAC }}</td>
                <td>{{ $itinerante->NUM_DOC }}</td>
                <td>{{ $itinerante->NOMBRE_COMPLETO }}</td> <!-- Nombre del personal -->
                <td>{{ $itinerante->N_MODULO }}</td>
                <td>{{ $itinerante->FECHAINICIO }}</td>
                <td>{{ $itinerante->FECHAFIN }}</td>
                <td>
                    <button class="nobtn bandejTool" data-tippy-content="Editar itinerante"
                        onclick="btnEditItinerante('{{ $itinerante->ID }}')">
                        <i class="las la-pen text-secondary font-16 text-success"></i>
                    </button>
                    <button class="nobtn bandejTool" data-tippy-content="Eliminar itinerante"
                        onclick="btnDeleteItinerante('{{ $itinerante->ID }}')">
                        <i class="las la-trash-alt text-secondary font-16 text-danger"></i>
                    </button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
