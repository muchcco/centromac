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

@endsection

@section('main')

<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row">
                <div class="col">
                    <h4 class="page-title">Lista de usuarios con acceso al sistema</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('inicio') }}"><i data-feather="home" class="align-self-center" style="height: 70%; display: block;"></i></a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);" style="color: #7081b9;">Usuarios</a></li>
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
                <h4 class="card-title text-white">LISTA DE USUARIOS DEL CENTRO MAC -  
                    @php
                        $us_id = auth()->user()->idcentro_mac;
                        $user = App\Models\User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

                        echo $user->NOMBRE_MAC;
                    @endphp
                </h4>
            </div><!--end card-header-->
            <div class="card-body bootstrap-select-1">
                
                <div class="row">
                    <div class="col-12 mb-3">
                        <button class="btn btn-success" data-toggle="modal" data-target="#large-Modal" onclick="btnAddUsuarios()"><i class="fa fa-plus" aria-hidden="true"></i>
                            Agregar Usuario</button>
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

<script>
$(document).ready(function() {
    tabla_seccion();
    $('.select2').select2();
});

function tabla_seccion(entidad = '') {
    $.ajax({
        type: 'GET',
        url: "{{ route('usuarios.tablas.tb_index') }}", // Ruta que devuelve la vista en HTML
        data: {entidad: entidad},
        beforeSend: function () {
            document.getElementById("table_data").innerHTML = '<i class="fa fa-spinner fa-spin"></i> ESPERE LA TABLA ESTA CARGANDO... ';
        },
        success: function(data) {
            $('#table_data').html(data); // Inserta la vista en un contenedor en tu página
        }
    });
}

function btnAddUsuarios() {

    $.ajax({
        type:'post',
        url: "{{ route('usuarios.modals.md_add_usuario') }}",
        dataType: "json",
        data:{"_token": "{{ csrf_token() }}"},
        success:function(data){
            $("#modal_show_modal").html(data.html);
            $("#modal_show_modal").modal('show');
        }
    });

}

function btnStoreUsuario() {
   
    var roles = [];
    $("[name='roles[]']:checked").each(function (i) {
        roles[i] = $(this).val();
    });

    console.log(roles);
    if (roles.length === 0){ //tell you if the array is empty
        alert("Por favor seleccione un perfil");
    }
    else {
        var formData = new FormData();
        formData.append("id_usuario", $("#id_usuario").val());
        formData.append('roles', roles.join(','));            
        formData.append("_token", $("input[name=_token]").val());

        $.ajax({
            type:'post',
            url: "{{ route('usuarios.store_user') }}",
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
                Toastify({
                    text: "Se creo el usuario, en los proximos minutos le llegará su contraseña por correo",
                    className: "info",
                    style: {
                        background: "#09A039",
                    }
                }).showToast();
            }
        });
    }

}

function btnPassUsuario(id){
    $.ajax({
        type:'post',
        url: "{{ route('usuarios.modals.md_password_usuario') }}",
        dataType: "json",
        data:{"_token": "{{ csrf_token() }}", id : id},
        success:function(data){
            $("#modal_show_modal").html(data.html);
            $("#modal_show_modal").modal('show');
        }
    });
}

function btnStoreUpdatePassword(id) {
    var Password = $('#password').val();
    var confirmPassword = $('#confirmPassword').val();

    if (Password !== confirmPassword) {
        $('#text-confirm').show(); // Mostrar el mensaje de confirmación
    } else {
        var formData = new FormData();
        formData.append("password", Password);
        formData.append('id', id);
        formData.append("_token", $("input[name=_token]").val());

        $.ajax({
            type:'post',
            url: "{{ route('usuarios.updatepass_user') }}",
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
                Toastify({
                    text: "Se actualizaró la contraseña",
                    className: "info",
                    style: {
                        background: "#206AC8",
                    }
                }).showToast();
            }
        });
    }

    
}


function btnEditarUsuario(id) {
    $.ajax({
        type:'post',
        url: "{{ route('usuarios.modals.md_edit_usuario') }}",
        dataType: "json",
        data:{"_token": "{{ csrf_token() }}", id : id},
        success:function(data){
            $("#modal_show_modal").html(data.html);
            $("#modal_show_modal").modal('show');
        }
    });
}

function btnStoreUpdate(id) {
    var roles = [];
    $("[name='roles[]']:checked").each(function (i) {
        roles[i] = $(this).val();
    });

    console.log(roles);
    if (roles.length === 0){ //tell you if the array is empty
        alert("Por favor seleccione un perfil");
    }
    else {
        var formData = new FormData();
        formData.append("name", $("#name").val());
        formData.append("flag", $("#flag").val());
        formData.append('roles', roles.join(','));
        formData.append('id', id);
        formData.append("_token", $("input[name=_token]").val());

        $.ajax({
            type:'post',
            url: "{{ route('usuarios.update_user') }}",
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
                $( "#act_role_sidebar" ).load(window.location.href + " #act_role_sidebar" ); 
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
}

function btnElimnarUsuario(id) {

    swal.fire({
        title: "Seguro que desea eliminar el usuario?",
        text: "El usuario será eliminado totalmente con su perfil asignado",
        icon: "error",
        showCancelButton: !0,
        confirmButtonText: "Aceptar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: "{{ route('usuarios.delete_user') }}",
                type: 'post',
                data: {"_token": "{{ csrf_token() }}", id: id},
                success: function(response){
                    console.log(response);

                    tabla_seccion(); 

                    Toastify({
                        text: "Se eliminó el usuario",
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

</script>

@endsection