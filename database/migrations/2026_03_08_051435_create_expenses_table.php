<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            
            // Quién registró el gasto
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // A qué corte de caja pertenece este gasto (para restarlo)
            $table->foreignId('cash_register_id')->constrained()->onDelete('cascade');
            
            // Descripción (Ej: "Caguamas", "Pago de gas")
            $table->string('description');
            
            // Categoría opcional (Insumos, Servicios, Otros)
            $table->string('category')->default('Otros');
            
            // Cuánto se sacó de la caja
            $table->decimal('amount', 10, 2); 
            
            // Foto del ticket de compra (opcional para comprobar)
            $table->string('receipt_image')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};