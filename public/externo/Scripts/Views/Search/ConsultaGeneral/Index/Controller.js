/// <summary>
/// Script de la aplicación
/// </summary>
/// <remarks>
/// Creacion: 	WECM 14092017
/// </remarks>
ns('PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Index');
PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Index.Controller = function () {
    var base = this;
    var options = {};
    var d = new Date();
    var mes = '';
    var MesActual = (d.getMonth() + 1) + '';
    base.Ini = function () {
        'use strict'
        //base.Control.CongresoModel = PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Models.ConsultaGeneral;
        base.Control.BtnBuscar().off('click');
        base.Control.BtnBuscar().on('click', base.Event.BtnBuscarClick);
        base.Control.BtnLimpiar().off('click');
        base.Control.BtnLimpiar().on('click', base.Event.BtnLimpiarClick);
        base.Control.BtnVerDetallle().off('click');
        base.Control.BtnVerDetallle().on('click', base.Event.BtnVerDetallleClick);
        base.Control.BtnVerDocumentos().off('click');
        base.Control.BtnVerDocumentos().on('click', base.Event.BtnDescagarDocumentoClick);
        base.Control.BtnVerSeguimiento().off('click');
        base.Control.BtnVerSeguimiento().on('click', base.Event.BtnVerSeguimientoClick);
        base.Control.BtnVerAnexo().off('click');
        base.Control.BtnVerAnexo().on('click', base.Event.BtnVerAnexoClick);
        base.Control.BtnExportarExcel().off('click');
        base.Control.BtnExportarExcel().on('click', base.Event.BtnExportExcelClick);
        base.Control.BtnExportarExcelSeguimiento().off('click');
        base.Control.BtnExportarExcelSeguimiento().on('click', base.Event.BtnExportExcelSeguimientoClick);
        base.Control.BtnExportarPDF().off('click');
        base.Control.BtnExportarPDF().on('click', base.Event.BtnExportPDFClick);

        base.Control.SlcDependencia().off('change');
        base.Control.SlcDependencia().on('change', base.Event.SlcDependenciaChange);
        base.Control.SlcDependenciaDestino().off('change');
        base.Control.SlcDependenciaDestino().on('change', base.Event.SlcDependenciaDestinoChange);
        base.Function.CrearGrid();                 
        
        base.Control.SlcDependencia().select2();
        base.Control.SlcTipoDocumento().select2();
        base.Control.SlcEstado().select2();
        base.Control.SlcProcedimientoTupa().select2();

        base.Control.SlcEstadoDestino().select2();
        base.Control.SlcDependenciaDestino().select2();
        //base.Control.slcTipoDocumentoDestino().select2();
        base.Control.slcResponsableDestino().select2();
         

        options.ranges = {
            'Hoy': [moment(), moment()],
            'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Los últimos 7 días': [moment().subtract(6, 'days'), moment()],
            'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
            'Este Mes': [moment().startOf('month'), moment().endOf('month')],
            'El Mes Pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'Este Año': [moment().startOf('year'), moment().endOf('year')],
            'El Año Pasado': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
        }; 
        base.Control.TxtFechaEmsion().daterangepicker(options, function (start, end, label) {
            base.Control.Filtros.FechaInicial = start.format('DD/MM/YYYY');
            base.Control.Filtros.FechaFinal = end.format('DD/MM/YYYY'); 
        });
        
        base.Control.GlyphiconFechaEmsion().click(function () {            
            base.Control.TxtFechaEmsion().click(); 
        });
        base.Control.DivAdvanceFilter().hide();
        base.Control.BtnAdvanceFilter().off('click');
        base.Control.BtnAdvanceFilter().on('click', base.Event.BtnAdvanceFilterClick);
                 
        //base.Control.TxtFechaEmsion().val('');
        if (MesActual.length == 1) { mes = '0' + MesActual; } else { mes = MesActual; }
        base.Control.Filtros.FechaInicial = d.getDate() + '/' + mes + '/' + d.getFullYear();
        base.Control.Filtros.FechaFinal = d.getDate() + '/' + mes + '/' + d.getFullYear(); 
        base.Event.BtnBuscarClick();

    };

    base.Control = {
        GrdResultado: null,
        SeleccionadoRegistro: [],
        BtnAdvanceFilter: function () { return $('#btnAdvanceFilter') },
        DivAdvanceFilter: function () { return $('#divAdvanceFilter') },
        SlcDependencia: function () { return $('#slcDependencia') },
        SlcTipoDocumento: function () { return $('#slcTipoDocumento') },
        SlcEstado: function () { return $('#slcEstado') },
        SlcProcedimientoTupa: function () { return $('#slcProcedimientoTupa') },
        TxtNroDocumento: function () { return $('#txtNroDocumento') },
        TxtNroExpediente: function () { return $('#txtNroExpediente') },
        TxtAsunto: function () { return $('#txtAsunto') },
        TxtFechaEmsion: function () { return $('#txtFechaEmsion'); },
        TxtRemite: function () { return $('#txtRemite'); },

        BtnBuscar: function () { return $('#btnBuscar'); },
        BtnLimpiar: function () { return $('#btnLimpiar'); },
        BtnVerDetallle: function () { return $('#btnVerDetallle'); },
        BtnVerDocumentos: function () { return $('#btnVerDocumentos'); },
        BtnVerSeguimiento: function () { return $('#btnVerSeguimiento'); },
        BtnVerAnexo: function () { return $('#btnVerAnexos'); },
        BtnExportarExcel: function () { return $('#btnExportarExcel'); },
        BtnExportarExcelSeguimiento: function () { return $('#btnExportarExcelSeguimiento'); },
        BtnExportarPDF: function () { return $('#btnExportarPDF'); },

        GlyphiconFechaEmsion: function () { return $('#glyphiconFechaEmsion'); },

        SlcEstadoDestino: function () { return $('#slcEstadoDestino'); },
        SlcDependenciaDestino: function () { return $('#slcDependenciaDestino'); },
        slcTipoDocumentoDestino: function () { return $('#slcTipoDocumentoDestino'); },
        slcResponsableDestino: function () { return $('#slcResponsableDestino'); },


        ModalVistaParcial: function () { return $('#VistaParcial'); },
        Mensaje: new Gob.Pcm.UI.Web.Components.Message(), 
        CongresoModel: null,
        Filtros:{},
    };

    base.Event = {
        BtnAdvanceFilterClick: function () {
           
            if (base.Control.DivAdvanceFilter().is(":visible")) {
                base.Control.DivAdvanceFilter().hide();
            }
            else {
                base.Control.DivAdvanceFilter().show();
            }
        },
        BtnDescagarDocumentoClick: function () { 
                var filtro = {Anio:null,NroEmision:null };                 
                if (base.Control.SeleccionadoRegistro != undefined) {
                    $.each(base.Control.SeleccionadoRegistro, function (index, value) {
                        filtro.Anio = value.Anio;
                        filtro.NroEmision = value.NroEmision;
                    });
                }
                if (filtro.Anio != null && filtro.NroEmision!=null) {
                    Gob.Pe.UI.Web.Components.Util.RedirectPost(PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Actions.DescargarDocumento, filtro);
                }
                else {
                    //BootstrapDialog.show({
                    //    title: 'Say-hello dialog',
                    //    message: 'Hi Apple!'
                    //});
                    base.Control.Mensaje.Warning({ title: 'Advertencia', message: 'Debe seleccionar un registros.' });
                }
                
        },
        BtnExportExcelClick: function () {
            base.Control.Filtros.Dependencia = base.Control.SlcDependencia().select2("val");
            base.Control.Filtros.TipoDocumento = base.Control.SlcTipoDocumento().select2("val");
            base.Control.Filtros.Estado = base.Control.SlcEstado().select2("val");
            base.Control.Filtros.NroDocumento = base.Control.TxtNroDocumento().val();
            base.Control.Filtros.NroExpediente = base.Control.TxtNroExpediente().val();
            base.Control.Filtros.Asunto = base.Control.TxtAsunto().val();
            base.Control.Filtros.Remite = base.Control.TxtRemite().val();
            base.Control.Filtros.CodigoProcedimiento = base.Control.SlcProcedimientoTupa().select2("val");
            base.Control.Filtros.DependenciaDestino = base.Control.SlcDependenciaDestino().select2("val");
            base.Control.Filtros.ResponsableDestino = base.Control.slcResponsableDestino().select2("val");
            base.Control.Filtros.EstadoDestino = base.Control.SlcEstadoDestino().select2("val");
            Gob.Pe.UI.Web.Components.Util.RedirectPost(PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Actions.ExportarExcel, base.Control.Filtros);
        },
        BtnExportExcelSeguimientoClick: function () {
            base.Control.Filtros.Dependencia = base.Control.SlcDependencia().select2("val");
            base.Control.Filtros.TipoDocumento = base.Control.SlcTipoDocumento().select2("val");
            base.Control.Filtros.Estado = base.Control.SlcEstado().select2("val");
            base.Control.Filtros.NroDocumento = base.Control.TxtNroDocumento().val();
            base.Control.Filtros.NroExpediente = base.Control.TxtNroExpediente().val();
            base.Control.Filtros.Asunto = base.Control.TxtAsunto().val();
            base.Control.Filtros.Remite = base.Control.TxtRemite().val();
            base.Control.Filtros.CodigoProcedimiento = base.Control.SlcProcedimientoTupa().select2("val");
            base.Control.Filtros.DependenciaDestino = base.Control.SlcDependenciaDestino().select2("val");
            base.Control.Filtros.ResponsableDestino = base.Control.slcResponsableDestino().select2("val");
            base.Control.Filtros.EstadoDestino = base.Control.SlcEstadoDestino().select2("val");
            Gob.Pe.UI.Web.Components.Util.RedirectPost(PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Actions.ExportarExcelSeguimiento, base.Control.Filtros);
        },
        BtnExportPDFClick: function () {
            base.Control.Filtros.Dependencia = base.Control.SlcDependencia().select2("val");
            base.Control.Filtros.TipoDocumento = base.Control.SlcTipoDocumento().select2("val");
            base.Control.Filtros.Estado = base.Control.SlcEstado().select2("val");
            base.Control.Filtros.NroDocumento = base.Control.TxtNroDocumento().val();
            base.Control.Filtros.NroExpediente = base.Control.TxtNroExpediente().val();
            base.Control.Filtros.Asunto = base.Control.TxtAsunto().val();
            base.Control.Filtros.Remite = base.Control.TxtRemite().val();
            base.Control.Filtros.CodigoProcedimiento = base.Control.SlcProcedimientoTupa().select2("val");
            base.Control.Filtros.DependenciaDestino = base.Control.SlcDependenciaDestino().select2("val");
            base.Control.Filtros.ResponsableDestino = base.Control.slcResponsableDestino().select2("val");
            base.Control.Filtros.EstadoDestino = base.Control.SlcEstadoDestino().select2("val");
            Gob.Pe.UI.Web.Components.Util.RedirectPost(PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Actions.ExportarPDF, base.Control.Filtros);
        },
        BtnDescagarDocumentoReferenciaClick: function (thiss) {
            var filtro = { Anio: null, NroEmision: null };
            filtro.Anio = $(thiss).attr('anio');
            filtro.NroEmision = $(thiss).attr('nroemision');                  
            if (filtro.Anio != null && filtro.NroEmision != null) {
                Gob.Pe.UI.Web.Components.Util.RedirectPost(PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Actions.DescargarDocumento, filtro);
            }
            else {
                base.Control.Mensaje.Warning({ title: 'Advertencia', message: 'Debe seleccionar un registros.' });
            }

        },
        BtnBuscarClick: function () {
            'use strict' 
            base.Control.Filtros.Dependencia=base.Control.SlcDependencia().select2("val");
            base.Control.Filtros.TipoDocumento = base.Control.SlcTipoDocumento().select2("val");
            base.Control.Filtros.Estado = base.Control.SlcEstado().select2("val");
            base.Control.Filtros.NroDocumento = base.Control.TxtNroDocumento().val();
            base.Control.Filtros.NroExpediente = base.Control.TxtNroExpediente().val();
            base.Control.Filtros.Asunto = base.Control.TxtAsunto().val();
            base.Control.Filtros.Remite = base.Control.TxtRemite().val();
            base.Control.Filtros.CodigoProcedimiento = base.Control.SlcProcedimientoTupa().select2("val");
            base.Control.Filtros.DependenciaDestino = base.Control.SlcDependenciaDestino().select2("val");
            base.Control.Filtros.ResponsableDestino = base.Control.slcResponsableDestino().select2("val");
            base.Control.Filtros.EstadoDestino = base.Control.SlcEstadoDestino().select2("val");
            base.Control.GrdResultado.Load(base.Control.Filtros);
        },

        BtnLimpiarClick: function () {
            base.Control.SlcDependencia().select2("val", "");
            base.Event.SlcDependenciaChange();
            base.Control.SlcTipoDocumento().select2("val", "");
            base.Control.SlcEstado().select2("val", "");
            base.Control.TxtNroDocumento().val("");
            base.Control.TxtNroExpediente().val("");
            base.Control.TxtAsunto().val("");
            base.Control.SlcEstadoDestino().select2("val", "");
            base.Control.SlcDependenciaDestino().select2("val", "");
            base.Control.SlcProcedimientoTupa().select2("val", "");
            base.Control.TxtRemite().val("");
            base.Event.SlcDependenciaDestinoChange();
            base.Control.slcResponsableDestino().select2("val", "");
        },

        BtnVerDetallleClick: function () { 
            var filtro;
            if (base.Control.SeleccionadoRegistro != undefined) {
                $.each(base.Control.SeleccionadoRegistro, function (index, value) {                    
                    filtro = value.Anio + value.NroEmision + value.NroDestino;
                });
            }
            if (filtro != null) {
                var url = PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Actions.RecibidosIndex + filtro;                
                base.Control.ModalVistaParcial().load(url, function (result) {
                    $('#registro').modal({ show: true, keyboard: false });                    
                });
                
            }
        },
        BtnVerSeguimientoClick: function () { 
                    var filtro;
                    if (base.Control.SeleccionadoRegistro != undefined) {
                        $.each(base.Control.SeleccionadoRegistro, function (index, value) {                    
                            filtro = value.Anio + value.NroEmision + value.NroDestino;
                        });
                    }
                    if (filtro != null) {
                        var url = PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Actions.SeguimientoIndex + filtro;
                        base.Control.ModalVistaParcial().load(url, function (result) {
                            $('#registro').modal({ show: true, keyboard: false });                    
                        });
                
                    }
         },
        BtnVerAnexoClick: function () { 
                    var filtro;
                    if (base.Control.SeleccionadoRegistro != undefined) {
                        $.each(base.Control.SeleccionadoRegistro, function (index, value) {                    
                            filtro = value.Anio + value.NroEmision + value.NroDestino;
                        });
                    }
                    if (filtro != null) {
                        var url = PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Actions.DocumentoAnexoIndex + filtro;
                        base.Control.ModalVistaParcial().load(url, function (result) {
                            $('#registro').modal({ show: true, keyboard: false });                    
                        });
                
                    }
         },
        SlcDependenciaChange: function () {
            'use strict'
            base.Ajax.AjaxBuscarTipoDocumento.send({
                Dependencia: base.Control.SlcDependencia().select2("val")
            });            
        },
        SlcDependenciaDestinoChange: function () {
            'use strict'
            base.Ajax.AjaxBuscarEmpleadoDependencia.send({
                Dependencia: base.Control.SlcDependenciaDestino().select2("val")
            });            
        },
         

        AjaxGrdResultadoSuccess: function (row, data) {
             
        },
        BtnGridSelectionRowClick: function (that, row) {
            base.Control.SeleccionadoRegistro =  [];
            $.each(row, function (index, value) {
                var indexItem = base.Control.SeleccionadoRegistro.indexOf(value);
                if ($(that).is(":checked")) { 
                    if (indexItem == -1) {
                        base.Control.SeleccionadoRegistro.push(value);
                    }
                } else {
                    if (indexItem != -1) {
                        base.Control.SeleccionadoRegistro.splice(indexItem, 1);
                    }
                }
            })
        },

        AjaxBuscarTipoDocumentoSuccess: function (resultado) {
            base.Control.SlcTipoDocumento().empty();
            base.Control.SlcTipoDocumento().append(new Option(PCM.Reclamo.Presentacion.Base.GenericoResource.EtiquetaTodos, ""));
            $.each(resultado.Result, function (index, value) {
                base.Control.SlcTipoDocumento().append(new Option(value.Documento, value.Codigo));
            });
        },
        AjaxBuscarEmpleadoDependenciaSuccess: function (resultado) {
            base.Control.slcResponsableDestino().empty();
            base.Control.slcResponsableDestino().append(new Option(PCM.Reclamo.Presentacion.Base.GenericoResource.EtiquetaTodos, ""));
            $.each(resultado.Result, function (index, value) {
                base.Control.slcResponsableDestino().append(new Option(value.Descripcion, value.Codigo));
            });
        },
    };

    base.Ajax = {
        AjaxBuscarTipoDocumento: new Gob.Pcm.UI.Web.Components.Ajax(
       {
           action: PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Actions.BuscarTipoDocumento,
           autoSubmit: false,
           onSuccess: base.Event.AjaxBuscarTipoDocumentoSuccess
       }),
        AjaxBuscarEmpleadoDependencia: new Gob.Pcm.UI.Web.Components.Ajax(
       {
           action: PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Actions.BuscarEmpleadoDependencia,
           autoSubmit: false,
           onSuccess: base.Event.AjaxBuscarEmpleadoDependenciaSuccess
       }),
    };

    base.Function = {
        CrearGrid: function () {
            var columns = new Array();
             
            columns.push({
                data: 'NumeroFila', 
                title: PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Resources.GridItem,
                
                width: "5%"
            });
            columns.push({ 
                data: 'FechaEmision',
                title: PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Resources.GridFechaEmision, 
                width: "10%", 
            });

            columns.push({
                data: 'Remite',
                title: PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Resources.GridRemite,
                width: "16%",
            });
            columns.push({
                data: 'TipoDocumento',
                title: PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Resources.GridTipoDocumento,
                mRender: function (data, type, full) {
                    return full.TipoDocumento + '</br>' + (full.NroDocumento == null ? "" : full.NroDocumento);
                },
                width: "14%"
            });
            //columns.push({
            //    data: 'NroDocumento',
            //    title: PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Resources.GridNroDocumento, 
            //    width:  "7%", 
            //});
           
            columns.push({
                data: 'Asunto',
                title: PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Resources.GridAsunto, 
                width: "20%",
                'class': 'justify',
            });            
            columns.push({
                data: 'Motivo',
                title: PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Resources.GridMotivo, 
                width: "12%"
            });             
            columns.push({
                data: 'NroExpediente',
                title: PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Resources.GridNroExpediente, 
                width: "7%"
            });
            columns.push({
                data: 'EstadoEmi',
                title: PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Resources.GridEstadoEmi,
                width: "7%"
            });
            columns.push({
                data: 'Destinatario',
                title: PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Resources.GridDestinatario,
                width: "16%"
            });
            columns.push({
                data: 'EstadoDes',
                title: PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Resources.GridEstadoDes,
                width: "7%"
            });

            columns.push({
                data: 'FechaRecepcion',
                title: PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Resources.GridFechaRecepcion,
                width: "10%",
            });

            //columns.push({
            //    "data": "", "title": Pe.Stracon.SGD.Presentacion.Base.GenericoResource.GridAcciones,
            //    "class": "controls",
            //    width: "5%",
            //    actionButtons: [
            //        //{ type: Gob.Pcm.UI.Web.Components.GridAction.ViewObjetivo, event: { on: 'click', callBack: base.Event.BtnGridEditClick } },
            //    ]
            //});


            base.Control.GrdResultado = new Gob.Pcm.UI.Web.Components.Grid({
                renderTo: 'divGrdResult',
                columns: columns,
                hasSelectionRows: true,                 
                ordering: true,
                hasTypeSelectionRows: "radio",
                columnDefs: [{ aTargets: [0], "orderable": false }, { aTargets: [1], "orderable": false }],
                selectionRowsEvent: { on: 'click', callBack: base.Event.BtnGridSelectionRowClick },                
                proxy: {
                    url: PCM.Reclamo.Presentacion.Search.ConsultaGeneral.Actions.Buscar,
                    source: 'Result'
                },
                returnCallbackComplete: base.Event.AjaxGrdResultadoSuccess,
            });
        },


         
    };
};

