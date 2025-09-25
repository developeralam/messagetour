<?php

namespace App\Models;

use App\Enum\AgentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentPaymentRequest extends Model
{
    protected $fillable = [
        'agent_id',
        'order_id',
        'amount',
        'status',
        'notes',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime'
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
