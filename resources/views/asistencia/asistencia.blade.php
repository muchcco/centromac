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
            <div class="card">
                <div class="card-header" style="background-color:#132842">
                    <h4 class="card-title text-white">Filtro de Búsqueda</h4>
                </div><!--end card-header-->
                <div class="card-body bootstrap-select-1">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="mb-3">Fecha:</label>
                                @php
                                    $fecha6dias = date('d-m-Y', strtotime(now()));
                                    $fecha6diasconvert = date('Y-m-d', strtotime($fecha6dias));
                                @endphp
                                <input type="date" name="fecha" id="fecha" class="form-control"
                                    value="{{ $fecha6diasconvert }}">
                            </div>
                        </div><!-- end col -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="mb-3">Entidad:</label>
                                <select name="entidad" id="entidad" class="form-control col-sm-12 select2">
                                    <option value="" selected>-- Selecciones una opción --</option>
                                    @forelse ($entidad as $ent)
                                        <option value="{{ $ent->IDENTIDAD }}">{{ $ent->NOMBRE_ENTIDAD }}</option>
                                    @empty
                                        <option value="">No hay datos disponibles</option>
                                    @endforelse
                                </select>
                            </div>
                        </div><!-- end col -->
                        <div class="col-md-4">
                            <div class="form-group" style="margin-top: 2.6em">
                                <button type="button" class="btn btn-primary" id="filtro" onclick="execute_filter()"><i
                                        class="fa fa-search" aria-hidden="true"></i> Buscar</button>
                                <button class="btn btn-dark" id="limpiar"><i class="fa fa-undo" aria-hidden="true"></i>
                                    Limpiar</button>
                            </div>
                        </div><!-- end col -->
                    </div>
                </div><!-- end card-body -->
            </div> <!-- end card -->
        </div> <!-- end col -->
    </div> <!-- end row -->

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
                            @role('Administrador|Supervisor|Especialista TIC|Especialista_TIC')
                                <button class="btn btn-danger" onclick="cerrarDia()" id="btnCerrarDia">
                                    <i class="fa fa-lock"></i> Cerrar Día
                                </button>

                                <button class="btn btn-dark" onclick="abrirModalCerrarMes()" id="btnCerrarMes">
                                    <i class="fa fa-calendar"></i> Cerrar Mes
                                </button>
                            @endrole
                            @role('Administrador')
                                <button class="btn btn-warning" onclick="btnRevertirDia()" id="btnRevertirDia">
                                    <i class="fa fa-undo"></i> Revertir Día
                                </button>
                            @endrole
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
        $(document).ready(function() {
            tabla_seccion();
            $(document).ready(function() {
                $('.select2').select2();
            });
        });
        const listaMacs = @json($macs);

        function tabla_seccion(fecha = '', entidad = '', estado = '') {
            $.ajax({
                type: 'GET',
                url: "{{ route('asistencia.verificar_cierre') }}",
                data: {
                    fecha: fecha
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
                            estado: estado
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
            console.log("adsda")
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
            var fecha = $('#fecha').val();
            var entidad = $('#entidad').val();
            var estado = $('#estado').val();

            // var proc_data = "fecha="+fecha+"&entidad="+entidad+"$estado="+estado;

            // console.log(proc_data);

            $.ajax({
                type: 'get',
                url: "{{ route('asistencia.tablas.tb_asistencia') }}",
                dataType: "",
                data: {
                    fecha: fecha,
                    entidad: entidad,
                    estado: estado
                },
                beforeSend: function() {
                    document.getElementById("filtro").innerHTML =
                        '<i class="fa fa-spinner fa-spin"></i> Buscando';
                    document.getElementById("filtro").style.disabled = true;
                },
                success: function(data) {
                    document.getElementById("filtro").innerHTML = '<i class="fa fa-search"></i> Buscar';
                    document.getElementById("filtro").style.disabled = false;
                    tabla_seccion(fecha, entidad, estado);
                },
                error: function(xhr, status, error) {
                    console.log("error");
                    console.log('Error:', error);
                }
            });

            // console.log('Fecha fecha: '+fecha,'Fecha Fin: ' +fechaFin,'Dependencia: ' +dependencia,'Estado: '+estado,'Usuario OEAS: '+us_oeas);
            //table_asistencia(fecha, entidad, estado);
        }

        function btnAddAsistencia() {

            $.ajax({
                type: 'post',
                url: "{{ route('asistencia.modals.md_add_asistencia') }}",
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

        function btnAddAsistenciaCallao() {

            $.ajax({
                type: 'post',
                url: "{{ route('asistencia.modals.md_add_asistencia_callao') }}",
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
                        tabla_seccion();
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

        function btnStoreTxt() {

            var file_data = $("#txt_file").prop("files")[0];
            var formData = new FormData();
            formData.append("txt_file", file_data);
            formData.append("_token", $("input[name=_token]").val());

            $.ajax({
                type: 'POST',
                url: "{{ route('asistencia.store_asistencia') }}", // Cambia la ruta según tu configuración
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    document.getElementById("btnEnviarForm").innerHTML =
                        '<i class="fa fa-spinner fa-spin"></i> Espere... Cargando datos';
                    document.getElementById("btnEnviarForm").disabled = true;
                },
                success: function(data) {
                    $("#modal_show_modal").modal('hide');
                    tabla_seccion();
                    Toastify({
                        text: "Se agregó exitosamente los registros",
                        className: "info",
                        gravity: "bottom",
                        style: {
                            background: "#47B257",
                        }
                    }).showToast();
                },
                error: function(error) {
                    $("#modal_show_modal").modal('hide');
                    Swal.fire({
                        icon: "error",
                        text: "Hubo un error al cargar las asistencias... Intentar nuevamente! Verifique que el archivo txt no tenga el caracter en la última fila ",
                        confirmButtonText: "Aceptar"
                    })
                }
            });

        }

        function btnStoreAccess() {
            var file_data = $("#txt_file").prop("files")[0];
            var formData = new FormData();
            formData.append("txt_file", file_data);
            formData.append("fecha_inicio", $("#fecha_inicio").val());
            formData.append("fecha_fin", $("#fecha_fin").val());
            formData.append("_token", $("input[name=_token]").val());

            // Inicia el polling para actualizar el progreso
            var pollingInterval = setInterval(function() {
                $.get("{{ route('asistencia.upload.progress') }}", function(data) {
                    // Actualiza el botón o una barra de progreso con data.progress
                    $("#btnEnviarForm").html('<i class="fa fa-spinner fa-spin"></i> Cargando datos... ' +
                        data.progress + '%');
                    // Si el progreso es 100%, detén el polling
                    if (data.progress >= 100) {
                        clearInterval(pollingInterval);
                    }
                });
            }, 1000); // consulta cada 1 segundo

            $.ajax({
                type: 'POST',
                url: "{{ route('asistencia.store_asistencia_callao') }}",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $("#btnEnviarForm").prop("disabled", true);
                    // Inicializa el botón con 0% o un mensaje inicial
                    $("#btnEnviarForm").html('<i class="fa fa-spinner fa-spin"></i> Espere... Cargando datos');
                },
                success: function(data) {
                    $("#modal_show_modal").modal('hide');
                    $("#btnEnviarForm").prop("disabled", false);
                    // Asegúrate de detener el polling al recibir la respuesta final
                    clearInterval(pollingInterval);
                    if (data.success) {
                        tabla_seccion();
                        Toastify({
                            text: data.message,
                            className: "info",
                            gravity: "bottom",
                            style: {
                                background: "#47B257",
                            }
                        }).showToast();
                    } else {
                        Swal.fire({
                            icon: "error",
                            text: data.message,
                            confirmButtonText: "Aceptar"
                        });
                    }
                },
                error: function(error) {
                    $("#modal_show_modal").modal('hide');
                    $("#btnEnviarForm").prop("disabled", false);
                    clearInterval(pollingInterval);
                    Swal.fire({
                        icon: "error",
                        text: "Hubo un error al cargar las asistencias. Intentar nuevamente.",
                        confirmButtonText: "Aceptar"
                    });
                }
            });
        }

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
                        tabla_seccion(); // Refresca la tabla

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
                                tabla_seccion(fecha); // refrescar tabla
                            } else {
                                Swal.fire('Error', data.message, 'error');
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
                                tabla_seccion();
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
    </script>
@endsection
