<table class="table table-hover table-bordered table-striped" id="table_asistencia">
    <thead class="tenca">
        <tr>
            <th  width="50px">N°</th>
            <th >Entidad</th>
            <th >Nombre del Servicio</th>
            <th style="width: 125px">Tipo de Servicio</th>
            <th >Costo (en soles)<br /> S/.</th>
            <th >Centro MAC</th>
            <th >Requisitos</th>
            <th >Requiere cita?</th>
            <th >Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($servicios as $i =>$servicio)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $servicio->NOMBRE_ENTIDAD }}</td>
                <td>{{ $servicio->NOMBRE_SERVICIO }}</td>
                <td style="width: 125px" class="pr-1">
                    <ul>
                        <li>Trámite: 
                            @if ($servicio->TRAMITE == 1)
                                SI
                            @else
                                NO
                            @endif
                        </li>
                        <li>Orientación:
                            @if ($servicio->ORIENTACION == 1)
                                SI
                            @else
                                NO
                            @endif
                        </li>
                    </ul>
                </td>
                <td>{{ $servicio->COSTO_SERV }}</td>
                <td>{{ $servicio->NOMBRE_MAC }}</td>
                <td>
                    {{ $servicio->REQUISITO_SERVICIO }}
                </td>               
                <td>
                    {{ $servicio->REQ_CITA }}
                </td>
                <td>                                                       
                    {{-- <button class="nobtn bandejTool" data-tippy-content="Editar servicio" onclick="btnEditarServicio('{{ $servicio->IDSERVICIOS }}' )"><i class="las la-pen text-secondary font-16 text-success"></i></button> --}}
                    <button class="nobtn bandejTool" data-tippy-content="Eliminar servicio" onclick="btnElimnarServicio('{{ $servicio->IDSERVICIOS }}' )"><i class="las la-trash-alt text-secondary font-16 text-danger"></i></button>
                </td>
                {{-- <td class="unalinea">
                    <button type="button"  name="boton" id="devolver" data-target="#modal-devolucion" data-toggle="modal"  class="btn btn-dark btn-sm bandejTool" disabled title="No puede realizar esta acción"><i class="fa fa-reply" aria-hidden="true"></i></button>
                    <button type="button"  name="boton" id="transferir" data-target="#modal-expediente" data-toggle="modal"  class="btn btn-info btn-sm bandejTool" data-tippy-content="transferir Documento Corregido" onclick="DevDocCorregido('{{ $servicio->IDENT_SERV }}', '{{ $servicio->IDSERVICIOS }}' )"><i class="fa fa-share" aria-hidden="true"></i></button>
                </td> --}}
            </tr>
        @endforeach
    </tbody>
</table>

<script>
$(document).ready(function() {

    $('#table_asistencia').DataTable({
        "responsive": true,
        "bLengthChange": true,
        "autoWidth": false,
        "searching": true,
        info: true,
        "ordering": false,
        language: {"url": "{{ asset('js/Spanish.json')}}"}, 
        "columns": [
            { "width": "5px" },
            { "width": "" },
            { "width": "" },
            { "width": "25" },
            { "width": "" },
            { "width": "" },
            { "width": "" },
            { "width": "" },
            { "width": "" }
        ]
    });

    tippy(".bandejTool", {
        allowHTML: true,
        followCursor: true,
    });
});
</script>