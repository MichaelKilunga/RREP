<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToBranch;
use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use Auditable, BelongsToBranch, BelongsToOrganization, HasUuid, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'price' => 'decimal:2',
        'rent_price' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'area_size' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'features_json' => 'array',
    ];

    public function propertyType(): BelongsTo
    {
        return $this->belongsTo(PropertyType::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(PropertyOwner::class, 'property_owner_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(PropertyUnit::class);
    }

    public function landParcel(): HasOne
    {
        return $this->hasOne(LandParcel::class);
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'property_amenity');
    }

    public function media(): HasMany
    {
        return $this->hasMany(PropertyMedia::class)->orderBy('display_order');
    }

    public function primaryMedia(): ?PropertyMedia
    {
        return $this->media()->where('is_primary', true)->first() ?: $this->media()->first();
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'property_interest_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function salesDeals(): HasMany
    {
        return $this->hasMany(SalesDeal::class);
    }

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->listing_type === 'Rent' || $this->listing_type === 'Lease') {
            return format_currency($this->rent_price, $this->currency).' / '.strtolower($this->rent_period);
        }

        return format_currency($this->price, $this->currency);
    }

    public function getPrimaryImageUrlAttribute(): string
    {
        $primary = $this->primaryMedia();
        if ($primary && $primary->mediaFile) {
            return $primary->mediaFile->url;
        }

        return asset('images/property-placeholder.jpg');
    }
}
