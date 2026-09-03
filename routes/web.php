<?php

use App\Http\Controllers\AIController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ComplianceController;
use App\Http\Controllers\CRMController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\LoyaltyController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PublicWebsiteController;
use App\Http\Controllers\RBACController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SalesDealController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SurveyGISController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkflowController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| RehoSpace Real Estate Platform (RREP) Web Routes
|--------------------------------------------------------------------------
*/

// Dynamic XML Sitemap for Google & Search Engine Indexing
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('public.sitemap');

// Authentication Routes
Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('login.post');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// Public Website & Marketplace Routes (SEO & Conversion Optimized)
Route::get('/', [PublicWebsiteController::class, 'home'])->name('public.home');
Route::get('/properties', [PublicWebsiteController::class, 'properties'])->name('public.properties');
Route::get('/buy', [PublicWebsiteController::class, 'buy'])->name('public.buy');
Route::get('/rent', [PublicWebsiteController::class, 'rent'])->name('public.rent');
Route::get('/land', [PublicWebsiteController::class, 'land'])->name('public.land');
Route::get('/projects', [PublicWebsiteController::class, 'projects'])->name('public.projects');
Route::get('/projects/{slug}', [PublicWebsiteController::class, 'showProject'])->name('public.projects.show');
Route::get('/locations', [PublicWebsiteController::class, 'locations'])->name('public.locations');
Route::get('/locations/{slug}', [PublicWebsiteController::class, 'showLocation'])->name('public.locations.show');
Route::get('/properties/{property:slug}', [PublicWebsiteController::class, 'showProperty'])->name('public.properties.show');

// Services & Land Survey Portal
Route::get('/services', [PublicWebsiteController::class, 'services'])->name('public.services');
Route::get('/services/land-survey', [PublicWebsiteController::class, 'landSurveyService'])->name('public.services.land_survey');
Route::get('/services/{slug}', [PublicWebsiteController::class, 'serviceDetail'])->name('public.services.detail');

// Resources, Blog & Static Pages
Route::get('/blog', [PublicWebsiteController::class, 'blog'])->name('public.blog');
Route::get('/blog/{slug}', [PublicWebsiteController::class, 'showArticle'])->name('public.blog.show');
Route::get('/about', [PublicWebsiteController::class, 'about'])->name('public.about');
Route::get('/contact', [PublicWebsiteController::class, 'contact'])->name('public.contact');
Route::get('/faq', [PublicWebsiteController::class, 'faq'])->name('public.faq');
Route::get('/favorites', [PublicWebsiteController::class, 'favorites'])->name('public.favorites');
Route::get('/compare', [PublicWebsiteController::class, 'compare'])->name('public.compare');
Route::get('/privacy', [PublicWebsiteController::class, 'privacy'])->name('public.privacy');
Route::get('/terms', [PublicWebsiteController::class, 'terms'])->name('public.terms');
Route::get('/cookies', [PublicWebsiteController::class, 'cookies'])->name('public.cookies');

// Conversion, Hold & Lead Capture Actions
Route::post('/inquire', [PublicWebsiteController::class, 'submitInquiry'])->name('public.inquire');
Route::post('/viewing/book', [PublicWebsiteController::class, 'bookViewing'])->name('public.viewing.book');
Route::post('/reservation/reserve', [PublicWebsiteController::class, 'reservePlot'])->name('public.reservation.reserve');
Route::post('/services/survey/request', [PublicWebsiteController::class, 'requestSurvey'])->name('public.survey.request');
Route::post('/contact/submit', [PublicWebsiteController::class, 'submitContact'])->name('public.contact.submit');
Route::post('/newsletter/subscribe', [PublicWebsiteController::class, 'subscribeNewsletter'])->name('public.newsletter.subscribe');
Route::post('/valuation/request', [PublicWebsiteController::class, 'requestValuation'])->name('public.valuation.request');

// Legacy Marketplace Aliases (Backwards Compatibility)
Route::prefix('marketplace')->name('marketplace.')->group(function () {
    Route::get('/', [PublicWebsiteController::class, 'properties'])->name('index');
    Route::get('/property/{property:slug}', [PublicWebsiteController::class, 'showProperty'])->name('show');
    Route::post('/inquiry', [PublicWebsiteController::class, 'submitInquiry'])->name('inquire');
});

// Protected Enterprise Staff & Portal Routes
Route::middleware('auth')->group(function () {
    // Executive Dashboard (FM-001)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Property & Land Asset Management (BM-001, BM-004)
    Route::prefix('admin')->group(function () {
        Route::post('properties/ai-description', [PropertyController::class, 'generateAiDescription'])->name('properties.ai_description');
        Route::resource('properties', PropertyController::class);
    });

    // CRM & Customer Intelligence (BM-003)
    Route::prefix('crm')->name('crm.')->group(function () {
        Route::get('leads', [CRMController::class, 'leads'])->name('leads');
        Route::post('leads', [CRMController::class, 'storeLead'])->name('leads.store');
        Route::post('leads/{lead}/stage', [CRMController::class, 'updateLeadStage'])->name('leads.update_stage');
        Route::post('leads/{lead}/activity', [CRMController::class, 'logActivity'])->name('leads.log_activity');

        Route::get('customers', [CRMController::class, 'customers'])->name('customers');
        Route::post('customers', [CRMController::class, 'storeCustomer'])->name('customers.store');
    });

    // Customer Loyalty & Retention Engine
    Route::prefix('loyalty')->name('loyalty.')->group(function () {
        Route::get('/', [LoyaltyController::class, 'index'])->name('index');
        Route::post('rules', [LoyaltyController::class, 'storeRule'])->name('rules.store');
        Route::post('rules/{rule}/update', [LoyaltyController::class, 'updateRule'])->name('rules.update');
        Route::post('redeem', [LoyaltyController::class, 'redeemVoucher'])->name('redeem');
        Route::post('adjust-points', [LoyaltyController::class, 'adjustPoints'])->name('adjust_points');
        Route::post('scan-all', [LoyaltyController::class, 'scanAll'])->name('scan_all');
    });

    // User Management & Personnel Elevation (SRS Section 3.2)
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::post('{user}/elevate', [UserController::class, 'elevateRole'])->name('elevate_role');
        Route::post('{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle_status');
    });

    // Reservations & Holds (BM-006)
    Route::prefix('reservations')->name('reservations.')->group(function () {
        Route::get('/', [ReservationController::class, 'index'])->name('index');
        Route::post('/', [ReservationController::class, 'store'])->name('store');
        Route::post('/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('cancel');
    });

    // Appointments & Viewings (BM-007)
    Route::prefix('appointments')->name('appointments.')->group(function () {
        Route::get('/', [AppointmentController::class, 'index'])->name('index');
        Route::post('/', [AppointmentController::class, 'store'])->name('store');
        Route::post('/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('status');
    });

    // Sales Deals & Contracts (BM-009)
    Route::resource('deals', SalesDealController::class)->only(['index', 'store', 'show']);

    // Finance, Billing & Installments (BM-011)
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('invoices', [FinanceController::class, 'invoices'])->name('invoices');
        Route::post('invoices', [FinanceController::class, 'storeInvoice'])->name('invoices.store');
        Route::get('invoices/{invoice}', [FinanceController::class, 'showInvoice'])->name('invoices.show');
        Route::post('payments/record', [FinanceController::class, 'recordPayment'])->name('payments.record');

        Route::get('expenses', [FinanceController::class, 'expenses'])->name('expenses');
        Route::post('expenses', [FinanceController::class, 'storeExpense'])->name('expenses.store');
    });

    // Land Survey & GIS Geospatial Engine (BM-008)
    Route::prefix('survey')->name('survey.')->group(function () {
        Route::get('/', [SurveyGISController::class, 'index'])->name('index');
        Route::post('/', [SurveyGISController::class, 'store'])->name('store');
        Route::get('map', [SurveyGISController::class, 'map'])->name('map');
        Route::get('{survey}', [SurveyGISController::class, 'show'])->name('show');
        Route::post('{survey}/status', [SurveyGISController::class, 'updateStatus'])->name('status');
        Route::post('{survey}/beacons', [SurveyGISController::class, 'addBeacon'])->name('beacons.add');
    });

    // Reports Center & BI (BM-015)
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('properties', [ReportController::class, 'propertyReport'])->name('properties');
        Route::get('sales', [ReportController::class, 'salesReport'])->name('sales');
        Route::get('agents', [ReportController::class, 'agentCommissionReport'])->name('agents');
        Route::get('rent-roll', [ReportController::class, 'rentRollReport'])->name('rent_roll');
        Route::get('survey', [ReportController::class, 'surveyReport'])->name('survey');
        Route::get('leads', [ReportController::class, 'leadsReport'])->name('leads');
    });

    // Self-Service Portals (BM-013)
    Route::prefix('portals')->name('portals.')->group(function () {
        Route::get('client', [PortalController::class, 'clientPortal'])->name('client');
        Route::get('owner', [PortalController::class, 'ownerPortal'])->name('owner');
        Route::post('owner/submit-property', [PortalController::class, 'submitOwnerProperty'])->name('owner.submit_property');
    });

    // Approval Workflows (BM-014)
    Route::prefix('workflows')->name('workflows.')->group(function () {
        Route::get('/', [WorkflowController::class, 'index'])->name('index');
        Route::post('{approval}/approve', [WorkflowController::class, 'approve'])->name('approve');
        Route::post('{approval}/reject', [WorkflowController::class, 'reject'])->name('reject');
    });

    // EDMS Document Records Vault (BM-010)
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [DocumentController::class, 'index'])->name('index');
        Route::post('/', [DocumentController::class, 'store'])->name('store');
    });

    // KYC & Regulatory Compliance (BM-019)
    Route::prefix('compliance')->name('compliance.')->group(function () {
        Route::get('kyc', [ComplianceController::class, 'kycQueue'])->name('kyc');
        Route::post('verify-customer/{customer}', [ComplianceController::class, 'verifyCustomer'])->name('verify_customer');
        Route::post('verify-owner/{owner}', [ComplianceController::class, 'verifyOwner'])->name('verify_owner');
    });

    // Marketing Campaigns & Broadcasts (BM-012)
    Route::prefix('marketing')->name('marketing.')->group(function () {
        Route::get('/', [CampaignController::class, 'index'])->name('index');
        Route::post('/', [CampaignController::class, 'store'])->name('store');
        Route::post('broadcast', [CampaignController::class, 'broadcast'])->name('broadcast');
    });

    // AI Smart Studio (BM-020)
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::get('chat', [AIController::class, 'chat'])->name('chat');
        Route::post('ask', [AIController::class, 'ask'])->name('ask');
    });

    // Notifications Dispatcher (FM-008)
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
    });

    // Settings & White-Label Customization (FM-004, FM-005, BM-018)
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::get('rbac', [RBACController::class, 'index'])->name('rbac');
        Route::post('rbac/{role}/update', [RBACController::class, 'updatePermissions'])->name('rbac.update');
        Route::post('branding', [SettingController::class, 'updateBranding'])->name('branding');
        Route::post('landing', [SettingController::class, 'updateLandingPage'])->name('landing');
        Route::post('pushsms', [SettingController::class, 'updatePushSms'])->name('pushsms');
        Route::get('sms-balance', [SettingController::class, 'checkSmsBalance'])->name('sms_balance');
        Route::post('toggles', [SettingController::class, 'updateFeatureToggles'])->name('toggles');
        Route::post('social', [SettingController::class, 'updateSocial'])->name('social');
        Route::post('module/{module}/toggle', [SettingController::class, 'toggleModule'])->name('toggle_module');
        Route::post('switch-branch', [SettingController::class, 'switchBranch'])->name('switch_branch');
        Route::post('branches', [SettingController::class, 'storeBranch'])->name('branches.store');
        Route::put('branches/{branch}', [SettingController::class, 'updateBranch'])->name('branches.update');
        Route::delete('branches/{branch}', [SettingController::class, 'destroyBranch'])->name('branches.destroy');
        Route::post('environment', [SettingController::class, 'switchEnvironment'])->name('environment');
        Route::post('purge-demo-data', [SettingController::class, 'purgeDemoData'])->name('purge_demo_data');
        Route::post('seed-demo-data', [SettingController::class, 'seedDemoData'])->name('seed_demo_data');
    });
});
