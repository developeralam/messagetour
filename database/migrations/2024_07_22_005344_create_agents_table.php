<?php

use App\Enum\AgentStatus;
use App\Enum\AgentType;
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
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->tinyInteger('agent_type');
            $table->string('agent_image')->nullable();
            $table->string('business_name')->nullable();
            $table->string('business_phone')->nullable();
            $table->string('business_email')->nullable();
            $table->string('business_logo')->nullable();
            $table->string('propiter_nid')->nullable();
            $table->string('propiter_etin_no')->nullable();
            $table->string('trade_licence')->nullable();
            $table->string('business_address')->nullable();
            $table->string('primary_contact_address')->nullable();
            $table->string('secondary_contact_address')->nullable();
            $table->integer('zipcode')->nullable();
            $table->date('validity')->nullable();
            $table->foreignId('country_id')->nullable()->constrained('countries');
            $table->foreignId('division_id')->nullable()->constrained('divisions');
            $table->foreignId('district_id')->nullable()->constrained('districts');
            $table->integer('wallet')->default(0);
            $table->tinyInteger('status')->default(AgentStatus::Pending);
            $table->foreignId('action_by')->nullable()->constrained('users');
            $table->integer('credit_limit')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
