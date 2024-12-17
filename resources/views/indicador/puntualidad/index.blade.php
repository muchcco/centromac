@extends('layouts.layout')


@section('main')
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="row">
                    <div class="col">
                        <h4 class="page-title">puntualidad</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('inicio') }}"><i data-feather="home"
                                        class="align-self-center" style="height: 70%; display: block;"></i></a></li>
                        </ol>
                    </div><!--end col-->
                </div><!--end row-->
            </div><!--end page-title-box-->
        </div><!--end col-->
    </div><!--end row-->

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header" style="background-color:#132842">
                    <h4 class="card-title text-white">Leyenda</h4>
                </div><!--end card-header-->
                <div class="card-body bootstrap-select-1">
                    <div class="row">
                        <div class="col-md-5 border-end">
                            <table class="table table-bordered ">
                                {{-- <thead>
                                    <tr>
                                        <th class="text-center">SI</th>
                                        <th class="text-center">NO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-center">0 a 20 % <br /> Deficiente</td>
                                        <td class="text-center">20 a 40 % <br /> Regular</td>
                                    </tr>
                                </tbody> --}}
                                <tr>
                                    <th class="text-center" style="color: black !important">SI</th>
                                    <td>Módulo ocupado 15 minutos antes del inicio de atención al público del Centro MAC.
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-center" style="background: #2F75B5">NO</th>
                                    <td>Módulo que no estuvo ocupado 15 minutos antes del inicio de atención al público del
                                        Centro MAC. </td>
                                </tr>

                            </table>
                        </div>

                        {{-- <div class="col-md-6">
                            <h5>Reporte </h5>
                            <button class="btn btn-success" onclick="CambioReport()">Reportes</button>
                        </div> --}}

                    </div>
                </div><!-- end card-body -->
            </div> <!-- end card -->
        </div> <!-- end col -->
    </div> <!-- end row -->
    <section>
        <div class="row" id="table_evalua">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header" style="background-color:#132842">
                        <h4 class="card-title text-white">INDICADOR DE puntualidad CENTRO MAC -
                            @php
                                $us_id = auth()->user()->idcentro_mac;
                                $user = App\Models\User::join(
                                    'M_CENTRO_MAC',
                                    'M_CENTRO_MAC.IDCENTRO_MAC',
                                    '=',
                                    'users.idcentro_mac',
                                )
                                    ->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)
                                    ->first();

                                echo $user->NOMBRE_MAC;
                            @endphp
                        </h4>
                    </div><!--end card-header-->
                    <div class="card-body">

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="mb-3">Centro MAC:</label>
                                    <select name="mac" id="mac" class="form-select" onchange="SearchMac()">
                                        @role('Administrador|Moderador')
                                            <option value="" disabled selected>-- Seleccione una opción --</option>
                                            @forelse ($mac as $m)
                                                <option value="{{ $m->IDCENTRO_MAC }}">{{ $m->NOMBRE_MAC }}</option>
                                            @empty
                                                <option value="">SIN RESULTADOS</option>
                                            @endforelse
                                        @else
                                            @forelse ($mac as $m)
                                                <option value="{{ $m->IDCENTRO_MAC }}" disabled selected>{{ $m->NOMBRE_MAC }}
                                                </option>
                                            @empty
                                                <option value="">SIN RESULTADOS</option>
                                            @endempty
                                        @endrole
                                </select>
                            </div>

                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="mb-3">Mes:</label>
                                <select name="mes" id="mes" class="form-select" onchange="SearchMes()">
                                    <option value="" disabled selected>-- Seleccione una opción --</option>
                                    <option value="01">Enero</option>
                                    <option value="02">Febrero</option>
                                    <option value="03">Marzo</option>
                                    <option value="04">Abril</option>
                                    <option value="05">Mayo</option>
                                    <option value="06">Junio</option>
                                    <option value="07">Julio</option>
                                    <option value="08">Agosto</option>
                                    <option value="09">Setiembre</option>
                                    <option value="10">Octubre</option>
                                    <option value="11">Noviembre</option>
                                    <option value="12">Diciembre</option>
                                </select>
                            </div>
                        </div><!-- end col -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="mb-3">Año:</label>
                                <select name="año" id="año" class="form-select año"
                                    onchange="SearchAño()"></select>
                            </div>
                        </div><!-- end col -->
                        <div class="col-md-3">
                            <div class="form-group" style="margin-top:2.6em;">
                                <button type="button" class="btn btn-success" id="filtro"
                                    onclick="exec_data_excel()">
                                    <i class="fa fa-file-excel-o" aria-hidden="true"></i>
                                    Exportar
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive">
                                <div class="table-responsive" id="table_data">

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row" style="display: none" id="reporte_eval">
        @include('formatos.evaluacion_motivacional.reporte')
    </div>
</section>
@endsection


@section('script')
<script src="{{ asset('Script/js/sweet-alert.min.js') }}"></script>
<script src="{{ asset('Vendor/toastr/toastr.min.js') }}"></script>
<script src="{{ asset('//cdn.jsdelivr.net/npm/sweetalert2@11') }}"></script>

<!-- Plugins js -->
<script src="{{ asset('nuevo/plugins/select2/select2.min.js') }}"></script>
{{-- <script src="{{ asset('nuevo/plugins/huebee/huebee.pkgd.min.js') }}"></script> --}}
<script src="{{ asset('nuevo/plugins/timepicker/bootstrap-material-datetimepicker.js') }}"></script>
<script src="{{ asset('nuevo/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js') }}"></script>
<script src="{{ asset('nuevo/plugins/bootstrap-touchspin/js/jquery.bootstrap-touchspin.min.js') }}"></script>
{{-- <script src="{{ asset('nuevo/assets/pages/jquery.forms-advanced.js') }}"></script> --}}
<!-- Required datatable js -->
<script src="{{ asset('nuevo/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.js') }}"></script>
<!-- Buttons examples -->
<script src="{{ asset('nuevo/plugins/datatables/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('nuevo/plugins/datatables/buttons.bootstrap5.min.js') }}"></script>
<script src="{{ asset('nuevo/plugins/datatables/jszip.min.js') }}"></script>
<script src="{{ asset('nuevo/plugins/datatables/pdfmake.min.js') }}"></script>
<script src="{{ asset('nuevo/plugins/datatables/vfs_fonts.js') }}"></script>
<script src="{{ asset('nuevo/plugins/datatables/buttons.html5.min.js') }}"></script>
<script src="{{ asset('nuevo/plugins/datatables/buttons.print.min.js') }}"></script>
<script src="{{ asset('nuevo/plugins/datatables/buttons.colVis.min.js') }}"></script>
<!-- Responsive examples -->
<script src="{{ asset('nuevo/plugins/datatables/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('nuevo/assets/pages/jquery.datatable.init.js') }}"></script>


<script src="{{ asset('js/toastr.min.js') }}"></script>

<script>
    $(document).ready(function() {
        tabla_seccion();
        $('.select2').select2();
    });


    // Función SearchMac
    function SearchMac() {
        var mac = $('#mac').val();
        var mes = $('#mes').val() || new Date().getMonth() +
            1; // Toma el mes seleccionado o el mes actual si no está definido
        var año = $('#año').val() || new Date().getFullYear(); // Año actual si no está definido

        if (!mac) {
            @if (!auth()->user()->hasRole('Administrador|Moderador'))
                mac = '{{ auth()->user()->idcentro_mac }}'; // Asigna el MAC del usuario
            @endif
        }

        tabla_seccion(mac, mes, año); // Llamada a la función con valores correctos
    }

    // Función tabla_seccion
    function tabla_seccion(mac, mes, año) {
        $.ajax({
            type: 'GET',
            url: "{{ route('indicador.puntualidad.tablas.tb_index') }}", // Ruta que devuelve la vista en HTML
            data: {
                mes: mes,
                año: año,
                mac: mac
            }, // Envía los datos en forma de objeto
            beforeSend: function() {
                document.getElementById("table_data").innerHTML =
                    '<i class="fa fa-spinner fa-spin"></i> ESPERE LA TABLA ESTA CARGANDO... ';
            },
            success: function(data) {
                $('#table_data').html(data); // Inserta la vista en un contenedor en tu página
            }
        });
    }



    function SearchMes() {
        var mac = $('#mac').val();
        var mes = $('#mes').val() || new Date().getMonth() +
            1; // Toma el mes seleccionado o el mes actual si no está definido
        var año = $('#año').val() || new Date().getFullYear(); // Año actual si no está definido

        if (!mac) {
            @if (!auth()->user()->hasRole('Administrador|Moderador'))
                mac = '{{ auth()->user()->idcentro_mac }}'; // Asigna el MAC del usuario
            @endif
        }

        tabla_seccion(mac, mes, año); // Llamada a la función con valores correctos
    }

    function SearchAño() {
        var mac = $('#mac').val();
        var mes = $('#mes').val() || new Date().getMonth() +
            1; // Toma el mes seleccionado o el mes actual si no está definido
        var año = $('#año').val() || new Date().getFullYear(); // Año actual si no está definido

        if (!mac) {
            @if (!auth()->user()->hasRole('Administrador|Moderador'))
                mac = '{{ auth()->user()->idcentro_mac }}'; // Asigna el MAC del usuario
            @endif
        }

        tabla_seccion(mac, mes, año); // Llamada a la función con valores correctos
    }

    /**************************************************************** CARGAR COMBOS POR FECHA ACTUAL *************************************************************/
    function ComboAno() {
        var n = (new Date()).getFullYear()
        var select = document.querySelector(".año");
        for (var i = n; i >= 2023; i--) select.options.add(new Option(i, i));
    };
    window.onload = ComboAno;

    // Obtén el elemento select por su ID
    var mesSelect = document.getElementById('mes');

    // Obtén el mes actual (0 = enero, 1 = febrero, ..., 11 = diciembre)
    var mesActual = new Date().getMonth() + 1;

    console.log(mesActual);

    // Selecciona el mes actual en el select
    mesSelect.selectedIndex = mesActual

    /****************************************************************************** FIN ************************************************************************/

    function exec_data_excel() {
        var mac = $('#mac').val();
        var año = document.getElementById('año').value;
        var mes = document.getElementById('mes').value;

        // Definimos la vista dende se enviara
        var link_up = "{{ route('indicador.puntualidad.export_excel') }}";

        // Crear la URL con las variables como parámetros de consulta
        var href = link_up + '?mac=' + mac + '&año=' + año + '&mes=' + mes;

        console.log(href);

        var blank = "_blank";

        window.open(href);
    }
</script>
@endsection
