<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToBranch;
use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

class RealEstateProject extends Model
{
    use Auditable, BelongsToBranch, BelongsToOrganization, HasUuid, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'starting_price' => 'decimal:2',
        'application_fee' => 'decimal:2',
        'price_per_sqm' => 'decimal:2',
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
        'is_pinned' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function service()
    {
        return $this->belongsTo(CompanyService::class, 'company_service_id');
    }

    public function isServiceProject(): bool
    {
        return $this->project_category === 'service';
    }

    /**
     * @return array<int, string>
     */
    public function getGalleryAttribute(): array
    {
        return array_values(array_filter((array) $this->gallery_images_json));
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(PropertyInquiry::class, 'real_estate_project_id');
    }

    public function parcels(): HasMany
    {
        return $this->hasMany(LandParcel::class, 'real_estate_project_id');
    }

    /**
     * Recompute plot counters from the parcels' statuses (used whenever a
     * parcel is reserved, released or sold).
     */
    public function syncPlotCounts(): void
    {
        $total = $this->parcels()->count();
        if ($total === 0) {
            return;
        }

        $this->update([
            'total_units' => $total,
            'available_units' => $this->parcels()->where('status', 'Available')->count(),
            'sold_plots' => $this->parcels()->where('status', 'Sold')->count(),
        ]);
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'From '.format_currency($this->starting_price, $this->currency, 0);
    }

    /**
     * Translated project status (Selling, Upcoming, Ongoing...) for badges.
     */
    public function getStatusLabelAttribute(): string
    {
        $slug = Str::snake(str_replace('-', ' ', (string) $this->project_status));
        foreach (['admin.status_'.$slug, 'admin.phase_'.$slug] as $key) {
            if (Lang::has($key)) {
                return __($key);
            }
        }

        return (string) $this->project_status;
    }

    /**
     * "Place, Council" for cards and tables; the council doubles as the town.
     */
    public function getLocationLabelAttribute(): string
    {
        return collect([$this->location_name, $this->council ?: $this->city])
            ->map(fn ($part) => trim((string) $part))
            ->filter()
            ->unique(fn ($part) => mb_strtolower($part))
            ->implode(', ');
    }

    public function getHeroImageUrlAttribute(): string
    {
        return $this->hero_image ?: asset('images/project-placeholder.jpg');
    }
}
