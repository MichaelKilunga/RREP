<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_inquiries', function (Blueprint $table) {
            $table->unsignedBigInteger('company_service_id')->nullable()->index()->after('real_estate_project_id');
        });

        // Backfill: quote requests carry the service title as a "[Title] ..." message prefix.
        $services = DB::table('company_services')->pluck('id', 'title');
        DB::table('property_inquiries')
            ->where('inquiry_type', 'Service Quote')
            ->whereNull('company_service_id')
            ->orderBy('id')
            ->each(function ($inquiry) use ($services) {
                if (preg_match('/^\[(.+?)\]/u', (string) $inquiry->message, $m) && isset($services[$m[1]])) {
                    DB::table('property_inquiries')->where('id', $inquiry->id)->update(['company_service_id' => $services[$m[1]]]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('property_inquiries', function (Blueprint $table) {
            $table->dropColumn('company_service_id');
        });
    }
};
