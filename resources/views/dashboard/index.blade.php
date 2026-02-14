@extends('layouts.app')

@section('title', 'Dashboard Avançado - Asset Management')

@section('page-title', 'Dashboard Estratégico')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@push('styles')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<!-- Font Awesome 6 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    /* ===== VARIÁVEIS DE CORES ===== */
    :root {
        --primary: #4361ee;
        --primary-light: #eef2ff;
        --primary-dark: #3730a3;
        --secondary: #8b5cf6;
        --secondary-light: #ede9fe;
        --success: #10b981;
        --success-light: #d1fae5;
        --warning: #f59e0b;
        --warning-light: #fef3c7;
        --danger: #ef4444;
        --danger-light: #fee2e2;
        --info: #3b82f6;
        --info-light: #dbeafe;
        --dark: #1f2937;
        --light: #f9fafb;
        --gray: #6b7280;
        --gray-light: #f3f4f6;
        --border: #e5e7eb;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --shadow-lg: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        --radius-sm: 6px;
        --radius: 12px;
        --radius-lg: 16px;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --font-sans: 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* ===== ESTILOS GLOBAIS ===== */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: var(--font-sans);
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .dashboard {
        padding: 2rem;
        min-height: 100vh;
        position: relative;
    }

    .dashboard-container {
        max-width: 1600px;
        margin: 0 auto;
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-xl);
        padding: 2rem;
        position: relative;
        overflow: hidden;
    }

    .dashboard-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--secondary), var(--success));
    }

    /* ===== HEADER DO DASHBOARD ===== */
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 2px solid var(--border);
        position: relative;
    }

    .dashboard-title-section {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }

    .dashboard-title {
        font-size: 2rem;
        font-weight: 700;
        color: var(--dark);
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .dashboard-title i {
        color: white;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        padding: 0.875rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow-md);
    }

    .dashboard-subtitle {
        color: var(--gray);
        font-size: 0.95rem;
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .dashboard-subtitle i {
        color: var(--primary);
    }

    /* ===== FILTROS DE PERÍODO ===== */
    .period-filter-section {
        background: var(--gray-light);
        padding: 1rem;
        border-radius: var(--radius);
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .period-filter {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .period-btn {
        padding: 0.625rem 1.25rem;
        border: 2px solid transparent;
        background: white;
        color: var(--gray);
        font-weight: 600;
        border-radius: var(--radius-sm);
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.625rem;
        font-size: 0.875rem;
        box-shadow: var(--shadow-sm);
    }

    .period-btn:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .period-btn.active {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        border-color: var(--primary-dark);
    }

    .period-badge {
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        padding: 0.5rem 1.25rem;
        border-radius: 50px;
        font-size: 0.875rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: var(--shadow-md);
    }

    /* ===== KPI CARDS ===== */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .kpi-card {
        background: white;
        border-radius: var(--radius);
        padding: 1.5rem;
        box-shadow: var(--shadow);
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: var(--transition);
        border: 1px solid var(--border);
        position: relative;
        overflow: hidden;
    }

    .kpi-card::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        opacity: 0;
        transition: var(--transition);
    }

    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .kpi-card:hover::after {
        opacity: 1;
    }

    .kpi-icon {
        width: 56px;
        height: 56px;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        box-shadow: var(--shadow-md);
    }

    .kpi-content {
        flex: 1;
    }

    .kpi-value {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 0.25rem;
        color: var(--dark);
    }

    .kpi-label {
        color: var(--gray);
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }

    .kpi-trend {
        font-size: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
        color: var(--success);
        font-weight: 600;
    }

    .kpi-trend.negative {
        color: var(--danger);
    }

    /* ===== STATS CARDS ===== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: var(--radius);
        padding: 1.5rem;
        box-shadow: var(--shadow);
        border-left: 4px solid var(--primary);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .stat-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--gray);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: white;
    }

    .stat-main-value {
        font-size: 2.5rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 0.5rem;
        color: var(--dark);
    }

    .stat-secondary {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: var(--gray);
        font-size: 0.875rem;
    }

    /* ===== PROJECTS SECTION ===== */
    .section-title {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .section-title h2 {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--dark);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .section-title h2 i {
        color: var(--primary);
        background: var(--primary-light);
        padding: 0.5rem;
        border-radius: var(--radius-sm);
    }

    .projects-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .project-card {
        background: white;
        border-radius: var(--radius);
        padding: 1.5rem;
        box-shadow: var(--shadow);
        transition: var(--transition);
        border: 1px solid var(--border);
        position: relative;
    }

    .project-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
        border-color: var(--primary);
    }

    .project-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .project-code {
        font-size: 0.875rem;
        color: var(--primary);
        font-weight: 600;
        background: var(--primary-light);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
    }

    .project-name {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0.5rem;
    }

    .project-stats {
        display: flex;
        gap: 1rem;
        margin: 1rem 0;
    }

    .project-stat-item {
        flex: 1;
        text-align: center;
    }

    .project-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--dark);
        line-height: 1;
        margin-bottom: 0.25rem;
    }

    .project-stat-label {
        font-size: 0.75rem;
        color: var(--gray);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .progress {
        height: 8px;
        border-radius: 4px;
        background: var(--gray-light);
        margin: 1rem 0;
        overflow: hidden;
    }

    .progress-bar {
        background: linear-gradient(90deg, var(--primary), var(--secondary));
        border-radius: 4px;
        transition: width 0.3s ease;
    }

    /* ===== TOP EMPLOYEES ===== */
    .top-employees-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
    }

    .employee-card {
        background: white;
        border-radius: var(--radius);
        padding: 1rem;
        box-shadow: var(--shadow);
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: var(--transition);
        border: 1px solid var(--border);
    }

    .employee-card:hover {
        transform: translateX(4px);
        border-color: var(--success);
        box-shadow: var(--shadow-md);
    }

    .employee-avatar {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 1.25rem;
    }

    .employee-info {
        flex: 1;
    }

    .employee-name {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0.25rem;
    }

    .employee-company {
        font-size: 0.75rem;
        color: var(--gray);
    }

    .employee-assets {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--primary);
    }

    /* ===== FINANCIAL SECTION ===== */
    .financial-summary {
        background: linear-gradient(135deg, #1e293b, #0f172a);
        border-radius: var(--radius);
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
    }

    .financial-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
    }

    .financial-item {
        background: rgba(255, 255, 255, 0.1);
        border-radius: var(--radius);
        padding: 1.5rem;
        backdrop-filter: blur(10px);
        transition: var(--transition);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .financial-item:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-4px);
    }

    .financial-value {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .financial-label {
        font-size: 0.875rem;
        opacity: 0.9;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* ===== TABLES ===== */
    .tables-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .table-container {
        background: white;
        border-radius: var(--radius);
        padding: 1.5rem;
        box-shadow: var(--shadow);
        transition: var(--transition);
    }

    .table-container:hover {
        box-shadow: var(--shadow-lg);
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border);
    }

    .table-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--dark);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .table-title i {
        color: var(--primary);
        background: var(--primary-light);
        padding: 0.5rem;
        border-radius: var(--radius-sm);
    }

    .summary-table {
        width: 100%;
        border-collapse: collapse;
    }

    .summary-table th {
        background: var(--gray-light);
        color: var(--gray);
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.875rem 1rem;
        text-align: left;
    }

    .summary-table td {
        padding: 0.875rem 1rem;
        border-bottom: 1px solid var(--border);
        color: var(--dark);
        font-size: 0.875rem;
    }

    .summary-table tr:hover td {
        background: var(--gray-light);
    }

    /* ===== ALERTS ===== */
    .alerts-section {
        background: white;
        border-radius: var(--radius);
        padding: 1.5rem;
        box-shadow: var(--shadow);
        margin-bottom: 2rem;
    }

    .alerts-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border);
    }

    .alerts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1rem;
    }

    .alert-item {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.25rem;
        background: var(--gray-light);
        border-radius: var(--radius);
        border-left: 4px solid;
        transition: var(--transition);
    }

    .alert-item:hover {
        transform: translateX(4px);
        box-shadow: var(--shadow-md);
    }

    .alert-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        color: white;
    }

    .alert-content {
        flex: 1;
    }

    .alert-title {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0.25rem;
        font-size: 0.95rem;
    }

    .alert-message {
        font-size: 0.875rem;
        color: var(--gray);
        margin-bottom: 0.5rem;
    }

    .alert-link {
        color: var(--primary);
        font-size: 0.75rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* ===== LOADING ===== */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .loading-spinner {
        width: 48px;
        height: 48px;
        border: 3px solid var(--border);
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* ===== ANIMATIONS ===== */
    .fade-in {
        animation: fadeIn 0.5s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .slide-in {
        animation: slideIn 0.5s ease-out;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateX(-20px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .pulse {
        animation: pulse 0.3s ease-in-out;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(0.95); }
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1200px) {
        .dashboard { padding: 1rem; }
        .dashboard-container { padding: 1.5rem; }
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .projects-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 768px) {
        .dashboard { padding: 0.5rem; }
        .dashboard-container { padding: 1rem; }
        .stats-grid { grid-template-columns: 1fr; }
        .projects-grid { grid-template-columns: 1fr; }
        .tables-grid { grid-template-columns: 1fr; }
        .period-filter-section { flex-direction: column; }
        .period-filter { width: 100%; }
        .period-btn { flex: 1; }
    }

    /* ===== DARK MODE ===== */
    [data-bs-theme="dark"] {
        --dark: #e2e8f0;
        --light: #1e293b;
        --gray: #94a3b8;
        --gray-light: #334155;
        --border: #475569;
    }

    [data-bs-theme="dark"] .dashboard-container,
    [data-bs-theme="dark"] .stat-card,
    [data-bs-theme="dark"] .project-card,
    [data-bs-theme="dark"] .table-container,
    [data-bs-theme="dark"] .alerts-section,
    [data-bs-theme="dark"] .employee-card {
        background: #1e293b;
        border-color: #334155;
    }

    [data-bs-theme="dark"] .period-filter {
        background: #334155;
    }

    [data-bs-theme="dark"] .period-btn:not(.active) {
        background: #1e293b;
        color: #cbd5e1;
    }

    [data-bs-theme="dark"] .summary-table th {
        background: #334155;
        color: #cbd5e1;
    }

    [data-bs-theme="dark"] .alert-item {
        background: #334155;
    }
</style>
@endpush

@section('content')
<div class="dashboard">
    <div class="dashboard-container fade-in">
        <!-- Header -->
        <div class="dashboard-header">
            <div class="dashboard-title-section">
                <div>
                    <h1 class="dashboard-title">
                        <i class="fas fa-chart-pie"></i>
                        Dashboard Estratégico
                    </h1>
                    <div class="dashboard-subtitle">
                        <i class="fas fa-calendar-alt"></i>
                        Última atualização: <span id="lastUpdateTime">agora mesmo</span>
                        <span class="mx-2">•</span>
                        <i class="fas fa-sync-alt"></i>
                        Auto-refresh a cada 2 minutos
                    </div>
                </div>
            </div>
            <div class="period-badge">
                <i class="fas fa-clock"></i>
                <span id="periodLabel">Este Mês</span>
            </div>
        </div>

        <!-- Filtros de Período -->
        <div class="period-filter-section fade-in">
            <div class="period-filter">
                <button class="period-btn active" data-period="today">
                    <i class="fas fa-sun"></i>
                    Hoje
                </button>
                <button class="period-btn" data-period="week">
                    <i class="fas fa-calendar-week"></i>
                    Esta Semana
                </button>
                <button class="period-btn" data-period="month">
                    <i class="fas fa-calendar-alt"></i>
                    Este Mês
                </button>
                <button class="period-btn" data-period="year">
                    <i class="fas fa-calendar"></i>
                    Este Ano
                </button>
                <button class="period-btn" data-period="all">
                    <i class="fas fa-infinity"></i>
                    Todo o Período
                </button>
            </div>
            <button class="btn btn-primary" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt me-2"></i>
                Atualizar
            </button>
        </div>

        <!-- KPIs Cards -->
        <div class="kpi-grid fade-in" style="animation-delay: 0.1s;">
            <div class="kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg, #4361ee, #3730a3);">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Projetos Ativos</div>
                    <div class="kpi-value" id="activeProjects">0</div>
                    <div class="kpi-trend" id="activeProjectsTrend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+12%</span>
                    </div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <i class="fas fa-cubes"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Ativos por Projeto</div>
                    <div class="kpi-value" id="assetsPerProject">0</div>
                    <div class="kpi-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+8%</span>
                    </div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Valor Médio Ativo</div>
                    <div class="kpi-value" id="avgAssetValue">0 MT</div>
                    <div class="kpi-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+5%</span>
                    </div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Taxa de Conclusão</div>
                    <div class="kpi-value" id="completionRate">0%</div>
                    <div class="kpi-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+8%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards Principais -->
        <div class="stats-grid fade-in" style="animation-delay: 0.2s;">
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Total de Ativos</span>
                    <div class="stat-icon-wrapper" style="background: linear-gradient(135deg, #4361ee, #3730a3);">
                        <i class="fas fa-box"></i>
                    </div>
                </div>
                <div class="stat-main-value" id="totalAssets">0</div>
                <div class="stat-secondary">
                    <span><i class="fas fa-calendar-alt me-1"></i><span id="periodAssets">0</span> este mês</span>
                    <span class="badge bg-success" id="processCompleteBadge">100% completo</span>
                </div>
            </div>
            <div class="stat-card" style="border-left-color: var(--success);">
                <div class="stat-header">
                    <span class="stat-title">Projetos</span>
                    <div class="stat-icon-wrapper" style="background: linear-gradient(135deg, #10b981, #059669);">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                </div>
                <div class="stat-main-value" id="totalProjects">0</div>
                <div class="stat-secondary">
                    <span><i class="fas fa-check-circle me-1"></i><span id="completedProjects">0</span> concluídos</span>
                    <span><i class="fas fa-spinner me-1"></i><span id="activeProjectsCount">0</span> ativos</span>
                </div>
            </div>
            <div class="stat-card" style="border-left-color: var(--warning);">
                <div class="stat-header">
                    <span class="stat-title">Ativos Atribuídos</span>
                    <div class="stat-icon-wrapper" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                        <i class="fas fa-user-tag"></i>
                    </div>
                </div>
                <div class="stat-main-value" id="assignedAssets">0</div>
                <div class="stat-secondary">
                    <span><i class="fas fa-box me-1"></i><span id="availableAssets">0</span> disponíveis</span>
                    <span><i class="fas fa-users me-1"></i><span id="employeesWithAssets">0</span> colaboradores</span>
                </div>
            </div>
            <div class="stat-card" style="border-left-color: var(--danger);">
                <div class="stat-header">
                    <span class="stat-title">Valor Total</span>
                    <div class="stat-icon-wrapper" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
                <div class="stat-main-value" id="totalValue">0 MT</div>
                <div class="stat-secondary">
                    <span><i class="fas fa-calendar-alt me-1"></i><span id="periodValue">0 MT</span> este mês</span>
                    <span><i class="fas fa-chart-line me-1"></i><span id="valueGrowth">+15%</span></span>
                </div>
            </div>
        </div>

        <!-- Seção de Projetos (Centro das Atenções) -->
        <div class="section-title fade-in" style="animation-delay: 0.3s;">
            <h2>
                <i class="fas fa-project-diagram"></i>
                Projetos em Destaque
            </h2>
            <a href="{{ route('projects.index') }}" class="btn btn-outline-primary">
                Ver todos os projetos
                <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>

        <div class="projects-grid fade-in" style="animation-delay: 0.3s;" id="projectsGrid">
            <!-- Carregamento inicial -->
            <div class="col-12">
                <div class="dashboard-loading">
                    <div class="loading-spinner"></div>
                </div>
            </div>
        </div>

        <!-- Gráficos Avançados -->
        <div class="row mt-4 fade-in" style="animation-delay: 0.4s;">
            <div class="col-xl-6 mb-4">
                <div class="chart-container">
                    <div class="chart-header">
                        <h3 class="chart-title">
                            <i class="fas fa-chart-pie"></i>
                            Distribuição de Projetos
                        </h3>
                    </div>
                    <div class="chart-wrapper" style="height: 350px;">
                        <canvas id="projectStatusChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 mb-4">
                <div class="chart-container">
                    <div class="chart-header">
                        <h3 class="chart-title">
                            <i class="fas fa-chart-line"></i>
                            Evolução de Ativos por Projeto
                        </h3>
                    </div>
                    <div class="chart-wrapper" style="height: 350px;">
                        <canvas id="projectAssetsEvolutionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Empresas e Top Colaboradores -->
        <div class="row fade-in" style="animation-delay: 0.5s;">
            <div class="col-xl-6 mb-4">
                <div class="table-container h-100">
                    <div class="table-header">
                        <h3 class="table-title">
                            <i class="fas fa-building"></i>
                            Empresas com Mais Ativos
                        </h3>
                        <span class="table-badge" id="topCompaniesCount">Top 10</span>
                    </div>
                    <div class="table-responsive">
                        <table class="summary-table">
                            <thead>
                                <tr>
                                    <th>Empresa</th>
                                    <th>Província</th>
                                    <th>Ativos</th>
                                    <th>Valor Total</th>
                                </tr>
                            </thead>
                            <tbody id="topCompaniesBody">
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <div class="dashboard-loading">
                                            <div class="loading-spinner"></div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 mb-4">
                <div class="table-container h-100">
                    <div class="table-header">
                        <h3 class="table-title">
                            <i class="fas fa-trophy"></i>
                            Top Colaboradores
                        </h3>
                        <span class="table-badge" id="topEmployeesCount">Mais ativos</span>
                    </div>
                    <div class="top-employees-grid" id="topEmployeesGrid">
                        <!-- Dados serão carregados via AJAX -->
                        <div class="col-12">
                            <div class="dashboard-loading">
                                <div class="loading-spinner"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumo Financeiro por Projeto -->
        <div class="financial-summary fade-in" style="animation-delay: 0.6s;">
            <div class="financial-header">
                <h3 class="financial-title">
                    <i class="fas fa-chart-pie"></i>
                    Resumo Financeiro por Projeto
                </h3>
            </div>
            <div class="financial-grid" id="financialProjectsGrid">
                <!-- Dados serão carregados via AJAX -->
                <div class="col-12">
                    <div class="dashboard-loading">
                        <div class="loading-spinner"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabelas de Ativos Recentes e Garantias -->
        <div class="tables-grid fade-in" style="animation-delay: 0.7s;">
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">
                        <i class="fas fa-history"></i>
                        Ativos Recentes
                    </h3>
                    <span class="table-badge" id="recentAssetsCount">0 ativos</span>
                </div>
                <div class="table-responsive">
                    <table class="summary-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nome</th>
                                <th>Projeto</th>
                                <th>Status</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody id="recentAssetsBody">
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="dashboard-loading">
                                        <div class="loading-spinner"></div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">
                        <i class="fas fa-calendar-times"></i>
                        Garantias a Expirar
                    </h3>
                    <span class="table-badge bg-warning text-dark" id="expiringCount">0 a expirar</span>
                </div>
                <div class="table-responsive">
                    <table class="summary-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nome</th>
                                <th>Projeto</th>
                                <th>Expira em</th>
                                <th>Dias</th>
                            </tr>
                        </thead>
                        <tbody id="expiringBody">
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="dashboard-loading">
                                        <div class="loading-spinner"></div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Alertas Inteligentes -->
        <div class="alerts-section fade-in" style="animation-delay: 0.8s;">
            <div class="alerts-header">
                <h3 class="alerts-title">
                    <i class="fas fa-bell"></i>
                    Alertas Inteligentes
                </h3>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-danger" id="criticalAlertsCount">0</span>
                    <span class="badge bg-warning" id="warningAlertsCount">0</span>
                    <span class="badge bg-info" id="infoAlertsCount">0</span>
                </div>
            </div>
            <div class="alerts-grid" id="alertsGrid">
                <!-- Alertas serão carregados via AJAX -->
                <div class="col-12">
                    <div class="dashboard-loading">
                        <div class="loading-spinner"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ===== CONFIGURAÇÃO GLOBAL =====
const CONFIG = {
    refreshInterval: 2 * 60 * 1000, // 2 minutos
    animationDuration: 300,
    currencySymbol: 'MT',
    dateFormat: 'dd/MM/yyyy',
    colors: {
        primary: '#4361ee',
        success: '#10b981',
        warning: '#f59e0b',
        danger: '#ef4444',
        info: '#3b82f6',
        secondary: '#8b5cf6',
        purple: '#8b5cf6',
        pink: '#ec4899',
        indigo: '#6366f1',
        cyan: '#06b6d4'
    }
};

// ===== VARIÁVEIS GLOBAIS =====
let currentPeriod = 'month';
let projectStatusChart = null;
let projectAssetsEvolutionChart = null;
let lastLoadTime = null;
let refreshTimer = null;

// ===== INICIALIZAÇÃO =====
$(document).ready(function() {
    console.log('🚀 Inicializando Dashboard Avançado...');
    
    // Configurar eventos
    setupPeriodFilters();
    
    // Carregar dados iniciais
    loadAllDashboardData();
    
    // Configurar auto-refresh
    startAutoRefresh();
    
    // Configurar listeners de visibilidade
    setupVisibilityListener();
    
    // Inicializar tooltips
    initTooltips();
});

// ===== SETUP DE EVENTOS =====
function setupPeriodFilters() {
    $('.period-btn').off('click').on('click', function(e) {
        e.preventDefault();
        
        const period = $(this).data('period');
        
        // Atualizar UI
        $('.period-btn').removeClass('active');
        $(this).addClass('active');
        
        // Atualizar período atual
        currentPeriod = period;
        
        // Atualizar label
        updatePeriodLabel();
        
        // Animar botão
        $(this).addClass('pulse');
        setTimeout(() => $(this).removeClass('pulse'), 300);
        
        // Recarregar dados
        loadAllDashboardData(true);
    });
}

function setupVisibilityListener() {
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'visible') {
            console.log('👁️ Janela visível, verificando atualização...');
            const now = new Date();
            if (lastLoadTime && (now - lastLoadTime) > 5 * 60 * 1000) {
                loadAllDashboardData();
            }
        }
    });
}

function startAutoRefresh() {
    if (refreshTimer) {
        clearInterval(refreshTimer);
    }
    
    refreshTimer = setInterval(function() {
        if (document.visibilityState === 'visible') {
            console.log('🔄 Auto-refresh em execução...');
            loadAllDashboardData();
        }
    }, CONFIG.refreshInterval);
}

function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// ===== CARREGAMENTO DE DADOS =====
function loadAllDashboardData(showLoader = true) {
    console.log(`📊 Carregando todos os dados do dashboard (período: ${currentPeriod})...`);
    lastLoadTime = new Date();
    
    if (showLoader) {
        showLoadingOverlay();
    }
    
    // Carregar KPIs primeiro
    loadKPIs().then(() => {
        return Promise.all([
            loadStatistics(),
            loadChartsData(),
            loadAlerts(),
            loadFinancialSummary()
        ]);
    }).then(() => {
        console.log('✅ Todos os dados carregados com sucesso');
        updateLastUpdateTime();
        hideLoadingOverlay();
        showToast('success', 'Dashboard atualizado com sucesso!');
    }).catch((error) => {
        console.error('❌ Erro ao carregar dados:', error);
        hideLoadingOverlay();
        showToast('error', 'Erro ao carregar dados do dashboard');
    });
}

function loadKPIs() {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: '{{ route("dashboard.kpis") }}',
            method: 'GET',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    updateKPIs(response.data);
                    resolve(response.data);
                } else {
                    reject(new Error(response.message));
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Erro ao carregar KPIs:', error);
                reject(error);
            }
        });
    });
}

function loadStatistics() {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: '{{ route("dashboard.statistics") }}',
            method: 'GET',
            data: { period: currentPeriod, _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    updateStatistics(response.data);
                    resolve(response.data);
                } else {
                    reject(new Error(response.message));
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Erro ao carregar estatísticas:', error);
                reject(error);
            }
        });
    });
}

function loadChartsData() {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: '{{ route("dashboard.charts") }}',
            method: 'GET',
            data: { period: currentPeriod, _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    updateCharts(response.data);
                    resolve(response.data);
                } else {
                    reject(new Error(response.message));
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Erro ao carregar gráficos:', error);
                reject(error);
            }
        });
    });
}

function loadAlerts() {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: '{{ route("dashboard.alerts") }}',
            method: 'GET',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    updateAlerts(response.data);
                    resolve(response.data);
                } else {
                    reject(new Error(response.message));
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Erro ao carregar alertas:', error);
                reject(error);
            }
        });
    });
}

function loadFinancialSummary() {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: '{{ route("dashboard.financial") }}',
            method: 'GET',
            data: { period: currentPeriod, _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    updateFinancialSummary(response.data);
                    resolve(response.data);
                } else {
                    reject(new Error(response.message));
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Erro ao carregar resumo financeiro:', error);
                reject(error);
            }
        });
    });
}

// ===== ATUALIZAÇÃO DA UI =====
function updateKPIs(kpis) {
    console.log('🎯 Atualizando KPIs...');
    
    // Projetos Ativos
    $('#activeProjects').text(formatNumber(kpis.active_projects || 0));
    
    // Ativos por Projeto
    $('#assetsPerProject').text(formatNumber(kpis.assets_per_project?.value || 0));
    
    // Valor Médio do Ativo
    $('#avgAssetValue').text(formatCurrency(kpis.avg_asset_value?.value || 0));
    
    // Taxa de Conclusão
    $('#completionRate').text(formatNumber(kpis.project_completion_rate?.value || 0) + '%');
}

function updateStatistics(data) {
    console.log('📈 Atualizando estatísticas...');
    
    const stats = data.assets_stats || {};
    const projectsStats = data.projects_stats || {};
    const counts = data.counts || {};
    
    // Stats Cards Principais
    $('#totalAssets').text(formatNumber(stats.total || 0));
    $('#totalProjects').text(formatNumber(projectsStats.total || 0));
    $('#assignedAssets').text(formatNumber(stats.atribuido || 0));
    $('#totalValue').text(formatCurrency(stats.valor_total || 0));
    
    // Valores secundários
    $('#periodAssets').text(formatNumber(stats.period_total || 0));
    $('#periodValue').text(formatCurrency(stats.valor_periodo || 0));
    $('#availableAssets').text(formatNumber(stats.disponivel || 0));
    $('#completedProjects').text(formatNumber(projectsStats.concluidos || 0));
    $('#activeProjectsCount').text(formatNumber(projectsStats.ativos || 0));
    
    // Process Status
    const completePercentage = stats.total > 0 
        ? ((stats.process_completo || 0) / stats.total * 100).toFixed(1) 
        : 0;
    $('#processCompleteBadge').text(`${completePercentage}% completo`);
    
    // Atualizar projetos em destaque
    updateProjectsDisplay(data.top_projects_by_value || [], data.top_projects_by_assets || []);
    
    // Atualizar empresas
    updateTopCompanies(data.top_companies || []);
    
    // Atualizar top colaboradores
    updateTopEmployees(data.top_employees || []);
    
    // Atualizar tabelas
    updateRecentAssetsTable(data.recent_assets || []);
    updateExpiringTable(data.expiring_warranty || []);
}

function updateProjectsDisplay(topProjectsByValue, topProjectsByAssets) {
    const grid = $('#projectsGrid');
    grid.empty();
    
    if (!topProjectsByValue || topProjectsByValue.length === 0) {
        grid.html(`
            <div class="col-12">
                <div class="empty-state">
                    <i class="fas fa-project-diagram fa-3x"></i>
                    <h4>Nenhum projeto encontrado</h4>
                    <p>Comece criando um novo projeto</p>
                    <a href="{{ route('projects.index') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-plus me-2"></i>Criar Projeto
                    </a>
                </div>
            </div>
        `);
        return;
    }
    
    // Combinar e mostrar top 4 projetos
    const projects = topProjectsByValue.slice(0, 4);
    
    projects.forEach((project, index) => {
        const budgetUsage = project.project_budget > 0 
            ? ((project.assets_total_value || 0) / project.project_budget * 100).toFixed(1)
            : 0;
        
        const card = `
            <div class="project-card fade-in" style="animation-delay: ${index * 0.1}s">
                <div class="project-header">
                    <span class="project-code">${project.code || 'N/A'}</span>
                    <span class="badge bg-${getProjectStatusColor(project.status)}">
                        ${project.status || 'Ativo'}
                    </span>
                </div>
                <h4 class="project-name">${project.name}</h4>
                <div class="project-stats">
                    <div class="project-stat-item">
                        <div class="project-stat-value">${formatNumber(project.assets_count || 0)}</div>
                        <div class="project-stat-label">Ativos</div>
                    </div>
                    <div class="project-stat-item">
                        <div class="project-stat-value">${formatCurrency(project.assets_total_value || 0)}</div>
                        <div class="project-stat-label">Valor</div>
                    </div>
                    <div class="project-stat-item">
                        <div class="project-stat-value">${formatNumber(project.requests_count || 0)}</div>
                        <div class="project-stat-label">Requisições</div>
                    </div>
                </div>
                <div class="mb-2 d-flex justify-content-between">
                    <span class="small">Orçamento: ${formatCurrency(project.project_budget || 0)}</span>
                    <span class="small fw-bold">${budgetUsage}% utilizado</span>
                </div>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: ${budgetUsage}%"></div>
                </div>
                <div class="mt-3 text-end">
                    <a href="{{ route('projects.index', '') }}" class="btn btn-sm btn-outline-primary">
                        Ver detalhes
                        <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        `;
        grid.append(card);
    });
}

function updateTopCompanies(companies) {
    const tbody = $('#topCompaniesBody');
    const countBadge = $('#topCompaniesCount');
    
    tbody.empty();
    
    if (!companies || companies.length === 0) {
        countBadge.text('0 empresas');
        tbody.html(`
            <tr>
                <td colspan="4" class="text-center py-4">
                    <div class="text-muted">
                        <i class="fas fa-building fa-2x mb-2"></i>
                        <p>Nenhuma empresa encontrada</p>
                    </div>
                </td>
            </tr>
        `);
        return;
    }
    
    countBadge.text(`Top ${companies.length}`);
    
    companies.slice(0, 5).forEach((company, index) => {
        const row = `
            <tr class="fade-in" style="animation-delay: ${index * 0.05}s">
                <td>
                    <div class="fw-bold">${company.name || 'N/A'}</div>
                    <small class="text-muted">${company.employees_count || 0} colaboradores</small>
                </td>
                <td>${company.province || 'N/A'}</td>
                <td>
                    <span class="badge bg-primary">${formatNumber(company.assets_count || 0)}</span>
                </td>
                <td class="fw-bold">${formatCurrency(company.assets_total_value || 0)}</td>
            </tr>
        `;
        tbody.append(row);
    });
}

function updateTopEmployees(employees) {
    const grid = $('#topEmployeesGrid');
    const countBadge = $('#topEmployeesCount');
    
    grid.empty();
    
    if (!employees || employees.length === 0) {
        countBadge.text('0 colaboradores');
        grid.html(`
            <div class="col-12">
                <div class="text-center py-4">
                    <i class="fas fa-users fa-2x text-muted mb-2"></i>
                    <p class="text-muted">Nenhum colaborador encontrado</p>
                </div>
            </div>
        `);
        return;
    }
    
    countBadge.text(`Top ${employees.length}`);
    
    employees.slice(0, 5).forEach((employee, index) => {
        const initials = employee.name
            .split(' ')
            .map(n => n[0])
            .slice(0, 2)
            .join('')
            .toUpperCase();
        
        const card = `
            <div class="employee-card fade-in" style="animation-delay: ${index * 0.05}s">
                <div class="employee-avatar">
                    ${initials}
                </div>
                <div class="employee-info">
                    <div class="employee-name">${employee.name}</div>
                    <div class="employee-company">${employee.company_name || 'N/A'}</div>
                    <div class="mt-2">
                        <span class="employee-assets">
                            <i class="fas fa-box me-1"></i>
                            ${formatNumber(employee.assets_count || 0)} ativos
                        </span>
                        <span class="ms-2 text-muted">
                            ${formatCurrency(employee.assets_total_value || 0)}
                        </span>
                    </div>
                </div>
            </div>
        `;
        grid.append(card);
    });
}

function updateRecentAssetsTable(assets) {
    const tbody = $('#recentAssetsBody');
    const countBadge = $('#recentAssetsCount');
    
    tbody.empty();
    
    if (!assets || assets.length === 0) {
        countBadge.text('0 ativos');
        tbody.html(`
            <tr>
                <td colspan="5" class="text-center py-4">
                    <div class="text-muted">
                        <i class="fas fa-box-open fa-2x mb-2"></i>
                        <p>Nenhum ativo recente</p>
                    </div>
                </td>
            </tr>
        `);
        return;
    }
    
    countBadge.text(`${assets.length} ativos`);
    
    assets.slice(0, 5).forEach((asset, index) => {
        const statusBadge = getStatusBadge(asset.asset_status);
        const row = `
            <tr class="fade-in" style="animation-delay: ${index * 0.05}s">
                <td><span class="fw-bold text-primary">${asset.code || 'N/A'}</span></td>
                <td>${asset.name || 'N/A'}</td>
                <td>
                    <span class="badge bg-info">${asset.project_name || 'N/A'}</span>
                </td>
                <td>${statusBadge}</td>
                <td class="fw-bold">${formatCurrency(asset.total_value || 0)}</td>
            </tr>
        `;
        tbody.append(row);
    });
}

function updateExpiringTable(assets) {
    const tbody = $('#expiringBody');
    const countBadge = $('#expiringCount');
    
    tbody.empty();
    
    if (!assets || assets.length === 0) {
        countBadge.text('0 a expirar');
        tbody.html(`
            <tr>
                <td colspan="5" class="text-center py-4">
                    <div class="text-muted">
                        <i class="fas fa-calendar-check fa-2x mb-2"></i>
                        <p>Nenhuma garantia a expirar</p>
                    </div>
                </td>
            </tr>
        `);
        return;
    }
    
    countBadge.text(`${assets.length} a expirar`);
    
    assets.slice(0, 5).forEach((asset, index) => {
        const days = asset.days_left || 0;
        const urgencyColor = getUrgencyColor(days);
        
        const row = `
            <tr class="fade-in" style="animation-delay: ${index * 0.05}s">
                <td><span class="fw-bold text-primary">${asset.code || 'N/A'}</span></td>
                <td>${asset.name || 'N/A'}</td>
                <td>
                    <span class="badge bg-info">${asset.project_name || 'N/A'}</span>
                </td>
                <td>${asset.warranty_expiry || 'N/A'}</td>
                <td>
                    <span class="badge bg-${urgencyColor}">
                        ${days > 0 ? days + ' dias' : 'Expirado'}
                    </span>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

function updateFinancialSummary(data) {
    console.log('💰 Atualizando resumo financeiro...');
    
    const grid = $('#financialProjectsGrid');
    grid.empty();
    
    const projects = data.by_project || [];
    
    if (projects.length === 0) {
        grid.html(`
            <div class="col-12">
                <div class="financial-item text-center">
                    <i class="fas fa-chart-line fa-3x mb-3 opacity-50"></i>
                    <p class="mb-0">Nenhum dado financeiro disponível</p>
                </div>
            </div>
        `);
        return;
    }
    
    // Mostrar top 4 projetos financeiros
    projects.slice(0, 4).forEach((project, index) => {
        const item = `
            <div class="financial-item fade-in" style="animation-delay: ${index * 0.1}s">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-primary">${project.code || 'N/A'}</span>
                        <h5 class="mt-2 mb-0 text-white">${project.name}</h5>
                    </div>
                    <span class="badge bg-${project.budget_usage_percentage > 80 ? 'warning' : 'success'}">
                        ${project.budget_usage_percentage || 0}% usado
                    </span>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <small class="opacity-75">Orçamento</small>
                        <div class="fw-bold">${formatCurrency(project.project_budget || 0)}</div>
                    </div>
                    <div class="col-6">
                        <small class="opacity-75">Gasto</small>
                        <div class="fw-bold">${formatCurrency(project.assets_total_value || 0)}</div>
                    </div>
                    <div class="col-6">
                        <small class="opacity-75">Restante</small>
                        <div class="fw-bold ${project.remaining_budget < 0 ? 'text-danger' : ''}">
                            ${formatCurrency(project.remaining_budget || 0)}
                        </div>
                    </div>
                    <div class="col-6">
                        <small class="opacity-75">Ativos</small>
                        <div class="fw-bold">${formatNumber(project.assets_count || 0)}</div>
                    </div>
                </div>
            </div>
        `;
        grid.append(item);
    });
}

function updateAlerts(data) {
    const container = $('#alertsGrid');
    const criticalCount = $('#criticalAlertsCount');
    const warningCount = $('#warningAlertsCount');
    const infoCount = $('#infoAlertsCount');
    
    container.empty();
    
    if (!data.alerts || data.alerts.length === 0) {
        criticalCount.text('0');
        warningCount.text('0');
        infoCount.text('0');
        container.html(`
            <div class="col-12">
                <div class="alert alert-success border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle fa-2x text-success me-3"></i>
                        <div>
                            <h6 class="mb-1">Tudo em ordem!</h6>
                            <p class="mb-0 text-muted">Não há alertas no momento</p>
                        </div>
                    </div>
                </div>
            </div>
        `);
        return;
    }
    
    criticalCount.text(data.critical_count || 0);
    warningCount.text(data.warning_count || 0);
    infoCount.text(data.info_count || 0);
    
    data.alerts.slice(0, 4).forEach((alert, index) => {
        const alertHtml = `
            <div class="alert-item ${alert.type} fade-in" style="animation-delay: ${index * 0.1}s">
                <div class="alert-icon ${alert.type}">
                    <i class="${alert.icon}"></i>
                </div>
                <div class="alert-content">
                    <h6 class="alert-title">${alert.title}</h6>
                    <p class="alert-message">${alert.message}</p>
                    ${alert.link ? `
                        <a href="${alert.link}" class="alert-link">
                            Ver detalhes
                            <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    ` : ''}
                </div>
            </div>
        `;
        container.append(alertHtml);
    });
}

function updateCharts(data) {
    console.log('📊 Atualizando gráficos...');
    
    // Gráfico de Distribuição de Projetos
    if (projectStatusChart) {
        projectStatusChart.destroy();
    }
    
    const statusData = data.project_status_distribution || {};
    createProjectStatusChart(statusData);
    
    // Gráfico de Evolução de Ativos por Projeto
    if (projectAssetsEvolutionChart) {
        projectAssetsEvolutionChart.destroy();
    }
    
    const evolutionData = data.assets_evolution_by_project || {};
    createProjectAssetsEvolutionChart(evolutionData);
}

function createProjectStatusChart(data) {
    const ctx = document.getElementById('projectStatusChart');
    if (!ctx) return;
    
    const labels = Object.keys(data);
    const values = Object.values(data);
    
    projectStatusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: [
                    CONFIG.colors.success,
                    CONFIG.colors.primary,
                    CONFIG.colors.warning,
                    CONFIG.colors.danger,
                    CONFIG.colors.info
                ],
                borderWidth: 3,
                borderColor: 'white',
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        font: { size: 12, weight: 500 }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.raw / total) * 100).toFixed(1);
                            return `${context.label}: ${context.raw} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '70%',
            animation: {
                animateScale: true,
                animateRotate: true,
                duration: 1000,
                easing: 'easeOutQuart'
            }
        }
    });
}

function createProjectAssetsEvolutionChart(data) {
    const ctx = document.getElementById('projectAssetsEvolutionChart');
    if (!ctx) return;
    
    // Preparar dados para o gráfico
    const projects = Object.keys(data).slice(0, 5);
    const months = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'];
    
    const datasets = projects.map((project, index) => {
        const projectData = data[project] || [];
        const monthData = months.map((_, monthIndex) => {
            const found = projectData.find(d => d.month === monthIndex + 1);
            return found ? found.total : 0;
        });
        
        return {
            label: project,
            data: monthData,
            borderColor: Object.values(CONFIG.colors)[index % Object.values(CONFIG.colors).length],
            backgroundColor: 'transparent',
            borderWidth: 2,
            pointRadius: 3,
            pointHoverRadius: 6,
            tension: 0.4
        };
    });
    
    projectAssetsEvolutionChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 20,
                        font: { size: 11 }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: ${context.parsed.y} ativos`;
                        }
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            }
        }
    });
}

// ===== FUNÇÕES AUXILIARES =====
function formatCurrency(value) {
    if (isNaN(value) || value === null) value = 0;
    return new Intl.NumberFormat('pt-PT', {
        style: 'currency',
        currency: 'MZN',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
}

function formatNumber(value) {
    if (isNaN(value) || value === null) value = 0;
    return new Intl.NumberFormat('pt-PT').format(value);
}

function getStatusBadge(status) {
    const badges = {
        'disponivel': { class: 'bg-success', icon: 'fas fa-check-circle', text: 'Disponível' },
        'atribuido': { class: 'bg-primary', icon: 'fas fa-user-tag', text: 'Atribuído' },
        'manutencao': { class: 'bg-warning', icon: 'fas fa-tools', text: 'Manutenção' },
        'inoperacional': { class: 'bg-danger', icon: 'fas fa-exclamation-triangle', text: 'Inoperacional' },
        'abatido': { class: 'bg-secondary', icon: 'fas fa-trash-alt', text: 'Abatido' }
    };
    
    const config = badges[status] || { class: 'bg-dark', icon: 'fas fa-question-circle', text: status || 'Desconhecido' };
    
    return `<span class="badge ${config.class} px-3 py-2"><i class="${config.icon} me-1"></i>${config.text}</span>`;
}

function getProjectStatusColor(status) {
    const colors = {
        'ativo': 'success',
        'concluido': 'primary',
        'suspenso': 'warning',
        'cancelado': 'danger'
    };
    return colors[status] || 'secondary';
}

function getUrgencyColor(days) {
    if (days <= 0) return 'danger';
    if (days <= 7) return 'danger';
    if (days <= 14) return 'warning';
    if (days <= 30) return 'info';
    return 'success';
}

function updatePeriodLabel() {
    const labels = {
        'today': 'Hoje',
        'week': 'Esta Semana',
        'month': 'Este Mês',
        'year': 'Este Ano',
        'all': 'Todo o Período'
    };
    $('#periodLabel').html(`<i class="fas fa-calendar-alt me-1"></i>${labels[currentPeriod] || 'Este Mês'}`);
}

function updateLastUpdateTime() {
    if (lastLoadTime) {
        const now = new Date();
        const diff = Math.floor((now - lastLoadTime) / 1000);
        
        let timeText;
        if (diff < 60) timeText = `${diff} segundos atrás`;
        else if (diff < 3600) timeText = `${Math.floor(diff / 60)} minutos atrás`;
        else timeText = `${Math.floor(diff / 3600)} horas atrás`;
        
        $('#lastUpdateTime').text(timeText);
    }
}

// ===== LOADING STATES =====
function showLoadingOverlay() {
    if ($('.loading-overlay').length === 0) {
        $('body').append(`
            <div class="loading-overlay fade-in">
                <div class="text-center">
                    <div class="loading-spinner mb-3"></div>
                    <p class="text-muted">Carregando dados do período selecionado...</p>
                </div>
            </div>
        `);
    }
}

function hideLoadingOverlay() {
    $('.loading-overlay').fadeOut(300, function() {
        $(this).remove();
    });
}

function showToast(type, message) {
    Swal.fire({
        icon: type,
        title: message,
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
}

// ===== FUNÇÕES DE CONTROLE =====
function refreshDashboard() {
    console.log('🔄 Atualização manual solicitada...');
    loadAllDashboardData(true);
}

// ===== EXPORTAÇÃO DE DADOS =====
function exportChart(chartId) {
    const canvas = document.getElementById(chartId);
    if (!canvas) {
        showToast('error', 'Gráfico não encontrado');
        return;
    }
    
    try {
        const link = document.createElement('a');
        const filename = `${chartId}-${currentPeriod}-${new Date().toISOString().split('T')[0]}.png`;
        
        link.download = filename;
        link.href = canvas.toDataURL('image/png', 1.0);
        link.click();
        
        showToast('success', 'Gráfico exportado com sucesso!');
    } catch (error) {
        console.error('Erro ao exportar gráfico:', error);
        showToast('error', 'Erro ao exportar gráfico');
    }
}

// ===== INICIALIZAÇÃO ADICIONAL =====
$(document).on('click', '.alert-link', function(e) {
    e.preventDefault();
    const url = $(this).attr('href');
    if (url) {
        window.location.href = url;
    }
});

console.log('✅ Dashboard Avançado inicializado com sucesso!');
</script>
@endpush