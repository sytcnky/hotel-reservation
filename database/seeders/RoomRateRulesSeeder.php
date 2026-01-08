<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomRateRulesSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = DB::table('rooms')
            ->select('id', 'capacity_adults', 'capacity_children')
            ->orderBy('id')
            ->get();

        $curr = DB::table('currencies')
            ->where('is_active', true)
            ->whereIn('code', ['TRY', 'GBP'])
            ->orderBy('id')
            ->get();

        $boards = DB::table('board_types')
            ->where('is_active', true)
            ->orderBy('id')
            ->limit(2)
            ->get();

        $boardsMap = DB::table('board_types')->pluck('slug', 'id'); // jsonb
        $currMap   = DB::table('currencies')->pluck('code', 'id');

        $start = now()->addDays(10)->toDateString();
        $end   = now()->addDays(20)->toDateString();
        $now   = now();

        $rules = [];

        foreach ($rooms as $idx => $room) {
            $occMax = max(1, (int) ($room->capacity_adults ?? 0) + (int) ($room->capacity_children ?? 0));

            foreach ($curr as $c) {
                foreach ($boards as $b) {
                    $boardLabel   = strtoupper(($boardsMap[$b->id]['tr'] ?? 'BOARD'));
                    $currencyCode = $currMap[$c->id] ?? 'CUR';

                    // default
                    $rules[] = [
                        'room_id'        => $room->id,
                        'currency_id'    => $c->id,
                        'board_type_id'  => $b->id,
                        'price_type'     => 'room_per_night',
                        'date_start'     => null,
                        'date_end'       => null,
                        'weekday_mask'   => 127,
                        'occupancy_min'  => 1,
                        'occupancy_max'  => $occMax,
                        'amount'         => 100 + ($idx * 5),
                        'priority'       => 10,
                        'allotment'      => 5,
                        'closed'         => false,
                        'cta'            => false,
                        'ctd'            => false,
                        'is_active'      => true,
                        'label'          => $boardLabel . ' · ' . $currencyCode . ' · Default',
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ];

                    // seasonal (higher priority)
                    $rules[] = [
                        'room_id'        => $room->id,
                        'currency_id'    => $c->id,
                        'board_type_id'  => $b->id,
                        'price_type'     => 'room_per_night',
                        'date_start'     => $start,
                        'date_end'       => $end,
                        'weekday_mask'   => 127,
                        'occupancy_min'  => 1,
                        'occupancy_max'  => $occMax,
                        'amount'         => 130 + ($idx * 5),
                        'priority'       => 5,
                        'allotment'      => 5,
                        'closed'         => false,
                        'cta'            => false,
                        'ctd'            => false,
                        'is_active'      => true,
                        'label'          => $boardLabel . ' · ' . $currencyCode . ' · Seasonal (' . $start . ' – ' . $end . ')',
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ];
                }
            }
        }

        DB::table('room_rate_rules')->insert($rules);
    }
}
