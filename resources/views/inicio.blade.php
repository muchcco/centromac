@extends('layouts.layout')

@section('main')
<div class="container-fluid">
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="row">
                    <div class="col">
                        <h4 class="page-title">Panel de Control</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('inicio') }}"><i data-feather="home" class="align-self-center" style="height: 70%; display: block;"></i></a></li>
                            {{-- <li class="breadcrumb-item"><a href="javascript:void(0);" style="color: #7081b9;">Pago Locadores</a></li> --}}
                        </ol>
                    </div><!--end col--> 
                </div><!--end row-->                                                              
            </div><!--end page-title-box-->
        </div><!--end col-->
    </div><!--end row-->
    <!-- end page title end breadcrumb -->
    
    <div class="row">
        <div class="col-lg-9">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card">
                        <div class="card-body">
                            <div class="row d-flex justify-content-center">
                                <div class="col">
                                    <p class="text-dark mb-0 fw-semibold">Asesores registrados</p>
                                    <h3 class="m-0">{{ $count_asesores }}</h3>
                                    
                                </div>
                                <div class="col-auto align-self-center">
                                    <div class="report-main-icon bg-light-alt">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users align-self-center text-muted icon-sm"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>  
                                    </div>
                                </div>
                            </div>
                        </div><!--end card-body--> 
                    </div><!--end card--> 
                </div> <!--end col--> 
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card">
                        <div class="card-body">
                            <div class="row d-flex justify-content-center">                                                
                                <div class="col">
                                    <p class="text-dark mb-0 fw-semibold">Entidades participantes</p>
                                    <h3 class="m-0">{{ $count_entidad }}</h3>
                                    
                                </div>
                                <div class="col-auto align-self-center">
                                    <div class="report-main-icon bg-light-alt">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clock align-self-center text-muted icon-sm"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>  
                                    </div>
                                </div> 
                            </div>
                        </div><!--end card-body--> 
                    </div><!--end card--> 
                </div> <!--end col--> 
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card">
                        <div class="card-body">
                            <div class="row d-flex justify-content-center">                                                
                                <div class="col">
                                    <p class="text-dark mb-0 fw-semibold">Servicios brindados</p>
                                    <h3 class="m-0">$2400</h3>
                                    
                                </div>
                                <div class="col-auto align-self-center">
                                    <div class="report-main-icon bg-light-alt">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-activity align-self-center text-muted icon-sm"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>  
                                    </div>
                                </div> 
                            </div>
                        </div><!--end card-body--> 
                    </div><!--end card--> 
                </div> <!--end col--> 
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card">
                        <div class="card-body">
                            <div class="row d-flex justify-content-center">
                                <div class="col">  
                                    <p class="text-dark mb-0 fw-semibold">Atenciones brindadas</p>                                         
                                    <h3 class="m-0">85000</h3>
                                    
                                </div>
                                <div class="col-auto align-self-center">
                                    <div class="report-main-icon bg-light-alt">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-briefcase align-self-center text-muted icon-sm"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>  
                                    </div>
                                </div> 
                            </div>
                        </div><!--end card-body--> 
                    </div><!--end card--> 
                </div> <!--end col-->                               
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">                      
                            <h4 class="card-title">Ocupabilidad del modulo</h4>                      
                        </div><!--end col-->
                        <div class="col-auto"> 
                            <div class="dropdown">
                                <a href="#" class="btn btn-sm btn-outline-light dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                   Todos<i class="las la-angle-down ms-1"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="#">Asistio</a>
                                    <a class="dropdown-item" href="#">No Asistio</a>
                                </div>
                            </div>         
                        </div><!--end col-->
                    </div>  <!--end row-->                                  
                </div><!--end card-header-->
                <div class="card-body"> 
                    <div class="table-responsive mt-2">
                        <table class="table border-dashed mb-0">
                            <thead>
                            <tr>
                                <th>Entidad</th>
                                <th class="text-end">Inicio de operacion</th>
                                <th class="text-end">Estado</th>
                            </tr>
                            </thead>
                            <tbody id="inicio_sesion">
                                <tr>
                                    <td colspan="3" id="filt_dat"><span id="filtro"></span></td>
                                </tr>
                            </tbody>
                        </table><!--end /table-->
                    </div><!--end /div-->                                 
                </div><!--end card-body--> 
            </div><!--end card--> 
        </div>

    </div>

</div><!-- container -->
@endsection

@section('script')
    
<script>

$(document).ready(function() {
    InicioSession();
});

function InicioSession() {
    console.log("asd");
    $.ajax({
        type: 'get',
        url: "{{ route('consultas_novo') }}",
        dataType: "json",
        data: {},
        beforeSend: function () {
            document.getElementById("filtro").innerHTML = '<i class="fa fa-spinner fa-spin"></i> Cargando datos, espere por favor...';
            document.getElementById("filtro").style.disabled = true;
        },
        success: function (data) {
            document.getElementById("filtro").style.display = 'none';
            document.getElementById("filt_dat").style.display = 'none';

            // Construir la tabla con los datos recibidos
            var tablaHtml = '<table class="table border-dashed mb-0">';
            tablaHtml += '<tbody id="inicio_sesion">';

            // Iterar sobre los datos y agregar filas a la tabla
            for (var i = 0; i < data.length; i++) {
                tablaHtml += '<tr>';
                tablaHtml += '<td>' + data[i].Entidad + '</td>';
                tablaHtml += '<td class="text-end">' + data[i].hora + '</td>';
                // Puedes ajustar la lógica para determinar el estado según tus necesidades
                tablaHtml += '<td class="text-end">' + (data[i].hora > '08:15:00' ? 'Tarde' : 'En hora') + '</td>';
                tablaHtml += '</tr>';
            }

            tablaHtml += '</tbody>';
            tablaHtml += '</table>';

            // Insertar la tabla dentro del elemento con id "inicio_sesion"
            $("#inicio_sesion").html(tablaHtml);
        },
        error: function (xhr, status, error) {
            console.log("error");
            console.log('Error:', error);
        }
    });

}

</script>

@endsection