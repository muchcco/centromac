<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Detalle del biometrico registrado </h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            
            <h5>Detalle de los ingresos del día <?php setlocale(LC_TIME, 'es_PE', 'Spanish_Spain', 'Spanish'); echo strftime('%d de %B del %Y',strtotime($fecha_));  ?></h5>

            <table class="table table-bordered">
                <tr class="bg-dark" style="color: #fff;">
                    <th>N°</th>
                    <th>Hora entrada</th>
                    <th>Hora salida (refrigerio)</th>
                    <th>Hora entrada (refrigerio)</th>
                    <th>Hora salida</th>
                </tr>
                @forelse ($query as $i => $q)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $q->HORA_1 }}</td>
                        <td>{{ $q->HORA_2 }}</td>
                        <td>{{ $q->HORA_3 }}</td>
                        <td>{{ $q->HORA_4 }}</td>
                    </tr>
                @empty
                    <tr>
                        <th colspan="5">No hay datos disponibles</th>
                    </tr>
                @endforelse
            </table>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
        </div>
    </div>
</div>

<script>
    tippy(".bandejTool", {
        allowHTML: true,
        followCursor: true,
    });
</script>