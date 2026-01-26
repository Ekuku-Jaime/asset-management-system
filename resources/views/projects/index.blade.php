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
                        <p class="text-muted mb-0">Gerir projetos do sistema</p>
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
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
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

<!-- Projects Table Card -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary active" id="btnActive">
                            Ativos
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btnInactive">
                            Eliminados
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
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover" id="projectsTable" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Projeto</th>
                                <th>Criado em</th>
                                <th>Estado</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .project-avatar {
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
        min-width: 150px;
    }
    
    .project-color-1 { background: linear-gradient(135deg, #4361ee, #3a56d4); }
    .project-color-2 { background: linear-gradient(135deg, #06d6a0, #0ac294); }
    .project-color-3 { background: linear-gradient(135deg, #ef476f, #e83e5f); }
    .project-color-4 { background: linear-gradient(135deg, #ffd166, #f9c74f); }
    .project-color-5 { background: linear-gradient(135deg, #7209b7, #560bad); }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let currentView = 'active';
    let table;
    
    // Function to get project color based on ID
    function getProjectColor(id) {
        const colors = [
            'project-color-1',
            'project-color-2', 
            'project-color-3',
            'project-color-4',
            'project-color-5'
        ];
        return colors[id % colors.length];
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
                type: 'GET'
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
                    data: 'name',
                    render: function(data, type, row) {
                        const initial = data.charAt(0).toUpperCase();
                        const colorClass = getProjectColor(row.id);
                        return `<div class="d-flex align-items-center">
                                    <div class="project-avatar ${colorClass} me-3">
                                        ${initial}
                                    </div>
                                    <div>
                                        <h6 class="mb-0">${data}</h6>
                                        
                                    </div>
                                </div>`;
                    }
                },
                { 
                    data: 'created_at',
                    render: function(data) {
                        
                        return `<div>
                                    <div>${data}</div>
                                    
                                </div>`;
                    }
                },
                { 
                    data: 'deleted_at',
                    className: 'text-center',
                    render: function(data) {
                        if (!data) {
                            return `<span class="badge bg-danger">Eliminado</span>`;
                        } else {
                            return `<span class="badge bg-success">Ativo</span>`;
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
                        
                        if (currentView === 'active') {
                            buttons += `<button class="btn btn-outline-primary btn-edit"
                                          data-id="${data}"
                                          data-name="${row.name}"
                                          title="Editar Projeto">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <button class="btn btn-outline-danger btn-delete"
                                            data-id="${data}"
                                            data-name="${row.name}"
                                            title="Eliminar Projeto">
                                        <i class="fas fa-trash"></i>
                                    </button>`;
                        } else {
                            buttons += `<button class="btn btn-outline-success btn-restore"
                                          data-id="${data}"
                                          data-name="${row.name}"
                                          title="Restaurar Projeto">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                    
                                    <button class="btn btn-outline-danger btn-force-delete"
                                            data-id="${data}"
                                            data-name="${row.name}"
                                            title="Eliminar Permanentemente">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>`;
                        }
                        
                        buttons += '</div>';
                        return buttons;
                    }
                }
            ],
            initComplete: function() {
                // Add search functionality
                $('#tableSearch').on('keyup', function() {
                    table.search(this.value).draw();
                });
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
            $(this).addClass('active').removeClass('btn-outline-primary').addClass('btn-primary');
            $('#btnInactive').removeClass('active btn-primary').addClass('btn-outline-secondary');
            initializeTable('active');
        }
    });
    
    $('#btnInactive').click(function() {
        if (currentView !== 'inactive') {
            currentView = 'inactive';
            $(this).addClass('active').removeClass('btn-outline-secondary').addClass('btn-primary');
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
                Toast.fire({
                    icon: 'success',
                    title: response.message
                });
                
                resetForm();
                table.ajax.reload();
                
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
                    
                    Toast.fire({
                        icon: 'error',
                        title: 'Por favor, corrija os erros no formulário'
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
    
    // Edit project
    $(document).on('click', '.btn-edit', function() {
        const projectId = $(this).data('id');
        const projectName = $(this).data('name');
        
        // Show form
        $('#projectFormCard').slideDown();
        $('#toggleForm').html('<i class="fas fa-times me-2"></i>Fechar Formulário');
        
        // Set form title
        $('#formTitle').html(`<i class="fas fa-edit me-2"></i>Editar Projeto: ${projectName}`);
        
        // Fill form data
        $('#project_id').val(projectId);
        $('#name').val(projectName);
        
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
            title: 'Eliminar Projeto',
            html: `<p>Tem a certeza que deseja eliminar o projeto <strong>${projectName}</strong>?</p>
                  <p class="text-muted"><small>Esta ação pode ser revertida posteriormente.</small></p>`,
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
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        Toast.fire({
                            icon: 'error',
                            title: xhr.responseJSON?.message || 'Erro ao eliminar projeto'
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
            title: 'Restaurar Projeto',
            html: `<p>Tem a certeza que deseja restaurar o projeto <strong>${projectName}</strong>?</p>`,
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
                    url: `/projects/${projectId}/restore`,
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
                        Toast.fire({
                            icon: 'error',
                            title: xhr.responseJSON?.message || 'Erro ao restaurar projeto'
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
            title: 'Eliminar Permanentemente',
            html: `<p>Tem a certeza ABSOLUTA que deseja eliminar permanentemente o projeto <strong>${projectName}</strong>?</p>
                  <p class="text-danger"><small>Esta ação NÃO pode ser revertida!</small></p>`,
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
                    url: `/projects/${projectId}/force`,
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
                        Toast.fire({
                            icon: 'error',
                            title: xhr.responseJSON?.message || 'Erro ao eliminar projeto'
                        });
                    }
                });
            } else if (result.isDenied) {
                // Restore instead
                $.ajax({
                    url: `/projects/${projectId}/restore`,
                    method: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        Toast.fire({
                            icon: 'success',
                            title: 'Projeto restaurado com sucesso!'
                        });
                        // Switch to active view
                        $('#btnActive').click();
                    },
                    error: function(xhr) {
                        Toast.fire({
                            icon: 'error',
                            title: xhr.responseJSON?.message || 'Erro ao restaurar projeto'
                        });
                    }
                });
            }
        });
    });
    
    // Reset form
    function resetForm() {
        $('#projectForm')[0].reset();
        $('#project_id').val('');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }
});
</script>
@endpush