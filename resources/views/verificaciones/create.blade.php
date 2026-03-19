@extends('layouts.layout')

@section('style')
    <link href="{{ asset('nuevo/plugins/select2/select2.min.css') }}" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    <style>
        .card {
            border-radius: 10px
        }

        .card-header {
            background: #132842;
            color: #fff
        }

        .switch-label {
            font-size: 12px;
            font-weight: 600
        }
    </style>
@endsection

@section('main')
    <div class="container-fluid">
        <h5 class="fw-bold text-uppercase">
            CHECKLIST DIARIO - {{ $nombreMac }}
        </h5>
        <small class="text-50">Módulo de Verificaciones</small>

        <div class="d-flex justify-content-end mb-2">
            <a href="{{ route('verificaciones.index') }}" class="btn btn-dark">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-header" style="background-color:#132842">
                <h4 class="card-title text-white mb-0">
                    Registro de Verificación
                </h4>
            </div>
            <div class="card-body">
                <form id="formChecklist" action="{{ route('verificaciones.store') }}" method="POST">
                    @csrf

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="fw-semibold mb-2">Tipo</label>
                            <select class="form-control" name="AperturaCierre" required>
                                <option value="">Seleccione</option>
                                <option value="0" {{ old('AperturaCierre') == 0 ? 'selected' : '' }}>Apertura</option>
                                <option value="1" {{ old('AperturaCierre') == 1 ? 'selected' : '' }}>Relevo</option>
                                <option value="2" {{ old('AperturaCierre') == 2 ? 'selected' : '' }}>Cierre</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-semibold mb-2">Fecha</label>
                            <input type="date" name="Fecha" id="fecha" class="form-control"
                                value="{{ old('Fecha', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle">
                            <thead style="background:#233e99;color:#fff" class="text-center">
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
                                        <td class="text-center">
                                            <input type="hidden" name="{{ $name }}" value="0">
                                            <div class="d-flex align-items-center justify-content-center gap-2">
                                                <span class="fw-bold text-success estado-label">SI</span>
                                                <div class="form-check form-switch m-0">
                                                    <input class="form-check-input switch-si-no" type="checkbox"
                                                        name="{{ $name }}" value="1" checked>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control"
                                                name="observaciones_{{ $name }}" placeholder="Observación...">
                                        </td>
                                    </tr>
                                @endforeach

                                <tr class="table-light">
                                    <td><strong>Observaciones Generales</strong></td>
                                    <td></td>
                                    <td><input type="text" class="form-control" name="Observaciones"></td>
                                </tr>

                            </tbody>
                        </table>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa fa-save"></i> Guardar
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // 🔥 CAMBIO SI / NO
        document.querySelectorAll('.switch-si-no').forEach(sw => {
            sw.addEventListener('change', function() {
                let label = this.closest('td').querySelector('.estado-label');
                if (this.checked) {
                    label.innerText = 'SI';
                    label.classList.replace('text-danger', 'text-success');
                } else {
                    label.innerText = 'NO';
                    label.classList.replace('text-success', 'text-danger');
                }
            });
        });

        // 🔥 VALIDAR FECHA
        document.getElementById('fecha').addEventListener('change', function() {
            let hoy = new Date().toISOString().split('T')[0];
            if (this.value > hoy) {
                Swal.fire('Fecha inválida', 'No puede seleccionar una fecha futura', 'warning');
                this.value = hoy;
            }
        });

        // 🔥 VALIDAR OBSERVACIONES
        document.getElementById('formChecklist').addEventListener('submit', function(e) {
            e.preventDefault();

            let errores = [];

            document.querySelectorAll('.switch-si-no').forEach(sw => {
                if (!sw.checked) {
                    let obs = document.querySelector('[name="observaciones_' + sw.name + '"]');
                    if (!obs.value.trim()) errores.push(sw.name);
                }
            });

            if (errores.length) {
                Swal.fire('Faltan observaciones', 'Debe completar observación en los ítems con NO', 'warning');
                return;
            }

            Swal.fire({
                title: '¿Guardar verificación?',
                text: 'Confirma el registro del checklist',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, guardar'
            }).then(res => {
                if (res.isConfirmed) e.target.submit();
            });
        });

        // 🔥 ERROR BACKEND (CLAVE)
        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: `{!! implode('<br>', $errors->all()) !!}`
            });
        @endif

        // 🔥 SUCCESS
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Correcto',
                text: '{{ session('success') }}'
            });
        @endif
    </script>
@endsection
