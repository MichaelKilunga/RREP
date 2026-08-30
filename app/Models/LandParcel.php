<?php

namespace App\Models;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LandParcel extends Model
{
    use HasUuid, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'acreage' => 'decimal:4',
        'boundary_coordinates_json' => 'array',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function surveyProjects(): HasMany
    {
        return $this->hasMany(SurveyProject::class);
    }
}
