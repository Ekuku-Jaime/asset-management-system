@extends('layouts.app')

@section('title', 'Gestão de Manutenções')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        :root {
            --maintenance-preventiva: #3498db;
            --maintenance-corretiva: #e74c3c;
            --maintenance-preditiva: #9b59b6;
            --status-agendada: #f39c12;
            --status-em_andamento: #3498db;
            --status-concluida: #27ae60;
            --status-cancelada: #95a5a6;
        }

        .maintenance-management {
            background: #f5f7fa;
            min-height: calc(100vh - 60px);
            padding: 1.5rem;
        }

        .page-header {
            background: white;
            border-radius: 8px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid var(--status-em_andamento);
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .stat-item {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }

        .stat-item:hover {
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
        }

        .stat-total .stat-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stat-agendada .stat-icon {
            background: var(--status-agendada);
        }

        .stat-em_andamento .stat-icon {
            background: var(--status-em_andamento);
        }

        .stat-concluida .stat-icon {
            background: var(--status-concluida);
        }

        .stat-cancelada .stat-icon {
            background: var(--status-cancelada);
        }

        /* Badges */
        .badge-preventiva {
            background-color: rgba(52, 152, 219, 0.1);
            color: var(--maintenance-preventiva);
            border: 1px solid var(--maintenance-preventiva);
        }

        .badge-corretiva {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--maintenance-corretiva);
            border: 1px solid var(--maintenance-corretiva);
        }

        .badge-preditiva {
            background-color: rgba(155, 89, 182, 0.1);
            color: var(--maintenance-preditiva);
            border: 1px solid var(--maintenance-preditiva);
        }

        /* Filters Panel */
        .filters-panel {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .filters-panel .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        /* Table */
        .table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        #maintenancesTable_wrapper {
            padding: 0;
        }

        #maintenancesTable thead th {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            padding: 1rem;
            font-weight: 600;
            color: #2c3e50;
            white-space: nowrap;
        }

        #maintenancesTable tbody td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f0f0f0;
        }

        #maintenancesTable tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05) !important;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.25rem;
            flex-wrap: nowrap;
        }

        .btn-action {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            border: 1px solid #dee2e6;
            background: white;
            color: #6c757d;
            transition: all 0.2s ease;
        }

        .btn-action:hover {
            background: #f8f9fa;
            border-color: #adb5bd;
            transform: translateY(-1px);
        }

        .btn-action.start:hover {
            color: var(--status-em_andamento);
            border-color: var(--status-em_andamento);
        }

        .btn-action.complete:hover {
            color: var(--status-concluida);
            border-color: var(--status-concluida);
        }

        .btn-action.cancel:hover {
            color: var(--status-cancelada);
            border-color: var(--status-cancelada);
        }

        /* Date Range Picker */
        .date-range-picker {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .date-range-picker input {
            flex: 1;
        }

        /* Loading */
        .table-loading {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--status-em_andamento);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .maintenance-management {
                padding: 1rem;
            }
            
            .stats-container {
                grid-template-columns: 1fr 1fr;
            }
            
            .filters-panel .row > div {
                margin-bottom: 1rem;
            }
            
            .action-buttons {
                flex-wrap: wrap;
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .date-range-picker {
                flex-direction: column;
            }
        }
    </style>
@endpush

@section('content')

<div class="container-fluid px-0 maintenance-management">
    <!-- Cabeçalho -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="h3 mb-1">
                    <i class="fas fa-tools text-primary me-2"></i>Gestão de Manutenções
                </h1>
                <p class="text-muted mb-0">Gerencie todas as manutenções de ativos</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-success" onclick="exportMaintenances()">
                    <i class="fas fa-file-excel me-2"></i>Exportar
                </button>
                <a href="{{ route('assets.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-boxes me-2"></i>Voltar para Activos
                </a>
            </div>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="stats-container">
        <div class="stat-item stat-total">
            <div class="stat-icon">
                <i class="fas fa-tools"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value" id="totalMaintenances">0</h3>
                <p class="stat-label">Total de Manutenções</p>
            </div>
        </div>
        <div class="stat-item stat-agendada">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value" id="scheduledMaintenances">0</h3>
                <p class="stat-label">Agendadas</p>
            </div>
        </div>
        <div class="stat-item stat-em_andamento">
            <div class="stat-icon">
                <i class="fas fa-play-circle"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value" id="inProgressMaintenances">0</h3>
                <p class="stat-label">Em Andamento</p>
            </div>
        </div>
        <div class="stat-item stat-concluida">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value" id="completedMaintenances">0</h3>
                <p class="stat-label">Concluídas</p>
            </div>
        </div>
        <div class="stat-item stat-cancelada">
            <div class="stat-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value" id="canceledMaintenances">0</h3>
                <p class="stat-label">Canceladas</p>
            </div>
        </div>
    </div>

    <!-- Painel de Filtros -->
    <div class="filters-panel">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Filtro Rápido</label>
                <select class="form-select" id="quickFilter">
                    <option value="all">Todas as Manutenções</option>
                    <option value="today">Para Hoje</option>
                    <option value="this_week">Esta Semana</option>
                    <option value="this_month">Este Mês</option>
                    <option value="overdue">Atrasadas</option>
                    <option value="pending">Pendentes</option>
                    <option value="completed">Concluídas</option>
                    <option value="costly">Custosas (> 10.000 MT)</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" id="statusFilter">
                    <option value="all">Todos os Status</option>
                    @foreach($maintenance_statuses as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Tipo</label>
                <select class="form-select" id="typeFilter">
                    <option value="all">Todos os Tipos</option>
                    @foreach($maintenance_types as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Período</label>
                <div class="date-range-picker">
                    <input type="text" class="form-control datepicker" id="dateFrom" placeholder="De...">
                    <span class="text-muted">-</span>
                    <input type="text" class="form-control datepicker" id="dateTo" placeholder="Até...">
                </div>
            </div>
            
            <div class="col-md-6">
                <label class="form-label">Pesquisar</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="tableSearch" 
                           placeholder="Pesquisar por ativo, descrição, técnico...">
                </div>
            </div>
            
            <div class="col-md-6">
                <label class="form-label">Ações</label>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary flex-fill" onclick="applyFilters()">
                        <i class="fas fa-filter me-2"></i>Aplicar Filtros
                    </button>
                    <button class="btn btn-outline-secondary" onclick="resetFilters()">
                        <i class="fas fa-redo me-2"></i>Limpar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Ações em Massa -->
    <div class="bulk-actions-container" id="bulkActions" style="display: none;">
        <div class="bulk-info">
            <span class="selected-count" id="selectedCount">0 selecionadas</span>
            <span class="text-muted small">
                <input type="checkbox" id="selectAll" class="me-2">
                Selecionar todas na página
            </span>
        </div>
        <div class="bulk-controls">
            <select class="form-select form-select-sm bulk-select" id="bulkActionSelect">
                <option value="">Ações em massa...</option>
                <option value="start">Iniciar Manutenções</option>
                <option value="complete">Concluir Manutenções</option>
                <option value="cancel">Cancelar Manutenções</option>
                <option value="delete">Eliminar Manutenções</option>
            </select>
            <button class="btn btn-primary btn-sm" onclick="executeBulkAction()">
                <i class="fas fa-play me-1"></i>Aplicar
            </button>
            <button class="btn btn-outline-secondary btn-sm" onclick="clearSelection()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- Tabela -->
    <div class="table-container">
        <div class="table-wrapper">
            <div class="table-loading d-none">
                <div class="spinner"></div>
            </div>
            
            <table class="table table-hover" id="maintenancesTable">
                <thead>
                    <tr>
                        <th width="50">
                            <input type="checkbox" class="select-checkbox" id="selectAllCheckbox">
                        </th>
                        <th width="150">Ativo</th>
                        <th width="100">Tipo</th>
                        <th width="100">Status</th>
                        <th width="120">Data Agendada</th>
                        <th width="150">Descrição</th>
                        <th width="100">Duração</th>
                        <th width="100">Custo</th>
                        <th width="150">Fornecedor</th>
                        <th width="150">Atribuído a</th>
                        <th width="100">Resultado</th>
                        <th width="150" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Dados carregados via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de Detalhes -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Detalhes da Manutenção</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Informação da Manutenção</h6>
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">ID:</th>
                                <td id="detailId"></td>
                            </tr>
                            <tr>
                                <th>Tipo:</th>
                                <td id="detailType"></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td id="detailStatus"></td>
                            </tr>
                            <tr>
                                <th>Resultado:</th>
                                <td id="detailResult"></td>
                            </tr>
                            <tr>
                                <th>Data Agendada:</th>
                                <td id="detailScheduledDate"></td>
                            </tr>
                            <tr>
                                <th>Data Conclusão:</th>
                                <td id="detailCompletedDate"></td>
                            </tr>
                            <tr>
                                <th>Duração Estimada:</th>
                                <td id="detailEstimatedDuration"></td>
                            </tr>
                            <tr>
                                <th>Duração Real:</th>
                                <td id="detailActualDuration"></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Informação do Ativo</h6>
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Código:</th>
                                <td id="detailAssetCode"></td>
                            </tr>
                            <tr>
                                <th>Nome:</th>
                                <td id="detailAssetName"></td>
                            </tr>
                            <tr>
                                <th>Nº Série:</th>
                                <td id="detailAssetSerial"></td>
                            </tr>
                            <tr>
                                <th>Marca/Modelo:</th>
                                <td id="detailAssetBrandModel"></td>
                            </tr>
                            <tr>
                                <th>Colaborador:</th>
                                <td id="detailAssetEmployee"></td>
                            </tr>
                            <tr>
                                <th>Empresa:</th>
                                <td id="detailAssetCompany"></td>
                            </tr>
                        </table>
                        
                        <h6 class="text-muted mb-3 mt-4">Informação Financeira</h6>
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Custo:</th>
                                <td id="detailCost"></td>
                            </tr>
                            <tr>
                                <th>Fornecedor:</th>
                                <td id="detailProvider"></td>
                            </tr>
                            <tr>
                                <th>Técnico:</th>
                                <td id="detailTechnician"></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="text-muted mb-2">Descrição</h6>
                        <div class="border rounded p-3 bg-light" id="detailDescription"></div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="text-muted mb-2">Observações</h6>
                        <div class="border rounded p-3 bg-light" id="detailNotes"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="detailEditButton" style="display: none;">
                    <i class="fas fa-edit me-1"></i>Editar
                </button>
                <button type="button" class="btn btn-success" id="detailCompleteButton" style="display: none;">
                    <i class="fas fa-check-circle me-1"></i>Concluir
                </button>
                <button type="button" class="btn btn-warning" id="detailStartButton" style="display: none;">
                    <i class="fas fa-play me-1"></i>Iniciar
                </button>
                <button type="button" class="btn btn-danger" id="detailCancelButton" style="display: none;">
                    <i class="fas fa-times-circle me-1"></i>Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Edição -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Editar Manutenção</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editMaintenanceForm">
                <div class="modal-body">
                    <input type="hidden" id="editMaintenanceId" name="id">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    
                    <div class="mb-3">
                        <label for="editMaintenanceType" class="form-label required">Tipo de Manutenção</label>
                        <select class="form-select" id="editMaintenanceType" name="maintenance_type" required>
                            @foreach($maintenance_types as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editDescription" class="form-label required">Descrição</label>
                        <textarea class="form-control" id="editDescription" 
                                  name="description" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editScheduledDate" class="form-label required">Data Agendada</label>
                        <input type="text" class="form-control datepicker" id="editScheduledDate" 
                               name="scheduled_date" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editEstimatedDuration" class="form-label">Duração Estimada (dias)</label>
                            <input type="number" class="form-control" id="editEstimatedDuration" 
                                   name="estimated_duration" min="1" placeholder="Ex: 7">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="editMaintenanceProvider" class="form-label">Fornecedor</label>
                            <input type="text" class="form-control" id="editMaintenanceProvider" 
                                   name="maintenance_provider" placeholder="Nome do prestador">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editNotes" class="form-label">Observações</label>
                        <textarea class="form-control" id="editNotes" 
                                  name="notes" rows="2" placeholder="Observações extras"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Conclusão -->
<div class="modal fade" id="completeModal" tabindex="-1" aria-labelledby="completeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="completeModalLabel">Concluir Manutenção</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="completeMaintenanceForm">
                <div class="modal-body">
                    <input type="hidden" id="completeMaintenanceId" name="maintenance_id">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    
                    <div class="mb-3">
                        <label for="completeResult" class="form-label required">Resultado</label>
                        <select class="form-select" id="completeResult" name="result" required>
                            @foreach($maintenance_results as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="completeActualDuration" class="form-label">Duração Real (dias)</label>
                            <input type="number" class="form-control" id="completeActualDuration" 
                                   name="actual_duration" min="1" placeholder="Duração real">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="completeCost" class="form-label">Custo (MT)</label>
                            <input type="number" class="form-control" id="completeCost" 
                                   name="cost" step="0.01" min="0" placeholder="0.00">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="completeTechnicianName" class="form-label">Técnico Responsável</label>
                        <input type="text" class="form-control" id="completeTechnicianName" 
                               name="technician_name" placeholder="Nome do técnico">
                    </div>
                    
                    <div class="mb-3">
                        <label for="completeNotes" class="form-label">Observações Finais</label>
                        <textarea class="form-control" id="completeNotes" 
                                  name="notes" rows="3" placeholder="Observações da conclusão"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Concluir Manutenção</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
let table;
let selectedMaintenances = new Set();

// Initialize DataTable
function initializeDataTable() {
    table = $('#maintenancesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("maintenances.datatable") }}',
            data: function(d) {
                d.quick_filter = $('#quickFilter').val();
                d.status_filter = $('#statusFilter').val();
                d.type_filter = $('#typeFilter').val();
                d.date_from = $('#dateFrom').val();
                d.date_to = $('#dateTo').val();
                d.search = $('#tableSearch').val();
            },
            dataSrc: function(json) {
                if (json.stats) {
                    updateStats(json.stats);
                }
                hideLoading();
                return json.data;
            },
            error: function(xhr, error, thrown) {
                hideLoading();
                showTableError();
            }
        },
        columns: [
            {
                data: 'checkbox',
                orderable: false,
                searchable: false,
                width: '50px'
            },
            {
                data: 'asset_info',
                orderable: false,
                searchable: true,
                width: '150px'
            },
            {
                data: 'type_badge',
                orderable: false,
                searchable: false,
                width: '100px'
            },
            {
                data: 'status_badge',
                orderable: false,
                searchable: false,
                width: '100px'
            },
            {
                data: 'scheduled_date_formatted',
                orderable: true,
                searchable: false,
                width: '120px'
            },
            {
                data: 'description',
                orderable: false,
                searchable: true,
                width: '150px',
                render: function(data, type, row) {
                    if (type === 'display') {
                        return data ? data.substring(0, 50) + (data.length > 50 ? '...' : '') : '--';
                    }
                    return data;
                }
            },
            {
                data: 'duration_info',
                orderable: false,
                searchable: false,
                width: '100px'
            },
            {
                data: 'cost_formatted',
                orderable: true,
                searchable: false,
                width: '100px'
            },
            {
                data: 'provider_info',
                orderable: false,
                searchable: true,
                width: '150px'
            },
            {
                data: 'assigned_to',
                orderable: false,
                searchable: true,
                width: '150px'
            },
            {
                data: 'result_badge',
                orderable: false,
                searchable: false,
                width: '100px'
            },
            {
                data: 'actions',
                orderable: false,
                searchable: false,
                className: 'text-center',
                width: '150px'
            }
        ],
        order: [[4, 'desc']], // Ordenar por data agendada
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        scrollX: true,
        paging: true,
        info: true,
        dom: '<"top"if>rt<"bottom"lp><"clear">',
        drawCallback: function() {
            updateSelectedCheckboxes();
            updateBulkActionsVisibility();
        },
        createdRow: function(row, data) {
            $(row).attr('data-maintenance-id', data.id);
        }
    });
}

// Initialize date pickers
function initializeDatePickers() {
    flatpickr('.datepicker', {
        locale: 'pt',
        dateFormat: 'Y-m-d',
        allowInput: true
    });
}

// Event Listeners
function initializeEventListeners() {
    // Search with debounce
    let searchTimeout;
    $('#tableSearch').on('keyup', function() {
        clearTimeout(searchTimeout);
        showLoading();
        searchTimeout = setTimeout(() => {
            table.search(this.value).draw();
        }, 500);
    });

    // Filters
    $('#quickFilter, #statusFilter, #typeFilter').on('change', function() {
        showLoading();
        table.draw();
    });

    // Select all checkbox
    $('#selectAll, #selectAllCheckbox').on('change', function() {
        const isChecked = $(this).prop('checked');
        $('.maintenance-checkbox:visible').prop('checked', isChecked).trigger('change');
    });

    // Individual checkboxes
    $(document).on('change', '.maintenance-checkbox', function() {
        const maintenanceId = $(this).data('id').toString();
        if ($(this).prop('checked')) {
            selectedMaintenances.add(maintenanceId);
        } else {
            selectedMaintenances.delete(maintenanceId);
        }
        updateSelectedCount();
        updateBulkActionsVisibility();
        updateSelectAllCheckbox();
    });

    // Apply filters button
    window.applyFilters = function() {
        showLoading();
        table.draw();
    };

    // Reset filters
    window.resetFilters = function() {
        $('#quickFilter').val('all');
        $('#statusFilter').val('all');
        $('#typeFilter').val('all');
        $('#dateFrom').val('');
        $('#dateTo').val('');
        $('#tableSearch').val('');
        showLoading();
        table.draw();
    };
}

// Utility Functions
function showLoading() {
    $('.table-loading').removeClass('d-none');
}

function hideLoading() {
    $('.table-loading').addClass('d-none');
}

function showTableError() {
    const tbody = $('#maintenancesTable tbody');
    tbody.html(`
        <tr>
            <td colspan="12" class="text-center py-5">
                <div class="text-danger">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                    <h5 class="mb-2">Erro ao carregar dados</h5>
                    <p class="text-muted mb-3">Ocorreu um erro ao carregar as manutenções.</p>
                    <button class="btn btn-primary" onclick="table.ajax.reload()">
                        <i class="fas fa-redo me-2"></i>Tentar novamente
                    </button>
                </div>
            </td>
        </tr>
    `);
}

function updateStats(stats) {
    $('#totalMaintenances').text(stats.total || 0);
    $('#scheduledMaintenances').text(stats.agendada || 0);
    $('#inProgressMaintenances').text(stats.em_andamento || 0);
    $('#completedMaintenances').text(stats.concluida || 0);
    $('#canceledMaintenances').text(stats.cancelada || 0);
}

function updateSelectedCount() {
    $('#selectedCount').text(`${selectedMaintenances.size} selecionada(s)`);
}

function updateSelectAllCheckbox() {
    const visibleCheckboxes = $('.maintenance-checkbox:visible');
    const checkedCheckboxes = $('.maintenance-checkbox:visible:checked');
    const allChecked = visibleCheckboxes.length > 0 && visibleCheckboxes.length === checkedCheckboxes.length;
    
    $('#selectAll, #selectAllCheckbox').prop('checked', allChecked);
}

function updateSelectedCheckboxes() {
    $('.maintenance-checkbox').each(function() {
        const maintenanceId = $(this).closest('tr').data('maintenance-id');
        if (maintenanceId) {
            $(this).prop('checked', selectedMaintenances.has(maintenanceId.toString()));
        }
    });
    updateSelectAllCheckbox();
}

function updateBulkActionsVisibility() {
    if (selectedMaintenances.size > 0) {
        $('#bulkActions').slideDown();
    } else {
        $('#bulkActions').slideUp();
    }
}

function clearSelection() {
    selectedMaintenances.clear();
    $('.maintenance-checkbox').prop('checked', false);
    updateSelectedCount();
    updateBulkActionsVisibility();
    updateSelectAllCheckbox();
    $('#bulkActionSelect').val('');
}

// Maintenance Functions
function showMaintenanceDetails(id) {
    showLoading();
    $.ajax({
        url: `/maintenances/${id}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                populateDetailsModal(response.data);
                $('#detailsModal').modal('show');
            }
        },
        complete: function() {
            hideLoading();
        }
    });
}

function populateDetailsModal(maintenance) {
    // Basic information
    $('#detailId').text(maintenance.id);
    $('#detailType').html(getMaintenanceTypeBadge(maintenance.maintenance_type));
    $('#detailStatus').html(getMaintenanceStatusBadge(maintenance.status));
    $('#detailResult').html(getMaintenanceResultBadge(maintenance.result));
    $('#detailScheduledDate').text(formatDate(maintenance.scheduled_date));
    $('#detailCompletedDate').text(formatDate(maintenance.completed_date));
    $('#detailEstimatedDuration').text(maintenance.estimated_duration ? maintenance.estimated_duration + ' dias' : '--');
    $('#detailActualDuration').text(maintenance.actual_duration ? maintenance.actual_duration + ' dias' : '--');
    $('#detailCost').text(maintenance.cost ? formatCurrency(maintenance.cost) : '--');
    $('#detailProvider').text(maintenance.maintenance_provider || '--');
    $('#detailTechnician').text(maintenance.technician_name || '--');
    $('#detailDescription').text(maintenance.description || '--');
    $('#detailNotes').text(maintenance.notes || '--');

    // Asset information
    if (maintenance.asset) {
        $('#detailAssetCode').text(maintenance.asset.code || '--');
        $('#detailAssetName').text(maintenance.asset.name || '--');
        $('#detailAssetSerial').text(maintenance.asset.serial_number || '--');
        $('#detailAssetBrandModel').text(`${maintenance.asset.brand || ''} ${maintenance.asset.model || ''}`.trim() || '--');
        $('#detailAssetEmployee').text(maintenance.asset.employee?.name || '--');
        $('#detailAssetCompany').text(maintenance.asset.employee?.company?.name || '--');
    } else {
        $('#detailAssetCode, #detailAssetName, #detailAssetSerial, #detailAssetBrandModel, #detailAssetEmployee, #detailAssetCompany')
            .text('--');
    }

    // Show/hide action buttons based on status
    $('#detailEditButton, #detailCompleteButton, #detailStartButton, #detailCancelButton').hide();

    if (maintenance.status !== 'concluida' && maintenance.status !== 'cancelada') {
        $('#detailEditButton').show().data('id', maintenance.id);
    }

    if (maintenance.status === 'agendada') {
        $('#detailStartButton').show().data('id', maintenance.id);
    }

    if (maintenance.status === 'em_andamento' || maintenance.status === 'agendada') {
        $('#detailCompleteButton').show().data('id', maintenance.id);
    }

    if (maintenance.status !== 'concluida' && maintenance.status !== 'cancelada') {
        $('#detailCancelButton').show().data('id', maintenance.id);
    }

    // Store maintenance ID for action buttons
    $('#detailsModal').data('maintenanceId', maintenance.id);
}

// Action button handlers
$('#detailEditButton').on('click', function() {
    const id = $(this).data('id');
    $('#detailsModal').modal('hide');
    editMaintenance(id);
});

$('#detailStartButton').on('click', function() {
    const id = $(this).data('id');
    $('#detailsModal').modal('hide');
    startMaintenance(id);
});

$('#detailCompleteButton').on('click', function() {
    const id = $(this).data('id');
    $('#detailsModal').modal('hide');
    completeMaintenance(id);
});

$('#detailCancelButton').on('click', function() {
    const id = $(this).data('id');
    $('#detailsModal').modal('hide');
    cancelMaintenance(id);
});

function editMaintenance(id) {
    showLoading();
    $.ajax({
        url: `/maintenances/${id}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                populateEditModal(response.data);
                $('#editModal').modal('show');
            }
        },
        complete: function() {
            hideLoading();
        }
    });
}

function populateEditModal(maintenance) {
    $('#editMaintenanceId').val(maintenance.id);
    $('#editMaintenanceType').val(maintenance.maintenance_type);
    $('#editDescription').val(maintenance.description);
    $('#editScheduledDate').val(maintenance.scheduled_date ? maintenance.scheduled_date.substring(0, 10) : '');
    $('#editEstimatedDuration').val(maintenance.estimated_duration || '');
    $('#editMaintenanceProvider').val(maintenance.maintenance_provider || '');
    $('#editNotes').val(maintenance.notes || '');
}

$('#editMaintenanceForm').on('submit', function(e) {
    e.preventDefault();
    
    const id = $('#editMaintenanceId').val();
    const formData = $(this).serialize();
    
    $.ajax({
        url: `/maintenances/${id}`,
        type: 'PUT',
        data: formData,
        success: function(response) {
            if (response.success) {
                $('#editModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Manutenção atualizada!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                table.ajax.reload();
                loadStats();
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: xhr.responseJSON?.message || 'Erro ao atualizar manutenção'
            });
        }
    });
});

function startMaintenance(id) {
    Swal.fire({
        title: 'Iniciar Manutenção?',
        text: 'Esta ação mudará o status para "Em Andamento".',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, iniciar!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/maintenances/${id}/status`,
                type: 'POST',
                data: {
                    status: 'em_andamento',
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Manutenção iniciada!',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        table.ajax.reload();
                        loadStats();
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: xhr.responseJSON?.message || 'Erro ao iniciar manutenção'
                    });
                }
            });
        }
    });
}

function completeMaintenance(id) {
    // Load maintenance details first
    $.ajax({
        url: `/maintenances/${id}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                $('#completeMaintenanceId').val(id);
                
                // Pre-fill form if there's existing data
                if (response.data.actual_duration) {
                    $('#completeActualDuration').val(response.data.actual_duration);
                }
                if (response.data.cost) {
                    $('#completeCost').val(response.data.cost);
                }
                if (response.data.technician_name) {
                    $('#completeTechnicianName').val(response.data.technician_name);
                }
                if (response.data.notes) {
                    $('#completeNotes').val(response.data.notes);
                }
                
                $('#completeModal').modal('show');
            }
        }
    });
}

$('#completeMaintenanceForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = $(this).serialize();
    const id = $('#completeMaintenanceId').val();
    
    $.ajax({
        url: `/maintenances/${id}/complete`,
        type: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                $('#completeModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Manutenção concluída!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                table.ajax.reload();
                loadStats();
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: xhr.responseJSON?.message || 'Erro ao concluir manutenção'
            });
        }
    });
});

function cancelMaintenance(id) {
    Swal.fire({
        title: 'Cancelar Manutenção?',
        text: 'Esta ação não pode ser revertida.',
        icon: 'warning',
        input: 'textarea',
        inputLabel: 'Motivo do cancelamento (opcional)',
        inputPlaceholder: 'Descreva o motivo do cancelamento...',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, cancelar!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/maintenances/${id}/status`,
                type: 'POST',
                data: {
                    status: 'cancelada',
                    notes: result.value,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Manutenção cancelada!',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        table.ajax.reload();
                        loadStats();
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: xhr.responseJSON?.message || 'Erro ao cancelar manutenção'
                    });
                }
            });
        }
    });
}

function confirmDeleteMaintenance(id) {
    Swal.fire({
        title: 'Eliminar Manutenção?',
        text: 'Esta ação não pode ser revertida!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, eliminar!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            deleteMaintenance(id);
        }
    });
}

function deleteMaintenance(id) {
    showLoading();
    
    $.ajax({
        url: `/maintenances/${id}`,
        type: 'DELETE',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Manutenção eliminada!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                table.ajax.reload();
                loadStats();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: response.message
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: xhr.responseJSON?.message || 'Erro ao eliminar manutenção'
            });
        },
        complete: function() {
            hideLoading();
        }
    });
}

// Bulk Actions
function executeBulkAction() {
    const action = $('#bulkActionSelect').val();
    
    if (!action) {
        Swal.fire({
            icon: 'warning',
            title: 'Selecione uma ação',
            text: 'Por favor, selecione uma ação para executar.'
        });
        return;
    }
    
    if (selectedMaintenances.size === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Nenhuma manutenção selecionada',
            text: 'Por favor, selecione pelo menos uma manutenção.'
        });
        return;
    }

    const actionTitles = {
        'start': 'Iniciar Manutenções',
        'complete': 'Concluir Manutenções',
        'cancel': 'Cancelar Manutenções',
        'delete': 'Eliminar Manutenções'
    };

    Swal.fire({
        title: actionTitles[action] || 'Confirmar Ação',
        text: `Deseja executar esta ação em ${selectedMaintenances.size} manutenção(ões)?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            performBulkAction(action);
        } else {
            $('#bulkActionSelect').val('');
        }
    });
}

async function performBulkAction(action) {
    try {
        showLoading();
        
        const data = {
            action: action,
            maintenance_ids: Array.from(selectedMaintenances),
            _token: '{{ csrf_token() }}'
        };
        
        // For complete action, we might need additional data
        if (action === 'complete') {
            const { value: result } = await Swal.fire({
                title: 'Resultado da Manutenção',
                input: 'select',
                inputOptions: {
                    @foreach($maintenance_results as $key => $label)
                    '{{ $key }}': '{{ $label }}',
                    @endforeach
                },
                inputPlaceholder: 'Selecione o resultado',
                showCancelButton: true,
                inputValidator: (value) => {
                    if (!value) {
                        return 'Selecione um resultado';
                    }
                }
            });
            
            if (!result) {
                hideLoading();
                $('#bulkActionSelect').val('');
                return;
            }
            
            data.result = result;
        }
        
        const response = await $.ajax({
            url: '{{ route("maintenances.bulk-action") }}',
            type: 'POST',
            data: data
        });

        if (response.success) {
            Swal.fire({
                icon: 'success',
                title: 'Ação Concluída',
                html: `
                    <div class="text-start">
                        <p>${response.message}</p>
                    </div>
                `
            });

            clearSelection();
            table.ajax.reload(null, false);
            loadStats();
        }
        
        $('#bulkActionSelect').val('');
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: error.responseJSON?.message || 'Ocorreu um erro ao executar a ação.'
        });
        $('#bulkActionSelect').val('');
    } finally {
        hideLoading();
    }
}

// Export function
function exportMaintenances() {
    showLoading();
    
    const params = {
        quick_filter: $('#quickFilter').val(),
        status_filter: $('#statusFilter').val(),
        date_from: $('#dateFrom').val(),
        date_to: $('#dateTo').val(),
        _token: '{{ csrf_token() }}'
    };
    
    $.ajax({
        url: '{{ route("maintenances.export") }}',
        type: 'POST',
        data: params,
        success: function(response) {
            if (response.success) {
                // Converter dados para Excel
                const ws = XLSX.utils.json_to_sheet(response.data);
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, "Manutenções");
                XLSX.writeFile(wb, `manutencoes_${new Date().toISOString().split('T')[0]}.xlsx`);
                
                Swal.fire({
                    icon: 'success',
                    title: 'Exportação concluída!',
                    text: 'O ficheiro foi descarregado com sucesso.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Erro na exportação',
                text: xhr.responseJSON?.message || 'Ocorreu um erro ao gerar o ficheiro.'
            });
        },
        complete: function() {
            hideLoading();
        }
    });
}

// Helper functions
function getMaintenanceTypeBadge(type) {
    const labels = @json($maintenance_types);
    const classes = {
        'preventiva': 'badge-preventiva',
        'corretiva': 'badge-corretiva',
        'preditiva': 'badge-preditiva'
    };
    
    return `<span class="badge ${classes[type] || 'badge-secondary'}">${labels[type] || type}</span>`;
}

function getMaintenanceStatusBadge(status) {
    const labels = @json($maintenance_statuses);
    const classes = {
        'agendada': 'badge-warning',
        'em_andamento': 'badge-info',
        'concluida': 'badge-success',
        'cancelada': 'badge-danger'
    };
    
    return `<span class="badge ${classes[status] || 'badge-secondary'}">${labels[status] || status}</span>`;
}

function getMaintenanceResultBadge(result) {
    if (!result) return '<span class="text-muted">--</span>';
    
    const labels = @json($maintenance_results);
    const classes = {
        'concluida': 'badge-success',
        'pendente': 'badge-warning',
        'cancelada': 'badge-danger'
    };
    
    return `<span class="badge ${classes[result] || 'badge-secondary'}">${labels[result] || result}</span>`;
}

function formatDate(dateString) {
    if (!dateString) return '--';
    return new Date(dateString).toLocaleDateString('pt-PT');
}

function formatCurrency(value) {
    return new Intl.NumberFormat('pt-PT', {
        style: 'currency',
        currency: 'MZN'
    }).format(value || 0);
}

// Load stats
function loadStats() {
    $.ajax({
        url: '{{ route("maintenances.stats") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                updateStats(response.data);
            }
        }
    });
}

// Initialize
$(document).ready(function() {
    initializeDatePickers();
    initializeDataTable();
    initializeEventListeners();
    loadStats();
    
    // Initialize Toast
    window.Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });
});
</script>
@endpush