<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'variables_json' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Accessor for body_template alias.
     */
    public function getBodyTemplateAttribute(): string
    {
        return $this->body ?? '';
    }
}
