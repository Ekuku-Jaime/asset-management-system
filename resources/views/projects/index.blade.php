@extends('layouts.app')

@section('title', 'Gestão de Projetos')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header Card -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0 fw-bold">
                            <i class="fas fa-project-diagram me-2"></i>Gestão de Projetos
                        </h4>
                        <p class="text-muted mb-0">Gerir projetos, orçamentos e cronogramas</p>
                    </div>
                    <button class="btn btn-primary" id="toggleForm">
                        <i class="fas fa-plus me-2"></i>Novo Projeto
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Form Card -->
<div class="row mb-4" id="projectFormCard" style="display: none;">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0" id="formTitle">
                    <i class="fas fa-project-diagram me-2"></i>Adicionar Novo Projeto
                </h5>
            </div>
            <div class="card-body">
                <form id="projectForm">
                    <input type="hidden" id="project_id" name="id">
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="name" class="form-label">Nome do Projeto *</label>
                            <input type="text" class="form-control" id="name" name="name" required
                                   placeholder="Digite o nome do projeto">
                            <div class="invalid-feedback" id="name-error"></div>
                            <small class="text-muted">Nome único que identifica o projeto</small>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="description" class="form-label">Descrição</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="3" placeholder="Descrição detalhada do projeto"></textarea>
                            <div class="invalid-feedback" id="description-error"></div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Data de Início</label>
                            <input type="date" class="form-control" id="start_date" name="start_date">
                            <div class="invalid-feedback" id="start_date-error"></div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">Data de Término</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                            <div class="invalid-feedback" id="end_date-error"></div>
                            <small class="text-muted">Deve ser igual ou posterior à data de início</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="ativo">Ativo</option>
                                <option value="suspenso">Suspenso</option>
                                <option value="concluido">Concluído</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                            <div class="invalid-feedback" id="status-error"></div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="total_value" class="form-label">Orçamento Total (MT)</label>
                            <div class="input-group">
                                <span class="input-group-text">MT</span>
                                <input type="number" class="form-control" id="total_value" name="total_value" 
                                       step="0.01" min="0" placeholder="0.00">
                            </div>
                            <div class="invalid-feedback" id="total_value-error"></div>
                            <small class="text-muted">Valor total do orçamento do projeto</small>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" class="btn btn-secondary" id="cancelForm">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span id="submitText">Guardar Projeto</span>
                            <span id="loadingSpinner" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i> A processar...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Projects Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Total Projetos</h6>
                        <h3 class="text-white mb-0" id="statsTotal">0</h3>
                    </div>
                    <i class="fas fa-project-diagram fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Ativos</h6>
                        <h3 class="text-white mb-0" id="statsActive">0</h3>
                    </div>
                    <i class="fas fa-check-circle fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Concluídos</h6>
                        <h3 class="text-white mb-0" id="statsCompleted">0</h3>
                    </div>
                    <i class="fas fa-flag-checkered fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Atrasados</h6>
                        <h3 class="text-white mb-0" id="statsOverdue">0</h3>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Projects Table Card -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary active" id="btnActive">
                            <i class="fas fa-check-circle me-2"></i>Ativos
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btnInactive">
                            <i class="fas fa-trash me-2"></i>Eliminados
                        </button>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <div class="input-group" style="width: 300px;">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control" id="tableSearch" 
                                   placeholder="Pesquisar projetos...">
                        </div>
                        
                        <button class="btn btn-outline-secondary" id="exportExcel" title="Exportar Excel">
                            <i class="fas fa-file-excel"></i>
                        </button>
                        <button class="btn btn-outline-secondary" id="exportPDF" title="Exportar PDF">
                            <i class="fas fa-file-pdf"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="projectsTable" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th width="50">#</th>
                                <th>Código</th>
                                <th>Projeto</th>
                                <th>Descrição</th>
                                <th>Início</th>
                                <th>Término</th>
                                <th>Status</th>
                                <th>Orçamento</th>
                                <th>Progresso</th>
                                <th>Gasto</th>
                                <th>Estatísticas</th>
                                <th>Criado em</th>
                                <th>Estado</th>
                                <th width="150">Ações</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include SweetAlert2 -->
{{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}
@endsection

@push('styles')
<style>
    .project-avatar {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 16px;
        color: white;
    }
    
    .project-color-1 { background: linear-gradient(135deg, #4361ee, #3a56d4); }
    .project-color-2 { background: linear-gradient(135deg, #10b981, #059669); }
    .project-color-3 { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .project-color-4 { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .project-color-5 { background: linear-gradient(135deg, #8b5cf6, #6d28d9); }
    
    .progress {
        border-radius: 4px;
        background-color: #e9ecef;
    }
    
    .progress-bar {
        transition: width 0.6s ease;
        border-radius: 4px;
    }
    
    .btn-group-sm > .btn, .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .table > :not(caption) > * > * {
        vertical-align: middle;
    }
    
    .card.bg-primary, .card.bg-success, .card.bg-warning, .card.bg-danger {
        border: none;
        transition: transform 0.3s;
    }
    
    .card.bg-primary:hover, .card.bg-success:hover, .card.bg-warning:hover, .card.bg-danger:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let currentView = 'active';
    let table;
    
    // Carregar estatísticas
    function loadStats() {
        $.ajax({
            url: '/projects/stats',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#statsTotal').text(response.data.total);
                    $('#statsActive').text(response.data.active);
                    $('#statsCompleted').text(response.data.completed);
                    $('#statsOverdue').text(response.data.overdue);
                }
            }
        });
    }
    
    loadStats();
    
    // Function to get project color based on ID
    function getProjectColor(id) {
        const colors = [
            'project-color-1',
            'project-color-2', 
            'project-color-3',
            'project-color-4',
            'project-color-5'
        ];
        return colors[(id % 5)];
    }
    
    // Initialize DataTable
    function initializeTable(view = 'active') {
        if ($.fn.DataTable.isDataTable('#projectsTable')) {
            table.destroy();
        }
        
        const url = view === 'active' 
            ? "{{ route('projects.data') }}" 
            : "{{ route('projects.data.trashed') }}";
            
        table = $('#projectsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: url,
                type: 'GET',
                data: function(d) {
                    d.status = $('#filterStatus').val();
                    d.date_range = $('#dateRange').val();
                }
            },
            pageLength: 25,
            responsive: true,
            order: [[11, 'desc']],
           
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'code', name: 'code' },
                { data: 'name', name: 'name' },
                { data: 'description', name: 'description', orderable: false },
                { data: 'start_date', name: 'start_date' },
                { data: 'end_date', name: 'end_date' },
                { data: 'status', name: 'status' },
                { data: 'total_value', name: 'total_value' },
                { data: 'progress', name: 'progress', orderable: false, searchable: false },
                { data: 'budget', name: 'budget', orderable: false, searchable: false },
                { data: 'stats', name: 'stats', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at' },
                { data: 'deleted_at', name: 'deleted_at' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            initComplete: function() {
                $('#tableSearch').on('keyup', function() {
                    table.search(this.value).draw();
                });
            },
            drawCallback: function() {
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        });
    }
    
    // Initialize with active projects
    initializeTable('active');
    
    // Toggle form visibility
    $('#toggleForm').click(function() {
        resetForm();
        $('#formTitle').html('<i class="fas fa-project-diagram me-2"></i>Adicionar Novo Projeto');
        $('#projectFormCard').slideToggle('fast', function() {
            if ($(this).is(':visible')) {
                $('#toggleForm').html('<i class="fas fa-times me-2"></i>Fechar Formulário');
            } else {
                $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Novo Projeto');
            }
        });
    });
    
    $('#cancelForm').click(function() {
        $('#projectFormCard').slideUp();
        $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Novo Projeto');
        resetForm();
    });
    
    // Toggle between active and inactive projects
    $('#btnActive').click(function() {
        if (currentView !== 'active') {
            currentView = 'active';
            $(this).addClass('active btn-primary').removeClass('btn-outline-primary');
            $('#btnInactive').removeClass('active btn-primary').addClass('btn-outline-secondary');
            initializeTable('active');
        }
    });
    
    $('#btnInactive').click(function() {
        if (currentView !== 'inactive') {
            currentView = 'inactive';
            $(this).addClass('active btn-primary').removeClass('btn-outline-secondary');
            $('#btnActive').removeClass('active btn-primary').addClass('btn-outline-primary');
            initializeTable('inactive');
        }
    });
    
    // Form submission
    $('#projectForm').submit(function(e) {
        e.preventDefault();
        
        const submitBtn = $('#submitBtn');
        const submitText = $('#submitText');
        const loadingSpinner = $('#loadingSpinner');
        const projectId = $('#project_id').val();
        
        // Show loading state
        submitBtn.prop('disabled', true);
        submitText.hide();
        loadingSpinner.show();
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        const url = projectId ? `/projects/${projectId}` : "{{ route('projects.store') }}";
        const method = projectId ? 'PUT' : 'POST';
        
        $.ajax({
            url: url,
            method: method,
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 2000
                });
                
                resetForm();
                table.ajax.reload();
                loadStats();
                
                // Hide form after success
                $('#projectFormCard').slideUp();
                $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Novo Projeto');
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, value) {
                        $(`#${key}`).addClass('is-invalid');
                        $(`#${key}-error`).text(value[0]);
                    });
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro de Validação',
                        text: 'Por favor, corrija os erros no formulário'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: xhr.responseJSON?.message || 'Ocorreu um erro'
                    });
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                submitText.show();
                loadingSpinner.hide();
            }
        });
    });
    
    // Edit project
    $(document).on('click', '.btn-edit', function() {
        const projectId = $(this).data('id');
        
        // Show form
        $('#projectFormCard').slideDown();
        $('#toggleForm').html('<i class="fas fa-times me-2"></i>Fechar Formulário');
        
        // Set form title
        $('#formTitle').html('<i class="fas fa-edit me-2"></i>Editar Projeto');
        
        // Load project data
        $.ajax({
            url: `/projects/${projectId}/edit`,
            method: 'GET',
            success: function(response) {
                const project = response.data;
                
                $('#project_id').val(project.id);
                $('#name').val(project.name);
                $('#description').val(project.description);
                $('#start_date').val(project.start_date);
                $('#end_date').val(project.end_date);
                $('#status').val(project.status);
                $('#total_value').val(project.total_value);
            }
        });
        
        // Scroll to form
        $('html, body').animate({
            scrollTop: $('#projectFormCard').offset().top - 20
        }, 500);
    });
    
    // Delete project (soft delete)
    $(document).on('click', '.btn-delete', function() {
        const projectId = $(this).data('id');
        const projectName = $(this).data('name');
        
        Swal.fire({
            title: 'Eliminar Projeto?',
            html: `<p>Tem a certeza que deseja eliminar o projeto <strong>${projectName}</strong>?</p>
                  <p class="text-warning"><small><i class="fas fa-info-circle me-1"></i>Esta ação pode ser revertida posteriormente.</small></p>`,
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
                    url: `/projects/${projectId}`,
                    method: 'DELETE',
                    dataType: 'json',
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminado!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 2000
                        });
                        table.ajax.reload();
                        loadStats();
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            text: xhr.responseJSON?.message || 'Erro ao eliminar projeto'
                        });
                    }
                });
            }
        });
    });
    
    // Restore project
    $(document).on('click', '.btn-restore', function() {
        const projectId = $(this).data('id');
        const projectName = $(this).data('name');
        
        Swal.fire({
            title: 'Restaurar Projeto?',
            html: `<p>Tem a certeza que deseja restaurar o projeto <strong>${projectName}</strong>?</p>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, restaurar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/projects/${projectId}/restore`,
                    method: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Restaurado!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 2000
                        });
                        $('#btnActive').click(); // Switch to active view
                        loadStats();
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            text: xhr.responseJSON?.message || 'Erro ao restaurar projeto'
                        });
                    }
                });
            }
        });
    });
    
    // Force delete project
    $(document).on('click', '.btn-force-delete', function() {
        const projectId = $(this).data('id');
        const projectName = $(this).data('name');
        
        Swal.fire({
            title: 'Eliminar Permanentemente?',
            html: `<p>Tem a certeza ABSOLUTA que deseja eliminar permanentemente o projeto <strong>${projectName}</strong>?</p>
                  <p class="text-danger"><strong><i class="fas fa-exclamation-triangle me-1"></i>Esta ação NÃO pode ser revertida!</strong></p>
                  <p class="text-muted"><small>Serão eliminados todos os registos associados a este projeto.</small></p>`,
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, eliminar permanentemente!',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/projects/${projectId}/force`,
                    method: 'DELETE',
                    dataType: 'json',
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminado!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 2000
                        });
                        table.ajax.reload();
                        loadStats();
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            text: xhr.responseJSON?.message || 'Erro ao eliminar projeto permanentemente'
                        });
                    }
                });
            }
        });
    });
    
    // Export Excel
    $('#exportExcel').click(function() {
        window.location.href = '/projects/export/excel' + (currentView === 'inactive' ? '?trashed=1' : '');
    });
    
    // Export PDF
    $('#exportPDF').click(function() {
        window.location.href = '/projects/export/pdf' + (currentView === 'inactive' ? '?trashed=1' : '');
    });
    
    // Reset form
    function resetForm() {
        $('#projectForm')[0].reset();
        $('#project_id').val('');
        $('#status').val('ativo');
        $('#total_value').val('');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }
});
</script>
@endpush