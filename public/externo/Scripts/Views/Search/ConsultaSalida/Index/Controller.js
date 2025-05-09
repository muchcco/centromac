﻿/// <summary>
/// Script de la aplicación
/// </summary>
/// <remarks>
/// Creacion: 	WECM 01032018
/// </remarks>
ns('PCM.Reclamo.Presentacion.Search.ConsultaSalida.Index');
PCM.Reclamo.Presentacion.Search.ConsultaSalida.Index.Controller = function () {
    var base = this;
    var options = {};
    var d = new Date();
    var mes = '';
    var MesActual = (d.getMonth() + 1) + '';
    base.Ini = function () {
        'use strict'
        //base.Control.CongresoModel = PCM.Reclamo.Presentacion.Search.ConsultaSalida.Models.ConsultaSalida;
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
        base.Control.SlcVencimiento().select2();

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
        SlcVencimiento: function () { return $('#slcVencimiento') },
        BtnBuscar: function () { return $('#btnBuscar'); },
        BtnLimpiar: function () { return $('#btnLimpiar'); },
        BtnVerDetallle: function () { return $('#btnVerDetallle'); },
        BtnVerDocumentos: function () { return $('#btnVerDocumentos'); },
        BtnVerSeguimiento: function () { return $('#btnVerSeguimiento'); },
        BtnVerAnexo: function () { return $('#btnVerAnexos'); },
        BtnExportarExcel: function () { return $('#btnExportarExcel'); },
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
                    Gob.Pe.UI.Web.Components.Util.RedirectPost(PCM.Reclamo.Presentacion.Search.ConsultaSalida.Actions.DescargarDocumento, filtro);
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
            base.Control.Filtros.DependenciaDestino = base.Control.SlcDependenciaDestino().select2("val");
            base.Control.Filtros.ResponsableDestino = base.Control.slcResponsableDestino().select2("val");
            base.Control.Filtros.EstadoDestino = base.Control.SlcEstadoDestino().select2("val"); 
            Gob.Pe.UI.Web.Components.Util.RedirectPost(PCM.Reclamo.Presentacion.Search.ConsultaSalida.Actions.ExportarExcel, base.Control.Filtros);
        },
        BtnExportPDFClick: function () {
            base.Control.Filtros.Dependencia = base.Control.SlcDependencia().select2("val");
            base.Control.Filtros.TipoDocumento = base.Control.SlcTipoDocumento().select2("val");
            base.Control.Filtros.Estado = base.Control.SlcEstado().select2("val");
            base.Control.Filtros.NroDocumento = base.Control.TxtNroDocumento().val();
            base.Control.Filtros.NroExpediente = base.Control.TxtNroExpediente().val();
            base.Control.Filtros.Asunto = base.Control.TxtAsunto().val();
            base.Control.Filtros.Remite = base.Control.TxtRemite().val(); 
            base.Control.Filtros.DependenciaDestino = base.Control.SlcDependenciaDestino().select2("val");
            base.Control.Filtros.ResponsableDestino = base.Control.slcResponsableDestino().select2("val");
            base.Control.Filtros.EstadoDestino = base.Control.SlcEstadoDestino().select2("val"); 
            Gob.Pe.UI.Web.Components.Util.RedirectPost(PCM.Reclamo.Presentacion.Search.ConsultaSalida.Actions.ExportarPDF, base.Control.Filtros);
        },
        BtnDescagarDocumentoReferenciaClick: function (thiss) {
            var filtro = { Anio: null, NroEmision: null };
            filtro.Anio = $(thiss).attr('anio');
            filtro.NroEmision = $(thiss).attr('nroemision');                  
            if (filtro.Anio != null && filtro.NroEmision != null) {
                Gob.Pe.UI.Web.Components.Util.RedirectPost(PCM.Reclamo.Presentacion.Search.ConsultaSalida.Actions.DescargarDocumento, filtro);
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
                var url = PCM.Reclamo.Presentacion.Search.ConsultaSalida.Actions.RecibidosIndex + filtro;                
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
                        var url = PCM.Reclamo.Presentacion.Search.ConsultaSalida.Actions.SeguimientoIndex + filtro;
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
                        var url = PCM.Reclamo.Presentacion.Search.ConsultaSalida.Actions.DocumentoAnexoIndex + filtro;
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
           action: PCM.Reclamo.Presentacion.Search.ConsultaSalida.Actions.BuscarTipoDocumento,
           autoSubmit: false,
           onSuccess: base.Event.AjaxBuscarTipoDocumentoSuccess
       }),
        AjaxBuscarEmpleadoDependencia: new Gob.Pcm.UI.Web.Components.Ajax(
       {
           action: PCM.Reclamo.Presentacion.Search.ConsultaSalida.Actions.BuscarEmpleadoDependencia,
           autoSubmit: false,
           onSuccess: base.Event.AjaxBuscarEmpleadoDependenciaSuccess
       }),
    };

    base.Function = {
        CrearGrid: function () {
            var columns = new Array();
             
            columns.push({
                data: 'NumeroFila', 
                title: PCM.Reclamo.Presentacion.Search.ConsultaSalida.Resources.GridItem,
                
                width: "5%"
            });
            columns.push({ 
                data: 'FechaEmision',
                title: PCM.Reclamo.Presentacion.Search.ConsultaSalida.Resources.GridFechaEmision, 
                width: "10%", 
            });

            columns.push({
                data: 'Remite',
                title: PCM.Reclamo.Presentacion.Search.ConsultaSalida.Resources.GridRemite,
                width: "16%",
            });
            columns.push({
                data: 'TipoDocumento',
                title: PCM.Reclamo.Presentacion.Search.ConsultaSalida.Resources.GridTipoDocumento,
                mRender: function (data, type, full) {
                    return full.TipoDocumento + '</br>' + (full.NroDocumento == null ? "" : full.NroDocumento);
                },
                width: "14%"
            });
            //columns.push({
            //    data: 'NroDocumento',
            //    title: PCM.Reclamo.Presentacion.Search.ConsultaSalida.Resources.GridNroDocumento, 
            //    width:  "7%", 
            //});
           
            columns.push({
                data: 'Asunto',
                title: PCM.Reclamo.Presentacion.Search.ConsultaSalida.Resources.GridAsunto, 
                width: "20%",
                'class': 'justify',
            });            
                   
            columns.push({
                data: 'NroExpediente',
                title: PCM.Reclamo.Presentacion.Search.ConsultaSalida.Resources.GridNroExpediente, 
                width: "7%"
            });
            columns.push({
                data: 'EstadoEmi',
                title: PCM.Reclamo.Presentacion.Search.ConsultaSalida.Resources.GridEstadoEmi,
                width: "7%"
            });
            columns.push({
                data: 'Destinatario',
                title: PCM.Reclamo.Presentacion.Search.ConsultaSalida.Resources.GridDestinatario,
                width: "16%"
            });
            //columns.push({
            //    data: 'EstadoDes',
            //    title: PCM.Reclamo.Presentacion.Search.ConsultaSalida.Resources.GridEstadoDes,
            //    width: "7%"
            //});

            //columns.push({
            //    data: 'FechaRecepcion',
            //    title: PCM.Reclamo.Presentacion.Search.ConsultaSalida.Resources.GridFechaRecepcion,
            //    width: "10%",
            //});

            //columns.push({
            //    data: '',
            //    title: PCM.Reclamo.Presentacion.Search.ConsultaSalida.Resources.GridFechaPlazo,
            //    width: '5%',
            //    mRender: function (data, type, full) {
            //        var coloresVencimiento = {
            //            0: { "text": "Normal", "color": "#009BE6", "descrip": "Documentos sin días límites de atención.", "cssClass": "estadoVenNormal" },
            //            1: { "text": "Proximo a vencer", "color": "#E97200", "descrip": "Con vencimiento menor o igual a 2 días.", "cssClass": "estadoVenProximoVencer" },
            //            2: { "text": "Vencido", "color": "#FFBABA", "descrip": "Con la fecha de vencimiento anterior a la fecha actual.", "cssClass": "estadoVenVencido" },
            //            3: { "text": "Vence hoy", "color": "#D00000", "descrip": "Fecha de vencimiento igual a la fecha actual.", "cssClass": "estadoVenVenceHoy" },
            //            4: { "text": "Por vencer", "color": "#D9D900", "descrip": "Tiempo de vecimiento mayor a 2 días.", "cssClass": "estadoVenPorVencer" },
            //            5: { "text": "Atendido", "color": "#009F01", "descrip": "Documentos con la fecha de atención o archivamiento.", "cssClass": "estadoVenAtendido" }
            //        };

            //        if (full.EstadoVencimiento == '0') {
            //            return '<div class="alert alert-success" style="border-radius: 15px;padding: 15px;width: 5px;margin-left: 20px;background-color: #009BE6;">  </div><strong>Normal</strong>';
            //        }
            //        if (full.EstadoVencimiento == '1') {
            //            return '<div class="alert alert-success" style="border-radius: 15px;padding: 15px;width: 5px;margin-left: 20px;background-color: #FEEFB3;">  </div><strong>' + full.FechaVencimiento + '<br>Próximo a Vencer</strong>';
            //        }
            //        if (full.EstadoVencimiento == '2') {
            //            return '<div class="alert alert-success" style="border-radius: 15px;padding: 15px;width: 5px;margin-left: 20px;background-color: #FFBABA;">  </div><strong>' + full.FechaVencimiento + '<br>Vencido</strong>';
            //        }
            //        if (full.EstadoVencimiento == '3') {
            //            return '<div class="alert alert-success" style="border-radius: 15px;padding: 15px;width: 5px;margin-left: 20px;background-color: #FFCCBA;">  </div><strong>' + full.FechaVencimiento + '<br>Vence Hoy</strong>';
            //        }
            //        if (full.EstadoVencimiento == '4') {
            //            return '<div class="alert alert-success" style="border-radius: 15px;padding: 15px;width: 5px;margin-left: 20px;background-color: #DFF2BF;">  </div><strong>' + full.FechaVencimiento + '<br>Por vencer</strong>';
            //        }
            //        if (full.EstadoVencimiento == '5') {
            //            return '<div class="alert alert-success" style="border-radius: 15px;padding: 15px;width: 5px;margin-left: 20px;background-color: #BDE5F8;">  </div><strong>' + full.FechaVencimiento + '<br>Atendido</strong>';
            //        }


            //    }
            //});

            base.Control.GrdResultado = new Gob.Pcm.UI.Web.Components.Grid({
                renderTo: 'divGrdResult',
                columns: columns,
                hasSelectionRows: true,                 
                ordering: true,
                hasTypeSelectionRows: "radio",
                columnDefs: [{ aTargets: [0], "orderable": false }, { aTargets: [1], "orderable": false } ],
                selectionRowsEvent: { on: 'click', callBack: base.Event.BtnGridSelectionRowClick },                
                proxy: {
                    url: PCM.Reclamo.Presentacion.Search.ConsultaSalida.Actions.Buscar,
                    source: 'Result'
                },
                returnCallbackComplete: base.Event.AjaxGrdResultadoSuccess,
            });
        },


         
    };
};

