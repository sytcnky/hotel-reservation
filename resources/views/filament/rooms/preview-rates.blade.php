<table class="w-full text-sm">
    <thead>
    <tr class="border-b">
        <th class="text-left py-2 px-3">Tarih</th>
        <th class="text-left py-2 px-3">Kişi/Gece</th>
        <th class="text-left py-2 px-3">Kural Adı</th>
        <th class="text-left py-2 px-3">Yetişkin</th>
        <th class="text-left py-2 px-3">Çocuk</th>
        <th class="text-left py-2 px-3">Toplam</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($rows as $r)
    @php
    $mode = $r['price_mode'] ?? null;         // 'person' | 'room'
    $unit = (float) ($r['unit_amount'] ?? 0); // kural birim fiyatı
    $adults = (int) data_get($r, 'meta.adults', 0);
    $childUnits = (float) data_get($r, 'meta.children_units_total', 0.0);

    if ($mode === 'person') {
    $adultTotal = $adults * $unit;
    $childTotal = $childUnits;
    } elseif ($mode === 'room') {
    $adultTotal = $unit;   // oda bazında tek fiyat
    $childTotal = 0.0;     // çocuk indirimi uygulanmaz
    } else {
    $adultTotal = 0.0;
    $childTotal = 0.0;
    }
    @endphp

    <tr class="border-b">
        <td class="py-2 px-3">{{ $r['date'] }}</td>

        @if (! $r['ok'])
        <td class="py-2 px-3">—</td>
        <td class="py-2 px-3">{{ $r['closed'] ? 'Kapalı' : 'admin.' }}</td>
        <td class="py-2 px-3">—</td>
        <td class="py-2 px-3">—</td>
        <td class="py-2 px-3">—</td>
        @else
        <td class="py-2 px-3">{{ $mode === 'person' ? 'Kişi' : 'Gece' }}</td>
        <td class="py-2 px-3">{{ $r['label'] ?: '—' }}</td>

        <td class="py-2 px-3">
            {{ number_format($adultTotal, 2, ',', '.') }} {{ $currency }}
        </td>
        <td class="py-2 px-3">
            {{ number_format($childTotal, 2, ',', '.') }} {{ $currency }}
        </td>
        <td class="py-2 px-3">
            {{ number_format($r['total'], 2, ',', '.') }} {{ $currency }}
        </td>
        @endif
    </tr>
    @endforeach
    </tbody>
</table>
