<table class="table table-hover table-bordered table-striped" id="table_formato">
    <tbody>
        <tr>                        
            <td style="border: 1px solid black" rowspan="3" colspan="2"><img src="{{ asset('imagen/mac_logo_export.jpg') }}" alt="" width="230px"></td>
            <td style="border: 1px solid black" colspan="8" rowspan="2">
                REPORTE CONSOLIDADO DE OCUPABILIDAD DE LOS MÓDULOS DE LAS ENTIDADES PARTICIPANTES POR MES<br />
                <span class="text-danger text-center">Período evaluado Enero a diciembre {{ $fecha_año }}</span> 
            </td>
            <td style="border: 1px solid black"> Código</td>
            <td style="border: 1px solid black" colspan="2">ANS1</td>
        </tr>
        <tr>
            <td style="border: 1px solid black">Versión</td>
            <td style="border: 1px solid black" colspan="2">1.0.0</td>
        </tr>
        <tr>
            <td style="border: 1px solid black">Centro MAC</td>
            <td style="border: 1px solid black"> {{ $name_mac }}</td>
            <td style="border: 1px solid black" colspan="2">MES:</td>
            <td style="border: 1px solid black" colspan="7" > 
                <?php
                    setlocale(LC_TIME, 'es_PE', 'Spanish_Spain', 'Spanish');

                    $numero_mes = (int)$fecha_mes; // Asegúrate de que $fecha_mes sea un entero
                    $nombre_mes = '';

                    $nombres_meses = [
                        1 => 'Enero',
                        2 => 'Febrero',
                        3 => 'Marzo',
                        4 => 'Abril',
                        5 => 'Mayo',
                        6 => 'Junio',
                        7 => 'Julio',
                        8 => 'Agosto',
                        9 => 'Septiembre',
                        10 => 'Octubre',
                        11 => 'Noviembre',
                        12 => 'Diciembre'
                    ];

                    if (isset($nombres_meses[$numero_mes])) {
                        $nombre_mes = $nombres_meses[$numero_mes];
                    } else {
                        // Manejar el caso en que el número de mes no sea válido
                        $nombre_mes = 'Mes no válido';
                    }

                    echo $nombre_mes;
                ?>

            </td>
            
        </tr>
    </tbody>
</table>

<table class="table table-hover table-bordered table-striped" id="table_formato">
    <thead class="tenca">
        <tr>
            <th>MODULOS</th>
            <th>NOMBRE DE LAS ENTIDADES</th>
            <th>1</th>
            <th>2</th>
            <th>3</th>
            <th>4</th>
            <th>5</th>
            <th>6</th>
            <th>7</th>
            <th>8</th>
            <th>9</th>
            <th>10</th>
            <th>11</th>
            <th>12</th>
            <th>13</th>
            <th>14</th>
            <th>15</th>
            <th>16</th>
            <th>17</th>
            <th>18</th>
            <th>19</th>
            <th>20</th>
            <th>21</th>
            <th>22</th>
            <th>23</th>
            <th>24</th>
            <th>25</th>
            <th>26</th>
            <th>27</th>
            <th>28</th>
            <th>29</th>
            <th>30</th>
            <th>31</th>
            <th>OBSERVACIONES O COMENTARIOS</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($query as $q)
            <tr>
                <td>{{ $q->N_MODULO }}</td>
                <td>{{ $q->NOMBRE_ENTIDAD }}</td>
                @for ($i = 1; $i <= 31; $i++)
                    @if ($q->NOMBRE_ENTIDAD == 'BANCO DE LA NACION')
                        <td style="{{ $q->{'DIA_' . $i} > 0 ? 'color: black !important; background: none' : 'background: #2F75B5; color: white !important' }}">
                            @if ($q->{'DIA_' . $i} > 0)
                                <span class="text-center">SI</span>
                            @else
                                <span class="text-center">NO</span>
                            @endif
                        </td>
                    @else
                        <td style="{{ $q->{'DIA_' . $i} > 0 ? 'color: black !important; background: none' : 'background: #2F75B5; color: white !important' }}">
                            @if ($q->{'DIA_' . $i} > 0)
                                <span class="text-center">SI</span>
                            @else
                                <span class="text-center">NO</span>
                            @endif
                        </td>
                    @endif
                    
                @endfor


                <td></td>
            </tr>
            
        @empty
            <tr>
                <td colspan="34" class="text-center">No hay datos disponibles</td>
            </tr>
        @endforelse
    </tbody>
</table>