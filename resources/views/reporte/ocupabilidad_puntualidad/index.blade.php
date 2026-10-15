@extends('layouts.layout')

@section('style')

<link rel="stylesheet" href="{{ asset('Vendor/toastr/toastr.min.css') }}">

<link href="{{ asset('nuevo/plugins/select2/select2.min.css') }}" rel="stylesheet" type="text/css">

<link href="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css">

<link href="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">

<style>

    .reporte-op .card-header {

        background: #132842

    }

    .reporte-op .card-title {

        color: #fff;

        margin: 0

    }

    .reporte-op .etiqueta {

        font-weight: 600;

        color: #24354a;

        margin-bottom: 7px

    }

    .reporte-op .tarjeta-indicador {

        border: 1px solid #e5eaf0;

        border-radius: 12px;

        padding: 16px;

        background: #fff;

        height: 100%

    }

    .reporte-op .tarjeta-indicador .valor {

        font-size: 25px;

        font-weight: 700;

        color: #132842

    }

    .reporte-op .tarjeta-indicador .descripcion {

        font-size: 13px;

        color: #64748b

    }

    .reporte-op .aviso-pendiente {

        background: #f1f5f9;

        border: 1px solid #e2e8f0;

        border-radius: 10px;

        padding: 15px;

        color: #475569

    }

</style>

@endsection

@section('main')

<div class="row">

    <div class="col-sm-12">

        <div class="page-title-box">

            <div class="row">

                <div class="col">

                    <h4 class="page-title">Reporte de Ocupabilidad y Puntualidad</h4>

                    <ol class="breadcrumb">

                        <li class="breadcrumb-item"><a href="{{ route('inicio') }}"><i data-feather="home" class="align-self-center"></i></a></li>

                        <li class="breadcrumb-item">Reportes</li>

                        <li class="breadcrumb-item active">Ocupabilidad y Puntualidad</li>

                    </ol>

                </div>

            </div>

        </div>

    </div>

</div>

<div class="card mb-3 reporte-op">

    <div class="card-header">

        <h4 class="card-title">Filtro de Búsqueda</h4>

    </div>

    <div class="card-body">

        <div class="row g-3 align-items-end">

            <div class="col-md-4"><label class="etiqueta" for="filtro_mac">Centro MAC</label>

                @role('Administrador|Moderador|Monitor')

                <select id="filtro_mac" class="form-control select2" style="width:100%">

                    <option value="">-- Todos los MAC --</option>@foreach($centros_mac as $mac)<option value="{{ $mac->idcentro_mac }}">{{ $mac->nombre_mac }}</option>@endforeach

                </select>

                @else

                <input class="form-control" value="{{ $nombre_mac_usuario ?? 'Sin asignar' }}" readonly><input type="hidden" id="filtro_mac" value="{{ auth()->user()->idcentro_mac }}">

                @endrole

            </div>

            <div class="col-md-3"><label class="etiqueta" for="filtro_fecha_inicio">Fecha Inicio</label><input type="date" id="filtro_fecha_inicio" class="form-control" value="{{ date('Y-m-01') }}"></div>

            <div class="col-md-3"><label class="etiqueta" for="filtro_fecha_fin">Fecha Fin</label><input type="date" id="filtro_fecha_fin" class="form-control" value="{{ date('Y-m-d') }}"></div>

            <div class="col-md-2 d-flex gap-2"><button type="button" id="btnBuscarReporte" class="btn btn-primary flex-fill"><i class="fa fa-search"></i> Buscar</button><button type="button" id="btnLimpiarReporte" class="btn btn-dark flex-fill"><i class="fa fa-undo"></i> Limpiar</button></div>

        </div>

        <div class="mt-3"><button type="button" id="btnToggleFiltros" class="btn btn-outline-dark w-100" style="font-weight:600"><i class="fa fa-filter"></i> Ver más filtros</button></div>

        <div id="extraFiltros" class="mt-3" style="display:none">

            <div class="row g-3">

                <div class="col-md-6"><label class="etiqueta" for="filtro_entidad">Entidad</label><select id="filtro_entidad" class="form-control select2" style="width:100%">

                        <option value="">-- Todas las entidades --</option>@foreach($entidades as $ent)<option value="{{ $ent->identidad }}">{{ $ent->abrev_entidad }} - {{ $ent->nombre_entidad }}</option>@endforeach

                    </select></div>

                <div class="col-md-6"><label class="etiqueta" for="filtro_resultado">Resultado</label><select id="filtro_resultado" class="form-control">

                        <option value="">-- Todos los resultados --</option>

                        <option value="PUNTUAL">Puntual</option>

                        <option value="TARDANZA">Tardanza</option>

                        <option value="FALTA">Falta</option>

                        <option value="FERIADO">Feriado</option>

                    </select></div>

            </div>

        </div>

    </div>

</div>

<div class="row g-3 mb-3 reporte-op">

    <div class="col-md-3">

        <div class="tarjeta-indicador">

            <div class="descripcion">Días de asistencia</div>

            <div id="indicador_asistencia" class="valor">—</div>

        </div>

    </div>

    <div class="col-md-3">

        <div class="tarjeta-indicador">

            <div class="descripcion">Ocupabilidad</div>

            <div id="indicador_ocupabilidad" class="valor">—</div>

        </div>

    </div>

    <div class="col-md-3">

        <div class="tarjeta-indicador">

            <div class="descripcion">Puntualidad</div>

            <div id="indicador_puntualidad" class="valor">—</div>

        </div>

    </div>

    <div class="col-md-3">

        <div class="tarjeta-indicador">

            <div class="descripcion">Centros MAC evaluados</div>

            <div id="indicador_centros" class="valor">—</div>

        </div>

    </div>

</div>

<div class="card reporte-op">

    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">

        <h4 class="card-title">Detalle de Ocupabilidad y Puntualidad</h4><button type="button" class="btn btn-success" id="btnExportarExcel"><i class="fa fa-file-excel"></i> Exportar Excel</button>

    </div>

    <div class="card-body">

        <div id="mensajeReporte" class="aviso-pendiente mb-3">Seleccione sus filtros y presione Buscar para consultar la asistencia.</div>

        <div id="table_data">@include('reporte.ocupabilidad_puntualidad.tablas.tb_index', ['detalle' => []])</div>

    </div>

</div>

<div id="bloqueoReporteOP" style="display:none;position:fixed;inset:0;background:rgba(8,22,42,.75);backdrop-filter:blur(5px);z-index:99999;align-items:center;justify-content:center">

    <div class="bg-white text-center shadow-lg" style="width:440px;max-width:92%;border-radius:18px;overflow:hidden">

        <div style="height:6px;background:linear-gradient(90deg,#132842,#198754)"></div>

        <div style="padding:32px 28px">

            <div style="width:76px;height:76px;border-radius:50%;background:#eaf6f0;display:flex;align-items:center;justify-content:center;margin:0 auto 20px"><i class="fas fa-file-excel" style="font-size:34px;color:#198754"></i></div>

            <h4 style="color:#132842;font-weight:700;margin-bottom:12px">Generando Reporte de Ocupabilidad y Puntualidad</h4>

            <p style="color:#64748b;font-size:14px;line-height:1.7;margin-bottom:20px">Procesando las marcaciones y calculando los indicadores.</p>

            <div class="progress mb-3" style="height:20px">

                <div id="barraProgresoOP" class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width:0%">0%</div>

            </div>

            <div style="font-size:13px;font-weight:600;color:#198754;margin-bottom:20px"><span id="estadoProgresoOP">Preparando archivos Excel...</span></div>

            <div style="background:#f1f5f9;border-radius:10px;padding:13px 15px;font-size:12px;color:#475569">Mantenga esta ventana abierta. La descarga comenzará al finalizar.</div>

        </div>

    </div>

</div>

@endsection

@section('script')

<script src="{{ asset('nuevo/plugins/select2/select2.min.js') }}"></script>

<script src="{{ asset('nuevo/plugins/datatables/jquery.dataTables.min.js') }}"></script>

<script src="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.js') }}"></script>

<script>

    $(function() {

        $('.select2').select2({

            width: '100%'

        });

        const tablaVacia = $('#table_data').html();

        const mensaje = (texto, error = false) => $('#mensajeReporte').text(texto).css('color', error ? '#b91c1c' : '#475569');

        function destruirTabla() {

            if ($.fn.DataTable.isDataTable('#table_formato2')) $('#table_formato2').DataTable().destroy();

        }

        function iniciarDataTable() {

            $('#table_formato2').DataTable({

                pageLength: 25,

                lengthMenu: [

                    [10, 25, 50, 100],

                    [10, 25, 50, 100]

                ],

                processing: true,

                deferRender: true,

                autoWidth: false,

                scrollX: true,

                order: [

                    [7, 'asc'],

                    [1, 'asc'],

                    [5, 'asc']

                ],

                columnDefs: [{

                    targets: 0,

                    orderable: false

                }],

                language: {

                    decimal: ',',

                    thousands: ',',

                    search: 'Buscar:',

                    lengthMenu: 'Mostrar \_MENU\_ registros',

                    info: 'Mostrando \_START\_ a \_END\_ de \_TOTAL\_ registros',

                    infoEmpty: 'Sin registros',

                    infoFiltered: '(filtrado de \_MAX\_ registros)',

                    zeroRecords: 'No se encontraron resultados',

                    emptyTable: 'Sin registros para mostrar',

                    processing: 'Procesando...',

                    paginate: {

                        first: 'Primero',

                        last: 'Último',

                        next: 'Siguiente',

                        previous: 'Anterior'

                    }

                }

            });

        }

        function limpiarResultados() {

            $('#indicador_asistencia,#indicador_ocupabilidad,#indicador_puntualidad,#indicador_centros').text('—');

            destruirTabla();

            $('#table_data').html(tablaVacia);

        }

        async function buscar() {

            const filtros = {

                idmac: $('#filtro_mac').val() || '',

                fecha_inicio: $('#filtro_fecha_inicio').val() || '',

                fecha_fin: $('#filtro_fecha_fin').val() || '',

                identidad: $('#filtro_entidad').val() || '',

                resultado: $('#filtro_resultado').val() || ''

            };

            if (!filtros.fecha_inicio || !filtros.fecha_fin || filtros.fecha_inicio > filtros.fecha_fin) {

                if (window.Swal) Swal.fire('Atención', 'Seleccione un rango de fechas válido.', 'warning');

                else alert('Seleccione un rango de fechas válido.');

                return;

            }

            const boton = $('#btnBuscarReporte');

            boton.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Consultando');

            mensaje('Consultando marcaciones y calculando indicadores...');

            try {

                const respuesta = await fetch(@json(route('reporte.ocupabilidad_puntualidad.tablas.tb_index')) + '?' + new URLSearchParams(filtros), {

                    credentials: 'same-origin',

                    headers: {

                        'Accept': 'application/json'

                    }

                });

                const json = await respuesta.json().catch(() => ({}));

                if (!respuesta.ok) throw new Error(json.message || 'Error HTTP ' + respuesta.status);

                $('#indicador_asistencia').text(json.indicadores.asistencias);

                $('#indicador_ocupabilidad').text(Number(json.indicadores.ocupabilidad).toFixed(2) + '%');

                $('#indicador_puntualidad').text(Number(json.indicadores.puntualidad).toFixed(2) + '%');

                $('#indicador_centros').text(json.indicadores.centros);

                destruirTabla();

                $('#table_data').html(json.html);

                iniciarDataTable();

                mensaje('Se muestran ' + json.mostrando + ' de ' + json.total_detalle + ' filas de detalle. Los indicadores corresponden a todos los módulos y días hábiles del periodo, antes de aplicar el filtro de resultado.');

            } catch (error) {

                mensaje(error.message, true);

                limpiarResultados();

            } finally {

                boton.prop('disabled', false).html('<i class="fa fa-search"></i> Buscar');

            }

        }

        $('#btnToggleFiltros').on('click', function() {

            const extra = $('#extraFiltros');

            const abrir = !extra.is(':visible');

            extra.slideToggle(200);

            $(this).html(abrir ? '<i class="fa fa-chevron-up"></i> Ver menos filtros' : '<i class="fa fa-filter"></i> Ver más filtros');

        });

        $('#btnLimpiarReporte').on('click', function() {

            @role('Administrador|Moderador|Monitor')

            $('#filtro_mac').val('').trigger('change.select2');

            @endrole

            $('#filtro_fecha_inicio').val("{{ date('Y-m-01') }}");

            $('#filtro_fecha_fin').val("{{ date('Y-m-d') }}");

            $('#filtro_entidad').val('').trigger('change.select2');

            $('#filtro_resultado').val('');

            limpiarResultados();

            mensaje('Filtros restablecidos.');

        });

        $('#btnExportarExcel').on('click', async function() {

            const filtros = {

                idmac: $('#filtro_mac').val() || '',

                fecha_inicio: $('#filtro_fecha_inicio').val() || '',

                fecha_fin: $('#filtro_fecha_fin').val() || '',

                identidad: $('#filtro_entidad').val() || '',

                resultado: $('#filtro_resultado').val() || ''

            };

            if (!filtros.fecha_inicio || !filtros.fecha_fin || filtros.fecha_inicio > filtros.fecha_fin) return alert('Seleccione fechas válidas.');

            const boton = $(this),

                bloqueo = $('#bloqueoReporteOP');

            const texto = $('#estadoProgresoOP'),

                barra = $('#barraProgresoOP');

            boton.prop('disabled', true);

            bloqueo.css('display', 'flex');

            $('body').css('overflow', 'hidden');

            barra.css('width', '0%').text('0%');

            texto.text('Identificando entidades...');

            async function enviar(url, body) {

                const res = await fetch(url, {

                    method: 'POST',

                    credentials: 'same-origin',

                    headers: {

                        'Content-Type': 'application/json',

                        'Accept': 'application/json',

                        'X-CSRF-TOKEN': @json(csrf_token())

                    },

                    body: JSON.stringify(body)

                });

                const result = await res.json().catch(() => ({}));

                if (!res.ok) throw new Error(result.message || result.error || 'Error HTTP ' + res.status);

                return result;

            }

            try {

                const iniciado = await enviar(@json(route('reporte.ocupabilidad_puntualidad.iniciar_exportacion')), filtros);

                for (let i = 0; i < iniciado.total; i++) {
                    let completo = false;
                    while (!completo) {
                        const resultado = await enviar(@json(route('reporte.ocupabilidad_puntualidad.procesar_entidad')), {token: iniciado.token, indice: i});
                        completo = resultado.completa === true;
                        const fraccion = completo ? 1 : (resultado.dia / resultado.dias);
                        const progreso = (100 * (i + fraccion) / iniciado.total).toFixed(1) + '%';
                        barra.css('width', progreso).text(progreso);
                        texto.text(completo ? 'Terminada '+ iniciado.entidades[i] : 'Procesando '+iniciado.entidades[i]+': '+resultado.dia+' de '+resultado.dias+' días');
                    }
                }
                texto.text('Preparando ZIP...');

                const final = await enviar(@json(route('reporte.ocupabilidad_puntualidad.finalizar_exportacion')), {

                    token: iniciado.token

                });

                texto.text('Descarga lista.');

                window.location.assign(final.descarga);

            } catch (e) {

                texto.text(e.message);

                if (window.Swal) Swal.fire('Error al exportar', e.message, 'error');

                else alert(e.message);

            } finally {

                boton.prop('disabled', false);

                setTimeout(() => {

                    bloqueo.hide();

                    $('body').css('overflow', '');

                }, 1500);

            }

        });

        $('#btnBuscarReporte').on('click', buscar);

        buscar();

    });

</script>

@endsection
