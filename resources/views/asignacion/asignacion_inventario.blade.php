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

<link href="{{ asset('css/toastr.min.css')}}" rel="stylesheet" type="text/css" /> 
<style>
    .select2-selection__rendered {
        line-height: 31px !important;
    }
    .select2-container .select2-selection--single {
        height: 35px !important;
    }
    .select2-selection__arrow {
        height: 34px !important;
    }
</style>

@endsection

@section('main')

<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row">
                <div class="col">
                    <h4 class="page-title">Asignación</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('inicio') }}"><i data-feather="home" class="align-self-center" style="height: 70%; display: block;"></i></a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Asignación</a></li>
                    </ol>
                </div><!--end col--> 
            </div><!--end row-->                                                              
        </div><!--end page-title-box-->
    </div><!--end col-->
</div><!--end row-->


<div class="row">
    <div class="col-12">
        <div class="card">                                        
            <div class="card-body">
                <div class="dastone-profile">
                    <div class="row">
                        <div class="col-lg-3 align-self-center mb-3 mb-lg-0">
                            <div class="dastone-profile-main">
                                <div class="dastone-profile-main-pic">
                                    <div class="avatar-box thumb-xxl align-self-center me-2">
                                        <span class="avatar-title bg-soft-info rounded-circle">{{ $personal->NOMBRE[0] }}{{ $personal->APE_PAT[0] }}</span>
                                    </div>
                                </div>
                                <div class="dastone-profile_user-detail">
                                    <h5 class="dastone-user-name">{{ $personal->NOMBRE }}</h5>                                                        
                                    <p class="mb-0 dastone-user-name-post">{{ $personal->APE_PAT }} {{ $personal->APE_MAT }}  </p>                                                        
                                </div>
                            </div>                                                
                        </div><!--end col-->
                        
                        <div class="col-lg-3 align-self-center"> 
                            <ul class="list-unstyled personal-detail mb-0">
                                <li class=""><i class="ti ti-mobile me-2 text-secondary font-16 align-middle"></i> <b> N° de Documento </b> : {{ $personal->TIPODOC_ABREV }} {{ $personal->NUM_DOC }}</li>
                                <li class="mt-2"><i class="ti ti-email text-secondary font-16 align-middle me-2"></i> <b> Entidad </b> : {{ $personal->ABREV_ENTIDAD }}</li>
                                <li class="mt-2"><i class="ti ti-email text-secondary font-16 align-middle me-2"></i> <b> Correo </b> : {{ $personal->CORREO }}<li>
                                <li class="mt-2"><i class="ti ti-email text-secondary font-16 align-middle me-2"></i> <b> Correo </b> : {{ $personal->CORREO }}<li>
                            </ul>                           
                        </div><!--end col-->
                        <div class="col-lg-6 align-self-center border-start"> 
                            <div id="documento_actualizar_estado">
                                @if ($asignacion_estado )
                                    @if ($asignacion_estado->IDESTADO_ASIG == '1')
                                        <p>Descargar borrador para la aprobación del Asesor(a): <a href="{{ url('asignacion/pdf/borrador_pdf',['idpersonal' => $personal->IDPERSONAL]) }}" class="text-primary" target="_blank">Descargar (clic)</a></p>
                                        <button class="btn btn-info" onclick="btnEstadoAprobar('{{ $asignacion_estado->IDASIG_PERSONAL }}')">Aprobar documento</button>
                                    @elseif($asignacion_estado->IDESTADO_ASIG == '2')
                                        <div class="alert alert-danger border-0" role="alert">
                                            <strong>Aviso!</strong> El documento se tendrá que adjuntar el mismo día que se realizarón las firmas.<br/> <button class="nobtn">regresar estado</button>
                                        </div>
                                        <p>Descargar documento original para la firma del Asesor(a): <a href="{{ url('asignacion/pdf/orginal_pdf',['idpersonal' => $personal->IDPERSONAL]) }}" class="text-primary" target="_blank">Descargar (clic)</a></p>                                        
                                        <div class="input-group">
                                            <input type="file" class="form-control" id="file_aprobado" name="file_aprobado">
                                            <button class="btn btn-primary" onclick="btnCargarDocumentoAprobacion('{{ $asignacion_estado->IDASIG_PERSONAL }}')">Agregar Documento</button>
                                        </div>
                                    @elseif($asignacion_estado->IDESTADO_ASIG == '3')

                                        <p>Descargar documento de devolucion de equipos del Asesor(a): <a href="{{ url('asignacion/pdf/orginal_pdf',['idpersonal' => $personal->IDPERSONAL]) }}" class="text-primary" target="_blank">Descargar (clic)</a></p>                                        
                                        <div class="input-group mb-3">
                                            <input type="file" class="form-control" id="file_aprobado" name="file_aprobado">
                                            <button class="btn btn-primary" onclick="btnCargarDocumentoAprobacion('{{ $asignacion_estado->IDASIG_PERSONAL }}')">Agregar Documento</button>
                                        </div>

                                        <table class="table table-bordered" >
                                            <tr class="bg-dark">
                                                <th>Nombre del Equipo</th>
                                                <th>Estado del documento</th>
                                                <th>Descargar Documento</th>
                                                <th>Regresar</th>                                            
                                            </tr>
                                            @forelse ($datos_acepta as $datos)
                                                <tr>
                                                    <td>{{ $datos->NOMBRE_DOCUMENTO }}</td>
                                                    <td>{{ $datos->NOMBRE_ESTADO_ASIG }}</td>
                                                    <td class="text-center"> <a href="{{ url($datos->NOMBRE_RUTA) }}" target="_blank"> <i class="fas fa-cloud-download-alt text-primary"></i> </a> </td>
                                                    <td class="text-center">
                                                        <button class="nobtn"><i class="fas fa-reply-all"></i></button>
                                                    </td>
                                                </tr>
                                            @empty
                                                
                                            @endforelse
                                        </table>

                                    @endif
                                @else
                                    <p>Tiene que ingresar almenos un bien</p>
                                @endif
                                
                                
                            </div>
                        </div><!--end col-->
                    </div><!--end row-->
                </div><!--end f_profile-->                                                                                
            </div><!--end card-body-->          
        </div> <!--end card-->    
    </div><!--end col-->
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header" style="background-color:#132842">
                <h4 class="card-title text-white">LISTA DE BIENES ASIGNADOS</h4>
            </div><!--end card-header-->

            <div class="card-body bootstrap-select-1">

                <div class="row mb-3">
                    <div class="col-12">
                        <div class="input-group">                               
                            <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />
                            <select name="idalmacen" id="idalmacen" class="form-control select2 " style="width: 92%">
                                <option value="0">-- Seleccionar un bien --</option>
                            </select>
                            <button type="button" class="btn btn-primary" id="btn-guardar" onclick="BtnGuardar('{{ $personal->IDPERSONAL }}')"> Agregar</button>
                        </div>
                    </div>
                </div>
                <div class="pb-4">
                    <ul class="nav-border nav nav-pills mb-0" id="pills-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="Profile_Project_tab" data-bs-toggle="pill" href="#Profile_Project"></a>
                        </li>
                    </ul>        
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/i18n/es.js"></script>  


<script>
$(document).ready(function() {
    tabla_seccion();
    /**************************************** SELECT 2 COMBO ***************************************************************/
    var csrf_token = $('meta[name="csrf-token"]').attr('content');
    $('.select2').select2({
        ajax: {
            url: "{{ route('asignacion.almacen_select') }}",
            dataType: 'json',
            type: "post",
            data: function (params) {
                return {
                    _token: csrf_token,
                    term: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            text: item.COD_INTERNO_PCM + ', ' + item.DESCRIPCION + ' - ' +item.MARCA + ' - ' + item.SERIE_MEDIDA + ' - ' + item.UBICACION_EQUIPOS,
                            id: item.IDALMACEN,
                            contryflage: item.DESCRIPCION
                        }
                    })
                };
            }
        },
        minimumInputLength: 0
    });

    // Agregar un escuchador de eventos para cambiar minimumInputLength después de escribir un carácter
    $('.select2').on('keyup', function () {
        var valorInput = $(this).val();

        // Cambiar minimumInputLength a 1 después de escribir al menos un carácter
        if (valorInput.length >= 1) {
            $('.select2').select2('options').set('minimumInputLength', 1);
        }
    });

    /**-********************************************************************************************************************************/
});

function tabla_seccion() {
    $.ajax({
        type: 'GET',
        url: "{{ route('asignacion.tablas.tb_asignacion') }}", // Ruta que devuelve la vista en HTML
        data: {},
        beforeSend: function () {
            document.getElementById("table_data").innerHTML = '<i class="fa fa-spinner fa-spin"></i> ESPERE LA TABLA ESTA CARGANDO... ';
        },
        success: function(data) {
            $('#table_data').html(data); // Inserta la vista en un contenedor en tu página
        }
    });
}

function BtnGuardar(idpersonal) {
    console.log(idpersonal);
    if($("#idalmacen").val() == '0' || $("#idalmacen").val() == ''){
        Swal.fire({
            icon: "info",
            text: "Tiene que seleccionar un bien!",
            confirmButtonText: "Aceptar"
        })
    }else{
        var formData = new FormData();
        formData.append("idalmacen", $("#idalmacen").val());
        formData.append("idpersonal", idpersonal);
        formData.append("_token", $("input[name=_token]").val());

        $.ajax({
            type:'post',
            url: "{{ route('asignacion.store_item') }}",
            dataType: "json",
            data:formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                document.getElementById("btn-guardar").innerHTML = '<i class="fa fa-spinner fa-spin"></i> Cargando';
                document.getElementById("btn-guardar").disabled = true;
            },
            success:function(data){        
                document.getElementById("btn-guardar").innerHTML = 'Agregar';
                document.getElementById("btn-guardar").disabled = false;
                $( "#idalmacen" ).load(window.location.href + " #idalmacen" ); 
                $( "#documento_actualizar_estado" ).load(window.location.href + " #documento_actualizar_estado" ); 
                tabla_seccion();
                Toastify({
                    text: "Se inresó el registro",
                    className: "info",
                    style: {
                        background: "#206AC8",
                    }
                }).showToast();
            }
        });
    }
}

function BtnElimnar(idasignacion){

    swal.fire({
            title: "Seguro que desea eliminar su calificación?",
            text: "La calificación será eliminado totalmente",
            icon: "error",
            showCancelButton: !0,
            confirmButtonText: "Aceptar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: "{{ route('asignacion.eliminar_item') }}",
                    type: 'post',
                    data: {"_token": "{{ csrf_token() }}", idasignacion: idasignacion},
                    success: function(response){
                        console.log(response);
                        tabla_seccion(); 
                        
                        // colocar el atributo data-delete para poder actualizar la

                        Toastify({
                            text: "Se eliminó el registro",
                            className: "danger",
                            style: {
                                background: "#DF1818",
                            }
                        }).showToast();
                    },
                    error: function(error){
                        console.log('Error '+error);
                    }
                });
            }
        })

}

function ModalEstado(idasignacion){

    $.ajax({
        type:'post',
        url: "{{ route('asignacion.modals.md_add_estado') }}",
        dataType: "json",
        data:{"_token": "{{ csrf_token() }}", idasignacion : idasignacion},
        success:function(data){
            $("#modal_show_modal").html(data.html);
            $("#modal_show_modal").modal('show');
        }
    });
}

function btnStoreEstado(idasignacion){

    var formData = new FormData();
        formData.append("estado", $("#estado").val());
        formData.append('idasignacion', idasignacion);
        formData.append("_token", $("input[name=_token]").val());

        $.ajax({
            type:'post',
            url: "{{ route('asignacion.store_estado') }}",
            dataType: "json",
            data:formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                document.getElementById("btnEnviarForm").innerHTML = '<i class="fa fa-spinner fa-spin"></i> Espere';
                document.getElementById("btnEnviarForm").disabled = true;
            },
            success:function(data){                
                $("#modal_show_modal").modal('hide');
                tabla_seccion();
                //$( "#estado_cambio" ).load(window.location.href + " #estado_cambio" ); 
                Toastify({
                    text: "Se actualizaron los cambios",
                    className: "info",
                    style: {
                        background: "#206AC8",
                    }
                }).showToast();
            }
        });
}

function ModalObservacion(idasignacion){

    $.ajax({
        type:'post',
        url: "{{ route('asignacion.modals.md_add_observacion') }}",
        dataType: "json",
        data:{"_token": "{{ csrf_token() }}", idasignacion : idasignacion},
        success:function(data){
            $("#modal_show_modal").html(data.html);
            $("#modal_show_modal").modal('show');
        }
    });
}

function btnStoreObservacion(idasignacion){

var formData = new FormData();
    formData.append("observacion", $("#observacion").val());
    formData.append('idasignacion', idasignacion);
    formData.append("_token", $("input[name=_token]").val());

    $.ajax({
        type:'post',
        url: "{{ route('asignacion.store_observacion') }}",
        dataType: "json",
        data:formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
            document.getElementById("btnEnviarForm").innerHTML = '<i class="fa fa-spinner fa-spin"></i> Espere';
            document.getElementById("btnEnviarForm").disabled = true;
        },
        success:function(data){                
            $("#modal_show_modal").modal('hide');
            tabla_seccion();
            //$( "#estado_cambio" ).load(window.location.href + " #estado_cambio" ); 
            Toastify({
                text: "Se actualizaron los cambios",
                className: "info",
                style: {
                    background: "#206AC8",
                }
            }).showToast();
        }
    });
}

function btnEstadoAprobar(asignacion_estado){

    swal.fire({
            title: "Seguro que el Asesor dio validez del borrador?",
            text: "No habrá modificaciones al momento de aceptar los cambios",
            icon: "error",
            showCancelButton: !0,
            confirmButtonText: "Aceptar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: "{{ route('asignacion.estado_borrador') }}",
                    type: 'post',
                    data: {"_token": "{{ csrf_token() }}", asignacion_estado: asignacion_estado},
                    success: function(response){
                        console.log(response);
                        tabla_seccion(); 
                        
                        // colocar el atributo data-delete para poder actualizar la
                        $( "#documento_actualizar_estado" ).load(window.location.href + " #documento_actualizar_estado" ); 
                        Toastify({
                            text: "Se actualizo el estado",
                            className: "info",
                            style: {
                                background: "#206AC8",
                            }
                        }).showToast();
                    },
                    error: function(error){
                        console.log('Error '+error);
                    }
                });
            }
        })
}

function btnCargarDocumentoAprobacion(asignacion_estado){

    var file_vacio = $("#file_aprobado").val();

    if (file_vacio == '' ) {
        Swal.fire({
            icon: "warning",
            text: "No ha cargado ningun documento!",
            confirmButtonText: "Aceptar"
        });
    } else {
        swal.fire({
            title: "Seguro que desea cargar el documento?",
            text: "Al cargar el documento se cambiara el estado del documento",
            icon: "info",
            showCancelButton: !0,
            confirmButtonText: "Aceptar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.value) {

                var file_data = $("#file_aprobado").prop("files")[0];
                var formData = new FormData();

                formData.append("file_aprobado", file_data);
                formData.append("asignacion_estado", asignacion_estado);
                formData.append("_token", "{{ csrf_token() }}");

                $.ajax({
                    url: "{{ route('asignacion.cargar_documento_acept') }}",
                    type: 'post',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response){
                        console.log(response);
                        tabla_seccion(); 
                        
                        // colocar el atributo data-delete para poder actualizar la
                        $( "#documento_actualizar_estado" ).load(window.location.href + " #documento_actualizar_estado" ); 
                        Toastify({
                            text: "Se actualizo el estado",
                            className: "info",
                            style: {
                                background: "#206AC8",
                            }
                        }).showToast();
                    },
                    error: function(error){
                        console.log('Error '+error);
                    }
                });
            }
        })
    }


    

}

</script>

@endsection