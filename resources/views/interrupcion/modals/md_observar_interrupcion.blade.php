<div class="modal-dialog" role="document">
    <div class="modal-content border-0 shadow-lg">
        <!-- 🔹 CABECERA -->
        <div class="modal-header" style="background-color:#132842; color:white;">
            <h4 class="modal-title">
                <i class="fa fa-exclamation-triangle me-2"></i> Observación de Interrupción
            </h4>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <!-- 🔹 CUERPO -->
        <div class="modal-body">
            <form id="form_observar_interrupcion">
                @csrf
                <input type="hidden" name="id_interrupcion" value="{{ $interrupcion->id_interrupcion }}">

                {{-- 🔹 Solo Admin/Moderador pueden observar --}}
                <div class="mb-3">
                    <label class="form-label fw-bold text-dark">¿Observado?</label>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="observado" id="chk_observado"
                            {{ $interrupcion->observado ? 'checked' : '' }}
                            {{ auth()->user()->hasAnyRole(['Administrador', 'Moderador'])? '': 'disabled' }}>
                        <label for="chk_observado" class="form-check-label">Marcar como observado</label>
                    </div>
                </div>

                {{-- 🔹 Retroalimentación visible para todos, editable solo por Admin/Moderador --}}
                <div class="mb-3">
                    <label class="form-label fw-bold text-dark">Retroalimentación</label>
                    <textarea class="form-control border-secondary shadow-sm" name="retroalimentacion" rows="4"
                        placeholder="Ingrese aquí la retroalimentación o detalle de la observación..."
                        {{ auth()->user()->hasAnyRole(['Administrador', 'Moderador'])? '': 'readonly' }}>{{ trim($interrupcion->retroalimentacion) }}</textarea>
                </div>

                {{-- 🟩 Solo aparece si fue observado y el usuario puede corregir --}}
                @if ($interrupcion->observado && $puedeCorregir)
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">¿Ya fue corregido?</label>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="corregido" id="chk_corregido"
                                {{ $interrupcion->corregido ? 'checked' : '' }}
                                {{ auth()->user()->hasAnyRole(['Supervisor', 'Especialista TIC', 'Moderador', 'Administrador'])? '': 'disabled' }}>
                            <label for="chk_corregido" class="form-check-label">Marcar como corregido</label>
                        </div>
                    </div>
                @endif

                {{-- 🕓 Datos de quién observó / corrigió --}}
                <div class="small text-muted border-top pt-3 mt-4">
                    @if ($interrupcion->observado_por)
                        <strong>Observado por:</strong> {{ $interrupcion->usuarioObservador->name ?? 'N/A' }} <br>
                        <strong>Fecha de observación:</strong>
                        {{ \Carbon\Carbon::parse($interrupcion->fecha_observado)->format('d/m/Y H:i') }} <br>
                    @endif

                    @if ($interrupcion->corregido_por)
                        <strong>Corregido por:</strong>
                        {{ \App\Models\User::find($interrupcion->corregido_por)->name ?? 'N/A' }} <br>
                        <strong>Fecha de corrección:</strong>
                        {{ \Carbon\Carbon::parse($interrupcion->fecha_corregido)->format('d/m/Y H:i') }}
                    @endif
                </div>
            </form>
        </div>

        <!-- 🔹 PIE -->
        <div class="modal-footer bg-white border-top">
            <button class="btn btn-outline-secondary" data-bs-dismiss="modal">
                <i class="fa fa-times me-1"></i> Cerrar
            </button>
            <button class="btn btn-outline-success" id="btnGuardarObservacion" onclick="guardarObservacion()">
                <i class="fa fa-save me-1"></i> Guardar
            </button>
        </div>
    </div>
</div>
