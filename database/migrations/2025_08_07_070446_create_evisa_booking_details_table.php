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
        Schema::create('evisa_booking_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visa_booking_id')->constrained('visa_bookings');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->date('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('nationality')->nullable();
            $table->foreignId('birth_country')->constrained('countries');
            $table->string('nid')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('passport_number')->nullable();
            $table->date('passport_issue_date')->nullable();
            $table->date('passport_exp_date')->nullable();
            $table->string('pa_house_no')->nullable();
            $table->string('pa_address')->nullable();
            $table->string('pa_zip_code')->nullable();
            $table->foreignId('pa_country')->constrained('countries');
            $table->foreignId('pa_division')->constrained('divisions');
            $table->foreignId('pa_district')->constrained('districts');
            $table->string('des_address')->nullable();
            $table->string('des_phone')->nullable();
            $table->string('des_post_code')->nullable();
            $table->date('arr_date')->nullable();
            $table->string('entry_port')->nullable();
            $table->string('purpose')->nullable();
            $table->string('med_appointment_doc')->nullable();
            $table->string('med_previous_doc')->nullable();
            $table->string('busi_invitation')->nullable();
            $table->string('busi_company_doc')->nullable();
            $table->string('busi_other_doc')->nullable();
            $table->string('fam_invitation')->nullable();
            $table->string('fam_rel_with_invitor')->nullable();
            $table->string('fam_invitor_passport')->nullable();
            $table->string('fam_invitor_other_doc')->nullable();
            $table->string('uni_admission_letter')->nullable();
            $table->string('uni_immi_approval')->nullable();
            $table->string('uni_other_doc')->nullable();
            $table->string('work_permit')->nullable();
            $table->string('work_recruiter')->nullable();
            $table->string('work_other_doc')->nullable();
            $table->string('passport_doc')->nullable();
            $table->string('bank_state_doc')->nullable();
            $table->string('bank_solvency_doc')->nullable();
            $table->string('busi_trade_license')->nullable();
            $table->string('busi_office_pad')->nullable();
            $table->string('busi_visiting_card')->nullable();
            $table->string('sholder_noc')->nullable();
            $table->string('sholder_visiting_card')->nullable();
            $table->string('sholder_pay_slip')->nullable();
            $table->string('stu_id_card')->nullable();
            $table->string('stu_last_certificate')->nullable();
            $table->string('govt_prof_doc')->nullable();
            $table->string('retired_doc')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evisa_booking_details');
    }
};
