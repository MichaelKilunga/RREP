<?php

namespace Tests\Feature;

use App\Models\CompanyService;
use App\Models\NewsletterSubscriber;
use App\Models\RealEstateProject;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Forms ask only for what is needed and explain what to enter: the project
 * form drops type / developer / town (council covers it), image uploads give
 * plain reasons, an oversized form gets a friendly page, and the footer
 * subscription says what to type.
 */
class FormClarityTest extends TestCase
{
    private function admin(): User
    {
        return User::where('email', 'admin@rehospace.com')->firstOrFail();
    }

    public function test_project_form_no_longer_asks_for_type_developer_or_town(): void
    {
        $this->actingAs($this->admin())->get(route('admin.projects.create'))
            ->assertOk()
            ->assertDontSee('name="project_type"', false)
            ->assertDontSee('name="developer_name"', false)
            ->assertDontSee('name="city"', false)
            ->assertSee('name="council"', false);

        $this->actingAs($this->admin())->post(route('admin.projects.store'), [
            'title' => 'Mtumba Gardens', 'project_category' => 'land', 'project_status' => 'Selling',
            'location_name' => 'Mtumba', 'council' => 'Dodoma City Council', 'region' => 'Dodoma',
        ])->assertRedirect(route('admin.projects.index'));

        $land = RealEstateProject::where('title', 'Mtumba Gardens')->firstOrFail();
        $this->assertSame('Dodoma City Council', $land->city);
        $this->assertSame('Mtumba, Dodoma City Council', $land->location_label);

        $service = CompanyService::create([
            'uuid' => Str::uuid(), 'title' => 'Boundary Survey', 'slug' => 'boundary-survey-form-test', 'category' => 'Surveying',
            'short_description' => 'Beacons and boundaries', 'icon' => 'bi-geo', 'is_active' => true,
        ]);
        $this->actingAs($this->admin())->post(route('admin.projects.store'), [
            'title' => 'Kibaha Boundary Survey', 'project_category' => 'service', 'company_service_id' => $service->id,
            'project_status' => 'Ongoing', 'council' => 'Kibaha Town Council',
        ])->assertRedirect(route('admin.projects.index'));

        $serviceProject = RealEstateProject::where('title', 'Kibaha Boundary Survey')->firstOrFail();
        $this->assertSame('Kibaha Town Council', $serviceProject->city);
        $this->assertSame('Kibaha Town Council', $serviceProject->location_name);

        $this->actingAs($this->admin())->post(route('admin.projects.store'), [
            'title' => 'No Place', 'project_category' => 'land', 'project_status' => 'Selling', 'location_name' => 'Somewhere',
        ])->assertSessionHasErrors('council');
    }

    public function test_service_thumbnail_errors_say_what_to_upload_instead(): void
    {
        $payload = ['title' => 'Topographic Survey', 'category' => 'Surveying', 'short_description' => 'Contours and levels.', 'icon' => 'bi-geo'];

        $this->actingAs($this->admin())->get(route('admin.services.create'))
            ->assertOk()
            ->assertSee('data-image-upload', false)
            ->assertSee(round(upload_limit_kb() / 1024, 1).' MB');

        $this->actingAs($this->admin())->post(route('admin.services.store'), $payload + [
            'thumbnail_file' => UploadedFile::fake()->create('IMG_2041.heic', 300, 'image/heic'),
        ])->assertSessionHasErrors(['thumbnail_file' => __('app.image_wrong_type')]);

        $this->actingAs($this->admin())->post(route('admin.services.store'), $payload + [
            'thumbnail_file' => UploadedFile::fake()->image('big.jpg')->size(upload_limit_kb() + 100),
        ])->assertSessionHasErrors(['thumbnail_file' => __('app.image_too_large', ['max' => round(upload_limit_kb() / 1024, 1)])]);

        $this->actingAs($this->admin())->post(route('admin.services.store'), $payload + [
            'thumbnail_file' => UploadedFile::fake()->image('ok.jpg', 1200, 800)->size(300),
        ])->assertSessionHasNoErrors();
    }

    public function test_an_oversized_form_gets_a_friendly_page_instead_of_a_raw_error(): void
    {
        $this->actingAs($this->admin())
            ->call('POST', route('admin.services.store'), [], [], [], ['CONTENT_LENGTH' => (string) (200 * 1024 * 1024)])
            ->assertStatus(413)
            ->assertSee(__('app.upload_too_large_title'));
    }

    public function test_footer_subscription_explains_what_to_enter_and_rejects_nonsense(): void
    {
        $this->get(route('public.home'))
            ->assertOk()
            ->assertSee('for="footerSubscribeContact"', false)
            ->assertSee(__('app.e_g_0784100200_or_email'));

        $this->from(route('public.home'))->post(route('public.newsletter.subscribe'), ['contact' => 'habari'])
            ->assertRedirect(route('public.home').'#newsletter')
            ->assertSessionHasErrorsIn('newsletter', 'contact');

        $this->from(route('public.home'))->post(route('public.newsletter.subscribe'), ['contact' => '0784 100 200'])
            ->assertRedirect(route('public.home').'#newsletter')
            ->assertSessionHas('newsletter_done');
        $this->assertTrue(NewsletterSubscriber::where('phone', '255784100200')->exists());
    }
}
