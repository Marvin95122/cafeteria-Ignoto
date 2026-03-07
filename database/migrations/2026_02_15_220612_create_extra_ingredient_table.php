<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extra_ingredient', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extra_id')->constrained()->onDelete('cascade');
            $table->foreignId('ingredient_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 10, 2); // Cuánto gasta
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extra_ingredient');
    }
};