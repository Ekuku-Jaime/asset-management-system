@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-lg">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <div class="avatar-initial bg-success rounded-circle text-white" style="width: 80px; height: 80px; line-height: 80px; font-size: 32px;">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>
                        <h2 class="mb-3">Conta Ativada com Sucesso!</h2>
                        <p class="text-muted mb-4">
                            Bem-vindo(a) ao Asset Management System, {{ auth()->user()->name }}!
                            A sua conta foi ativada e está pronta para usar.
                        </p>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="card border">
                                <div class="card-body">
                                    <i class="fas fa-user fa-2x text-primary mb-3"></i>
                                    <h5>Perfil</h5>
                                    <p class="text-muted small">Complete o seu perfil de utilizador</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-body">
                                    <i class="fas fa-dashboard fa-2x text-primary mb-3"></i>
                                    <h5>Dashboard</h5>
                                    <p class="text-muted small">Explore o sistema</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-rocket me-2"></i>Ir para o Dashboard
                        </a>
                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary">
                            <i class="fas fa-user-edit me-2"></i>Editar Perfil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection