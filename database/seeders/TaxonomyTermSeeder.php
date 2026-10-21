<?php

namespace Database\Seeders;

use App\Models\TaxonomyTerm;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TaxonomyTermSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            'project_type' => ['Master-Planned Land', 'Residential Estate', 'Commercial Plaza', 'Industrial Park'],
            'service_category' => ['Land', 'Surveying', 'Planning', 'Consultancy', 'Legal', 'Development', 'Property'],
            'document_type' => ['Title Deed', 'Survey Plan', 'Sale Agreement', 'Lease Agreement', 'NIDA Copy', 'TIN Certificate', 'Passport Copy', 'Valuation Report', 'TP Drawing', 'Other'],
        ];

        foreach ($groups as $group => $names) {
            foreach ($names as $order => $name) {
                TaxonomyTerm::firstOrCreate(
                    ['group' => $group, 'name' => $name],
                    ['uuid' => Str::uuid(), 'display_order' => $order]
                );
            }
        }
    }
}
