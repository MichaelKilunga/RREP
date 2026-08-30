<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'exchange_rate' => 'decimal:4',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];
}
