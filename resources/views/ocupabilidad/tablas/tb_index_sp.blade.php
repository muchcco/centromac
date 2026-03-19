<style>
    #table_formato2 tbody tr:hover {
        background-color: #f2f6ff;
        transition: 0.2s;
    }

    .progress-bar {
        transition: width 0.6s ease;
    }
</style>

{{-- 🔥 KPIs --}}
<div class="row mb-3">

    <div class="col-md-3">
        <div class="card text-center shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Ocupabilidad General</h6>
                <h4 class="fw-bold text-primary">{{ $promedio }}%</h4>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-center shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Cumplen ANS</h6>
                <h4 class="fw-bold text-success">{{ $cumplen }}</h4>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-center shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">No Cumplen</h6>
                <h4 class="fw-bold text-danger">{{ $noCumplen }}</h4>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-center shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Total Módulos</h6>
                <h4 class="fw-bold">{{ $total }}</h4>
            </div>
        </div>
    </div>

</div>

{{-- 🔥 TABLA --}}
<table class="table table-hover table-bordered table-striped" id="table_formato2">

    <thead class="tenca">
        <tr>
            <th>MÓDULO</th>
            <th>ENTIDAD / MAC</th>
            <th>DÍAS MARCADOS</th>
            <th>DÍAS HÁBILES</th>
            <th>PORCENTAJE</th>
            <th>ESTADO</th>
            <th>OCUPABILIDAD</th>
        </tr>
    </thead>

    <tbody>
        @forelse ($data as $fila)
            @php
                $pct = $fila->DIAS_HABILES > 0 ? ($fila->DIAS_ASISTENCIA / $fila->DIAS_HABILES) * 100 : 0;

                $pct = round($pct, 2);

                $barClass = $pct >= 95 ? 'bg-success' : ($pct >= 84 ? 'bg-warning' : 'bg-danger');
            @endphp

            <tr> {{-- 🔥 SIN COLOR EN TODA LA FILA --}}

                <td><strong>{{ $fila->N_MODULO }}</strong></td>

                <td>
                    {{ $fila->NOMBRE_ENTIDAD }}
                    <br>
                    <small class="text-muted">{{ $nombreMac }}</small>
                </td>

                <td>{{ $fila->DIAS_ASISTENCIA }}</td>

                <td>{{ $fila->DIAS_HABILES }}</td>

                <td><strong>{{ number_format($pct, 2) }}%</strong></td>

                {{-- 🔥 ESTADO --}}
                <td>
                    @if ($pct >= 95)
                        <span class="badge bg-success">Cumple</span>
                    @elseif ($pct >= 84)
                        <span class="badge bg-warning text-dark">En Riesgo</span>
                    @else
                        <span class="badge bg-danger">Crítico</span>
                    @endif
                </td>

                {{-- 🔥 BARRA --}}
                <td title="Asistencia: {{ $fila->DIAS_ASISTENCIA }} / {{ $fila->DIAS_HABILES }}">
                    <div class="progress rounded-pill" style="height:20px;">
                        <div class="progress-bar {{ $barClass }}" style="width:{{ $pct }}%;">
                            {{ number_format($pct, 1) }}%
                        </div>
                    </div>
                </td>

            </tr>

        @empty
            <tr>
                <td colspan="7" class="text-center">No hay datos disponibles</td>
            </tr>
        @endforelse

    </tbody>
</table>
<script>
(function () {

    const esMesActual = @json($esMesActual);
    const fechaCierre = @json($fechaCierre);
    const fechaHabiles = @json($fechaHabiles);

    if (esMesActual && fechaCierre) {

        Swal.fire({
            icon: 'info',
            title: '<span style="font-size:22px;font-weight:700;">📊 Corte de Información</span>',
            html: `
                <div style="font-size:15px;line-height:1.7;text-align:left;">
                    
                    <div>
                        <b>📌 Datos marcados:</b><br>
                        hasta <b style="color:#132842;">${fechaCierre}</b>
                    </div>

                    <hr style="margin:10px 0;">

                    <div>
                        <b>📅 Días hábiles considerados:</b><br>
                        hasta <b style="color:#132842;">${fechaHabiles}</b>
                    </div>

                    <div style="margin-top:10px;color:#6c757d;font-size:13px;">
                        (El día actual no se considera hasta su cierre completo)
                    </div>

                </div>
            `,
            confirmButtonText: '✔ Entendido',
            backdrop: 'rgba(19,40,66,0.4)',
            customClass: {
                popup: 'shadow-lg rounded-4',
                confirmButton: 'btn btn-primary px-4'
            },
            buttonsStyling: false
        });

    }

})();
</script>