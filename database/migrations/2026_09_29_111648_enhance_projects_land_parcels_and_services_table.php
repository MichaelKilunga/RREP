<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('company_services')) {
            Schema::create('company_services', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('title');
                $table->string('slug')->unique();
                $table->string('category', 50)->default('General');
                $table->text('short_description')->nullable();
                $table->longText('full_description')->nullable();
                $table->json('sub_services')->nullable();
                $table->string('icon')->default('bi-briefcase');
                $table->integer('display_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        Schema::table('real_estate_projects', function (Blueprint $table) {
            if (! Schema::hasColumn('real_estate_projects', 'region')) {
                $table->string('region')->nullable()->after('city');
            }
            if (! Schema::hasColumn('real_estate_projects', 'district')) {
                $table->string('district')->nullable()->after('region');
            }
            if (! Schema::hasColumn('real_estate_projects', 'council')) {
                $table->string('council')->nullable()->after('district');
            }
            if (! Schema::hasColumn('real_estate_projects', 'sold_plots')) {
                $table->integer('sold_plots')->default(0)->after('available_units');
            }
            if (! Schema::hasColumn('real_estate_projects', 'grace_period_days')) {
                $table->integer('grace_period_days')->default(30)->after('sold_plots');
            }
            if (! Schema::hasColumn('real_estate_projects', 'initial_payment_percent')) {
                $table->decimal('initial_payment_percent', 5, 2)->default(50.00)->after('grace_period_days');
            }
            if (! Schema::hasColumn('real_estate_projects', 'shapefile_path')) {
                $table->string('shapefile_path')->nullable()->after('master_plan_image');
            }
            if (! Schema::hasColumn('real_estate_projects', 'geojson_data')) {
                $table->longText('geojson_data')->nullable()->after('shapefile_path');
            }
        });

        Schema::table('land_parcels', function (Blueprint $table) {
            if (! Schema::hasColumn('land_parcels', 'real_estate_project_id')) {
                $table->foreignId('real_estate_project_id')->nullable()->after('property_id')->constrained('real_estate_projects')->nullOnDelete();
            }
        });

        try {
            DB::statement('ALTER TABLE land_parcels MODIFY property_id BIGINT UNSIGNED NULL;');
        } catch (Throwable $e) {
            // Ignore if already nullable or driver unsupported
        }

        Schema::table('land_parcels', function (Blueprint $table) {
            if (! Schema::hasColumn('land_parcels', 'block_number')) {
                $table->string('block_number', 50)->nullable()->after('parcel_number');
            }
            if (! Schema::hasColumn('land_parcels', 'price')) {
                $table->decimal('price', 16, 2)->default(0.00)->after('acreage');
            }
            if (! Schema::hasColumn('land_parcels', 'status')) {
                $table->string('status', 30)->default('Available')->after('price');
            }
            if (! Schema::hasColumn('land_parcels', 'land_use')) {
                $table->string('land_use', 100)->nullable()->after('zoning');
            }
            if (! Schema::hasColumn('land_parcels', 'area_sqm')) {
                $table->decimal('area_sqm', 12, 2)->nullable()->after('land_use');
            }
            if (! Schema::hasColumn('land_parcels', 'region')) {
                $table->string('region')->nullable()->after('area_sqm');
            }
            if (! Schema::hasColumn('land_parcels', 'district')) {
                $table->string('district')->nullable()->after('region');
            }
            if (! Schema::hasColumn('land_parcels', 'council')) {
                $table->string('council')->nullable()->after('district');
            }
            if (! Schema::hasColumn('land_parcels', 'lease_details')) {
                $table->string('lease_details')->nullable()->after('council');
            }
            if (! Schema::hasColumn('land_parcels', 'geojson_geometry')) {
                $table->longText('geojson_geometry')->nullable()->after('boundary_coordinates_json');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_services');
    }
};
