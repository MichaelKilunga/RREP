<?php

namespace App\Services\Finance;

use App\Models\Expense;
use App\Models\InstallmentSchedule;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SalesDeal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FinanceService
{
    public function createInvoice(array $data, array $items = []): Invoice
    {
        $data['invoice_number'] = 'INV-'.date('Ymd').'-'.strtoupper(Str::random(5));
        $data['currency'] = $data['currency'] ?? config('app.default_currency', 'TZS');

        $invoice = Invoice::create($data);

        $subtotal = 0;
        foreach ($items as $item) {
            $item['total_amount'] = $item['quantity'] * $item['unit_price'];
            $subtotal += $item['total_amount'];
            $invoice->items()->create($item);
        }

        $tax = $subtotal * (($data['tax_rate'] ?? 0) / 100);
        $discount = $data['discount_amount'] ?? 0;
        $total = ($subtotal + $tax) - $discount;

        $invoice->update([
            'subtotal' => $subtotal,
            'tax_amount' => $tax,
            'total_amount' => $total,
            'balance_due' => $total,
        ]);

        return $invoice;
    }

    public function recordPayment(array $data): Payment
    {
        $data['payment_number'] = 'PAY-'.date('Ymd').'-'.strtoupper(Str::random(5));
        $data['recorded_by'] = Auth::id();
        $data['currency'] = $data['currency'] ?? config('app.default_currency', 'TZS');

        $payment = Payment::create($data);

        if (! empty($data['invoice_id'])) {
            $invoice = Invoice::find($data['invoice_id']);
            $invoice?->recalculateTotals();
        }

        return $payment;
    }

    public function generateInstallmentPlan(SalesDeal $deal, int $numberOfInstallments, float $totalAmount, string $startDate): array
    {
        $installmentAmount = $totalAmount / $numberOfInstallments;
        $schedules = [];

        for ($i = 1; $i <= $numberOfInstallments; $i++) {
            $dueDate = date('Y-m-d', strtotime("+$i month", strtotime($startDate)));
            $title = ($i === 1) ? 'Down Payment / Milestone 1' : ($i === $numberOfInstallments ? 'Final Balance' : "Installment #{$i}");

            $schedules[] = InstallmentSchedule::create([
                'sales_deal_id' => $deal->id,
                'installment_number' => $i,
                'title' => $title,
                'due_date' => $dueDate,
                'amount' => $installmentAmount,
                'paid_amount' => 0.00,
                'status' => 'Pending',
            ]);
        }

        return $schedules;
    }

    public function createExpense(array $data): Expense
    {
        $data['expense_number'] = 'EXP-'.date('Ymd').'-'.strtoupper(Str::random(5));
        $data['recorded_by'] = Auth::id();

        return Expense::create($data);
    }
}
