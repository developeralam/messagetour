<?php

use App\Enum\GroupFlightStatus;
use App\Enum\GroupFlightType;
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
        Schema::create('group_flights', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->string('thumbnail');
            $table->text('description')->nullable();
            $table->tinyInteger('type')->default(GroupFlightType::Regular);
            $table->string('journey_route');
            $table->string('journey_transit');
            $table->string('return_route');
            $table->string('return_transit');
            $table->date('journey_date');
            $table->date('return_date');
            $table->string('airline_name');
            $table->string('airline_code');
            $table->string('baggage_weight');
            $table->boolean('is_food')->default(true);
            $table->integer('available_seat');
            $table->tinyInteger('status')->default(GroupFlightStatus::Active);
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
        Schema::dropIfExists('group_flights');
    }
};
