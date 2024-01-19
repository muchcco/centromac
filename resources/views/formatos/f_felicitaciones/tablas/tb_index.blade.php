<table class="table table-hover table-bordered table-striped" id="table_asistencia">
    <thead class="tenca">
        <tr>
            <th class="text-white" width="50px">N°</th>
            <th class="text-white">Correlativo</th>
            <th class="text-white">Fecha de registro</th>
            <th class="text-white">Centro MAC</th>
            <th class="text-white">Entidad</th>            
            <th class="text-white">Asesor(a)</th>
            <th class="text-white">Cuidadano(a)</th>
            <th class="text-white">Documento</th>
            <th class="text-white">Descripcion</th>
            <th class="text-white">Archivo</th>
            <th class="text-white">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($query as $i =>$que)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $que->CORRELATVIO }} - {{ $que->AÑO }}</td>
                <td>{{ $que->R_FECHA }}</td>
                <td>{{ $que->NOMBRE_MAC }}</td>
                <td>{{ $que->ABREV_ENTIDAD }}</td>
                <td>{{ $que->ASESOR }}</td>
                <td>{{ $que->NOMBREU }}</td>
                <td>{{ $que->DOCUMENTO }}</td>
                <td>
                    <span class="bandejTool" data-tippy-content="{{ $que->R_DESCRIPCION }}">{{  Illuminate\Support\Str::limit($que->R_DESCRIPCION, 50) }}</span>                    
                </td>
                <td class="text-center">
                    @if ($que->R_ARCHIVO_NOM == NULL)
                        No hay archivo
                    @else
                        <a href="{{ asset($que->R_ARCHIVO_RUT .'/'. $que->R_ARCHIVO_NOM) }}" target="_blank" rel="noopener noreferrer" class="bandejTool" data-tippy-content="Descargar el documento"> 
                            <svg xmlns="http://www.w3.org/2000/svg" height="16" width="16" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path fill="#e90707" d="M0 64C0 28.7 28.7 0 64 0L224 0l0 128c0 17.7 14.3 32 32 32l128 0 0 144-208 0c-35.3 0-64 28.7-64 64l0 144-48 0c-35.3 0-64-28.7-64-64L0 64zm384 64l-128 0L256 0 384 128zM176 352l32 0c30.9 0 56 25.1 56 56s-25.1 56-56 56l-16 0 0 32c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-48 0-80c0-8.8 7.2-16 16-16zm32 80c13.3 0 24-10.7 24-24s-10.7-24-24-24l-16 0 0 48 16 0zm96-80l32 0c26.5 0 48 21.5 48 48l0 64c0 26.5-21.5 48-48 48l-32 0c-8.8 0-16-7.2-16-16l0-128c0-8.8 7.2-16 16-16zm32 128c8.8 0 16-7.2 16-16l0-64c0-8.8-7.2-16-16-16l-16 0 0 96 16 0zm80-112c0-8.8 7.2-16 16-16l48 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-32 0 0 32 32 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-32 0 0 48c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-64 0-64z"/></svg>
                        </a> 
                    @endif
                    
                </td>
                <td class="text-center">
                    <button onclick="btnEditarFelicitacion('{{ $que->IDLIBRO_FELICITACION }}' )" class="nobtn bandejTool" data-tippy-content="Editar registro">
                        <i class="las la-pen text-secondary font-16 text-success"></i>
                    </button>
                    <button class="nobtn bandejTool" data-tippy-content="Eliminar registro" onclick="btnElimnarFelicitacion('{{ $que->IDLIBRO_FELICITACION }}' )"><i class="las la-trash-alt text-secondary font-16 text-danger"></i></button>
                </td>
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