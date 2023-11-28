@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header" style="background-color: #003893; color: #fff">Inciar Sesión</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">Ingrese su DNI</label>

                            <div class="col-md-6">
                                <input id="email" type="text" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus >

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">Contraseña</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4"></div>
                            <div class="col-md-6" style="">
                                <select id="roles"  class="form-select" aria-label="Default select example" name="perfil" style="display:none;">
                                </select>
                            </div>
                        </div>                      

                        <div class="row mb-3">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                    <label class="form-check-label" for="remember">
                                        Recuerdame
                                    </label>
                                </div>
                            </div>
                        </div>                        

                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    Ingresar
                                </button>

                                @if (Route::has('password.request'))
                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        Olvidaste tu contraseña?
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
<script src="{{asset('nuevo/assets/js/jquery.min.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@section('script')
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

    </script>
@endsection