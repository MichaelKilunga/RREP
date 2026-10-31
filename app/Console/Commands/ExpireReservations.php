<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('reservations:expire')]
#[Description('Expire plot/property holds past their end date and make the plots available again')]
class ExpireReservations extends Command
{
    public function handle(): int
    {
        $overdue = Reservation::withoutGlobalScopes()
            ->with(['landParcel.project', 'property'])
            ->where('status', 'Active')
            ->whereDate('reserved_until', '<', now()->toDateString())
            ->get();

        foreach ($overdue as $reservation) {
            $reservation->update([
                'status' => 'Expired',
                'cancellation_reason' => $reservation->cancellation_reason ?: 'Hold period ended',
            ]);

            if ($reservation->landParcel && $reservation->landParcel->status === 'Reserved') {
                $reservation->landParcel->update(['status' => 'Available']);
                $reservation->landParcel->project?->syncPlotCounts();
            }
            if ($reservation->property && $reservation->property->status === 'Reserved') {
                $reservation->property->update(['status' => 'Available']);
            }
        }

        $this->info("Expired {$overdue->count()} reservation(s).");

        return self::SUCCESS;
    }
}
