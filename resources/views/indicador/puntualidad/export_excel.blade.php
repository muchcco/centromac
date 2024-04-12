<table>
    <tr>
        <td></td>
    </tr>
</table>

<table >
    <tr>                        
        <th style="border: 1px solid black" rowspan="3" colspan="3"></th>
        <th style="border: 1px solid black" colspan="28" rowspan="2">
            REPORTE CONSOLIDADO DE PUNTUALIDAD DE OCUPABILIDAD DE LOS MÓDULOS DE LAS ENTIDADES PARTICIPANTES POR MES<br />
            Período evaluado Enero a diciembre {{ $fecha_año }}
        </th>
        <th style="border: 1px solid black"> Código</th>
        <th style="border: 1px solid black" colspan="2">ANS2</th>
    </tr>
    <tr>
        <th style="border: 1px solid black">Versión</th>
        <th style="border: 1px solid black" colspan="2">1.0.0</th>
    </tr>
    <tr>
        <th style="border: 1px solid black" colspan="2">Centro MAC</th>
        <th style="border: 1px solid rgb(0, 0, 0)" colspan="15">{{ $name_mac }} </th>
        <th style="border: 1px solid black" colspan="2">MES:</th>
        <th style="border: 1px solid black" colspan="12" >{{ $nombre_mes }}</th>        
    </tr>
</table>

<table>
    <tr>
        <td rowspan="2" colspan="2" style="text-align: end; border: none;">Leyenda</td>
        <td style="border: 1px solid #2F75B5; text-align: center;">SI</td>
        <td colspan="19" style="text-align: start !important; border: 1px solid #2F75B5;">Módulo ocupado 15 minutos antes del inicio de atención al público del Centro MAC. </td> 
    </tr>
    <tr>
        <td style="color: white; border: 1px solid #2F75B5; background: #2F75B5; text-align: center;">NO</td>
        <td colspan="19" style="text-align: start !important;border: 1px solid #2F75B5;">Módulo que no estuvo ocupado 15 minutos antes del inicio de atención al público del Centro MAC. </td>      
    </tr>
</table>

<table class="table table-bor" style="border: 1px solid black">
    <thead style="background: #3D61B2; color:#fff;">
        <tr style="border: 1px solid black; color: #fff;">
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">MODULOS</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">NOMBRE DE LAS ENTIDADES</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">1</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">2</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">3</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">4</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">5</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">6</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">7</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">8</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">9</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">10</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">11</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">12</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">13</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">14</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">15</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">16</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">17</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">18</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">19</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">20</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">21</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">22</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">23</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">24</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">25</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">26</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">27</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">28</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">29</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">30</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">31</th>
            <th style="color: white; border: 1px solid black; background-color: #0B22B4; ">OBSERVACIONES O COMENTARIOS</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($query as $q)
            <tr>
                <td style="border: 1px solid #2F75B5">{{ $q-> N_MODULO }}</td>
                <td style="border: 1px solid #2F75B5">{{ $q-> NOMBRE_ENTIDAD }}</td>
                @for ($i = 1; $i <= 31; $i++)
                    <td style="border: 1px solid #2F75B5; {{ isset($q->{'DIA_' . $i}) && $q->{'DIA_' . $i} < '08:16:00' ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5; ' }}">
                        @if (isset($q->{'DIA_' . $i}) && $q->{'DIA_' . $i} < '08:16:00')
                            <span class="text-center">SI</span>
                        @else
                            <span class="text-center">NO</span>
                        @endif
                    </td>
                @endfor
                <td style="border: 1px solid #2F75B5"></td>
             </tr>
            
        @empty
            <tr>
                <td colspan="34" class="text-center" style="border: 1px solid black">No hay datos disponibles</td>
            </tr>
        @endforelse
    </tbody>
</table>