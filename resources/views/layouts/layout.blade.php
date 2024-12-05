</html>

<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="Centros MAC" name="description" />
    <meta content="" name="author" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>SISTEMA INTRANET CENTRO MAC - SSCS - PCM</title>

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{asset('favicon.ico')}}">
    <!-- jvectormap -->
    <link href="{{asset('nuevo/plugins/jvectormap/jquery-jvectormap-2.0.2.css')}}" rel="stylesheet">

    <!-- App css -->
    <link href="{{asset('nuevo/assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('nuevo/assets/css/icons.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('nuevo/assets/css/metisMenu.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('nuevo/plugins/daterangepicker/daterangepicker.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('nuevo/assets/css/app.min.css')}}" rel="stylesheet" type="text/css" />

    <!--STYLESHEET-->
    <!--=================================================-->
    <!--Datatable [ DEMONSTRATION ]-->
    <!--<link rel="stylesheet" href="{ asset('Style\css\datatable.min.css') }}">-->
    <!--Font Awesome [ OPTIONAL ]-->
    <!--  Font Awesome 4.7.0 by https://fontawesome.com/v4/icons/ -->
    <link href="{{ asset('Vendor\plugins\font-awesome\css\font-awesome.min.css')}}" rel="stylesheet">
    <!--Bootstrap Datepicker [ OPTIONAL ]-->
    {{-- <script src="{{ asset('Vendor\bootstrap-datepicker\bootstrap-datepicker.min.css')}}"></script> --}}
    <!--Pace - Page Load Progress Par [OPTIONAL]-->
    {{-- <link href="{{ asset('Vendor\plugins\pace\pace.min.css')}}" rel="stylesheet"> --}}
    {{-- <script src="{{ asset('Vendor\plugins\pace\pace.min.js')}}"></script> --}}
    @yield('style')

    <style>
        /*
        body.dark-sidenav .left-sidenav {
            background-color: #5F3195;
        }*/
        table.dataTable td.dataTables_empty, table.dataTable th.dataTables_empty {
            text-align: center;
            color: red;
        }
        .bg-soft-primary {
            background: linear-gradient(#B8F2FC, #16DFFF);
        }
        .nav-logo{max-width: 100%;}
    
        thead{
            background-color: #3656ac!important;
    
        }
    
        tr th{
            color: #fff !important;
            vertical-align: middle !important;
            font-family: "Roboto",sans-serif;
            font-size: 14px;
            line-height: 20px;

        }
    
        .sorting{
            color: #fff !important;
            vertical-align: middle !important;
        }
    
        tr td, td a{
            color: #474747;
            font-family: "Roboto",sans-serif;
            font-size: 13px;
            line-height: 20px;
        }
    
        .form-control{
            border: 1px solid rgb(173, 173, 173);
        }
    
        .panel-inei{
            background: #112762;
            color:#fff;
        }

        .loader {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: fixed;
            text-align: center;
            vertical-align: text-bottom	;                     
            z-index:1;
            height: 100vh;
            width: 100vw;
            /* background-color: #fff; */
            opacity: .9;
        }

        .navbar-custom {
            background: #0C213A;
            min-height: 80px;
        }

        .nobtn{
                background: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 0 !important;
                cursor: pointer;            
                color: blue;
                font-size: 1em;
        }

        .nobtn:hover{
            text-decoration: underline; 
        }

        .hasError{
            border: 1px solid #f00 !important;
        }
    
        .loader {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: fixed;
            z-index: 9999;
            height: 100vh;
            width: 100vw;
            background-color: #fff;
            text-align: center;
        }

        .logo-container {
            position: relative;
            animation: bounce 1.5s infinite ease-in-out; /* Movimiento de la imagen */
        }

        .logo_d {
            width: 50px; /* Ajusta el tamaño de la imagen */
            animation: pulse-colors 2s infinite; /* Parpadeo de colores */
        }

        @keyframes pulse-colors {
            0% { filter: hue-rotate(0deg); }
            25% { filter: hue-rotate(90deg); }
            50% { filter: hue-rotate(180deg); }
            75% { filter: hue-rotate(270deg); }
            100% { filter: hue-rotate(360deg); }
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        .loading-text {
            font-size: 1.5rem;
            font-weight: bold;
            color: #007bff; /* Color azul */
            margin-top: 15px;
            animation: fade-in-out 1.5s infinite;
        }

        @keyframes fade-in-out {
            0%, 100% {
                opacity: 0.3;
            }
            50% {
                opacity: 1;
            }
        }

        .text-uppercase {
            text-transform: uppercase !important;
        }

     </style>

</head>
    <body class="dark-sidenav">
    {{-- <div class="loader">
        <div class="spinner-border spinner-border-custom-4 text-primary" role="status"></div><br />
        <div>Cargando...</div>
    </div> --}}
    <div class="loader">
        <div class="logo-container">
            <img src="{{ asset('imagen/mac-logo.png') }}" alt="Logo" class="logo_d" width="50">
        </div>
        <div class="loading-text">Cargando...</div>
    </div>

    
        @include('secciones.sidebar')
        <div id="app" style="width:100%; height: 100%;">
            <div class="page-wrapper">
                @include('secciones.header')
                <!-- Page Content-->
                <div class="page-content">
                    @yield('main')
                    @include('secciones.footer')
                </div>
                <!-- end page content -->
            </div>
        </div>
        <!-- jQuery  -->
        <script src="{{asset('nuevo/assets/js/jquery.min.js')}}"></script>
        <!-- end page-wrapper -->
        {{-- <script src="{{ asset('js/app.js')}}"></script> --}}
        <script src="{{asset('nuevo/assets/js/bootstrap.bundle.min.js')}}"></script>
        <script src="{{asset('nuevo/assets/js/metismenu.min.js')}}"></script>
        <script src="{{asset('nuevo/assets/js/waves.js')}}"></script>
        <script src="{{asset('nuevo/assets/js/feather.min.js')}}"></script>
        <script src="{{asset('nuevo/assets/js/simplebar.min.js')}}"></script>
        <script src="{{asset('nuevo/assets/js/moment.js')}}"></script>
        <script src="{{asset('nuevo/plugins/daterangepicker/daterangepicker.js')}}"></script>

        <!-- App js -->
        <script src="{{asset('nuevo/assets/js/app.js')}}"></script>
      

        {{-- CARGA IMAGEN ANTES DE CARGAR PAGINA --}}
        <script>
         $(document).ready(function(){
            mayuscular();
           $(".loader").fadeOut();
             $(window).resize(function(){    
                 if (window.matchMedia("(max-width: 700px)").matches) {
                     $('#divicnav1').addClass('col-3');
                     $('#divicnav2').addClass('col-6');
                     $('#divicnav3').addClass('col-3');

                     $('#divicnav1').removeClass('col-1');
                     $('#divicnav2').removeClass('col-10');
                     $('#divicnav3').removeClass('col-1');
                 } else {
                     $('#divicnav1').addClass('col-1');
                     $('#divicnav2').addClass('col-10');
                     $('#divicnav3').addClass('col-1');

                     $('#divicnav1').removeClass('col-3');
                     $('#divicnav2').removeClass('col-6');
                     $('#divicnav3').removeClass('col-3');
                 }
             }).resize()// trigger on page load
              
         });

         function mayuscular(){
            const nombresColumnIndex = 1; // Índice de la columna "NOMBRES" (comienza en 0)
            const centrosMacColumnIndex = 2; // Índice de la columna "CENTRO MAC"

            // Recorrer todas las filas del tbody
            $('.table_asistencia').each(function () {
                // Transformar NOMBRES a mayúsculas
                const nombresCell = $(this).find('td').eq(nombresColumnIndex);
                if (nombresCell.length) {
                    nombresCell.text(nombresCell.text().toUpperCase());
                }

                // Transformar CENTRO MAC a mayúsculas
                const centrosMacCell = $(this).find('td').eq(centrosMacColumnIndex);
                if (centrosMacCell.length) {
                    centrosMacCell.text(centrosMacCell.text().toUpperCase());
                }
            });
         }
        </script>

        <script>

        function ajaxRequest(url, type, data, successFunction){
                $.ajax({
                    url: url,
                    method: type,
                    data: data,
                    success: successFunction
                });

                $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

            }
        </script>

        <script>
            // Esperar 8 segundos y recargar solo si la página no se ha cargado completamente
            var cargaCompleta = false;

            setTimeout(function() {
                cargaCompleta = true;
            }, 8000);

            window.addEventListener('load', function() {
                cargaCompleta = true;
            });

            // Verificar si la carga está completa después de 8 segundos y recargar si es necesario
            setTimeout(function() {
                if (!cargaCompleta) {
                    window.location.reload();
                }
            }, 8000);
        </script>

        <!--JAVASCRIPT-->
        <!--=================================================-->
        <!--Tooltip [ RECOMENDADO ]-->
        {{-- <script src="{{ asset('Script/tippyjs/popper.min.js')}}"></script>
        <script src="{{ asset('Script/tippyjs/tippy-bundle.umd.js')}}"></script> --}}

        <!--Datatable [ RECOMENDADO ]-->
        <!--
        <script src="{ asset('Script/datatables/pdfmake.min.js') }}"></script>
        <script src="{ asset('Script/datatables/vfs_fonts.js') }}"></script>
        <script src="{ asset('Script/datatables/datatables.min.js') }}"></script>
        -->
        <!--Bootstrap Datepicker [ OPTIONAL ]-->
        <script src="{{ asset('Vendor\bootstrap-datepicker\bootstrap-datepicker.min.js')}}"></script>
        <!-- Development -->
        {{-- <script src="https://unpkg.com/@popperjs/core@2/dist/umd/popper.min.js"></script>
        <script src="https://unpkg.com/tippy.js@6/dist/tippy-bundle.umd.js"></script> --}}
        {{-- <script src="https://unpkg.com/popper.js@1"></script>
        <script src="https://unpkg.com/tippy.js@5"></script> --}}
        <script src="{{ asset('js\tipify5\popper.js')}}"></script>
        <script src="{{ asset('js\tipify5\tippy.js')}}"></script>

        <!-- CODIGO -->
        @yield('script')
        


        
    </body>
</html>
