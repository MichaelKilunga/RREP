<?php

namespace App\Models;

use App\Core\Licensing\LicenseManager;
use Illuminate\Database\Eloquent\Model;

class LicensedModule extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_core' => 'boolean',
        'settings_json' => 'array',
    ];

    protected static function booted()
    {
        static::saved(function () {
            app(LicenseManager::class)->clearCache();
        });
        static::deleted(function () {
            app(LicenseManager::class)->clearCache();
        });
    }
}
