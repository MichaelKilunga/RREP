<?php

use App\Models\TaxonomyTerm;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Expense categories became owner-editable (Settings -> Categories & Types).
     * Seed them from the defaults plus any category already used on expenses.
     */
    public function up(): void
    {
        if (DB::table('taxonomy_terms')->where('group', 'expense_category')->exists()) {
            return;
        }

        $names = collect(TaxonomyTerm::DEFAULTS['expense_category'])
            ->merge(DB::table('expenses')->whereNotNull('category')->distinct()->pluck('category'))
            ->map(fn ($n) => trim((string) $n))
            ->filter()
            ->unique()
            ->values();

        foreach ($names as $order => $name) {
            DB::table('taxonomy_terms')->insert([
                'uuid' => (string) Str::uuid(),
                'group' => 'expense_category',
                'name' => $name,
                'display_order' => $order,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('taxonomy_terms')->where('group', 'expense_category')->delete();
    }
};
