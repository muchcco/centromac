<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header" style="background-color:#1c2a48; color:white;">
            <h4 class="modal-title">Detalle de Interrupción</h4>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
            <ul class="list-group">
                {{-- Centro MAC --}}
                @if ($interrupcion->centroMac)
                    <li class="list-group-item">
                        <strong>Centro MAC:</strong> {{ $interrupcion->centroMac->nombre_mac }}
                    </li>
                @endif

                {{-- Tipificación --}}
                @if ($interrupcion->tipoIntObs)
                    <li class="list-group-item">
                        <strong>Tipificación:</strong>
                        {{ $interrupcion->tipoIntObs->tipo }}
                        {{ $interrupcion->tipoIntObs->numeracion }}
                        - {{ $interrupcion->tipoIntObs->nom_tipo_int_obs }}
                    </li>
                @endif

                {{-- Entidad --}}
                @if ($interrupcion->entidad)
                    <li class="list-group-item">
                        <strong>Entidad:</strong>
                        {{ $interrupcion->entidad->ABREV_ENTIDAD ?? '' }} -
                        {{ $interrupcion->entidad->NOMBRE_ENTIDAD ?? '' }}
                    </li>
                @endif

                {{-- Servicio involucrado --}}
                @if ($interrupcion->servicio_involucrado)
                    <li class="list-group-item">
                        <strong>Servicio Involucrado:</strong>
                        {{ $interrupcion->servicio_involucrado }}
                    </li>
                @endif

                {{-- Descripción --}}
                @if ($interrupcion->descripcion)
                    <li class="list-group-item">
                        <strong>Descripción:</strong> {{ $interrupcion->descripcion }}
                    </li>
                @endif

                {{-- Acción tomada --}}
                @if ($interrupcion->descripcion_accion)
                    <li class="list-group-item">
                        <strong>Acción Tomada:</strong> {{ $interrupcion->descripcion_accion }}
                    </li>
                @endif

                {{-- Fecha y hora de inicio --}}
                <li class="list-group-item">
                    <strong>Fecha Inicio:</strong>
                    {{ \Carbon\Carbon::parse($interrupcion->fecha_inicio)->format('d/m/Y') }}
                    &nbsp;&nbsp; <strong>Hora Inicio:</strong> {{ $interrupcion->hora_inicio }}
                </li>

                {{-- Fecha y hora de fin --}}
                @if ($interrupcion->fecha_fin)
                    <li class="list-group-item">
                        <strong>Fecha Fin:</strong>
                        {{ \Carbon\Carbon::parse($interrupcion->fecha_fin)->format('d/m/Y') }}
                        &nbsp;&nbsp; <strong>Hora Fin:</strong> {{ $interrupcion->hora_fin }}
                    </li>
                @endif

                {{-- Estado --}}
                <li class="list-group-item">
                    <strong>Estado:</strong>
                    @if ($interrupcion->estado === 'ABIERTO')
                        <span class="badge bg-danger">ABIERTO</span>
                    @else
                        <span class="badge bg-success">CERRADO</span>
                    @endif
                </li>

                {{-- Responsable --}}
                @if ($interrupcion->responsableUsuario)
                    <li class="list-group-item">
                        <strong>Responsable:</strong>
                        {{ strtoupper($interrupcion->responsableUsuario->name) }}
                    </li>
                @endif

                {{-- Fecha registro (solo para admins, monitor, moderador) --}}
                @if (auth()->user()->hasRole(['Administrador', 'Monitor', 'Moderador']))
                    @if ($interrupcion->created_at)
                        <li class="list-group-item">
                            <strong>Fecha de Registro:</strong>
                            {{ \Carbon\Carbon::parse($interrupcion->created_at)->format('d/m/Y H:i:s') }}
                        </li>
                    @endif
                @endif
            </ul>
        </div>

        <div class="modal-footer">
            <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
    </div>
</div>
