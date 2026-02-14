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
            $table->enum('maintenance_type', ['preventiva', 'corretiva', 'preditiva']);
            $table->text('description');
            $table->enum('status', ['agendada', 'em_andamento', 'concluida', 'cancelada'])->default('agendada');
            $table->dateTime('scheduled_date');
            $table->dateTime('completed_date')->nullable();
            $table->integer('estimated_duration')->nullable()->comment('Dias estimados');
            $table->integer('actual_duration')->nullable()->comment('Dias reais');
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('maintenance_provider')->nullable();
            $table->string('technician_name')->nullable();
            $table->enum('result', ['concluida', 'pendente', 'cancelada'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('asset_maintenances');
    }
};