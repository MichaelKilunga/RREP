<?php

namespace App\Models;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyUnit extends Model
{
    use HasUuid, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'rent_amount' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'size' => 'decimal:2',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
