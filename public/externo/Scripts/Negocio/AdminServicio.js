$(document).ready(function () {
    $('#btnMenu-Configuración').removeClass('nav-link');
    $('#btnMenu-Configuración').addClass('activo');

    window.history.go(+1);

    $("#gvServicio").bootstrapTable({
        cache: false,
        search: false,
        pagination: false
    }).bootstrapTable('resetView');

    $("#gvOperacion").bootstrapTable({
        cache: false,
        search: false,
        pagination: false
    }).bootstrapTable('resetView');

    $("#gvParametro").bootstrapTable({
        cache: false,
        search: false,
        pagination: false
    }).bootstrapTable('resetView');

    $("#rdMODOSERVICIO_NUEVO_1, #rdES_OBLIGATORIO_NUEVO_1, #rdTIPO_PARAMETRO_NUEVO_1").prop('checked', true);

    $('#ORDEN_NUEVO').filter_input_solonumeros();
    $("#DESCRIPCION_NUEVO, #ACCION_NUEVO").filter_input_alfanumericos();
    $("#PARAMETRO_NUEVO, #COMENTARIO_NUEVO").filter_input_alfanumericos();
    $("#ID_OBLIGATORIO_NUEVO, #TIPO_PARAMETRO_NUEVO").val('1');
});

$('#rdESTADO_NUEVO_1, #rdESTADO_NUEVO_0').click(function () {
    $('#ESTADO_VIGENCIA_NUEVO').val($(this).val());
})

$('#rdMODOSERVICIO_NUEVO_1').click(function () {
    $('#LINK_NUEVO').removeAttr('readonly');
    $("#ID_MODOSERVICIO_NUEVO").val($(this).val());
});

$('#rdMODOSERVICIO_NUEVO_2').click(function () {
    $('#LINK_NUEVO').attr('readonly', 'readonly')
    $("#ID_MODOSERVICIO_NUEVO").val($(this).val());
});
$('#rdMODOSERVICIO_NUEVO_3').click(function () {
    $('#LINK_NUEVO').attr('readonly', 'readonly');
    $("#ID_MODOSERVICIO_NUEVO").val($(this).val());
});

$('#rdTIPOACCESO_NUEVO_1, #rdTIPOACCESO_NUEVO_2').click(function () {
    $('#ID_TIPOACCESO_NUEVO').val($(this).val());
})

window.operateEvents = {
    'click .gvServicio-ibtnEliminar': function (e, value, row, index) {
        bootbox.confirm("¿Estás seguro de eliminar? ", function (result) {
            if (result) {
                Obtener_Servicio(1, row["ID_SERVICIO"]);
            }
        });
    },
    'click .gvServicio-ibtnEditar': function (e, value, row, index) {
        var url = '/Configuracion/AdminServicio/nuevaServicio?ID_SERVICIO=' + row["ID_SERVICIO"];
        showDialog_Servicio(url, 'Modificar Servicio');
    },
    'click .gvOperacion-ibtnEliminar': function (e, value, row, index) {
        bootbox.confirm("¿Estás seguro de eliminar? ", function (result) {
            if (result) {
                Obtener_Operacion(1, row["ID_OPERACION"]);
            }
        });
    },
    'click .gvOperacion-ibtnEditar': function (e, value, row, index)
    {
        var NOMBRE = row["SERVICIO"];
        var cont = NOMBRE.indexOf(' ');
        while (cont > 0) {
            NOMBRE = NOMBRE.replace(' ', '│');
            cont = NOMBRE.indexOf(' ');
        }

        var url = '/Configuracion/AdminServicio/nuevaOperacion?ID_OPERACION=' + row["ID_OPERACION"] + '&&ID_SERVICIO=' + row["ID_SERVICIO"] + '&&NOMBRE=' + NOMBRE;
        showDialog_Servicio(url, 'Modificar Operación');
    },
    'click .gvParametro-ibtnEliminar': function (e, value, row, index) {
        bootbox.confirm("¿Estás seguro de eliminar? ", function (result) {
            if (result) {
                Obtener_Parametro(1, row["ID_PARAMETRO"]);
            }
        });
    },
    'click .gvParametro-ibtnEditar': function (e, value, row, index) {

        var NOMBRE = row["PARAMETRO"];

        var cont = NOMBRE.indexOf(' ');
        while (cont > 0) {
            NOMBRE = NOMBRE.replace(' ', '│');
            cont = NOMBRE.indexOf(' ');
        }

        var url = '/Configuracion/AdminServicio/nuevaParametro?ID_PARAMETRO=' + row["ID_PARAMETRO"] + '&&ID_OPERACION=' + row["ID_OPERACION"] + '&&NOMBRE=' + NOMBRE;
        showDialog_Servicio(url, 'Modificar Parámetro');
    },
}


$("#btnBuscarServicio").click(function () {
    Obtener_Servicio(1, 0);
    //Obtener_Operacion(1, 0);
    //Obtener_Parametro(1, 0);
});

$("#btnNuevoServicio").click(function () {
    var url = '/Configuracion/AdminServicio/nuevaServicio?ID_SERVICIO=0';
    showDialog_Servicio(url, 'Nuevo Servicio');
});

function gvServicioNro(value, row, index) {
    return row["FILA"];
}

function gvTipoServicio(value, row, index) {
    var RETORNA = "";
    if (row["ID_TIPOSERVICIO"] == "1")
        RETORNA = "Parcial";
    if (row["ID_TIPOSERVICIO"] == "2")
        RETORNA = "Completo";

    return RETORNA;
}

function gvParametroEsObligatorio(value, row, index) {
    var RETORNA = "";
    if (row["ID_OBLIGATORIO"] == "1")
        RETORNA = "SI";
    if (row["ID_OBLIGATORIO"] == "2")
        RETORNA = "NO";

    return RETORNA;
}

function gvParametroTipoParametro(value, row, index) {
    var RETORNA = "";
    if (row["TIPO_PARAMETRO"] == "1")
        RETORNA = "Entrada";
    if (row["TIPO_PARAMETRO"] == "2")
        RETORNA = "Salida";

    return RETORNA;
}

function gvModoServicio(value, row, index) {
    var RETORNA = "";
    if (row["ID_MODOSERVICIO"] == "1")
        RETORNA = "Página Web";
    if (row["ID_MODOSERVICIO"] == "2")
        RETORNA = "Servicio Web";

    return RETORNA;
}

function gvOperacionNro(value, row, index) {
    return row["FILA"];
}

function gvParametroNro(value, row, index) {
    return row["FILA"];
}

function operateFormatter_Servicio(value, row, index) {
    return [
        '<a class="table-opciones espacio gvServicio-ibtnEditar" href="javascript:void(0)" title="Editar Servicio" ><span class="glyphicon glyphicon-pencil"></span></a>&nbsp;&nbsp;',
        '<a class="table-opciones espacio gvServicio-ibtnEliminar" href="javascript:void(0)" title ="Eliminar Servicio" ><span class="glyphicon glyphicon-trash" ></span></a>&nbsp;&nbsp;',

    ].join('');
}

function operateFormatter_Operacion(value, row, index) {
    return [
        '<a class="table-opciones espacio gvOperacion-ibtnEditar" href="javascript:void(0)" title="Editar Operacion" ><span class="glyphicon glyphicon-pencil"></span></a>&nbsp;&nbsp;',
        '<a class="table-opciones espacio gvOperacion-ibtnEliminar" href="javascript:void(0)" title ="Eliminar Operacion" ><span class="glyphicon glyphicon-trash" ></span></a>&nbsp;&nbsp;',

    ].join('');
}

function operateFormatter_Parametro(value, row, index) {
    return [
        '<a class="table-opciones espacio gvParametro-ibtnEditar" href="javascript:void(0)" title="Editar Parametro" ><span class="glyphicon glyphicon-pencil"></span></a>&nbsp;&nbsp;',
        '<a class="table-opciones espacio gvParametro-ibtnEliminar" href="javascript:void(0)" title ="Eliminar Parametro" ><span class="glyphicon glyphicon-trash" ></span></a>&nbsp;&nbsp;',

    ].join('');
}

function showDialog_Servicio(url, titulo) {

    var FormulariosPopupInstance = new BootstrapDialog({
        title: '<b></b>',
        message: $('<div></div>'),
        size: BootstrapDialog.SIZE_WIDE,
        closable: false,
        draggable: true
    });

    var message = $('<div></div>').load(url);
    FormulariosPopupInstance.setTitle(titulo);
    FormulariosPopupInstance.setMessage(message);
    FormulariosPopupInstance.open();
}

function Obtener_Servicio(numeroPagina, ID_SERVICIO) {

    $("#rowPaginadoServicio").empty();
    $("#intNumeroPaginaServicio").val(numeroPagina);

    var OBJ = new Object;
    OBJ.ID_SERVICIO = ID_SERVICIO;
    OBJ.TIPOSERVICIO = $("#TIPOSERVICIO").val();
    OBJ.ID_ENTIDAD = $("#ID_ENTIDAD").val();
    OBJ.MODOSERVICIO = $("#MODOSERVICIO").val();
    OBJ.NOMBRE = $("#NOMBRE").val();
    OBJ.TIPOACCESO = $("#TIPOACCESO").val();
    OBJ.ESTADO_VIGENCIA = 0;
    OBJ.intNumeroPaginaServicio = numeroPagina;
    OBJ.ESTADO_VIGENCIA = $('#ESTADO_VIGENCIA').val();
    var ID_SERVICIO_ = ID_SERVICIO;

    $.ajax({
        type: "POST",
        url: '/Configuracion/AdminServicio/ListarServicio',
        data: { models: OBJ },
        dataType: "json",
        success: function (data) {
            var _ID_SERVICIO = ID_SERVICIO_;
            if (data != null) {
                if (data != undefined) {

                    $('#gvParametro').bootstrapTable('removeAll');
                    $('#gvOperacion').bootstrapTable('removeAll');

                    fnCargar_Servicio(data);
                    fnPaginacion_Servicio(data);

                    if (_ID_SERVICIO > 0)
                        bootbox.alert('Eliminado correctamente.');
                }
            }
        }
    });
}

function Obtener_Operacion(numeroPagina, ID_OPERACION) {
    $("#rowPaginadoOperacion").empty();
    $("#intNumeroPaginaOperacion").val(numeroPagina);

    if (selectedServicioRow.ID_SERVICIO != undefined) {
        var OBJ = new Object;
        OBJ.ID_OPERACION = ID_OPERACION;
        OBJ.ID_SERVICIO = selectedServicioRow.ID_SERVICIO;
        OBJ.DESCRIPCION = $("#DESCRIPCION").val();
        OBJ.ACCION = $("#ACCION").val();
        OBJ.intNumeroPaginaOperacion = numeroPagina;
        var ID_OPERACION_ = ID_OPERACION;

        $.ajax({
            type: "POST",
            url: '/Configuracion/AdminServicio/ListarOperacion',
            data: { models: OBJ },
            dataType: "json",
            success: function (data) {
                var _ID_OPERACION = ID_OPERACION_;
                if (data != null) {
                    if (data != undefined) {
                        fnCargar_Operacion(data);
                        fnPaginacion_Operacion(data);

                        if (_ID_OPERACION > 0)
                            bootbox.alert('Eliminado correctamente.');
                    }
                }
            }
        });
    }
}

function Obtener_Parametro(numeroPagina, ID_PARAMETRO) {
    $("#rowPaginadoParametro").empty();
    $("#intNumeroPaginaParametro").val(numeroPagina);

    var OBJ = new Object;
    OBJ.ID_PARAMETRO = ID_PARAMETRO;
    OBJ.ID_OPERACION = selectedOperacionRow.ID_OPERACION;
    OBJ.intNumeroPaginaParametro = numeroPagina;
    var ID_PARAMETRO_ = ID_PARAMETRO;

    $.ajax({
        type: "POST",
        url: '/Configuracion/AdminServicio/ListarParametro',
        data: { models: OBJ },
        dataType: "json",
        success: function (data) {
            var _ID_PARAMETRO = ID_PARAMETRO_;
            if (data != null) {
                if (data != undefined) {
                    fnCargar_Parametro(data);
                    fnPaginacion_Parametro(data);

                    if (_ID_PARAMETRO > 0)
                        bootbox.alert('Eliminado correctamente.');
                }
            }
        }
    });
}

function fnCargar_Servicio(data) {
    $("#divCantidadRegistrosServicio")[0].innerText = "Se encontraron (0) registros";
    $("#gvServicio").bootstrapTable('load', data);

    $('#seccionOperacion').css('display', 'none');
    $('#seccionParametros').css('display', 'none');
}

function fnCargar_Operacion(data) {
    $("#gvOperacion").bootstrapTable('load', data);
}

function fnCargar_Parametro(data) {
    $("#gvParametro").bootstrapTable('load', data);
}

function fnPaginacion_Servicio(data) {
    if (data.length > 0) {
        var total_ = data[0].TOTAL;
        var paginado_ = data[0].PAGINADO;
        var _pagina = 0;
        var i = 1;
        while (_pagina < total_) {
            _pagina = _pagina + paginado_;
            i++;
        }

        var script_ = '<div id="divPaginadoServicio" class="text-center" > ';
        script_ += '<ul class="pagination pagination-sm pager" id ="myPagerServicio" > ';
        script_ += '<li><a href="#" class="prev_link" onclick ="Obtener_Servicio(1, 0);" >«</a></li>';
        for (var i_ = 1; i_ < i; i_++) {
            script_ += '<li><a href="#" class="page_link active" onclick ="Obtener_Servicio(' + i_ + ', 0);" > ' + i_ + '</a></li>';
        }
        script_ += '		<li><a href="#" class="next_link" onclick ="Obtener_Servicio(' + (i - 1) + ', 0);" >»</a></li></ul></div>';
        $("#rowPaginadoServicio").empty().append(script_);
        $("#divCantidadRegistrosServicio")[0].innerText = "Se encontraron (" + total_ + ") registros";
    }
}

function fnPaginacion_Operacion(data) {
    if (data.length > 0) {
        var total_ = data[0].TOTAL;
        var paginado_ = data[0].PAGINADO;
        var _pagina = 0;
        var i = 1;
        while (_pagina < total_) {
            _pagina = _pagina + paginado_;
            i++;
        }
        var script_ = '<div id="divPaginadoOperacion" class="text-center" > ';
        script_ += '<ul class="pagination pagination-sm pager" id ="myPagerOperacion" > ';
        script_ += '<li><a href="#" class="prev_link" onclick ="Obtener_Operacion(1, 0);" >«</a></li>';
        for (var i_ = 1; i_ < i; i_++) {
            script_ += '<li><a href="#" class="page_link active" onclick ="Obtener_Operacion(' + i_ + ', 0);" > ' + i_ + '</a></li>';
        }
        script_ += '		<li><a href="#" class="next_link" onclick ="Obtener_Operacion(' + (i - 1) + ', 0);" >»</a></li></ul></div>';
        $("#rowPaginadoOperacion").empty().append(script_);        
    }
}

function fnPaginacion_Parametro(data) {
    if (data.length > 0) {
        var total_ = data[0].TOTAL;
        var paginado_ = data[0].PAGINADO;
        var _pagina = 0;
        var i = 1;
        while (_pagina < total_) {
            _pagina = _pagina + paginado_;
            i++;
        }
        var script_ = '<div id="divPaginadoParametro" class="text-center" > ';
        script_ += '<ul class="pagination pagination-sm pager" id ="myPagerParametro" > ';
        script_ += '<li><a href="#" class="prev_link" onclick ="Obtener_Parametro(1, 0);" >«</a></li>';
        for (var i_ = 1; i_ < i; i_++) {
            script_ += '<li><a href="#" class="page_link active" onclick ="Obtener_Parametro(' + i_ + ', 0);" > ' + i_ + '</a></li>';
        }
        script_ += '		<li><a href="#" class="next_link" onclick ="Obtener_Parametro(' + (i - 1) + ', 0);" >»</a></li></ul></div>';
        $("#rowPaginadoParametro").empty().append(script_);
    }
}

$("#btnGuardarServicio").click(function () {
    $("#modalServicio").validationEngine();
    if ($("#modalServicio").validar())
        bootbox.confirm("¿Estás seguro de guardar, estos resultados? ", function (result) {
            if (result) {
                fnGrabar_Servicio();
            }
        });
});

$("#btnGuardarOperacion").click(function () {
    $("#modalOperacion").validationEngine();
    if ($("#modalOperacion").validar())
        bootbox.confirm("¿Estás seguro de guardar, estos resultados? ", function (result) {
            if (result) {
                fnGrabar_Operacion();
            }
        });
});

$("#btnGuardarParametro").click(function () {
    $("#modalParametro").validationEngine();
    if ($("#modalParametro").validar())
        bootbox.confirm("¿Estás seguro de guardar, estos resultados? ", function (result) {
            if (result) {
                fnGrabar_Parametro();
            }
        });
});

function fnGrabar_Servicio() {
    var isDNI_URL = '0';
    if ($("#chboxDNI").is(':checked')) {
        isDNI_URL = '1';
    }
    $("#FormularioGuardarServicio").empty().append(
        '<input id="ID_SERVICIO" name ="ID_SERVICIO" type="hidden" value ="' + $("#ID_SERVICIO_NUEVO").val() + '" />',
        '<input id="ID_TIPOSERVICIO" name ="ID_TIPOSERVICIO" type="hidden" value ="' + $("#ID_TIPOSERVICIO_NUEVO").val() + '" />',
        '<input id="ID_ENTIDAD" name ="ID_ENTIDAD" type="hidden" value ="' + $("#ID_ENTIDAD_NUEVO").val() + '" />',
        '<input id="ID_MODOSERVICIO" name ="ID_MODOSERVICIO" type="hidden" value ="' + $("#ID_MODOSERVICIO_NUEVO").val() + '" />',
        '<input id="NOMBRE" name ="NOMBRE" type="hidden" value ="' + $("#NOMBRE_NUEVO").val() + '" />',
        '<input id="ID_TIPOACCESO" name ="ID_TIPOACCESO" type="hidden" value ="' + $("#ID_TIPOACCESO_NUEVO").val() + '" />',
        '<input id="LINK" name ="LINK" type="hidden" value ="' + $("#LINK_NUEVO").val() + '" />',
        '<input id="ORDEN" name ="ORDEN" type="hidden" value ="' + $("#ORDEN_NUEVO").val() + '" />',
        '<input id="ESTADO_VIGENCIA" name ="ESTADO_VIGENCIA" type="hidden" value ="' + $("#ESTADO_VIGENCIA_NUEVO").val() + '" />',

        '<input id="ID_OPERACION" name ="ID_OPERACION" type="hidden" value ="0" />',
        '<input id="DESCRIPCION" name ="DESCRIPCION" type="hidden" value ="" />',
        '<input id="ACCION" name ="ACCION" type="hidden" value ="" />',                

        '<input id="ID_PARAMETRO" name ="ID_PARAMETRO" type="hidden" value ="0" />',
        '<input id="PARAMETRO" name ="PARAMETRO" type="hidden" value ="" />',
        '<input id="ID_OBLIGATORIO" name ="ID_OBLIGATORIO" type="hidden" value ="0" />',
        '<input id="ID_TIPO_PARAMETRO" name ="ID_TIPO_PARAMETRO" type="hidden" value ="0" />',
        '<input id="COMENTARIO" name ="COMENTARIO" type="hidden" value ="" />',
        '<input id="DNI_URL" name ="DNI_URL" type="hidden" value ="' + isDNI_URL+'" />',
    ).submit();
}

function fnGrabar_Operacion() {
    $("#FormularioGuardarOperacion").empty().append(
        '<input id="ID_SERVICIO" name ="ID_SERVICIO" type="hidden" value ="' + $("#ID_SERVICIO_OPERACION_NUEVO").val() + '" />',
        '<input id="ID_TIPOSERVICIO" name ="ID_TIPOSERVICIO" type="hidden" value ="0" />',
        '<input id="ID_ENTIDAD" name ="ID_ENTIDAD" type="hidden" value ="0" />',
        '<input id="ID_MODOSERVICIO" name ="ID_MODOSERVICIO" type="hidden" value ="0" />',
        '<input id="NOMBRE" name ="NOMBRE" type="hidden" value ="" />',
        '<input id="ID_TIPOACCESO" name ="ID_TIPOACCESO" type="hidden" value ="0" />',
        '<input id="LINK" name ="LINK" type="hidden" value ="" />',
        '<input id="ORDEN" name ="ORDEN" type="hidden" value ="0" />',
        '<input id="ESTADO_VIGENCIA" name ="ESTADO_VIGENCIA" type="hidden" value ="0" />',

        '<input id="ID_OPERACION" name ="ID_OPERACION" type="hidden" value ="' + $("#ID_OPERACION_NUEVO").val() + '" />',        
        '<input id="DESCRIPCION" name ="DESCRIPCION" type="hidden" value ="' + $("#DESCRIPCION_NUEVO").val() + '" />',
        '<input id="ACCION" name ="ACCION" type="hidden" value ="' + $("#ACCION_NUEVO").val() + '" />',

        '<input id="ID_PARAMETRO" name ="ID_PARAMETRO" type="hidden" value ="0" />',
        '<input id="PARAMETRO" name ="PARAMETRO" type="hidden" value ="" />',
        '<input id="ID_OBLIGATORIO" name ="ID_OBLIGATORIO" type="hidden" value ="0" />',
        '<input id="ID_TIPO_PARAMETRO" name ="ID_TIPO_PARAMETRO" type="hidden" value ="0" />',
        '<input id="COMENTARIO" name ="COMENTARIO" type="hidden" value ="" />',
    ).submit();
}

function fnGrabar_Parametro() {
    $("#FormularioGuardarParametro").empty().append(
        '<input id="ID_SERVICIO" name ="ID_SERVICIO" type="hidden" value ="0" />',
        '<input id="ID_TIPOSERVICIO" name ="ID_TIPOSERVICIO" type="hidden" value ="0" />',
        '<input id="ID_ENTIDAD" name ="ID_ENTIDAD" type="hidden" value ="0" />',
        '<input id="ID_MODOSERVICIO" name ="ID_MODOSERVICIO" type="hidden" value ="0" />',
        '<input id="NOMBRE" name ="NOMBRE" type="hidden" value ="" />',
        '<input id="ID_TIPOACCESO" name ="ID_TIPOACCESO" type="hidden" value ="0" />',
        '<input id="LINK" name ="LINK" type="hidden" value ="" />',
        '<input id="ORDEN" name ="ORDEN" type="hidden" value ="0" />',
        '<input id="ESTADO_VIGENCIA" name ="ESTADO_VIGENCIA" type="hidden" value ="0" />',

        '<input id="ID_OPERACION" name ="ID_OPERACION" type="hidden" value ="' + $("#ID_OPERACION_PARAMETRO_NUEVO").val() + '" />',
        '<input id="DESCRIPCION" name ="DESCRIPCION" type="hidden" value ="" />',
        '<input id="ACCION" name ="ACCION" type="hidden" value ="" />',

        '<input id="ID_PARAMETRO" name ="ID_PARAMETRO" type="hidden" value ="' + $("#ID_PARAMETRO_NUEVO").val() + '" />',
        '<input id="PARAMETRO" name ="PARAMETRO" type="hidden" value ="' + $("#PARAMETRO_NUEVO").val() + '" />',
        '<input id="ID_OBLIGATORIO" name ="ID_OBLIGATORIO" type="hidden" value ="' + $("#ID_OBLIGATORIO_NUEVO").val() + '" />',
        '<input id="ID_TIPO_PARAMETRO" name ="ID_TIPO_PARAMETRO" type="hidden" value ="' + $("#ID_TIPO_PARAMETRO_NUEVO").val() + '" />',
        '<input id="COMENTARIO" name ="COMENTARIO" type="hidden" value ="' + $("#COMENTARIO_NUEVO").val() + '" />',
    ).submit();
}



$('#rdES_OBLIGATORIO_NUEVO_1').click(function () {
    $("#ID_OBLIGATORIO_NUEVO").val($(this).val());
});

$('#rdES_OBLIGATORIO_NUEVO_2').click(function () {
    $("#ID_OBLIGATORIO_NUEVO").val($(this).val());
});

$('#rdTIPO_PARAMETRO_NUEVO_1').click(function () {
    $("#ID_TIPO_PARAMETRO_NUEVO").val($(this).val());
});

$('#rdTIPO_PARAMETRO_NUEVO_2').click(function () {
    $("#ID_TIPO_PARAMETRO_NUEVO").val($(this).val());
});


$('#btnAgregarOperacionServicio').click(function () {
    var url = '/Configuracion/AdminServicio/nuevaOperacion?ID_OPERACION=0';

    if (selectedServicioRow.ID_MODOSERVICIO != undefined) {
        if (selectedServicioRow.ID_MODOSERVICIO == 2) {
            var ID_SERVICIO = selectedServicioRow.ID_SERVICIO;
            var NOMBRE = selectedServicioRow.NOMBRE;

            var cont = NOMBRE.indexOf(' ');
            while (cont > 0) {
                NOMBRE = NOMBRE.replace(' ', '│');
                cont = NOMBRE.indexOf(' ');
            }

            showDialog_Servicio(url + '&&ID_SERVICIO=' + ID_SERVICIO + '&&NOMBRE=' + NOMBRE, 'Nueva Operación');
        }
    }
});
   
$('#btnAgregarParametrosServicio').click(function () {
    var url = '/Configuracion/AdminServicio/nuevaParametro?ID_PARAMETRO=0';

    if (selectedOperacionRow.ID_OPERACION != undefined) {
        var ID_OPERACION = selectedOperacionRow.ID_OPERACION;
        var NOMBRE = selectedOperacionRow.DESCRIPCION;

        var cont = NOMBRE.indexOf(' ');
        while (cont > 0) {
            NOMBRE = NOMBRE.replace(' ', '│');
            cont = NOMBRE.indexOf(' ');
        }

        showDialog_Servicio(url + '&&ID_OPERACION=' + ID_OPERACION + '&&NOMBRE=' + NOMBRE, 'Nueva Parámetro');
    }
});

function gvServicioEstado(value, row, index) {
    var retorna = "";
    if (row["ESTADO_VIGENCIA"] == "1")
        retorna = "Activo";
    if (row["ESTADO_VIGENCIA"] == "2")
        retorna = "Desactivo";
    return retorna;
}