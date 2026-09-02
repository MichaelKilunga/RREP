<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\LoyaltyPointTransaction;
use App\Models\LoyaltyReward;
use App\Models\LoyaltyRule;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LoyaltyService
{
    /**
     * Check if Loyalty feature is enabled.
     */
    public static function isEnabled(): bool
    {
        return SystemSetting::getVal('loyalty_enabled', '1') === '1';
    }

    /**
     * Generate dynamic Reward Code using prefix or company name.
     * Example: "AVENIX-7492-X8"
     */
    public static function generateRewardCode(?string $rulePrefix = null): string
    {
        $prefix = trim((string) $rulePrefix);

        if (empty($prefix)) {
            $globalPrefix = trim((string) SystemSetting::getVal('loyalty_code_prefix', ''));
            if (! empty($globalPrefix)) {
                $prefix = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $globalPrefix));
            }
        }

        if (empty($prefix)) {
            $companyName = (string) SystemSetting::getVal('company_name', 'AVENIX');
            $words = array_filter(explode(' ', trim($companyName)));
            $firstWord = reset($words) ?: 'AVENIX';
            $prefix = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $firstWord));
        }

        if (empty($prefix)) {
            $prefix = 'AVENIX';
        }

        do {
            $randDigits = str_pad((string) mt_rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            $randAlpha = strtoupper(Str::random(2));
            $code = "{$prefix}-{$randDigits}-{$randAlpha}";
        } while (LoyaltyReward::where('reward_code', $code)->exists());

        return $code;
    }

    /**
     * Award points to a customer for actions (plot reservation, survey booking, purchase, site visit).
     */
    public static function processCustomerAction(
        Customer $customer,
        string $actionType = 'plot_reservation',
        ?int $customPoints = null,
        ?int $userId = null,
        ?string $description = null
    ): array {
        if (! self::isEnabled()) {
            return ['status' => 'disabled', 'message' => 'Loyalty system is disabled in settings.'];
        }

        $defaultPoints = match ($actionType) {
            'plot_reservation' => (int) SystemSetting::getVal('loyalty_pts_reservation', '100'),
            'survey_booking' => (int) SystemSetting::getVal('loyalty_pts_survey', '150'),
            'property_purchase' => (int) SystemSetting::getVal('loyalty_pts_purchase', '500'),
            'site_viewing' => (int) SystemSetting::getVal('loyalty_pts_viewing', '25'),
            'birthday_bonus' => (int) SystemSetting::getVal('loyalty_pts_birthday', '100'),
            'referral' => (int) SystemSetting::getVal('loyalty_pts_referral', '200'),
            default => 50,
        };

        $pointsToAdd = $customPoints !== null ? $customPoints : $defaultPoints;
        $desc = $description ?: 'Earned '.$pointsToAdd.' loyalty points for '.str_replace('_', ' ', $actionType);

        $customer->loyalty_points = ($customer->loyalty_points ?? 0) + $pointsToAdd;
        $customer->lifetime_points = ($customer->lifetime_points ?? 0) + max(0, $pointsToAdd);
        $customer->transaction_count = ($customer->transaction_count ?? 0) + 1;
        $customer->last_interaction_at = now();
        $customer->save();

        // Create transaction log
        LoyaltyPointTransaction::create([
            'customer_id' => $customer->id,
            'type' => $actionType,
            'points' => $pointsToAdd,
            'transactions_delta' => 1,
            'description' => $desc,
            'created_by' => $userId,
        ]);

        // Evaluate for tier upgrades and unlocked rewards
        $issuedRewards = self::evaluateAndRewardCustomer($customer);

        return [
            'status' => 'success',
            'points_earned' => $pointsToAdd,
            'new_points' => $customer->loyalty_points,
            'new_tier' => $customer->loyalty_tier,
            'rewards_issued' => count($issuedRewards),
        ];
    }

    /**
     * Evaluate customer against active loyalty rules and issue reward vouchers.
     */
    public static function evaluateAndRewardCustomer(Customer $customer): array
    {
        $activeRules = LoyaltyRule::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('min_points', 'desc')
            ->orderBy('min_transactions', 'desc')
            ->get();

        if ($activeRules->isEmpty()) {
            self::seedDefaultRules();
            $activeRules = LoyaltyRule::where('is_active', true)->orderBy('min_points', 'desc')->get();
        }

        $issued = [];

        foreach ($activeRules as $rule) {
            $meetsPoints = $rule->min_points > 0 && ($customer->loyalty_points >= $rule->min_points || $customer->lifetime_points >= $rule->min_points);
            $meetsTrans = $rule->min_transactions > 0 && $customer->transaction_count >= $rule->min_transactions;

            if ($meetsPoints || $meetsTrans) {
                // Update customer tier to rule name
                if ($customer->loyalty_tier !== $rule->name) {
                    $customer->update(['loyalty_tier' => $rule->name]);
                }

                // Check if customer already received a reward for this specific rule
                $alreadyRewarded = LoyaltyReward::where('customer_id', $customer->id)
                    ->where('loyalty_rule_id', $rule->id)
                    ->exists();

                if (! $alreadyRewarded) {
                    $reward = self::issueRewardForCustomer($customer, $rule);
                    if ($reward) {
                        $issued[] = $reward;
                    }
                }
            }
        }

        return $issued;
    }

    /**
     * Issue a reward voucher for a customer.
     */
    public static function issueRewardForCustomer(Customer $customer, LoyaltyRule $rule, ?string $notes = null): ?LoyaltyReward
    {
        $code = self::generateRewardCode($rule->code_prefix);
        $validityDays = $rule->validity_days > 0 ? $rule->validity_days : (int) SystemSetting::getVal('loyalty_default_validity_days', '60');
        $expiresAt = now()->addDays($validityDays);

        $reward = LoyaltyReward::create([
            'customer_id' => $customer->id,
            'loyalty_rule_id' => $rule->id,
            'reward_code' => $code,
            'reward_name' => $rule->name.' Reward Voucher',
            'discount_type' => $rule->discount_type,
            'discount_value' => $rule->discount_value,
            'points_at_issuance' => $customer->loyalty_points,
            'status' => 'active',
            'issued_at' => now(),
            'expires_at' => $expiresAt,
            'notes' => $notes ?: "Unlocked upon reaching {$customer->loyalty_points} loyalty points / {$customer->transaction_count} transactions.",
        ]);

        // Dispatch SMS notification if enabled
        $autoSms = SystemSetting::getVal('loyalty_auto_sms_enabled', '1') === '1';
        if ($autoSms && ! empty($customer->phone)) {
            $smsSuccess = self::dispatchRewardSms($customer, $reward, $rule);
            $reward->update([
                'sms_sent' => $smsSuccess,
                'sms_status' => $smsSuccess ? 'delivered' : 'failed',
            ]);
        }

        return $reward;
    }

    /**
     * Send Loyalty Reward SMS to customer.
     */
    public static function dispatchRewardSms(Customer $customer, LoyaltyReward $reward, ?LoyaltyRule $rule = null): bool
    {
        if (empty($customer->phone)) {
            return false;
        }

        $template = null;
        if ($rule && ! empty($rule->sms_template)) {
            $template = $rule->sms_template;
        }

        if (empty($template)) {
            $template = (string) SystemSetting::getVal(
                'loyalty_sms_template',
                'Habari {customer_name}, asante kwa kuwa mteja mwaminifu wa {company_name}! Umepata punguzo la {discount}. Tumia nambari ya siri: {reward_code} (Inatumika hadi {expiry_date}).'
            );
        }

        $message = self::formatSmsTemplate($template, $customer, $reward);

        try {
            return SmsService::send($customer->phone, $message, 'loyalty_reward_unlocked', $customer->id);
        } catch (\Throwable $e) {
            Log::error("Failed to send Loyalty SMS to customer #{$customer->id}: ".$e->getMessage());

            return false;
        }
    }

    /**
     * Format SMS template placeholders.
     */
    public static function formatSmsTemplate(string $template, Customer $customer, LoyaltyReward $reward): string
    {
        $companyName = (string) SystemSetting::getVal('company_name', config('app.name', 'Avenix Co Ltd'));

        $replacements = [
            '{customer_name}' => $customer->full_name ?: 'Mteja',
            '{first_name}' => $customer->first_name ?: 'Mteja',
            '{points_balance}' => (string) $customer->loyalty_points,
            '{tier_name}' => (string) $customer->loyalty_tier,
            '{reward_code}' => $reward->reward_code,
            '{discount}' => $reward->formatted_discount,
            '{reward_name}' => $reward->reward_name,
            '{expiry_date}' => $reward->expires_at ? $reward->expires_at->format('Y-m-d') : 'N/A',
            '{company_name}' => $companyName,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Redeem a reward voucher code against an invoice or direct transaction.
     */
    public static function redeemRewardCode(string $code, ?int $userId = null, ?int $invoiceId = null): array
    {
        $code = strtoupper(trim($code));
        $reward = LoyaltyReward::with('customer')->where('reward_code', $code)->first();

        if (! $reward) {
            return ['status' => 'error', 'message' => "Reward code '{$code}' not found."];
        }

        if ($reward->status === 'redeemed') {
            $redeemerName = $reward->redeemer ? $reward->redeemer->name : 'Staff';

            return [
                'status' => 'error',
                'message' => "Reward code '{$code}' was already redeemed on ".($reward->redeemed_at ? $reward->redeemed_at->format('Y-m-d H:i') : '')." by {$redeemerName}.",
            ];
        }

        if ($reward->status === 'cancelled') {
            return ['status' => 'error', 'message' => "Reward code '{$code}' has been cancelled."];
        }

        if ($reward->is_expired) {
            $reward->update(['status' => 'expired']);

            return ['status' => 'error', 'message' => "Reward code '{$code}' expired on ".($reward->expires_at ? $reward->expires_at->format('Y-m-d') : '').'.'];
        }

        $reward->update([
            'status' => 'redeemed',
            'redeemed_at' => now(),
            'redeemed_by' => $userId,
            'invoice_id' => $invoiceId,
        ]);

        return [
            'status' => 'success',
            'message' => "Reward code '{$code}' redeemed successfully! Applied {$reward->formatted_discount}.",
            'customer_name' => $reward->customer->full_name,
            'discount' => $reward->formatted_discount,
            'discount_type' => $reward->discount_type,
            'discount_value' => $reward->discount_value,
            'reward_code' => $reward->reward_code,
        ];
    }

    /**
     * Scan all customers and issue rewards for eligible accounts.
     */
    public static function scanAndDispatchRewardsAll(): array
    {
        if (! self::isEnabled()) {
            return ['status' => 'error', 'message' => 'Loyalty system is disabled in settings.'];
        }

        $customers = Customer::where('loyalty_points', '>', 0)
            ->orWhere('transaction_count', '>', 0)
            ->get();

        $totalEvaluated = 0;
        $totalIssued = 0;

        foreach ($customers as $customer) {
            $totalEvaluated++;
            $issued = self::evaluateAndRewardCustomer($customer);
            $totalIssued += count($issued);
        }

        return [
            'status' => 'success',
            'customers_scanned' => $totalEvaluated,
            'rewards_issued' => $totalIssued,
            'message' => "Scanned {$totalEvaluated} customer(s). Issued {$totalIssued} new reward code(s).",
        ];
    }

    /**
     * Ensure default rules exist.
     */
    public static function ensureDefaultRulesExist(): void
    {
        self::seedDefaultRules();
    }

    /**
     * Seed initial sensible rules for Real Estate & Surveying.
     */
    public static function seedDefaultRules(): void
    {
        if (LoyaltyRule::count() === 0) {
            LoyaltyRule::create([
                'name' => 'Silver Investor',
                'code_prefix' => 'AVENIX',
                'min_points' => 100,
                'min_transactions' => 1,
                'discount_type' => 'percentage',
                'discount_value' => 5.00,
                'validity_days' => 60,
                'sms_template' => 'Hongera {customer_name}! Umepata daraja la Silver Investor kwa {company_name}. Punguzo lako: {discount} kwa kutumia kodi: {reward_code} (Hadi {expiry_date}).',
                'is_active' => true,
                'sort_order' => 1,
            ]);

            LoyaltyRule::create([
                'name' => 'Gold Estate Holder',
                'code_prefix' => 'AVENIX',
                'min_points' => 300,
                'min_transactions' => 3,
                'discount_type' => 'percentage',
                'discount_value' => 10.00,
                'validity_days' => 90,
                'sms_template' => 'Hongera {customer_name}! Umepandishwa hadhi kuwa Gold Estate Holder. Punguzo lako maalum: {discount} kwa huduma/viwanja. Kodi: {reward_code} (Hadi {expiry_date}). - {company_name}',
                'is_active' => true,
                'sort_order' => 2,
            ]);

            LoyaltyRule::create([
                'name' => 'Platinum Tycoon',
                'code_prefix' => 'AVENIX',
                'min_points' => 800,
                'min_transactions' => 5,
                'discount_type' => 'percentage',
                'discount_value' => 15.00,
                'validity_days' => 120,
                'sms_template' => 'Heshima kwako VIP {customer_name}! Umefikia hadhi ya juu ya Platinum Tycoon. Punguzo lako: {discount}. Tumia kodi: {reward_code} (Hadi {expiry_date}). - {company_name}',
                'is_active' => true,
                'sort_order' => 3,
            ]);
        }
    }
}
