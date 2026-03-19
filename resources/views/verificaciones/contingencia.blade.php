@extends('layouts.layout')

@section('style')
    <style>
        .table thead th {
            background: #233E99;
            color: white;
            font-size: 11px;
            text-align: center;
        }

        .table td {
            text-align: center;
            font-size: 11px;
            padding: 4px;
        }

        .ok {
            color: #198754;
            font-weight: bold;
        }

        .fail {
            color: #dc3545;
            font-weight: bold;
        }

        .sticky {
            position: sticky;
            left: 0;
            background: #fff;
            font-weight: bold;
        }
    </style>
@endsection

@section('main')
    <div class="container">
        <!-- 🔷 HEADER ESTILO SISTEMA -->
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <div class="row">
                        <div class="col">
                            <h4 class="page-title">Contingencia de Verificaciones</h4>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('inicio') }}">Inicio</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('verificaciones.index') }}">Verificaciones</a>
                                </li>
                                <li class="breadcrumb-item active">Contingencia Mensual</li>
                            </ol>
                        </div>

                        <div class="col-auto">
                            <a href="{{ route('verificaciones.index') }}" class="btn btn-secondary">
                                <i class="fa fa-arrow-left"></i> Volver
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 🔷 CARD FILTRO -->
        <div class="card mb-3">
            <div class="card-header" style="background-color:#132842">
                <h4 class="card-title text-white mb-0">Filtro de Contingencia</h4>
            </div>

            <div class="card-body">
                <form method="GET">
                    <div class="row align-items-end">

                        <!-- MES -->
                        <div class="col-md-4">
                            <label class="fw-bold text-dark mb-2">Mes:</label>
                            <input type="month" name="mes" value="{{ $mes }}" class="form-control">
                        </div>

                        <!-- BOTÓN FILTRAR -->
                        <div class="col-md-3 d-grid">
                            <button class="btn btn-primary">
                                <i class="fa fa-search"></i> Filtrar
                            </button>
                        </div>

                        <!-- OPCIONAL (FUTURO) -->
                        <div class="col-md-3 d-grid">
                            <button type="button" class="btn btn-success" onclick="window.print()">
                                <i class="fa fa-print"></i> Imprimir
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="table table-bordered">

                <thead>
                    <tr>
                        <th class="sticky">Campo</th>

                        @foreach (range(1, 31) as $d)
                            <th>{{ $d }}</th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>

                    @foreach ($campos as $campo)
                        @php
                            $nombre = preg_replace('/([a-z])([A-Z])/', '$1 $2', $campo);
                        @endphp

                        <tr>

                            <td class="sticky">{{ $nombre }}</td>

                            @foreach (range(1, 31) as $d)
                                @php
                                    $val = $matriz[$campo][$d] ?? '-';
                                @endphp

                                <td
                                    class="
                                {{ $val == '✔✔' ? 'ok' : ($val != '-' ? 'fail' : '') }}
                            ">
                                    {{ $val }}
                                </td>
                            @endforeach

                        </tr>
                    @endforeach

                </tbody>

            </table>
        </div>

    </div>
@endsection
