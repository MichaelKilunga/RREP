<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\BelongsToBranch;
use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use Auditable, BelongsToBranch, BelongsToOrganization, HasUuid, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesDeal(): BelongsTo
    {
        return $this->belongsTo(SalesDeal::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('total_amount');
        $tax = $subtotal * ($this->tax_rate / 100);
        $total = ($subtotal + $tax) - $this->discount_amount;
        $paid = $this->payments()->where('status', 'Completed')->sum('amount');
        $balance = max(0, $total - $paid);

        $status = 'Issued';
        if ($balance <= 0 && $total > 0) {
            $status = 'Paid';
        } elseif ($paid > 0) {
            $status = 'Partially Paid';
        } elseif ($this->due_date < now()->toDateString()) {
            $status = 'Overdue';
        }

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $tax,
            'total_amount' => $total,
            'paid_amount' => $paid,
            'balance_due' => $balance,
            'status' => $status,
        ]);
    }
}
