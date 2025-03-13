$(document).ready(function () {
    //if ($('#hCountIntentos').val() > 2) {
    //    $('#divCaptcha').show();
    //}
    //else {
    //    $('#divCaptcha').hide();
    //}
    RefrescarImagenCaptcha();

    $("#lnkRefrescarCaptcha").click(function () {
        RefrescarImagenCaptcha();
    });
});



function RefrescarImagenCaptcha() {
   
    $.ajax({
        type: "POST",
        url: myPath,
        contentType: "application/json; charset=utf-8",
        success: function (result) {
            $('#imgCaptcha').attr("src", "data:image/png;base64," + result);
        },
        error: function (err) {
            // alert(err.status + " - " + err.statusText);
        }
    });
}