@extends('layouts.app')

@section('title', 'Gestão de Remessas')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header Card -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0 fw-bold">
                            <i class="fas fa-shipping-fast me-2"></i>Gestão de Remessas
                        </h4>
                        <p class="text-muted mb-0">Gerir remessas do sistema</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-info" id="btnStatistics">
                            <i class="fas fa-chart-pie me-2"></i>Estatísticas
                        </button>
                        <button class="btn btn-outline-info" id="btnReport">
                            <i class="fas fa-chart-bar me-2"></i>Relatório
                        </button>
                        <button class="btn btn-primary" id="toggleForm">
                            <i class="fas fa-plus me-2"></i>Nova Remessa
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Form Card -->
<div class="row mb-4" id="shipmentFormCard" style="display: none;">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0" id="formTitle">
                    <i class="fas fa-shipping-fast me-2"></i>Adicionar Nova Remessa
                </h5>
            </div>
            <div class="card-body">
                <form id="shipmentForm">
                    <input type="hidden" id="shipment_id" name="id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="guide" class="form-label">Guia/Número da Remessa *</label>
                            <input type="text" class="form-control" id="guide" name="guide" required
                                   placeholder="Ex: REM2023001, GUID123456">
                            <div class="invalid-feedback" id="guide-error"></div>
                            <small class="text-muted">Código único da remessa</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="date" class="form-label">Data da Remessa *</label>
                            <input type="date" class="form-control" id="date" name="date" required
                                   max="{{ date('Y-m-d') }}">
                            <div class="invalid-feedback" id="date-error"></div>
                            <small class="text-muted">Data de envio (não pode ser futura)</small>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" id="cancelForm">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span id="submitText">Guardar Remessa</span>
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

<!-- Statistics Modal -->
<div class="modal fade" id="statisticsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-chart-pie me-2"></i>Estatísticas de Remessas
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
                    <i class="fas fa-chart-bar me-2"></i>Relatório de Remessas
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
                                    <th>Guia</th>
                                    <th>Data</th>
                                    <th>Criada em</th>
                                    <th>Status</th>
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

<!-- Shipments Table Card -->
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
                                   placeholder="Pesquisar remessas...">
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover" id="shipmentsTable" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Remessa</th>
                                <th>Data</th>
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
    .shipment-avatar {
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
        white-space: nowrap;
    }
    
    .guide-badge {
        font-family: 'Courier New', monospace;
        font-weight: bold;
        color: #7209b7;
        background-color: #f8f9fa;
        border: 1px solid #e0d6f5;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.875rem;
        display: inline-block;
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
        if ($.fn.DataTable.isDataTable('#shipmentsTable')) {
            table.destroy();
        }
        
        const url = view === 'active' 
            ? "{{ route('shipments.data') }}" 
            : "{{ route('shipments.data.trashed') }}";
            
        table = $('#shipmentsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: url,
                type: 'GET',
                data: function(d) {
                    d.view = view;
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
                    data: 'guide',
                    render: function(data, type, row) {
                        const initial = data.charAt(0).toUpperCase();
                        return `<div class="d-flex align-items-center">
                                    
                                    <div>
                                        <div class="fw-medium">${data}</div>
                                     
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
                            const date = new Date(row.date);
                            const today = new Date();
                            const diffDays = Math.floor((today - date) / (1000 * 60 * 60 * 24));
                            
                            if (diffDays <= 1) {
                                return `<span class="badge bg-success">Ativa (Hoje)</span>`;
                            } else if (diffDays <= 7) {
                                return `<span class="badge bg-success">Ativa (Semana)</span>`;
                            } else {
                                return `<span class="badge bg-success">Ativa</span>`;
                            }
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
                                          data-guide="${row.guide}"
                                          data-date="${row.date}"
                                          title="Editar Remessa">
                                        <i class="fas fa-edit"></i>
                                    </button>`;
                            
                            if (isAdmin) {
                                buttons += `<button class="btn btn-outline-danger btn-delete"
                                            data-id="${data}"
                                            data-guide="${row.guide}"
                                            title="Eliminar Remessa">
                                        <i class="fas fa-trash"></i>
                                    </button>`;
                            }
                        } else {
                            if (isAdmin) {
                                buttons += `<button class="btn btn-outline-success btn-restore"
                                          data-id="${data}"
                                          data-guide="${row.guide}"
                                          title="Restaurar Remessa">
                                        <i class="fas fa-undo"></i>
                                    </button>`;
                                
                                buttons += `<button class="btn btn-outline-danger btn-force-delete"
                                            data-id="${data}"
                                            data-guide="${row.guide}"
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
                // Add ID to row for easy reference
                $(row).attr('id', 'shipment-' + data.id);
            },
            initComplete: function() {
                // Add search functionality
                $('#tableSearch').on('keyup', function() {
                    table.search(this.value).draw();
                });
            }
        });
    }
    
    // Initialize with active shipments
    initializeTable('active');
    
    // Toggle form visibility
    $('#toggleForm').click(function() {
        resetForm();
        $('#formTitle').html('<i class="fas fa-shipping-fast me-2"></i>Adicionar Nova Remessa');
        $('#shipmentFormCard').slideToggle('fast', function() {
            if ($(this).is(':visible')) {
                $('#toggleForm').html('<i class="fas fa-times me-2"></i>Fechar Formulário');
            } else {
                $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Nova Remessa');
            }
        });
    });
    
    $('#cancelForm').click(function() {
        $('#shipmentFormCard').slideUp();
        $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Nova Remessa');
        resetForm();
    });
    
    // Toggle between active and inactive shipments
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
            url: "{{ route('shipments.statistics') }}",
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
                                                <h6 class="card-title mb-0">Total Remessas</h6>
                                                <h2 class="mb-0">${stats.total}</h2>
                                            </div>
                                            <i class="fas fa-box fa-2x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card stat-card bg-success text-white">
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
                            <div class="col-md-4 mb-3">
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
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="card stat-card bg-warning text-dark">
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
                            <div class="col-md-4 mb-3">
                                <div class="card stat-card bg-secondary text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="card-title mb-0">Mês Anterior</h6>
                                                <h2 class="mb-0">${stats.last_month}</h2>
                                            </div>
                                            <i class="fas fa-calendar fa-2x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card stat-card bg-danger text-white">
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
    
    // Date validation for shipment form
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
    $('#shipmentForm').submit(function(e) {
        e.preventDefault();
        
        const submitBtn = $('#submitBtn');
        const submitText = $('#submitText');
        const loadingSpinner = $('#loadingSpinner');
        const shipmentId = $('#shipment_id').val();
        
        // Validate date
        const selectedDate = new Date($('#date').val());
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate > today) {
            $('#date').addClass('is-invalid future-date');
            $('#date-error').text('A data da remessa não pode ser no futuro');
            return;
        }
        
        // Show loading state
        submitBtn.prop('disabled', true);
        submitText.hide();
        loadingSpinner.show();
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        const url = shipmentId ? `/shipments/${shipmentId}` : "{{ route('shipments.store') }}";
        const method = shipmentId ? 'PUT' : 'POST';
        
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
                $('#shipmentFormCard').slideUp();
                $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Nova Remessa');
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
    
    // Edit shipment
    $(document).on('click', '.btn-edit', function() {
        const shipmentId = $(this).data('id');
        const shipmentGuide = $(this).data('guide');
        const shipmentDate = $(this).data('date');
        
        // Show form
        $('#shipmentFormCard').slideDown();
        $('#toggleForm').html('<i class="fas fa-times me-2"></i>Fechar Formulário');
        
        // Set form title
        $('#formTitle').html(`<i class="fas fa-edit me-2"></i>Editar Remessa: ${shipmentGuide}`);
        
        // Format date for input field (YYYY-MM-DD)
        const dateObj = new Date(shipmentDate);
        const formattedDate = dateObj.toISOString().split('T')[0];
        
        // Fill form data
        $('#shipment_id').val(shipmentId);
        $('#guide').val(shipmentGuide);
        $('#date').val(formattedDate);
        
        // Validate date
        $('#date').trigger('change');
        
        // Scroll to form
        $('html, body').animate({
            scrollTop: $('#shipmentFormCard').offset().top - 20
        }, 500);
    });
    
    // Delete shipment (soft delete) - only for admins
    $(document).on('click', '.btn-delete', function() {
        if (!isAdmin) {
            Toast.fire({
                icon: 'warning',
                title: 'Apenas administradores podem eliminar remessas'
            });
            return;
        }
        
        const shipmentId = $(this).data('id');
        const shipmentGuide = $(this).data('guide');
        
        Swal.fire({
            title: 'Eliminar Remessa',
            html: `<p>Tem a certeza que deseja eliminar a remessa <strong>${shipmentGuide}</strong>?</p>
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
                    url: `/shipments/${shipmentId}`,
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
                                title: 'Apenas administradores podem eliminar remessas'
                            });
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: xhr.responseJSON?.message || 'Erro ao eliminar remessa'
                            });
                        }
                    }
                });
            }
        });
    });
    
    // Restore shipment - only for admins
    $(document).on('click', '.btn-restore', function() {
        if (!isAdmin) {
            Toast.fire({
                icon: 'warning',
                title: 'Apenas administradores podem restaurar remessas'
            });
            return;
        }
        
        const shipmentId = $(this).data('id');
        const shipmentGuide = $(this).data('guide');
        
        Swal.fire({
            title: 'Restaurar Remessa',
            html: `<p>Tem a certeza que deseja restaurar a remessa <strong>${shipmentGuide}</strong>?</p>
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
                    url: `/shipments/${shipmentId}/restore`,
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
                                title: 'Apenas administradores podem restaurar remessas'
                            });
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: xhr.responseJSON?.message || 'Erro ao restaurar remessa'
                            });
                        }
                    }
                });
            }
        });
    });
    
    // Force delete shipment - only for admins
    $(document).on('click', '.btn-force-delete', function() {
        if (!isAdmin) {
            Toast.fire({
                icon: 'warning',
                title: 'Apenas administradores podem eliminar permanentemente remessas'
            });
            return;
        }
        
        const shipmentId = $(this).data('id');
        const shipmentGuide = $(this).data('guide');
        
        Swal.fire({
            title: 'Eliminar Permanentemente',
            html: `<p>Tem a certeza ABSOLUTA que deseja eliminar permanentemente a remessa <strong>${shipmentGuide}</strong>?</p>
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
                    url: `/shipments/${shipmentId}/force`,
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
                                title: 'Apenas administradores podem eliminar permanentemente remessas'
                            });
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: xhr.responseJSON?.message || 'Erro ao eliminar remessa'
                            });
                        }
                    }
                });
            } else if (result.isDenied) {
                // Restore instead
                $.ajax({
                    url: `/shipments/${shipmentId}/restore`,
                    method: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        Toast.fire({
                            icon: 'success',
                            title: 'Remessa restaurada com sucesso!'
                        });
                        // Switch to active view
                        $('#btnActive').click();
                    },
                    error: function(xhr) {
                        Toast.fire({
                            icon: 'error',
                            title: xhr.responseJSON?.message || 'Erro ao restaurar remessa'
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
            url: "{{ route('shipments.report') }}",
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    // Populate table
                    response.data.forEach(function(shipment) {
                        const date = new Date(shipment.date);
                        const created = new Date(shipment.created_at);
                        const today = new Date();
                        const diffDays = Math.floor((today - date) / (1000 * 60 * 60 * 24));
                        
                        let statusBadge = 'bg-secondary';
                        let statusText = 'Antiga';
                        
                        if (diffDays <= 1) {
                            statusBadge = 'bg-info';
                            statusText = 'Hoje';
                        } else if (diffDays <= 7) {
                            statusBadge = 'bg-warning';
                            statusText = 'Esta Semana';
                        } else if (diffDays <= 30) {
                            statusBadge = 'bg-primary';
                            statusText = 'Este Mês';
                        }
                        
                        $('#reportTable tbody').append(`
                            <tr>
                                <td><strong>${shipment.guide}</strong></td>
                                <td>${date.toLocaleDateString('pt-PT')}</td>
                                <td>${created.toLocaleDateString('pt-PT')}</td>
                                <td><span class="badge ${statusBadge}">${statusText}</span></td>
                            </tr>
                        `);
                    });
                    
                    // Show summary
                    const startDate = new Date(response.period.start_date);
                    const endDate = new Date(response.period.end_date);
                    
                    $('#reportSummary').html(`
                        <i class="fas fa-info-circle me-1"></i>
                        Foram encontradas <strong>${response.count}</strong> remessas no período de 
                        <strong>${startDate.toLocaleDateString('pt-PT')}</strong> a 
                        <strong>${endDate.toLocaleDateString('pt-PT')}</strong>
                    `);
                    
                    $('#reportResults').show();
                } else {
                    $('#reportSummary').html(`
                        <div class="text-center py-3">
                            <i class="fas fa-search fa-2x text-muted mb-3"></i>
                            <p class="mb-0">Nenhuma remessa encontrada no período selecionado.</p>
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
        $('#shipmentForm')[0].reset();
        $('#shipment_id').val('');
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