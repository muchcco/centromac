<table class="table table-bordered table-striped text-center align-middle">
    <thead>
        <tr>
            <th>Centro MAC</th>
            @for ($d = 1; $d <= $diasMes; $d++)
                <th>{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}</th>
            @endfor
        </tr>
    </thead>
    <tbody>
        @forelse ($pivotData as $fila)
            <tr>
                <td class="fw-bold text-uppercase">{{ $fila['mac'] }}</td>
                @foreach ($fila['dias'] as $estado)
                    <td class="text-center p-0 align-middle">{!! $estado !!}</td>
                @endforeach
            </tr>
        @empty
            <tr>
                <td colspan="{{ $diasMes + 1 }}" class="text-center text-danger fw-bold">
                    No hay registros para este mes.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

{{-- 🎨 Estilos visuales --}}
<style>
    /* Encabezado */
    .table thead th {
        background-color: #132842 !important;
        color: #fff !important;
        text-align: center;
        font-weight: bold;
        border: 1px solid #ffffff;
    }

    /* Bordes suaves */
    .table-bordered th,
    .table-bordered td {
        border: 1px solid #ccc !important;
        vertical-align: middle !important;
        padding: 3px;
        height: 30px;
    }

    /* Hover en filas */
    .table tbody tr:hover {
        background-color: #f7f9fb !important;
    }

    /* ✅ Cerrado */
    .text-success {
        color: #198754 !important;
        font-weight: bold;
        font-size: 14px;
    }

    /* ❌ No cerrado */
    .text-danger {
        color: #dc3545 !important;
        font-weight: bold;
        font-size: 14px;
    }

    /* – Futuro */
    .text-secondary {
        color: #6c757d !important;
        font-size: 13px;
    }

    /* 🎨 DOMINGOS → gris completo */
    .celda-domingo {
        background-color: #b0b0b0 !important;
        width: 100%;
        height: 100%;
        border-radius: 0;
        display: block;
    }

    /* 🎉 FERIADOS → azul MAC completo */
    .celda-feriado {
        background-color: #132842 !important;
        width: 100%;
        height: 100%;
        border-radius: 0;
        display: block;
    }

    /* Compacto en pantallas pequeñas */
    @media (max-width: 992px) {

        .table th,
        .table td {
            padding: 2px !important;
            font-size: 11px !important;
        }
    }
</style>
