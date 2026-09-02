<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyReward extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'points_at_issuance' => 'integer',
        'sms_sent' => 'boolean',
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
        'redeemed_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(LoyaltyRule::class, 'loyalty_rule_id');
    }

    public function redeemer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'redeemed_by');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getFormattedDiscountAttribute(): string
    {
        if ($this->discount_type === 'percentage') {
            return "{$this->discount_value}% OFF";
        }

        return format_currency($this->discount_value);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast() && $this->status === 'active';
    }

    public function isValid(): bool
    {
        return $this->status === 'active' && (! $this->expires_at || $this->expires_at->isFuture());
    }
}
