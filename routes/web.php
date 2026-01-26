<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SupplierController;

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
