<?php

use App\Enum\BankStatus;
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
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ac_no');
            $table->string('branch')->nullable();
            $table->string('address');
            $table->string('swift_code');
            $table->integer('routing_no');
            $table->foreignId('country_id')->constrained('countries');
            $table->tinyInteger('status')->default(BankStatus::Active);
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
        Schema::dropIfExists('banks');
    }
};
