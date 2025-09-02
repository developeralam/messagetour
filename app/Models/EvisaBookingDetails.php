<?php

namespace App\Models;

use App\Models\Country;
use App\Enum\GenderType;
use App\Models\District;
use App\Models\Division;
use App\Enum\VisaPurpose;
use App\Models\VisaBooking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvisaBookingDetails extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'visa_booking_id',
        'first_name',
        'last_name',
        'dob',
        'gender',
        'nationality',
        'birth_country',
        'nid',
        'email',
        'phone',
        'passport_number',
        'passport_issue_date',
        'passport_exp_date',
        'pa_house_no',
        'pa_address',
        'pa_zip_code',
        'pa_country',
        'pa_division',
        'pa_district',
        'des_address',
        'des_phone',
        'des_post_code',
        'arr_date',
        'entry_port',
        'purpose',
        'med_appointment_doc',
        'med_previous_doc',
        'busi_invitation',
        'busi_company_doc',
        'busi_other_doc',
        'fam_invitation',
        'fam_rel_with_invitor',
        'fam_invitor_passport',
        'fam_invitor_other_doc',
        'uni_admission_letter',
        'uni_immi_approval',
        'uni_other_doc',
        'work_permit',
        'work_recruiter',
        'work_other_doc',
        'passport_doc',
        'bank_state_doc',
        'bank_solvency_doc',
        'busi_trade_license',
        'busi_office_pad',
        'busi_visiting_card',
        'sholder_noc',
        'sholder_visiting_card',
        'sholder_pay_slip',
        'stu_id_card',
        'stu_last_certificate',
        'govt_prof_doc',
        'retired_doc',
    ];

    protected $casts = [
        'dob' => 'date',
        'passport_issue_date' => 'date',
        'passport_exp_date' => 'date',
        'arr_date' => 'date',
        'gender' => GenderType::class,
        'purpose' => VisaPurpose::class,
    ];

    /**
     * Get the visa booking that owns the evisa booking details.
     */
    public function visaBooking(): BelongsTo
    {
        return $this->belongsTo(VisaBooking::class, 'visa_booking_id');
    }

    /**
     * Get the birth country that owns the evisa booking details.
     */
    public function birthCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'birth_country');
    }

    /**
     * Get  the present country that owns the evisa booking details.
     */
    public function presentCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'pa_country');
    }

    /**
     * Get  the present division that owns the evisa booking details.
     */
    public function presentDivision(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'pa_division');
    }

    /**
     * Get  the present district that owns the evisa booking details.
     */
    public function presentDistrict(): BelongsTo
    {
        return $this->belongsTo(District::class, 'pa_district');
    }
}
