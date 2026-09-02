<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Extend customers table with loyalty and birthday retention fields
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'loyalty_points')) {
                $table->integer('loyalty_points')->default(0)->after('notes');
                $table->integer('lifetime_points')->default(0)->after('loyalty_points');
                $table->string('loyalty_tier', 50)->default('Bronze Member')->after('lifetime_points');
                $table->integer('transaction_count')->default(0)->after('loyalty_tier');
                $table->date('date_of_birth')->nullable()->after('transaction_count');
                $table->unsignedTinyInteger('dob_day')->nullable()->after('date_of_birth');
                $table->unsignedTinyInteger('dob_month')->nullable()->after('dob_day');
                $table->timestamp('last_interaction_at')->nullable()->after('dob_month');
            }
        });

        // 2. Loyalty Rules Table (Dynamic tier & reward rules configured in admin)
        Schema::create('loyalty_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Silver Investor", "Gold Estate Holder", "Platinum VIP"
            $table->string('code_prefix', 20)->default('AVENIX');
            $table->integer('min_points')->default(0);
            $table->integer('min_transactions')->default(0);
            $table->string('discount_type', 20)->default('percentage'); // percentage, fixed
            $table->decimal('discount_value', 14, 2)->default(5.00);
            $table->integer('validity_days')->default(60);
            $table->text('sms_template')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(1);
            $table->timestamps();
        });

        // 3. Loyalty Reward Vouchers Table
        Schema::create('loyalty_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('loyalty_rule_id')->nullable()->constrained('loyalty_rules')->nullOnDelete();
            $table->string('reward_code', 50)->unique();
            $table->string('reward_name');
            $table->string('discount_type', 20)->default('percentage');
            $table->decimal('discount_value', 14, 2)->default(0.00);
            $table->integer('points_at_issuance')->default(0);
            $table->string('status', 30)->default('active'); // active, redeemed, expired, cancelled
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->foreignId('redeemed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->boolean('sms_sent')->default(false);
            $table->string('sms_status', 30)->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 4. Loyalty Point Transactions Ledger
        Schema::create('loyalty_point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50); // plot_reservation, survey_booking, property_purchase, birthday_bonus, manual_adjustment, referral
            $table->integer('points')->default(0); // positive or negative
            $table->integer('transactions_delta')->default(0);
            $table->string('description');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_point_transactions');
        Schema::dropIfExists('loyalty_rewards');
        Schema::dropIfExists('loyalty_rules');

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'loyalty_points',
                'lifetime_points',
                'loyalty_tier',
                'transaction_count',
                'date_of_birth',
                'dob_day',
                'dob_month',
                'last_interaction_at',
            ]);
        });
    }
};
