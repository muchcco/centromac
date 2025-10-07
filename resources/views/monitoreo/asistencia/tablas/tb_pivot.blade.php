<table class="table table-bordered table-striped text-center align-middle">
    <thead style="background-color:#8B0000; color:white;">
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
                    <td>{!! $estado !!}</td>
                @endforeach
            </tr>
        @empty
            <tr>
                <td colspan="{{ $diasMes + 1 }}">No hay registros para este mes.</td>
            </tr>
        @endforelse
    </tbody>
</table>
