<?php

use App\Enum\OrderStatus;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->foreignId('country_id')->nullable()->constrained('countries');
            $table->foreignId('division_id')->nullable()->constrained('divisions');
            $table->foreignId('district_id')->nullable()->constrained('districts');
            $table->integer('zipcode')->nullable();
            $table->foreignId('coupon_id')->nullable()->constrained('coupons');
            $table->integer('coupon_amount')->nullable();
            $table->integer('subtotal');
            $table->integer('delivery_charge')->nullable();
            $table->tinyInteger('shipping_method')->nullable();
            $table->integer('shipping_charge')->nullable();
            $table->integer('total_amount');
            $table->string('tran_id');
            $table->nullableMorphs('sourceable');
            $table->tinyInteger('status')->default(OrderStatus::Pending);
            $table->foreignId('payment_gateway_id')->nullable()->constrained('payment_gateways');
            $table->tinyInteger('payment_status')->nullable();
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
        Schema::dropIfExists('orders');
    }
};
