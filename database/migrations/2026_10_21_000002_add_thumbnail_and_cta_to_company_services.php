<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_services', function (Blueprint $table) {
            $table->string('thumbnail')->nullable()->after('icon');
            $table->string('cta_text', 60)->nullable()->after('thumbnail');
            $table->string('cta_url')->nullable()->after('cta_text');
        });
    }

    public function down(): void
    {
        Schema::table('company_services', function (Blueprint $table) {
            $table->dropColumn(['thumbnail', 'cta_text', 'cta_url']);
        });
    }
};
