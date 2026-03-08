<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            
            // El usuario (Gerente/Admin) que abrió o cerró la caja
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Monto con el que se inicia el día para dar cambio
            $table->decimal('opening_amount', 10, 2)->default(0); 
            
            // Total calculado por el sistema (Ventas - Gastos)
            $table->decimal('expected_amount', 10, 2)->nullable(); 
            
            // Lo que el gerente cuenta físicamente en el cajón al cerrar
            $table->decimal('actual_amount', 10, 2)->nullable(); 
            
            // Notas (Ej: "Faltaron 10 pesos" o "Caja cuadrada perfecto")
            $table->text('notes')->nullable(); 

            // Estado: 'abierta' o 'cerrada'
            $table->string('status')->default('abierta'); 
            
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_registers');
    }
};