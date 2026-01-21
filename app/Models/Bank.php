<?php

namespace App\Models;

use App\Models\User;
use App\Models\Country;
use App\Enum\BankStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bank extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'ac_no',
        'branch',
        'address',
        'swift_code',
        'routing_no',
        'country_id',
        'status',
        'action_by',
    ];

    protected $casts = [
        'status' => BankStatus::class,
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
    public function actionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by', 'id');
    }
}
