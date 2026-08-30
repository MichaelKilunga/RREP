<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->string('key')->index();
            $table->text('value')->nullable();
            $table->string('group', 50)->default('general'); // general, email, sms, payment, ai, gis, security
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->unique(['organization_id', 'key']);
        });

        Schema::create('branding_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('primary_color', 20)->default('#0d6efd');
            $table->string('secondary_color', 20)->default('#6c757d');
            $table->string('accent_color', 20)->default('#0dcaf0');
            $table->string('dark_color', 20)->default('#212529');
            $table->string('light_color', 20)->default('#f8f9fa');
            $table->string('sidebar_theme', 20)->default('dark'); // dark, light
            $table->string('header_logo')->nullable();
            $table->string('favicon')->nullable();
            $table->text('custom_css')->nullable();
            $table->string('company_tagline')->nullable();
            $table->timestamps();
        });

        Schema::create('licensed_modules', function (Blueprint $table) {
            $table->id();
            $table->string('module_code', 20)->unique(); // FM-001, BM-001, etc.
            $table->string('module_name');
            $table->string('module_slug', 50)->unique();
            $table->string('category', 30); // Foundation, Core Business, Growth, Intelligence
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_core')->default(false);
            $table->string('version', 20)->default('1.0.0');
            $table->string('license_tier', 30)->default('Enterprise'); // Standard, Professional, Enterprise
            $table->json('settings_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licensed_modules');
        Schema::dropIfExists('branding_configs');
        Schema::dropIfExists('system_settings');
    }
};
