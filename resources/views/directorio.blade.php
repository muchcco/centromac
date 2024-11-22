@extends('layouts.layout')

@section('main')
<div class="container-fluid">
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="row">
                    <div class="col">
                        <h4 class="page-title">Módulo de apoyo</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('inicio') }}"><i data-feather="home" class="align-self-center" style="height: 70%; display: block;"></i></a></li>
                            <li class="breadcrumb-item"><a href="javascript:void(0);" style="color: #7081b9;">Directorio de apoyo</a></li>
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
                <h4 class="card-title text-white">DIRECTORIO DE APOYO
                </h4>
            </div><!--end card-header-->
            <div class="card-body bootstrap-select-1">

                <div class="card-body">
                    <!-- Nav tabs -->
                    <ul class="nav nav-pills nav-justified" role="tablist">
                        <li class="nav-item waves-effect waves-light">
                            <a class="nav-link active" data-bs-toggle="tab" href="#home-1" role="tab" aria-selected="true">DIRECTORIO DE COORDINADORES</a>
                        </li>
                        <li class="nav-item waves-effect waves-light">
                            <a class="nav-link" data-bs-toggle="tab" href="#profile-1" role="tab" aria-selected="false">DIRECTORIO DE SUPERVISORES</a>
                        </li>
                        <li class="nav-item waves-effect waves-light">
                            <a class="nav-link" data-bs-toggle="tab" href="#settings-1" role="tab" aria-selected="false">DIRECTORIO DE ESPECIALISTAS TIC</a>
                        </li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div class="tab-pane p-3 active" id="home-1" role="tabpanel">
                            <p class="text-muted mb-0">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="table-responsive">
                                            <div class="table-responsive" id="table_data">
                                                <table class="table table-hover table-bordered table-striped" id="table_asistencia">
                                                    <thead class="tenca">
                                                        <tr>
                                                            <th>N°</th>
                                                            <th>NOMBRES</th>
                                                            <th>CENTRO MAC</th>
                                                            <th>CORREO</th>
                                                            <th>TELEFONO</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($coordinadores as $i => $c)
                                                            <tr>
                                                                <td>{{ $i + 1 }}</td>
                                                                <td>{{ $c->APE_PAT }} {{ $c->APE_MAT }}, {{ $c->NOMBRE }}</td>
                                                                <td>{{ $c->NOMBRE_MAC }} - {{ $c->NOMBRE_ENTIDAD }}</td>
                                                                <td>{{ $c->CORREO_INSTITUCIONAL }} / {{ $c->CORREO }}</td>
                                                                <td>{{ $c->CELULAR }} / {{ $c->TELEFONO }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="5">No hay datos disponibles</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                
                            </p>
                        </div>
                        <div class="tab-pane p-3" id="profile-1" role="tabpanel">
                            <p class="text-muted mb-0">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="table-responsive">
                                            <div class="table-responsive" id="table_data">
                                                <table class="table table-hover table-bordered table-striped" id="table_asistencia">
                                                    <thead class="tenca">
                                                        <tr>
                                                            <th>N°</th>
                                                            <th>NOMBRES</th>
                                                            <th>CENTRO MAC</th>
                                                            <th>CORREO</th>
                                                            <th>TELEFONO</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($supervisores as $i => $s)
                                                            <tr>
                                                                <td>{{ $i + 1 }}</td>
                                                                <td>{{ $s->APE_PAT }} {{ $s->APE_MAT }}, {{ $s->NOMBRE }}</td>
                                                                <td>{{ $s->NOMBRE_MAC }} - {{ $s->NOMBRE_ENTIDAD }}</td>
                                                                <td>{{ $s->CORREO_INSTITUCIONAL }} / {{ $s->CORREO }}</td>
                                                                <td>{{ $s->CELULAR }} / {{ $s->TELEFONO }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="5">No hay datos disponibles</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div> 
                            </p>
                        </div>
                        <div class="tab-pane p-3" id="settings-1" role="tabpanel">
                            <p class="text-muted mb-0">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="table-responsive">
                                            <div class="table-responsive" id="table_data">
                                                <table class="table table-hover table-bordered table-striped" id="table_asistencia">
                                                    <thead class="tenca">
                                                        <tr>
                                                            <th>N°</th>
                                                            <th>NOMBRES</th>
                                                            <th>CENTRO MAC</th>
                                                            <th>CORREO</th>
                                                            <th>TELEFONO</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($especialistas_tic as $i => $tic)
                                                            <tr>
                                                                <td>{{ $i + 1 }}</td>
                                                                <td>{{ $tic->APE_PAT }} {{ $tic->APE_MAT }}, {{ $tic->NOMBRE }}</td>
                                                                <td>{{ $tic->NOMBRE_MAC }} - {{ $tic->NOMBRE_ENTIDAD }}</td>
                                                                <td>{{ $tic->CORREO_INSTITUCIONAL }} / {{ $tic->CORREO }}</td>
                                                                <td>{{ $tic->CELULAR }} / {{ $tic->TELEFONO }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="5">No hay datos disponibles</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </p>
                        </div>
                    </div>    
                </div>
            </div>
        </div>
    </div>
</div>

@endsection