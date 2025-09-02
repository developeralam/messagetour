<?php

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
        Schema::create('other_transactions', function (Blueprint $table) {
            $table->id();
            $table->date('payment_date');
            $table->foreignId('receive_from')->constrained('chart_of_accounts');
            $table->foreignId('post_to')->constrained('chart_of_accounts');
            $table->decimal('amount', 15, 2);
            $table->text('note');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('other_transactions');
    }
};
