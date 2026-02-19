<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Adicionar novas colunas
            $table->unsignedBigInteger('shipment_id')->nullable()->after('project_id');
            $table->unsignedBigInteger('invoice_id')->nullable()->after('shipment_id');
            $table->unsignedBigInteger('supplier_id')->nullable()->after('invoice_id');
            $table->string('process_status')->default('incompleto')->after('supplier_id');
            $table->text('incomplete_reason')->nullable()->after('process_status');
            
            // Adicionar foreign keys
            $table->foreign('shipment_id')
                  ->references('id')
                  ->on('shipments')
                  ->onDelete('set null');
                  
            $table->foreign('invoice_id')
                  ->references('id')
                  ->on('invoices')
                  ->onDelete('set null');
                  
            $table->foreign('supplier_id')
                  ->references('id')
                  ->on('suppliers')
                  ->onDelete('set null');
                  
            // Índices para melhor performance
            $table->index('shipment_id');
            $table->index('invoice_id');
            $table->index('supplier_id');
            $table->index('process_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Remover foreign keys
            $table->dropForeign(['shipment_id']);
            $table->dropForeign(['invoice_id']);
            $table->dropForeign(['supplier_id']);
            
            // Remover índices
            $table->dropIndex(['shipment_id']);
            $table->dropIndex(['invoice_id']);
            $table->dropIndex(['supplier_id']);
            $table->dropIndex(['process_status']);
            
            // Remover colunas
            $table->dropColumn([
                'shipment_id',
                'invoice_id', 
                'supplier_id',
                'process_status',
                'incomplete_reason'
            ]);
        });
    }
};