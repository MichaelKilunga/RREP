<?php

namespace App\Models;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class CompanyService extends Model
{
    use HasUuid;

    protected $guarded = ['id'];

    protected $casts = [
        'sub_services' => 'array',
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    public function projects()
    {
        return $this->hasMany(RealEstateProject::class, 'company_service_id');
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail ?: null;
    }

    public function getCtaLabelAttribute(): string
    {
        return $this->cta_text
            ?: setting_trans('landing_services_cta_default', 'Omba Bei', 'Request a Quote')
            ?: __('pages.request_quote');
    }
}
