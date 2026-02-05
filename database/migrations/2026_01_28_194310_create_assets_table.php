<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            
            // Informações básicas
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('serial_number')->nullable()->unique();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('barcode')->nullable()->unique();
            
            // Categorização
            $table->enum('category', [
                'hardware', 'software', 'equipamento', 'mobiliario', 
                'veiculo', 'outro'
            ])->default('hardware');
            
            // Estado do ativo
            $table->enum('asset_status', [
                'disponivel', 'atribuido', 'manutencao', 
                'inoperacional', 'abatido'
            ])->default('disponivel');
            
            // Estado do processo
            $table->enum('process_status', [
                'incompleto', 'completo'
            ])->default('incompleto');
            
            // Informações financeiras
            $table->decimal('base_value', 15, 2)->default(0);
            $table->decimal('iva_value', 15, 2)->default(0);
            $table->decimal('total_value', 15, 2)->default(0);
            
            // Relacionamentos
            $table->foreignId('request_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('shipment_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
            
            // Informações de localização
            $table->string('location')->nullable(); // Província
            $table->string('department')->nullable();
            
            // Datas importantes
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->date('last_maintenance')->nullable();
            $table->date('next_maintenance')->nullable();
            $table->date('assignment_date')->nullable();
            
            // Soft deletes
            $table->softDeletes();
            $table->timestamps();
            
            // Índices
            $table->index('code');
            $table->index('asset_status');
            $table->index('process_status');
            $table->index('category');
            $table->index('barcode');
        });
    }

    public function down()
    {
        Schema::dropIfExists('assets');
    }
};