<table id="tabla_monitoreo_modulos" class="table table-hover table-bordered table-striped align-middle">
    <thead class="tenca">
        <tr>
            <th style="width: 5%">N°</th>
            <th style="width: 15%">Centro MAC</th>
            <th style="width: 15%">Número del Módulo</th>
            <th style="width: 25%">Entidad</th>
            <th style="width: 12%">Fecha Inicio</th>
            <th style="width: 12%">Fecha Fin</th>
            <th style="width: 10%">Tipo</th>
            <th style="width: 10%">Estado</th>
        </tr>
    </thead>

    <tbody>
        @forelse ($modulos as $index => $modulo)
        @php
        $inicioModulo = $modulo->FECHAINICIO
        ? \Carbon\Carbon::parse($modulo->FECHAINICIO)
        : \Carbon\Carbon::parse($fechaInicioMes);

        $finModulo = $modulo->FECHAFIN
        ? \Carbon\Carbon::parse($modulo->FECHAFIN)
        : null;

        $inicioPeriodo = \Carbon\Carbon::parse($fechaInicioMes);
        $finPeriodo = \Carbon\Carbon::parse($fechaFinMes);

        if ($finModulo && $finModulo->between($inicioPeriodo, $finPeriodo)) {
        $estadoPeriodo = '<span class="badge bg-warning text-dark">Vence</span>';
        } elseif ($inicioModulo->between($inicioPeriodo, $finPeriodo)) {
        $estadoPeriodo = '<span class="badge bg-info text-dark">Inicia</span>';
        } else {
        $estadoPeriodo = '<span class="badge bg-success">Activo</span>';
        }

        $tipo = $modulo->ES_ADMINISTRATIVO === 'SI'
        ? '<span class="badge bg-secondary">Administrativo</span>'
        : '<span class="badge bg-primary">Atención</span>';
        @endphp

        <tr>
            <td>{{ $index + 1 }}</td>

            <td class="text-uppercase">
                {{ $modulo->NOMBRE_MAC ?? 'NO REGISTRADO' }}
            </td>

            <td class="text-uppercase">
                <strong>{{ $modulo->N_MODULO }}</strong>
                <br>
                <small class="text-muted">ID: {{ $modulo->IDMODULO }}</small>
            </td>

            <td class="text-uppercase">
                {{ $modulo->NOMBRE_ENTIDAD ?? 'SIN ENTIDAD' }}
            </td>

            <td>
                {{ $modulo->FECHAINICIO ? \Carbon\Carbon::parse($modulo->FECHAINICIO)->format('d-m-Y') : 'Sin datos' }}
            </td>

            <td>
                {{ $modulo->FECHAFIN ? \Carbon\Carbon::parse($modulo->FECHAFIN)->format('d-m-Y') : 'Sin datos' }}
            </td>

            <td class="text-center">
                {!! $tipo !!}
            </td>

            <td class="text-center">
                {!! $estadoPeriodo !!}
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center text-muted">
                No se encontraron módulos activos para el periodo seleccionado.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>