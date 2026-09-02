<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_projects', function (Blueprint $table) {
            if (! Schema::hasColumn('survey_projects', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('property_id')->constrained('customers')->nullOnDelete();
                $table->string('survey_type', 50)->default('Cadastral Survey')->after('project_name');
                $table->decimal('latitude', 10, 8)->nullable()->after('location_name');
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
                $table->decimal('estimated_cost', 14, 2)->default(0.00)->after('total_area');
                $table->string('invoice_number', 50)->nullable()->after('estimated_cost');
                $table->text('client_notes')->nullable()->after('description');
            }
        });

        Schema::table('properties', function (Blueprint $table) {
            if (! Schema::hasColumn('properties', 'submission_status')) {
                $table->string('submission_status', 30)->default('Published')->after('status'); // Draft, Under Review, Approved, Rejected, Published
                $table->text('admin_review_notes')->nullable()->after('submission_status');
                $table->string('deed_document_url')->nullable()->after('virtual_tour_url');
                $table->string('survey_blueprint_url')->nullable()->after('deed_document_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('survey_projects', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn(['customer_id', 'survey_type', 'latitude', 'longitude', 'estimated_cost', 'invoice_number', 'client_notes']);
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['submission_status', 'admin_review_notes', 'deed_document_url', 'survey_blueprint_url']);
        });
    }
};
