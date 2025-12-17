<?php

namespace App\Console\Commands;

use App\Models\PaymentAttempt;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ExpirePendingPaymentAttempts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:expire-pending-payment-attempts {--minutes=10 : Pending TTL (dakika)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire pending payment attempts that exceeded TTL (defaults to 10 minutes).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $ttlMinutes = (int) $this->option('minutes');
        if ($ttlMinutes <= 0) {
            $ttlMinutes = 10;
        }

        $cutoff = Carbon::now()->subMinutes($ttlMinutes);

        // started_at öncelikli; yoksa created_at kullan
        $baseQuery = PaymentAttempt::query()
            ->where('status', PaymentAttempt::STATUS_PENDING)
            ->whereNull('completed_at')
            ->where(function ($q) use ($cutoff) {
                $q->whereNotNull('started_at')->where('started_at', '<=', $cutoff)
                    ->orWhere(function ($q2) use ($cutoff) {
                        $q2->whereNull('started_at')->where('created_at', '<=', $cutoff);
                    });
            });

        $total = (clone $baseQuery)->count();

        if ($total <= 0) {
            $this->info('No pending payment attempts to expire.');
            return self::SUCCESS;
        }

        $this->info("Expiring {$total} pending payment attempts (TTL={$ttlMinutes} min, cutoff={$cutoff->toDateTimeString()})...");

        $expired = 0;

        // Büyük tabloda güvenli çalışsın diye chunk ile güncelliyoruz
        (clone $baseQuery)
            ->orderBy('id')
            ->select('id')
            ->chunkById(500, function ($rows) use (&$expired) {
                $ids = $rows->pluck('id')->all();

                $affected = PaymentAttempt::query()
                    ->whereIn('id', $ids)
                    ->where('status', PaymentAttempt::STATUS_PENDING)
                    ->whereNull('completed_at')
                    ->update([
                        'status'        => PaymentAttempt::STATUS_EXPIRED,
                        'completed_at'  => now(),
                        'error_code'    => 'ttl_expired',
                        'error_message' => 'TTL dolduğu için ödeme denemesi zaman aşımına uğradı.',
                    ]);

                $expired += (int) $affected;
            });

        $this->info("Expired: {$expired}");

        return self::SUCCESS;
    }
}
