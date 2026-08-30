<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('real_estate_projects', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->nullable()->index();
            $table->foreignId('branch_id')->nullable()->index();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('developer_name');
            $table->string('project_type', 50)->default('Residential Estate'); // Residential Estate, Apartment Complex, Commercial Plaza, Mixed-Use Development, Master-Planned Land
            $table->string('project_status', 30)->default('Selling'); // Upcoming, Pre-Launch, Selling, Under Construction, Completed, Sold Out
            $table->decimal('starting_price', 16, 2)->default(0.00);
            $table->string('currency', 10)->default('TZS');
            $table->string('location_name');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('country')->default('Tanzania');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('total_units')->default(0);
            $table->integer('available_units')->default(0);
            $table->date('launch_date')->nullable();
            $table->date('expected_completion_date')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('master_plan_image')->nullable();
            $table->longText('description')->nullable();
            $table->json('amenities_json')->nullable();
            $table->json('unit_types_json')->nullable();
            $table->json('gallery_images_json')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->nullable()->index();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category', 50)->default('Market Insights'); // Buying Guide, Land Ownership, Land Surveying, Real Estate Investment, Renting Tips, Legal & Title Deeds
            $table->text('excerpt');
            $table->longText('content');
            $table->string('featured_image')->nullable();
            $table->string('author_name')->default('REMS Editorial Team');
            $table->string('author_role')->default('Real Estate Analyst');
            $table->integer('reading_time_minutes')->default(5);
            $table->json('tags_json')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->nullable()->index();
            $table->string('customer_name');
            $table->string('customer_role'); // Property Buyer, Land Investor, Tenant, Commercial Client, Land Survey Client
            $table->string('company')->nullable();
            $table->string('location')->nullable();
            $table->unsignedTinyInteger('rating')->default(5); // 1 to 5
            $table->text('feedback');
            $table->string('photo_url')->nullable();
            $table->boolean('is_featured')->default(true);
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->index();
            $table->string('category', 50)->default('General'); // Buying, Renting, Selling, Land & Plots, Survey & GIS, Verification, Accounts
            $table->string('question');
            $table->text('answer');
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('source', 50)->default('Public Website');
            $table->boolean('is_active')->default(true);
            $table->timestamp('subscribed_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('property_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->nullable()->constrained('properties')->nullOnDelete();
            $table->foreignId('real_estate_project_id')->nullable()->constrained('real_estate_projects')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('inquiry_type', 40)->default('General Inquiry'); // General Inquiry, Viewing Request, Land Survey Request, Valuation Request, Contact Message
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->text('message')->nullable();
            $table->date('preferred_date')->nullable();
            $table->string('preferred_time', 20)->nullable();
            $table->string('preferred_contact_method', 30)->default('WhatsApp'); // WhatsApp, Phone, Email
            $table->string('survey_type', 50)->nullable(); // Boundary Survey, Cadastral Plot Survey, Topographical, Sub-division, Beaconing
            $table->string('approx_land_size', 50)->nullable();
            $table->string('location_text')->nullable();
            $table->string('source', 50)->default('Website');
            $table->string('status', 30)->default('New'); // New, Contacted, In Progress, Closed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_inquiries');
        Schema::dropIfExists('newsletter_subscribers');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('real_estate_projects');
    }
};
