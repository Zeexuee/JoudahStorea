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
        if (!Schema::hasColumn('orders', 'delivered_confirmed_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('delivered_confirmed_at')->nullable()->after('cancel_reason');
            });
        }

        if (!Schema::hasColumn('orders', 'delivered_confirmed_by')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('delivered_confirmed_by', 10)->nullable()->after('delivered_confirmed_at'); // user|admin
            });
        }

        if (!Schema::hasColumn('shippings', 'reminder_last_sent_at')) {
            Schema::table('shippings', function (Blueprint $table) {
                $table->timestamp('reminder_last_sent_at')->nullable()->after('actual_delivery');
            });
        }

        if (!Schema::hasColumn('shippings', 'reminder_sent_count')) {
            Schema::table('shippings', function (Blueprint $table) {
                $table->unsignedInteger('reminder_sent_count')->default(0)->after('reminder_last_sent_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('orders', 'delivered_confirmed_at') || Schema::hasColumn('orders', 'delivered_confirmed_by')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'delivered_confirmed_at')) {
                    $table->dropColumn('delivered_confirmed_at');
                }

                if (Schema::hasColumn('orders', 'delivered_confirmed_by')) {
                    $table->dropColumn('delivered_confirmed_by');
                }
            });
        }

        if (Schema::hasColumn('shippings', 'reminder_last_sent_at') || Schema::hasColumn('shippings', 'reminder_sent_count')) {
            Schema::table('shippings', function (Blueprint $table) {
                if (Schema::hasColumn('shippings', 'reminder_last_sent_at')) {
                    $table->dropColumn('reminder_last_sent_at');
                }

                if (Schema::hasColumn('shippings', 'reminder_sent_count')) {
                    $table->dropColumn('reminder_sent_count');
                }
            });
        }
    }
};
