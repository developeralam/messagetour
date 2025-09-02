<?php

use App\Enum\TourStatus;
use App\Enum\TourType;
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
        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->text('location');
            $table->date('start_date');
            $table->date('end_date');
            $table->tinyInteger('member_range');
            $table->tinyInteger('minimum_passenger');
            $table->text('description')->nullable();
            $table->foreignId('country_id')->nullable()->constrained('countries');
            $table->foreignId('division_id')->nullable()->constrained('divisions');
            $table->foreignId('district_id')->nullable()->constrained('districts');
            $table->boolean('is_featured')->default(true)->comment('0=disabled, 1=enabled');
            $table->tinyInteger('type')->default(TourType::Tour);
            $table->integer('regular_price');
            $table->integer('offer_price')->nullable();
            $table->date('validity');
            $table->string('thumbnail')->nullable();
            $table->json('images')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('action_id')->nullable()->constrained('users');
            $table->tinyInteger('status')->default(TourStatus::Active);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};
