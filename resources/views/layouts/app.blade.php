<!DOCTYPE html>
<html lang="pt" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Asset Management')</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    {{-- <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css"> --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.0/css/buttons.bootstrap5.min.css">

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
            --sidebar-width: 260px;
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
            min-height: 100vh;
            position: relative;
        }

        /* Layout Wrapper */
        #wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
            position: relative;
        }

        /* Sidebar - Scrollable */
        #sidebar-wrapper {
            width: var(--sidebar-collapsed-width);
            height: 100vh;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            color: white;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: fixed;
            z-index: 1050;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
        }

        #sidebar-wrapper:hover,
        #sidebar-wrapper.expanded {
            width: var(--sidebar-width);
        }

        /* Sidebar Content - Scrollable Area */
        .sidebar-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            overflow-x: hidden;
        }

        /* Custom Scrollbar for Sidebar */
        .sidebar-content::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-content::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 3px;
        }

        .sidebar-content::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
            transition: background 0.3s;
        }

        .sidebar-content::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Hide scrollbar when not hovered (collapsed state) */
        #sidebar-wrapper:not(:hover):not(.expanded) .sidebar-content::-webkit-scrollbar {
            display: none;
        }

        #sidebar-wrapper:not(:hover):not(.expanded) .sidebar-content {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .sidebar-heading {
            padding: 1.5rem 1rem;
            font-size: 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            white-space: nowrap;
            flex-shrink: 0;
            background: rgba(0, 0, 0, 0.1);
        }

        .sidebar-heading .logo-icon {
            font-size: 1.75rem;
            color: #60a5fa;
            min-width: 40px;
            text-align: center;
        }

        .sidebar-logo-text {
            opacity: 0;
            transform: translateX(-10px);
            transition: opacity 0.3s, transform 0.3s;
            white-space: nowrap;
        }

        #sidebar-wrapper:hover .sidebar-logo-text,
        #sidebar-wrapper.expanded .sidebar-logo-text {
            opacity: 1;
            transform: translateX(0);
        }

        .sidebar-menu {
            flex: 1;
            padding: 1rem 0.5rem;
            overflow-y: auto;
            min-height: 0;
        }

        .sidebar-item {
            margin-bottom: 0.5rem;
            position: relative;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.875rem 0.75rem;
            color: #cbd5e1;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
            white-space: nowrap;
        }

        .sidebar-link i {
            font-size: 1.25rem;
            min-width: 40px;
            text-align: center;
            transition: transform 0.2s;
        }

        .sidebar-link:hover i {
            transform: scale(1.1);
        }

        .sidebar-link span {
            opacity: 0;
            transform: translateX(-10px);
            transition: opacity 0.3s 0.1s, transform 0.3s 0.1s;
        }

        #sidebar-wrapper:hover .sidebar-link span,
        #sidebar-wrapper.expanded .sidebar-link span {
            opacity: 1;
            transform: translateX(0);
        }

        .sidebar-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .sidebar-link.active {
            background: linear-gradient(135deg, var(--primary-color), #3a56d4);
            color: white;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        }

        .sidebar-badge {
            margin-left: auto;
            background: rgba(255, 255, 255, 0.15);
            color: white;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
            opacity: 0;
            transition: opacity 0.3s 0.2s;
        }

        #sidebar-wrapper:hover .sidebar-badge,
        #sidebar-wrapper.expanded .sidebar-badge {
            opacity: 1;
        }

        .sidebar-dropdown-menu {
            background: rgba(30, 41, 59, 0.95);
            border: none;
            border-radius: 10px;
            margin: 0.5rem 0 0 0;
            padding: 0.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            display: none;
        }

        .sidebar-dropdown.show .sidebar-dropdown-menu {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .sidebar-dropdown-item {
            color: #cbd5e1;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.25rem;
            transition: all 0.2s;
            text-decoration: none;
            display: block;
        }

        .sidebar-dropdown-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .sidebar-dropdown-item.active {
            background: rgba(67, 97, 238, 0.2);
            color: #60a5fa;
        }

        .sidebar-dropdown-toggle::after {
            content: '\f078';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            border: none;
            margin-left: auto;
            transition: transform 0.3s;
            opacity: 0;
        }

        #sidebar-wrapper:hover .sidebar-dropdown-toggle::after,
        #sidebar-wrapper.expanded .sidebar-dropdown-toggle::after {
            opacity: 1;
        }

        .sidebar-dropdown.show .sidebar-dropdown-toggle::after {
            transform: rotate(180deg);
        }

        .sidebar-footer {
            padding: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            flex-shrink: 0;
            background: rgba(0, 0, 0, 0.1);
        }

        /* Page Content */
        #page-content-wrapper {
            flex: 1;
            margin-left: var(--sidebar-collapsed-width);
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            width: calc(100% - var(--sidebar-collapsed-width));
        }

        #sidebar-wrapper.expanded ~ #page-content-wrapper {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
        }

        /* Navbar */
        #navbar-wrapper {
            height: var(--navbar-height);
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 1040;
            flex-shrink: 0;
        }

        .navbar-content {
            padding: 0 1.5rem;
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
            z-index: 1;
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

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 1.5rem;
            overflow-y: auto;
            overflow-x: hidden;
            min-height: calc(100vh - var(--navbar-height));
        }

        .main-content::-webkit-scrollbar {
            width: 8px;
        }

        .main-content::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        .main-content::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .main-content::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            #sidebar-wrapper {
                transform: translateX(-100%);
                width: var(--sidebar-width) !important;
                z-index: 1050;
            }

            #sidebar-wrapper.show {
                transform: translateX(0);
            }

            #page-content-wrapper {
                margin-left: 0 !important;
                width: 100% !important;
            }

            .navbar-content {
                padding: 0 1rem;
            }

            .main-content {
                padding: 1rem;
            }

            .page-title {
                font-size: 1.25rem;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 0.75rem;
            }
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

        [data-bs-theme="dark"] .table {
            --bs-table-color: #e2e8f0;
            --bs-table-bg: transparent;
            --bs-table-border-color: #334155;
        }

        [data-bs-theme="dark"] .table th {
            background: #1e293b;
            color: #cbd5e1;
        }

        [data-bs-theme="dark"] #navbar-wrapper {
            background: #1e293b;
        }

        [data-bs-theme="dark"] .page-title {
            color: #e2e8f0;
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

        [data-bs-theme="dark"] .main-content::-webkit-scrollbar-track {
            background: #1e293b;
        }

        [data-bs-theme="dark"] .main-content::-webkit-scrollbar-thumb {
            background: #475569;
        }

        [data-bs-theme="dark"] .main-content::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }

        /* User Menu */
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
            flex-shrink: 0;
        }

        .user-info {
            text-align: left;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.875rem;
            white-space: nowrap;
        }

        .user-role {
            font-size: 0.75rem;
            color: #94a3b8;
            white-space: nowrap;
        }

        .user-dropdown-menu {
            position: absolute;
            right: 0;
            top: calc(100% + 5px);
            background: white;
            border: none;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            padding: 0.5rem;
            min-width: 200px;
            display: none;
            z-index: 1060;
        }

        .user-dropdown-menu.show {
            display: block;
            animation: fadeIn 0.2s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 2000;
        }

        .loading-overlay.show {
            display: flex;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: var(--primary-color);
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Mobile Sidebar Overlay */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 1045;
            display: none;
        }

        .sidebar-overlay.show {
            display: block;
        }

        /* Tooltip adjustments for collapsed sidebar */
        .sidebar-link[data-bs-toggle="tooltip"]:hover::after {
            content: attr(title);
            position: absolute;
            left: calc(100% + 10px);
            top: 50%;
            transform: translateY(-50%);
            background: #1e293b;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            white-space: nowrap;
            z-index: 1070;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            pointer-events: none;
        }

        #sidebar-wrapper:hover .sidebar-link[data-bs-toggle="tooltip"]:hover::after,
        #sidebar-wrapper.expanded .sidebar-link[data-bs-toggle="tooltip"]:hover::after {
            display: none;
        }
    </style>

    @stack('styles')
</head>

<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <!-- Sidebar Content (Scrollable) -->
            <div class="sidebar-content">
                <!-- Sidebar Header -->
                <div class="sidebar-heading">
                    <i class="fas fa-boxes logo-icon"></i>
                    <span class="sidebar-logo-text">Asset Manager</span>
                </div>

                <!-- Sidebar Menu -->
                <div class="sidebar-menu">
                    <!-- Dashboard -->
                    <div class="sidebar-item">
                        <a href="{{ route('dashboard') }}"
                            class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                            data-bs-toggle="tooltip" title="Dashboard">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </div>

                    <!-- Assets -->
                    <div class="sidebar-item">
                        <a href="{{ route('assets.index') }}"
                            class="sidebar-link {{ request()->routeIs('assets.*') ? 'active' : '' }}"
                            data-bs-toggle="tooltip" title="Activos">
                            <i class="fas fa-box"></i>
                            <span>Activos</span>
                            @php
                                $pendingAssets = \App\Models\Asset::where('process_status', 'incompleto')->count();
                            @endphp
                            @if($pendingAssets > 0)
                                <span class="sidebar-badge">{{ $pendingAssets }}</span>
                            @endif
                        </a>
                    </div>

                    <!-- Gestão Dropdown -->
                    <div class="sidebar-item sidebar-dropdown">
                        <a class="sidebar-link sidebar-dropdown-toggle" href="#"
                           data-bs-toggle="tooltip" title="Gestão">
                            <i class="fas fa-folder"></i>
                            <span>Gestão</span>
                            <span class="sidebar-badge">7</span>
                        </a>
                        <div class="sidebar-dropdown-menu">
                            <a href="{{ route('companies.index') }}"
                                class="sidebar-dropdown-item {{ request()->routeIs('companies.*') ? 'active' : '' }}">
                                <i class="fas fa-building me-2"></i>Empresas
                            </a>
                            <a href="{{ route('employees.index') }}"
                                class="sidebar-dropdown-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                                <i class="fas fa-user-tie me-2"></i>Colaboradores
                            </a>
                            <a href="{{ route('suppliers.index') }}"
                                class="sidebar-dropdown-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                                <i class="fas fa-truck me-2"></i>Fornecedores
                            </a>
                            <a href="{{ route('projects.index') }}"
                                class="sidebar-dropdown-item {{ request()->routeIs('projects.*') ? 'active' : '' }}">
                                <i class="fas fa-project-diagram me-2"></i>Projetos
                            </a>
                            <a href="{{ route('invoices.index') }}"
                                class="sidebar-dropdown-item {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                                <i class="fas fa-file-invoice-dollar me-2"></i>Faturas
                            </a>
                            <a href="{{ route('shipments.index') }}"
                                class="sidebar-dropdown-item {{ request()->routeIs('shipments.*') ? 'active' : '' }}">
                                <i class="fas fa-shipping-fast me-2"></i>Remessas
                            </a>
                            <a href="{{ route('requests.index') }}"
                                class="sidebar-dropdown-item {{ request()->routeIs('requests.*') ? 'active' : '' }}">
                                <i class="fas fa-clipboard-list me-2"></i>Requisições
                            </a>
                        </div>
                    </div>

                    <!-- Manutenções -->
                    <div class="sidebar-item">
                        <a href="#"
                            class="sidebar-link {{ request()->routeIs('maintenances.*') ? 'active' : '' }}"
                            data-bs-toggle="tooltip" title="Manutenções">
                            <i class="fas fa-tools"></i>
                            <span>Manutenções</span>
                            @php
                                $pendingMaintenance = \App\Models\Asset::where('asset_status', 'manutencao')->count();
                            @endphp
                            @if($pendingMaintenance > 0)
                                <span class="sidebar-badge">{{ $pendingMaintenance }}</span>
                            @endif
                        </a>
                    </div>

                    <!-- Relatórios -->
                    <div class="sidebar-item">
                        <a href="#"
                            class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"
                            data-bs-toggle="tooltip" title="Relatórios">
                            <i class="fas fa-chart-bar"></i>
                            <span>Relatórios</span>
                        </a>
                    </div>

                    <!-- Utilizadores -->
                    <div class="sidebar-item">
                        <a href="{{ route('users.index') }}"
                            class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                            data-bs-toggle="tooltip" title="Utilizadores">
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

                    <!-- Configurações -->
                    <div class="sidebar-item">
                        <a href="#"
                            class="sidebar-link {{ request()->routeIs('settings.*') ? 'active' : '' }}"
                            data-bs-toggle="tooltip" title="Configurações">
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
                        <a href="#"
                            class="sidebar-link"
                            data-bs-toggle="tooltip" title="Ajuda">
                            <i class="fas fa-question-circle"></i>
                            <span>Ajuda</span>
                        </a>
                    </div>

                    <!-- Spacer for scroll -->
                    <div style="height: 20px;"></div>
                </div>
            </div>

            <!-- Sidebar Footer -->
            <div class="sidebar-footer">
                <div class="text-center text-muted small">
                    <div>v1.0.0</div>
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
                        <h1 class="page-title">
                            @hasSection('page-title')
                                @yield('page-title')
                            @else
                                @yield('title', 'Dashboard')
                            @endif
                        </h1>
                    </div>

                    <div class="navbar-right">
                        <!-- Mobile Sidebar Toggle -->
                        <button class="sidebar-toggle d-md-none" id="mobileSidebarToggle">
                            <i class="fas fa-bars"></i>
                        </button>

                        <!-- Theme Toggle -->
                        <button class="theme-toggle" id="themeToggle" title="Alternar tema">
                            <i class="fas fa-moon"></i>
                        </button>

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
                                <a href="#" class="user-dropdown-item">
                                    <i class="fas fa-user me-2"></i>
                                    <span>Meu Perfil</span>
                                </a>
                                <a href="#" class="user-dropdown-item">
                                    <i class="fas fa-cog me-2"></i>
                                    <span>Configurações</span>
                                </a>
                                <div class="dropdown-divider my-2"></div>
                                <a href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                    class="user-dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i>
                                    <span>Sair</span>
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                    style="display: none;">
                                    @csrf
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="main-content">
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

                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Erro!</strong> Por favor, verifique os seguintes campos:
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Content -->
                @yield('content')

                <!-- Footer -->
                <footer class="mt-5 pt-4 border-top text-muted small">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-0">
                                &copy; {{ date('Y') }} Asset Management System
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="mb-0">
                                <span class="me-3">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ now()->format('d/m/Y H:i') }}
                                </span>
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-database me-1"></i>
                                    {{ \App\Models\User::count() }} utilizadores
                                </span>
                            </p>
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
    {{-- <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script> --}}
    <script src="https://cdn.datatables.net/buttons/2.4.0/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.0/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.0/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.0/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.0/js/buttons.print.min.js"></script>

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

        // Show Toast Notification
        function showToast(type, message) {
            Toast.fire({
                icon: type,
                title: message
            });
        }

        // CSRF Token setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Loading Overlay
        function showLoading() {
            document.getElementById('loadingOverlay').classList.add('show');
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').classList.remove('show');
        }

        // DOM Elements
        const sidebar = document.getElementById('sidebar-wrapper');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const pageContent = document.getElementById('page-content-wrapper');

        // Sidebar Toggle (Desktop - Permanent toggle)
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            sidebar.classList.toggle('expanded');
            
            // Update icon
            const icon = this.querySelector('i');
            if (sidebar.classList.contains('expanded')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }

            // Save state to localStorage
            localStorage.setItem('sidebarExpanded', sidebar.classList.contains('expanded'));
        });

        // Mobile sidebar toggle
        document.getElementById('mobileSidebarToggle').addEventListener('click', function() {
            sidebar.classList.add('show');
            sidebarOverlay.classList.add('show');
        });

        // Close sidebar when clicking on overlay
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 992) {
                if (!sidebar.contains(event.target) && !document.getElementById('mobileSidebarToggle').contains(event.target)) {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                }
            }
        });

        // User Dropdown
        document.getElementById('userDropdownToggle').addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdown = document.getElementById('userDropdownMenu');
            dropdown.classList.toggle('show');
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const userDropdown = document.getElementById('userDropdownMenu');
            const userToggle = document.getElementById('userDropdownToggle');

            if (!userToggle.contains(event.target)) {
                userDropdown.classList.remove('show');
            }

            // Close sidebar dropdowns
            const sidebarDropdowns = document.querySelectorAll('.sidebar-dropdown.show');
            sidebarDropdowns.forEach(dropdown => {
                if (!dropdown.contains(event.target)) {
                    dropdown.classList.remove('show');
                }
            });
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
            
            showToast('success', `Tema alterado para ${newTheme === 'dark' ? 'escuro' : 'claro'}`);
        });

        // Sidebar dropdown toggle
        document.querySelectorAll('.sidebar-dropdown-toggle').forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const dropdown = this.closest('.sidebar-dropdown');
                dropdown.classList.toggle('show');
            });
        });

        // Initialize on DOM load
        document.addEventListener('DOMContentLoaded', function() {
            // Load saved preferences
            // Sidebar state
            const sidebarExpanded = localStorage.getItem('sidebarExpanded') === 'true';
            if (sidebarExpanded) {
                sidebar.classList.add('expanded');
                document.getElementById('sidebarToggle').querySelector('i').className = 'fas fa-times';
            }

            // Theme
            const savedTheme = localStorage.getItem('theme') || 'light';
            const html = document.documentElement;
            const themeIcon = document.getElementById('themeToggle').querySelector('i');

            html.setAttribute('data-bs-theme', savedTheme);
            themeIcon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';

            // Highlight active sidebar item
            const currentPath = window.location.pathname;
            document.querySelectorAll('.sidebar-link, .sidebar-dropdown-item').forEach(link => {
                if (link.href && currentPath.includes(new URL(link.href).pathname.replace(/\/$/, ''))) {
                    link.classList.add('active');
                    
                    // Expand parent dropdown if needed
                    const parentDropdown = link.closest('.sidebar-dropdown');
                    if (parentDropdown) {
                        parentDropdown.classList.add('show');
                    }
                }
            });

            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);

            // Handle sidebar height on window resize
            adjustSidebarHeight();
            window.addEventListener('resize', adjustSidebarHeight);

            // Prevent sidebar scroll when at top/bottom
            const sidebarContent = document.querySelector('.sidebar-content');
            sidebarContent.addEventListener('wheel', function(e) {
                const isScrollingDown = e.deltaY > 0;
                const isAtTop = this.scrollTop === 0;
                const isAtBottom = this.scrollTop + this.clientHeight >= this.scrollHeight - 1;

                if ((isScrollingDown && isAtBottom) || (!isScrollingDown && isAtTop)) {
                    e.preventDefault();
                }
            }, { passive: false });
        });

        // Adjust sidebar height based on window
        function adjustSidebarHeight() {
            const sidebar = document.getElementById('sidebar-wrapper');
            sidebar.style.height = window.innerHeight + 'px';
        }

        // DataTables Configuration
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
                responsive: false,
                pageLength: 10,
                lengthMenu: [
                    [5, 10, 25, 50, -1],
                    [5, 10, 25, 50, "Todos"]
                ],
                dom: '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                buttons: [
                    {
                        extend: 'copy',
                        text: '<i class="fas fa-copy"></i>',
                        titleAttr: 'Copiar',
                        className: 'btn btn-sm btn-outline-secondary'
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i>',
                        titleAttr: 'Excel',
                        className: 'btn btn-sm btn-outline-success'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i>',
                        titleAttr: 'PDF',
                        className: 'btn btn-sm btn-outline-danger'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i>',
                        titleAttr: 'Imprimir',
                        className: 'btn btn-sm btn-outline-info'
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="fas fa-columns"></i>',
                        titleAttr: 'Colunas',
                        className: 'btn btn-sm btn-outline-warning'
                    }
                ]
            });
        }

        // Handle AJAX errors globally
        $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
            hideLoading();
            
            if (jqxhr.status === 401) {
                showToast('error', 'Sessão expirada. Por favor, faça login novamente.');
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else if (jqxhr.status === 403) {
                showToast('error', 'Permissão negada.');
            } else if (jqxhr.status === 404) {
                showToast('error', 'Recurso não encontrado.');
            } else if (jqxhr.status === 422) {
                const errors = jqxhr.responseJSON.errors;
                let errorMessage = '';
                for (const field in errors) {
                    errorMessage += errors[field].join('<br>') + '<br>';
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Erro de Validação',
                    html: errorMessage
                });
            } else if (jqxhr.status === 500) {
                showToast('error', 'Erro interno do servidor.');
            } else {
                showToast('error', 'Ocorreu um erro. Por favor, tente novamente.');
            }
        });

        // AJAX start/stop loading
        $(document).ajaxStart(function() {
            showLoading();
        });

        $(document).ajaxStop(function() {
            hideLoading();
        });

        // Prevent form double submission
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form.tagName === 'FORM') {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processando...';
                    
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = submitBtn.innerHTML.replace('<i class="fas fa-spinner fa-spin me-2"></i>', '');
                    }, 3000);
                }
            }
        });
    </script>

    @stack('scripts')
</body>
</html>