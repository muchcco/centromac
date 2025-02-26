<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Cambiar MAC para - {{ $personal->NOMBRE }}  {{ $personal->APE_PAT }} {{ $personal->APE_MAT }}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="alerta">
            </div> 
            <h5>Seleccionar centro MAC (tabla maestra de personal donde se origina el registro)</h5>
            <form class="form-horizontal">
                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />
                <div class="alert custom-alert custom-alert-warning icon-custom-alert shadow-sm fade show d-flex justify-content-between" role="alert">  
                    <div class="media">
                        <i class="la la-exclamation-triangle alert-icon text-warning align-self-center font-30 me-3"></i>
                        <div class="media-body align-self-center">
                            <h5 class="mb-1 fw-bold mt-0">Importante</h5>
                            <span>Los cambios para esta sección es solamente si el asesor se encuentra en otro centro mac y ya no desea cambiar de entidad por el formulario.</span>
                        </div>
                    </div>                                  
                    {{-- <button type="button" class="btn-close align-self-center" data-bs-dismiss="alert" aria-label="Close"></button> --}}
                </div>
                <div class="row mb-3">
                    {{-- <label  class="col-3 col-form-label">Mac donde se registro</label> --}}
                    <div class="col-12">
                        <select id="mac" name="mac" class="form-select" onchange="btnUpdateMac('{{ $personal->IDPERSONAL }}')">
                            <option value="" disabled selected>-- SELECCIONE UNA OPCION --</option>
                            @forelse ($centromac as $mac)
                                <option value="{{ $mac->IDCENTRO_MAC }}" {{ $personal->IDMAC == $mac->IDCENTRO_MAC ? 'selected' : '' }}>{{ $mac->NOMBRE_MAC }}</option>
                            @empty
                                <option value="">No hay datos disponibles</option>
                            @endforelse
                        </select>                                             
                    </div>
                </div>
                <div class="row mb-3">
                    <h5 >Mac donde tiene vinculo (tabla detalle centro mac para el personal)</h5>
                    <div class="col-12" id="detalle_mac_container">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>N°</th>
                                    <th>MAC</th>
                                    <th>Eliminar</th>
                                </tr>                                
                            </thead>
                            <tbody>
                                @forelse ($detalle_mac as $i => $q )
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $q->NOMBRE_MAC }}</td>
                                        <td>
                                            <button type="button" class="nobtn bandejTool" data-tippy-content="Dar de baja"
                                                    onclick="btnElimnarMacE(this, '{{ $q->id }}')">
                                                <i class="las la-trash-alt text-secondary font-16 text-danger"></i>
                                            </button>

                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">No hay datos disponibles</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            {{-- <button type="button" class="btn btn-outline-success" id="btnEnviarForm" onclick="">Guardar</button> --}}
        </div>
    </div>
</div>

<script>


function btnUpdateMac(idpersonal){

    var tipo = $("#mac").val();
    console.log(tipo)

    if (tipo === ""){ 
        $('#mac').addClass("hasError");
    }
    else {
        var formData = new FormData();
        formData.append("mac", $("#mac").val());
        formData.append('idpersonal', idpersonal);
        formData.append("_token", $("input[name=_token]").val());

        $.ajax({
            type:'post',
            url: "{{ route('personal.update_mac') }}",
            dataType: "json",
            data:formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                
            },
            success:function(data){                
                tabla_seccion();
                Toastify({
                    text: "Registro cambio de centro mac original",
                    className: "info",
                    gravity: "bottom",
                    style: { background: "#47B257" }
                }).showToast();
            }
        });
    }

}

function btnElimnarMacE(element, id) {
    $.ajax({
        type: 'POST',
        url: "{{ route('personal.delete_mac_mod') }}",
        dataType: "json",
        data: {
            id: id,
            _token: $("input[name=_token]").val()
        },
        beforeSend: function () {
            // Opcional: mostrar spinner o deshabilitar el botón
        },
        success: function(response) {
            console.log(response)
            if (response.success) {
                // Remover la fila correspondiente al botón clickeado
                $(element).closest('tr').remove();
                tabla_seccion();
                Toastify({
                    text: "Registro eliminado correctamente",
                    className: "info",
                    gravity: "bottom",
                    style: { background: "#47B257" }
                }).showToast();
            } else {
                Swal.fire({
                    icon: "error",
                    text: response.message || "No se pudo eliminar el registro",
                    confirmButtonText: "Aceptar"
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: "error",
                text: "Hubo un error al procesar la eliminación.",
                confirmButtonText: "Aceptar"
            });
        }
    });
}


</script>