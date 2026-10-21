<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    /**
     * Notifications a staff member should see: broadcast rows plus their own.
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $q) use ($user) {
            $q->whereNull('user_id')->orWhere('user_id', $user->id);
        });
    }

    public function icon(): string
    {
        return match ($this->type) {
            'reservation' => 'bi-bookmark-check text-warning',
            'inquiry' => 'bi-funnel text-info',
            'viewing' => 'bi-calendar-check text-primary',
            'survey' => 'bi-compass text-success',
            default => 'bi-bell text-secondary',
        };
    }
}
