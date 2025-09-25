<?php

use App\Enum\TransactionStatus;
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
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->foreignId('agent_id')->nullable()->constrained('agents');
            $table->foreignId('account_id')->constrained('chart_of_accounts');
            $table->decimal('amount', 15, 2);
            $table->string('reference')->nullable();
            $table->text('remarks')->nullable();
            $table->tinyInteger('status')->default(TransactionStatus::PENDING);
            $table->foreignId('created_by')->nullable()->constrained('users');
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
        Schema::dropIfExists('incomes');
    }
};
