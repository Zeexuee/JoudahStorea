<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'discount_starts_at')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dateTime('discount_starts_at')->nullable()->after('discount_percent');
            });
        }

        if (!Schema::hasColumn('products', 'discount_ends_at')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dateTime('discount_ends_at')->nullable()->after('discount_starts_at');
            });
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $columns = [];

            foreach (['discount_starts_at', 'discount_ends_at'] as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $columns[] = $column;
                }
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};