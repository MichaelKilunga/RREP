<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_projects', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->nullable()->index();
            $table->foreignId('branch_id')->nullable()->index();
            $table->string('project_code', 50)->unique();
            $table->string('project_name');
            $table->foreignId('land_parcel_id')->nullable()->constrained('land_parcels')->nullOnDelete();
            $table->foreignId('property_id')->nullable()->constrained('properties')->nullOnDelete();
            $table->string('location_name');
            $table->decimal('total_area', 12, 4)->nullable();
            $table->string('area_unit', 20)->default('Acres'); // Acres, Hectares, Sqm
            $table->foreignId('lead_surveyor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('surveyor_license_number')->nullable();
            $table->string('status', 40)->default('Planning'); // Planning, Fieldwork, Beaconing, Computations, Verification, Approved, Completed
            $table->date('start_date')->nullable();
            $table->date('expected_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            $table->longText('boundary_geojson')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('survey_beacons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_project_id')->constrained()->cascadeOnDelete();
            $table->string('beacon_number', 50); // e.g. "B1", "CP-104", "TP-1"
            $table->decimal('latitude', 11, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('northing', 14, 4)->nullable();
            $table->decimal('easting', 14, 4)->nullable();
            $table->decimal('elevation', 8, 2)->nullable();
            $table->string('beacon_type', 50)->default('Concrete Pillar'); // Concrete Pillar, Iron Pin, Stone, GPS Monument
            $table->string('condition', 30)->default('Good'); // Good, Damaged, Replaced, Missing
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('survey_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_project_id')->constrained()->cascadeOnDelete();
            $table->string('milestone_name');
            $table->integer('sequence')->default(1);
            $table->string('status', 30)->default('Pending'); // Pending, In Progress, Completed
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_milestones');
        Schema::dropIfExists('survey_beacons');
        Schema::dropIfExists('survey_projects');
    }
};
