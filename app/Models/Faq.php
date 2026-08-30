<?php

namespace App\Models;

use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use BelongsToOrganization;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];
}
