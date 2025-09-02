<?php

use App\Enum\WithdrawStatus;
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
        Schema::create('withdraws', function (Blueprint $table) {
            $table->id();
            $table->foreignId('withdraw_method_id')->constrained('withdraw_methods');
            $table->integer('amount');
            $table->string('description');
            $table->string('trx_id')->nullable();
            $table->foreignId('agent_id')->constrained('agents');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->tinyInteger('status')->default(WithdrawStatus::Pending);
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
        Schema::dropIfExists('withdraws');
    }
};
