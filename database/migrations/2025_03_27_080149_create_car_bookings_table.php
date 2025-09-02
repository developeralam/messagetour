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
        Schema::create('car_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('car_id')->constrained('cars')->cascadeOnDelete();
            $table->foreignId('pickup_country_id')->constrained('countries')->cascadeOnDelete();
            $table->foreignId('pickup_division_id')->nullable()->constrained('divisions')->cascadeOnDelete();
            $table->foreignId('pickup_district_id')->nullable()->constrained('districts')->cascadeOnDelete();
            $table->foreignId('dropoff_district_id')->constrained('districts')->cascadeOnDelete();
            $table->dateTime('pickup_datetime');
            $table->dateTime('return_datetime');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_bookings');
    }
};
