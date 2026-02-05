@extends('layouts.app')

@section('title', 'Gestão de Activos')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    {{-- <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css"> --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-bg: #f8f9fa;
            --border-radius: 6px;
            --shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .asset-management {
            background: #f5f7fa;
            min-height: calc(100vh - 60px);
            padding: 1.5rem;
        }

        /* Header */
        .page-header {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.25rem 1.5rem;
            margin-bottom: 1rem;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--secondary-color);
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .stat-item {
            background: white;
            border-radius: var(--border-radius);
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: var(--shadow);
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
        }

        .stat-total .stat-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .stat-disponivel .stat-icon {
            background: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
        }

        .stat-atribuido .stat-icon {
            background: rgba(52, 152, 219, 0.1);
            color: var(--secondary-color);
        }

        .stat-inoperacional .stat-icon {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        .stat-content {
            flex: 1;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            line-height: 1.2;
        }

        .stat-label {
            font-size: 0.85rem;
            color: #6c757d;
            margin: 0;
        }

        /* Toolbar */
        .table-toolbar {
            background: white;
            border-radius: var(--border-radius);
            padding: 1rem 1.5rem;
            margin-bottom: 0.75rem;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .toolbar-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex: 1;
            min-width: 300px;
        }

        .toolbar-right {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        /* Search Box */
        .search-box {
            position: relative;
            flex: 1;
            max-width: 400px;
        }

        .search-box input {
            padding-left: 2.5rem;
            border-radius: 20px;
            border: 1px solid #dee2e6;
            height: 38px;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 0.9rem;
        }

        /* Quick Filters */
        .quick-filters {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .quick-filter {
            padding: 0.375rem 1rem;
            border-radius: 20px;
            border: 1px solid #dee2e6;
            background: white;
            color: #6c757d;
            font-size: 0.85rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quick-filter:hover,
        .quick-filter.active {
            background: var(--secondary-color);
            color: white;
            border-color: var(--secondary-color);
        }

        /* Bulk Actions */
        .bulk-actions-container {
            background: white;
            border-radius: var(--border-radius);
            padding: 0.75rem 1.5rem;
            margin-bottom: 0.75rem;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            min-height: 60px;
        }

        .bulk-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex: 1;
        }

        .selected-count {
            background: var(--secondary-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .bulk-controls {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .bulk-select {
            width: 200px;
        }

        /* Table Container */
        .table-container {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            margin-top: 0.75rem;
        }

        .table-wrapper {
            overflow-x: auto;
            min-height: 400px;
            position: relative;
        }

        /* Table Styling */
        #assetsTable {
            width: 100% !important;
            border-collapse: separate;
            border-spacing: 0;
            margin: 0;
        }

        #assetsTable thead th {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            padding: 1rem;
            font-weight: 600;
            color: var(--primary-color);
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        #assetsTable tbody td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f0f0f0;
            background: white;
        }

        #assetsTable tbody tr {
            transition: background-color 0.2s ease;
        }

        #assetsTable tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05) !important;
        }

        #assetsTable tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        /* Status Badges */
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            min-width: 100px;
            justify-content: center;
        }

        .badge-disponivel {
            background: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
            border: 1px solid var(--success-color);
        }

        .badge-atribuido {
            background: rgba(52, 152, 219, 0.1);
            color: var(--secondary-color);
            border: 1px solid var(--secondary-color);
        }

        .badge-manutencao {
            background: rgba(243, 156, 18, 0.1);
            color: var(--warning-color);
            border: 1px solid var(--warning-color);
        }

        .badge-inoperacional {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
            border: 1px solid var(--danger-color);
        }

        .badge-abatido {
            background: rgba(108, 117, 125, 0.1);
            color: #6c757d;
            border: 1px solid #6c757d;
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

        .btn-action.view:hover {
            color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-action.edit:hover {
            color: var(--success-color);
            border-color: var(--success-color);
        }

        .btn-action.assign:hover {
            color: #9b59b6;
            border-color: #9b59b6;
        }

        .btn-action.delete:hover {
            color: var(--danger-color);
            border-color: var(--danger-color);
        }

        /* Document Indicator */
        .document-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .document-count {
            min-width: 24px;
            height: 24px;
            background: var(--secondary-color);
            color: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* Export Buttons */
        .export-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-export {
            border-radius: 4px;
            padding: 0.375rem 0.75rem;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
        }

        /* Checkbox */
        .select-checkbox {
            width: 18px;
            height: 18px;
            border-radius: 3px;
            border: 2px solid #dee2e6;
            cursor: pointer;
            position: relative;
        }

        .select-checkbox:checked {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .select-checkbox:checked:after {
            content: '✓';
            position: absolute;
            color: white;
            font-size: 12px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
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
            border-top: 3px solid var(--secondary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Pagination */
        .pagination-container {
            background: white;
            border-top: 1px solid #dee2e6;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .pagination-info {
            color: #6c757d;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .toolbar-left {
                min-width: 100%;
                order: 2;
            }
            
            .toolbar-right {
                min-width: 100%;
                order: 1;
                justify-content: space-between;
            }
            
            .search-box {
                max-width: none;
            }
        }

        @media (max-width: 768px) {
            .asset-management {
                padding: 1rem;
            }
            
            .stats-container {
                grid-template-columns: 1fr 1fr;
            }
            
            .table-toolbar {
                flex-direction: column;
                gap: 0.75rem;
            }
            
            .toolbar-left,
            .toolbar-right {
                width: 100%;
            }
            
            .bulk-actions-container {
                flex-direction: column;
                align-items: stretch;
            }
            
            .bulk-info {
                justify-content: space-between;
            }
            
            .bulk-controls {
                width: 100%;
                justify-content: flex-start;
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
            
            .quick-filters {
                overflow-x: auto;
                padding-bottom: 0.5rem;
                -webkit-overflow-scrolling: touch;
            }
            
            .export-buttons {
                flex-wrap: wrap;
            }
            
            .pagination-container {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #dee2e6;
        }

        /* Fixed Columns */
        .fixed-first-column {
            position: sticky;
            left: 0;
            background: white;
            z-index: 5;
            box-shadow: 2px 0 2px -1px rgba(0,0,0,0.1);
        }

        .fixed-last-column {
            position: sticky;
            right: 0;
            background: white;
            z-index: 5;
            box-shadow: -2px 0 2px -1px rgba(0,0,0,0.1);
        }

        /* Estilo para os cards de formulário (igual ao companies) */
        .form-section {
            margin-bottom: 1.5rem;
        }

        .form-section-title {
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
            font-weight: 600;
        }

        .required:after {
            content: " *";
            color: #e74c3c;
        }

        .form-control:disabled,
        .form-select:disabled {
            background-color: #f8f9fa;
        }

        .form-text {
            font-size: 0.8rem;
            color: #6c757d;
        }
    </style>
@endpush

@section('content')

<div class="container-fluid px-0 asset-management">
    <!-- Cabeçalho -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="h3 mb-1">
                    <i class="fas fa-boxes text-primary me-2"></i>Gestão de Activos
                </h1>
                <p class="text-muted mb-0">Gerencie todos os ativos da empresa</p>
            </div>
            <button class="btn btn-primary" id="toggleForm">
                <i class="fas fa-plus me-2"></i>Novo Activo
            </button>
        </div>
    </div>

    <!-- Create/Edit Form Card -->
    <div class="row mb-4" id="assetFormCard" style="display: none;">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0" id="formTitle">
                        <i class="fas fa-boxes me-2"></i>Adicionar Novo Activo
                    </h5>
                </div>
                <div class="card-body">
                    <form id="assetForm">
                        <div class="alert alert-danger d-none" id="formErrors">
                            <ul class="mb-0" id="errorList"></ul>
                        </div>
                        
                        <input type="hidden" id="assetId" name="id">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        
                        <div class="form-section">
                            <h6 class="form-section-title">Informação Básica</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="assetName" class="form-label required">Nome do Activo</label>
                                    <input type="text" class="form-control" id="assetName" name="name" required
                                           placeholder="Digite o nome do activo">
                                    <div class="invalid-feedback" id="name-error"></div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="assetCode" class="form-label required">Código do Activo</label>
                                    <input type="text" class="form-control" id="assetCode" name="code" required
                                           placeholder="Ex: ASSET-001">
                                    <div class="invalid-feedback" id="code-error"></div>
                                    <div class="form-text">Código único identificador</div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="assetCategory" class="form-label required">Categoria</label>
                                    <select class="form-select" id="assetCategory" name="category" required>
                                        <option value="">Selecione uma categoria</option>
                                        <option value="hardware">Hardware</option>
                                        <option value="software">Software</option>
                                        <option value="equipamento">Equipamento</option>
                                        <option value="mobiliario">Mobiliário</option>
                                        <option value="veiculo">Veículo</option>
                                        <option value="outro">Outro</option>
                                    </select>
                                    <div class="invalid-feedback" id="category-error"></div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="serialNumber" class="form-label">Número de Série</label>
                                    <input type="text" class="form-control" id="serialNumber" name="serial_number"
                                           placeholder="Número de série do activo">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="purchaseDate" class="form-label">Data de Aquisição</label>
                                    <input type="date" class="form-control" id="purchaseDate" name="purchase_date">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="assetBrand" class="form-label">Marca</label>
                                    <input type="text" class="form-control" id="assetBrand" name="brand"
                                           placeholder="Marca do activo">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="assetModel" class="form-label">Modelo</label>
                                    <input type="text" class="form-control" id="assetModel" name="model"
                                           placeholder="Modelo do activo">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h6 class="form-section-title">Informação Financeira</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="baseValue" class="form-label required">Valor Base (MT)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">MT</span>
                                        <input type="number" class="form-control" id="baseValue" name="base_value" 
                                               step="0.01" min="0" required placeholder="0.00">
                                    </div>
                                    <div class="invalid-feedback" id="base_value-error"></div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="ivaValue" class="form-label required">IVA 16% (MT)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">MT</span>
                                        <input type="number" class="form-control" id="ivaValue" name="iva_value" 
                                               step="0.01" min="0" required placeholder="0.00">
                                    </div>
                                    <div class="invalid-feedback" id="iva_value-error"></div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="totalValue" class="form-label required">Valor Total (MT)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">MT</span>
                                        <input type="number" class="form-control" id="totalValue" name="total_value" 
                                               step="0.01" min="0" required placeholder="0.00">
                                    </div>
                                    <div class="invalid-feedback" id="total_value-error"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h6 class="form-section-title">Documentação</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="assetSupplier" class="form-label">Fornecedor</label>
                                    <select class="form-select select2" id="assetSupplier" name="supplier_id">
                                        <option value="">Selecione um fornecedor</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="assetInvoice" class="form-label">Factura</label>
                                    <select class="form-select select2" id="assetInvoice" name="invoice_id">
                                        <option value="">Selecione uma factura</option>
                                        @foreach($invoices as $invoice)
                                            <option value="{{ $invoice->id }}">{{ $invoice->number }} - {{ $invoice->supplier->name ?? '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="assetRequest" class="form-label">Requisição</label>
                                    <select class="form-select select2" id="assetRequest" name="request_id">
                                        <option value="">Selecione uma requisição</option>
                                        @foreach($requests as $req)
                                            <option value="{{ $req->id }}">{{ $req->code }} - {{ $req->project->name ?? 'N/A' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="assetShipment" class="form-label">Remessa</label>
                                    <select class="form-select select2" id="assetShipment" name="shipment_id">
                                        <option value="">Selecione uma remessa</option>
                                        @foreach($shipments as $shipment)
                                            <option value="{{ $shipment->id }}">{{ $shipment->tracking_number }} - {{ $shipment->supplier->name ?? '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h6 class="form-section-title">Atribuição e Localização</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="assetEmployee" class="form-label">Colaborador</label>
                                    <select class="form-select select2" id="assetEmployee" name="employee_id">
                                        <option value="">Selecione um colaborador</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}">
                                                {{ $employee->name }} ({{ $employee->company->name ?? 'N/A' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">Se selecionar um colaborador, o activo fica automaticamente "Atribuído"</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="warrantyEndDate" class="form-label">Data de Término da Garantia</label>
                                    <input type="date" class="form-control" id="warrantyEndDate" name="warranty_expiry">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="assetDepartment" class="form-label">Departamento</label>
                                    <input type="text" class="form-control" id="assetDepartment" name="department"
                                           placeholder="Departamento do activo">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="assetLocation" class="form-label">Localização</label>
                                    <input type="text" class="form-control" id="assetLocation" name="location"
                                           placeholder="Localização física do activo">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h6 class="form-section-title">Observações</h6>
                            <div class="mb-3">
                                <label for="assetDescription" class="form-label">Descrição / Observações</label>
                                <textarea class="form-control" id="assetDescription" name="description" rows="3"
                                          placeholder="Descrição detalhada ou observações sobre o activo"></textarea>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" id="cancelForm">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <span id="submitText">Guardar Activo</span>
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

    <!-- Estatísticas -->
    <div class="stats-container">
        <div class="stat-item stat-total">
            <div class="stat-icon">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value" id="totalAssets">0</h3>
                <p class="stat-label">Total de Activos</p>
            </div>
        </div>
        <div class="stat-item stat-disponivel">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value" id="availableAssets">0</h3>
                <p class="stat-label">Disponíveis</p>
            </div>
        </div>
        <div class="stat-item stat-atribuido">
            <div class="stat-icon">
                <i class="fas fa-user-tag"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value" id="assignedAssets">0</h3>
                <p class="stat-label">Atribuídos</p>
            </div>
        </div>
        <div class="stat-item stat-inoperacional">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value" id="inoperationalAssets">0</h3>
                <p class="stat-label">Inoperacionais</p>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="table-toolbar">
        <div class="toolbar-left">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" class="form-control" 
                       placeholder="Pesquisar por código, nome, série..." 
                       id="tableSearch">
            </div>
            
            <div class="quick-filters">
                <button class="quick-filter active" data-filter="all">
                    <i class="fas fa-layer-group"></i>
                    <span>Todos</span>
                </button>
                <button class="quick-filter" data-filter="disponivel">
                    <i class="fas fa-check-circle"></i>
                    <span>Disponíveis</span>
                </button>
                <button class="quick-filter" data-filter="atribuido">
                    <i class="fas fa-user-tag"></i>
                    <span>Atribuídos</span>
                </button>
                <button class="quick-filter" data-filter="inoperacional">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Inoperacionais</span>
                </button>
                <button class="quick-filter" data-filter="garantia">
                    <i class="fas fa-clock"></i>
                    <span>Garantia</span>
                </button>
            </div>
        </div>
        
        <div class="toolbar-right">
            <!-- Botões de Exportação -->
            <div class="export-buttons">
                <button class="btn btn-outline-success btn-export" onclick="exportToExcel()">
                    <i class="fas fa-file-excel"></i>
                    <span>Excel</span>
                </button>
                <button class="btn btn-outline-secondary btn-export" onclick="printTable()">
                    <i class="fas fa-print"></i>
                    <span>Imprimir</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Ações em Massa -->
    <div class="bulk-actions-container" id="bulkActions" style="display: none;">
        <div class="bulk-info">
            <span class="selected-count" id="selectedCount">0 selecionados</span>
            <span class="text-muted small">
                <input type="checkbox" id="selectAll" class="me-2">
                Selecionar todos na página
            </span>
        </div>
        <div class="bulk-controls">
            <select class="form-select form-select-sm bulk-select" id="bulkActionSelect">
                <option value="">Ações em massa...</option>
                <option value="assign">Atribuir a Colaborador</option>
                <option value="remove_assignment">Remover Atribuição</option>
                <option value="inoperational">Marcar como Inoperacional</option>
                <option value="writeOff">Abater Activo</option>
                <option value="delete">Eliminar</option>
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
            
            <table class="table table-hover" id="assetsTable">
    <thead>
        <tr>
            <th width="50" class="fixed-first-column">
                <input type="checkbox" class="select-checkbox" id="selectAllCheckbox">
            </th>
            <th width="100">Código</th>
            <th>Activo</th>
            <th width="120">Categoria</th>
            <th width="120">Estado</th>
            <th width="120">Nº Série</th>
            <th width="120">Modelo</th>
            <th width="120">Marca</th>
            <th width="120">Valor Base</th>
            <th width="120">IVA</th>
            <th width="120">Valor Total</th>
            <th width="150">Empresa Colab.</th>
            <th width="120">Fornecedor</th>
            <th width="120">Factura</th>
            <th width="120">Requisição</th>
            <th width="120">Remessa</th>
            <th width="120">Garantia</th>
            <th width="100">Documentos</th>
            <th width="200" class="text-center fixed-last-column">Ações</th>
        </tr>
    </thead>
    <tbody>
        <!-- Dados carregados via AJAX -->
    </tbody>
</table>
        </div>
        
        <!-- Paginação -->
        <div class="pagination-container">
            <div class="pagination-info">
                Mostrando <span id="currentCount">0</span> de <span id="totalCount">0</span> activos
            </div>
            <nav aria-label="Navegação">
                <ul class="pagination pagination-sm mb-0" id="paginationControls">
                    <!-- Gerado automaticamente -->
                </ul>
            </nav>
        </div>
    </div>

    <!-- Botão Flutuante -->
    <button class="btn btn-primary rounded-circle shadow-lg position-fixed" 
            style="bottom: 2rem; right: 2rem; width: 56px; height: 56px; z-index: 1000;"
            onclick="showCreateForm()"
            title="Novo Activo">
        <i class="fas fa-plus"></i>
    </button>
</div>

<!-- Modal de Atribuição -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignModalLabel">Atribuir Activo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="assignForm">
                <div class="modal-body">
                    <input type="hidden" id="assignAssetId" name="asset_id">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    
                    <div class="mb-3">
                        <label for="assignEmployee" class="form-label required">Colaborador</label>
                        <select class="form-select select2" id="assignEmployee" name="employee_id" required>
                            <option value="">Selecione um colaborador</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">
                                    {{ $employee->name }} ({{ $employee->company->name ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        O estado do activo será alterado automaticamente para "Atribuído"
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Atribuir</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Visualização Rápida -->
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickViewModalLabel">Detalhes do Activo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">Informação Básica</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Código:</th>
                                    <td id="qvCode" class="fw-bold"></td>
                                </tr>
                                <tr>
                                    <th>Nome:</th>
                                    <td id="qvName"></td>
                                </tr>
                                <tr>
                                    <th>Categoria:</th>
                                    <td id="qvCategory"></td>
                                </tr>
                                <tr>
                                    <th>Estado:</th>
                                    <td id="qvStatus"></td>
                                </tr>
                                <tr>
                                    <th>Nº Série:</th>
                                    <td id="qvSerial"></td>
                                </tr>
                                <tr>
                                    <th>Marca/Modelo:</th>
                                    <td id="qvBrandModel"></td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">Fornecedor e Documentos</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Fornecedor:</th>
                                    <td id="qvSupplier"></td>
                                </tr>
                                <tr>
                                    <th>Factura:</th>
                                    <td id="qvInvoice"></td>
                                </tr>
                                <tr>
                                    <th>Requisição:</th>
                                    <td id="qvRequest"></td>
                                </tr>
                                <tr>
                                    <th>Remessa:</th>
                                    <td id="qvShipment"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">Informação Financeira</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Valor Base:</th>
                                    <td id="qvBaseValue" class="text-end"></td>
                                </tr>
                                <tr>
                                    <th>IVA 16%:</th>
                                    <td id="qvIvaValue" class="text-end"></td>
                                </tr>
                                <tr>
                                    <th>Valor Total:</th>
                                    <td id="qvTotalValue" class="text-end"></td>
                                </tr>
                                <tr>
                                    <th>Data Aquisição:</th>
                                    <td id="qvPurchaseDate"></td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">Atribuição e Localização</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Colaborador:</th>
                                    <td id="qvEmployee"></td>
                                </tr>
                                <tr>
                                    <th>Data Atribuição:</th>
                                    <td id="qvAssignmentDate"></td>
                                </tr>
                                <tr>
                                    <th>Departamento:</th>
                                    <td id="qvDepartment"></td>
                                </tr>
                                <tr>
                                    <th>Localização:</th>
                                    <td id="qvLocation"></td>
                                </tr>
                                <tr>
                                    <th>Garantia:</th>
                                    <td id="qvWarranty"></td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">Observações</h6>
                            <div class="border rounded p-2 bg-light" id="qvNotes" style="min-height: 60px;">
                                -- Sem observações --
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" onclick="editFromQuickView()">
                    <i class="fas fa-edit me-1"></i>Editar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Documentos -->
<div class="modal fade" id="documentsModal" tabindex="-1" aria-labelledby="documentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentsModalLabel">Gestão de Documentos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Upload de Documentos</h6>
                            </div>
                            <div class="card-body">
                                <form id="uploadDocumentsForm" enctype="multipart/form-data">
                                    <input type="hidden" id="documentsAssetId" name="asset_id">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    
                                    <div class="mb-3">
                                        <label for="documentType" class="form-label">Tipo de Documento</label>
                                        <select class="form-select" id="documentType" name="document_type">
                                            <option value="manual">Manual</option>
                                            <option value="garantia">Garantia</option>
                                            <option value="fatura">Fatura</option>
                                            <option value="comprovativo">Comprovativo de Pagamento</option>
                                            <option value="certificado">Certificado</option>
                                            <option value="contrato">Contrato</option>
                                            <option value="outro">Outro</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="documentDescription" class="form-label">Descrição</label>
                                        <input type="text" class="form-control" id="documentDescription" 
                                               name="description" placeholder="Ex: Manual de utilizador">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="uploadDocuments" class="form-label">Selecionar Ficheiros</label>
                                        <input type="file" class="form-control" id="uploadDocuments" 
                                               name="documents[]" multiple required>
                                        <div class="form-text">Máx. 10MB por ficheiro. Formatos: PDF, DOC, XLS, JPG, PNG</div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-upload me-1"></i>Upload de Documentos
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Documentos do Activo</h6>
                                <button class="btn btn-sm btn-outline-primary" onclick="refreshDocuments()">
                                    <i class="fas fa-redo"></i> Atualizar
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="documentsList" style="max-height: 400px; overflow-y: auto;">
                                    <div class="text-center py-5">
                                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Nenhum documento carregado</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script> --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
let table;
let selectedAssets = new Set();

// ============ DATA TABLE INITIALIZATION ============

function initializeDataTable() {
    table = $('#assetsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("assets.datatable") }}',
            data: function(d) {
                d.quick_filter = $('.quick-filter.active').data('filter') || 'all';
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
                className: 'fixed-first-column',
                width: '50px'
            },
            {
                data: 'code',
                className: 'fw-semibold',
                width: '100px',
                render: function(data) {
                    return '<span class="text-primary">' + data + '</span>';
                }
            },
            {
                data: 'name',
                render: function(data, type, row) {
                    return '<div class="fw-semibold text-truncate" style="max-width: 200px;">' + data + '</div>';
                },
                width: '200px'
            },
            {
                data: 'category_badge',
                orderable: false,
                searchable: false,
                width: '120px'
            },
            {
                data: 'status_badge',
                orderable: false,
                searchable: false,
                width: '120px'
            },
            // Serial Number
            {
                data: 'serial_number',
                width: '120px',
                render: function(data, type, row) {
                    return data ? `<div class="text-truncate" title="${data}">${data}</div>` : '--';
                }
            },
            // Modelo
            {
                data: 'model',
                width: '120px',
                render: function(data, type, row) {
                    return data ? `<div class="text-truncate" title="${data}">${data}</div>` : '--';
                }
            },
            // Marca
            {
                data: 'brand',
                width: '120px',
                render: function(data, type, row) {
                    return data ? `<div class="text-truncate" title="${data}">${data}</div>` : '--';
                }
            },
            // Valor Base
            {
                data: 'base_value',
                width: '120px',
                render: function(data, type, row) {
                    return data ? formatCurrency(data) : '--';
                }
            },
            // IVA
            {
                data: 'iva_value',
                width: '120px',
                render: function(data, type, row) {
                    return data ? formatCurrency(data) : '--';
                }
            },
            // Valor Total
            {
                data: 'total_value',
                width: '120px',
                render: function(data, type, row) {
                    return data ? formatCurrency(data) : '--';
                }
            },
            // Empresa do Colaborador
            {
                data: 'company_name',
                width: '150px',
                render: function(data, type, row) {
                    return data ? `<div class="text-truncate" title="${data}">${data}</div>` : '--';
                }
            },
            // Fornecedor
            {
                data: 'supplier_info',
                width: '120px',
                render: function(data, type, row) {
                    return data ? `<div class="text-truncate" title="${data}">${data}</div>` : '--';
                }
            },
            // Factura
            {
                data: 'invoice_number',
                width: '120px',
                render: function(data, type, row) {
                    return data ? `<div class="text-truncate" title="${data}">${data}</div>` : '--';
                }
            },
            // Requisição
            {
                data: 'request_code',
                width: '120px',
                render: function(data, type, row) {
                    return data ? `<div class="text-truncate" title="${data}">${data}</div>` : '--';
                }
            },
            // Remessa
            {
                data: 'shipment_tracking',
                width: '120px',
                render: function(data, type, row) {
                    return data ? `<div class="text-truncate" title="${data}">${data}</div>` : '--';
                }
            },
            // Garantia
            {
                data: 'warranty_indicator',
                orderable: false,
                searchable: false,
                width: '120px'
            },
            // Documentos
            {
                data: 'documents_count',
                orderable: false,
                searchable: false,
                className: 'text-center',
                width: '100px',
                render: function(data, type, row) {
                    if (data > 0) {
                        return `
                            <button class="btn btn-sm btn-outline-primary" 
                                    onclick="showDocumentsModal(${row.id})"
                                    title="${data} documento(s)">
                                <i class="fas fa-file me-1"></i>${data}
                            </button>
                        `;
                    }
                    return `
                        <button class="btn btn-sm btn-outline-secondary" 
                                onclick="showDocumentsModal(${row.id})"
                                title="Adicionar documentos">
                            <i class="fas fa-plus"></i>
                        </button>
                    `;
                }
            },
            // Ações
            {
                data: 'actions',
                orderable: false,
                searchable: false,
                className: 'text-center fixed-last-column',
                width: '200px'
            }
        ],
        order: [[1, 'desc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            
        scrollX: true,
        scrollY: 'calc(100vh - 150px)',
        scrollCollapse: true,
        paging: true,
        info: true,
        dom: '<"top"if>rt<"bottom"lp><"clear">',
        drawCallback: function() {
            updateSelectedCheckboxes();
            updateBulkActionsVisibility();
            
            if (table && $.fn.DataTable.isDataTable('#assetsTable')) {
                table.columns.adjust();
            }
        },
        createdRow: function(row, data) {
            $(row).attr('data-asset-id', data.id);
        },
        initComplete: function() {
            setTimeout(function() {
                if (table && $.fn.DataTable.isDataTable('#assetsTable')) {
                    table.columns.adjust();
                }
            }, 100);
        }
    });
    
    $(window).on('resize', function() {
        if (table && $.fn.DataTable.isDataTable('#assetsTable')) {
            table.columns.adjust();
        }
    });
}

// Adicione esta função para formatar moeda
function formatCurrency(value) {
    return new Intl.NumberFormat('pt-PT', {
        style: 'currency',
        currency: 'MZN' // ou 'EUR' dependendo da sua moeda
    }).format(value || 0);
}

// ============ FORMULARIO DE ASSETS (igual ao companies) ============

// Toggle form visibility
$('#toggleForm').click(function() {
    resetAssetForm();
    $('#formTitle').html('<i class="fas fa-boxes me-2"></i>Adicionar Novo Activo');
    $('#assetFormCard').slideToggle('fast', function() {
        if ($(this).is(':visible')) {
            $('#toggleForm').html('<i class="fas fa-times me-2"></i>Fechar Formulário');
            // Scroll to form
            $('html, body').animate({
                scrollTop: $('#assetFormCard').offset().top - 20
            }, 500);
        } else {
            $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Novo Activo');
        }
    });
});

// Botão cancelar
$('#cancelForm').click(function() {
    $('#assetFormCard').slideUp();
    $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Novo Activo');
    resetAssetForm();
});

// Função para mostrar formulário de criação
function showCreateForm() {
    resetAssetForm();
    $('#formTitle').html('<i class="fas fa-boxes me-2"></i>Adicionar Novo Activo');
    $('#assetFormCard').slideDown('fast');
    $('#toggleForm').html('<i class="fas fa-times me-2"></i>Fechar Formulário');
    
    // Scroll to form
    $('html, body').animate({
        scrollTop: $('#assetFormCard').offset().top - 20
    }, 500);
}

// Resetar formulário
function resetAssetForm() {
    $('#assetForm')[0].reset();
    $('#assetId').val('');
    $('#formErrors').addClass('d-none');
    $('#errorList').empty();
    
    // Resetar selects
    $('#assetCategory').val('');
    
    // Limpar selects Select2
    $('.select2').val('').trigger('change');
    
    // Limpar erros de validação
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').text('');
}

// ============ EVENT LISTENERS ============

function initializeEventListeners() {
    // Search com debounce
    let searchTimeout;
    $('#tableSearch').on('keyup', function() {
        clearTimeout(searchTimeout);
        showLoading();
        searchTimeout = setTimeout(() => {
            table.search(this.value).draw();
        }, 500);
    });

    // Quick filters
    $('.quick-filter').on('click', function() {
        $('.quick-filter').removeClass('active');
        $(this).addClass('active');
        showLoading();
        table.draw();
    });

    // Select all checkbox
    $('#selectAll, #selectAllCheckbox').on('change', function() {
        const isChecked = $(this).prop('checked');
        $('.asset-checkbox:visible').prop('checked', isChecked).trigger('change');
    });

    // Individual checkboxes
    $(document).on('change', '.asset-checkbox', function() {
        const assetId = $(this).data('id').toString();
        if ($(this).prop('checked')) {
            selectedAssets.add(assetId);
        } else {
            selectedAssets.delete(assetId);
        }
        updateSelectedCount();
        updateBulkActionsVisibility();
        updateSelectAllCheckbox();
    });

    // Inicializar Select2 para modais e formulário principal
    $('.select2').select2({
        width: '100%',
        placeholder: 'Selecione uma opção',
        allowClear: true
    });
}

// ============ UTILITY FUNCTIONS ============

function showLoading() {
    $('.table-loading').removeClass('d-none');
}

function hideLoading() {
    $('.table-loading').addClass('d-none');
}

function showTableError() {
    const tbody = $('#assetsTable tbody');
    tbody.html(`
        <tr>
            <td colspan="12" class="text-center py-5">
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3 text-warning"></i>
                    <h5 class="mb-2">Erro ao carregar dados</h5>
                    <p class="text-muted mb-3">Ocorreu um erro ao carregar os activos.</p>
                    <button class="btn btn-primary" onclick="table.ajax.reload()">
                        <i class="fas fa-redo me-2"></i>Tentar novamente
                    </button>
                </div>
            </td>
        </tr>
    `);
}

function updateStats(stats) {
    $('#totalAssets').text(stats.total || 0);
    $('#availableAssets').text(stats.disponivel || 0);
    $('#assignedAssets').text(stats.atribuido || 0);
    $('#inoperationalAssets').text(stats.inoperacional || 0);
}

function updateSelectedCount() {
    $('#selectedCount').text(`${selectedAssets.size} selecionado(s)`);
}

function updateSelectAllCheckbox() {
    const visibleCheckboxes = $('.asset-checkbox:visible');
    const checkedCheckboxes = $('.asset-checkbox:visible:checked');
    const allChecked = visibleCheckboxes.length > 0 && visibleCheckboxes.length === checkedCheckboxes.length;
    
    $('#selectAll, #selectAllCheckbox').prop('checked', allChecked);
}

function updateSelectedCheckboxes() {
    $('.asset-checkbox').each(function() {
        const assetId = $(this).closest('tr').data('asset-id');
        if (assetId) {
            $(this).prop('checked', selectedAssets.has(assetId.toString()));
        }
    });
    updateSelectAllCheckbox();
}

function updateBulkActionsVisibility() {
    if (selectedAssets.size > 0) {
        $('#bulkActions').slideDown();
    } else {
        $('#bulkActions').slideUp();
    }
}

function clearSelection() {
    selectedAssets.clear();
    $('.asset-checkbox').prop('checked', false);
    updateSelectedCount();
    updateBulkActionsVisibility();
    updateSelectAllCheckbox();
    $('#bulkActionSelect').val('');
}

// ============ FORM FUNCTIONS ============

function showEditForm(id) {
    resetAssetForm();
    $('#formTitle').html('<i class="fas fa-edit me-2"></i>Editar Activo');
    $('#assetFormCard').slideDown();
    $('#toggleForm').html('<i class="fas fa-times me-2"></i>Fechar Formulário');
    
    // Buscar dados após abrir o formulário
    fetchAssetData(id);
    
    // Scroll to form
    $('html, body').animate({
        scrollTop: $('#assetFormCard').offset().top - 20
    }, 500);
}

function fetchAssetData(id) {
    showLoading();
    
    $.ajax({
        url: `/assets/${id}/edit`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                populateAssetForm(response.data);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Não foi possível carregar os dados do activo.'
                });
                $('#assetFormCard').slideUp();
                $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Novo Activo');
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: xhr.responseJSON?.message || 'Erro ao carregar dados do activo.'
            });
            $('#assetFormCard').slideUp();
            $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Novo Activo');
        },
        complete: function() {
            hideLoading();
        }
    });
}

function populateAssetForm(asset) {
    console.log('Dados do asset recebidos:', asset);
    
    // Campo oculto ID
    $('#assetId').val(asset.id || '');
    
    // Campos básicos
    $('#assetName').val(asset.name || '');
    $('#assetCode').val(asset.code || '');
    $('#assetCategory').val(asset.category || '');
    
    // Campos de texto
    $('#serialNumber').val(asset.serial_number || '');
    $('#purchaseDate').val(asset.purchase_date || '');
    $('#assetBrand').val(asset.brand || '');
    $('#assetModel').val(asset.model || '');
    $('#assetDepartment').val(asset.department || '');
    $('#assetLocation').val(asset.location || '');
    $('#assetDescription').val(asset.description || '');
    
    // Campos numéricos
    $('#baseValue').val(parseFloat(asset.base_value) || 0);
    $('#ivaValue').val(parseFloat(asset.iva_value) || 0);
    $('#totalValue').val(parseFloat(asset.total_value) || 0);
    
    // Campos de data
    $('#warrantyEndDate').val(asset.warranty_expiry || '');
    
    // Inicializar Select2 se necessário
    setTimeout(() => {
        // Fornecedor
        const supplierId = asset.supplier_id || (asset.supplier ? asset.supplier.id : '');
        if (supplierId) {
            $('#assetSupplier').val(supplierId).trigger('change');
        } else {
            $('#assetSupplier').val('').trigger('change');
        }
        
        // Factura
        const invoiceId = asset.invoice_id || (asset.invoice ? asset.invoice.id : '');
        if (invoiceId) {
            $('#assetInvoice').val(invoiceId).trigger('change');
        } else {
            $('#assetInvoice').val('').trigger('change');
        }
        
        // Requisição
        const requestId = asset.request_id || (asset.request ? asset.request.id : '');
        if (requestId) {
            $('#assetRequest').val(requestId).trigger('change');
        } else {
            $('#assetRequest').val('').trigger('change');
        }
        
        // Remessa
        const shipmentId = asset.shipment_id || (asset.shipment ? asset.shipment.id : '');
        if (shipmentId) {
            $('#assetShipment').val(shipmentId).trigger('change');
        } else {
            $('#assetShipment').val('').trigger('change');
        }
        
        // Colaborador
        const employeeId = asset.employee_id || (asset.employee ? asset.employee.id : '');
        if (employeeId) {
            $('#assetEmployee').val(employeeId).trigger('change');
        } else {
            $('#assetEmployee').val('').trigger('change');
        }
    }, 200);
}

// ============ FORM SUBMISSION ============

$('#assetForm').on('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = $('#submitBtn');
    const submitText = $('#submitText');
    const loadingSpinner = $('#loadingSpinner');
    const assetId = $('#assetId').val();
    
    // Show loading state
    submitBtn.prop('disabled', true);
    submitText.hide();
    loadingSpinner.show();
    
    // Clear previous errors
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').text('');
    $('#formErrors').addClass('d-none');
    $('#errorList').empty();
    
    // Coletar dados do formulário
    const formData = $(this).serialize();
    
    const url = assetId ? `/assets/${assetId}` : '/assets';
    const method = assetId ? 'PUT' : 'POST';
    
    $.ajax({
        url: url,
        type: method,
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Toast.fire({
                    icon: 'success',
                    title: response.message
                });
                
                resetAssetForm();
                table.ajax.reload();
                loadStats();
                
                // Hide form after success
                $('#assetFormCard').slideUp();
                $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Novo Activo');
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                
                // Mostrar erros no topo do formulário
                let errorHtml = '';
                $.each(errors, function(key, value) {
                    errorHtml += `<li>${value[0]}</li>`;
                    
                    // Marcar campos inválidos
                    $(`#${key}`).addClass('is-invalid');
                    $(`#${key}-error`).text(value[0]);
                });
                
                $('#errorList').html(errorHtml);
                $('#formErrors').removeClass('d-none');
                
                // Scroll para erros
                $('html, body').animate({
                    scrollTop: $('#formErrors').offset().top - 100
                }, 500);
                
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

// ============ ASSIGNMENT FUNCTIONS ============

function showAssignModal(id) {
    $('#assignAssetId').val(id);
    $('#assignForm')[0].reset();
    
    // Inicializar Select2 no modal de atribuição
    $('#assignModal').modal('show');
    
    setTimeout(() => {
        if (!$('#assignEmployee').hasClass('select2-hidden-accessible')) {
            $('#assignEmployee').select2({
                dropdownParent: $('#assignModal'),
                width: '100%'
            });
        }
        $('#assignEmployee').val('').trigger('change.select2');
    }, 500);
}

$('#assignForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = $(this).serialize();
    const assetId = $('#assignAssetId').val();
    
    $.ajax({
        url: `/assets/${assetId}/assign`,
        type: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                $('#assignModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Activo atribuído!',
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
                text: xhr.responseJSON?.message || 'Erro ao atribuir activo'
            });
        }
    });
});

function removeAssignment(id) {
    Swal.fire({
        title: 'Remover Atribuição?',
        text: 'O activo ficará disponível para nova atribuição.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, remover!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/assets/${id}/remove-assignment`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Atribuição removida!',
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
                        text: xhr.responseJSON?.message || 'Erro ao remover atribuição'
                    });
                }
            });
        }
    });
}

// ============ QUICK VIEW FUNCTIONS ============

function showQuickView(id) {
    showLoading();
    $.ajax({
        url: `/assets/${id}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                populateQuickView(response.data);
                $('#quickViewModal').modal('show');
            }
        },
        complete: function() {
            hideLoading();
        }
    });
}

function populateQuickView(asset) {
    $('#qvCode').text(asset.code);
    $('#qvName').text(asset.name);
    $('#qvCategory').text(getCategoryLabel(asset.category));
    $('#qvStatus').html(`<span class="status-badge ${getStatusClass(asset.asset_status)}">${getStatusLabel(asset.asset_status)}</span>`);
    $('#qvSerial').text(asset.serial_number || '--');
    $('#qvBrandModel').text(`${asset.brand || ''} ${asset.model || ''}`.trim() || '--');
    $('#qvSupplier').text(asset.supplier?.name || '--');
    $('#qvInvoice').text(asset.invoice?.number || '--');
    $('#qvRequest').text(asset.request?.code || '--');
    $('#qvShipment').text(asset.shipment?.tracking_number || '--');
    $('#qvBaseValue').text(formatCurrency(asset.base_value || 0));
    $('#qvIvaValue').text(formatCurrency(asset.iva_value || 0));
    $('#qvTotalValue').text(formatCurrency(asset.total_value || 0));
    $('#qvPurchaseDate').text(formatDate(asset.purchase_date));
    $('#qvEmployee').text(asset.employee?.name || '--');
    $('#qvAssignmentDate').text(formatDate(asset.assignment_date));
    $('#qvDepartment').text(asset.department || '--');
    $('#qvLocation').text(asset.location || '--');
    $('#qvWarranty').html(getWarrantyBadge(asset.warranty_expiry));
    $('#qvNotes').text(asset.description || '-- Sem observações --');
    
    $('#quickViewModal').data('assetId', asset.id);
}

function editFromQuickView() {
    const assetId = $('#quickViewModal').data('assetId');
    $('#quickViewModal').modal('hide');
    if (assetId) {
        showEditForm(assetId);
    }
}

function getCategoryLabel(category) {
    const categories = {
        'hardware': 'Hardware',
        'software': 'Software',
        'equipamento': 'Equipamento',
        'mobiliario': 'Mobiliário',
        'veiculo': 'Veículo',
        'outro': 'Outro'
    };
    return categories[category] || category;
}

function getStatusLabel(status) {
    const statuses = {
        'disponivel': 'Disponível',
        'atribuido': 'Atribuído',
        'manutencao': 'Em Manutenção',
        'inoperacional': 'Inoperacional',
        'abatido': 'Abatido'
    };
    return statuses[status] || status;
}

function getStatusClass(status) {
    const classes = {
        'disponivel': 'badge-disponivel',
        'atribuido': 'badge-atribuido',
        'manutencao': 'badge-manutencao',
        'inoperacional': 'badge-inoperacional',
        'abatido': 'badge-abatido'
    };
    return classes[status] || '';
}

function formatDate(dateString) {
    if (!dateString) return '--';
    return new Date(dateString).toLocaleDateString('pt-PT');
}

function getWarrantyBadge(endDate) {
    if (!endDate) return '<span class="badge bg-secondary">Sem garantia</span>';
    
    const today = new Date();
    const end = new Date(endDate);
    const daysLeft = Math.ceil((end - today) / (1000 * 60 * 60 * 24));
    
    if (daysLeft < 0) {
        return '<span class="badge bg-danger">Garantia expirada</span>';
    } else if (daysLeft <= 30) {
        return `<span class="badge bg-warning">Expira em ${daysLeft} dias</span>`;
    } else {
        return `<span class="badge bg-success">Até ${formatDate(endDate)}</span>`;
    }
}

// ============ DOCUMENT FUNCTIONS ============

function showDocumentsModal(id) {
    $('#documentsAssetId').val(id);
    $('#uploadDocumentsForm')[0].reset();
    $('#documentsList').html('<p class="text-center"><i class="fas fa-spinner fa-spin"></i> Carregando documentos...</p>');
    $('#documentsModal').modal('show');
    
    loadDocuments(id);
}

function loadDocuments(id) {
    $.ajax({
        url: `/assets/${id}/documents`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                updateDocumentsList(response.data);
            }
        },
        error: function() {
            $('#documentsList').html('<p class="text-danger">Erro ao carregar documentos</p>');
        }
    });
}

function updateDocumentsList(documents) {
    if (!documents || documents.length === 0) {
        $('#documentsList').html('<p class="text-muted text-center">Nenhum documento carregado</p>');
        return;
    }
    
    let html = `
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Tamanho</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    documents.forEach(doc => {
        const size = (doc.size / 1024).toFixed(2);
        const typeLabels = {
            'manual': 'Manual',
            'garantia': 'Garantia',
            'fatura': 'Fatura',
            'comprovativo': 'Comprovativo',
            'certificado': 'Certificado',
            'contrato': 'Contrato',
            'outro': 'Outro'
        };
        
        html += `
            <tr>
                <td class="text-truncate" style="max-width: 200px;" title="${doc.original_name}">
                    ${doc.original_name}
                </td>
                <td>${typeLabels[doc.document_type] || doc.document_type}</td>
                <td>${size} KB</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <a href="/assets/documents/${doc.id}/download" class="btn btn-info" title="Download">
                            <i class="fas fa-download"></i>
                        </a>
                        <button class="btn btn-danger" onclick="deleteDocument(${doc.id})" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    html += `</tbody></table></div>`;
    $('#documentsList').html(html);
}

$('#uploadDocumentsForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const assetId = $('#documentsAssetId').val();
    
    $.ajax({
        url: `/assets/${assetId}/documents`,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                $('#uploadDocumentsForm')[0].reset();
                loadDocuments(assetId);
                Swal.fire({
                    icon: 'success',
                    title: 'Documentos carregados!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: xhr.responseJSON?.message || 'Erro ao carregar documentos'
            });
        }
    });
});

function deleteDocument(documentId) {
    Swal.fire({
        title: 'Eliminar documento?',
        text: 'Esta ação não pode ser revertida!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, eliminar!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/assets/documents/${documentId}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        const assetId = $('#documentsAssetId').val();
                        loadDocuments(assetId);
                        Swal.fire({
                            icon: 'success',
                            title: 'Documento eliminado!',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                }
            });
        }
    });
}

function refreshDocuments() {
    const assetId = $('#documentsAssetId').val();
    if (assetId) {
        loadDocuments(assetId);
    }
}

// ============ STATUS CHANGE FUNCTIONS ============

function markInoperational(id) {
    Swal.fire({
        title: 'Marcar como Inoperacional',
        input: 'textarea',
        inputLabel: 'Motivo (opcional)',
        inputPlaceholder: 'Descreva o motivo...',
        showCancelButton: true,
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/assets/${id}/inoperational`,
                type: 'POST',
                data: {
                    reason: result.value,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Activo marcado como inoperacional!',
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
                        text: xhr.responseJSON?.message || 'Erro ao marcar como inoperacional'
                    });
                }
            });
        }
    });
}

function writeOffAsset(id) {
    Swal.fire({
        title: 'Abater Activo',
        input: 'textarea',
        inputLabel: 'Motivo (opcional)',
        inputPlaceholder: 'Descreva o motivo...',
        showCancelButton: true,
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/assets/${id}/write-off`,
                type: 'POST',
                data: {
                    reason: result.value,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Activo abatido!',
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
                        text: xhr.responseJSON?.message || 'Erro ao abater activo'
                    });
                }
            });
        }
    });
}

// ============ DELETE FUNCTIONS ============

function confirmDelete(id) {
    Swal.fire({
        title: 'Eliminar Activo?',
        text: 'Esta ação não pode ser revertida! O activo será movido para a lixeira.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, eliminar!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            deleteAsset(id);
        }
    });
}

function deleteAsset(id) {
    showLoading();
    
    $.ajax({
        url: `/assets/${id}`,
        type: 'DELETE',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Activo eliminado!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                table.ajax.reload();
                loadStats();
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Não foi possível eliminar o activo.'
            });
        },
        complete: function() {
            hideLoading();
        }
    });
}

// ============ BULK ACTIONS ============

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
    
    if (selectedAssets.size === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Nenhum activo selecionado',
            text: 'Por favor, selecione pelo menos um activo.'
        });
        return;
    }

    const actionTitles = {
        'assign': 'Atribuir Activos',
        'remove_assignment': 'Remover Atribuições',
        'inoperational': 'Marcar como Inoperacional',
        'writeOff': 'Abater Activos',
        'delete': 'Eliminar Activos'
    };

    Swal.fire({
        title: actionTitles[action] || 'Confirmar Ação',
        text: `Deseja executar esta ação em ${selectedAssets.size} activo(s)?`,
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
            asset_ids: Array.from(selectedAssets),
            _token: '{{ csrf_token() }}'
        };
        
        if (action === 'assign') {
            const { value: employeeId } = await Swal.fire({
                title: 'Selecionar Colaborador',
                input: 'select',
                inputOptions: {
                    @foreach($employees as $employee)
                    '{{ $employee->id }}': '{{ $employee->name }}',
                    @endforeach
                },
                inputPlaceholder: 'Selecione um colaborador',
                showCancelButton: true
            });
            
            if (!employeeId) {
                hideLoading();
                $('#bulkActionSelect').val('');
                return;
            }
            
            data.employee_id = employeeId;
        }
        
        const response = await $.ajax({
            url: '{{ route("assets.bulk-action") }}',
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

// ============ EXPORT FUNCTION ============

function exportToExcel() {
    showLoading();
    
    const quickFilter = $('.quick-filter.active').data('filter') || 'all';
    const searchTerm = $('#tableSearch').val();
    
    const params = {
        quick_filter: quickFilter,
        search: searchTerm,
        _token: '{{ csrf_token() }}'
    };
    
    $.ajax({
        url: '{{ route("assets.export") }}',
        type: 'POST',
        data: params,
        success: function(response) {
            if (response.success) {
                // Converter dados para Excel
                const ws = XLSX.utils.json_to_sheet(response.data);
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, "Activos");
                XLSX.writeFile(wb, `activos_${new Date().toISOString().split('T')[0]}.xlsx`);
                
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

function printTable() {
    const printContents = document.querySelector('.table-container').innerHTML;
    const originalContents = document.body.innerHTML;
    
    document.body.innerHTML = `
        <html>
            <head>
                <title>Activos - Impressão</title>
                // <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    @media print {
                        .table-container { 
                            margin: 0 !important;
                            padding: 0 !important;
                            box-shadow: none !important;
                        }
                        .action-buttons, .bulk-actions-container { display: none !important; }
                    }
                </style>
            </head>
            <body>
                ${printContents}
                <script>
                    window.onload = function() {
                        window.print();
                        setTimeout(function() {
                            document.body.innerHTML = originalContents;
                            window.location.reload();
                        }, 500);
                    }
                <\/script>
            </body>
        </html>
    `;
}

// ============ LOAD STATS ============

function loadStats() {
    $.ajax({
        url: '{{ route("assets.stats") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                updateStats(response.data);
            }
        }
    });
}

// ============ INITIALIZATION ============

$(document).ready(function() {
    initializeDataTable();
    initializeEventListeners();
    loadStats();
    
    // Inicializar Select2
    $('.select2').select2({
        width: '100%'
    });
    
    // Initialize Toast (se necessário)
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