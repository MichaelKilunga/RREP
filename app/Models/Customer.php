<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToBranch;
use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use Auditable, BelongsToBranch, BelongsToOrganization, HasUuid, SoftDeletes;

    protected $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function salesDeals(): HasMany
    {
        return $this->hasMany(SalesDeal::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function loyaltyRewards(): HasMany
    {
        return $this->hasMany(LoyaltyReward::class);
    }

    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyPointTransaction::class);
    }

    public function surveyProjects(): HasMany
    {
        return $this->hasMany(SurveyProject::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getActiveRewardCodesAttribute(): array
    {
        return $this->loyaltyRewards()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->pluck('reward_code')
            ->toArray();
    }
}
