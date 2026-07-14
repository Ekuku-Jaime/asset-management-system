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
        Schema::table('assets', function (Blueprint $table) {
            // Remover foreign keys primeiro
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['invoice_id']);
            $table->dropForeign(['shipment_id']);
            $table->dropForeign(['company_id']);
            $table->dropForeign(['project_id']);
            
            // Remover índices
            // $table->dropIndex(['supplier_id']);
            // $table->dropIndex(['invoice_id']);
            // $table->dropIndex(['shipment_id']);
            // $table->dropIndex(['company_id']);
            // $table->dropIndex(['project_id']);
            
            // Remover colunas
            $table->dropColumn([
                'supplier_id',
                'invoice_id',
                'shipment_id',
                'company_id',
                'location','department','process_status','project_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // Adicionar colunas de volta
            $table->unsignedBigInteger('supplier_id')->nullable()->after('request_id');
            $table->unsignedBigInteger('invoice_id')->nullable()->after('supplier_id');
            $table->unsignedBigInteger('shipment_id')->nullable()->after('invoice_id');
            $table->unsignedBigInteger('company_id')->nullable()->after('invoice_id');
            
            // Recriar foreign keys
            $table->foreign('supplier_id')
                  ->references('id')
                  ->on('suppliers')
                  ->onDelete('set null');
                  
            $table->foreign('invoice_id')
                  ->references('id')
                  ->on('invoices')
                  ->onDelete('set null');
                  
            $table->foreign('shipment_id')
                  ->references('id')
                  ->on('shipments')
                  ->onDelete('set null');
                
               $table->foreign('company_id')
                  ->references('id')
                  ->on('companies')
                  ->onDelete('set null');
                  
            // Recriar índices
            $table->index('supplier_id');
            $table->index('invoice_id');
            $table->index('shipment_id');
             $table->index('company_id');
        });
    }
};