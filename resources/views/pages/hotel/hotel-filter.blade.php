{{-- resources/views/pages/hotel/hotel-filter.blade.php --}}

<form id="hotelFilterForm" method="GET" action="{{ localized_route('hotels') }}" class="border rounded p-3 mb-4 bg-light" autocomplete="off">

    @php
        $filters = $filters ?? [];

        $categoryId  = (int) ($filters['category_id'] ?? 0);
        $boardTypeId = (int) ($filters['board_type_id'] ?? 0);

        $checkinVal  = (string) ($filters['checkin'] ?? '');

        $max = (int) ($filters['maxGuests'] ?? ($maxGuests ?? 1));
        $max = max(1, $max);

        $selectedGuests = (int) ($filters['guests'] ?? 2);
        $selectedGuests = max(1, min($max, $selectedGuests));

        // Konum select datasetleri
        $cityOptions     = $cities ?? collect();
        $districtOptions = $districts ?? collect();
        $areaOptions     = $areas ?? collect();

        $cityId     = (int) ($filters['city_id'] ?? 0);
        $districtId = (int) ($filters['district_id'] ?? 0);
        $areaId     = (int) ($filters['area_id'] ?? 0);

        $showCity     = $cityOptions->count() > 1;
        $showDistrict = $districtOptions->count() > 1;
        $showArea     = $areaOptions->count() > 1;

        $sortBy = (string) ($filters['sort_by'] ?? '');
    @endphp

    {{-- Kategori --}}
    <div class="mb-3">
        <label class="form-label small">{{ t('hotel_filter.label.category') }}</label>
        <select name="category_id" class="form-select">
            <option value="">{{ t('hotel_filter.option.select') }}</option>
            @foreach(($categories ?? collect()) as $cat)
                <option value="{{ $cat->id }}" {{ $categoryId === (int) $cat->id ? 'selected' : '' }}>
                    {{ $cat->name_l }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Tarih (range) --}}
    <div class="mb-3">
        <label class="form-label small">{{ t('hotel_filter.label.date_range') }}</label>

        <input
            type="text"
            id="checkin"
            name="checkin"
            class="form-control"
            placeholder="{{ t('hotel_filter.placeholder.date') }}"
            value="{{ $checkinVal }}"
            autocomplete="off"
        >
    </div>

    {{-- Konum --}}
    {{-- City / District / Area --}}
    @if($showCity)
        <div class="mb-3">
            <label class="form-label small">{{ t('hotel_filter.label.city') }}</label>
            <select name="city_id" class="form-select">
                <option value="">{{ t('hotel_filter.option.select') }}</option>
                @foreach($cityOptions as $opt)
                    <option value="{{ $opt->id }}" {{ $cityId === (int) $opt->id ? 'selected' : '' }}>
                        {{ $opt->name }}
                    </option>
                @endforeach
            </select>
        </div>
    @else
        @if($cityId)
            <input type="hidden" name="city_id" value="{{ $cityId }}">
        @endif
    @endif

    @if($showDistrict)
        <div class="mb-3">
            <label class="form-label small">{{ t('hotel_filter.label.district') }}</label>
            <select name="district_id" class="form-select">
                <option value="">{{ t('hotel_filter.option.select') }}</option>
                @foreach($districtOptions as $opt)
                    <option value="{{ $opt->id }}" {{ $districtId === (int) $opt->id ? 'selected' : '' }}>
                        {{ $opt->name }}
                    </option>
                @endforeach
            </select>
        </div>
    @else
        @if($districtId)
            <input type="hidden" name="district_id" value="{{ $districtId }}">
        @endif
    @endif

    @if($showArea)
        <div class="mb-3">
            <label class="form-label small">{{ t('hotel_filter.label.area') }}</label>
            <select name="area_id" class="form-select">
                <option value="">{{ t('hotel_filter.option.select') }}</option>
                @foreach($areaOptions as $opt)
                    <option value="{{ $opt->id }}" {{ $areaId === (int) $opt->id ? 'selected' : '' }}>
                        {{ $opt->name }}
                    </option>
                @endforeach
            </select>
        </div>
    @else
        @if($areaId)
            <input type="hidden" name="area_id" value="{{ $areaId }}">
        @endif
    @endif

    {{-- Board Type --}}
    <div class="mb-3">
        <label class="form-label small">{{ t('hotel_filter.label.board_type') }}</label>
        <select name="board_type_id" class="form-select">
            <option value="">{{ t('hotel_filter.option.select') }}</option>
            @foreach(($boardTypes ?? collect()) as $bt)
                <option value="{{ $bt->id }}" {{ $boardTypeId === (int) $bt->id ? 'selected' : '' }}>
                    {{ $bt->name_l }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Misafir --}}
    <div class="mb-3">
        <label class="form-label small">{{ t('hotel_filter.label.guests') }}</label>

        <select name="guests" class="form-select">
            @for($i = 1; $i <= $max; $i++)
                <option value="{{ $i }}" {{ $selectedGuests === $i ? 'selected' : '' }}>
                    {{ $i }}
                </option>
            @endfor
        </select>
    </div>

    <hr>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-fill">
            {{ t('hotel_filter.action.apply') }}
        </button>

        <a class="btn btn-outline-secondary flex-fill"
           href="{{ localized_route('hotels') }}">
            {{ t('hotel_filter.action.clear') }}
        </a>
    </div>
</form>
