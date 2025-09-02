<?php

use App\Enum\VisaStatus;
use App\Enum\VisaType;
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
        Schema::create('visas', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->string('sku_code');
            $table->foreignId('origin_country')->constrained('countries');
            $table->foreignId('destination_country')->constrained('countries');
            $table->tinyInteger('processing_time')->nullable();
            $table->string('application_form')->nullable();
            $table->integer('convenient_fee');
            $table->text('basic_info')->nullable();
            $table->text('depurture_requirements')->nullable();
            $table->text('destination_requirements')->nullable();
            $table->text('checklists')->nullable();
            $table->text('faq')->nullable();
            $table->tinyInteger('type')->default(VisaType::Tourist);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('action_id')->nullable()->constrained('users');
            $table->tinyInteger('status')->default(VisaStatus::Active);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visas');
    }
};
