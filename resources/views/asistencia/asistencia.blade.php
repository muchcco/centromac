@extends('layouts.layout')

@section('style')
    {{-- <link rel="stylesheet" href="{{ asset('Vendor/toastr/toastr.min.css') }}"> --}}
    {{-- <link rel="stylesheet" href="{{ asset('Vendor/toastr/toastr.min.css') }}"> --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

    <!-- Plugins css -->
    <link href="{{ asset('nuevo/plugins/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('nuevo/plugins/huebee/huebee.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('nuevo/plugins/timepicker/bootstrap-material-datetimepicker.css') }}" rel="stylesheet">
    <link href="{{ asset('nuevo/plugins/bootstrap-touchspin/css/jquery.bootstrap-touchspin.min.css') }}" rel="stylesheet" />
    <!-- DataTables -->
    <link href="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('nuevo/plugins/datatables/buttons.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Responsive datatable examples -->
    <link href="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('main')
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="row">
                    <div class="col">
                        <h4 class="page-title">Asistencia</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('inicio') }}"><i data-feather="home"
                                        class="align-self-center" style="height: 70%; display: block;"></i></a></li>
                            <li class="breadcrumb-item">Módulo de Asistencia</li>
                        </ol>
                    </div><!--end col-->
                </div><!--end row-->
            </div><!--end page-title-box-->
        </div><!--end col-->
    </div><!--end row-->

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background-color:#132842">
                    <h4 class="card-title text-white mb-0">Filtro de Búsqueda</h4>
                </div>

                <div class="card-body bootstrap-select-1">
                    <div class="row align-items-end">

                        {{-- 🔐 SOLO ADMINISTRADOR --}}
                        @role('Administrador')
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="mb-2 fw-semibold">Centro MAC</label>
                                    <select name="mac" id="mac" class="form-control select2">
                                        <option value="">-- Todos los MAC --</option>
                                        @foreach ($macs as $mac)
                                            <option value="{{ $mac->id }}"
                                                {{ isset($idmac) && $idmac == $mac->id ? 'selected' : '' }}>
                                                {{ $mac->nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endrole

                        {{-- 📅 FECHA --}}
                        <div class="@role('Administrador') col-md-3 @else col-md-4 @endrole">
                            <div class="form-group">
                                <label class="mb-2 fw-semibold">Fecha</label>
                                @php
                                    $fechaHoy = now()->format('Y-m-d');
                                @endphp
                                <input type="date" name="fecha" id="fecha" class="form-control"
                                    value="{{ $fechaHoy }}">
                            </div>
                        </div>

                        {{-- 🏛️ ENTIDAD --}}
                        <div class="@role('Administrador') col-md-3 @else col-md-4 @endrole">
                            <div class="form-group">
                                <label class="mb-2 fw-semibold">Entidad</label>
                                <select name="entidad" id="entidad" class="form-control select2">
                                    <option value="">-- Seleccione una opción --</option>
                                    @forelse ($entidad as $ent)
                                        <option value="{{ $ent->IDENTIDAD }}">
                                            {{ $ent->NOMBRE_ENTIDAD }}
                                        </option>
                                    @empty
                                        <option value="">No hay datos disponibles</option>
                                    @endforelse
                                </select>
                            </div>
                        </div>

                        {{-- 🔍 BOTONES --}}
                        <div class="@role('Administrador') col-md-3 @else col-md-4 @endrole text-end">
                            <div class="form-group">
                                <button type="button" class="btn btn-primary me-2" id="filtro"
                                    onclick="execute_filter()">
                                    <i class="fa fa-search"></i> Buscar
                                </button>

                                <button type="button" class="btn btn-dark" id="limpiar">
                                    <i class="fa fa-undo"></i> Limpiar
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header" style="background-color:#132842">
                    <h4 class="card-title text-white">ASISTENCIA DEL CENTRO MAC - {{ $name_mac }}</h4>

                </div><!--end card-header-->
                <div class="card-body bootstrap-select-1">
                    <div class="row">
                        <div class="col-12">
                            @role('Administrador|Especialista TIC|Especialista_TIC')
                                @if ($idmac == 10)
                                    <button class="btn btn-primary" data-toggle="modal" data-target="#large-Modal"
                                        onclick="btnAddAsistenciaCallao()"><i class="fa fa-upload" aria-hidden="true"></i>
                                        Subir Archivo de Asistencia
                                    </button>
                                @else
                                    <button class="btn btn-primary" data-toggle="modal" data-target="#large-Modal"
                                        onclick="btnAddAsistencia()"><i class="fa fa-upload" aria-hidden="true"></i>
                                        Subir Archivo de Asistencia
                                    </button>
                                @endif
                            @endrole

                            <button class="btn btn-success" data-toggle="modal" data-target="#large-Modal"
                                onclick="btnAgregarAsistencia()"><i class="fa fa-plus" aria-hidden="true"></i>
                                Agregar Asistencia Manualmente
                            </button>

                            <a class="btn btn-info" href="{{ route('asistencia.det_entidad', $idmac) }}"> Asistencia por
                                entidad
                            </a>
                            @role('Administrador|Especialista_TIC')
                                <button class="btn btn-purple" onclick="btnDowloadAssists();" id="cargandoAsistencia">
                                    <i class="fa fa-database" aria-hidden="true"></i>
                                    Cargar Asistencia
                                </button>


                                @if ($idmac == 11)
                                    <button class="btn btn-warning" onclick="migrarDatos()" id="cargandoMigra">
                                        <i class="fa fa-sync" aria-hidden="true"></i>
                                        Migrar Datos
                                    </button>
                                @endif
                            @endrole
                            @role('Administrador|Supervisor|Especialista TIC|Especialista_TIC|Coordinador')
                                <button class="btn btn-danger" onclick="cerrarDia()" id="btnCerrarDia">
                                    <i class="fa fa-lock"></i> Cerrar Día
                                </button>
                            @endrole
                            @role('Administrador|Moderador')
                                <button class="btn btn-secondary" onclick="btnExcepcionCierre()">
                                    <i class="fa fa-unlock"></i> Habilitar Cierre Fuera de Plazo
                                </button>
                            @endrole
                            @role('Administrador')
                                <button class="btn btn-dark" onclick="abrirModalCerrarMes()" id="btnCerrarMes">
                                    <i class="fa fa-calendar"></i> Cerrar Mes
                                </button>
                            @endrole
                            @hasanyrole('Administrador|Moderador')
                                <button class="btn btn-warning" onclick="btnRevertirDia()" id="btnRevertirDia">
                                    <i class="fa fa-undo"></i> Revertir Día
                                </button>
                            @endhasanyrole

                        </div>
                    </div>

                    <br />
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



    {{-- Ver Modales --}}
    <div class="modal fade" id="modal_show_modal" tabindex="-1" role="dialog"></div>

@endsection

@section('script')
    <script src="{{ asset('Script/js/sweet-alert.min.js') }}"></script>
    {{-- <script src="{{ asset('Vendor/toastr/toastr.min.js') }}"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="{{ asset('//cdn.jsdelivr.net/npm/sweetalert2@11') }}"></script>

    <script src="{{ asset('Vendor/toastr/toastr.min.js') }}"></script>



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

    <script>
        function refrescarTablaActual() {
            const fecha = $('#fecha').val();
            const entidad = $('#entidad').val();
            const estado = $('#estado').val();
            const mac = $('#mac').length ? $('#mac').val() : null;

            tabla_seccion(fecha, entidad, estado, mac);
        }


        $(document).ready(function() {
            tabla_seccion();
            $(document).ready(function() {
                $('.select2').select2();
            });
        });
        const listaMacs = @json($macs);

        function tabla_seccion(fecha = '', entidad = '', estado = '', mac = null) {
            $.ajax({
                type: 'GET',
                url: "{{ route('asistencia.verificar_cierre') }}",
                data: {
                    fecha: fecha,
                    mac: mac
                },
                success: function(resp) {
                    let urlTabla = resp.cerrado ?
                        "{{ route('asistencia.tablas.tb_asistencia_resumen') }}" :
                        "{{ route('asistencia.tablas.tb_asistencia') }}";

                    // Mostrar / ocultar botones
                    if (resp.cerrado) {
                        $('#btnCerrarDia').hide();
                        $('#btnCerrarMes').hide();
                        $('button[onclick="btnAgregarAsistencia()"]').hide();
                        $('button[onclick="btnAddAsistencia()"]').hide();
                        $('button[onclick="btnAddAsistenciaCallao()"]').hide();
                    } else {
                        $('#btnCerrarDia').show();
                        $('#btnCerrarMes').show();
                        $('button[onclick="btnAgregarAsistencia()"]').show();
                        $('button[onclick="btnAddAsistencia()"]').show();
                        $('button[onclick="btnAddAsistenciaCallao()"]').show();
                    }

                    // Cargar tabla
                    $.ajax({
                        type: 'GET',
                        url: urlTabla,
                        data: {
                            fecha: fecha,
                            entidad: entidad,
                            estado: estado,
                            mac: mac
                        },
                        beforeSend: function() {
                            $("#table_data").html(
                                '<i class="fa fa-spinner fa-spin"></i> ESPERE LA TABLA ESTA CARGANDO... '
                            );
                        },
                        success: function(data) {
                            $('#table_data').html(data);
                        }
                    });
                }
            });
        }

        $(document).ready(function() {
            const fechaInicial = $('#fecha').val();
            tabla_seccion(fechaInicial);
            $('.select2').select2();
        });

        $("#limpiar").on("click", function(e) {
            // console.log("adsda")
            e.preventDefault(); // Evita comportamiento por defecto del botón

            var fechaActual = new Date();
            var formatoFecha = fechaActual.toISOString().split('T')[0];

            document.getElementById('fecha').value = formatoFecha;

            // Regresa el select de entidad a su primera opción
            $('#entidad').val("").trigger("change");

            tabla_seccion(); // Refresca la tabla
        });

        // EJECUTA LOS FILTROS Y ENVIA AL CONTROLLADOR PARA  MOSTRAR EL RESULTADO EN LA TABLA
        var execute_filter = () => {
            const fecha = $('#fecha').val();
            const entidad = $('#entidad').val();
            const estado = $('#estado').val();

            // 🔐 Solo existe si es Administrador
            const mac = $('#mac').length ? $('#mac').val() : null;

            $.ajax({
                type: 'get',
                url: "{{ route('asistencia.tablas.tb_asistencia') }}",
                data: {
                    fecha: fecha,
                    entidad: entidad,
                    estado: estado,
                    mac: mac // 👈 se envía solo si existe
                },
                beforeSend: function() {
                    const btn = document.getElementById("filtro");
                    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Buscando';
                    btn.disabled = true;
                },
                success: function() {
                    const btn = document.getElementById("filtro");
                    btn.innerHTML = '<i class="fa fa-search"></i> Buscar';
                    btn.disabled = false;

                    // 🔥 refresca manteniendo filtros
                    tabla_seccion(fecha, entidad, estado, mac);
                },
                error: function(xhr, status, error) {
                    const btn = document.getElementById("filtro");
                    btn.innerHTML = '<i class="fa fa-search"></i> Buscar';
                    btn.disabled = false;

                    console.error('Error:', error);
                }
            });
        }

        function btnAddAsistencia() {
            $.ajax({
                type: 'post',
                url: "{{ route('asistencia.modals.md_add_asistencia') }}",
                dataType: "json",
                data: { "_token": "{{ csrf_token() }}" },
                success: function(data) {
                    _activeModalType = 'txt';
                    $("#modal_show_modal").html(data.html);
                    $("#modal_show_modal").modal('show');
                }
            });
        }

        function btnAddAsistenciaCallao() {
            // Solo detiene Callao — el polling TXT sigue activo en background
            _stopCallaoPolling();
            _callaoAborted       = false;
            _callaoQueuedSeconds = 0;
            $.ajax({
                type: 'post',
                url: "{{ route('asistencia.modals.md_add_asistencia_callao') }}",
                dataType: "json",
                data: { "_token": "{{ csrf_token() }}" },
                success: function(data) {
                    _activeModalType = 'callao';
                    $("#modal_show_modal").html(data.html);
                    $("#modal_show_modal").modal('show');
                }
            });
        }

        function btnAgregarAsistencia() {

            $.ajax({
                type: 'post',
                url: "{{ route('asistencia.modals.md_agregar_asistencia') }}",
                dataType: "json",
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                success: function(data) {
                    $("#modal_show_modal").html(data.html);
                    $("#modal_show_modal").modal('show');
                }
            });
        }

        function storeAsistenciatest() {
            const form = document.getElementById('formAsistenciatest');
            const formData = new FormData(form);

            // Validar antes de enviar el formulario
            const dni = $('#DNI').val();
            const fecha = $('#fecha').val();
            if (dni.length !== 8 || !fecha) {
                Swal.fire({
                    icon: 'error',
                    title: '¡Error!',
                    text: 'Por favor ingrese un DNI válido.',
                    confirmButtonText: 'Aceptar'
                });
                return;
            }

            fetch("{{ route('asistencia.store_agregar_asistencia') }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {

                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: data.message,
                            confirmButtonText: 'Aceptar'
                        });
                        refrescarTablaActual();
                        $("#modal_show_modal").modal('hide'); // Cerrar el modal
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '¡Error!',
                            text: data.message,
                            confirmButtonText: 'Aceptar'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: '¡Error!',
                        text: 'Hubo un error al procesar la solicitud. Por favor, inténtelo de nuevo.',
                        confirmButtonText: 'Aceptar'
                    });
                });
        }

        tippy(".bandejTool", {
            allowHTML: true,
            followCursor: true,
        });

        // ── Estado TXT (independiente de Callao) ──────────────────────────────
        var txtUploadToken    = null;
        var txtPollingInterval = null;
        var txtInProgress     = false;
        var txtQueuedSeconds  = 0;

        // ── Estado Callao (independiente de TXT) ──────────────────────────────
        var _callaoPolling       = null;
        var _callaoAborted       = false;
        var _callaoQueuedSeconds = 0;

        // Qué tipo de modal está activo ahora mismo ('txt', 'callao', o null)
        var _activeModalType = null;

        // Detiene TODOS los pollings (solo para cierre de página / navegación)
        function _stopAllPolling() {
            if (txtPollingInterval)  { clearInterval(txtPollingInterval);  txtPollingInterval  = null; }
            if (_callaoPolling)      { clearInterval(_callaoPolling);      _callaoPolling      = null; }
        }

        // Detiene solo el polling Callao
        function _stopCallaoPolling() {
            if (_callaoPolling) { clearInterval(_callaoPolling); _callaoPolling = null; }
        }

        window.addEventListener('beforeunload', function(e) {
            if (txtInProgress) {
                var msg = 'Si sale de esta ventana se cancelara el envio.';
                e.preventDefault();
                e.returnValue = msg;
                return msg;
            }
        });

        window.addEventListener('unload', function() {
            if (!txtInProgress || !txtUploadToken) return;
            var params = new URLSearchParams();
            params.append('token', txtUploadToken);
            params.append('_token', "{{ csrf_token() }}");
            navigator.sendBeacon("{{ route('asistencia.upload.cancel') }}", params);
        });

        $(document).on('click', '#btnCancelarUpload', function(e) {
            if (!txtInProgress || !txtUploadToken) return;
            e.preventDefault();
            if (!confirm('Si sale de esta ventana se cancelara el envio. Desea continuar?')) return;

            $.post("{{ route('asistencia.upload.cancel') }}", {
                token:  txtUploadToken,
                _token: "{{ csrf_token() }}"
            }).always(function() {
                if (txtPollingInterval) { clearInterval(txtPollingInterval); txtPollingInterval = null; }
                txtInProgress    = false;
                txtUploadToken   = null;
                txtQueuedSeconds = 0;
                $("#uploadProgressWrapper").addClass("d-none");
                $("#uploadQueueInfo").addClass("d-none").text("");
                $("#uploadProgressBar").css("width", "0%").text("0%");
                var $btn = $("#modal_show_modal #btnEnviarForm");
                $btn.prop("disabled", false).html("Importar");
                $("#modal_show_modal").modal('hide');
            });
        });

        // ── TXT: reinicia UI del modal TXT ───────────────────────────────────
        function _txtResetUI() {
            if (txtPollingInterval) { clearInterval(txtPollingInterval); txtPollingInterval = null; }
            txtInProgress    = false;
            txtQueuedSeconds = 0;
            var $btn = $("#modal_show_modal #btnEnviarForm");
            $btn.prop("disabled", false).html("Importar");
            $("#uploadProgressWrapper").addClass("d-none");
            $("#uploadQueueInfo").addClass("d-none").text("");
            $("#uploadProgressBar").css("width", "0%").text("0%");
        }

        function btnStoreTxt() {
            // Guardia de concurrencia: no iniciar si hay una carga Callao activa
            if (_callaoPolling || _activeModalType === 'callao') {
                Swal.fire({ icon: 'warning', title: 'Carga en progreso',
                    text: 'Hay una importación Callao en curso. Espere a que finalice antes de iniciar la carga TXT.',
                    confirmButtonText: 'Aceptar' });
                return;
            }

            var file_data = $("#txt_file").prop("files")[0];
            var formData  = new FormData();
            formData.append("txt_file", file_data);
            formData.append("_token", $("input[name=_token]").val());

            $.ajax({
                type: 'POST',
                url: "{{ route('asistencia.store_asistencia') }}",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    var $btn = $("#modal_show_modal #btnEnviarForm");
                    $btn.html('<i class="fa fa-spinner fa-spin"></i> Espere... Cargando datos').prop("disabled", true);
                    $("#uploadProgressWrapper").removeClass("d-none");
                    $("#uploadProgressBar").css("width", "0%").text("0%");
                    $("#uploadQueueInfo").addClass("d-none").text("");
                    txtInProgress    = true;
                    txtQueuedSeconds = 0;
                },
                success: function(data) {
                    if (!data.upload_token) {
                        _txtResetUI();
                        $("#modal_show_modal").modal('hide');
                        refrescarTablaActual();
                        Toastify({ text: "Se agregaron los registros", className: "info", gravity: "bottom",
                            style: { background: "#47B257" } }).showToast();
                        return;
                    }

                    txtUploadToken = data.upload_token;

                    txtPollingInterval = setInterval(function() {
                        $.get("{{ route('asistencia.upload.progress') }}", { token: txtUploadToken }, function(resp) {
                            var progress = parseInt(resp.progress) || 0;
                            var status   = resp.status || 'queued';

                            $("#uploadProgressBar").css("width", progress + "%").text(progress + "%");

                            // ── Status terminal ───────────────────────────────
                            if (status === 'completed') {
                                clearInterval(txtPollingInterval); txtPollingInterval = null;
                                txtInProgress = false; txtUploadToken = null; txtQueuedSeconds = 0;
                                refrescarTablaActual();
                                Toastify({ text: "Carga TXT terminada correctamente.", className: "info", gravity: "bottom",
                                    style: { background: "#47B257" } }).showToast();
                                // Solo cerrar modal si el modal TXT sigue activo
                                if (_activeModalType === 'txt') {
                                    _activeModalType = null;
                                    $("#modal_show_modal").modal('hide');
                                }
                                return;
                            }

                            if (status === 'failed') {
                                clearInterval(txtPollingInterval); txtPollingInterval = null;
                                txtInProgress = false; txtQueuedSeconds = 0;
                                var errMsg = resp.error || "Error desconocido en el procesamiento.";
                                if (_activeModalType === 'txt') {
                                    var $btn = $("#modal_show_modal #btnEnviarForm");
                                    $btn.prop("disabled", false).html("Importar");
                                }
                                Swal.fire({ icon: "error", title: "Error al procesar TXT",
                                    text: errMsg, confirmButtonText: "Aceptar" });
                                return;
                            }

                            if (status === 'cancelled') {
                                clearInterval(txtPollingInterval); txtPollingInterval = null;
                                txtInProgress = false; txtUploadToken = null; txtQueuedSeconds = 0;
                                if (_activeModalType === 'txt') {
                                    $("#uploadQueueInfo").removeClass("d-none").text("Carga cancelada.");
                                    var $btn = $("#modal_show_modal #btnEnviarForm");
                                    $btn.prop("disabled", false).html("Importar");
                                }
                                return;
                            }

                            // ── Status en progreso ────────────────────────────
                            if (status === 'queued') {
                                txtQueuedSeconds++;
                                var queueMsg = "En cola" + (resp.position !== null ? ": posición " + (resp.position + 1) : "") + "...";
                                if (txtQueuedSeconds >= 30) {
                                    queueMsg = "El archivo fue guardado, pero el worker aún no inicia el procesamiento. Verifique el estado de la cola.";
                                }
                                $("#uploadQueueInfo").removeClass("d-none").text(queueMsg);
                            } else if (status === 'processing' || status === 'running') {
                                txtQueuedSeconds = 0;
                                $("#uploadQueueInfo").removeClass("d-none").text("Procesando... " + progress + "%");
                            }
                        }).fail(function() {
                            // Error de red: se reintenta en el siguiente tick
                        });
                    }, 1000);
                },
                error: function(xhr) {
                    _txtResetUI();
                    var errMsg = (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : "Hubo un error al cargar las asistencias. Intente nuevamente.";
                    Swal.fire({ icon: "error", title: "Error al subir archivo", text: errMsg, confirmButtonText: "Aceptar" });
                }
            });
        }

        // ── Carga Callao por chunks ────────────────────────────────────────────

        // Genera token hexadecimal aleatorio de 16 chars
        function _callaoToken() {
            var arr = new Uint8Array(8);
            crypto.getRandomValues(arr);
            return Array.from(arr, function(b){ return b.toString(16).padStart(2,'0'); }).join('');
        }

        function _callaoBtn() { return $("#modal_show_modal #btnEnviarForm"); }

        // Muestra/actualiza la barra de la fase 1 (subida)
        function _callaoUploadUI(label, pct) {
            $("#callaoProgressArea").removeClass("d-none");
            $("#callaoPhaseUpload").removeClass("d-none");
            $("#callaoUploadLabel").text(label);
            $("#callaoUploadPct").text(pct + "%");
            $("#callaoUploadBar").css("width", pct + "%");
        }

        // Muestra/actualiza la barra de la fase 2 (procesamiento)
        function _callaoProcessUI(label, pct) {
            $("#callaoPhaseProcess").removeClass("d-none");
            $("#callaoProcessLabel").text(label);
            $("#callaoProcessPct").text(pct + "%");
            $("#callaoProcessBar").css("width", pct + "%");
        }

        // Resetea toda la UI del modal Callao al estado inicial
        function _callaoReset() {
            if (_callaoPolling) { clearInterval(_callaoPolling); _callaoPolling = null; }
            _callaoAborted       = false;
            _callaoQueuedSeconds = 0;
            if (_activeModalType === 'callao') { _activeModalType = null; }
            _callaoBtn().prop("disabled", false).html('<i class="fa fa-upload me-1"></i>Importar');
            $("#callaoProgressArea").addClass("d-none");
            $("#callaoPhaseProcess").addClass("d-none");
            $("#callaoUploadBar").css("width","0%");
            $("#callaoProcessBar").css("width","0%");
            $("#callaoProgressMsg").text("");
        }

        // Muestra error al usuario y deja el botón activo para reintentar
        function _callaoError(msg) {
            if (_callaoPolling) { clearInterval(_callaoPolling); _callaoPolling = null; }
            _callaoAborted       = false;
            _callaoQueuedSeconds = 0;
            _callaoBtn().prop("disabled", false).html('<i class="fa fa-upload me-1"></i>Importar');
            $("#callaoProgressMsg").html('<span class="text-danger"><i class="fa fa-exclamation-triangle me-1"></i>' + msg + '</span>');

            var mainMsg  = msg;
            var techMsg  = '';
            var splitIdx = msg.indexOf('Detalle técnico:');
            if (splitIdx === -1) splitIdx = msg.indexOf('(Código de salida:');
            if (splitIdx > 0) {
                mainMsg = msg.substring(0, splitIdx).trim();
                techMsg = msg.substring(splitIdx).trim();
            }

            var htmlContent = '<div style="text-align:left;font-size:0.95em"><p>' + mainMsg + '</p>';
            if (techMsg) {
                htmlContent += '<details style="margin-top:8px;cursor:pointer">'
                    + '<summary style="color:#888;font-size:0.85em">Detalle técnico</summary>'
                    + '<pre style="font-size:0.8em;background:#f5f5f5;padding:6px;border-radius:4px;white-space:pre-wrap;margin-top:4px">'
                    + techMsg + '</pre></details>';
            }
            htmlContent += '</div>';

            Swal.fire({ icon: 'error', title: 'Error al procesar archivo', html: htmlContent,
                confirmButtonText: 'Aceptar', width: techMsg ? 600 : 400 });
        }

        // Inicia polling del progreso del job Callao (fase 2)
        function _callaoStartPolling(token) {
            _callaoQueuedSeconds = 0;
            $("#callaoProgressMsg").text("Archivo en cola de procesamiento...");
            _callaoPolling = setInterval(function() {
                $.get("{{ route('asistencia.upload.progress') }}", { token: token }, function(resp) {
                    var jobPct = parseInt(resp.progress) || 0;
                    var status = resp.status || 'queued';

                    _callaoProcessUI("Procesando archivo... " + jobPct + "%", jobPct);

                    if (status === 'completed') {
                        clearInterval(_callaoPolling); _callaoPolling = null;
                        _callaoQueuedSeconds = 0;
                        _callaoProcessUI("¡Completado!", 100);
                        setTimeout(function() {
                            $("#modal_show_modal").modal("hide");
                            _callaoReset();
                            tabla_seccion();
                            Toastify({ text: "Asistencias Callao importadas correctamente.", className: "info",
                                gravity: "bottom", style: { background: "#47B257" } }).showToast();
                        }, 600);
                        return;
                    }

                    if (status === 'warning') {
                        clearInterval(_callaoPolling); _callaoPolling = null;
                        _callaoProcessUI("Proceso completado — sin registros nuevos.", 100);
                        _callaoBtn().prop("disabled", false).html('<i class="fa fa-upload me-1"></i>Importar');
                        if (_activeModalType === 'callao') { _activeModalType = null; }
                        Swal.fire({ icon: 'warning', title: 'Sin registros nuevos para importar',
                            html: '<div style="text-align:left;font-size:0.95em">' + (resp.error || '') + '</div>',
                            confirmButtonText: 'Aceptar', width: 520 });
                        return;
                    }

                    if (status === 'failed') {
                        clearInterval(_callaoPolling); _callaoPolling = null;
                        _callaoError(resp.error || "Error desconocido en el procesamiento.");
                        return;
                    }

                    if (status === 'cancelled') {
                        clearInterval(_callaoPolling); _callaoPolling = null;
                        _callaoReset();
                        return;
                    }

                    if (status === 'queued') {
                        _callaoQueuedSeconds++;
                        var queueMsg = "En cola de procesamiento...";
                        if (_callaoQueuedSeconds >= 30) {
                            queueMsg = "El archivo fue guardado, pero el worker aún no inicia el procesamiento. Verifique el estado de la cola.";
                        }
                        $("#callaoProgressMsg").text(queueMsg);
                    } else {
                        _callaoQueuedSeconds = 0;
                    }
                }).fail(function() {
                    // Error de red: se reintenta en el siguiente tick
                });
            }, 2000);
        }

        // Envía el chunk número `index` y recursa hasta terminar
        function _callaoSendChunk(file, token, index, totalChunks, csrf, fechaInicio, fechaFin) {
            if (_callaoAborted) return;

            var CHUNK = 512 * 1024; // 512 KB (bajo el límite del proxy externo)
            var start = index * CHUNK;
            var end   = Math.min(start + CHUNK, file.size);
            var blob  = file.slice(start, end);

            _callaoUploadUI("Subiendo parte " + (index + 1) + " de " + totalChunks + "...",
                Math.round((index / totalChunks) * 100));

            var fd = new FormData();
            fd.append("chunk",        blob, "chunk_" + index);
            fd.append("chunk_index",  index);
            fd.append("total_chunks", totalChunks);
            fd.append("upload_token", token);
            fd.append("filename",     file.name);
            fd.append("_token",       csrf);

            $.ajax({
                type: "POST", url: "{{ route('asistencia.callao.chunk') }}",
                data: fd, processData: false, contentType: false,
                success: function(resp) {
                    if (!resp.success) {
                        _callaoError("Error en parte " + (index + 1) + ": " + (resp.message || "respuesta inesperada."));
                        return;
                    }
                    if (index + 1 < totalChunks) {
                        _callaoSendChunk(file, token, index + 1, totalChunks, csrf, fechaInicio, fechaFin);
                    } else {
                        _callaoUploadUI("Subida completa. Ensamblando...", 100);
                        _callaoFinalize(token, totalChunks, file.name, csrf, fechaInicio, fechaFin);
                    }
                },
                error: function(xhr) {
                    var msg = xhr.status === 413
                        ? "El servidor rechazó la parte " + (index + 1) + " por tamaño (413). Contacte al administrador."
                        : ((xhr.responseJSON && xhr.responseJSON.message) || "Error de red al subir parte " + (index + 1) + ". Puedes reintentar.");
                    _callaoError(msg);
                }
            });
        }

        // Llama al endpoint de ensamblado y despacha el job
        function _callaoFinalize(token, totalChunks, filename, csrf, fechaInicio, fechaFin) {
            if (_callaoAborted) return;
            $("#callaoProgressMsg").text("Ensamblando partes...");

            $.ajax({
                type: "POST", url: "{{ route('asistencia.callao.finalize') }}",
                data: { _token: csrf, upload_token: token, total_chunks: totalChunks,
                        filename: filename, fecha_inicio: fechaInicio, fecha_fin: fechaFin },
                success: function(resp) {
                    if (!resp.success) { _callaoError(resp.message || "Error al ensamblar el archivo."); return; }
                    $("#callaoProgressMsg").text("Archivo en cola. Iniciando procesamiento...");
                    _callaoStartPolling(token);
                },
                error: function(xhr) {
                    _callaoError((xhr.responseJSON && xhr.responseJSON.message) || "Error al ensamblar el archivo. Puedes reintentar.");
                }
            });
        }

        // Punto de entrada — llamado desde onclick del botón Callao
        function btnStoreAccess() {
            // Guardia de concurrencia: no iniciar si hay una carga TXT activa
            if (txtInProgress) {
                Swal.fire({ icon: 'warning', title: 'Carga en progreso',
                    text: 'Hay una importación TXT en curso. Espere a que finalice antes de iniciar la carga Callao.',
                    confirmButtonText: 'Aceptar' });
                return;
            }

            var file = $("#txt_file").prop("files")[0];
            if (!file) {
                Swal.fire({ icon: "warning", text: "Selecciona un archivo .mdb o .accdb.", confirmButtonText: "Aceptar" }); return;
            }
            var ext = file.name.split(".").pop().toLowerCase();
            if (ext !== "mdb" && ext !== "accdb") {
                Swal.fire({ icon: "error", text: "Solo se permiten archivos .mdb y .accdb.", confirmButtonText: "Aceptar" }); return;
            }
            var fechaInicio = $("#fecha_inicio").val();
            var fechaFin    = $("#fecha_fin").val();
            if (!fechaInicio || !fechaFin) {
                Swal.fire({ icon: "warning", text: "Completa las fechas de inicio y fin.", confirmButtonText: "Aceptar" }); return;
            }

            _callaoReset();
            var CHUNK = 512 * 1024;
            var total = Math.ceil(file.size / CHUNK);
            var token = _callaoToken();
            var csrf  = $("#_callao_token").val();

            _callaoBtn().prop("disabled", true).html('<i class="fa fa-spinner fa-spin me-1"></i>Subiendo...');
            $("#callaoProgressArea").removeClass("d-none");
            _callaoSendChunk(file, token, 0, total, csrf, fechaInicio, fechaFin);
        }
        // ── Fin carga Callao por chunks ───────────────────────────────────────

        var btnModalView = (dni, fecha) => {
            console.log(dni);
            $.ajax({
                type: 'post',
                url: "{{ route('asistencia.modals.md_detalle') }}",
                dataType: "json",
                data: {
                    "_token": "{{ csrf_token() }}",
                    dni_: dni,
                    fecha_: fecha
                },
                success: function(data) {
                    $("#modal_show_modal").html(data.html);
                    $("#modal_show_modal").modal('show');

                }
            });
        }

        var btnDowloadAssists = () => {
            $.ajax({
                type: 'post',
                url: "{{ route('asistencia.dow_asistencia') }}",
                dataType: "json",
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                beforeSend: function() {
                    document.getElementById("cargandoAsistencia").innerHTML =
                        '<i class="fa fa-spinner fa-spin"></i> Cargando';
                    document.getElementById("cargandoAsistencia").disabled = true;
                },
                success: function(data) {
                    console.log(data);
                    document.getElementById("cargandoAsistencia").innerHTML =
                        '<i class="fa fa-database"></i> Cargar Asistencia';
                    document.getElementById("cargandoAsistencia").disabled = false;
                    Toastify({
                        text: "Se cargaron las asistencias con éxito",
                        className: "success",
                        gravity: "top",
                        style: {
                            background: "#47B257",
                        }
                    }).showToast();
                },
                error: function(error) {
                    Swal.fire({
                        icon: "error",
                        text: "Hubo un erro al cargar las asistencias... Intentar nuevamente!",
                        confirmButtonText: "Aceptar"
                    })
                }
            });
        }

        function migrarDatos() {
            const button = document.getElementById("cargandoMigra");
            button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Cargando';
            button.disabled = true;

            fetch("{{ route('asistencia.migrar.datos') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Toastify({
                            text: data.message,
                            className: "info",
                            gravity: "bottom",
                            position: "right",
                            style: {
                                background: "#47B257",
                            }
                        }).showToast();

                    } else {
                        Toastify({
                            text: data.message,
                            className: "error",
                            gravity: "bottom",
                            position: "right",
                            style: {
                                background: "#D9534F",
                            }
                        }).showToast();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Hubo un error al procesar la solicitud. Intentar nuevamente.',
                        confirmButtonText: 'Aceptar'
                    });
                })
                .finally(() => {
                    // Restaurar el botón después de la solicitud
                    button.innerHTML = '<i class="fa fa-sync"></i> Migrar Datos';
                    button.disabled = false;
                });
        }

        function updateObservationIcon(dni, count) {
            // 1) Localiza el <a> de la tabla principal
            const linkEl = document.querySelector(`#table_asistencia a[data-dni="${dni}"]`);
            if (!linkEl) return;

            // 2) Busca (o crea) el span.bandejTool
            let iconEl = linkEl.querySelector('.bandejTool');
            const title = `Este usuario tiene (${count}) observación(es)`;

            if (count > 0) {
                if (iconEl) {
                    // Actualiza el atributo y destruye tooltip previo
                    iconEl.setAttribute('data-tippy-content', title);
                    if (iconEl._tippy) iconEl._tippy.destroy();
                } else {
                    // Crea el span e inserta el icono
                    iconEl = document.createElement('span');
                    iconEl.className = 'bandejTool text-warning';
                    iconEl.setAttribute('data-tippy-content', title);
                    iconEl.innerHTML = '<i class="fa fa-comment"></i>';
                    linkEl.appendChild(iconEl);
                }
                // Inicializa el tooltip pura y exclusivamente sobre el nodo DOM
                tippy(iconEl, {
                    content: title,
                    allowHTML: true,
                    followCursor: true
                });
            } else {
                // Cuando no queden observaciones, destruye y remueve el span
                if (iconEl) {
                    if (iconEl._tippy) iconEl._tippy.destroy();
                    iconEl.remove();
                }
            }
        }

        function storeModuloChanges() {
            // Obtener los datos del formulario
            var formData = new FormData(document.getElementById("form-modificar-modulo"));

            // Enviar los datos por AJAX
            $.ajax({
                type: 'POST',
                url: "{{ route('itinerante.store_itinerante') }}", // Ajustar la ruta según corresponda
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    document.getElementById("btnEnviarForm").innerHTML =
                        '<i class="fa fa-spinner fa-spin"></i> ESPERE';
                    document.getElementById("btnEnviarForm").disabled = true;
                },
                success: function(response) {
                    if (response.status == 201) {
                        // Mostrar el mensaje de éxito
                        Swal.fire({
                            icon: "success",
                            text: response.message,
                            confirmButtonText: "Aceptar"
                        });
                        refrescarTablaActual();
                        // Refresca la tabla

                        // Cerrar el modal
                        $('#modal_show_modal').modal('hide');
                    } else {
                        // Si hay un mensaje de error, mostrarlo
                        document.getElementById('alerta').innerHTML =
                            `<div class="alert alert-warning">${response.message}</div>`;
                    }

                    // Restaurar el botón después de la solicitud
                    document.getElementById("btnEnviarForm").innerHTML = 'Guardar';
                    document.getElementById("btnEnviarForm").disabled = false;
                },
                error: function(xhr, status, error) {
                    // Capturamos la respuesta del backend
                    var response = xhr.responseJSON;

                    // console.log(response); // Aquí estamos depurando la respuesta

                    if (response && response.message) {
                        // Mostrar el mensaje de error específico desde el backend
                        Swal.fire({
                            icon: "error",
                            text: response.message, // Mensaje de error del backend
                            confirmButtonText: "Aceptar"
                        });
                    } else {
                        // Si no hay mensaje específico, mostramos uno genérico
                        Swal.fire({
                            icon: "error",
                            text: "Hubo un error al procesar la solicitud. Intentar nuevamente.",
                            confirmButtonText: "Aceptar"
                        });
                    }

                    // Restaurar el botón después de la solicitud
                    document.getElementById("btnEnviarForm").innerHTML = 'Guardar';
                    document.getElementById("btnEnviarForm").disabled = false;
                }
            });
        }

        function cerrarDia() {
            const fecha = $('#fecha').val();
            const idmac = "{{ $idmac }}"; // ya lo tienes cargado en la vista

            if (!fecha || !idmac) {
                Swal.fire('Error', 'Debe seleccionar una fecha y un MAC válido.', 'error');
                return;
            }

            Swal.fire({
                title: '¿Está seguro?',
                text: "Se cerrará la asistencia del día " + fecha,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, cerrar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("{{ route('asistencia.cerrar_dia') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                fecha: fecha,
                                idmac: idmac
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {

                                Swal.fire('Éxito', data.message, 'success');
                                tabla_seccion(fecha);

                            } else {

                                Swal.fire({
                                    icon: 'warning',
                                    title: data.message,
                                    html: data.detalle ? data.detalle : ''
                                });

                            }
                        })
                        .catch(err => {
                            Swal.fire('Error', 'Hubo un problema en el cierre.', 'error');
                        });
                }
            });
        }

        function btnRevertirDia() {
            $.post("{{ route('asistencia.modals.md_revertir') }}", {
                _token: "{{ csrf_token() }}"
            }, function(data) {
                $("#modal_show_modal").html(data.html);
                $("#modal_show_modal").modal('show');
            }, 'json');
        }

        function storeRevertir() {
            const idmac = $('#rev-idmac').val();
            const fecha = $('#rev-fecha').val();

            if (!idmac || !fecha) {
                Swal.fire('Error', 'Debe seleccionar MAC y fecha.', 'error');
                return;
            }

            $.post("{{ route('asistencia.revertir_dia') }}", {
                    idmac: idmac,
                    fecha: fecha,
                    _token: "{{ csrf_token() }}"
                })
                .done(resp => {
                    if (resp.ok) {
                        Toastify({
                            text: resp.msg,
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            style: {
                                background: "#198754"
                            }
                        }).showToast();
                        $("#modal_show_modal").modal('hide');
                        tabla_seccion(fecha);
                    } else {
                        Swal.fire('Error', resp.msg, 'error');
                    }
                })
                .fail(xhr => {
                    Swal.fire('Error', xhr.responseJSON?.msg || 'Error inesperado', 'error');
                });
        }

        function abrirModalCerrarMes() {
            $.post("{{ route('asistencia.modals.md_cerrar_mes') }}", {
                _token: "{{ csrf_token() }}"
            }, function(data) {
                $("#modal_show_modal").html(data.html);
                $("#modal_show_modal").modal('show'); // igual que los otros
            }, 'json');
        }


        function confirmarCerrarMes() {
            const anio = $('#cerrar-anio').val();
            const mes = $('#cerrar-mes').val();
            const hoy = new Date();
            let mesAnterior = hoy.getMonth(); // 0=enero, 9=octubre → devuelve mes actual -1
            let anioAnterior = hoy.getFullYear();

            if (mesAnterior === 0) {
                mesAnterior = 12;
                anioAnterior -= 1;
            }
            if (!anio || !mes) {
                Swal.fire('Error', 'Debe seleccionar un año y un mes.', 'error');
                return;
            }

            Swal.fire({
                title: '¿Está seguro?',
                text: "Se cerrará todo el mes " + mes + "-" + anio,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, cerrar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar loader mientras se procesa
                    Swal.fire({
                        title: 'Cerrando mes...',
                        text: 'Este proceso puede tardar un momento.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch("{{ route('asistencia.cerrar_mes') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                anio,
                                mes
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Éxito',
                                    text: data.message,
                                    confirmButtonText: 'Aceptar'
                                });

                                $("#modal_show_modal").modal('hide');
                                refrescarTablaActual();
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        })
                        .catch(err => {
                            Swal.fire('Error', 'Hubo un problema en el cierre de mes.', 'error');
                        });
                }
            });
        }

        function btnExcepcionCierre() {

            let fecha = $('#fecha').val()
            let mac = $('#mac').val()

            $.ajax({

                type: 'POST',
                url: "{{ route('asistencia.md_excepcion_cierre') }}",
                dataType: 'json',

                data: {
                    fecha: fecha,
                    idmac: mac,
                    _token: "{{ csrf_token() }}"
                },

                success: function(resp) {

                    if (!resp.success) {

                        Swal.fire(
                            'No permitido',
                            resp.msg,
                            'warning'
                        )

                        return
                    }

                    $('#modal_show_modal').html(resp.html)
                    $('#modal_show_modal').modal('show')

                },

                error: function() {

                    Swal.fire(
                        'Error',
                        'No se pudo cargar el modal',
                        'error'
                    )

                }

            })

        }

        function guardarExcepcion() {
            let fecha = $('#ex_fecha').val()
            let mac = $('#ex_mac').val()
            let motivo = $('#ex_motivo').val()
            let valido = $('#ex_valido_hasta').val()
            if (!fecha || !mac || !motivo) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos obligatorios',
                    text: 'Debe completar todos los campos'
                })
                return
            }
            Swal.fire({
                title: '¿Registrar excepción?',
                text: 'Se habilitará cierre fuera de plazo',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Registrar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(
                        "{{ route('asistencia.guardar_excepcion_cierre') }}", {

                            fecha: fecha,
                            idmac: mac,
                            motivo: motivo,
                            valido_hasta: valido,

                            _token: "{{ csrf_token() }}"

                        },
                        function(resp) {

                            if (resp.ok) {

                                Toastify({
                                    text: resp.msg,
                                    duration: 4000,
                                    gravity: "top",
                                    position: "right",
                                    style: {
                                        background: "#47B257"
                                    }
                                }).showToast()
                                $('#modal_show_modal').modal('hide')
                            } else {
                                Swal.fire(
                                    'Error',
                                    resp.msg,
                                    'error'
                                )
                            }
                        }
                    )
                }
            })
        }
    </script>
@endsection
