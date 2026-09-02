<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\LoyaltyReward;
use App\Models\LoyaltyRule;
use App\Models\User;
use App\Services\LoyaltyService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CustomerLoyaltyRetentionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        LoyaltyRule::query()->delete();
        LoyaltyService::ensureDefaultRulesExist();
    }

    public function test_loyalty_point_accumulation_and_tier_upgrade(): void
    {
        $customer = Customer::create([
            'first_name' => 'Neema',
            'last_name' => 'Kavishe',
            'phone' => '0755112233',
            'email' => 'neema@example.com',
            'loyalty_points' => 0,
            'loyalty_tier' => 'Bronze Member',
        ]);

        // 1. Process action with 100 points
        LoyaltyService::processCustomerAction($customer, 'plot_reservation', 100, null, 'Test plot reservation');
        $customer->refresh();

        $this->assertEquals(100, $customer->loyalty_points);
        $this->assertEquals('Silver Investor', $customer->loyalty_tier);

        // 2. Add 250 more points -> 350 total -> Gold tier
        LoyaltyService::processCustomerAction($customer, 'survey_booking', 250, null, 'Test survey booking');
        $customer->refresh();

        $this->assertEquals(350, $customer->loyalty_points);
        $this->assertEquals('Gold Estate Holder', $customer->loyalty_tier);

        // 3. Add 700 more points -> 1050 total -> Platinum tier
        LoyaltyService::processCustomerAction($customer, 'plot_purchase', 700, null, 'Test plot purchase');
        $customer->refresh();

        $this->assertEquals(1050, $customer->loyalty_points);
        $this->assertEquals('Platinum Tycoon', $customer->loyalty_tier);
    }

    public function test_voucher_code_generation_and_redemption(): void
    {
        $customer = Customer::create([
            'first_name' => 'Baraka',
            'last_name' => 'Massawe',
            'phone' => '0766223344',
            'email' => 'baraka@example.com',
            'loyalty_points' => 150,
            'loyalty_tier' => 'Silver Investor',
        ]);

        $reward = LoyaltyReward::create([
            'customer_id' => $customer->id,
            'reward_code' => LoyaltyService::generateRewardCode(),
            'reward_name' => '10% Survey Discount',
            'discount_type' => 'percentage',
            'discount_value' => 10.00,
            'points_cost' => 100,
            'expires_at' => now()->addDays(30),
            'status' => 'active',
        ]);

        $this->assertMatchesRegularExpression('/^[A-Z0-9]+-\d{4}-[A-Z0-9]{2}$/', $reward->reward_code);
        $admin = User::first() ?: User::create([
            'name' => 'Staff Cashier',
            'email' => 'cashier@avenix.co.tz',
            'password' => bcrypt('password'),
            'role' => 'Staff',
        ]);

        // Redeem voucher
        $redeemResult = LoyaltyService::redeemRewardCode($reward->reward_code, $admin->id);
        $this->assertEquals('success', $redeemResult['status']);
        $this->assertEquals(10.00, $redeemResult['discount_value']);

        $reward->refresh();
        $this->assertEquals('redeemed', $reward->status);
        $this->assertFalse($reward->isValid());
    }

    public function test_birthday_scheduler_command(): void
    {
        Http::fake([
            'https://pushsms.rehospace.com/api/v1/send' => Http::response(['status' => 'ok', 'id' => 103], 200),
        ]);

        $birthdayCustomer = Customer::create([
            'first_name' => 'Amani',
            'last_name' => 'Massawe',
            'phone' => '0788334455',
            'email' => 'amani@example.com',
            'date_of_birth' => now()->format('1990-m-d'),
            'loyalty_points' => 50,
        ]);

        $this->artisan('loyalty:birthdays')->assertSuccessful();

        $birthdayCustomer->refresh();
        $this->assertEquals(150, $birthdayCustomer->loyalty_points); // 50 initial + 100 birthday bonus
    }

    public function test_admin_loyalty_dashboard_routes(): void
    {
        $admin = User::first() ?: User::create([
            'name' => 'Admin User',
            'email' => 'admin@avenix.co.tz',
            'password' => bcrypt('password'),
            'role' => 'Super Admin',
        ]);

        $response = $this->actingAs($admin)->get(route('loyalty.index'));
        $response->assertStatus(200);
        $response->assertSee('Customer Loyalty & Retention Engine');
        $response->assertSee('Silver Investor');
        $response->assertSee('Gold Estate Holder');
        $response->assertSee('Platinum Tycoon');
    }
}
