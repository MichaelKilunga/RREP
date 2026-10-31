<?php

namespace App\Models;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

/**
 * An owner-editable category/type term (project types, service categories,
 * document types, ...) identified by its `group` slug. Values are plain names
 * rendered as-is, so owners can keep them in Swahili or English.
 */
class TaxonomyTerm extends Model
{
    use HasUuid;

    protected $guarded = ['id'];

    /** Groups manageable from Settings -> Categories & Types. */
    public const GROUPS = ['project_type', 'service_category', 'document_type', 'expense_category'];

    /** Default terms per group (seeded on install; fallback while a group is empty). */
    public const DEFAULTS = [
        'project_type' => ['Master-Planned Land', 'Residential Estate', 'Commercial Plaza', 'Industrial Park'],
        'service_category' => ['Land', 'Surveying', 'Planning', 'Consultancy', 'Legal', 'Development', 'Property'],
        'document_type' => ['Title Deed', 'Survey Plan', 'Sale Agreement', 'Lease Agreement', 'NIDA Copy', 'TIN Certificate', 'Passport Copy', 'Valuation Report', 'TP Drawing', 'Other'],
        'expense_category' => ['Survey', 'Marketing', 'Legal', 'Land Rent', 'Commission', 'Transport', 'Administrative', 'Utilities', 'Maintenance'],
    ];

    /**
     * Ordered term names for a group, falling back to the given defaults
     * while the group has no rows yet (fresh installs, partial migrations).
     *
     * @param  array<int, string>  $defaults
     * @return array<int, string>
     */
    public static function options(string $group, array $defaults = []): array
    {
        try {
            $terms = static::where('group', $group)
                ->orderBy('display_order')
                ->orderBy('name')
                ->pluck('name')
                ->all();
        } catch (\Throwable) {
            $terms = [];
        }

        return $terms !== [] ? $terms : $defaults;
    }
}
