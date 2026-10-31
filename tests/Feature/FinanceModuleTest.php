<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SystemSetting;
use App\Models\TaxonomyTerm;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Finance: multi-line invoices, edit/cancel/delete rules, safe payments
 * (customer match, no overpayment, void), receipts, expenses, overview.
 */
class FinanceModuleTest extends TestCase
{
    private function admin(): User
    {
        return User::where('email', 'admin@rehospace.com')->firstOrFail();
    }

    private function customer(): Customer
    {
        return Customer::create(['first_name' => 'Hamisi', 'last_name' => 'Juma', 'customer_type' => 'Individual', 'phone' => '0713222333']);
    }

    private function invoice(Customer $customer): Invoice
    {
        $this->actingAs($this->admin())->post(route('finance.invoices.store'), [
            'customer_id' => $customer->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'tax_rate' => 18,
            'discount_amount' => 100000,
            'items' => [
                ['description' => 'Plot 12 deposit', 'quantity' => 1, 'unit_price' => 1000000],
                ['description' => 'Survey fee', 'quantity' => 2, 'unit_price' => 250000],
            ],
        ])->assertRedirect();

        return Invoice::where('customer_id', $customer->id)->latest('id')->firstOrFail();
    }

    public function test_multi_line_invoice_totals_include_tax_and_discount(): void
    {
        $invoice = $this->invoice($this->customer());

        // (1,000,000 + 500,000) * 1.18 - 100,000
        $this->assertSame(2, $invoice->items()->count());
        $this->assertSame(1670000.0, (float) $invoice->total_amount);
        $this->assertSame(1670000.0, (float) $invoice->balance_due);
        $this->assertSame('Issued', $invoice->status);
    }

    public function test_invoices_can_be_edited_until_paid(): void
    {
        $customer = $this->customer();
        $invoice = $this->invoice($customer);

        $this->actingAs($this->admin())->put(route('finance.invoices.update', $invoice), [
            'customer_id' => $customer->id, 'issue_date' => now()->toDateString(), 'due_date' => now()->addDays(30)->toDateString(),
            'items' => [['description' => 'Full plot price', 'quantity' => 1, 'unit_price' => 3000000]],
        ])->assertRedirect(route('finance.invoices.show', $invoice));
        $this->assertSame(3000000.0, (float) $invoice->fresh()->total_amount);
        $this->assertSame(1, $invoice->items()->count());

        $invoice->update(['status' => 'Paid']);
        $this->actingAs($this->admin())->get(route('finance.invoices.edit', $invoice))->assertStatus(422);
    }

    public function test_payments_are_validated_against_customer_and_balance(): void
    {
        Http::fake(['*' => Http::response(['status' => 'ok'], 200)]);
        SystemSetting::setVal('pushsms_api_key', 'real_key_for_test');
        $customer = $this->customer();
        $invoice = $this->invoice($customer);
        $other = Customer::create(['first_name' => 'Other', 'last_name' => 'Person', 'customer_type' => 'Individual', 'phone' => '0713999888']);

        $base = ['invoice_id' => $invoice->id, 'payment_date' => now()->toDateString(), 'payment_method' => 'M-Pesa'];

        $this->actingAs($this->admin())->post(route('finance.payments.record'), $base + ['customer_id' => $other->id, 'amount' => 1000])
            ->assertSessionHasErrors('invoice_id');
        $this->actingAs($this->admin())->post(route('finance.payments.record'), $base + ['customer_id' => $customer->id, 'amount' => 9999999])
            ->assertSessionHasErrors('amount');

        $this->actingAs($this->admin())->post(route('finance.payments.record'), $base + ['customer_id' => $customer->id, 'amount' => 670000, 'send_receipt' => '1'])
            ->assertSessionHas('success');

        $invoice->refresh();
        $this->assertSame('Partially Paid', $invoice->status);
        $this->assertSame(1000000.0, (float) $invoice->balance_due);
        $this->assertDatabaseHas('communication_logs', ['customer_id' => $customer->id, 'template_code' => 'payment_receipt']);
    }

    public function test_voiding_a_payment_restores_the_balance_and_receipts_render(): void
    {
        $customer = $this->customer();
        $invoice = $this->invoice($customer);
        $this->actingAs($this->admin())->post(route('finance.payments.record'), [
            'invoice_id' => $invoice->id, 'customer_id' => $customer->id, 'amount' => 1670000,
            'payment_date' => now()->toDateString(), 'payment_method' => 'Bank Transfer',
        ]);
        $this->assertSame('Paid', $invoice->fresh()->status);
        $payment = Payment::where('invoice_id', $invoice->id)->firstOrFail();

        $this->actingAs($this->admin())->get(route('finance.payments.receipt', $payment))->assertOk()->assertSee($payment->payment_number);
        $this->actingAs($this->admin())->get(route('finance.payments'))->assertOk()->assertSee($payment->payment_number);

        $this->actingAs($this->admin())->post(route('finance.payments.void', $payment), ['reason' => 'Bounced'])->assertSessionHas('success');
        $this->assertSame('Voided', $payment->fresh()->status);
        $this->assertSame(1670000.0, (float) $invoice->fresh()->balance_due);
        $this->assertNotSame('Paid', $invoice->fresh()->status);
    }

    public function test_cancel_and_delete_are_blocked_while_payments_exist(): void
    {
        $customer = $this->customer();
        $invoice = $this->invoice($customer);
        $this->actingAs($this->admin())->post(route('finance.payments.record'), [
            'invoice_id' => $invoice->id, 'customer_id' => $customer->id, 'amount' => 1000, 'payment_date' => now()->toDateString(), 'payment_method' => 'Cash',
        ]);

        $this->actingAs($this->admin())->post(route('finance.invoices.cancel', $invoice))->assertSessionHas('error');
        $this->actingAs($this->admin())->delete(route('finance.invoices.destroy', $invoice))->assertSessionHas('error');

        $fresh = $this->invoice($customer);
        $this->actingAs($this->admin())->post(route('finance.invoices.cancel', $fresh))->assertSessionHas('success');
        $fresh->refresh()->recalculateTotals();
        $this->assertSame('Cancelled', $fresh->fresh()->status, 'Recalculation must not resurrect a cancelled invoice');
    }

    public function test_reminder_sms_is_sent_for_open_invoices(): void
    {
        Http::fake(['*' => Http::response(['status' => 'ok'], 200)]);
        SystemSetting::setVal('pushsms_api_key', 'real_key_for_test');
        $customer = $this->customer();
        $invoice = $this->invoice($customer);

        $this->actingAs($this->admin())->post(route('finance.invoices.remind', $invoice))->assertSessionHas('success');
        $this->assertDatabaseHas('communication_logs', ['customer_id' => $customer->id, 'template_code' => 'invoice_reminder', 'status' => 'Sent']);
    }

    public function test_expenses_use_editable_categories_and_support_edit_and_delete(): void
    {
        TaxonomyTerm::create(['group' => 'expense_category', 'name' => 'Beacon Materials']);

        $this->actingAs($this->admin())->get(route('finance.expenses'))->assertOk()->assertSee('Beacon Materials');

        $this->actingAs($this->admin())->post(route('finance.expenses.store'), [
            'category' => 'Beacon Materials', 'title' => 'Concrete beacons', 'amount' => 120000, 'expense_date' => now()->toDateString(), 'payment_method' => 'Cash',
        ])->assertSessionHas('success');
        $expense = Expense::where('title', 'Concrete beacons')->firstOrFail();

        $this->actingAs($this->admin())->put(route('finance.expenses.update', $expense), [
            'category' => 'Beacon Materials', 'title' => 'Concrete beacons (x20)', 'amount' => 150000, 'expense_date' => now()->toDateString(), 'payment_method' => 'Cash',
        ])->assertSessionHas('success');
        $this->assertSame(150000.0, (float) $expense->fresh()->amount);

        $this->actingAs($this->admin())->get(route('finance.expenses', ['category' => 'Beacon Materials']))->assertSee('Concrete beacons (x20)');

        $this->actingAs($this->admin())->delete(route('finance.expenses.destroy', $expense))->assertSessionHas('success');
        $this->assertSoftDeleted($expense);
    }

    public function test_finance_overview_for_accountants_not_sales_agents(): void
    {
        $this->actingAs(User::where('email', 'finance@rehospace.com')->firstOrFail())->get(route('finance.overview'))
            ->assertOk()
            ->assertSee('id="cashChart"', false)
            ->assertSee('Money owed by age');

        $this->actingAs(User::where('email', 'agent@rehospace.com')->firstOrFail())->get(route('finance.overview'))->assertForbidden();
    }
}
