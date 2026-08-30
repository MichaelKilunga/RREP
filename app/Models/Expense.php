<?php

namespace App\Models;

use App\Core\Traits\BelongsToBranch;
use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use BelongsToBranch, BelongsToOrganization, HasUuid, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'receipt_media_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
