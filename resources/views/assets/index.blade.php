@extends('layouts.app')

@section('title', 'Gestão de Activos')

@push('styles')
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
            --shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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

        .stat-manutencao .stat-icon {
            background: rgba(243, 156, 18, 0.1);
            color: var(--warning-color);
        }

        .stat-inoperacional .stat-icon {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        .stat-abatido .stat-icon {
            background: rgba(108, 117, 125, 0.1);
            color: #6c757d;
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

        /* Status Filters */
        .status-filters {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 0.5rem;
        }

        .status-filter {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            border: 1px solid #dee2e6;
            background: white;
            color: #6c757d;
            font-size: 0.75rem;
            transition: all 0.2s ease;
        }

        .status-filter:hover,
        .status-filter.active {
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
            padding: 0.75rem 1rem;
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

        .badge-process-complete {
            background: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
            border: 1px solid var(--success-color);
        }

        .badge-process-incomplete {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
            border: 1px solid var(--danger-color);
        }

        /* Life Status */
        .life-valid {
            color: var(--success-color);
            font-weight: 500;
        }

        .life-expiring {
            color: var(--warning-color);
            font-weight: 500;
        }

        .life-expired {
            color: var(--danger-color);
            font-weight: 500;
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

        .btn-action.maintenance:hover {
            color: var(--warning-color);
            border-color: var(--warning-color);
        }

        .btn-action.history:hover {
            color: #00bcd4;
            border-color: #00bcd4;
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
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
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

        /* Form Section */
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

        /* Info Cards */
        .info-card {
            background: #f8f9fa;
            border-radius: 4px;
            padding: 0.75rem;
            border-left: 3px solid var(--secondary-color);
        }

        .info-card-title {
            font-size: 0.75rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }

        .info-card-value {
            font-weight: 600;
            color: var(--primary-color);
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
            box-shadow: 2px 0 2px -1px rgba(0, 0, 0, 0.1);
        }

        .fixed-last-column {
            position: sticky;
            right: 0;
            background: white;
            z-index: 5;
            box-shadow: -2px 0 2px -1px rgba(0, 0, 0, 0.1);
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
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="assetCode" name="code"
                                                required placeholder="Ex: AST-0001">
                                            <button class="btn btn-outline-secondary" type="button"
                                                onclick="generateAssetCode()">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </div>
                                        <div class="invalid-feedback" id="code-error"></div>
                                        <div class="form-text">Código único identificador</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="assetCategory" class="form-label required">Categoria</label>
                                        <select class="form-select" id="assetCategory" name="category" required>
                                            <option value="">Selecione uma categoria</option>
                                            @foreach ($categories as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback" id="category-error"></div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="serialNumber" class="form-label">Número de Série</label>
                                        <input type="text" class="form-control" id="serialNumber" name="serial_number"
                                            placeholder="Número de série do activo">
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

                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label for="assetDescription" class="form-label">Descrição</label>
                                        <textarea class="form-control" id="assetDescription" name="description" rows="2"
                                            placeholder="Descrição detalhada do activo"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h6 class="form-section-title">Informação Financeira e Classificação</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="lifeDate" class="form-label">Fim da Vida Útil</label>
                                        <input type="date" class="form-control" id="lifeDate" name="life_date">
                                        <div class="form-text">Data prevista para fim da vida útil</div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="warrantyEndDate" class="form-label">Data de Término da
                                            Garantia</label>
                                        <input type="date" class="form-control" id="warrantyEndDate"
                                            name="warranty_expiry">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="totalValue" class="form-label required">Valor Total (MT)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">MT</span>
                                            <input type="number" class="form-control" id="totalValue"
                                                name="total_value" step="0.01" min="0" required
                                                placeholder="0.00">
                                        </div>
                                        <div class="invalid-feedback" id="total_value-error"></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="baseValue" class="form-label required">Valor Base (MT)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">MT</span>
                                            <input type="number" class="form-control" id="baseValue" name="base_value"
                                                step="0.01" min="0" required placeholder="0.00" readonly>
                                        </div>
                                        <div class="invalid-feedback" id="base_value-error"></div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="ivaValue" class="form-label required">IVA 16% (MT)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">MT</span>
                                            <input type="number" class="form-control" id="ivaValue" name="iva_value"
                                                step="0.01" min="0" required placeholder="0.00" readonly>
                                        </div>
                                        <div class="invalid-feedback" id="iva_value-error"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h6 class="form-section-title">Documentação e Requisição</h6>
                                <div class="row">
                                    <div class="col-md-5 mb-3">
                                        <label for="assetRequest" class="form-label required">Requisição</label>
                                        <select class="form-select select2" id="assetRequest" name="request_id" required>
                                            <option value="">Selecione uma requisição</option>
                                            @foreach ($requests as $req)
                                                <option value="{{ $req->id }}"
                                                    data-project="{{ $req->project->name ?? '' }}"
                                                    data-status="{{ $req->process_status }}">
                                                    {{ $req->code }} - {{ $req->project->name ?? 'N/A' }}
                                                    ({{ $req->process_status }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback" id="request_id-error"></div>
                                        <div class="form-text" id="requestInfo"></div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="economicClassifier" class="form-label">Classificador Económico</label>
                                        <input type="text" class="form-control" id="economicClassifier"
                                            name="economic_classifier" placeholder="Ex: 1.2.3.4">
                                        <div class="form-text">Código do classificador económico</div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="assetEmployee" class="form-label">Colaborador</label>
                                        <select class="form-select select2" id="assetEmployee" name="employee_id">
                                            <option value="">Selecione um colaborador</option>
                                            @foreach ($employees as $employee)
                                                <option value="{{ $employee->id }}">
                                                    {{ $employee->name }}
                                                    @if ($employee->company)
                                                        ({{ $employee->company->name }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="form-text">Se selecionar um colaborador, o activo fica automaticamente
                                            "Atribuído"</div>
                                    </div>
                                </div>

                                <div class="row" id="requestDetails" style="display: none;">
                                    <div class="col-12">
                                        <div class="info-card">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="info-card-title">Projecto</div>
                                                    <div class="info-card-value" id="requestProject"></div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="info-card-title">Tipo</div>
                                                    <div class="info-card-value" id="requestType"></div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="info-card-title">Data</div>
                                                    <div class="info-card-value" id="requestDate"></div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="info-card-title">Status</div>
                                                    <div class="info-card-value" id="requestStatus"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                {{-- <h6 class="form-section-title">Atribuição e Localização</h6>
                                <div class="row">






                                    <div class="col-md-12 mb-3">
                                        <label for="assetNotes" class="form-label">Observações</label>
                                        <textarea class="form-control" id="assetNotes" name="notes" rows="2" placeholder="Observações adicionais"></textarea>
                                    </div>

                                </div> --}}

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
            <div class="stat-item stat-manutencao">
                <div class="stat-icon">
                    <i class="fas fa-tools"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value" id="maintenanceAssets">0</h3>
                    <p class="stat-label">Em Manutenção</p>
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
            <div class="stat-item stat-abatido">
                <div class="stat-icon">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value" id="writtenOffAssets">0</h3>
                    <p class="stat-label">Abatidos</p>
                </div>
            </div>
        </div>

        <!-- Filtros Rápidos de Status -->
        <div class="status-filters">
            <button class="status-filter active" data-status="all">Todos</button>
            <button class="status-filter" data-status="disponivel">Disponíveis</button>
            <button class="status-filter" data-status="atribuido">Atribuídos</button>
            <button class="status-filter" data-status="manutencao">Em Manutenção</button>
            <button class="status-filter" data-status="inoperacional">Inoperacionais</button>
            <button class="status-filter" data-status="abatido">Abatidos</button>
            <button class="status-filter" data-status="garantia">Garantia a Expirar</button>
            <button class="status-filter" data-status="vida_util">Vida Útil a Terminar</button>
        </div>

        <!-- Toolbar -->
        <div class="table-toolbar">
            <div class="toolbar-left">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control"
                        placeholder="Pesquisar por código, nome, série, classificador..." id="tableSearch">
                </div>

                <div class="quick-filters">
                    <button class="quick-filter active" data-filter="all">
                        <i class="fas fa-layer-group"></i>
                        <span>Todos</span>
                    </button>
                    <button class="quick-filter" data-filter="ativos">
                        <i class="fas fa-check-circle"></i>
                        <span>Activos</span>
                    </button>
                    <button class="quick-filter" data-filter="inativos">
                        <i class="fas fa-trash"></i>
                        <span>Inativos</span>
                    </button>
                </div>

                <!-- Filtro temporal: Data de Requisição -->
                <div class="d-flex align-items-center ms-3" style="gap: 0.5rem;">
                    <label for="filterDateFrom" class="form-label mb-0" style="font-size:0.85rem;">Requisição de:</label>
                    <input type="date" class="form-control form-control-sm" id="filterDateFrom" style="width: 140px;">
                    <span style="font-size:0.85rem;">a</span>
                    <input type="date" class="form-control form-control-sm" id="filterDateTo" style="width: 140px;">
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
                    <option value="mark_maintenance">Marcar para Manutenção</option>
                    <option value="inoperational">Marcar como Inoperacional</option>
                    <option value="write_off">Abater Activo</option>
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
                            <th width="40" class="fixed-first-column">
                                <input type="checkbox" class="select-checkbox" id="selectAllCheckbox">
                            </th>
                            <th width="90">Código</th>
                            <th width="180">Activo</th>
                            <th width="100">Categoria</th>
                            <th width="110">Estado</th>
                            <th width="110">Nº Série</th>
                            <th width="100">Marca</th>
                            <th width="100">Modelo</th>
                            <th width="100">Classificador</th>
                            <th width="100">Vida Útil</th>
                            <th width="100">Valor Base</th>
                            <th width="80">IVA</th>
                            <th width="100">Valor Total</th>
                            <th width="120">Projecto</th>
                            <th width="150">Colaborador</th>
                            <th width="150">Empresa</th>
                            <th width="150">Departamento</th>
                            <th width="100">Província</th>
                            <th width="120">Fornecedor</th>
                            <th width="100">Factura</th>
                            <th width="120">Requisição</th>
                            <th width="100">Req. Tipo</th>
                            <th width="100">Req. Data</th>
                            <th width="100">Req. Status</th>
                            <th width="100">Remessa</th>
                            <th width="100">Rem. Data</th>
                            <th width="100">Garantia</th>
                            <th width="80">Docs</th>
                            <th width="80">Manut.</th>
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
            style="bottom: 2rem; right: 2rem; width: 56px; height: 56px; z-index: 1000;" onclick="showCreateForm()"
            title="Novo Activo">
            <i class="fas fa-plus"></i>
        </button>
    </div>

    <!-- Modal de Manutenção -->
    <div class="modal fade" id="maintenanceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Marcar para Manutenção</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="maintenanceForm">
                    <div class="modal-body">
                        <input type="hidden" id="maintenanceAssetId" name="asset_id">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <div class="mb-3">
                            <label for="maintenanceType" class="form-label required">Tipo de Manutenção</label>
                            <select class="form-select" id="maintenanceType" name="maintenance_type" required>
                                <option value="">Selecione o tipo</option>
                                <option value="preventiva">Preventiva</option>
                                <option value="corretiva">Corretiva</option>
                                <option value="preditiva">Preditiva</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="maintenanceDescription" class="form-label required">Descrição</label>
                            <textarea class="form-control" id="maintenanceDescription" name="description" rows="3" required
                                placeholder="Descreva o problema ou a manutenção necessária"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="estimatedDuration" class="form-label">Duração Estimada (dias)</label>
                                <input type="number" class="form-control" id="estimatedDuration"
                                    name="estimated_duration" min="1" placeholder="Ex: 7">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="maintenanceProvider" class="form-label">Fornecedor/Prestador</label>
                                <input type="text" class="form-control" id="maintenanceProvider"
                                    name="maintenance_provider" placeholder="Nome do prestador">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="maintenanceNotes" class="form-label">Observações</label>
                            <textarea class="form-control" id="maintenanceNotes" name="notes" rows="2"
                                placeholder="Observações extras"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Marcar para Manutenção</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Conclusão de Manutenção -->
    <div class="modal fade" id="completeMaintenanceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Concluir Manutenção</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="completeMaintenanceForm">
                    <div class="modal-body">
                        <input type="hidden" id="completeMaintenanceAssetId" name="asset_id">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <div class="mb-3">
                            <label for="maintenanceId" class="form-label required">Manutenção</label>
                            <select class="form-select" id="maintenanceId" name="maintenance_id" required>
                                <option value="">Selecione a manutenção</option>
                                <!-- Carregado via AJAX -->
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="maintenanceResult" class="form-label required">Resultado</label>
                            <select class="form-select" id="maintenanceResult" name="result" required>
                                <option value="concluida">Concluída com Sucesso</option>
                                <option value="pendente">Pendente/Incompleta</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="actualDuration" class="form-label">Duração Real (dias)</label>
                                <input type="number" class="form-control" id="actualDuration" name="actual_duration"
                                    min="1" placeholder="Duração real">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="maintenanceCost" class="form-label">Custo (MT)</label>
                                <input type="number" class="form-control" id="maintenanceCost" name="cost"
                                    step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="technicianName" class="form-label">Técnico Responsável</label>
                            <input type="text" class="form-control" id="technicianName" name="technician_name"
                                placeholder="Nome do técnico">
                        </div>

                        <div class="mb-3">
                            <label for="completionNotes" class="form-label">Observações Finais</label>
                            <textarea class="form-control" id="completionNotes" name="notes" rows="3"
                                placeholder="Observações da conclusão"></textarea>
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

    <!-- Modal de Atribuição -->
    <div class="modal fade" id="assignModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Atribuir Activo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="assignForm">
                    <div class="modal-body">
                        <input type="hidden" id="assignAssetId" name="asset_id">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <div class="mb-3">
                            <label for="assignEmployee" class="form-label required">Colaborador</label>
                            <select class="form-select select2-modal" id="assignEmployee" name="employee_id" required>
                                <option value="">Selecione um colaborador</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">
                                        {{ $employee->name }}
                                        @if ($employee->company)
                                            ({{ $employee->company->name }})
                                        @endif
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
    <div class="modal fade" id="quickViewModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalhes do Activo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                                    <tr>
                                        <th>Classificador Económico:</th>
                                        <td id="qvClassifier"></td>
                                    </tr>
                                </table>
                            </div>

                            <div class="mb-3">
                                <h6 class="text-muted mb-2">Documentação</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">Requisição:</th>
                                        <td id="qvRequest"></td>
                                    </tr>
                                    <tr>
                                        <th>Projecto:</th>
                                        <td id="qvProject"></td>
                                    </tr>
                                    <tr>
                                        <th>Fornecedor:</th>
                                        <td id="qvSupplier"></td>
                                    </tr>
                                    <tr>
                                        <th>Factura:</th>
                                        <td id="qvInvoice"></td>
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
                                <h6 class="text-muted mb-2">Informação Financeira e Temporal</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">Valor Base:</th>
                                        <td id="qvBaseValue" class="text-end fw-bold"></td>
                                    </tr>
                                    <tr>
                                        <th>IVA 16%:</th>
                                        <td id="qvIvaValue" class="text-end"></td>
                                    </tr>
                                    <tr>
                                        <th>Valor Total:</th>
                                        <td id="qvTotalValue" class="text-end fw-bold text-primary"></td>
                                    </tr>
                                    <tr>
                                        <th>Fim Vida Útil:</th>
                                        <td id="qvLifeDate"></td>
                                    </tr>
                                    <tr>
                                        <th>Garantia:</th>
                                        <td id="qvWarranty"></td>
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
                                </table>
                            </div>

                            <div class="mb-3">
                                <h6 class="text-muted mb-2">Observações</h6>
                                <div class="border rounded p-2 bg-light" id="qvNotes" style="min-height: 60px;">
                                    -- Sem observações --
                                </div>
                            </div>

                            <div class="mb-3">
                                <h6 class="text-muted mb-2">Status do Processo</h6>
                                <div class="d-flex gap-2 align-items-center">
                                    <span id="qvProcessStatus"></span>
                                    <span id="qvProcessReason" class="text-muted small"></span>
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
                    <button type="button" class="btn btn-info" onclick="showHistoryFromQuickView()">
                        <i class="fas fa-history me-1"></i>Histórico
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Histórico -->
    <div class="modal fade" id="historyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Histórico do Activo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="historyTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="maintenances-tab" data-bs-toggle="tab"
                                data-bs-target="#maintenances" type="button" role="tab">
                                Manutenções
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="assignments-tab" data-bs-toggle="tab"
                                data-bs-target="#assignments" type="button" role="tab">
                                Atribuições
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content mt-3" id="historyTabContent">
                        <div class="tab-pane fade show active" id="maintenances" role="tabpanel">
                            <div id="maintenancesList">
                                <div class="text-center py-4">
                                    <i class="fas fa-spinner fa-spin"></i> Carregando...
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="assignments" role="tabpanel">
                            <div id="assignmentsList">
                                <div class="text-center py-4">
                                    <i class="fas fa-spinner fa-spin"></i> Carregando...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Documentos -->
    <div class="modal fade" id="documentsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Gestão de Documentos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                                            <div class="form-text">Máx. 10MB por ficheiro. Formatos: PDF, DOC, XLS, JPG,
                                                PNG</div>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <script>
        let table;
        let selectedAssets = new Set();
        let currentAssetId = null;


        // ============ DATA TABLE INITIALIZATION ============
        // Filtro temporal: recarregar tabela ao mudar datas
        $(document).on('change', '#filterDateFrom, #filterDateTo', function() {
            if (table) table.ajax.reload();
        });

        function initializeDataTable() {
            table = $('#assetsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('assets.datatable') }}',
                    data: function(d) {
                        d.quick_filter = $('.quick-filter.active').data('filter') || 'all';
                        var status = $('.status-filter.active').data('status');
                        d.asset_status = (status && status !== 'all') ? status : '';
                        d.show_deleted = $('.quick-filter.active').data('filter') === 'inativos';


                        // Adicionar filtros avançados
                        d.category = $('#filterCategory').val() || '';
                        d.project_id = $('#filterProject').val() || '';
                        d.employee_id = $('#filterEmployee').val() || '';
                        d.date_from = $('#filterDateFrom').val() || '';
                        d.date_to = $('#filterDateTo').val() || '';
                        d.warranty_status = $('#filterWarranty').val() || '';
                        d.life_status = $('#filterLife').val() || '';
                    },

                    dataSrc: function(json) {

                        try {
                            if (json && json.stats) {
                                updateStats(json.stats);
                            }
                            if (json && json.recordsTotal) {
                                $('#totalCount').text(json.recordsTotal);
                            }
                            if (json && json.recordsFiltered) {
                                $('#currentCount').text(json.recordsFiltered);
                            }
                        } catch (e) {
                            console.error('Error updating stats:', e);
                        } finally {
                            hideLoading();
                        }
                        return json.data || [];
                    },
                    error: function(xhr, error, thrown) {
                        hideLoading();
                        showTableError();

                    }

                },
                columns: [{
                        data: 'checkbox',
                        orderable: false,
                        searchable: false,
                        className: 'fixed-first-column',
                        width: '40px'
                    },
                    {
                        data: 'code',
                        className: 'fw-semibold',
                        width: '90px',
                        render: function(data) {
                            return '<span class="text-primary">' + (data || '--') + '</span>';
                        }
                    },
                    {
                        data: 'name',
                        render: function(data, type, row) {
                            return '<div class="fw-semibold text-truncate" style="max-width: 180px;" title="' +
                                (data || '') + '">' + (data || '--') + '</div>';
                        },
                        width: '180px'
                    },
                    {
                        data: 'category_badge',
                        orderable: false,
                        searchable: false,
                        width: '100px'
                    },
                    {
                        data: 'status_badge',
                        orderable: false,
                        searchable: false,
                        width: '110px'
                    },
                    {
                        data: 'serial_number',
                        width: '110px',
                        render: function(data) {
                            return data ? '<div class="text-truncate" title="' + data + '">' + data +
                                '</div>' : '--';
                        }
                    },
                    {
                        data: 'brand',
                        width: '100px',
                        render: function(data) {
                            return data ? '<div class="text-truncate" title="' + data + '">' + data +
                                '</div>' : '--';
                        }
                    },
                    {
                        data: 'model',
                        width: '100px',
                        render: function(data) {
                            return data ? '<div class="text-truncate" title="' + data + '">' + data +
                                '</div>' : '--';
                        }
                    },
                    {
                        data: 'economic_classifier',
                        width: '100px',
                        render: function(data) {
                            return data ? '<span class="badge bg-light text-dark">' + data + '</span>' :
                                '--';
                        }
                    },
                    {
                        data: 'life_status',
                        orderable: false,
                        searchable: false,
                        width: '100px'
                    },
                    {
                        data: 'base_value',
                        width: '100px',
                        render: function(data) {
                            return data ? formatCurrency(data) : '--';
                        },
                        className: 'text-end'
                    },
                    {
                        data: 'iva_value',
                        width: '80px',
                        render: function(data) {
                            return data ? formatCurrency(data) : '--';
                        },
                        className: 'text-end'
                    },
                    {
                        data: 'total_value',
                        width: '100px',
                        render: function(data) {
                            return data ? '<span class="fw-bold">' + formatCurrency(data) + '</span>' :
                            '--';
                        },
                        className: 'text-end'
                    },
                    {
                        data: 'project_name',
                        width: '120px',
                        render: function(data) {
                            return data ? '<div class="text-truncate" title="' + data + '">' + data +
                                '</div>' : '--';
                        }
                    },
                    {
                        data: 'employee_name',
                        width: '150px',
                        render: function(data) {
                            return data ? '<div class="text-truncate" title="' + data + '">' + data +
                                '</div>' : '--';
                        }
                    },
                    {
                        data: 'company_name',
                        width: '150px',
                        render: function(data) {
                            return data ? '<div class="text-truncate" title="' + data + '">' + data +
                                '</div>' : '--';
                        }
                    },
                    {
                        data: 'employee_department',
                        width: '150px',
                        render: function(data) {
                            return data ? '<div class="text-truncate" title="' + data + '">' + data +
                                '</div>' : '--';
                        }
                    },
                    {
                        data: 'company_province',
                        width: '150px',
                        render: function(data) {
                            return data ? '<div class="text-truncate" title="' + data + '">' + data +
                                '</div>' : '--';
                        }
                    },
                    {
                        data: 'supplier_name',
                        width: '120px',
                        render: function(data) {
                            return data ? '<div class="text-truncate" title="' + data + '">' + data +
                                '</div>' : '--';
                        }
                    },
                    {
                        data: 'invoice_number',
                        width: '100px',
                        render: function(data) {
                            return data ? '<div class="text-truncate" title="' + data + '">' + data +
                                '</div>' : '--';
                        }
                    },
                    {
                        data: 'request_code',
                        width: '120px',
                        render: function(data) {
                            return data ? '<span class="badge bg-secondary">' + data + '</span>' : '--';
                        }
                    },
                    {
                        data: 'request_type',
                        width: '100px',
                        render: function(data) {
                            if (data === 'internal') return '<span class="badge bg-info">Interna</span>';
                            if (data === 'external') return '<span class="badge bg-warning">Externa</span>';
                            return '--';
                        }
                    },
                    {
                        data: 'request_date',
                        width: '100px',
                        render: function(data) {
                            return data ? data : '--';
                        }
                    },
                    {
                        data: 'request_status',
                        width: '100px',
                        render: function(data) {
                            if (data === 'completo')
                        return '<span class="badge bg-success">Completo</span>';
                            if (data === 'incompleto')
                            return '<span class="badge bg-danger">Incompleto</span>';
                            return '--';
                        }
                    },
                    {
                        data: 'shipment_tracking',
                        width: '100px',
                        render: function(data) {
                            return data ? '<div class="text-truncate" title="' + data + '">' + data +
                                '</div>' : '--';
                        }
                    },
                    {
                        data: 'shipment_date',
                        width: '100px',
                        render: function(data) {
                            return data ? formatDate(data) : '--';
                        }
                    },
                    {
                        data: 'warranty_indicator',
                        orderable: false,
                        searchable: false,
                        width: '100px'
                    },
                    {
                        data: 'documents_count',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        width: '80px',
                        render: function(data) {
                            if (data > 0) {
                                return '<span class="badge bg-info">' + data + '</span>';
                            }
                            return '<span class="badge bg-secondary">0</span>';
                        }
                    },
                    {
                        data: 'maintenances_count',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        width: '80px',
                        render: function(data) {
                            if (data > 0) {
                                return '<span class="badge bg-warning">' + data + '</span>';
                            }
                            return '<span class="badge bg-secondary">0</span>';
                        }
                    },
                    {
                        data: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-center fixed-last-column',
                        width: '200px'
                    }
                ],
                order: [
                    [1, 'desc']
                ],
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                scrollX: true,
                scrollY: 'calc(100vh - 250px)',
                scrollCollapse: true,
                paging: true,
                info: true,
                dom: '<"top"if>rt<"bottom"lp><"clear">',
                drawCallback: function() {
                    updateSelectedCheckboxes();
                    updateBulkActionsVisibility();
                    if (table) {
                        table.columns.adjust();
                    }
                },
                createdRow: function(row, data) {
                    $(row).attr('data-asset-id', data.id);
                    if (data.asset_status === 'abatido' || data.asset_status === 'inoperacional') {
                        $(row).addClass('opacity-75');
                    }
                },
                initComplete: function() {
                    setTimeout(function() {
                        if (table) {
                            table.columns.adjust();
                        }
                    }, 100);
                }
            });
        }

        // ============ FORMATTING FUNCTIONS ============

        function formatCurrency(value) {
            return new Intl.NumberFormat('pt-PT', {
                style: 'currency',
                currency: 'MZN'
            }).format(value || 0);
        }

        function formatDate(dateString) {
            if (!dateString) return '--';
            try {
                const date = new Date(dateString);
                return date.toLocaleDateString('pt-PT');
            } catch (e) {
                return '--';
            }
        }

        // ============ FORM FUNCTIONS ============



        // Toggle form visibility
        $('#toggleForm').click(function() {
            resetAssetForm();
            $('#formTitle').html('<i class="fas fa-boxes me-2"></i>Adicionar Novo Activo');
            $('#assetFormCard').slideToggle('fast', function() {
                if ($(this).is(':visible')) {
                    $('#toggleForm').html('<i class="fas fa-times me-2"></i>Fechar Formulário');
                    
                    $('html, body').animate({
                        scrollTop: $('#assetFormCard').offset().top - 20
                    }, 500);
                } else {
                    $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Novo Activo');
                }
            });
        });

        $('#cancelForm').click(function() {
            $('#assetFormCard').slideUp();
            $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Novo Activo');
            resetAssetForm();
        });

        function showCreateForm() {
            resetAssetForm();
            $('#formTitle').html('<i class="fas fa-boxes me-2"></i>Adicionar Novo Activo');
            $('#assetFormCard').slideDown('fast');
            $('#toggleForm').html('<i class="fas fa-times me-2"></i>Fechar Formulário');
            
            $('html, body').animate({
                scrollTop: $('#assetFormCard').offset().top - 20
            }, 500);
        }

        function resetAssetForm() {
            $('#assetForm')[0].reset();
            $('#assetId').val('');
            $('#formErrors').addClass('d-none');
            $('#errorList').empty();
            $('#requestDetails').hide();

            $('.select2').val('').trigger('change');
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
        }

        // ============ CALCULATIONS ============

        function calculateFinancialValues() {
            const totalValue = parseFloat($('#totalValue').val()) || 0;

            if (totalValue > 0) {
                const ivaValue = totalValue * 0.16;
                const baseValue = totalValue - ivaValue;

                $('#ivaValue').val(ivaValue.toFixed(2));
                $('#baseValue').val(baseValue.toFixed(2));
            } else {
                $('#ivaValue').val('');
                $('#baseValue').val('');
            }
        }

        $('#totalValue').on('input change', calculateFinancialValues);

        // ============ REQUEST INFO ============

        $('#assetRequest').change(function() {
            const selected = $(this).find('option:selected');
            if (selected.val()) {
                const project = selected.data('project');
                const status = selected.data('status');

                $('#requestProject').text(project || 'N/A');
                $('#requestStatus').text(status || 'N/A');
                $('#requestDetails').show();

                if (status === 'incompleto') {
                    $(this).addClass('is-invalid');
                    $('#requestInfo').text('Atenção: Esta requisição está incompleta').addClass('text-danger');
                } else {
                    $(this).removeClass('is-invalid');
                    $('#requestInfo').text('').removeClass('text-danger');
                }
            } else {
                $('#requestDetails').hide();
                $('#requestInfo').text('');
            }
        });

        // ============ FORM SUBMISSION ============

        $('#assetForm').on('submit', function(e) {
            e.preventDefault();

            const submitBtn = $('#submitBtn');
            const submitText = $('#submitText');
            const loadingSpinner = $('#loadingSpinner');
            const assetId = $('#assetId').val();

            submitBtn.prop('disabled', true);
            submitText.hide();
            loadingSpinner.show();

            $('#baseValue, #ivaValue').prop('readonly', false);

            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            $('#formErrors').addClass('d-none');
            $('#errorList').empty();

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
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });

                        resetAssetForm();
                        table.ajax.reload();
                        loadStats();

                        $('#assetFormCard').slideUp();
                        $('#toggleForm').html('<i class="fas fa-plus me-2"></i>Novo Activo');
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        let errorHtml = '';
                        $.each(errors, function(key, value) {
                            errorHtml += `<li>${value[0]}</li>`;
                            $(`#${key}`).addClass('is-invalid');
                            $(`#${key}-error`).text(value[0]);
                        });

                        $('#errorList').html(errorHtml);
                        $('#formErrors').removeClass('d-none');

                        $('html, body').animate({
                            scrollTop: $('#formErrors').offset().top - 100
                        }, 500);

                        Swal.fire({
                            icon: 'error',
                            title: 'Erro de Validação',
                            text: 'Por favor, corrija os erros no formulário'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: xhr.responseJSON?.message || 'Ocorreu um erro'
                        });
                    }
                },
                complete: function() {
                    submitBtn.prop('disabled', false);
                    submitText.show();
                    loadingSpinner.hide();
                    $('#baseValue, #ivaValue').prop('readonly', true);
                }
            });
        });

        // ============ EVENT LISTENERS ============

        function initializeEventListeners() {
            let searchTimeout;
            $('#tableSearch').on('keyup', function() {
                clearTimeout(searchTimeout);
                showLoading();
                searchTimeout = setTimeout(() => {
                    table.search(this.value).draw();
                }, 500);
            });

            $('.quick-filter').on('click', function() {
                $('.quick-filter').removeClass('active');
                $(this).addClass('active');
                showLoading();
                table.ajax.reload(null, false);
            });

            $('.status-filter').on('click', function() {
                $('.status-filter').removeClass('active');
                $(this).addClass('active');
                showLoading();
                table.ajax.reload(null, false);
            });

            $('#selectAll, #selectAllCheckbox').on('change', function() {
                const isChecked = $(this).prop('checked');
                $('.asset-checkbox:visible').prop('checked', isChecked).trigger('change');
            });

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

            $('.select2').select2({
                width: '100%',
                placeholder: 'Selecione uma opção',
                allowClear: true
            });

            // A inicialização do Select2 do modal será feita ao abrir o modal

            // Evita warning de aria-hidden/focus ao fechar o modal
            $('#assignModal').on('hidden.bs.modal', function () {
                $(this).find(':focus').blur();
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
            <td colspan="28" class="text-center py-5">
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

        function loadStats() {
            $.ajax({
                url: '{{ route('assets.stats') }}',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        updateStats(response.data);
                    }
                }
            });
        }

        function updateStats(stats) {
            $('#totalAssets').text(stats.total || 0);
            $('#availableAssets').text(stats.disponivel || 0);
            $('#assignedAssets').text(stats.atribuido || 0);
            $('#maintenanceAssets').text(stats.manutencao || 0);
            $('#inoperationalAssets').text(stats.inoperacional || 0);
            $('#writtenOffAssets').text(stats.abatido || 0);
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

        // ============ ASSET ACTIONS ============

        function showEditForm(id) {
            resetAssetForm();
            $('#formTitle').html('<i class="fas fa-edit me-2"></i>Editar Activo');
            $('#assetFormCard').slideDown();
            $('#toggleForm').html('<i class="fas fa-times me-2"></i>Fechar Formulário');

            fetchAssetData(id);

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
                complete: hideLoading
            });
        }

        function populateAssetForm(asset) {
            $('#assetId').val(asset.id || '');
            $('#assetName').val(asset.name || '');
            $('#assetCode').val(asset.code || '');
            $('#assetCategory').val(asset.category || '');
            $('#serialNumber').val(asset.serial_number || '');
            $('#assetBrand').val(asset.brand || '');
            $('#assetModel').val(asset.model || '');
            $('#economicClassifier').val(asset.economic_classifier || '');
            $('#lifeDate').val(asset.life_date || '');
            $('#totalValue').val(asset.total_value || 0);
            $('#baseValue').val(asset.base_value || 0);
            $('#ivaValue').val(asset.iva_value || 0);
            $('#warrantyEndDate').val(asset.warranty_expiry || '');
            $('#assetDepartment').val(asset.department || '');
            $('#assetLocation').val(asset.location || '');
            $('#assetDescription').val(asset.description || '');
            $('#assetNotes').val(asset.notes || '');

            calculateFinancialValues();

            setTimeout(() => {
                $('#assetSupplier').val(asset.supplier_id || '').trigger('change');
                $('#assetInvoice').val(asset.invoice_id || '').trigger('change');
                $('#assetRequest').val(asset.request_id || '').trigger('change');
                $('#assetShipment').val(asset.shipment_id || '').trigger('change');
                $('#assetEmployee').val(asset.employee_id || '').trigger('change');
            }, 200);
        }

        // ============ ASSIGNMENT FUNCTIONS ============

        function showAssignModal(id) {
            $('#assignAssetId').val(id);
            $('#assignForm')[0].reset();
            $('#assignEmployee').val('').trigger('change');
            $('#assignModal').modal('show');
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
                            title: 'Atribuído!',
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

        // ============ MAINTENANCE FUNCTIONS ============

        function showMaintenanceModal(id) {
            $('#maintenanceAssetId').val(id);
            $('#maintenanceForm')[0].reset();
            $('#maintenanceModal').modal('show');
        }

        function showCompleteMaintenanceModal(id) {
            $('#completeMaintenanceAssetId').val(id);
            $('#completeMaintenanceForm')[0].reset();
            loadPendingMaintenances(id);
            $('#completeMaintenanceModal').modal('show');
        }

        function loadPendingMaintenances(assetId) {
            $.ajax({
                url: `/assets/${assetId}/maintenances`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const select = $('#maintenanceId');
                        select.empty().append('<option value="">Selecione a manutenção</option>');

                        response.data.forEach(maintenance => {
                            if (maintenance.status === 'agendada') {
                                select.append(new Option(
                                    `${maintenance.maintenance_type} - ${maintenance.description.substring(0, 50)}...`,
                                    maintenance.id
                                ));
                            }
                        });

                        if (select.find('option').length === 1) {
                            select.empty().append('<option value="">Nenhuma manutenção pendente</option>');
                        }
                    }
                }
            });
        }

        $('#maintenanceForm').on('submit', function(e) {
            e.preventDefault();

            const formData = $(this).serialize();
            const assetId = $('#maintenanceAssetId').val();

            $.ajax({
                url: `/assets/${assetId}/maintenance`,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#maintenanceModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Marcado para manutenção!',
                            timer: 2000
                        });
                        table.ajax.reload();
                        loadStats();
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: xhr.responseJSON?.message || 'Erro ao marcar para manutenção'
                    });
                }
            });
        });

        $('#completeMaintenanceForm').on('submit', function(e) {
            e.preventDefault();

            const formData = $(this).serialize();
            const assetId = $('#completeMaintenanceAssetId').val();

            $.ajax({
                url: `/assets/${assetId}/complete-maintenance`,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#completeMaintenanceModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Manutenção concluída!',
                            timer: 2000
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

        // ============ STATUS CHANGE FUNCTIONS ============

        function markInoperational(id) {
            Swal.fire({
                title: 'Marcar como Inoperacional',
                input: 'textarea',
                inputLabel: 'Motivo (obrigatório)',
                inputPlaceholder: 'Descreva o motivo...',
                inputAttributes: {
                    required: true
                },
                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar',
                preConfirm: (reason) => {
                    if (!reason) {
                        Swal.showValidationMessage('O motivo é obrigatório');
                    }
                    return reason;
                }
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
                                    title: 'Marcado como inoperacional!',
                                    timer: 2000
                                });
                                table.ajax.reload();
                                loadStats();
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: xhr.responseJSON?.message ||
                                    'Erro ao marcar como inoperacional'
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
                inputLabel: 'Motivo (obrigatório)',
                inputPlaceholder: 'Descreva o motivo...',
                inputAttributes: {
                    required: true
                },
                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar',
                preConfirm: (reason) => {
                    if (!reason) {
                        Swal.showValidationMessage('O motivo é obrigatório');
                    }
                    return reason;
                }
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
                                    timer: 2000
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

        // ============ QUICK VIEW FUNCTIONS ============

        function showQuickView(id) {
            showLoading();
            currentAssetId = id;

            $.ajax({
                url: `/assets/${id}`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        populateQuickView(response.data);
                        $('#quickViewModal').modal('show');
                    }
                },
                complete: hideLoading
            });
        }

        function populateQuickView(asset) {
            $('#qvCode').text(asset.code || '--');
            $('#qvName').text(asset.name || '--');
            $('#qvCategory').text(getCategoryLabel(asset.category));
            $('#qvStatus').html(
                `<span class="status-badge ${getStatusClass(asset.asset_status)}">${getStatusLabel(asset.asset_status)}</span>`
                );
            $('#qvSerial').text(asset.serial_number || '--');
            $('#qvBrandModel').text(asset.brand && asset.model ? `${asset.brand} ${asset.model}` : (asset.brand || asset
                .model || '--'));
            $('#qvClassifier').text(asset.economic_classifier || '--');

            // Request info
            $('#qvRequest').text(asset.request?.code || '--');
            $('#qvProject').text(asset.request?.project?.name || '--');
            $('#qvSupplier').text(asset.request?.supplier?.name || '--');
            $('#qvInvoice').text(asset.request?.invoice?.number || '--');
            $('#qvShipment').text(asset.request?.shipment?.guide || '--');

            // Financial
            $('#qvBaseValue').text(asset.base_value ? formatCurrency(asset.base_value) : '--');
            $('#qvIvaValue').text(asset.iva_value ? formatCurrency(asset.iva_value) : '--');
            $('#qvTotalValue').text(asset.total_value ? formatCurrency(asset.total_value) : '--');

            // Dates
            $('#qvLifeDate').html(getLifeDateHtml(asset.life_date));
            $('#qvWarranty').html(getWarrantyBadge(asset.warranty_expiry));

            // Assignment
            $('#qvEmployee').text(asset.employee?.name || '--');
            $('#qvAssignmentDate').text(asset.assignment_date ? formatDate(asset.assignment_date) : '--');
            $('#qvDepartment').text(asset.department || '--');
            $('#qvLocation').text(asset.employee?.company.province || '--');
            $('#qvNotes').text(asset.description || '-- Sem observações --');

            // Process Status
            if (asset.request?.process_status) {
                const statusClass = asset.request.process_status === 'completo' ? 'badge bg-success' : 'badge bg-danger';
                $('#qvProcessStatus').html(`<span class="${statusClass}">${asset.request.process_status}</span>`);
                $('#qvProcessReason').text(asset.incomplete_reason || '');
            } else {
                $('#qvProcessStatus').html('<span class="badge bg-secondary">N/A</span>');
                $('#qvProcessReason').text('');
            }

            $('#quickViewModal').data('assetId', asset.id);
        }

        function getLifeDateHtml(lifeDate) {
            if (!lifeDate) return '--';

            const today = new Date();
            const life = new Date(lifeDate);
            const diffTime = life - today;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            const diffMonths = Math.ceil(diffDays / 30);

            if (diffDays < 0) {
                return '<span class="life-expired"><i class="fas fa-exclamation-circle me-1"></i>Expirada</span>';
            } else if (diffDays <= 180) {
                return `<span class="life-expiring"><i class="fas fa-clock me-1"></i>${diffMonths} meses (${diffDays} dias)</span>`;
            } else {
                return `<span class="life-valid">${formatDate(lifeDate)}</span>`;
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

        function getWarrantyBadge(endDate) {
            if (!endDate) return '<span class="badge bg-secondary">Sem garantia</span>';

            const today = new Date();
            const end = new Date(endDate);
            const daysLeft = Math.ceil((end - today) / (1000 * 60 * 60 * 24));

            if (daysLeft < 0) {
                return '<span class="badge bg-danger">Expirada</span>';
            } else if (daysLeft <= 30) {
                return `<span class="badge bg-warning text-dark">Expira em ${daysLeft} dias</span>`;
            } else {
                return `<span class="badge bg-success">Até ${formatDate(endDate)}</span>`;
            }
        }

        function editFromQuickView() {
            const assetId = $('#quickViewModal').data('assetId');
            $('#quickViewModal').modal('hide');
            if (assetId) {
                showEditForm(assetId);
            }
        }

        function showHistoryFromQuickView() {
            const assetId = $('#quickViewModal').data('assetId');
            $('#quickViewModal').modal('hide');
            if (assetId) {
                showHistoryModal(assetId);
            }
        }

        function showHistoryModal(id) {
            currentAssetId = id;
            $('#historyModal').modal('show');
            loadHistory(id);
        }

        function loadHistory(assetId) {
            $.ajax({
                url: `/assets/${assetId}/history`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        displayMaintenances(response.data.maintenances);
                        displayAssignments(response.data.assignments);
                    }
                },
                error: function() {
                    $('#maintenancesList').html(
                        '<div class="alert alert-danger">Erro ao carregar histórico</div>');
                    $('#assignmentsList').html(
                        '<div class="alert alert-danger">Erro ao carregar histórico</div>');
                }
            });
        }

        function displayMaintenances(maintenances) {
            if (!maintenances || maintenances.length === 0) {
                $('#maintenancesList').html('<p class="text-muted text-center py-3">Nenhuma manutenção registada</p>');
                return;
            }

            let html = '<div class="list-group">';
            maintenances.forEach(m => {
                const statusClass = {
                    'agendada': 'warning',
                    'em_andamento': 'info',
                    'concluida': 'success',
                    'cancelada': 'secondary'
                } [m.status] || 'secondary';

                html += `
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-${statusClass} me-2">${m.status}</span>
                        <strong>${m.maintenance_type}</strong>
                    </div>
                    <small class="text-muted">${formatDate(m.created_at)}</small>
                </div>
                <p class="mb-1 mt-2">${m.description}</p>
                ${m.technician_name ? `<small class="text-muted">Técnico: ${m.technician_name}</small>` : ''}
                ${m.cost ? `<small class="text-muted ms-3">Custo: ${formatCurrency(m.cost)}</small>` : ''}
            </div>
        `;
            });
            html += '</div>';
            $('#maintenancesList').html(html);
        }

        function displayAssignments(assignments) {
            if (!assignments || assignments.length === 0) {
                $('#assignmentsList').html('<p class="text-muted text-center py-3">Nenhuma atribuição registada</p>');
                return;
            }

            let html = '<div class="list-group">';
            assignments.forEach(a => {
                html += `
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${a.employee?.name || 'Colaborador removido'}</strong>
                        <span class="badge bg-${a.status === 'atribuido' ? 'success' : 'secondary'} ms-2">${a.status}</span>
                    </div>
                    <div>
                        <small class="text-muted">De: ${formatDate(a.assignment_date)}</small>
                        ${a.release_date ? `<small class="text-muted ms-2">Até: ${formatDate(a.release_date)}</small>` : ''}
                    </div>
                </div>
                ${a.notes ? `<p class="mb-0 mt-1 small text-muted">${a.notes}</p>` : ''}
            </div>
        `;
            });
            html += '</div>';
            $('#assignmentsList').html(html);
        }

        // ============ DOCUMENT FUNCTIONS ============

        function showDocumentsModal(id) {
            $('#documentsAssetId').val(id);
            $('#uploadDocumentsForm')[0].reset();
            $('#documentsList').html(
                '<p class="text-center"><i class="fas fa-spinner fa-spin"></i> Carregando documentos...</p>');
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

            let html = '<div class="list-group">';
            documents.forEach(doc => {
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
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-file me-2 text-primary"></i>
                        <span title="${doc.original_name}" class="text-truncate" style="max-width: 300px;">
                            ${doc.original_name}
                        </span>
                        <span class="badge bg-secondary ms-2">${typeLabels[doc.document_type] || doc.document_type}</span>
                        <small class="text-muted ms-2">${(doc.size / 1024).toFixed(2)} KB</small>
                    </div>
                </div>
                <div class="btn-group btn-group-sm">
                    <a href="/assets/documents/${doc.id}/download" class="btn btn-info" title="Download">
                        <i class="fas fa-download"></i>
                    </a>
                    <button class="btn btn-danger" onclick="deleteDocument(${doc.id})" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
            });
            html += '</div>';
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
                            timer: 2000
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
                                    timer: 1500
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
                            timer: 2000
                        });
                        table.ajax.reload();
                        loadStats();
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: xhr.responseJSON?.message || 'Não foi possível eliminar o activo.'
                    });
                },
                complete: hideLoading
            });
        }

        function restoreAsset(id) {
            Swal.fire({
                title: 'Restaurar Activo?',
                text: 'O activo será restaurado da lixeira.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sim, restaurar!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/assets/${id}/restore`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Activo restaurado!',
                                    timer: 2000
                                });
                                table.ajax.reload();
                                loadStats();
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: xhr.responseJSON?.message || 'Erro ao restaurar activo'
                            });
                        }
                    });
                }
            });
        }

        function forceDeleteAsset(id) {
            Swal.fire({
                title: 'Eliminar Permanentemente?',
                text: 'Esta ação NÃO pode ser revertida! Todos os documentos associados serão eliminados.',
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, eliminar permanentemente!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/assets/${id}/force-delete`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Activo eliminado permanentemente!',
                                    timer: 2000
                                });
                                table.ajax.reload();
                                loadStats();
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: xhr.responseJSON?.message ||
                                    'Erro ao eliminar permanentemente'
                            });
                        }
                    });
                }
            });
        }

        // ============ BULK ACTIONS ============

        function executeBulkAction() {
            const action = $('#bulkActionSelect').val();

            if (!action) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selecione uma ação'
                });
                return;
            }

            if (selectedAssets.size === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Nenhum activo selecionado'
                });
                return;
            }

            const actionTitles = {
                'assign': 'Atribuir Activos',
                'remove_assignment': 'Remover Atribuições',
                'mark_maintenance': 'Marcar para Manutenção',
                'inoperational': 'Marcar como Inoperacional',
                'write_off': 'Abater Activos',
                'delete': 'Eliminar Activos'
            };

            Swal.fire({
                title: actionTitles[action],
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
                    const {
                        value: employeeId
                    } = await Swal.fire({
                        title: 'Selecionar Colaborador',
                        input: 'select',
                        inputOptions: {
                            @foreach ($employees as $employee)
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
                    url: '{{ route('assets.bulk-action') }}',
                    type: 'POST',
                    data: data
                });

                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Ação Concluída',
                        text: response.message
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

        // ============ EXPORT FUNCTIONS ============

        function exportToExcel() {
            showLoading();

            const data = {
                quick_filter: $('.quick-filter.active').data('filter') || 'all',
                asset_status: $('.status-filter.active').data('status') || 'all',
                search: $('#tableSearch').val(),
                _token: '{{ csrf_token() }}'
            };

            $.ajax({
                url: '{{ route('assets.export') }}',
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        const ws = XLSX.utils.json_to_sheet(response.data);
                        const wb = XLSX.utils.book_new();
                        XLSX.utils.book_append_sheet(wb, ws, "Activos");
                        XLSX.writeFile(wb, `activos_${new Date().toISOString().split('T')[0]}.xlsx`);

                        Swal.fire({
                            icon: 'success',
                            title: 'Exportação concluída!',
                            timer: 2000
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro na exportação',
                        text: xhr.responseJSON?.message || 'Erro ao gerar ficheiro'
                    });
                },
                complete: hideLoading
            });
        }

        function printTable() {
            window.print();
        }

        // ============ INITIALIZATION ============

        $(document).ready(function() {
            initializeDataTable();
            initializeEventListeners();
            loadStats();


            $('.select2').select2({
                width: '100%'
            });

            // Corrige Select2 no modal de atribuição
            $('#assignModal').on('shown.bs.modal', function () {
                var $select = $('#assignEmployee');
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }
                $select.select2({
                    width: '100%',
                    dropdownParent: $('#assignModal')
                });
            });

            console.log('jj');

            // Global Toast/Swal
            window.Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        });
    </script>
@endpush
