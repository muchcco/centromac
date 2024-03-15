<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intranet CENTROS MAC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <script>
        function getRandomImage() {
            var images = [
                'anden.jpg',
                'arequipa.jpg',
                'lima-barranco.jpg',
                // Agrega aquí el nombre de todas tus imágenes en la carpeta imagen/auth
            ];
            var randomIndex = Math.floor(Math.random() * images.length);
            return images[randomIndex];
        }
        
        var randomImageUrl = '{{ asset('imagen/auth/') }}/' + getRandomImage();
        document.documentElement.style.setProperty('--random-image', 'url(' + randomImageUrl + ')');
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    <style>
        #bod_comp {
            position: relative;
            width: 100%;
            height: 100vh;
            overflow: hidden;
        }

        #bod_comp::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-image: var(--random-image);
            z-index: -1;            
        }


        #body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: flex-end; /* Alinea el contenido al extremo derecho */
            height: 100vh;
            width: 100%;            
            position: relative; /* Para que el contenido se posicione correctamente */
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }

        #dat_comp{
            display: flex;
            flex-direction:row;
            justify-content: space-between;
            width: 100%;
        }

        .text-inf{
            margin-left: 50px;
            color:#fff;
            position: relative; /* Para que el contenido se posicione correctamente */
            z-index: 1;
        }

        .login-container {
            background-color: rgba(255, 255, 255, 0.9);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            text-align: center;
            overflow: hidden;
            animation: slide-up 0.5s ease;
            margin-right: 50px; /* Ajusta el margen derecho según sea necesario */
        }

        @keyframes slide-up {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .login-container h1 {
            color: #333;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #666;
            text-align: left;
        }

        .form-group input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 3px;
            margin-top: 5px;
        }

        .form-group button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 3px;
            transition: background-color 0.3s ease;
        }

        .form-group button:hover {
            background-color: #0056b3;
        }

        .error-message {
            color: #e74c3c;
            margin-top: 10px;
        }

        .img-login{            
            border-bottom: 1px solid rgb(70, 70, 70);
            padding: 2em;
            margin-bottom: 2em;
            display: flex;
        }

        .text-icon{
            font-size: 12px;
        }

        .text-mac{
            font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
        }
    </style>
</head>
<body id="bod_comp">  
     <header>
         
     </header>

    <div id="body">
        <div class="cont-2"id="dat_comp">
            <div class="text-inf" >
                <span class="texto-informativo">
                    <h2>Sistema Intranet para los centros MAC</h2>
                    <p>Algunas sugerencias antes de ingresar!</p>
                    <p id="text-mac">Mac Rapidos, Mac Eficientes, Mac fácil ...</p>
                    <p>Se recomienda utilizar los siguientes navegadores</p>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" height="30" width="30" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path fill="#fff" d="M0 256C0 209.4 12.5 165.6 34.3 127.1L144.1 318.3C166 357.5 207.9 384 256 384C270.3 384 283.1 381.7 296.8 377.4L220.5 509.6C95.9 492.3 0 385.3 0 256zM365.1 321.6C377.4 302.4 384 279.1 384 256C384 217.8 367.2 183.5 340.7 160H493.4C505.4 189.6 512 222.1 512 256C512 397.4 397.4 511.1 256 512L365.1 321.6zM477.8 128H256C193.1 128 142.3 172.1 130.5 230.7L54.2 98.5C101 38.5 174 0 256 0C350.8 0 433.5 51.5 477.8 128V128zM168 256C168 207.4 207.4 168 256 168C304.6 168 344 207.4 344 256C344 304.6 304.6 344 256 344C207.4 344 168 304.6 168 256z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" height="30" width="30" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path fill="#fff" d="M130.2 127.5C130.4 127.6 130.3 127.6 130.2 127.5V127.5zM481.6 172.9C471 147.4 449.6 119.9 432.7 111.2C446.4 138.1 454.4 165 457.4 185.2C457.4 185.3 457.4 185.4 457.5 185.6C429.9 116.8 383.1 89.1 344.9 28.7C329.9 5.1 334 3.5 331.8 4.1L331.7 4.2C285 30.1 256.4 82.5 249.1 126.9C232.5 127.8 216.2 131.9 201.2 139C199.8 139.6 198.7 140.7 198.1 142C197.4 143.4 197.2 144.9 197.5 146.3C197.7 147.2 198.1 148 198.6 148.6C199.1 149.3 199.8 149.9 200.5 150.3C201.3 150.7 202.1 151 203 151.1C203.8 151.1 204.7 151 205.5 150.8L206 150.6C221.5 143.3 238.4 139.4 255.5 139.2C318.4 138.7 352.7 183.3 363.2 201.5C350.2 192.4 326.8 183.3 304.3 187.2C392.1 231.1 368.5 381.8 247 376.4C187.5 373.8 149.9 325.5 146.4 285.6C146.4 285.6 157.7 243.7 227 243.7C234.5 243.7 256 222.8 256.4 216.7C256.3 214.7 213.8 197.8 197.3 181.5C188.4 172.8 184.2 168.6 180.5 165.5C178.5 163.8 176.4 162.2 174.2 160.7C168.6 141.2 168.4 120.6 173.5 101.1C148.5 112.5 129 130.5 114.8 146.4H114.7C105 134.2 105.7 93.8 106.3 85.3C106.1 84.8 99 89 98.1 89.7C89.5 95.7 81.6 102.6 74.3 110.1C58 126.7 30.1 160.2 18.8 211.3C14.2 231.7 12 255.7 12 263.6C12 398.3 121.2 507.5 255.9 507.5C376.6 507.5 478.9 420.3 496.4 304.9C507.9 228.2 481.6 173.8 481.6 172.9z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" height="30" width="30" viewBox="0 0 496 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path fill="#fff" d="M313.9 32.7c-170.2 0-252.6 223.8-147.5 355.1 36.5 45.4 88.6 75.6 147.5 75.6 36.3 0 70.3-11.1 99.4-30.4-43.8 39.2-101.9 63-165.3 63-3.9 0-8 0-11.9-.3C104.6 489.6 0 381.1 0 248 0 111 111 0 248 0h.8c63.1 .3 120.7 24.1 164.4 63.1-29-19.4-63.1-30.4-99.3-30.4zm101.8 397.7c-40.9 24.7-90.7 23.6-132-5.8 56.2-20.5 97.7-91.6 97.7-176.6 0-84.7-41.2-155.8-97.4-176.6 41.8-29.2 91.2-30.3 132.9-5 105.9 98.7 105.5 265.7-1.2 364z"/></svg>

                    </div>
                </span>
            </div>
            <div class="login-container">
                <div class="img-login">
                     <img src="https://upload.wikimedia.org/wikipedia/commons/a/a7/PCM-PCM.png" width="230"> 
                    {{-- <img src="https://pbs.twimg.com/profile_images/1686603105033486336/nqiWn95n_200x200.jpg" width="100" > --}}
                </div>
                <form id="loginForm" class="login-form" method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="form-group">
                        <label for="username">Número de Documento:</label>
                        <input id="email" type="text" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="off" autofocus>
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña:</label>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <div class="col-md-12"></div>
                        <div class="col-md-12" style="">
                            <select id="roles"  class="form-select" aria-label="Default select example" name="perfil" style="display:none;">
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit">Iniciar sesión</button>
                    </div>
                    <div class="error-message" id="errorMessage"></div>
                </form>
            </div>
        </div>    
    </div>

<script src="{{asset('nuevo/assets/js/jquery.min.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function(){
        const input = document.getElementById("email");
            input.addEventListener("change", function(){
        removeOptions(document.getElementById('roles'));
            const email = document.getElementById("email").value;
            select = document.getElementById("roles");
            select.style.display = 'none';
            $.ajax({
                type:'get',
                url: "{{ route('login_verificacion.get') }}",
                dataType: "json",
                data:{"_token": "{{ csrf_token() }}", email : email },
                success:function(data){
                        select = document.getElementById("roles");
                        data.forEach(element => {
                            option = document.createElement("option");
                            option.value = element.role_id;
                            option.text = element.name;
                            select.appendChild(option);
                        });
                        select.style.display = 'block';
                        $("#boton").prop('disabled', false);
                        $("#texto-ingresar").css('color','#fff');
                },
                error: function( jqXHR, textStatus, errorThrown ) {
                    Swal.fire(
                    'Usuario Incorrecto!',
                    'Ingrese correctamente el usuario!',
                    'error'
                    );
                    $("#boton").prop('disabled', true);
                }
            });
        });
    });
    function removeOptions(selectElement) {
    var i, L = selectElement.options.length - 1;
    for(i = L; i >= 0; i--) {
        selectElement.remove(i);
    }
    }

    // Función para recargar la página
    function reloadPage() {
        location.reload();
    }

    var intervalo = 20 * 60 * 1000;

    setInterval(reloadPage, intervalo);



    function animateText() {
        var text = document.getElementById('text-mac');
        var letters = text.textContent.split('');
        text.textContent = '';
        letters.forEach(function(letter, i) {
            setTimeout(function() {
                text.textContent += letter;
                text.style.color = 'white'; 
            }, 100 * i);
        });
    }
    
    animateText();

</script>
</body>
</html>
