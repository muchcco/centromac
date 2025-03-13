$(document).ready(function () {
    $('#btnMenu-Configuración').removeClass('nav-link');
    $('#btnMenu-Configuración').addClass('activo');

    $("#gvSede").bootstrapTable({
        cache: false,
        search: false,
        pagination: false
    }).bootstrapTable('resetView');
});

window.operateEvents = {
    'click .gvSede-ibtnEliminar': function (e, value, row, index) {
        bootbox.confirm("¿Estás seguro de eliminar? ", function (result) {
            if (result) {
                Obtener_Sede(1, row["ID_SEDE"]);
            }
        });
    },
    'click .gvSede-ibtnEditar': function (e, value, row, index) {
        showDialog_Sede(row["ID_SEDE"], 'Edición de Sede');
    },
}

$("#btnBuscarSede").click(function () {
    Obtener_Sede(1, 0);
});

$("#btnNuevoSede").click(function () {
    showDialog_Sede(0, 'Nueva Sede');
});

function gvSedeNro(value, row, index) {
    return row["FILA"];
}

function operateFormatter_Sede(value, row, index) {
    return [
        '<a class="table-opciones espacio gvSede-ibtnEditar" href="javascript:void(0)" title="Editar Sede" ><span class="glyphicon glyphicon-pencil"></span></a>&nbsp;&nbsp;',
        '<a class="table-opciones espacio gvSede-ibtnEliminar" href="javascript:void(0)" title ="Eliminar Sede" ><span class="glyphicon glyphicon-trash" ></span></a>&nbsp;&nbsp;',

    ].join('');
}

function showDialog_Sede(ID_SEDE, titulo) {

    var FormulariosPopupInstance = new BootstrapDialog({
        title: '<b></b>',
        message: $('<div></div>'),
        size: BootstrapDialog.SIZE_WIDE,
        closable: false,
        draggable: true
    });

    var url = '/Configuracion/AdminSede/nuevaSede?ID_SEDE=' + ID_SEDE;

    var message = $('<div></div>').load(url);
    FormulariosPopupInstance.setTitle(titulo);
    FormulariosPopupInstance.setMessage(message);
    FormulariosPopupInstance.open();
}

function Obtener_Sede(numeroPagina, ID_SEDE) {
    $("#rowPaginadoSede").empty();
    $("#intNumeroPaginaSede").val(numeroPagina);

    var OBJ = new Object;
    OBJ.ID_SEDE = ID_SEDE;
    OBJ.ID_ENTIDAD = $("#ID_ENTIDAD_SEDE").val();
    OBJ.NOMBRE = $("#NOMBRE").val();
    OBJ.intNumeroPaginaSede = numeroPagina;

    $("#gvSede").bootstrapTable('showLoading');
    var ID_SEDE_ = ID_SEDE;

    $.ajax({
        type: "POST",
        url: '/Configuracion/AdminSede/ListarSede',
        data: { models: OBJ },
        dataType: "json",
        success: function (data) {
            var _ID_SEDE = ID_SEDE_;
            if (data != null) {
                if (data != undefined) {
                    $("#gvSede").bootstrapTable('hideLoading');
                    fnCargar_Sede(data);
                    fnPaginacion_Sede(data);

                    if (_ID_SEDE > 0)
                        bootbox.alert('Eliminado correctamente.');
                }
            }
        }
    });
}

function fnPaginacion_Sede(data) {
    if (data.length > 0) {
        var total_ = data[0].TOTAL;
        var paginado_ = data[0].PAGINADO;
        var _pagina = 0;
        var i = 1;
        while (_pagina < total_) {
            _pagina = _pagina + paginado_;
            i++;
        }

        var script_ = '<div id="divPaginadoSede" class="text-center" > ';
        script_ += '<ul class="pagination pagination-sm pager" id ="myPagerSede" > ';
        script_ += '<li><a href="#" class="prev_link" onclick ="Obtener_Sede(1, 0);" >«</a></li>';
        for (var i_ = 1; i_ < i; i_++) {
            script_ += '<li><a href="#" class="page_link active" onclick ="Obtener_Sede(' + i_ + ', 0);" > ' + i_ + '</a></li>';
        }
        script_ += '		<li><a href="#" class="next_link" onclick ="Obtener_Sede(' + (i - 1) + ', 0);" >»</a></li></ul></div>';
        $("#rowPaginadoSede").empty().append(script_);
        $("#divCantidadRegistrosSede")[0].innerText = "Se encontraron (" + total_ + ") registros";
    }
}

function fnCargar_Sede(data) {
    $("#divCantidadRegistrosSede")[0].innerText = "Se encontraron (0) registros";
    $("#gvSede").bootstrapTable('load', data);
}

$("#btnGuardarSede").click(function () {
    $("#modalSede").validationEngine();
    if ($("#modalSede").validar())
        bootbox.confirm("¿Estás seguro de guardar, estos resultados? ", function (result) {
            if (result) {
                fnGrabar_Sede();
            }
        });
});

function fnGrabar_Sede() {
    $("#FormularioGuardarSede").empty().append(
        '<input id="ID_SEDE" name ="ID_SEDE" type="hidden" value ="' + $("#ID_SEDE_NUEVO").val() + '" />',
        '<input id="ID_ENTIDAD" name ="ID_ENTIDAD" type="hidden" value ="' + $("#ID_ENTIDAD_SEDE_NUEVO").val() + '" />',
        '<input id="DEPARTAMENTO" name ="DEPARTAMENTO" type="hidden" value ="' + $("#DEPARTAMENTO_NUEVO").val() + '" />',
        '<input id="PROVINCIA" name ="PROVINCIA" type="hidden" value ="' + $("#PROVINCIA_NUEVO").val() + '" />',
        '<input id="DISTRITO" name ="DISTRITO" type="hidden" value ="' + $("#DISTRITO_NUEVO").val() + '" />',
        '<input id="NOMBRE" name ="NOMBRE" type="hidden" value ="' + $("#NOMBRE_NUEVO").val() + '" />',        
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