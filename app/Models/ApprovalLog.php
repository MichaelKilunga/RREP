<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalLog extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'action_at' => 'datetime',
    ];

    public function workflowApproval(): BelongsTo
    {
        return $this->belongsTo(WorkflowApproval::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
