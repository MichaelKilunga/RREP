<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyRule extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'min_points' => 'integer',
        'min_transactions' => 'integer',
        'discount_value' => 'decimal:2',
        'validity_days' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function rewards(): HasMany
    {
        return $this->hasMany(LoyaltyReward::class);
    }

    public function getFormattedDiscountAttribute(): string
    {
        if ($this->discount_type === 'percentage') {
            return "{$this->discount_value}% OFF";
        }

        return format_currency($this->discount_value).' DISCOUNT';
    }
}
