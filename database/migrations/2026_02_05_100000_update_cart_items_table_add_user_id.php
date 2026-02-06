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
        Schema::table('cart_items', function (Blueprint $table) {
            // Drop the existing unique constraint
            $table->dropUnique(['session_id', 'product_id']);
            
            // Add user_id column after id
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            
            // Create new unique constraint that allows multiple sessions but groups by user or session
            $table->unique(['user_id', 'product_id'], 'unique_user_product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            // Drop the new constraints
            $table->dropUnique('unique_user_product');
            
            // Re-create the old constraint
            $table->unique(['session_id', 'product_id']);
            
            // Remove user_id column
            $table->dropForeignKey(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
