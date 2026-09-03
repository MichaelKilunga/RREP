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

    public static function getVal(string $key, $default = null, ?int $orgId = null)
    {
        $orgId = $orgId ?? (function_exists('current_organization') ? current_organization()?->id : null);
        $cacheKey = "sys_setting_{$key}_".($orgId ?? 'all');

        return Cache::remember($cacheKey, 3600, function () use ($key, $default, $orgId) {
            $setting = null;
            if ($orgId !== null) {
                $setting = static::where('key', $key)->where('organization_id', $orgId)->latest('id')->first();
            }
            if (! $setting) {
                $setting = static::where('key', $key)->latest('id')->first();
            }

            return $setting ? $setting->value : $default;
        });
    }

    public static function setVal(string $key, $value, string $group = 'general', ?int $orgId = null): self
    {
        Cache::flush();

        if ($orgId === null) {
            static::where('key', $key)->update(['value' => (string) $value, 'group' => $group]);
        }

        return static::updateOrCreate(
            ['key' => $key, 'organization_id' => $orgId],
            ['value' => (string) $value, 'group' => $group]
        );
    }
}
