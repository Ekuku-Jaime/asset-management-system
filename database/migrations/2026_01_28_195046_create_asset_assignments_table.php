<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->date('assignment_date');
            $table->date('release_date')->nullable();
            $table->enum('status', ['atribuido', 'liberado', 'transferido'])->default('atribuido');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('asset_id');
            $table->index('employee_id');
            $table->index('assignment_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('asset_assignments');
    }
};