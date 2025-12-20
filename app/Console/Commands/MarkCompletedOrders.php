<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class MarkCompletedOrders extends Command
{
    protected $signature = 'app:mark-completed-orders {--dry-run : Sadece sayar, DB yazmaz}';
    protected $description = 'Confirmed siparişlerde, kalemlerden hesaplanan en son rezervasyon tarihi geçmişse status=completed yapar.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $today = Carbon::today(); // server tz (ICR’de prod’a geçerken TZ standardına göre çalışır)

        // Yalnızca operasyon onaylı siparişler completed olabilir.
        // Cancelled zaten ayrı.
        $orders = Order::query()
            ->where('status', 'confirmed')
            ->with(['items'])
            ->get();

        $toComplete = [];

        foreach ($orders as $order) {
            $latest = $this->resolveLatestReservationDate($order);

            if (! $latest) {
                continue;
            }

            // En son rezervasyon tarihi bugün'den küçükse tamamlandı say.
            if ($latest->lt($today)) {
                $toComplete[] = [
                    'order_id' => $order->id,
                    'code'     => $order->code,
                    'latest'   => $latest->toDateString(),
                ];
            }
        }

        if (empty($toComplete)) {
            $this->info('0 sipariş completed yapılacak.');
            return self::SUCCESS;
        }

        $this->info(count($toComplete) . ' sipariş completed yapılacak.');

        if ($dryRun) {
            foreach ($toComplete as $row) {
                $this->line(" - {$row['code']} (#{$row['order_id']}), latest={$row['latest']}");
            }
            $this->warn('dry-run: DB güncellemesi yapılmadı.');
            return self::SUCCESS;
        }

        $ids = array_map(fn ($r) => $r['order_id'], $toComplete);

        Order::query()
            ->whereIn('id', $ids)
            ->where('status', 'confirmed')
            ->update([
                'status'     => 'completed',
                'updated_at' => now(),
            ]);

        $this->info('Güncellendi: ' . count($ids));

        return self::SUCCESS;
    }

    /**
     * Completed hesabı yalnızca: hotel_room, villa, transfer, tour snapshot’larından yapılır.
     * hotel item’ları yok sayılır.
     */
    private function resolveLatestReservationDate(Order $order): ?Carbon
    {
        $dates = [];

        foreach ($order->items as $item) {
            $type = (string) ($item->product_type ?? '');
            $s    = (array) ($item->snapshot ?? []);

            if ($type === 'hotel_room') {
                $d = $this->parseYmd($s['checkout'] ?? null);
                if ($d) $dates[] = $d;
                continue;
            }

            if ($type === 'villa') {
                $d = $this->parseYmd($s['checkout'] ?? null);
                if ($d) $dates[] = $d;
                continue;
            }

            if ($type === 'tour' || $type === 'excursion') {
                $d = $this->parseYmd($s['date'] ?? null);
                if ($d) $dates[] = $d;
                continue;
            }

            if ($type === 'transfer') {
                // Roundtrip ise return_date; yoksa departure_date (oneway)
                $d = $this->parseYmd($s['return_date'] ?? null) ?: $this->parseYmd($s['departure_date'] ?? null);
                if ($d) $dates[] = $d;
                continue;
            }

            // Diğer product_type’lar ignore
        }

        if (empty($dates)) {
            return null;
        }

        // En geç tarih
        usort($dates, fn (Carbon $a, Carbon $b) => $a->getTimestamp() <=> $b->getTimestamp());
        return end($dates) ?: null;
    }

    private function parseYmd($value): ?Carbon
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
