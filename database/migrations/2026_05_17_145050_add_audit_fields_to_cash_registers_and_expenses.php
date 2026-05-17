<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->foreignId('closed_by')
                ->nullable()
                ->after('user_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->decimal('difference_amount', 10, 2)
                ->nullable()
                ->after('actual_amount');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->timestamp('cancelled_at')
                ->nullable()
                ->after('cancellation_reason');
        });
    }

    public function down(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->dropForeign(['closed_by']);
            $table->dropColumn(['closed_by', 'difference_amount']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('cancelled_at');
        });
    }
};