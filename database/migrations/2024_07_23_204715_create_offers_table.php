<?php

use App\Enum\OfferType;
use App\Enum\OfferStatus;
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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->tinyInteger('type')->default(OfferType::Coupon);
            $table->string('slug');
            $table->string('thumbnail');
            $table->text('description')->nullable();
            $table->foreignId('coupon_id')->nullable()->constrained('coupons');
            $table->string('link')->nullable();
            $table->text('applicable_users')->nullable();
            $table->text('avail_this_offer_step_1')->nullable();
            $table->text('avail_this_offer_step_2')->nullable();
            $table->text('avail_this_offer_step_3')->nullable();
            $table->foreignId('action_id')->nullable()->constrained('users');
            $table->tinyInteger('status')->default(OfferStatus::Active);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
