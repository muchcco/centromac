<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Importar asistencia Callao (.mdb / .accdb)</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

            <form id="formCallao" class="form">
                <div class="alert alert-danger border-0" role="alert">
                    <strong>Importante!</strong> Primero asigne los permisos necesarios al archivo antes de subirlo.
                </div>
                <input type="hidden" name="_token" id="_callao_token" value="{{ csrf_token() }}" />

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            @php
                                $fecha_hoy       = \Carbon\Carbon::now()->format('Y-m-d');
                                $fecha6diasconvert = \Carbon\Carbon::now()->subDays(6)->format('Y-m-d');
                            @endphp
                            <label class="form-label" for="fecha_inicio">Fecha de inicio</label>
                            <input type="date" class="form-control" id="fecha_inicio" required value="{{ $fecha6diasconvert }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label" for="fecha_fin">Fecha de fin</label>
                            <input type="date" class="form-control" id="fecha_fin" required value="{{ $fecha_hoy }}">
                        </div>
                    </div>
                </div>

                <h5>
                    Archivo Access
                    <span class="bandejTool" data-tippy-content="Solo archivos .mdb o .accdb" target="_blank">
                        <i class="fa fa-database" aria-hidden="true"></i>
                    </span>
                </h5>
                <div class="form-group mb-3">
                    <input type="file" class="form-control" name="txt_file" id="txt_file" accept=".mdb,.accdb">
                </div>

                {{-- ── Área de progreso (oculta hasta que inicia la carga) ──────── --}}
                <div id="callaoProgressArea" class="d-none mt-3">

                    {{-- Fase 1: subida de chunks --}}
                    <div id="callaoPhaseUpload">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">
                                <i class="fa fa-upload me-1"></i>
                                <span id="callaoUploadLabel">Preparando subida...</span>
                            </small>
                            <small id="callaoUploadPct" class="fw-bold">0%</small>
                        </div>
                        <div class="progress mb-2" style="height:16px;">
                            <div id="callaoUploadBar"
                                 class="progress-bar progress-bar-striped progress-bar-animated bg-info"
                                 role="progressbar" style="width:0%"></div>
                        </div>
                    </div>

                    {{-- Fase 2: procesamiento del job --}}
                    <div id="callaoPhaseProcess" class="d-none">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">
                                <i class="fa fa-cog fa-spin me-1"></i>
                                <span id="callaoProcessLabel">Procesando archivo...</span>
                            </small>
                            <small id="callaoProcessPct" class="fw-bold">0%</small>
                        </div>
                        <div class="progress mb-2" style="height:16px;">
                            <div id="callaoProcessBar"
                                 class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                 role="progressbar" style="width:0%"></div>
                        </div>
                    </div>

                    <div id="callaoProgressMsg" class="small text-muted mt-1"></div>
                </div>
                {{-- ─────────────────────────────────────────────────────────────── --}}

            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" id="btnCallaoCancel" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" id="btnEnviarForm" onclick="btnStoreAccess()">
                <i class="fa fa-upload me-1"></i>Importar
            </button>
        </div>
    </div>
</div>

<script>
    tippy(".bandejTool", { allowHTML: true, followCursor: true });
</script>
