<?php

namespace App\Models;

use App\Core\Traits\BelongsToBranch;
use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceRequest extends Model
{
    use BelongsToBranch, BelongsToOrganization, HasUuid, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'images_json' => 'array',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'reported_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(PropertyUnit::class, 'property_unit_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
