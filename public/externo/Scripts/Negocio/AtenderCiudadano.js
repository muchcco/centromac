$(document).ready(function () {

    
    $('#btnMenu-Atenciones').removeClass('nav-link');
    
    $('#btnMenu-Atenciones').addClass('activo');

    var arrSrv = new Object;
    setTimeout(function () {
        $('#btnBuscarServicioCiudadano').click(function () {
            fnBuscarGetObjetoServicio(1);
        });

       




        /*
        $('.btn-registro').click(function () {            
            var arrId = this.id.split('_');
            var IdServicio = (arrId.length == 2 ? arrId[1].trim() : '0');
            var idEnt = parseInt(this.id.substring(this.id.length - 4));
            var DNI = $('#DNI_CIUDADANO').val();
            var NOMBRE = fnCamposVacios($('#NOMBRE_CIUDADANO').val());
            var APELLIDO_PATERNO = fnCamposVacios($('#APELLIDO_PATERNO_CIUDADANO').val());
            var APELLIDO_MATERNO = fnCamposVacios($('#APELLIDO_MATERNO_CIUDADANO').val());
            var hddWPV = $('#hddWPV').val();
            var hddWPV = $('#hddWPV').val();
            $('#hddIDENTIDAD').val(idEnt);

            var OBJ = new Object;
            OBJ.SERVICIO = "";
            OBJ.DNI = DNI;
            OBJ.NOMBRE = NOMBRE;
            OBJ.APELLIDO_PATERNO = APELLIDO_PATERNO;
            OBJ.APELLIDO_MATERNO = APELLIDO_MATERNO;
            OBJ.ACCION = hddWPV;
            OBJ.intNumeroPaginaAtencion = 1;
  
            $.ajax({
                type: "GET",
                url: '/AtencionCiudadano/Servicios/ListaServiciosEntidad',
                data: { models: OBJ, strNombreServicio: "", intPagina: 1, strTipo: 0, intEntidad: idEnt },
                dataType: "json",
                success: function (data) {
                    if (data != null) {
                        fnListarServiciosPorEntidad(data, idEnt);
                    }
                }
            });

        });
        */

        $('.cssServicioLink').click(function () {
            var arrId = this.id.split('_');
            var IdServicio = (arrId.length == 2 ? arrId[1].trim() : '0');
            var DNI = $('#DNI_CIUDADANO').val();
            var NOMBRE = fnCamposVacios($('#NOMBRE_CIUDADANO').val());
            var APELLIDO_PATERNO = fnCamposVacios($('#APELLIDO_PATERNO_CIUDADANO').val());
            var APELLIDO_MATERNO = fnCamposVacios($('#APELLIDO_MATERNO_CIUDADANO').val());
            var hddWPV = $('#hddWPV').val();

            var strServicio = $(this).html().replace('<b>', '').replace('</b>', '').replace('.', '') + ".";
            bootbox.confirm("¿Está seguro de continuar?<br>Tramite: " + strServicio, function (result) {
                if (result) {
                    var url = '/AtencionCiudadano/Servicios/nuevaAtencion?Id=' + IdServicio + '&&DNI=' + DNI + '&&NOMBRE=' + NOMBRE + '&&APELLIDO_PATERNO=' + APELLIDO_PATERNO + '&&APELLIDO_MATERNO=' + APELLIDO_MATERNO + '&&WPV=' + hddWPV;
                    showDialog_ServicioCiudadano(url);
                }
            });
        });

    }, 500);

});

function fnAbrirDialogo(intIdSrv, strServicio, intModoServicio,nvUrl) {
    
    var IdServicio = intIdSrv;
    var DNI = $('#DNI_CIUDADANO').val();
    var NOMBRE = fnCamposVacios($('#NOMBRE_CIUDADANO').val());
    var APELLIDO_PATERNO = fnCamposVacios($('#APELLIDO_PATERNO_CIUDADANO').val());
    var APELLIDO_MATERNO = fnCamposVacios($('#APELLIDO_MATERNO_CIUDADANO').val());
    var hddWPV = $('#hddWPV').val();
    
   
    if (intModoServicio == 3) {
        var url = '/AtencionCiudadano/Servicios/inicioAtencion?Id=' + IdServicio + '&&DNI=' + DNI + '&&NOMBRE=' + NOMBRE + '&&APELLIDO_PATERNO=' + APELLIDO_PATERNO + '&&APELLIDO_MATERNO=' + APELLIDO_MATERNO + '&&WPV=' + hddWPV;
        $.ajax({
            type: "GET",
            url: url,
            dataType: "json",
            success: function (data) {
                $("#popup3 .close").click();
                
              //bootbox.alert("<b>Cerrar Trámite:</b> <br/>" + strServicio, function () {
              //    var urlR = '/AtencionCiudadano/Servicios/TerminarAtencion?IdAtencion=' + data;
              //    $.ajax({
              //        type: "GET",
              //        url: urlR,
              //        dataType: "json"
              //    });

                  
              //});

                bootbox.dialog({
                    title: 'Atención en curso',
                    message: strServicio + '<br/>¿El trámite fue concluido satisfactoriamente?',
                    size: 'large',
                    buttons: {
                       'Cerrar Trámite Incompleto': {
                            label: "&nbsp;&nbsp;&nbsp;SI&nbsp;&nbsp;&nbsp;",
                            className: 'btn-success',
                            callback: function () {
                         
                                var urlR = '/AtencionCiudadano/Servicios/TerminarAtencionCompleto?IdAtencion=' + data;
                                $.ajax({
                                    type: "GET",
                                    url: urlR,
                                    dataType: "json"
                                });
                                $('#NOMBRE_SERVICIO_CIUDADANO').val('');
                            }
                        },
                        'Cerrar Trámite Completo': {
                            label: "&nbsp;&nbsp;&nbsp;NO&nbsp;&nbsp;&nbsp;",
                            className: 'btn-warning',
                            callback: function () {

                                var urlR = '/AtencionCiudadano/Servicios/TerminarAtencionIncompleto?IdAtencion=' + data;
                                $.ajax({
                                    type: "GET",
                                    url: urlR,
                                    dataType: "json"
                                });
                                $('#NOMBRE_SERVICIO_CIUDADANO').val('');
                            }
                        }

                    }
                });

                window.open(nvUrl,'_new');

            }
        });


    }
    else { 
        var url2 = '/AtencionCiudadano/Servicios/nuevaAtencion?Id=' + IdServicio + '&&DNI=' + DNI + '&&NOMBRE=' + NOMBRE + '&&APELLIDO_PATERNO=' + APELLIDO_PATERNO + '&&APELLIDO_MATERNO=' + APELLIDO_MATERNO + '&&WPV=' + hddWPV;

        showDialog_ServicioCiudadano(url2);
    }
  /*  bootbox.confirm("¿Está seguro de continuar?<br>Tramite: " + strServicio, function (result) {
        if (result) {*/
            //var url = '/AtencionCiudadano/Servicios/nuevaAtencion?Id=' + IdServicio + '&&DNI=' + DNI + '&&NOMBRE=' + NOMBRE + '&&APELLIDO_PATERNO=' + APELLIDO_PATERNO + '&&APELLIDO_MATERNO=' + APELLIDO_MATERNO + '&&WPV=' + hddWPV;
            //showDialog_ServicioCiudadano(url);
     /*   }
    });*/
}

function fnListarServiciosPorEntidad(lista, iEntidad, DNI) {

    var strSeparador0 = "";
    var strSeparador1 = "";
    var strSeparador2 = "";
    var strListaServicio = "";
    var strListaServicio1 = "";
    var arrSERVICIO = [];
    var arrSERVICIO2 = [];
    var arrSERVICIO3 = [];
    var strClase = "";
    var strNombreEntidad = "";
    strSeparador0 = lista.SEPARADOR[0];
    strSeparador1 = lista.SEPARADOR[lista.SEPARADOR.length - 2];
    strSeparador2 = lista.SEPARADOR[lista.SEPARADOR.length - 1];
    arrSERVICIO = lista.SERVICIO.split(strSeparador0);
    arrSERVICIO2 = arrSERVICIO[0].split(strSeparador2);
    strNombreEntidad = arrSERVICIO2[1];
    strClase = arrSERVICIO2[3];

    strListaServicio1 = `<div class="form-horizontal" style="height:auto">
                        <div class="form-row" style="display:flex; align-items:center; margin-bottom:10px; justify-content:space-around; flex-wrap:wrap; padding-left:15px; padding-right:15px;"> `;
    
    for (var p = 0; p < arrSERVICIO.length; p++) {
        arrSERVICIO2 = arrSERVICIO[p].split(strSeparador2);
        arrSERVICIO3 = arrSERVICIO2[5].split(strSeparador1);
        var url = arrSERVICIO2[9];
        var dniUrl = arrSERVICIO2[11];
        if (dniUrl == '1') {
            url += DNI;
        }
        strListaServicio = strListaServicio +
            `<div style="margin-bottom:20px;"><a href="#">
                            <button class="btn-det-servicio" style="font-size:1rem;" onclick="fnAbrirDialogo(` + arrSERVICIO3[1] + `,'` + arrSERVICIO2[7] + `','` + arrSERVICIO2[10]+`','` + url + `');">`+
            (arrSERVICIO2[6] == '1' ? `<i class="fas fa-adjust" style="color: #BF0909; font-size:2rem;"></i>` : (arrSERVICIO2[6] == '2' ? `<i class="fas fa-check-circle" style="color: #00A65A; font-size:2rem;"></i>` : ` `)) 
            +'<br/>'+ arrSERVICIO2[7] + `</button></a></div>`;
    }
    strListaServicio = strListaServicio1 + strListaServicio + `</div></div>`;
    if (strListaServicio != '')
        $('#divInterno').html(strListaServicio);
    showDialog_ServiciosEntidad(strListaServicio, strClase, strNombreEntidad);
}

function digitar(number) {

    var valBusqueda = $("#DNI_BUSQUEDA").val();
    if (valBusqueda.length < 8) {
        $("#DNI_BUSQUEDA").val(valBusqueda + number);
    }
}
function LimpiarDigitos() {
    $("#DNI_BUSQUEDA").val('');
}

function fnListarControlesAtencion(condicion) {
    if (condicion == 1) {
        $('#divContenedor').empty().append(
            `<div class="form-horizontal">
                <div class="panel-default">
                    <img src="../Images/mac-v.jpg" class="mostrar-screen" style="width: 15%; height: 15%; position: absolute; top: 80px; left: 60px;" />
                    <row><h5><center>Ingrese el dni del ciudadano al que desea atender</center></h5>
                    </row>
                    </br>
                    <div class="d-flex justify-content-center">
                        <div class="containerTeclado"><input style="text-align:center; " class="form-control buscador validate[required, custom[onlyNumberSp]]" data-val="true" id="DNI_BUSQUEDA" maxlength="8" name="DNI" placeholder="Buscar DNI" type="text" value="">
                        </div>                     
                    </div>  
                    <div class="d-flex justify-content-center" style="margin:0;">
                        <img src="../Images/loading.gif" id="imgLoading" />
                    </div> 

                </div>
                <div class="col-12 d-flex justify-content-center px-0">
                  <div class="containerTeclado" style="float:left;" id="teclado">
                                                <span class="btn-Number" onclick="digitar(7)">7</span>
                                                <span class="btn-Number" onclick="digitar(8)">8</span>
                                                <span class="btn-Number" onclick="digitar(9)">9</span>          
                                                <span class="btn-Number" onclick="digitar(4)">4</span>
                                                <span class="btn-Number" onclick="digitar(5)">5</span>
                                                <span class="btn-Number" onclick="digitar(6)">6</span>
                                                <span class="btn-Number" onclick="digitar(1)">1</span>
                                                <span class="btn-Number" onclick="digitar(2)">2</span>
                                                <span class="btn-Number" onclick="digitar(3)">3</span>
                                                <span class="btn-Number" onclick="digitar(0)">0</span>
                                                <span class="btn-Number2" onclick="LimpiarDigitos()" value="9">&nbsp;Borrar&nbsp;</span>
                      </div>  
                   
                </div>  
<div class="d-flex justify-content-center mt-4">
<div class="containerTeclado mb-4">
    <button id="btnBuscarDniCiudadano"  type="button" class="btn-sucess col-lg-12 py-3"><i class="fas fa-search"></i> Buscar</button>
</div>
</div>
            </div>`
        );

       
        $('#DNI_BUSQUEDA').filter_input_solonumeros();

        $('#DNI_BUSQUEDA').on('keypress', function (e) {
            if (e.which === 13) {

                $('#btnBuscarDniCiudadano').click();
            }
        });

        $('#imgLoading').hide();

        $('#DNI_BUSQUEDA').focus();

        $('#btnBuscarDniCiudadano').click(function () {
            $('#imgLoading').show();
            var OBJ = new Object;
            OBJ.DNI = $("#DNI_BUSQUEDA").val();

            if (OBJ.DNI.length != 8) 
                alert('Ingrese un número de DNI correcto');

            if (OBJ.DNI != undefined)
                if (OBJ.DNI.length == 8) {
                    $.ajax({
                        type: "POST",
                        url: '/AtencionCiudadano/AtenderCiudadano/ListarCiudadano',
                        data: { models: OBJ },
                        dataType: "json",
                        success: function (data) {
                            if (data != null) {

                                if (data != undefined) {
                                    $('#imgLoading').hide();
                                    var strSRCFoto = '';
                                    var strDNI = '';
                                    if (data.FOTO != undefined)
                                        if (data.FOTO != '')
                                            strSRCFoto =  data.FOTO;

                                    $('#divCabecera').empty();
                                    $('#divContenedor').empty().append(
                                        `<div class="form-horizontal">
                                    <!--<div class="col-lg-2"></div>-->
                                    <div class="col-lg-12">
                                        <div class="panel panel-default">
                                            <h1 class="bg-titulo-reclamo">Datos del Ciudadano</h1>
                                            <div  id="collapseDatos">
                                                <div class="panel-body" >
                                                    <div class="form-horizontal">
                                                        <div class="form-group" style="margin-bottom: 10px;">
                                
                                                             <div class="col-12 col-md-4 center-block">
                                                                <div class="col-md-1 offset-md-1"></div>
                                                                <div class="col-md-11 text-center pt-3">` +
                                        (strSRCFoto == '' ? `<i class="fas fa-user-plus" style="color: #3c4145; font-size: 180px"></i>` : `<input type="hidden" id="UrlFoto" value="` + strSRCFoto + `"/><img id="imgAtencionFotos" src="` + strSRCFoto + `" class="img-bordered" alt="User Image" style="width: 220px; height: 300px;">`)
                                                                + `</div>
                                                            </div>
                                                            <div class="col-12 col-md-8 center-block ">
                                                            <br/>
                                                                <div class="form-horizontal center-block ">
                                                                    <div class="row form-group" style="margin-bottom: 10px;">
                                                                        <div class="col-md-5">
                                                                            <label class="control-label" for="DNI">DNI</label>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <input class="form-control input-lg validate[required]" id="DNI" name="DNI" readonly="readonly" type="text" value="` + data.DNI + `"  maxlength = "8"/>
                                                                        </div>
                                                                        <div class="col-md-1">
                                                                            
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="form-horizontal">
                                                                    <div class="row form-group" style="margin-bottom: 10px;">
                                                                        <div class="col-md-5">
                                                                            <label class="control-label" for="NOMBRE">NOMBRE</label>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <input class="form-control input-lg validate[required]" id="NOMBRE" name="NOMBRE" ` + (data.NOMBRE != '' ? `readonly="readonly"` : '') + ` type="text" value="` + data.NOMBRE + `" maxlength = "249"/>
                                                                          </div>
                                                                        <div class="col-md-1">
                                                                            
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="form-horizontal">
                                                                    <div class="row form-group" style="margin-bottom: 10px;">
                                                                        <div class="col-md-5">
                                                                            <label class="control-label" for="APELLIDO_PATERNO">APELLIDO PATERNO</label>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <input class="form-control input-lg validate[required]" id="APELLIDO_PATERNO" name="APELLIDO_PATERNO" ` + (data.APELLIDO_PATERNO != '' ? `readonly="readonly"` : '') + ` type="text" value="` + data.APELLIDO_PATERNO + `"  maxlength = "249"/>
                                                                          </div>
                                                                        <div class="col-md-1 offset-lg-1">
                                                                            
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="form-horizontal">
                                                                    <div class="row form-group" style="margin-bottom: 10px;">
                                                                        <div class="col-md-5">
                                                                            <label class="control-label" for="APELLIDO_MATERNO">APELLIDO MATERNO</label>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <input class="form-control input-lg validate[required]" id="APELLIDO_MATERNO" name="APELLIDO_MATERNO" ` + (data.APELLIDO_MATERNO != '' ? `readonly="readonly"` : '') + ` type="text" value="` + data.APELLIDO_MATERNO + `"  maxlength = "249"/>
                                                                          </div>
                                                                        <div class="col-md-1"></div>
                                                                    </div>
                                                                </div>                                                                
                                                                <div class="form-horizontal">
                                                                    <div class="row form-group" style="margin-bottom: 10px;">
                                                                        <div class="col-md-5">
                                                                            <label class="control-label" for="CORREO">CORREO</label>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <input class="form-control input-lg" id="CORREO" name="CORREO" type="text" value="" />
                                                                        </div>
                                                                        <div class="col-md-1"></div>
                                                                    </div>
                                                                </div>
                                                                <div class="form-horizontal">
                                                                    <div class="row form-group" style="margin-bottom: 10px;">
                                                                        <div class="col-md-5">
                                                                            <label class="control-label" for="TELEFONO">TELEFONO</label>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <input class="form-control input-lg" id="TELEFONO" name="TELEFONO" type="text" value="" />
                                                                        </div>
                                                                        <div class="col-md-1"></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3"></div>
                                </div>
                                <div class="form-horizontal">
                                    <div class="col-lg-12 text-center">
                                        <buttom id="btnIniciarAtencionTramite" class="btn btn-sucess"
                                           title="Iniciar atención del ciudadano">
                                            <span class="glyphicon glyphicon-th-list"></span>&nbsp;&nbsp; Iniciar Atención por Trámite
                                        </buttom>
                                        <a class="btn btn-gris" 
                                            href="/AtencionCiudadano/AtenderCiudadano" 
                                            title="Volver a Buscar nueva Persona">
                                            <span class="glyphicon glyphicon-repeat"></span>&nbsp;&nbsp; Volver a Buscar
                                        </a>
                                    </div>
                                </div>
                                `
                                    );

                                    $('#btnIniciarAtencionTramite').click(function () {

                                        if (($('#DNI').val() == '') || ($('#NOMBRE').val() == '') || ($('#APELLIDO_PATERNO').val() == '') || ($('#APELLIDO_MATERNO').val() == '')) {
                                            if ($('#DNI').val() == '') {
                                                alert('Falta ingresar el campo DNI');
                                                $('#DNI').focus();
                                            }

                                            else if ($('#NOMBRE').val() == '') {
                                                alert('Falta ingresar el campo Nombre');
                                                $('#NOMBRE').focus();
                                            }

                                            else if ($('#APELLIDO_PATERNO').val() == '') {
                                                alert('Falta ingresar el campo apellido paterno');
                                                $('#APELLIDO_PATERNO').focus();
                                            }

                                            else if ($('#APELLIDO_MATERNO').val() == '') {
                                                alert('Falta ingresar el campo apellido materno');
                                                $('#APELLIDO_MATERNO').focus();
                                            }                                                
                                        }
                                        else {
                                            $("#FormularioAtenderCiudadano").empty().append(
                                                '<input id="wpv" name ="wpv" type="hidden" value ="1" />',
                                                '<input id="DNI" name ="DNI" type="hidden" value ="' + $('#DNI').val() + '" />',
                                                '<input id="NOMBRE" name ="NOMBRE" type="hidden" value ="' + $('#NOMBRE').val() + '" />',
                                                '<input id="APELLIDO_PATERNO" name ="APELLIDO_PATERNO" type="hidden" value ="' + $('#APELLIDO_PATERNO').val() + '" />',
                                                '<input id="APELLIDO_MATERNO" name ="APELLIDO_MATERNO" type="hidden" value ="' + $('#APELLIDO_MATERNO').val() + '" />',
                                                '<input id="FOTO" name ="FOTO" type="hidden" value ="' + $('#UrlFoto').val() + '" />',
                                                '<input id="CORREO" name ="CORREO" type="hidden" value ="' + $('#CORREO').val() + '" />',
                                                '<input id="TELEFONO" name ="TELEFONO" type="hidden" value ="' + $('#TELEFONO').val() + '" />',                                                                                               
                                                '<input id="EVENTO" name ="EVENTO" type="hidden" value ="IniciarAtencionTramite" />'
                                            ).submit();
                                        }                                        
                                    });
                                }
                            }
                        }
                    });
                }
        });

        
    }
}


function fnInputSearch() {
    var OBJ = new Object;
    OBJ.SERVICIO = "";
    OBJ.DNI = $('#DNI_CIUDADANO').val();
    OBJ.NOMBRE = $('#NOMBRE_CIUDADANO').val();
    OBJ.APELLIDO_PATERNO = $('#APELLIDO_PATERNO_CIUDADANO').val();
    OBJ.APELLIDO_MATERNO = $('#APELLIDO_MATERNO_CIUDADANO').val();
    OBJ.ACCION = $('#hddWPV').val();
    OBJ.intNumeroPaginaAtencion = 1;

    $.ajax({
        type: "POST",
        url: '/AtencionCiudadano/Servicios/AutocompletarServicios',
        data: { models: OBJ },
        dataType: "json",
        success: function (data) {
            var result = [];

            for (var i = 0; i < data.length; i++){
                var _url = data[i].SERVICIO_LINK;
                if (data[i].DNI_URL == '1')
                    _url += OBJ.DNI;
                result.push({ id: data[i].ID_SERVICIO, label: data[i].SERVICIO_DESCRIPCION, category: data[i].ENTIDAD_NOMENCLATURA, ModoServicio: data[i].ID_MODOSERVICIO, url: _url  });

            }


            if (result != null) {

                /*AUTOCOMPLETAR*/
                $.widget("custom.catcomplete", $.ui.autocomplete, {
                    _create: function () {
                        this._super();
                        this.widget().menu("option", "items", "> :not(.ui-autocomplete-category)");
                    },
                    _renderMenu: function (ul, items) {
                        var that = this,
                            currentCategory = "";
                        $.each(items, function (index, item) {
                            var li;
                            if (item.category != currentCategory) {
                                ul.append("<b>" + item.category + "</b>");
                                currentCategory = item.category;
                            }
                            li = that._renderItemData(ul, item);
                            if (item.category) {
                                li.attr("aria-label", item.category + " : " + item.label);
                                //li.attr("onclick", 'fnBuscarGetObjetoServicio(1);');
                                li.attr("onclick", "fnAbrirDialogo(" + item.id + ",'" + item.label + "'," + item.ModoServicio + ",'" + item.url + "');");
                                
                                
                            }
                        });
                    }
                });

             

                $("#NOMBRE_SERVICIO_CIUDADANO").catcomplete({
                    delay: 0,
                    source: result
                });
                /**/




                //setTimeout(function () {



                //    $('#btnBuscarServicioCiudadano').click(function () {
                //        fnBuscarGetObjetoServicio(1);
                //    });
                //}, 500);
            }
        }
    });
}

function fnBuscarGetObjetoServicio(numeroPagina) {
    var OBJ = new Object;
    OBJ.SERVICIO = $('#NOMBRE_SERVICIO_CIUDADANO').val();
    OBJ.DNI = $('#DNI_CIUDADANO').val();
    OBJ.NOMBRE = $('#NOMBRE_CIUDADANO').val();
    OBJ.APELLIDO_PATERNO = $('#APELLIDO_PATERNO_CIUDADANO').val();
    OBJ.APELLIDO_MATERNO = $('#APELLIDO_MATERNO_CIUDADANO').val();
    OBJ.ACCION = $('#hddWPV').val();
    OBJ.intNumeroPaginaAtencion = numeroPagina;

    $.ajax({
        type: "POST",
        url: '/AtencionCiudadano/Servicios/ListarServicios',
        data: { models: OBJ },
        dataType: "json",
        success: function (data) {
            
            if (data != null) {
                fnGetObjetoServicio($('#hddWPV').val(), data.SERVICIO, data.SEPARADOR[0], data.SEPARADOR[data.SEPARADOR.length - 2], data.SEPARADOR[data.SEPARADOR.length - 1], data.DNI, data.NOMBRE, data.APELLIDO_PATERNO, data.APELLIDO_MATERNO, data.TOTAL, data.PAGINADO);
             
                //fnGetObjetoServicio_COMBO($('#hddWPV').val(), data.SERVICIO_COMBO, data.SEPARADOR[0], data.SEPARADOR[data.SEPARADOR.length - 2], data.SEPARADOR[data.SEPARADOR.length - 1], data.DNI, data.NOMBRE, data.APELLIDO_PATERNO, data.APELLIDO_MATERNO, data.TOTAL, data.PAGINADO);



                setTimeout(function () {
                


                    $('#btnBuscarServicioCiudadano').click(function () {
                        fnBuscarGetObjetoServicio(1);
                    });
                }, 500);
            }
        }
    });
}

function fnGetObjetoServicio(wpv, strCadena, strSeparador0, strSeparador1, strSeparador2, strDNI, strNOMBRE, strAPELLIDO_PATERNO, strAPELLIDO_MATERNO, TOTAL, PAGINADO) {
    
    var strDivPaginado = '';
    var total_ = (TOTAL == null ? 0 : parseInt(TOTAL));
    PAGINADO = 12;
    var paginado_ = (PAGINADO == null ? 0 : parseInt(PAGINADO));

    var _pagina = 0; i = 1;

    while (_pagina < total_) {
        _pagina = _pagina + paginado_;
        i++;
    }

    var script = '';
    for (var i_ = 1; i_ < i; i_++) 
        script += '<li><a href="#" class="page_link active" onclick ="fnBuscarGetObjetoServicio(' + i_ + ');" > ' + i_ + '</a></li>';

    script += '<li><a href="#" class="next_link" onclick ="fnBuscarGetObjetoServicio(' + (i - 1) + ');" >»</a></li>';
  
    strDivPaginado = `<div id="rowPaginadoEntidad" class="col-md-12 d-flex justify-content-center" style="clear:both;">
                        <div id="divPaginadoSede"> 
                            <ul class="pagination pagination-sm pager" id ="myPagerSede" >                                                        
                                <li><a href="#" class="prev_link" onclick ="fnBuscarGetObjetoServicio(1);" >«</a></li>`
                                + script + 
                            `</ul>
                        </div>
                    </div>`;

    var SERVICIO = strCadena;
    var arrSERVICIO = SERVICIO.split(strSeparador0);
     
    var strEntidad = "", strServicio = "", OBJ_ENTIDAD = new Object(), OBJ_SERVICIO = new Object(), ARRA_OBJ_ENTIDAD = [];
    var indice = -1;
    /*
    for (var e = 0; e < arrSERVICIO.length; e++) {
        var strEntidadServicio = arrSERVICIO[e]
        if (strEntidadServicio != "") {
            strEntidad = "";
            strServicio = "";

            var arrEntidadServicio = strEntidadServicio.split(strSeparador1);
            if (arrEntidadServicio.length == 2) {
                strEntidad = arrEntidadServicio[0];
                strServicio = arrEntidadServicio[1];
            }

            if (strEntidad != '') {
                var arrEntidad = strEntidad.split(strSeparador2);
                if (arrEntidad.length == 6) {
                    if (arrEntidad[0] != '' && arrEntidad[1] != '' && arrEntidad[2] != '' && arrEntidad[3] != '' && arrEntidad[4] != '') {
                        OBJ_ENTIDAD = new Object();
                        indice = -1;
                        for (var _e = 0; _e < ARRA_OBJ_ENTIDAD.length; _e++) {
                            if (ARRA_OBJ_ENTIDAD[_e].ID_ENTIDAD == arrEntidad[0].trim()) {
                                OBJ_ENTIDAD = ARRA_OBJ_ENTIDAD[_e];
                                indice = _e;
                                break;
                            }
                        }

                        if (indice == -1) {
                            OBJ_ENTIDAD.ID_ENTIDAD = arrEntidad[0].trim();
                            OBJ_ENTIDAD.ENTIDAD_NOMENCLATURA = arrEntidad[1].trim();
                            OBJ_ENTIDAD.ENTIDAD_DESCRIPCION = arrEntidad[2].trim();
                            OBJ_ENTIDAD.ENTIDAD_IMAGEN = arrEntidad[3].trim();
                            OBJ_ENTIDAD.ENTIDAD_ORDEN = arrEntidad[4].trim();
                            OBJ_ENTIDAD.ANCHO_ALTO_LOGO = arrEntidad[5].trim();
                            OBJ_ENTIDAD.ARRA_SERVICIO = [];
                        }

                        if (strServicio != '') {
                            var arrServicio = strServicio.split(strSeparador2);
                            if (arrServicio.length == 5) {
                                if (arrServicio[0] != '' && arrServicio[1] != '' && arrServicio[2] != '' && arrServicio[3] != '' && arrServicio[4] != '') {
                                    OBJ_SERVICIO = new Object();
                                    OBJ_SERVICIO.ID_SERVICIO = arrServicio[0].trim();
                                    OBJ_SERVICIO.SERVICIO_TIPO = arrServicio[1].trim();
                                    OBJ_SERVICIO.SERVICIO_DESCRIPCION = arrServicio[2].trim();
                                    OBJ_SERVICIO.SERVICIO_ORDEN = arrServicio[3].trim();
                                    OBJ_SERVICIO.SERVICIO_LINK = arrServicio[4].trim();
                                    OBJ_ENTIDAD.ARRA_SERVICIO.push(OBJ_SERVICIO);

                                    if (indice == -1) {
                                        ARRA_OBJ_ENTIDAD.push(OBJ_ENTIDAD);
                                    } else {
                                        ARRA_OBJ_ENTIDAD[indice] = OBJ_ENTIDAD;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    */
    for (var e = 0; e < arrSERVICIO.length; e++) {
        OBJ_ENTIDAD = new Object();
        var strEntidadServicio1 = arrSERVICIO[e]
        var arrEntidad1 = strEntidadServicio1.split(strSeparador2);
        OBJ_ENTIDAD.ID_ENTIDAD = arrEntidad1[0].trim();
        OBJ_ENTIDAD.ENTIDAD_NOMENCLATURA = arrEntidad1[1].trim();
        OBJ_ENTIDAD.ENTIDAD_DESCRIPCION = arrEntidad1[2].trim();
        OBJ_ENTIDAD.ENTIDAD_IMAGEN = arrEntidad1[3].trim();
        OBJ_ENTIDAD.ENTIDAD_ORDEN = arrEntidad1[4].trim();
        OBJ_ENTIDAD.ANCHO_ALTO_LOGO = arrEntidad1[5].trim();
        OBJ_ENTIDAD.ARRA_SERVICIO = [];
        ARRA_OBJ_ENTIDAD.push(OBJ_ENTIDAD);
    }
 
    var strDivPorEntidad = '', strDivPorServicio = '', strDivCabecera = '', strDivLeyenda = ''; i = 0; t = 0;
    while (i < ARRA_OBJ_ENTIDAD.length) {
        if (i == 0) {
            strDivCabecera = `        <br/>
                <div class="form-horizontal">
                    <div class="form-row col-md-12">
                        <div class="col-md-3 px-2 text-left mostrar-screen">
                            <img src="../Images/mac-h.jpg" style="width: 45%; height: 45%; top: 80px; left: 20px;" />    
                        </div>
                        <div class="col-sm-12 col-md-6 px-4"> CIUDADANO: <b> `+ strNOMBRE + ' ' + strAPELLIDO_PATERNO + ' ' + strAPELLIDO_MATERNO +
                            `</b><br/>DNI: <b> ` + strDNI + `</b>
                   
                            <input id="NOMBRE_CIUDADANO" name="NOMBRE_CIUDADANO" type="hidden" value="` + strNOMBRE + `">
                            <input id="APELLIDO_PATERNO_CIUDADANO" name="APELLIDO_PATERNO_CIUDADANO" type="hidden" value="` + strAPELLIDO_PATERNO + `">
                            <input id="APELLIDO_MATERNO_CIUDADANO" name="APELLIDO_MATERNO_CIUDADANO" type="hidden" value="` + strAPELLIDO_MATERNO + `">
                        </div>
                        <div class="col-sm-12 col-md-3 px-2 text-center mt-4 btnTerminarAtencion">
                            <input id="DNI_CIUDADANO" name="DNI_CIUDADANO" type="hidden" value="` + strDNI + `">
                            <a class="btn-green" href="/AtencionCiudadano/AtenderCiudadano" title="Volver a Buscar nueva Persona">
                                        <i class="fas fa-sign-out-alt"></i> Nueva Búsqueda
                            </a>
                        </div>
                        <hr class="col-md-12 m-4">
                    </div>
                </div>

               <div class="col-12"></div>
                `;


            strDivLeyenda = `       
                <div class="form-horizontal">
                    <div class="">
                        <div class="col-sm-12 col-md-2 ml-4">
                            <table style="width: 200px">
                                <tr>
                                    <td style="width: 50px; text-align: center; padding: 5px 2px 2px 5px;">
                                        <i class="fas fa-check-circle" style="color: #00a65a; font-size: 18px"></i>
                                    </td>
                                    <td style="width: 150px; padding: 6px 2px 1px 5px;">
                                        100% en línea
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 50px; text-align: center; padding: 2px 5px 5px 5px;">
                                        <i class="fas fa-adjust" style="color: #D60A0A; font-size: 18px"></i>
                                    </td>
                                    <td style="width: 150px; padding: 3px 5px 4px 5px;">
                                        Parcial
                                    </td>
                                </tr>
                            </table>
                        </div>
                     
                        <div class="BuscadorCiudadano row col-sm-12 d-flex align-items-center" style="margin-left:15px">
            
                            <label class="control-label col-sm-5 col-md-3 py-0 label-ocultos" style="float:left;" for="SERVICIO">Servicio</label>
                      
                            <input class="form-control col-sm-7 col-md-5 mr-2 buscador-servicios" style="float:left;" id="NOMBRE_SERVICIO_CIUDADANO" name="NOMBRE_SERVICIO_CIUDADANO" placeholder="Ingrese el servicio" maxlength="100" />
                            <button id="btnBuscarServicioCiudadano" type="button" class="btn-sucess" style="height:40px; float:left;"><span class="glyphicon glyphicon-search"></span>  Buscar</button>
                        </div>
                    </div>
                </div>`;
        }
        
        if (wpv == 1) {
            if (i <= ARRA_OBJ_ENTIDAD.length) {
                if (i == 0) {
                    strDivPorEntidad = strDivPorEntidad +
                        `                    <div class="form-horizontal">
                        <div class="form-row col-lg-12 d-flex flex-wrap justify-space-between scroll-text" id="servisCiudadano" style="margin-bottom: 10px; margin-top: 10px; height: 420px!important;"> `
                };

                strDivPorEntidad = strDivPorEntidad +
                    `                    <div class="col-md-3 text-center" style="margin-bottom: 20px;">
                                <a href="#">
                                    <button class="btn-registro" data-toggle="modal" data-target="#myModalSrv" id="divBtn_` + ponerCeros(ARRA_OBJ_ENTIDAD[i].ID_ENTIDAD) + `"><img width=100 src="/Uploads/Logo/` + ARRA_OBJ_ENTIDAD[i].ENTIDAD_IMAGEN + `" /></button>
                                </a>
                            </div>`

            };
            if (i + 1 < ARRA_OBJ_ENTIDAD.length) {
                strDivPorEntidad = strDivPorEntidad +
                    `                    <div class="col-md-3 text-center" style="margin-bottom: 20px;">
                                <a href="#">
                                    <button class="btn-registro" data-toggle="modal" data-target="#myModalSrv" id="divBtn_` + ponerCeros(ARRA_OBJ_ENTIDAD[i + 1].ID_ENTIDAD) + `"><img width=100 src="/Uploads/Logo/` + ARRA_OBJ_ENTIDAD[i + 1].ENTIDAD_IMAGEN + `" /></button>
                                </a>
                            </div>`
            } else { t = 1; };
            if (i + 2 < ARRA_OBJ_ENTIDAD.length) {
                strDivPorEntidad = strDivPorEntidad +
                    `                     <div class="col-md-3 text-center" style="margin-bottom: 20px;">
                                <a href="#">
                                    <button class="btn-registro" data-toggle="modal" data-target="#myModalSrv" id="divBtn_` + ponerCeros(ARRA_OBJ_ENTIDAD[i + 2].ID_ENTIDAD) + `"><img width=100 src="/Uploads/Logo/` + ARRA_OBJ_ENTIDAD[i + 2].ENTIDAD_IMAGEN + `" /></button>
                                </a>
                            </div>`
            } else { t = 1; };
            if (i + 3 < ARRA_OBJ_ENTIDAD.length) {
                strDivPorEntidad = strDivPorEntidad +
                    `                    <div class="col-md-3 text-center" style="margin-bottom: 20px;">
                                <a href="#">
                                    <button class="btn-registro" data-toggle="modal" data-target="#myModalSrv" id="divBtn_` + ponerCeros(ARRA_OBJ_ENTIDAD[i + 3].ID_ENTIDAD) + `"><img width=100 src="/Uploads/Logo/` + ARRA_OBJ_ENTIDAD[i + 3].ENTIDAD_IMAGEN + `" /></button>
                                </a>
                            </div>`
            } else { t = 1; };
            strDivPorEntidad = strDivPorEntidad + ` <br>`;
            if (t == 1) {
                t = 0;
                strDivPorEntidad = strDivPorEntidad + ` </div > </div>`;
            };
        }
        else {
            strDivPorEntidad = strDivPorEntidad +
                `                    <div class="form-horizontal">
                        <div class="form-group" style="margin-bottom: 10px;">
                            <div class="col-lg-6">
                                <div class="card-item-servicio">
                                    <div class="card-image">
                                        <img src="`+ ARRA_OBJ_ENTIDAD[i].ENTIDAD_IMAGEN + `" width="` + ANCHO + `" height="` + ALTO + `" />
                                    </div>
                                    <div class="card-content">
                                        <div id="divListaServicio_` + i + `"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6"></div>
                        </div>
                    </div>`;
        }        

        if (wpv == 2)
            strDivPorServicio = `<div id="divPorEntidadServicio"></div>`;

        i = i + 4;
    }

    
    if (strDivPorEntidad != '' && wpv == 1) {
        $("#divPorEntidad").empty().append('<div class="panel panel-default">' + strDivCabecera + '<div class="panel-body">' + strDivLeyenda + strDivPorEntidad + strDivPaginado + '</div></div>');
        arrSrv = ARRA_OBJ_ENTIDAD;
        var strListaServicio = "";
        for (var i_ = 0; i_ < ARRA_OBJ_ENTIDAD.length; i_++) {
            strListaServicio = "";
            for (var _i = 0; _i < ARRA_OBJ_ENTIDAD[i_].ARRA_SERVICIO.length; _i++) {

                var descripcion_ = ARRA_OBJ_ENTIDAD[i_].ARRA_SERVICIO[_i].SERVICIO_DESCRIPCION;
                var arrDescripcion_ = descripcion_.split('*');
                var tipo_ = 0;

                if (arrDescripcion_.length == 1) {
                    tipo_ = 1;
                    descripcion_ = arrDescripcion_[0];
                }

                if (arrDescripcion_.length == 3) {
                    var contadorVacio = 0;
                    arrDescripcion_.forEach(descripcion => {
                        if (descripcion.length == 0)
                            contadorVacio++;
                    });

                    if (contadorVacio == 0)
                        tipo_ = 2;

                    if (contadorVacio == 2)
                        tipo_ = 3;

                    if (contadorVacio == 1) {
                        if (arrDescripcion_[0] == "")
                            tipo_ = 4;
                        if (arrDescripcion_[2] == "")
                            tipo_ = 5;
                    }
                }

                strListaServicio = strListaServicio +
                    `<div class="form-group" style="margin: 10px 0px 10px 0px;">
                            <div class="col-lg-11"><a id="linkServicio_` + ARRA_OBJ_ENTIDAD[i_].ARRA_SERVICIO[_i].ID_SERVICIO.toString() + `" class="cssServicioLink" href="#">` +
                    (tipo_ == 1 ? descripcion_ : (tipo_ == 2 ? arrDescripcion_[0] + '<b>' + arrDescripcion_[1] + '</b>' + arrDescripcion_[2] : (tipo_ == 3 ? '<b>' + arrDescripcion_[1] + '</b>' : (tipo_ == 4 ? '<b>' + arrDescripcion_[1] + '</b> ' + arrDescripcion_[2] : (tipo_ == 5 ? arrDescripcion_[0] + '<b>' + arrDescripcion_[1] + '</b>' : '')))))
                    + `</a></div>
                            <div class="col-lg-1 text-center">` + (ARRA_OBJ_ENTIDAD[i_].ARRA_SERVICIO[_i].SERVICIO_TIPO == '1' ? `<i class="fas fa-adjust" style="color: blue; font-size: 18px"></i>` : (ARRA_OBJ_ENTIDAD[i_].ARRA_SERVICIO[_i].SERVICIO_TIPO == '2' ? `<i class="fas fa-check-circle" style="color: #00a65a; font-size: 18px"></i>` : ` `)) + `</div>
                        </div>`;

            }
            if (strListaServicio != '')
                $('#divListaServicio_' + i_).append(strListaServicio);
        }
    }

    if (strDivPorServicio != '' && wpv == 2) {
        $("#divPorEntidad").empty().append('<div class="panel panel-default">' + strDivCabecera + '<div class="panel-body">' + strDivLeyenda + strDivPorServicio + strDivPaginado + '</div></div>');

        var contadorServicio = 0; totalServicio = 0, strDivPorServicio = '';
        var ARRA_SERVICIO = []; s = 0;
        ARRA_OBJ_ENTIDAD.forEach(entidad => {
            entidad.ARRA_SERVICIO.forEach(servicio => {
                ARRA_SERVICIO.push(servicio);
            });
        });

        while (s < ARRA_SERVICIO.length) {
            var descripcion_1 = ARRA_SERVICIO[s].SERVICIO_DESCRIPCION;
            var arrDescripcion_1 = descripcion_1.split('*');
            var tipo_1 = 0;

            if (arrDescripcion_1.length == 1) {
                tipo_1 = 1;
                descripcion_1 = arrDescripcion_1[0];
            }

            if (arrDescripcion_1.length == 3) {
                var contadorVacio = 0;
                arrDescripcion_1.forEach(descripcion => {
                    if (descripcion.length == 0)
                        contadorVacio++;
                });

                if (contadorVacio == 0)
                    tipo_1 = 2;

                if (contadorVacio == 2)
                    tipo_1 = 3;

                if (contadorVacio == 1) {
                    if (arrDescripcion_1[0] == "")
                        tipo_1 = 4;
                    if (arrDescripcion_1[2] == "")
                        tipo_1 = 5;
                }
            }

            if (s + 2 <= ARRA_SERVICIO.length) {
                var descripcion_2 = ARRA_SERVICIO[s + 1].SERVICIO_DESCRIPCION;
                var arrDescripcion_2 = descripcion_2.split('*');
                var tipo_2 = 0;

                if (arrDescripcion_2.length == 1) {
                    tipo_2 = 1;
                    descripcion_2 = arrDescripcion_2[0];
                }

                if (arrDescripcion_2.length == 3) {
                    var contadorVacio = 0;
                    arrDescripcion_2.forEach(descripcion => {
                        if (descripcion.length == 0)
                            contadorVacio++;
                    });

                    if (contadorVacio == 0)
                        tipo_2 = 2;

                    if (contadorVacio == 2)
                        tipo_2 = 3;

                    if (contadorVacio == 1) {
                        if (arrDescripcion_2[0] == "")
                            tipo_2 = 4;
                        if (arrDescripcion_2[2] == "")
                            tipo_2 = 5;
                    }
                }

                strDivPorServicio = strDivPorServicio +
                    `                    <div class="form-horizontal">
                        <div class="form-group" style="margin-bottom: 2px;">
                            <div class="col-lg-6">
                                <div class="form-group" style="margin: 2px;">
                                    <span class="border border-primary span-style">
                                        <div class="col-lg-11" style="margin: 25px 0px 25px 0px;"><a id="linkServicio_` + ARRA_SERVICIO[s].ID_SERVICIO.toString() + `" class="cssServicioLink" href="#">` +
                    (tipo_1 == 1 ? descripcion_1 : (tipo_1 == 2 ? arrDescripcion_1[0] + '<b>' + arrDescripcion_1[1] + '</b>' + arrDescripcion_1[2] : (tipo_1 == 3 ? '<b>' + arrDescripcion_1[1] + '</b>' : (tipo_1 == 4 ? '<b>' + arrDescripcion_1[1] + '</b> ' + arrDescripcion_1[2] : (tipo_1 == 5 ? arrDescripcion_1[0] + '<b>' + arrDescripcion_1[1] + '</b>' : '')))))
                    + `</a></div>
                                        <div class="col-lg-1 text-center" style="margin: 25px 0px 25px 0px;">` + (ARRA_SERVICIO[s].SERVICIO_TIPO == '1' ? `<i class="fas fa-adjust" style="color: blue; font-size: 18px"></i>` : (ARRA_SERVICIO[s].SERVICIO_TIPO == '2' ? `<i class="fas fa-check-circle" style="color: #00a65a; font-size: 18px"></i>` : ` `)) + `</div>
                                    </span> 
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group" style="margin: 2px;">
                                    <span class="border border-primary span-style">
                                        
                                        <div class="col-lg-11" style="margin: 25px 0px 25px 0px;"><a id="linkServicio_` + ARRA_SERVICIO[s + 1].ID_SERVICIO.toString() + `" class="cssServicioLink" href="#">` +
                    (tipo_2 == 1 ? descripcion_2 : (tipo_2 == 2 ? arrDescripcion_2[0] + '<b>' + arrDescripcion_2[1] + '</b>' + arrDescripcion_2[2] : (tipo_2 == 3 ? '<b>' + arrDescripcion_2[1] + '</b>' : (tipo_2 == 4 ? '<b>' + arrDescripcion_2[1] + '</b> ' + arrDescripcion_2[2] : (tipo_2 == 5 ? arrDescripcion_2[0] + '<b>' + arrDescripcion_2[1] + '</b>' : '')))))
                    + `</a></div>
                                        <div class="col-lg-1 text-center" style="margin: 25px 0px 25px 0px;">` + (ARRA_SERVICIO[s + 1].SERVICIO_TIPO == '1' ? `<i class="fas fa-adjust" style="color: blue; font-size: 18px"></i>` : (ARRA_SERVICIO[s + 1].SERVICIO_TIPO == '2' ? `<i class="fas fa-check-circle" style="color: #00a65a; font-size: 18px"></i>` : ` `)) + `</div>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>`;
            }
            else {
                strDivPorServicio = strDivPorServicio +
                    `                    <div class="form-horizontal">
                        <div class="form-group" style="margin: 2px;">
                            <div class="col-lg-6">
                                <div class="form-group" style="margin-bottom: 2px;">
                                    <span class="border border-primary span-style">
                                        <div class="col-lg-11" style="margin: 25px 0px 25px 0px;"><a id="linkServicio_` + ARRA_SERVICIO[s].ID_SERVICIO.toString() + `" class="cssServicioLink" href="#">` +
                    (tipo_1 == 1 ? descripcion_1 : (tipo_1 == 2 ? arrDescripcion_1[0] + '<b>' + arrDescripcion_1[1] + '</b>' + arrDescripcion_1[2] : (tipo_1 == 3 ? '<b>' + arrDescripcion_1[1] + '</b>' : (tipo_1 == 4 ? '<b>' + arrDescripcion_1[1] + '</b> ' + arrDescripcion_1[2] : (tipo_1 == 5 ? arrDescripcion_1[0] + '<b>' + arrDescripcion_1[1] + '</b>' : '')))))
                    + `</a></div>
                                        <div class="col-lg-1 text-center" style="margin: 25px 0px 25px 0px;">` + (ARRA_SERVICIO[s].SERVICIO_TIPO == '1' ? `<i class="fas fa-adjust" style="color: blue; font-size: 18px"></i>` : (ARRA_SERVICIO[s].SERVICIO_TIPO == '2' ? `<i class="fas fa-check-circle" style="color: #00a65a; font-size: 18px"></i>` : ` `)) + `</div>
                                        
                                    </span>
                                </div>
                            </div>
                            <div class="col-lg-6"></div>
                        </div>
                    </div>`;
            }
            s = s + 2;
        }

        if (strDivPorServicio != '')
            $('#divPorEntidadServicio').empty().append(strDivPorServicio);
    }

    $('.cssServicioLink').click(function () {
        var arrId = this.id.split('_');
        var IdServicio = (arrId.length == 2 ? arrId[1].trim() : '0');
        var DNI = $('#DNI_CIUDADANO').val();
        var NOMBRE = fnCamposVacios($('#NOMBRE_CIUDADANO').val());
        var APELLIDO_PATERNO = fnCamposVacios($('#APELLIDO_PATERNO_CIUDADANO').val());
        var APELLIDO_MATERNO = fnCamposVacios($('#APELLIDO_MATERNO_CIUDADANO').val());
        var hddWPV = $('#hddWPV').val();

        var strServicio = $(this).html().replace('<b>', '').replace('</b>', '').replace('.', '') + ".";
        bootbox.confirm("¿Está seguro de continuar?<br>Tramite: " + strServicio, function (result) {
            if (result) {
                var url = '/AtencionCiudadano/Servicios/nuevaAtencion?Id=' + IdServicio + '&&DNI=' + DNI + '&&NOMBRE=' + NOMBRE + '&&APELLIDO_PATERNO=' + APELLIDO_PATERNO + '&&APELLIDO_MATERNO=' + APELLIDO_MATERNO + '&&WPV=' + hddWPV;
                showDialog_ServicioCiudadano(url);
            }
        });
    });

    $('.btn-registro').click(function () {
        var arrId = this.id.split('_');
        var IdServicio = (arrId.length == 2 ? arrId[1].trim() : '0');
        var idEnt = parseInt(this.id.substring(this.id.length - 4));
        var DNI = $('#DNI_CIUDADANO').val();
        var NOMBRE = fnCamposVacios($('#NOMBRE_CIUDADANO').val());
        var APELLIDO_PATERNO = fnCamposVacios($('#APELLIDO_PATERNO_CIUDADANO').val());
        var APELLIDO_MATERNO = fnCamposVacios($('#APELLIDO_MATERNO_CIUDADANO').val());
        var hddWPV = $('#hddWPV').val();
        $('#hddIDENTIDAD').val(idEnt);

        var OBJ = new Object;
        OBJ.SERVICIO = "";
        OBJ.DNI = DNI;
        OBJ.NOMBRE = NOMBRE;
        OBJ.APELLIDO_PATERNO = APELLIDO_PATERNO;
        OBJ.APELLIDO_MATERNO = APELLIDO_MATERNO;
        OBJ.ACCION = hddWPV;
        OBJ.intNumeroPaginaAtencion = 1;

        $.ajax({
            type: "GET",
            url: '/AtencionCiudadano/Servicios/ListaServiciosEntidad',
            data: { models: OBJ, strNombreServicio: "", intPagina: 1, strTipo: 0, intEntidad: idEnt },
            dataType: "json",
            success: function (data) {
                if (data != null) {
                    fnListarServiciosPorEntidad(data, idEnt, DNI);
                }
            }
        });

    });

    fnInputSearch();
    //return ARRA_OBJ_ENTIDAD;
}
function fnGetObjetoServicio_XXX(wpv, strCadena, strSeparador0, strSeparador1, strSeparador2, strDNI, strNOMBRE, strAPELLIDO_PATERNO, strAPELLIDO_MATERNO, TOTAL, PAGINADO) {
    
    var strDivPaginado = '';
    var total_ = (TOTAL == null ? 0 : parseInt(TOTAL));
    PAGINADO = 12;
    var paginado_ = (PAGINADO == null ? 0 : parseInt(PAGINADO));

    var _pagina = 0; i = 1;

    while (_pagina < total_) {
        _pagina = _pagina + paginado_;
        i++;
    }

    var script = '';
    for (var i_ = 1; i_ < i; i_++)
        script += '<li><a href="#" class="page_link active" onclick ="fnBuscarGetObjetoServicio(' + i_ + ');" > ' + i_ + '</a></li>';

    script += '<li><a href="#" class="next_link" onclick ="fnBuscarGetObjetoServicio(' + (i - 1) + ');" >»</a></li>';

    strDivPaginado = `<div id="rowPaginadoEntidad" class="col-md-12 d-flex justify-content-center" style="clear:both;">
                        <div id="divPaginadoSede"> 
                            <ul class="pagination pagination-sm pager" id ="myPagerSede" >                                                        
                                <li><a href="#" class="prev_link" onclick ="fnBuscarGetObjetoServicio(1);" >«</a></li>`
        + script +
        `</ul>
                        </div>
                    </div>`;

    var SERVICIO = strCadena;
    var arrSERVICIO = SERVICIO.split(strSeparador0);

    var strEntidad = "", strServicio = "", OBJ_ENTIDAD = new Object(), OBJ_SERVICIO = new Object(), ARRA_OBJ_ENTIDAD = [];
    var indice = -1;

    for (var e = 0; e < arrSERVICIO.length; e++) {
        var strEntidadServicio = arrSERVICIO[e]
        if (strEntidadServicio != "") {
            strEntidad = "";
            strServicio = "";

            var arrEntidadServicio = strEntidadServicio.split(strSeparador1);
            if (arrEntidadServicio.length == 2) {
                strEntidad = arrEntidadServicio[0];
                strServicio = arrEntidadServicio[1];
            }

            if (strEntidad != '') {
                var arrEntidad = strEntidad.split(strSeparador2);
                if (arrEntidad.length == 6) {
                    if (arrEntidad[0] != '' && arrEntidad[1] != '' && arrEntidad[2] != '' && arrEntidad[3] != '' && arrEntidad[4] != '') {
                        OBJ_ENTIDAD = new Object();
                        indice = -1;
                        for (var _e = 0; _e < ARRA_OBJ_ENTIDAD.length; _e++) {
                            if (ARRA_OBJ_ENTIDAD[_e].ID_ENTIDAD == arrEntidad[0].trim()) {
                                OBJ_ENTIDAD = ARRA_OBJ_ENTIDAD[_e];
                                indice = _e;
                                break;
                            }
                        }

                        if (indice == -1) {
                            OBJ_ENTIDAD.ID_ENTIDAD = arrEntidad[0].trim();
                            OBJ_ENTIDAD.ENTIDAD_NOMENCLATURA = arrEntidad[1].trim();
                            OBJ_ENTIDAD.ENTIDAD_DESCRIPCION = arrEntidad[2].trim();
                            OBJ_ENTIDAD.ENTIDAD_IMAGEN = arrEntidad[3].trim();
                            OBJ_ENTIDAD.ENTIDAD_ORDEN = arrEntidad[4].trim();
                            OBJ_ENTIDAD.ANCHO_ALTO_LOGO = arrEntidad[5].trim();
                            OBJ_ENTIDAD.ARRA_SERVICIO = [];
                        }

                        if (strServicio != '') {
                            var arrServicio = strServicio.split(strSeparador2);
                            if (arrServicio.length == 5) {
                                if (arrServicio[0] != '' && arrServicio[1] != '' && arrServicio[2] != '' && arrServicio[3] != '' && arrServicio[4] != '') {
                                    OBJ_SERVICIO = new Object();
                                    OBJ_SERVICIO.ID_SERVICIO = arrServicio[0].trim();
                                    OBJ_SERVICIO.SERVICIO_TIPO = arrServicio[1].trim();
                                    OBJ_SERVICIO.SERVICIO_DESCRIPCION = arrServicio[2].trim();
                                    OBJ_SERVICIO.SERVICIO_ORDEN = arrServicio[3].trim();
                                    OBJ_SERVICIO.SERVICIO_LINK = arrServicio[4].trim();
                                    OBJ_ENTIDAD.ARRA_SERVICIO.push(OBJ_SERVICIO);

                                    if (indice == -1) {
                                        ARRA_OBJ_ENTIDAD.push(OBJ_ENTIDAD);
                                    } else {
                                        ARRA_OBJ_ENTIDAD[indice] = OBJ_ENTIDAD;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    } 
    var strDivPorEntidad = '', strDivPorServicio = '', strDivCabecera = '', strDivLeyenda = ''; i = 0; t = 0;
    while (i < ARRA_OBJ_ENTIDAD.length) {
        if (i == 0) {
            strDivCabecera = `        <br/>
                <div class="form-horizontal">
                    <div class="form-row col-md-12">    
                        <div class="col-lg-6 px-4"> CIUDADANO: <b> `+ strNOMBRE + ' ' + strAPELLIDO_PATERNO + ' ' + strAPELLIDO_MATERNO +
                `</b><br/>DNI: <b> ` + strDNI + `</b>
                   
                            <input id="NOMBRE_CIUDADANO" name="NOMBRE_CIUDADANO" type="hidden" value="` + strNOMBRE + `">
                            <input id="APELLIDO_PATERNO_CIUDADANO" name="APELLIDO_PATERNO_CIUDADANO" type="hidden" value="` + strAPELLIDO_PATERNO + `">
                            <input id="APELLIDO_MATERNO_CIUDADANO" name="APELLIDO_MATERNO_CIUDADANO" type="hidden" value="` + strAPELLIDO_MATERNO + `">
                        </div>
                        <div class="col-lg-6 px-2 text-right btnTerminarAtencion">
                            <input id="DNI_CIUDADANO" name="DNI_CIUDADANO" type="hidden" value="` + strDNI + `">
                            <a class="btn-green" href="/AtencionCiudadano/AtenderCiudadano" title="Volver a Buscar nueva Persona">
                                        <i class="fas fa-sign-out-alt"></i> Nueva Búsqueda
                            </a>
                        </div>
                        <hr class="col-lg-12 m-4">
                    </div>
                </div>
<br/>
               <div class="col-12"></div>
                `;


            strDivLeyenda = `       
                <div class="form-horizontal">
                    <div class="row form-group">
                        <div class="col-lg-2 ml-4">
                            <table style="width: 200px">
                                <tr>
                                    <td style="width: 50px; text-align: center; padding: 5px 2px 2px 5px;">
                                        <i class="fas fa-check-circle" style="color: #00a65a; font-size: 18px"></i>
                                    </td>
                                    <td style="width: 150px; padding: 6px 2px 1px 5px;">
                                        100% en línea
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 50px; text-align: center; padding: 2px 5px 5px 5px;">
                                        <i class="fas fa-adjust" style="color: #D60A0A; font-size: 18px"></i>
                                    </td>
                                    <td style="width: 150px; padding: 3px 5px 4px 5px;">
                                        Parcial
                                    </td>
                                </tr>
                            </table>
                        </div>
                     
                        <div class="BuscadorCiudadano d-flex align-items-center">
            
                            <label class="control-label col-12 col-lg-3 py-0 label-ocultos" style="float:left;" for="SERVICIO">Servicio</label>
                      
                            <input class="form-control col-lg-5 mr-2 buscador-servicios" style="float:left;" id="NOMBRE_SERVICIO_CIUDADANO" name="NOMBRE_SERVICIO_CIUDADANO" placeholder="Ingrese el servicio" maxlength="100" />
                            <button id="btnBuscarServicioCiudadano" type="button" class="btn-sucess" style="height:40px; float:left;"><span class="glyphicon glyphicon-search"></span>  Buscar</button>
                        </div>
                    </div>
                </div>`;
        }

        if (wpv == 1) {
            if (i <= ARRA_OBJ_ENTIDAD.length) {
                if (i == 0) {
                    strDivPorEntidad = strDivPorEntidad +
                        `                    <div class="form-horizontal">
                        <div class="form-row col-lg-12 d-flex flex-wrap justify-space-between scroll-text" id="servisCiudadano" style="margin-bottom: 10px; margin-top: 10px; height: 420px!important;"> `
                };

                strDivPorEntidad = strDivPorEntidad +
                    `                    <div class="col-md-3 text-center" style="margin-bottom: 20px;">
                                <a href="#">
                                    <button class="btn-registro" data-toggle="modal" data-target="#myModalSrv" id="divBtn_` + ponerCeros(ARRA_OBJ_ENTIDAD[i].ID_ENTIDAD) + `"><img width=100 src="/Uploads/Logo/` + ARRA_OBJ_ENTIDAD[i].ENTIDAD_IMAGEN + `" /></button>
                                </a>
                            </div>`

            };
            if (i + 1 < ARRA_OBJ_ENTIDAD.length) {
                strDivPorEntidad = strDivPorEntidad +
                    `                    <div class="col-md-3 text-center" style="margin-bottom: 20px;">
                                <a href="#">
                                    <button class="btn-registro" data-toggle="modal" data-target="#myModalSrv" id="divBtn_` + ponerCeros(ARRA_OBJ_ENTIDAD[i + 1].ID_ENTIDAD) + `"><img width=100 src="/Uploads/Logo/` + ARRA_OBJ_ENTIDAD[i + 1].ENTIDAD_IMAGEN + `" /></button>
                                </a>
                            </div>`
            } else { t = 1; };
            if (i + 2 < ARRA_OBJ_ENTIDAD.length) {
                strDivPorEntidad = strDivPorEntidad +
                    `                     <div class="col-md-3 text-center" style="margin-bottom: 20px;">
                                <a href="#">
                                    <button class="btn-registro" data-toggle="modal" data-target="#myModalSrv" id="divBtn_` + ponerCeros(ARRA_OBJ_ENTIDAD[i + 2].ID_ENTIDAD) + `"><img width=100 src="/Uploads/Logo/` + ARRA_OBJ_ENTIDAD[i + 2].ENTIDAD_IMAGEN + `" /></button>
                                </a>
                            </div>`
            } else { t = 1; };
            if (i + 3 < ARRA_OBJ_ENTIDAD.length) {
                strDivPorEntidad = strDivPorEntidad +
                    `                    <div class="col-md-3 text-center" style="margin-bottom: 20px;">
                                <a href="#">
                                    <button class="btn-registro" data-toggle="modal" data-target="#myModalSrv" id="divBtn_` + ponerCeros(ARRA_OBJ_ENTIDAD[i + 3].ID_ENTIDAD) + `"><img width=100 src="/Uploads/Logo/` + ARRA_OBJ_ENTIDAD[i + 3].ENTIDAD_IMAGEN + `" /></button>
                                </a>
                            </div>`
            } else { t = 1; };
            strDivPorEntidad = strDivPorEntidad + ` <br>`;
            if (t == 1) {
                t = 0;
                strDivPorEntidad = strDivPorEntidad + ` </div > </div>`;
            };
        }
        else {
            strDivPorEntidad = strDivPorEntidad +
                `                    <div class="form-horizontal">
                        <div class="form-group" style="margin-bottom: 10px;">
                            <div class="col-lg-6">
                                <div class="card-item-servicio">
                                    <div class="card-image">
                                        <img src="`+ ARRA_OBJ_ENTIDAD[i].ENTIDAD_IMAGEN + `" width="` + ANCHO + `" height="` + ALTO + `" />
                                    </div>
                                    <div class="card-content">
                                        <div id="divListaServicio_` + i + `"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6"></div>
                        </div>
                    </div>`;
        }

        if (wpv == 2)
            strDivPorServicio = `<div id="divPorEntidadServicio"></div>`;

        i = i + 4;
    }


    if (strDivPorEntidad != '' && wpv == 1) {
        $("#divPorEntidad").empty().append('<div class="panel panel-default">' + strDivCabecera + '<div class="panel-body">' + strDivLeyenda + strDivPorEntidad + strDivPaginado + '</div></div>');
        arrSrv = ARRA_OBJ_ENTIDAD;
        var strListaServicio = "";
        for (var i_ = 0; i_ < ARRA_OBJ_ENTIDAD.length; i_++) {
            strListaServicio = "";
            for (var _i = 0; _i < ARRA_OBJ_ENTIDAD[i_].ARRA_SERVICIO.length; _i++) {

                var descripcion_ = ARRA_OBJ_ENTIDAD[i_].ARRA_SERVICIO[_i].SERVICIO_DESCRIPCION;
                var arrDescripcion_ = descripcion_.split('*');
                var tipo_ = 0;

                if (arrDescripcion_.length == 1) {
                    tipo_ = 1;
                    descripcion_ = arrDescripcion_[0];
                }

                if (arrDescripcion_.length == 3) {
                    var contadorVacio = 0;
                    arrDescripcion_.forEach(descripcion => {
                        if (descripcion.length == 0)
                            contadorVacio++;
                    });

                    if (contadorVacio == 0)
                        tipo_ = 2;

                    if (contadorVacio == 2)
                        tipo_ = 3;

                    if (contadorVacio == 1) {
                        if (arrDescripcion_[0] == "")
                            tipo_ = 4;
                        if (arrDescripcion_[2] == "")
                            tipo_ = 5;
                    }
                }

                strListaServicio = strListaServicio +
                    `<div class="form-group" style="margin: 10px 0px 10px 0px;">
                            <div class="col-lg-11"><a id="linkServicio_` + ARRA_OBJ_ENTIDAD[i_].ARRA_SERVICIO[_i].ID_SERVICIO.toString() + `" class="cssServicioLink" href="#">` +
                    (tipo_ == 1 ? descripcion_ : (tipo_ == 2 ? arrDescripcion_[0] + '<b>' + arrDescripcion_[1] + '</b>' + arrDescripcion_[2] : (tipo_ == 3 ? '<b>' + arrDescripcion_[1] + '</b>' : (tipo_ == 4 ? '<b>' + arrDescripcion_[1] + '</b> ' + arrDescripcion_[2] : (tipo_ == 5 ? arrDescripcion_[0] + '<b>' + arrDescripcion_[1] + '</b>' : '')))))
                    + `</a></div>
                            <div class="col-lg-1 text-center">` + (ARRA_OBJ_ENTIDAD[i_].ARRA_SERVICIO[_i].SERVICIO_TIPO == '1' ? `<i class="fas fa-adjust" style="color: blue; font-size: 18px"></i>` : (ARRA_OBJ_ENTIDAD[i_].ARRA_SERVICIO[_i].SERVICIO_TIPO == '2' ? `<i class="fas fa-check-circle" style="color: #00a65a; font-size: 18px"></i>` : ` `)) + `</div>
                        </div>`;

            }
            if (strListaServicio != '')
                $('#divListaServicio_' + i_).append(strListaServicio);
        }
    }

    if (strDivPorServicio != '' && wpv == 2) {
        $("#divPorEntidad").empty().append('<div class="panel panel-default">' + strDivCabecera + '<div class="panel-body">' + strDivLeyenda + strDivPorServicio + strDivPaginado + '</div></div>');

        var contadorServicio = 0; totalServicio = 0, strDivPorServicio = '';
        var ARRA_SERVICIO = []; s = 0;
        ARRA_OBJ_ENTIDAD.forEach(entidad => {
            entidad.ARRA_SERVICIO.forEach(servicio => {
                ARRA_SERVICIO.push(servicio);
            });
        });

        while (s < ARRA_SERVICIO.length) {
            var descripcion_1 = ARRA_SERVICIO[s].SERVICIO_DESCRIPCION;
            var arrDescripcion_1 = descripcion_1.split('*');
            var tipo_1 = 0;

            if (arrDescripcion_1.length == 1) {
                tipo_1 = 1;
                descripcion_1 = arrDescripcion_1[0];
            }

            if (arrDescripcion_1.length == 3) {
                var contadorVacio = 0;
                arrDescripcion_1.forEach(descripcion => {
                    if (descripcion.length == 0)
                        contadorVacio++;
                });

                if (contadorVacio == 0)
                    tipo_1 = 2;

                if (contadorVacio == 2)
                    tipo_1 = 3;

                if (contadorVacio == 1) {
                    if (arrDescripcion_1[0] == "")
                        tipo_1 = 4;
                    if (arrDescripcion_1[2] == "")
                        tipo_1 = 5;
                }
            }

            if (s + 2 <= ARRA_SERVICIO.length) {
                var descripcion_2 = ARRA_SERVICIO[s + 1].SERVICIO_DESCRIPCION;
                var arrDescripcion_2 = descripcion_2.split('*');
                var tipo_2 = 0;

                if (arrDescripcion_2.length == 1) {
                    tipo_2 = 1;
                    descripcion_2 = arrDescripcion_2[0];
                }

                if (arrDescripcion_2.length == 3) {
                    var contadorVacio = 0;
                    arrDescripcion_2.forEach(descripcion => {
                        if (descripcion.length == 0)
                            contadorVacio++;
                    });

                    if (contadorVacio == 0)
                        tipo_2 = 2;

                    if (contadorVacio == 2)
                        tipo_2 = 3;

                    if (contadorVacio == 1) {
                        if (arrDescripcion_2[0] == "")
                            tipo_2 = 4;
                        if (arrDescripcion_2[2] == "")
                            tipo_2 = 5;
                    }
                }

                strDivPorServicio = strDivPorServicio +
                    `                    <div class="form-horizontal">
                        <div class="form-group" style="margin-bottom: 2px;">
                            <div class="col-lg-6">
                                <div class="form-group" style="margin: 2px;">
                                    <span class="border border-primary span-style">
                                        <div class="col-lg-11" style="margin: 25px 0px 25px 0px;"><a id="linkServicio_` + ARRA_SERVICIO[s].ID_SERVICIO.toString() + `" class="cssServicioLink" href="#">` +
                    (tipo_1 == 1 ? descripcion_1 : (tipo_1 == 2 ? arrDescripcion_1[0] + '<b>' + arrDescripcion_1[1] + '</b>' + arrDescripcion_1[2] : (tipo_1 == 3 ? '<b>' + arrDescripcion_1[1] + '</b>' : (tipo_1 == 4 ? '<b>' + arrDescripcion_1[1] + '</b> ' + arrDescripcion_1[2] : (tipo_1 == 5 ? arrDescripcion_1[0] + '<b>' + arrDescripcion_1[1] + '</b>' : '')))))
                    + `</a></div>
                                        <div class="col-lg-1 text-center" style="margin: 25px 0px 25px 0px;">` + (ARRA_SERVICIO[s].SERVICIO_TIPO == '1' ? `<i class="fas fa-adjust" style="color: blue; font-size: 18px"></i>` : (ARRA_SERVICIO[s].SERVICIO_TIPO == '2' ? `<i class="fas fa-check-circle" style="color: #00a65a; font-size: 18px"></i>` : ` `)) + `</div>
                                    </span> 
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group" style="margin: 2px;">
                                    <span class="border border-primary span-style">
                                        
                                        <div class="col-lg-11" style="margin: 25px 0px 25px 0px;"><a id="linkServicio_` + ARRA_SERVICIO[s + 1].ID_SERVICIO.toString() + `" class="cssServicioLink" href="#">` +
                    (tipo_2 == 1 ? descripcion_2 : (tipo_2 == 2 ? arrDescripcion_2[0] + '<b>' + arrDescripcion_2[1] + '</b>' + arrDescripcion_2[2] : (tipo_2 == 3 ? '<b>' + arrDescripcion_2[1] + '</b>' : (tipo_2 == 4 ? '<b>' + arrDescripcion_2[1] + '</b> ' + arrDescripcion_2[2] : (tipo_2 == 5 ? arrDescripcion_2[0] + '<b>' + arrDescripcion_2[1] + '</b>' : '')))))
                    + `</a></div>
                                        <div class="col-lg-1 text-center" style="margin: 25px 0px 25px 0px;">` + (ARRA_SERVICIO[s + 1].SERVICIO_TIPO == '1' ? `<i class="fas fa-adjust" style="color: blue; font-size: 18px"></i>` : (ARRA_SERVICIO[s + 1].SERVICIO_TIPO == '2' ? `<i class="fas fa-check-circle" style="color: #00a65a; font-size: 18px"></i>` : ` `)) + `</div>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>`;
            }
            else {
                strDivPorServicio = strDivPorServicio +
                    `                    <div class="form-horizontal">
                        <div class="form-group" style="margin: 2px;">
                            <div class="col-lg-6">
                                <div class="form-group" style="margin-bottom: 2px;">
                                    <span class="border border-primary span-style">
                                        <div class="col-lg-11" style="margin: 25px 0px 25px 0px;"><a id="linkServicio_` + ARRA_SERVICIO[s].ID_SERVICIO.toString() + `" class="cssServicioLink" href="#">` +
                    (tipo_1 == 1 ? descripcion_1 : (tipo_1 == 2 ? arrDescripcion_1[0] + '<b>' + arrDescripcion_1[1] + '</b>' + arrDescripcion_1[2] : (tipo_1 == 3 ? '<b>' + arrDescripcion_1[1] + '</b>' : (tipo_1 == 4 ? '<b>' + arrDescripcion_1[1] + '</b> ' + arrDescripcion_1[2] : (tipo_1 == 5 ? arrDescripcion_1[0] + '<b>' + arrDescripcion_1[1] + '</b>' : '')))))
                    + `</a></div>
                                        <div class="col-lg-1 text-center" style="margin: 25px 0px 25px 0px;">` + (ARRA_SERVICIO[s].SERVICIO_TIPO == '1' ? `<i class="fas fa-adjust" style="color: blue; font-size: 18px"></i>` : (ARRA_SERVICIO[s].SERVICIO_TIPO == '2' ? `<i class="fas fa-check-circle" style="color: #00a65a; font-size: 18px"></i>` : ` `)) + `</div>
                                        
                                    </span>
                                </div>
                            </div>
                            <div class="col-lg-6"></div>
                        </div>
                    </div>`;
            }
            s = s + 2;
        }

        if (strDivPorServicio != '')
            $('#divPorEntidadServicio').empty().append(strDivPorServicio);
    }

    $('.cssServicioLink').click(function () {
        var arrId = this.id.split('_');
        var IdServicio = (arrId.length == 2 ? arrId[1].trim() : '0');
        var DNI = $('#DNI_CIUDADANO').val();
        var NOMBRE = fnCamposVacios($('#NOMBRE_CIUDADANO').val());
        var APELLIDO_PATERNO = fnCamposVacios($('#APELLIDO_PATERNO_CIUDADANO').val());
        var APELLIDO_MATERNO = fnCamposVacios($('#APELLIDO_MATERNO_CIUDADANO').val());
        var hddWPV = $('#hddWPV').val();

        var strServicio = $(this).html().replace('<b>', '').replace('</b>', '').replace('.', '') + ".";
        bootbox.confirm("¿Está seguro de continuar?<br>Tramite: " + strServicio, function (result) {
            if (result) {
                var url = '/AtencionCiudadano/Servicios/nuevaAtencion?Id=' + IdServicio + '&&DNI=' + DNI + '&&NOMBRE=' + NOMBRE + '&&APELLIDO_PATERNO=' + APELLIDO_PATERNO + '&&APELLIDO_MATERNO=' + APELLIDO_MATERNO + '&&WPV=' + hddWPV;
                showDialog_ServicioCiudadano(url);
            }
        });
    });

    $('.btn-registro').click(function () {
        var arrId = this.id.split('_');
        var IdServicio = (arrId.length == 2 ? arrId[1].trim() : '0');
        var idEnt = parseInt(this.id.substring(this.id.length - 4));
        var DNI = $('#DNI_CIUDADANO').val();
        var NOMBRE = fnCamposVacios($('#NOMBRE_CIUDADANO').val());
        var APELLIDO_PATERNO = fnCamposVacios($('#APELLIDO_PATERNO_CIUDADANO').val());
        var APELLIDO_MATERNO = fnCamposVacios($('#APELLIDO_MATERNO_CIUDADANO').val());
        var hddWPV = $('#hddWPV').val();
        $('#hddIDENTIDAD').val(idEnt);

        var OBJ = new Object;
        OBJ.SERVICIO = "";
        OBJ.DNI = DNI;
        OBJ.NOMBRE = NOMBRE;
        OBJ.APELLIDO_PATERNO = APELLIDO_PATERNO;
        OBJ.APELLIDO_MATERNO = APELLIDO_MATERNO;
        OBJ.ACCION = hddWPV;
        OBJ.intNumeroPaginaAtencion = 1;

        $.ajax({
            type: "GET",
            url: '/AtencionCiudadano/Servicios/ListaServiciosEntidad',
            data: { models: OBJ, strNombreServicio: "", intPagina: 1, strTipo: 0, intEntidad: idEnt },
            dataType: "json",
            success: function (data) {
                if (data != null) {
                    fnListarServiciosPorEntidad(data, idEnt, DNI);
                }
            }
        });

    });

    fnInputSearch();
    //return ARRA_OBJ_ENTIDAD;
}


function fnGetObjetoServicio_COMBO(wpv, strCadena, strSeparador0, strSeparador1, strSeparador2, strDNI, strNOMBRE, strAPELLIDO_PATERNO, strAPELLIDO_MATERNO, TOTAL, PAGINADO) {
    
    var strDivPaginado = '';
    var total_ = (TOTAL == null ? 0 : parseInt(TOTAL));
    PAGINADO = 12;
    var paginado_ = (PAGINADO == null ? 0 : parseInt(PAGINADO));

    var _pagina = 0; i = 1;

    
     

    var SERVICIO = strCadena;
    var arrSERVICIO = SERVICIO.split(strSeparador0);

    var strEntidad = "", strServicio = "", OBJ_ENTIDAD = new Object(), OBJ_SERVICIO = new Object(), ARRA_OBJ_ENTIDAD = [];
    var indice = -1;

    for (var e = 0; e < arrSERVICIO.length; e++) {
       
        var strEntidadServicio = arrSERVICIO[e];
        if (strEntidadServicio != "") {
            
            strEntidad = "";
            strServicio = "";

            var arrEntidadServicio = strEntidadServicio.split(strSeparador1);
            if (arrEntidadServicio.length == 2) {
                strEntidad = arrEntidadServicio[0];
                strServicio = arrEntidadServicio[1];
            }

            if (strEntidad != '') {
                var arrEntidad = strEntidad.split(strSeparador2);

                if (arrEntidad.length == 6) {
                    if (arrEntidad[0] != '' && arrEntidad[1] != '' && arrEntidad[2] != '' && arrEntidad[3] != '' && arrEntidad[4] != '') {
                        OBJ_ENTIDAD = new Object();
                        indice = -1;
                        for (var _e = 0; _e < ARRA_OBJ_ENTIDAD.length; _e++) {
                            if (ARRA_OBJ_ENTIDAD[_e].ID_ENTIDAD == arrEntidad[0].trim()) {
                                OBJ_ENTIDAD = ARRA_OBJ_ENTIDAD[_e];
                                indice = _e;
                                break;
                            }
                        }

                        if (indice == -1) {
                            OBJ_ENTIDAD.ID_ENTIDAD = arrEntidad[0].trim();
                            OBJ_ENTIDAD.ENTIDAD_NOMENCLATURA = arrEntidad[1].trim();
                            OBJ_ENTIDAD.ENTIDAD_DESCRIPCION = arrEntidad[2].trim();
                            OBJ_ENTIDAD.ENTIDAD_IMAGEN = arrEntidad[3].trim();
                            OBJ_ENTIDAD.ENTIDAD_ORDEN = arrEntidad[4].trim();
                            OBJ_ENTIDAD.ANCHO_ALTO_LOGO = arrEntidad[5].trim();
                            OBJ_ENTIDAD.ARRA_SERVICIO = [];
                        }

                        if (strServicio != '') {

                            var arrServicio = strServicio.split(strSeparador2);

                            if (arrServicio.length == 8) {
                                if (arrServicio[0] != '' && arrServicio[1] != '' && arrServicio[2] != '' && arrServicio[3] != '' && arrServicio[4] != '') {
                                    OBJ_SERVICIO = new Object();
                                    OBJ_SERVICIO.ID_SERVICIO = arrServicio[0].trim();
                                    OBJ_SERVICIO.SERVICIO_TIPO = arrServicio[1].trim();
                                    OBJ_SERVICIO.SERVICIO_DESCRIPCION = arrServicio[2].trim();
                                    OBJ_SERVICIO.SERVICIO_ORDEN = arrServicio[3].trim();
                                    OBJ_SERVICIO.SERVICIO_LINK = arrServicio[4].trim();
                                    OBJ_ENTIDAD.ARRA_SERVICIO.push(OBJ_SERVICIO);
                                  
                                    if (indice == -1) {
                                        ARRA_OBJ_ENTIDAD.push(OBJ_ENTIDAD);
                                    } else {
                                        ARRA_OBJ_ENTIDAD[indice] = OBJ_ENTIDAD;
                                    }

                                }
                            }
                        }
                    }
                }
            }
        }
    }

    var strDivPorEntidad = '', strDivPorServicio = '', strDivCabecera = '', strDivLeyenda = ''; i = 0; t = 0;


    if ( wpv == 1) {
        //$("#divPorEntidad").empty().append('<div class="panel panel-default">' + strDivCabecera + '<div class="panel-body">' + strDivLeyenda + strDivPorEntidad + strDivPaginado + '</div></div>');
        arrSrv = ARRA_OBJ_ENTIDAD;
        var strListaServicio = "";
        for (var i_ = 0; i_ < ARRA_OBJ_ENTIDAD.length; i_++) {
            strListaServicio = "";
            for (var _i = 0; _i < ARRA_OBJ_ENTIDAD[i_].ARRA_SERVICIO.length; _i++) {

                var descripcion_ = ARRA_OBJ_ENTIDAD[i_].ARRA_SERVICIO[_i].SERVICIO_DESCRIPCION;
                var arrDescripcion_ = descripcion_.split('*');
                var tipo_ = 0;

                if (arrDescripcion_.length == 1) {
                    tipo_ = 1;
                    descripcion_ = arrDescripcion_[0];
                }

                if (arrDescripcion_.length == 3) {
                    var contadorVacio = 0;
                    arrDescripcion_.forEach(descripcion => {
                        if (descripcion.length == 0)
                            contadorVacio++;
                    });

                    if (contadorVacio == 0)
                        tipo_ = 2;

                    if (contadorVacio == 2)
                        tipo_ = 3;

                    if (contadorVacio == 1) {
                        if (arrDescripcion_[0] == "")
                            tipo_ = 4;
                        if (arrDescripcion_[2] == "")
                            tipo_ = 5;
                    }
                }

                strListaServicio = strListaServicio +
                    `<div class="form-group" style="margin: 10px 0px 10px 0px;">
                            <div class="col-lg-11"><a id="linkServicio_` + ARRA_OBJ_ENTIDAD[i_].ARRA_SERVICIO[_i].ID_SERVICIO.toString() + `" class="cssServicioLink" href="#">` +
                    (tipo_ == 1 ? descripcion_ : (tipo_ == 2 ? arrDescripcion_[0] + '<b>' + arrDescripcion_[1] + '</b>' + arrDescripcion_[2] : (tipo_ == 3 ? '<b>' + arrDescripcion_[1] + '</b>' : (tipo_ == 4 ? '<b>' + arrDescripcion_[1] + '</b> ' + arrDescripcion_[2] : (tipo_ == 5 ? arrDescripcion_[0] + '<b>' + arrDescripcion_[1] + '</b>' : '')))))
                    + `</a></div>
                            <div class="col-lg-1 text-center">` + (ARRA_OBJ_ENTIDAD[i_].ARRA_SERVICIO[_i].SERVICIO_TIPO == '1' ? `<i class="fas fa-adjust" style="color: blue; font-size: 18px"></i>` : (ARRA_OBJ_ENTIDAD[i_].ARRA_SERVICIO[_i].SERVICIO_TIPO == '2' ? `<i class="fas fa-check-circle" style="color: #00a65a; font-size: 18px"></i>` : ` `)) + `</div>
                        </div>`;

            }
            if (strListaServicio != '')
                $('#divListaServicio_' + i_).append(strListaServicio);
        }
    }
   
    if (strDivPorServicio != '' && wpv == 2) {
        $("#divPorEntidad").empty().append('<div class="panel panel-default">' + strDivCabecera + '<div class="panel-body">' + strDivLeyenda + strDivPorServicio + strDivPaginado + '</div></div>');

        var contadorServicio = 0; totalServicio = 0, strDivPorServicio = '';
        var ARRA_SERVICIO = []; s = 0;
        ARRA_OBJ_ENTIDAD.forEach(entidad => {
            entidad.ARRA_SERVICIO.forEach(servicio => {
                ARRA_SERVICIO.push(servicio);
            });
        });

        while (s < ARRA_SERVICIO.length) {
            var descripcion_1 = ARRA_SERVICIO[s].SERVICIO_DESCRIPCION;
            var arrDescripcion_1 = descripcion_1.split('*');
            var tipo_1 = 0;

            if (arrDescripcion_1.length == 1) {
                tipo_1 = 1;
                descripcion_1 = arrDescripcion_1[0];
            }

            if (arrDescripcion_1.length == 3) {
                var contadorVacio = 0;
                arrDescripcion_1.forEach(descripcion => {
                    if (descripcion.length == 0)
                        contadorVacio++;
                });

                if (contadorVacio == 0)
                    tipo_1 = 2;

                if (contadorVacio == 2)
                    tipo_1 = 3;

                if (contadorVacio == 1) {
                    if (arrDescripcion_1[0] == "")
                        tipo_1 = 4;
                    if (arrDescripcion_1[2] == "")
                        tipo_1 = 5;
                }
            }

            if (s + 2 <= ARRA_SERVICIO.length) {
                var descripcion_2 = ARRA_SERVICIO[s + 1].SERVICIO_DESCRIPCION;
                var arrDescripcion_2 = descripcion_2.split('*');
                var tipo_2 = 0;

                if (arrDescripcion_2.length == 1) {
                    tipo_2 = 1;
                    descripcion_2 = arrDescripcion_2[0];
                }

                if (arrDescripcion_2.length == 3) {
                    var contadorVacio = 0;
                    arrDescripcion_2.forEach(descripcion => {
                        if (descripcion.length == 0)
                            contadorVacio++;
                    });

                    if (contadorVacio == 0)
                        tipo_2 = 2;

                    if (contadorVacio == 2)
                        tipo_2 = 3;

                    if (contadorVacio == 1) {
                        if (arrDescripcion_2[0] == "")
                            tipo_2 = 4;
                        if (arrDescripcion_2[2] == "")
                            tipo_2 = 5;
                    }
                }

                strDivPorServicio = strDivPorServicio +
                    `                    <div class="form-horizontal">
                        <div class="form-group" style="margin-bottom: 2px;">
                            <div class="col-lg-6">
                                <div class="form-group" style="margin: 2px;">
                                    <span class="border border-primary span-style">
                                        <div class="col-lg-11" style="margin: 25px 0px 25px 0px;"><a id="linkServicio_` + ARRA_SERVICIO[s].ID_SERVICIO.toString() + `" class="cssServicioLink" href="#">` +
                    (tipo_1 == 1 ? descripcion_1 : (tipo_1 == 2 ? arrDescripcion_1[0] + '<b>' + arrDescripcion_1[1] + '</b>' + arrDescripcion_1[2] : (tipo_1 == 3 ? '<b>' + arrDescripcion_1[1] + '</b>' : (tipo_1 == 4 ? '<b>' + arrDescripcion_1[1] + '</b> ' + arrDescripcion_1[2] : (tipo_1 == 5 ? arrDescripcion_1[0] + '<b>' + arrDescripcion_1[1] + '</b>' : '')))))
                    + `</a></div>
                                        <div class="col-lg-1 text-center" style="margin: 25px 0px 25px 0px;">` + (ARRA_SERVICIO[s].SERVICIO_TIPO == '1' ? `<i class="fas fa-adjust" style="color: blue; font-size: 18px"></i>` : (ARRA_SERVICIO[s].SERVICIO_TIPO == '2' ? `<i class="fas fa-check-circle" style="color: #00a65a; font-size: 18px"></i>` : ` `)) + `</div>
                                    </span> 
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group" style="margin: 2px;">
                                    <span class="border border-primary span-style">
                                        
                                        <div class="col-lg-11" style="margin: 25px 0px 25px 0px;"><a id="linkServicio_` + ARRA_SERVICIO[s + 1].ID_SERVICIO.toString() + `" class="cssServicioLink" href="#">` +
                    (tipo_2 == 1 ? descripcion_2 : (tipo_2 == 2 ? arrDescripcion_2[0] + '<b>' + arrDescripcion_2[1] + '</b>' + arrDescripcion_2[2] : (tipo_2 == 3 ? '<b>' + arrDescripcion_2[1] + '</b>' : (tipo_2 == 4 ? '<b>' + arrDescripcion_2[1] + '</b> ' + arrDescripcion_2[2] : (tipo_2 == 5 ? arrDescripcion_2[0] + '<b>' + arrDescripcion_2[1] + '</b>' : '')))))
                    + `</a></div>
                                        <div class="col-lg-1 text-center" style="margin: 25px 0px 25px 0px;">` + (ARRA_SERVICIO[s + 1].SERVICIO_TIPO == '1' ? `<i class="fas fa-adjust" style="color: blue; font-size: 18px"></i>` : (ARRA_SERVICIO[s + 1].SERVICIO_TIPO == '2' ? `<i class="fas fa-check-circle" style="color: #00a65a; font-size: 18px"></i>` : ` `)) + `</div>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>`;
            }
            else {
                strDivPorServicio = strDivPorServicio +
                    `                    <div class="form-horizontal">
                        <div class="form-group" style="margin: 2px;">
                            <div class="col-lg-6">
                                <div class="form-group" style="margin-bottom: 2px;">
                                    <span class="border border-primary span-style">
                                        <div class="col-lg-11" style="margin: 25px 0px 25px 0px;"><a id="linkServicio_` + ARRA_SERVICIO[s].ID_SERVICIO.toString() + `" class="cssServicioLink" href="#">` +
                    (tipo_1 == 1 ? descripcion_1 : (tipo_1 == 2 ? arrDescripcion_1[0] + '<b>' + arrDescripcion_1[1] + '</b>' + arrDescripcion_1[2] : (tipo_1 == 3 ? '<b>' + arrDescripcion_1[1] + '</b>' : (tipo_1 == 4 ? '<b>' + arrDescripcion_1[1] + '</b> ' + arrDescripcion_1[2] : (tipo_1 == 5 ? arrDescripcion_1[0] + '<b>' + arrDescripcion_1[1] + '</b>' : '')))))
                    + `</a></div>
                                        <div class="col-lg-1 text-center" style="margin: 25px 0px 25px 0px;">` + (ARRA_SERVICIO[s].SERVICIO_TIPO == '1' ? `<i class="fas fa-adjust" style="color: blue; font-size: 18px"></i>` : (ARRA_SERVICIO[s].SERVICIO_TIPO == '2' ? `<i class="fas fa-check-circle" style="color: #00a65a; font-size: 18px"></i>` : ` `)) + `</div>
                                        
                                    </span>
                                </div>
                            </div>
                            <div class="col-lg-6"></div>
                        </div>
                    </div>`;
            }
            s = s + 2;
        }

        if (strDivPorServicio != '')
            $('#divPorEntidadServicio').empty().append(strDivPorServicio);
    }
     /*
    $('.cssServicioLink').click(function () {
        var arrId = this.id.split('_');
        var IdServicio = (arrId.length == 2 ? arrId[1].trim() : '0');
        var DNI = $('#DNI_CIUDADANO').val();
        var NOMBRE = fnCamposVacios($('#NOMBRE_CIUDADANO').val());
        var APELLIDO_PATERNO = fnCamposVacios($('#APELLIDO_PATERNO_CIUDADANO').val());
        var APELLIDO_MATERNO = fnCamposVacios($('#APELLIDO_MATERNO_CIUDADANO').val());
        var hddWPV = $('#hddWPV').val();

        var strServicio = $(this).html().replace('<b>', '').replace('</b>', '').replace('.', '') + ".";
        bootbox.confirm("¿Está seguro de continuar?<br>Tramite: " + strServicio, function (result) {
            if (result) {
                var url = '/AtencionCiudadano/Servicios/nuevaAtencion?Id=' + IdServicio + '&&DNI=' + DNI + '&&NOMBRE=' + NOMBRE + '&&APELLIDO_PATERNO=' + APELLIDO_PATERNO + '&&APELLIDO_MATERNO=' + APELLIDO_MATERNO + '&&WPV=' + hddWPV;
                showDialog_ServicioCiudadano(url);
            }
        });
    });

    $('.btn-registro').click(function () {
        var arrId = this.id.split('_');
        var IdServicio = (arrId.length == 2 ? arrId[1].trim() : '0');
        var idEnt = parseInt(this.id.substring(this.id.length - 4));
        var DNI = $('#DNI_CIUDADANO').val();
        var NOMBRE = fnCamposVacios($('#NOMBRE_CIUDADANO').val());
        var APELLIDO_PATERNO = fnCamposVacios($('#APELLIDO_PATERNO_CIUDADANO').val());
        var APELLIDO_MATERNO = fnCamposVacios($('#APELLIDO_MATERNO_CIUDADANO').val());
        var hddWPV = $('#hddWPV').val();
        $('#hddIDENTIDAD').val(idEnt);

        var OBJ = new Object;
        OBJ.SERVICIO = "";
        OBJ.DNI = DNI;
        OBJ.NOMBRE = NOMBRE;
        OBJ.APELLIDO_PATERNO = APELLIDO_PATERNO;
        OBJ.APELLIDO_MATERNO = APELLIDO_MATERNO;
        OBJ.ACCION = hddWPV;
        OBJ.intNumeroPaginaAtencion = 1;

        $.ajax({
            type: "GET",
            url: '/AtencionCiudadano/Servicios/ListaServiciosEntidad',
            data: { models: OBJ, strNombreServicio: "", intPagina: 1, strTipo: 0, intEntidad: idEnt },
            dataType: "json",
            success: function (data) {
             
                if (data != null) {
                    fnListarServiciosPorEntidad(data, idEnt, DNI);
                }
            }
        });

    });
*/
    fnInputSearch();
    //return ARRA_OBJ_ENTIDAD;
}

function fnCamposVacios(PALABRA) {
    var cont = PALABRA.indexOf(' ');
    while (cont > 0) {
        PALABRA = PALABRA.replace(' ', '│');
        cont = PALABRA.indexOf(' ');
    }
    return PALABRA;
}


$('#aTabListaPorEntidad').click(function () {

    $("#FormularioListaServicios").empty().append(
        '<input id="wpv" name ="wpv" type="hidden" value ="1" />',
        '<input id="DNI" name ="DNI" type="hidden" value ="' + $('#DNI_CIUDADANO').val() + '" />',
        '<input id="NOMBRE" name ="NOMBRE" type="hidden" value ="' + $('#NOMBRE_CIUDADANO').val() + '" />',
        '<input id="APELLIDO_PATERNO" name ="APELLIDO_PATERNO" type="hidden" value ="' + $('#APELLIDO_PATERNO_CIUDADANO').val() + '" />',
        '<input id="APELLIDO_MATERNO" name ="APELLIDO_MATERNO" type="hidden" value ="' + $('#APELLIDO_MATERNO_CIUDADANO').val() + '" />',
        '<input id="CORREO" name ="CORREO" type="hidden" value ="" />',
        '<input id="TELEFONO" name ="TELEFONO" type="hidden" value ="" />',
        '<input id="EVENTO" name ="EVENTO" type="hidden" value ="TabListaPorEntidad" />',
    ).submit();
});

$('#aTabListaPorServicio').click(function () {

    $("#FormularioListaServicios").empty().append(
        '<input id="wpv" name ="wpv" type="hidden" value ="2" />',
        '<input id="DNI" name ="DNI" type="hidden" value ="' + $('#DNI_CIUDADANO').val() + '" />',
        '<input id="NOMBRE" name ="NOMBRE" type="hidden" value ="' + $('#NOMBRE_CIUDADANO').val() + '" />',
        '<input id="APELLIDO_PATERNO" name ="APELLIDO_PATERNO" type="hidden" value ="' + $('#APELLIDO_PATERNO_CIUDADANO').val() + '" />',
        '<input id="APELLIDO_MATERNO" name ="APELLIDO_MATERNO" type="hidden" value ="' + $('#APELLIDO_MATERNO_CIUDADANO').val() + '" />',
        '<input id="CORREO" name ="CORREO" type="hidden" value ="" />',
        '<input id="TELEFONO" name ="TELEFONO" type="hidden" value ="" />',
        '<input id="EVENTO" name ="EVENTO" type="hidden" value ="TabListaPorServicio" />',
    ).submit();
});

$('#btnCerrarTramite').click(function () {
    $("#FormularioNuevaAtencion").empty().append(
        '<input id="wpv" name ="wpv" type="hidden" value ="' + $('#hddWPVNA').val() + '" />',
        '<input id="DNI" name ="DNI" type="hidden" value ="' + $('#NUEVAATENCION_DNI').text() + '" />',
        '<input id="NOMBRE" name ="NOMBRE" type="hidden" value ="' + $('#NUEVAATENCION_NOMBRE').text() + '" />',
        '<input id="APELLIDO_PATERNO" name ="APELLIDO_PATERNO" type="hidden" value ="' + $('#NUEVAATENCION_APELLIDO_PATERNO').text() + '" />',
        '<input id="APELLIDO_MATERNO" name ="APELLIDO_MATERNO" type="hidden" value ="' + $('#NUEVAATENCION_APELLIDO_MATERNO').text() + '" />',
        '<input id="CORREO" name ="CORREO" type="hidden" value ="" />',
        '<input id="TELEFONO" name ="TELEFONO" type="hidden" value ="" />',
        '<input id="EVENTO" name ="EVENTO" type="hidden" value ="CerrarTramite" />',
    ).submit();
});

function ponerCeros(num) {
   var txt;
    while (num.length < 4) {
        num = '0' + num.toString();
    }
    txt = num;
    return txt;
}