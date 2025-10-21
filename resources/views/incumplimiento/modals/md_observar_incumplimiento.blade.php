<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header" style="background-color:#132842; color:white;">
            <h4 class="modal-title">
                <i class="fa fa-exclamation-triangle me-1"></i> Observación de Incidente Operativo
            </h4>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
            <form id="form_observar_incumplimiento">
                @csrf
                <input type="hidden" name="id_observacion" value="{{ $incumplimiento->id_observacion }}">

                {{-- 🔹 OBSERVACIÓN (solo Admin o Moderador) --}}
                @php
                    $puedeObservar = auth()
                        ->user()
                        ->hasAnyRole(['Administrador', 'Moderador']);
                    $puedeCorregir = auth()
                        ->user()
                        ->hasAnyRole(['Supervisor', 'Especialista TIC', 'Moderador', 'Administrador']);
                @endphp

                <div class="mb-3">
                    <label class="form-label fw-bold">¿Observado?</label>
                    <div>
                        <input type="hidden" name="observado" value="0">
                        <input type="checkbox" name="observado" id="chk_observado" value="1"
                            {{ $incumplimiento->observado ? 'checked' : '' }} {{ $puedeObservar ? '' : 'disabled' }}>
                        <span>Marcar como observado</span>
                    </div>
                </div>

                {{-- 🔹 RETROALIMENTACIÓN (solo Admin o Moderador) --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Retroalimentación</label>
                    <textarea class="form-control" name="retroalimentacion" rows="4"
                        placeholder="Ingrese aquí la retroalimentación o detalle de la observación..."
                        {{ $puedeObservar ? '' : 'readonly' }}>{{ $incumplimiento->retroalimentacion }}</textarea>
                </div>

                {{-- 🔹 CORRECCIÓN (solo visible si está observado y el rol puede corregir) --}}
                @if ($incumplimiento->observado && $puedeCorregir)
                    <div class="mb-3">
                        <label class="form-label fw-bold">¿Ya fue corregido?</label>
                        <div>
                            <input type="hidden" name="corregido" value="0">
                            <input type="checkbox" name="corregido" id="chk_corregido" value="1"
                                {{ $incumplimiento->corregido ? 'checked' : '' }}
                                {{ $puedeCorregir ? '' : 'disabled' }}>
                            <span>Marcar como corregido</span>
                        </div>
                    </div>
                @endif

                {{-- 🕓 INFORMACIÓN HISTÓRICA --}}
                <div class="small text-muted mt-3">
                    @if ($incumplimiento->observado_por)
                        <strong>Observado por:</strong> {{ $incumplimiento->usuarioObservador->name ?? 'N/A' }} <br>
                        <strong>Fecha de observación:</strong>
                        {{ \Carbon\Carbon::parse($incumplimiento->fecha_observado)->format('d/m/Y H:i') }} <br>
                    @endif

                    @if ($incumplimiento->corregido_por)
                        <strong>Corregido por:</strong>
                        {{ \App\Models\User::find($incumplimiento->corregido_por)->name ?? 'N/A' }} <br>
                        <strong>Fecha de corrección:</strong>
                        {{ \Carbon\Carbon::parse($incumplimiento->fecha_corregido)->format('d/m/Y H:i') }}
                    @endif
                </div>
            </form>
        </div>

        <div class="modal-footer">
            <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>

            {{--  Solo roles autorizados pueden guardar --}}
            @if ($puedeObservar || $puedeCorregir)
                <button type="button" class="btn btn-outline-success" id="btnGuardarObservacion"
                    onclick="guardarObservacion()">
                    <i class="fa fa-save me-1"></i> Guardar
                </button>
            @endif
        </div>
    </div>
</div>
