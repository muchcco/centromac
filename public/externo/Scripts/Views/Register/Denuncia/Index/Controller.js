/// <summary>
/// Script de la aplicación
/// </summary>
/// <remarks>
/// Creacion: 	WECM 14092017
/// </remarks>
ns('PCM.Reclamo.Presentacion.Register.Denuncia.Index');
PCM.Reclamo.Presentacion.Register.Denuncia.Index.Controller = function () {
    var base = this;
    base.Ini = function () {
        'use strict'
        base.Control.DenunciaModel = PCM.Reclamo.Presentacion.Register.Denuncia.Models.Denuncia;
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
        //base.Control.BtnGrabar().off('click');
        base.Control.BtnGrabar().on('click', base.Event.BtnGrabarClick);
       // base.Control.BtnGrabar().off('click');
        base.Control.BtnAgregarDocSustento().on('click', base.Event.BtnAgregarDocSustentoClick);        
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
        base.Control.TxtNumeroDocumentoRepresentante().off('change');
        base.Control.TxtNumeroDocumentoRepresentante().on('change', base.Event.TxtNumeroDocumentoRepresentanteChange);

        
        base.Control.chkServidorPCM().on('click', base.Event.chkServidorPCMClick);
        base.Control.chkDenunciaAnterior().on('click', base.Event.chkDenunciaAnteriorClick);

        
        

        base.Control.TxtNumeroDocumento().blur(base.Event.TxtNumeroDocumentoBlur);

        base.Control.ValRegistro = new Gob.Pcm.UI.Web.Components.Validator({
            form: base.Control.FormRegistro(),
            messages: PCM.Reclamo.Presentacion.Register.Denuncia.Resources,
            validationsExtra: base.Function.ValidacionesExtras()
        });


        base.Event.CheckboxNombresClick();
        $('#frm-ServidorPCM').hide();
        $('#frm-DenunciaAnterior').hide();
        $('#lstAdjuntoDenuncia').val("");
        $('#DenunciaSummaryErrorMessage').hide();
    };
 
    base.Control = {
        Mensaje: new Gob.Pcm.UI.Web.Components.Message(),
        FormRegistro: function () { return $('#frmRegistrarDenuncia'); },
        DenunciaModel: null,
        BtnGrabar: function () { return $('#btnGrabar') },
        BtnBuscar: function () { return $('#btnBuscar') },
        BtnEnviarCorreo: function () { return $('#btnEnviarCorreo') },
        BtnImprimir: function () { return $('#btnImprimir') },
        BtnAgregarDocSustento: function () { return $('#btnAgregarDocSustento') },

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
        SlcMedioEntrega: function () { return $('#slcMedioEntrega') },
        TxtObservaciones: function () { return $('#txtObservaciones') },
        TxtNroDocumentoGeneral: function () { return $('#txtNroDocumentoGeneral') },

        chkServidorPCM: function () { return $('#chkServidorPCM') },
        chkDenunciaAnterior: function () { return $('#chkDenunciaAnterior') },
        TxtNumeroDocumentoRepresentante: function () { return $('#txtNumeroDocumentoRepresentante') },
        TxtApellidoPaternoRepresentante: function () { return $('#txtNombreApellidosRepresentante') },
        TxtApellidoMaternoRepresentante: function () { return $('#txtApellidoMaternoRepresentante') },
        TxtNombresRepresentante: function () { return $('#txtNombresRepresentante') },
        TxtEntidadDenunciaAnterior: function () { return $('#txtEntidadDenunciaAnterior') },
        SlcDependenicaPCM: function () { return $('#slcDependenicaPCM') },
        SlcCargoPCM: function () { return $('#slcCargoPCM') },
        TxtVinculoDenunciados: function () { return $('#txtVinculoDenunciados') },

        SlcDependenciaDenunciado1: function () { return $('#slcDependenciaDenunciado1') },
        SlcCargoDenunciado1: function () { return $('#slcCargoDenunciado1') },
        TxtNombreDenunciado1: function () { return $('#txtNombreDenunciado1') },
        TxtDNIDenunciado1: function () { return $('#txtDNIDenunciado1') },
        TxtDireccionDenunciado1: function () { return $('#txtDireccionDenunciado1') },
        TxtTelefonoDenunciado1: function () { return $('#txtTelefonoDenunciado1') },
        TxtCorreoDenunciado1: function () { return $('#txtCorreoDenunciado1') },

        SlcDependenciaDenunciado2: function () { return $('#slcDependenciaDenunciado2') },
        SlcCargoDenunciado2: function () { return $('#slcCargoDenunciado2') },
        TxtNombreDenunciado2: function () { return $('#txtNombreDenunciado2') },
        TxtDNIDenunciado2: function () { return $('#txtDNIDenunciado2') },
        TxtDireccionDenunciado2: function () { return $('#txtDireccionDenunciado2') },
        TxtTelefonoDenunciado2: function () { return $('#txtTelefonoDenunciado2') },
        TxtCorreoDenunciado2: function () { return $('#txtCorreoDenunciado2') },
        
        SlcDependenciaDenunciado3: function () { return $('#slcDependenciaDenunciado3') },
        SlcCargoDenunciado3: function () { return $('#slcCargoDenunciado3') },
        TxtNombreDenunciado3: function () { return $('#txtNombreDenunciado3') },
        TxtDNIDenunciado3: function () { return $('#txtDNIDenunciado3') },
        TxtDireccionDenunciado3: function () { return $('#txtDireccionDenunciado3') },
        TxtTelefonoDenunciado3: function () { return $('#txtTelefonoDenunciado3') },
        TxtCorreoDenunciado3: function () { return $('#txtCorreoDenunciado3') },

        SlcDependenciaDenunciado4: function () { return $('#slcDependenciaDenunciado4') },
        SlcCargoDenunciado4: function () { return $('#slcCargoDenunciado4') },
        TxtNombreDenunciado4: function () { return $('#txtNombreDenunciado4') },
        TxtDNIDenunciado4: function () { return $('#txtDNIDenunciado4') },
        TxtDireccionDenunciado4: function () { return $('#txtDireccionDenunciado4') },
        TxtTelefonoDenunciado4: function () { return $('#txtTelefonoDenunciado4') },
        TxtCorreoDenunciado4: function () { return $('#txtCorreoDenunciado4') },

        TxtFundamentoDenuncia: function () { return $('#txtFundamentoDenuncia') },
        TxtIndicacionActoCorrupcion: function () { return $('#txtIndicacionActoCorrupcion') },
        

        DivInformacion: function () { return $('#div-informacion') },
        DivServidorPCM: function () { return $('#frm-ServidorPCM') },
        DivDenunciaAnterior: function () { return $('#frm-DenunciaAnterior') },
        DivRespuesta: function () { return $('#div-respuesta') },
        LblExpediente: function () { return $('#lblExpediente') },

        DniValido: false,
        DniValidado: null,
        RucValido: false,
        RucValidado: null,

        TxtApellidoPaternoDenunciado: function () { return $('#txtApellidoPaternoDenunciado') },
        TxtApellidoMaternoDenunciado: function () { return $('#txtApellidoMaternoDenunciado') },
        TxtNombresDenunciado: function () { return $('#TxtNombresDenunciado') },

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
                Gob.Pe.UI.Web.Components.Util.RedirectPost(PCM.Reclamo.Presentacion.Register.Denuncia.Actions.DescargarDocumento, filtro);
            } 
        },
        chkServidorPCMClick: function () {
            if ($('#chkServidorPCM').is(':checked')) {
                $('#frm-ServidorPCM').show();
            }
            else {
                $('#frm-ServidorPCM').hide();
            }
        },
        chkDenunciaAnteriorClick: function () {
            if ($('#chkDenunciaAnterior').is(':checked'))
            {
                $('#frm-DenunciaAnterior').show();
            }
            else
            {
                $('#frm-DenunciaAnterior').hide();
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
                MedioEntrega: base.Control.SlcMedioEntrega().val(),
                MedioEntregaDescripcion: base.Control.SlcMedioEntrega().find('option:selected').text(),
                Observaciones: base.Control.TxtObservaciones().val(),
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
        TxtNumeroDocumentoRepresentanteChange: function () {
            'use strict'
            if (!base.Control.IsFind) {
                base.Ajax.AjaxBuscarRepresentante.send({
                    documento: base.Control.TxtNumeroDocumentoRepresentante().val()
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
        BtnGrabarClick: function () {
        //BtnGrabarClick: function (e) {
            //e.preventDefault();
            
            if (base.Control.ValRegistro.isValid()) {
                var tipoPersona = "";
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
                    CorreoElectronico: base.Control.TxtCorreoElectronico().val(),
                    Telefono: base.Control.TxtTelefono().val(),

                    NumeroDocumentoRepresentante: base.Control.TxtNumeroDocumentoRepresentante().val(),
                    ApellidoPaternoRepresentante: base.Control.TxtApellidoPaternoRepresentante().val(),
                    ApellidoMaternoRepresentante: base.Control.TxtApellidoMaternoRepresentante().val(),
                    NombresRepresentante: base.Control.TxtNombresRepresentante().val(),

                    isServidorPCM: base.Control.chkServidorPCM().is(':checked'),
                    DependenicaPCM: base.Control.SlcDependenicaPCM().val(),
                    CargoPCM: base.Control.SlcCargoPCM().val(),
                    VinculoDenunciados: base.Control.TxtVinculoDenunciados().val(),

                    DependenciaDenunciado1: base.Control.SlcDependenciaDenunciado1().val(),
                    CargoDenunciado1: base.Control.SlcCargoDenunciado1().val(),
                    NombreDenunciado1:base.Control.TxtApellidoPaternoDenunciado().val()+' '+base.Control.TxtApellidoMaternoDenunciado().val()+' '+base.Control.TxtNombresDenunciado().val(),
                    DNIDenunciado1: base.Control.TxtDNIDenunciado1().val(),
                    DireccionDenunciado1: base.Control.TxtDireccionDenunciado1().val(),
                    TelefonoDenunciado1: base.Control.TxtTelefonoDenunciado1().val(),
                    CorreoDenunciado1: base.Control.TxtCorreoDenunciado1().val(),
                    /*
                    DependenciaDenunciado2: base.Control.SlcDependenciaDenunciado2().val(),
                    CargoDenunciado2: base.Control.SlcCargoDenunciado2().val(),
                    DNIDenunciado2: base.Control.TxtDNIDenunciado2().val(),
                    DireccionDenunciado2: base.Control.TxtDireccionDenunciado2().val(),
                    TelefonoDenunciado2: base.Control.TxtTelefonoDenunciado2().val(),
                    CorreoDenunciado2: base.Control.TxtCorreoDenunciado2().val(),

                    DependenciaDenunciado3: base.Control.SlcDependenciaDenunciado3().val(),
                    CargoDenunciado3: base.Control.SlcCargoDenunciado3().val(),
                    DNIDenunciado3: base.Control.TxtDNIDenunciado3().val(),
                    DireccionDenunciado3: base.Control.TxtDireccionDenunciado3().val(),
                    TelefonoDenunciado3: base.Control.TxtTelefonoDenunciado3().val(),
                    CorreoDenunciado3: base.Control.TxtCorreoDenunciado3().val(),

                    DependenciaDenunciado4: base.Control.SlcDependenciaDenunciado4().val(),
                    CargoDenunciado4: base.Control.SlcCargoDenunciado4().val(),
                    DNIDenunciado4: base.Control.TxtDNIDenunciado4().val(),
                    DireccionDenunciado4: base.Control.TxtDireccionDenunciado4().val(),
                    TelefonoDenunciado4: base.Control.TxtTelefonoDenunciado4().val(),
                    CorreoDenunciado4: base.Control.TxtCorreoDenunciado4().val(),
                    */
                    FundamentoDenuncia: base.Control.TxtFundamentoDenuncia().val(),
                    IndicacionActoCorrupcion: base.Control.TxtIndicacionActoCorrupcion().val(),

                    isDenunciaAnterior: base.Control.chkDenunciaAnterior().is(':checked'),
                    EntidadDenunciaAnterior: base.Control.TxtEntidadDenunciaAnterior().val(),
                    DepartamentoDescrip: base.Control.SlcDepartamento().find('option:selected').text(),
                    ProvinciaDescrip: base.Control.SlcProvincia().find('option:selected').text(),
                    DistritoDescrip: base.Control.SlcDistrito().find('option:selected').text(),
                    DependenciaPCMDescrip: base.Control.SlcDependenicaPCM().find('option:selected').text(),
                    CargoPCMDescrip: base.Control.SlcCargoPCM().find('option:selected').text(),
                    DependenciaDenunciado1Descrip: base.Control.SlcDependenciaDenunciado1().find('option:selected').text(),
                    CargoDenunciado1Descrip: base.Control.SlcCargoDenunciado1().find('option:selected').text(),
                    /*
                    DependenciaDenunciado2Descrip: base.Control.SlcDependenciaDenunciado2().find('option:selected').text(),
                    CargoDenunciado2Descrip: base.Control.SlcCargoDenunciado2().find('option:selected').text(),
                    DependenciaDenunciado3Descrip: base.Control.SlcDependenciaDenunciado3().find('option:selected').text(),
                    CargoDenunciado3Descrip: base.Control.SlcCargoDenunciado3().find('option:selected').text(),
                    DependenciaDenunciado4Descrip: base.Control.SlcDependenciaDenunciado4().find('option:selected').text(),
                    CargoDenunciado4Descrip: base.Control.SlcCargoDenunciado4().find('option:selected').text(),
                    //Observaciones: base.Control.TxtObservaciones().val(),
                    */

                    lstArchivosSustento: JSON.parse($('#lstAdjuntoDenuncia').val()),
                    EnviarCorreoUsuario: false,
                };
                base.Control.RptLblExpediente().html('');
                base.Control.RptLblFecha().html('');
                base.Control.RptLblNombres().html('');
                base.Control.RptLblCorreo().html('');
                base.Control.RptLblDescripcionAsunto().html('');
                base.Control.RptLblMedioEntrega().html('');
                base.Ajax.AjaxRegistrar.submit();
            }
            else {
                $('#DenunciaSummaryErrorMessage').show();
            }
        },
        BtnAgregarDocSustentoClick: function () {
      
                var fileUpload = $("#DocumentUploadTB").get(0);  
                var files = fileUpload.files;  
              
                var ext = fileUpload.files[0].name.split('.').pop();
              
                switch (ext) {
                    case 'jpg':
                    case 'jpeg':
                    case 'png':
                    case 'doc':
                    case 'docx':
                    case 'xls':
                    case 'xlsx':
                    case 'ppt':
                    case 'pptx':
                    case 'zip':
                    case 'rar':
                    case 'pdf': break;
                    default:
                        alert('El tipo de archivo es permitido (se permiten archivos tipo .JPG,.PNG,.DOC,.XLS,.PPT,.PDF,.ZIP)');
                }
                if (fileUpload.files[0].size > 10000000)
                { alert("El archivo no debe superar los 10MB"); }
                else { 

                // Create FormData object  
                var fileData = new FormData();  
  
                // Looping over all files and add it to FormData object  
                for (var i = 0; i < files.length; i++) {  
                    fileData.append(files[i].name, files[i]);  
                }  
              
               //  Adding one more key to FormData object  
               // fileData.append('username', 'Manas');  
                
               

                $.ajax({  
                    url: '../Register/Denuncia/save_fileSustento',
                    type: "POST",  
                    contentType: false, // Not to set any content header  
                    processData: false, // Not to process data  
                    data: fileData,  
                    success: function (result) {  
                        if (result != '-1') {
                            var listaAdjuntos = [];
                            if ($('#lstAdjuntoDenuncia').val() != '') {
                                listaAdjuntos = JSON.parse($('#lstAdjuntoDenuncia').val());
                            }
                            
                            var a = { "nvUrl": result };

                            listaAdjuntos.push(a);
                            $('#lstAdjuntoDenuncia').val(JSON.stringify(listaAdjuntos));

                            //cargando en pantalla
                            listaAdjuntosResult = [];
                            listaAdjuntosResult = JSON.parse($('#lstAdjuntoDenuncia').val());
                            $('#tblAdjuntosDenuncia').html('');
                            
                            for (var i = 0; i < listaAdjuntosResult.length; i++) {
                                var b = listaAdjuntosResult[i].nvUrl;
                                var c = '"'+b+'"';
                                $('#tblAdjuntosDenuncia').append(b + "&nbsp;&nbsp;<a target='_blank' href='../DocSustentoTemp/" + b + "'><span class='glyphicon glyphicon-folder-open'></span></a>&nbsp;&nbsp;<span onclick='removeAdjunto(" + c + ");' class='glyphicon glyphicon-trash' style='cursor:pointer;'></span></br>");
                            }
                           
                        }
                        else { /*alert('No hay archivo adjunto');*/ }
                       
                    },  
                    error: function (err) {  
                        alert(err.statusText);  
                    }  
                });
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
            base.Control.SlcTipoDocumento().append(new Option(PCM.Reclamo.Presentacion.Register.Denuncia.Resources.DNI, PCM.Reclamo.Presentacion.Register.Denuncia.Resources.DNI));

            base.Control.TxtNumeroDocumento().attr('maxlength', 8);

            $('#divPersonaNatural').show();
            $('#divRepresentante').hide();
            $('#divChkServidorPCM').show();
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
            base.Control.SlcTipoDocumento().append(new Option(PCM.Reclamo.Presentacion.Register.Denuncia.Resources.RUC, PCM.Reclamo.Presentacion.Register.Denuncia.Resources.RUC));

            base.Control.TxtNumeroDocumento().attr('maxlength', 11);
            $('#divPersonaNatural').hide();
            $('#divRepresentante').show();
            $('#divChkServidorPCM').hide();
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
            if (base.Control.DenunciaModel != null && base.Control.DenunciaModel.ListaTipoDocumento != null) {                
                $.each(base.Control.DenunciaModel.ListaTipoDocumento, function (index, value) {
                    base.Control.SlcTipoDocumento().append(new Option(value.Text, value.Value));
                });
            }

            base.Control.TxtNumeroDocumento().attr('maxlength', 12);
            $('#divPersonaNatural').hide();
            $('#divRepresentante').hide();
            $('#divChkServidorPCM').hide();
            
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
        AjaxBuscarRepresentanteSuccess: function (data) {
            if (data.IsSuccess) {
                var result = data.Result;
                if (result.length > 0) {
                    base.Control.TxtApellidoPaternoRepresentante().val(result[0].Paterno);
                    base.Control.TxtApellidoMaternoRepresentante().val(result[0].Materno);
                    base.Control.TxtNombresRepresentante().val(result[0].Nombres);
                   

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
                base.Control.RptLblMedioEntrega().html(base.Control.SlcMedioEntrega().find('option:selected').text());                 
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
           action: PCM.Reclamo.Presentacion.Register.Denuncia.Actions.BuscarPersona,
           autoSubmit: false,
           onSuccess: base.Event.AjaxBuscarPersonaSuccess
       }),
        AjaxBuscarRepresentante: new Gob.Pcm.UI.Web.Components.Ajax(
      {
          action: PCM.Reclamo.Presentacion.Register.Denuncia.Actions.BuscarPersona,
          autoSubmit: false,
          onSuccess: base.Event.AjaxBuscarRepresentanteSuccess
      }),
        AjaxBuscarUbigeoProvincia: new Gob.Pcm.UI.Web.Components.Ajax(
       {
           action: PCM.Reclamo.Presentacion.Register.Denuncia.Actions.BuscarUbigeo,
           autoSubmit: false,
           onSuccess: base.Event.AjaxBuscarUbigeoProvinciaSuccess
       }),

        AjaxBuscarUbigeoDistrito: new Gob.Pcm.UI.Web.Components.Ajax(
       {
           action: PCM.Reclamo.Presentacion.Register.Denuncia.Actions.BuscarUbigeo,
           autoSubmit: false,
           onSuccess: base.Event.AjaxBuscarUbigeoDistritoSuccess
       }),
        AjaxRegistrar: new Gob.Pcm.UI.Web.Components.Ajax(
        {
            action: PCM.Reclamo.Presentacion.Register.Denuncia.Actions.Registrar,
            autoSubmit: false,
            onSuccess: base.Event.AjaxRegistrarSuccess
        }),
        AjaxSendEmail: new Gob.Pcm.UI.Web.Components.Ajax(
       {
           action: PCM.Reclamo.Presentacion.Register.Denuncia.Actions.EnviarCorreo,
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
                    if (base.Control.CheckboxRazonSocial().is(':checked') && (base.Control.TxtApellidoPaternoRepresentante().val() == null || base.Control.TxtApellidoPaternoRepresentante().val() == "")) {
                        base.Control.TxtApellidoPaternoRepresentante().addClass("hasError");
                        isValido = false;
                    } else {
                        base.Control.TxtApellidoPaternoRepresentante().removeClass("hasError");
                    }
                    return isValido;
                },
                codeMessage: "EtiquetaApellidoPaterno"
            });
            validationsExtra.push({
                Event: function () {
                    var isValido = true;
                    if (base.Control.CheckboxRazonSocial().is(':checked') && (base.Control.TxtApellidoMaternoRepresentante().val() == null || base.Control.TxtApellidoMaternoRepresentante().val() == "")) {
                        base.Control.TxtApellidoMaternoRepresentante().addClass("hasError");
                        isValido = false;
                    } else {
                        base.Control.TxtApellidoMaternoRepresentante().removeClass("hasError");
                    }
                    return isValido;
                },
                codeMessage: "EtiquetaApellidoMaterno"
            });
            validationsExtra.push({
                Event: function () {
                    var isValido = true;
                    if (base.Control.CheckboxRazonSocial().is(':checked') && (base.Control.TxtNombresRepresentante().val() == null || base.Control.TxtNombresRepresentante().val() == "")) {
                        base.Control.TxtNombresRepresentante().addClass("hasError");
                        isValido = false;
                    } else {
                        base.Control.TxtNombresRepresentante().removeClass("hasError");
                    }
                    return isValido;
                },
                codeMessage: "EtiquetaNombres"
            });
            validationsExtra.push({
                Event: function () {
                    var isValido = true;
                    if (base.Control.TxtTelefono().val() == "") {
                        base.Control.TxtTelefono().addClass("hasError");
                        isValido = false;
                    } 
                    else {
                        base.Control.TxtTelefono().removeClass("hasError");
                    }
                    return isValido;
                },
                codeMessage: "EtiquetaCorreoElectronico"
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

            validationsExtra.push({
                Event: function () {
                    var isValido = true;
                    if ($('#lstAdjuntoDenuncia').val()== '') {
                        $('#DocumentUploadTB').addClass("hasError");
                        isValido = false;
                    }
                    else {
                        $('#DocumentUploadTB').removeClass("hasError");
                    }
                    return isValido;
                },
                codeMessage: "EtiquetaValidaAdjuntos"
            });

            validationsExtra.push({
                Event: function () {
                    var isValido = true;
                    if ( (base.Control.TxtApellidoPaternoDenunciado().val() == null || base.Control.TxtApellidoPaternoDenunciado().val() == "")) {
                        base.Control.TxtApellidoPaternoDenunciado().addClass("hasError");
                        isValido = false;
                    } else {
                        base.Control.TxtApellidoPaternoDenunciado().removeClass("hasError");
                    }
                    return isValido;
                },
                codeMessage: "EtiquetaApellidoPaternoDenunciado"
            });
            validationsExtra.push({
                Event: function () {
                    var isValido = true;
                    if ((base.Control.TxtApellidoMaternoDenunciado().val() == null || base.Control.TxtApellidoMaternoDenunciado().val() == "")) {
                        base.Control.TxtApellidoMaternoDenunciado().addClass("hasError");
                        isValido = false;
                    } else {
                        base.Control.TxtApellidoMaternoDenunciado().removeClass("hasError");
                    }
                    return isValido;
                },
                codeMessage: "EtiquetaApellidoMaternoDenunciado"
            });
            validationsExtra.push({
                Event: function () {
                    var isValido = true;
                    if ((base.Control.TxtNombresDenunciado().val() == null || base.Control.TxtNombresDenunciado().val() == "")) {
                        base.Control.TxtNombresDenunciado().addClass("hasError");
                        isValido = false;
                    } else {
                        base.Control.TxtNombresDenunciado().removeClass("hasError");
                    }
                    return isValido;
                },
                codeMessage: "EtiquetaNombresDenunciado"
            });

            return validationsExtra;
        }
    };


};
function removeAdjunto(string) {
    //cargando en pantalla
    listaAdjuntosResult = [];
    listaModificar = [];
    listaAdjuntosResult = JSON.parse($('#lstAdjuntoDenuncia').val());
 
    $('#tblAdjuntosDenuncia').html('');

   

    if (listaAdjuntosResult.length > 0) {
        for (var i = 0; i < listaAdjuntosResult.length; i++) {
     
            if (listaAdjuntosResult[i].nvUrl.trim() != string.trim()) {
                var b = listaAdjuntosResult[i].nvUrl;
                var c = '"' + b + '"';
                $('#tblAdjuntosDenuncia').append(b + "&nbsp;&nbsp;<a target='_blank' href='../DocSustentoTemp/" + b + "'><span class='glyphicon glyphicon-folder-open'></span></a>&nbsp;&nbsp;<span onclick='removeAdjunto(" + c + ");' class='glyphicon glyphicon-trash' style='cursor:pointer;'></span></br>");
                var a = { "nvUrl": listaAdjuntosResult[i].nvUrl };
                listaModificar.push(a);
            }
         
            
        }

        if (listaModificar.length > 0) {
            $('#lstAdjuntoDenuncia').val(JSON.stringify(listaModificar));
        }
        else { $('#lstAdjuntoDenuncia').val(''); }

        $.ajax({
            url: PCM.Reclamo.Presentacion.Register.Denuncia.Actions.SaveFileSustento,
            type: "POST",
            dataType: "json",
            contentType: "application/json; charset=utf-8",
            data: "url=" + string,
            success: function (result) {
                return false;
            }
        });

    }

};
