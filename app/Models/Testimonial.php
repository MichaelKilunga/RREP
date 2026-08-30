<?php

namespace App\Models;

use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use BelongsToOrganization, HasUuid;

    protected $guarded = ['id'];

    protected $casts = [
        'rating' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];
}
