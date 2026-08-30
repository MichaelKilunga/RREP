<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->nullable()->index();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('employment_status', 50)->nullable();
            $table->string('employer_name')->nullable();
            $table->decimal('monthly_income', 14, 2)->nullable();
            $table->boolean('kyc_verified')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('leases', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->nullable()->index();
            $table->foreignId('branch_id')->nullable()->index();
            $table->string('lease_number', 50)->unique();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('rent_amount', 14, 2);
            $table->decimal('deposit_amount', 14, 2)->default(0.00);
            $table->string('payment_cycle', 30)->default('Monthly'); // Monthly, Quarterly, Semi-Annually, Annually
            $table->longText('terms_conditions')->nullable();
            $table->string('status', 30)->default('Active'); // Draft, Active, Expiring, Expired, Terminated, Renewed
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('rent_schedules', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('lease_id')->constrained()->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->date('due_date');
            $table->decimal('rent_amount', 14, 2);
            $table->decimal('late_fee', 12, 2)->default(0.00);
            $table->decimal('total_due', 14, 2);
            $table->decimal('paid_amount', 14, 2)->default(0.00);
            $table->string('status', 30)->default('Pending'); // Pending, Paid, Overdue
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->nullable()->index();
            $table->foreignId('branch_id')->nullable()->index();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->string('request_number', 50)->unique();
            $table->string('category', 50); // Plumbing, Electrical, Structural, HVAC, Painting, Appliance, Other
            $table->string('priority', 20)->default('Medium'); // Low, Medium, High, Emergency
            $table->string('title');
            $table->text('description');
            $table->json('images_json')->nullable();
            $table->decimal('estimated_cost', 12, 2)->nullable();
            $table->decimal('actual_cost', 12, 2)->nullable();
            $table->string('assigned_contractor')->nullable();
            $table->string('status', 30)->default('Reported'); // Reported, Scheduled, In Progress, Resolved, Closed, Cancelled
            $table->timestamp('reported_at')->useCurrent();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
        Schema::dropIfExists('rent_schedules');
        Schema::dropIfExists('leases');
        Schema::dropIfExists('tenants');
    }
};
