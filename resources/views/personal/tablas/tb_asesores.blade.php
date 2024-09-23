<table class="table table-hover table-bordered table-striped" id="table_asistencia">
    <thead class="tenca">
        <tr>
            <th class="text-white" width="50px">N°</th>
            <th class="text-white">Asesor</th>
            <th class="text-white">Número de Documento</th>
            <th class="text-white">Entidad</th>
            <th class="text-white">Módulo</th>
            <th class="text-white">Centro MAC</th>            
            <th class="text-white">Correo</th>            
            <th class="text-white">% de progreso</th>
            <th class="text-white">Estado</th>
            <th class="text-white">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($query as $i =>$que)
            @php
                $total = $que->TOTAL_CAMPOS;
                $datos_complet = $que->DIFERENCIA_CAMPOS;

                // Calculamos el porcentaje de completitud
                $porcentaje = ($total != 0) ? round((100 * $datos_complet) / $total, 2) : 0; // Redondea a 2 decimales

                // Definimos la clase de alerta en función del porcentaje
                if ($porcentaje == 100) {
                    $alert_class = 'bg-success'; // Verde para 100%
                } elseif ($porcentaje >= 40 && $porcentaje < 100) {
                    $alert_class = 'bg-warning'; // Amarillo entre 60% y 99.99%
                } else {
                    $alert_class = 'bg-danger'; // Rojo para menos del 60%
                }
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>
                    <span class="{{ $que->NOMBREU ? '' : 'text-danger' }}">
                        {{ $que->NOMBREU ?? 'Datos incompletos' }}
                    </span>
                </td>
                <td>{{ $que->TIPODOC_ABREV }} - {{ $que->NUM_DOC }}</td>
                <td>{{ $que->ABREV_ENTIDAD }}</td>
                <td>                    
                    @if ($que->N_MODULO === NULL)
                        <button class="nobtn bandejTool" data-tippy-content="Agregar módulo" onclick="btnModulo('{{ $que->IDPERSONAL }}' )">
                            <i class="las la-hand-point-up text-secondary font-16 text-primary"></i>
                        </button>
                    @else
                        <button class="nobtn bandejTool" data-tippy-content="Cambiar módulo" onclick="btnModulo('{{ $que->IDPERSONAL }}' )">
                            {{ $que->N_MODULO }}
                        </button>
                    @endif
                </td>
                <td>{{ $que->NOMBRE_MAC }}</td>                
                <td>
                    <span class="{{ $que->CORREO ? '' : 'text-danger' }}">
                        {{ $que->CORREO ?? 'Datos incompletos' }}
                    </span>
                </td>
                <td>                    
                    <div class="progress " style="height: 20px" id="progreso_por" >
                        <div class="progress-bar badge  {{ $alert_class }}"  role="progressbar" style="width: {{ $porcentaje }}%;" aria-valuenow="{{ $porcentaje }}" aria-valuemin="0" aria-valuemax="50">{{ $porcentaje }}%</div>
                    </div>
                </td>
                <td class="text-center">
                    @if ($que->FLAG == '1')
                        <span class="badge badge-soft-success px-2">Activo</span>
                    @elseif($que->FLAG == '2')
                        <span class="badge badge-soft-danger px-2">Inactivo</span>                        
                    @endif
                </td>
                <td>
                    <a href="http://190.187.182.55:8081/external-mac/formdata?num_doc={{ $que->NUM_DOC }}" class="nobtn bandejTool" data-tippy-content="Editar personal" target="_blank"><i class="las la-pen text-secondary font-16 text-success"></i></a>
                    <button class="nobtn bandejTool" data-tippy-content="Editar Entidad" onclick="btnCambiarEntidad('{{ $que->IDPERSONAL }}' )"><i class="las la-building text-secondary font-16 text-info"></i></button>
                    <button class="nobtn bandejTool" data-tippy-content="Dar de baja" onclick="btnElimnarServicio('{{ $que->IDPERSONAL }}' )"><i class="las la-trash-alt text-secondary font-16 text-danger"></i></button>
                </td>
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
            { "width": "" },
            { "width": "" },
            { "width": "" },
            { "width": "" },
            { "width": "" },
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