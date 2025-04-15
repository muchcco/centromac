<table class="table table-hover table-bordered table-striped" id="table_asistencia">
    <thead class="tenca">
        <tr>
            <th  width="50px">N°</th>
            <th >Nombres y Apellidos</th>
            <th >Nombre de usuario</th>
            <th >Correo</th>
            <th >Perfil</th>
            <th >Centro MAC</th>
            <th >Estado</th>            
            <th >Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($usuarios as $i =>$usuario)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $usuario->name }}</td>
                <td>{{ $usuario->email }}</td>
                <td>{{ $usuario->CORREO }}</td>
                <td>{{ app\models\User::find($usuario->id)->getRoleNames()->implode(', ') }}</td>
                <td>{{ $usuario->NOMBRE_MAC }}</td>              
                <td>
                    @if ($usuario->flag == 1)
                        <span class="badge badge-soft-success px-2">Activo</span>
                    @else
                        <span class="badge badge-soft-danger px-2">Inactivo</span>
                    @endif
                </td>                
                <td>
                    <button class="nobtn bandejTool" data-tippy-content="Cambiar contraseña" onclick="btnPassUsuario('{{ $usuario->id }}' )"><i class="las la-key text-secondary font-15 text-info"></i></button>                                                     
                    <button class="nobtn bandejTool" data-tippy-content="Editar usuario" onclick="btnEditarUsuario('{{ $usuario->id }}' )"><i class="las la-pen text-secondary font-16 text-success"></i></button>
                    <button class="nobtn bandejTool" data-tippy-content="Eliminar usuario" onclick="btnElimnarUsuario('{{ $usuario->id }}' )"><i class="las la-trash-alt text-secondary font-16 text-danger"></i></button>
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
            { "width": ""}
        ]
    });

    tippy(".bandejTool", {
        allowHTML: true,
        followCursor: true,
    });
});
</script>