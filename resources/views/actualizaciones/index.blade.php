@extends('layouts.layout')

@section('main')
    <div class="card">
        <div class="card-header" style="background-color:#132842;">
            <h4 class="card-title text-white mb-0">
                <i data-feather="git-commit"></i> Cambios recientes del sistema (GitHub)
            </h4>
        </div>
        <div class="card-body">
            @if (count($commits) > 0)
                <div class="list-group">
                    @foreach ($commits as $commit)
                        <a href="{{ $commit['html_url'] }}" target="_blank" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">{{ Str::limit($commit['commit']['message'], 80) }}</h5>
                                <small>{{ \Carbon\Carbon::parse($commit['commit']['author']['date'])->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1 text-muted">
                                <strong>Autor:</strong> {{ $commit['commit']['author']['name'] }}
                            </p>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="alert alert-warning text-center mb-0">
                    <i data-feather="alert-circle"></i> No se pudieron obtener los commits recientes del repositorio.
                </div>
            @endif
        </div>
    </div>
@endsection
