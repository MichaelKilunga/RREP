<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToBranch;
use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
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
     * The distinct land-use / ownership combinations of the project's plots;
     * a light relation for card facts that avoids loading every plot.
     */
    public function parcelKinds(): HasMany
    {
        return $this->hasMany(LandParcel::class, 'real_estate_project_id')
            ->select(['real_estate_project_id', 'land_use', 'title_deed_type'])
            ->distinct();
    }

    /**
     * Everything a public project card needs, loaded in a fixed number of queries.
     */
    public function scopeWithCardData(Builder $query): Builder
    {
        return $query->with(['service', 'parcelKinds'])
            ->withMin('parcels', 'price')
            ->withMax('parcels', 'price')
            ->withMin('parcels', 'area_sqm')
            ->withMax('parcels', 'area_sqm');
    }

    /**
     * Up to `$limit` quick facts for a land project card, drawn only from data
     * the project really has: plot sizes, land use, price per m², ownership
     * document and a future completion date.
     *
     * @return array<int, array{icon: string, label: string, value: string, hint?: string}>
     */
    public function cardFacts(int $limit = 4): array
    {
        $facts = [];

        $minArea = (float) ($this->parcels_min_area_sqm ?? 0);
        $maxArea = (float) ($this->parcels_max_area_sqm ?? 0);
        if ($minArea > 0) {
            $facts[] = [
                'icon' => 'bi-aspect-ratio',
                'label' => __('marketplace.fact_plot_sizes'),
                'value' => ($maxArea > $minArea ? number_format($minArea).' – '.number_format($maxArea) : number_format($minArea)).' m²',
            ];
        }

        $kinds = $this->relationLoaded('parcelKinds') ? $this->parcelKinds : collect();
        $uses = $kinds->pluck('land_use')->filter()->map(fn (string $use) => self::localisedPair($use))->unique()->values();
        if ($uses->isNotEmpty()) {
            $facts[] = [
                'icon' => 'bi-buildings',
                'label' => __('marketplace.fact_land_use'),
                'value' => $uses->first().($uses->count() > 1 ? ' +'.($uses->count() - 1) : ''),
                'hint' => $uses->implode(', '),
            ];
        }

        if ((float) $this->price_per_sqm > 0) {
            $facts[] = [
                'icon' => 'bi-rulers',
                'label' => __('marketplace.fact_price_per_sqm'),
                'value' => format_currency($this->price_per_sqm, null, 0).' / m²',
            ];
        }

        $deed = $kinds->pluck('title_deed_type')->filter()->first();
        if ($deed) {
            $facts[] = ['icon' => 'bi-file-earmark-check', 'label' => __('marketplace.fact_ownership'), 'value' => self::localisedPair($deed)];
        }

        if ($this->expected_completion_date?->isFuture()) {
            $facts[] = [
                'icon' => 'bi-calendar-check',
                'label' => __('marketplace.fact_completion'),
                'value' => $this->expected_completion_date->translatedFormat('M Y'),
            ];
        }

        return array_slice($facts, 0, $limit);
    }

    /**
     * Bilingual values such as "Makazi / Residential" show only the half that
     * matches the current language.
     */
    private static function localisedPair(string $value): string
    {
        $parts = array_map('trim', explode(' / ', $value));
        if (count($parts) !== 2) {
            return trim($value);
        }

        return app()->getLocale() === 'sw' ? $parts[0] : $parts[1];
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
