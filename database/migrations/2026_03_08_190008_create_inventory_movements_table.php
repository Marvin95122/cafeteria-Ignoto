<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            
            // A que ingrediente/materia prima le estamos moviendo el stock
            $table->foreignId('ingredient_id')->constrained()->onDelete('cascade');
            
            // Quien (Gerente/Admin) registró este movimiento
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Tipo de movimiento: 'entrada' (sumar stock) o 'merma' (restar stock por accidente)
            $table->enum('type', ['entrada', 'merma']);
            
            // Cuanto estamos sumando o restando (1000 gramos, 2 litros)
            $table->decimal('quantity', 10, 2);
            
            // Razon del movimiento ("Compra de algun lado", "Se derramo medio litro por accidente")
            $table->string('reason');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};