<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Seleccionar los perfiles a mostrar</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            
            <h5>Verificar los perfiles</h5>
            <form action="" class="form">
                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />
                <input type="hidden" name="idmac" id="idmac" value="{{ $idmac }}" />
                <div class="form-group">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Perfil</th>
                                <th>Seleccionar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($perfiles as $i => $q)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $q->nome }}</td>
                                    <td>
                                        <input
                                          type="checkbox"
                                          name="perfiles[]"
                                          class="perfil-checkbox"
                                          checked="true"
                                          value="{{ $q->id }}"
                                        >
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">No hay datos</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-outline-success" onclick="btnStoreUsuario()" id="btnEnviarForm">Crear</button>
        </div>
    </div>
</div>
<script>