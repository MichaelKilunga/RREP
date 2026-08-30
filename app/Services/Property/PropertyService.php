<?php

namespace App\Services\Property;

use App\Models\AuditLog;
use App\Models\LandParcel;
use App\Models\Property;
use App\Models\PropertyUnit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PropertyService
{
    public function createProperty(array $data): Property
    {
        $data['slug'] = Str::slug($data['title']).'-'.Str::lower(Str::random(6));
        $data['property_code'] = $data['property_code'] ?? 'RREP-'.strtoupper(Str::random(6));
        $data['currency'] = $data['currency'] ?? config('app.default_currency', 'TZS');

        $property = Property::create($data);

        // Link amenities if provided
        if (! empty($data['amenities'])) {
            $property->amenities()->sync($data['amenities']);
        }

        // Create Land Parcel if type is land or parcel data exists
        if (! empty($data['parcel_number']) || ! empty($data['acreage'])) {
            LandParcel::create([
                'property_id' => $property->id,
                'parcel_number' => $data['parcel_number'] ?? 'P-'.strtoupper(Str::random(5)),
                'deed_number' => $data['deed_number'] ?? null,
                'survey_plan_number' => $data['survey_plan_number'] ?? null,
                'title_deed_type' => $data['title_deed_type'] ?? 'Freehold',
                'acreage' => $data['acreage'] ?? ($property->area_size ? $property->area_size / 4046.86 : 1.0),
                'zoning' => $data['zoning'] ?? 'Residential',
                'boundary_coordinates_json' => $data['boundary_coordinates_json'] ?? null,
            ]);
        }

        // Log audit
        AuditLog::create([
            'user_id' => Auth::id(),
            'user_name' => Auth::user()?->name ?? 'System',
            'event' => 'created',
            'auditable_type' => Property::class,
            'auditable_id' => $property->id,
            'new_values' => $property->toArray(),
        ]);

        return $property;
    }

    public function updateProperty(Property $property, array $data): Property
    {
        $old = $property->toArray();
        $property->update($data);

        if (isset($data['amenities'])) {
            $property->amenities()->sync($data['amenities']);
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'user_name' => Auth::user()?->name ?? 'System',
            'event' => 'updated',
            'auditable_type' => Property::class,
            'auditable_id' => $property->id,
            'old_values' => $old,
            'new_values' => $property->toArray(),
        ]);

        return $property;
    }

    public function addUnit(Property $property, array $unitData): PropertyUnit
    {
        return $property->units()->create($unitData);
    }
}
