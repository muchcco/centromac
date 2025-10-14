<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Detalle de Incidente</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
            <ul class="list-group">
                @if ($incumplimiento->centroMac)
                    <li class="list-group-item">
                        <strong>Centro MAC:</strong> {{ $incumplimiento->centroMac->nombre_mac }}
                    </li>
                @endif

                @if ($incumplimiento->tipoIntObs)
                    <li class="list-group-item">
                        <strong>Tipificación:</strong>
                        {{ $incumplimiento->tipoIntObs->tipo }}
                        {{ $incumplimiento->tipoIntObs->numeracion }}
                        - {{ $incumplimiento->tipoIntObs->nom_tipo_int_obs }}
                    </li>
                @endif

                @if ($incumplimiento->entidad)
                    <li class="list-group-item">
                        <strong>Entidad:</strong> {{ $incumplimiento->entidad->NOMBRE_ENTIDAD }}
                    </li>
                @endif

                @if ($incumplimiento->descripcion)
                    <li class="list-group-item">
                        <strong>Descripción:</strong> {{ $incumplimiento->descripcion }}
                    </li>
                @endif

                @if ($incumplimiento->descripcion_accion)
                    <li class="list-group-item">
                        <strong>Acción Tomada:</strong> {{ $incumplimiento->descripcion_accion }}
                    </li>
                @endif

                <li class="list-group-item">
                    <strong>Fecha Incumplimiento:</strong>
                    {{ \Carbon\Carbon::parse($incumplimiento->fecha_observacion)->format('d/m/Y') }}
                </li>

                <li class="list-group-item">
                    <strong>Estado:</strong>
                    @if ($incumplimiento->estado === 'ABIERTO')
                        <span class="badge bg-danger">ABIERTO</span>
                    @else
                        <span class="badge bg-success">CERRADO</span>
                    @endif
                </li>

                @if ($incumplimiento->fecha_solucion)
                    <li class="list-group-item">

                        <strong>Fecha Cierre:</strong>
                        {{ \Carbon\Carbon::parse($incumplimiento->fecha_solucion)->format('d/m/Y') }}

                    </li>
                @endif
                @if ($incumplimiento->responsableUsuario)
                    <li class="list-group-item">
                        <strong>Responsable:</strong> {{ strtoupper($incumplimiento->responsableUsuario->name) }}
                    </li>
                @endif

                @if (auth()->user()->hasRole(['Administrador', 'Monitor','Moderador']))
                    @if ($incumplimiento->created_at)
                        <li class="list-group-item">
                            <strong>Fecha de Registro:</strong>
                            {{ \Carbon\Carbon::parse($incumplimiento->created_at)->format('d/m/Y H:i:s') }}
                        </li>
                    @endif
                @endif
                @if ($incumplimiento->archivo)
                    <li class="list-group-item">
                        <strong>Archivo Adjunto:</strong>
                        <a class="text-uppercase" href="{{ asset($incumplimiento->archivo) }}" target="_blank">
                            Ver archivo
                        </a>
                    </li>
                @endif
            </ul>
        </div>

        <div class="modal-footer">
            <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
    </div>
</div>
