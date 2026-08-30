<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_campaigns', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->nullable()->index();
            $table->foreignId('branch_id')->nullable()->index();
            $table->string('campaign_code', 50)->unique();
            $table->string('name');
            $table->string('campaign_type', 50); // Social Media, Email Blast, SMS Blast, Billboard, Exhibition, Radio/TV, Print
            $table->string('target_audience')->nullable();
            $table->decimal('budget', 14, 2)->default(0.00);
            $table->decimal('spent_amount', 14, 2)->default(0.00);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedInteger('leads_generated')->default(0);
            $table->unsignedInteger('conversions_count')->default(0);
            $table->string('status', 30)->default('Draft'); // Draft, Active, Paused, Completed, Cancelled
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('workflow_approvals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->nullable()->index();
            $table->string('approvable_type');
            $table->unsignedBigInteger('approvable_id');
            $table->string('workflow_type', 50); // Discount, Reservation_Cancel, Commission_Payout, Lease_Termination, Survey_Approval, Expense_Approval
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('current_approver_role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->foreignId('current_approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('step_number')->default(1);
            $table->integer('total_steps')->default(1);
            $table->string('status', 30)->default('Pending'); // Pending, Approved, Rejected
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('approval_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_approval_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->string('action', 30); // Approved, Rejected, Reassigned, Commented
            $table->text('comments')->nullable();
            $table->timestamp('action_at')->useCurrent();
        });

        Schema::create('ai_chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->nullable()->index();
            $table->string('session_title')->default('New Conversation');
            $table->timestamps();
        });

        Schema::create('ai_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_chat_session_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('prompt');
            $table->longText('response');
            $table->string('model', 50)->default('gemini-1.5-flash');
            $table->integer('tokens_used')->default(0);
            $table->string('feature', 50)->default('Chat'); // Chat, Description_Generation, Valuation_Estimate, Smart_Search
            $table->json('metadata_json')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_interactions');
        Schema::dropIfExists('ai_chat_sessions');
        Schema::dropIfExists('approval_logs');
        Schema::dropIfExists('workflow_approvals');
        Schema::dropIfExists('marketing_campaigns');
    }
};
