/// <summary>
/// Script de la aplicación
/// </summary>
/// <remarks>
/// Creacion: 	WECM 14092017
/// </remarks>
ns('PCM.Reclamo.Presentacion.Register.Consulta.Index');
PCM.Reclamo.Presentacion.Register.Consulta.Index.Controller = function () {
    var base = this;
    base.Ini = function () {
        'use strict'
        base.Control.ConsultaModel = PCM.Reclamo.Presentacion.Register.Consulta.Models.Consulta;
        $('[data-toggle="tooltip"]').tooltip();
        base.Control.BtnGrabar().off('click');
        base.Control.BtnGrabar().on('click', base.Event.BtnGrabarClick);

        base.Control.ValRegistro = new Gob.Pcm.UI.Web.Components.Validator({
            form: base.Control.FormRegistro(),
            messages: PCM.Reclamo.Presentacion.Register.Consulta.Resources,
            validationsExtra: base.Function.ValidacionesExtras()
        });

        //base.Control.SlcDepartamento().select2();
        //base.Control.SlcProvincia().select2();
       
    };
    base.Control = {
        Mensaje: new Gob.Pcm.UI.Web.Components.Message(),
        FormRegistro: function () { return $('#frmRegistrarConsulta'); },
        ConsultaModel: null,
        BtnGrabar: function () { return $('#btnGrabar') },
        BtnBuscar: function () { return $('#btnBuscar') },
        BtnEnviarCorreo: function () { return $('#btnEnviarCorreo') },
        BtnImprimir: function () { return $('#btnImprimir') },
        SlcTipoDocumento: function () { return $('#slcTipoDocumento') },
        TxtNumeroDocumento: function () { return $('#txtNumeroDocumento') },
        TxtCodigoConsulta: function () { return $('#txtCodigoConsulta') },
        txtValorCaptcha: function () { return $('#txtValorCaptcha') },
        DivInformacion: function () { return $('#div-informacion') },
        DivRespuesta: function () { return $('#div-respuesta') },
        rptLblFechaRegistro: function () { return $('#rptLblFechaRegistro') },
        rptLblExpediente: function () { return $('#rptLblExpediente') },
        rptLblTipoDocumento: function () { return $('#rptLblTipoDocumento') },
        rptLblNroDocumento: function () { return $('#rptLblNroDocumento') },
        rptLblInstitucion: function () { return $('#rptLblInstitucion') },
        rptLblRemitente: function () { return $('#rptLblRemitente') },
        rptLblFolios: function () { return $('#rptLblFolios') },
        rptLblSumilla: function () { return $('#rptLblSumilla') },
        rptLblObservaciones: function () { return $('#rptLblObservaciones') },
        rptLblOficinaAtencion: function () { return $('#rptLblOficinaAtencion') },
        rptLblEstadoDocumento: function () { return $('#rptLblEstadoDocumento') },
        rptLblObservacionFinalizar: function () { return $('#rptLblObservacionFinalizar') }
        
       

    };
    base.Event = {
        BtnGrabarClick: function (e) {
            e.preventDefault();
            if (base.Control.ValRegistro.isValid()) {
               
                base.Ajax.AjaxRegistrar.data = {                   
                    NumeroDocumento: base.Control.TxtNumeroDocumento().val(),
                    CodigoCaptcha: base.Control.txtValorCaptcha().val()
                };

                base.Ajax.AjaxRegistrar.submit();
            }
        },
        AjaxRegistrarSuccess: function (data) {
           
            if (data.IsSuccess==true) {              

                if (data.Message!='')
                {
                    $('#div-exception').show();
                    $('#idTextResultado').text(data.Message);
                    $('#div-respuesta').hide();
                }
                else{

                    $('#div-exception').hide();
                //  base.Control.DivInformacion().hide();
                base.Control.DivRespuesta().show();                 
                //$('#pdfResult').html('<iframe src="../Register/Consulta/PDFRespuesta?emi=' + data.Result[0].NumeroEmision + '&anio=' + data.Result[0].Anio + '" width="100%" height="1200" id="iFramePdf" frameBorder="1"></iframe>');
                base.Control.rptLblFechaRegistro().html(data.Result[0].FechaExpediente);
                base.Control.rptLblExpediente().html(data.Result[0].NroExpediente);
                base.Control.rptLblTipoDocumento().html(data.Result[0].TipoDocumento);
                base.Control.rptLblNroDocumento().html(data.Result[0].NroDoc);
                base.Control.rptLblInstitucion().html(data.Result[0].Institucion);
                base.Control.rptLblRemitente().html(data.Result[0].Remitente);
                base.Control.rptLblFolios().html(data.Result[0].Folios);
                base.Control.rptLblSumilla().html(data.Result[0].Sumilla);
                base.Control.rptLblObservaciones().html(data.Result[0].Observacion);
                base.Control.rptLblOficinaAtencion().html(data.Result[0].OficinaAtencion);
                base.Control.rptLblEstadoDocumento().html(data.Result[0].Estado);
                base.Control.rptLblObservacionFinalizar().html(data.Result[0].ObsFinalizar);                
                
                }
            }
            
           
        }
    };
    base.Ajax = {
        AjaxBuscarPersona: new Gob.Pcm.UI.Web.Components.Ajax(
       {
           action: PCM.Reclamo.Presentacion.Register.Consulta.Actions.BuscarPersona,
           autoSubmit: false,
           onSuccess: base.Event.AjaxBuscarPersonaSuccess
       }),
        AjaxBuscarUbigeoProvincia: new Gob.Pcm.UI.Web.Components.Ajax(
       {
           action: PCM.Reclamo.Presentacion.Register.Consulta.Actions.BuscarUbigeo,
           autoSubmit: false,
           onSuccess: base.Event.AjaxBuscarUbigeoProvinciaSuccess
       }),

        AjaxBuscarUbigeoDistrito: new Gob.Pcm.UI.Web.Components.Ajax(
       {
           action: PCM.Reclamo.Presentacion.Register.Consulta.Actions.BuscarUbigeo,
           autoSubmit: false,
           onSuccess: base.Event.AjaxBuscarUbigeoDistritoSuccess
       }),
        AjaxRegistrar: new Gob.Pcm.UI.Web.Components.Ajax(
        {
            action: PCM.Reclamo.Presentacion.Register.Consulta.Actions.Registrar,
            autoSubmit: false,
            onSuccess: base.Event.AjaxRegistrarSuccess
        }),
        AjaxSendEmail: new Gob.Pcm.UI.Web.Components.Ajax(
       {
           action: PCM.Reclamo.Presentacion.Register.Consulta.Actions.EnviarCorreo,
           autoSubmit: false,
           onSuccess: base.Event.AjaxSendEmailSuccess
       }),
    };
    base.Function = {
        ValidacionesExtras: function () {
            var validationsExtra = new Array();
            validationsExtra.push({
                Event: function () {
                   var isValido = true;
                   if (base.Control.TxtNumeroDocumento().val() == null || base.Control.TxtNumeroDocumento().val() == "") {
                       base.Control.TxtNumeroDocumento().addClass("hasError");
                        isValido = false;
                    } else {
                       base.Control.TxtNumeroDocumento().removeClass("hasError");
                    }
                    return isValido;
                },
                codeMessage: "EtiquetaNroExpediente"
            });

            return validationsExtra;
        }
    };


};