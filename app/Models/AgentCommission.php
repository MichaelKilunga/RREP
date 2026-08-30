<?php

namespace App\Models;

use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentCommission extends Model
{
    use BelongsToOrganization, HasUuid;

    protected $guarded = ['id'];

    protected $casts = [
        'deal_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'total_commission' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function salesDeal(): BelongsTo
    {
        return $this->belongsTo(SalesDeal::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}
