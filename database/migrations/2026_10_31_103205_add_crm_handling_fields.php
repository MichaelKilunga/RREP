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
            $table->unsignedBigInteger('assigned_to')->nullable()->index()->after('status');
            $table->text('staff_notes')->nullable()->after('assigned_to');
            $table->unsignedBigInteger('lead_id')->nullable()->index()->after('staff_notes');
            $table->timestamp('handled_at')->nullable()->after('lead_id');
        });

        Schema::table('communication_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->index()->after('organization_id');
        });

        // Link past SMS to customers by their normalised phone number.
        $byPhone = [];
        foreach (DB::table('customers')->whereNull('deleted_at')->get(['id', 'phone']) as $customer) {
            $digits = preg_replace('/\D+/', '', (string) $customer->phone);
            if (str_starts_with($digits, '0')) {
                $digits = '255'.substr($digits, 1);
            } elseif (strlen($digits) === 9) {
                $digits = '255'.$digits;
            }
            if ($digits !== '') {
                $byPhone[$digits] = $customer->id;
            }
        }
        DB::table('communication_logs')->whereNull('customer_id')->orderBy('id')->each(function ($log) use ($byPhone) {
            if (isset($byPhone[$log->recipient])) {
                DB::table('communication_logs')->where('id', $log->id)->update(['customer_id' => $byPhone[$log->recipient]]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('property_inquiries', function (Blueprint $table) {
            $table->dropColumn(['assigned_to', 'staff_notes', 'lead_id', 'handled_at']);
        });
        Schema::table('communication_logs', function (Blueprint $table) {
            $table->dropColumn('customer_id');
        });
    }
};
