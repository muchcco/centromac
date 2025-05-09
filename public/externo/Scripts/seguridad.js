﻿
// timeout before a callback is called

let timeout;

// traversing the DOM and getting the input and span using their IDs

let password = document.getElementById('txtcontrasenaNueva')
let strengthBadge = document.getElementById('StrengthDisp')

// The strong and weak password Regex pattern checker

let strongPassword = new RegExp('(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9])(?=.{8,})')
let mediumPassword = new RegExp('((?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9])(?=.{6,}))|((?=.*[a-z])(?=.*[A-Z])(?=.*[^A-Za-z0-9])(?=.{8,}))')

function StrengthChecker(PasswordParameter) {
    // We then change the badge's color and text based on the password strength

    if (strongPassword.test(PasswordParameter)) {
        strengthBadge.style.backgroundColor = "green"
        strengthBadge.textContent = 'Constraseña Fuerte'
    } else if (mediumPassword.test(PasswordParameter)) {
        strengthBadge.style.backgroundColor = 'blue'
        strengthBadge.textContent = 'Contraseña Media'
    } else {
        strengthBadge.style.backgroundColor = 'red'
        strengthBadge.textContent = 'Contraseña Debil'
    }
}

// Adding an input event listener when a user types to the  password input 

password.addEventListener("input", function (evt)  {

    //The badge is hidden by default, so we show it

    strengthBadge.style.display = 'block'
    clearTimeout(timeout);

    //We then call the StrengChecker function as a callback then pass the typed password to it

    timeout = setTimeout(() => StrengthChecker(password.value), 500);

    //Incase a user clears the text, the badge is hidden again

    if (password.value.length !== 0) {
        strengthBadge.style.display != 'block'
    } else {
        strengthBadge.style.display = 'none'
    }
});

function VerClave() {
    var tipo = document.getElementById("txtClaveNueva");
    if (tipo.type == "password") {
        tipo.type = "text";
    } else {
        tipo.type = "password";
    }
}
function VerClave2() {
    var tipo = document.getElementById("txtClaveRepita");
    if (tipo.type == "password") {
        tipo.type = "text";
    } else {
        tipo.type = "password";
    }
}
