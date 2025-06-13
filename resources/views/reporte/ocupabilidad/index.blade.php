@extends('layouts.layout')

@section('style')
    <link href="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('nuevo/plugins/datatables/buttons.bootstrap5.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet"/>
@endsection


@section('main')
    {{-- ───── Título y migas de pan ───── --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Reporte de Ocupabilidad</h4>
            </div>
        </div>
    </div>

    {{-- ───── Leyenda semáforo ───── --}}
    <div class="row">
        <div class="col-lg-12">
            <x-card title="Leyenda" color="#132842">
                <div class="row">
                    <div class="col-md-5 border-end">
                        <table class="table table-bordered mb-0">
                            <tr><th class="text-center bg-success">≥ 95 %</th><td>Cumple ANS</td></tr>
                            <tr><th class="text-center bg-warning">&gt; 84 % y &lt; 95 %</th><td>Cerca de cumplir</td></tr>
                            <tr><th class="text-center bg-danger">≤ 84 %</th><td>No cumple ANS</td></tr>
                        </table>
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    {{-- ───── Filtro Mes / Año ───── --}}
    <div class="row">
        <div class="col-lg-12">
            <x-card title="Filtro de Búsqueda" color="#132842">
                <div class="row">
                    <div class="col-md-3">
                        <label class="mb-2">Mes:</label>
                        <select id="mes" class="form-select">
                            <option value="" selected disabled>-- Mes --</option>
                            @foreach ([
                                '01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril',
                                '05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto',
                                '09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre'
                            ] as $v=>$txt)
                                <option value="{{ $v }}">{{ $txt }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="mb-2">Año:</label>
                        <select id="anio" class="form-select"></select>
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button id="btnBuscar" class="btn btn-primary me-1" onclick="buscar()">
                            <i class="fa fa-search"></i> Buscar
                        </button>
                        <button id="btnLimpiar" class="btn btn-dark" onclick="limpiar()">
                            <i class="fa fa-undo"></i> Limpiar
                        </button>
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button id="btnExcel" class="btn btn-success" onclick="exportar()">
                            <i class="fa fa-file-excel-o"></i> Exportar
                        </button>
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    {{-- ───── Tabla resultado ───── --}}
    <div class="row">
        <div class="col-lg-12">
            <x-card title="Lista de Módulos" color="#132842">
                <div class="table-responsive" id="table_data"><!-- ajax --></div>
            </x-card>
        </div>
    </div>
@endsection


@section('script')
    <script src="{{ asset('nuevo/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/dataTables.responsive.min.js') }}"></script>

    <script>
        /* ─── Llenar combo año dinámico ─── */
        (() => {
            const sel = document.getElementById('anio');
            const hoy = new Date().getFullYear();
            for (let y = hoy; y >= 2023; y--) {
                sel.add(new Option(y, y));
            }
        })();

        /* ─── Buscar ─── */
        function buscar() {
            const mes  = $('#mes').val(),
                  anio = $('#anio').val();

            if (!mes || !anio) {
                alert('Seleccione Mes y Año');
                return;
            }

            $.ajax({
                url : "{{ route('ocupabilidad.tb_index') }}",   // ajusta al nombre de ruta
                type: 'GET',
                data: { mes, año: anio },
                beforeSend() { $('#btnBuscar').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>'); },
                success(res) { $('#table_data').html(res); },
                error()  { alert('Error al obtener datos'); },
                complete(){ $('#btnBuscar').prop('disabled', false).html('Buscar'); }
            });
        }

        function limpiar() {
            $('#mes').val('');
            $('#anio').val('');
            $('#table_data').empty();
        }

        function exportar() {
            const mes = $('#mes').val(), anio = $('#anio').val();
            if (!mes || !anio) { alert('Seleccione Mes y Año'); return; }
            window.location = "{{ route('ocupabilidad.export_excel') }}?mes="+mes+"&año="+anio;
        }

        /* Autoseleccionar mes actual */
        document.getElementById('mes').value = ('0'+(new Date().getMonth()+1)).slice(-2);
    </script>
@endsection
