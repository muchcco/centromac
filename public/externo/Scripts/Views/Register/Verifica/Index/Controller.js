/// <summary>
/// Script de la aplicación
/// </summary>
/// <remarks>
/// Creacion: 	WECM 14092017
/// </remarks>
ns('PCM.Reclamo.Presentacion.Register.Verifica.Index');
PCM.Reclamo.Presentacion.Register.Verifica.Index.Controller = function () {
    var base = this;
    base.Ini = function () {
        'use strict'
        base.Control.VerificaModel = PCM.Reclamo.Presentacion.Register.Verifica.Models.Verifica;
        $('[data-toggle="tooltip"]').tooltip();
        base.Control.BtnGrabar().off('click');
        base.Control.BtnGrabar().on('click', base.Event.BtnGrabarClick);

        base.Control.ValRegistro = new Gob.Pcm.UI.Web.Components.Validator({
            form: base.Control.FormRegistro(),
            messages: PCM.Reclamo.Presentacion.Register.Verifica.Resources,
            validationsExtra: base.Function.ValidacionesExtras()
        });

        //base.Control.SlcDepartamento().select2();
        //base.Control.SlcProvincia().select2();
        //base.Control.SlcDistrito().select2();
       // base.Event.CheckboxNombresClick(); 
    };
    base.Control = {
        Mensaje: new Gob.Pcm.UI.Web.Components.Message(),
        FormRegistro: function () { return $('#frmRegistrarVerifica'); },
        VerificaModel: null,
        BtnGrabar: function () { return $('#btnGrabar') },
        BtnBuscar: function () { return $('#btnBuscar') },
        BtnEnviarCorreo: function () { return $('#btnEnviarCorreo') },
        BtnImprimir: function () { return $('#btnImprimir') },
        SlcTipoDocumento: function () { return $('#slcTipoDocumento') },
        TxtNumeroDocumento: function () { return $('#txtNumeroDocumento') },
        TxtCodigoVerifica: function () { return $('#txtCodigoVerifica') },
        txtValorCaptcha: function () { return $('#txtValorCaptcha') },
        DivInformacion: function () { return $('#div-informacion') },
        DivRespuesta: function () { return $('#div-respuesta') },
        TableAnexosBody: function () { return $('#tableAnexosBody') }


    };
    base.Event = {
        BtnGrabarClick: function (e) {
            e.preventDefault();
            if (base.Control.ValRegistro.isValid()) {
               
                base.Ajax.AjaxRegistrar.data = {                   
                    TipoDocumento: base.Control.SlcTipoDocumento().val(),
                    NumeroDocumento: base.Control.TxtNumeroDocumento().val(),
                    CodigoVerifica: base.Control.TxtCodigoVerifica().val(),
                    CodigoCaptcha: base.Control.txtValorCaptcha().val(),
                    EnviarCorreoUsuario: false,
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
                $('#pdfResult').html('<iframe src="../Register/Verifica/PDFRespuesta?emi=' + data.Result[0].Result[0].NumeroEmision + '&anio=' + data.Result[0].Result[0].Anio + '&CodigoVerifica=' + data.Result[0].Result[0].CodVerificacion + '" width="100%" height="1200" id="iFramePdf" frameBorder="1"></iframe>');
                //base.Control.RptLblFecha().html(data.Fecha);
                // base.Control.RptLblNombres().html(base.Control.TxtApellidoPaterno().val() + ' ' + base.Control.TxtApellidoMaterno().val() + ' ' + base.Control.TxtNombres().val() + '' + base.Control.TxtRazonSocial().val());
                //base.Control.RptLblCorreo().html(base.Control.TxtCorreoElectronico().val());
                //base.Control.RptLblDescripcionAsunto().html(base.Control.TxtDescripcion().val());
                    // base.Control.RptLblMedioEntrega().html(base.Control.SlcMedioEntrega().find('option:selected').text());            

               
                if (data.Result[0].IsSuccess) {
                    base.Control.TableAnexosBody().empty();
                 
                    if (data.Result[1].Result.length > 0) {
                           
                            $.each(data.Result[1].Result, function (index, value) {
                                var tr = $('<tr/>');
                                /*tr.append("<td>" + value.Descripcion + "</td>");*/
                                tr.append("<td>" + value.NombreAnexo + "</td>");
                                tr.append("<td style='width:40px;text-align: center; cursor:pointer;' onclick='PCM.Reclamo.Presentacion.Register.Verifica.Index.Vista.Event.BtnDescagarDocumentoAnexoClick(this);' anexo='" + value.NroDocumento + "'   ><span class='glyphicon glyphicon-folder-open' title='" + PCM.Reclamo.Presentacion.Base.GenericoResource.EtiquetaVerDocumento + "'></span></td>");
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
            }
            
           
        },
        BtnDescagarDocumentoAnexoClick: function (thiss) {
            'use strict'
            var filtro = { NroDocumento: null };
            filtro.NroDocumento = $(thiss).attr('anexo');
            if (filtro.NroDocumento != null) {
                Gob.Pe.UI.Web.Components.Util.RedirectPost(PCM.Reclamo.Presentacion.Register.Verifica.Actions.DescargarDocumentoAnexo, filtro);
            }
            else {
                base.Control.Mensaje.Warning({ title: 'Advertencia', message: 'Debe seleccionar un registros.' });
            }

        }
    };
    base.Ajax = {
        AjaxBuscarPersona: new Gob.Pcm.UI.Web.Components.Ajax(
       {
           action: PCM.Reclamo.Presentacion.Register.Verifica.Actions.BuscarPersona,
           autoSubmit: false,
           onSuccess: base.Event.AjaxBuscarPersonaSuccess
       }),
        AjaxBuscarUbigeoProvincia: new Gob.Pcm.UI.Web.Components.Ajax(
       {
           action: PCM.Reclamo.Presentacion.Register.Verifica.Actions.BuscarUbigeo,
           autoSubmit: false,
           onSuccess: base.Event.AjaxBuscarUbigeoProvinciaSuccess
       }),

        AjaxBuscarUbigeoDistrito: new Gob.Pcm.UI.Web.Components.Ajax(
       {
           action: PCM.Reclamo.Presentacion.Register.Verifica.Actions.BuscarUbigeo,
           autoSubmit: false,
           onSuccess: base.Event.AjaxBuscarUbigeoDistritoSuccess
       }),
        AjaxRegistrar: new Gob.Pcm.UI.Web.Components.Ajax(
        {
            action: PCM.Reclamo.Presentacion.Register.Verifica.Actions.Registrar,
            autoSubmit: false,
            onSuccess: base.Event.AjaxRegistrarSuccess
        }),
        AjaxSendEmail: new Gob.Pcm.UI.Web.Components.Ajax(
       {
           action: PCM.Reclamo.Presentacion.Register.Verifica.Actions.EnviarCorreo,
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
                codeMessage: "EtiquetaNumeroDocumento"
            });
            validationsExtra.push({
                Event: function () {
                    var isValido = true;
                    if (base.Control.TxtCodigoVerifica().val() == null || base.Control.TxtCodigoVerifica().val() == "") {
                        base.Control.TxtCodigoVerifica().addClass("hasError");
                        isValido = false;
                    } else {
                        base.Control.TxtCodigoVerifica().removeClass("hasError");
                    }
                    return isValido;
                },
                codeMessage: "EtiquetaCodigoVerifica"
            });
            validationsExtra.push({
                Event: function () {
                    var isValido = true;
                   /* if (base.Control.CheckboxNombres().is(':checked') && (base.Control.TxtNombres().val() == null || base.Control.TxtNombres().val() == "")) {
                        base.Control.TxtNombres().addClass("hasError");
                        isValido = false;
                    } else {
                        base.Control.TxtNombres().removeClass("hasError");
                    }*/
                    return isValido;
                },
                codeMessage: "EtiquetaNombres"
            });
           validationsExtra.push({
                Event: function () {
                    var isValido = true;
                    if (base.Control.SlcTipoDocumento().val() == "") {
                        base.Control.SlcTipoDocumento().addClass("hasError");
                        isValido = false;
                    } 
                    else {
                        base.Control.SlcTipoDocumento().removeClass("hasError");
                    }
                    return isValido;
                },
                codeMessage: "EtiquetaTipoDocumento"
            });
            return validationsExtra;
        }
    };


};