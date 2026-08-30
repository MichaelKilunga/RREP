<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->nullable()->index();
            $table->foreignId('branch_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('company_name')->nullable();
            $table->string('customer_type', 30)->default('Individual'); // Individual, Corporate
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('alt_phone')->nullable();
            $table->string('national_id_passport')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('Tanzania');
            $table->string('kyc_status', 30)->default('Pending'); // Pending, Verified, Rejected
            $table->string('source', 50)->default('Direct'); // Website, Walk-in, Referral, Facebook, Instagram, Google
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->nullable()->index();
            $table->foreignId('branch_id')->nullable()->index();
            $table->string('license_number')->nullable();
            $table->string('designation')->default('Sales Agent'); // Senior Broker, Sales Executive, Junior Agent
            $table->decimal('commission_rate', 5, 2)->default(5.00); // 5.00%
            $table->decimal('total_sales_volume', 16, 2)->default(0.00);
            $table->integer('active_deals_count')->default(0);
            $table->date('hire_date')->nullable();
            $table->string('status', 30)->default('Active'); // Active, On Leave, Inactive
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->nullable()->index();
            $table->foreignId('branch_id')->nullable()->index();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('source', 50)->default('Website');
            $table->string('stage', 30)->default('New'); // New, Contacted, Qualified, Viewing, Proposal, Negotiation, Won, Lost
            $table->string('priority', 20)->default('Medium'); // Low, Medium, High, Urgent
            $table->decimal('estimated_value', 16, 2)->nullable();
            $table->foreignId('assigned_agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->foreignId('property_interest_id')->nullable()->constrained('properties')->nullOnDelete();
            $table->text('lost_reason')->nullable();
            $table->timestamp('next_followup_at')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->string('activity_type', 30); // Call, Email, Meeting, Note, Site Visit, WhatsApp, SMS
            $table->string('summary');
            $table->text('details')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->nullable()->index();
            $table->foreignId('branch_id')->nullable()->index();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->string('reservation_number', 50)->unique();
            $table->decimal('reservation_fee', 14, 2)->default(0.00);
            $table->decimal('deposit_paid', 14, 2)->default(0.00);
            $table->date('reserved_from');
            $table->date('reserved_until');
            $table->string('status', 30)->default('Active'); // Active, Expired, Converted, Cancelled
            $table->text('cancellation_reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->nullable()->index();
            $table->foreignId('branch_id')->nullable()->index();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->string('appointment_number', 50)->unique();
            $table->dateTime('scheduled_at');
            $table->integer('duration_minutes')->default(60);
            $table->string('meeting_type', 40)->default('Site Viewing'); // Site Viewing, Office Consultation, Virtual Tour
            $table->string('status', 30)->default('Pending'); // Pending, Confirmed, Completed, Cancelled, Rescheduled, No Show
            $table->text('notes')->nullable();
            $table->integer('feedback_score')->nullable(); // 1 to 5
            $table->text('feedback_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sales_deals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->nullable()->index();
            $table->foreignId('branch_id')->nullable()->index();
            $table->string('deal_number', 50)->unique();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->decimal('sale_price', 16, 2);
            $table->string('payment_plan_type', 30)->default('Outright'); // Outright, Installment, Mortgage
            $table->integer('total_installments')->default(1);
            $table->date('agreement_date');
            $table->date('closing_date')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(5.00);
            $table->decimal('commission_amount', 14, 2)->default(0.00);
            $table->string('status', 30)->default('Active'); // Draft, Pending Approval, Active, Completed, Defaulted, Terminated
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_deals');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('lead_activities');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('agents');
        Schema::dropIfExists('customers');
    }
};
