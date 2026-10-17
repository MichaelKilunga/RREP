<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

/**
 * Renders every GET page of the public site and the staff console so view
 * refactors (layouts, partials, design system) cannot silently break a page.
 */
class PageSmokeTest extends TestCase
{
    /**
     * @return array<int, string>
     */
    public static function publicRoutes(): array
    {
        return [
            'public.home', 'public.properties', 'public.buy', 'public.rent', 'public.land', 'public.projects',
            'public.locations', 'public.services', 'public.services.land_survey', 'public.blog', 'public.about',
            'public.contact', 'public.faq', 'public.favorites', 'public.compare', 'public.privacy', 'public.terms',
            'public.cookies', 'marketplace.index', 'login',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function staffRoutes(): array
    {
        return [
            'dashboard', 'admin.properties.index', 'admin.properties.create', 'admin.services.index', 'admin.services.create',
            'admin.projects.index', 'admin.projects.create', 'crm.leads', 'crm.customers', 'loyalty.index', 'users.index',
            'reservations.index', 'appointments.index', 'deals.index', 'finance.invoices', 'finance.expenses',
            'survey.index', 'survey.map', 'reports.index', 'reports.properties', 'reports.sales', 'reports.agents',
            'reports.rent_roll', 'reports.survey', 'reports.leads', 'portals.client', 'portals.owner', 'workflows.index',
            'documents.index', 'compliance.kyc', 'marketing.index', 'ai.chat', 'notifications.index', 'settings.index',
            'settings.rbac',
        ];
    }

    public function test_every_public_page_renders(): void
    {
        foreach (self::publicRoutes() as $routeName) {
            $response = $this->get(route($routeName));

            $this->assertSame(200, $response->status(), "Public route [{$routeName}] did not render.");
        }
    }

    public function test_every_public_page_renders_in_swahili(): void
    {
        $this->withSession(['locale' => 'sw']);

        foreach (['public.home', 'public.properties', 'public.land', 'public.projects', 'public.services.land_survey', 'public.contact'] as $routeName) {
            $response = $this->get(route($routeName));

            $this->assertSame(200, $response->status(), "Swahili route [{$routeName}] did not render.");
            $response->assertSee('<html lang="sw"', false);
        }
    }

    public function test_every_staff_page_renders_for_an_administrator(): void
    {
        $admin = User::where('email', 'admin@rehospace.com')->firstOrFail();

        foreach (self::staffRoutes() as $routeName) {
            $response = $this->actingAs($admin)->get(route($routeName));

            $this->assertSame(200, $response->status(), "Staff route [{$routeName}] did not render.");
        }
    }

    public function test_layouts_expose_mobile_navigation_and_design_tokens(): void
    {
        $public = $this->get(route('public.home'));
        $public->assertSee('public-bottom-nav', false);
        $public->assertSee('--rrep-primary-rgb', false);

        $admin = User::where('email', 'admin@rehospace.com')->firstOrFail();
        $staff = $this->actingAs($admin)->get(route('dashboard'));
        $staff->assertSee('app-bottom-nav', false);
        $staff->assertSee('class="app-shell"', false);
    }
}
