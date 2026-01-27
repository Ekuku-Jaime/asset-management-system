@extends('layouts.app')

@section('title', 'Gestão de Faturas')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header Card -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0 fw-bold">
                            <i class="fas fa-file-invoice-dollar me-2"></i>Gestão de Faturas
                        </h4>
                        <p class="text-muted mb-0">Gerir faturas do sistema</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-info" id="btnReport">
                            <i class="fas fa-chart-bar me-2"></i>Relatório
                        </button>
                        <button class="btn btn-primary" id="toggleForm">
                            <i class="fas fa-plus me-2"></i>Nova Fatura
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Form Card -->
<div class="row mb-4" id="invoiceFormCard" style="display: none;">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0" id="formTitle">
                    <i class="fas fa-file-invoice-dollar me-2"></i>Adicionar Nova Fatura
                </h5>
            </div>
            <div class="card-body">
                <form id="invoiceForm" enctype="multipart/form-data">
                    <input type="hidden" id="invoice_id" name="id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="number" class="form-label">Número da Fatura *</label>
                            <input type="text" class="form-control" id="number" name="number" required
                                   placeholder="Ex: FT/2023/001">
                            <div class="invalid-feedback" id="number-error"></div>
                            <small class="text-muted">Código único da fatura</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="date" class="form-label">Data da Fatura *</label>
                            <input type="date" class="form-control" id="date" name="date" required
                                   max="{{ date('Y-m-d') }}">
                            <div class="invalid-feedback" id="date-error"></div>
                            <small class="text-muted">Data da emissão (não pode ser futura)</small>
                        </div>
                    </div>
                    
                    <!-- Documentos -->
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="documents" class="form-label">Documentos de Comprovativo (Opcional)</label>
                            <input type="file" class="form-control" id="documents" name="documents[]" 
                                   multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
                            <div class="invalid-feedback" id="documents-error"></div>
                            
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Tipos permitidos: PDF, JPG, PNG, DOC, XLS (Máx. 10MB cada)
                            </small>
                            
                            <!-- Preview de documentos -->
                            <div id="documentsPreview" class="mt-3"></div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" id="cancelForm">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span id="submitText">Guardar Fatura</span>
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

<!-- Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-chart-bar me-2"></i>Relatório de Faturas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="reportForm">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Data Inicial *</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">Data Final *</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" required>
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
                                    <th>Número</th>
                                    <th>Data</th>
                                    <th>Estado</th>
                                    <th>Criada em</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0" id="reportSummary"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Documents Modal -->
<div class="modal fade" id="documentsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-paperclip me-2"></i>Documentos da Fatura
                    <span id="modalInvoiceNumber" class="text-primary"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="documentsList" class="mb-4"></div>
                
                <!-- Upload new documents -->
                <div class="card" id="uploadNewCard">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-upload me-2"></i>Adicionar Novos Documentos
                        </h6>
                    </div>
                    <div class="card-body">
                        <form id="uploadDocumentsForm" enctype="multipart/form-data">
                            <input type="hidden" id="modalInvoiceId" name="invoice_id">
                            
                            <div class="mb-3">
                                <label for="newDocuments" class="form-label">Selecionar Ficheiros</label>
                                <input type="file" class="form-control" id="newDocuments" 
                                       name="documents[]" multiple>
                                <small class="text-muted">Selecione um ou mais ficheiros</small>
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload me-2"></i>Carregar Documentos
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Invoices Table Card -->
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
                                   placeholder="Pesquisar faturas...">
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover" id="invoicesTable" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fatura</th>
                                <th>Data</th>
                                <th>Estado</th>
                                <th>Registada em</th>
                                <th>Estado Sistema</th>
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
    .invoice-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #ef476f, #e83e5f);
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
    
    .invoice-number {
        font-family: 'Courier New', monospace;
        font-weight: bold;
        color: #4361ee;
    }
    
    .date-badge {
        font-size: 0.875rem;
        padding: 0.25rem 0.75rem;
    }
    
    .future-date {
        border-color: #ef476f !important;
        background-color: rgba(239, 71, 111, 0.1) !important;
    }
    
    .file-icon {
        font-size: 1.5rem;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let currentView = 'active';
    let table;
    const isAdmin = {{ auth()->user()->isAdmin() ? 'true' : 'false' }};
    
    // Initialize DataTable
    function initializeTable(view = 'active') {
        if ($.fn.DataTable.isDataTable('#invoicesTable')) {
            table.destroy();
        }
        
        const url = view === 'active' 
            ? "{{ route('invoices.data') }}" 
            : "{{ route('invoices.data.trashed') }}";
            
        table = $('#invoicesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: url,
                type: 'GET'
            },
            responsive: true,
            order: [[0, 'desc']],
            
            columns: [
                { 
                    data: 'id',
                    className: 'fw-semibold',
                    width: '5%'
                },
                { 
                    data: 'number',
                    render: function(data, type, row) {
                        const initial = data.charAt(0).toUpperCase();
                        return `<div class="d-flex align-items-center">
                                    <div class="invoice-avatar me-3">
                                        ${initial}
                                    </div>
                                    <div>
                                        <h6 class="mb-0 invoice-number">${data}</h6>
                                    </div>
                                </div>`;
                    }
                },
                { 
                    data: 'date',
                    render: function(data) {
                        const date = new Date(data);
                        const today = new Date();
                        const isFuture = date > today;
                        
                        let badgeClass = 'bg-light text-dark';
                        let icon = '';
                        
                        if (isFuture) {
                            badgeClass = 'bg-warning text-dark';
                            icon = '<i class="fas fa-exclamation-triangle me-1"></i>';
                        } else if (date.getFullYear() === today.getFullYear()) {
                            badgeClass = 'bg-info text-white';
                        }
                        
                        return `<span class="badge ${badgeClass} date-badge">
                                    ${icon}${date.toLocaleDateString('pt-PT')}
                                </span>`;
                    }
                },
                { 
                    data: 'status',
                    className: 'text-center',
                    render: function(data) {
                        let badgeClass = 'bg-warning';
                        let icon = '<i class="fas fa-times-circle me-1"></i>';
                        let text = 'Incompleto';
                        console.log(data);
                        
                        
                        if (data === 'completo') {
                            badgeClass = 'bg-success';
                            icon = '<i class="fas fa-check-circle me-1"></i>';
                            text = 'Completo';
                        }
                        
                        return `<span class="badge ${badgeClass}">
                                    ${icon}${text}
                                </span>`;
                    }
                },
                { 
                    data: 'created_at',
                    render: function(data) {
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
                    render: function(data) {
                        console.log(data);
                        
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
                        
                        // Botão de documentos (sempre visível)
                        buttons += `<button class="btn btn-outline-info btn-documents"
                                      data-id="${data}"
                                      data-number="${row.number}"
                                      title="Ver Documentos">
                                    <i class="fas fa-paperclip"></i>
                                </button>`;
                        
                        if (currentView === 'active') {
                            buttons += `<button class="btn btn-outline-primary btn-edit"
                                          data-id="${data}"
                                          data-number="${row.number}"
                                          data-date="${row.date}"
                                          title="Editar Fatura">
                                        <i class="fas fa-edit"></i>
                                    </button>`;
                            
                            // Only show delete button for admins
                            if (isAdmin) {
                                buttons += `<button class="btn btn-outline-danger btn-delete"
                                            data-id="${data}"
                                            data-number="${row.number}"
                                            title="Eliminar Fatura">
                                        <i class="fas fa-trash"></i>
                                    </button>`;
                            }
                        } else {
                            // Only show restore/force delete for admins
                            if (isAdmin) {
                                buttons += `<button class="btn btn-outline-success btn-restore"
                                          data-id="${data}"
                                          data-number="${row.number}"
                                          title="Restaurar Fatura">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                    
                                    <button class="btn btn-outline-danger btn-force-delete"
                                            data-id="${data}"
                                            data-number="${row.number}"
                                            title="Eliminar Permanentemente">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>`;
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
    
    // Initialize with active invoices
    initializeTable('active');
    
    // Toggle form visibility
    $('#toggleForm').click(function() {
        resetForm();
        $('#formTitle').html('<i class="fas fa-file-invoice-dollar me-2"></i>Adicionar Nova Fatura');
        $('#invoiceFormCard').slideToggle('fast', function() {
            if ($(this).is(':visible')) {
                $('#toggleForm').html('<i class="fas fa-times me-2"></i>Fechar Formulário');
            } else {
                $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Nova Fatura');
            }
        });
    });
    
    $('#cancelForm').click(function() {
        $('#invoiceFormCard').slideUp();
        $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Nova Fatura');
        resetForm();
    });
    
    // Toggle between active and inactive invoices
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
    
    // Show report modal
    $('#btnReport').click(function() {
        $('#reportModal').modal('show');
        resetReport();
    });
    
    // Date validation for invoice form
    $('#date').on('change', function() {
        const selectedDate = new Date($(this).val());
        const today = new Date();
        
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
    $('#invoiceForm').submit(function(e) {
        e.preventDefault();
        
        const submitBtn = $('#submitBtn');
        const submitText = $('#submitText');
        const loadingSpinner = $('#loadingSpinner');
        const invoiceId = $('#invoice_id').val();
        
        // Validate date
        const selectedDate = new Date($('#date').val());
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate > today) {
            $('#date').addClass('is-invalid future-date');
            $('#date-error').text('A data da fatura não pode ser no futuro');
            return;
        }
        
        // Show loading state
        submitBtn.prop('disabled', true);
        submitText.hide();
        loadingSpinner.show();
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        const url = invoiceId ? `/invoices/${invoiceId}` : "{{ route('invoices.store') }}";
        const method = invoiceId ? 'PUT' : 'POST';
        
        const formData = new FormData(this);
        
        $.ajax({
            url: url,
            method: method,
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                Toast.fire({
                    icon: 'success',
                    title: response.message
                });
                
                resetForm();
                table.ajax.reload();
                
                // Hide form after success
                $('#invoiceFormCard').slideUp();
                $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Nova Fatura');
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
    
    // Edit invoice
    $(document).on('click', '.btn-edit', function() {
        const invoiceId = $(this).data('id');
        const invoiceNumber = $(this).data('number');
        const invoiceDate = $(this).data('date');
        
        // Show loading
        const button = $(this);
        button.prop('disabled', true).find('i').addClass('fa-spinner fa-spin');
        
        // Get invoice data with documents
        $.ajax({
            url: `/invoices/${invoiceId}/edit`,
            method: 'GET',
            success: function(response) {
                // Show form
                $('#invoiceFormCard').slideDown();
                $('#toggleForm').html('<i class="fas fa-times me-2"></i>Fechar Formulário');
                
                // Set form title
                $('#formTitle').html(`<i class="fas fa-edit me-2"></i>Editar Fatura: ${invoiceNumber}`);
                
                // Format date for input field (YYYY-MM-DD)
                const dateObj = new Date(invoiceDate);
                const formattedDate = dateObj.toISOString().split('T')[0];
                
                // Fill form data
                $('#invoice_id').val(invoiceId);
                $('#number').val(invoiceNumber);
                $('#date').val(formattedDate);
                
                // Show existing documents
                previewDocuments(response.data.documents);
                
                // Validate date
                $('#date').trigger('change');
                
                // Scroll to form
                $('html, body').animate({
                    scrollTop: $('#invoiceFormCard').offset().top - 20
                }, 500);
            },
            error: function() {
                Toast.fire({
                    icon: 'error',
                    title: 'Erro ao carregar dados da fatura'
                });
            },
            complete: function() {
                button.prop('disabled', false).find('i').removeClass('fa-spinner fa-spin');
            }
        });
    });
    
    // Show documents modal
    $(document).on('click', '.btn-documents', function() {
        const invoiceId = $(this).data('id');
        const invoiceNumber = $(this).data('number');
        showDocuments(invoiceId, invoiceNumber);
    });
    
    // Delete invoice (soft delete) - only for admins
    $(document).on('click', '.btn-delete', function() {
        if (!isAdmin) {
            Toast.fire({
                icon: 'warning',
                title: 'Apenas administradores podem eliminar faturas'
            });
            return;
        }
        
        const invoiceId = $(this).data('id');
        const invoiceNumber = $(this).data('number');
        
        Swal.fire({
            title: 'Eliminar Fatura',
            html: `<p>Tem a certeza que deseja eliminar a fatura <strong>${invoiceNumber}</strong>?</p>
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
                    url: `/invoices/${invoiceId}`,
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
                                title: 'Apenas administradores podem eliminar faturas'
                            });
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: xhr.responseJSON?.message || 'Erro ao eliminar fatura'
                            });
                        }
                    }
                });
            }
        });
    });
    
    // Restore invoice - only for admins
    $(document).on('click', '.btn-restore', function() {
        if (!isAdmin) {
            Toast.fire({
                icon: 'warning',
                title: 'Apenas administradores podem restaurar faturas'
            });
            return;
        }
        
        const invoiceId = $(this).data('id');
        const invoiceNumber = $(this).data('number');
        
        Swal.fire({
            title: 'Restaurar Fatura',
            html: `<p>Tem a certeza que deseja restaurar a fatura <strong>${invoiceNumber}</strong>?</p>
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
                    url: `/invoices/${invoiceId}/restore`,
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
                                title: 'Apenas administradores podem restaurar faturas'
                            });
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: xhr.responseJSON?.message || 'Erro ao restaurar fatura'
                            });
                        }
                    }
                });
            }
        });
    });
    
    // Force delete invoice - only for admins
    $(document).on('click', '.btn-force-delete', function() {
        if (!isAdmin) {
            Toast.fire({
                icon: 'warning',
                title: 'Apenas administradores podem eliminar permanentemente faturas'
            });
            return;
        }
        
        const invoiceId = $(this).data('id');
        const invoiceNumber = $(this).data('number');
        
        Swal.fire({
            title: 'Eliminar Permanentemente',
            html: `<p>Tem a certeza ABSOLUTA que deseja eliminar permanentemente a fatura <strong>${invoiceNumber}</strong>?</p>
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
                    url: `/invoices/${invoiceId}/force`,
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
                                title: 'Apenas administradores podem eliminar permanentemente faturas'
                            });
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: xhr.responseJSON?.message || 'Erro ao eliminar fatura'
                            });
                        }
                    }
                });
            } else if (result.isDenied) {
                // Restore instead
                $.ajax({
                    url: `/invoices/${invoiceId}/restore`,
                    method: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        Toast.fire({
                            icon: 'success',
                            title: 'Fatura restaurada com sucesso!'
                        });
                        // Switch to active view
                        $('#btnActive').click();
                    },
                    error: function(xhr) {
                        Toast.fire({
                            icon: 'error',
                            title: xhr.responseJSON?.message || 'Erro ao restaurar fatura'
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
            url: "{{ route('invoices.report') }}",
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.data.length > 0) {
                    // Populate table
                    response.data.forEach(function(invoice) {
                        const date = new Date(invoice.date);
                        const created = new Date(invoice.created_at);
                        const statusBadge = invoice.status === 'completo' ? 
                            '<span class="badge bg-success">Completo</span>' : 
                            '<span class="badge bg-warning">Incompleto</span>';
                        
                        $('#reportTable tbody').append(`
                            <tr>
                                <td><strong>${invoice.number}</strong></td>
                                <td>${date.toLocaleDateString('pt-PT')}</td>
                                <td>${statusBadge}</td>
                                <td>${created.toLocaleDateString('pt-PT')}</td>
                            </tr>
                        `);
                    });
                    
                    // Show summary
                    const startDate = new Date(response.period.start_date);
                    const endDate = new Date(response.period.end_date);
                    
                    $('#reportSummary').html(`
                        <i class="fas fa-info-circle me-1"></i>
                        Foram encontradas <strong>${response.count}</strong> faturas no período de 
                        <strong>${startDate.toLocaleDateString('pt-PT')}</strong> a 
                        <strong>${endDate.toLocaleDateString('pt-PT')}</strong>
                    `);
                    
                    $('#reportResults').show();
                } else {
                    $('#reportSummary').html(`
                        <div class="text-center py-3">
                            <i class="fas fa-search fa-2x text-muted mb-3"></i>
                            <p class="mb-0">Nenhuma fatura encontrada no período selecionado.</p>
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
    
    // Upload new documents
    $('#uploadDocumentsForm').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const invoiceId = $('#modalInvoiceId').val();
        
        $.ajax({
            url: `/invoices/${invoiceId}/documents`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Toast.fire({
                    icon: 'success',
                    title: response.message
                });
                loadDocuments(invoiceId);
                table.ajax.reload();
                $('#uploadDocumentsForm')[0].reset();
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Erro de validação nos ficheiros'
                    });
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: xhr.responseJSON?.message || 'Erro ao carregar documentos'
                    });
                }
            }
        });
    });
    
    // Remove document from edit form
    $(document).on('click', '.btn-remove-existing-doc', function() {
        const docId = $(this).data('id');
        const docName = $(this).closest('.d-flex').find('span').text();
        
        Swal.fire({
            title: 'Remover Documento',
            html: `Tem certeza que deseja remover <strong>${docName}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, remover',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed && isAdmin) {
                $.ajax({
                    url: `/invoices/documents/${docId}`,
                    method: 'DELETE',
                    success: function() {
                        Toast.fire({
                            icon: 'success',
                            title: 'Documento removido com sucesso!'
                        });
                        // Remove from preview
                        $(this).closest('.d-flex').remove();
                        // Update status in table
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        Toast.fire({
                            icon: 'error',
                            title: xhr.responseJSON?.message || 'Erro ao remover documento'
                        });
                    }
                });
            } else if (!isAdmin) {
                Toast.fire({
                    icon: 'warning',
                    title: 'Apenas administradores podem remover documentos'
                });
            }
        });
    });
    
    // Functions
    function showDocuments(invoiceId, invoiceNumber) {
        $('#modalInvoiceId').val(invoiceId);
        $('#modalInvoiceNumber').text(invoiceNumber);
        
        // Only show upload card for admins
        if (!isAdmin) {
            $('#uploadNewCard').hide();
        } else {
            $('#uploadNewCard').show();
        }
        
        loadDocuments(invoiceId);
        $('#documentsModal').modal('show');
    }
    
    function loadDocuments(invoiceId) {
        $.ajax({
            url: `/invoices/${invoiceId}/documents`,
            method: 'GET',
            success: function(response) {
                let html = '';
                
                if (response.data.length > 0) {
                    html += '<h6 class="mb-3">Documentos Anexados</h6>';
                    html += '<div class="list-group">';
                    
                    response.data.forEach(function(doc) {
                        const sizeMB = (doc.size / (1024*1024)).toFixed(2);
                        const icon = getFileIcon(doc.mime_type);
                        
                        html += `
                        <div class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <i class="${icon} fa-lg me-3 text-primary"></i>
                                    <div>
                                        <h6 class="mb-1">${doc.original_name}</h6>
                                        <small class="text-muted">
                                            ${doc.mime_type} • ${sizeMB} MB
                                        </small>
                                    </div>
                                </div>
                                <div class="btn-group">
                                    <a href="/invoices/documents/${doc.id}/download" 
                                       class="btn btn-sm btn-outline-primary"
                                       target="_blank">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    ${isAdmin ? 
                                        `<button class="btn btn-sm btn-outline-danger btn-remove-doc"
                                           data-id="${doc.id}"
                                           data-name="${doc.original_name}">
                                            <i class="fas fa-trash"></i>
                                        </button>` : ''
                                    }
                                </div>
                            </div>
                        </div>`;
                    });
                    
                    html += '</div>';
                } else {
                    html += `
                    <div class="text-center py-4">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">Nenhum documento anexado</p>
                    </div>`;
                }
                
                $('#documentsList').html(html);
            },
            error: function() {
                $('#documentsList').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Erro ao carregar documentos
                    </div>
                `);
            }
        });
    }

    // Evento para remover documento (no modal de documentos)
$(document).on('click', '.btn-remove-doc', function() {
    const docId = $(this).data('id');
    const docName = $(this).data('name');
    
    Swal.fire({
        title: 'Remover Documento',
        html: `Tem certeza que deseja remover <strong>${docName}</strong>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim, remover',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/invoices/documents/${docId}`, // CORRIGIDO
                method: 'DELETE',
                success: function(response) {
                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    });
                    // Recarregar lista de documentos
                    loadDocuments($('#modalInvoiceId').val());
                    // Atualizar tabela para refletir mudança de status
                    table.ajax.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 403) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Apenas administradores podem remover documentos'
                        });
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: xhr.responseJSON?.message || 'Erro ao remover documento'
                        });
                    }
                }
            });
        }
    });
});
    
    function getFileIcon(mimeType) {
        if (mimeType.includes('pdf')) return 'fas fa-file-pdf';
        if (mimeType.includes('image')) return 'fas fa-file-image';
        if (mimeType.includes('word') || mimeType.includes('document')) return 'fas fa-file-word';
        if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return 'fas fa-file-excel';
        return 'fas fa-file';
    }
    
    function previewDocuments(documents) {
        let html = '';
        
        if (documents && documents.length > 0) {
            html += '<div class="card"><div class="card-body"><h6>Documentos Existentes</h6>';
            
            documents.forEach(function(doc) {
                const icon = getFileIcon(doc.mime_type);
                
                html += `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center">
                        <i class="${icon} me-2"></i>
                        <span>${doc.original_name}</span>
                    </div>
                    <div>
                        <a href="/invoices/documents/${doc.id}/download" 
                           class="btn btn-sm btn-outline-primary"
                           target="_blank">
                            <i class="fas fa-download"></i>
                        </a>
                        ${isAdmin ? 
                            `<button class="btn btn-sm btn-outline-danger btn-remove-existing-doc"
                               data-id="${doc.id}">
                                <i class="fas fa-times"></i>
                            </button>` : ''
                        }
                    </div>
                </div>`;
            });
            
            html += '</div></div>';
        }
        
        $('#documentsPreview').html(html);
    }
    
    function resetForm() {
        $('#invoiceForm')[0].reset();
        $('#invoice_id').val('');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#date').removeClass('future-date');
        $('#documentsPreview').empty();
    }
    
    function resetReport() {
        $('#reportForm')[0].reset();
        $('#reportResults').hide();
        $('#reportTable tbody').empty();
    }
});
</script>
@endpush