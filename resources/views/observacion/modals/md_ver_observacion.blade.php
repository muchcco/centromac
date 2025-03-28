<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Detalle de Observación</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

            <ul class="list-group">
                @if ($observacion->centroMac)
                    <li class="list-group-item"><strong>Centro MAC:</strong> {{ $observacion->centroMac->nombre_mac }}
                    </li>
                @endif

                @if ($observacion->tipoIntObs)
                    <li class="list-group-item"><strong>Tipificación:</strong> {{ $observacion->tipoIntObs->tipo }}
                        {{ $observacion->tipoIntObs->numeracion }} - {{ $observacion->tipoIntObs->nom_tipo_int_obs }}
                    </li>
                @endif

                @if ($observacion->entidad)
                    <li class="list-group-item"><strong>Entidad:</strong> {{ $observacion->entidad->nombre_entidad }}
                    </li>
                @endif

                @if ($observacion->servicio_involucrado)
                    <li class="list-group-item"><strong>Servicio Involucrado:</strong>
                        {{ $observacion->servicio_involucrado }}</li>
                @endif

                @if ($observacion->descripcion)
                    <li class="list-group-item"><strong>Descripción:</strong> {{ $observacion->descripcion }}</li>
                @endif

                @if ($observacion->descripcion_accion)
                    <li class="list-group-item"><strong>Acción Tomada:</strong> {{ $observacion->descripcion_accion }}
                    </li>
                @endif

                <li class="list-group-item"><strong>Fecha Observación:</strong> {{ $observacion->fecha_observacion }}
                </li>

                @if ($observacion->estado != 'NO SUBSANADO')
                    <li class="list-group-item"><strong>Estado Final:</strong> {{ $observacion->estado }}</li>
                @endif

                @if ($observacion->accion_correctiva)
                    <li class="list-group-item"><strong>Acción Correctiva:</strong>
                        {{ $observacion->accion_correctiva }}</li>
                @endif

                @if ($observacion->fecha_solucion)
                    <li class="list-group-item"><strong>Fecha Solución:</strong> {{ $observacion->fecha_solucion }}
                    </li>
                @endif
                @if ($observacion->archivo)
                    <li class="list-group-item"><strong>Archivo Adjunto:</strong><a class="text-uppercase"
                            href="{{ asset($observacion->archivo) }}" target="_blank">Ver archivo</a></li>
                @endif

            </ul>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
    </div>
</div>
