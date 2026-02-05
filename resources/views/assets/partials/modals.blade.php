<!-- ========== MODAL DE ATRIBUIÇÃO ========== -->
<div class="modal fade" id="assignmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-check me-2"></i>Atribuir Activo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="assignmentForm">
                    <input type="hidden" id="assignment_asset_id" name="asset_id">
                    
                    <div class="mb-3">
                        <label for="assignment_employee_id" class="form-label">Colaborador *</label>
                        <select class="form-select select2-modal" id="assignment_employee_id" name="employee_id" required>
                            <option value="">Selecione um colaborador</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">
                                    {{ $employee->name }} ({{ $employee->document }}) - {{ $employee->company->name ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="assignment_employee_id-error"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="assignment_date" class="form-label">Data de Atribuição</label>
                        <input type="date" class="form-control" id="assignment_date" name="assignment_date"
                               value="{{ date('Y-m-d') }}">
                    </div>
                    
                    <div class="mb-3">
                        <label for="assignment_notes" class="form-label">Observações</label>
                        <textarea class="form-control" id="assignment_notes" name="notes" 
                                  rows="2" placeholder="Observações sobre a atribuição..."></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-check me-2"></i>Atribuir
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ========== MODAL DE MANUTENÇÃO ========== -->
<div class="modal fade" id="maintenanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-tools me-2"></i>Enviar para Manutenção
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="maintenanceForm">
                    <input type="hidden" id="maintenance_asset_id" name="asset_id">
                    
                    <div class="mb-3">
                        <label for="maintenance_date" class="form-label">Data de Manutenção *</label>
                        <input type="date" class="form-control" id="maintenance_date" name="maintenance_date"
                               value="{{ date('Y-m-d') }}" required>
                        <div class="invalid-feedback" id="maintenance_date-error"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="expected_return" class="form-label">Retorno Esperado</label>
                        <input type="date" class="form-control" id="expected_return" name="expected_return">
                    </div>
                    
                    <div class="mb-3">
                        <label for="maintenance_description" class="form-label">Descrição *</label>
                        <textarea class="form-control" id="maintenance_description" name="description" 
                                  rows="3" required placeholder="Descreva o problema ou serviço necessário..."></textarea>
                        <div class="invalid-feedback" id="maintenance_description-error"></div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="provider" class="form-label">Fornecedor</label>
                            <input type="text" class="form-control" id="provider" name="provider"
                                   placeholder="Nome do fornecedor de manutenção">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cost" class="form-label">Custo Estimado (MT)</label>
                            <div class="input-group">
                                <span class="input-group-text">MT</span>
                                <input type="number" class="form-control" id="cost" name="cost"
                                       min="0" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-tools me-2"></i>Enviar para Manutenção
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ========== MODAL DE COMPLETAR MANUTENÇÃO ========== -->
<div class="modal fade" id="completeMaintenanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>Completar Manutenção
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="completeMaintenanceForm">
                    <input type="hidden" id="complete_maintenance_asset_id" name="asset_id">
                    
                    <div class="mb-3">
                        <label for="return_date" class="form-label">Data de Retorno *</label>
                        <input type="date" class="form-control" id="return_date" name="return_date"
                               value="{{ date('Y-m-d') }}" required>
                        <div class="invalid-feedback" id="return_date-error"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="maintenance_cost" class="form-label">Custo Real (MT)</label>
                        <div class="input-group">
                            <span class="input-group-text">MT</span>
                            <input type="number" class="form-control" id="maintenance_cost" name="cost"
                                   min="0" step="0.01" placeholder="0.00">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="maintenance_result_description" class="form-label">Descrição do Resultado</label>
                        <textarea class="form-control" id="maintenance_result_description" name="description" 
                                  rows="3" placeholder="Descreva o resultado da manutenção..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="maintenance_notes" class="form-label">Observações</label>
                        <textarea class="form-control" id="maintenance_notes" name="notes" 
                                  rows="2" placeholder="Observações adicionais..."></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check-circle me-2"></i>Completar Manutenção
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ========== MODAL DE DOCUMENTOS ========== -->
<div class="modal fade" id="documentsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-paperclip me-2"></i>Documentos do Activo
                    <span id="modalAssetCode" class="text-primary ms-2"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <!-- Lista de documentos -->
                <div id="documentsList" class="mb-4">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">A carregar...</span>
                        </div>
                    </div>
                </div>
                
                <!-- Upload de novos documentos -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-upload me-2"></i>Adicionar Novos Documentos
                        </h6>
                    </div>
                    <div class="card-body">
                        <form id="uploadDocumentsForm" enctype="multipart/form-data">
                            <input type="hidden" id="modalAssetId" name="asset_id">
                            
                            <div class="mb-3">
                                <label for="newDocuments" class="form-label">Selecionar Ficheiros</label>
                                <input type="file" class="form-control" id="newDocuments" 
                                       name="documents[]" multiple>
                                <small class="text-muted">Selecione um ou mais ficheiros</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="document_type" class="form-label">Tipo de Documento</label>
                                <select class="form-select" id="document_type" name="document_type">
                                    <option value="outro">Outro</option>
                                    <option value="manual">Manual</option>
                                    <option value="garantia">Garantia</option>
                                    <option value="fatura">Fatura</option>
                                    <option value="comprovativo">Comprovativo</option>
                                    <option value="certificado">Certificado</option>
                                </select>
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

<!-- ========== MODAL DE CONFIRMAÇÃO DE ELIMINAÇÃO ========== -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirmar Eliminação
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja eliminar este activo?</p>
                <p class="text-muted small">Esta ação não pode ser desfeita. O activo será movido para a lista de eliminados.</p>
                
                <input type="hidden" id="delete_asset_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<!-- ========== MODAL DE CONFIRMAÇÃO DE RESTAURAÇÃO ========== -->
<div class="modal fade" id="confirmRestoreModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-success">
                    <i class="fas fa-undo me-2"></i>Confirmar Restauração
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja restaurar este activo?</p>
                <p class="text-muted small">O activo será movido de volta para a lista de activos ativos.</p>
                
                <input type="hidden" id="restore_asset_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="confirmRestoreBtn">Restaurar</button>
            </div>
        </div>
    </div>
</div>

<!-- ========== MODAL DE DETALHES DO ACTIVO ========== -->
<div class="modal fade" id="assetDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle me-2"></i>Detalhes do Activo
                    <span id="detailsAssetCode" class="text-primary ms-2"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div id="assetDetailsContent">
                    <!-- Conteúdo será carregado via AJAX -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos específicos para modais */
    .select2-modal {
        width: 100% !important;
    }
    
    .modal-content {
        border: none;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px 10px 0 0;
    }
    
    .modal-header .btn-close {
        filter: invert(1);
    }
    
    .document-item {
        transition: all 0.3s ease;
    }
    
    .document-item:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
    }
</style>

<script>
// ========== FUNÇÕES ESPECÍFICAS PARA MODAIS ==========

// Inicializar Select2 nos modais
function initModalSelect2() {
    $('#assignmentModal').on('shown.bs.modal', function() {
        $('#assignment_employee_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Selecione um colaborador',
            dropdownParent: $('#assignmentModal')
        });
    });
    
    $('#assignmentModal').on('hidden.bs.modal', function() {
        $('#assignment_employee_id').select2('destroy');
    });
}

// Formulário de atribuição
$('#assignmentForm').on('submit', function(e) {
    e.preventDefault();
    const assetId = $('#assignment_asset_id').val();
    
    $.ajax({
        url: '{{ route("assets.assign", ":id") }}'.replace(':id', assetId),
        type: 'POST',
        data: $(this).serialize(),
        headers: { 'X-CSRF-TOKEN': csrfToken },
        success: function(response) {
            if (response.success) {
                showSuccess(response.message);
                $('#assignmentModal').modal('hide');
                loadAssets();
            } else {
                showError(response.message);
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                displayModalErrors(xhr.responseJSON.errors, '#assignmentForm');
            } else {
                showError(xhr.responseJSON?.message || 'Erro ao atribuir activo');
            }
        }
    });
});

// Formulário de manutenção
$('#maintenanceForm').on('submit', function(e) {
    e.preventDefault();
    const assetId = $('#maintenance_asset_id').val();
    
    $.ajax({
        url: '{{ route("assets.send-to-maintenance", ":id") }}'.replace(':id', assetId),
        type: 'POST',
        data: $(this).serialize(),
        headers: { 'X-CSRF-TOKEN': csrfToken },
        success: function(response) {
            if (response.success) {
                showSuccess(response.message);
                $('#maintenanceModal').modal('hide');
                loadAssets();
            } else {
                showError(response.message);
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                displayModalErrors(xhr.responseJSON.errors, '#maintenanceForm');
            } else {
                showError(xhr.responseJSON?.message || 'Erro ao enviar para manutenção');
            }
        }
    });
});

// Formulário de completar manutenção
$('#completeMaintenanceForm').on('submit', function(e) {
    e.preventDefault();
    const assetId = $('#complete_maintenance_asset_id').val();
    
    $.ajax({
        url: '{{ route("assets.complete-maintenance", ":id") }}'.replace(':id', assetId),
        type: 'POST',
        data: $(this).serialize(),
        headers: { 'X-CSRF-TOKEN': csrfToken },
        success: function(response) {
            if (response.success) {
                showSuccess(response.message);
                $('#completeMaintenanceModal').modal('hide');
                loadAssets();
            } else {
                showError(response.message);
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                displayModalErrors(xhr.responseJSON.errors, '#completeMaintenanceForm');
            } else {
                showError(xhr.responseJSON?.message || 'Erro ao completar manutenção');
            }
        }
    });
});

// Upload de documentos
$('#uploadDocumentsForm').on('submit', function(e) {
    e.preventDefault();
    const assetId = $('#modalAssetId').val();
    const formData = new FormData(this);
    
    $.ajax({
        url: '{{ route("assets.upload-documents", ":id") }}'.replace(':id', assetId),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: { 'X-CSRF-TOKEN': csrfToken },
        success: function(response) {
            if (response.success) {
                showSuccess(response.message);
                $('#newDocuments').val('');
                loadAssetDocuments(assetId);
            } else {
                showError(response.message);
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                showError('Erro ao carregar documentos: ' + Object.values(xhr.responseJSON.errors).flat().join(', '));
            } else {
                showError(xhr.responseJSON?.message || 'Erro ao carregar documentos');
            }
        }
    });
});

// Carregar documentos do activo
function loadAssetDocuments(assetId) {
    $('#documentsList').html(`
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">A carregar...</span>
            </div>
        </div>
    `);

    $.ajax({
        url: '{{ route("assets.documents", ":id") }}'.replace(':id', assetId),
        type: 'GET',
        success: function(response) {
            if (response.success) {
                displayDocuments(response.data);
            } else {
                $('#documentsList').html('<div class="alert alert-danger">Erro ao carregar documentos</div>');
            }
        },
        error: function() {
            $('#documentsList').html('<div class="alert alert-danger">Erro ao carregar documentos</div>');
        }
    });
}

// Exibir documentos
function displayDocuments(documents) {
    const container = $('#documentsList');
    
    if (!documents || documents.length === 0) {
        container.html('<div class="alert alert-info">Nenhum documento encontrado</div>');
        return;
    }
    
    let html = '<div class="list-group">';
    
    documents.forEach(doc => {
        const iconClass = getFileIcon(doc.mime_type);
        const fileSize = (doc.size / 1024).toFixed(2); // KB
        const uploadedAt = new Date(doc.created_at).toLocaleDateString('pt-PT');
        
        html += `
            <div class="list-group-item list-group-item-action document-item d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="fas ${iconClass} me-3 text-primary fs-4"></i>
                    <div>
                        <div class="fw-bold">${doc.original_name}</div>
                        <div class="small text-muted">
                            <span class="badge bg-light text-dark">${doc.document_type}</span>
                            <span class="ms-2">${fileSize} KB</span>
                            <span class="ms-2">${uploadedAt}</span>
                        </div>
                    </div>
                </div>
                <div class="btn-group">
                    <a href="{{ route("assets.download-document", ":id") }}".replace(':id', doc.id) 
                       class="btn btn-sm btn-outline-primary" data-id="${doc.id}" target="_blank">
                        <i class="fas fa-download"></i>
                    </a>
                    @if(auth()->user()->isAdmin())
                    <button class="btn btn-sm btn-outline-danger delete-document" data-id="${doc.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                    @endif
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.html(html);
}

// Eliminar documento
$(document).on('click', '.delete-document', function() {
    const documentId = $(this).data('id');
    const assetId = $('#modalAssetId').val();
    
    if (confirm('Deseja eliminar este documento?')) {
        $.ajax({
            url: '{{ route("assets.delete-document", ":id") }}'.replace(':id', documentId),
            type: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function(response) {
                if (response.success) {
                    showSuccess(response.message);
                    loadAssetDocuments(assetId);
                } else {
                    showError(response.message);
                }
            },
            error: function() {
                showError('Erro ao eliminar documento');
            }
        });
    }
});

// Confirmar eliminação
$('#confirmDeleteBtn').on('click', function() {
    const assetId = $('#delete_asset_id').val();
    
    $.ajax({
        url: '{{ route("assets.destroy", ":id") }}'.replace(':id', assetId),
        type: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        success: function(response) {
            if (response.success) {
                showSuccess(response.message);
                $('#confirmDeleteModal').modal('hide');
                loadAssets();
            } else {
                showError(response.message);
            }
        },
        error: function() {
            showError('Erro ao eliminar activo');
        }
    });
});

// Confirmar restauração
$('#confirmRestoreBtn').on('click', function() {
    const assetId = $('#restore_asset_id').val();
    
    $.ajax({
        url: '{{ route("assets.restore", ":id") }}'.replace(':id', assetId),
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        success: function(response) {
            if (response.success) {
                showSuccess(response.message);
                $('#confirmRestoreModal').modal('hide');
                loadAssets();
            } else {
                showError(response.message);
            }
        },
        error: function() {
            showError('Erro ao restaurar activo');
        }
    });
});

// Função auxiliar para mostrar erros nos modais
function displayModalErrors(errors, formId) {
    $(formId + ' .is-invalid').removeClass('is-invalid');
    $(formId + ' .invalid-feedback').empty();
    
    for (const field in errors) {
        const input = $(formId + ` [name="${field}"]`);
        const errorDiv = $(formId + ` #${field}-error`);
        
        if (input.length) {
            input.addClass('is-invalid');
            if (errorDiv.length) {
                errorDiv.text(errors[field][0]);
            } else {
                input.after(`<div class="invalid-feedback">${errors[field][0]}</div>`);
            }
        }
    }
}

// Inicializar modais
function initModals() {
    initModalSelect2();
    
    // Limpar formulários quando modais são fechados
    $('.modal').on('hidden.bs.modal', function() {
        $(this).find('form')[0]?.reset();
        $(this).find('.is-invalid').removeClass('is-invalid');
        $(this).find('.invalid-feedback').empty();
        $(this).find('.select2-modal').val('').trigger('change');
    });
}

// Adicionar ao inicializador principal
// $(document).ready(function() {
//     // ... outro código ...
//     initModals();
// });
</script>