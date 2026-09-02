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

class SurveyProject extends Model
{
    use Auditable, BelongsToBranch, BelongsToOrganization, HasUuid, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'total_area' => 'decimal:4',
        'start_date' => 'date',
        'expected_completion_date' => 'date',
        'actual_completion_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function parcel(): BelongsTo
    {
        return $this->belongsTo(LandParcel::class, 'land_parcel_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function leadSurveyor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_surveyor_id');
    }

    public function beacons(): HasMany
    {
        return $this->hasMany(SurveyBeacon::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(SurveyMilestone::class)->orderBy('sequence');
    }
}
