<?php

use App\Enum\RentalType;
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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->integer('model_year');
            $table->string('image')->nullable();
            $table->integer('seating_capacity');
            $table->integer('car_cc');
            $table->tinyInteger('car_type');
            $table->foreignId('country_id')->constrained('countries');
            $table->foreignId('division_id')->constrained('divisions');
            $table->foreignId('district_id')->nullable()->constrained('districts');
            $table->string('color');
            $table->text('include_with_pricing')->nullable();
            $table->text('exclude_with_pricing')->nullable();
            $table->string('area_limitation')->nullable();
            $table->string('max_distance')->nullable();
            $table->boolean('ac_facility')->nullable();
            $table->decimal('price_2_hours', 10, 2);
            $table->decimal('price_4_hours', 10, 2);
            $table->decimal('price_half_day', 10, 2);
            $table->decimal('price_day', 10, 2);
            $table->decimal('price_per_day', 10, 2);
            $table->string('service_type')->nullable();
            $table->integer('extra_time_cost_by_hour')->nullable();
            $table->integer('extra_time_cost')->nullable();
            $table->tinyInteger('with_driver')->default(RentalType::WithDriver);
            $table->tinyInteger('status');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('action_id')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
