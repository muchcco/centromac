/// Copyright (c) 2017.
/// All rights reserved.
/// <summary>
/// Controlador de métodos genericos
/// </summary>
/// <remarks>
/// Creacion: WECM 25092017 <br />
/// </remarks>
ns('Gob.Pe.UI.Web.Components.Util');
Gob.Pe.UI.Web.Components.Util.GetKeyCode = function (e) {
    return e.keyCode ? e.keyCode : e.which ? e.which : e.charCode;
}

Gob.Pe.UI.Web.Components.Util.GetValueCopy = function (e) {
    var texto = "";
    if (window.clipboardData) {
        texto = window.clipboardData.getData('Text');
    }
    else {
        texto = e.originalEvent.clipboardData.getData('text/plain');
    }
    return texto;
}

Gob.Pe.UI.Web.Components.Util.ValidateBlurOnlyDate = function (e) {
    var ok = true;
    if (e.value.length < 10) {
        ok = false;
    }
    else {
        try {
            var date = $.datepicker.parseDate(Gob.Pe.UI.Web.Formato.Fecha, e.value);
            ok = (date.getFullYear() >= 1900)
        }
        catch (err) {
            ok = false;
        }
    }

    if (!ok) {
        $('#' + e.id).val('');
    }

    return ok;
}

Gob.Pe.UI.Web.Components.Util.ValidateCopyDate = function validarCopyFecha(e) {
    var texto = GetCopiedValue(e);
    return ValidateCopyDate(texto);
}

Gob.Pe.UI.Web.Components.Util.ValidateCopyOnlyNumeric = function (e) {
    var text = Gob.Pe.UI.Web.Components.Util.GetValueCopy(e);
    return Gob.Pe.UI.Web.Components.Util.ValidateStringOnlyNumeric(text);
}

Gob.Pe.UI.Web.Components.Util.GetCopiedValue = function (e) {
    var text = "";
    if (window.clipboardData) {
        text = window.clipboardData.getData('Text');
    }
    else {
        text = e.originalEvent.clipboardData.getData('text/plain');
    }
    return text;
}

Gob.Pe.UI.Web.Components.Util.ValidateStringOnlyNumeric = function (text) {
    var patron = /^[0-9\r\n]+$/;
    if (!text.search(patron))
        return true;
    else
        return false;
}

Gob.Pe.UI.Web.Components.Util.ValidaCadenaSoloTexto = function (text) {
    var patron = /^[a-zA-Z]*$/;
    if (!text.search(patron))
        return true;
    else
        return false;
}

Gob.Pe.UI.Web.Components.Util.ValidateOnlyNumbers = function (e) {
    /*Validar la existencia del objeto event*/
    e = (e) ? e : event;

    var key = Gob.Pe.TemplateApp.Presentation.Web.Shared.Util.GetKeyCode(e);

    /*Predefinir como invalido*/
    var result = false;

    if (key >= 48 && key <= 57)
    { result = true; }
    if (evento.charCode == 0)/*direccionales*/
    { result = true; }

    if (key == 13)/*enter*/
    { result = true; }

    /*Regresar la result*/
    return result;
}

Gob.Pe.UI.Web.Components.Util.ValidateCopyOnlyAlphanumeric = function (e) {
    var text = Gob.Pe.UI.Web.Components.Util.GetValueCopy(e);
    var patron = /^[\u00F1A-Za-z0-9\-.\s]+$/i;
    var result = patron.test(text);
    return result;
}

Gob.Pe.UI.Web.Components.Util.ValidarCopiarSoloTexto = function (e) {
    var text = Gob.Pe.UI.Web.Components.Util.GetValueCopy(e);
    return Gob.Pe.UI.Web.Components.Util.ValidaCadenaSoloTexto(text);
}

Gob.Pe.UI.Web.Components.Util.ValidarCopiarSoloNumeros = function (e) {
    var text = Gob.Pe.UI.Web.Components.Util.GetValueCopy(e);
    return Gob.Pe.UI.Web.Components.Util.ValidateStringOnlyNumeric(text);
}

Gob.Pe.UI.Web.Components.Util.ValidateCopyDate = function validarCampoFecha(value) {
    var date_regex = /^(0[1-9]|1\d|2\d|3[01])\/(0[1-9]|1[0-2])\/(19|20)\d{2}$/;
    return date_regex.test(value);
};

Gob.Pe.UI.Web.Components.Util.RemoveDrop = function () {
    var controls = $("input[type=text], input[type=password], textarea");
    controls.bind("drop", function () {
        return false;
    });
    controls = undefined;
}
 
Gob.Pe.UI.Web.Components.Util.RenderIndicadorCheck = function (data, type, full) {
    var etiqueta = '';

    if (data === true)
        etiqueta = '<span class="control-table"><i class="fa fa-check-square" style="font-size: 16px"></i></span>'; 
    else if (data === false)
        etiqueta = '<span class="control-table"><i class="fa fa-square-o" style="font-size: 16px"></i></span>'; 

    return etiqueta;
}

Gob.Pe.UI.Web.Components.Util.RenderIcono = function (clase, icono, tooltip) {
    var etiqueta = '';

    if (tooltip)
        etiqueta = 'data-toggle="tooltip" data-placement="top" title="' + tooltip + '"'

    etiqueta = '<span class="control-table ' + clase + '" ' + etiqueta + '><i class="fa ' + icono + '"></i></span>';

    return etiqueta;
}

Gob.Pe.UI.Web.Components.Util.RenderIconoAccion = function (editar, eliminar) {
    var etiqueta = '';

    editar = (editar !== false);
    eliminar = (eliminar !== false);

    if (editar)
        etiqueta += Gob.Pe.UI.Web.Components.Util.RenderIcono('edit', 'fa-edit', Pe.Stracon.Politicas.Presentacion.Base.GenericoResource.EtiquetaEditar);

    if (eliminar)
        etiqueta += Gob.Pe.UI.Web.Components.Util.RenderIcono('delete', 'fa-trash', Pe.Stracon.Politicas.Presentacion.Base.GenericoResource.EtiquetaEliminar);

    return etiqueta;
}


Gob.Pe.UI.Web.Components.Util.RedirectPost = function (location, args) {
    var form = '';
    $.each(args, function (key, value) {
        form += '<input type="hidden" name="' + key + '" value="' + value + '">';
    });
    var submit = $('<form action="' + location + '" method="POST" target="_blank">' + form + '</form>');//_self
    $('body').after(submit);
    submit.submit();
}

Gob.Pe.UI.Web.Components.Util.RedirectReportingPost = function (location, datos, parametros) {
    var form = '';
    $.each(datos, function (key, value) {
        form += '<input type="hidden" name="' + key + '" value="' + value + '">';
    });
    $.each(parametros, function (key, value) {
        form += '<input type="hidden" name="Parametros[' + key + '].Name"  value="' + value.Name + '">';
        form += '<input type="hidden" name="Parametros[' + key + '].Values"  value="' + value.Values + '">';
        form += '<input type="hidden" name="Parametros[' + key + '].Visible"  value="' + value.Visible + '">';
    });
    var submit = $('<form action="' + location + '" method="POST" target="_self">' + form + '</form>');
    $('body').after(submit);
    submit.submit();
}

Gob.Pe.UI.Web.Components.Util.Left = function (cadena, len) {
    if (len <= 0)
        return "";
    else if (len > String(cadena).length)
        return cadena;
    else
        return String(cadena).substring(0, len);
}

Gob.Pe.UI.Web.Components.Util.Right = function (cadena, len) {
    if (len <= 0)
        return "";
    else if (len > String(cadena).length)
        return str;
    else {
        var iLen = String(cadena).length;
        return String(cadena).substring(iLen, iLen - len);
    }
};

Gob.Pe.UI.Web.Components.Util.StringFormat = function () {
    // The string containing the format items (e.g. "{0}")
    // will and always has to be the first argument.
    var theString = arguments[0];

    // start with the second argument (i = 1)
    for (var i = 1; i < arguments.length; i++) {
        // "gm" = RegEx options for Global search (more than one instance)
        // and for Multiline search
        var regEx = new RegExp("\\{" + (i - 1) + "\\}", "gm");
        theString = theString.replace(regEx, arguments[i]);
    }

    return theString;
};

Gob.Pe.UI.Web.Components.Util.SoloLetras = function (e) {
    var tecla = (document.all) ? e.keyCode : e.which;
    patron = /^[a-zA-ZáéíóúàèìòùÀÈÌÒÙÁÉÍÓÚñÑüÜ\s]+$/;
    te = String.fromCharCode(tecla);
    return patron.test(te);

};
Gob.Pe.UI.Web.Components.Util.SoloNumeros = function (e) {
    var tecla = (document.all) ? e.keyCode : e.which;
    patron = /^[0-9]+$/;
    te = String.fromCharCode(tecla);
    return patron.test(te);
};
Gob.Pe.UI.Web.Components.Util.SoloEmail = function (value) {
    patron = /^[A-Z0-9._%+-]+@([A-Z0-9-]+\.)+[A-Z]{2,4}$/i;
    return patron.test(value);
};

Gob.Pe.UI.Web.Components.Util.MergeData = function (temp, dataarry) {
    return temp.replace(/\$\{([\w]+)\}/g, function (s1, s2) { var s = dataarry[s2]; if (typeof (s) != "undefined") { return s; } else { return s1; } });
};

(function ($, undefined) {
    $.fn.getCursorPosition = function () {
        var el = $(this).get(0);
        var pos = 0;
        if ('selectionStart' in el) {
            pos = el.selectionStart;
        } else if ('selection' in document) {
            el.focus();
            var Sel = document.selection.createRange();
            var SelLength = document.selection.createRange().text.length;
            Sel.moveStart('character', -el.value.length);
            pos = Sel.text.length - SelLength;
        }
        return pos;
    }
})(jQuery);

//Giovanni Rivera C.
function getCursorTinyPosicion(editor) {
    //set a bookmark so we can return to the current position after we reset the content later
    var bm = editor.selection.getBookmark(0);
    //select the bookmark element
    var selector = "[data-mce-type=bookmark]";
    var bmElements = editor.dom.select(selector);
    //put the cursor in front of that element
    editor.selection.select(bmElements[0]);
    editor.selection.collapse();
    //add in my special span to get the index...
    //we won't be able to use the bookmark element for this because each browser will put id and class attributes in different orders.
    var elementID = "######cursor######";
    var positionString = '<span id="' + elementID + '"></span>';
    editor.selection.setContent(positionString);
    //get the content with the special span but without the bookmark meta tag
    var content = editor.getContent({ format: "html" });
    //find the index of the span we placed earlier
    var index = content.indexOf(positionString);
    //remove my special span from the content
    editor.dom.remove(elementID, false);
    //move back to the bookmark
    editor.selection.moveToBookmark(bm);
    return index;
};

Gob.Pe.UI.Web.Components.Util.ConvertToDecimal = function (value) {

    var format = Gob.Pe.UI.Web.Formato.Decimal;
    var number = value;

    format = format.replace(Gob.Pe.UI.Web.Formato.DecimalSeparadorDecimal, '@');
    format = format.replace(Gob.Pe.UI.Web.Formato.DecimalSeparadorMiles, ',');
    format = format.replace('@', '.');

    number = number.replace(Gob.Pe.UI.Web.Formato.DecimalSeparadorDecimal, '@');
    number = number.replace(Gob.Pe.UI.Web.Formato.DecimalSeparadorMiles, ',');
    number = number.replace('@', '.');

    number = $.parseNumber(number, { format: format });

    return parseFloat(number);
};

Gob.Pe.UI.Web.Components.Util.DecimalConvertToString = function (value) {

    var format = Gob.Pe.UI.Web.Formato.Decimal;
    var number = value.toString();

    format = format.replace(Gob.Pe.UI.Web.Formato.DecimalSeparadorDecimal, '@');
    format = format.replace(Gob.Pe.UI.Web.Formato.DecimalSeparadorMiles, ',');
    format = format.replace('@', '.');

    if (typeof decimal == 'string') {
        number = number.replace(Gob.Pe.UI.Web.Formato.DecimalSeparadorDecimal, '@');
        number = number.replace(Gob.Pe.UI.Web.Formato.DecimalSeparadorMiles, '');
        number = number.replace('@', '.');
    }

    number = $.formatNumber(number, { format: format });

    number = number.replace('.', '@');
    number = number.replace(',', Gob.Pe.UI.Web.Formato.DecimalSeparadorMiles);
    number = number.replace('@', Gob.Pe.UI.Web.Formato.DecimalSeparadorDecimal);

    return number;

};

Gob.Pe.UI.Web.Components.Util.ValidateDateRange = function (txtStart, txtEnd) {

    var isValid = true;

    var valueStart = txtStart.is('input') ? txtStart.val() : txtStart.html();
    var valueEnd = txtEnd.is('input') ? txtEnd.val() : txtEnd.html();

    if (valueStart != '' && valueEnd != '') {
        var dateStart = $.datepicker.parseDate(Gob.Pe.UI.Web.Formato.Fecha, valueStart);
        var dateEnd = $.datepicker.parseDate(Gob.Pe.UI.Web.Formato.Fecha, valueEnd);
        isValid = (dateStart <= dateEnd);
    }
    return isValid;
}