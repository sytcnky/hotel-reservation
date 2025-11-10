<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomRateRule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'room_id',
        'currency_id',
        'board_type_id',
        'price_type',
        'date_start',
        'date_end',
        'weekday_mask',
        'occupancy_min',
        'occupancy_max',
        'amount',
        'los_min',
        'los_max',
        'allotment',
        'closed',
        'cta',
        'ctd',
        'priority',
        'is_active',
        'note'
    ];

    protected $casts = [
        'date_start' => 'date',
        'date_end' => 'date',
        'closed' => 'boolean',
        'cta' => 'boolean',
        'ctd' => 'boolean',
        'is_active' => 'boolean',
        'weekday_mask' => 'integer',
        'occupancy_min' => 'integer',
        'occupancy_max' => 'integer',
        'los_min' => 'integer',
        'los_max' => 'integer',
        'allotment' => 'integer',
        'priority' => 'integer',
        'amount' => 'decimal:2',
    ];

    // ilişkiler
    public function room(): BelongsTo { return $this->belongsTo(Room::class); }
    public function currency(): BelongsTo { return $this->belongsTo(Currency::class); }
    public function boardType(): BelongsTo { return $this->belongsTo(BoardType::class); }

    // yardımcı: gün dizisinden mask üret
    public static function weekdaysToMask(?array $days): int
    {
        // $days => [1..7], 1=Mon ... 7=Sun
        $mask = 0;
        foreach ((array) $days as $d) {
            $bit = max(1, min(7, (int)$d));
            $mask |= (1 << ($bit - 1));
        }
        return $mask;
    }
}
