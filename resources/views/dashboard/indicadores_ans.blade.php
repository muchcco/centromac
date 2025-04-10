@extends('layouts.layout')

@section('style')
    <link rel="stylesheet" href="{{ asset('Vendor/toastr/toastr.min.css') }}">
    <link href="{{ asset('nuevo/plugins/select2/select2.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/toastr.min.css') }}" rel="stylesheet" />

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
                            <li class="breadcrumb-item active" style="color: #7081b9;">Indicadores ANS</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenedor del Dashboard ANS --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header" style="background-color:#132842">
                    <h4 class="card-title text-white mb-0">Indicadores de ANS</h4>
                </div>
                <div class="card-body">
                    <iframe
                        src="https://app.powerbi.com/view?r=eyJrIjoiNmM1Mzk5ZWYtODY1Zi00MDJhLTllM2EtYWEyZjA2Y2RhOGYyIiwidCI6IjM0YjQ4ZTRlLTI1MTktNDA2MC1hMDllLTViMDVkOTAxYTRkNyJ9"
                        frameborder="0"
                        class="power-bi-dash">
                    </iframe>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal por si lo necesitas --}}
    <div class="modal fade" id="modal_show_modal" tabindex="-1" role="dialog"></div>
@endsection

@section('script')
    <script src="{{ asset('Vendor/toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/select2/select2.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('nuevo/plugins/datatables/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('js/toastr.min.js') }}"></script>
@endsection
