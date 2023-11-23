<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Correo</title>

    <link href="{{ asset('https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css')}}" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    
    
    <style>
        header{
            background: #BF0909;
        }

        h2{
            font-family: "Trebuchet MS",Helvetica,sans-serif;
        }

        .container{
            padding:1em;
            max-width: 90%;
        }

        .cab{
            display: flex !important;
            width: 100%;
            align-items: center;

        }

        .col{
            display: flex !important;
            justify-content: center !important;
        }

        .title{
            color:#fff;

        }

        #main{
            width: 100%;
        }

        .carp{
            display: flex;
            justify-content: center;
        }

        .form-p{
            width: 90%;
            display: flex;
            
        }

        .buut{
            margin-left: .2em;
            margin-right: .2em;
        }
        .password{
            color: red;
            background: red;
        }
        .texto-footer{
            text-size-adjust: .8em;
            color: #313ef7a1;
        }
    </style>

</head>
<body>

<header>
    <center style="color:#fff;"><h1>Envio de Correo</h1></center>
</header>
<br />
<main>
    <section>
        <div class="row">
            <p>Estimado(a)  {{ $personal->NOMBRE }} se remite el siguiente correo con sus credenciales para el ingreso del aplicativo intranet del Centro Mac.</p>
        </div>        
    </section>

    <section>
        <div class="card">
            <div class="card-header">
                <h4>Datos de Sesión:</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        Usuario: {{ $personal->NUM_DOC }}
                     </div>
                     <div class="col-12">
                        Contraseña:  <span class="password"> {{ $personal->NUM_DOC }} </span>
                     </div>
                </div>
            </div>
        </div>
        <br />
        <br />
    </section>
</main>

<p class="texto-footer">Especialista TIC - Centro MAC<br />
Subsecretaría de Calidad de Servicios<br />
Secretaría de Gestión Pública<br />
Presidencia del Consejo de Ministros<br />
Celular: 986184484</p><br />
<img src="{{ asset('imagen/logo-pcm.png') }}" alt="PCM">

<footer>
    <div class="row">
        <center>2023 © pcm - centros mac</center>
    </div> 
</footer>




<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js')}}" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    
</body>
</html>