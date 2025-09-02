<?php

use App\Enum\CorporateQueryStatus;
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
        Schema::create('corporate_queries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('destination_country')->constrained('countries');
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->text('group_size');
            $table->string('travel_date');
            $table->string('program');
            $table->tinyInteger('hotel_type');
            $table->tinyInteger('hotel_room_type')->nullable();
            $table->string('meals');
            $table->text('meals_choices')->nullable();
            $table->text('recommend_places')->nullable();
            $table->text('activities')->nullable();
            $table->boolean('visa_service')->nullable();
            $table->boolean('air_ticket')->nullable();
            $table->boolean('tour_guide')->nullable();
            $table->tinyInteger('status')->default(CorporateQueryStatus::Pending);
            $table->foreignId('action_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('corporate_queries');
    }
};
