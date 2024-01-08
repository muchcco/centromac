<div class="modal-dialog modal-lg" role="document" style="max-width: 80%">
    <div class="modal-content" >
        <div class="modal-header">
            <h4 class="modal-title">Añadir nuevo servicio</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="alerta">
            </div>            
            <h5>Buscar Servicio</h5>

            <table class="table table-hover table-bordered table-striped" id="table_servicios">
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
                            <td class="text-center">
                                <button class="nobtn bandejTool" data-tippy-content="Añadir Servicio" onclick="AddServicio('{{ $servicio->IDSERVICIOS }}' )"><i class=" fa fa-plus text-secondary font-16 text-danger"></i></button> 
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
           
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
    
        $('#table_servicios').DataTable({
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