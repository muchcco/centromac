@if (!$disponible)
    <div class="alert alert-warning mb-0">
        Falta crear la tabla <strong>d_personal_asistencia_dia</strong>.
    </div>
@elseif ($dias->isEmpty())
    <div class="text-muted font-13 border rounded p-3">
        Sin dias especiales registrados.
    </div>
@else
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-striped mb-0">
            <thead class="table-light">
                <tr>
                    <th>Fecha</th>
                    <th>Horario</th>
                    <th class="text-center">Accion</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dias as $dia)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($dia->fecha)->format('d-m-Y') }}</td>
                        <td>{{ substr($dia->hora_ingreso, 0, 5) }} - {{ substr($dia->hora_salida, 0, 5) }}</td>
                        <td class="text-center">
                            <button type="button" class="nobtn" onclick="eliminarDiaEspecial('{{ $dia->id }}')">
                                <i class="las la-trash-alt text-danger font-16"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
