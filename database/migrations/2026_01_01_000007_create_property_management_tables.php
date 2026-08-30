<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category', 50); // Residential, Commercial, Industrial, Land, Agricultural, Mixed
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('amenities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category', 50)->default('General'); // Interior, Exterior, Security, Utilities, Community
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        Schema::create('property_owners', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('national_id')->nullable();
            $table->string('tax_pin')->nullable();
            $table->string('address')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('kyc_status', 30)->default('Pending'); // Pending, Verified, Rejected
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->nullable()->index();
            $table->foreignId('branch_id')->nullable()->index();
            $table->foreignId('property_type_id')->constrained();
            $table->foreignId('property_owner_id')->nullable()->constrained('property_owners')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('property_code', 50)->unique();
            $table->string('listing_type', 30)->default('Sale'); // Sale, Rent, Lease, Joint Venture
            $table->string('status', 30)->default('Available'); // Available, Reserved, Under Contract, Sold, Leased, Maintenance, Off Market
            $table->decimal('price', 16, 2)->default(0.00);
            $table->decimal('rent_price', 16, 2)->nullable();
            $table->string('rent_period', 20)->default('Monthly'); // Monthly, Quarterly, Annually
            $table->decimal('deposit_amount', 16, 2)->nullable();
            $table->string('currency', 10)->default('TZS');
            $table->string('address');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('country')->default('Tanzania');
            $table->string('postal_code', 20)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('area_size', 12, 2)->nullable();
            $table->string('area_unit', 20)->default('Sqm'); // Sqm, Sqft, Acres, Hectares
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->integer('floors')->default(1);
            $table->integer('parking_spaces')->nullable();
            $table->integer('year_built')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->longText('description')->nullable();
            $table->string('video_url')->nullable();
            $table->string('virtual_tour_url')->nullable();
            $table->json('features_json')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('property_units', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('unit_number', 50);
            $table->string('floor_number', 20)->nullable();
            $table->string('unit_type', 50)->nullable(); // 1BHK, 2BHK, Studio, Penthouse, Shop, Office
            $table->decimal('size', 10, 2)->nullable();
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->decimal('rent_amount', 14, 2)->nullable();
            $table->decimal('sale_price', 16, 2)->nullable();
            $table->string('status', 30)->default('Available'); // Available, Reserved, Leased, Sold, Maintenance
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['property_id', 'unit_number']);
        });

        Schema::create('land_parcels', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('parcel_number', 100);
            $table->string('deed_number', 100)->nullable();
            $table->string('survey_plan_number', 100)->nullable();
            $table->string('title_deed_type', 50)->default('Freehold'); // Freehold, Leasehold, Customary, Right of Occupancy
            $table->decimal('acreage', 10, 4);
            $table->integer('tenure_years_remaining')->nullable();
            $table->string('zoning', 50)->nullable(); // Residential, Commercial, Agricultural, Industrial
            $table->string('topography', 50)->nullable(); // Flat, Gentle Slope, Hillside
            $table->string('soil_type', 50)->nullable(); // Loam, Sandy, Clay, Volcanic
            $table->json('boundary_coordinates_json')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('property_amenity', function (Blueprint $table) {
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('amenity_id')->constrained()->cascadeOnDelete();
            $table->primary(['property_id', 'amenity_id']);
        });

        Schema::create('property_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_file_id')->constrained('media_files')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->integer('display_order')->default(0);
            $table->string('caption')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_media');
        Schema::dropIfExists('property_amenity');
        Schema::dropIfExists('land_parcels');
        Schema::dropIfExists('property_units');
        Schema::dropIfExists('properties');
        Schema::dropIfExists('property_owners');
        Schema::dropIfExists('amenities');
        Schema::dropIfExists('property_types');
    }
};
