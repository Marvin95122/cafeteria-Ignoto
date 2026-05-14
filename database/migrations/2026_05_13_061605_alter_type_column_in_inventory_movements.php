<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminamos la restricción estricta convirtiendo la columna a texto libre
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->string('type')->change();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            // Si nos arrepentimos, regresa a ser estricto
            $table->enum('type', ['entrada', 'merma'])->change();
        });
    }
};