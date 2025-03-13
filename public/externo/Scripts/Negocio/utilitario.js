
/**
* serializeObject2: convierte los campos(input, textarea, select) del contenedor al que se aplique a un objeto
* @returns {object} Retorna un objeto con las valores de los campos del contenedor
*/
$.fn.serializeObject2 = function () {
    var o = {};
    var fields = $("input, textarea, select", $(this));
    $(fields).each(function (i, element) {
        if (o[$(element).attr("name")] !== undefined) {

        } else {
            o[$(element).attr("name")] = $(element).val();
        }
    });
    return o;
};

/*
* macExpressDatePicker: Adiciona el formato para los campos tipo Fecha y instancia el datePicker de JqueryUI
* @param {boolean} readOnlyControl, indica si el campo es readOnly, lo cual define que no se debe instanciar el datePicker, false por defecto  
*/
$.fn.macExpressDatePicker = function (readOnlyControl) {
    "use strict";
    readOnlyControl = readOnlyControl || false;
    // constructor ----------------------------------------------------------------------------
    var initial = function (control, readOnlyControl) {
        var padre = control.parent();
        var datePicker = $('<div class="input-group input-group-sm"></div>').append('<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>');
        padre.append(datePicker); $(control).appendTo($('div.input-group.input-group-sm', padre));
        if (!readOnlyControl) { $(control).mask('99/99/9999').datepicker(); }

    };
    return this.each(function () {
        var element = $(this); if (!element) { return this; } initial(element, readOnlyControl);
    });
};

$.fn.validacion = function (settings) {
    "use strict";
    var form = this;
    if ($(form).is('form')) {
        $(form).submit(function (event) {
            if ($(form).validar()) {
                if (settings.callback != null) { settings.callback(); }
                return true;
            } else {
                alertaPopup('Complete los campos obligatorios, que se encuentran marcados en rojo'); event.preventDefault();
            }
        });
    }
    //si agregamos validaciones manuales ===
    settings = $.extend({}, $.fn.validacionDefaults, settings || {});
    for (var i = 0; i < settings.validationAdd.length; i++) {
        if (settings.validationAdd[i].tipo == 'requiredOneOf') {
            $(settings.validationAdd[i].controls).attr('data-val', 'true').attr('data-val-requiredoneof', settings.validationAdd[i].groupName);
        }
        else if (settings.validationAdd[i].tipo == 'requiredOneOfIf') {
            $(settings.validationAdd[i].controls).attr({
                'data-val': 'true',
                'data-val-requiredoneofif': settings.validationAdd[i].groupName,
                'data-val-requiredoneofif-compare': settings.validationAdd[i].compare,
                'data-val-requiredoneofif-conditional': settings.validationAdd[i].conditional,
                'data-val-requiredoneofif-value': settings.validationAdd[i].value
            });
        }
        else if (settings.validationAdd[i].tipo == 'requiredIf') {
            $('#' + settings.validationAdd[i].control).attr({ 'data-val': 'true', 'data-val-requiredif': settings.validationAdd[i].compare, 'data-val-requiredif-conditional': settings.validationAdd[i].conditional, 'data-val-requiredif-value': settings.validationAdd[i].value });
        } else if (settings.validationAdd[i].tipo == 'gridRequiereDetalle') {
            $(form).append('<input type="hidden" id="req' + settings.validationAdd[i].control + '" name="req' + settings.validationAdd[i].control + '" value="" />');
            $('#req' + settings.validationAdd[i].control).attr({ 'data-val': 'true', 'data-val-gridrequired': settings.validationAdd[i].control, 'data-val-gridrequired-min': settings.validationAdd[i].minRows });
        }
    }
};

$.fn.validar = function (settings) {
    "use strict";
    // constructor ----------------------------------------------------------------------------
    var formulario = $(this);
    settings = $.extend({}, $.fn.validarDefaults, settings || {});
    if (!formulario) {  return this; }
    var retval = true;
    $('.' + settings.errorClass, formulario).removeClass(settings.errorClass);
    var campos = $('input[data-val="true"], select[data-val="true"], textarea[data-val="true"]', formulario);
    
    $.each(campos, function (indice, elemento) {
        //'1: valido tipos de datos ===
        //var attr = $(this).attr('data-val-number');
        // For some browsers, `attr` is undefined; for others,
        // `attr` is false.  Check for both.
        if (typeof $(elemento).attr('data-val-number') !== typeof undefined && $(elemento).attr('data-val-number') !== false) {
            if (!$.isNumeric($(elemento).val())) {
                // si no es numero
                $(elemento).removeClass(settings.validClass).addClass(settings.errorClass); retval = false;
               
            } /*else {
                $(elemento).removeClass(settings.errorClass).addClass(settings.validClass);
            }*/
        }
        //'2: valido los requeridos ==
        if (typeof $(elemento).attr('data-val-required') !== typeof undefined && $(elemento).attr('data-val-required') !== false) {
            if ($.trim($(elemento).val()) == '') {
                // si es un bootstrap-select
                $('button.dropdown-toggle', $(elemento).parent()).addClass(settings.errorClass);
                $(elemento).removeClass(settings.validClass).addClass(settings.errorClass); retval = false;
             
            } /*else {
                $(elemento).removeClass(settings.errorClass).addClass(settings.validClass);
            }*/
        }
        //'3: valido las longitudes del contenido ===
        if (typeof $(elemento).attr('data-val-length') !== typeof undefined && $(elemento).attr('data-val-length') !== false) {
            if ($.trim($(elemento).val()).length > parseInt($(elemento).attr('data-val-length-max'))) {
                $(elemento).removeClass(settings.validClass).addClass(settings.errorClass); retval = false;
               
            } /*else {
                $(elemento).removeClass(settings.errorClass).addClass(settings.validClass);
            }*/
        }
    });
  
    var requeridoUnoDe = $(campos).filter(function (i, campo) {
        var g = typeof $(campo).attr('data-val-requiredoneof') !== typeof undefined && $(campo).attr('data-val-requiredoneof') !== false;
        if (g) { $(campo).removeClass(settings.validClass + ' ' + settings.errorClass); }
        return g;
    });
    $.each(requeridoUnoDe, function (indice, elemento) {
        if (!$(elemento).hasClass(settings.validClass) || !$(elemento).hasClass(settings.errorClass)) {
            var mismogrupo = $(campos).filter(function (i2, elemento2) { return $(elemento2).attr('data-val-requiredoneof') == $(elemento).attr('data-val-requiredoneof'); });
            var cantidad = $(mismogrupo).filter(function (i2, elemento2) {

                return ($(elemento2).is(':checkbox') || $(elemento2).is(':radio') ? $(elemento2).is(':checked') : ($.trim($(elemento2).val()) != ''));
                //return $.trim($(elemento2).val()) != ''; 

            }).length;
            if (cantidad == 0) {
                retval = false;
                $.each(mismogrupo, function (indice, elemento3) {
                    if ($(elemento3).is(':checkbox') || $(elemento3).is(':radio')) {
                        $(elemento3).parent().removeClass(settings.errorClass).addClass(settings.errorClass);
                    } else {
                        $(elemento3).removeClass(settings.validClass).addClass(settings.errorClass);
                    }
                    //$(elemento3).removeClass(settings.validClass).addClass(settings.errorClass); 
                });
            } else {
                $.each(mismogrupo, function (indice, elemento3) {
                    if ($(elemento3).is(':checkbox') || $(elemento3).is(':radio')) {
                        $(elemento3).parent().removeClass(settings.errorClass).addClass(settings.validClass);
                    } else {
                        $(elemento3).removeClass(settings.errorClass).addClass(settings.validClass);
                    }
                    //$(elemento3).removeClass(settings.errorClass).addClass(settings.validClass); 
                });
            }
        }
       
    });
   
    var requeridoSi = $(campos).filter(function (i, campo) {
        return typeof $(campo).attr('data-val-requiredif') !== typeof undefined && $(campo).attr('data-val-requiredif') !== false;
    });
    $.each(requeridoSi, function (indice, elemento) {

        var compare = $(elemento).attr('data-val-requiredif');
        var condition = $(elemento).attr('data-val-requiredif-conditional');
        var value = $(elemento).attr('data-val-requiredif-value');
        if (condition == '=') { //si es igual
            //Para soportar varios elementos a comparar
            if (compare.indexOf(',') == -1) {
                compare = '' + ($('#' + compare).is(':checkbox') || $('#' + compare).is(':radio') ? $('#' + compare).is(':checked') : $('#' + compare).val());
                if (value == compare) {
                    if ($(elemento).is(':checkbox') || $(elemento).is(':radio')) {
                        if ($(elemento).is(':checked')) { $(elemento).parent().removeClass(settings.errorClass).addClass(settings.validClass); }
                        else { retval = false; $(elemento).parent().removeClass(settings.validClass).addClass(settings.errorClass); }
                    } else {
                        if ($(elemento).val() != '') { $(elemento).removeClass(settings.errorClass).addClass(settings.validClass); }
                        else { retval = false; $(elemento).removeClass(settings.validClass).addClass(settings.errorClass); }
                    }
                } else {
                    if ($(elemento).is(':checkbox') || $(elemento).is(':radio')) {
                        $(elemento).parent().removeClass(settings.errorClass).addClass(settings.validClass);
                    } else {
                        $(elemento).removeClass(settings.errorClass).addClass(settings.validClass);
                    }
                }
            } else {
                $.each(compare.split(','), function (indice4, elemento4) {
                    var d = '' + ($('#' + elemento4).is(':checkbox') || $('#' + elemento4).is(':radio') ? $('#' + elemento4).is(':checked') : $('#' + elemento4).val());
                    if (value == d) {
                        if ($(elemento).is(':checkbox') || $(elemento).is(':radio')) {
                            if ($(elemento).is(':checked')) { $(elemento).parent().removeClass(settings.errorClass).addClass(settings.validClass); }
                            else { retval = false; $(elemento).parent().removeClass(settings.validClass).addClass(settings.errorClass); }
                        } else {
                            if ($(elemento).val() != '') { $(elemento).removeClass(settings.errorClass).addClass(settings.validClass); }
                            else { retval = false; $(elemento).removeClass(settings.validClass).addClass(settings.errorClass); }
                        }
                    }
                });
            }
        } else if (condition == '!=') { //si es diferente
            compare = ($('#' + compare).is(':checkbox') || $('#' + compare).is(':radio') ? $('#' + compare).is(':checked') : $('#' + compare).val());
            if (value != compare) {
                if ($(elemento).is(':checkbox')) {
                    if ($(elemento).is(':checked')) { $(elemento).parent().removeClass(settings.errorClass).addClass(settings.validClass); }
                    else { retval = false; $(elemento).parent().removeClass(settings.validClass).addClass(settings.errorClass); }
                } else {
                    if ($(elemento).val() != '') { $(elemento).removeClass(settings.errorClass).addClass(settings.validClass); }
                    else { retval = false; $(elemento).removeClass(settings.validClass).addClass(settings.errorClass); }
                }
            } else { $(elemento).parent().removeClass(settings.errorClass).addClass(settings.validClass); }
        }
    });

    var requeridoUnoDeSi = $(campos).filter(function (i, campo) {
        var g = typeof $(campo).attr('data-val-requiredoneofif') !== typeof undefined && $(campo).attr('data-val-requiredoneofif') !== false;
        if (g) { $(campo).removeClass(settings.validClass + ' ' + settings.errorClass); }
        return g;
    });
    $.each(requeridoUnoDeSi, function (indice, elemento) {

        if (!$(elemento).hasClass(settings.validClass) || !$(elemento).hasClass(settings.errorClass)) {
            var compare = $(elemento).attr('data-val-requiredoneofif-compare');
            var condition = $(elemento).attr('data-val-requiredoneofif-conditional');
            var value = $(elemento).attr('data-val-requiredoneofif-value');
            if (condition == '=') { //si es igual
                //Para soportar varios elementos a comparar
                if (compare.indexOf(',') == -1) {
                    compare = '' + ($('#' + compare).is(':checkbox') || $('#' + compare).is(':radio') ? $('#' + compare).is(':checked') : $('#' + compare).val());
                    if (value == compare) {
                        var mismogrupo = $(campos).filter(function (i2, elemento2) { return $(elemento2).attr('data-val-requiredoneofif') == $(elemento).attr('data-val-requiredoneofif'); });
                        var cantidad = $(mismogrupo).filter(function (i2, elemento2) { return IsCheckedOrHasValue(elemento2); }).length;
                        if (cantidad == 0) {
                            retval = false;
                            $.each(mismogrupo, function (indice, elemento3) {
                                //$(elemento3).removeClass(settings.validClass).addClass(settings.errorClass);
                                if ($(elemento3).parent().is('td')) { $(elemento3).parent().addClass('danger'); }
                                else {
                                    if ($(elemento).is(':checkbox') || $(elemento).is(':radio')) { $(elemento3).parent().removeClass(settings.validClass).addClass(settings.errorClass); }
                                    else { $(elemento3).removeClass(settings.validClass).addClass(settings.errorClass); }
                                }
                            });
                        } else {
                            $.each(mismogrupo, function (indice, elemento3) {
                                //$(elemento3).removeClass(settings.errorClass).addClass(settings.validClass); 
                                if ($(elemento3).parent().is('td')) { $(elemento3).parent().removeClass('danger'); }
                                else {
                                    if ($(elemento3).is(':checkbox') || $(elemento3).is(':radio')) { $(elemento3).parent().removeClass(settings.errorClass).addClass(settings.validClass); }
                                    else { $(elemento3).removeClass(settings.errorClass).addClass(settings.validClass); }
                                }
                            });
                        }
                    }
                } else {
                    $.each(compare.split(','), function (indice4, elemento4) {
                        var d = '' + ($('#' + elemento4).is(':checkbox') || $('#' + elemento4).is(':radio') ? $('#' + elemento4).is(':checked') : $('#' + elemento4).val());
                        if (value == d) {
                            var mismogrupo = $(campos).filter(function (i2, elemento2) { return $(elemento2).attr('data-val-requiredoneofif') == $(elemento).attr('data-val-requiredoneofif'); });
                            var cantidad = $(mismogrupo).filter(function (i2, elemento2) {
                                return ($(elemento2).is(':checkbox') || $(elemento2).is(':radio') ? $(elemento2).is(':checked') : ($.trim($(elemento2).val()) != ''));
                            }).length;
                            if (cantidad == 0) {
                                retval = false;
                                $.each(mismogrupo, function (indice, elemento3) {
                                    if ($(elemento3).parent().is('td')) { $(elemento3).parent().addClass('danger'); }
                                    else {
                                        if ($(elemento).is(':checkbox') || $(elemento).is(':radio')) { $(elemento3).parent().removeClass(settings.validClass).addClass(settings.errorClass); }
                                        else { $(elemento3).removeClass(settings.validClass).addClass(settings.errorClass); }
                                    }
                                });
                            } else {
                                $.each(mismogrupo, function (indice, elemento3) {
                                    if ($(elemento3).parent().is('td')) { $(elemento3).parent().removeClass('danger'); }
                                    else {
                                        if ($(elemento3).is(':checkbox') || $(elemento3).is(':radio')) { $(elemento3).parent().removeClass(settings.errorClass).addClass(settings.validClass); }
                                        else { $(elemento3).removeClass(settings.errorClass).addClass(settings.validClass); }
                                    }
                                });
                            }
                        }
                    });
                }
            }

        }
    });

    var gridRequerido = $(campos).filter(function (i, campo) {
        var g = typeof $(campo).attr('data-val-gridrequired') !== typeof undefined && $(campo).attr('data-val-gridrequired') !== false;
        if (g) { $(campo).removeClass(settings.validClass + ' ' + settings.errorClass); }
        return g;
    });
    $.each(gridRequerido, function (indice, elemento) {

        if (!$(elemento).hasClass(settings.validClass) || !$(elemento).hasClass(settings.errorClass)) {
            var cantidad = $('#' + $(elemento).attr('data-val-gridrequired')).bootstrapTable('getData').length;
            if (cantidad < parseInt($(elemento).attr('data-val-gridrequired-min'))) {
                retval = false;
                $('#' + $(elemento).attr('data-val-gridrequired') + ' tr.no-records-found').addClass('danger').find('td').html('Debe ingresar al menos ' + $(elemento).attr('data-val-gridrequired-min') + ' detalle');
            } /*else {
                $.each(mismogrupo, function (indice, elemento3) {
                    if ($(elemento3).is(':checkbox') || $(elemento3).is(':radio')) {
                        $(elemento3).parent().removeClass(settings.errorClass).addClass(settings.validClass);
                    } else {
                        $(elemento3).removeClass(settings.errorClass).addClass(settings.validClass);
                    }
                });
            }*/
        }

    });
    return retval;
};


/**
* macexpressFileUpload: implementa la validación, del tipo de archivo adjuntado
* @param {object} settings, objecto del tipo { types: "img", callback: function (control) { }, descripcionControl: "#", maxFileSize: 2105843 };
* @returns {elements} Returns el, los elementos encontrados 
*/
$.fn.macexpressFileUpload = function (settings) {
    "use strict";

    // constructor ----------------------------------------------------------------------------
    var defaults = { types: "img", callback: function (control) { }, descripcionControl: "#", maxFileSize: 2048, hasValue: false, btnSelect: '', btnDownload: '', addHelpText: true, addHelpTextDescripcion: true },
        initial = function (control, settings) {

            //if (navigator.appVersion.indexOf('MSIE') == -1) {
            // no es IE
            var newControl = $('<div class="input-group input-group-sm"></div>');
            var opciones = '';
            var padre = $(control).parent();
            if (settings.hasValue != '') {
                opciones = '<span class="input-group-btn"><span class="btn btn-default btn-file">Cambiar&hellip;</span>' +
                    '<button id="' + settings.btnDownload + '" class="btn btn-default btn-view" type="button">Ver</button>' +
                    '</span>';
            } else {
                opciones = '<span class="input-group-btn"><span class="btn btn-default btn-file">Examinar&hellip;</span></span>';
            }

            $(newControl).append(
                '<span class="input-group-addon"><span class="glyphicon glyphicon-cloud-upload"></span></span>',
                '<input class="form-control" id="' + settings.descripcionControl + '" name="' + settings.descripcionControl + '" readonly="readOnly" type="text" value="' + settings.hasValue + '" />',
                opciones
            );
            $(padre).append(newControl);
            if (settings.addHelpText) {
                var extArray = '', extensiones = '';
                extensiones = settings.types.split(',');
                for (var i = 0; i < extensiones.length; i++) {
                    extArray = extArray + (extArray != '' ? ',' : '');
                    if (extensiones[i] == "img") {
                        extArray = extArray + ".gif, .jpg, .png, .tif";
                    } else if (extensiones[i] == "docs") {
                        extArray = extArray + ".doc, .docx, .pdf, .txt";
                    } else if (extensiones[i] == "pdf") {
                        extArray = extArray + " .pdf";
                    } else if (extensiones[i] == "xls") {
                        extArray = extArray + ".xls, .xlsx";
                    } else if (extensiones[i] == "audio") {
                        extArray = extArray + ".mp3, .wma, .wav";
                    } else if (extensiones[i] == "video") {
                        extArray = extArray + ".avi, .mpg, .wmv, .mp4, .3gp, .mov";
                    } else if (extensiones[i] == "all") {
                        extArray = extArray + ".doc, .docx, .xls, .xlsx, .pdf, .gif, .jpg, .png, .tif, .mp3, .wma, .wav, .avi, .mpg, .wmv, .mp4, .3gp, .mov";
                    }
                }
                if (extArray != '') { extArray = '[' + extArray + ']'; }

                $(padre).append('<p class="help-block text-left">Adjuntar un Archivo ' + extArray + ' de hasta ' + (Math.floor(settings.maxFileSize / 1024)) + ' MB [' + settings.maxFileSize + ' kb] ' + (settings.addHelpTextDescripcion ? ', ingrese una descripci&oacute;n' : '') + '</p>');
            }
            $(control).appendTo($('span.btn-file', padre));
            //}

            //-----------
            $(control).on("change", function () {
                var file = $(this).val().split("\\"), extArray = '', allowSubmit = 0, ext = "", cont = 0, extensiones = '';
                if (!file || file.length == 0 || file == "") {
                    $('#' + settings.descripcionControl).val("");
                    return;
                }
                extensiones = settings.types.split(',');
                for (var i = 0; i < extensiones.length; i++) {
                    extArray = extArray + (extArray != '' ? ',' : '');
                    if (extensiones[i] == "img") {
                        extArray = extArray + ".gif,.jpg,.png,.tif";
                    } else if (extensiones[i] == "docs") {
                        extArray = extArray + ".doc,.docx,.pdf,.txt";
                    } else if (extensiones[i] == "pdf") {
                        extArray = extArray + ".pdf";
                    } else if (extensiones[i] == "xls") {
                        extArray = extArray + ".xls,.xlsx";
                    } else if (extensiones[i] == "audio") {
                        extArray = extArray + ".mp3,.wma,.wav";
                    } else if (extensiones[i] == "video") {
                        extArray = extArray + ".avi,.mpg,.wmv,.mp4,.3gp,.mov";
                    } else if (extensiones[i] == "all") {
                        extArray = extArray + ".doc,.docx,.xls,.xlsx,.pdf,.gif,.jpg,.png,.tif,.mp3,.wma,.wav,.avi,.mpg,.wmv,.mp4,.3gp,.mov";
                    }
                }
                extArray = extArray.split(',');
                cont = file[file.length - 1].split(".");
                ext = "." + file[file.length - 1].split(".")[cont.length - 1];
                for (var i = 0; i < extArray.length; i++) {
                    if (extArray[i] == ext.toLowerCase()) {
                        allowSubmit = 1;
                        break;
                    }
                }
                if (allowSubmit == 1) {
                    if (file[file.length - 1].length > 150) { allowSubmit = -1; }
                }
                if (allowSubmit == 0) {
                    $(this).val("");
                    $('#' + settings.descripcionControl).val("");
                    alertaPopup("Usted sólo puede subir archivos con extensiones [" + (extArray.join(",")) + "]\nPor favor seleccione otro archivo");
                    //} else if ($(this)[0].files[0].size > settings.maxFileSize) {
                    //    $(this).val("");
                    //    $(settings.descripcionControl).val("");
                    //    alertaPopup("El archivo supera los 2MB [2048 KB] permitidos. Seleccione un archivo que no sobrepase el tamaño máximo permitido");
                } else if (allowSubmit == -1) {
                    $(this).val("");
                    $('#' + settings.descripcionControl).val("");
                    alertaPopup("El nombre del archivo pasa los 150 caracteres permitios.\n\nPor favor seleccione otro archivo o redusca su nombre");
                } else {
                    var nombre = $(this).val().split("\\");
                    nombre = nombre[nombre.length - 1];
                    $('#' + settings.descripcionControl).val(nombre);
                    settings.callback($(this));
                }
            });
            //--------
        };

    return this.each(function () {
        var element = $(this);
        settings = $.extend({}, defaults, settings || {});
        if (!element) { return this; }
        initial(element, settings);
    });
};