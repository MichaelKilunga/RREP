<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandParcel;
use App\Models\RealEstateProject;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Plot-level inventory management for land projects: add, edit, re-price,
 * change status (keeping reservations consistent) and export plots.
 */
class ProjectParcelController extends Controller
{
    public const STATUSES = ['Available', 'Reserved', 'Sold'];

    public function store(Request $request, RealEstateProject $project): RedirectResponse
    {
        $data = $this->validated($request, $project);

        $parcel = $project->parcels()->create($data + [
            'region' => $project->region,
            'district' => $project->district,
            'council' => $project->council,
        ]);

        $this->priceFromRate($project, $parcel, $request->filled('price'));
        $project->syncPlotCounts();

        return back()->withFragment('plots')->with('success', __('admin.plot_added', ['plot' => $parcel->parcel_number]));
    }

    public function update(Request $request, RealEstateProject $project, LandParcel $parcel): RedirectResponse
    {
        $data = $this->validated($request, $project, $parcel);
        $oldStatus = $parcel->status;

        $parcel->update($data);
        $this->priceFromRate($project, $parcel, $request->filled('price'));
        $this->reconcileReservations(collect([$parcel]), $oldStatus);
        $project->syncPlotCounts();

        return back()->withFragment('plots')->with('success', __('admin.plot_updated', ['plot' => $parcel->parcel_number]));
    }

    public function updateStatus(Request $request, RealEstateProject $project, LandParcel $parcel): JsonResponse|RedirectResponse
    {
        $request->validate(['status' => ['required', Rule::in(self::STATUSES)]]);

        $oldStatus = $parcel->status;
        $parcel->update(['status' => $request->status]);
        $this->reconcileReservations(collect([$parcel]), $oldStatus);
        $project->syncPlotCounts();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('admin.plot_status_saved', ['plot' => $parcel->parcel_number, 'status' => __('admin.plot_status_'.strtolower($parcel->status))]),
                'counts' => $this->counts($project->fresh()),
            ]);
        }

        return back()->withFragment('plots')->with('success', __('admin.plot_updated', ['plot' => $parcel->parcel_number]));
    }

    public function destroy(RealEstateProject $project, LandParcel $parcel): RedirectResponse
    {
        $number = $parcel->parcel_number;
        $this->reconcileReservations(collect([$parcel->fill(['status' => 'Available'])]), 'Reserved');
        $parcel->delete();
        $project->syncPlotCounts();

        return back()->withFragment('plots')->with('success', __('admin.plot_deleted', ['plot' => $number]));
    }

    public function bulk(Request $request, RealEstateProject $project): RedirectResponse
    {
        $data = $request->validate([
            'action' => 'required|in:status,price,rate,delete',
            'status' => ['required_if:action,status', 'nullable', Rule::in(self::STATUSES)],
            'price' => 'required_if:action,price|nullable|numeric|min:0',
            'rate' => 'required_if:action,rate|nullable|numeric|min:0',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $parcels = $project->parcels()->whereIn('id', $data['ids'])->get();

        match ($data['action']) {
            'status' => $parcels->each(function (LandParcel $parcel) use ($data) {
                $old = $parcel->status;
                $parcel->update(['status' => $data['status']]);
                $this->reconcileReservations(collect([$parcel]), $old);
            }),
            'price' => $parcels->each->update(['price' => $data['price']]),
            'rate' => $parcels->each(fn (LandParcel $parcel) => $parcel->update([
                'price' => round((float) $parcel->area_sqm * (float) $data['rate'], 2),
            ])),
            'delete' => $parcels->each(function (LandParcel $parcel) {
                $this->reconcileReservations(collect([$parcel->fill(['status' => 'Available'])]), 'Reserved');
                $parcel->delete();
            }),
        };

        $project->syncPlotCounts();
        $this->refreshStartingPrice($project);

        return back()->withFragment('plots')->with('success', __('admin.plots_bulk_done', ['count' => $parcels->count()]));
    }

    public function export(RealEstateProject $project): StreamedResponse
    {
        $parcels = $project->parcels()->orderBy('block_number')->orderBy('parcel_number')->get();
        $filename = str($project->title)->slug().'-plots.csv';

        return response()->streamDownload(function () use ($parcels) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Plot', 'Block', 'Area (m2)', 'Price (TZS)', 'Status', 'Land use', 'Title type']);
            foreach ($parcels as $parcel) {
                fputcsv($out, [
                    $parcel->parcel_number, $parcel->block_number, $parcel->area_sqm, $parcel->price,
                    $parcel->status, $parcel->land_use, $parcel->title_deed_type,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, RealEstateProject $project, ?LandParcel $parcel = null): array
    {
        $data = $request->validate([
            'parcel_number' => [
                'required', 'string', 'max:100',
                Rule::unique('land_parcels')
                    ->where('real_estate_project_id', $project->id)
                    ->where('block_number', $request->input('block_number'))
                    ->whereNull('deleted_at')
                    ->ignore($parcel?->id),
            ],
            'block_number' => 'nullable|string|max:50',
            'area_sqm' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'status' => ['required', Rule::in(self::STATUSES)],
            'land_use' => 'nullable|string|max:100',
            'title_deed_type' => 'nullable|string|max:50',
        ]);

        $data['acreage'] = round(((float) ($data['area_sqm'] ?? 0)) / 4046.8564224, 4);
        $data['price'] = $data['price'] ?? 0;

        return $data;
    }

    /**
     * A plot without its own price follows the project's price per m2.
     */
    protected function priceFromRate(RealEstateProject $project, LandParcel $parcel, bool $priceGiven): void
    {
        if (! $priceGiven && (float) $project->price_per_sqm > 0 && (float) $parcel->area_sqm > 0) {
            $parcel->update(['price' => round((float) $parcel->area_sqm * (float) $project->price_per_sqm, 2)]);
        }
        $this->refreshStartingPrice($project);
    }

    protected function refreshStartingPrice(RealEstateProject $project): void
    {
        $cheapest = (float) $project->parcels()->where('price', '>', 0)->min('price');
        if ($cheapest > 0 && (float) $project->price_per_sqm > 0) {
            $project->update(['starting_price' => $cheapest]);
        }
    }

    /**
     * Keep plot holds consistent with a manual status change: releasing a
     * reserved plot cancels its active hold, selling it converts the hold.
     *
     * @param  Collection<int, LandParcel>  $parcels
     */
    protected function reconcileReservations(Collection $parcels, ?string $oldStatus): void
    {
        foreach ($parcels as $parcel) {
            if ($oldStatus === $parcel->status) {
                continue;
            }

            $active = Reservation::where('land_parcel_id', $parcel->id)->where('status', 'Active');

            if ($parcel->status === 'Available') {
                $active->update(['status' => 'Cancelled', 'cancellation_reason' => __('admin.plot_released_by_staff')]);
            } elseif ($parcel->status === 'Sold') {
                $active->update(['status' => 'Converted']);
            }
        }
    }

    /**
     * @return array<string, int>
     */
    protected function counts(RealEstateProject $project): array
    {
        $parcels = $project->parcels()->get(['status']);

        return [
            'total' => $parcels->count(),
            'Available' => $parcels->where('status', 'Available')->count(),
            'Reserved' => $parcels->where('status', 'Reserved')->count(),
            'Sold' => $parcels->where('status', 'Sold')->count(),
        ];
    }
}
