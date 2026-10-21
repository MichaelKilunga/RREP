<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('real_estate_projects', function (Blueprint $table) {
            $table->string('project_category', 20)->default('land')->index()->after('project_type'); // land | service
            $table->foreignId('company_service_id')->nullable()->after('project_category')->constrained('company_services')->nullOnDelete();
            $table->string('client_name')->nullable()->after('company_service_id');
        });
    }

    public function down(): void
    {
        Schema::table('real_estate_projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_service_id');
            $table->dropColumn(['project_category', 'client_name']);
        });
    }
};
