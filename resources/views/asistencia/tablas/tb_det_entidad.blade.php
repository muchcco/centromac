<table class="table table-hover table-bordered table-striped">
    <thead class="tenca">
        <tr>
            <th class="text-center" colspan="5">REPORTE PERSONALIZADO</th>
        </tr>
        <tr>
            <th class="th">N°</th>
            <th class="th">Entidad</th>
            <th class="th">N° de Asesores</th>
            <th class="th">Exportar por Mes completo</th>
            <th class="th">Exportar (personalizado)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $i => $dat)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $dat->NOMBRE_ENTIDAD }}</td>
                <td class="text-center">{{ $dat->COUNT_PER }}</td>
                <td class="text-center">
                    <button class="nobtn text-dark bandejTool" data-tippy-content="Descargar en formato Excel" id="btn-dow-excel-group" onclick="BtnDowloadExcel('{{ $dat->IDENTIDAD }}')" ><i class="fa fa-download" aria-hidden="true"></i></button>
                </td>
                <td class="text-center">
                    <button class="nobtn text-dark bandejTool" data-tippy-content="Descargar en formato Excel de forma personalizada" data-toggle="modal" data-target="#large-Modal" onclick="btnExcelPersonalizado('{{ $dat->IDENTIDAD }}')" ><i class="fa fa-download" aria-hidden="true"></i></button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="table table-hover table-bordered table-striped">
    <thead class="tenca">
        <tr>
            <th class="th text-center" colspan="5">REPORTE GENERAL</th>
        </tr>
        <tr>
            <th class="th">N°</th>
            <th class="th">Entidad</th>
            <th class="th">N° de Asesores</th>
            <th class="th">Exportar por Mes completo</th>
            <th class="th">Exportar (personalizado)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>Reporte de todas las entidades</td>
            <td></td>
            <td class="text-center">
                <button class="nobtn text-dark bandejTool" data-tippy-content="Descargar en formato Excel" id="btn-dow-excel-group" onclick="BtnDowloadExcelGeneral()" ><i class="fa fa-download" aria-hidden="true"></i></button>
            </td>
            <td class="text-center">
                <button class="nobtn text-dark bandejTool" disabled="true" data-tippy-content="Descargar en formato Excel de forma personalizada" data-toggle="modal" data-target="#large-Modal" onclick="btnExcelPersonalizado('')" ><i class="fa fa-download" aria-hidden="true"></i></button>
            </td>
        </tr>
    </tbody>
</table>