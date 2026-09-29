<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('newsletter_subscribers')) {
            Schema::table('newsletter_subscribers', function (Blueprint $table) {
                if (! Schema::hasColumn('newsletter_subscribers', 'phone')) {
                    $table->string('phone', 50)->nullable()->after('id');
                }
                if (Schema::hasColumn('newsletter_subscribers', 'email')) {
                    $table->string('email')->nullable()->change();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('newsletter_subscribers')) {
            Schema::table('newsletter_subscribers', function (Blueprint $table) {
                if (Schema::hasColumn('newsletter_subscribers', 'phone')) {
                    $table->dropColumn('phone');
                }
            });
        }
    }
};
