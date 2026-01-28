<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->enum('status', ['incompleto', 'completo'])
                  ->default('incompleto')
                  ->after('date');
        });
    }

    public function down()
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};