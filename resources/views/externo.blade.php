@extends('layouts.layout')

@section('main')
<div class="container-fluid">
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="row">
                    <div class="col">
                        <h4 class="page-title">Ventanas externas</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('inicio') }}"><i data-feather="home" class="align-self-center" style="height: 70%; display: block;"></i></a></li>
                            <li class="breadcrumb-item"><a href="javascript:void(0);" style="color: #7081b9;">Páginas de apoyo</a></li>
                        </ol>
                    </div><!--end col--> 
                </div><!--end row-->                                                              
            </div><!--end page-title-box-->
        </div><!--end col-->
    </div><!--end row-->
    <!-- end page title end breadcrumb -->
    

</div><!-- container -->



<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header" style="background-color:#132842">
                <h4 class="card-title text-white">LISTA DE PÁGINAS EXTERNAS 
                </h4>
            </div><!--end card-header-->
            <div class="card-body bootstrap-select-1">
                
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <div class="table-responsive col-5" id="table_data">
                                <table class="table table-bordered ">
                                    <tr>
                                        <th class="text-dark"><li>Formulario de registro</li> </th>
                                        <td><a href="http://190.187.182.55:8081/external-mac/personal" target="_blank">Ir al link</a></td>
                                    </tr>
                                    {{-- <tr>
                                        <th class="text-dark"><li>Revisión de ciudadanos por entidad</li></th>
                                        <td><a href="{{ route('vista') }}" target="_blank">Ir al link</a></td>
                                    </tr> --}}
                                    {{-- <tr>
                                        <th class="text-dark"><li>PowerApp</li></th>
                                        <td><a href="https://apps.powerapps.com/play/e/default-34b48e4e-2519-4060-a09e-5b05d901a4d7/a/a2036540-c323-4a79-8cf2-0a9330c23119?tenantId=34b48e4e-2519-4060-a09e-5b05d901a4d7&hint=7bbca76f-2366-4f77-82a7-401fa606b4c1&sourcetime=1720627181714&source=portal" target="_blank">Ir al link</a></td>
                                    </tr> --}}
                                    <tr>
                                        <th class="text-dark"><li>Registro de asistencia manual</li></th>
                                        <td><a href="http://190.187.182.55:8081/external-mac/assists" target="_blank">Ir al link</a></td>
                                    </tr>
                                </table>
                                
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

@endsection