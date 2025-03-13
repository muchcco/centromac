$(document).ready(function () {
    $('#btnMenu-Configuración').removeClass('nav-link');
    $('#btnMenu-Configuración').addClass('activo');

    $("#gvUsuario").bootstrapTable({
        cache: false,
        search: false,
        pagination: false
    }).bootstrapTable('resetView');

    $('#FECHA_CADUCA_NUEVO, #FECHA_CADUCA').mask("99/99/9999").datetimepicker({
        format: 'DD/MM/YYYY'
    });

    $('#DNI, #DNI_NUEVO').filter_input_solonumeros();
    $('#NOMBRE, #APELLIDO_PATERNO, #APELLIDO_MATERNO, #USUARIO, #NOMBRE_NUEVO, #APELLIDO_PATERNO_NUEVO, #APELLIDO_MATERNO_NUEVO, #USUARIO_NUEVO, #PASSWORD_NUEVO').filter_input_alfanumericos();
});

$('#rdESTADO_NUEVO_1, #rdESTADO_NUEVO_0').click(function () {
    $('#ESTADO_VIGENCIA_NUEVO').val($(this).val());
})

window.operateEvents = {
    'click .gvUsuario-ibtnEliminar': function (e, value, row, index) {
        bootbox.confirm("¿Estás seguro de eliminar? ", function (result) {
            if (result) {
                Obtener_Usuario(1, row["ID_USUARIO"], 4);
            }
        });
    },
    'click .gvUsuario-ibtnEditar': function (e, value, row, index) {
        showDialog_Usuario(row["ID_USUARIO"], 'Edición de Usuario');
    }    
}

$("#btnBuscarUsuario").click(function () {
    Obtener_Usuario(1, 0, 1);
});

$("#btnNuevoUsuario").click(function () {
    showDialog_Usuario(0, 'Nuevo Usuario');
});

function gvUsuarioNro(value, row, index) {
    return row["FILA"];
}

function gvUsuarioNombreCompleto(value, row, index) {
    return row["NOMBRE"] + ' ' + row["APELLIDO_PATERNO"] + ' ' + row["APELLIDO_MATERNO"];
}

function gvUsuarioTipoUsuario(value, row, index) {
    var retorna = '';
    if (row["TIPO_USUARIO"] == "1")
        retorna = "Asesor";
    if (row["TIPO_USUARIO"] == "2")
        retorna = "Supervisor Municipal";
    if (row["TIPO_USUARIO"] == "3")
        retorna = "Administrador SAC";

    return retorna;
}

function gvUsuarioFechaCaduca(value, row, index) {
    return moment(value).format('DD/MM/YYYY');
}

function gvUsuarioEstado(value, row, index) {
    var retorna = "";
    if (row["ESTADO_VIGENCIA"] == "1")
        retorna = "Activo";
    if (row["ESTADO_VIGENCIA"] == "2")
        retorna = "Desactivo";
    return retorna;
}

function operateFormatter_Usuario(value, row, index) {
    return [
        '<a class="table-opciones espacio gvUsuario-ibtnEditar" href="javascript:void(0)" title="Editar Usuario" ><span class="glyphicon glyphicon-pencil"></span></a>&nbsp;&nbsp;',
        '<a class="table-opciones espacio gvUsuario-ibtnEliminar" href="javascript:void(0)" title ="Eliminar Usuario" ><span class="glyphicon glyphicon-trash" ></span></a>&nbsp;&nbsp;',

    ].join('');
}

function showDialog_Usuario(ID_USUARIO, titulo) {

    var FormulariosPopupInstance = new BootstrapDialog({
        title: '<b></b>',
        message: $('<div></div>'),
        size: BootstrapDialog.SIZE_WIDE,
        closable: false,
        draggable: true
    });

    var url = '/Configuracion/AdminUsuario/nuevaUsuario?ID_USUARIO=' + ID_USUARIO;

    var message = $('<div></div>').load(url);
    FormulariosPopupInstance.setTitle(titulo);
    FormulariosPopupInstance.setMessage(message);
    FormulariosPopupInstance.open();
}

function Obtener_Usuario(numeroPagina, ID_USUARIO, TIPO) {
    $("#rowPaginadoUsuario").empty();
    $("#intNumeroPaginaUsuario").val(numeroPagina);

    var OBJ = new Object;
    OBJ.ID_USUARIO = ID_USUARIO;
    OBJ.ID_ENTIDAD = $("#ID_ENTIDAD").val();
    OBJ.DNI = $("#DNI").val();
    OBJ.NOMBRE = $("#NOMBRE").val();
    OBJ.APELLIDO_PATERNO = $("#APELLIDO_PATERNO").val();
    OBJ.APELLIDO_MATERNO = $("#APELLIDO_MATERNO").val();
    OBJ.USUARIO = $("#USUARIO").val();
    OBJ.ESTADO_VIGENCIA = $("#ESTADO_VIGENCIA").val();
    OBJ.TIPO_USUARIO = $("#TIPO_USUARIO").val();
    OBJ.TIPO = TIPO;
    OBJ.intNumeroPaginaUsuario = numeroPagina;

    $("#gvUsuario").bootstrapTable('showLoading');
    var ID_USUARIO_ = ID_USUARIO;

    $.ajax({
        type: "POST",
        url: '/Configuracion/AdminUsuario/ListarUsuario',
        data: { models: OBJ },
        dataType: "json",
        success: function (data) {
            var _ID_USUARIO = ID_USUARIO_;
            if (data != null) {
                if (data != undefined) {
                    $("#gvUsuario").bootstrapTable('hideLoading');
                    fnCargar_Usuario(data);
                    fnPaginacion_Usuario(data);

                    if (_ID_USUARIO > 0)
                        bootbox.alert('Eliminado correctamente.');
                }
            }
        }
    });
}

function fnPaginacion_Usuario(data) {
    if (data.length > 0) {
        var total_ = data[0].TOTAL;
        var paginado_ = data[0].PAGINADO;
        var _pagina = 0;
        var i = 1;
        while (_pagina < total_) {
            _pagina = _pagina + paginado_;
            i++;
        }

        var script_ = '<div id="divPaginadoUsuario" class="text-center" > ';
        script_ += '<ul class="pagination pagination-sm pager" id ="myPagerUsuario" > ';
        script_ += '<li><a href="#" class="prev_link" onclick ="Obtener_Usuario(1, 0, 1);" >«</a></li>';
        for (var i_ = 1; i_ < i; i_++) {
            script_ += '<li><a href="#" class="page_link active" onclick ="Obtener_Usuario(' + i_ + ', 0, 1);" > ' + i_ + '</a></li>';
        }
        script_ += '		<li><a href="#" class="next_link" onclick ="Obtener_Usuario(' + (i - 1) + ', 0, 1);" >»</a></li></ul></div>';
        $("#rowPaginadoUsuario").empty().append(script_);
        $("#divCantidadRegistrosUsuario")[0].innerText = "Se encontraron (" + total_ + ") registros";
    }
}


$("#btnGuardarUsuario").click(function ()
{
    let administrador = '3';
    $('.ID_ENTIDAD_USUARIO_NUEVOformError').css('display', 'none');
    $('.ID_SEDE_USUARIO_NUEVOformError').css('display', 'none');

    if ($('#TIPO_USUARIO_NUEVO').val() != administrador) {
        $('#ID_ENTIDAD_USUARIO_NUEVO').addClass('validate[required]');
        $('#ID_SEDE_USUARIO_NUEVO').addClass('validate[required]');
    }    

    if ($("#modalUsuario").validar()) {
        let bit = false;

        if (($('#TIPO_USUARIO_NUEVO').val() == administrador) && ($('#ID_ENTIDAD_USUARIO_NUEVO').val() == '' && ($('#ID_SEDE_USUARIO_NUEVO').val() == '' || $('#ID_SEDE_USUARIO_NUEVO').val() == '0')))
            bit = true;

        if (($('#TIPO_USUARIO_NUEVO').val() != administrador) && ($('#ID_ENTIDAD_USUARIO_NUEVO').val() != '' && ($('#ID_SEDE_USUARIO_NUEVO').val() != '' && $('#ID_SEDE_USUARIO_NUEVO').val() != '0')))
            bit = true;

        if (bit) {
            bootbox.confirm("¿Estás seguro de guardar, estos resultados? ", function (result) {
                if (result) {
                    fnGrabar_Usuario();
                }
            });
        }
        else
            $("#modalUsuario").submit();
    }        
    else {
        debugger;
        $("#modalUsuario").submit();
    } 
});

function fnCargar_Usuario(data) {
    $("#divCantidadRegistrosUsuario")[0].innerText = "Se encontraron (0) registros";
    $("#gvUsuario").bootstrapTable('load', data);
}

function fnGrabar_Usuario()
{
    var OBJ = new Object;
    OBJ.ID_USUARIO = $('#ID_USUARIO_NUEVO').val();
    OBJ.ID_ENTIDAD = 0;
    OBJ.DNI = $("#DNI_NUEVO").val();
    OBJ.NOMBRE = '';
    OBJ.APELLIDO_PATERNO = '';
    OBJ.APELLIDO_MATERNO = '';
    OBJ.USUARIO = $("#USUARIO_NUEVO").val();    
    OBJ.ESTADO_VIGENCIA = 0;
    OBJ.TIPO_USUARIO = 0;
    OBJ.intNumeroPaginaUsuario = 1;
    OBJ.TIPO = 3;

    $.ajax({
        type: "POST",
        url: '/Configuracion/AdminUsuario/ListarUsuario',
        data: { models: OBJ },
        dataType: "json",
        success: function (data) {
            if (data != null) {
                if (data != undefined) {
                    var strMensaje = '';
                    switch (data[0].ID_USUARIO) {
                        case -1: strMensaje = 'La Persona ya tiene asignado un Usuario'; break;
                        case -2: strMensaje = 'La Persona ya tiene asignado un Usuario diferente'; break;
                        case -3: strMensaje = 'El Usuario ' + $('#USUARIO_NUEVO').val() + ', ya esta en uso'; break;
                    }

                    if (strMensaje == '')
                        $("#FormularioGuardarUsuario").empty().append(
                            '<input id="ID_USUARIO" name ="ID_USUARIO" type="hidden" value ="' + $("#ID_USUARIO_NUEVO").val() + '" />',
                            '<input id="ID_ENTIDAD" name ="ID_ENTIDAD" type="hidden" value ="' + $("#ID_ENTIDAD_USUARIO_NUEVO").val() + '" />',
                            '<input id="ID_SEDE" name ="ID_SEDE" type="hidden" value ="' + $("#ID_SEDE_USUARIO_NUEVO").val() + '" />',
                            '<input id="DNI" name ="DNI" type="hidden" value ="' + $("#DNI_NUEVO").val() + '" />',
                            '<input id="NOMBRE" name ="NOMBRE" type="hidden" value ="' + $("#NOMBRE_NUEVO").val() + '" />',
                            '<input id="APELLIDO_PATERNO" name ="APELLIDO_PATERNO" type="hidden" value ="' + $("#APELLIDO_PATERNO_NUEVO").val() + '" />',
                            '<input id="APELLIDO_MATERNO" name ="APELLIDO_MATERNO" type="hidden" value ="' + $("#APELLIDO_MATERNO_NUEVO").val() + '" />',
                            '<input id="USUARIO" name ="USUARIO" type="hidden" value ="' + $("#USUARIO_NUEVO").val() + '" />',
                            '<input id="PASSWORD" name ="PASSWORD" type="hidden" value ="' + Base64.encode($("#PASSWORD_NUEVO").val()) + '" />',
                            '<input id="FECHA_CADUCA" name ="FECHA_CADUCA" type="hidden" value ="' + $("#FECHA_CADUCA_NUEVO").val() + '" />',
                            '<input id="ESTADO_VIGENCIA" name ="ESTADO_VIGENCIA" type="hidden" value ="' + $("#ESTADO_VIGENCIA_NUEVO").val() + '" />',
                            '<input id="TIPO_USUARIO" name ="TIPO_USUARIO" type="hidden" value ="' + $("#TIPO_USUARIO_NUEVO").val() + '" />',
                        ).submit();
                    else
                        bootbox.alert(strMensaje);
                }
            }
        }
    });
}

$("#btnBuscarDNIUsuario").click(function () {
    alert('hola');
});

$('#btnBuscarDniExterna').click(function () {
    var strRuc = $('#DNI_NUEVO').val();
    $.ajax({
        url: '/Configuracion/AdminUsuario/ListarPersonaExterna',
        data: "{ 'dni': '" + strRuc + "'}",
        dataType: 'json',
        type: 'POST',
        contentType: 'application/json; charset=utf-8',
        dataFilter: function (data) { return data; },
        success: function (data) {
            if (data != null) {
                $('#DNI_NUEVO').val(data.DNI);
                $('#NOMBRE_NUEVO').val(data.NOMBRE);
                $('#APELLIDO_PATERNO_NUEVO').val(data.APELLIDO_PATERNO);
                $('#APELLIDO_MATERNO_NUEVO').val(data.APELLIDO_MATERNO);
            }
        },
        error: function (XMLHttpRequest, textStatus, errorThronw) {
            alert(textStatus);
        },
        failure: function (XMLHttpRequest, textStatus, errorThronw) {
            alert(textStatus);
        }
    });
});

$('#ID_ENTIDAD_USUARIO_NUEVO').change(function () {
    $("#ID_SEDE_USUARIO_NUEVO").empty().append("<option value=''>SELECCIONE</option>");

    if ($('#ID_ENTIDAD_USUARIO_NUEVO').val() != '') {
        var OBJ = new Object;
        OBJ.ID_ENTIDAD = fnGetTexto($('#ID_ENTIDAD_USUARIO_NUEVO').val());

        $.ajax({
            type: "POST",
            url: '/Configuracion/AdminUsuario/ListarEntidad',
            data: { models: OBJ },
            dataType: "json",
            success: function (data) {
                if (data != null)
                    if (data != undefined)
                        fnCargarSedeUsuario(data);
            }
        });
    }
    
});

$("#TIPO_USUARIO_NUEVO").change(function () {
    let administrador = '3';

    if (($('#TIPO_USUARIO_NUEVO').val() == administrador) || ($('#TIPO_USUARIO_NUEVO').val() == '')) {
        $('#ID_ENTIDAD_USUARIO_NUEVO').removeClass('validate[required]');
        $('#ID_SEDE_USUARIO_NUEVO').removeClass('validate[required]');
        $('#ID_ENTIDAD_USUARIO_NUEVO').prop("disabled", true);
        $('#ID_SEDE_USUARIO_NUEVO').prop("disabled", true);
        $('#ID_ENTIDAD_USUARIO_NUEVO').removeAttr('data-val-number');
        $('#ID_SEDE_USUARIO_NUEVO').removeAttr('data-val-number');
        $('#ID_ENTIDAD_USUARIO_NUEVO').val('');
        $('#ID_SEDE_USUARIO_NUEVO').val('');
    }
    else {
        $('#ID_ENTIDAD_USUARIO_NUEVO').prop("disabled", false);
        $('#ID_SEDE_USUARIO_NUEVO').prop("disabled", false);
        $('#ID_ENTIDAD_USUARIO_NUEVO').addClass('validate[required]');
        $('#ID_SEDE_USUARIO_NUEVO').addClass('validate[required]');        
        $('#ID_ENTIDAD_USUARIO_NUEVO').attr('data-val-number');
        $('#ID_SEDE_USUARIO_NUEVO').attr('data-val-number');
    }
        
});

function fnCargarSedeUsuario(data) {
    $("#ID_SEDE_USUARIO_NUEVO").empty().append("<option value=''>SELECCIONE</option>");

    for (var i = 0; i < data.length; i++) {
        if (data[i].Text != "SELECCIONE") {
            $("#ID_SEDE_USUARIO_NUEVO").append("<option value='" + data[i].ID_SEDE + "'>" + data[i].NOMBRE + "</option>");
        }
    }
}

function fnGetTexto(campo) {
    if (campo == undefined) {
        return "0";
    } else if (campo == "") {
        return "0";
    } else return campo;
}