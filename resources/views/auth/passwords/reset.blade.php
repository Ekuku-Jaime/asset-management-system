@extends('layouts.guest')

@section('title', 'Redefinir Palavra-passe')
@section('icon', 'fa-key')
@section('subtitle', 'Defina uma nova palavra-passe para a sua conta')

@section('content')
<form method="POST" action="{{ route('password.update') }}" class="needs-validation" novalidate>
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    <div class="mb-4">
        <label for="email" class="form-label">
            <i class="fas fa-envelope me-2"></i>Endereço de Email
        </label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-user"></i>
            </span>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                   name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus
                   placeholder="exemplo@empresa.com">
            @error('email')
                <div class="invalid-feedback">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>

    <div class="mb-4">
        <label for="password" class="form-label">
            <i class="fas fa-lock me-2"></i>Nova Palavra-passe
        </label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-key"></i>
            </span>
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                   name="password" required autocomplete="new-password" minlength="8"
                   placeholder="Introduza a nova palavra-passe">
            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                <i class="fas fa-eye"></i>
            </button>
            @error('password')
                <div class="invalid-feedback">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    {{ $message }}
                </div>
            @enderror
        </div>
        <div class="form-text mt-1">
            <small>Palavra-passe deve ter pelo menos 8 caracteres</small>
        </div>
    </div>

    <div class="mb-4">
        <label for="password-confirm" class="form-label">
            <i class="fas fa-lock me-2"></i>Confirmar Palavra-passe
        </label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-key"></i>
            </span>
            <input id="password-confirm" type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
                   name="password_confirmation" required autocomplete="new-password" minlength="8"
                   placeholder="Confirme a nova palavra-passe">
            <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                <i class="fas fa-eye"></i>
            </button>
            @error('password_confirmation')
                <div class="invalid-feedback">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>

    <div class="d-grid gap-2 mb-4">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-key me-2"></i>
            Redefinir Palavra-passe
        </button>
    </div>

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
        function togglePasswordField(buttonId, inputId) {
            $(buttonId).click(function() {
                const passwordInput = $(inputId);
                const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
                passwordInput.attr('type', type);
                $(this).find('i').toggleClass('fa-eye fa-eye-slash');
            });
        }

        togglePasswordField('#togglePassword', '#password');
        togglePasswordField('#togglePasswordConfirm', '#password-confirm');

        // Form validation
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