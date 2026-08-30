<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyBeacon extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'northing' => 'decimal:4',
        'easting' => 'decimal:4',
        'elevation' => 'decimal:2',
    ];

    public function surveyProject(): BelongsTo
    {
        return $this->belongsTo(SurveyProject::class);
    }
}
