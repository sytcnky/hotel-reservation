<?php

namespace App\Services;

use App\Models\Location;
use App\Models\TransferRoute;
use Illuminate\Support\Facades\Cache;

class TransferLocationSelectService
{
    /**
     * Aktif transfer rotalarında kullanılan lokasyonları select için üretir.
     *
     * @return array<int, array{id:int, label:string}>
     */
    public function getOptions(): array
    {
        return Cache::remember('transfer_locations_for_select', 600, function () {
            $routes = TransferRoute::query()
                ->where('is_active', true)
                ->get(['from_location_id', 'to_location_id']);

            $locationIds = $routes
                ->flatMap(fn ($r) => [$r->from_location_id, $r->to_location_id])
                ->filter()
                ->unique()
                ->values();

            if ($locationIds->isEmpty()) {
                return [];
            }

            $locations = Location::query()
                ->whereIn('id', $locationIds)
                ->orderBy('id')
                ->get(['id', 'name']);

            return $locations
                ->map(function (Location $location) {
                    $label = trim((string) ($location->name ?? ''));

                    return [
                        'id'    => (int) $location->id,
                        'label' => $label !== '' ? $label : ('#' . $location->id),
                    ];
                })
                ->values()
                ->all();
        });
    }
}
