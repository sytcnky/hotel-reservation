<table class="w-full text-sm">
    <thead>
    <tr class="border-b">
        <th class="text-left py-2 px-3">Tarih</th>
        <th class="text-left py-2 px-3">Kural Adı</th>
        <th class="text-left py-2 px-3">Gece Fiyatı</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($rows as $r)
    @php
    $unit   = (float) ($r['unit_amount'] ?? 0);
    $ok     = (bool) ($r['ok'] ?? false);
    @endphp

    <tr class="border-b">
        <td class="py-2 px-3">{{ $r['date'] }}</td>

        @if (! $ok)
        <td class="py-2 px-3">{{ $r['label'] ?? '—' }}</td>
        <td class="py-2 px-3">—</td>
        @else
        <td class="py-2 px-3">{{ $r['label'] ?: '—' }}</td>
        <td class="py-2 px-3">
            {{ number_format($unit, 2, ',', '.') }} {{ $currency }}
        </td>
        @endif
    </tr>
    @endforeach
    </tbody>
</table>
