@extends('layouts.layout')

@section('style')
    <link href="{{ asset('css/toastr.min.css') }}" rel="stylesheet">

    <style>
        .power-bi-dash {
            width: 100%;
            height: 800px;
            border: 1px solid #5f5f5f;
        }
    </style>
@endsection

@section('main')
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="row">
                    <div class="col">
                        <h4 class="page-title">Reportes - DASHBOARD</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('inicio') }}">
                                    <i data-feather="home" class="align-self-center"></i>
                                </a>
                            </li>
                            <li class="breadcrumb-item active" style="color:#7081b9;">
                                Reporte ALO MAC
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenedor Dashboard ALO MAC --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card">

                <div class="card-header" style="background-color:#132842">
                    <h4 class="card-title text-white mb-0">
                        Reporte ALO MAC
                    </h4>
                </div>

                <div class="alert custom-alert custom-alert-warning icon-custom-alert shadow-sm fade show d-flex justify-content-between mt-3"
                    role="alert">
                    <div class="media">
                        <i class="la la-exclamation-triangle alert-icon text-warning align-self-center font-30 me-3"></i>
                        <div class="media-body align-self-center">
                            <h5 class="mb-1 fw-bold mt-0">Importante</h5>
                            <span>
                                Si el dashboard no se visualiza,
                                <a href="https://tinyurl.com/Dashboardalomacv1" target="_blank"
                                    class="text-decoration-underline fw-bold fs-4 text-dark">
                                    dar click aquí
                                </a>
                                para abrirlo en una nueva pestaña.
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <iframe src="https://tinyurl.com/Dashboardalomacv1" frameborder="0" class="power-bi-dash">
                    </iframe>
                </div>

            </div>
        </div>
    </div>

    {{-- Modal por si se reutiliza --}}
    <div class="modal fade" id="modal_show_modal" tabindex="-1" role="dialog"></div>
@endsection

@section('script')
    <script src="{{ asset('js/toastr.min.js') }}"></script>
@endsection
