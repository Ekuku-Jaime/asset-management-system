<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('asset_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
            $table->string('filename');
            $table->string('original_name');
            $table->string('mime_type');
            $table->integer('size');
            $table->string('path');
            $table->enum('document_type', [
                'manual', 'garantia', 'fatura', 'comprovativo', 
                'certificado', 'outro'
            ])->default('outro');
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index('asset_id');
            $table->index('document_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('asset_documents');
    }
};