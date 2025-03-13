/// <summary>
/// Script de la aplicación
/// </summary>
/// <remarks>
/// Creacion: 	WECM 28092017
/// </remarks>
ns('PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Anexo');
PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Anexo.Controller = function () {
    var base = this;
    base.Ini = function () {
        'use strict'
        base.Event.CargarSeguimientoFlujoClick();
        base.Control.BtnAbrirDocumento().off('click');
        base.Control.BtnAbrirDocumento().on('click', base.Event.BtnAbrirDocumentoClick);

    };

    base.Control = {
        HdnId: function () { return $('#hdnId') },
        TreeviewFlujoSeguimiento: function () { return $('#treeviewFlujoSeguimiento') },

        TxtEtiquetaTipoDocumento: function () { return $('#txtEtiquetaTipoDocumento') },
        TxtEtiquetaNroDocumento: function () { return $('#txtEtiquetaNroDocumento') },
        TxtEtiquetaFechaEmision: function () { return $('#txtEtiquetaFechaEmision') },
        TxtEtiquetaElaboro: function () { return $('#txtEtiquetaElaboro') },
        TxtEtiquetaAsunto: function () { return $('#txtEtiquetaAsunto') },
        TxtEtiquetaEmisor: function () { return $('#txtEtiquetaEmisor') },
        TxtEtiquetaEstado: function () { return $('#txtEtiquetaEstado') },

        TxtDestEtiquetaDependencia: function () { return $('#txtDestEtiquetaDependencia') },
        TxtDestEtiquetaReceptor: function () { return $('#txtDestEtiquetaReceptor') },
        TxtDestEtiquetaEstado: function () { return $('#txtDestEtiquetaEstado') },
        TxtDestEtiquetaFechaRecepcion: function () { return $('#txtDestEtiquetaFechaRecepcion') },
        TxtDestEtiquetaFechaAtendido: function () { return $('#txtDestEtiquetaFechaAtendido') },
        TxtDestEtiquetaTramite: function () { return $('#txtDestEtiquetaTramite') },
        TxtDestEtiquetaPrioridad: function () { return $('#txtDestEtiquetaPrioridad') },
        TxtDestEtiquetaIndicaciones: function () { return $('#txtDestEtiquetaIndicaciones') },

        BtnAbrirDocumento: function () { return $('#btn-abrirdocumento') },
        TableAnexosBody: function () { return $('#tableAnexosBody') },
    };

    base.Event = {
        BtnAbrirDocumentoClick: function () {
            if (base.Control.Anio != null && base.Control.NroEmision != null) {
                var filtro = { Anio: base.Control.Anio, NroEmision: base.Control.NroEmision };
                Gob.Pe.UI.Web.Components.Util.RedirectPost(PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Actions.DescargarDocumento, filtro);
            }

        },
        CargarSeguimientoFlujoClick: function () {
            base.Ajax.AjaxCargarAnexo.data = { Id: base.Control.HdnId().val() };
            base.Ajax.AjaxCargarAnexo.submit();
        },
        CargarAnexoDetalleClick: function (id) {
            base.Ajax.AjaxAnexoDetalle.data = { Id: id };
            base.Ajax.AjaxAnexoDetalle.submit();
        },
        BtnDescagarDocumentoAnexoClick: function (thiss) {
            'use strict'
            var filtro = { NroDocumento: null};
            filtro.NroDocumento = $(thiss).attr('anexo');
            if (filtro.NroDocumento != null ) {
                Gob.Pe.UI.Web.Components.Util.RedirectPost(PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Actions.DescargarDocumentoAnexo, filtro);
            }
            else {
                base.Control.Mensaje.Warning({ title: 'Advertencia', message: 'Debe seleccionar un registros.' });
            }

        },
        AjaxCargarAnexoSuccess: function (datas) {
            'use strict'
            if (datas.IsSuccess) {
                if (datas.Result[0].IsSuccess) {
                    base.Control.TreeviewFlujoSeguimiento().treeview({
                        levels: 5,
                        data: datas.Result[0].Result[0],
                        multiSelect: false,
                        highlightSelected: true,
                        onNodeSelected: function (event, node) {
                            base.Control.Anio = node.textIdAnio;
                            base.Control.NroEmision = node.textIdNroEmision;
                            base.Event.CargarAnexoDetalleClick(node.textId)
                        },
                        onNodeUnselected: function (event, node) {
                        }
                    });

                }
                
                if (datas.Result[1].IsSuccess) {
                    var data = datas.Result[1].Result[0];
                    //Remitente
                    base.Control.TxtEtiquetaTipoDocumento().html(data.TipoDocumento); if (data.TipoDocumento != null) base.Control.TxtEtiquetaTipoDocumento().attr("style", "height: 100%;");
                    base.Control.TxtEtiquetaNroDocumento().html(data.NroDocumento); if (data.NroDocumento != null) base.Control.TxtEtiquetaNroDocumento().attr("style", "height: 100%;");
                    base.Control.TxtEtiquetaFechaEmision().html(data.FechaEmision); if (data.FechaEmision != null) base.Control.TxtEtiquetaFechaEmision().attr("style", "height: 100%;");
                    base.Control.TxtEtiquetaElaboro().html(data.RemiteElaboro); if (data.RemiteElaboro != null) base.Control.TxtEtiquetaElaboro().attr("style", "height: 100%;");
                    base.Control.TxtEtiquetaEmisor().html(data.Remite); if (data.Remite != null) base.Control.TxtEtiquetaEmisor().attr("style", "height: 100%;");
                    base.Control.TxtEtiquetaAsunto().html(data.Asunto); if (data.Asunto != null) base.Control.TxtEtiquetaAsunto().attr("style", "height: 100%;");
                    base.Control.TxtEtiquetaEstado().html(data.EstadoEmisor); if (data.EstadoEmisor != null) base.Control.TxtEtiquetaEstado().attr("style", "height: 100%;");
                    base.Control.Anio = data.Anio;
                    base.Control.NroEmision = data.NroEmision;
                    //Destinatario

                    //base.Control.TxtDestEtiquetaDependencia().html(data.Dependencia + ' ' + data.Destinatario); if (data.Dependencia != null) base.Control.TxtDestEtiquetaDependencia().attr("style", "height: 100%;");
                    //base.Control.TxtDestEtiquetaReceptor().html(data.Recepcionado); if (data.Recepcionado != null) base.Control.TxtDestEtiquetaReceptor().attr("style", "height: 100%;");
                    //base.Control.TxtDestEtiquetaEstado().html(data.Estado); if (data.Estado != null) base.Control.TxtDestEtiquetaEstado().attr("style", "height: 100%;");
                    //base.Control.TxtDestEtiquetaFechaRecepcion().html(data.FechaRecepcion); if (data.FechaRecepcion != null) base.Control.TxtDestEtiquetaFechaRecepcion().attr("style", "height: 100%;");
                    //base.Control.TxtDestEtiquetaFechaAtendido().html(data.FechaAtencion); if (data.FechaAtencion != null) base.Control.TxtDestEtiquetaFechaAtendido().attr("style", "height: 100%;");
                    //base.Control.TxtDestEtiquetaTramite().html(data.Motivo); if (data.Motivo != null) base.Control.TxtDestEtiquetaTramite().attr("style", "height: 100%;");
                    //base.Control.TxtDestEtiquetaPrioridad().html(data.Prioridad); if (data.Prioridad != null) base.Control.TxtDestEtiquetaPrioridad().attr("style", "height: 100%;");
                    //base.Control.TxtDestEtiquetaIndicaciones().html(data.Observacion); if (data.Observacion != null) base.Control.TxtDestEtiquetaIndicaciones().attr("style", "height: 100%;");

                } 
                if (datas.Result[2].IsSuccess) {
                    base.Control.TableAnexosBody().empty(); 
                    if (datas.Result[2].Result != null  ) { 
                        $.each(datas.Result[2].Result, function (index, value) { 
                            var tr = $('<tr/>');
                            tr.append("<td>" + value.Descripcion + "</td>");
                            tr.append("<td>" + value.NombreAnexo + "</td>");  
                            tr.append("<td style='width:40px;text-align: center;' onclick='PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Anexo.Vista.Event.BtnDescagarDocumentoAnexoClick(this);' anexo='" + value.NroDocumento + "'   ><span class='glyphicon glyphicon-folder-open' title='" + PCM.Reclamo.Presentacion.Base.GenericoResource.EtiquetaVerDocumento + "'></span></td>");
                            base.Control.TableAnexosBody().append(tr); 
                        });
                    }
                    else {
                        var tr = $('<tr/>');
                        tr.append('<td colspan="3">No hay registros...</td>');
                        base.Control.TableAnexosBody().append(tr);
                    }
                   
                }
            }

        },
        AjaxAnexoDetalleSuccess: function (datas) {
            'use strict'
            if (datas.IsSuccess) {
                if (datas.Result[0].IsSuccess) {
                    var data = datas.Result[0].Result[0];
                   
                    //Remitente
                    base.Control.TxtEtiquetaTipoDocumento().html(data.TipoDocumento); if (data.TipoDocumento != null) base.Control.TxtEtiquetaTipoDocumento().attr("style", "height: 100%;"); else base.Control.TxtEtiquetaTipoDocumento().attr("style", "min-height: 100%;");
                    base.Control.TxtEtiquetaNroDocumento().html(data.NroDocumento); if (data.NroDocumento != null) base.Control.TxtEtiquetaNroDocumento().attr("style", "height: 100%;"); else base.Control.TxtEtiquetaNroDocumento().attr("style", "min-height: 100%;");
                    base.Control.TxtEtiquetaFechaEmision().html(data.FechaEmision); if (data.FechaEmision != null) base.Control.TxtEtiquetaFechaEmision().attr("style", "height: 100%;"); else base.Control.TxtEtiquetaFechaEmision().attr("style", "min-height: 100%;");
                    base.Control.TxtEtiquetaElaboro().html(data.RemiteElaboro); if (data.RemiteElaboro != null) base.Control.TxtEtiquetaElaboro().attr("style", "height: 100%;"); else base.Control.TxtEtiquetaElaboro().attr("style", "min-height: 100%;");
                    base.Control.TxtEtiquetaEmisor().html(data.Remite); if (data.Remite != null) base.Control.TxtEtiquetaEmisor().attr("style", "height: 100%;"); else base.Control.TxtEtiquetaEmisor().attr("style", "min-height: 100%;");
                    base.Control.TxtEtiquetaAsunto().html(data.Asunto); if (data.Asunto != null) base.Control.TxtEtiquetaAsunto().attr("style", "height: 100%;"); else base.Control.TxtEtiquetaAsunto().attr("style", "min-height: 100%;");
                    base.Control.TxtEtiquetaEstado().html(data.EstadoEmisor); if (data.EstadoEmisor != null) base.Control.TxtEtiquetaEstado().attr("style", "height: 100%;"); else base.Control.TxtEtiquetaEstado().attr("style", "min-height: 100%;");
                    //Destinatario

                    //base.Control.TxtDestEtiquetaDependencia().html(data.Dependencia + ' ' + data.Destinatario); if (data.Dependencia != null) base.Control.TxtDestEtiquetaDependencia().attr("style", "height: 100%;"); else base.Control.TxtDestEtiquetaDependencia().attr("style", "min-height: 100%;");
                    //base.Control.TxtDestEtiquetaReceptor().html(data.Recepcionado); if (data.Recepcionado != null) base.Control.TxtDestEtiquetaReceptor().attr("style", "height: 100%;"); else base.Control.TxtDestEtiquetaReceptor().attr("style", "min-height: 100%;");
                    //base.Control.TxtDestEtiquetaEstado().html(data.Estado); if (data.Estado != null) base.Control.TxtDestEtiquetaEstado().attr("style", "height: 100%;"); else base.Control.TxtDestEtiquetaEstado().attr("style", "min-height: 100%;");
                    //base.Control.TxtDestEtiquetaFechaRecepcion().html(data.FechaRecepcion); if (data.FechaRecepcion != null) base.Control.TxtDestEtiquetaFechaRecepcion().attr("style", "height: 100%;"); else base.Control.TxtDestEtiquetaFechaRecepcion().attr("style", "min-height: 100%;");
                    //base.Control.TxtDestEtiquetaFechaAtendido().html(data.FechaAtencion); if (data.FechaAtencion != null) base.Control.TxtDestEtiquetaFechaAtendido().attr("style", "height: 100%;"); else base.Control.TxtDestEtiquetaFechaAtendido().attr("style", "min-height: 100%;");
                    //base.Control.TxtDestEtiquetaTramite().html(data.Motivo); if (data.Motivo != null) base.Control.TxtDestEtiquetaTramite().attr("style", "height: 100%;"); else base.Control.TxtDestEtiquetaTramite().attr("style", "min-height: 100%;");
                    //base.Control.TxtDestEtiquetaPrioridad().html(data.Prioridad); if (data.Prioridad != null) base.Control.TxtDestEtiquetaPrioridad().attr("style", "height: 100%;"); else base.Control.TxtDestEtiquetaPrioridad().attr("style", "min-height: 100%;");
                    //base.Control.TxtDestEtiquetaIndicaciones().html(data.Observacion); if (data.Observacion != null) base.Control.TxtDestEtiquetaIndicaciones().attr("style", "height: 100%;"); else base.Control.TxtDestEtiquetaIndicaciones().attr("style", "min-height: 100%;");
                }
                if (datas.Result[1].IsSuccess) {
                    base.Control.TableAnexosBody().empty();
                    if (datas.Result[1].Result != null ) {
                        $.each(datas.Result[1].Result, function (index, value) {
                            var tr = $('<tr/>');
                            tr.append("<td>" + value.Descripcion + "</td>");
                            tr.append("<td>" + value.NombreAnexo + "</td>");
                            tr.append("<td style='width:40px;text-align: center;' onclick='PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Anexo.Vista.Event.BtnDescagarDocumentoAnexoClick(this);' anexo='" + value.NroDocumento + "'><span class='glyphicon glyphicon-folder-open' title='" + PCM.Reclamo.Presentacion.Base.GenericoResource.EtiquetaVerDocumento + "'></span></td>");
                            base.Control.TableAnexosBody().append(tr);
                        });
                    }
                    else {
                        var tr = $('<tr/>');
                        tr.append('<td colspan="3">No hay registros...</td>');
                        base.Control.TableAnexosBody().append(tr);
                    }

                }


            }


        },

    };

    base.Ajax = {

        AjaxCargarAnexo: new Gob.Pcm.UI.Web.Components.Ajax({
            action: PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Actions.AnexoFlujo,
            autoSubmit: false,
            onSuccess: base.Event.AjaxCargarAnexoSuccess
        }),
        AjaxAnexoDetalle: new Gob.Pcm.UI.Web.Components.Ajax({
            action: PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Actions.AnexoDetalle,
            autoSubmit: false,
            onSuccess: base.Event.AjaxAnexoDetalleSuccess
        })
    };

    base.Function = {


    };
};















try {
    $(document).ready(function () {
        'use strict';
        PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Anexo.Vista = new PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Anexo.Controller();
        PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Anexo.Vista.Ini();
    });
} catch (ex) {
}