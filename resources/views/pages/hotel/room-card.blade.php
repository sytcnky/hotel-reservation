<div class="room-card border rounded overflow-hidden mb-4">
    @php
    $state   = $room['state'] ?? 'no_context';
    $pricing = $room['pricing'] ?? null;
    $canBook = $state === 'priced';
    @endphp

    <!-- Oda görsel, başlık, fiyat -->
    <div class="d-flex flex-wrap p-3 gap-3">
        <!-- Sol: Görsel -->
        @php
        // İlk görseli al; yoksa ImageHelper üzerinden placeholder üret
        $coverImage = $room['images'][0] ?? \App\Support\Helpers\ImageHelper::normalize(null);
        $coverImage['alt'] = $room['name'] ?? ($coverImage['alt'] ?? '');
        @endphp

        <div>
            <x-responsive-image
                :image="$coverImage"
                class="room-thumb rounded img-fluid"
                sizes="150px"
            />
        </div>

        <div class="d-flex flex-fill align-items-xl-center">
            <!-- Oda Bilgileri -->
            <div class="flex-grow-1">
                <h5 class="mb-1">{{ $room['name'] }}</h5>

                @if(!empty($room['description']))
                <p class="text-muted mb-2">{{ $room['description'] }}</p>
                @endif

                <div class="text-success small">
                    <i class="fi fi-rr-credit-card align-middle"></i>
                    Son Dakika Fırsatı %35 İndirim + 9 Taksit
                </div>
            </div>
        </div>

        <!-- Sağ: Fiyat / Mesaj -->
        <div class="w-100 w-md-auto align-content-xl-center">
            <div class="d-flex justify-content-between flex-md-column text-md-end gap-1">

                @if($state === 'no_context')
                {{-- Form boş: sadece "başlayan fiyatlar" placeholder --}}
                <div class="fs-5 fw-bold">
                    Başlayan fiyatlar
                </div>

                @elseif($state === 'over_capacity')
                <div class="text-danger small">
                    {{ $room['capacity_message'] ?? 'Bu odanın maksimum kapasitesi aşıldı.' }}
                </div>

                @elseif($state === 'over_max_nights')
                <div class="text-danger small">
                    {{ $room['stay_message'] ?? 'Bu odanın maksimum konaklama süresi aşıldı.' }}
                </div>

                @elseif($state === 'unavailable')
                <div class="text-muted small">
                    Seçilen tarihlerde müsait değil.
                </div>

                @elseif($state === 'priced' && $pricing)
                {{-- Ana: Toplam fiyat --}}
                <div class="fs-5 fw-bold">
                    {{ number_format($pricing['total_amount'] ?? 0, 0, ',', '.') }}
                    {{ $pricing['currency'] ?? '₺' }}
                </div>

                {{-- Alt satırlar: oda bazlı / kişi bazlı detay --}}
                @if(($pricing['mode'] ?? null) === 'per_room')
                {{-- Oda bazlı fiyat --}}
                <div class="small text-muted">
                    {{ number_format($pricing['room_per_night'] ?? 0, 0, ',', '.') }}
                    {{ $pricing['currency'] ?? '₺' }} / gece
                </div>

                @elseif(($pricing['mode'] ?? null) === 'per_person')
                {{-- Kişi bazlı fiyat --}}
                @if(($pricing['adult_count'] ?? 0) > 0)
                <div class="small text-muted">
                    Yetişkin:
                    {{ number_format($pricing['adult_per_night'] ?? 0, 0, ',', '.') }}
                    {{ $pricing['currency'] ?? '₺' }} / gece
                </div>
                @endif

                @if(($pricing['child_count'] ?? 0) > 0)
                <div class="small text-muted">
                    Çocuk:
                    {{ number_format($pricing['child_per_night'] ?? 0, 0, ',', '.') }}
                    {{ $pricing['currency'] ?? '₺' }} / gece
                </div>
                @endif
                @endif

                @else
                <div class="small text-muted">
                    Fiyat bilgisi şu an hesaplanamadı.
                </div>
                @endif

            </div>
        </div>
    </div>

    <!-- Oda detayları -->
    <div class="room-details-wrapper collapse-wrapper border-top">
        <div class="room-details collapse-content p-3">
            <div class="row">
                {{-- Galeri --}}
                <div class="col-md-7">
                    <div class="gallery">
                        <!-- Ana görseller -->
                        <div class="main-gallery position-relative bg-black d-flex align-items-center justify-content-center rounded mb-3"
                             style="height: 300px;">
                            @foreach ($room['images'] as $index => $image)
                            <x-responsive-image
                                :image="$image"
                                preset="gallery"
                                class="gallery-image position-absolute top-0 start-0 w-100 h-100 {{ $index !== 0 ? 'd-none' : '' }}"
                                style="object-fit: contain;"
                                :data-index="$index"
                            />
                            @endforeach
                        </div>

                        <!-- Thumbnails -->
                        <div class="d-flex gap-2 overflow-auto thumbnail-scroll">
                            @foreach ($room['images'] as $index => $image)
                            <div class="flex-shrink-0 rounded overflow-hidden bg-black"
                                 data-gallery-thumb
                                 style="width: 72px; height: 72px; cursor: pointer;">
                                <x-responsive-image
                                    :image="$image"
                                    class="w-100 h-100"
                                    preset="gallery-thumb"
                                    style="object-fit: cover;"
                                    sizes="72px"
                                />
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Sağ: Bilgiler --}}
                <div class="col-md-5">
                    <h4 class="my-3 text-primary">Oda Özellikleri:</h4>

                    <ul class="list-unstyled mb-3 small">
                        <li class="border-bottom mb-2 p-1">
                            <i class="fi fi-rr-square-dashed align-middle"></i>
                            <span class="ms-1">Oda Boyutu:</span>
                            <strong>{{ $room['size'] }} m²</strong>
                        </li>

                        <li class="border-bottom mb-2 p-1">
                            <i class="fi fi-rs-bed-alt align-middle"></i>
                            <span class="ms-1">Yatak Tipi:</span>
                            <strong>{{ $room['bed_type'] }}</strong>
                        </li>

                        <li class="border-bottom mb-2 p-1">
                            <i class="fi fi-rs-users align-middle"></i>
                            <span class="ms-1">Kapasite:</span>
                            <strong>
                                {{ $room['capacity']['adults'] }} yetişkin
                                @if(($room['capacity']['children'] ?? 0) > 0)
                                , {{ $room['capacity']['children'] }} çocuk
                                @endif
                            </strong>
                        </li>

                        <li class="border-bottom mb-2 p-1">
                            <i class="fi fi-rs-leaf-heart align-middle"></i>
                            <span class="ms-1">Manzara:</span>
                            <strong>{{ $room['view'] }}</strong>
                        </li>

                        <li class="border-bottom mb-2 p-1">
                            <i class="fi fi-rr-smoking align-middle"></i>
                            <span class="ms-1">Sigara:</span>
                            <strong>{{ $room['smoking'] ? 'İçilebilir' : 'İçilmez' }}</strong>
                        </li>
                    </ul>

                    @if(!empty($room['facilities']))
                    <ul class="list-inline">
                        @foreach ($room['facilities'] as $facility)
                        <li class="list-inline-item badge bg-light text-dark border mb-1">
                            {{ $facility }}
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Oda Footer -->
    <div class="row border border-top">
        <!-- Sol taraf: Özellikler -->
        <div class="col-6 text-center room-footer-toggle px-3 py-2 bg-white" style="cursor: pointer;">
            <a class="btn btn-outline-secondary w-100 room-toggle-details">
                Oda Özellikleri
            </a>
        </div>

        <!-- Sağ taraf: Rezervasyon -->
        <div class="row col-6 text-white text-center py-2">
            <form method="POST"
                  action="{{ localized_route('hotel.book') }}"
                  class="w-100">
                @csrf

                @php
                // searchParams, parent view'den geliyor (HotelController::show)
                $ctx = $searchParams ?? null;
                $pricing = $room['pricing'] ?? null;

                // checkin/checkout Carbon → Y-m-d
                $checkin  = $ctx['checkin']  ?? null;
                $checkout = $ctx['checkout'] ?? null;
                @endphp

                <input type="hidden" name="hotel_id"   value="{{ $hotel['id'] }}">
                <input type="hidden" name="hotel_name" value="{{ $hotel['name'] }}">
                <input type="hidden" name="room_id"    value="{{ $room['id'] }}">
                <input type="hidden" name="room_name"  value="{{ $room['name'] }}">

                @if($checkin && $checkout)
                <input type="hidden" name="checkin"  value="{{ $checkin->format('Y-m-d') }}">
                <input type="hidden" name="checkout" value="{{ $checkout->format('Y-m-d') }}">
                <input type="hidden" name="nights"   value="{{ $ctx['nights'] ?? 1 }}">
                @endif

                <input type="hidden" name="adults"   value="{{ $ctx['adults']   ?? 2 }}">
                <input type="hidden" name="children" value="{{ $ctx['children'] ?? 0 }}">

                @if(!empty($ctx['board_type_id']))
                <input type="hidden" name="board_type_id" value="{{ $ctx['board_type_id'] }}">
                @endif

                {{-- Görsel ve yardımcı alanlar (snapshot için) --}}
                @php
                // Oda için: small > thumb
                $roomCover  = $room['images'][0]['small']  ?? $room['images'][0]['thumb']  ?? null;

                // Otel için fallback: small > thumb
                $hotelCover = $hotel['images'][0]['small'] ?? $hotel['images'][0]['thumb'] ?? null;

                $coverImage = $roomCover ?? $hotelCover ?? '';
                @endphp

                <input type="hidden" name="cover_image" value="{{ $coverImage }}">
                <input type="hidden" name="board_type_name" value="{{ $selectedBoardName }}">

                @if($pricing)
                <input type="hidden" name="currency"    value="{{ $pricing['currency'] ?? 'TRY' }}">
                <input type="hidden" name="price_total" value="{{ $pricing['total_amount'] ?? 0 }}">
                @endif

                <button type="submit"
                        class="btn btn-primary w-100"
                        {{ $canBook && $pricing ? '' : 'disabled' }}>
                Sepete Ekle
                </button>
            </form>
        </div>

    </div>

</div>
