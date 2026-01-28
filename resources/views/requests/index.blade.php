@extends('layouts.app')

@section('title', 'Gestão de Requisições')
@section('page-title', 'Requisições')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Requisições</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Gestão de Requisições</h1>
        <p class="mb-0 text-muted">Gerir requisições internas e externas</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-info" id="btnStatistics">
            <i class="fas fa-chart-pie me-2"></i>Estatísticas
        </button>
        <button class="btn btn-outline-info" id="btnReport">
            <i class="fas fa-chart-bar me-2"></i>Relatório
        </button>
        <button class="btn btn-primary" id="toggleForm">
            <i class="fas fa-plus me-2"></i>Nova Requisição
        </button>
    </div>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Create/Edit Form Card -->
        <div class="card mb-4" id="requestFormCard" style="display: none;">
            <div class="card-header bg-light">
                <h5 class="mb-0" id="formTitle">
                    <i class="fas fa-clipboard-list me-2"></i>Adicionar Nova Requisição
                </h5>
            </div>
            <div class="card-body">
                <form id="requestForm">
                    <input type="hidden" id="request_id" name="id">
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="code" class="form-label">Código da Requisição *</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="code" name="code" required
                                       placeholder="Ex: REQ-000001">
                                <button type="button" class="btn btn-outline-secondary" id="generateCode">
                                    <i class="fas fa-sync-alt"></i> Gerar
                                </button>
                            </div>
                            <div class="invalid-feedback" id="code-error"></div>
                            <small class="text-muted">Código único da requisição</small>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="date" class="form-label">Data da Requisição *</label>
                            <input type="date" class="form-control" id="date" name="date" required
                                   max="{{ date('Y-m-d') }}">
                            <div class="invalid-feedback" id="date-error"></div>
                            <small class="text-muted">Data da requisição (não pode ser futura)</small>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="type" class="form-label">Tipo de Requisição *</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="">Selecione o tipo</option>
                                <option value="internal">Interna</option>
                                <option value="external">Externa</option>
                            </select>
                            <div class="invalid-feedback" id="type-error"></div>
                            <small class="text-muted">Tipo de requisição</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="project_id" class="form-label">Projeto (Opcional)</label>
                            <select class="form-select" id="project_id" name="project_id">
                                <option value="">Selecione um projeto</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="project_id-error"></div>
                            <small class="text-muted">Associar a um projeto existente</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="description" class="form-label">Descrição</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="3" placeholder="Descrição da requisição..."></textarea>
                            <div class="invalid-feedback" id="description-error"></div>
                            <small class="text-muted">Descrição opcional da requisição (máx. 500 caracteres)</small>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" id="cancelForm">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span id="submitText">Guardar Requisição</span>
                            <span id="loadingSpinner" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i> A processar...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Statistics Modal -->
        <div class="modal fade" id="statisticsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-chart-pie me-2"></i>Estatísticas de Requisições
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row" id="statisticsContent">
                            <div class="col-md-12 text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">A carregar...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Modal -->
        <div class="modal fade" id="reportModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-chart-bar me-2"></i>Relatório de Requisições
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="reportForm">
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label for="start_date" class="form-label">Data Inicial *</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="end_date" class="form-label">Data Final *</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="report_type" class="form-label">Tipo</label>
                                    <select class="form-select" id="report_type" name="type">
                                        <option value="">Todos</option>
                                        <option value="internal">Internas</option>
                                        <option value="external">Externas</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="report_project_id" class="form-label">Projeto</label>
                                    <select class="form-select" id="report_project_id" name="project_id">
                                        <option value="">Todos os projetos</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    Cancelar
                                </button>
                                <button type="submit" class="btn btn-primary" id="generateReportBtn">
                                    <span>Gerar Relatório</span>
                                    <span id="reportLoading" style="display: none;">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </span>
                                </button>
                            </div>
                        </form>
                        
                        <div id="reportResults" class="mt-4" style="display: none;">
                            <hr>
                            <h6 class="mb-3">Resultados do Relatório</h6>
                            <div class="table-responsive">
                                <table class="table table-sm" id="reportTable">
                                    <thead>
                                        <tr>
                                            <th>Código</th>
                                            <th>Data</th>
                                            <th>Tipo</th>
                                            <th>Projeto</th>
                                            <th>Descrição</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Results will be populated here -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="alert alert-info mt-3 mb-0" id="reportSummary">
                                <!-- Summary will be populated here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Requests Table Card -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary active" id="btnActive">
                            Ativas
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btnInactive">
                            Eliminadas
                        </button>
                        <button type="button" class="btn btn-outline-info" id="btnFilterInternal">
                            <i class="fas fa-building me-1"></i> Internas
                        </button>
                        <button type="button" class="btn btn-outline-warning" id="btnFilterExternal">
                            <i class="fas fa-external-link-alt me-1"></i> Externas
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-outline-success dropdown-toggle" type="button" id="filterProjectDropdown" data-bs-toggle="dropdown">
                                <i class="fas fa-project-diagram me-1"></i> Projeto
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="filterProjectDropdown">
                                <li><a class="dropdown-item" href="#" data-project-id="">Todos os Projetos</a></li>
                                <li><hr class="dropdown-divider"></li>
                                @foreach($projects as $project)
                                <li><a class="dropdown-item" href="#" data-project-id="{{ $project->id }}">{{ $project->name }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <div class="input-group" style="width: 300px;">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control" id="tableSearch" 
                                   placeholder="Pesquisar requisições...">
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover" id="requestsTable" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Requisição</th>
                                <th>Data</th>
                                <th>Tipo</th>
                                <th>Projeto</th>
                                <th>Descrição</th>
                                <th>Registada em</th>
                                <th>Estado</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables will populate this -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .request-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #06d6a0, #0ac294);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 16px;
    }
    
    .table-actions {
        min-width: 180px;
        white-space: nowrap;
    }
    
    .request-code {
        font-family: 'Courier New', monospace;
        font-weight: bold;
        color: #4361ee;
    }
    
    .date-badge {
        font-size: 0.875rem;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
    }
    
    .future-date {
        border-color: #ef476f !important;
        background-color: rgba(239, 71, 111, 0.1) !important;
    }
    
    .stat-card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
    }
    
    .type-badge-internal {
        background: linear-gradient(135deg, #4361ee, #3a56d4);
        color: white;
    }
    
    .type-badge-external {
        background: linear-gradient(135deg, #ffd166, #f9c74f);
        color: #000;
    }
    
    .description-truncate {
        max-width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .project-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let currentView = 'active';
    let currentFilter = 'all';
    let currentProjectId = '';
    let table;
    const isAdmin = {{ auth()->user()->isAdmin() ? 'true' : 'false' }};
    
    // Initialize DataTable
    function initializeTable(view = 'active', filter = 'all', projectId = '') {
        if ($.fn.DataTable.isDataTable('#requestsTable')) {
            table.destroy();
        }
        
        const url = view === 'active' 
            ? "{{ route('requests.data') }}" 
            : "{{ route('requests.data.trashed') }}";
            
        table = $('#requestsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: url,
                type: 'GET',
                data: function(d) {
                    d.view = view;
                    d.filter = filter;
                    d.project_id = projectId;
                }
            },
            responsive: true,
            order: [[0, 'desc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-PT.json'
            },
            columns: [
                { 
                    data: 'id',
                    className: 'fw-semibold',
                    width: '5%'
                },
                { 
                    data: 'code',
                    render: function(data, type, row) {
                        const initial = data.charAt(0).toUpperCase();
                        return `<div class="d-flex align-items-center">
                                    <div class="request-avatar me-3">
                                        ${initial}
                                    </div>
                                    <div>
                                        <div class="fw-medium request-code">${data}</div>
                                        <small class="text-muted">ID: ${row.id}</small>
                                    </div>
                                </div>`;
                    }
                },
                { 
                    data: 'date',
                    render: function(data, type, row) {
                        if (!data) return '';
                        
                        const date = new Date(data);
                        const today = new Date();
                        const diffDays = Math.floor((today - date) / (1000 * 60 * 60 * 24));
                        
                        let badgeClass = 'bg-light text-dark';
                        let icon = '';
                        
                        if (diffDays <= 1) {
                            badgeClass = 'bg-info text-white';
                            icon = '<i class="fas fa-bolt me-1"></i>';
                        } else if (diffDays <= 7) {
                            badgeClass = 'bg-warning text-dark';
                            icon = '<i class="fas fa-clock me-1"></i>';
                        } else if (date.getFullYear() === today.getFullYear() && date.getMonth() === today.getMonth()) {
                            badgeClass = 'bg-primary text-white';
                        }
                        
                        return `<span class="badge ${badgeClass} date-badge">
                                    ${icon}${date.toLocaleDateString('pt-PT')}
                                </span>`;
                    }
                },
                { 
                    data: 'type',
                    render: function(data) {
                        if (data === 'internal') {
                            return `<span class="badge type-badge-internal">Interna</span>`;
                        } else if (data === 'external') {
                            return `<span class="badge type-badge-external">Externa</span>`;
                        }
                        return data;
                    }
                },
                { 
                    data: 'project_id',
                    render: function(data, type, row) {
                        if (row.project) {
                            return `<span class="badge bg-info project-badge">${row.project.name}</span>`;
                        } else {
                            return '<span class="badge bg-secondary project-badge">Sem Projeto</span>';
                        }
                    }
                },
                { 
                    data: 'description',
                    render: function(data) {
                        if (!data) return '<span class="text-muted">Sem descrição</span>';
                        return `<span class="description-truncate" title="${data}">${data}</span>`;
                    }
                },
                { 
                    data: 'created_at',
                    render: function(data) {
                        if (!data) return '';
                        const date = new Date(data);
                        return `<div>
                                    <div>${date.toLocaleDateString('pt-PT')}</div>
                                    <small class="text-muted">${date.toLocaleTimeString('pt-PT', {hour: '2-digit', minute:'2-digit'})}</small>
                                </div>`;
                    }
                },
                { 
                    data: 'deleted_at',
                    className: 'text-center',
                    render: function(data, type, row) {
                        if (data) {
                            return `<span class="badge bg-danger">Eliminada</span>`;
                        } else {
                            return `<span class="badge bg-success">Ativa</span>`;
                        }
                    }
                },
                { 
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    className: 'text-end table-actions',
                    render: function(data, type, row) {
                        let buttons = '<div class="btn-group btn-group-sm" role="group">';
                        
                        if (!row.deleted_at) {
                            buttons += `<button class="btn btn-outline-primary btn-edit"
                                          data-id="${data}"
                                          data-code="${row.code}"
                                          data-date="${row.date}"
                                          data-type="${row.type}"
                                          data-description="${row.description || ''}"
                                          data-project-id="${row.project_id || ''}"
                                          title="Editar Requisição">
                                        <i class="fas fa-edit"></i>
                                    </button>`;
                            
                            if (isAdmin) {
                                buttons += `<button class="btn btn-outline-danger btn-delete"
                                            data-id="${data}"
                                            data-code="${row.code}"
                                            title="Eliminar Requisição">
                                        <i class="fas fa-trash"></i>
                                    </button>`;
                            }
                        } else {
                            if (isAdmin) {
                                buttons += `<button class="btn btn-outline-success btn-restore"
                                          data-id="${data}"
                                          data-code="${row.code}"
                                          title="Restaurar Requisição">
                                        <i class="fas fa-undo"></i>
                                    </button>`;
                                
                                buttons += `<button class="btn btn-outline-danger btn-force-delete"
                                            data-id="${data}"
                                            data-code="${row.code}"
                                            title="Eliminar Permanentemente">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>`;
                            } else {
                                buttons = '<small class="text-muted">Apenas admin</small>';
                            }
                        }
                        
                        buttons += '</div>';
                        return buttons;
                    }
                }
            ],
            createdRow: function(row, data, dataIndex) {
                $(row).attr('id', 'request-' + data.id);
            },
            initComplete: function() {
                $('#tableSearch').on('keyup', function() {
                    table.search(this.value).draw();
                });
            }
        });
    }
    
    // Initialize with active requests
    initializeTable('active', 'all', '');
    
    // Toggle form visibility
    $('#toggleForm').click(function() {
        resetForm();
        $('#formTitle').html('<i class="fas fa-clipboard-list me-2"></i>Adicionar Nova Requisição');
        $('#requestFormCard').slideToggle('fast', function() {
            if ($(this).is(':visible')) {
                $('#toggleForm').html('<i class="fas fa-times me-2"></i>Fechar Formulário');
                generateAutoCode();
            } else {
                $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Nova Requisição');
            }
        });
    });
    
    $('#cancelForm').click(function() {
        $('#requestFormCard').slideUp();
        $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Nova Requisição');
        resetForm();
    });
    
    // Generate auto code
    $('#generateCode').click(function() {
        generateAutoCode();
    });
    
    function generateAutoCode() {
        $.ajax({
            url: "{{ route('requests.generate-code') }}",
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#code').val(response.code);
                }
            }
        });
    }
    
    // View toggles
    $('#btnActive').click(function() {
        if (currentView !== 'active') {
            currentView = 'active';
            $(this).addClass('active').removeClass('btn-outline-primary').addClass('btn-primary');
            $('#btnInactive').removeClass('active btn-primary').addClass('btn-outline-secondary');
            initializeTable('active', currentFilter, currentProjectId);
        }
    });
    
    $('#btnInactive').click(function() {
        if (currentView !== 'inactive') {
            currentView = 'inactive';
            $(this).addClass('active').removeClass('btn-outline-secondary').addClass('btn-primary');
            $('#btnActive').removeClass('active btn-primary').addClass('btn-outline-primary');
            initializeTable('inactive', currentFilter, currentProjectId);
        }
    });
    
    // Filter toggles
    $('#btnFilterInternal').click(function() {
        if (currentFilter !== 'internal') {
            currentFilter = 'internal';
            $(this).addClass('active').removeClass('btn-outline-info').addClass('btn-info text-white');
            $('#btnFilterExternal').removeClass('active btn-warning').addClass('btn-outline-warning');
            initializeTable(currentView, 'internal', currentProjectId);
        } else {
            currentFilter = 'all';
            $(this).removeClass('active btn-info text-white').addClass('btn-outline-info');
            initializeTable(currentView, 'all', currentProjectId);
        }
    });
    
    $('#btnFilterExternal').click(function() {
        if (currentFilter !== 'external') {
            currentFilter = 'external';
            $(this).addClass('active').removeClass('btn-outline-warning').addClass('btn-warning text-dark');
            $('#btnFilterInternal').removeClass('active btn-info').addClass('btn-outline-info');
            initializeTable(currentView, 'external', currentProjectId);
        } else {
            currentFilter = 'all';
            $(this).removeClass('active btn-warning text-dark').addClass('btn-outline-warning');
            initializeTable(currentView, 'all', currentProjectId);
        }
    });
    
    // Project filter dropdown
    $('.dropdown-item[data-project-id]').click(function(e) {
        e.preventDefault();
        const projectId = $(this).data('project-id');
        currentProjectId = projectId;
        
        // Update dropdown button text
        if (projectId) {
            const projectName = $(this).text();
            $('#filterProjectDropdown').html(`<i class="fas fa-project-diagram me-1"></i> ${projectName}`);
        } else {
            $('#filterProjectDropdown').html(`<i class="fas fa-project-diagram me-1"></i> Projeto`);
        }
        
        initializeTable(currentView, currentFilter, projectId);
    });
    
    // Show statistics modal
    $('#btnStatistics').click(function() {
        $('#statisticsModal').modal('show');
        loadStatistics();
    });
    
    // Show report modal
    $('#btnReport').click(function() {
        $('#reportModal').modal('show');
        resetReport();
    });
    
    // Load statistics
    function loadStatistics() {
        $.ajax({
            url: "{{ route('requests.statistics') }}",
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.statistics) {
                    const stats = response.statistics;
                    
                    const statsHtml = `
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="card stat-card bg-primary text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="card-title mb-0">Total Requisições</h6>
                                                <h2 class="mb-0">${stats.total}</h2>
                                            </div>
                                            <i class="fas fa-clipboard-list fa-2x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card stat-card bg-info text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="card-title mb-0">Com Projeto</h6>
                                                <h2 class="mb-0">${stats.with_project}</h2>
                                            </div>
                                            <i class="fas fa-project-diagram fa-2x opacity-50"></i>
                                        </div>
                                        <div class="mt-2 small">
                                            ${stats.with_project_percentage}% do total
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card stat-card bg-secondary text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="card-title mb-0">Sem Projeto</h6>
                                                <h2 class="mb-0">${stats.without_project}</h2>
                                            </div>
                                            <i class="fas fa-times-circle fa-2x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="card stat-card bg-success text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="card-title mb-0">Internas</h6>
                                                <h2 class="mb-0">${stats.internal}</h2>
                                            </div>
                                            <i class="fas fa-building fa-2x opacity-50"></i>
                                        </div>
                                        <div class="mt-2 small">
                                            ${stats.internal_percentage}% do total
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card stat-card bg-warning text-dark">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="card-title mb-0">Externas</h6>
                                                <h2 class="mb-0">${stats.external}</h2>
                                            </div>
                                            <i class="fas fa-external-link-alt fa-2x opacity-50"></i>
                                        </div>
                                        <div class="mt-2 small">
                                            ${stats.external_percentage}% do total
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card stat-card bg-danger text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="card-title mb-0">Hoje</h6>
                                                <h2 class="mb-0">${stats.today}</h2>
                                            </div>
                                            <i class="fas fa-calendar-day fa-2x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="card stat-card bg-info text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="card-title mb-0">Esta Semana</h6>
                                                <h2 class="mb-0">${stats.this_week}</h2>
                                            </div>
                                            <i class="fas fa-calendar-week fa-2x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card stat-card bg-primary text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="card-title mb-0">Este Mês</h6>
                                                <h2 class="mb-0">${stats.this_month}</h2>
                                            </div>
                                            <i class="fas fa-calendar-alt fa-2x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card stat-card bg-dark text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="card-title mb-0">Últimos 7 Dias</h6>
                                                <h2 class="mb-0">${stats.recent}</h2>
                                            </div>
                                            <i class="fas fa-clock fa-2x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card stat-card bg-secondary text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="card-title mb-0">Mês Passado</h6>
                                                <h2 class="mb-0">${stats.last_month}</h2>
                                            </div>
                                            <i class="fas fa-history fa-2x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    $('#statisticsContent').html(statsHtml);
                } else {
                    $('#statisticsContent').html(`
                        <div class="col-md-12">
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                ${response.message || 'Erro ao carregar estatísticas'}
                            </div>
                        </div>
                    `);
                }
            },
            error: function(xhr) {
                $('#statisticsContent').html(`
                    <div class="col-md-12">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Erro ao carregar estatísticas. Tente novamente.
                        </div>
                    </div>
                `);
            }
        });
    }
    
    // Date validation for request form
    $('#date').on('change', function() {
        const selectedDate = new Date($(this).val());
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate > today) {
            $(this).addClass('future-date');
            $(this).next('.invalid-feedback').text('A data não pode ser no futuro');
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('future-date');
            $(this).removeClass('is-invalid');
        }
    });
    
    // Form submission
    $('#requestForm').submit(function(e) {
        e.preventDefault();
        
        const submitBtn = $('#submitBtn');
        const submitText = $('#submitText');
        const loadingSpinner = $('#loadingSpinner');
        const requestId = $('#request_id').val();
        
        // Validate date
        const selectedDate = new Date($('#date').val());
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate > today) {
            $('#date').addClass('is-invalid future-date');
            $('#date-error').text('A data da requisição não pode ser no futuro');
            return;
        }
        
        // Show loading state
        submitBtn.prop('disabled', true);
        submitText.hide();
        loadingSpinner.show();
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        const url = requestId ? `/requests/${requestId}` : "{{ route('requests.store') }}";
        const method = requestId ? 'PUT' : 'POST';
        
        $.ajax({
            url: url,
            method: method,
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                Toast.fire({
                    icon: 'success',
                    title: response.message
                });
                
                resetForm();
                table.ajax.reload();
                
                // Hide form after success
                $('#requestFormCard').slideUp();
                $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Nova Requisição');
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, value) {
                        $(`#${key}`).addClass('is-invalid');
                        if (key === 'date' && value[0].includes('futuro')) {
                            $(`#${key}`).addClass('future-date');
                        }
                        $(`#${key}-error`).text(value[0]);
                    });
                    
                    Toast.fire({
                        icon: 'error',
                        title: 'Por favor, corrija os erros no formulário'
                    });
                } else if (xhr.status === 403) {
                    Toast.fire({
                        icon: 'error',
                        title: xhr.responseJSON?.message || 'Permissão negada'
                    });
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: xhr.responseJSON?.message || 'Ocorreu um erro'
                    });
                }
            },
            complete: function() {
                // Reset button state
                submitBtn.prop('disabled', false);
                submitText.show();
                loadingSpinner.hide();
            }
        });
    });
    
    // Edit request
    $(document).on('click', '.btn-edit', function() {
        const requestId = $(this).data('id');
        const requestCode = $(this).data('code');
        const requestDate = $(this).data('date');
        const requestType = $(this).data('type');
        const requestDescription = $(this).data('description');
        const requestProjectId = $(this).data('project-id');
        
        // Show form
        $('#requestFormCard').slideDown();
        $('#toggleForm').html('<i class="fas fa-times me-2"></i>Fechar Formulário');
        
        // Set form title
        $('#formTitle').html(`<i class="fas fa-edit me-2"></i>Editar Requisição: ${requestCode}`);
        
        // Format date for input field (YYYY-MM-DD)
        const dateObj = new Date(requestDate);
        const formattedDate = dateObj.toISOString().split('T')[0];
        
        // Fill form data
        $('#request_id').val(requestId);
        $('#code').val(requestCode);
        $('#date').val(formattedDate);
        $('#type').val(requestType);
        $('#description').val(requestDescription);
        $('#project_id').val(requestProjectId || '');
        
        // Validate date
        $('#date').trigger('change');
        
        // Scroll to form
        $('html, body').animate({
            scrollTop: $('#requestFormCard').offset().top - 20
        }, 500);
    });
    
    // Delete request (soft delete) - only for admins
    $(document).on('click', '.btn-delete', function() {
        if (!isAdmin) {
            Toast.fire({
                icon: 'warning',
                title: 'Apenas administradores podem eliminar requisições'
            });
            return;
        }
        
        const requestId = $(this).data('id');
        const requestCode = $(this).data('code');
        
        Swal.fire({
            title: 'Eliminar Requisição',
            html: `<p>Tem a certeza que deseja eliminar a requisição <strong>${requestCode}</strong>?</p>
                  <p class="text-muted"><small>Esta ação pode ser revertida posteriormente.</small></p>
                  <p class="text-warning"><small><i class="fas fa-shield-alt"></i> Apenas administradores podem executar esta ação</small></p>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, eliminar!',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/requests/${requestId}`,
                    method: 'DELETE',
                    dataType: 'json',
                    success: function(response) {
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        if (xhr.status === 403) {
                            Toast.fire({
                                icon: 'error',
                                title: 'Apenas administradores podem eliminar requisições'
                            });
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: xhr.responseJSON?.message || 'Erro ao eliminar requisição'
                            });
                        }
                    }
                });
            }
        });
    });
    
    // Restore request - only for admins
    $(document).on('click', '.btn-restore', function() {
        if (!isAdmin) {
            Toast.fire({
                icon: 'warning',
                title: 'Apenas administradores podem restaurar requisições'
            });
            return;
        }
        
        const requestId = $(this).data('id');
        const requestCode = $(this).data('code');
        
        Swal.fire({
            title: 'Restaurar Requisição',
            html: `<p>Tem a certeza que deseja restaurar a requisição <strong>${requestCode}</strong>?</p>
                  <p class="text-warning"><small><i class="fas fa-shield-alt"></i> Apenas administradores podem executar esta ação</small></p>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#06d6a0',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, restaurar!',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/requests/${requestId}/restore`,
                    method: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        if (xhr.status === 403) {
                            Toast.fire({
                                icon: 'error',
                                title: 'Apenas administradores podem restaurar requisições'
                            });
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: xhr.responseJSON?.message || 'Erro ao restaurar requisição'
                            });
                        }
                    }
                });
            }
        });
    });
    
    // Force delete request - only for admins
    $(document).on('click', '.btn-force-delete', function() {
        if (!isAdmin) {
            Toast.fire({
                icon: 'warning',
                title: 'Apenas administradores podem eliminar permanentemente requisições'
            });
            return;
        }
        
        const requestId = $(this).data('id');
        const requestCode = $(this).data('code');
        
        Swal.fire({
            title: 'Eliminar Permanentemente',
            html: `<p>Tem a certeza ABSOLUTA que deseja eliminar permanentemente a requisição <strong>${requestCode}</strong>?</p>
                  <p class="text-danger"><small>Esta ação NÃO pode ser revertida!</small></p>
                  <p class="text-warning"><small><i class="fas fa-shield-alt"></i> Apenas administradores podem executar esta ação</small></p>`,
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, eliminar permanentemente!',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            showDenyButton: true,
            denyButtonText: 'Apenas arquivar',
            denyButtonColor: '#ffd166'
        }).then((result) => {
            if (result.isConfirmed) {
                // Force delete
                $.ajax({
                    url: `/requests/${requestId}/force`,
                    method: 'DELETE',
                    dataType: 'json',
                    success: function(response) {
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        if (xhr.status === 403) {
                            Toast.fire({
                                icon: 'error',
                                title: 'Apenas administradores podem eliminar permanentemente requisições'
                            });
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: xhr.responseJSON?.message || 'Erro ao eliminar requisição'
                            });
                        }
                    }
                });
            } else if (result.isDenied) {
                // Restore instead
                $.ajax({
                    url: `/requests/${requestId}/restore`,
                    method: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        Toast.fire({
                            icon: 'success',
                            title: 'Requisição restaurada com sucesso!'
                        });
                        // Switch to active view
                        $('#btnActive').click();
                    },
                    error: function(xhr) {
                        Toast.fire({
                            icon: 'error',
                            title: xhr.responseJSON?.message || 'Erro ao restaurar requisição'
                        });
                    }
                });
            }
        });
    });
    
    // Generate report
    $('#reportForm').submit(function(e) {
        e.preventDefault();
        
        const generateBtn = $('#generateReportBtn');
        const reportLoading = $('#reportLoading');
        
        // Show loading state
        generateBtn.prop('disabled', true);
        generateBtn.find('span:first').hide();
        reportLoading.show();
        
        // Clear previous results
        $('#reportResults').hide();
        $('#reportTable tbody').empty();
        
        $.ajax({
            url: "{{ route('requests.report') }}",
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    // Populate table
                    response.data.forEach(function(request) {
                        const date = new Date(request.date);
                        let typeBadge = request.type === 'internal' 
                            ? '<span class="badge type-badge-internal">Interna</span>'
                            : '<span class="badge type-badge-external">Externa</span>';
                        
                        let projectBadge = request.project 
                            ? `<span class="badge bg-info project-badge">${request.project.name}</span>`
                            : '<span class="badge bg-secondary project-badge">Sem Projeto</span>';
                        
                        $('#reportTable tbody').append(`
                            <tr>
                                <td><strong>${request.code}</strong></td>
                                <td>${date.toLocaleDateString('pt-PT')}</td>
                                <td>${typeBadge}</td>
                                <td>${projectBadge}</td>
                                <td>${request.description || '-'}</td>
                            </tr>
                        `);
                    });
                    
                    // Show summary
                    const startDate = new Date(response.period.start_date);
                    const endDate = new Date(response.period.end_date);
                    
                    $('#reportSummary').html(`
                        <i class="fas fa-info-circle me-1"></i>
                        Foram encontradas <strong>${response.count}</strong> requisições no período de 
                        <strong>${startDate.toLocaleDateString('pt-PT')}</strong> a 
                        <strong>${endDate.toLocaleDateString('pt-PT')}</strong>
                        <br>
                        <small class="mt-1">
                            <i class="fas fa-building me-1"></i> Internas: ${response.stats.internal} | 
                            <i class="fas fa-external-link-alt me-1"></i> Externas: ${response.stats.external}
                            <br>
                            <i class="fas fa-project-diagram me-1"></i> Com Projeto: ${response.stats.with_project} | 
                            <i class="fas fa-times-circle me-1"></i> Sem Projeto: ${response.stats.without_project}
                        </small>
                    `);
                    
                    $('#reportResults').show();
                } else {
                    $('#reportSummary').html(`
                        <div class="text-center py-3">
                            <i class="fas fa-search fa-2x text-muted mb-3"></i>
                            <p class="mb-0">Nenhuma requisição encontrada no período selecionado.</p>
                        </div>
                    `);
                    $('#reportResults').show();
                }
            },
            error: function(xhr) {
                Toast.fire({
                    icon: 'error',
                    title: xhr.responseJSON?.message || 'Erro ao gerar relatório'
                });
            },
            complete: function() {
                // Reset button state
                generateBtn.prop('disabled', false);
                generateBtn.find('span:first').show();
                reportLoading.hide();
            }
        });
    });
    
    // Reset form
    function resetForm() {
        $('#requestForm')[0].reset();
        $('#request_id').val('');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#date').removeClass('future-date');
    }
    
    // Reset report
    function resetReport() {
        $('#reportForm')[0].reset();
        $('#reportResults').hide();
        $('#reportTable tbody').empty();
    }
});
</script>
@endpush