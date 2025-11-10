<div class="room-card border rounded overflow-hidden mb-4">
    <!-- Oda görsel, başlık, fiyat -->
    <div class="d-flex flex-wrap p-3 gap-3">
        <!-- Sol: Görsel + Bilgi (mobilde yan yana) -->
        <div class="">
            <!-- Görsel -->
            <img src="{{ $room['images'][0] ?? '/images/placeholder.jpg' }}" alt="{{ $room['name'] }}" class="rounded img-fluid" style="max-width: 150px;">
        </div>

        <div class="d-flex flex-fill">
            <!-- Oda Bilgileri -->
            <div class="flex-grow-1">
                <h5 class="mb-1">{{ $room['name'] }}</h5>
                <p class="text-muted mb-2">{{ $room['description'] }}</p>
                <div class="text-danger small"><i class="fi fi-rs-user align-middle"></i> yeni üyelere 15% indirim!</span></div>
                <div class="text-success small"><i class="fi fi-rr-credit-card align-middle"></i> Son Dakika Fırsatı %35 İndirim + 9 Taksit</div>
            </div>
        </div>

        <!-- Sağ: Fiyat -->
        <div class="w-100 w-md-auto">
            <div class="d-flex justify-content-between flex-md-column text-md-end gap-1">
                <div class="fs-5 fw-bold">
                    <div class="fs-6 text-black-50">
                        <del>{{ number_format($room['price_per_night'] * 1.2, 0, ',', '.') }}₺</del>
                    </div>
                    {{ number_format($room['price_per_night'], 0, ',', '.') }}₺
                </div>
                <div class="small text-muted align-baseline">
                    Toplam <strong>1</strong> Gece<br>
                    15 Gün iptal süresi <span data-bs-toggle="tooltip" title="Otele giriş tarihinize 15 gün kalana kadar iptal - değişiklik talep etmeniz durumunda işlem ücretiniz tahsil edilerek paranız ödeme şeklinize göre iade edilir.">
                        <i class="fi fi-br-info"></i>
                    </span>
                </div>
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
                            <img src="{{ $image }}"
                                 class="gallery-image position-absolute top-0 start-0 w-100 h-100 {{ $index !== 0 ? 'd-none' : '' }}"
                                 style="object-fit: contain;"
                                 alt="Oda Görseli">
                            @endforeach
                        </div>

                        <!-- Thumbnails -->
                        <div class="d-flex gap-2 overflow-auto thumbnail-scroll">
                            @foreach ($room['images'] as $index => $image)
                            <div class="flex-shrink-0 rounded overflow-hidden bg-black"
                                 data-gallery-thumb
                                 style="width: 72px; height: 72px; cursor: pointer;">
                                <img src="{{ $image }}"
                                     class="w-100 h-100"
                                     style="object-fit: cover;">
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>


                {{-- Sağ: Bilgiler --}}
                <div class="col-md-5">

                    <h4 class="my-3 text-primary">Oda Özellikleri:</h4>

                    <ul class="list-unstyled mb-3 small">
                        <li class="border-bottom mb-2 p-1"><i class="fi fi-rr-square-dashed align-middle"></i> <span class="ms-1">Oda Boyutu:</span> <strong>{{ $room['size'] }} m²</strong></li>
                        <li class="border-bottom mb-2 p-1"><i class="fi fi-rs-bed-alt align-middle"></i> <span class="ms-1">Yatak Tipi:</span> <strong>{{ $room['bed_type'] }}</strong></li>
                        <li class="border-bottom mb-2 p-1"><i class="fi fi-rs-users align-middle"></i> <span class="ms-1">Kapasite:</span> <strong>{{ $room['capacity']['adults'] }} yetişkin{{ $room['capacity']['children'] > 0 ? ', '.$room['capacity']['children'].' çocuk' : '' }}</strong></li>
                        <li class="border-bottom mb-2 p-1"><i class="fi fi-rs-leaf-heart align-middle"></i> <span class="ms-1">Manzara:</span> <strong>{{ $room['view'] }}</strong></li>
                        <li class="border-bottom mb-2 p-1"><i class="fi fi-rr-smoking align-middle"></i> <span class="ms-1">Sigara:</span> <strong>{{ $room['smoking'] ? 'İçilebilir' : 'İçilmez' }}</strong></li>
                    </ul>

                    <ul class="list-inline">
                        @foreach ($room['facilities'] as $facility)
                        <li class="list-inline-item badge bg-light text-dark border mb-1">{{ $facility }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Oda Footer -->
    <div class="row border border-top">
        <!-- Sol taraf: Özellikler -->
        <div class="col-6 text-center room-footer-toggle flex-fill px-3 py-2 bg-white room-toggle-details" style="cursor: pointer;">
            Odanın Tüm Özellikleri
        </div>

        <!-- Sağ taraf: Rezervasyon -->
        <div class="row col-6 flex-fill bg-primary text-white text-center py-2" style="cursor: pointer;">
            <strong>Rezervasyon Yap</strong>
        </div>
    </div>
</div>
