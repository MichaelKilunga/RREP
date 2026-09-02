<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\LoyaltyRule;
use App\Models\SystemSetting;
use App\Services\LoyaltyService;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendBirthdayLoyaltyNotifications extends Command
{
    protected $signature = 'loyalty:birthdays {--force : Force execution regardless of scheduled time}';

    protected $description = 'Send automated birthday wishes and discount vouchers to customers';

    public function handle(): int
    {
        $enabled = SystemSetting::getVal('birthday_notifications_enabled', '1') === '1';
        $force = $this->option('force');

        if (! $enabled && ! $force) {
            $this->info('Birthday notifications are disabled in settings (use --force to override).');

            return 0;
        }

        $today = Carbon::today();
        $customers = Customer::where(function ($q) use ($today) {
            $q->where(function ($sub) use ($today) {
                $sub->where('dob_day', $today->day)
                    ->where('dob_month', $today->month);
            })->orWhere(function ($sub) use ($today) {
                $sub->whereNotNull('date_of_birth')
                    ->whereMonth('date_of_birth', $today->month)
                    ->whereDay('date_of_birth', $today->day);
            });
        })->get();

        if ($customers->isEmpty()) {
            $this->info("No birthdays found for today (Day: {$today->day}, Month: {$today->month}).");

            return 0;
        }

        $companyName = SystemSetting::getVal('company_name', 'Avenix Co Ltd');
        $template = SystemSetting::getVal(
            'sms_template_birthday',
            'Heri ya Siku ya Kuzaliwa {customer_name}! 🎂 Kampuni ya {company_name} inakutakia heri na baraka tele. Tumia zawadi yako ya punguzo {discount} (Kodi: {reward_code}) kwenye ununuzi wa kiwanja au huduma ya survey.'
        );

        $birthdayRule = LoyaltyRule::where('is_active', true)->first() ?: new LoyaltyRule([
            'id' => null,
            'name' => 'Birthday Special',
            'code_prefix' => 'BDAY',
            'discount_type' => 'percentage',
            'discount_value' => 10.00,
            'validity_days' => 30,
        ]);

        $count = 0;
        foreach ($customers as $customer) {
            try {
                // Award birthday bonus points
                LoyaltyService::processCustomerAction($customer, 'birthday_bonus', 100, null, 'Birthday bonus reward');

                // Generate voucher
                $reward = LoyaltyService::issueRewardForCustomer($customer, $birthdayRule, 'Automated Birthday Gift Voucher');

                if ($reward && ! empty($customer->phone)) {
                    $discountStr = $reward->formatted_discount;
                    $msg = str_replace(
                        ['{customer_name}', '{first_name}', '{company_name}', '{discount}', '{reward_code}'],
                        [$customer->full_name, $customer->first_name, $companyName, $discountStr, $reward->reward_code],
                        $template
                    );

                    $sent = SmsService::send($customer->phone, $msg, 'birthday_wish_voucher', $customer->id);
                    if ($sent) {
                        $this->info("📱 Birthday SMS with voucher {$reward->reward_code} sent to {$customer->phone}");
                    } else {
                        $this->warn("⚠️ Birthday SMS failed for {$customer->phone}");
                    }
                }

                $count++;
            } catch (\Throwable $e) {
                Log::error("Failed to send birthday notification to customer #{$customer->id}: ".$e->getMessage());
                $this->error("Error sending to customer #{$customer->id}: ".$e->getMessage());
            }
        }

        $this->info("Processed birthday retention notifications for {$count} customer(s).");

        return 0;
    }
}
