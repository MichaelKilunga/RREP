<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToBranch;
use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RealEstateProject extends Model
{
    use Auditable, BelongsToBranch, BelongsToOrganization, HasUuid, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'starting_price' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'total_units' => 'integer',
        'available_units' => 'integer',
        'launch_date' => 'date',
        'expected_completion_date' => 'date',
        'amenities_json' => 'array',
        'unit_types_json' => 'array',
        'gallery_images_json' => 'array',
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function inquiries(): HasMany
    {
        return $this->hasMany(PropertyInquiry::class, 'real_estate_project_id');
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'From '.format_currency($this->starting_price, $this->currency);
    }

    public function getHeroImageUrlAttribute(): string
    {
        return $this->hero_image ?: asset('images/project-placeholder.jpg');
    }
}
