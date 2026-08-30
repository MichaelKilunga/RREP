<?php

namespace App\Core\Traits;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToOrganization
{
    public static function bootBelongsToOrganization(): void
    {
        static::creating(function ($model) {
            if (empty($model->organization_id) && session()->has('current_organization_id')) {
                $model->organization_id = session('current_organization_id');
            } elseif (empty($model->organization_id) && auth()->check() && auth()->user()->organization_id) {
                $model->organization_id = auth()->user()->organization_id;
            }
        });

        static::addGlobalScope('organization', function (Builder $builder) {
            if (session()->has('current_organization_id')) {
                $builder->where($builder->getModel()->getTable().'.organization_id', session('current_organization_id'));
            } elseif (auth()->check() && ! auth()->user()->isSuperAdmin() && auth()->user()->organization_id) {
                $builder->where($builder->getModel()->getTable().'.organization_id', auth()->user()->organization_id);
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
