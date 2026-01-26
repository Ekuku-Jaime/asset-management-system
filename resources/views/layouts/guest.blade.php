<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Management - {{ $title ?? 'Ativação de Conta' }}</title>
    
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
        }
        
        .auth-header h2 {
            margin-bottom: 0.5rem;
            font-weight: 600;
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
        
        .logo {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-bottom: 2rem;
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
                        <h2><i class="fas fa-user-shield me-2"></i>Asset Management</h2>
                        <p class="mb-0">{{ $subtitle ?? 'Sistema de Gestão de Ativos' }}</p>
                    </div>
                    
                    <div class="auth-body">
                        @yield('content')
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <p class="text-muted">
                        <small>
                            &copy; {{ date('Y') }} Asset Management System. Todos os direitos reservados.
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