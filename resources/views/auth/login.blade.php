@extends('layouts.guest')

@section('title', 'Login')
@section('icon', 'fa-sign-in-alt')
@section('subtitle', 'Aceda à sua conta')

@section('content')
<form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate>
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

    <div class="mb-4">
        <label for="password" class="form-label">
            <i class="fas fa-lock me-2"></i>Palavra-passe
        </label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-key"></i>
            </span>
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                   name="password" required autocomplete="current-password"
                   placeholder="Introduza a sua palavra-passe">
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
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
            <label class="form-check-label" for="remember">
                <i class="fas fa-check-circle me-1"></i>
                Manter sessão iniciada
            </label>
        </div>
    </div>

    <div class="d-grid gap-2 mb-4">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-sign-in-alt me-2"></i>
            Iniciar Sessão
        </button>
    </div>

    @if (Route::has('password.request'))
    <div class="text-center mb-3">
        <a class="text-decoration-none" href="{{ route('password.request') }}">
            <i class="fas fa-key me-1"></i>
            Esqueceu-se da palavra-passe?
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
        // Toggle password visibility
        $('#togglePassword').click(function() {
            const passwordInput = $('#password');
            const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
            passwordInput.attr('type', type);
            
            // Toggle eye icon
            $(this).find('i').toggleClass('fa-eye fa-eye-slash');
        });
        
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