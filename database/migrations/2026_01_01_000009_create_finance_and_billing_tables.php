<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->nullable()->index();
            $table->foreignId('branch_id')->nullable()->index();
            $table->string('invoice_number', 50)->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_deal_id')->nullable()->constrained('sales_deals')->nullOnDelete();
            $table->foreignId('property_id')->nullable()->constrained('properties')->nullOnDelete();
            $table->date('issue_date');
            $table->date('due_date');
            $table->decimal('subtotal', 16, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 14, 2)->default(0.00);
            $table->decimal('discount_amount', 14, 2)->default(0.00);
            $table->decimal('total_amount', 16, 2)->default(0.00);
            $table->decimal('paid_amount', 16, 2)->default(0.00);
            $table->decimal('balance_due', 16, 2)->default(0.00);
            $table->string('currency', 10)->default('TZS');
            $table->string('status', 30)->default('Issued'); // Draft, Issued, Partially Paid, Paid, Overdue, Cancelled
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('quantity', 8, 2)->default(1.00);
            $table->decimal('unit_price', 14, 2);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('total_amount', 14, 2);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->nullable()->index();
            $table->foreignId('branch_id')->nullable()->index();
            $table->string('payment_number', 50)->unique();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 16, 2);
            $table->string('currency', 10)->default('TZS');
            $table->date('payment_date');
            $table->string('payment_method', 50)->default('Bank Transfer'); // Cash, Bank Transfer, Mobile Money, Cheque, Credit Card
            $table->string('reference_number')->nullable();
            $table->string('status', 30)->default('Completed'); // Pending, Completed, Failed, Refunded
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable();
            $table->timestamps();
        });

        Schema::create('installment_schedules', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('sales_deal_id')->constrained('sales_deals')->cascadeOnDelete();
            $table->integer('installment_number');
            $table->string('title')->nullable(); // e.g. "Down Payment", "Milestone 1 (Foundation)", "Final Balance"
            $table->date('due_date');
            $table->decimal('amount', 16, 2);
            $table->decimal('paid_amount', 16, 2)->default(0.00);
            $table->decimal('penalty_amount', 14, 2)->default(0.00);
            $table->string('status', 30)->default('Pending'); // Pending, Partially Paid, Paid, Overdue
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->nullable()->index();
            $table->foreignId('branch_id')->nullable()->index();
            $table->foreignId('property_id')->nullable()->constrained('properties')->nullOnDelete();
            $table->string('expense_number', 50)->unique();
            $table->string('category', 50); // Maintenance, Utilities, Legal, Marketing, Commission, Administrative, Tax, Other
            $table->string('title');
            $table->decimal('amount', 14, 2);
            $table->string('currency', 10)->default('TZS');
            $table->date('expense_date');
            $table->string('payee')->nullable();
            $table->string('payment_method', 50)->default('Bank Transfer');
            $table->foreignId('receipt_media_id')->nullable()->constrained('media_files')->nullOnDelete();
            $table->string('status', 30)->default('Approved'); // Pending, Approved, Paid, Rejected
            $table->foreignId('approved_by')->nullable();
            $table->foreignId('recorded_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('agent_commissions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->nullable()->index();
            $table->foreignId('sales_deal_id')->constrained('sales_deals')->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->decimal('deal_amount', 16, 2);
            $table->decimal('commission_rate', 5, 2);
            $table->decimal('total_commission', 14, 2);
            $table->decimal('paid_amount', 14, 2)->default(0.00);
            $table->decimal('balance_due', 14, 2)->default(0.00);
            $table->string('status', 30)->default('Pending'); // Pending, Approved, Partially Paid, Paid
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_commissions');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('installment_schedules');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
