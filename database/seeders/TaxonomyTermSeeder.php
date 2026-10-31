<?php

namespace Database\Seeders;

use App\Models\TaxonomyTerm;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TaxonomyTermSeeder extends Seeder
{
    public function run(): void
    {
        foreach (TaxonomyTerm::DEFAULTS as $group => $names) {
            foreach ($names as $order => $name) {
                TaxonomyTerm::firstOrCreate(
                    ['group' => $group, 'name' => $name],
                    ['uuid' => Str::uuid(), 'display_order' => $order]
                );
            }
        }
    }
}
