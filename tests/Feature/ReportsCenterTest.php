<?php

namespace Tests\Feature;

use App\Models\CommunicationLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\LandParcel;
use App\Models\PropertyInquiry;
use App\Models\RealEstateProject;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Reports: shared period filter, new financial / plot sales / customers / SMS
 * reports, permission-aware reports center, CSV exports gated by export_reports.
 */
class ReportsCenterTest extends TestCase
{
    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    private function customer(string $firstName = 'Neema'): Customer
    {
        return Customer::create(['first_name' => $firstName, 'last_name' => 'Mushi', 'customer_type' => 'Individual', 'phone' => '0716'.random_int(100000, 999999)]);
    }

    /**
     * @return array{period: string, from: string, to: string}
     */
    private function range(string $from, string $to): array
    {
        return ['period' => 'custom', 'from' => $from, 'to' => $to];
    }

    public function test_requests_report_and_its_csv_follow_the_chosen_period(): void
    {
        $admin = $this->user('admin@rehospace.com');
        PropertyInquiry::create(['inquiry_type' => 'Service Quote', 'name' => 'Recent Requester', 'phone' => '0715000111', 'message' => 'Bei ya upimaji', 'status' => 'New']);
        $old = PropertyInquiry::create(['inquiry_type' => 'Service Quote', 'name' => 'Old Requester', 'phone' => '0715000222', 'message' => 'Bei ya ramani', 'status' => 'Resolved']);
        $old->forceFill(['created_at' => now()->subDays(120)])->save();

        $this->actingAs($admin)->get(route('reports.requests'))
            ->assertOk()
            ->assertSee('Recent Requester')
            ->assertDontSee('Old Requester');
        $this->actingAs($admin)->get(route('reports.requests', ['period' => 'all']))->assertSee('Old Requester');
        $this->actingAs($admin)->get(route('reports.requests', $this->range(now()->subDays(150)->toDateString(), now()->subDays(100)->toDateString())))
            ->assertSee('Old Requester')
            ->assertDontSee('Recent Requester');
        $this->actingAs($admin)->get(route('reports.requests', ['period' => 'custom', 'from' => 'not-a-date']))->assertOk();

        $csv = $this->actingAs($admin)->get(route('reports.requests', ['period' => 'this_month', 'export' => 'csv']))->assertOk()->streamedContent();
        $this->assertStringContainsString('Recent Requester', $csv);
        $this->assertStringNotContainsString('Old Requester', $csv);
    }

    public function test_financial_statement_totals_the_period_and_is_limited_to_finance_roles(): void
    {
        $admin = $this->user('admin@rehospace.com');
        $customer = $this->customer();

        $this->actingAs($admin)->post(route('finance.invoices.store'), [
            'customer_id' => $customer->id, 'issue_date' => '2019-03-05', 'due_date' => '2019-03-30',
            'items' => [['description' => 'Plot deposit', 'quantity' => 1, 'unit_price' => 1000000]],
        ])->assertRedirect();
        $invoice = Invoice::where('customer_id', $customer->id)->firstOrFail();
        $this->actingAs($admin)->post(route('finance.payments.record'), [
            'invoice_id' => $invoice->id, 'customer_id' => $customer->id, 'amount' => 600000, 'payment_date' => '2019-03-10', 'payment_method' => 'M-Pesa',
        ])->assertSessionHas('success');
        $this->actingAs($admin)->post(route('finance.expenses.store'), [
            'category' => 'Survey', 'title' => 'Beacon materials', 'amount' => 150000, 'expense_date' => '2019-03-12', 'payment_method' => 'Cash',
        ])->assertSessionHas('success');

        $march = $this->range('2019-03-01', '2019-03-31');
        $accountant = $this->user('finance@rehospace.com');
        $this->actingAs($accountant)->get(route('reports.finance', $march))
            ->assertOk()
            ->assertSee('id="cashChart"', false)
            ->assertViewHas('stats', fn (array $stats) => $stats['invoiced'] === 1000000.0
                && $stats['collected'] === 600000.0
                && $stats['expenses'] === 150000.0
                && $stats['net'] === 450000.0
                && $stats['collection_rate'] === 60.0);

        $csv = $this->actingAs($accountant)->get(route('reports.finance', $march + ['export' => 'csv']))->assertOk()->streamedContent();
        $this->assertStringContainsString('TOTAL,600000,150000,450000', $csv);

        $agent = $this->user('agent@rehospace.com');
        $this->actingAs($agent)->get(route('reports.finance'))->assertForbidden();
        $this->actingAs($agent)->get(route('reports.sms'))->assertForbidden();
    }

    public function test_plot_sales_report_counts_holds_sales_deposits_and_conversion(): void
    {
        $customer = $this->customer('Juma');
        $projectId = RealEstateProject::where('project_category', 'land')->value('id');
        $plot = fn (string $no, string $status) => LandParcel::create([
            'uuid' => Str::uuid(), 'real_estate_project_id' => $projectId, 'parcel_number' => $no, 'block_number' => 'R',
            'status' => $status, 'acreage' => 0.1, 'area_sqm' => 400, 'price' => 5000000,
        ]);

        $sale = Reservation::create([
            'land_parcel_id' => $plot('901', 'Sold')->id, 'customer_id' => $customer->id, 'reservation_number' => 'RESV-SOLD1',
            'deposit_paid' => 1000000, 'reserved_from' => '2019-05-02', 'reserved_until' => '2019-05-20', 'status' => 'Converted',
        ]);
        $sale->forceFill(['created_at' => '2019-05-02 09:00:00', 'updated_at' => '2019-05-10 09:00:00'])->save();
        $hold = Reservation::create([
            'land_parcel_id' => $plot('902', 'Reserved')->id, 'customer_id' => $customer->id, 'reservation_number' => 'RESV-HOLD1',
            'deposit_paid' => 500000, 'reserved_from' => '2019-05-03', 'reserved_until' => '2019-05-25', 'status' => 'Active',
        ]);
        $hold->forceFill(['created_at' => '2019-05-03 09:00:00', 'updated_at' => '2019-05-03 09:00:00'])->save();

        $this->actingAs($this->user('admin@rehospace.com'))->get(route('reports.plot_sales', $this->range('2019-05-01', '2019-05-31')))
            ->assertOk()
            ->assertSee('RESV-SOLD1')
            ->assertSee('RESV-HOLD1')
            ->assertViewHas('stats', fn (array $stats) => $stats['holds'] === 2
                && $stats['sales'] === 1
                && $stats['conversion'] === 50.0
                && $stats['deposits'] === 1500000.0
                && $stats['sales_value'] === 5000000.0);
    }

    public function test_sms_report_shows_success_rate_and_failure_reasons(): void
    {
        $log = function (string $status, ?string $error = null): void {
            CommunicationLog::create([
                'recipient' => '255715111222', 'channel' => 'sms', 'template_code' => 'payment_receipt',
                'body' => 'Tumepokea malipo yako', 'status' => $status, 'error_message' => $error,
            ])->forceFill(['created_at' => '2019-07-05 10:00:00'])->save();
        };
        $log('Sent');
        $log('Sent');
        $log('Sent');
        $log('Failed', 'Insufficient SMS balance');

        $this->actingAs($this->user('admin@rehospace.com'))->get(route('reports.sms', $this->range('2019-07-01', '2019-07-31')))
            ->assertOk()
            ->assertSee('Insufficient SMS balance')
            ->assertViewHas('stats', fn (array $stats) => $stats['total'] === 4 && $stats['failed'] === 1 && $stats['success_rate'] === 75.0);
    }

    public function test_customers_report_lists_new_customers_in_the_period(): void
    {
        $customer = $this->customer('Rehema');
        $customer->forceFill(['city' => 'Dodoma', 'source' => 'Facebook', 'created_at' => '2019-08-03 10:00:00'])->save();

        $this->actingAs($this->user('admin@rehospace.com'))->get(route('reports.customers', $this->range('2019-08-01', '2019-08-31')))
            ->assertOk()
            ->assertSee('Rehema Mushi')
            ->assertSee('Dodoma')
            ->assertViewHas('stats', fn (array $stats) => $stats['new'] === 1);
    }

    public function test_reports_center_shows_only_permitted_reports_and_exports(): void
    {
        $this->actingAs($this->user('admin@rehospace.com'))->get(route('reports.index'))
            ->assertOk()
            ->assertViewHas('headlines')
            ->assertSee(route('reports.finance'), false)
            ->assertSee(route('reports.sms'), false)
            ->assertSee(route('reports.plot_sales'), false)
            ->assertSee(route('reports.customers'), false);

        $agent = $this->user('agent@rehospace.com');
        $this->actingAs($agent)->get(route('reports.index'))
            ->assertOk()
            ->assertSee(route('reports.plot_sales'), false)
            ->assertDontSee(route('reports.finance'), false)
            ->assertDontSee(route('reports.sms'), false);

        $this->actingAs($agent)->get(route('reports.requests'))->assertOk()->assertDontSee('export=csv', false);
        $this->actingAs($agent)->get(route('reports.requests', ['export' => 'csv']))->assertForbidden();
    }
}
