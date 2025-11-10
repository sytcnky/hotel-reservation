<form method="GET" action="{{ route('hotels') }}" class="border rounded p-3 mb-4 bg-light">
    <!-- Kategori -->
    <div class="mb-3">
        <label class="form-label small">Konaklama Türü</label>
        <select name="category" class="form-select">
            <option value="">Tümü</option>
            <option value="Apart Otel" {{ request('category') == 'Apart Otel' ? 'selected' : '' }}>Apart Otel</option>
            <option value="Butik Otel" {{ request('category') == 'Butik Otel' ? 'selected' : '' }}>Butik Otel</option>
            <option value="Tatil Köyü" {{ request('category') == 'Tatil Köyü' ? 'selected' : '' }}>Tatil Köyü</option>
        </select>
    </div>

    <!-- Yıldız -->
    <div class="mb-3">
        <label class="form-label small">Yıldız</label>
        <select name="stars" class="form-select">
            <option value="">Tümü</option>
            @for ($i = 5; $i >= 1; $i--)
            <option value="{{ $i }}" {{ request('stars') == $i ? 'selected' : '' }}>{{ $i }} yıldız</option>
            @endfor
        </select>
    </div>

    <!-- Konum -->
    <div class="mb-3">
        <label class="form-label small">Konum</label>
        <select name="region" class="form-select">
            <option value="">Tümü</option>
            <option value="Muğla" {{ request('region') == 'Muğla' ? 'selected' : '' }}>Muğla</option>
            <option value="Antalya" {{ request('region') == 'Antalya' ? 'selected' : '' }}>Antalya</option>
        </select>
    </div>

    <!-- Konsept -->
    <div class="mb-3">
        <label class="form-label small">Konsept</label>
        <select name="board_type" class="form-select">
            <option value="">Tümü</option>
            <option value="Her şey dahil" {{ request('board_type') == 'Her şey dahil' ? 'selected' : '' }}>Her şey dahil</option>
            <option value="Oda + Kahvaltı" {{ request('board_type') == 'Oda + Kahvaltı' ? 'selected' : '' }}>Oda + Kahvaltı</option>
            <option value="Ultra Her şey dahil" {{ request('board_type') == 'Ultra Her şey dahil' ? 'selected' : '' }}>Ultra Her şey dahil</option>
        </select>
    </div>

    <!-- Fiyat Aralığı -->
    <div class="mb-3">
        <label class="form-labe small">Fiyat (gece)</label>
        <div class="d-flex gap-2">
            <input type="number" name="price_min" class="form-control" placeholder="Min"
                   value="{{ request('price_min') }}">
            <input type="number" name="price_max" class="form-control" placeholder="Max"
                   value="{{ request('price_max') }}">
        </div>
    </div>

    <hr>

    <!-- Olanaklar -->
    <div class="mb-3">
        <label class="form-label d-block small">Tesis Olanakları</label>

        @php
        // Otelleri sayfada zaten gösteriyorsak onları kullan, yoksa önce topla
        $allFacilities = collect($hotels ?? [])
        ->pluck('facilities')   // her otelin facilities dizisi
        ->flatten()
        ->unique()
        ->sort()
        ->values();

        $selectedFacilities = request()->input('facilities', []);
        @endphp


        @foreach ($allFacilities as $index => $facility)
        @php $isChecked = in_array($facility, $selectedFacilities); @endphp

        <div class="form-check {{ $index >= 5 && !$isChecked ? 'd-none extra-facility' : '' }}">
            <input
                class="form-check-input"
                type="checkbox"
                name="facilities[]"
                value="{{ $facility }}"
                id="facility_{{ Str::slug($facility) }}"
                {{ $isChecked ? 'checked' : '' }}
            >
            <label class="form-check-label" for="facility_{{ Str::slug($facility) }}">
                {{ $facility }}
            </label>
        </div>
        @endforeach


        @if ($allFacilities->count() > 5)
        <button
            type="button"
            class="btn btn-link p-0 mt-1 btn-sm"
            id="toggleFacilitiesBtn"
        >
            Tümünü Göster
        </button>
        @endif
    </div>
    @php
    $childFeatures = collect($hotels ?? [])
    ->pluck('features') // her otelin features dizisi
    ->flatten()
    ->filter(fn($group) => $group->category === 'Çocuk Hizmetleri')
    ->pluck('items')
    ->flatten()
    ->unique()
    ->sort()
    ->values();

    $selectedChildItems = request()->input('children_features', []);
    @endphp

    <hr>

    <!-- Çocuk Hizmetleri -->
    <div class="mb-3">
        <label class="form-label d-block small">Çocuk Hizmetleri</label>

        @foreach ($childFeatures as $index => $item)
        @php $isChecked = in_array($item, $selectedChildItems); @endphp

        <div class="form-check {{ $index >= 5 && !$isChecked ? 'd-none extra-child-feature' : '' }}">
            <input
                class="form-check-input"
                type="checkbox"
                name="children_features[]"
                value="{{ $item }}"
                id="child_{{ Str::slug($item) }}"
                {{ $isChecked ? 'checked' : '' }}
            >
            <label class="form-check-label" for="child_{{ Str::slug($item) }}">
                {{ $item }}
            </label>
        </div>
        @endforeach

        @if ($childFeatures->count() > 5)
        <button type="button" class="btn btn-link p-0 mt-1 btn-sm" id="toggleChildFeaturesBtn">
            Tümünü Göster
        </button>
        @endif
    </div>


    <!-- Gönder -->
    <div class="mb-3">
        <button type="submit" class="btn btn-primary w-100">Filtrele</button>
    </div>
</form>
