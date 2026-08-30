<?php

namespace App\Core\Traits;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToBranch
{
    public static function bootBelongsToBranch(): void
    {
        static::creating(function ($model) {
            if (empty($model->branch_id) && session()->has('current_branch_id')) {
                $model->branch_id = session('current_branch_id');
            } elseif (empty($model->branch_id) && auth()->check() && auth()->user()->branch_id) {
                $model->branch_id = auth()->user()->branch_id;
            }
        });

        static::addGlobalScope('branch', function (Builder $builder) {
            if (session()->has('current_branch_id') && session('current_branch_id') !== 'all') {
                $builder->where($builder->getModel()->getTable().'.branch_id', session('current_branch_id'));
            }
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
