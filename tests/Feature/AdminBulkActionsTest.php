<?php

namespace Tests\Feature;

use App\Models\CommunicationLog;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\LandParcel;
use App\Models\Payment;
use App\Models\RealEstateProject;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Select / select-all and bulk actions across CRM, reservations, finance and
 * the SMS log, including the rules that keep data consistent: customers with
 * open business stay, cancelled holds release plots, deleted payments give
 * invoices their balance back.
 */
class AdminBulkActionsTest extends TestCase
{
    private function admin(): User
    {
        return User::where('email', 'admin@rehospace.com')->firstOrFail();
    }

    private function customer(string $firstName): Customer
    {
        return Customer::create(['first_name' => $firstName, 'last_name' => 'Kweka', 'customer_type' => 'Individual', 'phone' => '0716'.random_int(100000, 999999)]);
    }

    private function plot(string $status): LandParcel
    {
        return LandParcel::create([
            'uuid' => Str::uuid(), 'real_estate_project_id' => RealEstateProject::where('project_category', 'land')->value('id'),
            'parcel_number' => (string) random_int(1000, 9999), 'block_number' => 'Z', 'status' => $status, 'acreage' => 0.1, 'area_sqm' => 400, 'price' => 4000000,
        ]);
    }

    private function hold(Customer $customer, LandParcel $plot, string $status = 'Active'): Reservation
    {
        return Reservation::create([
            'land_parcel_id' => $plot->id, 'customer_id' => $customer->id, 'reservation_number' => 'RESV-'.Str::random(6),
            'deposit_paid' => 500000, 'reserved_from' => now()->subDays(2), 'reserved_until' => now()->addDays(5), 'status' => $status,
        ]);
    }

    private function invoice(Customer $customer, float $amount = 1000000): Invoice
    {
        $this->actingAs($this->admin())->post(route('finance.invoices.store'), [
            'customer_id' => $customer->id, 'issue_date' => now()->toDateString(), 'due_date' => now()->addDays(14)->toDateString(),
            'items' => [['description' => 'Plot deposit', 'quantity' => 1, 'unit_price' => $amount]],
        ])->assertRedirect();

        return Invoice::where('customer_id', $customer->id)->latest('id')->firstOrFail();
    }

    private function pay(Invoice $invoice, float $amount): Payment
    {
        $this->actingAs($this->admin())->post(route('finance.payments.record'), [
            'invoice_id' => $invoice->id, 'customer_id' => $invoice->customer_id, 'amount' => $amount,
            'payment_date' => now()->toDateString(), 'payment_method' => 'M-Pesa',
        ])->assertSessionHas('success');

        return Payment::where('invoice_id', $invoice->id)->latest('id')->firstOrFail();
    }

    public function test_every_bulk_list_offers_a_select_all_button(): void
    {
        $customer = $this->customer('Upendo');
        $this->hold($customer, $this->plot('Reserved'));
        $this->invoice($customer);
        Expense::create(['expense_number' => 'EXP-T1', 'category' => 'Survey', 'title' => 'Beacons', 'amount' => 1000, 'expense_date' => now(), 'payment_method' => 'Cash']);
        CommunicationLog::create(['recipient' => '255716000111', 'channel' => 'sms', 'body' => 'Habari', 'status' => 'Sent']);

        foreach (['crm.customers' => 'customers', 'reservations.index' => 'reservations', 'finance.invoices' => 'invoices', 'finance.expenses' => 'expenses', 'sms.index' => 'smslogs'] as $route => $group) {
            $this->actingAs($this->admin())->get(route($route))
                ->assertOk()
                ->assertSee('data-bulk-all="'.$group.'"', false)
                ->assertSee('data-bulk-form="'.$group.'"', false);
        }
        $this->actingAs($this->admin())->get(route('crm.requests'))->assertOk()->assertSee('data-bulk-all="requests"', false);
    }

    public function test_bulk_customer_delete_keeps_customers_with_open_business(): void
    {
        $free = $this->customer('Huru');
        $holding = $this->customer('Mwenye');
        $this->hold($holding, $this->plot('Reserved'));

        $this->actingAs($this->admin())->post(route('crm.customers.bulk'), ['action' => 'delete', 'ids' => [$free->id, $holding->id]])
            ->assertSessionHas('error', fn (string $message) => str_contains($message, 'Mwenye Kweka'));

        $this->assertSoftDeleted($free);
        $this->assertNotSoftDeleted($holding);

        $agent = User::where('email', 'agent@rehospace.com')->firstOrFail();
        $this->actingAs($agent)->post(route('crm.customers.bulk'), ['action' => 'delete', 'ids' => [$holding->id]])->assertForbidden();
    }

    public function test_reservations_bulk_cancel_releases_plots_and_delete_keeps_sold_plots_sold(): void
    {
        $customer = $this->customer('Baraka');
        $first = $this->hold($customer, $plotA = $this->plot('Reserved'));
        $second = $this->hold($customer, $plotB = $this->plot('Reserved'));
        $sold = $this->hold($customer, $plotC = $this->plot('Sold'), 'Converted');

        $this->actingAs($this->admin())->post(route('reservations.bulk'), ['action' => 'cancel', 'ids' => [$first->id, $second->id, $sold->id]])
            ->assertSessionHas('success');

        $this->assertSame('Cancelled', $first->fresh()->status);
        $this->assertSame('Available', $plotA->fresh()->status);
        $this->assertSame('Available', $plotB->fresh()->status);
        $this->assertSame('Converted', $sold->fresh()->status, 'Only active holds can be cancelled');

        $this->actingAs($this->admin())->delete(route('reservations.destroy', $sold))->assertSessionHas('success');
        $this->assertSoftDeleted($sold);
        $this->assertSame('Sold', $plotC->fresh()->status);

        $active = $this->hold($customer, $plotD = $this->plot('Reserved'));
        $this->actingAs($this->admin())->post(route('reservations.bulk'), ['action' => 'delete', 'ids' => [$active->id]])->assertSessionHas('success');
        $this->assertSoftDeleted($active);
        $this->assertSame('Available', $plotD->fresh()->status);
    }

    public function test_deleting_payments_restores_invoice_balances(): void
    {
        $customer = $this->customer('Neema');
        $invoice = $this->invoice($customer, 1000000);
        $payment = $this->pay($invoice, 1000000);
        $this->assertSame('Paid', $invoice->fresh()->status);

        $this->actingAs($this->admin())->delete(route('finance.payments.destroy', $payment))->assertSessionHas('success');
        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
        $this->assertSame(1000000.0, (float) $invoice->fresh()->balance_due);
        $this->assertNotSame('Paid', $invoice->fresh()->status);

        $first = $this->pay($invoice, 300000);
        $second = $this->pay($invoice, 200000);
        $this->assertSame(500000.0, (float) $invoice->fresh()->balance_due);

        $this->actingAs($this->admin())->post(route('finance.payments.bulk'), ['action' => 'delete', 'ids' => [$first->id, $second->id]])
            ->assertSessionHas('success');
        $this->assertSame(1000000.0, (float) $invoice->fresh()->balance_due);
        $this->assertSame(0.0, (float) $invoice->fresh()->paid_amount);
    }

    public function test_bulk_invoice_cancel_skips_invoices_that_have_payments(): void
    {
        $customer = $this->customer('Rehema');
        $unpaid = $this->invoice($customer, 400000);
        $partlyPaid = $this->invoice($customer, 600000);
        $this->pay($partlyPaid, 100000);

        $this->actingAs($this->admin())->post(route('finance.invoices.bulk'), ['action' => 'cancel', 'ids' => [$unpaid->id, $partlyPaid->id]])
            ->assertSessionHas('error');

        $this->assertSame('Cancelled', $unpaid->fresh()->status);
        $this->assertSame(0.0, (float) $unpaid->fresh()->balance_due);
        $this->assertSame('Partially Paid', $partlyPaid->fresh()->status);
    }

    public function test_expenses_can_be_deleted_in_bulk(): void
    {
        $ids = collect(['Fuel', 'Printing'])->map(fn (string $title) => Expense::create([
            'expense_number' => 'EXP-'.Str::random(5), 'category' => 'Transport', 'title' => $title, 'amount' => 25000, 'expense_date' => now(), 'payment_method' => 'Cash',
        ])->id);

        $this->actingAs($this->admin())->post(route('finance.expenses.bulk'), ['action' => 'delete', 'ids' => $ids->all()])->assertSessionHas('success');

        $ids->each(fn (int $id) => $this->assertSoftDeleted('expenses', ['id' => $id]));
    }

    public function test_sms_logs_can_be_deleted_one_at_a_time_or_in_bulk(): void
    {
        $logs = collect(range(1, 3))->map(fn (int $n) => CommunicationLog::create([
            'recipient' => '25571600011'.$n, 'channel' => 'sms', 'body' => 'Ujumbe '.$n, 'status' => 'Failed', 'error_message' => 'HTTP 500',
        ]));

        $this->actingAs($this->admin())->delete(route('sms.destroy', $logs[0]))->assertSessionHas('success');
        $this->assertDatabaseMissing('communication_logs', ['id' => $logs[0]->id]);

        $this->actingAs($this->admin())->post(route('sms.bulk'), ['action' => 'delete', 'ids' => [$logs[1]->id, $logs[2]->id]])->assertSessionHas('success');
        $this->assertDatabaseMissing('communication_logs', ['id' => $logs[2]->id]);

        $accountant = User::where('email', 'finance@rehospace.com')->firstOrFail();
        $this->actingAs($accountant)->post(route('sms.bulk'), ['action' => 'delete', 'ids' => [1]])->assertForbidden();
    }
}
