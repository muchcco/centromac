<table class="table table-hover table-bordered table-striped" id="table_monitoreo_asistencia">
    <thead class="tenca">
        <tr>
            <th>N°</th>
            <th>Centro MAC</th>
            <th>Fecha de cierre</th>
            <th>Fecha de registro</th>
            <th>Usuario que cerró</th>
            <th>Tipo de cierre</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($cierres as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="text-uppercase">{{ $item->nombre_mac }}</td>

                {{-- 🔹 Fecha del cierre --}}
                <td>
                    @if ($item->fecha)
                        {{ \Carbon\Carbon::parse($item->fecha)->format('d-m-Y') }}
                    @else
                        <span class="badge bg-secondary">Sin cierre</span>
                    @endif
                </td>

                {{-- 🔹 Fecha de registro --}}
                <td>
                    @if ($item->fecha_registro)
                        {{ \Carbon\Carbon::parse($item->fecha_registro)->format('d-m-Y H:i') }}
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>

                {{-- 🔹 Usuario --}}
                <td>{{ $item->user_nombre ?? '—' }}</td>

                {{-- 🔹 Tipo de cierre --}}
                <td>
                    @if ($item->tipo_cierre == 'MES')
                        <span class="badge bg-danger">Cierre Mensual</span>
                    @elseif ($item->tipo_cierre == 'DIA')
                        <span class="badge bg-success">Cierre Diario</span>
                    @else
                        <span class="badge bg-secondary">Desconocido</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center text-muted">No hay cierres registrados para este mes.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<script>
    $(document).ready(function() {
        $('#table_monitoreo_asistencia').DataTable({
            responsive: true,
            bLengthChange: true,
            pageLength: 20,
            lengthMenu: [
                [10, 20, 40, -1],
                [10, 20, 40, "Todos"]
            ],
            autoWidth: false,
            searching: true,
            ordering: true,
            info: true,
            language: {
                url: "{{ asset('js/Spanish.json') }}"
            }
        });
    });
</script>
