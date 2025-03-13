$(document).ready(function () {
    $('#btnMenu-Configuración').removeClass('nav-link');
    $('#btnMenu-Configuración').addClass('activo');

    $("#ID_ENTIDAD, #ORDEN, #RUC, #ID_ENTIDAD_NUEVO, #ORDEN_NUEVO, #RUC_NUEVO").filter_input_solonumeros();
    $("#NOMBRE, #NOMBRE_NUEVO").filter_input_alfanumericos();
});

window.operateEvents = {
    'click .gvEntidad-ibtnEliminar': function (e, value, row, index) {
        bootbox.confirm("¿Estás seguro de eliminar? ", function (result) {
            if (result) {
                Obtener_Entidad(1, row["ID_ENTIDAD"]);
            }
        });

    },
    'click .gvEntidad-ibtnEditar': function (e, value, row, index) {
        showDialog_Entidad(row["ID_ENTIDAD"], 'Editar Entidad');
    },
}

$("#btnBuscarEntidad").click(function () {
    Obtener_Entidad(1, 0);
});

$("#btnNuevoEntidad").click(function () {
    showDialog_Entidad(0, 'Nueva Entidad');
});

function gvEntidadNro(value, row, index) {
    return row["FILA"];
}

function operateFormatter_Entidad(value, row, index) {
    return [
        '<a class="table-opciones espacio gvEntidad-ibtnEditar" href="javascript:void(0)" title="Editar Entidad" ><span class="glyphicon glyphicon-pencil"></span></a>&nbsp;&nbsp;',
        '<a class="table-opciones espacio gvEntidad-ibtnEliminar" href="javascript:void(0)" title ="Eliminar Entidad" ><span class="glyphicon glyphicon-trash" ></span></a>&nbsp;&nbsp;',

    ].join('');
}

function showDialog_Entidad(ID_ENTIDAD, titulo) {

    var FormulariosPopupInstance = new BootstrapDialog({
        title: '<b></b>',
        message: $('<div></div>'),
        size: BootstrapDialog.SIZE_WIDE,
        closable: false,
        draggable: true
    });

    var url = '/Configuracion/AdminEntidad/nuevaEntidad?ID_ENTIDAD=' + ID_ENTIDAD;

    var message = $('<div></div>').load(url);
    FormulariosPopupInstance.setTitle(titulo);
    FormulariosPopupInstance.setMessage(message);
    FormulariosPopupInstance.open();
}

function Obtener_Entidad(numeroPagina, ID_ENTIDAD) {
    $("#rowPaginadoEntidad").empty();
    $("#intNumeroPaginaEntidad").val(numeroPagina);

    var OBJ = new Object;
    OBJ.ID_ENTIDAD = ID_ENTIDAD;
    OBJ.NOMBRE = $("#NOMBRE").val();
    OBJ.RUC = $("#RUC").val();
    OBJ.ORDEN = $("#ORDEN").val();
    OBJ.intNumeroPaginaEntidad = numeroPagina;

    $("#gvEntidad").bootstrapTable('showLoading');
    var ID_ENTIDAD_ = ID_ENTIDAD;
    $.ajax({
        type: "POST",
        url: '/Configuracion/AdminEntidad/ListarEntidad',
        data: { models: OBJ },
        dataType: "json",
        success: function (data) {
            var _ID_ENTIDAD = ID_ENTIDAD_;
            if (data != null) {
                if (data != undefined) {
                    $("#gvEntidad").bootstrapTable('hideLoading');
                    fnCargar_Entidad(data);
                    fnPaginacion_Entidad(data);

                    if (_ID_ENTIDAD > 0)
                        bootbox.alert('Eliminado correctamente.');
                }
            }
        }
    });
}

function fnPaginacion_Entidad(data) {
    if (data.length > 0) {
        var total_ = data[0].TOTAL;
        var paginado_ = data[0].PAGINADO;
        var _pagina = 0;
        var i = 1;
        while (_pagina < total_) {
            _pagina = _pagina + paginado_;
            i++;
        }

        var script_ = '<div id="divPaginadoEntidad" class="text-center" > ';
        script_ += '<ul class="pagination pagination-sm pager" id ="myPagerEntidad" > ';
        script_ += '<li><a href="#" class="prev_link" onclick ="Obtener_Entidad(1, 0);" >«</a></li>';
        for (var i_ = 1; i_ < i; i_++) {
            script_ += '<li><a href="#" class="page_link active" onclick ="Obtener_Entidad(' + i_ + ', 0);" > ' + i_ + '</a></li>';
        }
        script_ += '		<li><a href="#" class="next_link" onclick ="Obtener_Entidad(' + (i - 1) + ', 0);" >»</a></li></ul></div>';
        $("#rowPaginadoEntidad").empty().append(script_);
        $("#divCantidadRegistrosEntidad")[0].innerText = "Se encontraron (" + total_ + ") registros";
    }
}

function fnCargar_Entidad(data) {
    $("#divCantidadRegistrosEntidad")[0].innerText = "Se encontraron (0) registros";
    $("#gvEntidad").bootstrapTable('load', data);
}

$("#btnGuardarEntidad").click(function () {
    //$("#modalEntidad").validationEngine();
    if ($("#modalEntidad").validar())
        bootbox.confirm("¿Estás seguro de guardar, estos resultados? ", function (result) {
            if (result) {
                $("#modalEntidad").submit();
                //fnGrabar_Entidad();
            }
        });
});

function fnGrabar_Entidad() {
    $("#FormularioGuardarEntidad").empty().append(
        '<input id="ID_ENTIDAD" name ="ID_ENTIDAD" type="hidden" value ="' + $("#ID_ENTIDAD_NUEVO").val() + '" />',
        '<input id="NOMBRE" name ="NOMBRE" type="hidden" value ="' + $("#NOMBRE_NUEVO").val() + '" />',
        '<input id="RUC" name ="RUC" type="hidden" value ="' + $("#RUC_NUEVO").val() + '" />',
        '<input id="DEPARTAMENTO" name ="DEPARTAMENTO" type="hidden" value ="' + $("#DEPARTAMENTO_NUEVO").val() + '" />',
        '<input id="PROVINCIA" name ="PROVINCIA" type="hidden" value ="' + $("#PROVINCIA_NUEVO").val() + '" />',
        '<input id="DISTRITO" name ="DISTRITO" type="hidden" value ="' + $("#DISTRITO_NUEVO").val() + '" />',
        '<input id="COD_UBIGEO" name ="COD_UBIGEO" type="hidden" value ="' + $("#DEPARTAMENTO_NUEVO").val() + $("#PROVINCIA_NUEVO").val() + $("#DISTRITO_NUEVO").val() + '" />',
        '<input id="ORDEN" name ="ORDEN" type="hidden" value ="' + $("#ORDEN_NUEVO").val() + '" />',
        '<input id="ES_MUNICIPALIDAD" name ="ES_MUNICIPALIDAD" type="hidden" value ="' + $("#ES_MUNICIPALIDAD").val() + '" />',
    ).submit();
}

$('#DEPARTAMENTO_NUEVO').change(function () {
    $("#PROVINCIA_NUEVO").empty().append("<option value='0'>SELECCIONE</option>");
    var OBJ = new Object;
    OBJ.CONDICION = 2;
    OBJ.COD_UBIGEO_DEPARTAMENTO = fnGetTexto($('#DEPARTAMENTO_NUEVO').val());
    OBJ.COD_UBIGEO_PROVINCIA = "01";

    $.ajax({
        type: "POST",
        url: '/Configuracion/AdminEntidad/ListarUbigeo',
        data: { models: OBJ },
        dataType: "json",
        success: function (data) {
            if (data != null)
                if (data != undefined)
                    fnCargarProvincia(data);
        }
    });
});

$("#PROVINCIA_NUEVO").change(function () {
    $("#DISTRITO_NUEVO").empty().append("<option value='0'>SELECCIONE</option>");
    var OBJ = new Object;
    OBJ.CONDICION = 3;
    OBJ.COD_UBIGEO_DEPARTAMENTO = fnGetTexto($('#DEPARTAMENTO_NUEVO').val());
    OBJ.COD_UBIGEO_PROVINCIA = fnGetTexto($('#PROVINCIA_NUEVO').val());;

    $.ajax({
        type: "POST",
        url: '/Configuracion/AdminEntidad/ListarUbigeo',
        data: { models: OBJ },
        dataType: "json",
        success: function (data) {
            if (data != null)
                if (data != undefined)
                    fnCargarDistrito(data);
        }
    });
});


function fnCargarDistrito(data) {
    $("#DISTRITO_NUEVO").empty().append("<option value='0'>SELECCIONE</option>");

    for (var i = 0; i < data.length; i++) {
        if (data[i].Text != "SELECCIONE") {
            $("#DISTRITO_NUEVO").append("<option value='" + data[i].COD_UBIGEO + "'>" + data[i].DISTRITO + "</option>");
        }
    }
}

function fnCargarProvincia(data) {
    $("#PROVINCIA_NUEVO").empty().append("<option value='0'>SELECCIONE</option>");

    for (var i = 0; i < data.length; i++) {
        if (data[i].Text != "SELECCIONE") {
            $("#PROVINCIA_NUEVO").append("<option value='" + data[i].COD_UBIGEO + "'>" + data[i].PROVINCIA + "</option>");
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