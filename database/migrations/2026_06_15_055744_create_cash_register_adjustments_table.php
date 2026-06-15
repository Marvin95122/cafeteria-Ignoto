<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_register_adjustments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cash_register_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('field_name');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->text('reason');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_register_adjustments');
    }
};