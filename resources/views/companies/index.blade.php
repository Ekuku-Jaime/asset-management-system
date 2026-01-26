@extends('layouts.app')

@section('title', 'Gestão das Instituições')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header Card -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0 fw-bold">
                            <i class="fas fa-building me-2"></i>Gestão de Instituições
                        </h4>
                        <p class="text-muted mb-0">Gerir empresas do sistema</p>
                    </div>
                    <button class="btn btn-primary" id="toggleForm">
                        <i class="fas fa-plus me-2"></i>Nova Empresa
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Form Card -->
<div class="row mb-4" id="companyFormCard" style="display: none;">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0" id="formTitle">
                    <i class="fas fa-building me-2"></i>Adicionar Nova Instituição
                </h5>
            </div>
            <div class="card-body">
                <form id="companyForm">
                    <input type="hidden" id="company_id" name="id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nome da Instituição *</label>
                            <input type="text" class="form-control" id="name" name="name" required
                                   placeholder="Digite o nome da empresa">
                            <div class="invalid-feedback" id="name-error"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="province" class="form-label">Província *</label>
                            <select class="form-select" id="province" name="province" required>
                                <option value="">Selecione uma província</option>
                                @foreach($provinces as $province)
                                    <option value="{{ $province }}">{{ $province }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="province-error"></div>
                            <small class="text-muted">Província onde a instituição está localizada</small>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" id="cancelForm">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span id="submitText">Guardar Instituição</span>
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

<!-- Companies Table Card -->
<div class="row">
    <div class="col-12">
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
                    </div>
                    
                    <div class="d-flex gap-2">
                        <div class="input-group" style="width: 300px;">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control" id="tableSearch" 
                                   placeholder="Pesquisar empresas...">
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover" id="companiesTable" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Empresa</th>
                                <th>Província</th>
                                <th>Registada em</th>
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
    .company-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #7209b7, #560bad);
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
    
    .province-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
    }
    
    .province-maputo { background-color: #4361ee; color: white; }
    .province-gaza { background-color: #06d6a0; color: white; }
    .province-inhambane { background-color: #ef476f; color: white; }
    .province-sofala { background-color: #ffd166; color: #000; }
    .province-manica { background-color: #7209b7; color: white; }
    .province-tete { background-color: #f3722c; color: white; }
    .province-zambezia { background-color: #43aa8b; color: white; }
    .province-nampula { background-color: #277da1; color: white; }
    .province-cabo-delgado { background-color: #f8961e; color: white; }
    .province-niassa { background-color: #90be6d; color: white; }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let currentView = 'active';
    let table;
    
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
    
    // Initialize DataTable
    function initializeTable(view = 'active') {
        if ($.fn.DataTable.isDataTable('#companiesTable')) {
            table.destroy();
        }
        
        const url = view === 'active' 
            ? "{{ route('companies.data') }}" 
            : "{{ route('companies.data.trashed') }}";
            
        table = $('#companiesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: url,
                type: 'GET'
            },
            responsive: true,
            order: [[0, 'desc']],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-PT.json'
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
                                    <div class="company-avatar me-3">
                                        ${initial}
                                    </div>
                                    <div>
                                        <h6 class="mb-0">${data}</h6>
                                      
                                    </div>
                                </div>`;
                    }
                },
                { 
                    data: 'province',
                    render: function(data) {
                        const badgeClass = getProvinceClass(data);
                        return `<span class="badge ${badgeClass} province-badge">${data}</span>`;
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
                        
                        if (currentView === 'active') {
                            buttons += `<button class="btn btn-outline-primary btn-edit"
                                          data-id="${data}"
                                          data-name="${row.name}"
                                          data-province="${row.province}"
                                          title="Editar Empresa">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <button class="btn btn-outline-danger btn-delete"
                                            data-id="${data}"
                                            data-name="${row.name}"
                                            title="Eliminar Empresa">
                                        <i class="fas fa-trash"></i>
                                    </button>`;
                        } else {
                            buttons += `<button class="btn btn-outline-success btn-restore"
                                          data-id="${data}"
                                          data-name="${row.name}"
                                          title="Restaurar Empresa">
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
    
    // Initialize with active companies
    initializeTable('active');
    
    // Toggle form visibility
    $('#toggleForm').click(function() {
        resetForm();
        $('#formTitle').html('<i class="fas fa-building me-2"></i>Adicionar Nova Empresa');
        $('#companyFormCard').slideToggle('fast', function() {
            if ($(this).is(':visible')) {
                $('#toggleForm').html('<i class="fas fa-times me-2"></i>Fechar Formulário');
            } else {
                $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Nova Empresa');
            }
        });
    });
    
    $('#cancelForm').click(function() {
        $('#companyFormCard').slideUp();
        $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Nova Empresa');
        resetForm();
    });
    
    // Toggle between active and inactive companies
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
    $('#companyForm').submit(function(e) {
        e.preventDefault();
        
        const submitBtn = $('#submitBtn');
        const submitText = $('#submitText');
        const loadingSpinner = $('#loadingSpinner');
        const companyId = $('#company_id').val();
        
        // Show loading state
        submitBtn.prop('disabled', true);
        submitText.hide();
        loadingSpinner.show();
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        const url = companyId ? `/companies/${companyId}` : "{{ route('companies.store') }}";
        const method = companyId ? 'PUT' : 'POST';
        
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
                $('#companyFormCard').slideUp();
                $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Nova Empresa');
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
    
    // Edit company
    $(document).on('click', '.btn-edit', function() {
        const companyId = $(this).data('id');
        const companyName = $(this).data('name');
        const companyProvince = $(this).data('province');
        
        // Show form
        $('#companyFormCard').slideDown();
        $('#toggleForm').html('<i class="fas fa-times me-2"></i>Fechar Formulário');
        
        // Set form title
        $('#formTitle').html(`<i class="fas fa-edit me-2"></i>Editar Empresa: ${companyName}`);
        
        // Fill form data
        $('#company_id').val(companyId);
        $('#name').val(companyName);
        $('#province').val(companyProvince);
        
        // Scroll to form
        $('html, body').animate({
            scrollTop: $('#companyFormCard').offset().top - 20
        }, 500);
    });
    
    // Delete company (soft delete)
    $(document).on('click', '.btn-delete', function() {
        const companyId = $(this).data('id');
        const companyName = $(this).data('name');
        
        Swal.fire({
            title: 'Eliminar Empresa',
            html: `<p>Tem a certeza que deseja eliminar a empresa <strong>${companyName}</strong>?</p>
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
                    url: `/companies/${companyId}`,
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
                            title: xhr.responseJSON?.message || 'Erro ao eliminar empresa'
                        });
                    }
                });
            }
        });
    });
    
    // Restore company
    $(document).on('click', '.btn-restore', function() {
        const companyId = $(this).data('id');
        const companyName = $(this).data('name');
        
        Swal.fire({
            title: 'Restaurar Empresa',
            html: `<p>Tem a certeza que deseja restaurar a empresa <strong>${companyName}</strong>?</p>`,
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
                    url: `/companies/${companyId}/restore`,
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
                            title: xhr.responseJSON?.message || 'Erro ao restaurar empresa'
                        });
                    }
                });
            }
        });
    });
    
    // Force delete company
    $(document).on('click', '.btn-force-delete', function() {
        const companyId = $(this).data('id');
        const companyName = $(this).data('name');
        
        Swal.fire({
            title: 'Eliminar Permanentemente',
            html: `<p>Tem a certeza ABSOLUTA que deseja eliminar permanentemente a empresa <strong>${companyName}</strong>?</p>
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
                    url: `/companies/${companyId}/force`,
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
                            title: xhr.responseJSON?.message || 'Erro ao eliminar empresa'
                        });
                    }
                });
            } else if (result.isDenied) {
                // Restore instead
                $.ajax({
                    url: `/companies/${companyId}/restore`,
                    method: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        Toast.fire({
                            icon: 'success',
                            title: 'Empresa restaurada com sucesso!'
                        });
                        // Switch to active view
                        $('#btnActive').click();
                    },
                    error: function(xhr) {
                        Toast.fire({
                            icon: 'error',
                            title: xhr.responseJSON?.message || 'Erro ao restaurar empresa'
                        });
                    }
                });
            }
        });
    });
    
    // Reset form
    function resetForm() {
        $('#companyForm')[0].reset();
        $('#company_id').val('');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }
});
</script>
@endpush