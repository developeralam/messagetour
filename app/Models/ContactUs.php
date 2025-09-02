<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactUs extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email_address',
        'phone',
        'subject',
        'message',
        'action_by'
    ];

    public function actionby(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by', 'id');
    }
}
