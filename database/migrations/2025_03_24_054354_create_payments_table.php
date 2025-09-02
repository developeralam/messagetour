<?php

use App\Enum\WalletPaymentStatus;
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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents');
            $table->foreignId('payment_gateway_id')->constrained('payment_gateways');
            $table->string('depositBank_name');
            $table->foreignId('receive_bank_id')->constrained('banks');
            $table->string('branch_name')->nullable();
            $table->integer('amount');
            $table->date('desposit_date');
            $table->string('image')->nullable();
            $table->string('reference')->nullable();
            $table->tinyInteger('status')->default(WalletPaymentStatus::Pending);
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
        Schema::dropIfExists('payments');
    }
};
