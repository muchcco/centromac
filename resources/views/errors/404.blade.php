<div class="container text-center">
    <h1 class="display-1">404</h1>
    <p class="lead">Lo sentimos, la página que estás buscando no se pudo encontrar.</p>
    @if(!empty($errorMessage))
        <p class="text-muted">Error: {{ $errorMessage }}</p>
    @endif
    <a href="{{ url('/') }}" class="btn btn-primary">Volver a la página principal</a>
</div>
