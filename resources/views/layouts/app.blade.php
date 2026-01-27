<!DOCTYPE html>
<html lang="pt" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Asset Management')</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #7209b7;
            --success-color: #06d6a0;
            --warning-color: #ffd166;
            --danger-color: #ef476f;
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 70px;
            --navbar-height: 70px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: #f8fafc;
            color: #334155;
            overflow-x: hidden;
        }

        /* Layout Wrapper */
        #wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* Sidebar */
        #sidebar-wrapper {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            color: white;
            transition: all 0.3s ease;
            position: fixed;
            z-index: 1000;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
        }

        #sidebar-wrapper.collapsed {
            margin-left: calc(var(--sidebar-collapsed-width) - var(--sidebar-width));
        }

        .sidebar-heading {
            padding: 1.5rem 1.5rem 1rem;
            font-size: 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-heading .logo-icon {
            font-size: 1.75rem;
            color: #60a5fa;
        }

        .list-group {
            padding: 1rem 0.75rem;
        }

        .sidebar-item {
            margin-bottom: 0.5rem;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.875rem 1rem;
            color: #cbd5e1;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .sidebar-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .sidebar-link.active {
            background: linear-gradient(135deg, var(--primary-color), #3a56d4);
            color: white;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        }

        .sidebar-link i {
            font-size: 1.25rem;
            width: 24px;
            text-align: center;
        }

        .sidebar-badge {
            margin-left: auto;
            background: rgba(255, 255, 255, 0.15);
            color: white;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
        }

        .sidebar-dropdown {
            position: relative;
        }

        .sidebar-dropdown .dropdown-toggle::after {
            margin-left: auto;
            transition: transform 0.3s;
        }

        .sidebar-dropdown.show .dropdown-toggle::after {
            transform: rotate(180deg);
        }

        .sidebar-dropdown-menu {
            background: rgba(30, 41, 59, 0.9);
            border: none;
            border-radius: 10px;
            margin: 0.5rem 0 0 0;
            padding: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .sidebar-dropdown-item {
            color: #cbd5e1;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.25rem;
            transition: all 0.2s;
        }

        .sidebar-dropdown-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        /* Page Content */
        #page-content-wrapper {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: all 0.3s ease;
            min-height: 100vh;
        }

        #page-content-wrapper.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        /* Navbar */
        #navbar-wrapper {
            height: var(--navbar-height);
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .navbar-content {
            padding: 0 2rem;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            color: #64748b;
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .sidebar-toggle:hover {
            background: #f1f5f9;
            color: var(--primary-color);
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .user-menu {
            position: relative;
        }

        .user-dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: none;
            border: none;
            color: #475569;
            padding: 0.5rem;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .user-dropdown-toggle:hover {
            background: #f1f5f9;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .user-info {
            text-align: left;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.875rem;
        }

        .user-role {
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .user-dropdown-menu {
            position: absolute;
            right: 0;
            top: 100%;
            background: white;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 0.5rem;
            min-width: 200px;
            display: none;
        }

        .user-dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: #475569;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .user-dropdown-item:hover {
            background: #f8fafc;
            color: var(--primary-color);
        }

        .user-dropdown-item i {
            width: 20px;
            text-align: center;
        }

        .notification-badge {
            position: relative;
        }

        .notification-icon {
            color: #64748b;
            font-size: 1.25rem;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.2s;
            cursor: pointer;
            position: relative;
        }

        .notification-icon:hover {
            background: #f1f5f9;
            color: var(--primary-color);
        }

        .notification-dot {
            position: absolute;
            top: 5px;
            right: 5px;
            width: 8px;
            height: 8px;
            background: var(--danger-color);
            border-radius: 50%;
            border: 2px solid white;
        }

        /* Main Content */
        .main-content {
            padding: 2rem;
            min-height: calc(100vh - var(--navbar-height));
        }

        /* Card Styles */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: white;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #f1f5f9;
            padding: 1.5rem;
            border-radius: 15px 15px 0 0 !important;
        }

        .card-title {
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Button Styles */
        .btn {
            border-radius: 10px;
            font-weight: 500;
            padding: 0.625rem 1.25rem;
            transition: all 0.2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #3a56d4);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #3a56d4, #2e47b5);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }

        /* Table Styles */
        .table {
            --bs-table-bg: transparent;
            --bs-table-striped-bg: #f8fafc;
            margin-bottom: 0;
        }

        .table th {
            font-weight: 600;
            color: #64748b;
            border-top: none;
            padding: 1rem;
            background: #f8fafc;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background-color: #f1f5f9;
        }

        /* Badge Styles */
        .badge {
            padding: 0.5rem 0.875rem;
            font-weight: 500;
            border-radius: 20px;
        }

        /* DataTables Customization */
        .dataTables_wrapper {
            padding: 0;
        }

        .dataTables_filter input {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            transition: all 0.2s;
        }

        .dataTables_filter input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
            outline: none;
        }

        /* Responsive */
        @media (max-width: 768px) {
            #sidebar-wrapper {
                margin-left: calc(var(--sidebar-collapsed-width) - var(--sidebar-width));
            }

            #sidebar-wrapper.show {
                margin-left: 0;
            }

            #page-content-wrapper {
                margin-left: var(--sidebar-collapsed-width);
            }

            #page-content-wrapper.expanded {
                margin-left: 0;
            }

            .navbar-content {
                padding: 0 1rem;
            }

            .main-content {
                padding: 1rem;
            }

            .user-info {
                display: none;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }

        /* Theme Toggle */
        .theme-toggle {
            background: none;
            border: none;
            color: #64748b;
            font-size: 1.25rem;
            padding: 0.5rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .theme-toggle:hover {
            background: #f1f5f9;
            color: var(--primary-color);
        }

        /* Dark Theme */
        [data-bs-theme="dark"] {
            --bs-body-bg: #0f172a;
            --bs-body-color: #e2e8f0;
        }

        [data-bs-theme="dark"] .card {
            background: #1e293b;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.2);
        }

        [data-bs-theme="dark"] .card-header {
            background: #1e293b;
            border-bottom-color: #334155;
        }

        [data-bs-theme="dark"] .table th {
            background: #1e293b;
            color: #cbd5e1;
        }

        [data-bs-theme="dark"] .navbar-content {
            background: #1e293b;
        }

        [data-bs-theme="dark"] .sidebar-toggle,
        [data-bs-theme="dark"] .notification-icon,
        [data-bs-theme="dark"] .theme-toggle {
            color: #cbd5e1;
        }

        [data-bs-theme="dark"] .sidebar-toggle:hover,
        [data-bs-theme="dark"] .notification-icon:hover,
        [data-bs-theme="dark"] .theme-toggle:hover {
            background: #334155;
            color: #60a5fa;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <!-- Sidebar Header -->
            <div class="sidebar-heading">
                <i class="fas fa-chart-line logo-icon"></i>
                <span class="sidebar-logo-text">Asset Management</span>
            </div>

            <!-- Sidebar Menu -->
            <div class="list-group list-group-flush">
                <!-- Dashboard -->
                <div class="sidebar-item">
                    <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </div>

                <!-- Gestão Dropdown -->
                <div class="sidebar-item sidebar-dropdown">
                    <a class="sidebar-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-folder"></i>
                        <span>Gestão</span>
                        <span class="sidebar-badge">6</span>
                    </a>
                    <div class="dropdown-menu sidebar-dropdown-menu">
                        <a href="{{ route('companies.index') }}" class="sidebar-dropdown-item {{ request()->routeIs('companies.*') ? 'active' : '' }}">
                            <i class="fas fa-building me-2"></i>Empresas
                        </a>
                        <a href="{{ route('employees.index') }}" class="sidebar-dropdown-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                            <i class="fas fa-user-tie me-2"></i>Colabora
                        </a>
                        <a href="{{ route('suppliers.index') }}" class="sidebar-dropdown-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                            <i class="fas fa-truck me-2"></i>Fornece
                        </a>
                        <a href="{{ route('projects.index') }}" class="sidebar-dropdown-item {{ request()->routeIs('projects.*') ? 'active' : '' }}">
                            <i class="fas fa-project-diagram me-2"></i>Projetos
                        </a>
                        <a href="{{ route('invoices.index') }}" class="sidebar-dropdown-item {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                            <i class="fas fa-file-invoice-dollar me-2"></i>Faturas
                        </a>
                        <a href="{{ route('shipments.index') }}" class="sidebar-dropdown-item {{ request()->routeIs('shipments.*') ? 'active' : '' }}">
                            <i class="fas fa-shipping-fast me-2"></i>Remessas
                        </a>
                    </div>
                </div>

                <!-- Utilizadores -->
                <div class="sidebar-item">
                    <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>Utilizadores</span>
                        @php
                            $pendingUsers = \App\Models\User::where('active', false)->count();
                        @endphp
                        @if($pendingUsers > 0)
                            <span class="sidebar-badge">{{ $pendingUsers }}</span>
                        @endif
                    </a>
                </div>

                <!-- Relatórios -->
                <div class="sidebar-item">
                    <a href="#" class="sidebar-link">
                        <i class="fas fa-chart-bar"></i>
                        <span>Relatórios</span>
                    </a>
                </div>

                <!-- Configurações -->
                <div class="sidebar-item">
                    <a href="#" class="sidebar-link">
                        <i class="fas fa-cog"></i>
                        <span>Configurações</span>
                    </a>
                </div>

                <!-- Divider -->
                <div class="sidebar-item mt-4">
                    <div style="height: 1px; background: rgba(255, 255, 255, 0.1); margin: 1rem 0;"></div>
                </div>

                <!-- Ajuda -->
                <div class="sidebar-item">
                    <a href="#" class="sidebar-link">
                        <i class="fas fa-question-circle"></i>
                        <span>Ajuda & Suporte</span>
                    </a>
                </div>
            </div>

            <!-- Sidebar Footer -->
            <div class="sidebar-footer p-3 mt-auto">
                <div class="text-center text-muted small">
                    <div>Asset Management v1.0</div>
                    <div class="mt-1">&copy; {{ date('Y') }}</div>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <!-- Navbar -->
            <nav id="navbar-wrapper">
                <div class="navbar-content">
                    <div class="navbar-left">
                        <button class="sidebar-toggle" id="sidebarToggle">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
                    </div>

                    <div class="navbar-right">
                        <!-- Theme Toggle -->
                        <button class="theme-toggle" id="themeToggle" title="Alternar tema">
                            <i class="fas fa-moon"></i>
                        </button>

                        <!-- Notifications -->
                        <div class="notification-badge">
                            <div class="notification-icon" id="notificationToggle">
                                <i class="fas fa-bell"></i>
                                <span class="notification-dot"></span>
                            </div>
                        </div>

                        <!-- User Menu -->
                        <div class="user-menu">
                            <button class="user-dropdown-toggle" id="userDropdownToggle">
                                <div class="user-avatar">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <div class="user-info">
                                    <div class="user-name">{{ auth()->user()->name }}</div>
                                    <div class="user-role">{{ ucfirst(auth()->user()->role) }}</div>
                                </div>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="user-dropdown-menu" id="userDropdownMenu">
                                {{-- <a href="{{ route('profile') }}" class="user-dropdown-item">
                                    <i class="fas fa-user"></i>
                                    <span>Meu Perfil</span>
                                </a>
                                <a href="{{ route('settings') }}" class="user-dropdown-item">
                                    <i class="fas fa-cog"></i>
                                    <span>Configurações</span>
                                </a> --}}
                                <div class="dropdown-divider"></div>
                                <a href="{{ route('logout') }}" 
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                                   class="user-dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Sair</span>
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="main-content fade-in">
                <!-- Page Header -->
                @hasSection('page-header')
                    <div class="page-header mb-4">
                        @yield('page-header')
                    </div>
                @endif

                <!-- Breadcrumb -->
                @hasSection('breadcrumb')
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            @yield('breadcrumb')
                        </ol>
                    </nav>
                @endif

                <!-- Content -->
                @yield('content')

                <!-- Footer -->
                <footer class="mt-5 pt-4 border-top text-muted small">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-0">
                                    &copy; {{ date('Y') }} Asset Management System. Todos os direitos reservados.
                                </p>
                            </div>
                            <div class="col-md-6 text-end">
                                <p class="mb-0">
                                    <span class="me-3">Versão 1.0.0</span>
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-database me-1"></i>
                                        {{ \App\Models\User::count() }} usuários
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </footer>
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // SweetAlert Global Config
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        // CSRF Token setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Sidebar Toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar-wrapper');
            const pageContent = document.getElementById('page-content-wrapper');
            
            sidebar.classList.toggle('collapsed');
            pageContent.classList.toggle('expanded');
            
            // Update icon
            const icon = this.querySelector('i');
            if (sidebar.classList.contains('collapsed')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-chevron-right');
            } else {
                icon.classList.remove('fa-chevron-right');
                icon.classList.add('fa-bars');
            }
            
            // Save state to localStorage
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });

        // User Dropdown
        document.getElementById('userDropdownToggle').addEventListener('click', function() {
            const dropdown = document.getElementById('userDropdownMenu');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const userDropdown = document.getElementById('userDropdownMenu');
            const userToggle = document.getElementById('userDropdownToggle');
            
            if (!userToggle.contains(event.target) && !userDropdown.contains(event.target)) {
                userDropdown.style.display = 'none';
            }
        });

        // Theme Toggle
        document.getElementById('themeToggle').addEventListener('click', function() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            const icon = this.querySelector('i');
            
            html.setAttribute('data-bs-theme', newTheme);
            icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            
            // Save theme preference
            localStorage.setItem('theme', newTheme);
        });

        // Notification Toggle (placeholder)
        document.getElementById('notificationToggle').addEventListener('click', function() {
            Toast.fire({
                icon: 'info',
                title: 'Sistema de notificações em desenvolvimento'
            });
        });

        // Load saved preferences
        document.addEventListener('DOMContentLoaded', function() {
            // Load sidebar state
            const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (sidebarCollapsed) {
                const sidebar = document.getElementById('sidebar-wrapper');
                const pageContent = document.getElementById('page-content-wrapper');
                const toggleIcon = document.getElementById('sidebarToggle').querySelector('i');
                
                sidebar.classList.add('collapsed');
                pageContent.classList.add('expanded');
                toggleIcon.classList.remove('fa-bars');
                toggleIcon.classList.add('fa-chevron-right');
            }

            // Load theme
            const savedTheme = localStorage.getItem('theme') || 'light';
            const html = document.documentElement;
            const themeIcon = document.getElementById('themeToggle').querySelector('i');
            
            html.setAttribute('data-bs-theme', savedTheme);
            themeIcon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';

            // Auto-hide dropdowns on mobile
            if (window.innerWidth <= 768) {
                document.getElementById('sidebar-wrapper').classList.add('collapsed');
                document.getElementById('page-content-wrapper').classList.add('expanded');
            }
        });

        // DataTables Portuguese Translation
        if ($.fn.DataTable) {
            $.extend(true, $.fn.dataTable.defaults, {
                language: {
                    "sEmptyTable": "Não foi encontrado nenhum registo",
                    "sLoadingRecords": "A carregar...",
                    "sProcessing": "A processar...",
                    "sLengthMenu": "Mostrar _MENU_ registos",
                    "sZeroRecords": "Não foram encontrados resultados",
                    "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registos",
                    "sInfoEmpty": "Mostrando de 0 até 0 de 0 registos",
                    "sInfoFiltered": "(filtrado de _MAX_ registos no total)",
                    "sInfoPostFix": "",
                    "sSearch": "Procurar:",
                    "sUrl": "",
                    "oPaginate": {
                        "sFirst": "Primeiro",
                        "sPrevious": "Anterior",
                        "sNext": "Seguinte",
                        "sLast": "Último"
                    },
                    "oAria": {
                        "sSortAscending": ": Ordenar colunas de forma ascendente",
                        "sSortDescending": ": Ordenar colunas de forma descendente"
                    }
                },
                responsive: true,
                pageLength: 10,
                lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"]],
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
            });
        }

        // Auto-dismiss alerts
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });

        // Active sidebar item highlight
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const sidebarLinks = document.querySelectorAll('.sidebar-link, .sidebar-dropdown-item');
            
            sidebarLinks.forEach(function(link) {
                if (link.href && link.href.includes(currentPath)) {
                    link.classList.add('active');
                    
                    // Expand parent dropdown if needed
                    const parentDropdown = link.closest('.sidebar-dropdown');
                    if (parentDropdown) {
                        parentDropdown.classList.add('show');
                    }
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>