@extends('layouts.guest')

@section('title', 'Ativar Conta')

@php
    $subtitle = 'Complete o seu registo';
@endphp

@section('content')
@if ($user)
    <div class="text-center mb-4">
        <div class="avatar avatar-xl mb-3">
            <div class="avatar-initial bg-primary rounded-circle text-white" style="width: 80px; height: 80px; line-height: 80px; font-size: 32px;">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
        </div>
        <h4 class="mb-1">Olá, {{ $user->name }}!</h4>
        <p class="text-muted">Por favor, defina a sua password para ativar a conta</p>
    </div>
    
    <form method="POST" action="{{ route('activation.complete') }}" id="activationForm">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" value="{{ $user->email }}" readonly>
            <small class="text-muted">O seu endereço de email</small>
        </div>
        
        <div class="mb-3">
            <label for="password" class="form-label">Nova Password *</label>
            <div class="input-group">
                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                       id="password" name="password" required 
                       placeholder="Mínimo 8 caracteres"
                       oninput="checkPasswordStrength()">
                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                    <i class="fas fa-eye"></i>
                </button>
                @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            
            <div class="password-strength mt-2">
                <div class="strength-bar" id="strengthBar"></div>
            </div>
            <div class="instructions" id="passwordInstructions"></div>
        </div>
        
        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Confirmar Password *</label>
            <div class="input-group">
                <input type="password" class="form-control" 
                       id="password_confirmation" name="password_confirmation" required
                       placeholder="Repita a password">
                <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirmation">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            <div class="instructions" id="passwordMatch"></div>
        </div>
        
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                <span id="btnText">Ativar Conta</span>
                <span id="btnLoading" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i> A processar...
                </span>
            </button>
        </div>
        
        <div class="text-center mt-4">
            <p class="text-muted mb-0">
                <small>
                    <i class="fas fa-info-circle me-1"></i>
                    O link de ativação expira em 48 horas
                </small>
            </p>
        </div>
    </form>
@else
    <div class="text-center py-5">
        <div class="mb-4">
            <i class="fas fa-exclamation-triangle fa-3x text-warning"></i>
        </div>
        <h4 class="mb-3">Link Inválido ou Expirado</h4>
        <p class="text-muted mb-4">
            O link de ativação é inválido ou já expirou. 
            Por favor, contacte o administrador do sistema para um novo convite.
        </p>
        <a href="mailto:{{ config('mail.from.address') }}" class="btn btn-outline-primary">
            <i class="fas fa-envelope me-2"></i>Contactar Administrador
        </a>
    </div>
@endif
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle password visibility
    $('#togglePassword').click(function() {
        const passwordInput = $('#password');
        const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
        passwordInput.attr('type', type);
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });
    
    $('#togglePasswordConfirmation').click(function() {
        const confirmInput = $('#password_confirmation');
        const type = confirmInput.attr('type') === 'password' ? 'text' : 'password';
        confirmInput.attr('type', type);
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });
    
    // Check password match
    $('#password_confirmation').on('input', function() {
        const password = $('#password').val();
        const confirm = $(this).val();
        
        if (confirm.length > 0) {
            if (password === confirm) {
                $('#passwordMatch').html('<i class="fas fa-check-circle text-success me-1"></i>As passwords coincidem');
            } else {
                $('#passwordMatch').html('<i class="fas fa-times-circle text-danger me-1"></i>As passwords não coincidem');
            }
        } else {
            $('#passwordMatch').html('');
        }
    });
    
    // Form submission
    $('#activationForm').submit(function(e) {
        const submitBtn = $('#submitBtn');
        const btnText = $('#btnText');
        const btnLoading = $('#btnLoading');
        
        submitBtn.prop('disabled', true);
        btnText.hide();
        btnLoading.show();
    });
});

function checkPasswordStrength() {
    const password = $('#password').val();
    const strengthBar = $('#strengthBar');
    const instructions = $('#passwordInstructions');
    
    if (password.length === 0) {
        strengthBar.css({
            'width': '0%',
            'background-color': '#e2e8f0'
        });
        instructions.html('');
        return;
    }
    
    let strength = 0;
    let tips = [];
    
    // Check length
    if (password.length >= 8) strength += 25;
    else tips.push('Use pelo menos 8 caracteres');
    
    // Check for mixed case
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 25;
    else tips.push('Use letras maiúsculas e minúsculas');
    
    // Check for numbers
    if (/\d/.test(password)) strength += 25;
    else tips.push('Adicione pelo menos um número');
    
    // Check for special characters
    if (/[^A-Za-z0-9]/.test(password)) strength += 25;
    else tips.push('Adicione um caractere especial (@, #, $, etc.)');
    
    // Update strength bar
    let color;
    let text;
    
    if (strength < 50) {
        color = '#ef476f'; // Red
        text = 'Fraca';
    } else if (strength < 75) {
        color = '#ffd166'; // Yellow
        text = 'Média';
    } else {
        color = '#06d6a0'; // Green
        text = 'Forte';
    }
    
    strengthBar.css({
        'width': strength + '%',
        'background-color': color
    });
    
    // Update instructions
    if (tips.length > 0) {
        instructions.html('<small>' + tips.join('<br>') + '</small>');
    } else {
        instructions.html('<i class="fas fa-check-circle text-success me-1"></i><small>Password forte!</small>');
    }
}
</script>
@endpush