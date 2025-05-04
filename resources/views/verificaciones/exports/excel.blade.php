<table>
    <thead>
        <!-- Primer encabezado con título unificado para las 6 columnas -->
        <tr>
            <th colspan="6" style="text-align: center; font-size: 16px; font-weight: bold; border: none; width: 100%;">
                FORMATO 11 - PM 1.2.1 MONITOREO DE GESTION (CHECK LIST OPERATIVO)
            </th>
        </tr>

        <!-- Segundo encabezado sin el logo (El logo se maneja desde el controlador) -->
        <tr>
            <!-- Logo Celda con 100px de ancho y altura ajustada -->
            <th
                style="border: 1px solid black; width: 100px; height: 100px; text-align: center; vertical-align: middle;">
                <!-- Logo se maneja desde el controlador -->
            </th>

            <!-- Texto de REGISTRO DE CHECK LIST OPERATIVO en las otras 5 celdas -->
            <th colspan="5"
                style="text-align: center; font-size: 16px; font-weight: bold; border: 1px solid black; width: 100%; text-align: center; vertical-align: middle; height: 100px;">
                REGISTRO DE CHECK LIST OPERATIVO
            </th>
        </tr>
        <!-- Tercer encabezado con las 6 columnas del formato -->
        <tr>
            <th
                style="background-color: #132842; color: white; border: 1px solid black; text-align: center; vertical-align: middle; height: 70px;">
                TIPO DE EJECUCIÓN
            </th>
            <th
                style="background-color: #132842; color: white; border: 1px solid black; text-align: center; vertical-align: middle; height: 70px;">
                OBSERVACIONES
            </th>
            <th
                style="background-color: #132842; color: white; border: 1px solid black; text-align: center; vertical-align: middle; height: 70px;">
                FECHA
            </th>
            <th
                style="background-color: #132842; color: white; border: 1px solid black; text-align: center; vertical-align: middle; height: 70px;">
                HORA
            </th>
            <th
                style="background-color: #132842; color: white; border: 1px solid black; text-align: center; vertical-align: middle; height: 70px;">
                ACCIÓN RESULTADO
            </th>
            <th
                style="background-color: #132842; color: white; border: 1px solid black; text-align: center; vertical-align: middle; height: 70px;">
                RESPONSABLE
            </th>

        </tr>
    </thead>
    <tbody>
    <tbody>
        @foreach ($verificaciones as $verificacion)
            <tr>
                <td
                    style="border: 1px solid black; text-align: center; vertical-align: middle; word-wrap: break-word; width: 180px;">
                    {{ $verificacion['tipoEjecucion'] }}</td>
                <td
                    style="border: 1px solid black; text-align: center; vertical-align: middle; word-wrap: break-word; width: 180px;">
                    {{ $verificacion['observaciones'] }}</td>
                <td
                    style="border: 1px solid black; text-align: center; vertical-align: middle; word-wrap: break-word; width: 180px;">
                    {{ $verificacion['fecha'] }}</td>
                <td
                    style="border: 1px solid black; text-align: center; vertical-align: middle; word-wrap: break-word; width: 180px;">
                    {{ $verificacion['hora'] }}</td>
                <td
                    style="border: 1px solid black; text-align: center; vertical-align: middle; word-wrap: break-word; width: 180px;">
                    {{ $verificacion['porcentajeSi'] }}%</td>
                <td
                    style="border: 1px solid black; text-align: center; vertical-align: middle; word-wrap: break-word; width: 180px;">
                    {{ $verificacion['responsable'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
<table>
    <tr>
        <td colspan="6" style="text-align: center; font-size: 16px; font-weight: bold;">
            Total de registros: {{ $totalRegistros }}
        </td>
    </tr>
</table>    
