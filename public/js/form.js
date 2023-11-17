
function MostrarMensaje(titulo, mensaje, tipo) {
    var $toast = toastr[tipo](mensaje, titulo);
}

var TPersona = (val) =>{

  console.log(val);

  if(val == '1' ){
    $('#divRuc').hide();
  }else if(val == '2'){
    $('#divRuc').show();
  }

}

var TipoDocumento = (val) => {

  console.log(val);

  if(val == '1'){
    $("#sMaterno").text("(*)");
    $('#n_documento').attr("maxlength", "8");
  }else if(val == '2'){
    $("#sMaterno").text("");
    $('#n_documento').val("");
    $('#n_documento').attr("maxlength", "10");
  }else if(val == '3'){
    $("#sMaterno").text("");
    $('#n_documento').attr("maxlength", "10");
  }

}

var Mayuscula = (e) => {
     e.value = e.value.toUpperCase();    
}

var isNumber = (evt) =>{
  evt = (evt) ? evt : window.event;
  var charCode = (evt.which) ? evt.which : evt.keyCode;
  if (charCode > 31 && (charCode < 48 || charCode > 57)) {
      return false;
  }
  return true;
}

var otros_tvp = () => {

    if(document.querySelector(".dato_otros").checked == true){
        document.getElementById('tvl_otros_esp').style.display = '';
    }else{
        document.getElementById('tvl_otros_esp').style.display = 'none';
    }

}

var otros_gi = () => {

if(document.querySelector(".otros_gi").checked == true){
    document.getElementById('gi_otro_compl').style.display = '';
}else{
    document.getElementById('gi_otro_compl').style.display = 'none';
}

}