{{-- home hero tabs bloğu (senin mevcut home blade içine birebir koyabilirsin) --}}

<div class="w-100 rounded mb-5">
    <!-- Nav Tabs -->
    <ul class="nav nav-tabs hero-tabs justify-content-center border-0 gap-1 position-relative"
        id="searchTabs" role="tablist">

        {{-- HERO Transparent --}}
        <x-responsive-image
            :image="$page->home_hero_transparent_image"
            preset="listing-card"
            class="hero-tabs-girl position-absolute bottom-0 end-0"
        />

        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-otel" data-bs-toggle="tab" data-bs-target="#content-otel"
                    type="button" role="tab">
                <i class="fi fi-rs-bed-alt d-block d-lg-none fs-1 pb-1"></i>Konaklama
            </button>
        </li>

        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-transfer" data-bs-toggle="tab" data-bs-target="#content-transfer"
                    type="button" role="tab">
                <i class="fi fi-rs-car-alt d-block d-lg-none fs-1 pb-1"></i>Transfer
            </button>
        </li>

        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-tur" data-bs-toggle="tab" data-bs-target="#content-tur"
                    type="button" role="tab">
                <i class="fi fi-rs-swimmer d-block d-lg-none fs-1 pb-1"></i>Günlük Tur
            </button>
        </li>
    </ul>

    <!-- Tab İçerikleri -->
    <div class="tab-content bg-white rounded-3 p-3 p-lg-5" id="searchTabsContent">

        {{-- HOTEL --}}
        <div class="tab-pane fade show active" id="content-otel" role="tabpanel">
            <form id="homeHotelSearchForm"
                  class="row g-3 needs-validation"
                  action="{{ localized_route('hotels') }}"
                  method="GET"
                  autocomplete="off"
                  novalidate
            >

                {{-- Tarih (range) --}}
                <div class="col-md-6">
                    <label for="home_hotel_checkin" class="form-label">Giriş - Çıkış Tarihi</label>
                    <div class="input-group">
                        <input type="text"
                               id="home_hotel_checkin"
                               name="checkin"
                               class="form-control date-input"
                               placeholder="Tarih seçin"
                               autocomplete="off"
                               required>
                        <span class="input-group-text bg-white"><i class="fi fi-rr-calendar"></i></span>
                    </div>
                </div>

                {{-- Kişi seçimi (adult + child) --}}
                <div class="col-md-4 position-relative">
                    <label for="home_hotel_guestInput" class="form-label">Kişi Sayısı</label>

                    <div class="guest-picker-wrapper position-relative" data-home-hotel-guests>
                        <div class="input-group">
                            <input type="text"
                                   id="home_hotel_guestInput"
                                   class="form-control guest-wrapper"
                                   placeholder="Kişi sayısı seçin"
                                   readonly>
                            <span class="input-group-text bg-white"><i class="fi fi-rr-user"></i></span>
                        </div>

                        {{-- Hidden: guestpicker bu ikisini günceller --}}
                        <input type="hidden" name="adults" value="2">
                        <input type="hidden" name="children" value="0">

                        {{-- Hidden: listing filtresi --}}
                        <input type="hidden" name="guests" value="2" data-home-hotel-guests-total>

                        {{-- Dropdown --}}
                        <div class="guest-dropdown border rounded shadow-sm bg-white p-3 position-absolute w-100"
                             style="z-index: 10; top: 100%; display: none;">

                            {{-- Yetişkin --}}
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Yetişkin</span>
                                <div class="input-group input-group-sm" style="width: 120px;">
                                    <button type="button" class="btn btn-outline-secondary minus" data-type="adult">−</button>
                                    <input type="text" class="form-control text-center" data-type="adult" value="2" readonly>
                                    <button type="button" class="btn btn-outline-secondary plus" data-type="adult">+</button>
                                </div>
                            </div>

                            {{-- Çocuk --}}
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Çocuk</span>
                                <div class="input-group input-group-sm" style="width: 120px;">
                                    <button type="button" class="btn btn-outline-secondary minus" data-type="child">−</button>
                                    <input type="text" class="form-control text-center" data-type="child" value="0" readonly>
                                    <button type="button" class="btn btn-outline-secondary plus" data-type="child">+</button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-md-2 d-grid align-self-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fi fi-rr-search me-2"></i>
                    </button>
                </div>
            </form>
        </div>

        {{-- TRANSFER --}}
        <div class="tab-pane fade" id="content-transfer" role="tabpanel">
            <form id="homeTransferSearchForm"
                  class="row g-3 needs-validation"
                  action="{{ localized_route('transfers') }}"
                  method="GET"
                  novalidate>

                {{-- Yön --}}
                <div class="col-12 text-center mb-2">
                    <div class="btn-group bg-white shadow-sm w-auto" role="group" aria-label="Yön Seçimi">
                        <input type="radio" class="btn-check" name="direction" id="home_oneway" value="oneway" checked required>
                        <label class="btn btn-outline-primary" for="home_oneway">Tek Yön</label>

                        <input type="radio" class="btn-check" name="direction" id="home_roundtrip" value="roundtrip" required>
                        <label class="btn btn-outline-primary" for="home_roundtrip">Gidiş - Dönüş</label>
                    </div>
                </div>

                {{-- Nereden --}}
                <div class="col-lg-2">
                    <label for="home_from_location_id" class="form-label">Nereden</label>
                    <div class="input-group">
                        <select class="form-select" id="home_from_location_id" name="from_location_id" required>
                            <option value="">Seçiniz</option>
                            @foreach($transferLocations as $location)
                                <option value="{{ $location['id'] }}">{{ $location['label'] }}</option>
                            @endforeach
                        </select>
                        <span class="input-group-text bg-white"><i class="fi fi-rr-marker"></i></span>
                    </div>
                </div>

                {{-- Nereye --}}
                <div class="col-lg-2">
                    <label for="home_to_location_id" class="form-label">Nereye</label>
                    <div class="input-group">
                        <select class="form-select" id="home_to_location_id" name="to_location_id" required>
                            <option value="">Seçiniz</option>
                            @foreach($transferLocations as $location)
                                <option value="{{ $location['id'] }}">{{ $location['label'] }}</option>
                            @endforeach
                        </select>
                        <span class="input-group-text bg-white"><i class="fi fi-rr-marker"></i></span>
                    </div>
                </div>

                {{-- Tarihler --}}
                <div class="col-lg-4">
                    <div class="row g-3">
                        <div class="col">
                            <label for="home_departure_date" class="form-label">Geliş Tarihi</label>
                            <div class="input-group has-validation">
                                <input type="text"
                                       class="form-control date-input"
                                       placeholder="Tarih seçin"
                                       id="home_departure_date"
                                       name="departure_date"
                                       required>
                                <span class="input-group-text bg-white"><i class="fi fi-rr-calendar"></i></span>
                            </div>
                        </div>

                        <div class="col d-none" id="homeReturnDateWrapper">
                            <label for="home_return_date" class="form-label">Dönüş Tarihi</label>
                            <div class="input-group has-validation">
                                <input type="text"
                                       class="form-control date-input"
                                       placeholder="Tarih seçin"
                                       id="home_return_date"
                                       name="return_date">
                                <span class="input-group-text bg-white"><i class="fi fi-rr-calendar"></i></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kişi (adult+child+infant) --}}
                <div class="col-lg-3">
                    <label for="home_transfer_guestInput" class="form-label">Kişi Sayısı</label>

                    <div class="guest-picker-wrapper position-relative">
                        <div class="input-group has-validation">
                            <input type="text"
                                   id="home_transfer_guestInput"
                                   class="form-control guest-wrapper"
                                   placeholder="Kişi sayısı seçin"
                                   readonly>
                            <span class="input-group-text bg-white"><i class="fi fi-rr-user"></i></span>
                        </div>

                        {{-- Hidden alanlar: guestpicker burayı dolduracak --}}
                        <input type="hidden" name="adults" value="2">
                        <input type="hidden" name="children" value="0">
                        <input type="hidden" name="infants" value="0">

                        <div class="guest-dropdown border rounded shadow-sm bg-white p-3 position-absolute w-100"
                             style="z-index: 10; top: 100%; display: none;">
                            {{-- Yetişkin --}}
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Yetişkin</span>
                                <div class="input-group input-group-sm" style="width: 120px;">
                                    <button type="button" class="btn btn-outline-secondary minus" data-type="adult">−</button>
                                    <input type="text" class="form-control text-center" data-type="adult" value="2" readonly>
                                    <button type="button" class="btn btn-outline-secondary plus" data-type="adult">+</button>
                                </div>
                            </div>

                            {{-- Çocuk --}}
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Çocuk</span>
                                <div class="input-group input-group-sm" style="width: 120px;">
                                    <button type="button" class="btn btn-outline-secondary minus" data-type="child">−</button>
                                    <input type="text" class="form-control text-center" data-type="child" value="0" readonly>
                                    <button type="button" class="btn btn-outline-secondary plus" data-type="child">+</button>
                                </div>
                            </div>

                            {{-- Bebek --}}
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Bebek</span>
                                <div class="input-group input-group-sm" style="width: 120px;">
                                    <button type="button" class="btn btn-outline-secondary minus" data-type="infant">−</button>
                                    <input type="text" class="form-control text-center" data-type="infant" value="0" readonly>
                                    <button type="button" class="btn btn-outline-secondary plus" data-type="infant">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Ara --}}
                <div class="col-lg-1 d-grid align-self-end">
                    <button type="submit" class="btn btn-primary d-block" title="Transferleri Göster">
                        <i class="fi fi-rr-search"></i>
                    </button>
                </div>
            </form>
        </div>

        {{-- TUR --}}
        <div class="tab-pane fade" id="content-tur" role="tabpanel">
            <form id="homeTourSearchForm"
                  class="row g-2 justify-content-center"
                  method="GET"
                  action="{{ localized_route('excursions') }}"
                  autocomplete="off">

                <div class="col-12 col-md-10">
                    <select class="form-select" name="category">
                        <option value="">Tüm Turlar</option>

                        @foreach ($categories as $cat)
                            <option value="{{ $cat['slug'] }}"
                                @selected(request('category') === $cat['slug'])>
                                {{ $cat['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 d-grid align-self-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fi fi-rr-search me-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
