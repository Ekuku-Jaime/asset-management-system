@extends('layouts.app')

@section('title', 'Auditoria do Sistema')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1 fw-bold">
                                <i class="fas fa-history me-2 text-primary"></i>
                                Auditoria do Sistema
                            </h4>
                            <p class="text-muted mb-0">
                                Registo detalhado de todas as ações realizadas no sistema
                            </p>
                        </div>
                        <button class="btn btn-success" id="exportAudit">
                            <i class="fas fa-download me-2"></i>
                            Exportar Logs
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50">Total de Registos</h6>
                            <h3 class="text-white mb-0" id="totalLogs">0</h3>
                        </div>
                        <i class="fas fa-database fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50">Hoje</h6>
                            <h3 class="text-white mb-0" id="todayLogs">0</h3>
                        </div>
                        <i class="fas fa-calendar-day fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50">Utilizadores Únicos</h6>
                            <h3 class="text-white mb-0" id="uniqueUsers">0</h3>
                        </div>
                        <i class="fas fa-users fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50">IPs Únicos</h6>
                            <h3 class="text-white mb-0" id="uniqueIps">0</h3>
                        </div>
                        <i class="fas fa-network-wired fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2"></i>Filtros
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Tipo de Evento</label>
                            <select class="form-select" id="filterEventType">
                                <option value="">Todos</option>
                                <option value="CREATE">CREATE</option>
                                <option value="UPDATE">UPDATE</option>
                                <option value="DELETE">DELETE</option>
                                <option value="RESTORE">RESTORE</option>
                                <option value="LOGIN">LOGIN</option>
                                <option value="LOGOUT">LOGOUT</option>
                                <option value="EXPORT">EXPORT</option>
                                <option value="IMPORT">IMPORT</option>
                                <option value="ASSIGN">ASSIGN</option>
                                <option value="UNASSIGN">UNASSIGN</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Utilizador</label>
                            <select class="form-select" id="filterUser">
                                <option value="">Todos</option>
                                <!-- Carregado via AJAX -->
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Modelo</label>
                            <select class="form-select" id="filterModel">
                                <option value="">Todos</option>
                                <option value="Asset">Ativos</option>
                                <option value="Project">Projetos</option>
                                <option value="Employee">Colaboradores</option>
                                <option value="Company">Empresas</option>
                                <option value="Supplier">Fornecedores</option>
                                <option value="Request">Requisições</option>
                                <option value="Invoice">Faturas</option>
                                <option value="Shipment">Remessas</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Período</label>
                            <input type="text" class="form-control" id="filterDateRange" placeholder="Selecionar período">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary" id="applyFilters">
                                <i class="fas fa-search me-2"></i>Aplicar Filtros
                            </button>
                            <button class="btn btn-secondary" id="clearFilters">
                                <i class="fas fa-times me-2"></i>Limpar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Logs -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="auditTable" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Data/Hora</th>
                                    <th>Utilizador</th>
                                    <th>Tipo</th>
                                    <th>Descrição</th>
                                    <th>IP / Dispositivo</th>
                                    <th>Alterações</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Alterações -->
<div class="modal fade" id="changesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-code-branch me-2 text-info"></i>
                    Detalhes das Alterações
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3 text-danger">
                            <i class="fas fa-minus-circle me-2"></i>Valores Antigos
                        </h6>
                        <pre id="oldValues" class="bg-light p-3 rounded" style="max-height: 400px; overflow: auto;"></pre>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3 text-success">
                            <i class="fas fa-plus-circle me-2"></i>Valores Novos
                        </h6>
                        <pre id="newValues" class="bg-light p-3 rounded" style="max-height: 400px; overflow: auto;"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .avatar-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
    }
    
    pre {
        white-space: pre-wrap;
        word-wrap: break-word;
        font-size: 12px;
        margin: 0;
    }
    
    .badge {
        padding: 6px 10px;
        font-weight: 500;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
$(document).ready(function() {
    let table;

    // Carregar estatísticas
    function loadStats() {
        $.ajax({
            url: '/audit/stats',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#totalLogs').text(response.data.total_logs);
                    $('#todayLogs').text(response.data.today_logs);
                    $('#uniqueUsers').text(response.data.unique_users);
                    $('#uniqueIps').text(response.data.unique_ips);
                }
            }
        });
    }

    loadStats();

    // Inicializar DataTable
    function initializeTable() {
        if ($.fn.DataTable.isDataTable('#auditTable')) {
            table.destroy();
        }

        table = $('#auditTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("audit.data") }}',
                data: function(d) {
                    d.event_type = $('#filterEventType').val();
                    d.user_id = $('#filterUser').val();
                    d.model_type = $('#filterModel').val();
                    d.date_range = $('#filterDateRange').val();
                }
            },
            pageLength: 50,
            order: [[1, 'desc']],
           
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'performed_at', name: 'performed_at' },
                { data: 'user_name', name: 'user_name' },
                { data: 'event_type', name: 'event_type' },
                { data: 'description', name: 'description' },
                { data: 'ip_address', name: 'ip_address' },
                { data: 'changes', name: 'changes', orderable: false, searchable: false }
            ],
            initComplete: function() {
                // Carregar utilizadores para o filtro
                $.ajax({
                    url: '/users/data',
                    method: 'GET',
                    success: function(response) {
                        let options = '<option value="">Todos</option>';
                        response.data.forEach(user => {
                            options += `<option value="${user.id}">${user.name}</option>`;
                        });
                        $('#filterUser').html(options);
                    }
                });
            }
        });
    }

    initializeTable();

    // Inicializar date range picker
    flatpickr("#filterDateRange", {
        mode: "range",
        dateFormat: "Y-m-d",
        // locale: "fr"
    });

    // Aplicar filtros
    $('#applyFilters').click(function() {
        table.ajax.reload();
    });

    // Limpar filtros
    $('#clearFilters').click(function() {
        $('#filterEventType').val('');
        $('#filterUser').val('');
        $('#filterModel').val('');
        $('#filterDateRange').val('');
        table.ajax.reload();
    });

    // Ver alterações
    $(document).on('click', '.view-changes', function() {
        const oldValues = $(this).data('old');
        const newValues = $(this).data('new');

        $('#oldValues').html(JSON.stringify(oldValues, null, 2));
        $('#newValues').html(JSON.stringify(newValues, null, 2));
        $('#changesModal').modal('show');
    });

    // Exportar logs
    $('#exportAudit').click(function() {
        const dateRange = $('#filterDateRange').val();
        let url = '/audit/export';
        
        if (dateRange) {
            url += '?date_range=' + encodeURIComponent(dateRange);
        }
        
        window.location.href = url;
    });
});
</script>
@endpush