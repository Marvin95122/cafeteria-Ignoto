<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2); // Precio del producto en ese momento
            $table->decimal('subtotal', 10, 2); // Cantidad * Precio + Extras
            
            // Aquí guardaremos los extras seleccionados en formato JSON 
            // Ej: [{"id":1, "name":"Leche extra", "price":15}]
            $table->json('extras')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};