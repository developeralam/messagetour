<?php

use App\Enum\AmountType;
use App\Enum\CommissionStatus;
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
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->double('amount')->default(0);
            $table->tinyInteger('commission_role');
            $table->tinyInteger('product_type');
            $table->tinyInteger('amount_type')->default(AmountType::Percent);
            $table->tinyInteger('status')->default(CommissionStatus::Active);
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
        Schema::dropIfExists('commissions');
    }
};
