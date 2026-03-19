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
                <h6 class="text-muted">Puntualidad General (8:16)</h6>
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
            <th>PUNTUALES</th>
            <th>DÍAS MARCADOS</th>
            <th>PORCENTAJE</th>
            <th>ESTADO</th>
            <th>PUNTUALIDAD</th>
        </tr>
    </thead>

    <tbody>
        @forelse ($resultados as $r)
            @php
                $pct = $r->DIAS_ASISTENCIA > 0 ? ($r->PUNTUALES_816 / $r->DIAS_ASISTENCIA) * 100 : 0;

                $pct = round($pct, 2);

                $barClass = $pct >= 95 ? 'bg-success' : ($pct >= 84 ? 'bg-warning' : 'bg-danger');
            @endphp

            <tr> {{-- 🔥 sin fondo rojo completo --}}

                <td><strong>{{ $r->N_MODULO }}</strong></td>

                <td>
                    {{ $r->NOMBRE_ENTIDAD }}
                    <br>
                    <small class="text-muted">{{ $r->NOMBRE_MAC }}</small>
                </td>

                <td class="">
                    {{ $r->PUNTUALES_816 }}
                </td>

                <td>{{ $r->DIAS_ASISTENCIA }}</td>

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
                <td title="Puntuales: {{ $r->PUNTUALES_816 }} / {{ $r->DIAS_ASISTENCIA }}">
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
