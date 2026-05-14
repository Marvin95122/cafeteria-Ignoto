<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Usamos unique() para que no se repitan números de teléfono
            $table->string('phone')->unique(); 
            // Inician con 0 Puntos Ignoto
            $table->integer('points')->default(0); 
            $table->timestamps();
        });

        // Opcional: Agregamos la relación a la tabla orders para saber qué cliente hizo qué compra
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
        });
        Schema::dropIfExists('customers');
    }
};