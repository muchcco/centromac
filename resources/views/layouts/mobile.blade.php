<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>División Curva en el Body</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="{{ asset('https://use.fontawesome.com/releases/v5.15.3/css/all.css')}}"  integrity="sha384-SZXxX4whJ79/gErwcOYf+zWLeJdY/qpuqC4cAa9rOGUstPomtqpuNWT9wdPEn2fk" crossorigin="anonymous">
    <style>
        body {
            margin: 0;
            height: 100vh;
            background: linear-gradient(to top, #fff 70%, #003893 30%);
            position: relative;
            /* overflow: hidden; */
        }

        .head{
            /* border: 1px solid red; */
            display: flex;
            flex-direction: row;
            justify-content: space-between;
        }

        .t-loggin{
            /* border: 1px solid blue; */
            display: flex;
            justify-content: end;
            align-items: center;
            align-self: center;
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
       
    </style>
    @yield('estilo')
</head>
<body>
    @yield('main')

    

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js')}}" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

    @yield('script')
</body>
</html>
