<table style="width:100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 13px;">
    <thead>

        <!-- 🔷 TITULO -->
        <tr>
            <th colspan="6"
                style="text-align: center; font-size: 16px; font-weight: bold; border: none; padding: 10px;">
                FORMATO 11 - PM 1.2.1 MONITOREO DE GESTION (CHECK LIST OPERATIVO)
            </th>
        </tr>

        <!-- 🔷 SUBTITULO + LOGO -->
        <tr>
            <th style="border: 1px solid black; width: 100px; height: 80px; text-align: center; vertical-align: middle;">
                <!-- LOGO LO PONE EL EXPORT -->
            </th>

            <th colspan="5"
                style="text-align: center; font-size: 15px; font-weight: bold; border: 1px solid black; vertical-align: middle;">
                REGISTRO DE CHECK LIST OPERATIVO
            </th>
        </tr>

        <!-- 🔷 CABECERA -->
        <tr>
            <th style="background:#132842; color:white; border:1px solid black; text-align:center;">
                TIPO DE EJECUCIÓN
            </th>
            <th style="background:#132842; color:white; border:1px solid black; text-align:center;">
                OBSERVACIONES
            </th>
            <th style="background:#132842; color:white; border:1px solid black; text-align:center;">
                FECHA
            </th>
            <th style="background:#132842; color:white; border:1px solid black; text-align:center;">
                HORA
            </th>
            <th style="background:#132842; color:white; border:1px solid black; text-align:center;">
                ACCIÓN / RESULTADO
            </th>
            <th style="background:#132842; color:white; border:1px solid black; text-align:center;">
                RESPONSABLE
            </th>
        </tr>

    </thead>

    <tbody>
        @forelse ($verificaciones as $verificacion)
            <tr>

                <td style="border:1px solid black; text-align:center; padding:5px;">
                    {{ $verificacion['tipoEjecucion'] }}
                </td>

                <td style="border:1px solid black; padding:5px;">
                    {{ $verificacion['observaciones'] }}
                </td>

                <td style="border:1px solid black; text-align:center;">
                    {{ $verificacion['fecha'] }}
                </td>

                <td style="border:1px solid black; text-align:center;">
                    {{ $verificacion['hora'] }}
                </td>

                <!-- 🔥 COLOR DINÁMICO -->
                <td
                    style="border:1px solid black; text-align:center; font-weight:bold;
                    color:
                        {{ $verificacion['porcentajeSi'] >= 95
                            ? '#198754'
                            : ($verificacion['porcentajeSi'] >= 85
                                ? '#ffc107'
                                : '#dc3545') }}">
                    {{ $verificacion['porcentajeSi'] }}%
                </td>

                <td style="border:1px solid black; text-align:center;">
                    {{ $verificacion['responsable'] }}
                </td>

            </tr>
        @empty
            <tr>
                <td colspan="6" style="text-align:center; padding:10px;">
                    No hay registros
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<br>

<!-- 🔷 TOTAL -->
<table style="width:100%;">
    <tr>
        <td colspan="6" style="text-align: center; font-size: 14px; font-weight: bold; padding: 10px;">
            Total de registros: {{ $totalRegistros }}
        </td>
    </tr>
</table>
