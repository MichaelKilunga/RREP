<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('real_estate_projects', function (Blueprint $table) {
            $table->decimal('application_fee', 14, 2)->nullable()->after('initial_payment_percent');
        });
    }

    public function down(): void
    {
        Schema::table('real_estate_projects', function (Blueprint $table) {
            $table->dropColumn('application_fee');
        });
    }
};
