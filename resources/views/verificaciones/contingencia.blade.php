@extends('layouts.layout')

@section('style')
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        .table thead th {
            background-color: #233E99;
            color: white;
            font-weight: bold;
        }

        .table tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }

        .table tbody tr:hover {
            background-color: #e2e6ea;
        }

        .text-center {
            text-align: center;
        }

        .container {
            margin-top: 20px;
        }

        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }

        .table td {
            padding: 10px;
        }

        .table th {
            padding: 10px;
        }

        .form-control {
            border-radius: 0.25rem;
            box-shadow: none;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 0.25rem;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .header {
            margin-bottom: 20px;
        }

        .header h2 {
            font-size: 28px;
            font-weight: bold;
        }

        .header a {
            margin-left: 15px;
        }
    </style>
@endsection

@section('main')
    <div class="container">
        <div class="header d-flex justify-content-between align-items-center">
            <h2>Verificaciones Diarias</h2>
            <a href="{{ route('verificaciones.index') }}" class="btn btn-secondary">Volver al Listado</a>
        </div>

        <form method="GET" action="{{ route('verificaciones.filtrar') }}" class="mb-4">
            @csrf
            <div class="form-row">
                <div class="col-md-5">
                    <label for="fecha_inicio">Fecha de Inicio:</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" required>
                </div>
                <div class="col-md-5">
                    <label for="fecha_fin">Fecha de Fin:</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </div>
            </div>
        </form>

        <div class="table-container">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th rowspan="2" class="text-center">Campo/Día</th>
                        @foreach ($tablaContingencia as $fecha => $tipos)
                            <th colspan="3" class="text-center">{{ $fecha }}</th> <!-- Cambiar a colspan="3" -->
                        @endforeach
                    </tr>
                    <tr>
                        @foreach ($tablaContingencia as $fecha => $tipos)
                            <th class="text-center">Apertura</th>
                            <th class="text-center">Relevo</th> <!-- Nueva columna para Relevo -->
                            <th class="text-center">Cierre</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($campos as $campo)
                        <tr>
                            <td>{{ $campo }}</td>
                            @foreach ($tablaContingencia as $fecha => $tipos)
                                <td class="text-center">{{ $tipos['Apertura'][$campo] ?? 'N/A' }}</td>
                                <td class="text-center">{{ $tipos['Relevo'][$campo] ?? 'N/A' }}</td> <!-- Nueva celda para Relevo -->
                                <td class="text-center">{{ $tipos['Cierre'][$campo] ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
