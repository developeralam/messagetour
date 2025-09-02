<?php

use App\Enum\HotelRoomType;
use App\Enum\HotelRoomStatus;
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
        Schema::create('hotel_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels');
            $table->string('name');
            $table->string('room_no');
            $table->string('slug');
            $table->tinyInteger('type')->default(HotelRoomType::Economy);
            $table->string('room_size')->nullable();
            $table->tinyInteger('max_occupancy')->default(1);
            $table->unsignedInteger('regular_price');
            $table->unsignedInteger('offer_price')->nullable();
            $table->string('thumbnail');
            $table->json('images')->nullable();
            $table->tinyInteger('status')->default(HotelRoomStatus::Available);
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
        Schema::dropIfExists('hotel_rooms');
    }
};
