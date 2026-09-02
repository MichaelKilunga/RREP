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
        $cacheKey = "sys_setting_{$key}_".($orgId ?? 'all');

        return Cache::remember($cacheKey, 3600, function () use ($key, $default, $orgId) {
            $query = static::where('key', $key);
            if ($orgId !== null) {
                $query->where('organization_id', $orgId);
            }
            $setting = $query->latest('updated_at')->first();

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
