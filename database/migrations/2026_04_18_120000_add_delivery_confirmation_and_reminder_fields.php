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
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('delivered_confirmed_at')->nullable()->after('cancel_reason');
            $table->string('delivered_confirmed_by', 10)->nullable()->after('delivered_confirmed_at'); // user|admin
            $table->index('delivered_confirmed_at');
        });

        Schema::table('shippings', function (Blueprint $table) {
            $table->timestamp('reminder_last_sent_at')->nullable()->after('actual_delivery');
            $table->unsignedInteger('reminder_sent_count')->default(0)->after('reminder_last_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['delivered_confirmed_at']);
            $table->dropColumn(['delivered_confirmed_at', 'delivered_confirmed_by']);
        });

        Schema::table('shippings', function (Blueprint $table) {
            $table->dropColumn(['reminder_last_sent_at', 'reminder_sent_count']);
        });
    }
};
