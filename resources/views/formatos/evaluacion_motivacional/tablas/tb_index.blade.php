<table class="table table-hover table-bordered table-striped" id="table_formato">
    <thead class="tenca">
        <tr>
            <th colspan="4"  class="thead-b">
                <img src="{{ asset('imagen/mac_logo_export.jpg') }}" alt="">
                {{-- <p>EVALUACION</p> --}}
            </th>
            <th class="bandejTool" data-tippy-content="Quien  propone soluciones inmediatas a problemas o controversias en la atención al ciudadano. Por ejemplo: el asesor que utiliza todas las herramientas posibles para concretar la atención al ciudadano (se cae el sistema, llama a su entidad para hacer el trámite por teléfono)">
                Proactividad
            </th>
            <th class="bandejTool" data-tippy-content="Se evaluará la empatía, carisma, amabilidad, comunicación asertiva con el ciudadano. Que el asesor salude y se despida del ciudadano de forma correcta, que se dirija a él con respeto.">Calidad de Servicio</th>
            <th class="bandejTool" data-tippy-content="El asesor que va más allá de lo que su contrato laboral establece. Por ejemplo: aquel asesor que se queda horas extras para culminar la atención a los ciudadanos. Que se ofrece a ayudar en tareas de atención al ciudadano o que no están necesariamente relacionadas con el trámite de su entidad. ">Compromiso</th>
            <th class="bandejTool" data-tippy-content="Al asesor que respeta las indicaciones sobre la vestimenta que realiza los coordinadores. Mantener la pulcritud y prolijidad">Vestimenta</th>
            <th colspan="2"  class="thead-b"></th>
        </tr>
        <tr>
            <th  width="50px">N°</th>
            <th >Entidad </th>
            <th >DNI </th>
            <th >Nombres y Apellidos</th>
            <th class="text-center">0 - 5</th>
            <th class="text-center">0 - 5</th>
            <th class="text-center">0 - 5</th>
            <th class="text-center">0 - 5</th>                      
            <th >Puntuación Total</th>
            <th >Accion</th>  
        </tr>
    </thead>
    <tbody>
        @forelse ($query as $i =>$que)
            <tr id="del-text">
                <td>{{ $i + 1 }}</td>
                <td>{{ $que->ABREV_ENTIDAD }}</td>
                <td>{{ $que->NUM_DOC }}</td>
                <td>{{ $que->NOMBREU }} {{ $que->PROACTIVIDAD  }} </td>
                <td class="text-center" >
                    {{-- <input type="button" value="{{ is_null($que->PROACTIVIDAD) ? 0 : $que->PROACTIVIDAD }}" data-fila="{{ $i }}" class="proactividad bandejTool {{ !is_null($que->PROACTIVIDAD) ? 'seleccionado' : '' }}" data-tippy-content="Malo"> --}}
                    <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" />

                    {{-- <input type="button" value="{{ is_null($que->PROACTIVIDAD) ? 0 : $que->PROACTIVIDAD }}" id="proactividad" name="{{ $que->IDPERSONAL }}" data-entidad="{{ $que->IDENTIDAD }}" data-fila="0_{{ $i }}" class="{{ !is_null($que->PROACTIVIDAD) ? 'seleccionado' : '' }} bandejTool" data-tippy-content="Malo"> --}}
                    <input type="button" value="{{ $que->PROACTIVIDAD == '1' ? '1' : '1' }}" id="proactividad" name="{{ $que->IDPERSONAL }}" data-entidad="{{ $que->IDENTIDAD }}" data-fila="1_{{ $i }}" class="{{ $que->PROACTIVIDAD == '1' ? 'seleccionado' : '' }} bandejTool" data-tippy-content="Deficiente">
                    <input type="button" value="{{ $que->PROACTIVIDAD == '2' ? '2' : '2' }}" id="proactividad" name="{{ $que->IDPERSONAL }}" data-entidad="{{ $que->IDENTIDAD }}" data-fila="2_{{ $i }}" class="{{ $que->PROACTIVIDAD == '2' ? 'seleccionado' : '' }} bandejTool" data-tippy-content="Regular">
                    <input type="button" value="{{ $que->PROACTIVIDAD == '3' ? '3' : '3' }}" id="proactividad" name="{{ $que->IDPERSONAL }}" data-entidad="{{ $que->IDENTIDAD }}" data-fila="3_{{ $i }}" class="{{ $que->PROACTIVIDAD == '3' ? 'seleccionado' : '' }} bandejTool" data-tippy-content="Bueno">
                    <input type="button" value="{{ $que->PROACTIVIDAD == '4' ? '4' : '4' }}" id="proactividad" name="{{ $que->IDPERSONAL }}" data-entidad="{{ $que->IDENTIDAD }}" data-fila="4_{{ $i }}" class="{{ $que->PROACTIVIDAD == '4' ? 'seleccionado' : '' }} bandejTool" data-tippy-content="Muy Bueno">
                    <input type="button" value="{{ $que->PROACTIVIDAD == '5' ? '5' : '5' }}" id="proactividad" name="{{ $que->IDPERSONAL }}" data-entidad="{{ $que->IDENTIDAD }}" data-fila="5_{{ $i }}" class="{{ $que->PROACTIVIDAD == '5' ? 'seleccionado' : '' }} bandejTool" data-tippy-content="Excelente">
                </td>
                <td class="text-center" >
                    {{-- <input type="button" value="{{ is_null($que->CALIDAD_SERVICIO) ? 0 : $que->CALIDAD_SERVICIO }}" id="calidad_servicio" name="{{ $que->IDPERSONAL }}" data-entidad="{{ $que->IDENTIDAD }}" data-fila="0_{{ $i }}" class="{{ !is_null($que->CALIDAD_SERVICIO) ? 'seleccionado' : '' }} bandejTool" data-tippy-content="Malo"> --}}
                    <input type="button" value="{{ $que->CALIDAD_SERVICIO == '1' ? '1' : '1' }}" id="calidad_servicio" name="{{ $que->IDPERSONAL }}" data-entidad="{{ $que->IDENTIDAD }}" data-fila="1_{{ $i }}" class="{{ $que->CALIDAD_SERVICIO == '1' ? 'seleccionado' : '' }} bandejTool" data-tippy-content="Deficiente">
                    <input type="button" value="{{ $que->CALIDAD_SERVICIO == '2' ? '2' : '2' }}" id="calidad_servicio" name="{{ $que->IDPERSONAL }}" data-entidad="{{ $que->IDENTIDAD }}" data-fila="2_{{ $i }}" class="{{ $que->CALIDAD_SERVICIO == '2' ? 'seleccionado' : '' }} bandejTool" data-tippy-content="Regular">
                    <input type="button" value="{{ $que->CALIDAD_SERVICIO == '3' ? '3' : '3' }}" id="calidad_servicio" name="{{ $que->IDPERSONAL }}" data-entidad="{{ $que->IDENTIDAD }}" data-fila="3_{{ $i }}" class="{{ $que->CALIDAD_SERVICIO == '3' ? 'seleccionado' : '' }} bandejTool" data-tippy-content="Bueno">
                    <input type="button" value="{{ $que->CALIDAD_SERVICIO == '4' ? '4' : '4' }}" id="calidad_servicio" name="{{ $que->IDPERSONAL }}" data-entidad="{{ $que->IDENTIDAD }}" data-fila="4_{{ $i }}" class="{{ $que->CALIDAD_SERVICIO == '4' ? 'seleccionado' : '' }} bandejTool" data-tippy-content="Muy Bueno">
                    <input type="button" value="{{ $que->CALIDAD_SERVICIO == '5' ? '5' : '5' }}" id="calidad_servicio" name="{{ $que->IDPERSONAL }}" data-entidad="{{ $que->IDENTIDAD }}" data-fila="5_{{ $i }}" class="{{ $que->CALIDAD_SERVICIO == '5' ? 'seleccionado' : '' }} bandejTool" data-tippy-content="Excelente">
                </td>              
                <td class="text-center" >
                    {{-- <input type="button" value="{{ is_null($que->COMPROMISO) ? 0 : $que->COMPROMISO }}" id="compromiso" name="{{ $que->IDPERSONAL }}" data-entidad="{{ $que->IDENTIDAD }}" data-fila="0_{{ $i }}" class="{{ !is_null($que->COMPROMISO) ? 'seleccionado' : '' }} bandejTool" data-tippy-content="Malo"> --}}
                    <input type="button" value="{{ $que->COMPROMISO == '1' ? '1' : '1' }}" id="compromiso" name="{{ $que->IDPERSONAL }}" data-entidad="{{ $que->IDENTIDAD }}" data-fila="1_{{ $i }}" class="{{ $que->COMPROMISO == '1' ? 'seleccionado' : ''}} bandejTool" data-tippy-content="Deficiente">
                    <input type="button" value="{{ $que->COMPROMISO == '2' ? '2' : '2' }}" id="compromiso" name="{{ $que->IDPERSONAL }}" data-entidad="{{ $que->IDENTIDAD }}" data-fila="2_{{ $i }}" class="{{ $que->COMPROMISO == '2' ? 'seleccionado' : ''}} bandejTool" data-tippy-content="Regular">
                    <input type="button" value="{{ $que->COMPROMISO == '3' ? '3' : '3' }}" id="compromiso" name="{{ $que->IDPERSONAL }}" data-entidad="{{ $que->IDENTIDAD }}" data-fila="3_{{ $i }}" class="{{ $que->COMPROMISO == '3' ? 'seleccionado' : ''}} bandejTool" data-tippy-content="Bueno">
                    <input type="button" value="{{ $que->COMPROMISO == '4' ? '4' : '4' }}" id="compromiso" name="{{ $que->IDPERSONAL }}" data-entidad="{{ $que->IDENTIDAD }}" data-fila="4_{{ $i }}" class="{{ $que->COMPROMISO == '4' ? 'seleccionado' : ''}} bandejTool" data-tippy-content="Muy Bueno">
                    <input type="button" value="{{ $que->COMPROMISO == '5' ? '5' : '5' }}" id="compromiso" name="{{ $que->IDPERSONAL }}" data-entidad="{{ $que->IDENTIDAD }}" data-fila="5_{{ $i }}" class="{{ $que->COMPROMISO == '5' ? 'seleccionado' : ''}} bandejTool" data-tippy-content="Excelente">

                </td>
                <td class="text-center" >
                    {{-- <input type="button" value="{{ is_null($que->VESTIMENTA) ? 0 : $que->VESTIMENTA }}" id="vestimenta" name="{{ $que->IDPERSONAL }}" data-entidad="{{ $que->IDENTIDAD }}" data-fila="0_{{ $i }}" class="{{ !is_null($que->VESTIMENTA) ? 'seleccionado' : '' }} bandejTool" data-tippy-content="Malo"> --}}
                    <input type="button" value="{{ $que->VESTIMENTA == '1' ? '1' : '1' }}" id="vestimenta" name="{{ $que->IDPERSONAL }}" data-entidad="{{ $que->IDENTIDAD }}" data-fila="1_{{ $i }}" class="{{ $que->VESTIMENTA == '1' ? 'seleccionado' : '' }} bandejTool" data-tippy-content="Deficiente">
                    <input type="button" value="{{ $que->VESTIMENTA == '2' ? '2' : '2' }}" id="vestimenta" name="{{ $que->IDPERSONAL }}" data-entidad="{{ $que->IDENTIDAD }}" data-fila="2_{{ $i }}" class="{{ $que->VESTIMENTA == '2' ? 'seleccionado' : '' }} bandejTool" data-tippy-content="Regular">
                    <input type="button" value="{{ $que->VESTIMENTA == '3' ? '3' : '3' }}" id="vestimenta" name="{{ $que->IDPERSONAL }}" data-entidad="{{ $que->IDENTIDAD }}" data-fila="3_{{ $i }}" class="{{ $que->VESTIMENTA == '3' ? 'seleccionado' : '' }} bandejTool" data-tippy-content="Bueno">
                    <input type="button" value="{{ $que->VESTIMENTA == '4' ? '4' : '4' }}" id="vestimenta" name="{{ $que->IDPERSONAL }}" data-entidad="{{ $que->IDENTIDAD }}" data-fila="4_{{ $i }}" class="{{ $que->VESTIMENTA == '4' ? 'seleccionado' : '' }} bandejTool" data-tippy-content="Muy Bueno">
                    <input type="button" value="{{ $que->VESTIMENTA == '5' ? '5' : '5' }}" id="vestimenta" name="{{ $que->IDPERSONAL }}" data-entidad="{{ $que->IDENTIDAD }}" data-fila="5_{{ $i }}" class="{{ $que->VESTIMENTA == '5' ? 'seleccionado' : '' }} bandejTool" data-tippy-content="Excelente">
                </td>
                <td style="max-width: 40px; aling-text:center" class="text-center">
                    <input type="text" class="text-cente nobtn total_puntos" id="total_puntos" name="total_puntos" disabled style="max-width: 40px; aling-text:center" value="{{ $que->TOTAL_P }}">
                </td>                
                <td>
                    <button class="btn-danger bandejTool" data-tippy-content="Elminar calificación para {{ $que->NOMBREU }}"  onclick="btnEliminarRegistro('{{ $que->IDEEVAL_MOTIVACIONAL }}')">Borrar</button>   
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="text-center text-danger">No hay asesores registrados para esta fecha</td>
            </tr>
        @endforelse
    </tbody>
</table>

<script>
$(document).ready(function() {

    tippy(".bandejTool", {
        allowHTML: true,
        followCursor: true,
    });

    CargaBton();

});

function CargaBton() {
    var grupos = document.querySelectorAll('.text-center');

    grupos.forEach(function (grupo) {
        var botones = grupo.querySelectorAll('input[type="button"]');

        botones.forEach(function (boton) {
            boton.addEventListener('click', function () {
                valorSeleccionado = parseInt(this.value);
                var filaCompleta = this.getAttribute('data-fila'); // Obtener el valor de data-fila
                var fila = filaCompleta.split('_')[0]; // Obtener el primer dígito de data-fila
                var nombre = this.id; // Obtener el ID del botón
                var nombreInput = this.name; // Obtener el valor del atributo name del botón
                var identidad = this.getAttribute('data-entidad'); // Obtener el valor del atributo name del botón

                botones.forEach(function (otroBoton) {
                    otroBoton.disabled = false;
                    otroBoton.classList.remove('seleccionado');
                });

                console.log("ID: " + fila + ", Nombre: " + nombre + ", Name: " + nombreInput + ", Número seleccionado: " + valorSeleccionado);

                this.disabled = true;
                this.classList.add('seleccionado');

                sumarTotal(grupo);

                GuardarBTN(fila, nombreInput, nombre, identidad);
            });
        });
    });
}

function sumarTotal(grupo) {
    var fila = grupo.closest('tr');

    var totalPuntosElement = fila.querySelector('.total_puntos');

    if (totalPuntosElement) {
        var botonesSeleccionados = fila.querySelectorAll('.seleccionado');

        var total = Array.from(botonesSeleccionados).reduce(function (sum, boton) {
            return sum + parseInt(boton.value);
        }, 0);

        totalPuntosElement.value = total;
    } else {
        console.error("Elemento no encontrado en la fila actual.");
    }
}


function GuardarBTN(fila, nombreInput, nombre, identidad) {
    console.log("Desde GuardarBTN - Fila: " + fila + ", Nombre Input: " + nombreInput + "entidad es "+ identidad);
    // Variables locales para almacenar los valores de las evaluaciones
    var evaluaciones = {
        proactividad: "",
        calidad_servicio: "",
        compromiso: "",
        vestimenta: ""
    };
    var numero = fila.split('_')[0]; // Obtener el primer dígito de la fila
    // Asignar el valor según el nombre de la evaluación
    evaluaciones[nombre] = numero;
    // console.log(evaluaciones);

    // Crear un objeto FormData
    var formData = new FormData();
    formData.append('evaluaciones', JSON.stringify(evaluaciones));
    formData.append('idpersonal', nombreInput);
    formData.append('mes', "{{ $fecha_mes }}");
    formData.append('año', "{{ $fecha_año }}");
    formData.append('entidad', identidad);
    formData.append("_token", $("input[name=_token]").val());

    // console.log(formData);

    $.ajax({
        type: 'post',
        url: "{{ route('formatos.evaluacion_motivacional.store_datos') }}",
        dataType: "json",
        data: formData,
        processData: false,
        contentType: false,
        success: function(data){
            // Manejar la respuesta del servidor si es necesario
            $( "#total_puntos" ).load(window.location.href + " #total_puntos" ); 
        },
        error: function(xhr, textStatus, errorThrown) {
            console.log(xhr.responseText);
        }
    });
}

function btnEliminarRegistro (id) {
    console.log(id)
    if (!id) {
        Swal.fire({
            icon: "warning",
            text: "No hay calificación en proceso!",
            confirmButtonText: "Aceptar"
        });
    } else {
        
        swal.fire({
            title: "Seguro que desea eliminar su calificación?",
            text: "La calificación será eliminado totalmente",
            icon: "error",
            showCancelButton: !0,
            confirmButtonText: "Aceptar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: "{{ route('formatos.evaluacion_motivacional.delete_datos') }}",
                    type: 'post',
                    data: {"_token": "{{ csrf_token() }}", id: id},
                    success: function(response){
                        console.log(response);

                        // tabla_seccion(); 
                        $( "#del-text" ).load(window.location.href + " #del-text" ); 

                        Toastify({
                            text: "Se eliminó calificación",
                            className: "danger",
                            style: {
                                background: "#DF1818",
                            }
                        }).showToast();

                    },
                    error: function(error){
                        console.log('Error '+error);
                    }
                });
            }

        })

    }

 
}

</script>