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
                // Records created from the public website carry no branch; they
                // must stay visible whichever branch a staff member has selected.
                $builder->where(function (Builder $query) {
                    $query->where($query->getModel()->getTable().'.branch_id', session('current_branch_id'))
                        ->orWhereNull($query->getModel()->getTable().'.branch_id');
                });
            }
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
