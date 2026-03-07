<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla de Materia Prima
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unit');
            $table->decimal('current_quantity', 10, 2)->default(0); // Cuánto hay en almacén
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // 2. Tabla Pivote (La Receta)
        Schema::create('ingredient_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('ingredient_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 10, 2); // Cuánto usa este producto
            $table->timestamps();
        });

        // 3. Modificar Tabla Productos (Agregar el switch)
        Schema::table('products', function (Blueprint $table) {
            // Si es true, calculamos stock con ingredientes. Si es false, usamos el número fijo.
            $table->boolean('use_dynamic_stock')->default(false)->after('stock');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('use_dynamic_stock');
        });
        Schema::dropIfExists('ingredient_product');
        Schema::dropIfExists('ingredients');
    }
};