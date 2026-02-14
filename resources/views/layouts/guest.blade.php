<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Management - {{ $title ?? 'Login na Conta' }}</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #4361ee;
            --gradient-start: #4361ee;
            --gradient-end: #3a56d4;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .auth-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .auth-header {
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }
        
        .logo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        
        /* Logo sem filtro para manter cores originais */
        .logo-image {
            height: 70px;
            width: auto;
            margin-bottom: 1rem;
            max-width: 200px;
            object-fit: contain;
            /* Sombra para destacar no fundo azul */
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }
        
        .logo-text {
            text-align: center;
        }
        
        .logo-text h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0 0 0.3rem 0;
            line-height: 1.2;
            color: white;
        }
        
        .logo-text p {
            font-size: 0.9rem;
            opacity: 0.9;
            margin: 0;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .page-title {
            margin: 1.5rem 0 0.5rem 0;
            font-weight: 600;
            font-size: 1.5rem;
        }
        
        .page-subtitle {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 0;
        }
        
        .auth-body {
            padding: 2rem;
            background: white;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 500;
            transition: transform 0.2s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        .form-control {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }
        
        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 4px;
            background: #e2e8f0;
        }
        
        .password-strength .strength-bar {
            height: 100%;
            border-radius: 2px;
            transition: width 0.3s, background-color 0.3s;
        }
        
        .instructions {
            font-size: 0.875rem;
            color: #64748b;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="auth-card">
                    <div class="auth-header">
                        <div class="logo-container">
                            <!-- Logo colorido -->
                            @if(file_exists(public_path('images/logo.png')))
                                <img src="{{ asset('images/logo.png') }}" 
                                     alt="Asset Management Logo" 
                                     class="logo-image">
                            @else
                                <!-- Fallback textual -->
                                <div class="logo-text">
                                    <h1>Asset Management</h1>
                                    <p>Sistema de Gestão de Ativos</p>
                                </div>
                            @endif
                        </div>
                        
                        <h2 class="page-title">
                            <i class="fas {{ $icon ?? 'fa-user-shield' }} me-2"></i>
                            {{ $title ?? 'Ativação de Conta' }}
                        </h2>
                        <p class="page-subtitle">{{ $subtitle ?? 'Gestão Inteligente de Recursos' }}</p>
                    </div>
                    
                    <div class="auth-body">
                        @yield('content')
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <p class="text-muted">
                        <small>
                            <i class="fas fa-copyright me-1"></i>
                            {{ date('Y') }} Asset Management System. Todos os direitos reservados.
                        </small>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    @stack('scripts')
</body>
</html>