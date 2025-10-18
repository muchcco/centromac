<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header bg-primary text-white">
            <h4 class="modal-title">
                Observación de Interrupción
            </h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
            <form id="form_observar_interrupcion">
                @csrf
                <input type="hidden" name="id_interrupcion" value="{{ $interrupcion->id_interrupcion }}">

                {{-- 🔹 Solo Admin/Moderador pueden observar --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">¿Observado?</label>
                    <div>
                        <input type="checkbox" name="observado" id="chk_observado"
                            {{ $interrupcion->observado ? 'checked' : '' }}
                            {{ auth()->user()->hasAnyRole(['Administrador', 'Moderador'])? '': 'disabled' }}>
                        <span>Marcar como observado</span>
                    </div>
                </div>

                {{-- 🔹 Retroalimentación visible para todos, editable solo por Admin/Moderador --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Retroalimentación</label>
                    <textarea class="form-control" name="retroalimentacion" rows="4"
                        {{ auth()->user()->hasAnyRole(['Administrador', 'Moderador'])? '': 'readonly' }}>
        {{ $interrupcion->retroalimentacion }}
    </textarea>
                </div>

                {{-- 🟩 Solo aparece si fue observado y el usuario puede corregir --}}
                @if ($interrupcion->observado && $puedeCorregir)
                    <div class="mb-3">
                        <label class="form-label fw-bold">¿Ya fue corregido?</label>
                        <div>
                            <input type="checkbox" name="corregido" id="chk_corregido"
                                {{ $interrupcion->corregido ? 'checked' : '' }}
                                {{ auth()->user()->hasAnyRole(['Supervisor', 'Especialista TIC', 'Moderador', 'Administrador'])? '': 'disabled' }}>
                            <span>Marcar como corregido</span>
                        </div>
                    </div>
                @endif

                {{-- 🕓 Datos de quién observó / corrigió --}}
                <div class="small text-muted mt-3">
                    @if ($interrupcion->observado_por)
                        <strong>Observado por:</strong> {{ $interrupcion->usuarioObservador->name ?? 'N/A' }} <br>
                        <strong>Fecha de observación:</strong>
                        {{ \Carbon\Carbon::parse($interrupcion->fecha_observado)->format('d/m/Y H:i') }}
                        <br>
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

        <div class="modal-footer">
            <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button class="btn btn-outline-success" id="btnGuardarObservacion" onclick="guardarObservacion()">
                Guardar
            </button>
        </div>
    </div>
</div>
