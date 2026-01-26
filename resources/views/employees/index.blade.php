@extends('layouts.app')

@section('title', 'Gestão de Colaboradores')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header Card -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0 fw-bold">
                            <i class="fas fa-user-tie me-2"></i>Gestão de Colaboradores
                        </h4>
                        <p class="text-muted mb-0">Gerir colaboradores do sistema</p>
                    </div>
                    <button class="btn btn-primary" id="toggleForm">
                        <i class="fas fa-plus me-2"></i>Novo Colaborador
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Form Card -->
<div class="row mb-4" id="employeeFormCard" style="display: none;">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0" id="formTitle">
                    <i class="fas fa-user-plus me-2"></i>Adicionar Novo Colaborador
                </h5>
            </div>
            <div class="card-body">
                <form id="employeeForm">
                    <input type="hidden" id="employee_id" name="id">
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="name" class="form-label">Nome Completo *</label>
                            <input type="text" class="form-control" id="name" name="name" required
                                   placeholder="Digite o nome completo">
                            <div class="invalid-feedback" id="name-error"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="document" class="form-label">Documento *</label>
                            <input type="text" class="form-control" id="document" name="document" required
                                   placeholder="Ex: BI/Passaporte">
                            <div class="invalid-feedback" id="document-error"></div>
                            <small class="text-muted">Número do documento de identificação</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="company_id" class="form-label">Empresa *</label>
                            <select class="form-select" id="company_id" name="company_id" required>
                                <option value="">Selecione uma empresa</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" data-province="{{ $company->province }}">
                                        {{ $company->name }} - {{ $company->province }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="company_id-error"></div>
                            <small class="text-muted" id="company-province"></small>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" id="cancelForm">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span id="submitText">Guardar Colaborador</span>
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

<!-- Employees Table Card -->
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
                                   placeholder="Pesquisar colaboradores...">
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover" id="employeesTable" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Colaborador</th>
                                <th>Documento</th>
                                <th>Empresa</th>
                                <th>Registado em</th>
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
    .employee-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #f8961e, #f3722c);
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
    }
    
    .document-badge {
        font-family: 'Courier New', monospace;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.875rem;
    }
    
    .company-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        background-color: #4361ee;
        color: white;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let currentView = 'active';
    let table;
    const isAdmin = {{ auth()->user()->isAdmin() ? 'true' : 'false' }};
    
    // Function to get province badge class
    function getProvinceClass(province) {
        const classes = {
            'Maputo Cidade': 'province-maputo',
            'Maputo Província': 'province-maputo',
            'Gaza': 'province-gaza',
            'Inhambane': 'province-inhambane',
            'Sofala': 'province-sofala',
            'Manica': 'province-manica',
            'Tete': 'province-tete',
            'Zambézia': 'province-zambezia',
            'Nampula': 'province-nampula',
            'Cabo Delgado': 'province-cabo-delgado',
            'Niassa': 'province-niassa'
        };
        return classes[province] || 'bg-secondary';
    }
    
    // Show province when company is selected
    $('#company_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        const province = selectedOption.data('province');
        if (province) {
            $('#company-province').html(`<i class="fas fa-map-marker-alt me-1"></i>${province}`);
        } else {
            $('#company-province').html('');
        }
    });
    
    // Initialize DataTable
    function initializeTable(view = 'active') {
        if ($.fn.DataTable.isDataTable('#employeesTable')) {
            table.destroy();
        }
        
        const url = view === 'active' 
            ? "{{ route('employees.data') }}" 
            : "{{ route('employees.data.trashed') }}";
            
        table = $('#employeesTable').DataTable({
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
                        return `<div class="d-flex align-items-center">
                                    <div class="employee-avatar me-3">
                                        ${initial}
                                    </div>
                                    <div>
                                        <h6 class="mb-0">${data}</h6>
                                       
                                    </div>
                                </div>`;
                    }
                },
                { 
                    data: 'document',
                    render: function(data) {
                        return `<span class="document-badge">${data}</span>`;
                    }
                },
                { 
                    data: 'company_name',
                    render: function(data) {
                        if (data === 'Empresa Eliminada') {
                            return `<span class="badge bg-danger">${data}</span>`;
                        }
                        return `<span class="badge company-badge">${data}</span>`;
                    }
                },
                { 
                    data: 'created_at',
                    render: function(data) {
                        const date = new Date(data);
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
                                          data-document="${row.document}"
                                          data-company-id="${row.company_id}"
                                          title="Editar Colaborador">
                                        <i class="fas fa-edit"></i>
                                    </button>`;
                            
                            // Only show delete button for admins
                            if (isAdmin) {
                                buttons += `<button class="btn btn-outline-danger btn-delete"
                                            data-id="${data}"
                                            data-name="${row.name}"
                                            title="Eliminar Colaborador">
                                        <i class="fas fa-trash"></i>
                                    </button>`;
                            }
                        } else {
                            // Only show restore/force delete for admins
                            if (isAdmin) {
                                buttons += `<button class="btn btn-outline-success btn-restore"
                                          data-id="${data}"
                                          data-name="${row.name}"
                                          title="Restaurar Colaborador">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                    
                                    <button class="btn btn-outline-danger btn-force-delete"
                                            data-id="${data}"
                                            data-name="${row.name}"
                                            title="Eliminar Permanentemente">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>`;
                            } else {
                                buttons += `<span class="text-muted"><small>Apenas para administradores</small></span>`;
                            }
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
    
    // Initialize with active employees
    initializeTable('active');
    
    // Toggle form visibility
    $('#toggleForm').click(function() {
        resetForm();
        $('#formTitle').html('<i class="fas fa-user-plus me-2"></i>Adicionar Novo Colaborador');
        $('#employeeFormCard').slideToggle('fast', function() {
            if ($(this).is(':visible')) {
                $('#toggleForm').html('<i class="fas fa-times me-2"></i>Fechar Formulário');
            } else {
                $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Novo Colaborador');
            }
        });
    });
    
    $('#cancelForm').click(function() {
        $('#employeeFormCard').slideUp();
        $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Novo Colaborador');
        resetForm();
    });
    
    // Toggle between active and inactive employees
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
    $('#employeeForm').submit(function(e) {
        e.preventDefault();
        
        const submitBtn = $('#submitBtn');
        const submitText = $('#submitText');
        const loadingSpinner = $('#loadingSpinner');
        const employeeId = $('#employee_id').val();
        
        // Show loading state
        submitBtn.prop('disabled', true);
        submitText.hide();
        loadingSpinner.show();
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        const url = employeeId ? `/employees/${employeeId}` : "{{ route('employees.store') }}";
        const method = employeeId ? 'PUT' : 'POST';
        
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
                $('#employeeFormCard').slideUp();
                $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Novo Colaborador');
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
    
    // Edit employee
    $(document).on('click', '.btn-edit', function() {
        const employeeId = $(this).data('id');
        const employeeName = $(this).data('name');
        const employeeDocument = $(this).data('document');
        const employeeCompanyId = $(this).data('company-id');
        
        // Show form
        $('#employeeFormCard').slideDown();
        $('#toggleForm').html('<i class="fas fa-times me-2"></i>Fechar Formulário');
        
        // Set form title
        $('#formTitle').html(`<i class="fas fa-edit me-2"></i>Editar Colaborador: ${employeeName}`);
        
        // Fill form data
        $('#employee_id').val(employeeId);
        $('#name').val(employeeName);
        $('#document').val(employeeDocument);
        $('#company_id').val(employeeCompanyId);
        
        // Trigger province display
        $('#company_id').trigger('change');
        
        // Scroll to form
        $('html, body').animate({
            scrollTop: $('#employeeFormCard').offset().top - 20
        }, 500);
    });
    
    // Delete employee (soft delete) - only for admins
    $(document).on('click', '.btn-delete', function() {
        if (!isAdmin) {
            Toast.fire({
                icon: 'warning',
                title: 'Apenas administradores podem eliminar colaboradores'
            });
            return;
        }
        
        const employeeId = $(this).data('id');
        const employeeName = $(this).data('name');
        
        Swal.fire({
            title: 'Eliminar Colaborador',
            html: `<p>Tem a certeza que deseja eliminar o colaborador <strong>${employeeName}</strong>?</p>
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
                    url: `/employees/${employeeId}`,
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
                                title: 'Apenas administradores podem eliminar colaboradores'
                            });
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: xhr.responseJSON?.message || 'Erro ao eliminar colaborador'
                            });
                        }
                    }
                });
            }
        });
    });
    
    // Restore employee - only for admins
    $(document).on('click', '.btn-restore', function() {
        if (!isAdmin) {
            Toast.fire({
                icon: 'warning',
                title: 'Apenas administradores podem restaurar colaboradores'
            });
            return;
        }
        
        const employeeId = $(this).data('id');
        const employeeName = $(this).data('name');
        
        Swal.fire({
            title: 'Restaurar Colaborador',
            html: `<p>Tem a certeza que deseja restaurar o colaborador <strong>${employeeName}</strong>?</p>
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
                    url: `/employees/${employeeId}/restore`,
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
                                title: 'Apenas administradores podem restaurar colaboradores'
                            });
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: xhr.responseJSON?.message || 'Erro ao restaurar colaborador'
                            });
                        }
                    }
                });
            }
        });
    });
    
    // Force delete employee - only for admins
    $(document).on('click', '.btn-force-delete', function() {
        if (!isAdmin) {
            Toast.fire({
                icon: 'warning',
                title: 'Apenas administradores podem eliminar permanentemente colaboradores'
            });
            return;
        }
        
        const employeeId = $(this).data('id');
        const employeeName = $(this).data('name');
        
        Swal.fire({
            title: 'Eliminar Permanentemente',
            html: `<p>Tem a certeza ABSOLUTA que deseja eliminar permanentemente o colaborador <strong>${employeeName}</strong>?</p>
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
                    url: `/employees/${employeeId}/force`,
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
                                title: 'Apenas administradores podem eliminar permanentemente colaboradores'
                            });
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: xhr.responseJSON?.message || 'Erro ao eliminar colaborador'
                            });
                        }
                    }
                });
            } else if (result.isDenied) {
                // Restore instead
                $.ajax({
                    url: `/employees/${employeeId}/restore`,
                    method: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        Toast.fire({
                            icon: 'success',
                            title: 'Colaborador restaurado com sucesso!'
                        });
                        // Switch to active view
                        $('#btnActive').click();
                    },
                    error: function(xhr) {
                        Toast.fire({
                            icon: 'error',
                            title: xhr.responseJSON?.message || 'Erro ao restaurar colaborador'
                        });
                    }
                });
            }
        });
    });
    
    // Reset form
    function resetForm() {
        $('#employeeForm')[0].reset();
        $('#employee_id').val('');
        $('#company-province').html('');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }
});
</script>
@endpush