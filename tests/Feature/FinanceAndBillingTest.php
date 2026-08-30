<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Property;
use App\Models\User;
use Tests\TestCase;

class FinanceAndBillingTest extends TestCase
{
    protected function getAdmin(): User
    {
        return User::where('email', 'admin@rehospace.com')->first();
    }

    public function test_invoices_page_renders(): void
    {
        $response = $this->actingAs($this->getAdmin())->get('/finance/invoices');
        $response->assertStatus(200);
        $response->assertSee('Invoices');
    }

    public function test_invoice_creation_and_payment_recording(): void
    {
        $customer = Customer::first();
        $property = Property::first();

        $response = $this->actingAs($this->getAdmin())->post('/finance/invoices', [
            'customer_id' => $customer->id,
            'property_id' => $property->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'items' => [
                [
                    'description' => 'Initial Commitment Fee',
                    'quantity' => 1,
                    'unit_price' => 10000000.00,
                ],
            ],
        ]);

        $invoice = Invoice::latest()->first();
        $this->assertEquals(10000000.00, (float) $invoice->total_amount);
        $this->assertEquals(10000000.00, (float) $invoice->balance_due);

        // Record a payment against this invoice
        $this->actingAs($this->getAdmin())->post('/finance/payments/record', [
            'invoice_id' => $invoice->id,
            'customer_id' => $customer->id,
            'amount' => 10000000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'Bank Transfer',
            'reference_number' => 'TEST-REF-001',
        ]);

        $invoice->refresh();
        $this->assertEquals(0.00, (float) $invoice->balance_due);
        $this->assertEquals('Paid', $invoice->status);
    }
}
