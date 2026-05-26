<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('comments', 'rating')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->unsignedTinyInteger('rating')->nullable()->after('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('comments', 'rating')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->dropColumn('rating');
            });
        }
    }
};