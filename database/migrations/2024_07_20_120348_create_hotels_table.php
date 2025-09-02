<?php

use App\Enum\HotelStatus;
use App\Enum\HoteIsFeaturedStatus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->text('address')->nullable();
            $table->foreignId('country_id')->nullable()->constrained('countries');
            $table->foreignId('division_id')->nullable()->constrained('divisions');
            $table->foreignId('district_id')->nullable()->constrained('districts');
            $table->integer('zipcode')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->time('checkin_time')->nullable();
            $table->time('checkout_time')->nullable();
            $table->boolean('is_featured')->default(HoteIsFeaturedStatus::Active);
            $table->text('description')->nullable();
            $table->integer('type')->default(0);
            $table->string('thumbnail')->nullable();
            $table->json('images')->nullable();
            $table->text('google_map_iframe')->nullable();
            $table->tinyInteger('status')->default(HotelStatus::Active);
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
        Schema::dropIfExists('hotels');
    }
};
