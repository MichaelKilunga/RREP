---
paths:
  - 'resources/views/**, app/Http/Controllers/**'
---

# Controllers

## Category/type option lists come from TaxonomyTerm, never hardcoded arrays
Owner-editable select options (project types, service categories, document types) live in the taxonomy_terms table, managed at Settings → Categories & Types (routes settings.taxonomies.*, partial settings/partials/taxonomies.blade.php). In views call `\App\Models\TaxonomyTerm::options('group', [...defaults])` — the defaults array is only a fallback for empty installs and must mirror TaxonomyTermSeeder. Term names render as-is (no __() label mapping), so owners can keep them in Swahili. When adding a new categorised field, add a group constant to TaxonomyTerm::GROUPS, seed defaults in TaxonomyTermSeeder, and add the group card to the taxonomies settings partial.
