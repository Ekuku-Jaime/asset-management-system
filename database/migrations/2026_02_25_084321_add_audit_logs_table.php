<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            
            // Quem
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->string('user_role')->nullable();
            
            // Quando
            $table->timestamp('performed_at')->useCurrent();
            
            // O quê
            $table->string('event_type'); // CREATE, UPDATE, DELETE, RESTORE, LOGIN, LOGOUT, etc.
            $table->string('auditable_type'); // Model class
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->string('description');
            
            // De onde
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('browser')->nullable();
            $table->string('platform')->nullable();
            $table->string('device')->nullable();
            
            // O que mudou (dados)
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            
            // Metadados adicionais
            $table->string('request_method')->nullable();
            $table->string('request_url')->nullable();
            $table->string('session_id')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('user_id');
            $table->index('event_type');
            $table->index('performed_at');
            $table->index('ip_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};