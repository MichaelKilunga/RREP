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
}
