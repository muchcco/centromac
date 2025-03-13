// Mostrar fecha actual
var fecha = new Date(); 
var mes = fecha.getMonth() + 1; 
var dia = fecha.getDate(); 
var anio = fecha.getFullYear();
if (dia < 10)
    dia = '0' + dia;
if (mes < 10)
    mes = '0' + mes
//document.getElementById('fechaActual').value = dia + "-" + mes + "-" + anio;
$('#fechaReclamo').text(dia + "/" + mes + "/" + anio);

// Mostrar otra sede
function mostrarSede(select) {
    if (select.value == "Otra Sede") {
        divSede = document.getElementById("divOtraSede");
        divSede.style.display = "block";
    } else {
        divSede = document.getElementById("divOtraSede");
        divSede.style.display = "none";
    }
}

// Mostrar motivo consulta
function mostrarSelect(select, idElemento) {
    if (select.value == 'Otro') {
        divSede = document.getElementById(idElemento);
        divSede.style.display = "block";
    } else {
        divSede = document.getElementById(idElemento);
        divSede.style.display = "none";
    }
}

function mostrarOcultarElemento(estado, elemento) {
    if (estado == 'true') {
        document.getElementById(elemento).style.display = 'block';
    } else {
        document.getElementById(elemento).style.display = 'none';
    }
}

// + info collapse button
$('#more').click(function () {
    if ($('button span').hasClass('glyphicon-chevron-down')) {
        $('#more').html('<span class="glyphicon glyphicon-chevron-up"></span> Less Info');
    }
    else {
        $('#more').html('<span class="glyphicon glyphicon-chevron-down"></span> More Info');
    }
}); 

//Tabs Menu
$(function () {
    var $a = $(".tabs li");
    $a.click(function () {
        $a.removeClass("active");
        $(this).addClass("active");
    });
});

// Cargar modales anidados
$(document).ready(function () {

    $('#openBtn').click(function () {
        $('#myModal').modal({
            show: true
        })
    });

    $(document).on('show.bs.modal', '.modal', function (event) {
        var zIndex = 1040 + (10 * $('.modal:visible').length);
        $(this).css('z-index', zIndex);
        setTimeout(function () {
            $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
        }, 0);
    });
});

//Cargar modal cambiar clave
$(window).on('load', function () {
    $('#myModalClave').modal('show');
});

// Botones Prev Next Form Wizard
$(document).ready(function () {
    $('.btnNext').click(function () {
        $('.nav-tabs .active').parent().next('li').find('a').trigger('click');
    });

    $('.btnPrevious').click(function () {
        $('.nav-tabs .active').parent().prev('li').find('a').trigger('click');
    });
});

// Tooltips
$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();
});

