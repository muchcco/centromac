@extends('layouts.layout')

@section('style')
<link rel="stylesheet" href="{{ asset('Vendor/toastr/toastr.min.css') }}">
<!-- Plugins css -->
<link href="{{ asset('nuevo/plugins/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('nuevo/plugins/huebee/huebee.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('nuevo/plugins/timepicker/bootstrap-material-datetimepicker.css') }}" rel="stylesheet">
<link href="{{ asset('nuevo/plugins/bootstrap-touchspin/css/jquery.bootstrap-touchspin.min.css') }}" rel="stylesheet" />
<!-- DataTables -->
<link href="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{ asset('nuevo/plugins/datatables/buttons.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
<!-- Responsive datatable examples -->
<link href="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.css')}}" rel="stylesheet" type="text/css" /> 

<style>
    /* Estilo del scrollbar */
    #tabla_acceso_entidad {
        height: 500px; /* Altura fija */
        overflow-y: auto; /* Activar scrollbar vertical cuando sea necesario */
        scrollbar-width: thin; /* Ancho del scrollbar */
    }

    #tabla_acceso_entidad::-webkit-scrollbar {
        width: 5px; /* Ancho del scrollbar en navegadores webkit */
    }

    #tabla_acceso_entidad::-webkit-scrollbar-thumb {
        background-color: #888; /* Color del thumb (la barra del scrollbar) */
        border-radius: 4px; /* Bordes redondeados */
    }

    #tabla_acceso_entidad::-webkit-scrollbar-thumb:hover {
        background-color: #555; /* Cambia de color al pasar el mouse sobre el thumb */
    }
</style>

@endsection

@section('main')

<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row">
                <div class="col">
                    <h4 class="page-title">Configuración</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('inicio') }}"><i data-feather="home" class="align-self-center" style="height: 70%; display: block;"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{route('configuracion.nuevo_mac') }}" style="color: #7081b9;">MAC</a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);" style="color: #7081b9;">MAC - {{ $mac->NOMBRE_MAC }}</a></li>
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
                <h4 class="card-title text-white"> CONFIGURACION DEL SISTEMA DEL CENTRO MAC - {{ $mac->NOMBRE_MAC }}</h4>
            </div><!--end card-header-->
            <div class="card-body bootstrap-select-1">
                <ul>
                    <li>CONFIGURACION DE TABLAS</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                <div class="media mb-3">
                    <img src="{{ asset('imagen/logo-novosga.png') }}" alt="" class="thumb-md rounded-circle">
                    <div class="media-body align-self-center text-truncate ms-3">
                        <h4 class="m-0 fw-semibold text-dark font-15">Configuración de biométrico</h4>
                        <p class="text-muted mb-0 font-13">
                            <span class="text-dark">Actual : </span>
                            @if ($configuracionBiometrico)
                                {{ strtoupper($configuracionBiometrico->nombre_biometrico) }}
                                desde {{ \Carbon\Carbon::parse($configuracionBiometrico->fecha_inicio)->format('d/m/Y') }}
                            @else
                                Sin configuración activa
                            @endif
                        </p>
                    </div>
                </div>
                <hr class="hr-dashed">
                <form id="formBiometrico" class="row align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Tipo de biométrico</label>
                        <select name="tipo_biometrico" id="tipo_biometrico" class="form-select">
                            <option value="">-- Seleccionar --</option>
                            <option value="1" {{ optional($configuracionBiometrico)->tipo_biometrico == 1 ? 'selected' : '' }}>TXT</option>
                            <option value="2" {{ optional($configuracionBiometrico)->tipo_biometrico == 2 ? 'selected' : '' }}>Access</option>
                            <option value="3" {{ optional($configuracionBiometrico)->tipo_biometrico == 3 ? 'selected' : '' }}>Excel / CSV</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio_biometrico" value="{{ optional($configuracionBiometrico)->fecha_inicio ?? now()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha fin</label>
                        <input type="date" class="form-control" id="fecha_fin_biometrico" value="{{ optional($configuracionBiometrico)->fecha_fin }}">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-primary btn-block col-12" id="btnGuardarConfigBiometrico" onclick="guardarConfigBiometrico()">Guardar</button>
                    </div>
                </form>
                <hr class="hr-dashed">
                <div id="datos-biometrico">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-semibold m-0">Biométricos registrados:</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Nombre</th>
                                    <th>Fecha inicio</th>
                                    <th>Fecha fin</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($configuracionesBiometrico as $bio)
                                    <tr>
                                        <td>{{ $bio->tipo_biometrico }}</td>
                                        <td>{{ strtoupper($bio->nombre_biometrico) }}</td>
                                        <td>{{ $bio->fecha_inicio ? \Carbon\Carbon::parse($bio->fecha_inicio)->format('d/m/Y') : '-' }}</td>
                                        <td>{{ $bio->fecha_fin ? \Carbon\Carbon::parse($bio->fecha_fin)->format('d/m/Y') : '-' }}</td>
                                        <td>
                                            @if ((int) $bio->status === 1 && (int) $bio->flag === 1)
                                                <span class="badge bg-success">Activo</span>
                                            @else
                                                <span class="badge bg-secondary">Baja</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ((int) $bio->status === 1 && (int) $bio->flag === 1)
                                                <button type="button"
                                                    class="nobtn bandejTool"
                                                    data-tippy-content="Dar de baja"
                                                    onclick="btnBajaBiometrico('{{ $bio->idconfiguracion_biometrico }}', '{{ $bio->fecha_inicio }}')">
                                                    <i class="las la-calendar-times text-secondary font-16 text-danger"></i>
                                                </button>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No hay biométricos registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">                                        
                <div class="media mb-3">
                    <img src="{{ asset('imagen/logo-novosga.png') }}" alt="" class="thumb-md rounded-circle">                                      
                    <div class="media-body align-self-center text-truncate ms-3">                                            
                        <h4 class="m-0 fw-semibold text-dark font-15">Configuración para accesos al novosga</h4>   
                        <p class="text-muted  mb-0 font-13"><span class="text-dark">Acceso : </span>Por el personal TIC</p>                                         
                    </div><!--end media-body-->
                </div>   
                <hr class="hr-dashed">
                <div class="d-flex justify-content-between mb-3">  
                    <h6 class="fw-semibold m-0">Accesos:</h6>                      
                </div>  
            </div>           
        </div><!--end card-->
    </div><!--end col-->
    <div class="col-lg-4">
        <div class="card" id="tabla_acceso_entidad" >
            <div class="card-body">
                <div class="media mb-3">
                    <img src="{{ asset('imagen/logo-novosga.png') }}" alt="" class="thumb-md rounded-circle">
                    <div class="media-body align-self-center text-truncate ms-3">
                        <h4 class="m-0 fw-semibold text-dark font-15">Agregar Entidades</h4>
                        <p class="text-muted  mb-0 font-13"><span class="text-dark">Acceso : </span>Por el personal TIC</p>
                    </div><!--end media-body-->
                </div>
                <hr class="hr-dashed">
                <div class="d-flex justify-content-between mb-3">
                    <h6 class="fw-semibold m-0">Agregar:</h6>
                    <div class="chat-search col-10">
                        <div class="form-group">
                            <div class="input-group">
                                <select name="addEntidad" id="addEntidad" class="select2 form-select ">
                                    <option value="">-- Seleccionar Entidad --</option>
                                    @foreach ($entidad_completo as $ent)
                                        <option value="{{ $ent->IDENTIDAD }}">{{ $ent->NOMBRE_ENTIDAD }} - {{ $ent->ABREV_ENTIDAD }}</option>
                                    @endforeach
                                </select>
                                <span class="shadow-none col-3">
                                    <button type="button" id="btn-guardar" class="btn btn-primary btn-sm" onclick="btnAddEntidad()">Agregar</button>
                                </span>
                            </div>
                        </div>
                    </div>
        
                </div>
                <hr class="hr-dashed">
                <div class="d-flex justify-content-between mb-3">
                    <div class="col-lg-12" id="datos">
                        <ul class="list-group list-group-flush">
        
                            @forelse ($entidad as $e)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="la la-check text-muted font-16 me-2"></i>{{ $e->NOMBRE_ENTIDAD }}
                                    </div>
                                    <button class="nobtn badge badge-primary badge-pill bandejTool" data-tippy-content="Eliminar" onclick="btnEliminarEntidad('{{ $e->IDMAC_ENTIDAD }}')"><i class="las la-trash-alt text-secondary font-16 text-danger"></i></button>
                                </li>
                            @empty
                                <p>No hay entidades registradas.</p>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div><!--end card-->
        
    </div><!--end col-->

    <!-- <div class="col-lg-4">
        <div class="card"  >
            <div class="card-body">
                <div class="media mb-3">
                    <img src="{{ asset('imagen/logo-novosga.png') }}" alt="" class="thumb-md rounded-circle">
                    <div class="media-body align-self-center text-truncate ms-3">
                        <h4 class="m-0 fw-semibold text-dark font-15">Agregar Módulos</h4>
                        <p class="text-muted  mb-0 font-13"><span class="text-dark">Acceso : </span>Por el personal TIC</p>
                    </div>
                </div>
                <hr class="hr-dashed">
                <div class="d-flex justify-content-between mb-1">
                    <form class="col-12" >
                        <div class="mb-3 row">
                            <label for="horizontalInput1" class="col-sm-2 form-label align-self-center mb-lg-0">N° de Módulo</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="n_modulo" name="n_modulo" placeholder="Número de Módulo" onkeypress="return isNumber(event)">
                            </div>
                        </div>
                            
                        <div class="mb-3 row" id="datos-mod-enc">
                            <label for="horizontalInput2" class="col-sm-2 form-label align-self-center mb-lg-0">Entidad</label>
                            <div class="col-sm-10">
                                <select name="addModEnt" id="addModEnt" class="select2 form-select ">
                                    <option value="">-- Seleccionar Entidad --</option>
                                    @foreach ($entidad as $ent)
                                        <option value="{{ $ent->IDENTIDAD }}">{{ $ent->NOMBRE_ENTIDAD }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 ms-auto">
                                <button type="submit" class="btn btn-primary btn-block col-12" id="btn-guardar-2" onclick="btnAddModulo()">Agregar</button>
                            </div>
                        </div> 
                    </form>
        
                </div>
                <hr class="hr-dashed">
                <div class="d-flex justify-content-between mb-3">
                    <div class="col-lg-12" id="datos-modd">
                        <ul class="list-group list-group-flush">
                            @forelse ($modulos as $mod)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="la la-check text-muted font-16 me-2"></i>{{ $mod->ABREV_ENTIDAD }} - <strong>Módulo({{ $mod->N_MODULO }})</strong> 
                                    </div>
                                    <button class="nobtn badge badge-primary badge-pill bandejTool" data-tippy-content="Eliminar" onclick="btnEliminarModulo('{{ $mod->IDMODULO }}')"><i class="las la-trash-alt text-secondary font-16 text-danger"></i></button>
                                </li>
                            @empty
                                <p>No hay módulos registrados.</p>
                            @endforelse
                           
                        </ul>
                    </div>
                </div>
            </div>
        </div> -->
        
    
</div>


{{-- Ver Modales --}}
<div class="modal fade" id="modal_show_modal" tabindex="-1" role="dialog" ></div>
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
<script src="{{asset('nuevo/plugins/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.js')}}"></script>
<!-- Buttons examples -->
<script src="{{asset('nuevo/plugins/datatables/dataTables.buttons.min.js')}}"></script>
<script src="{{asset('nuevo/plugins/datatables/buttons.bootstrap5.min.js')}}"></script>
<script src="{{asset('nuevo/plugins/datatables/jszip.min.js')}}"></script>
<script src="{{asset('nuevo/plugins/datatables/pdfmake.min.js')}}"></script>
<script src="{{asset('nuevo/plugins/datatables/vfs_fonts.js')}}"></script>
<script src="{{asset('nuevo/plugins/datatables/buttons.html5.min.js')}}"></script>
<script src="{{asset('nuevo/plugins/datatables/buttons.print.min.js')}}"></script>
<script src="{{asset('nuevo/plugins/datatables/buttons.colVis.min.js')}}"></script>
<!-- Responsive examples -->
<script src="{{asset('nuevo/plugins/datatables/dataTables.responsive.min.js')}}"></script>
<script src="{{asset('nuevo/plugins/datatables/responsive.bootstrap4.min.js')}}"></script>
<script src="{{asset('nuevo/assets/pages/jquery.datatable.init.js')}}"></script>
    
<script src="{{asset('js/toastr.min.js')}}"></script>

<script src="{{ asset('js/form.js') }}"></script>

<script>
$(document).ready(function() {
    // tabla_seccion();
    tippy(".bandejTool", {
        allowHTML: true,
        followCursor: true,
    });
    $('.select2').select2();

    

});

// function tabla_seccion() {
//     $.ajax({
//         type: 'GET',
//         url: "{{ route('configuracion.tablas.tb_nuevo_mac') }}", // Ruta que devuelve la vista en HTML
//         data: {},
//         beforeSend: function () {
//             document.getElementById("table_data").innerHTML = '<i class="fa fa-spinner fa-spin"></i> ESPERE LA TABLA ESTA CARGANDO... ';
//         },
//         success: function(data) {
//             $('#table_data').html(data); // Inserta la vista en un contenedor en tu página
//         }
//     });
// }

function btnAddEntidad() {

    var mac = "{{ $mac->IDCENTRO_MAC }}";

    var formData = new FormData();
    formData.append("addEntidad", $("#addEntidad").val());
    formData.append("idmac", mac);
    formData.append("_token", $("input[name=_token]").val());

    $.ajax({
        type:'post',
        url: "{{ route('configuracion.addEntidad') }}",
        dataType: "json",
        data:formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
            document.getElementById("btn-guardar").innerHTML = '<i class="fa fa-spinner fa-spin"></i> Espere';
            document.getElementById("btn-guardar").disabled = true;
        },
        success:function(data){        
            document.getElementById("btn-guardar").innerHTML = 'Agregar';
            document.getElementById("btn-guardar").disabled = false;
            $( "#datos" ).load(window.location.href + " #datos" ); 
            $( "#datos-mod-enc" ).load(window.location.href + " #datos-mod-enc" );
            // tabla_seccion();
        },
        error: function(xhr, status, error) {
            document.getElementById("btn-guardar").innerHTML = 'Agregar';
            document.getElementById("btn-guardar").disabled = false;

            let mensaje = "Ocurrio un error al registrar la entidad.";

            if (xhr.responseJSON && xhr.responseJSON.message) {
                mensaje = xhr.responseJSON.message;
            } else if (xhr.responseText) {
                try {
                    const respuesta = JSON.parse(xhr.responseText);
                    if (respuesta.message) {
                        mensaje = respuesta.message;
                    }
                } catch (e) {
                    mensaje = xhr.responseText;
                }
            } else if (error) {
                mensaje = error;
            }

            console.error("Error en addEntidad:", {
                status: status,
                error: error,
                responseText: xhr.responseText
            });
            toastr.error(mensaje);
        }
    });


}

function guardarConfigBiometrico() {
    var mac = "{{ $mac->IDCENTRO_MAC }}";
    var formData = new FormData();
    formData.append("idmac", mac);
    formData.append("tipo_biometrico", $("#tipo_biometrico").val());
    formData.append("fecha_inicio", $("#fecha_inicio_biometrico").val());
    formData.append("fecha_fin", $("#fecha_fin_biometrico").val());
    formData.append("_token", $("input[name=_token]").val());

    $.ajax({
        type: 'post',
        url: "{{ route('configuracion.storeConfiguracionBiometrico') }}",
        dataType: "json",
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
            document.getElementById("btnGuardarConfigBiometrico").innerHTML = '<i class="fa fa-spinner fa-spin"></i> Espere';
            document.getElementById("btnGuardarConfigBiometrico").disabled = true;
        },
        success: function (data) {
            toastr.success(data.message || "Configuración guardada correctamente.");
            $("#datos-biometrico").load(window.location.href + " #datos-biometrico > *", function () {
                tippy(".bandejTool", {
                    allowHTML: true,
                    followCursor: true,
                });
            });
            document.getElementById("btnGuardarConfigBiometrico").innerHTML = 'Guardar';
            document.getElementById("btnGuardarConfigBiometrico").disabled = false;
        },
        error: function (xhr) {
            document.getElementById("btnGuardarConfigBiometrico").innerHTML = 'Guardar';
            document.getElementById("btnGuardarConfigBiometrico").disabled = false;

            var mensaje = "Ocurrio un error al guardar la configuración.";
            if (xhr.responseJSON && xhr.responseJSON.message) {
                mensaje = xhr.responseJSON.message;
            }
            toastr.error(mensaje);
        }
    });
}

function btnBajaBiometrico(id, fechaInicio) {
    var hoy = new Date().toISOString().split('T')[0];

    swal.fire({
        title: "Dar de baja biométrico",
        html: '<div class="text-start">' +
            '<label class="form-label">Fecha fin</label>' +
            '<input type="date" id="fecha_fin_baja_biometrico" class="form-control" value="' + hoy + '" min="' + fechaInicio + '">' +
            '</div>',
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Dar de baja",
        cancelButtonText: "Cancelar",
        preConfirm: function () {
            var fechaFin = document.getElementById("fecha_fin_baja_biometrico").value;
            if (!fechaFin) {
                Swal.showValidationMessage("Seleccione la fecha fin.");
                return false;
            }
            if (fechaFin < fechaInicio) {
                Swal.showValidationMessage("La fecha fin no puede ser menor a la fecha de inicio.");
                return false;
            }
            return fechaFin;
        }
    }).then((result) => {
        if (!result.value) {
            return;
        }

        $.ajax({
            type: 'post',
            url: "{{ route('configuracion.bajaConfiguracionBiometrico') }}",
            dataType: "json",
            data: {
                "_token": "{{ csrf_token() }}",
                idconfiguracion_biometrico: id,
                idmac: "{{ $mac->IDCENTRO_MAC }}",
                fecha_fin: result.value
            },
            success: function (data) {
                toastr.success(data.message || "Biométrico dado de baja correctamente.");
                $("#datos-biometrico").load(window.location.href + " #datos-biometrico > *", function () {
                    tippy(".bandejTool", {
                        allowHTML: true,
                        followCursor: true,
                    });
                });
            },
            error: function (xhr) {
                var mensaje = "Ocurrio un error al dar de baja el biométrico.";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    mensaje = xhr.responseJSON.message;
                }
                toastr.error(mensaje);
            }
        });
    });
}


function btnEliminarEntidad(id) {

    swal.fire({
        title: "Seguro que desea eliminar la entidad?",
        text: "La entidad será eliminado totalmente ",
        icon: "error",
        showCancelButton: !0,
        confirmButtonText: "Aceptar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: "{{ route('configuracion.deleteEntidad') }}",
                type: 'post',
                data: {"_token": "{{ csrf_token() }}", id: id},
                success: function(response){
                    console.log(response);
                    $( "#datos" ).load(window.location.href + " #datos" );
                    $( "#datos-mod-enc" ).load(window.location.href + " #datos-mod-enc" );
                },
                error: function(error){
                    console.log('Error '+error);
                }
            });
        }
    })
}


function btnAddModulo(){

    var mac = "{{ $mac->IDCENTRO_MAC }}";

    var formData = new FormData();
    formData.append("n_modulo", $("#n_modulo").val());
    formData.append("addModEnt", $("#addModEnt").val());
    formData.append("idmac", mac);
    formData.append("_token", $("input[name=_token]").val());

    $.ajax({
        type:'post',
        url: "{{ route('configuracion.addModulo') }}",
        dataType: "json",
        data:formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
            document.getElementById("btn-guardar-2").innerHTML = '<i class="fa fa-spinner fa-spin"></i> Espere';
            document.getElementById("btn-guardar-2").disabled = true;
        },
        success:function(data){        
            document.getElementById("btn-guardar-2").innerHTML = 'Agregar';
            document.getElementById("btn-guardar-2").disabled = false;
            $( "#datos-modd" ).load(window.location.href + " #datos-modd" );

            $("#n_modulo").val('');
            // $("#addModEnt").val("");
            // tabla_seccion();
        }
    });
}

function btnEliminarModulo(id) {

swal.fire({
    title: "Seguro que desea eliminar la entidad?",
    text: "La entidad será eliminado totalmente ",
    icon: "error",
    showCancelButton: !0,
    confirmButtonText: "Aceptar",
    cancelButtonText: "Cancelar"
}).then((result) => {
    if (result.value) {
        $.ajax({
            url: "{{ route('configuracion.deleteModulo') }}",
            type: 'post',
            data: {"_token": "{{ csrf_token() }}", id: id},
            success: function(response){
                console.log(response);
                $( "#datos-modd" ).load(window.location.href + " #datos-modd" );
            },
            error: function(error){
                console.log('Error '+error);
            }
        });
    }
})
}

</script>

@endsection
