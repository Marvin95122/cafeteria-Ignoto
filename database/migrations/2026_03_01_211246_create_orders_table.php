<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // Empleado/Admin/Gerente que registró la venta
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            $table->string('customer_name')->nullable(); // Nombre del cliente
            $table->decimal('total', 10, 2); // Total cobrado
            $table->string('payment_method')->default('efectivo'); // efectivo, tarjeta, transferencia
            $table->string('status')->default('completado'); // completado, cancelado
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};