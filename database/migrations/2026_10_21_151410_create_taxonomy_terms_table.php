<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taxonomy_terms', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->string('group', 60)->index();
            $table->string('name', 120);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->unique(['group', 'name', 'organization_id']);
        });
    }

    public function down(): void
    {
        Schema::drop('taxonomy_terms');
    }
};
