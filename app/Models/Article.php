<?php

namespace App\Models;

use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use BelongsToOrganization, HasUuid, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'tags_json' => 'array',
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'reading_time_minutes' => 'integer',
    ];

    public function getFeaturedImageUrlAttribute(): string
    {
        return $this->featured_image ?: asset('images/blog-placeholder.jpg');
    }
}
