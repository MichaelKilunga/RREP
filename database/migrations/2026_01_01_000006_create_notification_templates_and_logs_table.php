<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique(); // lead_assigned, viewing_scheduled, invoice_issued, payment_received, etc.
            $table->string('name');
            $table->string('channel', 30); // email, sms, whatsapp, push, in_app
            $table->string('subject')->nullable();
            $table->text('body');
            $table->json('variables_json')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('communication_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->nullable()->index();
            $table->string('recipient');
            $table->string('channel', 30); // email, sms, whatsapp, push
            $table->string('template_code', 50)->nullable()->index();
            $table->string('subject')->nullable();
            $table->text('body');
            $table->string('status', 30)->default('Queued'); // Queued, Sent, Delivered, Failed
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_logs');
        Schema::dropIfExists('notification_templates');
    }
};
