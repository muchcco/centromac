/// <summary>
/// Script de la aplicación
/// </summary>
/// <remarks>
/// Creacion: 	WECM 14092017
/// </remarks>
ns('PCM.Reclamo.Presentacion.Register.Reclamo.Index');
PCM.Reclamo.Presentacion.Register.Reclamo.Index.Controller = function () {
    var base = this;
    base.Ini = function () {
        'use strict'
        base.Control.ReclamoModel = PCM.Reclamo.Presentacion.Register.Reclamo.Models.Reclamo;
        $('[data-toggle="tooltip"]').tooltip();
        base.Control.SlcDepartamento().off('change');
        base.Control.SlcDepartamento().on('change', base.Event.SlcDepartamentoChange);
        base.Control.SlcProvincia().off('change');
        base.Control.SlcProvincia().on('change', base.Event.SlcProvinciaChange);
        base.Control.CheckboxNombres().off('click');
        base.Control.CheckboxNombres().on('click', base.Event.CheckboxNombresClick);
        base.Control.CheckboxRazonSocial().off('click');
        base.Control.CheckboxRazonSocial().on('click', base.Event.CheckboxRazonSocialClick);
        base.Control.CheckboxOtros().off('click');
        base.Control.CheckboxOtros().on('click', base.Event.CheckboxOtrosClick);
        base.Control.BtnGrabar().off('click');
        base.Control.BtnGrabar().on('click', base.Event.BtnGrabarClick);
        base.Control.BtnEnviarCorreo().off('click');
        base.Control.BtnEnviarCorreo().on('click', base.Event.BtnEnviarCorreoClick);
        base.Control.BtnImprimir().off('click');
        base.Control.BtnImprimir().on('click', base.Event.BtnImprimirClick);
        base.Control.BtnBuscar().off('click');
        base.Control.BtnBuscar().on('click', base.Event.BtnBuscarClick);
        base.Control.TxtNroDocumentoGeneral().off('keypress');
        base.Control.TxtNroDocumentoGeneral().on('keypress', base.Event.TxtNroDocumentoGeneralKeyPress);
        base.Control.TxtNumeroDocumento().off('change');
        base.Control.TxtNumeroDocumento().on('change', base.Event.TxtNumeroDocumentoChange);

        base.Control.TxtNumeroDocumento().blur(base.Event.TxtNumeroDocumentoBlur);

        base.Control.ValRegistro = new Gob.Pcm.UI.Web.Components.Validator({
            form: base.Control.FormRegistro(),
            messages: PCM.Reclamo.Presentacion.Register.Reclamo.Resources,
            validationsExtra: base.Function.ValidacionesExtras()
        });

        //base.Control.SlcDepartamento().select2();
        //base.Control.SlcProvincia().select2();
        //base.Control.SlcDistrito().select2();
        base.Event.CheckboxNombresClick(); 
    };
    base.Control = {
        Mensaje: new Gob.Pcm.UI.Web.Components.Message(),
        FormRegistro: function () { return $('#frmRegistrarReclamo'); },
        ReclamoModel: null,
        BtnGrabar: function () { return $('#btnGrabar') },
        BtnBuscar: function () { return $('#btnBuscar') },
        BtnEnviarCorreo: function () { return $('#btnEnviarCorreo') },
        BtnImprimir: function () { return $('#btnImprimir') },

        SlcDepartamento: function () { return $('#slcDepartamento') },
        SlcProvincia: function () { return $('#slcProvincia') },
        SlcDistrito: function () { return $('#slcDistrito') },
        SlcTipoDocumento: function () { return $('#slcTipoDocumento') }, 

        CheckboxNombres: function () { return $('#checkboxNombres') },
        CheckboxRazonSocial: function () { return $('#checkboxRazonSocial') },
        CheckboxOtros: function () { return $('#checkboxOtros') },

        TxtApellidoPaterno: function () { return $('#txtApellidoPaterno') },
        TxtApellidoMaterno: function () { return $('#txtApellidoMaterno') },
        TxtNombres: function () { return $('#txtNombres') },
        TxtRazonSocial: function () { return $('#txtRazonSocial') },
        TxtNumeroDocumento: function () { return $('#txtNumeroDocumento') },        
        TxtDomicilio: function () { return $('#txtDomicilio') },
        TxtUrbanizacion: function () { return $('#txtUrbanizacion') },
        TxtCorreoElectronico: function () { return $('#txtCorreoElectronico') },
        TxtTelefono: function () { return $('#txtTelefono') },
        TxtDescripcion: function () { return $('#txtDescripcion') },
   //     SlcMedioEntrega: function () { return $('#slcMedioEntrega') },
     //   TxtObservaciones: function () { return $('#txtObservaciones') },
        TxtNroDocumentoGeneral: function () { return $('#txtNroDocumentoGeneral') },

        DivInformacion: function () { return $('#div-informacion') },
        DivRespuesta: function () { return $('#div-respuesta') },
        LblExpediente: function () { return $('#lblExpediente') },

        DniValido: false,
        DniValidado: null,
        RucValido: false,
        RucValidado: null,
        //Radiocheckdni: function () { return $('#radiocheckdni') },
        //Radiocheckruc: function () { return $('#radiocheckruc') },
        //Radiocheckce: function () { return $('#radiocheckce') },
        //Radiochecklm: function () { return $('#radiochecklm') },
        //Radiocheckotro: function () { return $('#radiocheckotro') },

        RptLblExpediente: function () { return $('#rptLblExpediente') },
        RptLblFecha: function () { return $('#rptLblFecha') },
        RptLblNombres: function () { return $('#rptLblNombres') },
        RptLblCorreo: function () { return $('#rptLblCorreo') },
        RptLblDescripcionAsunto: function () { return $('#rptLblDescripcionAsunto') },
        RptLblMedioEntrega: function () { return $('#rptLblMedioEntrega') }, 
    };
    base.Event = {
        BtnImprimirClick: function () {
            var filtro = { NroExpediente: null };
            filtro.NroExpediente = base.Control.RptLblExpediente().html(); 
            if (filtro.NroExpediente != null  ) {
                Gob.Pe.UI.Web.Components.Util.RedirectPost(PCM.Reclamo.Presentacion.Register.Reclamo.Actions.DescargarDocumento, filtro);
            } 
        },
        BtnEnviarCorreoClick: function (e) {
            e.preventDefault();
            if (base.Control.TxtCorreoElectronico().val() == "") {
                base.Control.Mensaje.Information({ message: 'No hay correo ingresado...' });
                return false;
            }
            var tipoPersona = "";
            if (base.Control.CheckboxNombres().is(':checked'))
                tipoPersona = base.Control.CheckboxNombres().val();
            else if (base.Control.CheckboxRazonSocial().is(':checked'))
                tipoPersona = base.Control.CheckboxRazonSocial().val();
            else if (base.Control.CheckboxOtros().is(':checked'))
                tipoPersona = base.Control.CheckboxOtros().val();
            base.Ajax.AjaxSendEmail.data = {
                TipoPersona: tipoPersona,
                ApellidoPaterno: base.Control.TxtApellidoPaterno().val(),
                ApellidoMaterno: base.Control.TxtApellidoMaterno().val(),
                Nombres: base.Control.TxtNombres().val(),
                RazonSocial: base.Control.TxtRazonSocial().val(),
                TipoDocumento: base.Control.SlcTipoDocumento().val(),
                TipoDocumentoDescripcion: base.Control.SlcTipoDocumento().find('option:selected').text(),
                NumeroDocumento: base.Control.TxtNumeroDocumento().val(),
                Domicilio: base.Control.TxtDomicilio().val(),
                Urbanizacion: base.Control.TxtUrbanizacion().val(),
                Departamento: base.Control.SlcDepartamento().val(),
                Provincia: base.Control.SlcProvincia().val(),
                Distrito: base.Control.SlcDistrito().val(),
                CorreoElectronico: base.Control.TxtCorreoElectronico().val(),
                Descripcion: base.Control.TxtDescripcion().val(),
                MedioEntrega:'',// base.Control.SlcMedioEntrega().val(),
                MedioEntregaDescripcion: '',//base.Control.SlcMedioEntrega().find('option:selected').text(),
                Observaciones: '',//base.Control.TxtObservaciones().val(),
                EnviarCorreoUsuario: true,
                FechaInicial: base.Control.RptLblFecha().html(),
                CodigoExpediente:base.Control.RptLblExpediente().html()
            };
            base.Ajax.AjaxSendEmail.submit();
        },
        TxtNumeroDocumentoChange: function () {
            'use strict'
            if (!base.Control.IsFind) {
                base.Ajax.AjaxBuscarPersona.send({
                    documento: base.Control.TxtNumeroDocumento().val()
                });
            }
        },
        TxtNroDocumentoGeneralKeyPress: function (event) {
            'use strict'
            if (event.which == 13  ) {
                event.preventDefault();
                base.Event.BtnBuscarClick();
            }
        },
        BtnBuscarClick: function () {
            'use strict'
            base.Control.IsFind = true;
            if (base.Control.TxtNroDocumentoGeneral().val() != "") {
                base.Ajax.AjaxBuscarPersona.send({
                    documento: base.Control.TxtNroDocumentoGeneral().val()
                });
            }
            else {
                base.Control.TxtNroDocumentoGeneral().focus();
                base.Control.Mensaje.Information({ message: 'Ingrese el número del identidad para buscar..' });
            }
        },
        BtnGrabarClick: function (e) {
            e.preventDefault();
            if (base.Control.ValRegistro.isValid()) {
                var tipoPersona="";
                if (base.Control.CheckboxNombres().is(':checked'))
                    tipoPersona = base.Control.CheckboxNombres().val();
                else if (base.Control.CheckboxRazonSocial().is(':checked'))
                    tipoPersona = base.Control.CheckboxRazonSocial().val();
                else if (base.Control.CheckboxOtros().is(':checked'))
                    tipoPersona = base.Control.CheckboxOtros().val();
                base.Ajax.AjaxRegistrar.data = {                   
                    TipoPersona: tipoPersona,
                    ApellidoPaterno: base.Control.TxtApellidoPaterno().val(),
                    ApellidoMaterno: base.Control.TxtApellidoMaterno().val(),
                    Nombres: base.Control.TxtNombres().val(),
                    RazonSocial: base.Control.TxtRazonSocial().val(),
                    TipoDocumento: base.Control.SlcTipoDocumento().val(),
                    TipoDocumentoDescripcion: base.Control.SlcTipoDocumento().find('option:selected').text(),
                    NumeroDocumento: base.Control.TxtNumeroDocumento().val(),
                    Domicilio: base.Control.TxtDomicilio().val(),
                    Urbanizacion: base.Control.TxtUrbanizacion().val(),
                    Departamento: base.Control.SlcDepartamento().val(),
                    Provincia: base.Control.SlcProvincia().val(),
                    Distrito: base.Control.SlcDistrito().val(),

                    DepartamentoDescrip: base.Control.SlcDepartamento().find('option:selected').text(),
                    ProvinciaDescrip: base.Control.SlcProvincia().find('option:selected').text(),
                    DistritoDescrip: base.Control.SlcDistrito().find('option:selected').text(),

                    CorreoElectronico: base.Control.TxtCorreoElectronico().val(),
                    Telefono: base.Control.TxtTelefono().val(),
                    Descripcion: base.Control.TxtDescripcion().val(),
                    MedioEntrega: '',//base.Control.SlcMedioEntrega().val(),
                    MedioEntregaDescripcion: '',//base.Control.SlcMedioEntrega().find('option:selected').text(),
                    Observaciones: '',//base.Control.TxtObservaciones().val(),
                    EnviarCorreoUsuario: true,
                };
                base.Control.RptLblExpediente().html('');
                base.Control.RptLblFecha().html('');
                base.Control.RptLblNombres().html('');
                base.Control.RptLblCorreo().html('');
                base.Control.RptLblDescripcionAsunto().html('');
                base.Control.RptLblMedioEntrega().html('');
                base.Ajax.AjaxRegistrar.submit();
            }
        },
        CheckboxNombresClick: function () {
            'use strict'
            base.Control.TxtApellidoPaterno().prop("disabled", false);
            base.Control.TxtApellidoMaterno().prop("disabled", false);
            base.Control.TxtNombres().prop("disabled", false);
            base.Control.TxtRazonSocial().prop("disabled", true);
            base.Control.TxtRazonSocial().val("");

            base.Control.SlcTipoDocumento().empty();
            base.Control.SlcTipoDocumento().append(new Option(PCM.Reclamo.Presentacion.Register.Reclamo.Resources.DNI, PCM.Reclamo.Presentacion.Register.Reclamo.Resources.DNI));

            base.Control.TxtNumeroDocumento().attr('maxlength', 8);

            //base.Control.Radiocheckdni().prop("disabled", false);
            //base.Control.Radiocheckruc().prop("disabled", true);
            //base.Control.Radiocheckce().prop("disabled", true);
            //base.Control.Radiochecklm().prop("disabled", true);
            //base.Control.Radiocheckotro().prop("disabled", true);

            //base.Control.Radiocheckdni().prop('checked', true);
            //base.Control.Radiocheckruc().prop('checked', false);
            //base.Control.Radiocheckce().prop('checked', false);
            //base.Control.Radiochecklm().prop('checked', false);
            //base.Control.Radiocheckotro().prop('checked', false);
        },
        CheckboxRazonSocialClick: function () {
            'use strict'
            base.Control.TxtApellidoPaterno().prop("disabled", true);
            base.Control.TxtApellidoMaterno().prop("disabled", true);
            base.Control.TxtNombres().prop("disabled", true);
            base.Control.TxtRazonSocial().prop("disabled", false);
            base.Control.TxtApellidoPaterno().val("");
            base.Control.TxtApellidoMaterno().val("");
            base.Control.TxtNombres().val("");

            base.Control.SlcTipoDocumento().empty();
            base.Control.SlcTipoDocumento().append(new Option(PCM.Reclamo.Presentacion.Register.Reclamo.Resources.RUC, PCM.Reclamo.Presentacion.Register.Reclamo.Resources.RUC));

            base.Control.TxtNumeroDocumento().attr('maxlength', 11);
            //base.Control.Radiocheckdni().prop("disabled", true);
            //base.Control.Radiocheckruc().prop("disabled", false);
            //base.Control.Radiocheckce().prop("disabled", true);
            //base.Control.Radiochecklm().prop("disabled", true);
            //base.Control.Radiocheckotro().prop("disabled", true);

            //base.Control.Radiocheckdni().prop('checked', false);
            //base.Control.Radiocheckruc().prop('checked', true);
            //base.Control.Radiocheckce().prop('checked', false);
            //base.Control.Radiochecklm().prop('checked', false);
            //base.Control.Radiocheckotro().prop('checked', false);
        },
        CheckboxOtrosClick: function () {
            'use strict'
            base.Control.TxtApellidoPaterno().prop("disabled", true);
            base.Control.TxtApellidoMaterno().prop("disabled", true);
            base.Control.TxtNombres().prop("disabled", true);
            base.Control.TxtRazonSocial().prop("disabled", false);
            base.Control.SlcTipoDocumento().empty();
            if (base.Control.ReclamoModel != null && base.Control.ReclamoModel.ListaTipoDocumento != null) {                
                $.each(base.Control.ReclamoModel.ListaTipoDocumento, function (index, value) {
                    base.Control.SlcTipoDocumento().append(new Option(value.Text, value.Value));
                });
            }

            base.Control.TxtNumeroDocumento().attr('maxlength', 12);
            //base.Control.Radiocheckdni().prop("disabled", true);
            //base.Control.Radiocheckruc().prop("disabled", true);
            //base.Control.Radiocheckce().prop("disabled", false);
            //base.Control.Radiochecklm().prop("disabled", false);
            //base.Control.Radiocheckotro().prop("disabled", false);

            //base.Control.Radiocheckdni().prop('checked', false);
            //base.Control.Radiocheckruc().prop('checked', false);
            //base.Control.Radiocheckce().prop('checked', true);
            //base.Control.Radiochecklm().prop('checked', false);
            //base.Control.Radiocheckotro().prop('checked', false);
        },
        SlcDepartamentoChange: function () {
            'use strict'
            base.Ajax.AjaxBuscarUbigeoProvincia.send({
                tipo:"PRO" ,
                filtro: base.Control.SlcDepartamento().val()
            });
        },
        SlcProvinciaChange: function () {
            'use strict'
            base.Ajax.AjaxBuscarUbigeoDistrito.send({
                tipo: "DIS",
                filtro: base.Control.SlcDepartamento().val() + base.Control.SlcProvincia().val()
            });
        },
        AjaxBuscarPersonaSuccess: function (data) { 
            if (data.IsSuccess) {
                var result = data.Result;
                if (result.length > 0) {
                    base.Control.TxtApellidoPaterno().val(result[0].Paterno);
                    base.Control.TxtApellidoMaterno().val(result[0].Materno);
                    base.Control.TxtNombres().val(result[0].Nombres);
                    base.Control.TxtRazonSocial().val(result[0].RazonSocial);
                    base.Control.TxtNumeroDocumento().val(result[0].NroDocumento);
                    base.Control.TxtDomicilio().val(result[0].Domicilio);
                    base.Control.TxtUrbanizacion().val(result[0].Urbanizacion);
                    base.Control.TxtCorreoElectronico(result[0].Correo).val(result[0].Correo);
                    base.Control.TxtTelefono().val(result[0].Telefono);
                    base.Control.SlcDepartamento().val(result[0].CodDep);
                    base.Event.SlcDepartamentoChange();
                    base.Control.SlcProvincia().val(result[0].CodPro);

                    if(result[0].TipoPersona=="PJ")
                    {
                        base.Control.CheckboxNombres().prop("checked", false);
                        base.Control.CheckboxRazonSocial().prop("checked", true);
                        base.Event.CheckboxRazonSocialClick();
                    }
                    if(result[0].TipoPersona=="PN")
                    {
                        base.Control.CheckboxRazonSocial().prop("checked", false);
                        base.Control.CheckboxNombres().prop("checked", true);
                        base.Event.CheckboxNombresClick();
                    }

                }
            }
            
        },

        AjaxBuscarUbigeoProvinciaSuccess: function (resultado) {
            base.Control.SlcProvincia().empty();
            base.Control.SlcProvincia().append(new Option(PCM.Reclamo.Presentacion.Base.GenericoResource.EtiquetaTodos, ""));
            base.Control.SlcDistrito().empty();
            base.Control.SlcDistrito().append(new Option(PCM.Reclamo.Presentacion.Base.GenericoResource.EtiquetaTodos, ""));
            $.each(resultado.Result, function (index, value) {
                base.Control.SlcProvincia().append(new Option(value.Nombre, value.Codigo));
            });
        },
        AjaxBuscarUbigeoDistritoSuccess: function (resultado) {
            base.Control.SlcDistrito().empty();
            base.Control.SlcDistrito().append(new Option(PCM.Reclamo.Presentacion.Base.GenericoResource.EtiquetaTodos, ""));
            $.each(resultado.Result, function (index, value) {
                base.Control.SlcDistrito().append(new Option(value.Nombre, value.Codigo));
            });
        },
        AjaxRegistrarSuccess: function (data) { 
            if (data.IsSuccess) { 
                base.Control.DivInformacion().hide();
                base.Control.DivRespuesta().show();                 
                base.Control.RptLblExpediente().html(data.Result);
                base.Control.RptLblFecha().html(data.Fecha);
                base.Control.RptLblNombres().html(base.Control.TxtApellidoPaterno().val() + ' ' + base.Control.TxtApellidoMaterno().val() + ' ' + base.Control.TxtNombres().val() + '' + base.Control.TxtRazonSocial().val());
                base.Control.RptLblCorreo().html(base.Control.TxtCorreoElectronico().val());
                base.Control.RptLblDescripcionAsunto().html(base.Control.TxtDescripcion().val());
               // base.Control.RptLblMedioEntrega().html(base.Control.SlcMedioEntrega().find('option:selected').text());                 
            }
            else {                
                base.Control.Mensaje.Information({ message: 'Se genero un error al registrar la información..' });
            }
           
        },
        AjaxSendEmailSuccess: function (data) {
            if (data.IsSuccess) { 
                base.Control.Mensaje.Information({ message: 'Se envió correctamente el correo..' });
                base.Control.BtnEnviarCorreo().hide();
            }
            else {                
                base.Control.Mensaje.Information({ message: 'Se genero un error al enviar la información..' });
            }
           
        },
        TxtNumeroDocumentoBlur: function (e) {
            if (base.Control.SlcTipoDocumento().val() == "DNI" && base.Control.DniValidado != base.Control.TxtNumeroDocumento().val()) {
                base.Control.DniValido = false;
                base.Control.RucValido = false;
            } else if (base.Control.SlcTipoDocumento().val() == "RUC"  && base.Control.RucValidado != base.Control.TxtNumeroDocumento().val()) {
                base.Control.DniValido = false;
                base.Control.RucValido = false;
            }
        },
    };
    base.Ajax = {
        AjaxBuscarPersona: new Gob.Pcm.UI.Web.Components.Ajax(
       {
           action: PCM.Reclamo.Presentacion.Register.Reclamo.Actions.BuscarPersona,
           autoSubmit: false,
           onSuccess: base.Event.AjaxBuscarPersonaSuccess
       }),
        AjaxBuscarUbigeoProvincia: new Gob.Pcm.UI.Web.Components.Ajax(
       {
           action: PCM.Reclamo.Presentacion.Register.Reclamo.Actions.BuscarUbigeo,
           autoSubmit: false,
           onSuccess: base.Event.AjaxBuscarUbigeoProvinciaSuccess
       }),

        AjaxBuscarUbigeoDistrito: new Gob.Pcm.UI.Web.Components.Ajax(
       {
           action: PCM.Reclamo.Presentacion.Register.Reclamo.Actions.BuscarUbigeo,
           autoSubmit: false,
           onSuccess: base.Event.AjaxBuscarUbigeoDistritoSuccess
       }),
        AjaxRegistrar: new Gob.Pcm.UI.Web.Components.Ajax(
        {
            action: PCM.Reclamo.Presentacion.Register.Reclamo.Actions.Registrar,
            autoSubmit: false,
            onSuccess: base.Event.AjaxRegistrarSuccess
        }),
        AjaxSendEmail: new Gob.Pcm.UI.Web.Components.Ajax(
       {
           action: PCM.Reclamo.Presentacion.Register.Reclamo.Actions.EnviarCorreo,
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
                    if (base.Control.CheckboxNombres().is(':checked') && base.Control.TxtNumeroDocumento().val().length<8 ) {
                        base.Control.TxtNumeroDocumento().addClass("hasError");
                        isValido = false;
                    }
                    else if (base.Control.CheckboxRazonSocial().is(':checked') && base.Control.TxtNumeroDocumento().val().length < 11) {
                        base.Control.TxtNumeroDocumento().addClass("hasError");
                        isValido = false;
                    }
                    else {
                        base.Control.TxtNumeroDocumento().removeClass("hasError");
                    }
                    return isValido;
                },
                codeMessage: "EtiquetaNumeroDocumentoValido"
            });

            validationsExtra.push({
                Event: function () {
                    var isValido = true;
                    if (base.Control.CheckboxNombres().is(':checked') && (base.Control.TxtApellidoPaterno().val() == null || base.Control.TxtApellidoPaterno().val() == "")) {
                        base.Control.TxtApellidoPaterno().addClass("hasError");
                        isValido = false;
                    } else {
                        base.Control.TxtApellidoPaterno().removeClass("hasError");
                    }
                    return isValido;
                },
                codeMessage: "EtiquetaApellidoPaterno"
            });
            validationsExtra.push({
                Event: function () {
                    var isValido = true;
                    if (base.Control.CheckboxNombres().is(':checked') && (base.Control.TxtApellidoMaterno().val() == null || base.Control.TxtApellidoMaterno().val() == "")) {
                        base.Control.TxtApellidoMaterno().addClass("hasError");
                        isValido = false;
                    } else {
                        base.Control.TxtApellidoMaterno().removeClass("hasError");
                    }
                    return isValido;
                },
                codeMessage: "EtiquetaApellidoMaterno"
            });
            validationsExtra.push({
                Event: function () {
                    var isValido = true;
                    if (base.Control.CheckboxNombres().is(':checked') && (base.Control.TxtNombres().val() == null || base.Control.TxtNombres().val() == "")) {
                        base.Control.TxtNombres().addClass("hasError");
                        isValido = false;
                    } else {
                        base.Control.TxtNombres().removeClass("hasError");
                    }
                    return isValido;
                },
                codeMessage: "EtiquetaNombres"
            });
            validationsExtra.push({
                Event: function () {
                    var isValido = true;
                    if (base.Control.CheckboxRazonSocial().is(':checked') && (base.Control.TxtRazonSocial().val() == null || base.Control.TxtRazonSocial().val() == "")) {
                        base.Control.TxtRazonSocial().addClass("hasError");
                        isValido = false;
                    }
                    else if (base.Control.CheckboxOtros().is(':checked') && (base.Control.TxtRazonSocial().val() == null || base.Control.TxtRazonSocial().val() == "")) {
                        base.Control.TxtRazonSocial().addClass("hasError");
                        isValido = false;
                    }
                    else {
                        base.Control.TxtRazonSocial().removeClass("hasError");
                    }
                    return isValido;
                },
                codeMessage: "EtiquetaRazonSocial"
            });
            validationsExtra.push({
                Event: function () {
                    var isValido = true;
                    if (base.Control.TxtCorreoElectronico().val() == "") {
                        base.Control.TxtCorreoElectronico().addClass("hasError");
                        isValido = false;
                    } 
                    else {
                        base.Control.TxtCorreoElectronico().removeClass("hasError");
                    }
                    return isValido;
                },
                codeMessage: "EtiquetaCorreoElectronico"
            });
            return validationsExtra;
        }
    };


};