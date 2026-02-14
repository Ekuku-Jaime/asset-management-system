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
        Schema::table('projects', function (Blueprint $table) {
            $table->string('code')->unique()->after('id');
            $table->text('description')->nullable()->after('name');
            $table->date('start_date')->nullable()->after('description');
            $table->date('end_date')->nullable()->after('start_date');
            $table->enum('status', ['ativo', 'concluido', 'suspenso', 'cancelado'])->default('ativo')->after('end_date');
            $table->decimal('total_value', 15, 2)->default(0)->after('status');
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['code', 'description', 'start_date', 'end_date', 'status', 'total_value']);
           
        });
    }
};