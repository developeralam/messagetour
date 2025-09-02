<?php

namespace App\Models;

use App\Models\User;
use App\Enum\AmountType;
use App\Enum\CommisionRole;
use App\Enum\ProductType;
use App\Enum\CommissionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'commission_role',
        'amount',
        'product_type',
        'amount_type',
        'status',
        'action_by'
    ];

    protected $casts = [
        'commission_role' => CommisionRole::class,
        'product_type' => ProductType::class,
        'amount_type' => AmountType::class,
        'status' => CommissionStatus::class,
    ];

    public function actionby(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by', 'id');
    }
}
