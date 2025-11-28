<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Order extends Model
{
    use SoftDeletes;

    protected $table = 'orders';

    protected $guarded = [];

    protected $casts = [
        'total_amount'     => 'float',
        'total_prepayment' => 'float',
        'discount_amount'  => 'float',
        'billing_address'  => 'array',
        'metadata'         => 'array',
        'paid_at'          => 'datetime',
        'cancelled_at'     => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (blank($order->code)) {
                $last = static::withTrashed()
                    ->where('code', 'LIKE', 'ORD-%')
                    ->orderByDesc('id')
                    ->value('code');

                $n = 1;
                if ($last && preg_match('/ORD-(\d+)/', $last, $m)) {
                    $n = ((int) $m[1]) + 1;
                }

                $order->code = sprintf('ORD-%06d', $n);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Order Items (Infolist için)
    |--------------------------------------------------------------------------
    */

    public function getItemsForInfolistAttribute(): array
    {
        return $this->items
            ->map(function (OrderItem $item): array {
                $s        = (array) ($item->snapshot ?? []);
                $type     = $item->product_type;
                $currency = strtoupper($item->currency ?? $this->currency ?? 'TRY');

                $image = $this->resolveItemImageFromSnapshot($s);

                // Para formatlayıcı
                $formatMoney = function ($amount) use ($currency): ?string {
                    if ($amount === null) {
                        return null;
                    }

                    $amount = (float) $amount;

                    return number_format($amount, 0, ',', '.') . ' ' . $currency;
                };

                // Tarih formatlayıcı
                $formatDateTime = function (?string $date, ?string $time = null): ?string {
                    if (blank($date)) {
                        return null;
                    }

                    $value = trim($date . ' ' . (string) $time);

                    try {
                        return Carbon::parse($value)->format('d.m.Y H:i');
                    } catch (\Throwable $e) {
                        return $value;
                    }
                };

                $formatDateOnly = function (?string $value): ?string {
                    if (blank($value)) {
                        return null;
                    }

                    try {
                        return Carbon::parse($value)->format('d.m.Y');
                    } catch (\Throwable $e) {
                        return $value;
                    }
                };


                // Kişi sayısı — sadece sayısal ham veri
                $pax = null;

                $a = (int) ($s['adults']   ?? 0);
                $c = (int) ($s['children'] ?? 0);
                $i = (int) ($s['infants']  ?? 0);

                $paxParts = [];

                if ($a) {
                    // admin.orders.pax.adult: ':count Yetişkin' | ':count Yetişkin'
                    $paxParts[] = trans_choice('admin.orders.pax.adult', $a, ['count' => $a]);
                }

                if ($c) {
                    // admin.orders.pax.child: ':count Çocuk' | ':count Çocuk'
                    $paxParts[] = trans_choice('admin.orders.pax.child', $c, ['count' => $c]);
                }

                if ($i) {
                    // admin.orders.pax.infant: ':count Bebek' | ':count Bebek'
                    $paxParts[] = trans_choice('admin.orders.pax.infant', $i, ['count' => $i]);
                }

                if ($paxParts) {
                    $pax = implode(', ', $paxParts);
                }

                // Ortak base
                $base = [
                    'type'  => $type,
                    'image' => $image,
                    'pax'   => $pax,
                ];

                // Otel
                if ($type === 'hotel' || $type === 'hotel_room') {
                    return $base + [
                            'hotel_name' => $s['hotel_name']      ?? $s['title'] ?? null,
                            'room_name'  => $s['room_name']       ?? null,
                            'board_type' => $s['board_type_name'] ?? null,
                            'checkin'    => $formatDateOnly($s['checkin']  ?? null),
                            'checkout'   => $formatDateOnly($s['checkout'] ?? null),
                            'paid'       => $formatMoney($item->total_price ?? $item->unit_price),
                        ];
                }

                // Villa
                if ($type === 'villa') {
                    $total = isset($s['price_total'])      ? (float) $s['price_total']      : null;
                    $pre   = isset($s['price_prepayment']) ? (float) $s['price_prepayment'] : null;

                    $remaining = null;
                    $rateText  = null;

                    if ($total !== null && $pre !== null) {
                        $remaining = max($total - $pre, 0);

                        if ($total > 0) {
                            $rate     = round($pre / $total * 100);
                            $rateText = " ({$rate}%)";
                        }
                    }

                    return $base + [
                            'villa_name' => $s['villa_name'] ?? $s['title'] ?? null,
                            'checkin'    => $formatDateOnly($s['checkin']  ?? null),
                            'checkout'   => $formatDateOnly($s['checkout'] ?? null),
                            'paid'       => $pre        !== null ? $formatMoney($pre)         . $rateText : null,
                            'remaining'  => $remaining  !== null ? $formatMoney($remaining)   : null,
                            'total'      => $total      !== null ? $formatMoney($total)       : null,
                        ];
                }

                // Tur
                if ($type === 'tour' || $type === 'excursion') {
                    // Tarih + saat birleştir
                    $tourDateTime = null;

                    if (! empty($s['date'])) {
                        $tourDateTime = $s['date'];

                        // Snapshot'ta hangi alan varsa onu ekle
                        if (! empty($s['pickup_time'])) {
                            $tourDateTime .= ' ' . $s['pickup_time'];
                        } elseif (! empty($s['time'])) {
                            $tourDateTime .= ' ' . $s['time'];
                        }
                    }

                    return $base + [
                            'tour_name' => $s['tour_name'] ?? $s['title'] ?? null,
                            // Saat varsa $formatDate ile (d.m.Y H:i), yoksa sadece tarihi göster
                            'date'      => $tourDateTime
                                ? $formatDateOnly($tourDateTime)
                                : $formatDateOnly($s['date'] ?? null),
                            'paid'      => $formatMoney($item->total_price ?? $item->unit_price),
                        ];
                }

                // Transfer
                if ($type === 'transfer') {
                    $route = null;

                    if (! empty($s['from_label']) || ! empty($s['to_label'])) {
                        $route = trim(
                            (string) ($s['from_label'] ?? '') .
                            ' → ' .
                            (string) ($s['to_label']   ?? '')
                        );
                    }

                    $departureDate = $formatDateTime(
                        $s['departure_date']       ?? null,
                        $s['pickup_time_outbound'] ?? null,
                    );

                    $returnDate = $formatDateTime(
                        $s['return_date']         ?? null,
                        $s['pickup_time_return'] ?? null,
                    );

                    return $base + [
                            'route'            => $route,
                            // snapshot’ta şu an sadece vehicle_id ve vehicle_image var,
                            // bu yüzden isim boş geliyor:
                            'vehicle'          => $s['vehicle_name'] ?? null,
                            'departure_date'   => $departureDate,
                            'return_date'      => $returnDate,
                            'departure_flight' => $s['flight_number_outbound'] ?? null,
                            'return_flight'    => $s['flight_number_return']   ?? null,
                            'paid'             => $formatMoney($item->total_price ?? $item->unit_price),
                        ];
                }


                // Fallback – tip tanımsızsa en azından tutarı göster
                return $base + [
                        'paid' => $formatMoney($item->total_price ?? $item->unit_price),
                    ];
            })
            ->values()
            ->all();
    }

    /**
     * Snapshot içinden kart görselini çözer.
     *
     * Desteklenen anahtarlar:
     * - hotel_image
     * - villa_image
     * - vehicle_image
     * - cover_image
     *
     * Hem array (['thumb' => ...]) hem de direkt string URL durumunu destekler.
     */
    protected function resolveItemImageFromSnapshot(array $s): ?string
    {
        foreach (['hotel_image', 'villa_image', 'vehicle_image', 'cover_image'] as $key) {
            if (!array_key_exists($key, $s) || blank($s[$key])) {
                continue;
            }

            $value = $s[$key];

            // Spatie normalize edilmiş array: ['thumb' => '...', ...]
            if (is_array($value) && !empty($value['thumb'])) {
                return $value['thumb'];
            }

            // Transfer özelinde olduğu gibi direkt string URL
            if (is_string($value)) {
                return $value;
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function items()
    {
        return $this->hasMany(\App\Models\OrderItem::class);
    }
}
