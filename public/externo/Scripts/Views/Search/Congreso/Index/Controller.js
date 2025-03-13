/// <summary>
/// Script de la aplicación
/// </summary>
/// <remarks>
/// Creacion: 	WECM 14092017
/// </remarks>
ns('PCM.Reclamo.Presentacion.Search.Congreso.Index');
PCM.Reclamo.Presentacion.Search.Congreso.Index.Controller = function () {
    var base = this;
    var options = {};
    var d = new Date();
    var mes = '';
    var MesActual = (d.getMonth() + 1) + '';
    base.Ini = function () {
        'use strict'
        //base.Control.CongresoModel = PCM.Reclamo.Presentacion.Search.Congreso.Models.Congreso;

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

        //base.Control.SlcDependencia().off('change');
        //base.Control.SlcDependencia().on('change', base.Event.SlcDependenciaChange);

        base.Function.CrearGrid();    
        base.Control.SlcDependencia().select2();
        base.Control.SlcTipoDocumento().select2();
        base.Control.SlcEstado().select2();
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
        //base.Control.TxtFechaEmsion().val('');
        if (MesActual.length == 1) { mes = '0' + MesActual; } else { mes = MesActual; }
        base.Control.Filtros.FechaInicial = d.getDate() + '/' + mes + '/' + d.getFullYear();
        base.Control.Filtros.FechaFinal = d.getDate() + '/' + mes + '/' + d.getFullYear();
        base.Event.BtnBuscarClick();
        base.Control.DivAdvanceFilter().hide();
        base.Control.BtnAdvanceFilter().off('click');
        base.Control.BtnAdvanceFilter().on('click', base.Event.BtnAdvanceFilterClick);
    };

    base.Control = {
        GrdResultado: null,
        SeleccionadoRegistro: [],
        BtnAdvanceFilter: function () { return $('#btnAdvanceFilter') },
        SlcDependencia: function () { return $('#slcDependencia') },
        SlcTipoDocumento: function () { return $('#slcTipoDocumento') },
        DivAdvanceFilter: function () { return $('#divAdvanceFilter') },
        SlcEstado: function () { return $('#slcEstado') },
        SlcVencimiento: function () { return $('#slcVencimiento') },
        SlcReiterativo: function () { return $('#slcReiterativo') },
        SlcTipoInvitacion: function () { return $('#slcTipoInvitacion') },
        TxtNroDocumento: function () { return $('#txtNroDocumento') },
        TxtNroExpediente: function () { return $('#txtNroExpediente') },
        TxtAsunto: function () { return $('#txtAsunto') },
        TxtFechaEmsion: function () { return $('#txtFechaEmsion'); },
        TxtCongresista: function () { return $('#txtCongresista'); },
         

        BtnBuscar: function () { return $('#btnBuscar'); },
        BtnLimpiar: function () { return $('#btnLimpiar'); },
        BtnVerDetallle: function () { return $('#btnVerDetallle'); },
        BtnVerDocumentos: function () { return $('#btnVerDocumentos'); },
        BtnVerSeguimiento: function () { return $('#btnVerSeguimiento'); },
        BtnVerAnexo: function () { return $('#btnVerAnexos'); },
        BtnExportarExcel: function () { return $('#btnExportarExcel'); },
        BtnExportarPDF: function () { return $('#btnExportarPDF'); },

        GlyphiconFechaEmsion: function () { return $('#glyphiconFechaEmsion'); },

        ModalVistaParcial: function () { return $('#VistaParcial'); },
        Mensaje: new Gob.Pcm.UI.Web.Components.Message(), 
        CongresoModel: null,
        Filtros: {},
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
        BtnExportExcelClick: function () {
            'use strict'
            base.Control.Filtros.Dependencia = base.Control.SlcDependencia().select2("val");
            base.Control.Filtros.TipoDocumento = base.Control.SlcTipoDocumento().select2("val");
            base.Control.Filtros.Estado = base.Control.SlcEstado().select2("val");
            base.Control.Filtros.NroDocumento = base.Control.TxtNroDocumento().val();
            base.Control.Filtros.NroExpediente = base.Control.TxtNroExpediente().val();
            base.Control.Filtros.Asunto = base.Control.TxtAsunto().val();
            base.Control.Filtros.Remite = base.Control.TxtCongresista().val();
            base.Control.Filtros.EstadoVencimiento = base.Control.SlcVencimiento().select2("val");
            base.Control.Filtros.Reiterativo = base.Control.SlcReiterativo().val();
            base.Control.Filtros.TipoInvitacion = base.Control.SlcTipoInvitacion().val();
            Gob.Pe.UI.Web.Components.Util.RedirectPost(PCM.Reclamo.Presentacion.Search.Congreso.Actions.ExportarExcel, base.Control.Filtros);
        },
        BtnExportPDFClick: function () {
            'use strict'
            base.Control.Filtros.Dependencia = base.Control.SlcDependencia().select2("val");
            base.Control.Filtros.TipoDocumento = base.Control.SlcTipoDocumento().select2("val");
            base.Control.Filtros.Estado = base.Control.SlcEstado().select2("val");
            base.Control.Filtros.NroDocumento = base.Control.TxtNroDocumento().val();
            base.Control.Filtros.NroExpediente = base.Control.TxtNroExpediente().val();
            base.Control.Filtros.Asunto = base.Control.TxtAsunto().val();
            base.Control.Filtros.Remite = base.Control.TxtCongresista().val();
            base.Control.Filtros.EstadoVencimiento = base.Control.SlcVencimiento().select2("val");
            base.Control.Filtros.Reiterativo = base.Control.SlcReiterativo().val();
            base.Control.Filtros.TipoInvitacion = base.Control.SlcTipoInvitacion().val();
            Gob.Pe.UI.Web.Components.Util.RedirectPost(PCM.Reclamo.Presentacion.Search.Congreso.Actions.ExportarPDF, base.Control.Filtros);
        },
        BtnDescagarDocumentoClick: function () {
            'use strict'
            var filtro = { Anio: null, NroEmision: null };
            if (base.Control.SeleccionadoRegistro != undefined) {
                $.each(base.Control.SeleccionadoRegistro, function (index, value) {
                    filtro.Anio = value.Anio;
                    filtro.NroEmision = value.NroEmision;
                });
            }
            if (filtro.Anio != null && filtro.NroEmision != null) {
                Gob.Pe.UI.Web.Components.Util.RedirectPost(PCM.Reclamo.Presentacion.Search.Congreso.Actions.DescargarDocumento, filtro);
            }
            else {
                base.Control.Mensaje.Warning({ title: 'Advertencia', message: 'Debe seleccionar un registros.' });
            }

        },
        BtnDescagarDocumentoReferenciaClick: function (thiss) {
            'use strict'
            var filtro = { Anio: null, NroEmision: null };
            filtro.Anio = $(thiss).attr('anio');
            filtro.NroEmision = $(thiss).attr('nroemision');
            if (filtro.Anio != null && filtro.NroEmision != null) {
                Gob.Pe.UI.Web.Components.Util.RedirectPost(PCM.Reclamo.Presentacion.Search.Congreso.Actions.DescargarDocumento, filtro);
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
            base.Control.Filtros.Remite = base.Control.TxtCongresista().val();
            base.Control.Filtros.EstadoVencimiento = base.Control.SlcVencimiento().select2("val");
            base.Control.Filtros.Reiterativo = base.Control.SlcReiterativo().val();
            base.Control.Filtros.TipoInvitacion = base.Control.SlcTipoInvitacion().val();
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
            base.Control.SlcReiterativo().val("");
            base.Control.SlcTipoInvitacion().val("")
        },

        BtnVerDetallleClick: function () {
            'use strict'
            var filtro;
            if (base.Control.SeleccionadoRegistro != undefined) {
                $.each(base.Control.SeleccionadoRegistro, function (index, value) {
                    filtro = value.Anio + value.NroEmision + value.NroDestino;
                });
            }
            if (filtro != null) {
                var url = PCM.Reclamo.Presentacion.Search.Congreso.Actions.RecibidosIndex + filtro;
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
                var url = PCM.Reclamo.Presentacion.Search.Congreso.Actions.SeguimientoIndex + filtro;
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
                var url = PCM.Reclamo.Presentacion.Search.Congreso.Actions.DocumentoAnexoIndex + filtro;
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

         
        AjaxGrdResultadoSuccess: function (row, data) {
             
        },
        BtnGridSelectionRowClick: function (that, row) {
            base.Control.SeleccionadoRegistro = [];
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
        
    };

    base.Ajax = {
        AjaxBuscarTipoDocumento: new Gob.Pcm.UI.Web.Components.Ajax(
       {
           action: PCM.Reclamo.Presentacion.Search.Congreso.Actions.BuscarTipoDocumento,
           autoSubmit: false,
           onSuccess: base.Event.AjaxBuscarTipoDocumentoSuccess
       }),
         
    };

    base.Function = {
        CrearGrid: function () {
            var columns = new Array();
             
            columns.push({
                data: 'NumeroFila', 
                title: PCM.Reclamo.Presentacion.Search.Congreso.Resources.GridItem,
                width: "5%"
            });
            columns.push({ 
                data: 'FechaEmision',
                title: PCM.Reclamo.Presentacion.Search.Congreso.Resources.GridFechaEmision, 
                width: "10%", 
            });
            columns.push({
                data: 'Remite',
                title: PCM.Reclamo.Presentacion.Search.Congreso.Resources.GridCongreso,
                width: "16%",
            });
            columns.push({
                data: 'Reiterativo',
                title: PCM.Reclamo.Presentacion.Search.Congreso.Resources.GridReiterativo,
                width: "7%",
                mRender: function (data, type, full) {
                    return (full.Reiterativo == '1' ? "SI" : "");
                }
            });
            columns.push({
                data: 'TipoDocumento',
                title: PCM.Reclamo.Presentacion.Search.Congreso.Resources.GridTipoDocumento,
                mRender: function (data, type, full) {
                    return full.TipoDocumento + '</br>' + (full.NroDocumento == null ? "" : full.NroDocumento);
                },
                width: "14%"
            });
            
            
            columns.push({
                data: 'Asunto',
                title: PCM.Reclamo.Presentacion.Search.Congreso.Resources.GridAsunto, 
                width: "23%",
                'class': 'justify',
            });
           
            //columns.push({
            //    data: 'Motivo',
            //    title: PCM.Reclamo.Presentacion.Search.Congreso.Resources.GridMotivo, 
            //    width: "7%"
            //});
            columns.push({
                data: 'AtensionSG',
                title: PCM.Reclamo.Presentacion.Search.Congreso.Resources.GridAtensionSG,
                width: "10%"
            });
            columns.push({
                data: 'NroExpediente',
                title: PCM.Reclamo.Presentacion.Search.Congreso.Resources.GridNroExpediente, 
                width: "7%"
            });

            columns.push({
                data: 'EstadoEmi',
                title: PCM.Reclamo.Presentacion.Search.Congreso.Resources.GridEstadoEmi,
                width: "7%"
            });
            columns.push({
                data: 'Destinatario',
                title: PCM.Reclamo.Presentacion.Search.Congreso.Resources.GridDestinatario,
                width: "16%"
            });
            columns.push({
                data: 'EstadoDes',
                title: PCM.Reclamo.Presentacion.Search.Congreso.Resources.GridEstadoDes,
                width: "7%"
            });
             
             
            columns.push({
                data: '',
                title: PCM.Reclamo.Presentacion.Search.Congreso.Resources.GridFechaPlazo,
                width: '5%',
                mRender: function (data, type, full) { 
                    var coloresVencimiento = {
                        0: { "text": "Normal", "color": "#009BE6", "descrip": "Documentos sin días límites de atención.", "cssClass": "estadoVenNormal" },
                        1: { "text": "Proximo a vencer", "color": "#E97200", "descrip": "Con vencimiento menor o igual a 2 días.", "cssClass": "estadoVenProximoVencer" },
                        2: { "text": "Vencido", "color": "#FFBABA", "descrip": "Con la fecha de vencimiento anterior a la fecha actual.", "cssClass": "estadoVenVencido" },
                        3: { "text": "Vence hoy", "color": "#D00000", "descrip": "Fecha de vencimiento igual a la fecha actual.", "cssClass": "estadoVenVenceHoy" },
                        4: { "text": "Por vencer", "color": "#D9D900", "descrip": "Tiempo de vecimiento mayor a 2 días.", "cssClass": "estadoVenPorVencer" },
                        5: { "text": "Atendido", "color": "#009F01", "descrip": "Documentos con la fecha de atención o archivamiento.", "cssClass": "estadoVenAtendido" }
                    };

                    if (full.EstadoVencimiento == '0') {
                        return '<div class="alert alert-success" style="border-radius: 15px;padding: 15px;width: 5px;margin-left: 20px;background-color: #009BE6;">  </div><strong>Normal</strong>';
                    }
                    if (full.EstadoVencimiento == '1') {
                        return '<div class="alert alert-success" style="border-radius: 15px;padding: 15px;width: 5px;margin-left: 20px;background-color: #FEEFB3;">  </div><strong>' + full.FechaVencimiento + '<br>Próximo a Vencer</strong>';
                    }
                    if (full.EstadoVencimiento == '2') {
                        return '<div class="alert alert-success" style="border-radius: 15px;padding: 15px;width: 5px;margin-left: 20px;background-color: #FFBABA;">  </div><strong>' + full.FechaVencimiento + '<br>Vencido</strong>';
                    }
                    if (full.EstadoVencimiento == '3') {
                        return '<div class="alert alert-success" style="border-radius: 15px;padding: 15px;width: 5px;margin-left: 20px;background-color: #FFCCBA;">  </div><strong>' + full.FechaVencimiento + '<br>Vence Hoy</strong>';
                    }
                    if (full.EstadoVencimiento == '4') {
                        return '<div class="alert alert-success" style="border-radius: 15px;padding: 15px;width: 5px;margin-left: 20px;background-color: #DFF2BF;">  </div><strong>' + full.FechaVencimiento + '<br>Por vencer</strong>';
                    }
                    if (full.EstadoVencimiento == '5') {
                        return '<div class="alert alert-success" style="border-radius: 15px;padding: 15px;width: 5px;margin-left: 20px;background-color: #BDE5F8;">  </div><strong>' + full.FechaVencimiento + '<br>Atendido</strong>';
                    }
                        
                     
                }
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
                scrollX: "",
                ordering: true,
                hasTypeSelectionRows:  "radio",
                columnDefs: [{ aTargets: [0], "orderable": false }, { aTargets: [1], "orderable": false }, { aTargets: [11], "orderable": false }, { aTargets: [12], "orderable": false }],
                selectionRowsEvent: { on: 'click', callBack: base.Event.BtnGridSelectionRowClick },                
                proxy: {
                    url: PCM.Reclamo.Presentacion.Search.Congreso.Actions.Buscar,
                    source: 'Result'
                },
                returnCallbackComplete: base.Event.AjaxGrdResultadoSuccess,
            });
        },


         
    };
};