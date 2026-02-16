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
        Schema::create('shippings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('courier')->comment('Courier code: jne, pos, tiki, etc');
            $table->string('courier_name')->nullable()->comment('Courier display name');
            $table->string('service')->comment('Service code: REG, OKE, ECO, etc');
            $table->string('service_description')->nullable()->comment('Service description from API');
            $table->unsignedBigInteger('cost')->comment('Shipping cost in IDR');
            $table->unsignedInteger('weight')->comment('Total weight in grams');
            $table->unsignedInteger('origin_city_id')->comment('Origin city ID from Rajaongkir');
            $table->unsignedInteger('destination_city_id')->comment('Destination city ID from Rajaongkir');
            $table->string('tracking_number')->unique()->nullable()->comment('Courier tracking number');
            $table->enum('status', ['pending', 'picked_up', 'in_transit', 'out_for_delivery', 'delivered', 'failed', 'returned'])->default('pending');
            $table->dateTime('estimated_delivery')->nullable();
            $table->dateTime('actual_delivery')->nullable();
            $table->text('notes')->nullable()->comment('Additional shipping notes');
            $table->timestamps();

            $table->index('order_id');
            $table->index('status');
            $table->index('tracking_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shippings');
    }
};
