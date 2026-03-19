@extends('layouts.layout')

@section('style')
    <link rel="stylesheet" href="{{ asset('Vendor/toastr/toastr.min.css') }}">
    <style>
        .card-header {
            background: #132842;
            color: white;
        }

        .estado-label {
            font-weight: bold;
            font-size: 13px;
        }
    </style>
@endsection

@section('main')
    <div class="container-fluid">

        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <div class="row">
                        <div class="col">
                            <h5 class="fw-bold text-uppercase">
                                CHECKLIST DIARIO - {{ $nombreMac }}
                            </h5>

                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('inicio') }}">
                                        <i class="fa fa-home"></i>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('verificaciones.index') }}">Verificaciones</a>
                                </li>
                                <li class="breadcrumb-item active">
                                    Checklist Diario
                                </li>
                            </ol>

                        </div>

                        <!-- 🔙 BOTÓN A LA DERECHA -->
                        <div class="col-auto align-self-center">
                            <a href="{{ route('verificaciones.index') }}" class="btn btn-dark">
                                <i class="fa fa-arrow-left"></i> Volver
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">

            <!-- 🔵 HEADER ESTILO ANS -->
            <div class="card-header" style="background-color:#132842">
                <h4 class="card-title text-white mb-0">
                    Registro de Verificación
                </h4>
            </div>

            <div class="card-body">

                <form id="formEditar" action="{{ route('verificaciones.update', $verificacion->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- 🔹 CABECERA -->
                    <div class="row align-items-end mb-4">

                        <div class="col-md-6">
                            <label class="fw-bold text-dark mb-2">Tipo:</label>
                            <select disabled class="form-control">
                                <option {{ $verificacion->AperturaCierre == 0 ? 'selected' : '' }}>Apertura</option>
                                <option {{ $verificacion->AperturaCierre == 1 ? 'selected' : '' }}>Relevo</option>
                                <option {{ $verificacion->AperturaCierre == 2 ? 'selected' : '' }}>Cierre</option>
                            </select>
                            <input type="hidden" name="AperturaCierre" value="{{ $verificacion->AperturaCierre }}">
                        </div>

                        <div class="col-md-6">
                            <label class="fw-bold text-dark mb-2">Fecha:</label>
                            <input type="date" class="form-control"
                                value="{{ \Carbon\Carbon::parse($verificacion->Fecha)->format('Y-m-d') }}" readonly>
                        </div>

                    </div>
                    <!--TABLA -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle">

                            <thead style="background:#233e99; color:white;" class="text-center">
                                <tr>
                                    <th>Pregunta</th>
                                    <th width="200">Respuesta</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>

                            <tbody>

                                @php
                                    $campos = [
                                        'ModuloDeRecepcion' => 'Modulo de Recepción',
                                        'OrdenadoresDeFila' => 'Ordenadores de Fila',
                                        'SillasDeOrientadores' => 'Sillas de Orientadores',
                                        'Ticketera' => 'Ticketera',
                                        'LectorDeCodBarras' => 'Lector de Código de Barras',
                                        'ServicioDeTelefonia1800' => 'Telefonía 1800',
                                        'InsumoRecepcion' => 'Insumos Recepción',
                                        'SillaRuedas' => 'Silla de Ruedas',
                                        'TvZonaAtencion' => 'TV Zona Atención',
                                        'SillasEspera' => 'Sillas de Espera',
                                        'SillaAsesor' => 'Silla de Asesor',
                                        'SillasAtencion' => 'Sillas de Atención',
                                        'ModuloAtencion' => 'Módulo de Atención',
                                        'PcAsesores' => 'PC Asesores',
                                        'ImpresorasZonaAtencion' => 'Impresoras',
                                        'InsumoMateriales' => 'Insumos Materiales',
                                        'ModuloOficina' => 'Módulo Oficina',
                                        'SillaOficina' => 'Silla Oficina',
                                        'InsumoOficina' => 'Insumo Oficina',
                                        'SistemaIluminaria' => 'Iluminación',
                                        'OrdenLimpieza' => 'Orden y Limpieza',
                                        'Senialeticas' => 'Señaléticas',
                                        'EquipoAireAcondicionado' => 'Aire Acondicionado',
                                        'ServiciosHigienicos' => 'Servicios Higiénicos',
                                        'Comedor' => 'Comedor',
                                        'Internet' => 'Internet',
                                        'SistemasColas' => 'Sistema de Colas',
                                        'SistemaDeCitas' => 'Sistema de Citas',
                                        'SistemaAudio' => 'Sistema Audio',
                                        'SistemaVideovigilancia' => 'Videovigilancia',
                                        'CorreoElectronico' => 'Correo',
                                        'ActiveDirectory' => 'Active Directory',
                                        'FileServer' => 'File Server',
                                        'Antivirus' => 'Antivirus',
                                    ];
                                @endphp

                                @foreach ($campos as $name => $label)
                                    <tr>

                                        <td>{{ $label }}</td>

                                        <!-- 🔥 SWITCH PRO -->
                                        <td class="text-center">

                                            <input type="hidden" name="{{ $name }}" value="0">

                                            <div class="d-flex justify-content-center align-items-center gap-2">

                                                <span
                                                    class="estado-label {{ $verificacion->$name ? 'text-success' : 'text-danger' }}">
                                                    {{ $verificacion->$name ? 'SI' : 'NO' }}
                                                </span>

                                                <div class="form-check form-switch m-0">
                                                    <input class="form-check-input switch-si-no" type="checkbox"
                                                        name="{{ $name }}" value="1"
                                                        {{ $verificacion->$name ? 'checked' : '' }}>
                                                </div>

                                            </div>

                                        </td>

                                        <td>
                                            <input type="text" class="form-control"
                                                name="observaciones_{{ $name }}"
                                                value="{{ $observaciones[$name] ?? '' }}" placeholder="Observación...">
                                        </td>

                                    </tr>
                                @endforeach

                            </tbody>

                        </table>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa fa-save"></i> Actualizar
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        /* 🔥 SWITCH DINÁMICO */
        document.querySelectorAll('.switch-si-no').forEach(sw => {

            sw.addEventListener('change', function() {

                let label = this.closest('td').querySelector('.estado-label');

                if (this.checked) {
                    label.innerText = 'SI';
                    label.classList.remove('text-danger');
                    label.classList.add('text-success');
                } else {
                    label.innerText = 'NO';
                    label.classList.remove('text-success');
                    label.classList.add('text-danger');
                }

            });

        });

        /* 🔥 CONFIRMACIÓN UPDATE */
        document.getElementById('formEditar').addEventListener('submit', function(e) {

            e.preventDefault();

            Swal.fire({
                title: '¿Actualizar verificación?',
                text: 'Se guardarán los cambios realizados',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, actualizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });

        });
    </script>
@endsection
