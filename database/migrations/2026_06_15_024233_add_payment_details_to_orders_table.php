<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'cash_received')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->decimal('cash_received', 10, 2)->nullable();
            });
        }

        if (!Schema::hasColumn('orders', 'cash_change')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->decimal('cash_change', 10, 2)->nullable();
            });
        }

        if (!Schema::hasColumn('orders', 'points_earned')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->integer('points_earned')->default(0);
            });
        }

        if (!Schema::hasColumn('orders', 'points_used')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->integer('points_used')->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('orders', 'cash_received')) {
                $columns[] = 'cash_received';
            }

            if (Schema::hasColumn('orders', 'cash_change')) {
                $columns[] = 'cash_change';
            }

            if (Schema::hasColumn('orders', 'points_earned')) {
                $columns[] = 'points_earned';
            }

            if (Schema::hasColumn('orders', 'points_used')) {
                $columns[] = 'points_used';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};