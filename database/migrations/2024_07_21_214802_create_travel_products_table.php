<?php

use App\Enum\TravelProductStatus;
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
        Schema::create('travel_products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->string('sku');
            $table->string('brand');
            $table->text('description')->nullable();
            $table->integer('regular_price');
            $table->integer('offer_price')->nullable();
            $table->string('thumbnail');
            $table->integer('stock');
            $table->boolean('is_featured')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('action_id')->nullable()->constrained('users');
            $table->tinyInteger('status')->default(TravelProductStatus::Active);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_products');
    }
};
