/*

Author - Rudolf Naprstek
Website - http://www.thimbleopensource.com/tutorials-snippets/jquery-plugin-filter-text-input
Version - 1.5.3
Release - 12th February 2014

Thanks to Niko Halink from ARGH!media for bugfix!
 
Remy Blom: Added a callback function when the filter surpresses a keypress in order to give user feedback

Don Myers: Added extension for using predefined filter masks

Richard Eddy: Added extension for using negative number
  

*/

(function ($) {

    $.fn.extend({

        filter_input: function (options) {

            var defaults = {
                regex: ".",
                negkey: false, // use "-" if you want to allow minus sign at the beginning of the string
                live: false,
                events: 'keypress paste'
            }

            var options = $.extend(defaults, options);

            function filter_input_function(event) {

                var input = (event.input) ? event.input : $(this);
                if (event.ctrlKey || event.altKey) return;
                if (event.type == 'keypress') {

                    var key = event.charCode ? event.charCode : event.keyCode ? event.keyCode : 0;

                    // 8 = backspace, 9 = tab, 13 = enter, 35 = end, 36 = home, 37 = left, 39 = right, 46 = delete
                    if (key == 8 || key == 9 || key == 13 || key == 35 || key == 36 || key == 37 || key == 39 || key == 46) {

                        // if charCode = key & keyCode = 0
                        // 35 = #, 36 = $, 37 = %, 39 = ', 46 = .

                        if (event.charCode == 0 && event.keyCode == key) {
                            return true;
                        }
                    }
                    var string = String.fromCharCode(key);
                    // if they pressed the defined negative key
                    if (options.negkey && string == options.negkey) {
                        // if there is already one at the beginning, remove it
                        if (input.val().substr(0, 1) == string) {
                            input.val(input.val().substring(1, input.val().length)).change();
                        } else {
                            // it isn't there so add it to the beginning of the string
                            input.val(string + input.val()).change();
                        }
                        return false;
                    }
                    var regex = new RegExp(options.regex);
                } else if (event.type == 'paste') {
                    input.data('value_before_paste', event.target.value);
                    setTimeout(function () {
                        filter_input_function({ type: 'after_paste', input: input });
                    }, 1);
                    return true;
                } else if (event.type == 'after_paste') {
                    var string = input.val();
                    var regex = new RegExp('^(' + options.regex + ')+$');
                } else {
                    return false;
                }

                if (regex.test(string)) {
                    return true;
                } else if (typeof (options.feedback) == 'function') {
                    options.feedback.call(this, string);
                }
                if (event.type == 'after_paste') input.val(input.data('value_before_paste'));
                return false;
            }

            var jquery_version = $.fn.jquery.split('.');
            if (options.live) {
                if (parseInt(jquery_version[0]) >= 1 && parseInt(jquery_version[1]) >= 7) {
                    $(this).on(options.events, filter_input_function);
                } else {
                    $(this).live(options.events, filter_input_function);
                }
            } else {
                return this.each(function () {
                    var input = $(this);
                    if (parseInt(jquery_version[0]) >= 1 && parseInt(jquery_version[1]) >= 7) {
                        input.off(options.events).on(options.events, filter_input_function);
                    } else {
                        input.unbind(options.events).bind(options.events, filter_input_function);
                    }
                });
            }

        }
    });
    /***************************    Metodos agragados    **************************/
    $.fn.extend({
        filter_input_fecha: function () {
            var input = $(this);
            $(input).each(function (i, row) {
                $(input[i]).attr("title", "Ingrese un valor con el formato dia/mes/año (dd/mm/yyyy). Ej: 01/12/2014")
                //.tooltip({ position: { my: "right bottom+10", at: "left bottom"} }) //para Jquery UI
                    .filter_input({ regex: '[0-9/]' });
                //.attr('data-placement','right') //para bootstrap
                //.tooltip();
            });
            return input;
        },
        filter_input_hora: function () {
            var input = $(this);
            $(input).each(function (i, row) {
                $(input[i]).attr("title", "Ingrese un valor con el formato hora:minuto (HH:mm, desde 00:00 hasta 23:59). Ej: 16:40")
                //.tooltip({ position: { my: "right bottom+10", at: "left bottom"} }) //para Jquery UI
                    .filter_input({ regex: '[0-9:]' });
                //.attr('data-placement','right') //para bootstrap
                //.tooltip();
            });
            return input;
        },
        filter_input_alfanumericos: function () {
            var input = $(this);
            $(input).each(function (i, row) {
                $(input[i]).filter_input({ regex: '[a-zA-Z\u00f1\u00d1\u00e1\u00e9\u00ed\u00f3\u00fa\u00c1\u00c9\u00cd\u00d3\u00da\u00c7\u00e7\u00C4\u00E4\u00D6\u00F6\u00dc\u00fc\0-9.# \-/]' });
            });
            return input;
        },
        filter_input_porcentaje: function () {
            var input = $(this);
            $(input).each(function (i, row) {
                $(input[i]).filter_input({ regex: '[0-9.]' });
            });
            return input;
        },
        filter_input_moneda: function () {
            var input = $(this);
            $(input).each(function (i, row) {
                $(input[i]).filter_input({ regex: '[0-9.,]' });
            });
            return input;
        },
        filter_input_solonumeros: function () {
            var input = $(this);
            $(input).each(function (i, row) {
                $(input[i]).filter_input({ regex: '[0-9]' });
            });
            return input;
        },
        filter_input_email: function () {
            var input = $(this);
            $(input).each(function (i, row) {
                $(input[i]).attr("title", "Solo se permite el ingreso de los siguientes caracteres: Letras desde A hasta Z (mayusculas y minusculas), Numeros y @ _ - .  Ej: labc123@abc.com")
                    .filter_input({ regex: '[A-Za-z0-9_@.\-]' });
                //.tooltip({ position: { my: "left top+10", at: "left bottom"} }) //para Jquery UI
                //.attr('data-placement','top') //para bootstrap
                //.tooltip();    
            });
            return input;
        },
        filter_input_movil: function () {
            var input = $(this);
            $(input).each(function (i, row) {
                $(input[i]).attr("title", "Solo se permite el ingreso de los siguientes caracteres: Numeros y # * - .  Ej: 999999999, 999-999-999, 999-9999")
                    .filter_input({ regex: '[0-9#*\-]' });
                //.tooltip({ position: { my: "left top+10", at: "left bottom"} }) //para Jquery UI
                //.attr('data-placement', 'top') //para bootstrap
                //.tooltip();
            });
            return input;
        },
        filter_input_sololetras: function () {
            var input = $(this);
            $(input).each(function (i, row) {
                $(input[i]).filter_input({ regex: '[a-zA-Z\u00f1\u00d1\u00e1\u00e9\u00ed\u00f3\u00fa\u00c1\u00c9\u00cd\u00d3\u00da\u00c7\u00e7\u00C4\u00E4\u00D6\u00F6\u00dc\u00fc ]' });
            });
            return input;
        },
        filter_input_direccion: function () {
            var input = $(this);
            $(input).each(function (i, row) {
                $(input[i]).attr("title", "Solo se permite el ingreso de los siguientes caracteres: Letras desde A hasta Z (mayusculas, minusculas y diéresis), Numeros y # - /.  Ej: Av. Buenos Aires 165 s/n AA.HH. Mz. Lt. Int 5 ")
                    .filter_input({ regex: '[a-zA-Z\u00f1\u00d1\u00e1\u00e9\u00ed\u00f3\u00fa\u00c1\u00c9\u00cd\u00d3\u00da\u00c7\u00e7\u00C4\u00E4\u00D6\u00F6\u00dc\u00fc\0-9.# \-/]' });
                //.tooltip({ position: { my: "left top+10", at: "left bottom"} })
                //.attr('data-placement', 'top') //para bootstrap
                //.tooltip();

            });
            return input;
        }
    });
    /***************************   Fin Metodos agragados  **************************/
})(jQuery);    /// <reference path="../Infraestructura/Validacion.vb" />
