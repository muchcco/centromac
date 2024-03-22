<table>
    <tr>
        <td></td>
    </tr>
</table>

<table >
    <tr>                        
        <th style="border: 1px solid black" rowspan="3" colspan="3"></th>
        <th style="border: 1px solid black" colspan="28" rowspan="2">
            REPORTE CONSOLIDADO DE OCUPABILIDAD DE LOS MÓDULOS DE LAS ENTIDADES PARTICIPANTES POR MES<br />
            Período evaluado Enero a diciembre {{ $fecha_año }}
        </th>
        <th style="border: 1px solid black"> Código</th>
        <th style="border: 1px solid black" colspan="2">ANS1</th>
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
        <td colspan="19" style="text-align: start !important; border: 1px solid #2F75B5;">Módulo con presencia de asesor(a) de servicio de la entidad participante</td> 
    </tr>
    <tr>
        <td style="color: white; border: 1px solid #2F75B5; background: #2F75B5; text-align: center;">NO</td>
        <td colspan="19" style="text-align: start !important;border: 1px solid #2F75B5;">Módulo que estuvo sin presencia de asesor(a) de servicio, durante un día de operación del Centro MAC</td>      
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
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_1 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5; ' }}">
                    @if ($q->DIA_1 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_2 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_2 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_3 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_3 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_4 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_4 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_5 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_5 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_6 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_6 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_7 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_7 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_8 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_8 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_9 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_9 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_10 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_10 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_11 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_11 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_12 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_12 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_13 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_13 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_14 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_14 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_15 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_15 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_16 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_16 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_17 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_17 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_18 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_18 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_19 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_19 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_20 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_20 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_21 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_21 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_22 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_22 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_23 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_23 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_24 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_24 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_25 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_25 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_26 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_26 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_27 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_27 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_28 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_28 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_29 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_29 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_30 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_30 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td  style="border: 1px solid #2F75B5; {{ $q->DIA_31 > 0 ? 'color: black !important; background: none;' : 'color: white; background: #2F75B5;' }}">
                    @if ($q->DIA_31 > 0)
                        <span class="text-center">SI</span>
                    @else
                        <span class="text-center">NO</span>
                    @endif
                </td>
                <td style="border: 1px solid #2F75B5"></td>
             </tr>
            
        @empty
            <tr>
                <td colspan="34" class="text-center" style="border: 1px solid black">No hay datos disponibles</td>
            </tr>
        @endforelse
    </tbody>
</table>