@extends('layouts.app')

@section('title', 'Gestão de Fornecedores')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header Card -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0 fw-bold">
                            <i class="fas fa-truck me-2"></i>Gestão de Fornecedores
                        </h4>
                        <p class="text-muted mb-0">Gerir fornecedores do sistema</p>
                    </div>
                    <button class="btn btn-primary" id="toggleForm">
                        <i class="fas fa-plus me-2"></i>Novo Fornecedor
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Form Card -->
<div class="row mb-4" id="supplierFormCard" style="display: none;">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0" id="formTitle">
                    <i class="fas fa-user-plus me-2"></i>Adicionar Novo Fornecedor
                </h5>
            </div>
            <div class="card-body">
                <form id="supplierForm">
                    <input type="hidden" id="supplier_id" name="id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nome do Fornecedor *</label>
                            <input type="text" class="form-control" id="name" name="name" required
                                   placeholder="Digite o nome completo">
                            <div class="invalid-feedback" id="name-error"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nuit" class="form-label">NUIT *</label>
                            <input type="text" min="9" max="9" class="form-control" id="nuit" name="nuit" required
                                   placeholder="Ex: 123456789">
                            <div class="invalid-feedback" id="nuit-error"></div>
                            <small class="text-muted">Número Único de Identificação Tributária</small>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" id="cancelForm">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span id="submitText">Guardar Fornecedor</span>
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

<!-- Suppliers Table Card -->
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
                                   placeholder="Pesquisar fornecedores...">
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover" id="suppliersTable" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fornecedor</th>
                                <th>NUIT</th>
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
    .supplier-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #4361ee, #3a56d4);
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
    
    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let currentView = 'active';
    let table;
    
    // Initialize DataTable
    function initializeTable(view = 'active') {
        if ($.fn.DataTable.isDataTable('#suppliersTable')) {
            table.destroy();
        }
        
        const url = view === 'active' 
            ? "{{ route('suppliers.data') }}" 
            : "{{ route('suppliers.data.trashed') }}";
            
        table = $('#suppliersTable').DataTable({
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
                                    <div class="supplier-avatar me-3">
                                        ${initial}
                                    </div>
                                    <div>
                                        <h6 class="mb-0">${data}</h6>
                                        
                                    </div>
                                </div>`;
                    }
                },
                { 
                    data: 'nuit',
                    render: function(data) {
                        return `<code class="bg-light p-1 rounded">${data}</code>`;
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
                                          data-nuit="${row.nuit}"
                                          title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <button class="btn btn-outline-danger btn-delete"
                                            data-id="${data}"
                                            data-name="${row.name}"
                                            title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>`;
                        } else {
                            buttons += `<button class="btn btn-outline-success btn-restore"
                                          data-id="${data}"
                                          data-name="${row.name}"
                                          title="Restaurar">
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
    
    // Initialize with active suppliers
    initializeTable('active');
    
    // Toggle form visibility
    $('#toggleForm').click(function() {
        resetForm();
        $('#formTitle').html('<i class="fas fa-user-plus me-2"></i>Adicionar Novo Fornecedor');
        $('#supplierFormCard').slideToggle('fast', function() {
            if ($(this).is(':visible')) {
                $('#toggleForm').html('<i class="fas fa-times me-2"></i>Fechar Formulário');
            } else {
                $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Novo Fornecedor');
            }
        });
    });
    
    $('#cancelForm').click(function() {
        $('#supplierFormCard').slideUp();
        $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Novo Fornecedor');
        resetForm();
    });
    
    // Toggle between active and inactive suppliers
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
    $('#supplierForm').submit(function(e) {
        e.preventDefault();
        
        const submitBtn = $('#submitBtn');
        const submitText = $('#submitText');
        const loadingSpinner = $('#loadingSpinner');
        const supplierId = $('#supplier_id').val();
        
        // Show loading state
        submitBtn.prop('disabled', true);
        submitText.hide();
        loadingSpinner.show();
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        const url = supplierId ? `/suppliers/${supplierId}` : "{{ route('suppliers.store') }}";
        const method = supplierId ? 'PUT' : 'POST';
        
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
                $('#supplierFormCard').slideUp();
                $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Novo Fornecedor');
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
    
    // Edit supplier
    $(document).on('click', '.btn-edit', function() {
        const supplierId = $(this).data('id');
        const supplierName = $(this).data('name');
        const supplierNuit = $(this).data('nuit');
        
        // Show form
        $('#supplierFormCard').slideDown();
        $('#toggleForm').html('<i class="fas fa-times me-2"></i>Fechar Formulário');
        
        // Set form title
        $('#formTitle').html(`<i class="fas fa-edit me-2"></i>Editar Fornecedor: ${supplierName}`);
        
        // Fill form data
        $('#supplier_id').val(supplierId);
        $('#name').val(supplierName);
        $('#nuit').val(supplierNuit);
        
        // Scroll to form
        $('html, body').animate({
            scrollTop: $('#supplierFormCard').offset().top - 20
        }, 500);
    });
    
    // Delete supplier (soft delete)
    $(document).on('click', '.btn-delete', function() {
        const supplierId = $(this).data('id');
        const supplierName = $(this).data('name');
        
        Swal.fire({
            title: 'Eliminar Fornecedor',
            html: `<p>Tem a certeza que deseja eliminar o fornecedor <strong>${supplierName}</strong>?</p>
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
                    url: `/suppliers/${supplierId}`,
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
                            title: xhr.responseJSON?.message || 'Erro ao eliminar fornecedor'
                        });
                    }
                });
            }
        });
    });
    
    // Restore supplier
    $(document).on('click', '.btn-restore', function() {
        const supplierId = $(this).data('id');
        const supplierName = $(this).data('name');
        
        Swal.fire({
            title: 'Restaurar Fornecedor',
            html: `<p>Tem a certeza que deseja restaurar o fornecedor <strong>${supplierName}</strong>?</p>`,
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
                    url: `/suppliers/${supplierId}/restore`,
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
                            title: xhr.responseJSON?.message || 'Erro ao restaurar fornecedor'
                        });
                    }
                });
            }
        });
    });
    
    // Force delete supplier
    $(document).on('click', '.btn-force-delete', function() {
        const supplierId = $(this).data('id');
        const supplierName = $(this).data('name');
        
        Swal.fire({
            title: 'Eliminar Permanentemente',
            html: `<p>Tem a certeza ABSOLUTA que deseja eliminar permanentemente o fornecedor <strong>${supplierName}</strong>?</p>
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
                    url: `/suppliers/${supplierId}/force`,
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
                            title: xhr.responseJSON?.message || 'Erro ao eliminar fornecedor'
                        });
                    }
                });
            } else if (result.isDenied) {
                // Restore instead
                $.ajax({
                    url: `/suppliers/${supplierId}/restore`,
                    method: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        Toast.fire({
                            icon: 'success',
                            title: 'Fornecedor restaurado com sucesso!'
                        });
                        // Switch to active view
                        $('#btnActive').click();
                    },
                    error: function(xhr) {
                        Toast.fire({
                            icon: 'error',
                            title: xhr.responseJSON?.message || 'Erro ao restaurar fornecedor'
                        });
                    }
                });
            }
        });
    });
    
    // Reset form
    function resetForm() {
        $('#supplierForm')[0].reset();
        $('#supplier_id').val('');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }
});
</script>
@endpush