<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\CompanyController;

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');
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
    Route::get('/', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/data', [ProjectController::class, 'data'])->name('projects.data');
    Route::get('/data/trashed', [ProjectController::class, 'dataTrashed'])->name('projects.data.trashed');
    Route::post('/', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::post('/{id}/restore', [ProjectController::class, 'restore'])->name('projects.restore');
    Route::delete('/{id}/force', [ProjectController::class, 'forceDelete'])->name('projects.force');
    Route::get('/search', [ProjectController::class, 'search'])->name('projects.search');
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
// Auth::routes();
use App\Http\Controllers\Auth\ActivationController;

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
