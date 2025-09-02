<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChartOfAccount extends Model
{
    use SoftDeletes;

    protected $fillable = ['parent_id', 'code', 'name', 'type', 'current_balance'];

    public function parent()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id', 'id');
    }

    public function ledgers()
    {
        return $this->hasMany(Ledger::class, 'account_id', 'id');
    }

    public function scopeAccount($query)
    {
        return $query->whereNotNull('parent_id');
    }

    // This flag controls whether to skip the boot logic
    public static bool $skipCodeGeneration = false;

    protected static function booted()
    {
        static::creating(function ($account) {
            // Check if skipping is enabled
            if (self::$skipCodeGeneration) {
                return; // Skip code generation logic
            }
            // Set a base code for each type
            $baseCode = [
                'asset' => 100,
                'liability' => 201,
                'expense' => 500,
                'revenue' => 400,
            ];

            $type = $account->type;

            // Get the last code for the current type
            $lastCode = ChartOfAccount::where('type', $type)
                ->orderByDesc('code')
                ->first();

            // Generate the new code
            if ($lastCode) {
                $account->code = $lastCode->code + 1;
            } else {
                // If no previous code exists, start from the base code
                $account->code = $baseCode[$type];
            }
        });
    }
}
