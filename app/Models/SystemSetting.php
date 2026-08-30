<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $guarded = ['id'];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public static function getVal(string $key, $default = null)
    {
        return Cache::remember("sys_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            return $setting ? $setting->value : $default;
        });
    }

    public static function setVal(string $key, $value, string $group = 'general', ?int $orgId = null): self
    {
        Cache::forget("sys_setting_{$key}");

        return static::updateOrCreate(
            ['key' => $key, 'organization_id' => $orgId],
            ['value' => $value, 'group' => $group]
        );
    }
}
