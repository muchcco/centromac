<div class="metric-strip">
    <div class="metric-item">
        <div class="metric-label">Horas generadas</div>
        <div class="metric-value">{{ $summary['total_horas'] }}</div>
    </div>
    <div class="metric-item">
        <div class="metric-label">Usadas aprobadas</div>
        <div class="metric-value">{{ $summary['usadas_horas'] }}</div>
    </div>
    <div class="metric-item">
        <div class="metric-label">Saldo disponible</div>
        <div class="metric-value">{{ $summary['saldo_horas'] }}</div>
    </div>
    <div class="metric-item">
        <div class="metric-label">Dias con extra</div>
        <div class="metric-value">{{ $summary['registros_extra'] }}</div>
    </div>
    <div class="metric-item">
        <div class="metric-label">Personas</div>
        <div class="metric-value">{{ $summary['personas'] }}</div>
    </div>
    <div class="metric-item">
        <div class="metric-label">Registros</div>
        <div class="metric-value">{{ $summary['registros'] }}</div>
    </div>
</div>

<div class="alert alert-info py-2 mb-3">
    <strong>Usadas aprobadas:</strong> considera solo las horas consumidas desde <code>/miasistencia</code> con estado APROBADO.
</div>

<div class="table-responsive">
    <table class="table table-hover table-bordered table-striped" id="table_reporte_asignacion">
        <thead class="tenca">
            <tr>
                <th>N</th>
                <th>Personal</th>
                <th>DNI</th>
                <th>Centro MAC</th>
                <th>Entidad</th>
                <th>Modulo</th>
                <th>Fecha</th>
                <th>Ingreso prog.</th>
                <th>Salida prog.</th>
                <th>Ingreso real</th>
                <th>Salida real</th>
                <th>Marc.</th>
                <th>Horas extra</th>
                <th>Usadas</th>
                <th>Saldo</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $i => $row)
                <tr class="{{ $row->minutos_saldo_disponible > 0 ? 'table-success' : ($row->minutos_usados_aprobados > 0 ? 'table-warning' : '') }}">
                    <td>{{ $i + 1 }}</td>
                    <td class="text-uppercase">{{ $row->nombre_completo }}</td>
                    <td>{{ $row->NUM_DOC }}</td>
                    <td>{{ $row->nombre_mac }}</td>
                    <td>{{ $row->entidad }}</td>
                    <td>{{ $row->modulo }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->FECHA)->format('d-m-Y') }}</td>
                    <td>{{ substr($row->ingreso_programado, 0, 5) }}</td>
                    <td>{{ substr($row->salida_programada, 0, 5) }}</td>
                    <td>{{ $row->asistencia_ingreso ? substr($row->asistencia_ingreso, 0, 5) : '-' }}</td>
                    <td>{{ $row->asistencia_salida ? substr($row->asistencia_salida, 0, 5) : '-' }}</td>
                    <td>{{ $row->total_marcaciones }}</td>
                    <td>
                        @if ($row->minutos_extra > 0)
                            <span class="badge bg-success">{{ $row->horas_extra }}</span>
                        @else
                            <span class="badge bg-secondary">00:00</span>
                        @endif
                    </td>
                    <td>
                        @if ($row->minutos_usados_aprobados > 0)
                            <span class="badge bg-primary">{{ $row->horas_usadas_aprobadas }}</span>
                        @else
                            <span class="badge bg-secondary">00:00</span>
                        @endif
                    </td>
                    <td>
                        @if ($row->minutos_saldo_disponible > 0)
                            <span class="badge bg-dark">{{ $row->horas_saldo_disponible }}</span>
                        @else
                            <span class="badge bg-secondary">00:00</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        $('#table_reporte_asignacion').DataTable({
            destroy: true,
            responsive: true,
            bLengthChange: true,
            autoWidth: false,
            searching: true,
            pageLength: 25,
            info: true,
            ordering: true,
            language: {
                url: "{{ asset('js/Spanish.json') }}"
            }
        });
    });
</script>
