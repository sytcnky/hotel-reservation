<?php

namespace App\Support\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class SequenceService
{
    /**
     * @param 'order'|'payment' $scope
     */
    public static function next(string $scope): array
    {
        $today = CarbonImmutable::today()->toDateString(); // 'YYYY-MM-DD'
        $table = $scope === 'payment' ? 'payment_counters' : 'order_counters';

        return DB::transaction(function () use ($table, $today, $scope) {
            // satırı kilitle
            $row = DB::table($table)->where('counter_date', $today)->lockForUpdate()->first();

            if (!$row) {
                DB::table($table)->insert([
                    'counter_date' => $today,
                    'last_number'  => 0,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
                $row = (object)['last_number' => 0];
            }

            $next = $row->last_number + 1;

            DB::table($table)
                ->where('counter_date', $today)
                ->update(['last_number' => $next, 'updated_at' => now()]);

            // Format: O20251112-000123  /  P20251112-000123
            $prefix = $scope === 'payment' ? 'P' : 'O';
            $ymd    = now()->format('Ymd');
            $code   = sprintf('%s%s-%06d', $prefix, $ymd, $next);

            return ['number' => $code, 'seq' => $next, 'date' => $ymd];
        });
    }
}
