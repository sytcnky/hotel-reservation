@php
    $items = collect($order->items_for_infolist ?? []);
    $discounts = collect($order->discounts_for_infolist ?? []);

    $labels = [
        'hotel_name'       => 'Otel',
        'room_name'        => 'Oda',
        'board_type'       => 'Konaklama Tipi',

        'villa_name'       => 'Villa',
        'total'            => 'Toplam Ücret',
        'remaining'        => 'Kalan Tutar',

        'tour_name'        => 'Tur',
        'excursion_name'   => 'Tur',

        'route'            => 'Rota',
        'vehicle'          => 'Araç',

        'checkin'          => 'Giriş Tarihi',
        'checkout'         => 'Çıkış Tarihi',
        'departure_date'   => 'Geliş',
        'departure_flight' => 'Uçuş',
        'return_date'      => 'Dönüş',
        'return_flight'    => 'Dönüş Uçuşu',

        'date'             => 'Tarih',
        'pax'              => 'Misafirler',
    ];
@endphp



@if($items->isNotEmpty())
    @foreach($items as $it)
        {{-- Item Card --}}
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
               style="
                   margin:0 0 14px 0;
                   background:#ffffff;
                   border:1px solid #e6e8ec;
                   border-radius:10px;
               ">
            <tr>
                <td style="padding:14px;">

                    {{-- Image + content side-by-side --}}
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            @if(!empty($it['image']))
                                <td width="25%" valign="top" style="padding-right:18px;">
                                    <img src="{{ $it['image'] }}"
                                         width="100%"
                                         style="display:block; border-radius:8px;"
                                         alt="{{ $it['title'] ?? 'Ürün görseli' }}">
                                </td>
                            @endif

                            <td valign="top" style="font-size:14px; line-height:20px; color:#0f172a;">

                                {{-- Title --}}
                                @if(!empty($it['title']))
                                    <div style="font-size:15px; font-weight:700; margin:0 0 8px 0;">
                                        {{ $it['title'] }}
                                    </div>
                                @endif

                                {{-- Details (label top, value bottom) --}}
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                    @php
                                        $dateKeys = ['checkin','checkout','departure_date','return_date','date'];
                                    @endphp

                                    @foreach($labels as $key => $label)
                                        @php
                                            $value = $it[$key] ?? null;

                                            if ($value !== null && $value !== '' && in_array($key, $dateKeys, true)) {
                                                $value = \App\Support\Date\DatePresenter::human((string) $value, locale: app()->getLocale(), pattern: 'd F Y');
                                            }
                                        @endphp

                                        @continue($value === null || $value === '')

                                        <tr>
                                            <td style="padding:0 0 6px 0;">
                                                <div style="font-size:14px; color:#6b7280; line-height:18px;">
                                                    {{ $label }}
                                                </div>
                                                <div style="font-size:14px; color:#0f172a; line-height:20px;">
                                                    {{ $value }}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>

                            </td>
                        </tr>
                    </table>

                    {{-- Price --}}
                    @if(!empty($it['paid']))
                        <div style="height:1px; background:#eef0f3; margin:12px 0;"></div>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td style="font-size:14px; color:#6b7280;">
                                    Tutar
                                </td>
                                <td align="right" style="font-size:16px; font-weight:700; color:#0f172a; white-space:nowrap;">
                                    {{ $it['paid'] }}
                                </td>
                            </tr>
                        </table>
                    @endif

                </td>
            </tr>
        </table>
    @endforeach
@endif

{{-- Discounts --}}
@if($discounts->isNotEmpty())
    <div style="margin:12px 0 6px 0; font-size:15px; font-weight:700;">
        Uygulanmış İndirimler
    </div>

    @foreach($discounts as $d)
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
               style="margin:0 0 6px 0; font-size:14px;">
            <tr>
                <td>
                    {{ $d['label'] ?? '-' }}
                </td>
                <td align="right" style="white-space:nowrap;">
                    <strong>-{{ $d['amount'] ?? '-' }}</strong>
                </td>
            </tr>
        </table>
    @endforeach

    <div style="height:1px; background:#e6e8ec; margin:12px 0;"></div>
@endif

{{-- Total --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
       style="font-size:16px; font-weight:700;">
    <tr>
        <td>Toplam Tutar</td>
        <td align="right" style="white-space:nowrap;">
            {{ \App\Support\Currency\CurrencyPresenter::format($order->total_amount ?? null, $order->currency ?? null) }}
        </td>
    </tr>
</table>
