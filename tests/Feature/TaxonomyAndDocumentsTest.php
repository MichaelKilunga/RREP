<?php

namespace Tests\Feature;

use App\Models\MediaFile;
use App\Models\TaxonomyTerm;
use App\Models\User;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Owner-editable categories/types (Settings -> Categories & Types) and the
 * upgraded Documents vault (wide format support, preview, delete).
 */
class TaxonomyAndDocumentsTest extends TestCase
{
    private function admin(): User
    {
        return User::where('email', 'admin@rehospace.com')->firstOrFail();
    }

    public function test_taxonomy_terms_can_be_added_renamed_and_deleted(): void
    {
        $this->actingAs($this->admin())->post(route('settings.taxonomies.store'), [
            'group' => 'project_type',
            'name' => 'Beach Resort Estate',
        ])->assertRedirect();

        $term = TaxonomyTerm::where('group', 'project_type')->where('name', 'Beach Resort Estate')->firstOrFail();

        // The new type immediately appears on the project form.
        $this->actingAs($this->admin())->get(route('admin.projects.create'))
            ->assertSee('Beach Resort Estate');

        $this->actingAs($this->admin())->put(route('settings.taxonomies.update', $term), [
            'name' => 'Lakefront Estate',
        ])->assertRedirect();
        $this->assertSame('Lakefront Estate', $term->fresh()->name);

        $this->actingAs($this->admin())->delete(route('settings.taxonomies.destroy', $term))->assertRedirect();
        $this->assertNull(TaxonomyTerm::find($term->id));
    }

    public function test_settings_page_shows_the_categories_tab_with_seeded_groups(): void
    {
        $this->actingAs($this->admin())->get(route('settings.index'))
            ->assertSee('Categories & Types')
            ->assertSee('Project Types')
            ->assertSee('Service Categories')
            ->assertSee('Document Types')
            ->assertSee('Master-Planned Land');
    }

    public function test_service_and_document_forms_use_taxonomy_terms(): void
    {
        TaxonomyTerm::create(['group' => 'service_category', 'name' => 'Environmental Audit']);
        TaxonomyTerm::create(['group' => 'document_type', 'name' => 'Court Ruling']);

        $this->actingAs($this->admin())->get(route('admin.services.create'))
            ->assertSee('Environmental Audit');
        $this->actingAs($this->admin())->get(route('documents.index'))
            ->assertSee('Court Ruling');
    }

    public function test_documents_accept_office_formats_and_can_be_previewed_and_deleted(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post(route('documents.store'), [
            'file_name' => 'Mkataba wa Mauzo Minara',
            'document_type' => 'Sale Agreement',
            'file' => File::create('mkataba.docx', 120),
        ])->assertRedirect()->assertSessionHas('success');

        $doc = MediaFile::where('file_name', 'Mkataba wa Mauzo Minara')->firstOrFail();
        Storage::disk('public')->assertExists($doc->file_path);

        // A PDF/image gets a preview button; every row offers download + delete.
        $page = $this->actingAs($this->admin())->get(route('documents.index'));
        $page->assertSee('Mkataba wa Mauzo Minara');
        $page->assertSee('docPreviewModal', false);
        $page->assertSee(route('documents.destroy', $doc), false);

        $this->actingAs($this->admin())->delete(route('documents.destroy', $doc))->assertRedirect();
        Storage::disk('public')->assertMissing($doc->file_path);
        $this->assertNull(MediaFile::find($doc->id));
    }

    public function test_documents_reject_unsupported_executables(): void
    {
        $this->actingAs($this->admin())->post(route('documents.store'), [
            'file_name' => 'Bad file',
            'document_type' => 'Other',
            'file' => File::create('virus.exe', 10),
        ])->assertSessionHasErrors('file');
    }
}
