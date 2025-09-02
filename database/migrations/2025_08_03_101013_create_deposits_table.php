<?php

use App\Enum\DepositStatus;
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
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents');
            $table->integer('amount');
            $table->string('trx_id')->unique()->nullable();
            $table->tinyInteger('payment_type')->nullable();
            $table->string('deposit_form')->nullable();
            $table->foreignId('deposit_to')->nullable()->constrained('banks');
            $table->string('branch')->nullable();
            $table->string('payment_slip')->nullable();
            $table->date('deposit_date');
            $table->tinyInteger('status')->default(DepositStatus::Pending);
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
        Schema::dropIfExists('deposits');
    }
};
