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
        @foreach ($query as $i => $que)
            @php
                $total = $que->TOTAL_CAMPOS;
                $datos_complet = $que->DIFERENCIA_CAMPOS;
                $porcentaje = $total != 0 ? round((100 * $datos_complet) / $total, 2) : 0;
                $alert_class = $porcentaje == 100 ? 'bg-success' : ($porcentaje >= 40 ? 'bg-warning' : 'bg-danger');
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="text-uppercase">
                    <span class="{{ $que->NOMBREU ? '' : 'text-danger' }}">
                        {{ $que->NOMBREU ?? 'Datos incompletos' }}
                    </span>
                </td>
                <td>{{ $que->TIPODOC_ABREV }} - {{ $que->NUM_DOC }}</td>
                <td>{{ $que->NOMBRE_ENTIDAD }}</td>
                <td>
                    @if ($que->N_MODULO === null)
                        <a class="nobtn bandejTool" data-tippy-content="Agregar módulo"
                            href="{{ route('personalModulo.index') }}">
                            <i class="las la-hand-point-up text-secondary font-16 text-primary"></i>
                        </a>
                    @else
                       
                            {{ $que->N_MODULO }}
                    
                    @endif
                </td>
                <td>
                    {{ $que->NOMBRE_MAC }} 
                    @if ($que->COUNT_DPM > 1)
                        <span class="bandejTool" data-tippy-content="El principal mac donde se registro el asesor fue {{ $que->PRINCIPAL_MAC }}"><svg xmlns="http://www.w3.org/2000/svg" height="16" width="16" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path fill="#f50f0f" d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/></svg> </span> 
                    @endif
                    
                </td>
                <td>
                    <span class="{{ $que->CORREO ? '' : 'text-danger' }}">
                        {{ $que->CORREO ?? 'Datos incompletos' }}
                    </span>
                </td>
                <td>
                    <div class="progress " style="height: 20px">
                        <div class="progress-bar badge {{ $alert_class }}" role="progressbar"
                            style="width: {{ $porcentaje }}%;" aria-valuenow="{{ $porcentaje }}" aria-valuemin="0"
                            aria-valuemax="100">{{ $porcentaje }}%</div>
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
                    <a href="http://190.187.182.55:8081/external-mac/formdata?num_doc={{ $que->NUM_DOC }}"
                        class="nobtn bandejTool" data-tippy-content="Editar personal" target="_blank"><i
                            class="las la-pen text-secondary font-16 text-success"></i></a>
                    {{-- <button class="nobtn bandejTool" data-tippy-content="Editar Entidad"
                        onclick="btnCambiarEntidad('{{ $que->IDPERSONAL }}' )"><i
                            class="las la-building text-secondary font-16 text-info"></i></button> --}}
                    <button class="nobtn bandejTool" data-tippy-content="Dar de baja"
                        onclick="btnElimnarServicio('{{ $que->IDPERSONAL }}' )"><i
                            class="las la-trash-alt text-secondary font-16 text-danger"></i></button>
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
            "ordering": true,
            language: {
                "url": "{{ asset('js/Spanish.json') }}"
            },
            "columns": [{
                    "width": ""
                },
                {
                    "width": ""
                },
                {
                    "width": ""
                },
                {
                    "width": ""
                },
                {
                    "width": ""
                },
                {
                    "width": ""
                },
                {
                    "width": ""
                },
                {
                    "width": ""
                },
                {
                    "width": ""
                },
                {
                    "width": ""
                }
            ]
        });
        tippy(".bandejTool", {
            allowHTML: true,
            followCursor: true,
        });
    });
</script>
