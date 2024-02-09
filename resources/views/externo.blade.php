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
                            {{-- <li class="breadcrumb-item"><a href="javascript:void(0);" style="color: #7081b9;">Pago Locadores</a></li> --}}
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
                <h4 class="card-title text-white">LISTA DE PAGINAS EXTERNAS DEL CENTRO MAC -  
                    @php
                        $us_id = auth()->user()->idcentro_mac;
                        $user = App\Models\User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

                        echo $user->NOMBRE_MAC;
                    @endphp
                </h4>
            </div><!--end card-header-->
            <div class="card-body bootstrap-select-1">
                
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <div class="table-responsive col-5" id="table_data">
                                <table class="table table-bordered ">
                                    <tr>
                                        <th class="text-dark"><li>Formulario de llenado para los asesoras</li> </th>
                                        <td><a href="{{ route('validar') }}" target="_blank">Ir al link</a></td>
                                    </tr>
                                    <tr>
                                        <th class="text-dark"><li>Revisión de ciudadanos por entidad</li></th>
                                        <td><a href="{{ route('vista') }}" target="_blank">Ir al link</a></td>
                                    </tr>
                                    <tr>
                                        <th class="text-dark"><li>Revisión de servicios por entidad</li></th>
                                        <td><a href="{{ route('servicios') }}" target="_blank">Ir al link</a></td>
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