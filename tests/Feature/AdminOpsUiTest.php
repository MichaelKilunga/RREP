<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Reservation;
use App\Models\SystemSetting;
use App\Models\User;
use Tests\TestCase;

/**
 * Responsiveness and Tanzania-context upgrades of the staff console
 * operations views (CRM, sales, finance, survey, reports, admin).
 */
class AdminOpsUiTest extends TestCase
{
    protected function admin(): User
    {
        return User::where('email', 'admin@rehospace.com')->firstOrFail();
    }

    public function test_leads_pipeline_shows_stage_tabs_and_a_card_grid_instead_of_a_kanban(): void
    {
        $response = $this->actingAs($this->admin())->get(route('crm.leads'));

        // Stage filter pills with counts replace the sideways-scrolling kanban columns.
        $response->assertDontSee('kanban-board', false);
        $response->assertSee(route('crm.leads', ['stage' => 'Won']), false);
        $response->assertSee('lead-stage-select', false);
        $response->assertSee(route('crm.leads.store'), false);
        $response->assertSee('value="WhatsApp"', false);
        $response->assertSee('value="Property Portal"', false);
        // The log-activity trigger is a labelled journal button, not an SMS-like icon.
        $response->assertSee('bi-journal-plus', false);
        $response->assertDontSee('bi-chat-left-text text-primary', false);

        // Filtering by a stage keeps the page rendering.
        $this->actingAs($this->admin())->get(route('crm.leads', ['stage' => 'Won']))->assertStatus(200);
    }

    public function test_properties_and_land_survey_panels_are_hidden_until_toggled_on(): void
    {
        $dash = $this->actingAs($this->admin())->get(route('dashboard'));
        $dash->assertDontSee('navCollapseProperties', false);
        $dash->assertDontSee('navCollapseSurvey', false);
        $dash->assertSee(route('documents.index'), false); // Documents stays reachable

        // The dashboard itself is project/request-centric while the modules are off.
        $dash->assertSee('Recent Website Requests');
        $dash->assertSee('New Requests');
        $dash->assertDontSee('Recent Properties');
        $dash->assertSee(route('admin.projects.create'), false);

        SystemSetting::setVal('feature_properties_module_enabled', '1');
        SystemSetting::setVal('feature_land_survey_module_enabled', '1');

        $dash = $this->actingAs($this->admin())->get(route('dashboard'));
        $dash->assertSee('navCollapseProperties', false);
        $dash->assertSee('navCollapseSurvey', false);
        $dash->assertSee('Recent Properties');
        $dash->assertDontSee('Recent Website Requests');
    }

    public function test_customers_list_stacks_on_phones_and_offers_whatsapp_and_region_select(): void
    {
        $customer = Customer::first();

        $response = $this->actingAs($this->admin())->get(route('crm.customers'));

        $response->assertSee('table-stack', false);
        $response->assertSee('btn-whatsapp', false);
        $response->assertSee('data-tz-phone', false);
        $response->assertSee('name="tax_number"', false);
        $response->assertSee('<option value="Kigoma"', false);
        $response->assertSee(format_phone_tz($customer->phone));
    }

    public function test_customer_store_persists_tin_number(): void
    {
        $this->actingAs($this->admin())->post(route('crm.customers.store'), [
            'first_name' => 'Zawadi',
            'last_name' => 'Mrema',
            'customer_type' => 'Individual',
            'phone' => '0784 100 200',
            'national_id_passport' => '19900101-12345-00001-23',
            'tax_number' => '123-456-789',
            'city' => 'Kilimanjaro',
        ]);

        $this->assertDatabaseHas('customers', [
            'phone' => '0784 100 200',
            'tax_number' => '123-456-789',
            'city' => 'Kilimanjaro',
        ]);
    }

    public function test_reservations_show_deposit_percentage_and_status_badges(): void
    {
        $reservation = Reservation::with('property')->whereHas('property')->first();

        $response = $this->actingAs($this->admin())->get(route('reservations.index'));

        $response->assertSee('badge-status-', false);
        $response->assertSee('modal-fullscreen-sm-down', false);
        $response->assertSee('<span class="input-group-text">TSh</span>', false);

        if ($reservation && $reservation->property->price > 0) {
            $response->assertSee(format_currency($reservation->deposit_paid));
            $response->assertSee('% of price');
        }
    }

    public function test_payment_modal_lists_tanzanian_mobile_money_methods(): void
    {
        $response = $this->actingAs($this->admin())->get(route('finance.invoices'));

        $response->assertSee('name="payment_method"', false);
        $response->assertSee('value="M-Pesa"', false);
        $response->assertSee('value="Tigo Pesa"', false);
        $response->assertSee('value="Airtel Money"', false);
        $response->assertSee('value="Bank Transfer"', false);
        $response->assertSee('name="reference_number"', false);
    }

    public function test_invoice_sheet_is_print_ready_with_tin_and_payment_instructions(): void
    {
        $invoice = Invoice::with('customer')->firstOrFail();

        $response = $this->actingAs($this->admin())->get(route('finance.invoices.show', $invoice));

        $response->assertSee('invoice-sheet', false);
        $response->assertSee('size: A4', false);
        $response->assertSee('TIN:');
        $response->assertSee('Lipa Namba');
        $response->assertSee('window.print()', false);
        $response->assertSee($invoice->invoice_number);
        $response->assertSee(format_currency($invoice->total_amount, $invoice->currency));
    }

    public function test_reports_use_kpi_cards_and_responsive_chart_wrappers(): void
    {
        $response = $this->actingAs($this->admin())->get(route('reports.sales'));

        $response->assertSee('kpi-card', false);
        $response->assertSee('filter-bar', false);
        $response->assertSee('class="ratio ratio-16x9"', false);
        $response->assertSee('table-stack', false);
        $response->assertSee('Sales Revenue');
    }

    public function test_rbac_matrix_binds_checkboxes_to_each_role_form(): void
    {
        $response = $this->actingAs($this->admin())->get(route('settings.rbac'));

        $response->assertSee('rbac-matrix', false);
        $response->assertSee('form="rbacForm', false);
        $response->assertSee('name="permissions[]"', false);
    }

    public function test_empty_state_renders_when_there_are_no_customers(): void
    {
        Customer::query()->delete();

        $response = $this->actingAs($this->admin())->get(route('crm.customers'));

        $response->assertSee('empty-state', false);
        $response->assertSee('No customers registered yet');
    }

    public function test_ops_pages_render_swahili_copy(): void
    {
        $this->withSession(['locale' => 'sw']);

        $customers = $this->actingAs($this->admin())->get(route('crm.customers'));
        $customers->assertSee('Ongeza Mteja');
        $customers->assertSee('Mkoa');

        $reservations = $this->actingAs($this->admin())->get(route('reservations.index'));
        $reservations->assertSee('Uhifadhi wa Mali');
        // 'Tango' means cucumber in Swahili; the deposit vocabulary is amana/uhifadhi now.
        $reservations->assertDontSee('Tango');
    }
}
