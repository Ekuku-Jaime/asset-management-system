@extends('layouts.guest')

@section('title', 'Recuperar Palavra-passe')
@section('icon', 'fa-unlock-alt')
@section('subtitle', 'Enviamos-lhe um link para redefinir a sua palavra-passe')

@section('content')
@if (session('status'))
    <div class="alert alert-success" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('status') }}
    </div>
@endif

<form method="POST" action="{{ route('password.email') }}" class="needs-validation" novalidate>
    @csrf

    <div class="mb-4">
        <label for="email" class="form-label">
            <i class="fas fa-envelope me-2"></i>Endereço de Email
        </label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-user"></i>
            </span>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                   name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                   placeholder="exemplo@empresa.com">
            @error('email')
                <div class="invalid-feedback">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    {{ $message }}
                </div>
            @enderror
        </div>
        <div class="form-text mt-1">
            <small>Introduza o email associado à sua conta</small>
        </div>
    </div>

    <div class="d-grid gap-2 mb-4">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-paper-plane me-2"></i>
            Enviar Link de Recuperação
        </button>
    </div>

    @if (Route::has('login'))
    <div class="text-center mb-3">
        <a class="text-decoration-none" href="{{ route('login') }}">
            <i class="fas fa-arrow-left me-1"></i>
            Voltar ao início de sessão
        </a>
    </div>
    @endif

    <div class="text-center pt-3 border-top">
        <p class="text-muted mb-0">
            <small>
                <i class="fas fa-shield-alt me-1"></i>
                Conexão segura SSL/TLS encriptada
            </small>
        </p>
    </div>
</form>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('form.needs-validation').on('submit', function(event) {
            if (!this.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            $(this).addClass('was-validated');
        });
    });
</script>
@endpush