<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('asset_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
            $table->date('maintenance_date');
            $table->date('return_date')->nullable();
            $table->date('expected_return')->nullable();
            $table->decimal('cost', 15, 2)->nullable();
            $table->text('description');
            $table->string('provider')->nullable();
            $table->enum('status', [
                'agendado', 'em_andamento', 'completado', 
                'cancelado', 'inoperacional', 'abatido'
            ])->default('agendado');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('asset_id');
            $table->index('maintenance_date');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('asset_maintenances');
    }
};