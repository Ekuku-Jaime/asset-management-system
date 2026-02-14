<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\Auth\ActivationController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaintenanceController;

// Dashboard Routes
Route::prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/statistics', [DashboardController::class, 'getStatistics'])->name('dashboard.statistics');
    Route::get('/charts', [DashboardController::class, 'getChartsData'])->name('dashboard.charts');
    Route::get('/alerts', [DashboardController::class, 'getAlerts'])->name('dashboard.alerts');
    Route::get('/financial', [DashboardController::class, 'getFinancialSummary'])->name('dashboard.financial');
    Route::get('/kpis', [DashboardController::class, 'getKPISummary'])->name('dashboard.kpis');
    Route::get('/timeline', [DashboardController::class, 'getProjectTimeline'])->name('dashboard.timeline');
});
// Rota para o convite: quando o usuário clicar no link do email
Route::get('/register/{token}', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.complete');

// Outras rotas de autenticação (login, logout, etc.)
Auth::routes(['register' => false]); // Desabilitamos o registro padrão

// Rota protegida para o dashboard
// Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard')->middleware('auth');
Route::middleware(['auth'])->group(function () {
  Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/data', [UserController::class, 'data'])->name('users.data');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::post('/users/{user}/resend', [UserController::class, 'resend'])->name('users.resend');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');   
});


// Suppliers Routes
Route::middleware(['auth'])->prefix('suppliers')->group(function () {
    Route::get('/', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/data', [SupplierController::class, 'data'])->name('suppliers.data');
    Route::get('/data/trashed', [SupplierController::class, 'dataTrashed'])->name('suppliers.data.trashed');
    Route::post('/', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
    Route::put('/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    Route::post('/{id}/restore', [SupplierController::class, 'restore'])->name('suppliers.restore');
    Route::delete('/{id}/force', [SupplierController::class, 'forceDelete'])->name('suppliers.force');
    Route::get('/search', [SupplierController::class, 'search'])->name('suppliers.search');
});

// Projects Routes
Route::middleware(['auth'])->prefix('projects')->group(function () {
    
// DataTables
    Route::get('/data', [ProjectController::class, 'data'])->name('projects.data');
    Route::get('/data/trashed', [ProjectController::class, 'dataTrashed'])->name('projects.data.trashed');
     // Stats
    Route::get('/stats', [ProjectController::class, 'getStats'])->name('projects.stats');
    
     // Search
    Route::get('/search', [ProjectController::class, 'search'])->name('projects.search');
// List views
    Route::get('/', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('/create', [ProjectController::class, 'create'])->name('projects.create');
    
    
   
    // CRUD operations
    Route::post('/', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    
    // Soft delete operations
    Route::post('/{id}/restore', [ProjectController::class, 'restore'])->name('projects.restore');
    Route::delete('/{id}/force', [ProjectController::class, 'forceDelete'])->name('projects.force');
    
   
});


// Companies Routes
Route::middleware(['auth'])->prefix('companies')->group(function () {
    Route::get('/', [CompanyController::class, 'index'])->name('companies.index');
    Route::get('/data', [CompanyController::class, 'data'])->name('companies.data');
    Route::get('/data/trashed', [CompanyController::class, 'dataTrashed'])->name('companies.data.trashed');
    Route::post('/', [CompanyController::class, 'store'])->name('companies.store');
    Route::get('/{company}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
    Route::put('/{company}', [CompanyController::class, 'update'])->name('companies.update');
    Route::delete('/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');
    Route::post('/{id}/restore', [CompanyController::class, 'restore'])->name('companies.restore');
    Route::delete('/{id}/force', [CompanyController::class, 'forceDelete'])->name('companies.force');
    Route::get('/search', [CompanyController::class, 'search'])->name('companies.search');
    Route::get('/provinces', [CompanyController::class, 'provinces'])->name('companies.provinces');
});

// Employees Routes
Route::middleware(['auth'])->prefix('employees')->group(function () {
    Route::get('/', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/data', [EmployeeController::class, 'data'])->name('employees.data');
    Route::get('/data/trashed', [EmployeeController::class, 'dataTrashed'])->name('employees.data.trashed');
    Route::post('/', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    Route::post('/{id}/restore', [EmployeeController::class, 'restore'])->name('employees.restore');
    Route::delete('/{id}/force', [EmployeeController::class, 'forceDelete'])->name('employees.force');
    Route::get('/search', [EmployeeController::class, 'search'])->name('employees.search');
    Route::get('/by-company/{companyId}', [EmployeeController::class, 'byCompany'])->name('employees.by-company');
});
// Invoices Routes
Route::middleware(['auth'])->prefix('invoices')->group(function () {
    // Rotas principais das faturas
    Route::get('/', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/data', [InvoiceController::class, 'data'])->name('invoices.data');
    Route::get('/data/trashed', [InvoiceController::class, 'dataTrashed'])->name('invoices.data.trashed');
    Route::post('/', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
    Route::put('/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
    Route::delete('/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
    Route::post('/{id}/restore', [InvoiceController::class, 'restore'])->name('invoices.restore');
    Route::delete('/{id}/force', [InvoiceController::class, 'forceDelete'])->name('invoices.force');
    Route::get('/search', [InvoiceController::class, 'search'])->name('invoices.search');
    Route::post('/report', [InvoiceController::class, 'report'])->name('invoices.report');
  
    // Rotas de documentos - CORRETAS
    Route::post('/{invoice}/documents', [InvoiceController::class, 'uploadDocuments'])
        ->name('invoices.documents.upload');
        
    Route::delete('/documents/{document}', [InvoiceController::class, 'removeDocument'])
        ->name('invoices.documents.destroy');
        
    Route::get('/documents/{document}/download', [InvoiceController::class, 'downloadDocument'])
        ->name('invoices.documents.download');
        
    Route::get('/{invoice}/documents', [InvoiceController::class, 'listDocuments'])
        ->name('invoices.documents.index');
});

// Shipments Routes
Route::middleware(['auth'])->prefix('shipments')->group(function () {
    Route::get('/', [ShipmentController::class, 'index'])->name('shipments.index');
    Route::get('/data', [ShipmentController::class, 'data'])->name('shipments.data');
    Route::get('/data/trashed', [ShipmentController::class, 'dataTrashed'])->name('shipments.data.trashed');
    Route::post('/', [ShipmentController::class, 'store'])->name('shipments.store');
    Route::get('/{shipment}/edit', [ShipmentController::class, 'edit'])->name('shipments.edit');
    Route::put('/{shipment}', [ShipmentController::class, 'update'])->name('shipments.update');
    Route::delete('/{shipment}', [ShipmentController::class, 'destroy'])->name('shipments.destroy');
    Route::post('/{id}/restore', [ShipmentController::class, 'restore'])->name('shipments.restore');
    Route::delete('/{id}/force', [ShipmentController::class, 'forceDelete'])->name('shipments.force');
    Route::get('/search', [ShipmentController::class, 'search'])->name('shipments.search');
    Route::post('/report', [ShipmentController::class, 'report'])->name('shipments.report');
    Route::get('/statistics', [ShipmentController::class, 'statistics'])->name('shipments.statistics');
    
    // Document routes
    Route::post('/{shipment}/documents', [ShipmentController::class, 'uploadDocuments'])
        ->name('shipments.documents.upload');
        
    Route::delete('/documents/{document}', [ShipmentController::class, 'removeDocument'])
        ->name('shipments.documents.destroy');
        
    Route::get('/documents/{document}/download', [ShipmentController::class, 'downloadDocument'])
        ->name('shipments.documents.download');
        
    Route::get('/{shipment}/documents', [ShipmentController::class, 'listDocuments'])
        ->name('shipments.documents.index');
});

// Requests Routes
Route::middleware(['auth'])->prefix('requests')->group(function () {
    Route::get('/', [RequestController::class, 'index'])->name('requests.index');
    Route::get('/data', [RequestController::class, 'data'])->name('requests.data');
    Route::get('/data/trashed', [RequestController::class, 'dataTrashed'])->name('requests.data.trashed');
    Route::post('/', [RequestController::class, 'store'])->name('requests.store');
    Route::get('/{request}/edit', [RequestController::class, 'edit'])->name('requests.edit');
    Route::put('/{request}', [RequestController::class, 'update'])->name('requests.update');
    Route::delete('/{request}', [RequestController::class, 'destroy'])->name('requests.destroy');
    Route::post('/{id}/restore', [RequestController::class, 'restore'])->name('requests.restore');
    Route::delete('/{id}/force', [RequestController::class, 'forceDelete'])->name('requests.force');
    Route::get('/search', [RequestController::class, 'search'])->name('requests.search');
    Route::post('/report', [RequestController::class, 'report'])->name('requests.report');
    Route::get('/statistics', [RequestController::class, 'statistics'])->name('requests.statistics');
    Route::get('/generate-code', [RequestController::class, 'generateCode'])->name('requests.generate-code');
    Route::get('/by-project/{projectId}', [RequestController::class, 'byProject'])->name('requests.by-project');
});
// Auth::routes();


// Rotas de Activos
// Rotas de Activos
// Rotas de Activos
// ========== ROTAS ESPECÍFICAS (DEVEM VIR ANTES) ==========


// ========== ROUTE RESOURCE (DEVEM VIR DEPOIS) ==========


// Rota principal
Route::get('assets', [AssetController::class, 'index'])->name('assets.index');

Route::prefix('assets')->name('assets.')->group(function () {
    // DataTables
    Route::get('datatable', [AssetController::class, 'datatable'])->name('datatable');
    
    // Estatísticas
    Route::get('stats', [AssetController::class, 'stats'])->name('stats');
    
    // Exportação
    Route::post('export', [AssetController::class, 'export'])->name('export');
    
    // Ações em massa
    Route::post('bulk-action', [AssetController::class, 'bulkAction'])->name('bulk-action');
    
    // Documentos
    Route::get('{asset}/documents', [AssetController::class, 'listDocuments'])->name('documents');
    Route::post('{asset}/documents', [AssetController::class, 'uploadDocuments'])->name('upload-documents');
    Route::delete('documents/{document}', [AssetController::class, 'removeDocument'])->name('remove-document');
    Route::get('documents/{document}/download', [AssetController::class, 'downloadDocument'])->name('download-document');
    
    // Administração
    Route::post('{id}/restore', [AssetController::class, 'restore'])->name('restore');
    Route::delete('{id}/force-delete', [AssetController::class, 'forceDelete'])->name('force-delete');
    
    // Ações rápidas
    Route::get('{asset}/quick-view', [AssetController::class, 'quickView'])->name('quick-view');
    Route::post('{asset}/assign', [AssetController::class, 'assign'])->name('assign');
    Route::post('{asset}/remove-assignment', [AssetController::class, 'removeAssignment'])->name('remove-assignment');
    Route::post('{asset}/process-status', [AssetController::class, 'updateProcessStatus'])->name('process-status');
    Route::post('{asset}/inoperational', [AssetController::class, 'markInoperational'])->name('inoperational');
    Route::post('{asset}/write-off', [AssetController::class, 'writeOff'])->name('write-off');
    
    // CRUD básico
    Route::get('create', [AssetController::class, 'create'])->name('create');
    Route::get('{asset}/edit', [AssetController::class, 'edit'])->name('edit');
    Route::get('{asset}', [AssetController::class, 'show'])->name('show');
    Route::post('/', [AssetController::class, 'store'])->name('store');
    Route::put('{asset}', [AssetController::class, 'update'])->name('update');
    Route::delete('{asset}', [AssetController::class, 'destroy'])->name('destroy');


    // Manutenções
Route::post('{asset}/maintenance', [AssetController::class, 'markMaintenance'])->name('maintenance');
Route::post('{asset}/complete-maintenance', [AssetController::class, 'completeMaintenance'])->name('complete-maintenance');
Route::get('{asset}/maintenances', [AssetController::class, 'listMaintenances'])->name('maintenances');
});

// Rotas específicas para DataTables e operações

// Rotas públicas de ativação
Route::get('/ativar/{token}', [ActivationController::class, 'showActivationForm'])
    ->name('activation.show');

Route::post('/ativar', [ActivationController::class, 'activate'])
    ->name('activation.complete');

Route::get('/ativacao-sucesso', [ActivationController::class, 'success'])
    ->name('activation.success')
    ->middleware('auth');

// Desativar o registro padrão do Laravel
Auth::routes(['register' => false]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Manutenções Routes
Route::middleware(['auth'])->prefix('maintenances')->group(function () {
    Route::get('/', [MaintenanceController::class, 'index'])->name('maintenances.index');
    Route::get('/data', [MaintenanceController::class, 'datatable'])->name('maintenances.datatable');
    Route::post('/export', [MaintenanceController::class, 'export'])->name('maintenances.export');
    Route::get('/stats', [MaintenanceController::class, 'stats'])->name('maintenances.stats');
    Route::post('/bulk-action', [MaintenanceController::class, 'bulkAction'])->name('maintenances.bulk-action');
    
    // Individual maintenance operations
    Route::get('/{maintenance}', [MaintenanceController::class, 'show'])->name('maintenances.show');
    Route::put('/{maintenance}', [MaintenanceController::class, 'update'])->name('maintenances.update');
    Route::delete('/{maintenance}', [MaintenanceController::class, 'destroy'])->name('maintenances.destroy');
    Route::post('/{maintenance}/status', [MaintenanceController::class, 'updateStatus'])->name('maintenances.update-status');
    Route::post('/{maintenance}/complete', [MaintenanceController::class, 'completeMaintenance'])->name('maintenances.complete');
});