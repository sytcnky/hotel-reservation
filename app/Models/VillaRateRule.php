<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VillaRateRule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'villa_id',
        'currency_id',
        'label',
        'priority',
        'date_start',
        'date_end',
        'weekday_mask',
        'amount',
        'closed',
        'cta',
        'ctd',
        'is_active',
        'note',
        'min_nights',
        'max_nights',
    ];

    protected $casts = [
        'date_start' => 'date',
        'date_end'   => 'date',
        'closed'     => 'boolean',
        'cta'        => 'boolean',
        'ctd'        => 'boolean',
        'is_active'  => 'boolean',
        'amount'     => 'decimal:2',
        'min_nights' => 'integer',
        'max_nights' => 'integer',
    ];

    public function villa(): BelongsTo
    {
        return $this->belongsTo(Villa::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public static function weekdaysToMask(array $weekdays): int
    {
        $mask = 0;

        foreach ($weekdays as $day) {
            $d = (int) $day;

            if ($d >= 1 && $d <= 7) {
                $mask |= (1 << ($d - 1));
            }
        }

        return $mask;
    }

    public static function maskToWeekdays(?int $mask): array
    {
        if (! $mask) {
            return [];
        }

        $days = [];

        for ($i = 1; $i <= 7; $i++) {
            if ($mask & (1 << ($i - 1))) {
                $days[] = $i;
            }
        }

        return $days;
    }
}
